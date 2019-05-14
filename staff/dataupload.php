<?php
require_once '../PHPExcel/IOFactory.php';
include_once "../common/checkback.php";

$table = explode('/', $_SERVER['PHP_SELF'])[1];

//获取表字段名
$sql = "select COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}'";
$stmt = $pdo->query($sql);
$COLUMN_NAME = $stmt->fetchAll(PDO::FETCH_COLUMN);

$keys="";



//1.接收提交文件的用户
//我们这里需要使用到 $_FILES
/*echo "<pre>";
print_r($_FILES);
echo "</pre>";*/

//其实我们在上传文件时，点击上传后，数据由http协议先发送到apache服务器那边，这里apache服务器已经将上传的文件存放到了服务器下的C:\windows\Temp目录下了。这时我们只需转存到我们需要存放的目录即可。

//php中自身对上传的文件大小存在限制默认为2M

//获取文件的大小
$file_size=$_FILES['excel']['size'];
if($file_size>10*1024*1024) {
	echo "文件过大，不能上传大于4M的文件";
	exit();
}

$file_type=$_FILES['excel']['type'];
//echo $file_type;
//if($file_type!="image/jpeg" && $file_type!='image/pjpeg') {
//    echo "文件类型只能为jpg格式";
//    exit();
//}


//判断是否上传成功（是否使用post方式上传）
if(is_uploaded_file($_FILES['excel']['tmp_name'])) {
	//把文件转存到你希望的目录（不要使用copy函数）
	$uploaded_file=$_FILES['excel']['tmp_name'];

	//我们给每个用户动态的创建一个文件夹
//	$user_path_out=$_SERVER['DOCUMENT_ROOT']."/excel/up";
	$user_path_out="./excelfiles";

	//判断该用户文件夹 是否已经有这个文件夹
	if(!file_exists($user_path_out)) {
		mkdir($user_path_out);
	}
	$year = date('Y');
	$month= date('m');
	$day= date('d');
	$str = $year.$month.$day;
	//$move_to_file=$user_path."/".$_FILES['imagelistToUpload']['name'];
	$file_true_name=$_FILES['excel']['name'];
	$urlt = $str.rand(1,1000).substr($file_true_name,strrpos($file_true_name,"."));
	$move_to_file=$user_path_out."/".$urlt;
//	$move_to_file_out=$user_path_out."/".$urlt;

	//echo "$uploaded_file   $move_to_file";
	if(move_uploaded_file($uploaded_file,iconv("utf-8","gb2312",$move_to_file))) {
		$fileName = $move_to_file;

		if (!file_exists($fileName)) {
			return "文件不存在！";
		}else{
			// 载入当前文件
			$phpExcel = PHPExcel_IOFactory::load($fileName);
			// 设置为默认表
			$phpExcel->setActiveSheetIndex(0);
			// 获取表格数量
			$sheetCount = $phpExcel->getSheetCount();
			// 获取行数
			$row = $phpExcel->getActiveSheet()->getHighestRow();
			// 获取列数
			$column = $phpExcel->getActiveSheet()->getHighestColumn();
			echo "表格数目为：$sheetCount" . "表格的行数：$row" . "列数：$column";

			$data = [];
			$flag = 1;
			$num = 2;
			//获取第一行作为keys 第一行为字段名

			for ($c = 'A'; $c <= $column; $c++) {
				$keys .= "{$phpExcel->getActiveSheet()->getCell($c . 1)->getValue()},";
			}
			$keys = substr($keys,0,strlen($keys)-1);
//			exit($keys);
			// 行数循环 跳过第2行 第2行是注释
			for ($i = 3,$index=0; $i <= $row; $i++,$index++) {
				// 列数循环
				for ($c = 'A'; $c <= $column; $c++) {
					$data[$index] .= "'{$phpExcel->getActiveSheet()->getCell($c . $i)->getValue()}',";
				}
				$data[$index] = substr($data[$index],0,strlen($data[$index])-1);
			}
//			echo "<pre>";
//			print_r($data);
			foreach ($data as $d){
				$sql = "insert into {$table} ({$keys}) values ({$d})";
				echo $sql.'<br>';
//exit();
				$stmt = $pdo->exec($sql);
				$num++;
				if ($stmt == 1) {
					echo "true<br>";
				} else {
					echo "false<br>";

					$flag = 0;
				}
			}
			echo $num;
			echo $row;
			if($num == $row){
				if($flag){
					echo "<script>alert('全部成功');window.location='list.php'</script>";
				}else{
					echo "<script>alert('部分成功，请检查excel文件,有部分数据重复');window.history.back();</script>";
				}
			}else{
				echo "<script>alert('发送故障');window.history.back();</script>";
			}

		}
	} else {
//		echo $move_to_file."上传失败";
		echo "<script>alert('失败');window.history.back();</script>";
	}
} else {
//	echo "上传失败";
	echo "<script>alert('失败');window.history.back();</script>";
}

