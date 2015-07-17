<?php
require_once('include/bittorrent.php');
dbconn();
//require_once(get_langfile_path());
//require(get_langfile_path("",true));
loggedinorreturn();
parked();

$tjuptid = $_POST['tjuptid'];
$username = $_POST['username'];
$institution = $_POST['institution'];

$res = sql_query("SELECT * FROM carsimapping WHERE tjuptid = $tjuptid and username = $username and institution = '$institution'") or sqlerr();
if(mysql_num_rows($res) == 0)
	$ret = sql_query("INSERT INTO carsimapping (tjuptid, username, institution) VALUES ($tjuptid, $username, '$institution')") or sqlerr(__FILE__, __LINE__);
echo(mysql_num_rows($res));	
//echo("sb");
redirect("" . get_protocol_prefix() . "$BASEURL/userdetails.php?id=".$tjuptid);
?>