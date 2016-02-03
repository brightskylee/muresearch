<?php

include_once "FixCertificate.php";
include_once "URLParser.php";
include_once "oneRecordArrayFormation.php";
include_once "sphinxDB_connect.php";

function pubmedHandler($query_str, $sphinxPort, $numOfResults=10){
	
	$table_name = "pubmed";
	$mysqli = sphinxDB_connect($sphinxPort);
	
	$pubmedSearchURLPrefix = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?RetMax=".$numOfResults."&db=pubmed&term=";
	$url_parser = new URLParser($pubmedSearchURLPrefix.urlencode($query_str));
	$pubmedSearchResultArray = $url_parser->XMLToArray();
	
	if(array_key_exists('Count', $pubmedSearchResultArray) && $pubmedSearchResultArray['Count'] > 0){//if pubmed search returns results
		
		if($pubmedSearchResultArray['Count'] == 1 || $pubmedSearchResultArray['RetMax'] == 1){
			$pubmedSearchResultArray['IdList']['Id'] = array($pubmedSearchResultArray['IdList']['Id']);
		}
		
		$pubmedFetchURL = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&retmode=xml&id=";
		foreach($pubmedSearchResultArray['IdList']['Id'] as $ind => $pubid){
			$pubmedFetchURL .= $pubid.",";
		}
		$pubmedFetchURL = rtrim($pubmedFetchURL,",");
		$pubmedFetchXML = file_get_contents($pubmedFetchURL);
		
		if($pubmedFetchXML == FALSE){
			die("file_get_contents pubmed fetch failed");
		}
		
		$pubmedFetchResult = simplexml_load_string($pubmedFetchXML);
		$pubmedFetchResultArray = json_decode(json_encode($pubmedFetchResult), TRUE);
		
		if(!array_key_exists('PubmedArticle', $pubmedFetchResultArray)){
			trigger_error("PubmedArticle not set in pubmedFetchResultArray", E_USER_ERROR);
			return FALSE;
		}
		
		$pubmedFetchResultArray['PubmedArticle'] = isset($pubmedFetchResultArray['PubmedArticle'][0]) 
													? $pubmedFetchResultArray['PubmedArticle'] 
													: array($pubmedFetchResultArray['PubmedArticle']);
		
		$id = 1;
		$ins_qry = "INSERT INTO $table_name (id,pubid,title,authors,abstract,url,date,keywords,affiliations) VALUES";
		foreach ($pubmedFetchResultArray['PubmedArticle'] as $record){

			if(!array_key_exists('MedlineCitation', $record)){
				trigger_error("MedlineCitation not set in one pubmed record", E_USER_ERROR);
				return FALSE;
			}
  
            $pubid = $record['MedlineCitation']['PMID'];
            $title = $record['MedlineCitation']['Article']['ArticleTitle'];
			
			$abstract = "";
			if(array_key_exists("Abstract", $record['MedlineCitation']['Article']) && array_key_exists("AbstractText", $record['MedlineCitation']['Article']['Abstract'])){

					$record['MedlineCitation']['Article']['Abstract']['AbstractText'] = 
						is_array($record['MedlineCitation']['Article']['Abstract']['AbstractText']) ? 
						$record['MedlineCitation']['Article']['Abstract']['AbstractText'] : 
						array($record['MedlineCitation']['Article']['Abstract']['AbstractText']);
							
						foreach($record['MedlineCitation']['Article']['Abstract']['AbstractText'] as $ab){
							if(is_string($ab)){
								$abstract .= $ab . " ";	
							}
						}		
			}
			
			$authors = "";
			$affiliations = "";
			if(array_key_exists('AuthorList', $record['MedlineCitation']['Article']) 
				&& array_key_exists('Author', $record['MedlineCitation']['Article']['AuthorList'])
				&& !empty($record['MedlineCitation']['Article']['AuthorList'])){
					
				$record['MedlineCitation']['Article']['AuthorList']['Author'] =
					isset($record['MedlineCitation']['Article']['AuthorList']['Author'][0]) ?
					$record['MedlineCitation']['Article']['AuthorList']['Author'] :
					array($record['MedlineCitation']['Article']['AuthorList']['Author']);
						
					foreach($record['MedlineCitation']['Article']['AuthorList']['Author'] as $a){
						
						if(array_key_exists('LastName', $a) && array_key_exists('ForeName', $a)){
							$authors .= $a['LastName'] . ", " . $a['ForeName']. " | ";
							if(array_key_exists('AffiliationInfo', $a) && !empty($a['AffiliationInfo'])){
								$a['AffiliationInfo'] = isset($a['AffiliationInfo'][0]) ?
								$a['AffiliationInfo'] : array($a['AffiliationInfo']);
								foreach($a['AffiliationInfo'] as $af){
									$affiliations .= $af['Affiliation'].";";
								}
								$affiliations = rtrim($affiliations, ";");
								$affiliations .= " | ";
							}
							else{
								$affiliations .= "NULL"." | ";
							}
						}
					}
					$authors = rtrim($authors, "|");
					$affiliations = rtrim($affiliations, "|");
			}
			
			$keywords = "";
			if(array_key_exists("KeywordList", $record['MedlineCitation'])
				&& !empty($record['MedlineCitation']['KeywordList'])){
					
					$record['MedlineCitation']['KeywordList']['Keyword'] = 
						isset($record['MedlineCitation']['KeywordList']['Keyword'][0]) ? 
						$record['MedlineCitation']['KeywordList']['Keyword'] : 
						array($record['MedlineCitation']['KeywordList']['Keyword']);
					
					foreach($record['MedlineCitation']['KeywordList']['Keyword'] as $k){
						$keywords .= $k . " | ";
					}
					$keywords = rtrim($keywords, "|");
			}
			
			$date = "";
			if(array_key_exists("ArticleDate", $record['MedlineCitation']['Article'])
				&& !empty($record['MedlineCitation']['Article']['ArticleDate'])){
				$date = $record['MedlineCitation']['Article']['ArticleDate']['Year'] . "-" .
						$record['MedlineCitation']['Article']['ArticleDate']['Month'] . "-" .
						$record['MedlineCitation']['Article']['ArticleDate']['Day'];
			}
			
			$url = 'http://www.ncbi.nlm.nih.gov/pubmed/'.$pubid;
			
            $title = $mysqli->real_escape_string($title);
			$abstract = $mysqli->real_escape_string($abstract);
			$authors = $mysqli->real_escape_string($authors);
			$affiliations = $mysqli->real_escape_string($affiliations);
			$keywords = $mysqli->real_escape_string($keywords);
			
			$ins_qry .= "($id, $pubid, '$title', '$authors', '$abstract', '$url', '$date', '$keywords', '$affiliations'),";
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
			$pubmedResultArray[] = array("pubid" => $row['pubid'],
                                         "title" => $row['title'],
                                         "abstract" => $row['abstract'],
										 "authors" => $row['authors'],
										 "keywords" => $row['keywords'],
										 "date" => $row['date'],
                                         "url" => $row['url'],
										 "affiliations" => $row['affiliations']); 
		}

		return $pubmedResultArray;
	}
    
	return FALSE;
}

//print_r(pubmedHandler("soybean protein", 9306));
?>
