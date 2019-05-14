<?php
try{
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=byddps;","byddps","Byddps123");
    $pdo->query("SET NAMES utf8");
}catch(PDOException $e){
    die("数据库连接失败".$e->getMessage());
}
?>