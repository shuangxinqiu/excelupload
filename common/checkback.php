<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE');
header('Content-type:text/html;charset=utf-8');

include_once "dbconn.php";
//include_once "../wechat/auth.php";
session_start();

//ini_set('session.gc_maxlifetime', 36000); //设置时间
if(!strpos($_SERVER['PHP_SELF'],"index.php")&&!strpos($_SERVER['PHP_SELF'],"action.php")){
//    echo $_SERVER['PHP_SELF'];
    $_SESSION['ref']=null;
    $_SESSION['refback']=$url= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
}
if(!isset($_SESSION['backuser'])||$_SESSION['backuser']==""||$_SESSION['backuser']==null){
    header("location:/login.php");
}else{
    $BackUserId=$_SESSION['backuser']['id'];
}


