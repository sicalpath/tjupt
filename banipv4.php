<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR)
stderr("Sorry", "Access denied.");
$type_ = array('ip'=>"封禁单个ip",'building'=>"封禁全楼",'school'=>"封禁全校");

$remove = (int)$_GET['remove'];
if (is_valid_id($remove))
{
  sql_query("DELETE FROM bans WHERE id=".mysql_real_escape_string($remove)) or sqlerr();
  write_log("Ban ".htmlspecialchars($remove)." was removed by $CURUSER[id] ($CURUSER[username])",'mod');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && get_user_class() >= UC_ADMINISTRATOR)
{
	$first = trim($_POST["first"]);
	$last = trim($_POST["last"]);
	$comment = trim($_POST["comment"]);
	$days=0;
	if ($_POST["fast"]=="never")$until=sqlesc('0000-00-00 00:00:00');
	else{
	if ($_POST["fast"]) $days=$_POST["fast"];
	if ($_POST["week"]) $days+=7*$_POST["week"];
	if ($_POST["days"]) $days+=$_POST["days"];
	if($days)$until=sqlesc(date("Y-m-d H:i:s",time()+86400*$days));
	}
	if (!$first || !$last || !$comment)
		stderr("Error", "数据丢失！");
	$firstlong = ip2long($first);
	$lastlong = ip2long($last);
	if (!$firstlong||!$lastlong)
		stderr("Error", "IP地址格式不正确！");
	if ($firstlong>$lastlong)
		stderr("Error", "起始IP不应该大于终止IP！");
	$comment = sqlesc($comment);
	$added = sqlesc(date("Y-m-d H:i:s"));
	sql_query("INSERT INTO bans (added, addedby, first, last, comment, until) VALUES($added, ".mysql_real_escape_string($CURUSER[id]).", $firstlong, $lastlong, $comment, $until )") or sqlerr(__FILE__, __LINE__);
	header("Location: $_SERVER[REQUEST_URI]");
	die;
}

//ob_start("ob_gzhandler");

$res = sql_query("SELECT * FROM bans ORDER BY added DESC") or sqlerr();

stdhead("IP地址封禁");

print("<h1>已封禁列表</h1>\n");

if (mysql_num_rows($res) == 0)
  print("<p align=center><b>列表为空！</b></p>\n");
else
{
  print("<table border=1 cellspacing=0 cellpadding=5>\n");
  print("<tr><td class=colhead>添加时间</td><td class=colhead align=left>起始IP</td><td class=colhead align=left>终止IP</td><td class=colhead align=left>预计解封时间</td>".
    "<td class=colhead align=left>操作人</td><td class=colhead align=left>备注</td><td class=colhead>移除</td></tr>\n");

  while ($arr = mysql_fetch_assoc($res))
  {
 	  print("<tr><td>".gettime($arr[added])."</td><td align=left>".long2ip($arr[first])."</td><td align=left>".long2ip($arr[last])."</td><td align=left>".($arr[until]=='0000-00-00 00:00:00'?"手动解除封禁":gettime($arr[until]))."</td><td align=left>". get_username($arr['addedby']) .
 	    "</td><td align=left>$arr[comment]</td><td><a href=banipv4.php?remove=$arr[id]>移除</a></td></tr>\n");
  }
  print("</table>\n");
}

if (get_user_class() >= UC_ADMINISTRATOR)
{
	print("<h1>添加新条目</h1>\n");
	print("<table border=1 cellspacing=0 cellpadding=5>\n");
	print("<form method=post action=banipv4.php>\n");
	print("<tr><td class=rowhead>起始IP：</td><td><input type=text name=first size=40></td></tr>\n");
	print("<tr><td class=rowhead>终止IP：</td><td><input type=text name=last size=40></td></tr>\n");
	print("<tr><td class=rowhead>封禁时间</td><td><select name=\"fast\"> <option value=\"never\" selected=\"selected\">不限时</option><option value=\"1\" > 1 天</option><option value=\"7\" > 1 周</option><option value=\"14\"> 2 周</option><option value=\"28\"> 4 周</option><option value=\"56\"> 8 周</option></select>+<input type=text name=week size=2>周+<input type=text name=days size=2>日</td></tr>\n");
	print("<tr><td class=rowhead>备注信息</td><td><input type=text name=comment size=40></td></tr>\n");
	print("<tr><td colspan=2 align=center><input type=submit value='确认' class=btn></td></tr>\n");
	print("</form>\n</table>\n");
}

stdfoot();

?>
