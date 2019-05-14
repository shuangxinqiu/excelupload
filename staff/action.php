<?php
include_once "../common/checkback.php";
$table = explode('/', $_SERVER['PHP_SELF'])[1];
switch($_REQUEST['action']) {
    case "one"://获取单条数据操作
        $where="where 1=1";
        foreach ($_GET as $key => $value)
        {
            if($key!='action')
            $where.=" and $key='{$value}'";
        }
        $sql = "select * from {$table} {$where}";
        $stmt = $pdo->query($sql);
        if($stmt->rowCount() > 0){
            $x = $stmt->fetch(PDO::FETCH_ASSOC);
            exit(json_encode($x));
        }else{
            exit(0);
        }
    break;
    /*获取列表*/
    case "list":
        $limit="";
        $orderby="";
        $sort="";
        $where="where 1=1";
        foreach ($_GET as $key => $value)
        {
            if($key!='limit'&&$key!='orderby'&&$key!='sort'&&$key!='action')
            $where.=" and $key='{$value}'";
        }
        $sql = "select * from {$table} {$where}";
        $stmt = $pdo->query($sql);
        if($stmt->rowCount() > 0){
            $x = $stmt->fetch(PDO::FETCH_ASSOC);
            exit(json_encode($x));
        }else{
            exit(0);
        }
        if(isset($_GET['limit'])&&$_GET['limit']!=""){
            $limit=$_GET['limit'];
        }
        if(isset($_GET['orderby'])&&$_GET['orderby']!=""){
            $orderby="order by ".$_GET['orderby'];
            if(isset($_GET['sort'])&&$_GET['sort']!=""){
                $sort=$_GET['sort'];
            }
        }
        $sql = "select * from {$table} {$where} {$orderby} {$sort} {$limit}";
        $stmt = $pdo->query($sql);
        if($stmt->rowCount() > 0){
            $x = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($x);
            //echo print_r($x,false);
        }else{
            echo "[]";
        }
    break;
    /*表单post添加*/
    case "add":
        $keys="";
        $values="";
        foreach ($_POST as $key => $value)
        {
            $keys.=$key.",";
            $values.="'{$value}',";
        }
        $keys = substr($keys,0,strlen($keys)-1);
        $values = substr($values,0,strlen($values)-1);
        $sql = "insert into {$table}({$keys}) values({$values})";
        $stmt = $pdo->exec($sql);
        echo"<script>history.go(-2);</script>";
        break;

    case "delete"://删除操作
        $where="where";
        foreach ($_POST as $key => $value)
        {
            $where.=" $key='{$value}' and";
        }
        $where = substr($where,0,strlen($where)-4);
        $sql = "delete from {$table} {$where}";
        $stmt=$pdo->exec($sql);
        echo"<script>history.go(-2);</script>";
    break;

    case "deletes"://删除操作
        $where="where";
        foreach ($_POST as $key => $value)
        {
			$value = trim($value,"[]");
            $where.=" $key in ({$value}) and";
        }
        $where = substr($where,0,strlen($where)-4);
        $sql = "delete from {$table} {$where}";
        echo $sql;
        $stmt=$pdo->exec($sql);
        echo"<script>history.go(-2);</script>";
    break;

    case "edit"://更新操作,get作为条件，post作为set
        $where="";
        $set="";
        foreach ($_POST as $key => $value)
        {
            if(!isset($_GET[$key]))
            $set.=" $key='{$value}',";
        }
        foreach ($_GET as $key => $value)
        {
            if($key!='action')
            $where.=" $key='{$value}' and";
        }
        $set = substr($set,0,strlen($set)-1);
        $where = substr($where,0,strlen($where)-4);
        $sql = "update {$table} set {$set} where {$where}";
        $stmt=$pdo->exec($sql);
        echo"<script>history.go(-2);</script>";
    break;



}
?>