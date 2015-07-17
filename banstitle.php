<?php
require "include/bittorrent.php";
dbconn ();
loggedinorreturn ();
if (get_user_class () < UC_MODERATOR)
	stderr ( "Sorry", "Access denied." );

$remove = ( int ) $_GET ['remove'];
if (is_valid_id ( $remove )) {
	sql_query ( "DELETE FROM bannedtitle WHERE id=" . mysql_real_escape_string ( $remove ) ) or sqlerr ();
	write_log ( "Ban title " . htmlspecialchars ( $remove ) . " was removed by $CURUSER[id] ($CURUSER[username])", 'mod' );
}

if ($_SERVER ["REQUEST_METHOD"] == "POST" && get_user_class () >= UC_MODERATOR) {
	$keywords = trim ( $_POST ["keywords"] );
	$catid = (0 + $_POST ["type"]);
	
	$until = $_POST ["timeuntil"];
	if (! until) {
		$until = '0000-00-00 00:00:00';
	}
	$until = sqlesc ( $until );
	
	$comment = trim ( $_POST ["comment"] );
	
	if (! $keywords || ! $catid) {
		stderr ( "出错了", "请填写关键词、类型。" );
	}
	
	$keywords = sqlesc ( $keywords );
	$comment = sqlesc ( $comment );
	$added = sqlesc ( date ( "Y-m-d H:i:s" ) );
	sql_query ( "INSERT INTO bannedtitle (keywords, catid, added, until, comment, addedby) VALUES($keywords, $catid, $added, $until, $comment, " . mysql_real_escape_string ( $CURUSER [id] ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
	header ( "Location: $_SERVER[REQUEST_URI]" );
	die ();
}

// ob_start("ob_gzhandler");

$res = sql_query ( "SELECT bannedtitle.*, categories.name as catname FROM bannedtitle LEFT JOIN categories ON categories.id = bannedtitle.catid ORDER BY keywords ASC" ) or sqlerr ();

stdhead ( "禁止发布的资源" );

print ("<h1>已禁止发布的列表（中英文标题的关键词）</h1>\n") ;

if (mysql_num_rows ( $res ) == 0)
	print ("<p align=center><b>列表为空！</b></p>\n") ;
else {
	print ("<table border=1 cellspacing=0 cellpadding=5>\n") ;
	print ("<tr><td class=colhead>关键词</td><td class=colhead>类型</td><td class=colhead align=left>添加时间</td><td class=colhead align=left>预计解封时间</td>" . "<td class=colhead align=left>操作人</td><td class=colhead align=left>备注</td><td class=colhead>移除</td></tr>\n") ;
	
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		print ("<tr><td>" . $arr [keywords] . "</td><td>" . $arr [catname] . "</td><td>" . gettime ( $arr [added] ) . "</td><td>" . ($arr [until] == '0000-00-00 00:00:00' ? "手动解除封禁" : gettime ( $arr [until] )) . "</td><td align=left>" . get_username ( $arr ['addedby'] ) . "</td><td align=left>$arr[comment]</td><td><a href=banstitle.php?remove=$arr[id]>移除</a></td></tr>\n") ;
	}
	print ("</table>\n") ;
}

if (get_user_class () >= UC_MODERATOR) {
	print ("<h1>添加新条目</h1>") ;
	print ("<table border=1 cellspacing=0 cellpadding=5>") ;
	print ("<form method=post action=banstitle.php>") ;
	print ("<tr><td class=rowhead>关键词</td><td><input type=text name=keywords size=40></td></tr>") ;
	
	$s = "<select name=\"type\" id=\"browsecat\" >\n<option value=\"0\">" . "请选择" . "</option>";
	$cats = genrelist ( $browsecatmode );
	foreach ( $cats as $row )
		$s .= "<option value=\"" . $row ["id"] . "\">" . htmlspecialchars ( $row ["name"] ) . "</option>";
	$s .= "</select>";
	print ("<tr><td class=rowhead>类型</td><td>" . $s . "</td></tr>") ;
	
	print ('<tr><td class=rowhead>解封时间</td><td><input type="text" name="timeuntil" id="timeuntil" />&nbsp;<b>不填或"0000-00-00 00:00:00"为手动解封</b></td></tr>') ;
	print ("<tr><td class=rowhead>备注</td><td><input type=text name=comment size=40></td></tr>") ;
	print ("<tr><td colspan=2 align=center><input type=submit value='确认' class=btn></td></tr>") ;
	print ("</form></table>") ;
	
	echo '
<link rel="stylesheet" href="js/jquery-ui-css/jquery-ui-1.8.23.custom.css" type="text/css" />
<script type="text/javascript" src="js/jquery-ui-1.8.23.custom.min.js"></script>
	
<link rel="stylesheet" href="styles/jquery-ui.css" type="text/css" />
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="js/jquery.ui.spinner.min.js"></script>
';
	print ('<script type="text/javascript">
$(function(){
	$("#timeuntil").datetimepicker({dateFormat: "yy-mm-dd", showSecond: true, timeFormat:"hh:mm:ss", minDate: new Date("0000-00-00 00:00:00")});
});
</script>') ;
}

stdfoot ();

?>
