<?php

/*
description:GET方式调用，传递参数为种子id，正常调用成功后输出为1，出现异常会打印错误。
by xiaobao
************/
//FTP服务器参数

header("Expires:   Mon,   26   Jul   1997   00:00:00   GMT");  
header("Cache-Control:   no-cache,   must-revalidate");  
header("Pragma:   no-cache");

require_once("include/bittorrent.php");
dbconn(true);
require_once(get_langfile_path("torrents.php"));
loggedinorreturn();
$seedServer="202.113.13.92";
$seedServerUsername="ina";
$seedServerPassword="tjuina";
$ftppath = "/home/ina/rtorrent/watch/";
require "include/bittorrent.php";
dbconn();
loggedinorreturn();

if (get_user_class() < 13 )  //权限验证，类管理员以上，可调
stderr("Error", "Access denied.");
if(!is_numeric($_GET['id']))              //传值检测
stderr("Error", "limited numeric.");
$id=$_GET['id'];

$res = sql_query("SELECT * FROM autoseeding WHERE torrentid = ".$id." AND remark = 'seed'" ) or sqlerr();

if(mysql_num_rows($res) > 0)
{
	//ftp操作，撤种。
	$conn_id = ftp_connect($seedServer,21) or die("Couldn't connect to $seedServer");
	ftp_login($conn_id, $seedServerUsername, $seedServerPassword);
	if(ftp_delete($conn_id, $ftppath.$_GET['id'].".torrent")) {
	$row = mysql_fetch_assoc($res);
	sql_query("DELETE FROM autoseeding WHERE torrentid = ".sqlesc($id)." AND remark = 'seed'") or sqlerr(__FILE__, __LINE__);
	sql_query("DELETE FROM peers WHERE userid = 99 AND torrent = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	sql_query("UPDATE torrents SET ".($row['completed']=="yes"?"seeders = seeders":"leechers = leechers")." - 1 WHERE id = ".$row['torrentid']) or sqlerr(__FILE__, __LINE__);
	write_log("Torrent ".$_GET['id']." is UnServerSeeding by ".$CURUSER[username],'mod');
	}
	else {
		echo "failed";
	}

ftp_close($conn_id);
exit;
}
else{

$passkey="6f5dcb625c5f3ee971d9cdf590b0f554";  //test by Wall-E'passkey
$announce_urls[0] = "http://pttracker4.tju.edu.cn/walleannounce.php";
$base_announce_url = ($announce_urls[1] == ""?$announce_urls[0]:"http://pttracker.tju.edu.cn/walleannounce.php");   //announce地址
$res = sql_query("SELECT name, filename, save_as,  size, owner,banned FROM torrents WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_assoc($res);
//echo "dd";
$fn = "$torrent_dir/$id.torrent";
if (!$row || !is_file($fn) || !is_readable($fn))
	stderr("Error", "file is not find.");
//	echo "dd";
sql_query("UPDATE torrents SET hits = hits + 1 WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
require_once "include/benc.php";
//echo "ddd";
$dict = bdec_file($fn, $max_torrent_size);
$dict['value']['announce']['value'] = $base_announce_url . "?passkey=$passkey";
$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);
if ($announce_urls[1] != "") // add multi-tracker
{
	$dict['value']['announce-list']['type'] = "list";
	$dict['value']['announce-list']['value'][0]['type'] = "list";
	$dict['value']['announce-list']['value'][0]['value'][0]["type"] = "string";
	$dict['value']['announce-list']['value'][0]['value'][0]["value"] = $announce_urls[0] . "?passkey=$passkey";
	$dict['value']['announce-list']['value'][0]['value'][0]["string"] = strlen($dict['value']['announce-list']['value'][0]['value'][0]["value"]).":".$dict['value']['announce-list']['value'][0]['value'][0]["value"];
	$dict['value']['announce-list']['value'][0]['value'][0]["strlen"] = strlen($dict['value']['announce-list']['value'][0]['value'][0]["string"]);
	$dict['value']['announce-list']['value'][0]['string'] = "l".$dict['value']['announce-list']['value'][0]['value'][0]["string"]."e";
	$dict['value']['announce-list']['value'][0]['strlen'] = strlen($dict['value']['announce-list']['value'][0]['string']);
	$dict['value']['announce-list']['value'][1]['type'] = "list";
	$dict['value']['announce-list']['value'][1]['value'][0]["type"] = "string";
	$dict['value']['announce-list']['value'][1]['value'][0]["value"] = $announce_urls[1] . "?passkey=$passkey";
	$dict['value']['announce-list']['value'][1]['value'][0]["string"] = strlen($dict['value']['announce-list']['value'][0]['value'][0]["value"]).":".$dict['value']['announce-list']['value'][0]['value'][0]["value"];
	$dict['value']['announce-list']['value'][1]['value'][0]["strlen"] = strlen($dict['value']['announce-list']['value'][0]['value'][0]["string"]);
	$dict['value']['announce-list']['value'][1]['string'] = "l".$dict['value']['announce-list']['value'][0]['value'][0]["string"]."e";
	$dict['value']['announce-list']['value'][1]['strlen'] = strlen($dict['value']['announce-list']['value'][0]['string']);
	$dict['value']['announce-list']['string'] = "l".$dict['value']['announce-list']['value'][0]['string'].$dict['value']['announce-list']['value'][1]['string']."e";
	$dict['value']['announce-list']['strlen'] = strlen($dict['value']['announce-list']['string']);

}

$handle=fopen("temp_torrent/temp.torrent",'w');//将种子写入临时文件
if (fwrite($handle, benc($dict)) === FALSE) {
        stderr("Error", "Can't Open TempFile.");
        //exit;
    }
//fclose($handle);


if (@$login_result = ftp_login($conn_id, $seedServerUsername, $seedServerPassword)) {
    echo "Connected as $seedServerUsername@$seedServer\n";
} else {
    echo "Couldn't connect as $ftp_user\r\n";
}
//ftp_pasv($conn_id,FALSE);
echo "pasv:".ftp_pasv($conn_id,true)."<br>";

/*if(ftp_get($conn_id, $_GET['id'].".torrent", "rtorrent/watch/13.torrent", FTP_ASCII)) {
    echo "Successfully uploaded ".$_GET['id'].".torrent\r\n";
	//sql_query("INSERT INTO autoseeding (torrentid) VALUES (".sqlesc($id).")") or sqlerr(__FILE__, __LINE__);
} else {
    echo "There was a problem while uploading ".$_GET['id'].".torrent\r\n";
    exit;
}
*/
if(ftp_put($conn_id, $ftppath.$_GET['id'].".torrent", "temp_torrent/temp.torrent", FTP_BINARY)) {
    echo "Successfully uploaded ".$_GET['id'].".torrent\r\n";
	sql_query("INSERT INTO autoseeding (torrentid , remark ) VALUES (".sqlesc($id)." , 'seed')") or sqlerr(__FILE__, __LINE__);
	echo $_GET['id'].".torrent is transfer to seedServer";
	write_log("Torrent ".$_GET['id']." is serverSeeding by ".$CURUSER[username],'mod');
} else {
    echo "There was a problem while uploading ".$_GET['id'].".torrent\r\n";
}
ftp_close($conn_id);

$delete_path="temp_torrent/temp.torrent";
fclose($handle);
//echo $delete_path;
//unlink($delete_path);
    if(file_exists($delete_path)){  
        if(unlink($delete_path)){  
            echo "删除文件成功";  
        }  
    }  
    else{  
        echo "文件似乎不存在无法删除";  
    } 
	
}


	
?>
