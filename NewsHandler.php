<?php
include_once "FixCertificate.php";
include_once "databaseConfig.php";

function NewsHandler($query_str, $port){

	global $context;
	
	$news_url_prefix = "https://webservices.doit.missouri.edu/MobileNewsWs/NewsWebService.asmx/NewsItemSearch?passcode=KDoQKBZISnXz51Do6cOF&searchString=";
	$news_xml = file_get_contents($news_url_prefix.urlencode($query_str), false, $context);
	$news_string_array = simplexml_load_string($news_xml, null, LIBXML_NOCDATA);
	$news_item_array = json_decode(json_encode($news_string_array), TRUE);
	$table_name = "news";

	 if(array_key_exists('item', $news_item_array)){
	
		$news_item_array['item'] = (isset($news_item_array['item'][0])) ? $news_item_array['item'] : array($news_item_array['item']);
		$mysqli = new mysqli("127.0.0.1", "", "", "", $port);

		if($mysqli->connect_error){
			die("Connect error (".$mysqli->connect_errno .")");
		}
		if(!$mysqli->query("truncate RTINDEX $table_name")){
			echo "Error truncate:".$mysqli->error;
		}
			
		$id = 1;
		 foreach($news_item_array['item'] as $ind => $attr_array){
			 
			$title = $mysqli->real_escape_string($attr_array['title']);
			$link = $mysqli->real_escape_string($attr_array['link']);
			$pubdate = $mysqli->real_escape_string($attr_array['pubdate']);
		
			$q = "INSERT INTO $table_name (id, title, link, pubdate) values($id, '$title', '$link', '$pubdate')";
			if(!$mysqli->query($q)){
				exit("Error insert: ". $mysqli->error);
			}
		++$id;
		}
		
		$rank_query = "SELECT *,weight() AS weight FROM $table_name where MATCH('$query_str') LIMIT 0,1000 OPTION ranker=MATCHANY;";
		if(!($ranked_result = $mysqli->query($rank_query))){
			exit("Error rank: ". $mysqli->error);
		}
		
		//Fetch the re-ranked result
		while($row = $ranked_result->fetch_assoc()){
			$NewsResultArray[] = array("title" => $row['title'],
                                         "link" => $row['link'],
                                         "pubdate" => $row['pubdate']);
		}
		
		if(isset($NewsResultArray)){
			return $NewsResultArray;	
		}
		else{
			return null;
		}
		
	}
	
	
}
//print_r(NewsHandler("mizzou", 9306));
?>
