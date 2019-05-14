<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 15:35
 */
include_once "../common/checkback.php";

//根据路径获取表名
$table = explode('/', $_SERVER['PHP_SELF'])[1];

$where = "where 1=1 ";

//获取表字段名
$sql = "select COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}'";
$stmt = $pdo->query($sql);
$COLUMN_NAME = $stmt->fetchAll(PDO::FETCH_COLUMN);

//获取表字段注释
$sql = "select COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}'";
$stmt = $pdo->query($sql);
$COLUMN_COMMENT = $stmt->fetchAll(PDO::FETCH_COLUMN);
//根据字段名拼接where
foreach ($COLUMN_NAME as $cn) {
	if ($_REQUEST[$cn]) {
		$where .= "and {$cn} like '%{$_REQUEST[$cn]}%'";
	}
}

//获取列表
$sql = "select * from {$table} {$where}";
$stmt = $pdo->query($sql);
if ($stmt->rowCount() > 0) {
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
//	array_unshift($list,$COLUMN_COMMENT );
} else {
	$list = [];
}
//echo "<pre>";
//var_dump($list);
//echo "</pre>";
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



    /**
	 * exportExcel($data,$title,$filename);
	 * 导出数据为excel表格
	 *@param $data  一个二维数组,结构如同从数据库查出来的数组
	 *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
	 *@param $filename 下载的文件名
	 *@examlpe
	exportExcel($arr,array('id','账户','密码','昵称'),'文件名!');
	 */
    function exportExcel($data=array(),$title=array(),$comment=array(),$filename='report',$sign=array())
	{
		header("Content-type:application/octet-stream");
		header("Accept-Ranges:bytes");
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=".$filename.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		//导出xls开始
		if (!empty($title))
		{
		    $index=0;
		    $newtitle=array();
            $newcomment=array();
			foreach ($title as $k => $v)
			{
			    if($sign[$v]==1){
                    array_push($newtitle,iconv("UTF-8", "GBK",$v));
                    array_push($newcomment,iconv("UTF-8", "GBK",$comment[$index]));
                }
                $index++;
			}
			$title= implode("\t", $newtitle);
			echo "$title\n";
            $comment= implode("\t", $newcomment);
            echo "$comment\n";
		}

		if (!empty($data))
		{
			foreach($data as $key=>$val)
			{
			    $newdata=array();
				foreach ($val as $ck => $cv)
				{
                    if($sign[$ck]==1) {
                        array_push($newdata, iconv("UTF-8", "GBK", $cv));
                    }
				}
                $newdata=implode("\t", $newdata);
                echo "$newdata\n";

            }
        }
	}

//$total = array();
//$fp = fopen('card.txt', 'r');
//$i = 0;
//while ($v = fgets($fp, 100))
//{
//	$arr = explode(",", trim($v));
//	$total[$i]["cid"]           = "=\"".$arr[0]."\"";
//	$total[$i]["code"]          = "=\"".$arr[1]."\"";
//	$total[$i]["ctime"]         = '2014-09-10 14:06:29';
//	$total[$i]["expireday"]     = '2015-12-31 23:59:59';
//	$i++;
//}
exportExcel($list, $COLUMN_NAME,$COLUMN_COMMENT, $table.'Data',$sign);
//exit;
