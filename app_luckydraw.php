<?php
require_once ('include/bittorrent.php');
dbconn ();
loggedinorreturn ();
parked ();

$lang_app_luckydraw = array (
		'std_sorry' => "对不起！",
		'std_karma_system_disabled' => "魔力值系统当前处于关闭中。",
		'std_points_active' => "不过你的魔力值仍在计算中。",
		'std_error' => "这谁家熊孩子啊",
		'std_invalid_action' => '<img border=0 src="pic/smilies/503.gif"><br /><br />别到处乱跑了，回家吃饭去吧！',
		'text_no_permission' => "<b>错误！</b>你没有该权限。",
		'head_karma_page' => "的抽奖箱",
		'text_karma_system' => "幸运抽奖中心",
		'text_exchange_your_karma' => "用你的魔力值（当前",
		'text_for_goodies' => "）来玩儿吧！" 
);
function get_loglast() {
	global $Cache;
	$res = sql_query ( "SELECT id, time_start, time_until, ticket_price, ticket_win FROM app_luckydraw WHERE status = '2' AND time_until < " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . " ORDER BY id DESC limit 1" ) or sqlerr ( __FILE__, __LINE__ );
	$row = mysql_fetch_assoc ( $res );
	if ($row) {
		$last = '<ul style="text-align:left;">';
		$last = $last . '<li>时间：' . $row ["time_start"] . '至' . $row ["time_until"] . '</li>';
		$last = $last . '<li>彩券价格：' . $row ["ticket_price"] . '魔力值/张</li>';
		$last = $last . '<li>彩券奖金：' . $row ["ticket_win"] . '魔力值/张</li>';
		$winner_tickets = $Cache->get_value ( 'app_luckydraw_' . $row ["id"] . '_winner_tickets' );
		$winner_usernames = $Cache->get_value ( 'app_luckydraw_' . $row ["id"] . '_winner_usernames' );
		if (! $winner_tickets || ! $winner_usernames) {
			$winner_tickets = '';
			$winner_usernames = '';
			$res2 = sql_query ( "SELECT luckydraw_id, user_id, ticket_id, win_or_lose FROM app_luckydraw_players WHERE luckydraw_id = $row[id] AND win_or_lose = '1' ORDER BY ticket_id" ) or sqlerr ( __FILE__, __LINE__ );
			while ( $row2 = mysql_fetch_assoc ( $res2 ) ) {
				if ($winner_tickets) {
					$winner_tickets = $winner_tickets . ', ' . $row2 ["ticket_id"];
					$winner_usernames = $winner_usernames . ', ' . get_username ( $row2 ["user_id"] );
				} else {
					$winner_tickets = $winner_tickets . $row2 ["ticket_id"];
					$winner_usernames = $winner_usernames . get_username ( $row2 ["user_id"] );
				}
			}
			$Cache->cache_value ( 'app_luckydraw_' . $row ["id"] . '_winner_tickets', $winner_tickets, 86400 );
			$Cache->cache_value ( 'app_luckydraw_' . $row ["id"] . '_winner_usernames', $winner_usernames, 86400 );
		}
		
		$last = $last . '<li>中奖号码：' . $winner_tickets . '</li>';
		$last = $last . '<li>中奖人：' . $winner_usernames . '</li>';
		$last = $last . '</ul>';
	} else {
		$last = "乃乃乃，不要这样折磨北洋媛了吧~";
	}
	
	return $last;
}
function get_current1($row = false) {
	if ($row == false) {
		$res = sql_query ( "SELECT id, time_start, time_until, ticket_price FROM app_luckydraw WHERE status = '1' ORDER BY id DESC limit 1" ) or sqlerr ( __FILE__, __LINE__ );
		$row = mysql_fetch_assoc ( $res );
	}
	if (! $row) {
		$res = sql_query ( "SELECT id, time_start, time_until, ticket_price FROM app_luckydraw WHERE status = '0' AND time_start <= " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . " ORDER BY id DESC limit 1" ) or sqlerr ( __FILE__, __LINE__ );
		$row = mysql_fetch_assoc ( $res );
		if (! $row) {
			return "当前没有彩券可买";
		} else {
			sql_query ( "UPDATE app_luckydraw SET status = '1' WHERE id=" . $row ["id"] ) or sqlerr ( __FILE__, __LINE__ );
		}
	}
	
	$ret = '出售时间：<b>' . $row ["time_start"] . '</b>&nbsp;至&nbsp;<b>' . $row ["time_until"] . '</b><br/><br/>';
	$start_time = strtotime ( $row ["time_start"] );
	$end_time = strtotime ( $row ["time_until"] );
	$now_time = time ();
	if ($start_time > $now_time) {
		$ret = $ret . "奖券尚未开始销售";
	} elseif ($end_time < $now_time) {
		$ret = $ret . "奖券已停止销售，等待开奖";
	} else {
		$remain_time = $end_time - $now_time;
		$remian_hours = intval ( $remain_time / 3600 );
		$remain_time = $remain_time % 3600;
		$remian_mins = intval ( $remain_time / 60 );
		$remain_secs = $remain_time % 60;
		$ret = $ret . "剩余时间：<b>" . $remian_hours . ":" . ($remian_mins > 9 ? $remian_mins : "0" . $remian_mins) . ":" . ($remain_secs > 9 ? $remain_secs : "0" . $remain_secs) . "</b>";
	}
	$ret = $ret . "<br/><br/>每张彩券价格是 <b>" . $row ["ticket_price"] . "</b> 魔力值";
	
	return $ret;
}
function print_logstable($res) {
	global $Cache;
	print ('<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0">') ;
	print ('<tr><td class="colhead" align="center">抽奖时间</td><td class="colhead" align="center">彩券单价</td><td class="colhead" align="center">单注奖金</td><td class="colhead" align="center">中奖号码</td><td class="colhead" align="center">中奖人</td></tr>') ;
	
	while ( $row = mysql_fetch_assoc ( $res ) ) {
		print ('<tr>') ;
		print ('<td class="text" align="center" width="120px">' . $row ["time_until"] . '</td>') ;
		print ('<td class="text" align="center" width="60px">' . $row ["ticket_price"] . '</td>') ;
		print ('<td class="text" align="center" width="60px">' . $row ["ticket_win"] . '</td>') ;
		
		$winner_tickets = $Cache->get_value ( 'app_luckydraw_' . $row ["id"] . '_winner_tickets' );
		$winner_usernames = $Cache->get_value ( 'app_luckydraw_' . $row ["id"] . '_winner_usernames' );
		if (! $winner_tickets || ! $winner_usernames) {
			$winner_tickets = '';
			$winner_usernames = '';
			$res2 = sql_query ( "SELECT user_id, ticket_id FROM app_luckydraw_players WHERE luckydraw_id = $row[id] AND win_or_lose = '1' ORDER BY ticket_id" ) or sqlerr ( __FILE__, __LINE__ );
			while ( $row2 = mysql_fetch_assoc ( $res2 ) ) {
				if ($winner_tickets) {
					$winner_tickets = $winner_tickets . ', ' . $row2 ["ticket_id"];
					$winner_usernames = $winner_usernames . ', ' . get_username ( $row2 ["user_id"] );
				} else {
					$winner_tickets = $winner_tickets . $row2 ["ticket_id"];
					$winner_usernames = $winner_usernames . get_username ( $row2 ["user_id"] );
				}
			}
			$Cache->cache_value ( 'app_luckydraw_' . $row ["id"] . '_winner_tickets', $winner_tickets, 86400 );
			$Cache->cache_value ( 'app_luckydraw_' . $row ["id"] . '_winner_usernames', $winner_usernames, 86400 );
		}
		print ('<td class="text" align="center">' . $winner_tickets . '</td>') ;
		print ('<td class="text" align="center">' . $winner_usernames . '</td>') ;
		
		print ('</tr>') ;
	}
	
	print ('</table><br/>') ;
}

if ($bonus_tweak == "disable" || $bonus_tweak == "disablesave") {
	stderr ( $lang_app_luckydraw ['std_sorry'], $lang_app_luckydraw ['std_karma_system_disabled'] . ($bonus_tweak == 'disablesave' ? '<b>' . $lang_app_luckydraw ['std_points_active'] . '</b>' : ''), false );
}

if (get_user_class () < UC_POWER_USER) {
	stderr ( $lang_app_luckydraw ['std_error'], $lang_app_luckydraw ['std_invalid_action'], false );
}

$action = htmlspecialchars ( $_GET ['action'] );
$allowed_actions = array (
		"loglast",
		"loghistory",
		"viewcurrent",
		"buytickets",
		"dobuytickets",
		"manage",
		"addnext",
		"deletedraw" 
);
if (! $action) {
	$action = 'loglast';
}
if (! in_array ( $action, $allowed_actions )) {
	stderr ( $lang_app_luckydraw ['std_error'], $lang_app_luckydraw ['std_invalid_action'], false );
}

stdhead ( $CURUSER ['username'] . $lang_app_luckydraw ['head_karma_page'] );

$bonus = number_format ( $CURUSER ['seedbonus'], 1 );

if ($action == "loglast") { // 上期信息
	print ('<table align="center" width="60%" border="0" cellspacing="0" cellpadding="0">') ;
	print ('<tr><td class="colhead" align="center">' . $SITENAME . $lang_app_luckydraw ['text_karma_system'] . '</td></tr>') ;
	if ($msg) {
		print ('<tr><td align="center"><font class="striking">' . $msg . '</font></td></tr>') ;
	}
	
	print ('<tr><td class="text" align="center">' . $lang_app_luckydraw ['text_exchange_your_karma'] . '<b>' . $bonus . '</b>' . $lang_app_luckydraw ['text_for_goodies'] . '<br/></td></tr>') ;
	
	print ('<tr><td class="colhead" align="left">' . '上次彩券抽奖结果' . '</td></tr>') ;
	print ('<tr><td class="text" align="left">' . get_loglast () . '</td></tr>') ;
	
	print ('<tr><td class="colhead" align="left">' . '正在出售的幸运彩券' . '</td></tr>') ;
	print ('<tr><td class="text" align="left">' . get_current1 () . '</td></tr>') ;
	
	print ('<tr><td class="text" align="left">' . '<a class="faqlink" href="./app_luckydraw.php?action=buytickets">购买彩券</a>&nbsp;&nbsp;&nbsp;' . '<a class="faqlink" href="./app_luckydraw.php?action=viewcurrent">本期信息</a>&nbsp;&nbsp;&nbsp;' . '<a class="faqlink" href="./app_luckydraw.php?action=loghistory">抽奖历史</a>' . (get_user_class () < UC_STAFFLEADER ? '' : '&nbsp;&nbsp;&nbsp;<a class="faqlink" href="./app_luckydraw.php?action=manage">彩券管理</a>') . '</td></tr>') ;
	print ('</table><br/>') ;
} elseif ($action == "loghistory") { // 历史记录
	$sql = 'SELECT COUNT(*) FROM app_luckydraw WHERE status = "2" AND time_until <= ' . sqlesc ( date ( "Y-m-d H:i:s" ) );
	$res = sql_query ( $sql ) or die ( mysql_error () );
	$count = 0;
	while ( $row = mysql_fetch_array ( $res ) )
		$count += $row [0];
		
		// if ($CURUSER ["torrentsperpage"]) {
		// $logsperpage = ( int ) $CURUSER ["torrentsperpage"];
		// } elseif ($logsperpage_main) {
		// $logsperpage = $logsperpage_main;
		// } else {
		// $logsperpage = 20;
		// }
	$logsperpage = 20;
	
	$addparam = "action=loghistory&";
	if ($pagerlink != "") {
		if ($addparam {strlen ( $addparam ) - 1} != ";") { // & = &amp;
			$addparam = $addparam . "&" . $pagerlink;
		} else {
			$addparam = $addparam . $pagerlink;
		}
	}
	
	if ($count) {
		list ( $pagertop, $pagerbottom, $limit ) = pager ( $logsperpage, $count, "?" . $addparam );
		$query = "SELECT app_luckydraw.id, app_luckydraw.time_until, app_luckydraw.ticket_price, app_luckydraw.ticket_win FROM app_luckydraw WHERE app_luckydraw.status = '2' ORDER BY app_luckydraw.time_until DESC " . " $limit";
		$res = sql_query ( $query ) or die ( mysql_error () );
		
		print ('<h1 align="center">抽奖历史<br/></h1>') ;
		print_logstable ( $res );
		print ($pagerbottom) ;
	} else {
		unset ( $res );
		
		print ('<h1 align="center">尚未进行过开奖<br/><br/><a href="./app_luckydraw.php">返回</a></h1>') ;
	}
	
	// --- end loghistory
} elseif ($action == "viewcurrent") { // 当期信息
	
	$res = sql_query ( "SELECT id, time_start, time_until, ticket_price FROM app_luckydraw WHERE status = '1' ORDER BY id DESC limit 1" ) or sqlerr ( __FILE__, __LINE__ );
	$row = mysql_fetch_assoc ( $res );
	if ($row) {
		print ('<table align="center" width="60%" border="0" cellspacing="0" cellpadding="0">') ;
		print ('<tr><td class="colhead" align="left" colspan="2">' . '正在出售的幸运彩券' . '</td></tr>') ;
		print ('<tr><td class="text" align="left" colspan="2">' . get_current1 ( $row ) . '</td></tr>') ;
		
		print ('<tr><td class="colhead" align="center" width="100%">用户名</td><td class="colhead" align="center">持有彩券数</td></tr>') ;
		
		$res2 = sql_query ( "SELECT user_id, COUNT(*) AS cnts FROM app_luckydraw_players WHERE luckydraw_id = " . $row ["id"] . " GROUP BY user_id ORDER BY cnts DESC, user_id DESC" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $row2 = mysql_fetch_assoc ( $res2 ) ) {
			print ('<tr>') ;
			print ('<td align="center" class="rowfollow nowrap">' . get_username ( $row2 ["user_id"] ) . '</td>') ;
			print ('<td align="center" class="rowfollow nowrap">' . $row2 ["cnts"] . '</td>') ;
			print ('</tr>') ;
		}
		
		print ('<tr><td class="text" align="center" colspan="2">' . '<a class="faqlink" href="./app_luckydraw.php?action=buytickets">购买彩券</a></tr>') ;
		
		print ('</table><br/>') ;
	} else {
		print ('<h1 align="center">当前没有彩券出售<br /><br /><a href="./app_luckydraw.php">返回</a></h1>') ;
	}
} elseif ($action == "buytickets") { // 购买彩券
	$res = sql_query ( "SELECT id, time_start, time_until, ticket_price, ticket_tax_rate, ticket_max_win_x, ticket_win, user_max, winners_max, ticket_total, bonuspool FROM app_luckydraw WHERE status = '1' AND time_until > " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . " ORDER BY id DESC limit 1" ) or sqlerr ( __FILE__, __LINE__ );
	$row = mysql_fetch_assoc ( $res );
	if (! $row) {
		print ('<h1 align="center">当期没有彩券可买<br /><br /><a href="./app_luckydraw.php">返回</a></h1>') ;
	} else {
		print ('<table align="center" width="80%" border="0" cellspacing="0" cellpadding="0">') ;
		print ('<tr><td class="colhead" align="center">' . '购买彩券' . '</td></tr>') ;
		
		$current_info = '<ul style="text-align:left;">';
		$current_info = $current_info . '<li>本期彩券价格：<b>' . $row ["ticket_price"] . '</b> 魔力值/张</li>';
		$current_info = $current_info . '<li>本期开奖时间：<b>' . $row ["time_until"] . '</b></li>';
		$current_info = $current_info . '<li>本期将产生 <b>' . $row ["winners_max"] . ' </b> 个中奖号码</li>';
		$current_info = $current_info . '<li>每张中奖彩券的持有者将会获得 <b>' . $row ["ticket_win"] . '</b> 个魔力值</li>';
		$current_info = $current_info . '<li>系统会按购买的先后顺序发放彩券号（1，2，......）</li>';
		$current_info = $current_info . '<li>彩券购买后不予退款</li>';
		$current_info = $current_info . '<li>个人买的越多，中奖概率越大</li>';
		$current_info = $current_info . '<li>当期卖出的彩券数越多，奖金越高</li>';
		if ($row ["ticket_max_win_x"] > 0) {
			// if ($row ["ticket_win"] >= $row ["ticket_max_win_x"] * $row
			// ["ticket_price"]) {
			$current_info = $current_info . '<li>单注奖金最高为彩券单价的 <b>' . $row ["ticket_max_win_x"] . '</b> 倍，多出的部分将作为底金滚入下一期抽奖</li>';
			// }
		} elseif ($row ["ticket_max_win_x"] == 0) {
			$current_info = $current_info . '<li>单注奖金无上限，本期开奖后奖金池底金将清空</li>';
		}
		
		$user_tickets = '';
		$user_res = sql_query ( "SELECT id, luckydraw_id, user_id, ticket_id, ticket_time FROM app_luckydraw_players WHERE luckydraw_id = " . $row ["id"] . " AND user_id = " . sqlesc ( $CURUSER ["id"] ) . " ORDER BY ticket_id ASC" ) or sqlerr ( __FILE__, __LINE__ );
		$user_buyed = 0;
		while ( $t_row = mysql_fetch_assoc ( $user_res ) ) {
			if ($user_tickets) {
				$user_tickets = $user_tickets . ', <b>' . $t_row ["ticket_id"] . '</b>';
			} else {
				$user_tickets = $user_tickets . '<b>' . $t_row ["ticket_id"] . '</b>';
			}
			$user_buyed = $user_buyed + 1;
		}
		if ($user_tickets) {
			$current_info = $current_info . '<li>您目前持有的彩券号为：' . $user_tickets . '</li>';
		}
		
		$current_info = $current_info . '</ul>';
		print ('<tr><td class="text" align="left">' . $current_info . '</td></tr>') ;
		print ('</table><br/><br/>') ;
		
		print ('<form action="?action=dobuytickets" method="post">') ;
		print ('<table align="center" width="400px" border="0" cellspacing="0" cellpadding="0">') ;
		print ('<tr><td class="text" align="left">奖金池</td><td class="rowfollow" align="right">' . number_format ( $row ["ticket_total"] * $row ["ticket_price"], 1 ) . ' + ' . number_format ( $row ["bonuspool"], 1 ) . ' 个魔力值</td></tr>') ;
		print ('<tr><td class="text" align="left">目前售出的彩券数</td><td class="rowfollow" align="right">' . $row ["ticket_total"] . ' 张</td></tr>') ;
		print ('<tr><td class="text" align="left">您已经购买</td><td class="rowfollow" align="right">' . $user_buyed . ' 张</td></tr>') ;
		print ('<tr><td class="text" align="left">您还能购买</td><td class="rowfollow" align="right">' . (0 + $row ["user_max"] - $user_buyed) . ' 张</td></tr>') ;
		print ('<tr><td class="text" align="center" colspan="2">') ;
		print ('<input type="text" style="width:50px" name="ticketnumbers" id="ticketnumbers" value="0" />&nbsp;<input type="submit" name="submit" value="购买" />') ;
		print ('</td></tr>') ;
		print ('<script type="text/javascript">
$(function(){
	$("#ticketnumbers").spinner({min: 0, max: ' . (0 + $row ["user_max"] - $user_buyed) . ', step: 1});
});
</script>') ;
		print ('</table><br/>') ;
		print ('</form>') ;
	}
} elseif ($action == "dobuytickets") { // 执行购买彩券
	$ticketnumbers = 0 + $_POST ["ticketnumbers"];
	
	$res = sql_query ( "SELECT id, time_start, time_until, ticket_price, ticket_tax_rate, ticket_max_win_x, ticket_win, user_max, winners_max, ticket_total, bonuspool FROM app_luckydraw WHERE status = '1' AND time_until > " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . " ORDER BY id DESC limit 1" ) or sqlerr ( __FILE__, __LINE__ );
	$row = mysql_fetch_assoc ( $res );
	if (! $row) {
		print ('<h1 align="center">当期没有彩券可买<br /><br /><a href="./app_luckydraw.php">返回</a></h1>') ;
	} else {
		$user_res = get_row_count ( "app_luckydraw_players", "WHERE luckydraw_id = " . $row ["id"] . " AND user_id = " . sqlesc ( $CURUSER ["id"] ) );
		if ($ticketnumbers <= 0) {
			print ('<h1 align="center">请输入有效的购买数量！本期每个人最多购买' . $row ['user_max'] . '张彩券。<br /><br /><a href="./app_luckydraw.php">返回</a></h1>') ;
		} elseif ($user_res + $ticketnumbers > $row ['user_max']) {
			print ('<h1 align="center">想作弊？没门！本期每个人最多购买' . $row ['user_max'] . '张彩券。<br /><br /><a href="./app_luckydraw.php">返回</a></h1>') ;
		} elseif ($CURUSER ['seedbonus'] < $ticketnumbers * $row ['ticket_price']) {
			print ('<h1 align="center">想作弊？没门！魔力值不足！<br /><br /><a href="./app_luckydraw.php">返回</a></h1>') ;
		} else {
			$newtickettotal = $row ["ticket_total"] + $ticketnumbers;
			if ($row ['winners_max'] > 0) {
				if ($row ["winners_max"] >= $newtickettotal) {
					$newticketwin = round ( ($row ["bonuspool"] + $newtickettotal * $row ['ticket_price'] * (1 - $row ['ticket_tax_rate'])) / $newtickettotal, 1 );
				} else {
					$newticketwin = round ( ($row ["bonuspool"] + $newtickettotal * $row ['ticket_price'] * (1 - $row ['ticket_tax_rate'])) / $row ['winners_max'], 1 );
				}
			} else {
				$newticketwin = round ( ($row ["bonuspool"] + $newtickettotal * $row ['ticket_price'] * (1 - $row ['ticket_tax_rate'])), 1 );
			}
			$pay_bouns = $ticketnumbers * $row ['ticket_price'];
			
			if ($row ['ticket_max_win_x'] > 0) {
				if ($newticketwin >= $row ['ticket_max_win_x'] * $row ['ticket_price']) {
					$newticketwin = $row ['ticket_max_win_x'] * $row ['ticket_price'];
				}
			}
			
			sql_query ( "UPDATE app_luckydraw SET ticket_total = " . sqlesc ( $newtickettotal ) . ", ticket_win = " . sqlesc ( $newticketwin ) . "  WHERE id=" . $row ["id"] ) or sqlerr ( __FILE__, __LINE__ );
			
			for($i = 1; $i <= $ticketnumbers; $i ++) {
				sql_query ( 'INSERT INTO app_luckydraw_players (luckydraw_id, user_id, ticket_id, ticket_time) VALUES (' . $row ["id"] . ', ' . sqlesc ( $CURUSER ["id"] ) . ', ' . sqlesc ( $row ["ticket_total"] + $i ) . ', ' . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ')' ) or sqlerr ( __FILE__, __LINE__ );
			}
			
			sql_query ( "UPDATE users SET seedbonus = seedbonus - " . sqlesc ( $pay_bouns ) . " WHERE id = " . sqlesc ( $CURUSER ["id"] ) ) or sqlerr ( __FILE__, __LINE__ );
			
			print ('<h1 align="center">成功购买了' . $ticketnumbers . '张彩券！本期每个人最多购买' . $row ['user_max'] . '张彩券。<br /><br /><a href="./app_luckydraw.php">返回</a></h1>') ;
		}
	}
} elseif ($action == "manage") { // 管理
	if (get_user_class () < UC_ADMINISTRATOR) {
		stderr ( $lang_app_luckydraw ['std_error'], $lang_app_luckydraw ['std_invalid_action'], false );
	}
	print ('<table align="center" width="80%" border="0" cellspacing="0" cellpadding="0">') ;
	print ('<tr><td class="colhead" align="left" colspan="8">' . '尚未开始的抽奖' . '</td></tr>') ;
	print ('<tr><td class="colhead" align="center">序号</td><td class="colhead" align="center">开始时间</td><td class="colhead" align="center">结束时间</td><td class="colhead" align="center">单价</td><td class="colhead" align="center">限购</td><td class="colhead" align="center">中奖人数</td><td class="colhead" align="center">添加人</td><td class="colhead" align="center">操作</td></tr>') ;
	
	$res = sql_query ( "SELECT id, time_start, time_until, ticket_price, user_max, winners_max, addby FROM app_luckydraw WHERE status = '0' ORDER BY id ASC" ) or sqlerr ( __FILE__, __LINE__ );
	while ( $row = mysql_fetch_assoc ( $res ) ) {
		print ('<tr>') ;
		print ('<td class="text" align="center">' . $row ['id'] . '</td>') ;
		print ('<td class="text" align="center">' . $row ['time_start'] . '</td>') ;
		print ('<td class="text" align="center">' . $row ['time_until'] . '</td>') ;
		print ('<td class="text" align="center">' . $row ['ticket_price'] . '</td>') ;
		print ('<td class="text" align="center">' . $row ['user_max'] . '</td>') ;
		print ('<td class="text" align="center">' . $row ['winners_max'] . '</td>') ;
		print ('<td class="text" align="center">' . get_username ( $row ['addby'] ) . '</td>') ;
		print ('<td class="text" align="center">' . '<a href="?action=deletedraw&id=' . $row ['id'] . '">删除</a>' . '</td>') ;
		print ('</tr>') ;
	}
	print ('</table><br/><br/>') ;
	
	print ('<table align="center" width="60%" border="0" cellspacing="0" cellpadding="0">') ;
	print ('<tr><td class="colhead" align="left">' . '添加新的抽奖' . '</td></tr>') ;
	print ('<tr>') ;
	print ('<form action="?action=addnext" method="post">') ;
	print ('<table align="center" width="60%" border="0" cellspacing="0" cellpadding="0">') ;
	
	$myinput = '<b>开始时间:&nbsp;</b><input type="text" name="timestart" id="timestart" value="' . date ( "Y-m-d H:i:s" ) . '" />';
	print ('<tr><td class="rowfollow" align="left">' . $myinput . '</td></tr>') ;
	$myinput = '<b>结束时间:&nbsp;</b><input type="text" name="timeuntil" id="timeuntil" value="' . date ( "Y-m-d H:i:s", time () + 24 * 3600 ) . '" />';
	print ('<tr><td class="rowfollow" align="left">' . $myinput . '</td></tr>') ;
	$myinput = '<b>彩券单价:&nbsp;</b><input type="text" style="width:70px" name="ticketprice" id="ticketprice" value="500" />&nbsp;个魔力值';
	print ('<tr><td class="rowfollow" align="left">' . $myinput . '</td></tr>') ;
	$myinput = '<b>每个用户最多购买&nbsp;</b><input type="text" style="width:50px" name="usermax" id="usermax" value="20" />&nbsp;张彩券';
	print ('<tr><td class="rowfollow" align="left">' . $myinput . '</td></tr>') ;
	$myinput = '<b>中奖人数:&nbsp;</b><input type="text" style="width:50px" name="winnermax" id="winnermax" value="10" />';
	print ('<tr><td class="rowfollow" align="left">' . $myinput . '</td></tr>') ;
	$myinput = '&nbsp;&nbsp;<input type="submit" name="submit" value="添加" />';
	print ('<tr><td class="text" align="left">' . $myinput . '</td></tr>') ;
	print ('<script type="text/javascript">
$(function(){
	$("#timestart").datetimepicker({dateFormat: "yy-mm-dd", showSecond: true, timeFormat:"hh:mm:ss", minDate: new Date()});
	$("#timeuntil").datetimepicker({dateFormat: "yy-mm-dd", showSecond: true, timeFormat:"hh:mm:ss", minDate: new Date()});
	$("#ticketprice").spinner({min: 100, max: 100000, step: 50});
	$("#usermax").spinner({min: 1, max: 1000, step: 1});
	$("#winnermax").spinner({min: 1, max: 20, step: 1});
});
</script>') ;
	print ('</table>') ;
	print ('</form>') ;
	print ('</tr>') ;
	print ('</table><br/>') ;
} elseif ($action == "addnext") { // 添加下一期
	if (get_user_class () < UC_ADMINISTRATOR) {
		stderr ( $lang_app_luckydraw ['std_error'], $lang_app_luckydraw ['std_invalid_action'], false );
	}
	
	$time_start = $_POST ["timestart"];
	$time_until = $_POST ["timeuntil"];
	$ticket_price = 0 + $_POST ["ticketprice"];
	$user_max = 0 + $_POST ["usermax"];
	$winner_max = 0 + $_POST ["winnermax"];
	if (strtotime ( $time_until ) <= strtotime ( $time_start )) {
		print ('<h1 align="center">结束时间必须大于开始时间<br /><br /><a href="?action=manage">返回重填</a></h1>') ;
	} elseif ($ticket_price <= 0 || $user_max <= 0 || $winner_max <= 0) {
		print ('<h1 align="center">单价、限购或中奖人数填写不正确<br /><br /><a href="?action=manage">返回重填</a></h1>') ;
	} else {
		$time_start = sqlesc ( $time_start );
		$time_until = sqlesc ( $time_until );
		$ticket_price = sqlesc ( $ticket_price );
		$user_max = sqlesc ( $user_max );
		$winner_max = sqlesc ( $winner_max );
		
		$res = sql_query ( "SELECT id, time_start, time_until FROM app_luckydraw WHERE status < '2' AND time_until >= " . $time_start . " AND time_start <= " . $time_until ) or sqlerr ( __FILE__, __LINE__ );
		$row = mysql_fetch_assoc ( $res );
		if ($row) {
			print ('<h1 align="center">同一时间最多只能安排一期抽奖<br /><br /><a href="?action=manage">返回重填</a></h1>') ;
		} else {
			sql_query ( 'INSERT INTO app_luckydraw (time_start, time_until, ticket_price, user_max, winners_max, addby) VALUES (' . $time_start . ', ' . $time_until . ', ' . $ticket_price . ', ' . $user_max . ', ' . $winner_max . ', ' . sqlesc ( $CURUSER ["id"] ) . ')' ) or sqlerr ( __FILE__, __LINE__ );
			print ('<h1 align="center">添加成功<br /><br /><a href="?action=manage">继续添加</a></h1>') ;
		}
	}
} elseif ($action == "deletedraw") { // 刪除未开始的
	if (get_user_class () < UC_ADMINISTRATOR) {
		stderr ( $lang_app_luckydraw ['std_error'], $lang_app_luckydraw ['std_invalid_action'], false );
	}
	
	$id_del = 0 + $_GET ['id'];
	$res = sql_query ( "SELECT id, time_until FROM app_luckydraw WHERE id=" . sqlesc ( $id_del ) . " AND status='0'" ) or sqlerr ( __FILE__, __LINE__ );
	$row = mysql_fetch_assoc ( $res );
	if ($row) {
		sql_query ( "DELETE FROM app_luckydraw WHERE id=" . sqlesc ( $row ['id'] ) . " AND status='0'" ) or sqlerr ( __FILE__, __LINE__ );
		print ('<h1 align="center">删除成功<br /><br /><a href="?action=manage">返回</a></h1>') ;
	} else {
		print ('<h1 align="center">删除失败，只能删除尚未开始的抽奖<br /><br /><a href="?action=manage">返回</a></h1>') ;
	}
} else {
	print ('<h1 align="center">这谁家熊孩子啊，别到处乱跑了，<br /><br /><a href="./index.php">回家</a>吃饭去吧！</h1>') ;
}

stdfoot ();
?>
