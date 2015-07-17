<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();

parked();
if($_POST["id"]){

$attachid = 0+$_POST["id"];
int_check($attachid,true);
$query = sql_query("SELECT * FROM attachments WHERE id = '".$attachid."'");
if(mysql_num_rows($query) == 1)
$arr = mysql_fetch_assoc($query);
else stderr("出错了！","附件不存在！<a href=javascript:history.go(-1)>点击这里返回</a>",0);

$userid = $arr["userid"];
int_check($userid,true);

if ($CURUSER["id"] != $userid && get_user_class() < UC_MODERATOR )
permissiondenied();

$users=sql_query("SELECT * FROM users WHERE id = '".$userid."'") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($users) == 1)
$user = mysql_fetch_assoc($users);

if (($user["privacy"] == "strong") && (get_user_class() < $prfmanage_class) && $CURUSER[id] != $user[id])//隐私等级高
permissiondenied();

if ($_POST['sure'])
{
	$filepath = dirname(__FILE__)."/attachments/";

	if(file_exists($filepath.$arr["location"])){
		unlink($filepath.$arr["location"]);
	}
	if(file_exists($filepath.$arr["location"].".thumb.jpg")){
		unlink($filepath.$arr["location"].".thumb.jpg");
	}
	
	sql_query("DELETE FROM attachments WHERE id = '".$attachid."'") or sqlerr(__FILE__, __LINE__);
	
	if (!empty($_POST["returnto"]))
		header("Location: $_POST[returnto]");
	else
		header("Location: userhistory.php?action=viewattach&id=".$userid);
}
$body = format_comment("[attach]".$arr["dlkey"]."[/attach]");
stderr("确认删除附件！","<form action=delattachment.php method=post>你确信要删除以下附件？&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type=hidden name=id value=".$attachid." />
<input type=hidden name=returnto value=".$_POST["returnto"]." />
<input type=submit name=sure value=\"删除\" />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=button onclick=\"location.href='javascript:history.go(-1)'\" value=返回 />
</form><br>文件名：".$arr["filename"]."<br/>".$body,0);
}

elseif($_POST["deleteids"]){

$ids = $_POST["deleteids"];
if(!is_array($ids))
stderr("出错了！","附件列表不存在！<a href=javascript:history.go(-1)>点击这里返回</a>",0);
foreach($ids as $attachid)
{
int_check($attachid,true);
$query = sql_query("SELECT * FROM attachments WHERE id = '".$attachid."'");

if(mysql_num_rows($query) == 1)
	$arr = mysql_fetch_assoc($query);
else
	{$print.="附件 ".$attachid." 不存在!<br/><br/>\n";continue;}

$userid = $arr["userid"];
int_check($userid,true);

if ($CURUSER["id"] != $userid && get_user_class() < UC_MODERATOR )
	{$print.="你没有删除附件 ".$attachid." 的权限<br/><br/>\n";continue;}

$users=sql_query("SELECT * FROM users WHERE id = '".$userid."'") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($users) == 1)
	$user = mysql_fetch_assoc($users);

if (($user["privacy"] == "strong") && (get_user_class() < $prfmanage_class) && $CURUSER[id] != $user[id])//隐私等级高
	{$print.="你没有删除附件 ".$attachid." 的权限<br/><br/>\n";continue;}

if ($_POST['sure'])
	{
		$filepath = dirname(__FILE__)."/attachments/";
	
		if(file_exists($filepath.$arr["location"])){
			unlink($filepath.$arr["location"]);
		}
		if(file_exists($filepath.$arr["location"].".thumb.jpg")){
			unlink($filepath.$arr["location"].".thumb.jpg");
		}
		
		sql_query("DELETE FROM attachments WHERE id = '".$attachid."'") or sqlerr(__FILE__, __LINE__);
	}
else
$print .= "文件名：".$arr["filename"]."\n<input type=hidden name=deleteids[] value=\"".$arr["id"]."\" /><br/>\n".format_comment("[attach]".$arr["dlkey"]."[/attach]")."<br/><br/>\n";
}
if ($_POST['sure'])
{
	if (!empty($_POST["returnto"]))
		header("Location: $_POST[returnto]");
	else
		header("Location: index.php");
}

else
	stderr(
		"确认删除附件！","<form action=delattachment.php method=post>你确信要删除以下附件？&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type=hidden name=returnto value=".$_POST["returnto"]." /><br/><br/>\n".
		"<input type=submit name=sure value=\"删除\" />".
		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=button onclick=\"location.href='javascript:history.go(-1)'\" value=返回 /><br/><br/>\n".
		$print.
		"<input type=submit name=sure value=\"删除\" />".
		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=button onclick=\"location.href='javascript:history.go(-1)'\" value=返回 /><br/>\n".
		"</form>"
	,0);

}

else {
	if (!empty($_POST["returnto"]))
		header("Location: $_POST[returnto]");
	else
		header("Location: index.php");
}

?>


