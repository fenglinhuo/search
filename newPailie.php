<?php

date_default_timezone_set("Asia/Hong_Kong");
$money=array(1,5,10,25,50);

function countsMoney(){

}

function notInclude($i){

}

function paile($n,$i=0){
    global $money;
    if($n<$money[$i]){
        return 0;
    }else if($n==$money[$i]){
        return 1;
    }else if($n==0){
        return 1;
    }
    else if($i>=4){
        return 0;
    }else {
     return paile($n,$i+1)+paile($n-$money[$i],$i);
    }
}

//echo paile(100);

$stramp = strtotime("");
$stramp2 = strtotime("now");
//echo $stramp."\n";
//echo $stramp2."\n";
//echo ceil(($stramp2-$stramp)/(3600*24)/365)."\n";

//echo date('Y-m-d H:i:s',$stramp);

$username="\u5929\u5929\u4ed6\u4eba";
var_dump(json_decode($username,true));
$username="天天他人";
echo json_encode($username);




?>