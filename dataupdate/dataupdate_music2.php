<?php
ob_start();
set_time_limit(0);
ignore_user_abort(TRUE);
header("Content-Type:text/html; charset=utf-8");
header("Connection: close");
ob_end_flush();
flush();

require_once("include/bittorrent.php");
dbconn();
if (ob_get_level() == 0) ob_start();
$i=0;
$query = "select id, name from torrents where category=406";
$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

while($arr = mysql_fetch_assoc($res)){
	$fullname=$arr["name"];
	$hqname = substr($fullname ,1,strpos($fullname,"]")-1);
	$secondname = str_replace("[".$hqname."]","",$fullname);
	
	$sql = "";
	$specificcat = "";
	$language = "";
	
	//$query1 = "select specificcat,hqname,artist,format,issuedate,language,hqtone from torrentsinfo where torid = ".$arr["id"]. " and hqname like '%?%'";
	$query1 = "select artist,specificcat from torrentsinfo where torid = ".$arr["id"]. " and language = '??'";
	//$query1 = "select specificcat from torrentsinfo where torid = ".$arr["id"]. " and hqtone = '??'";
	$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
	while($arr1 = mysql_fetch_assoc($res1)){
		if($arr1['artist']!=""){				
			$artist = substr($secondname ,1,strpos($secondname,"]")-1);
			$thirdname = str_replace("[".$artist."]","",$secondname);
			$name1 = $thirdname;
		}
		else
			$name1 = $secondname;
			
		if($arr1['specificcat']!="")				
			$specificcat = substr($name1 ,1,strpos($name1,"]")-1);
		
		if($specificcat=="华语")
			$language = "国语";
		elseif($specificcat=="欧美")
			$language = "英语";
		
		$sql = "";
		if($language!="")
			$sql = "language = '".mysql_escape_string($language)."'";
		//print($i."   ");
		//print($arr[id]."  ");
		//print("SQL：".$sql);
		
		/*$hqtone = "无损";
		$sql = "hqtone = '".mysql_escape_string($hqtone)."'";*/
		if($sql!="")
			sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
		print("<br/>");
		$i++;
		
	}
}
ob_flush();
flush();
	
?>