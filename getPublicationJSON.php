<?php
require_once('vendor/autoload.php');
use Everyman\Neo4j\Cypher\Query;

$client = new Everyman\Neo4j\Client('localhost', 7474);
$client->getTransport()
    ->setAuth('neo4j', 'muresearch');
	
if(isset($_POST['data']) && !empty($_POST['data'])){
	
	$stringJSON = stripslashes($_POST['data']);
	$d = json_decode($stringJSON, true);
	$unifiedName = $d['name'];
	
	$q = "match (u:Person {name:\"".$unifiedName."\"}) - [r:Wrote] -> (p:Publication) return p.title as title, p.url as url";
	$query = new Query($client, $q);
	$result = $query->getResultSet();
	
	$publicationArray = array();
	foreach($result as $r){
		$url = (isset($r['url'])) ? $r['url'] : "";
		$publicationArray[] = array("title" => $r['title'], "url" => $url);
	}

	echo json_encode($publicationArray);
}
?>