<?php
include_once "pubmedHandler.php";
include_once "IEEEHandler.php";
include_once "EventsHandler.php";
include_once "NewsHandler.php";
include_once "SphinxConfig.php";
include_once "contact_search.php";


//if(isset($_POST['submit_search']) && !empty($_POST['datasources'])){
function search($qry, $ds, $num_of_records=10){
	
	//$qry = $_POST['queryString'];
	//$ds = $_POST['datasources'];
	
	$nf = new Sphinx();
	$sphinxPort = $nf->getPort();
	
	$nf->initiateFiles();

	foreach($ds as $datasource){
		switch($datasource){
			case "mospace":
				$nf->putMOSpace();
				break;
			case "ieee":
				$nf->putIEEE();
				break;
			case "pubmed":
				$nf->putPubmed();
				break;
			case "googlescholar":
				$nf->putGoogleScholar();
				break;
			case "news":
				$nf->putNews();
				break;
			case "events":
				$nf->putEvents();
				break;
			default:
				break;
		}
	}

	$nf->putIndexer();
	$nf->putSearchd();
	
	$nf->startSearchd();
	
	$post_result_data = array();
	foreach($ds as $datasource){
		switch($datasource){
			case "mospace":
				$post_result_data = array_merge($post_result_data, array("mospace" => MOSpaceHandler($qry, $sphinxPort)));
				break;
			case "pubmed":
				$post_result_data = array_merge($post_result_data, array("pubmed" => pubmedHandler($qry, $sphinxPort, $num_of_records)));
				break;
			case "ieee":
				$post_result_data = array_merge($post_result_data, array("ieee" => IEEEHandler($qry, $sphinxPort, $num_of_records)));
				break;
			case "events":
				$post_result_data = array_merge($post_result_data, array("events" => EventsHandler($qry, $sphinxPort)));
				break;
			case "news":
				$post_result_data = array_merge($post_result_data, array("news" => NewsHandler($qry, $sphinxPort)));
				break;
			default:
				break;
		}
	}

	$post_result_data = array_merge($post_result_data, array("contacts" => ContactHandler($qry, $sphinxPort)));
	//echo "<pre>";
	//print_r(json_encode($post_result_data));
	//echo "</pre>";
	$nf->stopSearchd();
	$nf->deleteSphinxFiles();
	$nf->releasePort();
	unset($nf);
	
	return $post_result_data;
}
