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

	$query = "SELECT id, name FROM torrents WHERE category=409 ";
	//$query = "SELECT name,id FROM torrents WHERE id=318";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	$i=0;
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset = array();
		
		$fullname=$arr["name"];
		$platform = substr($fullname ,1,strpos($fullname,"]")-1);
		$secondname = str_replace("[".$platform."]","",$fullname);
		
		$specificcat = "";
		$ename = "";
		$cname = "";
		$language = "";
		$format = "";
		$tvshowsremarks = "";
		$thirdname = "";
		$forthname = "";
		$name1 = "";
		$name2 = "";
		$secondname1 = "";
		$thirdname1 = "";
		$secondname2 = "";
		$sql = "";
		
		$query1 = "select specificcat,ename,cname,language,format,tvshowsremarks from torrentsinfo where torid = ".$arr["id"];
		$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
		while($arr1 = mysql_fetch_assoc($res1)){
			if($arr1['specificcat']!=""){				
				$specificcat = substr($secondname ,1,strpos($secondname,"]")-1);
				$thirdname = str_replace("[".$specificcat."]","",$secondname);
				
				if($arr1['ename']!=""){
					$ename = substr($thirdname ,1,strpos($thirdname,"]")-1);
					$forthname = str_replace("[".$ename."]","",$thirdname);
				}
			}
			else{
				if($arr1['ename']!=""){
					$ename = substr($secondname ,1,strpos($secondname,"]")-1);
					$thirdname = str_replace("[".$ename."]","",$secondname);
				}
			}
			if($forthname!="")
				$name1 = $forthname;
			elseif($thirdname!="")
				$name1 = $thirdname;
			else
				$name1 = $secondname;
				
			//if($specificcat!="" && strstr($arr1["specificcat"], '?'))
				//$sql = "specificcat = '".mysql_escape_string($specificcat)."'";
						
			if($arr1['cname']!=""){				
				$cname = substr($name1 ,1,strpos($name1,"]")-1);
				$secondname1 = str_replace("[".$cname."]","",$name1);
				
				if($arr1['language']!="")
					$language = substr($secondname1 ,1,strpos($secondname1,"]")-1);
					$thirdname1 = str_replace("[".$language."]","",$secondname1);
			}
			else{
				if($arr1['language']!="")
					$language = substr($name1 ,1,strpos($name1,"]")-1);
					$secondname1 = str_replace("[".$language."]","",$name1);
			}
			if($thirdname1!="")
				$name2 = $thirdname1;
			elseif($secondname1!="")
				$name2 = $secondname1;
			else
				$name2 = $name1;
				
			if($arr1['format']!=""){				
				$format = substr($name2 ,1,strpos($name2,"]")-1);
				$secondname2 = str_replace("[".$format."]","",$name2);
				
				if($arr1['tvshowsremarks']!="")
					$tvshowsremarks = substr($secondname2 ,1,strpos($secondname2,"]")-1);
			}
			else{
				if($arr1['tvshowsremarks']!="")
					$tvshowsremarks = substr($name2 ,1,strpos($name2,"]")-1);
			}
			
			if($cname!="" && strstr($arr1["cname"], '?'))
				$updateinfoset[] = "cname = '".mysql_escape_string($cname)."'";	
			if($language!="" && strstr($arr1["language"], '?'))
				$updateinfoset[] = "language = '".mysql_escape_string($language)."'";
			if($format!="" && strstr($arr1["format"], '?'))
				$updateinfoset[] = "format = '".mysql_escape_string($format)."'";	
			if($tvshowsremarks!="" && strstr($arr1["tvshowsremarks"], '?'))
				$updateinfoset[] = "tvshowsremarks = '".mysql_escape_string($tvshowsremarks)."'";
			$sql=join(",",$updateinfoset);
				
			if($sql!=""){
				print($i."   ");
				//print($arr[id]."   ");//specificcat,issuedate,cname,ename,language,
				//print("SQL：".$sql."  ");
				sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
				print("<br/>");
				$i++;
			}
		}
		ob_flush();
		flush();
	}
?>