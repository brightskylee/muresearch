<?php
require_once("vendor/autoload.php");
require_once("URLParser.php");
require_once("addMOProperty.php");
use Everyman\Neo4j\Cypher\Query;

//Only search coauthor information from pubmed and IEEE datasource
class CoauthorNetworkCrawler{
	
	private $pubmed_search_url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&RetMax=20";
	private $pubmed_fetch_url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&retmode=xml&id=";
	private $ieee_url = "http://ieeexplore.ieee.org/gateway/ipsSearch.jsp";
	private $firstName;
	private $lastName;
	private $affiliation;
	
	public function __construct($name, $affiliation){
		$parser = new HumanNameParser_Parser($name);
		$this->firstName = $parser->getFirst();
		$this->lastName = $parser->getLast();
		$this->affiliation = $affiliation;
	}
	

	public function crawlIEEE(){
		
		$url = $this->ieee_url . "?au=" . urlencode($this->firstName." ".$this->lastName);
		$IEEE_parser = new URLParser($url);
		$IEEE_item_array = $IEEE_parser -> XMLToArray();
		
		$retVal = array();
		if(array_key_exists('document', $IEEE_item_array) && !empty($IEEE_item_array['document'])){
			
			$IEEE_item_array['document'] = isset($IEEE_item_array['document'][0]) ? $IEEE_item_array['document'] : array($IEEE_item_array['document']);
			foreach($IEEE_item_array['document'] as $ind => $attr_array){

				$title = $attr_array['title'];
				$pdf_url = $attr_array['pdf'];
				//$affiliations = (isset($attr_array['affiliations'])) ? $attr_array['affiliations'] : "";

				$authorListString = $attr_array['authors'];

				$authorListArray = explode("; ", $authorListString);
				$peopleList = array();
				foreach($authorListArray as $au){
					$parser = new HumanNameParser_Parser($au);
					$peopleList[] = array("firstName" => $parser->getFirst(), "lastName" => $parser->getLast(), "affiliation" => "");
				}

				$retVal[] = array("title" => $title, "url" => $pdf_url, "people" => $peopleList);
			}
		}

		return $retVal;

	}
	
	public function crawlPubmed(){
		$search_url = $this->pubmed_search_url . "&term=".urlencode($this->firstName." ".$this->lastName."[Author]"." AND ".$this->affiliation."[Affiliation]");
		$search_url_parser = new URLParser($search_url);
		$pubmedSearch_item_array = $search_url_parser->XMLToArray();

		$returnVal = array();
		if(array_key_exists('Count', $pubmedSearch_item_array) && $pubmedSearch_item_array['Count'] > 0){
			
			if($pubmedSearch_item_array['Count'] == 1 || $pubmedSearch_item_array['RetMax'] == 1){
				$pubmedSearch_item_array['IdList']['Id'] = array($pubmedSearch_item_array['IdList']['Id']);
			}

			$pid_url = "";
			foreach($pubmedSearch_item_array['IdList']['Id'] as $ind => $pubid){
				$pid_url .= $pubid . ",";
			}
			$pid_url = rtrim($pid_url,",");
			$fetch_url = $this->pubmed_fetch_url . $pid_url;
			$fetch_url_parser = new URLParser($fetch_url);
			$pubmedFetchResultArray = $fetch_url_parser -> XMLToArray();

			if(!array_key_exists('PubmedArticle', $pubmedFetchResultArray)){
				trigger_error("PubmedArticle not set in pubmedFetchResultArray", E_USER_ERROR);
				return null;
			}

			$pubmedFetchResultArray['PubmedArticle'] = isset($pubmedFetchResultArray['PubmedArticle'][0]) 
											? $pubmedFetchResultArray['PubmedArticle'] 
											: array($pubmedFetchResultArray['PubmedArticle']);
											
			foreach ($pubmedFetchResultArray['PubmedArticle'] as $record){
			
				if(!array_key_exists('MedlineCitation', $record)){
					trigger_error("MedlineCitation not set in one pubmed record", E_USER_ERROR);
					continue;
				}
				
				$coauthorList = array();
				$title = $record['MedlineCitation']['Article']['ArticleTitle'];
				$url = 'http://www.ncbi.nlm.nih.gov/pubmed/'.$record['MedlineCitation']['PMID'];
				if(array_key_exists('AuthorList', $record['MedlineCitation']['Article']) 
					&& array_key_exists('Author', $record['MedlineCitation']['Article']['AuthorList'])
					&& !empty($record['MedlineCitation']['Article']['AuthorList'])){
						
						$authorList = $record['MedlineCitation']['Article']['AuthorList']['Author'];
				}
				else{
					trigger_error("authorlist/author not set or author is empty", E_USER_ERROR);
					continue;
				}
				
				if(!isset($authorList[0])){
					continue;
				}
				
				if(count($authorList) > 20){
					continue;
				}
										
				foreach($authorList as $a){
					
					//converting pubmed name to neo4j compatible format
					$parser = new HumanNameParser_Parser($a['LastName']. ", ". $a['ForeName']);

					$first = $parser->getFirst();
					$last = $parser->getLast();

					if(array_key_exists('AffiliationInfo', $a)){
						if(array_key_exists('Affiliation', $a['AffiliationInfo'])){
							$affi = $a['AffiliationInfo']['Affiliation'];
						}

						else if(array_key_exists(0, $a['AffiliationInfo']) && array_key_exists('Affiliation', $a['AffiliationInfo'][0])){
							$affi = $a['AffiliationInfo'][0]['Affiliation'];
						}
					}
					else{
						$affi = "";
					}

					$coauthorList[] = array("firstName" => $first, "lastName" => $last, "affiliation" => $affi);

				}
				
				$returnVal[] = array("title"=>$title, "url" => $url, "people"=>$coauthorList);
			}
		}
		return $returnVal;
		
	}
	
	public function insertDB($host, $port, $username, $auth, $custom_data = null){
		require('vendor/autoload.php');
		$client = new Everyman\Neo4j\Client($host, $port);
		$client->getTransport()
				->setAuth($username, $auth);
				
		if(is_null($custom_data)){
			$values = $this->crawlPubmed();
		}
		else{
			$values = $custom_data;
		}
		//$values = array_merge($this->crawlPubmed(), $this->crawlIEEE());

		//print_r($values);
		//return;

		if(!is_array($values) || !isset($values[0])){
			print_r($values);
			die("something is wrong with the values");
		}
		
		$transaction = $client->beginTransaction();
		$neo4jQueryArray = array();
		//$queryForCentralPerson = "merge (u:Person {name: \"".$this->lastName.", ".$this->firstName."\"}) set u.affiliation = \"".$this->affiliation."\"";
		//$query = new Query($client, $queryForCentralPerson);
		//$result = $transaction->addStatements($query);
		
		foreach($values as $one){
			//print_r($one) has the format:
			//[17] => Array
			//(
			//	[title] => RNA-protein distance patterns in ribosomes reveal the mechanism of translational attenuation.
			//  [url] => http://www.ncbi.nlm.nih.gov/pubmed/25326828
            //  [people] => Array
			//  (
			//	  [0] => Array
			//	  (
			//		[firstName] => DongMei
			//      [lastName] => Yu
			//      [affiliation] => Department of Biological Engineering, University of Missouri, Columbia, MO, 65211, USA.
			//    )

            //    [1] => Array
			//    (
			//	     [firstName] => Chao
            //       [lastName] => Zhang
            //       [affiliation] =>
            //    )
			//  )
			//)

			(array_key_exists('title', $one) && !empty($one['title'])) ? $articleTitle = $one['title'] : die("why the title has not been set?");
			if(!array_key_exists('url', $one)){
				$articleURL = "";
			}
			else{
				$articleURL = $one['url'];
			}
			(array_key_exists('people', $one) && is_array($one['people']) && isset($one['people'][0])) ? $articleAuthorList = $one['people'] : die("People list might be wrong");

			$queryToCreatePublication = "merge (paper:Publication {title: \"". addslashes($articleTitle)."\"}) set paper.url = \"".$articleURL."\"";
			$neo4jQueryArray[] = new Query($client, $queryToCreatePublication);
			//$result = $transaction->addStatements($query);
			
			//$queryToCreateWroteRelation = "match (u:Person {name: \"". $this->lastName.", ".$this->firstName."\"}), (p:Publication {title: \"".addslashes($theTitle)."\"}) create unique (u)-[r:Wrote]->(p)";
			//echo $queryToCreateWroteRelation."\n";
			//$query = new Query($client, $queryToCreateWroteRelation);
			//$result = $transaction->addStatements($query);
			
			foreach($articleAuthorList as $p){
				if(!is_array($p) || !array_key_exists('firstName', $p) || !array_key_exists('lastName', $p) || !array_key_exists('affiliation', $p)){
					die("Ariticle Author List format Error");
				}

				if(empty($p['firstName']) || empty($p['lastName'])){
					continue;
				}

				if(empty($p['affiliation'])){
					$queryToCreatePerson = "merge (author:Person {name: \"".$p['lastName'].", ".$p['firstName']."\"})";
				}
				else{
					$queryToCreatePerson = "merge (author:Person {name: \"".$p['lastName'].", ".$p['firstName']."\"}) set author.affiliation = \"".addslashes($p['affiliation'])."\"";
				}

				$neo4jQueryArray[] = new Query($client, $queryToCreatePerson);
				
				$queryToCreateWroteRelation = "match (u:Person {name: \"". $p['lastName'].", ".$p['firstName']."\"}), (p:Publication {title: \"".addslashes($articleTitle)."\"}) create unique (u)-[r:Wrote]->(p)";
				$neo4jQueryArray[] = new Query($client, $queryToCreateWroteRelation);
				
				$queryToAdjustNumOfPublication = "match (u:Person {name: \"". $p['lastName'].", ".$p['firstName'] ."\"}) - [:Wrote] - (p) with u, count(distinct p) as num set u.publicationNum = num";
				$neo4jQueryArray[] = new Query($client, $queryToAdjustNumOfPublication);

				//addMOProperty($p['lastName'].", ".$p['firstName'], $client);
				//should be after transition->commit()


			}
			
			for($i=0; $i<sizeof($articleAuthorList); $i++){
				for($j=$i+1; $j<sizeof($articleAuthorList); $j++){
					$queryToCreateCoauthoredRelation = "match (p:Publication), (u1:Person {name: \"".$articleAuthorList[$i]['lastName'].", ".$articleAuthorList[$i]['firstName']."\"}), (u2:Person {name: \"".$articleAuthorList[$j]['lastName'].", ".$articleAuthorList[$j]['firstName']."\"})
														where (u1)-[:Wrote]->(p) AND (u2)-[:Wrote]->(p) with count(p) as n
														match (u1:Person {name: \"".$articleAuthorList[$i]['lastName'].", ".$articleAuthorList[$i]['firstName']."\"}), (u2:Person {name: \"".$articleAuthorList[$j]['lastName'].", ".$articleAuthorList[$j]['firstName']."\"})
														create unique (u1)-[r:COAUTHORED]-(u2) set r.numOfPapers = n";
					//echo $queryToCreateCoauthoredRelation."\n";
					$neo4jQueryArray[] = new Query($client, $queryToCreateCoauthoredRelation);
					//$result = $query->getResultSet();
					//$result = $transaction->addStatements($query);
				}
			}
		}
		$transaction -> addStatements($neo4jQueryArray, true);

		foreach($values as $one){
			(array_key_exists('people', $one) && is_array($one['people']) && isset($one['people'][0])) ? $articleAuthorList = $one['people'] : die("People list might be wrong");
			foreach($articleAuthorList as $p) {
				if(!is_array($p) || !array_key_exists('firstName', $p) || !array_key_exists('lastName', $p) || !array_key_exists('affiliation', $p)){
					die("Ariticle Author List format Error");
				}

				if(empty($p['firstName']) || empty($p['lastName'])){
					continue;
				}

				addMOProperty($p['lastName'].", ".$p['firstName'], $client);
			}
		}

	}
}

?>
