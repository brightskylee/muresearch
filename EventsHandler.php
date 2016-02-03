<?php
include_once "FixCertificate.php";
include_once "databaseConfig.php";

function EventsHandler($query_str, $port){

	global $context;
	global $context2;
	
	
	$events_url_prefix = "https://webservices.doit.missouri.edu/MobileEventsWs/EventsWs.asmx/EventsItemSearch?pass=fhLSH4wunAM5v4t0zCp6&titleQuery=".urlencode($query_str)."&descrQuery=&venueQuery=&dateQuery=";
	$events_xml = file_get_contents($events_url_prefix,false, $context);
    $events_string_array = simplexml_load_string($events_xml, null, LIBXML_NOCDATA);
	$events_item_array = json_decode(json_encode($events_string_array), TRUE);
	$table_name = "events";
	
	
	if(array_key_exists('item', $events_item_array) && !empty($events_item_array)){
	
		$mysqli = new mysqli("127.0.0.1", "", "", "", $port);
		if($mysqli->connect_error){
			die("Connect error (".$mysqli->connect_errno .")");
		}
		if(!$mysqli->query("truncate RTINDEX $table_name")){
			echo "Error truncate:".$mysqli->error;
		}
	
		$events_id_prefix="https://webservices.doit.missouri.edu/MobileEventsWs/EventsWs.asmx/EventsItemDescr?pass=fhLSH4wunAM5v4t0zCp6&itemId=";
		
		$id = 1;
		foreach($events_item_array['item'] as $ind => $attr_array){
		
		    $events_id = $attr_array['id'];
			$title = $mysqli->real_escape_string($attr_array['title']); 
			$start = $mysqli->real_escape_string($attr_array['start']);
			
			$end = (is_array($attr_array['end']) && empty($attr_array['end'])) ? "" : $mysqli->real_escape_string($attr_array['end']);
			$venue = $mysqli->real_escape_string($attr_array['venue']);

			$events_xml_id = file_get_contents("https://webservices.doit.missouri.edu/MobileEventsWs/EventsWs.asmx/EventsItemDescr?pass=fhLSH4wunAM5v4t0zCp6&itemId=$events_id",false, $context);
			
			$events_string_array_id = simplexml_load_string($events_xml_id,null,LIBXML_NOCDATA);
			$events_item_array_id = json_decode(json_encode($events_string_array_id),TRUE);

			
			$link = $mysqli->real_escape_string($events_item_array_id['description']['link']);
			$descr = $mysqli->real_escape_string($events_item_array_id['description']['descr']); 
			
			$q = "INSERT INTO $table_name (id, title, start,end, venue,link,descr) values ($id, '$title', '$start', '$end','$venue','$link','$descr')";
			if(!$mysqli->query($q)){
				echo "Error insert:".$mysqli->error;
			}
			
			++$id;
		}
		
		$rank_query = "SELECT *,weight() AS weight FROM $table_name where MATCH('$query_str') LIMIT 0,1000 OPTION ranker=MATCHANY;";
		if(!($ranked_result = $mysqli->query($rank_query))){
			exit("Error rank: ". $mysqli->error);
		}
		
		//Fetch the re-ranked result
		while($row = $ranked_result->fetch_assoc()){
			$EventsResultArray[] = array("title" => $row['title'],
                                         "start" => $row['start'],
                                         "end" => $row['end'],
										 "venue" => $row['venue'],
										 "link" => $row['link'],
										 "description" => $row['descr']);
		}  
	
		return $EventsResultArray;
	}
    
	
}
?>
