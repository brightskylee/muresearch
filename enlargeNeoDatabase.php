<?php
require_once("CoauthorNetworkCrawler.php");
require_once('vendor/autoload.php');
use Everyman\Neo4j\Cypher\Query;


//TODO: Actually need a better algorithm to refine the affiliation string
//So far only filter out the phrase with the possibleAffiliationKeywords
function filterAffiliation($affiliation){

	$possibleAffiliationKeywords = array("university", "institute", "academy", "laboratory");
	
	$affiliation_parts = explode(",", $affiliation);
	foreach($possibleAffiliationKeywords as $keyword){	
		foreach($affiliation_parts as $part){
			if(strpos(strtolower($part), $keyword) !== FALSE)
				return trim($part);
		}
	}
	
	return "";
}
	

function enlargeNeoDatabase($unifiedName){

	
	$client = new Everyman\Neo4j\Client('localhost', 7474);
	$client->getTransport()
    	->setAuth('neo4j', 'muresearch');

	
	//Check if the author exists
	$exist = FALSE;
	$authorExists = "match (u:Person {name: \"". $unifiedName. "\"}) return u";
	$query = new Query($client, $authorExists);
	$result = $query->getResultSet();

	foreach($result as $r){
		$exist = TRUE;
	}

	if(!$exist){//Author does not exists. Must be of Mizzou
		
		$crawler = new CoauthorNetworkCrawler($unifiedName, $affiliation = "University of Missouri");
		$crawler->insertDB('localhost',7474,'neo4j','muresearch');
		
		$setHasSearched = "match (u:Person {name: \"". $unifiedName. "\"}) set u.hasSearched = 1";
		$query = new Query($client, $setHasSearched);
		$result = $query->getResultSet();
		return;
	}
	
	
	
	
	//Check if we need to crawl the network for this author while the author already exists.
	$hasSearched = "match (u:Person {name: \"". $unifiedName. "\"}) return has(u.hasSearched) as hasSearched";
	$query = new Query($client, $hasSearched);
	$result = $query->getResultSet();

	
	foreach($result as $r){
		
		if($r['hasSearched']){//The user has been searched before. No need to re-search it
			return;
		}
		else{//The user has NOT been seasrched before.
			
			//Get the affiliation of this user
			
			$getAffiliation = "match (u:Person {name: \"". $unifiedName. "\"}) return u.affiliation as affiliation";
			$query = new Query($client, $getAffiliation);
			$result = $query->getResultSet();
			

			foreach($result as $r){
				
				if(!empty($r['affiliation'])){//Affiliation info presented
					$affiliation = filterAffiliation($r['affiliation']);
				}
				else{//Affiliation info empty
					$affiliation = "";
				}
			}
			
			$setHasSearched = "match (u:Person {name: \"". $unifiedName. "\"}) set u.hasSearched = 1";
			$query = new Query($client, $setHasSearched);
			$result = $query->getResultSet();
			
			//echo $affiliation."\n";
			$crawler = new CoauthorNetworkCrawler($unifiedName, $affiliation);
			//print_r($crawler->crawlPubmed());
			$crawler->insertDB('localhost',7474,'neo4j','muresearch');
			return;
		}
			
	}
}

if(isset($_POST['data']) && !empty($_POST['data'])){
	$stringJSON = stripslashes($_POST['data']);
	$d = json_decode($stringJSON, true);
	$unifiedName = $d['name'];
	
	enlargeNeoDatabase($unifiedName);
}
//enlargeNeoDatabase("Valliyodan, Babu");
//enlargeNeoDatabase("Liu, Yang");
?>
