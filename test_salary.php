<?php
require_once("include/bittorrent.php");
dbconn();
function uploaderAutoSalary(){
	//获取发布员列表
	$uploaders=array();
	$query="select * from users where class >=12 and class<=13";
	$result=sql_query($query) or sqlerr ( __FILE__, __LINE__ );
	while($row=mysql_fetch_assoc($result)){
		$uploaders[]=$row['id'];
	}
	//遍历发布员列表
	//从上个月开始
	$start=date('Y-m',time()-30*86400);
	$end=date('Y-m');
	$startDate=$start.'-01 00:00:00';
	$endDate=$end.'-01 00:00:00';
	foreach($uploaders as $uploader){
		$query="select count(*) as torrents_count,sum(size) as total_size from torrents where owner=".$uploader." and added >=".sqlesc($startDate)." and added<".sqlesc($endDate);
		$result=sql_query($query) or sqlerr ( __FILE__, __LINE__ );
		$row=mysql_fetch_assoc($result);
		print_r($row);
	}
	
}
uploaderAutoSalary();
