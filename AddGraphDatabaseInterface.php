<?php

require_once "vendor/autoload.php";
require_once "CoauthorNetworkCrawler.php";

$host = "localhost";
$port = 7474;
$username = "neo4j";
$auth = "muresearch";

function checkDataFormat4thLevel($var){
    if(is_array($var)){


        $key = array_keys($var);
        $val = array_values($var);
        $count = count($var);

        if($count == 3){
            if($key[1] == "firstname" && is_string($val[1]) ){
                if($key[2] == "lastname" && is_string($val[2])){
                    if($key[3] == "affiliation" && is_string($val[3])){
                        return true;
                    }
                }
            }
        }

        return false;
    }
    else{
        return false;
    }
}

function checkDataFormat3rdLevel($var){
    if(is_array($var)){

        $key = array_keys($var);
        $val = array_values($var);
        $count = count($var);

        for($i = 0; $i< $count; $i++){
            if(!is_int($key[$i]) || !checkDataFormat4thLevel($val[$i])){
                return false;
            }
        }

        return true;
    }
    else{
        return false;
    }
}

function checkDataFormat2ndLevel($var){

    if(is_array($var)){


        $key = array_keys($var);
        $val = array_values($var);
        $count = count($var);

        if($count == 3){
            if($key[1] == "title" && is_string($val[1]) ){
                if($key[2] == "url" && is_string($val[2])){
                    if($key[3] == "people" && is_array($val[3]) && checkDataFormat3rdLevel($val[3])){
                        return true;
                    }
                }
            }
        }

        return false;
    }
    else{
        return false;
    }
}

function checkDataFormat1stLevel($var){
    if(is_array($var)){

        $key = array_keys($var);
        $val = array_values($var);
        $count = count($var);

        for($i = 0; $i< $count; $i++){
            if(!is_int($key[$i]) || !checkDataFormat2ndLevel($val)){
                return false;
            }
        }

        return true;
    }
    else{
        return false;
    }
}

if(isset($_POST['data'])) {

    $var = json_decode($_POST['data']) or die("posted data not in json format");


    //if (checkDataFormat1stLevel($var)) {
        $c = new CoauthorNetworkCrawler(null, null);
        $c->insertDB($host, $port, $username, $auth, $var);
    //}
    //else{
    //    print("something wrong in the format of the posted data");
    //}
}
else{
    print("This file should be posted");
}
