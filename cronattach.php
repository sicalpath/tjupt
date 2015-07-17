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

$limit=5;
if(!isset($_GET["id"]))die;
$mod=0+$_GET["id"];
if($mod>=$limit||$mod<0)die;

	$query = "SELECT * FROM attachments WHERE ( MOD(ID,".$limit.")=$mod AND cache_at ='0000-00-00 00:00:00' ) ORDER BY id DESC";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	while($arr = mysql_fetch_assoc($res))
	{	
		$keyword1="'%".$arr["location"]."%'";
		$keyword2="'%[attach]".$arr["dlkey"]."[/attach]%'";
		$keyword3="'%getattachment.php%dlkey=".$arr["dlkey"]."%' AND ".$arr["id"].">0";

		$forums1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM posts WHERE body like ".$keyword1." OR body like ".$keyword2." OR body like ".$keyword3 ));
		$forums=$forums1[0];
		
		$torrents1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM torrents WHERE descr like ".$keyword1." OR descr like ".$keyword2." OR descr like ".$keyword3));
		$torrents=$torrents1[0];
		
		$offers1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM offers WHERE descr like ".$keyword1." OR descr like ".$keyword2." OR descr like ".$keyword3));
		$offers=$offers1[0];
		
		$messages1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM messages WHERE msg like ".$keyword1." OR msg like ".$keyword2." OR msg like ".$keyword3));
		$messages=$messages1[0];
		
		$comments1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM comments WHERE text like ".$keyword1." OR text like ".$keyword2." OR text like ".$keyword3));
		$comments=$comments1[0];
		
		$requests1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM req WHERE introduce like ".$keyword1." OR introduce like ".$keyword2." OR introduce like ".$keyword3));
		$requests=$requests1[0];
		
		$query2 = "UPDATE attachments SET cache_at =NOW(),forums ='".$forums."', torrents = '".$torrents."', offers ='".$offers."', messages ='".$messages."', comments ='".$comments."', requests ='".$requests."' WHERE id = '".$arr["id"] ."'";
		
		$res2 = sql_query($query2) or sqlerr(__FILE__, __LINE__);
		
	}
	
	
	
	
		$query = "SELECT * FROM attachments WHERE MOD(ID,".$limit.")=$mod ORDER BY cache_at ASC LIMIT 2000";
		
		$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
		
		
	while($arr = mysql_fetch_assoc($res))
	{	
		$keyword1="'%".$arr["location"]."%'";
		$keyword2="'%[attach]".$arr["dlkey"]."[/attach]%'";
		$keyword3="'%getattachment.php%dlkey=".$arr["dlkey"]."%' AND ".$arr["id"].">0";

		$forums1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM posts WHERE body like ".$keyword1." OR body like ".$keyword2." OR body like ".$keyword3 ));
		$forums=$forums1[0];
		
		$torrents1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM torrents WHERE descr like ".$keyword1." OR descr like ".$keyword2." OR descr like ".$keyword3));
		$torrents=$torrents1[0];
		
		$offers1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM offers WHERE descr like ".$keyword1." OR descr like ".$keyword2." OR descr like ".$keyword3));
		$offers=$offers1[0];
		
		$messages1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM messages WHERE msg like ".$keyword1." OR msg like ".$keyword2." OR msg like ".$keyword3));
		$messages=$messages1[0];
		
		$comments1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM comments WHERE text like ".$keyword1." OR text like ".$keyword2." OR text like ".$keyword3));
		$comments=$comments1[0];
		
		$requests1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM req WHERE introduce like ".$keyword1." OR introduce like ".$keyword2." OR introduce like ".$keyword3));
		$requests=$requests1[0];
		
		$query2 = "UPDATE attachments SET cache_at =NOW(),forums ='".$forums."', torrents = '".$torrents."', offers ='".$offers."', messages ='".$messages."', comments ='".$comments."', requests ='".$requests."' WHERE id = '".$arr["id"] ."'";
		
		$res2 = sql_query($query2) or sqlerr(__FILE__, __LINE__);
		
	}
	
	
	