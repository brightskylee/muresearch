<?php
//include_once "research_query.php";
include_once "FixCertificate.php";
include_once "databaseConfig.php";
require_once("vendor/autoload.php");

function searchFromPeopleFinder($nameString, $mysqli_object){
	
	//global $context;
	
	//$returnVal = [];
	//$baseURL = "https://webservices.doit.missouri.edu";
	//if(empty($people_list)){
		//return $returnVal;
	//}
	
	$qry = "SELECT LastName, FirstName, Department, Title, Email from MOSpacePeople where LastName =  ?  AND FirstName =  ? ";
	

	$parser = new HumanNameParser_Parser($nameString);

	if(!($stmt = $mysqli_object->prepare($qry))){
		exit("statement prepared failed: (". $mysqli_object->errno.") ". $mysqli_object->error);
	}

	$bind_param_last_name = $parser->getLast();
	$bind_param_first_name = $parser->getFirst();

	if(!$stmt->bind_param("ss", $bind_param_last_name, $bind_param_first_name)){
		exit("bind parameter failed: (". $stmt->errno.")".$stmt->error);
	}

	if(!$stmt->execute()){
		exit("Execute failed: (". $stmt->errno. ")". $stmt->error);
	}

	$stmt->store_result();
	//echo $stmt->num_rows."\n";
	if( $stmt->num_rows == 0){
		return false;
	}

	if($stmt->num_rows > 0){
		$stmt->bind_result($last, $first, $dep, $title, $email);
		$stmt->fetch();
		return array("LastName" => $last, "FirstName" => $first, "Department" => $dep, "Title" => $title, "Email" => $email);
	}
}

function MOSpaceHandler($qry, $port){
	
	$table_name = 'mospace';
	$host = '127.0.0.1';
	
	$sphinx_conn = new mysqli($host, '', '','', $port);
	if($sphinx_conn->connect_errno){
		printf("Connect failed: %s\n", $sphinx_conn->connect_error);
		exit();
	}

	$qry = $sphinx_conn->real_escape_string($qry);
	$rankerOption = 'matchany';
	$query = "SELECT  title, creator, description, identifier from ".$table_name." where match('" . $qry . "') option ranker=".$rankerOption;
	
	if(!($result = $sphinx_conn->query($query))){
		printf("Error: %s\n", $sphinx_conn->error);
		exit();
	}
	
	$MOSpaceResult = array();
	while($r = $result->fetch_assoc()){
		
		$match = array();
		preg_match('/http:\/\/hdl.handle.net\/[0-9]+\/[0-9]+/', $r['identifier'], $match);
		$link = (isset($match[0])) ? $match[0] : "";
		$MOSpaceResult[] = array('title' => $r['title'], 'authors' => $r['creator'], 'abstract' => $r['description'], 'url' => $link);
	}
	
	return $MOSpaceResult;
}

function ContactHandler($qry, $port){


	$mysql_conn = new mysqli(HOSTNAME, USERNAME, PASSWD, DATABASE);
	if($mysql_conn->connect_errno){
		printf("Connect failed: %s\n", $mysql_conn->connect_error);
		exit();
	}
	
	$table_name = 'mospace';
	$host = '127.0.0.1';
	
	$sphinx_conn = new mysqli($host,'','','',$port);
	if($sphinx_conn->connect_errno){
		printf("Connect failed: %s\n", $sphinx_conn->connect_error);
		exit();
	}

	$qry = $sphinx_conn->real_escape_string($qry);
	$rankerOption = 'matchany';
	$query = "SELECT creator from ".$table_name." where match('" . $qry . "') option ranker=".$rankerOption;
		
	//$mysql_qry = "SELECT title, creator, description FROM MOSpaceAll where match(title, description) against('".$qry."')";
	//$mysql_qry = "SELECT title, creator, description FROM MOSpaceAll where title like '%".$qry."%' or description like '%".$qry."%'";
	if(!($result = $sphinx_conn->query($query))){
		printf("Error: %s\n", $sphinx_conn->error);
		exit();
	}
	
	
		//$counter = 0;
    	$peopleResult = array();
		while(($r = $result->fetch_assoc()) /*&& $counter < 5*/){
//			print_r($r);
			if(array_key_exists('creator', $r) && !empty($r['creator'])){
				$people_list = explode(";", $r['creator']);
				foreach($people_list as $p){
					//echo $p."<br>";
					$p = implode(",", array_slice(explode(",", $p), 0, 2));
					if($tmp = searchFromPeopleFinder($p, $mysql_conn)){
						if(!in_array($tmp, $peopleResult)){
							$peopleResult[] = $tmp;
		//					$counter++;
						}
					}
				}
			}
		
		}
	$result->free();
	return $peopleResult;

}
	//if(isset($_REQUEST['key']) && !empty($_REQUEST['key'])){
		
		//$search = new ResearchQuery;
		//MOSpaceHandler($_REQUEST['key']);
	//}
//print_r(ContactHandler("protein structure prediction", 9306));

?>
