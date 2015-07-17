<?php
require_once("include/bittorrent.php");
dbconn();

/************** limit by ip address *******************/
if($_SERVER['REMOTE_ADDR']=="59.67.33.217")
{
/******************get invite code**********************/
$hash  = md5(mt_rand(1,10000).$CURUSER['PTadmin'].TIMENOW.$CURUSER['passhash']);
$msg="http://pt.tju.edu.cn/signup.php?type=invite&invitenumber=$hash";

/********   write invite code into mysql  **************/
sql_query("INSERT INTO invites (inviter, invitee, hash, time_invited) VALUES ('".mysql_real_escape_string(9999)."', '".mysql_real_escape_string("ptmaster@tju.edu.cn")."', '".mysql_real_escape_string($hash)."', " . sqlesc(date("Y-m-d H:i:s")) . ")");

/********************output the key**********************/
echo($hash);
}

/*
else{
$msg="Your ip ".$_SERVER['REMOTE_ADDR']." is not allowed";
echo($msg);
}*/
/*
<?php
     $url = "http://202.113.13.170/self_invite.php";
     $fp = @fopen($url,"r") or die("This page not exist");
     $code = fgets($fp,'35');
     print "<a href=http://pt.tju.edu.cn/signup.php?type=invite&invitenumber=".$code.">http://pt.tju.edu.cn/signup.php?type=invite&invitenumber=".$code."</a>";
     fclose($fp);
?>

*/