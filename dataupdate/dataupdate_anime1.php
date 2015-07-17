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

	$query = "SELECT id, name FROM torrents WHERE category=405 ";
	//$query = "SELECT name,id FROM torrents WHERE id=318";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	$i=0;
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset =array();
		
		$fullname=$arr["name"];
		$specificcat = substr($fullname ,1,strpos($fullname,"]")-1);
		$secondname = str_replace("[".$specificcat."]","",$fullname);
		$substeam = "";
		$ename = "";
		$cname = "";
		$resolution = "";
		$animenum = "";
		$format = "";
		$issuedate = "";
		
		$query1 = "select ename, cname,specificcat,substeam,resolution,animenum,format,issuedate from torrentsinfo where torid = ".$arr["id"];
		$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
		while($arr1 = mysql_fetch_assoc($res1)){
			if($arr1['substeam']!=""){				
				$substeam = substr($secondname ,1,strpos($secondname,"]")-1);
				$thirdname = str_replace("[".$substeam."]","",$secondname);
						
				if($arr1['cname']!=""){
					$cname = substr($thirdname ,1,strpos($thirdname,"]")-1);	
					$forthname = str_replace("[".$cname."]","",$thirdname);
				}	
			}
			else{
				if($arr1['cname']!=""){
					$cname = substr($secondname ,1,strpos($secondname,"]")-1);	
					$thirdname = str_replace("[".$cname."]","",$secondname);
				}
			}	
		}
		
		if($specificcat!="" && strstr($arr1["specificcat"], '?'))
				$updateinfoset[] = "specificcat = '".mysql_escape_string($specificcat)."'";
			if($substeam!="" && strstr($arr1["substeam"], '?'))
				$updateinfoset[] = "substeam = '".mysql_escape_string($substeam)."'";		
			if($cname!="" && strstr($arr1["cname"], '?'))
				$updateinfoset[] = "cname = '".mysql_escape_string($cname)."'";	
		
		$sql=join(",",$updateinfoset);
		//print($arr[id]."  ");
		//print("类型：".$specificcat."  ");
		//print("中文名：".$cname."  ");
		//print("字幕组：".$substeam."  ");
		
		if($sql!="")
			sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
		print($i."<br/>");
		$i++;
	
		ob_flush();
		flush();
	}
?>