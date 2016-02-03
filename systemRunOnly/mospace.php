<?php
include_once "FixCertificate.php";
include_once "databaseConfig.php";

$i = 0;
//471 lines
//$url = "https://mospace.umsystem.edu/oai/request?verb=ListIdentifiers&metadataPrefix=oai_dc&set=com_10355_29";
$url = "https://mospace.umsystem.edu/oai/request?verb=ListRecords&metadataPrefix=oai_dc";
//$url = "https://mospace.umsystem.edu/oai/request?verb=ListRecords&resumptionToken=MTo0MDB8Mjpjb21fMTAzNTVfMjl8Mzp8NDp8NTpvYWlfZGM=";
//$url = "https://mospace.umsystem.edu/oai/request?verb=ListRecords&metadataPrefix=oai_dc&set=com_10355_9";
function parseOAI($url, $mysqli){
	
	global $context;
	global $i;
	
	
	//$identifierXML = file_get_contents($url, false, $context);
	$listRecordXML = file_get_contents($url, false, $context) or die("The mospace server might failed\n");
	//$identifierXMLObject = simplexml_load_string($identifierXML, null, LIBXML_NOCDATA);
	$listRecordXMLObject = simplexml_load_string($listRecordXML, null, LIBXML_NOCDATA);
	//$identifierArray = json_decode(json_encode($identifierXMLObject), TRUE);
	$recordXMLObject = $listRecordXMLObject->ListRecords->record;
	
	$nextTokenArray = json_decode(json_encode($listRecordXMLObject->ListRecords->resumptionToken), true);
	if(array_key_exists(0, $nextTokenArray)){
		$nextToken = $nextTokenArray[0];
	}
	
//if(empty($nextTokenObjectArray)){
//	echo "already last page";
//}
//else{
//	var_dump($nextTokenObjectArray[0]);
//}
	
	foreach($recordXMLObject as $ind => $recordNode){
		$i++;
		$recordArray = json_decode(json_encode($recordNode->metadata->children('oai_dc', 1)->dc->children('dc', 1)), TRUE);
		
		foreach($recordArray as $tag => $val){
			if(is_array($val)){
				$recordArray[$tag] = implode("|", $val);
			}
		}
		
		echo "<pre>";
		print_r($recordArray);
		echo "</pre>";
		
		$table_name = "MOSpaceAll";
		
		$query_column_name_str = '';
		$query_column_value_str = '';
		
		
		foreach($recordArray as $tag => $val){
			$query_column_name_str .= $tag.", ";
			$query_column_value_str .= "\"".$mysqli->real_escape_string($val)."\"".", ";
		}
		
		$query_column_name_str = preg_replace('/,\s*$/', "", $query_column_name_str);
		$query_column_value_str = preg_replace('/,\s*$/', "", $query_column_value_str);
		$query_str = "insert ignore into ".$table_name. " (". $query_column_name_str. ")". " values(".$query_column_value_str.");";

		if(!$mysqli->query($query_str)){
			die( "Error insert:".$mysqli->error);
		}

	}
	
	

	/*
	if(array_key_exists("resumptionToken", $identifierArray['ListIdentifiers'])){
		if(is_string($identifierArray['ListIdentifiers']['resumptionToken'])){
			$nextToken = $identifierArray['ListIdentifiers']['resumptionToken'];
		}
	}
	//$tag_array = array();
	
	
	
	foreach($identifierArray['ListIdentifiers']['header'] as $ind => $val){
		$i++;
		$record_url = "https://mospace.umsystem.edu/oai/request?verb=GetRecord&metadataPrefix=oai_dc&identifier=".$val['identifier'];
		//echo $record_url;
		$record_xml = file_get_contents($record_url, false, $context);
		
		$recordXMLObject = simplexml_load_string($record_xml, null, LIBXML_NOCDATA);
		$rNode = $recordXMLObject->GetRecord->record;
		$recordXMLArray = json_decode(json_encode($rNode->metadata->children('oai_dc', 1)->dc->children('dc', 1)), true);
		//$tag_array = array_unique(array_merge($tag_array, array_keys($array)));
		
		foreach($recordXMLArray as $tag => $val){
			if(is_array($val)){
				$recordXMLArray[$tag] = implode("; ", $val);
			}
		}
		
		//	echo "<pre>";
			print_r($recordXMLArray);
		//	echo "</pre>";
		

		$table_name = "MOSpaceAll";
		
		$query_column_name_str = '';
		$query_column_value_str = '';
		
		
		foreach($recordXMLArray as $tag => $val){
			$query_column_name_str .= $tag.", ";
			$query_column_value_str .= "\"".$mysqli->real_escape_string($val)."\"".", ";
		}
		
		$query_column_name_str = preg_replace('/,\s*$/', "", $query_column_name_str);
		$query_column_value_str = preg_replace('/,\s*$/', "", $query_column_value_str);
		$query_str = "insert ignore into ".$table_name. " (". $query_column_name_str. ")". " values(".$query_column_value_str.");";
        //echo $query_str;
        //echo "<br>";
		//echo $query_column_name_str;
		//echo "<br>";
		//echo $query_column_value_str;
		if(!$mysqli->query($query_str)){
			die( "Error insert:".$mysqli->error);
		}

		/*
		foreach($recordXMLNode->record as $rNode){
		
			//var_dump($rNode->metadata->children('oai_dc', 1));

			var_dump($rNode->metadata->children('oai_dc', 1)->dc->children('dc', 1));
			echo "</pre>";
		} 
	} */
	if(isset($nextToken)){

		$nextURL = "https://mospace.umsystem.edu/oai/request?verb=ListRecords&resumptionToken=".$nextToken;
        return $nextURL;
    }
    else{
        return null;
    }
}

$mysqli = new mysqli(HOSTNAME, USERNAME, PASSWD, DATABASE);
if($mysqli->connect_error){
	die("Connect error (".$mysqli->connect_errno .")");
}

$next = parseOAI($url, $mysqli);
while(!empty($next)){
    $next = parseOAI($next, $mysqli);
}
echo "Insert to database succeed";
echo $i;

//$nextTokenObjectArray = json_decode(json_encode($XMLObjectArray -> ListIdentifiers ->resumptionToken), true);
//if(empty($nextTokenObjectArray)){
//	echo "already last page";
//}
//else{
//	var_dump($nextTokenObjectArray[0]);
//}
//echo "<pre>";
//var_dump($XMLObjectArray);
//echo "</pre>";
?>
