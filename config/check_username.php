<?php
include("/include/bittorrent.php");
dbconn();
/*
$auth_key = "031fXxVutTt18ZJPW6g57gEQ44jfl58VxI7t7RI3TShgkNr215eKmSw3Z9Gm1XIg";
$username = isset($_POST['username']) ? daddslashes($_POST['username']) : '';
$password = isset($_POST['password']) ? daddslashes($_POST['password']) : '';
$validate = isset($_POST['validate']) ? daddslashes($_POST['validate']) : '';
if ($validate != md5($username . $password . $auth_key))
{
	die('1');
}

$res = sql_query("SELECT id, passhash, secret, enabled, status, passkey FROM users WHERE username = " . sqlesc($username));
$row = mysql_fetch_array($res);

if ((!$row)||($row['status'] == 'pending')||($row["passhash"] != md5($row["secret"] . $password . $row["secret"]))||($row["enabled"] == "no"))
{
	die('2');
}
else
{
	echo $row['passkey'];
}*/

?>