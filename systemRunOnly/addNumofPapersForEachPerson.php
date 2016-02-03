<?php

require_once('vendor/autoload.php');
use Everyman\Neo4j\Cypher\Query;

$client = new Everyman\Neo4j\Client('localhost', 7474);
$client->getTransport()
    ->setAuth('neo4j', 'muresearch');
	
$q = "match (u:Person) return u.name as name";
$query = new Query($client, $q);
$result = $query->getResultSet();

$transaction = $client->beginTransaction();
foreach($result as $r){

	
	$q_str = "match (u:Person {name: \"". $r['name']."\"}) - [:Wrote] - (p) with u, count(distinct p) as num set u.publicationNum = num";
	$query = new Query($client, $q_str);
	$result = $transaction->addStatements($query);
}

$transaction->commit();