<?php

function sphinxDB_connect($sphinxPort){

    $mysqli = new mysqli("127.0.0.1", "", "", "", $sphinxPort);
    if($mysqli->connect_error){
        die("Connect error (".$mysqli->connect_errno .")");
    }

    return $mysqli;
}