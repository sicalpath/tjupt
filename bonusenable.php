<?php
require "include/bittorrent.php";
dbconn();
parked();
function enable_the_account($name,$points){
	$res=	sql_query("select * from users WHERE username = ".sqlesc($name)) or sqlerr(__FILE__, __LINE__);
	$row= mysql_fetch_array($res);
	if ($row[enabled]=='no'){
	sql_query("UPDATE users SET enabled = 'yes', seedbonus = seedbonus - $points WHERE username = ".sqlesc($name)) or sqlerr(__FILE__, __LINE__);
	write_log("用户 $name 使用 $points 个魔力值复活");
	if (get_single_value("users","class","WHERE username = ".sqlesc($name)) == UC_PEASANT){
				$length = 14*86400; // warn users until 14 days
				$until = sqlesc(date("Y-m-d H:i:s",(strtotime(date("Y-m-d H:i:s")) + $length)));
				sql_query("UPDATE users SET enabled='yes', leechwarn='yes', leechwarnuntil=$until WHERE username = ".sqlesc($name));
			}
			else{
				sql_query("UPDATE users SET enabled='yes', leechwarn='no' WHERE username = ".sqlesc($name)) or sqlerr(__FILE__, __LINE__);
			}
	stdmsg("成功！","账号复活成功。",0);
stdfoot();}
else{
		stdmsg("错误！","该账号没有被封禁。",0);
stdfoot();}
}
stdhead('魔力值解封');
	global $Cache;
if (! $invite_bonus = $Cache->get_value ( 'invite_bonus' )) {
	$totalalive = get_row_count ( "users", "WHERE status!='pending' AND class > 0 AND enabled='yes'" );
	$totalbonus = get_single_value ( "users WHERE enabled='yes'", "sum(seedbonus) " );
	$totalinvites = get_single_value ( "users WHERE enabled='yes'", "sum(invites)" );
	// $invite_bonus=$oneinvite_bonus*exp(($totalalive+$totalinvites/8-$maxusers)/800);
	$invite_bonus = 0.75 * $oneinvite_bonus / 100000000 * $totalbonus * exp ( $totalalive / 20000 ) * (log ( $totalinvites + 1 ) + log ( $maxusers / ($maxusers - $totalalive) )) / 25 - (rand ( 0, 1895 ) > time () % 1895 ? time () % 1895 : rand ( 0, 1895 ));
	$Cache->cache_value ( 'invite_bonus', $invite_bonus, 300 );
}
$enable_bonus =(int)(0.6*$invite_bonus);
if ($username=$_POST[account])
{
	if(!$password=$_POST[passwd])
	{stdmsg("出错了！","没有输入密码 <input type=button value=\"返回上一页\" onclick=\"location.href='javascript:history.go(-1)'\" />",0);
	stdfoot();}
	else{
	$res = sql_query("SELECT id, passhash, secret, seedbonus, enabled, status FROM users WHERE username = " . sqlesc($username));
	$row = mysql_fetch_array($res);
		if ($row["passhash"] != md5($row["secret"] . $password . $row["secret"]))
				{stdmsg("出错了！","密码不正确 <input type=button value=\"返回上一页\" onclick=\"location.href='javascript:history.go(-1)'\" />", 0);
		stdfoot();}
		elseif($row["seedbonus"]< $enable_bonus)
		{stdmsg("抱歉！","您的魔力值（ ".(int)$row["seedbonus"]."）不足以复活您的账号，因为目前复活价格是 $enable_bonus 。不过，您可以请求您的朋友用<b>魔力值使用</b>里的<b>复活</b>功能来复活您的账号。<input type=button value=\"返回上一页\" onclick=\"location.href='javascript:history.go(-1)'\" />", 0);
		stdfoot();}
		else
	enable_the_account($username,$enable_bonus);}}
	else{
	stdmsg("用魔力值复活自己的账号","<form action=\"?action=exchange\" method=\"post\"><br />请输入你的用户名<input type=text name=account /><br />请输入你的密码<input type=password name=passwd /><input type=submit value=\"确定\"> &nbsp;<input type=button value=\"返回\" onclick=\"location.href='javascript:history.go(-1)'\" /></form>",0);
stdfoot();}
	?>