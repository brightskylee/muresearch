<?php

//This function will generalize $arr to 0 => $arr
//If $arr only represent one data record
function oneRecordArrayFormation($arr){
    if(isset($arr[0]))
        return $arr;
    else
        return array($arr);
}