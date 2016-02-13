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

	$query = "SELECT id, name FROM torrents WHERE category=403";
	//$query = "SELECT name,id FROM torrents WHERE id=45998";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	$i = 1;
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset =array();
		
		$fullname=$arr["name"];
		$district = "";	
		$issuedate = "";
		$cname = "";
		$tvshowcontent = "";
		
		$query1 = "select district, issuedate, cname, tvshowscontent from torrentsinfo where torid = ".$arr["id"];
		$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
		while($arr1 = mysql_fetch_assoc($res1)){
			if($arr1['district']!=""){				
				$district = substr($fullname ,1,strpos($fullname,"]")-1);
				$secondname = str_replace("[".$district."]","",$fullname);
						
				if($arr1['issuedate']!=""){
					$issuedate = substr($secondname ,1,strpos($secondname,"]")-1);	
					$thirdname = str_replace("[".$issuedate."]","",$secondname);
					
					if($arr1['cname']!=""){
						$cname = substr($thirdname ,1,strpos($thirdname,"]")-1);
						$forthname = str_replace("[".$cname."]","",$thirdname);
						
						if($arr1['tvshowscontent']!=""){
							$tvshowcontent = substr($forthname ,1,strpos($forthname,"]")-1);
						}
					}
					else
						if($arr1['tvshowscontent']!=""){
							$tvshowcontent = substr($thirdname ,1,strpos($thirdname,"]")-1);
						}
				}
				else{
					if($arr1['cname']!=""){
						$cname = substr($secondname ,1,strpos($secondname,"]")-1);
						$thirdname = str_replace("[".$cname."]","",$secondname);
						
						if($arr1['tvshowscontent']!=""){
							$tvshowcontent = substr($thirdname ,1,strpos($thirdname,"]")-1);
						}
					}
					else
						if($arr1['tvshowscontent']!=""){
							$tvshowcontent = substr($secondname ,1,strpos($secondname,"]")-1);
						}
				}
				
			}
			else{
				if($arr1['issuedate']!=""){
					$issuedate = substr($fullname ,1,strpos($fullname,"]")-1);	
					$secondname = str_replace("[".$issuedate."]","",$fullname);
					
					if($arr1['cname']!=""){
						$cname = substr($secondname ,1,strpos($secondname,"]")-1);
						$thirdname = str_replace("[".$cname."]","",$secondname);
						
						if($arr1['tvshowscontent']!=""){
							$tvshowcontent = substr($thirdname ,1,strpos($thirdname,"]")-1);
						}
					}
					else
						if($arr1['tvshowscontent']!=""){
							$tvshowcontent = substr($secondname ,1,strpos($secondname,"]")-1);
						}
				}
				else{
					if($arr1['cname']!=""){
						$cname = substr($fullname ,1,strpos($fullname,"]")-1);
						$secondname = str_replace("[".$cname."]","",$fullname);
						
						if($arr1['tvshowscontent']!=""){
							$tvshowcontent = substr($secondname ,1,strpos($secondname,"]")-1);
						}
					}
					else
						if($arr1['tvshowscontent']!=""){
							$tvshowcontent = substr($fullname ,1,strpos($fullname,"]")-1);
						}
				}
			}
				
		}
		
		$language = "";
		if($district == "大陆" || $district == "港台")
			$language = "国语";
		elseif($district == "日韩")
			$language = "韩语";
		elseif($district == "欧美")
			$language = "英语";
		
		
		$updateinfoset[] = "district = '".mysql_escape_string($district)."'";
		$updateinfoset[] = "cname = '".mysql_escape_string($cname)."'";
		$updateinfoset[] = "issuedate = '".mysql_escape_string($issuedate)."'";
		$updateinfoset[] = "tvshowscontent = '".mysql_escape_string($tvshowcontent)."'";
		$updateinfoset[] = "language = '".mysql_escape_string($language)."'";
		
		$sql=join(",",$updateinfoset);
		//$sql = "language = '".mysql_escape_string($language)."'";
		$sql=join(",",$updateinfoset);
		//print($arr[id]."SQL：".$sql."<br/>");
		//print("地区：".$district."  ");
		//print("中文名：".$cname."  ");
		//print("发布日期：".$issuedate."  ");
		//print("节目内容：".$tvshowcontent."  ");
		sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		print($i); $i++;
		print("<br/>");
	
		ob_flush();
		flush();
	}

/*$sql = "select id, name from torrents where category = 401";
$res = sql_query($sql);
$row = mysql_fetch_array($res);
while($row){
	sql_query("UPDATE torrentsinfo SET " . join(",", $updateinfoset) . " WHERE torid = $id") or sqlerr(__FILE__, __LINE__);
	
}*/


?>