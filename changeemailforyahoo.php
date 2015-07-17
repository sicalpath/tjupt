<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path("takeinvite.php","",""));
parked();
stdhead("修改邮箱");
function bark($msg) {
	stdmsg('失败！', $msg);
  stdfoot();
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$username=$_POST['account'];
	$email=$_POST['email'];
	$password=$_POST['password'];
	$res = sql_query("SELECT id, email, passhash, secret FROM users WHERE username = " . sqlesc($username));
	$row = mysql_fetch_array($res);
	if (!$username)
	  bark('请输入用户名!');
  if ($row["passhash"] != md5($row["secret"] . $password . $row["secret"]))
		bark('密码错误！');
	if (!(strstr($row['email'],'@yahoo.cn')||strstr($row['email'],'@yahoo.com.cn')))
		bark('您的邮箱不是中国雅虎邮箱，暂时不支持修改邮箱服务');
	if (!$email)
    bark($lang_takeinvite['std_must_enter_email']);
	if (!check_email($email))
	bark($lang_takeinvite['std_invalid_email_address']);
	if(EmailBanned($email))
    bark($lang_takeinvite['std_email_address_banned']);
	if(!EmailAllowed($email))
    bark($lang_takeinvite['std_wrong_email_address_domains'].allowedemails());	
	sql_query("UPDATE users SET email=".sqlesc($email)." WHERE id=$row[id]")or sqlerr(__FILE__, __LINE__);
	stdmsg('邮箱修改成功！', '请到<a class=faqlink href=usercp.php>个人页面</a>查看。');
  stdfoot();
  exit;
}
?>
<h1>修改邮箱</h1>
<form method=post action=changeemailforyahoo.php>
<table border=1 cellspacing=0 cellpadding=5>
<tr><td class=rowhead>请输入用户名</td><td><input type=text name=account size=40></td></tr>
<tr><td class=rowhead>请输入新邮箱</td><td><input type=text name=email size=40>注意：修改后没有验证环节，因此请谨慎修改，避免填错。</td></tr>
<tr><td class=rowhead>请输入你的密码</td><td><input type=password name=password size=40>如果在此页面发现任何bug请反馈至管理组，谢谢</td></tr>
<tr><td colspan=2 align=center><input type=submit value="确定" class=btn></td></tr>
</table>
<?php

  stdfoot();
?>