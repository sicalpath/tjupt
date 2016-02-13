<?php
require_once('include/bittorrent.php');
dbconn();
loggedinorreturn();
parked();
function card_log($text)
{
	$text = sqlesc($text);
	$added = sqlesc(date("Y-m-d H:i:s"));
	sql_query("INSERT INTO cardlog (added, txt) VALUES($added, $text)") or sqlerr(__FILE__, __LINE__);
}
function sendmessage($subject,$msg,$receiver,$sender)
{
	global $CURUSER;
	if (!isset($sender))
	$sender=$CURUSER[id];
	$subject=sqlesc($subject);
	$msg = sqlesc($msg);
	$added = sqlesc(date("Y-m-d H:i:s"));
	if(isset($subject)&&isset($msg)&&isset($receiver)&&isset($sender)&&isset($added))
	sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES($sender, $subject, $receiver, $msg, $added)") or sqlerr(__FILE__, __LINE__);
}

function buycards($type,$value,$price,$torrentid=0)
{
	global $CURUSER;
	if (!(isset($type)&&isset($value)&&isset($price)))
	stderr('抱歉', '卡片信息有误。', true, false, true, true);
	if (($type=='free')&&(!isset($torrentid)))
	stderr('抱歉', '没有提供种子id。', true, false, true, true);
	$bonuscomment = $CURUSER['bonuscomment'];
	$bonuscomment = date("Y-m-d") . " - 花费 $price 魔力值购买了一张道具卡。\n " .$bonuscomment;
	$sellprice=0.6*$price;
	$type=sqlesc($type);
	if ($CURUSER[seedbonus]<$price)
	stderr('抱歉', '您的魔力值不足。', true, false, true, true);
	sql_query("update users set seedbonus = seedbonus - $price, bonuscomment = ".sqlesc($bonuscomment)." where id = $CURUSER[id]") or sqlerr(__FILE__,__LINE__);
	sql_query("INSERT INTO bonuscards (userid, type, value, price, torrentid) VALUES( $CURUSER[id] , $type , $value , $sellprice , $torrentid )") or sqlerr(__FILE__, __LINE__);
	$price=(int)$price;
	card_log("用户".$CURUSER[username]."(".$CURUSER[id].")购买了一张原价为".$price."的道具卡。");
	stderr('成功', '成功购买了价值'.$price.'的道具卡，赶快去<a href=bonusapps.php class=faqlink>使用</a>吧！', false, false, true, false);
	}
$action = htmlspecialchars($_GET['action']);
$do = htmlspecialchars($_GET['do']);
unset($msg);
if (isset($do)) {
	if ($do == "free")
	$msg = "优惠卡购买成功";
	elseif ($do == "upload")
	$msg = "上传卡购买成功";
	elseif ($do == "download")
	$msg =  "下载卡购买成功";
	elseif ($do == "invite")
	$msg =  "邀请卡购买成功";
	else
	$msg = '';
}
	stdhead("道具卡商城");
if (!$action) {
?>
<table width="100%" >
<tr><td class="colhead" colspan="5" align="center"><font class="big">道具卡商城</font></td></tr>
<?php
    if ($msg)
	print("<tr><td align=\"center\" colspan=\"5\"><font class=\"striking\">". $msg ."</font></td></tr>");
?>
<tr><td class="text" align="center" colspan="5"><b>用你的魔力值来购买神奇的道具卡！道具卡购买后需使用才能生效！道具卡可以转让，也可以出售，出售价格为购入价格的60%。</b></td></tr>
<tr><td class="colhead" align="center">项目</td><td class="colhead" align="left">简介</td><td class="colhead" width="200px" align="center">价格</td><td class="colhead" align="center">类别</td><td class="colhead" align="center">交换</td></tr>
<tr><form action="?action=exchange" method="post"><td class="rowhead_center"><input type="hidden" name="type" value="free"><b>1</b></td><td class="rowfollow" align="left"><h1>优惠卡</h1>这种卡片能使一个种子在24小时变为你想要的优惠种类！免费有以下几种：<br><span style="color: #7c7ff6;"><b>50%下载</b></span>  <span style="color: #f0cc00;"><b>免费</b></span>  <span style="color: #aaaaaa;"><b>2x上传</b></span>  <span style="color: #7ad6ea;"><b>50%下载&amp;2x上传</b></span>  <span style="color: #99cc66;"><b>免费&amp;2x上传</b></span><br>价格与优惠种类以及种子体积有关。<font color=red>只有当前没有任何优惠的种子才能使用优惠卡哦！</font></td><td class="rowfollow" align="center"><span style="color: #7c7ff6;"><b>50%下载</b></span>——500/GB<br><span style="color: #f0cc00;"><b>免费</b></span>——1000/GB<br><span style="color: #aaaaaa;"><b>2x上传</b></span>——1000/GB<br><span style="color: #7ad6ea;"><b>50%下载&amp;2x上传</b></span>——1500/GB<br><span style="color: #99cc66;"><b>免费&amp;2x上传</b></span>——2000/GB</td><td class="rowfollow" align="center">
优惠类型：
	<select class="med" name="value" style="width: 100px;">
    <option value="2">free</option>
    <option value="3">2x</option>
    <option value="4">2xfree</option>
    <option value="5">50%</option>
    <option value="6">2x50%</option>
</select>
<br>种子ID：<input type="text" style="width: 80px" name="torrentid"><br></td>
<td><input type="submit" name="buy" value="交换"></td></form></tr>
<tr><form action="?action=exchange" method="post"><td class="rowhead_center"><input type="hidden" name="type" value="upload"><b>2</b></td><td class="rowfollow" align="left"><h1>上传卡</h1>碰碰运气吧！这张卡可以让你获得随机数值的上传量！<br>随机范围：普通卡：1G-10G；高级卡：10G-100G</td><td class="rowfollow" align="center">普通卡：2000<br>高级卡：20000</td><td class="rowfollow" align="center"><select class="med" name="value" style="width: 100px;">
    <option value="1">普通卡</option>
    <option value="2">高级卡</option>
</select></td>
<td><input type="submit" name="buy" value="交换"></td></form></tr>
<tr><form action="?action=exchange" method="post"><td class="rowhead_center"><input type="hidden" name="type" value="download"><b>2</b></td><td class="rowfollow" align="left"><h1>下载卡</h1>碰碰运气吧！这张卡可以让你获得随机数值的下载量！<br>随机范围：普通卡：1G-10G；高级卡：10G-100G</td><td class="rowfollow" align="center">普通卡：10000<br>高级卡：100000</td><td class="rowfollow" align="center"><select class="med" name="value" style="width: 100px;">
    <option value="1">普通卡</option>
    <option value="2">高级卡</option>
</select></td>
<td><input type="submit" name="buy" value="交换"></td></form></tr>
</table>
<?php
}
if ($action == "exchange") {
if ($_POST[buy])
{
	$type=$_POST[type];
	$value=$_POST[value];
	$torrentid=$_POST[torrentid];
	$allowedtypes=array('free','upload','download','invite','tool');
	if (!in_array($type, $allowedtypes))
	stderr('错误', '没有这类道具卡。', true, false, true, true);
	switch ($type){
	case 'free':
	$allowedvalues=array(2,3,4,5,6);
	if (!in_array($value, $allowedvalues))
	stderr('错误', '没有这类道具卡。', true, false, true, true);
	if (!is_numeric($torrentid))
	stderr('错误', '种子id错误。', true, false, true, true);
	$res=sql_query("select * from torrents where id = $torrentid");
	$row=mysql_fetch_array($res);
	if (!$row)
	stderr('错误', '找不到id为'.$torrentid.'的种子。', true, false, true, true);
	$size=$row[size];
	$sizegb=$size/(1024*1024*1024);
	if ($value==2||$value==3)
	$pricepergb=1000;
	elseif ($value==4)
	$pricepergb=2000;
	elseif ($value==5)
	$pricepergb=500;
	elseif ($value==6)
	$pricepergb=1500;
	else
 	$pricepergb=1000;
	$price=$sizegb*$pricepergb;
	break;
	
	case 'upload':
	$allowedvalues=array(1,2);
	if (!in_array($value, $allowedvalues))
	stderr('错误', '没有这类道具卡。', true, false, true, true);
	$price=2000;
	if ($value==1)
	$price=2000;
	elseif ($value==2)
	$price=20000;
	break;
	
	case 'download':
	$allowedvalues=array(1,2);
	if (!in_array($value, $allowedvalues))
	stderr('错误', '没有这类道具卡。', true, false, true, true);
	$price=10000;
	if ($value==1)
	$price=10000;
	elseif ($value==2)
	$price=100000;
	break;
	
	case 'invite':
	$allowedvalues=array(1,2,3,4,5,6,7,8,9,10);
	if (!in_array($value, $allowedvalues))
	stderr('错误', '没有这类道具卡。', true, false, true, true);
	$price=10000*$value;
	break;
	
	case tool:
	stderr('错误', '没有这类道具卡。', true, false, true, true);
	break;
	}
	if (!isset($torrentid))
	$torrentid=0;
	buycards($type,$value,$price,$torrentid);

}
}
stdfoot();
?>
