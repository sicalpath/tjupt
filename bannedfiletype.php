<?php
require "include/bittorrent.php";
dbconn ();
loggedinorreturn ();
if (get_user_class () < UC_MODERATOR)
	stderr ( "Sorry", "Access denied." );

$remove = ( int ) $_GET ['remove'];
if (is_valid_id ( $remove )) {
	sql_query ( "DELETE FROM banned_file_type WHERE id=" . mysql_real_escape_string ( $remove ) ) or sqlerr ();
}

if ($_SERVER ["REQUEST_METHOD"] == "POST" && get_user_class () >= UC_MODERATOR) {
	$type = trim ( $_POST ["type"] );
	$catid = $_POST['catid'];
	$class2 = 'banned';
	if ($_POST ["class"] == 1)
	$class2 = 'banned';
	if ($_POST ["class"] == 2)
	$class2 = 'notallowed';
	if (! $type || ! $catid) {
		stderr ( "出错了", "请填写文件类型、版块。" );
	}
	
	$type = sqlesc ( $type );
	foreach ($catid as $cid)
	sql_query ( "INSERT INTO banned_file_type (type, catid, class) VALUES($type, $cid, '".$class2."')" ) or sqlerr ( __FILE__, __LINE__ );
	header ( "Location: $_SERVER[REQUEST_URI]" );
	die ();
}

// ob_start("ob_gzhandler");

$res = sql_query ( "SELECT banned_file_type.*, categories.name as catname FROM banned_file_type LEFT JOIN categories ON categories.id = banned_file_type.catid ORDER BY catid ASC" ) or sqlerr ();

stdhead ( "禁止发布的文件类型" );

print ("<h1>已禁止发布文件类型</h1>\n") ;
?>
<br/><h2>说明：</h2><table width="100%"><tbody><tr><td class="text" valign="top"><div style="margin-left: 16pt;">1.banned:含有此类文件的种子将无法发布并且不会转为候选。<br/>2.notallowed:含有此类文件的种子将无法发布而会转为候选。<br/>3.默认banned:(qsv)|(KUX);<br/>默认notallowed:(torrent)|(!ut)|(url)|(qdl2)|(td)|(tdl)|(td\.cfg)|(tmp)。<br/></div></td></tr></tbody></table>
<?php
if (mysql_num_rows ( $res ) == 0)
	print ("<p align=center><b>列表为空！</b></p>\n") ;
else {
	print ("<table border=1 cellspacing=0 cellpadding=5>\n") ;
	print ("<tr><td class=colhead>文件类型</td><td class=colhead>版块</td><td class=colhead>属性</td><td class=colhead>操作</td></tr>\n") ;
	
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		print ("<tr><td>" . $arr [type] . "</td><td>" . $arr [catname] . "</td><td>" . $arr ['class'] . "</td><td><a class=faqlink href=bannedfiletype.php?remove=$arr[id]>移除</a> </td></tr>\n") ;
	}
	print ("</table>\n") ;
}

if (get_user_class () >= UC_MODERATOR) {
	print ("<h1>添加新条目</h1>") ;
	print ("<table border=1 cellspacing=0 cellpadding=5>") ;
	print ("<form method=post action=bannedfiletype.php>") ;
	print ("<tr><td class=rowhead>文件类型</td><td><input type=text name=type size=40></td></tr>") ;
	$s ='';
	$cats = genrelist ( $browsecatmode );
	foreach ( $cats as $row )
		$s .= "<label><input id=\"" . $row ["id"] . "\" type=\"checkbox\" value=\"" . $row ["id"] . "\" name=\"catid[]\">" . htmlspecialchars ( $row ["name"] ) . "</label>";
	print ("<tr><td class=rowhead>版块</td><td>" . $s . "</td></tr>") ;
	print ("<tr><td class=rowhead>属性</td><td><select name=\"class\" id=\"class\">
<option value=0>请选择</option><option value=1>banned</option><option value=2>notallowed</option></td></tr>") ;
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
