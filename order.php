<?php
require_once ('include/bittorrent.php');
dbconn ();
loggedinorreturn ();
parked ();
function ordmenu($selected = "list") {
		global $CURUSER;
		begin_main_frame ();
		
		print ("<div id=\"lognav\"><ul id=\"logmenu\" class=\"menu\">") ;
		
		print ("<li" . ($selected == "list" ? " class=selected" : "") . "><a href=\"?action=list\">我的订单</a></li>") ;
		
		print ("<li" . ($selected == "new" ? " class=selected" : "") . "><a href=\"?action=new\">下 单</a></li>") ;
		
		print ("<li" . ($selected == "note" ? " class=selected" : "") . "><a href=\"?action=note\">订购说明</a></li>") ;

		if (get_user_class()<13&&$CURUSER[id]!=24298)
		print ("<li" . ($selected == "manage" ? " class=selected" : "") . "><a href=\"sendmessage.php?receiver=24298\">联系客服</a></li>") ;
		
		else
		print ("<li" . ($selected == "manage" ? " class=selected" : "") . "><a href=\"?action=manage\">订单管理</a></li>") ;

		print ("</ul></div>") ;
		
		end_main_frame ();
	}
$action = isset ( $_POST ['action'] ) ? htmlspecialchars ( $_POST ['action'] ) : (isset ( $_GET ['action'] ) ? htmlspecialchars ( $_GET ['action'] ) : '');

$allowed_actions = array (
		"list",
		"new",
		"delete",
		"sent",
		"manage",
		"note"

);
if (! $action)
	
	$action = 'new';
if (! in_array ( $action, $allowed_actions ))
	
	$action = 'new';

	switch ($action) {
	case "list" :
	{
	$sql = "SELECT * FROM orders WHERE uid = $CURUSER[id]";
	$res = sql_query ( $sql );
	$rownumber = mysql_num_rows( $res );
	stdhead("我的订单");
	ordmenu('list');
	if ($rownumber == 0)
	stderr ( "没有订单", "<a href=order.php?action=new>点击这里下单</a>", 0, 0, 0, 0 );
	else{
	?>
<h1>我的订单</h1>
<table class="main" border="1" cellspacing="0" cellpadding="5"><tbody><tr>
<form method=post action=order.php?action=delete>
<td class="colhead">订单号</td>
<td class="colhead">商品</td>
<td class="colhead"> 数量 </td>
<td class="colhead"> 姓名 </td>
<td class="colhead"> 用户名 </td>
<td class="colhead"> 手机号码</td>
<td class="colhead"> 批次</td>
<td class="colhead"> 状态</td>
<td class="colhead" align="center"> 操作 </td>
</tr>
<?php
	while ( $row = mysql_fetch_assoc ( $res ) ) 
	{
	$id=$row[id];
	$uid=$row[uid];
	$name=$row[name];
	$contact=$row[contact];
	$group=$row[groups];
	$type=$row[type];
	$shangpin='款式'.$type.'';
	$num=$row[num];
	$status=$row[status];
	if ($status=='sending'){
	$option="<a class= faqlink href=order.php?action=delete&id=$id>删除</a>";
	$state='待发货';}
	else 	{$option="-";
	$state='已发货';}
print("<tr>
	<td class=\"rowfollow\">$id</td>
	<td class=\"rowfollow\">$shangpin</td>
	<td class=\"rowfollow\">$num</td>
	<td class=\"rowfollow\">$name</td>
	<td class=\"rowfollow\">".get_username($uid)."</td>
	<td class=\"rowfollow\">$contact</td>
	<td class=\"rowfollow\">$group</td>
	<td class=\"rowfollow\">$state</td>
	<td class=\"rowfollow\">$option</td>
	</tr>");
}print("</tbody></table>");
}
break;
}



	case "new" :
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if (!$_POST[type]||!$_POST[contact]||!$_POST[num])
		stderr("错误", "必须填写款式、数量和联系方式。");
	$typeid=$_POST[type];
	$contact=$_POST[contact];
		if(!preg_match("/^1\d{10}/",$contact))
		stderr("错误", "请填写正确的手机号码！");
	$num=$_POST[num];
	if(!preg_match("/^([1-9][0-9]*)/",$num))
		stderr("错误", "数量有误！");
	$name=$_POST[name];
	$uid=$CURUSER[id];
	sql_query ( "INSERT INTO orders (uid, type, num, name, contact) VALUES($uid, $typeid ,$num,'".$name."', '".$contact."')" ) or sqlerr ( __FILE__, __LINE__ );
	stdhead("下单成功");
		print("<h1>成功</h1></br>"); 
		print("点击<a class=faqlink href=order.php?action=list>这里</a>查看订单，点击<a class=faqlink href=order.php?action=new>这里</a>继续下单！");
		stdfoot();
		die();

	}


stdhead("北洋媛徽章预定");
	ordmenu('new');

?>
<h1>北洋媛徽章预定</h1>
<form method="post" action=order.php?action=new enctype="multipart/form-data">
<table border=1 cellspacing=0 cellpadding=5>
<tr><td class="rowhead">徽章款式</td>
<td class="rowfollow" align="left"><img src="/banners/type1.png" width="300" height="300" /></br>款式一</td><td class="rowfollow" align="left"><img src="/banners/type2.png" width="300"/></br>款式二</td><td class="rowfollow" align="left"><img src="/banners/type3.png" width="300"/></br>款式三</td></tr>
<tr><td class="rowhead">你的选择</td>
	<td class="rowfollow" align="left"><label><input id="1" type="radio" value="1" name="type">款式一</label><label><input id="2" type="radio" value="2" name="type">款式二</label><label><input id="3" type="radio" value="3" name="type">款式三</label></td><td class="rowfollow" align="left">每次下单只能选择一个款式，但如果两款都要请分两次下单。</td><td></td></tr>
<tr><td class="rowhead">数量</td>
<td class="rowfollow" align="left"><input type="text" id="num" value="1" name="num" autocomplete="off" style="width: 200px; border: 1px solid gray"></td><td class="rowfollow" align="left">必填。每个徽章价格人民币2.00元。</td><td></td></tr>
<tr><td class="rowhead">真实姓名</td>
<td class="rowfollow" align="left"><input type="text" id="name" name="name" autocomplete="off" style="width: 200px; border: 1px solid gray"></td><td class="rowfollow" align="left"></td><td></td></tr>
<tr><td class="rowhead">手机号</td>
<td class="rowfollow" align="left"><input type="text" id="contact" name="contact" autocomplete="off" style="width: 200px; border: 1px solid gray"></td><td class="rowfollow" align="left">必填，方便我们的工作人员联系您</td><td></td></tr>
<tr><td class="rowhead"></td><td class="rowfollow"><input type="submit" value="提交"><input type="reset" value="重置"></td><td></td><td></td></tr>
</table>
</form>
<?php
	break;
	}
	case "delete" :
	{
	if ($_SERVER["REQUEST_METHOD"] == "GET")
	{
	if (!$_GET[id])
	stderr("错误", "请返回！");
	$id=$_GET[id];
	$sql = "SELECT * FROM orders WHERE id =$id";
	$res = sql_query ( $sql );
	$row=mysql_fetch_array($res);
	if (!$row)
	stderr("错误", "请返回！");
	if (($row[uid]!=$CURUSER[id])&&(get_user_class()<14)&&$CURUSER[id]!=24298)
	stderr("错误", "请返回！");
	else
	sql_query("delete from orders where id=$id");
	stdhead("取消订单成功");
		print("<h1>取消订单成功</h1></br>"); 
		print("点击<a class=faqlink href=order.php?action=list>这里</a>查看订单，点击<a class=faqlink href=order.php?action=new>这里</a>继续下单！");
		stdfoot();
		}
	else
	stderr("错误", "请返回！");

	break;
	}
	case "sent" :
	{
	if ($_SERVER["REQUEST_METHOD"] == "GET")
	{
	if (!$_GET[id])
	stderr("错误", "请返回！");
	$id=$_GET[id];
	$sql = "SELECT * FROM orders WHERE id =$id";
	$res = sql_query ( $sql );
	$row=mysql_fetch_array($res);
	if (!$row)
	stderr("错误", "请返回！");
	if (($row[uid]!=$CURUSER[id])&&(get_user_class()<14)&&$CURUSER[id]!=24298)
	stderr("错误", "请返回！");
	else
	sql_query("update orders set status ='sent' where id=$id");
	stdhead("确认发货成功");
		print("<h1>确认发货成功</h1></br>"); 
		print("点击<a class=faqlink href=order.php?action=manage>这里</a>查看订单。");
		stdfoot();
		}
	else
	stderr("错误", "请返回！");

	break;
	}

	case "manage" :
	{
	if (get_user_class()<13&&$CURUSER[id]!=24298)
	stderr ( "错误", "您没有该权限");
	stdhead("订单管理");
	ordmenu('manage');
	$grouplink=
	$group = isset ( $_POST ['group'] ) ? htmlspecialchars ( $_POST ['group'] ) : (isset ( $_GET ['group'] ) ? htmlspecialchars ( $_GET ['group'] ) : '');
	$allowed_group=array(1,2,3);
	if (!in_array($group,$allowed_group))
	$group=3;
	$grouplink="</br><a class=faqlink href=?action=manage&group=2>第一批</a> <a class=faqlink href=?action=manage&group=2>第二批</a> 全部";
	switch ($group){
	case 1:{
	$grouplink="</br>第一批 <a class=faqlink href=?action=manage&group=2>第二批</a> <a class=faqlink href=?action=manage&group=3>全部</a>";
	$sql = "SELECT * FROM orders where groups = 1 order by uid";
	$res = sql_query ( $sql );
	$rownumber = mysql_num_rows( $res );
	$sql1 = "SELECT sum(num) as sum1 FROM orders where type=1 and groups = 1";
	$res1 = sql_query ( $sql1 );
	$row1 = mysql_fetch_array( $res1 );
	$sql2 = "SELECT sum(num) as sum2 FROM orders where type=2 and groups = 1";
	$res2 = sql_query ( $sql2 );
	$row2 = mysql_fetch_array( $res2 );
	$sql3 = "SELECT sum(num) as sum3 FROM orders where type=3 and groups = 1";
	$res3 = sql_query ( $sql3 );
	$row3 = mysql_fetch_array( $res3 );	
	break;}
	case 2:{
	$grouplink="</br><a class=faqlink href=?action=manage&group=1>第一批</a> 第二批 <a class=faqlink href=?action=manage&group=3>全部</a>";
	$sql = "SELECT * FROM orders where groups = 2 order by uid";
	$res = sql_query ( $sql );
	$rownumber = mysql_num_rows( $res );
	$sql1 = "SELECT sum(num) as sum1 FROM orders where type=1 and groups = 2";
	$res1 = sql_query ( $sql1 );
	$row1 = mysql_fetch_array( $res1 );
	$sql2 = "SELECT sum(num) as sum2 FROM orders where type=2 and groups = 2";
	$res2 = sql_query ( $sql2 );
	$row2 = mysql_fetch_array( $res2 );
	$sql3 = "SELECT sum(num) as sum3 FROM orders where type=3 and groups = 2";
	$res3 = sql_query ( $sql3 );
	$row3 = mysql_fetch_array( $res3 );		
	break;}

	case 3:{
	$grouplink="</br><a class=faqlink href=?action=manage&group=1>第一批</a> <a class=faqlink href=?action=manage&group=2>第二批</a> 全部";
	$sql = "SELECT * FROM orders order by uid";
	$res = sql_query ( $sql );
	$rownumber = mysql_num_rows( $res );
	$sql1 = "SELECT sum(num) as sum1 FROM orders where type=1";
	$res1 = sql_query ( $sql1 );
	$row1 = mysql_fetch_array( $res1 );
	$sql2 = "SELECT sum(num) as sum2 FROM orders where type=2";
	$res2 = sql_query ( $sql2 );
	$row2 = mysql_fetch_array( $res2 );
	$sql3 = "SELECT sum(num) as sum3 FROM orders where type=3";
	$res3 = sql_query ( $sql3 );
	$row3 = mysql_fetch_array( $res3 );		
	break;}
}
	if ($rownumber  == 0)
		stderr ( "没有订单", "<a href=order.php?action=new>点击这里下单</a>", 0, 0, 0, 0 );
				
	else{
	print $grouplink;
	?>
<h1>管理订单</h1>
<h2>款式一数量:<? print("$row1[sum1]");?></h2>
<h2>款式二数量:<? print("$row2[sum2]");?></h2>
<h2>款式三数量:<? print("$row3[sum3]");?></h2>

<table class="main" border="1" cellspacing="0" cellpadding="5"><tbody><tr>
<form method=post action=order.php?action=delete>
<td class="colhead">订单号</td>
<td class="colhead">商品</td>
<td class="colhead"> 数量 </td>
<td class="colhead"> 姓名 </td>
<td class="colhead"> 用户名 </td>
<td class="colhead"> 手机号码</td>
<td class="colhead"> 状态</td>
<td class="colhead" align="center"> 操作 </td>
</tr>
<?php
	while ( $row = mysql_fetch_array ( $res ) ) 
	{
	$id=$row[id];
	$uid=$row[uid];
	$name=$row[name];
	$contact=$row[contact];
	$type=$row[type];
	$shangpin='款式'.$type.'';
	$num=$row[num];
	$status=$row[status];
	if ($status=='sending'){
	$option="<a class= faqlink href=order.php?action=delete&id=$id>删除</a>  <a class= faqlink href=order.php?action=sent&id=$id>发货</a>";
	$state='待发货';}
	else 	{$option="-";
	$state='已发货';}

print("<tr>
	<td class=\"rowfollow\">$id</td>
	<td class=\"rowfollow\">$shangpin</td>
	<td class=\"rowfollow\">$num</td>
	<td class=\"rowfollow\">$name</td>
	<td class=\"rowfollow\">".get_username($uid)."<a href=\"sendmessage.php?receiver=$uid\" title=\"发送短讯\"><img class=\"button_pm\" src=\"pic/trans.gif\" alt=\"pm\"></a></td>
	<td class=\"rowfollow\">$contact</td>
	<td class=\"rowfollow\">$state</td>
	<td class=\"rowfollow\">$option</td>
	</tr>");
}	print("</tbody></table>");

	}
	break;
	}
		case "note" :
	{
	stdhead("订购说明");
	ordmenu('note');
?>
<table class="main" width="940" border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="embedded"><h2 align="left">北洋媛三周年站庆纪念徽章订购说明 - </h2><table width="100%" border="1" cellspacing="0" cellpadding="10"><tbody><tr><td class="text">
1.本次纪念徽章共三款，每款价格2.00元/个。</br>
2.订购时间：</br>
	第一批：9月24日-9月28日。</br>
	第二批：9月29日-10月7日。</br>
3.送货及付款方法：</br>
	第一批：可选择站庆聚会期间现场付款领取，也可以选择站庆后到工作人员处领取。</br>
	第二批：站庆后由工作人员联系到工作人员处领取。</br>
	由于校外用户配送和付款的不便，暂时无法向校外用户发售。如果您特别非常极度想要，请联系客服。</br>
4.如有问题，请联系客服：<? print(get_username(24298));?><a href="sendmessage.php?receiver=24298" title="发送短讯"><img class="button_pm" src="pic/trans.gif" alt="pm"></a>
</td></tr></tbody></table></table>
<?
	}
	}
	stdfoot();
