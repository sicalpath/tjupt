<?php
require_once("include/bittorrent.php");
header("Content-Type: text/html; charset=utf-8");

dbconn();
require_once(get_langfile_path("", false, get_langfolder_cookie()));
failedloginscheck ();
cur_user_check () ;

function bark($text = "")
{
  global $lang_takecarsilogin;
  $text =  ($text == "" ? $lang_takecarsilogin['std_login_fail_note'] : $text);
  stderr($lang_takecarsilogin['std_login_fail'], $text,false);
}

//if ($iv == "yes")
	//check_code ($_POST['imagehash'], $_POST['imagestring'],'login.php',true);
	
$tjuptid = $_POST["carsiselect"];
	
$res = sql_query("SELECT * FROM carsimapping WHERE tjuptid = " . sqlesc($tjuptid));
$row_ = mysql_fetch_array($res);

$ret = sql_query("SELECT passhash, secret, enabled, status FROM users WHERE id = " . sqlesc($tjuptid));
$row = mysql_fetch_array($ret);

if (!$row_)
	failedlogins();

if ($_POST["securelogin"] == "yes")
{
	$securelogin_indentity_cookie = true;
	$passh = md5($row["passhash"].$_SERVER["REMOTE_ADDR"]);
}
else
{
	$securelogin_indentity_cookie = false;
	$passh = md5($row["passhash"]);
}


if ($securelogin=='yes' || $_POST["ssl"] == "yes")
{
	$pprefix = "https://";
	$ssl = true;
}
else
{
	$pprefix = "http://";
	$ssl = false;
}
if ($securetracker=='yes' || $_POST["trackerssl"] == "yes")
{
	$trackerssl = true;
}
else
{
	$trackerssl = false;
}

	logincookie($tjuptid, $passh,1,0,$securelogin_indentity_cookie, $ssl, $trackerssl);
	//sessioncookie($tjuptid, $passh,false);

if (!empty($_POST["returnto"]))
	header("Location: $_POST[returnto]");
else
	header("Location: index.php");
?>
