<?php

include_once "databaseConfig.php";

$mysqli = new mysqli(HOSTNAME, USERNAME, PASSWD, DATABASE);
if($mysqli->connect_errno){
    exit("error:". $mysqli->connect_error);
}
//echo json_encode($_POST);

$theid=$_POST['user_id'];
if (isset($_POST['is_title'])) {
	$new_title=$_POST['title'];
	$query="UPDATE registeredUser SET title='$new_title' WHERE id=$theid";
	mysqli_query($mysqli, $query);
	echo json_encode($_POST,true);
}
else if(isset($_POST['is_phone'])) {
	$new_phone=$_POST['phone'];
	$query="UPDATE registeredUser SET phone='$new_phone' WHERE id=$theid";
	mysqli_query($mysqli, $query);

	echo json_encode($_POST,true);
}
else if(isset($_POST['is_email'])) {
	$new_email=$_POST['email'];
	$query="UPDATE registeredUser SET email='$new_email' WHERE id=$theid";
	mysqli_query($mysqli, $query);

	echo json_encode($_POST,true);
}
else if(isset($_POST['is_department'])) {
	$new_department=$_POST['department'];
	$query="UPDATE registeredUser SET department='$new_department' WHERE id=$theid";
	mysqli_query($mysqli, $query);

	echo json_encode($_POST,true);
}
else if(isset($_POST['is_overview'])) {
	$new_description=$_POST['description'];
	$query="UPDATE registeredUser SET description='$new_description' WHERE id=$theid";
	mysqli_query($mysqli, $query);

	echo json_encode($_POST,true);
}
else if(isset($_POST['is_photo_url'])) {
	$new_url=$_POST['photo_url'];
	$query="UPDATE registeredUser SET photoImageURL='$new_url' WHERE id=$theid";
	mysqli_query($mysqli, $query);

	echo json_encode($_POST,true);
}
?>
