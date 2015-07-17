<?php
//本文件用于保存torrent.php页面里的“搜索箱”和“急需做种”的显示设置。
require_once("include/bittorrent.php");
dbconn();
loggedinorreturn();
global $CURUSER;
if($_GET['needseeding']){
if(strpos($CURUSER['notifs'], "[closeneedseeding]") !== false)
sql_query("update users set notifs=replace(notifs,\"[closeneedseeding]\",\"\") where id=".sqlesc($CURUSER["id"]));
else{
$notifs = $CURUSER['notifs']."[closeneedseeding]";
sql_query("update users set notifs='".$notifs."' where id=".sqlesc($CURUSER["id"]));}}
if($_GET['searchboxmain']){
if(strpos($CURUSER['notifs'], "[closesearchbox]") !== false)
sql_query("update users set notifs=replace(notifs,\"[closesearchbox]\",\"\") where id=".sqlesc($CURUSER["id"]));
else{
$notifs = $CURUSER['notifs']."[closesearchbox]";
sql_query("update users set notifs='".$notifs."' where id=".sqlesc($CURUSER["id"]));}}?>
<script type="text/javascript">
window.opener=null;
window.open('','_self');
self.close();
</script>