<?php
require_once("include/bittorrent.php");
dbconn();
function mysql_open()
{
$conn=mysql_connect($mysql_host ,$mysql_user ,$mysql_pass);
      mysql_query("set names UTF8"); //指定字符集为UTF8
      mysql_select_db($mysql_db , $conn);
	  return $conn;
}
	//设置页面编码
header("Content-type:text/html;charset=UTF-8");
$wantusername=trim($_GET["wantusername"]);
$conn=mysql_open();
$sql="select * from users where username='$wantusername'";
$query=mysql_query($sql);
$rst=mysql_fetch_object($query);
mysql_close($conn);
if ($rst==false)
{
echo 'false';
}
else
{
echo 'true';
}
?>
