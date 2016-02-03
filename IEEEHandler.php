<?php

include_once "FixCertificate.php";
include_once "databaseConfig.php";
include_once "URLParser.php";
include_once "oneRecordArrayFormation.php";
include_once "sphinxDB_connect.php";

function IEEEHandler($query_str, $sphinxPort, $numOfResults=10){

	$table_name = "ieee";
	$mysqli = sphinxDB_connect($sphinxPort);
	
	$IEEE_url_prefix = "http://ieeexplore.ieee.org/gateway/ipsSearch.jsp?hc=".$numOfResults."&ti=";
	$url_parser = new URLParser($IEEE_url_prefix.urlencode($query_str));
	$IEEE_item_array = $url_parser->XMLToArray();

	
	$ins_qry = "INSERT INTO $table_name (id,title, authors, pubtitle, pubtype, volume, issue, abstract, affiliation, issn, mdurl, pdf) VALUES";
	if(array_key_exists('document', $IEEE_item_array) && !empty($IEEE_item_array['document'])){

		$IEEE_item_array['document'] = oneRecordArrayFormation($IEEE_item_array['document']);
		$id = 1;
		foreach($IEEE_item_array['document'] as $ind => $attr_array){
			
			$title = array_key_exists('title',$attr_array) ? $attr_array['title'] : ""; 
			$authors = array_key_exists('authors', $attr_array) ? $attr_array['authors'] : "";
			
			if(!is_string($authors) || empty($authors)){
				continue;
			}
			
			$pubtitle = array_key_exists('pubtitle', $attr_array) ? $attr_array['pubtitle'] : "";
			$pubtype = array_key_exists('pubtype', $attr_array) ? $attr_array['pubtype'] : "";
			$volume = array_key_exists('volume', $attr_array) ? $attr_array['volume'] : "";
			$issue = array_key_exists('issue', $attr_array) ? $attr_array['issue'] : "";
			
			$abstract = array_key_exists('abstract', $attr_array) ? $attr_array['abstract'] : "";
			if($abstract != strip_tags($abstract)){
				$abstract = "";
			}
			
			$affiliation = array_key_exists('affiliation', $attr_array) ? $attr_array['affiliation'] : "";
			$issn = array_key_exists('issn', $attr_array) ? $attr_array['issn'] : "";
			$mdurl = array_key_exists('mdurl', $attr_array) ? $attr_array['mdurl'] : "";
			$pdf = array_key_exists('pdf', $attr_array) ? $attr_array['pdf'] : "";

			$title = $mysqli->real_escape_string($title);
			$authors = $mysqli->real_escape_string($authors);
			$pubtitle = $mysqli->real_escape_string($pubtitle);
			$pubtype = $mysqli->real_escape_string($pubtype);
			$volume = $mysqli->real_escape_string($volume);
			$issue = $mysqli->real_escape_string($issue);
			$abstract = $mysqli->real_escape_string($abstract);
			$affiliation = $mysqli->real_escape_string($affiliation);
			$issn = $mysqli->real_escape_string($issn);
			$mdurl = $mysqli->real_escape_string($mdurl);
			$pdf = $mysqli->real_escape_string($pdf);
			
			$ins_qry .= "($id, '$title', '$authors','$pubtitle','$pubtype','$volume','$issue','$abstract', '$affiliation', '$issn','$mdurl','$pdf'),";
			++$id;
		}
		$ins_qry = rtrim($ins_qry, ",");
		
		if(!$mysqli->query("TRUNCATE RTINDEX ".$table_name)){
			exit("Error truncate: ". $mysqli->error);
		}
		
		if(!$mysqli->query($ins_qry)){
			exit("Error insert: ". $mysqli->error);
		}
		
		//Rerank the result
		$rank_query = "SELECT *,weight() AS weight FROM $table_name where MATCH('$query_str') LIMIT 0,1000 OPTION ranker=MATCHANY;";
		if(!($ranked_result = $mysqli->query($rank_query))){
			exit("Error rank: ". $mysqli->error);
		}
		
		//Fetch the re-ranked result
		while($row = $ranked_result->fetch_assoc()){
			$IEEEResultArray[] = array("title" => $row['title'],
                                         "authors" => $row['authors'],
                                         "pubtitle" => $row['pubtitle'],
										 "pubtyle" => $row['pubtype'],
										 "volume" => $row['volume'],
										 "issue" => $row['issue'],
										 "abstract" => $row['abstract'],
										 "issn" => $row['issn'],
										 "mdurl" => $row['mdurl'],
										 "pdf" => $row['pdf']); 
		}  
		
		return $IEEEResultArray;
	}
	
	
}


?>
