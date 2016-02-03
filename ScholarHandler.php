<?php include_once "databaseConfig.php";


//include_once "databaseConfig.php";
function ScholarHandler($query_str){
//function scholarHandler($key){
//if(isset($_GET['key'])){

// $key = $_GET['key'];
$key = $query_str;

$scholarSearch_url_prefix = "http://muresearch.missouri.edu/scholar_result.php?key=";
$scholarSearch_json = file_get_contents($scholarSearch_url_prefix.urlencode($key));

//echo $scholarSearch_json;

//$json = '{"a":1,"b":2,"c":3,"d":4,"e":5}';
//$content = json_decode($scholarSearch_json,TRUE);

//var_dump(json_decode($scholarSearch_json,true));

//var_dump(json_decode($json));

$content = json_decode($scholarSearch_json,true);// get data into variable

/**** import data in to database ******/
//var_dump( $content);

$dbconn = mysqli_connect(HOSTNAME, USERNAME, PASSWD, DATABASE);
                if (mysqli_connect_errno()){
                        echo "Failed to connect to MySql:" . mysqli_connect_error();
                }
               //else{
			   //echo success login mysql;
			   //}
$queryInfo1="CREATE TABLE IF NOT EXISTS scholar(id int(10) NOT NULL, 
                                            title text,
		                                     url text,
		                                     year int,
		                                     num_citations int,
		                                     abstract text,
		                                     PRIMARY KEY(id))";
$queryInfo2="DROP TABLE IF EXISTS scholar;";									



$id=1;


$RU = mysqli_query($dbconn, $queryInfo2);
		 if(!$RU){
             echo 'delete failed';
			 
        }
		//echo "i am here";
		$create = mysqli_query($dbconn, $queryInfo1);
		if(!$create){
             echo 'create result failed';
        }

// foreach($content as $section=>$article){
	// echo "i am here";
		            // $num=0;		
		            // $tmp[0]="";		
	                // foreach($article as $key => $value){
					// $tmp[$num]=$value;
					// $num++;
					
			        // }
					//print_r($tmp);
		// $insert="INSERT INTO scholar(id, title,url,year,num_citations,abstract) 
		// VALUES($id, '$tmp[0]', '$tmp[1]', '$tmp[2]', '$tmp[3]', '$tmp[8]')";		
		// $test = mysqli_query($dbconn, $insert);
		 // if(!$test){
		  // echo 'insertion failed';
		 // }
		 // $id=$id+1;
		// }
		
$length = count($content);
for($i=0;$i < $length; $i++){
//	 echo  $i.' title is '.$content[$i]['title'].'<br />';
// echo $i.' url is '.$content[$i]['url'].'<br />';
// echo $i.' year is '.$content[$i]['year'].'<br />';
// echo $i.' citations is '.$content[$i]['citation'].'<br />';
// echo $i.' abstract is '.$content[$i]['abstract'].'<br />';
 
    $tmp[0] = $content[$i]['title'];
	$tmp[1] = $content[$i]['url'];
	$tmp[2] = $content[$i]['year'];
	$tmp[3] = $content[$i]['citation'];
	$tmp[4] = $content[$i]['abstract'];
 
 
	$insert="INSERT INTO scholar(id, title,url,year,num_citations,abstract) 
		VALUES($id, '$tmp[0]', '$tmp[1]', '$tmp[2]', '$tmp[3]', '$tmp[4]')";		
		$test = mysqli_query($dbconn, $insert);
		 if(!$test){
		  echo 'insertion failed';
		 }
		 $id=$id+1;





//print_r($content);

//}
}
//}
}

?>
