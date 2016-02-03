<?php

include_once "databaseConfig.php";
include_once "FixCertificate.php";
include_once "People.php";
include_once "./HumanNameParser/init.php";

$offset = 32397;
$range = 2000;

$table = "MOSpaceAll";
$query = "SELECT creator FROM ".$table. " Limit ".$offset.",". $range;
$baseURL = "https://webservices.doit.missouri.edu";

$mysqli = new mysqli(HOSTNAME, USERNAME, PASSWD, DATABASE);
if($mysqli->connect_errno){
	exit("Error: ". $mysqli->connect_error);
}

if($stmt = $mysqli->prepare($query)){
	
	$stmt->execute();
	$stmt->bind_result($result);
	
	//For testing purpose only 
	$i = 0;
	
	while($stmt->fetch()){
		echo "row ".($offset + $i).": ";
		$i++;
		if(!empty($result)){
			$people_array = explode(";", $result);
			foreach($people_array as $p){
				if(str_word_count($p) == 1){
					continue;
				}
				
				if(strpos("$p", ", ") !== false){
					$tmp = explode(",", $p);
					if(in_array(trim($tmp[1]),array("Jr.")) && isset($tmp[2])){
						$p = $tmp[0] . ", " . $tmp[2];
					}
					else{
						$p = implode(",", array_slice($tmp, 0, 2));
					}
				}
				//$p = implode(",", array_slice(explode(",", $p), 0, 2));
				//echo $p."\n";
				$parser = new HumanNameParser_Parser($p);
				$searchURL = "/peoplefinderWS/peoplefinderws.asmx/PeopleFinderXml?firstName=".urlencode($parser->getFirst())."&lastname=".urlencode($parser->getLast())."&department=&phoneno=&email=";
				$peopleInfoXML = file_get_contents($baseURL.$searchURL, false, $context) or die("The mospace server might failed\n");
				//echo $baseURL.$searchURL;
				$peopleInfoXMLObject = simplexml_load_string($peopleInfoXML, null, LIBXML_NOCDATA);
				if($peopleInfoXMLObject['found'] == 0 || $peopleInfoXMLObject['found'] >= 49){
					continue;
				}
			//echo "<pre>";
			///print_r($peopleInfoXMLObject->Person);
			//echo "</pre>";
			
			$personObject = new Person();
			$personObject->setLastName($parser->getLast());
			$personObject->setFirstName($parser->getFirst());
			
			//For test purpose only
			//echo $personObject->getLastName().", ".$personObject->getFirstName().";   ";
			
			$personObject->setDepartment($peopleInfoXMLObject->Person->Department);
			$personObject->setTitle($peopleInfoXMLObject->Person->Title);
			//$personObject->setImageURL("http://freelanceme.net/Images/default%20profile%20picture.png");
			if($peopleInfoXMLObject->Person->Phone != null){
				$personObject->setPhone($peopleInfoXMLObject->Person->Phone);
			}
			$personObject->setEmail($peopleInfoXMLObject->Person->{'E-mail'});
			$personObject->insertToDatabase();
			
			}
			echo "\n";	
			//For testing purpose only
			//if($i++ > 10){
				//break;
			//}
		}
	}
	
	
}

?>
