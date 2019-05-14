<?php
include_once "../common/checkback.php";
$table = explode('/', $_SERVER['PHP_SELF'])[1];
$sql = "select * from config where TableName='{$table}'  and UserTypeId='{$_SESSION['backuser']['UserTypeId']}'";
$stmt = $pdo->query($sql);
if ($stmt->rowCount() > 0) {
    $cf = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $cf = [];
}
//var_dump($cf);
$sign=array();
foreach ($cf as $c){
    $sign[$c['ColumnName']]=$c['Visible'];
}
var_dump($sign);
