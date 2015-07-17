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

	$query = "SELECT id, name FROM torrents WHERE category=411 ";
	//$query = "SELECT name,id FROM torrents WHERE id=318";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	$i=0;
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset = array();
		$sql = "";
		$specificcat = "";
		$cname = "";
		$secondname = "";
		
		$fullname=$arr["name"];
		$specificcat = substr($fullname ,1,strpos($fullname,"]")-1);
		$secondname = str_replace("[".$specificcat."]","",$fullname);
		$cname = substr($secondname ,1,strpos($secondname,"]")-1);
		
		$query1 = "select specificcat,cname from torrentsinfo where torid = ".$arr["id"];
		$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
		
		while($arr1 = mysql_fetch_assoc($res1)){
			
			if($specificcat!="" && strstr($arr1["specificcat"], '?'))
				$updateinfoset[] = "specificcat = '".mysql_escape_string($specificcat)."'";				
			if($cname!="" && strstr($arr1["cname"], '?'))
				$updateinfoset[] = "cname = '".mysql_escape_string($cname)."'";	
				
			$sql=join(",",$updateinfoset);
				
			if($sql!=""){
				print($i."   ");
				print($arr[id]."   ");//specificcat,issuedate,cname,ename,language,
				print("SQL：".$sql."  ");
				sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
				print("<br/>");
				$i++;
			}
		}
		ob_flush();
		flush();
	}
?>