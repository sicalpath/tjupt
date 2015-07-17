<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
require_once(get_langfile_path("",true));
loggedinorreturn();

function bark($msg) {
  global $lang_delete;
  stdhead();
  stdmsg($lang_delete['std_delete_failed'], $msg);
  stdfoot();
  exit;
}

if (!mkglobal("id"))
	bark($lang_delete['std_missing_form_date']);

$id = 0 + $id;
if (!$id)
	die();

$res = sql_query("SELECT name,owner,seeders,added,anonymous FROM torrents WHERE id = ".sqlesc($id));
$row = mysql_fetch_array($res);
$uploader = mysql_fetch_array(sql_query("SELECT username,class FROM users WHERE id = ".$row[owner]));
if (!$row)
	die();

if ($CURUSER["id"] != $row["owner"] && get_user_class() < $torrentmanage_class)
	bark($lang_delete['std_not_owner']);

$rt = 0 + $_POST["reasontype"];

if (!is_int($rt) || $rt < 1 || $rt > 6)
	bark($lang_delete['std_invalid_reason']."$rt.");

$r = $_POST["r"];
$reason = $_POST["reason"];

if ($rt == 1)
	$reasonstr = "断种";
elseif ($rt == 2)
	$reasonstr = "重复" . ($reason[0] ? (" - " . trim($reason[0])) : "");
elseif ($rt == 3)
	$reasonstr = "劣质" . ($reason[1] ? (" - " . trim($reason[1])) : "");
elseif ($rt == 4)
{
	if (!$reason[2])
		bark($lang_delete['std_describe_violated_rule']);
  $reasonstr = " 违规 - " . trim($reason[2]);
}
elseif ($rt == 5)
	{$reasonstr = "合集已出，删除单集和小合集，感谢您对北洋园PT的贡献";
		$time = $row['added'];
		$timestamp = strtotime($time);
		if (($uploader['class']==UC_UPLOADER)&&(date(n,$timestamp)==date(n)))
	sql_query("UPDATE uploaders SET deleted_torrents = deleted_torrents + 1 WHERE uid = ".$row["owner"] );}
else
{
	if (!$reason[3])
		bark($lang_delete['std_enter_reason']);
  $reasonstr = trim($reason[3]);
}

deletetorrent($id,$reasonstr);

if ($row['anonymous'] == 'yes' && $CURUSER["id"] == $row["owner"]) {
	write_log("匿名发布者删除了资源 $id ($row[name]) 。理由： $reasonstr",'normal');
} else {
	write_log(($CURUSER["id"] == $row["owner"]?"用户 ":"管理员 ")."$CURUSER[username] 删除了资源 $id ($row[name])。理由： $reasonstr",'normal');
}

//===remove karma
//KPS("-",$uploadtorrent_bonus,$row["owner"]);

//Send pm to torrent uploader
if ($CURUSER["id"] != $row["owner"]){
	$dt = sqlesc(date("Y-m-d H:i:s"));
	$subject = sqlesc($lang_delete_target[get_user_lang($row["owner"])]['msg_torrent_deleted']);
	$msg = sqlesc($lang_delete_target[get_user_lang($row["owner"])]['msg_the_torrent_you_uploaded'].$row['name'].$lang_delete_target[get_user_lang($row["owner"])]['msg_was_deleted_by']."[url=userdetails.php?id=".$CURUSER['id']."]".$CURUSER['username']."[/url]".$lang_delete_target[get_user_lang($row["owner"])]['msg_reason_is'].$reasonstr);
	sql_query("INSERT INTO messages (sender, receiver, subject, added, msg) VALUES(0, $row[owner], $subject, $dt, $msg)") or sqlerr(__FILE__, __LINE__);
}
stdhead($lang_delete['head_torrent_deleted']);

if (isset($_POST["returnto"]))
	$ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">".$lang_delete['text_go_back']."</a>";
else
	$ret = "<a href=\"index.php\">".$lang_delete['text_back_to_index']."</a>";

?>
<h1><?php echo $lang_delete['text_torrent_deleted'] ?></h1>
<p><?php echo  $ret ?></p>
<?php
stdfoot();
