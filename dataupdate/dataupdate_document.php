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

	$query = "SELECT id, name FROM torrents WHERE category=404 ";
	//$query = "SELECT name,id FROM torrents WHERE id=243";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	$i=0;
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset =array();
		
		$fullname=$arr["name"];
		$specificcat = "";	
		$ename = "";
		$cname = "";
		
		$query1 = "select ename, cname,specificcat from torrentsinfo where torid = ".$arr["id"];
		$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
		while($arr1 = mysql_fetch_assoc($res1)){
			if($arr1['cname']!=""){				
				$cname = substr($fullname ,1,strpos($fullname,"]")-1);
				$secondname = str_replace("[".$cname."]","",$fullname);
						
				if($arr1['ename']!=""){
					$ename = substr($secondname ,1,strpos($secondname,"]")-1);	
					$thirdname = str_replace("[".$ename."]","",$secondname);
					
					if($arr1['specificcat']!="")
						$specificcat = substr($thirdname ,1,strpos($thirdname,"]")-1);
				}
				else
					if($arr1['specificcat']!="")
						$specificcat = substr($secondname ,1,strpos($secondname,"]")-1);				
			}
			else{
				if($arr1['ename']!=""){
					$ename = substr($fullname ,1,strpos($fullname,"]")-1);	
					$secondname = str_replace("[".$ename."]","",$fullname);
					
					if($arr1['specificcat']!="")
						$specificcat = substr($secondname ,1,strpos($secondname,"]")-1);
				}
				else
					if($arr1['specificcat']!="")
						$specificcat = substr($fullname ,1,strpos($fullname,"]")-1);
			}	
			if($specificcat!="" && strpos($arr1["specificcat"], '?')===0)
				$updateinfoset[] = "specificcat = '".mysql_escape_string($specificcat)."'";
			if($cname!="" && strpos($arr1["cname"], '?')===0)
				$updateinfoset[] = "cname = '".mysql_escape_string($cname)."'";		
		}
		
		
		
		$sql=join(",",$updateinfoset);
		//print($arr[id]."  ");
		//print("类型：".$specificcat."  ");
		//print("中文名：".$cname."  ");
		//print("英文名：".$ename."  ");
		
		if($sql!="")
			sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
		print($i."<br/>");
		$i++;
	
		ob_flush();
		flush();
	}
?>