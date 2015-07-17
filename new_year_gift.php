<?php
require_once('include/bittorrent.php');
dbconn();
loggedinorreturn();
$query_pic=sql_query("SELECT * FROM gift WHERE userid ='".$CURUSER["id"]."'") or sqlerr(__FILE__, __LINE__);
if($_GET["pic"])
{
QRcode::png(md5(mysql_num_rows($query_pic).$CURUSER["username"].$CURUSER["id"]),false,'L',5);
}
elseif(isset($_POST["newusername"]))
{
if(!mysql_num_rows(sql_query("SELECT * FROM gift WHERE id=2012 AND userid='".$CURUSER["id"]."'")))
{
stderr("破解不成功","你被记录在案！！！");
write_log("用户" . $CURUSER["username"] . "(IP: " . $CURUSER["ip"] . " )试图破解礼物系统（以幸运儿的资格修改用户名）",'mod');
}
if($_POST["newusername"]=="")stderr("修改不成功","用户名为空！！！");
if(mysql_num_rows(sql_query("SELECT * FROM users WHERE username='".mysql_real_escape_string($_POST["newusername"])."'")))
stderr("修改不成功","用户名已存在！！！");
sql_query("UPDATE users SET username='".mysql_real_escape_string($_POST["newusername"])."' WHERE id = ".$CURUSER["id"]);
write_log("幸运用户" . $CURUSER["username"] . "(ID: " . $CURUSER["id"] . " )成功将用户名修改为".$_POST["newusername"],'normal');
stderr("修改成功","请以新用户名登录系统！！！");
}
else
{
if(date("Y-m-d H:i:s")<"2012-01-23 00:00:00" )
stderr("怎么那么早就来拜年了？","北洋媛还没有来得及准备好利市呢！大年初一再来逗利市吧～～");
if(date("Y-m-d H:i:s")>="2012-02-07 00:00:00")
stderr("你来得也太晚了点吧！","元宵节都过了，北洋媛的利市也早就派完了！明年早些过来给北洋媛拜年吧～～");
if($_POST["answer"])
{
$query=sql_query("SELECT * FROM gift WHERE userid ='".$CURUSER["id"]."'") or sqlerr(__FILE__, __LINE__);

	if($_POST["answer"]==md5(md5(mysql_num_rows($query_pic).$CURUSER["username"].$CURUSER["id"]).$CURUSER["username"].md5(mysql_num_rows($query_pic).$CURUSER["username"].$CURUSER["id"])))
	{
		if(mysql_num_rows($query) >=10 )stderr("你是怎么来到这里的？","今年派发给你的十封利市你都已经领过了！明年再来给北洋媛拜年吧～～");
		else
		{
		sql_query("INSERT INTO gift (userid, time) VALUES ( $CURUSER[id], ".sqlesc(date("Y-m-d H:i:s")).")") or sqlerr(__FILE__, __LINE__);
		$insertid = mysql_insert_id();
		$point=rand(0,100);
		$amount=($insertid % 100) + $point;
		$msg="恭喜恭喜！您领到了北洋媛送出的第 $insertid 封新年利市， ".$amount." 个魔力值( ".($insertid%100)." 个抢顺序魔力值 + ".$point." 个随机魔力值)已经加入到您的帐户中，请注意查收！";
		$newbonus=$CURUSER['seedbonus']+$point+$insertid % 100;
		
		if($insertid%100==0)
		{
		$amount=2012 + $point;
		$msg="恭喜恭喜！您领到了北洋媛送出的第 $insertid 封新年利市， ".$amount." 个魔力值( 2012 个抢顺序魔力值+ ".$point." 个随机魔力值)已经加入到您的帐户中，请注意查收！";
		$newbonus=$CURUSER['seedbonus']+$point+2012;
		}
		if($insertid < 10)
		{
		$amount=500 + $point;
		$msg="恭喜恭喜！您领到了北洋媛送出的第 $insertid 封新年利市， ".$amount." 个魔力值( 500 个抢顺序魔力值+ ".$point." 个随机魔力值)已经加入到您的帐户中，请注意查收！";
		$newbonus=$CURUSER['seedbonus']+$point+2012;
		}
		
		if($insertid == 2012 )
		{
		$amount=20120 + $point;
		$msg="恭喜恭喜！您领到了北洋媛送出的第 2012 封新年利市， ".$amount." 个魔力值( 20120 个抢顺序魔力值+ ".$point." 个随机魔力值)已经加入到您的帐户中，请注意查收！<br/>恭喜您获得了北洋媛送出的新年大礼，特别礼物是修改用户名一次（可以是中文名喔！）<br/>请注意：修改机会只有一次，请事先查询好站内是否存在相同用户名。关闭本页面视为放弃该次修改用户名的机会！<br/><br/>。请在下面输入您的新用户名：<form action=".str_replace("/","",$_SERVER['PHP_SELF'])." method= post ><input typt=text name=newusername><input type=submit name=submit value=\"确定\" ></form>";
		$newbonus=$CURUSER['seedbonus']+$point+20120;
		}
		sql_query("UPDATE users SET seedbonus ='".$newbonus."' WHERE id = '".$CURUSER["id"]."'") or sqlerr(__FILE__, __LINE__);
		stderr("恭喜您收到北洋媛派出的新年利市！",$msg,0);
		}
	
	}
	
	$notice="<h1>答案不正确，请再试一次吧！</h1><br/>\n";
}

if (!$newyeargift = $Cache->get_value('newyeargift'))
{
	$newyeargift=mysql_num_rows(sql_query("SELECT * FROM gift"));
	$Cache->cache_value('newyeargift', $newyeargift, 300);
}
stdhead("新年利市");
print($notice);

?>
<table width="60%" border="1" cellspacing="0" cellpadding="10">
<tr><td class="text"  align="left">
<font color="#FF0000" size="+2"><b>利市派发规则：</b></font><br/>
&nbsp;&nbsp;&nbsp;&nbsp;1.利市派发时段为农历壬辰年正月初一至正月十五。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;2.每位用户最多可以领取到10封新年利市。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;3.序号为第1-10、整百、2012的利市为大面额利市，其余利市价值为序号的末两位+100以内的随机面额。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;4.只有正确破解北洋媛设置的密码方可领取到利市封。
</td></tr>
<tr><td class="text"  align="left">
<font size="-1"><b>截至当前，北洋媛共派发出利市 <? echo $newyeargift;?> 封。(统计数据将保留五分钟)<br />
您已领取到利市 <? echo mysql_num_rows($query_pic); ?> 封。</b></font><br/>

</td></tr>
<? echo mysql_num_rows($query_pic)>=10?"<!--":""; ?> 

<tr><td class="text"  align="center">
<img src="<?php echo str_replace("/","",$_SERVER['PHP_SELF']); ?>?pic=1" /></td></tr>
<tr><td class="text"  align="center">
<form action="<?php echo str_replace("/","",$_SERVER['PHP_SELF']); ?>" method="post" />答案：<input type="text" name="answer"><input type="submit" name="submit" value="确定" /></form></td></tr>

<? echo mysql_num_rows($query_pic)>=10?"-->":""; ?> 

<tr><td class="text"  align="left">
<font color="#FF0000" size="-1"><b>密码破解攻略：</b></font><br/>
&nbsp;&nbsp;&nbsp;&nbsp;1.老规矩，先把二维码解出来。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;2.设解密得到的结果为A，写出字符串“A用户名A”(注意用户名的大小写)。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;3.将第2步得到的长字符串使用32位MD5函数加密，结果即所求的答案。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;4.最后的答案全部为数字或0-f的小写字母。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;5.<a href="md5.php">提供一个安全运算MD5的网页</a>。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;6.这么做目的不是特地折腾大家，而是想让大家了解北洋园PT站的密码储存方式。<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;流程中的用户名用各位的密码代替，前后各拼上一串随机生成的长字符串，MD5<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;求值之后储存在数据库中。如此一来，即使不幸被黑客获取到整个数据库，破解<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;每个用户的密码均会是一项很大的工程。并且单个用户的密码被破解不会影响到<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;其他用户的密码安全。<br/>
</td></tr>


</table>

<?php
stdfoot();
}

