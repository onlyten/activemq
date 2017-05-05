<?php
$conn = mysql_connect('localhost', 'root', '');
mysql_select_db('test', $conn);
mysql_query("SET NAMES UTF8");

ignore_user_abort();//关闭浏览器仍然执行
set_time_limit(0);//让程序一直执行下去
$interval=5;//每隔一定时间运行
do{
    $sql = "INSERT INTO a (student_id) VALUES (23)";
    mysql_query($sql);
    sleep($interval);//等待时间，进行下一次操作。
}while(0);




mysql_query("BEGIN"); //或者mysql_query("START TRANSACTION");
$sql = "";
$sql2 = "";//故意写错
$res = mysql_query($sql);
$res1 = mysql_query($sql2); 
if($res && $res1){
	mysql_query("COMMIT");
	echo '提交成功';
}else{
	mysql_query("ROLLBACK");
	echo '数据回滚';
}
mysql_query("END"); 


?>