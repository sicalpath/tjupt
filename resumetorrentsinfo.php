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

	$query = "SELECT name,id,descr FROM torrents WHERE category=401 ";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset =array();
		$fullname=$arr["name"];
		$location=substr($fullname ,1,strpos($fullname,"]")-1);
		$secondname=str_replace("[".$location."]","",$fullname);
		$cname=substr($secondname ,1,strpos($secondname,"]")-1);
	/*
		if($location == "欧洲" || $location == "北美")
			$language = "英语";
		elseif($location == "大陆")
			$language = "国语";
		elseif($location == "香港")
			$language = "粤语";
		elseif($location == "日本")
			$language = "日语";
		elseif($location == "韩国")
			$language = "韩语";
	*/
		$descr=$arr["descr"];
//		print $descr;

		if(preg_match('/类[\s,　]*别[\s,\:,：,　]+.*\n/',$descr,$match))
		{
			$lang=$match[0];
			$language = str_replace(" ","",$lang);
			$language = str_replace("　","",$language);
			$language = str_replace("类别","",$language);
			$language = str_replace("\n","",$language);
			$language = str_replace("\r","",$language);
			$language = str_replace("：","",$language);
			$language = str_replace(":","",$language);
			$language = str_replace("】","",$language);
			$language = str_replace("[color=red]","",$language);
			$language = str_replace("[color=Red]","",$language);
			$language = str_replace("[/color]","",$language);
			if(strlen($language)<20)
			{
			$updateinfoset[] = "specificcat = '".mysql_escape_string($language)."'";
		$sql=join(",",$updateinfoset);
		print($arr[id]."SQL：".$sql."  ");
		sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		}
		}
//		$updateinfoset[] = "cname = '".mysql_escape_string($cname)."'";
//		$updateinfoset[] = "district = '".mysql_escape_string($location)."'";
		
		
		
		
		print("<br/>");
	
		ob_flush();
		flush();
	}
//	if($id)
//	sql_query("UPDATE iplog SET duplicate='yes' WHERE id= '".join("' OR id = '",$id)."'" );
//	print("<br/>共删除了".$count."行记录！");