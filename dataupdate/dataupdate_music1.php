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
	$artist = "";
	$specificcat = "";
	$issuedate = "";
	$format = "";
	$name1 = "";
	$name2 = "";
	$name3 = "";
	
	//$query1 = "select specificcat,hqname,artist,format,issuedate,language,hqtone from torrentsinfo where torid = ".$arr["id"]. " and hqname like '%?%'";
	$query1 = "select artist,specificcat,issuedate,format from torrentsinfo where torid = ".$arr["id"]. " and format like '%?%'";
	$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
	while($arr1 = mysql_fetch_assoc($res1)){
		if($arr1['artist']!=""){				
			$artist = substr($secondname ,1,strpos($secondname,"]")-1);
			$thirdname = str_replace("[".$artist."]","",$secondname);
			$name1 = $thirdname;
		}
		else
			$name1 = $secondname;
			
		if($arr1['specificcat']!=""){				
			$specificcat = substr($name1 ,1,strpos($name1,"]")-1);
			$name2 = str_replace("[".$specificcat."]","",$name1);
		}
		else
			$name2 = $name1;
			
		if($arr1['issuedate']!=""){				
			$issuedate = substr($name2 ,1,strpos($name2,"]")-1);
			$name3 = str_replace("[".$issuedate."]","",$name2);
		}
		else
			$name3 = $name2;
			
		if($arr1['format']!="")			
			$format = substr($name3 ,1,strpos($name3,"]")-1);
		
		$sql = "";
		if($format!="")
			$sql = "format = '".mysql_escape_string($format)."'";
		print($i."   ");
		//print($arr[id]."  ");
		//print("SQL：".$sql);
		
		if($sql!="")
			sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
		print("<br/>");
		$i++;
		
	}
}
ob_flush();
flush();
	
?>