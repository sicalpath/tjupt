<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR)
stderr("出错啦！", "没有权限进行本操作！");
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if ($_POST['doit'] == 'yes') {
	if ($_POST["seedbonus2"] == "")
	stderr("错误！", "丢失了重要数据");
	$seedbonus = sqlesc($_POST["seedbonus2"]);
	$msg = $_POST['seedreason'];
	if($seedbonus > 0){
		$msg = "管理组为您增加了".$seedbonus."个魔力值。原因是：".$msg;
	}elseif($seedbonus< 0){
		$msg = "管理组为您减少了".abs($seedbonus)."个魔力值。原因是：".$msg;
	}
	$subject="福利来啦";
	$msg = sqlesc($msg);
		sql_query("UPDATE users SET seedbonus = seedbonus + $seedbonus WHERE status='confirmed'");
		$query = sql_query("SELECT id FROM users WHERE status='confirmed'");
		$dt = sqlesc(date("Y-m-d H:i:s"));
		while($dat=mysql_fetch_assoc($query))
		{
			sql_query("INSERT INTO messages (sender, receiver, added,  subject, msg) VALUES (0, $dat[id], $dt, '" . $subject ."', " . $msg .")") or sqlerr(__FILE__,__LINE__);
		}
		stderr("成功", "成功给所有用户发放$seedbonus"."个魔力值");
		die;
	}
	
	if ($_POST['wage'] == 'yes') {
	if (get_user_class() < UC_ADMINISTRATOR)
	stderr("出错啦", "只有网站主管方有本权限");
	$point=$_POST['amount'];
	$class=$_POST['class'];
	if(!is_numeric($point))stderr("别玩了！", "填写的魔力值不是正常的数值！");
	if($point<=0)stderr("别玩了！", "工资怎么会是负数呢？");
	$dt = sqlesc(date("Y-m-d H:i:s"));
	if($class=="forummods")//论坛版主
	{
	$classname="论坛版主";
	$query1 = sql_query("SELECT userid FROM forummods GROUP by userid");
		while($dat=mysql_fetch_assoc($query1))
		{	sql_query("UPDATE users SET seedbonus = seedbonus + $point WHERE id=$dat[userid] AND class < 12");
			if(mysql_num_rows(sql_query("SELECT * FROM users WHERE id=$dat[userid] AND class < 12"))>0)sql_query("INSERT INTO messages (sender, receiver, added,  subject, msg) VALUES (0, $dat[userid], $dt, '福利来啦！', '管理组为所有"."$classname"."发放了$point"."个魔力值，请笑纳！')") or sqlerr(__FILE__,__LINE__);
		}
	}
	elseif($class=="designer")//美工
	{
	$classname="美工";
	$query3 = sql_query("SELECT * FROM users WHERE designer='yes'");
		while($dat=mysql_fetch_assoc($query3))
		{	sql_query("UPDATE users SET seedbonus = seedbonus + $point WHERE id=".$dat[id]);
			if(mysql_num_rows(sql_query("SELECT * FROM users WHERE id=".$dat[id]))>0)sql_query("INSERT INTO messages (sender, receiver, added,  subject, msg) VALUES (0, $dat[id], $dt, '福利来啦！', '管理组为所有"."$classname"."发放了$point"."个魔力值，请笑纳！')") or sqlerr(__FILE__,__LINE__);}}
	elseif($class=="support")//客服
	{
	$classname="客服";
	$query3 = sql_query("SELECT * FROM users WHERE support='yes'");
		while($dat=mysql_fetch_assoc($query3))
		{	sql_query("UPDATE users SET seedbonus = seedbonus + $point WHERE id=".$dat[id]);
			if(mysql_num_rows(sql_query("SELECT * FROM users WHERE id=".$dat[id]))>0)sql_query("INSERT INTO messages (sender, receiver, added,  subject, msg) VALUES (0, $dat[id], $dt, '福利来啦！', '管理组为所有"."$classname"."发放了$point"."个魔力值，请笑纳！')") or sqlerr(__FILE__,__LINE__);}}
	elseif($class=="all")
{	$classname="用户";
	$query2 = sql_query("SELECT * FROM users");
		while($dat=mysql_fetch_assoc($query2))
		{	sql_query("UPDATE users SET seedbonus = seedbonus + $point WHERE id=".$dat[id]);
			if(mysql_num_rows(sql_query("SELECT * FROM users WHERE id=".$dat[id]))>0)sql_query("INSERT INTO messages (sender, receiver, added,  subject, msg) VALUES (0, $dat[id], $dt, '福利来啦！', '管理组为所有"."$classname"."发放了$point"."个魔力值，请笑纳！')") or sqlerr(__FILE__,__LINE__);}}
	
	else{
	$classname=($class==10?"贵宾":($class==11?"养老族":($class==12?"上传员":($class==13?"类管理员":($class==14?"管理员":($class==15?"维护开发员":"主管"))))));
		sql_query("UPDATE users SET seedbonus = seedbonus + $point WHERE class = $class");
		$query = sql_query("SELECT id FROM users WHERE class  = $class");

		while($dat=mysql_fetch_assoc($query))
		{	sql_query("INSERT INTO messages (sender, receiver, added,  subject, msg) VALUES (0, $dat[id], $dt, '福利来啦！', '管理组为所有"."$classname"."发放了$point"."个魔力值，请笑纳！')") or sqlerr(__FILE__,__LINE__);
		}
	}
//		write_log("25.0 bonus point is sent to everyone. -" . $CURUSER["username"] ,'normal');
		stderr("成功", "成功给所有$classname"."发放$point"."个魔力值");
		die;
	}

	if ($_POST["username"] == "" ||$_POST["seedbonus"] == "")
	stderr("错误！", "丢失了重要数据");
	$username = sqlesc($_POST["username"]);
	$seedbonus = sqlesc($_POST["seedbonus"]);
	$reason = sqlesc($_POST["reason"]);
/*	if ( $seedbonus == 0 )
	{
		stderr("错误！", "魔力值未变动");
		header("Location: " . get_protocol_prefix() . "$BASEURL/userdetails.php?id=".htmlspecialchars($arr[0]));
		die;
	}
*/	$arr= mysql_fetch_assoc(sql_query("SELECT id, bonuscomment FROM users WHERE username=$username"));
	$bonuscomment=$arr[bonuscomment];
	$bonuscomment = date("Y-m-d") . " " .($seedbonus>=0?$seedbonus:-$seedbonus). " Points ".($seedbonus>=0?"added":"subtracted")." by ".$CURUSER["username"].".\n" .htmlspecialchars($bonuscomment);
	sql_query("UPDATE users SET seedbonus=seedbonus + $seedbonus, bonuscomment = ".sqlesc($bonuscomment)." WHERE username=$username") or sqlerr(__FILE__, __LINE__);
	$dt = sqlesc(date("Y-m-d H:i:s"));
	print_r( $arr );
	if ( $seedbonus > 0 )
		$bonuschangedmsg = '站点为您增加了[color=red][b]'.$seedbonus.'[/b][/color]个魔力值，感谢您对本站的贡献！';
	elseif ( $seedbonus < 0 )
		$bonuschangedmsg = '您的魔力值被减少了[color=red][b]'.abs($seedbonus).'[/b][/color]个，请注意您在本站的言行！';
	else
		$bonuschangedmsg = '魔力值好像没有变化，今天不会是4月1日吧？！';
	if ( $reason )
		$bonuschangedmsg .= '原因是：'.chr(10).chr(10).'[quote]'.$reason.'[/quote]';
	$bonuschangedmsg .= chr(10).chr(10).chr(10).'操作人：'.$CURUSER['username'];
		print($bonuschangedmsg);

	sql_query("INSERT INTO messages (sender, receiver, added,  subject, msg) VALUES (0, $arr[id], $dt, '魔力值变动', ".sqlesc($bonuschangedmsg).")") or sqlerr(__FILE__,__LINE__);
	$res = sql_query("SELECT id FROM users WHERE username=$username");
	$arr = mysql_fetch_row($res);
	if (!$arr)
	stderr("Error", "Unable to update account.");
	header("Location: " . get_protocol_prefix() . "$BASEURL/userdetails.php?id=".htmlspecialchars($arr[0]));
	die;
}
stdhead("改变用户的魔力值");
?>
<h1>改变用户的魔力值</h1>
<?php
begin_main_frame("",false, 50);
begin_main_frame("调整某个特定用户的魔力值",false,50);
echo "<form method=\"post\" action=\"amountbonus.php\">";
print("<table width=100% border=1 cellspacing=0 cellpadding=4>\n");
?>
<tr><td class="rowhead" width="100px">用户名：</td><td class="rowfollow"><input type="text" name="username" size="20"/></td></tr>
<tr><td class="rowhead">魔力值调整量：</td><td class="rowfollow"><input type="text" name="seedbonus" size="5"/></td></tr>
<tr><td class="rowhead">原因：</td><td class="rowfollow"><textarea rows="10" name="reason" cols="60"></textarea></td></tr>
<tr><td colspan="2" class="toolbox" align="center"><input type="submit" value="就这样！" class="btn"/></td></tr>
<?php end_table();?>
</form>
<?php end_main_frame();?>
<?php begin_main_frame("给所有用户发放魔力值",false,30);?>
<form action="amountbonus.php" method="post">
<table width=100% border=1 cellspacing=0 cellpadding=5>
<tr><td class="rowhead">魔力值改变量</td><td><input type="text" name="seedbonus2" size="28"></td></tr>
<tr><td class="rowhead">原因</td><td class="rowfollow"><textarea name="seedreason" wrap="virtual" rows="3" cols="28"></textarea></td></tr>
<tr><td class="toolbox" colspan="2" align="center"><input type = "hidden" name = "doit" value = "yes" />
<input type="submit" class="btn" value="确定" />
</td></tr>
<?php end_table();?>
</form>
<?php
//UC_STAFFLEADER
if (get_user_class() >= 14)
{
 
begin_main_frame("发工资",false,50);
echo "
<form action=\"amountbonus.php\" method=\"post\" >
<table width=100% border=1 cellspacing=0 cellpadding=5>
<tr><td class=\"rowfollow\" width=\"100%\">
给<select name=\"class\"><option value=\"all\">所有用户<option value=10>贵宾</option><option value=11>养老族</option><option value=12>上传员</option><option value=13 selected=\"selected\">类管理员</option><option value=14>管理员</option><option value=15>维护开发员</option><option value=16>主管</option><option value=\"forummods\">论坛版主</option><option value=\"designer\">美工</option><option value=\"support\">客服</option></select>等级用户增加<input name = \"amount\" value=\"5000\" size=\"8\"/>个魔力值
<br /><br /></td></tr>
<tr><td class=\"toolbox\" align=\"center\"><input type = \"hidden\" name = \"wage\" value = \"yes\" />
<input type=\"submit\" class=\"btn\" value=\"确定\" />
</td></tr>";
end_table();
echo "</form>";
}
?>


<?php
end_main_frame();
end_main_frame();
end_main_frame();
stdfoot();
