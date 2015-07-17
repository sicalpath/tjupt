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
		$query = "select torid, district from torrentsinfo where category=405 and district like '%?%'";
		$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
		while($arr = mysql_fetch_assoc($res)){
			$district = "日漫";
			$sql = "district = '".mysql_escape_string($district)."'";
			
			print($i."   ");
			print($arr[torid]."  ");
			print("SQL：".$sql);
		
			if($sql!="")
				sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[torid]") or sqlerr(__FILE__, __LINE__);
		
			print("<br/>");
			$i++;
		}
		ob_flush();
		flush();
	
?>