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

$res = sql_query("SELECT * FROM carsimapping WHERE tjuptid = $tjuptid AND username = $username AND institution = '$institution'") or sqlerr();
if(mysql_num_rows($res) != 0){
	while($row=mysql_fetch_assoc($res))
		sql_query("DELETE FROM carsimapping WHERE id=".sqlesc($row['id'])) or sqlerr(__FILE__, __LINE__);
}

//echo(mysql_num_rows($res));	
//echo("sb");
//redirect("" . get_protocol_prefix() . "$BASEURL/userdetails.php?id=".$tjuptid);
redirect("" . get_protocol_prefix() . "$BASEURL/offers.php");
?>