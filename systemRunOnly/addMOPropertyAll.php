<?php

require_once('vendor/autoload.php');
require_once("./HumanNameParser/init.php");
require_once('databaseConfig.php');
require_once('URLParser.php');
use Everyman\Neo4j\Cypher\Query;

function findIfProfessor($title_str, $keywords=array("Professor", "Prof")){

	//@Debug
	echo "\nThe title is: $title_str\n";

	foreach($keywords as $k){
		if(($found = stripos($title_str, $k, 0)) !== false){
			return 1;
		}
	}
	return 0;
}

$client = new Everyman\Neo4j\Client('localhost', 7474);
$client->getTransport()
    ->setAuth('neo4j', 'muresearch');
	
$q = "match (u:Person) return u.name as name";
$query = new Query($client, $q);
$result = $query->getResultSet();

$mysqli = new mysqli(HOSTNAME, USERNAME, PASSWD, DATABASE);
if($mysqli->connect_errno){
	die("error: ". $mysqli->connect_error);
}

$localTable = "MOSpacePeople";

$transaction = $client->beginTransaction();
foreach($result as $r){

	$parser = new HumanNameParser_Parser($r['name']);
	$last = $parser->getLast();
	$first = $parser->getFirst();

	//@Debug
	echo "Checking ".$last.", ".$first.".....";
	$q = "SELECT * from ".$localTable." where firstname='".$mysqli->real_escape_string($first)."' and lastname='".$mysqli->real_escape_string($last)."'";

	$fromMU = 0;
	$isProfessor = 0;

	if($result = $mysqli->query($q)){
		if($result->num_rows == 1){
			//@Debug
			echo "found in local database.....";

			$fromMU = 1;
			if($resAssocArray = $result->fetch_assoc()){
				if(isset($resAssocArray['Title'])){
					$isProfessor = findIfProfessor($resAssocArray['Title']) == 1 ? 1 : 0;

					//@Debug
					if($isProfessor == 1) echo "is a prof\n"; else echo "NOT a prof\n";
				}
				else{
					$isProfessor = 0;
					//@Debu
					echo "NOT a prof\n";
				}
			}
			else{
				die("fetch result from MOSpacePeople failed");
			}
		}
		else{
			$peopleFinderURL = "https://webservices.doit.missouri.edu/peoplefinderWS/peoplefinderws.asmx/PeopleFinderXml?firstName=".urlencode($first)."&lastname=".urlencode($last)."&department=&phoneno=&email=";
			$url_parser = new URLParser($peopleFinderURL);
			$retArr = $url_parser->XMLToArray();

			if(intval($retArr['@attributes']['found']) == 1){
				//@Debug
				echo "found in Peoplefinder...";

				$fromMU = 1;
				$title = (array_key_exists("Title", $retArr['Person']) && !empty($retArr['Person']['Title'])) ? $retArr['Person']['Title'] : "";
				$isProfessor = findIfProfessor($title) == 1 ? 1 : 0;

				//@Debug
				if($isProfessor == 1) echo "is a prof\n"; else echo "NOT a prof\n";


			}
			else{
				$isProfessor = 0;
				//@Debug
				echo "NOT a prof\n";
			}
		}

	}
	else{
		die("query: ". $q."\nFailed");
	}
	
	$q_str = "match (u:Person {name: \"". $r['name']."\"}) set u.fromMU = ".$fromMU.", u.isProfessor = ".$isProfessor;
	$query = new Query($client, $q_str);
	$result = $transaction->addStatements($query);
}

$transaction->commit();
