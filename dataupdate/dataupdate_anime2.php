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
		
		$name1 = "";
		$thirdname = "";
		$forthname = "";
		
		$query1 = "select ename, cname,specificcat,substeam,resolution,animenum,format,issuedate from torrentsinfo where torid = ".$arr["id"]." and animenum LIKE '%?%'";
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
			
			if($animenum!="")		
			$sql = "animenum = '".$animenum."'";//$sql=join(",",$updateinfoset);
			print($i."   ");
			print($arr[id]."  ");
			print("集数：".$animenum."  ");
		
			if($sql!="")
				sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
			print("<br/>");
			$i++;
		}
		ob_flush();
		flush();
	}
?>