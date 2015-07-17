<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if (get_user_class() < UC_SYSOP)
stderr("Error", "Permission denied.");
$class = 0 + $_POST["class"];
	if ($class)
		int_check($class,true);
$or = $_POST["or"];

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
$res = sql_query("SELECT id, username, email FROM users WHERE email LIKE \"%@yahoo.cn\" OR email LIKE \"%@yahoo.com.cn\"") or sqlerr(__FILE__, __LINE__); 
/*$res = sql_query("SELECT id, username, email FROM users WHERE email ='scaryken@163.com'") or sqlerr(__FILE__, __LINE__);*/
$subject = substr(htmlspecialchars(trim($_POST["subject"])), 0, 80);
if ($subject == "") $subject = "(no subject)";
$subject = "北洋园PT关于中国雅虎邮箱修改邮箱的通知";

$message1 =<<<EOD
<br/>
<br/>
中国雅虎邮箱于2013年4月18日启动整体迁移，详情点击<a target="_blank" href="http://migration.cn.yahoo.com/explain.php">这里</a>。
<br/>
为了保障本站广大中国雅虎邮箱用户的权益，本站提供中国雅虎邮箱用户修改注册邮箱的服务。点击<a target="_blank" href="http://pt.tju.edu.cn/changeemailforyahoo.php">这里</a>就可以修改邮箱啦，而且这个页面支持校外IPV4访问哦！
<br/>再贴一遍链接以防邮箱吞链接，上面链接失效的话请贴到地址栏~<br/>
http://pt.tju.edu.cn/changeemailforyahoo.php
<br/>有什么问题请回复邮件联系管理组
<br/>
<br/>
爱你的北洋媛
<br/>
EOD;
if ($message1 == "") stderr("Error", "Empty message!");

while($arr=mysql_fetch_array($res)){

$to = $arr["email"];


$message = "Message received from ".$SITENAME." on " . date("Y-m-d H:i:s") . ".\n" .
"---------------------------------------------------------------------\n\n" .
$message1 . "\n\n" .
"---------------------------------------------------------------------\n$SITENAME\n";

$success = sent_mail($to,$SITENAME,$SITEEMAIL,$subject,$message,"Mass Mail",false);	
}


if ($success)
stderr("Success", "Messages sent.");
else
stderr("Error", "Try again.");

}

stdhead("Mass E-mail Gateway");
?>

<p><table border=0 class=main cellspacing=0 cellpadding=0><tr>
<td class=embedded style='padding-left: 10px'><font size=3><b>发送邮件给 @yahoo.cn and @yahoo.com.cn</b></font></td>
</tr></table></p>
<table border=1 cellspacing=0 cellpadding=5>
<form method=post action=massmail2.php>

<?php
/*if (get_user_class() == UC_MODERATOR && $CURUSER["class"] > UC_POWER_USER)
printf("<input type=hidden name=class value=$CURUSER[class]\n");
else
{
print("<tr><td class=rowhead>Classe</td><td colspan=2 align=left><select name=or><option value='<'><<option value='>'>><option value='='>=<option value='<='><=<option value='>='>>=</select><select name=class>\n");
if (get_user_class() == UC_MODERATOR)
$maxclass = UC_POWER_USER;
else
$maxclass = get_user_class() - 1;
for ($i = 0; $i <= $maxclass; ++$i)
print("<option value=$i" . ($CURUSER["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i,false,true,true) . "\n");
print("</select></td></tr>\n");
}*/
?>


<tr><td class=rowhead>Subject</td><td><input type=text name=subject size=80></td></tr>
<tr><td class=rowhead>Body</td><td><textarea name=message cols=80 rows=20></textarea></td></tr>
<tr><td colspan=2 align=center><input type=submit value="Send" class=btn></td></tr>
</form>
</table>

<?php
stdfoot();
