<?php
require "include/bittorrent.php";
dbconn(true);
function gbkToUtf8 ($value) {
   return iconv("gbk","UTF-8", $value);
}

$torrentid = 0 + $_GET["torrentid"];
$torrent = sql_query("SELECT * FROM torrents where id = ".sqlesc($torrentid)." limit 0, 1;") or die(mysql_error());
if(mysql_num_rows($torrent) > 0) {
	$row = mysql_fetch_array($torrent);
	print($row['descr']);
} else {
	die(0);
}

?>
