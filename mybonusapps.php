<?php
require_once ('include/bittorrent.php');
dbconn ();
require_once (get_langfile_path ());
loggedinorreturn ();
parked ();
function bonusarray($option) {
	global $lang_mybonusapps;
	global $Cache;
	$bonus = array ();
	switch ($option) {
		case 1 :
			{ // 碰运气
				$bonus ['points'] = 1000;
				$bonus ['art'] = 'luck';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonusapps ['text_luck'];
				$bonus ['description'] = $lang_mybonusapps ['text_luck_note'];
				break;
			}
		case 2 :
			{ //
				$bonus ['points'] = 100;
				$bonus ['art'] = 'enableaccount';
				$bonus ['menge'] = 0;
				$bonus ['name'] = "21点";
				$bonus ['description'] = "传统的21点游戏,您要抓足够接近21点，和对手对抗。<br />A在总分不超过21时作11，总分超过21则作1。J,Q,K作为10。";
				break;
			}
		case 3 :
			{ // 幸运抽奖
				$bonus ['points'] = 0;
				$bonus ['art'] = 'luckydraw';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonusapps ['text_luckydraw'];
				$bonus ['description'] = $lang_mybonusapps ['text_luckdraw_note'];
				break;
			}
		case 4 :
			{ // 竞猜大厅
				$bonus ['points'] = 0;
				$bonus ['art'] = 'quiz';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonusapps ['text_quiz'];
				$bonus ['description'] = $lang_mybonusapps ['text_quiz_note'];
				break;
			}
		case 5 :
			{ // 道具中心
				$bonus ['points'] = 0;
				$bonus ['art'] = 'items';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonusapps ['text_items'];
				$bonus ['description'] = $lang_mybonusapps ['text_items_note'];
				break;
			}
		
		default :
			break;
	}
	return $bonus;
}

if ($bonus_tweak == "disable" || $bonus_tweak == "disablesave")
	stderr ( $lang_mybonusapps ['std_sorry'], $lang_mybonusapps ['std_karma_system_disabled'] . ($bonus_tweak == "disablesave" ? "<b>" . $lang_mybonusapps ['std_points_active'] . "</b>" : ""), false );

$allowed_actions = array (
		"default",
		"viewluck",
		"viewluck2",
		"exchange"
);

$action = htmlspecialchars ( $_GET ['action'] );
if (! $action) {
	$action = 'default';
}
if (! in_array ( $action, $allowed_actions )) {
	stderr ( $lang_mybonusapps ['std_invalid_error'], $lang_mybonusapps ['std_invalid_action'] );
}

$do = htmlspecialchars ( $_GET ['do'] );
unset ( $msg );
if (isset ( $do )) {
	$msg = '';
}

stdhead ( $CURUSER ['username'] . $lang_mybonusapps ['head_karma_page'] );

$bonus = number_format ( $CURUSER ['seedbonus'], 1 );
if ($action == "default") {
	print ("<table align=\"center\" width=\"940\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n") ;
	print ("<tr><td class=\"colhead\" colspan=\"4\" align=\"center\"><font class=\"big\">" . $SITENAME . $lang_mybonusapps ['text_karma_system'] . "</font></td></tr>\n") ;
	if ($msg)
		print ("<tr><td align=\"center\" colspan=\"4\"><font class=\"striking\">" . $msg . "</font></td></tr>") ;
	?>
<tr>
	<td class="text" align="center" colspan="4"><?php echo $lang_mybonusapps['text_exchange_your_karma']?><?php echo $bonus?><?php echo $lang_mybonusapps['text_for_goodies']?>
<br /> <b><?php echo $lang_mybonusapps['text_no_buttons_note'] ?></b></td>
</tr>
<?php
	
	print ("<tr><td class=\"colhead\" align=\"center\">" . $lang_mybonusapps ['col_option'] . "</td>" . "<td class=\"colhead\" align=\"left\">" . $lang_mybonusapps ['col_description'] . "</td>" . "<td class=\"colhead\" align=\"center\">" . $lang_mybonusapps ['col_points'] . "</td>" . "<td class=\"colhead\" align=\"center\">" . $lang_mybonusapps ['col_trade'] . "</td>" . "</tr>") ;
	
	for($i = 1; $i < 6; $i ++) {
		$bonusarray = bonusarray ( $i );
		print ("<tr>") ;
		print ("<form action=\"?action=exchange\" method=\"post\">") ;
		print ("<td class=\"rowhead_center\"><input type=\"hidden\" name=\"option\" value=\"" . $i . "\" /><b>" . $i . "</b></td>") ;
		if ($i == 1) { // 碰运气
			$otheroption = "<b>" . $lang_mybonusapps ['text_to_be_play'] . "</b><input type=\"text\" name=\"luckbonus\" id=\"luckbonus\" style=\"width: 80px\" value=\"1000\" />" . $lang_mybonusapps ['text_karma_points'];
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "</br></br>" . $otheroption . "</td><td class=\"rowfollow nowrap\" align='center'>" . $lang_mybonusapps ['text_min'] . "10<br />" . $lang_mybonusapps ['text_max'] . "1,000</td>") ;
		} elseif ($i == 2) { // 21点
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "<br /><br /></td><td class=\"rowfollow nowrap\" align='center'>每局100</td>") ;
		} elseif ($i == 3) { // 幸运抽奖
			$otheroption = '';
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "</br></br>" . $otheroption . "</td><td class=\"rowfollow nowrap\" align='center'>" . $lang_mybonusapps ['text_luckydraw_points'] . "</td>") ;
		} elseif ($i == 4) { // 竞猜大厅
			$otheroption = '';
			print ("<td class=\"rowfollow\" align='left'><h1><font color='red'>" . $bonusarray ['name'] . "</font></h1>" . $bonusarray ['description'] . "</br></br>" . $otheroption . "</td><td class=\"rowfollow nowrap\" align='center'>" . $lang_mybonusapps ['text_quiz_points'] . "</td>") ;
		} elseif ($i == 5) { // 道具中心
			$otheroption = '';
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "</br></br>" . $otheroption . "</td><td class=\"rowfollow nowrap\" align='center'>" . $lang_mybonusapps ['text_items_points'] . "</td>") ;
		}
		
		if ($CURUSER ['seedbonus'] >= $bonusarray ['points']) {
			if ($i == 1) {
				if ($Cache->get_value ( 'app_luck_' . $CURUSER ['id'] ) != '')
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" disabled=\"true\" value=\"" . $lang_mybonusapps ['submit_karma_luck'] . "\" /><br />上次时间：<br />" . $Cache->get_value ( 'app_luck_' . $CURUSER ['id'] ) . "</td>") ;
				else
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonusapps ['submit_karma_luck'] . "\" /></td>") ;
			} elseif ($i == 2) {
				print ("<td class=\"rowfollow\" align=\"center\"><input type=button value=\"" . ($CURUSER ["class"] < UC_USER ? "User" . $lang_mybonus ['text_plus_only'] . "\" disabled" : ($CURUSER ['seedbonus'] < 100 ? $lang_mybonus ['text_more_points_needed'] . "\" disabled" : "我要玩\" onclick=\"location.href='blackjack.php'\"")) . "/></td>") ;
			} elseif ($i == 3) {
				if (get_user_class () >= UC_POWER_USER) {
					$intd = '<input type="button" name="gotoluckydraw" value="' . $lang_mybonusapps ['text_luckydraw_goto'] . '" onclick="window.location=\'app_luckydraw.php\';"/>';
				} else {
					$intd = '<input type="button" name="gotoluckydraw" disabled="true" value="' . $lang_mybonusapps ['text_class_too_low'] . '" />';
				}
				print ("<td class=\"rowfollow\" align=\"center\">" . $intd . "</td>") ;
			} elseif ($i == 4) {
				$intd = '<input type="button" name="gotoquiz" value="' . $lang_mybonusapps ['text_quiz_goto'] . '" onclick="window.location=\'jc_currentbet_L.php\';"/>';
				print ("<td class=\"rowfollow\" align=\"center\">" . $intd . "</td>") ;
			} elseif ($i == 5) {
				$intd = '<input type="button" name="gotoitems" value="' . $lang_mybonusapps ['text_items_goto'] . '" onclick="window.location=\'app_items.php\';"/>';
				$intd = '<input type="button" name="gotoitems" disabled="true" value="' . $lang_mybonusapps ['text_unavailable'] . '" />';
				print ("<td class=\"rowfollow\" align=\"center\">" . $intd . "</td>") ;
			}
		} else {
			print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonusapps ['text_more_points_needed'] . "\" disabled=\"disabled\" /></td>") ;
		}
		print ("</form>") ;
		print ("</tr>") ;
	}
	
	print ("</table><br />") ;
	
	print ('<script type="text/javascript">$(function(){
				$("#luckbonus").spinner({
				    min: 10,
				    max: 1000,
				    step: 0.1  
				});				
			});
			</script>') ;
}

if ($action == "viewluck") {
	$lucklog = $Cache->get_value ( 'app_luck_log' );
	print ("<table width=100%>") ;
	
	if ($lucklog == "") {
		print ("<tr><td class=\"rowfollow\" align=\"left\"><h1>北洋媛很伤心，最近没有人来碰运气，呜呜呜……</h1></td></tr>") ;
	} else {
		$lucklog = json_decode ( $lucklog );
		$lucklog = array_reverse ( $lucklog );
		print ("<tr><td class=\"rowfollow\" align=\"left\">") ;
		print ("<h1>北洋媛回忆起了" . count ( $lucklog ) . "条玩家碰运气的信息</h1>") ;
		print ("</td></tr>") ;
		foreach ( $lucklog as $log ) {
			print ("<tr><td height=30 class=\"rowfollow\" align=\"left\">") ;
			print ($log . "<br />") ;
			print ("</td></tr>") ;
		}
	}
	
	print ("<tr><td class=\"rowfollow\" align=\"left\"></br>" . $lang_mybonusapps ['text_return_apps'] . "</td></tr>") ;
	print ("</table>") ;
} elseif ($action == "viewluck2") {
	$lucklog2 = $Cache->get_value ( 'app_luck_log2' );
	// var_dump ( $lucklog2 );
	if ($lucklog2 == "") {
		print ("<tr><td class=\"rowfollow\" align=\"left\"><h1>北洋媛很伤心，最近没有人来碰运气，呜呜呜……</h1></td></tr>") ;
	} else {
		require_once ('HighRoller/HighRoller.php');
		require_once ('HighRoller/HighRollerSeriesData.php');
		require_once ('HighRoller/HighRollerLineChart.php');
		require_once ('HighRoller/HighRollerScatterChart.php');
		
		$lucklog2 = json_decode ( $lucklog2 );
		
		$chartData_time = array ();
		$chartData_user = array ();
		$chartData_bpay = array ();
		$chartData_bget = array ();
		$chartData_gain = array ();
		$chartData_rate = array ();
		$chartData_xy = array ();
		foreach ( $lucklog2 as $log2 ) {
			$arr2 = explode ( ",", $log2 );
			$chartData_time [] = $arr2 [0];
			$chartData_user [] = $arr2 [1];
			$chartData_bpay [] = array (
					strtotime ( $arr2 [0] ) * 1000,
					$arr2 [2] / 100 
			);
			$chartData_bget [] = array (
					strtotime ( $arr2 [0] ) * 1000,
					($arr2 [2] + $arr2 [3]) / 100 
			);
			$chartData_gain [] = array (
					strtotime ( $arr2 [0] ) * 1000,
					$arr2 [3] / 100 
			);
			$chartData_rate [] = array (
					strtotime ( $arr2 [0] ) * 1000,
					round ( $arr2 [3] / $arr2 [2], 3 ) 
			);
			$chartData_xy [] = array (
					$arr2 [2] / 100,
					$arr2 [3] / 100 
			);
		}
		
		echo HighRoller::setHighChartsLocation ( "js/highcharts/highcharts.js" );
		$linechart1 = new HighRollerLineChart ();
		$linechart1->chart->renderTo = 'linechart';
		$linechart1->chart->type = 'spline';
		$linechart1->chart->zoomType = 'x';
		$linechart1->title->text = '收益率时间序列图';
		$linechart1->xAxis->type = 'datetime';
		$linechart1->xAxis->title->text = null;
		$linechart1->yAxis->title->text = '收益率';
		$linechart1->plotOptions->series->type = 'area';
		$linechart1->plotOptions->series->name = '收益率';
		
		$series11 = new HighRollerSeriesData ();
		$series11->addName ( '收益率' )->addData ( $chartData_rate );
		
		$linechart1->addSeries ( $series11 );
		print ('<h1>北洋媛碰运气盈亏情况统计</h1>') ;
		print ('<div id="linechart"></div>') ;
		print ('<script type="text/javascript">' . $linechart1->renderChart () . '</script>') ;
		
		print ("<br /><br />") ;
		
		$linechart2 = new HighRollerLineChart ();
		$linechart2->chart->renderTo = 'linechart2';
		$linechart2->chart->type = 'spline';
		$linechart2->chart->zoomType = 'x';
		$linechart2->title->text = '魔力值收支情况';
		$linechart2->xAxis->type = 'datetime';
		$linechart2->xAxis->title->text = null;
		$linechart2->yAxis->title->text = '魔力值（百个）';
		$linechart2->plotOptions->series->type = 'area';
		$linechart2->plotOptions->series->name = '魔力值';
		$linechart2->tooltip->crosshairs = true;
		$linechart2->tooltip->shared = true;
		
		$series21 = new HighRollerSeriesData ();
		$series21->addName ( '投入魔力值' )->addData ( $chartData_bpay );
		$series22 = new HighRollerSeriesData ();
		$series22->addName ( '收获魔力值' )->addData ( $chartData_bget );
		$series23 = new HighRollerSeriesData ();
		$series23->addName ( '净赚魔力值' )->addData ( $chartData_gain );
		
		$linechart2->addSeries ( $series21 );
		$linechart2->addSeries ( $series22 );
		$linechart2->addSeries ( $series23 );
		print ('<div id="linechart2"></div>') ;
		print ('<script type="text/javascript">' . $linechart2->renderChart () . '</script>') ;
		
		print ("<br /><br />") ;
		
		$scatterchart = new HighRollerScatterChart ();
		$scatterchart->chart->renderTo = 'scatterchart';
		$scatterchart->chart->type = 'scatter';
		$scatterchart->chart->zoomType = 'xy';
		$scatterchart->title->text = '魔力值收支散点图';
		$scatterchart->xAxis->title->text = '投入魔力值（百个）';
		$scatterchart->yAxis->title->text = '净赚魔力值（百个）';
		
		$series31 = new HighRollerSeriesData ();
		$series31->addName ( '魔力值收支' )->addData ( $chartData_xy );
		$scatterchart->addSeries ( $series31 );
		print ('<div id="scatterchart"></div>') ;
		print ('<script type="text/javascript">' . $scatterchart->renderChart () . '</script>') ;
	}
}

// Bonus exchange
if ($action == "exchange") {
	if ($_POST ["userid"] || $_POST ["points"] || $_POST ["bonus"] || $_POST ["art"]) {
		write_log ( "User " . $CURUSER ["username"] . "," . $CURUSER ["ip"] . " is trying to cheat at bonus system", 'mod' );
		die ( $lang_mybonusapps ['text_cheat_alert'] );
	}
	$option = ( int ) $_POST ["option"];
	$bonusarray = bonusarray ( $option );
	
	$points = $bonusarray ['points'];
	$userid = $CURUSER ['id'];
	$art = $bonusarray ['art'];
	
	$bonuscomment = $CURUSER ['bonuscomment'];
	$seedbonus = $CURUSER ['seedbonus'] - $points;
	
	if ($CURUSER ['seedbonus'] >= $points) {
		// === trade for upload
		if ($art == "luck") {
			if ($Cache->get_value ( 'app_luck_' . $CURUSER ['id'] ) != '') {
				stdmsg ( $lang_mybonusapps ['text_error'], $lang_mybonusapps ['text_cheat_alert'] );
				stdfoot ();
				die ();
			}
			$luckbonus = 0 + $_POST ['luckbonus'];
			if ($luckbonus < 10 || $luckbonus > 1000) {
				stdmsg ( $lang_mybonusapps ['text_error'], $lang_mybonusapps ['bonus_amount_not_allowed'] );
				stdfoot ();
				die ();
			}
			$luckbonus = round ( $luckbonus, 1 );
			$retluckbonus = mt_rand ( 0, $luckbonus * 2 );
			if ($retluckbonus > 1.618 * $luckbonus || $retluckbonus < (1 - 0.618) * $luckbonus) {
				$retluckbonus2 = mt_rand ( 0, $luckbonus * 2 );
				if ($retluckbonus2 > 1.618 * $luckbonus || $retluckbonus2 < (1 - 0.618) * $luckbonus) {
					$luckalpha = mt_rand ( 0, 10 ) / 10;
					$retluckbonus = $luckalpha * $retluckbonus + (1 - $luckalpha) * $retluckbonus2;
				} else {
					$retluckbonus = $retluckbonus2;
				}
			}
			$sqlluckbonus = round($retluckbonus - $luckbonus, 1);
			
			sql_query ( "UPDATE users SET seedbonus = seedbonus + $sqlluckbonus WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
			if ($sqlluckbonus > 0)
				$retinfo = "恭喜" . $CURUSER ['username'] . "<b><font color=red>获得了" . $sqlluckbonus . "个</font></b>魔力值";
			elseif ($sqlluckbonus == 0)
				$retinfo = $CURUSER ['username'] . "既没有得到也没有失去魔力值";
			else
				$retinfo = "很遗憾，" . $CURUSER ['username'] . "<b><font color=green>失去了" . abs ( $sqlluckbonus ) . "个</font></b>魔力值";
			$message = $CURUSER ['username'] . "使用了<b>" . $luckbonus . "</b>" . $lang_mybonusapps ['text_point'] . "，获得了<b>" . $retluckbonus . "</b>" . $lang_mybonusapps ['text_point'] . "，" . $retinfo;
			stdmsg ( $lang_mybonusapps ['text_success'], $message . "</br></br>" . $lang_mybonusapps ['text_return_apps'] );
			$date = date ( "Y-m-d H:i:s" );
			$Cache->cache_value ( 'app_luck_' . $CURUSER ['id'], $date, 600 );
			// 碰运气记录开始
			$lucklog = json_decode ( $Cache->get_value ( 'app_luck_log' ) );
			$lucklog [] = $date . " " . $message;
			if (count ( $lucklog ))
				$lucklog = array_slice ( $lucklog, - 50 );
			$Cache->cache_value ( 'app_luck_log', json_encode ( $lucklog ), 36000 );
			
			$lucklog2 = json_decode ( $Cache->get_value ( 'app_luck_log2' ) );
			$lucklog2 [] = $date . "," . $CURUSER ['username'] . "," . $luckbonus . "," . $sqlluckbonus;
			if (count ( $lucklog2 ))
				$lucklog2 = array_slice ( $lucklog2, - 256 );
			$Cache->cache_value ( 'app_luck_log2', json_encode ( $lucklog2 ), 86400 );
			// 碰运气记录结束
			// 碰运气结果写入数据库
			sql_query ( 'INSERT INTO app_tryluck (trytime, userid, bonus_pay, bonus_gain) VALUES (' . sqlesc ( $date ) . ', ' . sqlesc ( $CURUSER ["id"] ) . ', ' . sqlesc ( $luckbonus ) . ', ' . sqlesc ( $sqlluckbonus ) . ')' ) or sqlerr ( __FILE__, __LINE__ );
			
			stdfoot ();
			die ();
		}
	}
}

stdfoot ();
?>
