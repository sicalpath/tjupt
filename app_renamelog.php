<?php
require_once ('include/bittorrent.php');
dbconn ();
loggedinorreturn ();
parked ();

$lang_app_renamelog = array (
		'std_error' => "这谁家熊孩子啊",
		'std_invalid_action' => '别到处乱跑了，回家吃饭去吧！',
		'text_no_permission' => "<b>错误！</b>你没有该权限。",
		'head_karma_page' => "北洋园用户改名历史",
		'text_karma_system' => " <b>园道&bull;轮回天生</b> 使用记录",
		'text_no_log' => "还没有人用过哦~<br/><br/><a href='mybonus.php'>返回</a>",
		'return_to_bonus_system' => "<a class='faqlink' href='mybonus.php'>返回魔力值系统</a>" 
);
function print_logstable($res, $action, $sort) {
	print ('<table class="main" border="0" cellspacing="0" cellpadding="0">') ;
	print ('<tr>') ;
	$mytd = '<td class="colhead" align="center"><a href="' . htmlspecialchars ( "app_renamelog.php?action=" . $action . "&sort=" . ($sort == "useridasc" ? "useriddesc" : "useridasc") ) . '">用户ID</a></td>';
	$mytd .= '<td class="colhead" align="center">当前用户名</td>';
	$mytd .= '<td class="colhead" align="center"><a href="' . htmlspecialchars ( "app_renamelog.php?action=" . $action . "&sort=" . ($sort == "timerenameasc" ? "timerenamedesc" : "timerenameasc") ) . '">轮回时间</a></td>';
	$mytd .= '<td class="colhead" align="center"><a href="' . htmlspecialchars ( "app_renamelog.php?action=" . $action . "&sort=" . ($sort == "oldnameasc" ? "oldnamedesc" : "oldnameasc") ) . '">轮回前用户名</a></td>';
	$mytd .= '<td class="colhead" align="center"><a href="' . htmlspecialchars ( "app_renamelog.php?action=" . $action . "&sort=" . ($sort == "newnameasc" ? "newnamedesc" : "newnameasc") ) . '">轮回后用户名</a></td>';
	$mytd .= '<td class="colhead" align="center">花费魔力值</td>';
	print ($mytd) ;
	print ('<tr/>') ;
	
	while ( $row = mysql_fetch_assoc ( $res ) ) {
		print ('<tr>') ;
		print ('<td class="rowfollow" align="center" >' . $row ["userid"] . '</td>') ;
		print ('<td class="rowfollow" align="center" >' . get_username ( $row ["userid"] ) . '</td>') ;		
		print ('<td class="rowfollow" align="center" >' . $row ["timerename"] . '</td>') ;
		print ('<td class="rowfollow" align="center" >' . $row ["oldname"] . '</td>') ;
		print ('<td class="rowfollow" align="center" >' . $row ["newname"] . '</td>') ;
		print ('<td class="rowfollow" align="center" >' . $row ["paybonus"] . '</td>') ;
		print ('</tr>') ;
	}
	
	print ('</table><br/>') ;
}

$action = htmlspecialchars ( $_GET ['action'] );
$allowed_actions = array (
		"renamelog" 
);

if (! $action) {
	$action = 'renamelog';
}

if (! in_array ( $action, $allowed_actions )) {
	stderr ( $lang_app_renamelog ['std_error'], $lang_app_renamelog ['std_invalid_action'] );
}

if ($_GET ["sort"]) {
	switch ($_GET ["sort"]) {
		case 'useridasc' :
			{
				$orderby = "userid ASC";
				break;
			}
		case 'useriddesc' :
			{
				$orderby = "userid DESC";
				break;
			}
		case 'oldnameasc' :
			{
				$orderby = "oldname ASC";
				break;
			}
		case 'oldnamedesc' :
			{
				$orderby = "oldname DESC";
				break;
			}
		case 'newnameasc' :
			{
				$orderby = "newname ASC";
				break;
			}
		case 'newnamedesc' :
			{
				$orderby = "newname DESC";
				break;
			}
		case 'timerenameasc' :
			{
				$orderby = "timerename ASC";
				break;
			}
		case 'timerenamedesc' :
			{
				$orderby = "timerename DESC";
				break;
			}
		default :
			{
				$orderby = "id DESC";
			}
	}
} else {
	$orderby = "id DESC";
}

stdhead ( $lang_app_renamelog ['head_karma_page'] );

if ($action == "renamelog") {
	$sql = 'SELECT COUNT(*) FROM app_rename';
	$res = sql_query ( $sql ) or die ( mysql_error () );
	$count = 0;
	while ( $row = mysql_fetch_array ( $res ) )
		$count += $row [0];
	
	if ($CURUSER ["torrentsperpage"]) {
		$logsperpage = ( int ) $CURUSER ["torrentsperpage"];
	} elseif ($logsperpage_main) {
		$logsperpage = $logsperpage_main;
	} else {
		$logsperpage = 50;
	}
	$addparam = "action=renamelog&";
	
	if ($count) {
		list ( $pagertop, $pagerbottom, $limit ) = pager ( $logsperpage, $count, "?" . $addparam );
		$query = "SELECT id, userid, oldname, newname, timerename, paybonus FROM app_rename ORDER BY " . $orderby . " " . $limit;
		$res = sql_query ( $query ) or die ( mysql_error () );
		
		print ('<h1 align="center">' . $lang_app_renamelog ["text_karma_system"] . '</h1><br/>') ;
		print_logstable ( $res, $action, $_GET ["sort"] );
		print ($pagerbottom) ;
		print ('<br/><h1 align="center">' . $lang_app_renamelog ["return_to_bonus_system"] . '</h1>') ;
	} else {
		unset ( $res );
		print ('<h1 align="center">' . $lang_app_renamelog ["text_no_log"] . '</h1>') ;
	}
} else {
	stderr ( $lang_app_renamelog ['std_error'], $lang_app_renamelog ['std_invalid_action'] );
}

stdfoot ();
?>
