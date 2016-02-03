<?php
require_once('vendor/autoload.php');
use Everyman\Neo4j\Cypher\Query;

$client = new Everyman\Neo4j\Client('localhost', 7474);
$client->getTransport()
    ->setAuth('neo4j', 'muresearch');

/*
function findMaxNumCoauthoredPapers($client, $unifiedName){
	$q = "match (u:Person {name: \"".$unifiedName."\"}) - [r:COAUTHORED] - (:Person) return max(r.numOfPapers) as num";
	$query = new Query($client, $q);
	$result = $query->getResultSet();

	foreach($result as $r){
		return $r['num'];
	}
}

function findMaxNumPublication($client, $unifiedName){
	$q = "match (u:Person {name: \"".$unifiedName."\"}) - [r:COAUTHORED] - (u2:Person) return u.publicationNum as maxMain,max(u2.publicationNum) as maxOthers";

	$query = new Query($client, $q);
	$result = $query->getResultSet();

	foreach($result as $r){
		if($r['maxMain'] >= $r['maxOthers']){
			return $r['maxMain'];
		}
		else{
			return $r['maxOthers'];
		}
	}
}
*/

function mapSize($maxSize, $minSize, $data){
	if($data <= ($maxSize - $minSize)){
		return $data+$minSize;
	}
	else{
		return $maxSize;
	}
}



if(isset($_POST['data']) && !empty($_POST['data'])){
	
	$stringJSON = stripslashes($_POST['data']);
	$d = json_decode($stringJSON, true);
	$unifiedName = $d['name'];


	//$maxNodeSizePubNum = findMaxNumPublication($client, $unifiedName);
	$maxNodeSize = 50;
	$minNodeSize = 15;

	//$maxEdgeWidthCoauthorNum = findMaxNumCoauthoredPapers($client, $unifiedName);
	$maxEdgeWidth = 10;
	$minEdgeWidth = 1;


	$q = "match (u:Person {name: \"".$unifiedName."\"})-[r:COAUTHORED]-(u2:Person)
		return u.name as mainAuthor,
			u.publicationNum as mainAuthorPubNum,
			u.isProfessor as mainAuthorProfessor,
			r.numOfPapers as numCoauthored,
			u2.name as coAuthor,
			u2.fromMU as fromMU,
			u2.publicationNum as coAuthorPubNum,
			u2.isProfessor as coAuthorProfessor";

	$query = new Query($client, $q);
	$result = $query->getResultSet();


	$nodeArray = array();
	$edgeArray = array();

	$setMain = 0;
	foreach($result as $r){

		if( $setMain == 0){
			$nodeArray[] = array("data" => array("id"=>$r['mainAuthor'],
												"name"=>$r['mainAuthor'],
												"isProfessor"=>$r['mainAuthorProfessor'],
												"nodeSize"=> mapSize($maxNodeSize, $minNodeSize, $r['mainAuthorPubNum']))
												//"nodeSize" => 30)
								);
			$setMain = 1;
		}

		$nodeArray[] = array("data" => array("id"=>$r['coAuthor'],
											"name"=>$r['coAuthor'],
											"fromMU"=>$r['fromMU'],
											"isProfessor"=>$r['coAuthorProfessor'],
											"nodeSize"=> mapSize($maxNodeSize, $minNodeSize, $r['coAuthorPubNum']))
											//"nodeSize"=>10)
							);

		$edgeArray[] = array("data" => array("number" => $r['numCoauthored'],
											"source"=>$r['mainAuthor'],
											"target"=>$r['coAuthor'],
											"edgeSize" => mapSize($maxEdgeWidth, $minEdgeWidth, $r['numCoauthored']))
							);
	}
	
	$q = "match (u1) - [r:COAUTHORED] - (u2)
		where u1 - [:COAUTHORED] - ({name: \"".$unifiedName."\"})
		AND u2 - [:COAUTHORED] - ({name: \"".$unifiedName."\"})
		AND ID(u1) < ID(u2)
		return u1.name as author1, r.numOfPapers as numCoauthored, u2.name as author2";

	$query = new Query($client, $q);
	$result = $query->getResultSet();
	
	foreach($result as $r){
		$edgeArray[] = array("data" => array("number" => $r['numCoauthored'],
											"source" => $r['author1'],
											"target" => $r['author2'],
											"edgeSize" => mapSize($maxEdgeWidth, $minEdgeWidth, $r['numCoauthored']))
						);

	}

	echo json_encode(array("nodes" => $nodeArray, "edges" => $edgeArray));

	
}

