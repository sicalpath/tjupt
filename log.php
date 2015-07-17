<?php
require "include/bittorrent.php";
dbconn ();
require_once (get_langfile_path ());
loggedinorreturn ();
if (get_user_class () < $log_class) {
	stderr ( $lang_log ['std_sorry'], $lang_log ['std_permission_denied_only'] . get_user_class_name ( $log_class, false, true, true ) . $lang_log ['std_or_above_can_view'], false );
}
function permissiondeny() {
	global $lang_log;
	stderr ( $lang_log ['std_sorry'], $lang_log ['std_permission_denied'], false );
}
function logmenu($selected = "dailylog") {
	global $lang_log;
	global $showfunbox_main;
	begin_main_frame ();
	print ("<div id=\"lognav\"><ul id=\"logmenu\" class=\"menu\">") ;
	print ("<li" . ($selected == "dailylog" ? " class=selected" : "") . "><a href=\"log.php?action=dailylog\">" . $lang_log ['text_daily_log'] . "</a></li>") ;
	print ("<li" . ($selected == "chartlog" ? " class=selected" : "") . "><a href=\"log.php?action=chartlog\">" . "&nbsp;图&nbsp;形&nbsp;日&nbsp;志&nbsp;" . "</a></li>") ;
	print ("<li" . ($selected == "chronicle" ? " class=selected" : "") . "><a href=\"log.php?action=chronicle\">" . $lang_log ['text_chronicle'] . "</a></li>") ;
	if ($showfunbox_main == 'yes')
		print ("<li" . ($selected == "funbox" ? " class=selected" : "") . "><a href=\"log.php?action=funbox\">" . $lang_log ['text_funbox'] . "</a></li>") ;
	print ("<li" . ($selected == "news" ? " class=selected" : "") . "><a href=\"log.php?action=news\">" . $lang_log ['text_news'] . "</a></li>") ;
	print ("<li" . ($selected == "poll" ? " class=selected" : "") . "><a href=\"log.php?action=poll\">" . $lang_log ['text_poll'] . "</a></li>") ;
	print ("<li" . ($selected == "administrator" ? " class=selected" : "") . "><a href=\"log.php?action=administrator\">" . $lang_log ['head_administrator'] . "</a></li>") ;
	print ("<li" . ($selected == "uploader" ? " class=selected" : "") . "><a href=\"log.php?action=uploader\">" . $lang_log ['head_uploader'] . "</a></li>") ;
	
	print ("</ul></div>") ;
	end_main_frame ();
}
function searchtable($title, $action, $opts = array()) {
	global $lang_log;
	print ("<table border=1 cellspacing=0 width=940 cellpadding=5>\n") ;
	print ("<tr><td class=colhead align=left>" . $title . "</td></tr>\n") ;
	print ("<tr><td class=toolbox align=left><form method=\"get\" action='" . $_SERVER ['PHP_SELF'] . "'>\n") ;
	print ("<input type=\"text\" name=\"query\" style=\"width:500px\" value=\"" . $_GET ['query'] . "\">\n") ;
	if ($opts) {
		print ($lang_log ['text_in'] . "<select name=search>") ;
		foreach ( $opts as $value => $text )
			print ("<option value='" . $value . "'" . ($value == $_GET ['search'] ? " selected" : "") . ">" . $text . "</option>") ;
		print ("</select>") ;
	}
	print ("<input type=\"hidden\" name=\"action\" value='" . $action . "'>&nbsp;&nbsp;") ;
	print ("<input type=submit value=" . $lang_log ['submit_search'] . "></form>\n") ;
	print ("</td></tr></table><br />\n") ;
}
function additem($title, $action) {
	global $lang_log;
	print ("<table border=1 cellspacing=0 width=940 cellpadding=5>\n") ;
	print ("<tr><td class=colhead align=left>" . $title . "</td></tr>\n") ;
	print ("<tr><td class=toolbox align=left><form method=\"post\" action='" . $_SERVER ['PHP_SELF'] . "'>\n") ;
	print ("<textarea name=\"txt\" style=\"width:500px\" rows=\"3\" >" . $row ["txt"] . "</textarea>\n") ;
	print ("<input type=\"hidden\" name=\"action\" value=" . $action . ">") ;
	print ("<input type=\"hidden\" name=\"do\" value=\"add\">") ;
	print ("<input type=submit value=" . $lang_log ['submit_add'] . "></form>\n") ;
	print ("</td></tr></table><br />\n") ;
}
function edititem($title, $action, $id) {
	global $lang_log;
	$result = sql_query ( "SELECT * FROM " . $action . " where id = " . sqlesc ( $id ) ) or sqlerr ( __FILE__, __LINE__ );
	if ($row = mysql_fetch_array ( $result )) {
		print ("<table border=1 cellspacing=0 width=940 cellpadding=5>\n") ;
		print ("<tr><td class=colhead align=left>" . $title . "</td></tr>\n") ;
		print ("<tr><td class=toolbox align=left><form method=\"post\" action='" . $_SERVER ['PHP_SELF'] . "'>\n") ;
		print ("<textarea name=\"txt\" style=\"width:500px\" rows=\"3\" >" . $row ["txt"] . "</textarea>\n") ;
		print ("<input type=\"hidden\" name=\"action\" value=" . $action . ">") ;
		print ("<input type=\"hidden\" name=\"do\" value=\"update\">") ;
		print ("<input type=\"hidden\" name=\"id\" value=" . $id . ">") ;
		print ("<input type=submit value=" . $lang_log ['submit_okay'] . " style='height: 20px' /></form>\n") ;
		print ("</td></tr></table><br />\n") ;
	}
}

// my codes
function searchadmin($title, $opts = array()) {
	global $lang_log;
	print ("<table border=1 cellspacing=0 width=940 cellpadding=5>\n") ;
	print ("<tr><td class=colhead align=left>" . $title . "</td></tr>\n") ;
	print ("<tr><td class=toolbox align=left><form method=\"get\" action='log_admins.php'>\n") ;
	print ("<select name=\"queryclass\" style=\"width:100px\" >\n") ;
	$modclass = array (
			12 => "发布员",
			13 => "类管理员",
			14 => "管理员",
			15 => "维护开发员",
			16 => "主管" 
	);
	foreach ( $modclass as $value => $name )
		print ("<option value=\"" . $value . "\"" . ($_GET ['queryclass'] == $value || ($value == 13 && ! isset ( $_GET ['queryclass'] )) ? " selected " : "") . ">" . $name . " </option>\n") ;
	print ("<select>") ;
	if ($opts) {
		print ($lang_log ['searchtype'] . "<select name=searchclass>") ;
		foreach ( $opts as $value => $text )
			print ("<option value='" . $value . "'" . ($value == $_GET ['searchclass'] ? " selected" : "") . ">" . $text . "</option>") ;
		print ("</select>") ;
	}
	
	print ("<input type=submit value=" . $lang_log ['submit_search'] . "></form>\n") ;
	print ("</td></tr></table><br />\n") ;
}
// end of my codes

$action = isset ( $_POST ['action'] ) ? htmlspecialchars ( $_POST ['action'] ) : (isset ( $_GET ['action'] ) ? htmlspecialchars ( $_GET ['action'] ) : '');
$allowed_actions = array (
		"dailylog",
		"chartlog",
		"chronicle",
		"funbox",
		"news",
		"poll",
		"administrator",
		"uploader" 
);
if (! $action)
	$action = 'dailylog';
if (! in_array ( $action, $allowed_actions ))
	stderr ( $lang_log ['std_error'], $lang_log ['std_invalid_action'] );
else {
	switch ($action) {
		case "dailylog" :
			stdhead ( $lang_log ['head_site_log'] );
			
			$query = mysql_real_escape_string ( trim ( $_GET ["query"] ) );
			$from = mysql_real_escape_string ( trim ( $_GET ["from"] ) );
			$to = mysql_real_escape_string ( trim ( $_GET ["to"] ) );
			if (is_numeric ( strtotime ( $from ) ))
				$from = strtotime ( $from );
			if (is_numeric ( strtotime ( $to ) ))
				$to = strtotime ( $to );
			if (is_numeric ( strtotime ( $from ) ))
				$from = strtotime ( $from );
			if (is_numeric ( strtotime ( $to ) ))
				$to = strtotime ( $to );
			
			$search = $_GET ["search"];
			
			$addparam = "";
			$wherea = "";
			if (get_user_class () >= $confilog_class) {
				switch ($search) {
					case "mod" :
						$wherea = " WHERE security_level = 'mod'";
						break;
					case "normal" :
						$wherea = " WHERE security_level = 'normal'";
						break;
					case "all" :
						break;
				}
				$addparam = ($wherea ? "search=" . rawurlencode ( $search ) . "&" : "");
			} else {
				$wherea = " WHERE security_level = 'normal'";
			}
			
			if ($query) {
				$wherea .= ($wherea ? " AND " : " WHERE ") . " txt LIKE '%$query%' ";
				$addparam .= "query=" . rawurlencode ( $query ) . "&";
			}
			if (is_numeric ( $from )) {
				$wherea .= ($wherea ? " AND " : " WHERE ") . " added >= " . sqlesc ( date ( "Y-m-d H:i:s", $from ) ) . " ";
				$addparam .= "from=" . rawurlencode ( $from ) . "&";
			}
			if (is_numeric ( $to )) {
				$wherea .= ($wherea ? " AND " : " WHERE ") . " added < " . sqlesc ( date ( "Y-m-d H:i:s", $to ) ) . " ";
				$addparam .= "to=" . rawurlencode ( $to ) . "&";
			}
			
			logmenu ( 'dailylog' );
			$opt = array (
					all => $lang_log ['text_all'],
					normal => $lang_log ['text_normal'],
					mod => $lang_log ['text_mod'] 
			);
			searchtable ( $lang_log ['text_search_log'], 'dailylog', $opt );
			
			// my codes
			if (get_user_class () > 12) {
				$optclass = array (
						above => $lang_log ['moreequal'],
						exact => $lang_log ['equal'],
						below => $lang_log ['less'] 
				);
				searchadmin ( $lang_log ['hint'], $optclass );
			}
			// end of my codes
			
			$res = sql_query ( "SELECT COUNT(*) FROM sitelog" . $wherea );
			$row = mysql_fetch_array ( $res );
			$count = $row [0];
			
			$perpage = 50;
			
			list ( $pagertop, $pagerbottom, $limit ) = pager ( $perpage, $count, "log.php?action=dailylog&" . $addparam );
			
			$res = sql_query ( "SELECT added, txt FROM sitelog $wherea ORDER BY added DESC, id DESC $limit" ) or sqlerr ( __FILE__, __LINE__ );
			if (mysql_num_rows ( $res ) == 0)
				print ($lang_log ['text_log_empty']) ;
			else {
				
				// echo $pagertop;
				
				print ("<table width=940 border=1 cellspacing=0 cellpadding=5>\n") ;
				print ("<tr><td class=colhead align=center><img class=\"time\" src=\"pic/trans.gif\" alt=\"time\" title=\"" . $lang_log ['title_time_added'] . "\" /></td><td class=colhead align=left>" . $lang_log ['col_event'] . "</td></tr>\n") ;
				while ( $arr = mysql_fetch_assoc ( $res ) ) {
					$color = "";
					if (strpos ( $arr ['txt'], 'was uploaded by' ) || strpos ( $arr ['txt'], '上传了' ))
						$color = "green";
					if (strpos ( $arr ['txt'], '新增了' ) || strpos ( $arr ['txt'], 'was added by' ))
						$color = "teal";
					if (strpos ( $arr ['txt'], 'was deleted by' ) || strpos ( $arr ['txt'], '删除了' ))
						$color = "red";
					if (strpos ( $arr ['txt'], '确认了' ))
						$color = "purple";
					if (strpos ( $arr ['txt'], 'was edited by' ) || strpos ( $arr ['txt'], '编辑了' ))
						$color = "blue";
					if (strpos ( $arr ['txt'], 'settings updated by' ))
						$color = "darkred";
					print ("<tr><td class=\"rowfollow nowrap\" align=center>" . gettime ( $arr ['added'], true, false ) . "</td><td class=rowfollow align=left><font color='" . $color . "'>" . htmlspecialchars ( $arr ['txt'] ) . "</font></td></tr>\n") ;
				}
				print ("</table>") ;
				
				echo $pagerbottom;
			}
			
			print ($lang_log ['time_zone_note']) ;
			
			stdfoot ();
			die ();
			break;
		case "chartlog" :
			stdhead ( $lang_log ['head_site_log'] );
			logmenu ( 'chartlog' );
			
			require_once ('HighRoller/HighRoller.php');
			require_once ('HighRoller/HighRollerSeriesData.php');
			require_once ('HighRoller/HighRollerLineChart.php');
			require_once ('HighRoller/HighRollerPieChart.php');
			
			echo HighRoller::setHighChartsLocation ( "js/highcharts/highcharts.js" );
			
			// ---新增资源统计---
			$res_new_date = $Cache->get_value ( 'chartlog_res_new_date' );
			$res_new_count = $Cache->get_value ( 'chartlog_res_new_count' );
			$res_new_size = $Cache->get_value ( 'chartlog_res_new_size' );
			
			if (! $res_new_date || ! $res_new_count || ! $res_new_size) {
				$query = "select date(added) as date, count(*) as count, sum(size)/1024/1024/1024 as size from torrents where pulling_out = '0' group by date( added ) order by date( added ) desc limit 30";
				$res_new = sql_query ( $query ) or sqlerr ( __FILE__, __LINE__ );
				
				$res_new_date = array ();
				$res_new_count = array ();
				$res_new_size = array ();
				
				while ( $myrow = mysql_fetch_assoc ( $res_new ) ) {
					$res_new_date [] = $myrow ["date"];
					$res_new_count [] = intval ( $myrow ["count"] );
					$res_new_size [] = round ( floatval ( $myrow ["size"] ), 2 );
				}
				$res_new_date = array_reverse ( $res_new_date );
				$res_new_count = array_reverse ( $res_new_count );
				$res_new_size = array_reverse ( $res_new_size );
				
				$Cache->cache_value ( 'chartlog_res_new_date', json_encode ( $res_new_date ), 900 );
				$Cache->cache_value ( 'chartlog_res_new_count', json_encode ( $res_new_count ), 900 );
				$Cache->cache_value ( 'chartlog_res_new_size', json_encode ( $res_new_size ), 900 );
			} else {
				$res_new_date = json_decode ( $res_new_date );
				$res_new_count = json_decode ( $res_new_count );
				$res_new_size = json_decode ( $res_new_size );
			}
			$linechart1 = new HighRollerLineChart ();
			$linechart1->chart->renderTo = 'linechart1';
			$linechart1->chart->width = 940;
			// $linechart1->chart->type = 'spline';
			// $linechart1->chart->zoomType = 'x';
			$linechart1->title->text = '最近30天新增资源统计';
			$linechart1->xAxis->title->text = null;
			$linechart1->xAxis->categories = $res_new_date;
			$linechart1->xAxis->tickInterval = 3;
			$linechart1->yAxis->title->text = null;
			$linechart1->yAxis->min = 0;
			$linechart1->plotOptions->series->name = '新增资源';
			
			$series11 = new HighRollerSeriesData ();
			$series11->addName ( '新增资源数（个）' )->addData ( $res_new_count );
			$series12 = new HighRollerSeriesData ();
			$series12->addName ( '新增资源大小（GB）' )->addData ( $res_new_size );
			
			$linechart1->addSeries ( $series11 );
			$linechart1->addSeries ( $series12 );
			
			// -----资源分类统计-----
			$res_cat_count = $Cache->get_value ( 'chartlog_res_cat_count' );
			$res_cat_size = $Cache->get_value ( 'chartlog_res_cat_size' );
			if (! $res_cat_count || ! $res_cat_size) {
				$query = "select torrents.category, categories.name, count(*) as count, sum(size)/1024/1024/1024/1024 as size from torrents left join categories on torrents.category=categories.id where torrents.pulling_out = '0' group by category order by count desc";
				$res_cat = sql_query ( $query ) or sqlerr ( __FILE__, __LINE__ );
				
				$res_cat_count = array ();
				$res_cat_size = array ();
				
				while ( $myrow = mysql_fetch_assoc ( $res_cat ) ) {
					$res_cat_count [] = array (
							$myrow ["name"],
							intval ( $myrow ["count"] ) 
					);
					$res_cat_size [] = array (
							$myrow ["name"],
							round ( floatval ( $myrow ["size"] ), 2 ) 
					);
					$my_cat_size [] = round ( floatval ( $myrow ["size"] ), 2 );
				}
				
				array_multisort ( $my_cat_size, SORT_DESC, $res_cat_size );
				$Cache->cache_value ( 'chartlog_res_cat_count', json_encode ( $res_cat_count ), 900 );
				$Cache->cache_value ( 'chartlog_res_cat_size', json_encode ( $res_cat_size ), 900 );
			} else {
				$res_cat_count = json_decode ( $res_cat_count );
				$res_cat_size = json_decode ( $res_cat_size );
			}
			$piechart1 = new HighRollerPieChart ();
			$piechart1->chart->renderTo = 'piechart1';
			$piechart1->chart->width = 470;
			$piechart1->title->text = '北洋媛资源分布1';
			$piechart1->plotOptions->pie->showInLegend = true;
			$piechart1->plotOptions->pie->allowPointSelect = true;
			$piechart1->plotOptions->pie->cursor = 'pointer';
			$piechart1->plotOptions->pie->showInLegend = true;
			$piechart1->plotOptions->pie->dataLabels->enabled = false;
			
			$series21 = new HighRollerSeriesData ();
			$series21->addName ( '资源数（个）' )->addData ( $res_cat_count );
			$piechart1->addSeries ( $series21 );
			
			$piechart2 = new HighRollerPieChart ();
			$piechart2->chart->renderTo = 'piechart2';
			$piechart2->chart->width = 470;
			$piechart2->title->text = '北洋媛资源分布2';
			$piechart2->plotOptions->pie->showInLegend = true;
			$piechart2->plotOptions->pie->allowPointSelect = true;
			$piechart2->plotOptions->pie->cursor = 'pointer';
			$piechart2->plotOptions->pie->showInLegend = true;
			$piechart2->plotOptions->pie->dataLabels->enabled = false;
			
			$series22 = new HighRollerSeriesData ();
			$series22->addName ( '资源大小（TB）' )->addData ( $res_cat_size );
			$piechart2->addSeries ( $series22 );
			
			// print ('<h1>北洋媛资源统计</h1>') ;
			print ('<table width=940 border=0 cellspacing=0 cellpadding=0>') ;
			print ('<tr><td class=colhead align=center colspan=2>北洋媛资源统计</td></tr>') ;
			print ('<tr><td colspan=2><div id="linechart1"></div></td></tr>') ;
			print ('<tr><td>') ;
			print ('<div id="piechart1"></div>') ;
			print ('</td><td>') ;
			print ('<div id="piechart2"></div>') ;
			print ('</td></tr>') ;
			print ('</table>') ;
			print ('<script type="text/javascript">' . $linechart1->renderChart () . '</script>') ;
			print ('<script type="text/javascript">' . $piechart1->renderChart () . '</script>') ;
			print ('<script type="text/javascript">' . $piechart2->renderChart () . '</script>') ;
			stdfoot ();
			die ();
			break;
		case "chronicle" :
			stdhead ( $lang_log ['head_chronicle'] );
			$query = mysql_real_escape_string ( trim ( $_GET ["query"] ) );
			if ($query) {
				$wherea = " WHERE txt LIKE '%$query%' ";
				$addparam = "query=" . rawurlencode ( $query ) . "&";
			} else {
				$wherea = "";
				$addparam = "";
			}
			logmenu ( "chronicle" );
			searchtable ( $lang_log ['text_search_chronicle'], 'chronicle' );
			if (get_user_class () >= $chrmanage_class)
				additem ( $lang_log ['text_add_chronicle'], 'chronicle' );
			if ($_GET ['do'] == "del" || $_GET ['do'] == 'edit' || $_POST ['do'] == "add" || $_POST ['do'] == "update") {
				$txt = $_POST ['txt'];
				if (get_user_class () < $chrmanage_class)
					permissiondeny ();
				elseif ($_POST ['do'] == "add")
					sql_query ( "INSERT INTO chronicle (userid,added, txt) VALUES ('" . $CURUSER ["id"] . "', now(), " . sqlesc ( $txt ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
				elseif ($_POST ['do'] == "update") {
					$id = 0 + $_POST ['id'];
					if (! $id) {
						header ( "Location: log.php?action=chronicle" );
						die ();
					} else
						sql_query ( "UPDATE chronicle SET txt=" . sqlesc ( $txt ) . " WHERE id=" . $id ) or sqlerr ( __FILE__, __LINE__ );
				} else {
					$id = 0 + $_GET ['id'];
					if (! $id) {
						header ( "Location: log.php?action=chronicle" );
						die ();
					} elseif ($_GET ['do'] == "del")
						sql_query ( "DELETE FROM chronicle where id = '" . $id . "'" ) or sqlerr ( __FILE__, __LINE__ );
					elseif ($_GET ['do'] == "edit")
						edititem ( $lang_log ['text_edit_chronicle'], 'chronicle', $id );
				}
			}
			
			$res = sql_query ( "SELECT COUNT(*) FROM chronicle" . $wherea );
			$row = mysql_fetch_array ( $res );
			$count = $row [0];
			
			$perpage = 50;
			
			list ( $pagertop, $pagerbottom, $limit ) = pager ( $perpage, $count, "log.php?action=chronicle&" . $addparam );
			$res = sql_query ( "SELECT id, added, txt FROM chronicle $wherea ORDER BY added DESC $limit" ) or sqlerr ( __FILE__, __LINE__ );
			if (mysql_num_rows ( $res ) == 0)
				print ($lang_log ['text_chronicle_empty']) ;
			else {
				
				// echo $pagertop;
				
				print ("<table width=940 border=1 cellspacing=0 cellpadding=5>\n") ;
				print ("<tr><td class=colhead align=center>" . $lang_log ['col_date'] . "</td><td class=colhead align=left>" . $lang_log ['col_event'] . "</td>" . (get_user_class () >= $chrmanage_class ? "<td class=colhead align=center>" . $lang_log ['col_modify'] . "</td>" : "") . "</tr>\n") ;
				while ( $arr = mysql_fetch_assoc ( $res ) ) {
					$date = gettime ( $arr ['added'], true, false );
					print ("<tr><td class=rowfollow align=center><nobr>$date</nobr></td><td class=rowfollow align=left>" . format_comment ( $arr ["txt"], true, false, true ) . "</td>" . (get_user_class () >= $chrmanage_class ? "<td align=center nowrap><b><a href=\"log.php?action=chronicle&do=edit&id=" . $arr ["id"] . "\">" . $lang_log ['text_edit'] . "</a>&nbsp;|&nbsp;<a href=\"log.php?action=chronicle&do=del&id=" . $arr ["id"] . "\"><font color=red>" . $lang_log ['text_delete'] . "</font></a></b></td>" : "") . "</tr>\n") ;
				}
				print ("</table>") ;
			}
			print ($lang_log ['time_zone_note']) ;
			stdfoot ();
			die ();
			break;
		case "funbox" :
			stdhead ( $lang_log ['head_funbox'] );
			$query = mysql_real_escape_string ( trim ( $_GET ["query"] ) );
			$search = $_GET ["search"];
			if ($query) {
				switch ($search) {
					case "title" :
						$wherea = " WHERE title LIKE '%$query%' AND status != 'banned'";
						break;
					case "body" :
						$wherea = " WHERE body LIKE '%$query%' AND status != 'banned'";
						break;
					case "both" :
						$wherea = " WHERE (body LIKE '%$query%' or title LIKE '%$query%') AND status != 'banned'";
						break;
				}
				$addparam = "search=" . rawurlencode ( $search ) . "&query=" . rawurlencode ( $query ) . "&";
			} else {
				$wherea = " WHERE status != 'banned'";
				$addparam = "";
			}
			logmenu ( "funbox" );
			$opt = array (
					title => $lang_log ['text_title'],
					body => $lang_log ['text_body'],
					both => $lang_log ['text_both'] 
			);
			searchtable ( $lang_log ['text_search_funbox'], 'funbox', $opt );
			$res = sql_query ( "SELECT COUNT(*) FROM fun " . $wherea );
			$row = mysql_fetch_array ( $res );
			$count = $row [0];
			
			$perpage = 10;
			list ( $pagertop, $pagerbottom, $limit ) = pager ( $perpage, $count, "log.php?action=funbox&" . $addparam );
			$res = sql_query ( "SELECT added, body, title, status FROM fun $wherea ORDER BY added DESC $limit" ) or sqlerr ( __FILE__, __LINE__ );
			if (mysql_num_rows ( $res ) == 0)
				print ($lang_log ['text_funbox_empty']) ;
			else {
				
				// echo $pagertop;
				while ( $arr = mysql_fetch_assoc ( $res ) ) {
					$date = gettime ( $arr ['added'], true, false );
					print ("<table width=940 border=1 cellspacing=0 cellpadding=5>\n") ;
					print ("<tr><td class=rowhead width='10%'>" . $lang_log ['col_title'] . "</td><td class=rowfollow align=left>" . $arr ["title"] . " - <b>" . $arr ["status"] . "</b></td></tr><tr><td class=rowhead width='10%'>" . $lang_log ['col_date'] . "</td><td class=rowfollow align=left>" . $date . "</td></tr><tr><td class=rowhead width='10%'>" . $lang_log ['col_body'] . "</td><td class=rowfollow align=left>" . format_comment ( $arr ["body"], false, false, true ) . "</td></tr>\n") ;
					print ("</table><br />") ;
				}
				echo $pagerbottom;
			}
			
			print ($lang_log ['time_zone_note']) ;
			stdfoot ();
			die ();
			break;
		case "news" :
			stdhead ( $lang_log ['head_news'] );
			$query = mysql_real_escape_string ( trim ( $_GET ["query"] ) );
			$search = $_GET ["search"];
			if ($query) {
				switch ($search) {
					case "title" :
						$wherea = " WHERE title LIKE '%$query%' ";
						break;
					case "body" :
						$wherea = " WHERE body LIKE '%$query%' ";
						break;
					case "both" :
						$wherea = " WHERE body LIKE '%$query%' or title LIKE '%$query%'";
						break;
				}
				$addparam = "search=" . rawurlencode ( $search ) . "&query=" . rawurlencode ( $query ) . "&";
			} else {
				$wherea = "";
				$addparam = "";
			}
			logmenu ( "news" );
			$opt = array (
					title => $lang_log ['text_title'],
					body => $lang_log ['text_body'],
					both => $lang_log ['text_both'] 
			);
			searchtable ( $lang_log ['text_search_news'], 'news', $opt );
			
			$res = sql_query ( "SELECT COUNT(*) FROM news" . $wherea );
			$row = mysql_fetch_array ( $res );
			$count = $row [0];
			
			$perpage = 20;
			
			list ( $pagertop, $pagerbottom, $limit ) = pager ( $perpage, $count, "log.php?action=news&" . $addparam );
			$res = sql_query ( "SELECT id, added, body, title FROM news $wherea ORDER BY added DESC $limit" ) or sqlerr ( __FILE__, __LINE__ );
			if (mysql_num_rows ( $res ) == 0)
				print ($lang_log ['text_news_empty']) ;
			else {
				
				// echo $pagertop;
				while ( $arr = mysql_fetch_assoc ( $res ) ) {
					$date = gettime ( $arr ['added'], true, false );
					print ("<table width=940 border=1 cellspacing=0 cellpadding=5>\n") ;
					print ("<tr><td class=rowhead width='10%'>" . $lang_log ['col_title'] . "</td><td class=rowfollow align=left>" . $arr ["title"] . "</td></tr><tr><td class=rowhead width='10%'>" . $lang_log ['col_date'] . "</td><td class=rowfollow align=left>" . $date . "</td></tr><tr><td class=rowhead width='10%'>" . $lang_log ['col_body'] . "</td><td class=rowfollow align=left>" . format_comment ( $arr ["body"], false, false, true ) . "</td></tr>\n") ;
					print ("</table><br />") ;
				}
				echo $pagerbottom;
			}
			
			print ($lang_log ['time_zone_note']) ;
			
			stdfoot ();
			die ();
			break;
		case "poll" :
			$do = $_GET ["do"];
			$pollid = $_GET ["pollid"];
			$returnto = htmlspecialchars ( $_GET ["returnto"] );
			if ($do == "delete") {
				if (get_user_class () < $chrmanage_class)
					stderr ( $lang_log ['std_error'], $lang_log ['std_permission_denied'] );
				
				int_check ( $pollid, true );
				
				$sure = $_GET ["sure"];
				if (! $sure)
					stderr ( $lang_log ['std_delete_poll'], $lang_log ['std_delete_poll_confirmation'] . "<a href=log.php?action=poll&do=delete&pollid=$pollid&returnto=$returnto&sure=1>" . $lang_log ['std_here_if_sure'], false );
				
				sql_query ( "DELETE FROM pollanswers WHERE pollid = $pollid" ) or sqlerr ();
				sql_query ( "DELETE FROM polls WHERE id = $pollid" ) or sqlerr ();
				$Cache->delete_value ( 'current_poll_content' );
				$Cache->delete_value ( 'current_poll_result', true );
				if ($returnto == "main")
					header ( "Location: " . get_protocol_prefix () . "$BASEURL" );
				else
					header ( "Location: " . get_protocol_prefix () . "$BASEURL/log.php?action=poll&deleted=1" );
				die ();
			}
			
			$rows = sql_query ( "SELECT COUNT(*) FROM polls" ) or sqlerr ();
			$row = mysql_fetch_row ( $rows );
			$pollcount = $row [0];
			if ($pollcount == 0)
				stderr ( $lang_log ['std_sorry'], $lang_log ['std_no_polls'] );
			$polls = sql_query ( "SELECT * FROM polls ORDER BY id DESC LIMIT 1," . ($pollcount - 1) ) or sqlerr ();
			stdhead ( $lang_log ['head_previous_polls'] );
			logmenu ( "poll" );
			print ("<table border=1 cellspacing=0 width=940 cellpadding=5>\n") ;
			// print("<tr><td class=colhead
			// align=center>".$lang_log['text_previous_polls']."</td></tr>\n");
			
			function srt($a, $b) {
				if ($a [0] > $b [0])
					return - 1;
				if ($a [0] < $b [0])
					return 1;
				return 0;
			}
			
			while ( $poll = mysql_fetch_assoc ( $polls ) ) {
				$o = array (
						$poll ["option0"],
						$poll ["option1"],
						$poll ["option2"],
						$poll ["option3"],
						$poll ["option4"],
						$poll ["option5"],
						$poll ["option6"],
						$poll ["option7"],
						$poll ["option8"],
						$poll ["option9"],
						$poll ["option10"],
						$poll ["option11"],
						$poll ["option12"],
						$poll ["option13"],
						$poll ["option14"],
						$poll ["option15"],
						$poll ["option16"],
						$poll ["option17"],
						$poll ["option18"],
						$poll ["option19"] 
				);
				
				print ("<tr><td align=center>\n") ;
				
				print ("<p class=sub>") ;
				$added = gettime ( $poll ['added'], true, false );
				
				print ($added) ;
				
				if (get_user_class () >= $pollmanage_class) {
					print (" - [<a href=makepoll.php?action=edit&pollid=$poll[id]><b>" . $lang_log ['text_edit'] . "</b></a>]\n") ;
					print (" - [<a href=log.php?action=poll&do=delete&pollid=$poll[id]><b>" . $lang_log ['text_delete'] . "</b></a>]\n") ;
				}
				
				print ("<a name=$poll[id]>") ;
				
				print ("</p>\n") ;
				
				print ("<table class=main border=1 cellspacing=0 cellpadding=5><tr><td class=text>\n") ;
				
				print ("<p align=center><b>" . $poll ["question"] . "</b></p>") ;
				
				$pollanswers = sql_query ( "SELECT selection FROM pollanswers WHERE pollid=" . $poll ["id"] . " AND  selection < 20" ) or sqlerr ();
				
				$tvotes = mysql_num_rows ( $pollanswers );
				
				$vs = array (); // count for each option ([0]..[19])
				$os = array (); // votes and options: array(array(123, "Option 1"),
				                // array(45, "Option 2"))
				                
				// Count votes
				while ( $pollanswer = mysql_fetch_row ( $pollanswers ) )
					$vs [$pollanswer [0]] += 1;
				
				reset ( $o );
				for($i = 0; $i < count ( $o ); ++ $i)
					if ($o [$i])
						$os [$i] = array (
								$vs [$i],
								$o [$i] 
						);
				
				print ("<table width=100% class=main border=0 cellspacing=0 cellpadding=0>\n") ;
				$i = 0;
				while ( $a = $os [$i] ) {
					if ($tvotes > 0)
						$p = round ( $a [0] / $tvotes * 100 );
					else
						$p = 0;
					print ("<tr><td class=embedded>" . $a [1] . "&nbsp;&nbsp;</td><td class=\"embedded nowrap\">" . "<img class=\"bar_end\" src=\"pic/trans.gif\" alt=\"\" /><img class=\"unsltbar\" src=\"pic/trans.gif\" style=\"width: " . ($p * 3) . "px\" /><img class=\"bar_end\" src=\"pic/trans.gif\" alt=\"\" /> $p%</td></tr>\n") ;
					++ $i;
				}
				print ("</table>\n") ;
				$tvotes = number_format ( $tvotes );
				print ("<p align=center>" . $lang_log ['text_votes'] . "$tvotes</p>\n") ;
				
				print ("</td></tr></table><br /><br />\n") ;
				
				print ("</p></td></tr>\n") ;
			}
			print ("</table>") ;
			print ($lang_log ['time_zone_note']) ;
			stdfoot ();
			die ();
			break;
		
		case "administrator0" :
			stdhead ( $lang_log ['head_administrator'] );
			logmenu ( "administrator" );
			$from = (date ( "w" ) != 0) ? strtotime ( "this week Sunday" ) - 14 * 86400 : strtotime ( "next week Sunday" ) - 14 * 86400;
			$to = (date ( "w" ) != 0) ? strtotime ( "this week Sunday" ) - 7 * 86400 : strtotime ( "next week Sunday" ) - 7 * 86400;
			
			print ("<table width=940 border=1 cellspacing=0 cellpadding=5>\n") ;
			print ("<h1 align=\"center\">" . $lang_log ['head_administrator'] . "  -  From " . date ( "Y-m-d", $from ) . " To " . date ( "Y-m-d", $to - 1 ) . "</h1>") ;
			print ("<tr><td class=colhead align=center>" . $lang_log ['username'] . "</td><td class=colhead align=center>" . $lang_log ['upload'] . "</td><td class=colhead align=center>" . $lang_log ['subupload'] . "</td><td class=colhead align=center>" . $lang_log ['edit'] . "</td><td class=colhead align=center>" . $lang_log ['deltorrent'] . "</td><td class=colhead align=center>" . $lang_log ['deloffer'] . "</td><td class=colhead align=center>" . $lang_log ['delsub'] . "</td><td class=colhead align=center>" . $lang_log ['freeze'] . "</td><td class=colhead align=center>" . $lang_log ['thaw'] . "</td><td class=colhead align=center>" . $lang_log ['allowoffer'] . "</td><td class=colhead align=center>" . $lang_log ['autoupload'] . "</td>"/*<td class=colhead align=center>".$lang_log['serviceseed']."</td><td class=colhead align=center>".$lang_log['unserviceseed']."</td>"*/."</tr>\n") ;
			$res = sql_query ( "select username,class from users where class<='14' AND class>='13' ORDER BY class DESC ,id ASC" ) or sqlerr ( __FILE__, __LINE__ );
			while ( $row = mysql_fetch_array ( $res ) )
				$usernames [] = $row;
			global $Cache;
			if (! $log_admin = $Cache->get_value ( 'log_admin' )) {
				$log_admin = array ();
				foreach ( $usernames as $user ) {
					$field = array (
							'upload' => "用户 " . $user ['username'] . " 上传了资源%",
							'subuploaded' => "" . $user ['username'] . " 上传了 %字幕%",
							'edit' => "管理员 " . $user ['username'] . " 编辑了资源% ",
							'deltorrent' => "管理员 " . $user ['username'] . " 删除了资源%",
							'deloffer' => "管理员 " . $user ['username'] . " 删除了候选%",
							'delsub' => "管理员 " . $user ['username'] . " 删除了 %字幕%",
							'freeze' => "管理员 " . $user ['username'] . " 冻结了候选%",
							'thaw' => "管理员 " . $user ['username'] . " 解冻了候选%",
							'allowoffer' => "管理员 " . $user ['username'] . " 允许了候选%",
							'autoupload' => "% AutoUploaded by " . $user ['username'] 
					);
					// 'serviceseed' => "% serverSeeding by ".$user['username'],
					// 'unserviceseed' => "% UnServerSeeding by
					// ".$user['username']
					
					foreach ( $field as $name => $value ) {
						$wheres = "SELECT count(id) as " . $name . " FROM sitelog WHERE added >= '" . date ( "Y-m-d H:i:s", $from ) . "' AND added <'" . date ( "Y-m-d H:i:s", $to ) . "' AND txt like '$value'";
						$res = mysql_fetch_assoc ( sql_query ( $wheres ) );
						$log_admin [$user ['username']] [$name] = $res [$name];
					}
				}
				if (date ( "w" ) != 0)
					$time = strtotime ( "this week Sunday" );
				else
					$time = strtotime ( "next week Sunday" );
				$log_admin ['time'] ['at'] = strtotime ( "now" );
				$log_admin ['time'] ['until'] = $time;
				$time -= strtotime ( "now" );
				$Cache->cache_value ( 'log_admin', $log_admin, $time );
			}
			foreach ( $usernames as $user ) {
				$field = array (
						'upload' => "用户 " . $user ['username'] . " 上传了资源%",
						'subuploaded' => "" . $user ['username'] . " 上传了 %字幕%",
						'edit' => "管理员 " . $user ['username'] . " 编辑了资源% ",
						'deltorrent' => "管理员 " . $user ['username'] . " 删除了资源%",
						'deloffer' => "管理员 " . $user ['username'] . " 删除了候选%",
						'delsub' => "管理员 " . $user ['username'] . " 删除了 %字幕%",
						'freeze' => "管理员 " . $user ['username'] . " 冻结了候选%",
						'thaw' => "管理员 " . $user ['username'] . " 解冻了候选%",
						'allowoffer' => "管理员 " . $user ['username'] . " 允许了候选%",
						'autoupload' => "% AutoUploaded by " . $user ['username'] 
				);
				// 'serviceseed' => "% serverSeeding by ".$user['username'],
				// 'unserviceseed' => "% UnServerSeeding by ".$user['username']
				
				print ("<tr><td align=center><b><a class=" . ($user ['class'] == 14 ? "Administrator_Name" : "Moderator_Name") . " href=\"log.php?query=%25" . $user ['username'] . "%25&from=$from&to=$to&search=all&action=dailylog\">" . $user ['username'] . "</a></b></td>") ;
				foreach ( $field as $name => $value ) {
					$val = $log_admin [$user ['username']] [$name];
					$colorid = ( int ) ($val * 256 / 20);
					$color = ($colorid >= 256 ? "FF" : ($colorid < 16 ? "0" : "") . dechex ( $colorid ));
					print ("<td align=center >" . ($val > 0 ? "<b>" : "") . "<a href=\"log.php?query=" . rawurlencode ( $value ) . "&search=all&from=$from&to=$to&action=dailylog\"><font color=#" . $color . "0000>" . $val . "</font></a>" . ($val > 0 ? "</b>" : "") . "</td>") ;
				}
				print ("</tr>\n") ;
			}
			print ("</table>") ;
			
			$cachetimeat = $log_admin ['time'] ['at'];
			$cachetimeat = "Cache at:" . date ( "Y-m-d H:i:s", $cachetimeat );
			$cachetimeuntil = $log_admin ['time'] ['until'];
			$cachetimeuntil = "Cache until:" . date ( "Y-m-d H:i:s", $cachetimeuntil );
			print ("<h4>&lt;" . $cachetimeat . " &nbsp;&nbsp;" . $cachetimeuntil . "&gt;</h4>" . $lang_log ['time_zone_note']) ;
			
			echo $pagerbottom;
			
			stdfoot ();
			die ();
			break;
		
		case "administrator" :
			if (get_user_class () < UC_MODERATOR) {
				permissiondeny ();
			}
			stdhead ( $lang_log ['head_administrator'] );
			logmenu ( "administrator" );
			
			$from = (date ( "w" ) != 0) ? strtotime ( "this week Sunday" ) - 14 * 86400 : strtotime ( "next week Sunday" ) - 14 * 86400;
			$to = (date ( "w" ) != 0) ? strtotime ( "this week Sunday" ) - 7 * 86400 : strtotime ( "next week Sunday" ) - 7 * 86400;
			
			print ("<table width=940 border=1 cellspacing=0 cellpadding=5>\n") ;
			print ("<h1 align=\"center\">" . $lang_log ['head_administrator'] . "  -  From " . date ( "Y-m-d", $from ) . " To " . date ( "Y-m-d", $to - 1 ) . "</h1>") ;
			print ("<tr><td class=colhead align=center>" . $lang_log ['username'] . "</td><td class=colhead align=center>" . $lang_log ['upload'] . "</td><td class=colhead align=center>" . $lang_log ['subupload'] . "</td><td class=colhead align=center>" . $lang_log ['edit'] . "</td><td class=colhead align=center>" . $lang_log ['deltorrent'] . "</td><td class=colhead align=center>" . $lang_log ['manageoffer'] . "</td><td class=colhead align=center>" . $lang_log ['delsub'] . "</td><td class=colhead align=center>" . $lang_log ['managereq'] . "</td><td class=colhead align=center>" . $lang_log ['autoupload'] . "</td>"/*<td class=colhead align=center>".$lang_log['serviceseed']."</td><td class=colhead align=center>".$lang_log['unserviceseed']."</td>"*/."</tr>\n") ;
			$res = sql_query ( "select username,class from users where class<='14' AND class>='13' ORDER BY class DESC ,id ASC" ) or sqlerr ( __FILE__, __LINE__ );
			while ( $row = mysql_fetch_array ( $res ) )
				$usernames [] = $row;
			global $Cache;
			if (! $log_admin = $Cache->get_value ( 'log_admin_new' )) {
				$log_admin = array ();
				foreach ( $usernames as $user ) {
					$field = array (
							'upload' => "用户 " . $user ['username'] . " 上传了资源%",
							'subuploaded' => "" . $user ['username'] . " 上传了 %字幕%",
							'edit' => "管理员 " . $user ['username'] . " 编辑了资源% ",
							'deltorrent' => "管理员 " . $user ['username'] . " 删除了资源%",
							'manageoffer' => "管理员 " . $user ['username'] . " %候选%",
							'delsub' => "管理员 " . $user ['username'] . " 删除了 %字幕%",
							'managereq' => "管理员 " . $user ['username'] . " %求种%",
							'autoupload' => "% AutoUploaded by " . $user ['username'] 
					);
					// 'serviceseed' => "% serverSeeding by ".$user['username'],
					// 'unserviceseed' => "% UnServerSeeding by
					// ".$user['username']
					
					foreach ( $field as $name => $value ) {
						$wheres = "SELECT count(id) as " . $name . " FROM sitelog WHERE added >= '" . date ( "Y-m-d H:i:s", $from ) . "' AND added <'" . date ( "Y-m-d H:i:s", $to ) . "' AND txt like '$value'";
						$res = mysql_fetch_assoc ( sql_query ( $wheres ) );
						$log_admin [$user ['username']] [$name] = $res [$name];
					}
				}
				if (date ( "w" ) != 0)
					$time = strtotime ( "this week Sunday" );
				else
					$time = strtotime ( "next week Sunday" );
				$log_admin ['time'] ['at'] = strtotime ( "now" );
				$log_admin ['time'] ['until'] = $time;
				$time -= strtotime ( "now" );
				$Cache->cache_value ( 'log_admin_new', $log_admin, $time );
			}
			foreach ( $usernames as $user ) {
				$field = array (
						'upload' => "用户 " . $user ['username'] . " 上传了资源%",
						'subuploaded' => "" . $user ['username'] . " 上传了 %字幕%",
						'edit' => "管理员 " . $user ['username'] . " 编辑了资源% ",
						'deltorrent' => "管理员 " . $user ['username'] . " 删除了资源%",
						'manageoffer' => "管理员 " . $user ['username'] . " %候选%",
						'delsub' => "管理员 " . $user ['username'] . " 删除了 %字幕%",
						'managereq' => "管理员 " . $user ['username'] . " %求种%",
						'autoupload' => "% AutoUploaded by " . $user ['username'] 
				);
				// 'serviceseed' => "% serverSeeding by ".$user['username'],
				// 'unserviceseed' => "% UnServerSeeding by ".$user['username']
				
				print ("<tr><td align=center><b><a class=" . ($user ['class'] == 14 ? "Administrator_Name" : "Moderator_Name") . " href=\"log.php?query=%25" . $user ['username'] . "%25&from=$from&to=$to&search=all&action=dailylog\">" . $user ['username'] . "</a></b></td>") ;
				foreach ( $field as $name => $value ) {
					$val = $log_admin [$user ['username']] [$name];
					$colorid = ( int ) ($val * 256 / 20);
					$color = ($colorid >= 256 ? "FF" : ($colorid < 16 ? "0" : "") . dechex ( $colorid ));
					print ("<td align=center >" . ($val > 0 ? "<b>" : "") . "<a href=\"log.php?query=" . rawurlencode ( $value ) . "&search=all&from=$from&to=$to&action=dailylog\"><font color=#" . $color . "0000>" . $val . "</font></a>" . ($val > 0 ? "</b>" : "") . "</td>") ;
				}
				print ("</tr>\n") ;
			}
			print ("</table>") ;
			
			$cachetimeat = $log_admin ['time'] ['at'];
			$cachetimeat = "Cache at:" . date ( "Y-m-d H:i:s", $cachetimeat );
			$cachetimeuntil = $log_admin ['time'] ['until'];
			$cachetimeuntil = "Cache until:" . date ( "Y-m-d H:i:s", $cachetimeuntil );
			print ("<h4>&lt;" . $cachetimeat . " &nbsp;&nbsp;" . $cachetimeuntil . "&gt;</h4>" . $lang_log ['time_zone_note']) ;
			
			echo $pagerbottom;
			
			stdfoot ();
			die ();
			break;
		
		case "uploader" :
			if (get_user_class () < UC_UPLOADER) {
				permissiondeny ();
			}
			
			$standard = array (
					'num' => 20,
					'size' => 30 * 1024 * 1024 * 1024 
			);
			header ( "Expires:   Mon,   26   Jul   1997   00:00:00   GMT" );
			header ( "Cache-Control:   no-cache,   must-revalidate" );
			header ( "Pragma:   no-cache" );
			stdhead ( $lang_log ['head_uploader'] );
			logmenu ( "uploader" );
			
			$last = strtotime ( date ( "Y-m-1 0:0:0", strtotime ( "-2 month " ) ) );
			$from = strtotime ( date ( "Y-m-1 0:0:0", strtotime ( "last month " ) ) );
			$to = strtotime ( date ( "Y-m-1 0:0:0" ) );
			
			if ($_GET ['askforleave'] == $CURUSER ["id"]) {
				global $CURUSER;
				if (strpos ( $CURUSER ['stafffor'], "(请假中)" ) !== false) {
					sql_query ( "UPDATE users SET stafffor= replace(stafffor,\"(请假中)\",\"\") WHERE id = $CURUSER[id]" );
					stdmsg ( "成功", "销假成功" );
				} else {
					sql_query ( "UPDATE users SET stafffor= '" . $CURUSER ['stafffor'] . "(请假中)' WHERE id = $CURUSER[id]" );
					stdmsg ( "成功", "请假成功" );
				}
				
				$Cache->delete_value ( "staff_page", true );
				die ();
			} elseif ($_GET ['askforleave']) {
				stdmsg ( "出错了", "你不是" . get_username ( $_GET ['askforleave'] ) . "，无法修改其请假状态" );
				die ();
			}
			
			print ("<table width=400 border=1 cellspacing=0 cellpadding=5>\n") ;
			print ("<h1 align=\"center\">" . $lang_log ['head_uploader'] . "  -  From " . date ( "Y-m-d", $from ) . " To " . date ( "Y-m-d", $to - 1 ) . "</h1>") ;
			if (get_user_class () >= 12) {
				print ("<h5 align=\"center\"> <a class=\"faqlink\"  href=\"uploaders.php\">查看当月考核完成情况</a></h5>") ;
			}
			print ("<tr><td class=colhead align=center>" . $lang_log ['username'] . "</td><td class=colhead align=center>" . $lang_log ['up_num'] . "</td><td class=colhead align=center>合集删种</td><td class=colhead align=center>" . $lang_log ['up_size'] . "</td>" . ($CURUSER ['class'] >= UC_MODERATOR ? "<td class=colhead align=center>" . "合格与否" . "</td>" : "") . "<td class=colhead align=center>" . "工资" . "</td><td class=colhead align=center>" . "请假状态" . "</td></tr>\n") ;
			$res = sql_query ( "select id, username, stafffor from users where class ='12' ORDER BY username ASC ,id ASC" ) or sqlerr ( __FILE__, __LINE__ );
			while ( $row = mysql_fetch_array ( $res ) )
				$usernames [] = $row;
			global $Cache;
			if (! $log_uploader = $Cache->get_value ( 'log_uploader' )) {
				foreach ( $usernames as $user ) {
					if (strpos ( $user ['stafffor'], "(请假中)" ) !== false)
						$log_uploader [$user ['username']] ["thisnum"] = "请假";
					else {
						$wheres = "SELECT count(*) as num , sum(size) as size FROM torrents WHERE added >= '" . date ( "Y-m-d H:i:s", $from ) . "' AND added <'" . date ( "Y-m-d H:i:s", $to ) . "' AND owner = '" . $user ["id"] . "' ";
						$res = mysql_fetch_assoc ( sql_query ( $wheres ) );
						$log_uploader [$user ['username']] ["thisnum"] = $res ["num"];
						$log_uploader [$user ['username']] ["thissize"] = $res ["size"];
						$wheres = "SELECT count(*) as num , sum(size) as size FROM torrents WHERE added >= '" . date ( "Y-m-d H:i:s", $last ) . "' AND added <'" . date ( "Y-m-d H:i:s", $from ) . "' AND owner = '" . $user ["id"] . "' ";
						$res = mysql_fetch_assoc ( sql_query ( $wheres ) );
						$log_uploader [$user ['username']] ["lastnum"] = $res ["num"];
						$log_uploader [$user ['username']] ["lastsize"] = $res ["size"];
					}
				}
				$time = strtotime ( date ( "Y-m-1 0:0:0", strtotime ( "next month " ) ) ) - strtotime ( "now" );
				$log_uploader ['time'] ['at'] = strtotime ( "now" );
				$log_uploader ['time'] ['until'] = strtotime ( date ( "Y-m-1 0:0:0", strtotime ( "next month " ) ) );
				$Cache->cache_value ( 'log_uploader', $log_uploader, $time );
			}
			foreach ( $usernames as $user ) {
				print ("<tr><td align=center>".get_username($user ['id'], false, true, true, false, false, false). "</a></b></td>") ;
				if ($log_uploader [$user ['username']] ["thisnum"] == "请假") {
					$num = $sumsize = $hege = "<font color=gray >(已请假)</font>";
					$salary = 2000;
				} else {
					$num=/*$log_uploader[$user['username']]["lastnum"]<$standard['num']?
		($log_uploader[$user['username']]["thisnum"]."<font color=gray >(".($log_uploader[$user['username']]["lastnum"]-$standard['num']).")</font>"):*/
		(($log_uploader [$user ['username']] ["thisnum"] < $standard ['num'] ? "<font color=#990000>" : "") . $log_uploader [$user ['username']] ["thisnum"] . ($log_uploader [$user ['username']] ["thisnum"] < $standard ['num'] ? "</font>" : ""));
					$sumsize = (($log_uploader [$user ['username']] ["thissize"] < $standard ['size'] ? "<font color=#990000>" : "") . mksize ( $log_uploader [$user ['username']] ["thissize"] ) . ($log_uploader [$user ['username']] ["thissize"] < $standard ['size'] ? "</font>" : ""));
					$hege = (($log_uploader [$user ['username']] ["thisnum"] < $standard ['num'] || $log_uploader [$user ['username']] ["thissize"] < $standard ['size']) ? "<font color=#990000>-" : "<font color=#007000>pass") . "</font>";
					$row = mysql_fetch_array ( sql_query ( "select deleted_last from uploaders where uid = ".$user ['id']) );
					$deleted= $row['deleted_last'];
					$log_uploader [$user ['username']] ["deleted"] = $res ["deleted_last"];
					$salary = salary ( $log_uploader [$user ['username']] ["thisnum"]+$deleted, $log_uploader [$user ['username']] ["thissize"] / (1024 * 1024 * 1024), $standard ['num'], $standard ['size'] / (1024 * 1024 * 1024) );
				}
				print ("<td align=center ><b>" . $num . "</b></td>") ;
				print ("<td align=center ><b>" . $deleted . "</b></td>") ;
				print ("<td align=center ><b>" . $sumsize . "</b></td>") ;
				print (($CURUSER ['class'] >= UC_MODERATOR ? "<td align=center ><b>" . $hege . "</b></td>" : "")) ;
				print ("<td align=center ><b>" . $salary . "</b></td>") ;
				if ($user ['id'] == $CURUSER ['id'])
					$askfor = "<a href=log.php?action=uploader&amp;askforleave=" . $user ['id'] . ">" . ((strpos ( $user ['stafffor'], "(请假中)" ) !== false) ? "我要销假" : "我要请假") . "</a>";
				elseif (strpos ( $user ['stafffor'], "(请假中)" ) !== false)
					$askfor = "<font color=#990000>请假中</font>";
				else
					$askfor = "正常";
				print ("<td align=center ><b>" . $askfor . "</font></b></td></tr>\n") ;
			}
			print ("</table>") ;
			$cachetimeat = $log_uploader ['time'] ['at'];
			$cachetimeat = "Cache at:" . date ( "Y-m-d H:i:s", $cachetimeat );
			$cachetimeuntil = $log_uploader ['time'] ['until'];
			$cachetimeuntil = "Cache until:" . date ( "Y-m-d H:i:s", $cachetimeuntil );
	}
	print ("<h4>&lt;" . $cachetimeat . " &nbsp;&nbsp;" . $cachetimeuntil . "&gt;</h4>" . $lang_log ['time_zone_note']) ;
	stdfoot ();
	die ();
	break;
}

?>
