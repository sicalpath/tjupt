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

	$query = "SELECT id, name FROM torrents WHERE category=407 ";
	//$query = "SELECT name,id FROM torrents WHERE id=318";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	$i=0;
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset =array();
		
		$fullname=$arr["name"];
		$specificcat = substr($fullname ,1,strpos($fullname,"]")-1);
		$secondname = str_replace("[".$specificcat."]","",$fullname);
		
		$language = "";
		$ename = "";
		$cname = "";
		$issuedate = "";
		$thirdname = "";
		$forthdname = "";
		$name1 = "";
		$name2 = "";
		$name3 = "";
		
		$query1 = "select specificcat,issuedate,cname,ename,language from torrentsinfo where torid = ".$arr["id"];
		$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
		while($arr1 = mysql_fetch_assoc($res1)){
			if($arr1['issuedate']!=""){				
				$issuedate = substr($secondname ,1,strpos($secondname,"]")-1);
				$thirdname = str_replace("[".$issuedate."]","",$secondname);
				$name1 = $thirdname;
			}
			else
				$name1 = $secondname;
			
			
			$cname = substr($name1 ,1,strpos($name1,"]")-1);
			$name2 = str_replace("[".$cname."]","",$name1);	
			
			if($arr1['ename']!=""){				
				$ename = substr($name2 ,1,strpos($name2,"]")-1);
				$forthdname = str_replace("[".$ename."]","",$name2);
				$name3 = $forthdname;
			}
			else
				$name3 = $name2;
				
			if($arr1['language']!=""){				
				$language = substr($name3 ,1,strpos($name3,"]")-1);
			}
			
			//if($specificcat!="" && strstr($arr1["specificcat"], '?'))
				//$updateinfoset[] = "specificcat = '".mysql_escape_string($specificcat)."'";
			//if($issuedate!="" && strstr($arr1["issuedate"], '?'))
				//$updateinfoset[] = "issuedate = '".mysql_escape_string($issuedate)."'";		
			if($cname!="" && strstr($arr1["cname"], '?'))
				$updateinfoset[] = "cname = '".mysql_escape_string($cname)."'";	
			if($ename!="" && strstr($arr1["ename"], '?'))
				$updateinfoset[] = "ename = '".mysql_escape_string($ename)."'";	
			if($language!="" && strstr($arr1["language"], '?'))
				$updateinfoset[] = "language = '".mysql_escape_string($language)."'";	
		
			$sql=join(",",$updateinfoset);
			if($sql!=""){
				print($i."   ");
				//print($arr[id]."  ");
		
				//print("SQL：".$sql."  ");
		
				//if($sql!="")
				sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
				print("<br/>");
				$i++;
		}
		
		
		}
		ob_flush();
		flush();
	}
?>