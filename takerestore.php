<?php
require_once("include/bittorrent.php");
dbconn();
loggedinorreturn();
if (get_user_class() < $torrentmanage_class)
permissiondenied();

$res = sql_query("SELECT id,name,owner FROM torrents WHERE id=".sqlesc($_GET['id'])." LIMIT 1") or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_array($res);

sql_query("UPDATE torrents SET pulling_out = '0' WHERE id=".sqlesc($_GET['id']));

write_log(($CURUSER["id"] == $row["owner"]?"发布者":"管理员")." $CURUSER[username] 恢复了资源 {$_GET['id']} ({$row[name]}) ",'normal');

stdhead('操作成功');
begin_main_frame();
print("<center>已经恢复 {$row['name']}</center>");
end_main_frame();
stdfoot();
?>
