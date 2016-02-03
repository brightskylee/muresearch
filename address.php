<?php
    session_start(); 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Page</title>
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<?php
    
    $user_check=$_SESSION['login_user'];
    
    include 'db.php';
    $connection = mysql_connect($dbhost, $dbuser, $dbpass, $dbname);
    
  //  $newEmail = stripslashes($newEmail);
//    $newEmail = mysql_real_escape_string($newEmail);
        

    $db = mysql_select_db($dbname, $connection);

    $query = mysql_query("select username, address from newlogin where username='$user_check'", $connection);
    $array = mysql_fetch_assoc($query);
    $name = $array['username'];
    $address = $array['address'];
    $rows = mysql_num_rows($name);
        
   
        mysql_close($connection); 
?>

<body>
    
    <div id="profile">
        <b id="welcome">Welcome : <i><?php echo $name; ?></i></b>
        <b id="logout"><a href="logout.php">Log Out</a></b>
        </br>
    </br>
    <p><a href="profile.php">Return</a></p>
    <form action="successful.php" method="post">
    <div class="linkInfo">

        <label>Address:</label>
        <?php
            
         echo "<input id='newAddress' name='newAddress' placeholder='newAddress' type='text' class='form-control' value='$address'>";
        ?>
        <input name="addressDone" type="submit" value="Done" class="btn btn-danger">
    </div>
    </form>
    </div>

</body>
</html>