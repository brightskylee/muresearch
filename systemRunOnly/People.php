<?php

include_once "databaseConfig.php";

class Person{
	
	protected $lastName = null;
	protected $firstName = null;
	protected $department = null;
	protected $title = null;
	//protected $imageURL = null;
	protected $phone = null;
	protected $email = null;
	
	
	public function setLastName($lastName){
		$this->lastName = $lastName;
	}
	
	public function setFirstName($firstName){
		$this->firstName = $firstName;
	}
	
	public function setTitle($title){
		$this->title = $title;
	}
	
	public function setDepartment($department){
		$this->department = $department;
	}
	
	//public function setImageURL($imageURL){
	//	$path_parts = pathinfo($imageURL);
	//	if(in_array($path_parts['extension'], array("png","jpg", "gif", "bmp"))){
	//		$this->imageURL = $imageURL;
	//	}
        //else{
	//		exit("ERROR: imageURL must be of a image type");
	//	}		
	//}
	
	public function setPhone($phoneNumber){
		$this->phone = $phoneNumber;
	}
	
	public function setEmail($email){
		$this->email = $email;
	}
	
	public function getLastName(){
		return $this->lastName;
	}
	
	public function getFirstName(){
		return $this->firstName;
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function getDepartment(){
		return $this->department;
	}
	
	public function getPhone(){
		return $this->phone;
	}
	
	public function getEmail(){
		return $this->email;
	}
	
	public function insertToDatabase(){
		$table = "MOSpacePeople";
		$database = new mysqli(HOSTNAME, USERNAME, PASSWD, DATABASE);
		if($database->connect_errno){
			exit("Databse connection ERROR(".$database->errno."): ".$database->error);
		}
		
		$qry = "INSERT IGNORE INTO ".$table. " (LastName, FirstName, Department, Title, Phone, Email) VALUES(?, ?, ?, ?, ?, ?)";
		
		if($stmt = $database->prepare($qry)){
			$stmt->bind_param("ssssss", $this->lastName, $this->firstName, $this->department, $this->title, $this->phone, $this->email);
			
			if(!$stmt->execute()){
				exit($stmt->error);
			}
			if($database->affected_rows == 1){
				echo $this->lastName.", ".$this->firstName."; ";
			}
			
			$stmt->close();
		}
		
		$database->close();
	}

}
