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

	//$query = "SELECT format, issuedate FROM torrentsinfo WHERE category = 405 and format like '%?%'";
	$query = "SELECT id, name FROM torrents WHERE category = 405";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	$i=0;
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset =array();
		
		$fullname=$arr["name"];
		//print($i." ".$fullname."<br>");$i++;
		$specificcat = substr($fullname ,1,strpos($fullname,"]")-1);
		$secondname = str_replace("[".$specificcat."]","",$fullname);
		$substeam = "";
		$ename = "";
		$cname = "";
		$resolution = "";
		$animenum = "";
		$format = "";
		$issuedate = "";
		
		$name1 = "";
		$thirdname = "";
		$forthname = "";
		
		$name2 = "";
		$thirdname1 = "";
		$forthname1 = "";
		$secondname1 = "";
		
		$query1 = "select ename, cname,specificcat,substeam,resolution,animenum,format,issuedate from torrentsinfo where torid = ".$arr["id"]." and issuedate like '%?%'";
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
			
			if($forthname!="")
				$name1 = $forthname;
			elseif($thirdname!="")
				$name1 = $thirdname;
			else
				$name1 = $secondname;
				
			if($arr1['ename']!=""){
				$ename = substr($name1 ,1,strpos($name1,"]")-1);
				$secondname1 = str_replace("[".$ename."]","",$name1);
				
				if($arr1['resolution']!=""){
					$resolution = substr($secondname1 ,1,strpos($secondname1,"]")-1);
					$thirdname1 = str_replace("[".$resolution."]","",$secondname1);	
					
					if($arr1['animenum']!=""){
						$animenum = substr($thirdname1 ,1,strpos($thirdname1,"]")-1);
						$forthname1 = str_replace("[".$animenum."]","",$thirdname1);
					}
				}
				else
					if($arr1['animenum']!=""){
						$animenum = substr($secondname1 ,1,strpos($secondname1,"]")-1);
						$thirdname1 = str_replace("[".$animenum."]","",$secondname1);
					}	
			}
			else
				if($arr1['resolution']!=""){
					$resolution = substr($name1 ,1,strpos($name1,"]")-1);
					$secondname1 = str_replace("[".$resolution."]","",$name1);	
					
					if($arr1['animenum']!=""){
						$animenum = substr($secondname1 ,1,strpos($secondname1,"]")-1);
						$thirdname1 = str_replace("[".$animenum."]","",$secondname1);
					}
				}
				else
					if($arr1['animenum']!=""){
						$animenum = substr($name1 ,1,strpos($name1,"]")-1);
						$secondname1 = str_replace("[".$animenum."]","",$name1);
					}
					
			if($forthname1!="")
				$name2 = $forthname1;
			elseif($thirdname1!="")
				$name2 = $thirdname1;
			elseif(secondname1!="")
				$name2 = $secondname1;
			else
				$name2 = $name1;
			
			if($arr1['format']!=""){				
				$format = substr($name2 ,1,strpos($name2,"]")-1);
				$seconddname2 = str_replace("[".$format."]","",$name2);
			}
			else{
				$seconddname2 = $name2;
			}
			
			if($arr1['issuedate']!="")				
				$issuedate = substr($seconddname2 ,1,strpos($seconddname2,"]")-1);
			
			if($issuedate!="" && strstr($arr1["issuedate"], '?'))
				$sql = "issuedate = '".mysql_escape_string($issuedate)."'";
			
			print($i."   ");
			print($arr[id]."  ");
			//print("SQL：".$sql."  ");
		
			if($sql!="")
				sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
			print("<br/>");
			$i++;
		}
		ob_flush();
		flush();
	}
?>