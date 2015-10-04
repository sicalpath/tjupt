<?php
require_once ("include/bittorrent.php");
require_once ("include/tjuip_helper.php");
dbconn ();
require_once (get_langfile_path ());
require_once (get_langfile_path ( "", true ));
require_once (get_langfile_path ( "takeupload.php","",""));
loggedinorreturn ();
parked ();
if ($enableoffer == 'no')
	permissiondenied ();
function bark($msg) {
	global $lang_offers;
	stdhead ( $lang_offers ['head_offer_error'] );
	stdmsg ( $lang_offers ['std_error'], $msg );
	stdfoot ();
	exit ();
}

assert_tjuip_or_mod();

if ($_GET ["category"]) {
	$categ = isset ( $_GET ['category'] ) ? ( int ) $_GET ['category'] : 0;
	if (! is_valid_id ( $categ ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
}

if ($_GET ["id"]) {
	$id = 0 + htmlspecialchars ( $_GET ["id"] );
	if (preg_match ( '/^[0-9]+$/', ! $id ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
}

// ==== add offer
if ($_GET ["add_offer"]) {
	if (get_user_class () < $addoffer_class)
		permissiondenied ();
	$add_offer = 0 + $_GET ["add_offer"];
	if ($add_offer != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	stdhead ( $lang_offers ['head_offer'] );
	
	print ("<p>" . $lang_offers ['text_red_star_required'] . "</p>") ;
	
	print ("<div align=\"center\"><form id=\"compose\" action=\"?new_offer=1\" name=\"compose\" method=\"post\" onsubmit=\"return validate('browsecat')\">" . "<table width=940 border=0 cellspacing=0 cellpadding=5><tr><td class=colhead align=center colspan=2>" . $lang_offers ['text_offers_open_to_all'] . "</td></tr>\n") ;
	
	$s = "<select name=type id=\"browsecat\" onChange=\"getcategory('class2','browsecat')\">\n<option value=0>" . $lang_offers ['select_type_select'] . "</option>\n";
	$cats = genrelist ( $browsecatmode );
	foreach ( $cats as $row )
		$s .= "<option value=" . $row ["id"] . ">" . htmlspecialchars ( $row ["name"] ) . "</option>\n";
	$s .= "</select><div id=\"class2\" ></div>\n";
	print ("<tr><td class=rowhead align=right><b>" . $lang_offers ['row_type'] . "<font color=red>*</font></b></td><td class=rowfollow align=left> $s</td></tr>" . "<tr><td class=rowhead align=right valign=top><b>" . $lang_offers ['row_description'] . "<b><font color=red>*</font></td><td class=rowfollow align=left>\n") ;
	textbbcode ( "compose", "body", $body, false );
	print ("</td></tr><tr><td class=toolbox align=center colspan=2><input id=qr type=submit class=btn value=" . $lang_offers ['submit_add_offer'] . " ></td></tr></table></form><br />\n") ;
	stdfoot ();
	die ();
}
// === end add offer

// === take new offer
if ($_GET ["new_offer"]) {
	if (get_user_class () < $addoffer_class)
		permissiondenied ();
	$new_offer = 0 + $_GET ["new_offer"];
	if ($new_offer != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$userid = 0 + $CURUSER ["id"];
	if (preg_match ( "/^[0-9]+$/", ! $userid ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
		
		/*
	 * $name = $_POST["name"]; if ($name == "")
	 * bark($lang_offers['std_must_enter_name']);
	 */
	
	$cat = (0 + $_POST ["type"]);
	if (! is_valid_id ( $cat ))
		bark ( $lang_offers ['std_must_select_category'] );
	
	$descrmain = unesc ( $_POST ["body"] );
	if (! $descrmain)
		bark ( $lang_offers ['std_must_enter_description'] );
	
	if (! empty ( $_POST ['picture'] )) {
		$picture = unesc ( $_POST ["picture"] );
		if (! preg_match ( "/^http:\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $picture ))
			stderr ( $lang_offers ['std_error'], $lang_offers ['std_wrong_image_format'] );
		$pic = "[img]" . $picture . "[/img]\n";
	}
	$catid = unesc ( trimcomma ( 0 + $_POST ["type"] ) );
	$cname = unesc ( trimcomma ( $_POST ["cname"] ) );
	$ename = unesc ( trimcomma ( $_POST ["ename"] ) );
	if (is_banned_title ( $cname, $catid )) {
	bark ( $lang_takeupload ['std_banned_title1'] . $cname . $lang_takeupload ['std_banned_title2'] . $lang_takeupload ['std_banned_title_hit'] );
} else if (is_banned_title ( $ename, $catid )) {
	bark ( $lang_takeupload ['std_banned_title1'] . $ename . $lang_takeupload ['std_banned_title2'] . $lang_takeupload ['std_banned_title_hit'] );
}
	
	$descr = $pic;
	$descr .= $descrmain;
	
	$res = sql_query ( "SELECT name FROM offers WHERE name =" . sqlesc ( $_POST [name] ) ) or sqlerr ( __FILE__, __LINE__ );
	$arr = mysql_fetch_assoc ( $res );
	
	/**
	 * *************************保存offer数据，接收用户输入分类信息及保存分类信息***************************************
	 */
	$name = "*";
	$ret = sql_query ( "INSERT INTO offers (userid, name, descr, category, added) VALUES (" . implode ( ",", array_map ( "sqlesc", array (
			$CURUSER ["id"],
			$name,
			$descr,
			0 + $_POST ["type"] 
	) ) ) . ", '" . date ( "Y-m-d H:i:s" ) . "')" );
	if (! $ret) {
		if (mysql_errno () == 1062)
			bark ( "!!!" );
		bark ( "mysql puked: " . mysql_error () );
	}
	$id = mysql_insert_id ();
	
	/**
	 * *************************接收用户输入分类信息及保存分类信息***************************************
	 */
	$catid = unesc ( trimcomma ( 0 + $_POST ["type"] ) );
	$cname = unesc ( trimcomma ( $_POST ["cname"] ) );
	$ename = unesc ( trimcomma ( $_POST ["ename"] ) );
	$specificcat = unesc ( trimcomma ( $_POST ["specificcat"] ) );
	$issuedate = unesc ( trimcomma ( $_POST ["issuedate"] ) );
	$subsinfo = unesc ( trimcomma ( $_POST ["subsinfo"] ) );
	$district = unesc ( trimcomma ( $_POST ["district"] ) );
	$format = unesc ( trimcomma ( $_POST ["format"] ) );
	$language = unesc ( trimcomma ( $_POST ["language"] ) );
	
	$nameset = "";
	
	if ($catid == 401) {
		$imdbnum = unesc ( trimcomma ( $_POST ["imdbnum"] ) );
		if ($district != "")
			$nameset .= "[" . $district . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
	}
	if ($catid == 402) {
		$tvalias = unesc ( trimcomma ( $_POST ["tvalias"] ) );
		$tvseasoninfo = unesc ( trimcomma ( $_POST ["tvseasoninfo"] ) );
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($tvseasoninfo != "")
			$nameset .= "[" . $tvseasoninfo . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($language != "")
			$nameset .= "[" . $language . "]";
		if ($subsinfo != 0) {
			$result = sql_query ( "SELECT * FROM subsinfo WHERE id = " . $subsinfo );
			$result_ = mysql_fetch_array ( $result );
			$nameset .= "[" . $result_ [name] . "]";
		}
	}
	if ($catid == 403) {
		$tvshowscontent = unesc ( trimcomma ( $_POST ["tvshowscontent"] ) );
		$tvshowsguest = unesc ( trimcomma ( $_POST ["tvshowsguest"] ) );
		$tvshowsremarks = unesc ( trimcomma ( $_POST ["tvshowsremarks"] ) );
		if ($district != "")
			$nameset .= "[" . $district . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($tvshowscontent != "")
			$nameset .= "[" . $tvshowscontent . "]";
	}
	if ($catid == 404) {
		$version = unesc ( trimcomma ( $_POST ["version"] ) );
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
	}
if ($catid == 405) {
	$animenum = unesc ( trimcomma ( $_POST ["animenum"] ) );
	$substeam = unesc ( trimcomma ( $_POST ["substeam"] ) );
	$resolution = unesc ( trimcomma ( $_POST ["resolution"] ) );
	if ($specificcat != "")
		$nameset .= "[" . $specificcat . "]";
	if ($substeam != "")
		$nameset .= "[" . $substeam . "]";
	if ($cname != "")
		$nameset .= "[" . $cname . "]";
	if ($ename != "")
		$nameset .= "[" . $ename . "]";
	if ($resolution != "")
		$nameset .= "[" . $resolution . "]";
	if ($animenum != "")
		$nameset .= "[" . $animenum . "]";
	if ($format != "")
		$nameset .= "[" . $format . "]";
	if ($issuedate != "")
		$nameset .= "[" . $issuedate . "]";
}
	if ($catid == 406) {
		$hqname = unesc ( trimcomma ( $_POST ["hqname"] ) );
		$artist = unesc ( trimcomma ( $_POST ["artist"] ) );
		$hqtone = unesc ( trimcomma ( $_POST ["hqtone"] ) );
		if ($hqname != "")
			$nameset .= "[" . $hqname . "]";
		if ($artist != "")
			$nameset .= "[" . $artist . "]";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
	}
	if ($catid == 407) {
		$resolution = unesc ( trimcomma ( $_POST ["resolution"] ) );
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($language != "")
			$nameset .= "[" . $language . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
	}
	if ($catid == 408) {
		$version = unesc ( trimcomma ( $_POST ["version"] ) );
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
	}
	if ($catid == 409) {
		$company = unesc ( trimcomma ( $_POST ["company"] ) );
		$platform = unesc ( trimcomma ( $_POST ["platform"] ) );
		$tvshowsremarks = unesc ( trimcomma ( $_POST ["tvshowsremarks"] ) );
		if ($platform != "")
			$nameset .= "[" . $platform . "]";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($language != "")
			$nameset .= "[" . $language . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($tvshowsremarks != "")
			$nameset .= "[" . $tvshowsremarks . "]";
	}
	if ($catid == 410) {
		$tvshowsremarks = unesc ( trimcomma ( $_POST ["tvshowsremarks"] ) );
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($tvshowsremarks != "")
			$nameset .= "[" . $tvshowsremarks . "]";
	}
	if ($catid == 411) {
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($subsinfo != 0) {
			$result = sql_query ( "SELECT * FROM subsinfo WHERE id = " . $subsinfo );
			$result_ = mysql_fetch_array ( $result );
			$nameset .= "[" . $result_ [name] . "]";
		}
	}
	if ($catid == 412) {
		if ($district != "")
			$nameset .= "[" . $district . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($language != "")
			$nameset .= "[" . $language . "]";
	}
	
	$offerinfo = sql_query ( "INSERT INTO offersinfo (offerid,category,cname,ename,issuedate,subsinfo,format,imdbnum,specificcat,language,district,version,substeam,animenum,resolution,tvalias,tvseasoninfo,tvshowscontent,tvshowsguest,tvshowsremarks,company,platform,artist,hqname,hqtone) VALUES (" . sqlesc ( $id ) . "," . sqlesc ( $catid ) . "," . sqlesc ( $cname ) . "," . sqlesc ( $ename ) . "," . sqlesc ( $issuedate ) . "," . sqlesc ( $subsinfo ) . "," . sqlesc ( $format ) . "," . sqlesc ( $imdbnum ) . "," . sqlesc ( $specificcat ) . "," . sqlesc ( $language ) . "," . sqlesc ( $district ) . "," . sqlesc ( $version ) . "," . sqlesc ( $substeam ) . "," . sqlesc ( $animenum ) . "," . sqlesc ( $resolution ) . "," . sqlesc ( $tvalias ) . "," . sqlesc ( $tvseasoninfo ) . "," . sqlesc ( $tvshowscontent ) . "," . sqlesc ( $tvshowsguest ) . "," . sqlesc ( $tvshowsremarks ) . "," . sqlesc ( $company ) . "," . sqlesc ( $platform ) . "," . sqlesc ( $artist ) . "," . sqlesc ( $hqname ) . "," . sqlesc ( $hqtone ) . ")" );
	
	if (! $offerinfo) {
		if (mysql_errno () == 1062)
			bark ( $lang_takeupload ['std_torrent_existed'] );
		bark ( "mysql puked: " . mysql_error () );
		// bark("mysql puked: ".preg_replace_callback('/./s', "hex_esc2",
	// mysql_error()));
	}
	
	if ($nameset != "") {
		sql_query ( "UPDATE offers SET name = " . sqlesc ( $nameset ) . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
	}
	
	if ($catid == 4013)
		if ($cname != "")
			sql_query ( "UPDATE offers SET name = " . sqlesc ( $cname ) . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
	
	write_log ( "用户 $CURUSER[username] 新增了候选 $id (" . $nameset . ") ", 'normal' );
	
	header ( "Refresh: 0; url=offers.php?id=$id&off_details=1" );
	
	stdhead ( $lang_offers ['head_success'] );
	/**
	 * ****************************************************************
	 */
	
	stdfoot ();
	die ();
}
// ==end take new offer

// === offer details
if ($_GET ["off_details"]) {
	
	$off_details = 0 + $_GET ["off_details"];
	if ($off_details != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$id = 0 + $_GET ["id"];
	if (! $id)
		die ();
		// stderr("Error", "I smell a rat!");
	
	$res = sql_query ( "SELECT * FROM offers WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
	$num = mysql_fetch_array ( $res );
	
	$s = $num ["name"];
	
	stdhead ( $lang_offers ['head_offer_detail_for'] . " \"" . $s . "\"" );
	print ("<h1 align=\"center\" id=\"top\">" . htmlspecialchars ( $s ) . "</h1>") ;
	
	print ("<table width=\"940\" cellspacing=\"0\" cellpadding=\"5\">") ;
	$offertime = gettime ( $num ['added'], true, false );
	if ($CURUSER ['timetype'] != 'timealive')
		$offertime = $lang_offers ['text_at'] . $offertime;
	else
		$offertime = $lang_offers ['text_blank'] . $offertime;
	tr ( $lang_offers ['row_info'], $lang_offers ['text_offered_by'] . get_username ( $num ['userid'] ) . $offertime, 1 );
	if ($num ["allowed"] == "pending")
		$status = "<font color=\"red\">" . $lang_offers ['text_pending'] . "</font>";
	elseif ($num ["allowed"] == "allowed")
		$status = "<font color=\"green\">" . $lang_offers ['text_allowed'] . "</font>";
	elseif ($num ["allowed"] == "freeze")
		$status = "<font color=\"red\">" . $lang_offers ['text_freeze'] . "</font>";
	else
		$status = "<font color=\"red\">" . $lang_offers ['text_denied'] . "</font>";
	tr ( $lang_offers ['row_status'], $status, 1 );
	// === if you want to have a pending thing for uploaders use this next bit
	if (get_user_class () >= $offermanage_class && $num ["allowed"] == "allowed")
		tr ( $lang_offers ['text_freeze'], "<table><tr>
	<td class=\"embedded\"><form method=\"post\" action=\"?freeze_offer=1\"><input type=\"hidden\" value=\"" . $id . "\" name=\"offerid\" />" . "<input class=\"btn\" type=\"submit\" value=\"" . $lang_offers ['submit_let_votes_freeze'] . "\" />&nbsp;&nbsp;</form></td>
	<td class=\"embedded\"><form method=\"post\" action=\"?id=" . $id . "&amp;finish_offer=1\">" . "<input type=\"hidden\" value=\"" . $id . "\" name=\"finish\" /><input 
	class=\"btn\" type=\"submit\" value=\"" . $lang_offers ['submit_let_votes_decide'] . "\" /></form></td>
	</tr></table>", 1 );
	
	if (get_user_class () >= $offermanage_class && $num ["allowed"] == "pending")
		tr ( $lang_offers ['row_allow'], "<table><tr>
	<td class=\"embedded\"><form method=\"post\" action=\"?allow_offer=1\"><input type=\"hidden\" value=\"" . $id . "\" 
	name=\"offerid\" />" . "<input class=\"btn\" type=\"submit\" value=\"" . $lang_offers ['submit_allow'] . "\" />&nbsp;&nbsp;</form></td>
	<td class=\"embedded\"><form method=\"post\" action=\"?freeze_offer=1\"><input type=\"hidden\" value=\"" . $id . "\" name=\"offerid\" />" . "<input class=\"btn\" type=\"submit\" value=\"" . $lang_offers ['submit_let_votes_freeze'] . "\" />&nbsp;&nbsp;</form></td>
	</tr></table>", 1 );
	
	if (get_user_class () >= $offermanage_class && $num ["allowed"] == "freeze")
		tr ( $lang_offers ['row_thaw'], "<table><tr>
	<td class=\"embedded\"><form method=\"post\" action=\"?thaw_offer=1\"><input type=\"hidden\" value=\"" . $id . "\" name=\"offerid\" />" . "<input class=\"btn\" type=\"submit\" value=\"" . $lang_offers ['submit_let_votes_cancel_freeze'] . "\" />&nbsp;&nbsp;</form></td>
	</tr></table>", 1 );
	
	$zres = sql_query ( "SELECT COUNT(*) from offervotes where vote='yeah' and offerid=$id" );
	$arr = mysql_fetch_row ( $zres );
	$za = $arr [0];
	$pres = sql_query ( "SELECT COUNT(*) from offervotes where vote='against' and offerid=$id" );
	$arr2 = mysql_fetch_row ( $pres );
	$protiv = $arr2 [0];
	// === in the following section, there is a line to report comment... either
	// remove the link or change it to work with your report script :)
	
	// if pending
	if ($num ["allowed"] == "pending") {
		tr ( $lang_offers ['row_vote'], "<b>" . "<a href=\"?id=" . $id . "&amp;vote=yeah\"><font color=\"green\">" . $lang_offers ['text_for'] . "</font></a></b>" . (get_user_class () >= $againstoffer_class ? " - <b><a href=\"?id=" . $id . "&amp;vote=against\">" . "<font color=\"red\">" . $lang_offers ['text_against'] . "</font></a></b>" : ""), 1 );
		tr ( $lang_offers ['row_vote_results'], "<b>" . $lang_offers ['text_for'] . ":</b> $za  <b>" . $lang_offers ['text_against'] . "</b> $protiv &nbsp; &nbsp; <a href=\"?id=" . $id . "&amp;offer_vote=1\"><i>" . $lang_offers ['text_see_vote_detail'] . "</i></a>", 1 );
	}
	// ===upload torrent message
	if ($num ["allowed"] == "allowed" && $CURUSER ["id"] != $num ["userid"])
		tr ( $lang_offers ['row_offer_allowed'], $lang_offers ['text_voter_receives_pm_note'], 1 );
	if ($num ["allowed"] == "allowed" && $CURUSER ["id"] == $num ["userid"]) {
		tr ( $lang_offers ['row_offer_allowed'], $lang_offers ['text_urge_upload_offer_note'], 1 );
	}
	if ($CURUSER [id] == $num [userid] || get_user_class () >= $offermanage_class) {
		$edit = "<a href=\"?id=" . $id . "&amp;edit_offer=1\"><img class=\"dt_edit\" src=\"pic/trans.gif\" alt=\"edit\" />&nbsp;<b><font class=\"small\">" . $lang_offers ['text_edit_offer'] . "</font></b></a>&nbsp;|&nbsp;";
		$delete = "<a href=\"?id=" . $id . "&amp;del_offer=1&amp;sure=0\"><img class=\"dt_delete\" src=\"pic/trans.gif\" alt=\"delete\" />&nbsp;<b><font class=\"small\">" . $lang_offers ['text_delete_offer'] . "</font></b></a>&nbsp;|&nbsp;";
	}
	$report = "<a href=\"report.php?reportofferid=" . $id . "\"><img class=\"dt_report\" src=\"pic/trans.gif\" alt=\"report\" />&nbsp;<b><font class=\"small\">" . $lang_offers ['report_offer'] . "</font></b></a>";
	tr ( $lang_offers ['row_action'], $edit . $delete . $report, 1 );
	
	/**
	 * ********************************************显示候选详细信息*************************************************
	 */
	$ret = sql_query ( "SELECT * FROM offersinfo WHERE offerid = " . $id . " LIMIT 1" ) or sqlerr ();
	$row_ = mysql_fetch_array ( $ret );
	$catid = $row_ [category];
	
	if ($catid != "407" && $catid != "410") {
		if ($row_ ["cname"] != "")
			$cname = $lang_offers ['cname'] . $row_ [cname] . "<br /><br />";
		if ($row_ ["ename"] != "")
			$ename = $lang_offers ['ename'] . $row_ [ename] . "<br /><br />";
		if ($row_ ["issuedate"] != "")
			$issuedate = $lang_offers ['issuedate'] . $row_ [issuedate] . "<br /><br />";
		if ($row_ ["subsinfo"] != 0) {
			$result = sql_query ( "SELECT * FROM subsinfo WHERE id = " . $row_ ["subsinfo"] );
			$result_ = mysql_fetch_array ( $result );
			$subsinfo = $lang_offers ['subsinfo'] . $result_ [name] . "<br /><br />";
		}
	}
	
	if ($catid == 401) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['movie'] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['catmovie'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["district"] != "")
			$district = $lang_offers ['districtmovie'] . $row_ [district] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formatmovie'] . $row_ [format] . "<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langmovie'] . $row_ [language] . "<br /><br />";
		if ($row_ ["imdbnum"] != "")
			$imdbnum = $lang_offers ['imdbnum'] . $row_ [imdbnum] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $issuedate . $subsinfo . $specificcat . $district . $format . $language . $imdbnum, 1 );
	}
	if ($catid == 402) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['tvseries'] . "<br /><br />";
		if ($row_ ["tvalias"] != "")
			$tvalias = $lang_offers ['tvalias'] . $row_ [tvalias] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formattvseries'] . $row_ [format] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['cattvseries'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langtvseries'] . $row_ [language] . "<br /><br />";
		if ($row_ ["tvseasoninfo"] != "")
			$tvseasoninfo = $lang_offers ['tvseasoninfo'] . $row_ [tvseasoninfo] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $issuedate . $specificcat . $format . $language . $subsinfo . $tvalias . $tvseasoninfo, 1 );
	}
	if ($catid == 403) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['tvshows'] . "<br /><br />";
		if ($row_ ["district"] != "")
			$district = $lang_offers ['districttvshows'] . $row_ [district] . "<br /><br />";
		if ($row_ ["tvshowscontent"] != "")
			$tvshowscontent = $lang_offers ['tvshowscontent'] . $row_ [tvshowscontent] . "<br /><br />";
		if ($row_ ["tvshowsguest"] != "")
			$tvshowsguest = $lang_offers ['tvshowsguest'] . $row_ [tvshowsguest] . "<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langtvshows'] . $row_ [language] . "<br /><br />";
		if ($row_ ["tvshowsremarks"] != "")
			$tvshowsremarks = $lang_offers ['tvshowsremarks'] . $row_ [tvshowsremarks] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formattvshows'] . $row_ [format] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $issuedate . $subsinfo . $district . $tvshowscontent . $tvshowsguest . $language . $format . $tvshowsremarks, 1 );
	}
	if ($catid == 404) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['doc'] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['catdocum'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formatdocum'] . $row_ [format] . "<br /><br />";
		if ($row_ ["version"] != "")
			$version = $lang_offers ['version'] . $row_ [version] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $issuedate . $specificcat . $format . $version, 1 );
	}
	if ($catid == 405) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['anime'] . "<br /><br />";
		if ($row_ ["animenum"] != "")
			$animenum = $lang_offers ['animenum'] . $row_ [animenum] . "<br /><br />";
		if ($row_ ["substeam"] != "")
			$substeam = $lang_offers ['substeam'] . $row_ [substeam] . "<br /><br />";
		if ($row_ ["resolution"] != "")
			$resolution = $lang_offers ['resolutionanime'] . $row_ [resolution] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['catanime'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["district"] != "")
			$district = $lang_offers ['districtanime'] . $row_ [district] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formatanime'] . $row_ [format] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $issuedate . $specificcat . $format . $animenum . $substeam . $resolution . $district, 1 );
	}
	if ($catid == 406) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['mv'] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['cathq'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["hqname"] != "")
			$hqname = $lang_offers ['hqname'] . $row_ [hqname] . "<br /><br />";
		if ($row_ ["artist"] != "")
			$artist = $lang_offers ['artist'] . $row_ [artist] . "<br /><br />";
		if ($row_ ["hqtone"] != "")
			$hqtone = $lang_offers ['hqtone'] . $row_ [hqtone] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formathq'] . $row_ [format] . "<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langhq'] . $row_ [language] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $hqname . $issuedate . $artist . $specificcat . $format . $language . $hqtone, 1 );
	}
	if ($catid == 407) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['sports'] . "<br /><br />";
		if ($row_ ["cname"] != "")
			$cname = $lang_offers ['matchcat'] . $row_ [cname] . "<br /><br />";
		if ($row_ ["ename"] != "")
			$ename = $lang_offers ['versus'] . $row_ [ename] . "<br /><br />";
		if ($row_ ["issuedate"] != "")
			$issuedate = $lang_offers ['matchdate'] . $row_ [issuedate] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['catsports'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langsports'] . $row_ [language] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formatsports'] . $row_ [format] . "<br /><br />";
		if ($row_ ["resolution"] != "")
			$resolution = $lang_offers ['resolutionsports'] . $row_ [resolution] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $issuedate . $specificcat . $format . $resolution . $language, 1 );
	}
	if ($catid == 408) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['software'] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['catsoftware'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langsoftware'] . $row_ [language] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formatsoftware'] . $row_ [format] . "<br /><br />";
		if ($row_ ["version"] != "")
			$version = $lang_offers ['version'] . $row_ [version] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $issuedate . $specificcat . $format . $language . $version, 1 );
	}
	if ($catid == 409) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['game'] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['catgame'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langgame'] . $row_ [language] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formatgame'] . $row_ [format] . "<br /><br />";
		if ($row_ ["platform"] != "")
			$platform = $lang_offers ['platform'] . $row_ [platform] . "<br /><br />";
		if ($row_ ["company"] != "")
			$company = $lang_offers ['company'] . $row_ [company] . "<br /><br />";
		if ($row_ ["tvshowsremarks"] != "")
			$tvshowsremarks = $lang_offers ['tvshowsremarks'] . $row_ [tvshowsremarks] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $specificcat . $format . $platform . $language . $company . $tvshowsremarks, 1 );
	}
	if ($catid == 410) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['misc'] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['catothers'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["cname"] != "") {
			$cname_ = $lang_offers ['othertitle'] . $row_ ["cname"] . "<br /><br />";
			if ($row_ ["format"] != "")
				$format = $lang_offers ['formatothers'] . $row_ [format] . "<br /><br />";
			if ($row_ ["tvshowsremarks"] != "")
				$tvshowsremarks = $lang_offers ['tvshowsremarks'] . $row_ [tvshowsremarks] . "<br /><br />";
			tr ( $lang_offers ['detailsinfo'], $category . $specificcat . $cname_ . $format . $tvshowsremarks, 1 );
		}
	}
	if ($catid == 411) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . $lang_offers ['newsreel'] . "<br /><br />";
		if ($row_ ["format"] != "")
			$format = $lang_offers ['formatnewsreel'] . $row_ [format] . "<br /><br />";
		if ($row_ ["specificcat"] != "")
			$specificcat = $lang_offers ['catnewsreel'] . $row_ [specificcat] . "<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langnewsreel'] . $row_ [language] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $issuedate . $specificcat . $format . $language . $subsinfo, 1 );
	}
	if ($catid == 412) {
		if ($row_ ["category"] != "")
			$category = $lang_offers ['category'] . "移动视频<br /><br />";
		if ($row_ ["language"] != "")
			$language = $lang_offers ['langmovie'] . $row_ [language] . "<br /><br />";
		if ($row_ ["district"] != "")
			$district = $lang_offers ['districtmovie'] . $row_ [district] . "<br /><br />";
		tr ( $lang_offers ['detailsinfo'], $category . $cname . $ename . $language . $subsinfo . $district, 1 );
	}
	if ($catid == 4013) {
		if ($row_ ["cname"] != "") {
			$cname_ = $lang_offers ['othertitle'] . $row_ ["cname"] . "<br /><br />";
			tr ( $lang_offers ['detailsinfo'], $cname_, 1 );
		}
	}
	/**
	 * *************************************************************************************************************
	 */
	
	if ($num ["descr"]) {
		$off_bb = format_comment ( $num ["descr"] );
		tr ( $lang_offers ['row_description'], $off_bb, 1 );
	}
	print ("</table>") ;
	// -----------------COMMENT SECTION ---------------------//
	$commentbar = "<p align=\"center\"><a class=\"index\" href=\"comment.php?action=add&amp;pid=" . $id . "&amp;type=offer\">" . $lang_offers ['text_add_comment'] . "</a></p>\n";
	$subres = sql_query ( "SELECT COUNT(*) FROM comments WHERE offer = $id" );
	$subrow = mysql_fetch_array ( $subres );
	$count = $subrow [0];
	if (! $count) {
		print ("<h1 id=\"startcomments\" align=\"center\">" . $lang_offers ['text_no_comments'] . "</h1>\n") ;
	} 

	else {
		list ( $pagertop, $pagerbottom, $limit ) = pager ( 10, $count, "offers.php?id=$id&off_details=1&", array (
				lastpagedefault => 1 
		) );
		
		$subres = sql_query ( "SELECT id, text, user, added, editedby, editdate FROM comments  WHERE offer = " . sqlesc ( $id ) . " ORDER BY id $limit" ) or sqlerr ( __FILE__, __LINE__ );
		$allrows = array ();
		while ( $subrow = mysql_fetch_array ( $subres ) )
			$allrows [] = $subrow;
			
			// end_frame();
			// print($commentbar);
		print ($pagertop) ;
		
		commenttable ( $allrows, "offer", $id );
		print ($pagerbottom) ;
	}
	
	print ('<script type="text/javascript">
function quick_reply_to(username)
{
	parent.document.getElementById("quickreplytext").focus();
    parent.document.getElementById("quickreplytext").value = "@" + username + " "+parent.document.getElementById("quickreplytext").value;
}
</script>') ;
	print ("<a name='quickreply' id='quickreply'> </a><table style='border:1px solid #000000;'><tr>" . "<td class=\"text\" align=\"center\"><b>" . $lang_offers ['text_quick_comment'] . "</b><br /><br />" . "<form id=\"compose\" name=\"comment\" method=\"post\" action=\"comment.php?action=add&amp;type=offer\" onsubmit=\"return postvalid(this);\">" . "<input type=\"hidden\" name=\"pid\" value=\"" . $id . "\" /><br />") ;
	quickreply ( 'comment', 'body', $lang_offers ['submit_add_comment'] );
	print ("</form></td></tr></table>") ;
	print ($commentbar) ;
	stdfoot ();
	die ();
}
// === end offer details
// === allow offer by staff
if ($_GET ["allow_offer"]) {
	
	if (get_user_class () < $offermanage_class)
		stderr ( $lang_offers ['std_access_denied'], $lang_offers ['std_mans_job'] );
	
	$allow_offer = 0 + $_GET ["allow_offer"];
	if ($allow_offer != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
		
		// === to allow the offer credit to S4NE for this next bit :)
		// if ($_POST["offerid"]){
	$offid = 0 + $_POST ["offerid"];
	if (! is_valid_id ( $offid ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$res = sql_query ( "SELECT users.username, offers.userid, offers.name FROM offers inner join users on offers.userid = users.id where offers.id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	$arr = mysql_fetch_assoc ( $res );
	if ($offeruptimeout_main) {
		$timeouthour = floor ( $offeruptimeout_main / 3600 );
		$timeoutnote = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_you_must_upload_in'] . $timeouthour . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_hours_otherwise'];
	} else
		$timeoutnote = "";
	$msg = "$CURUSER[username]" . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_has_allowed'] . "[b][url=offers.php?id=$offid&off_details=1]" . $arr [name] . "[/url][/b]. " . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_find_offer_option'] . $timeoutnote;
	
	$subject = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_your_offer_allowed'];
	$allowedtime = date ( "Y-m-d H:i:s" );
	sql_query ( "INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $arr[userid], '" . $allowedtime . "', " . sqlesc ( $msg ) . ", " . sqlesc ( $subject ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
	sql_query ( "UPDATE offers SET allowed = 'allowed', allowedtime = '" . $allowedtime . "' WHERE id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	
	write_log ( "管理员 $CURUSER[username] 允许了候选 $offid ($arr[name])", 'normal' );
	header ( "Refresh: 0; url=offers.php?id=$offid&off_details=1" );
}

// === end allow the offer

// === freeze offer by staff
if ($_GET ["freeze_offer"]) {
	
	if (get_user_class () < $offermanage_class)
		stderr ( $lang_offers ['std_access_denied'], $lang_offers ['std_mans_job'] );
	
	$freeze_offer = 0 + $_GET ["freeze_offer"];
	if ($freeze_offer != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
		
		// === to allow the offer credit to S4NE for this next bit :)
		// if ($_POST["offerid"]){
	$offid = 0 + $_POST ["offerid"];
	if (! is_valid_id ( $offid ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$res = sql_query ( "SELECT users.username, offers.userid, offers.name FROM offers inner join users on offers.userid = users.id where offers.id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	$arr = mysql_fetch_assoc ( $res );
	
	$msg = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_offer_is_wrong'] . "[b][url=userdetails.php?id=$CURUSER[id]]" . "$CURUSER[username]" . "[/url][/b] " . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_has_freezed'] . "[b][url=offers.php?id=$offid&off_details=1]" . $arr [name] . "[/url][/b]. " . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_revise_your_offer'];
	
	$subject = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_subject_freezed'];
	$allowedtime = date ( "Y-m-d H:i:s" );
	sql_query ( "INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $arr[userid], '" . $allowedtime . "', " . sqlesc ( $msg ) . ", " . sqlesc ( $subject ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
	sql_query ( "UPDATE offers SET allowed = 'freeze' WHERE id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	
	write_log ( "管理员 $CURUSER[username] 冻结了候选 $offid ($arr[name])", 'normal' );
	header ( "Refresh: 0; url=offers.php?id=$offid&off_details=1" );
}
// === end freeze the offer

// === thaw offer by staff
if ($_GET ["thaw_offer"]) {
	
	if (get_user_class () < $offermanage_class)
		stderr ( $lang_offers ['std_access_denied'], $lang_offers ['std_mans_job'] );
	
	$thaw_offer = 0 + $_GET ["thaw_offer"];
	if ($thaw_offer != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
		
		// === to allow the offer credit to S4NE for this next bit :)
		// if ($_POST["offerid"]){
	$offid = 0 + $_POST ["offerid"];
	if (! is_valid_id ( $offid ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$res = sql_query ( "SELECT users.username, offers.userid, offers.name FROM offers inner join users on offers.userid = users.id where offers.id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	$arr = mysql_fetch_assoc ( $res );
	
	$msg = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_your_offer'] . "[b][url=offers.php?id=$offid&off_details=1]" . $arr [name] . "[/url][/b]. " . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_was_thawed_by'] . "[b][url=userdetails.php?id=$CURUSER[id]]" . "$CURUSER[username]" . "[/url][/b] " . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_has_thawed'];
	
	$subject = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_subject_thawed'];
	$allowedtime = date ( "Y-m-d H:i:s" );
	sql_query ( "INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $arr[userid], '" . $allowedtime . "', " . sqlesc ( $msg ) . ", " . sqlesc ( $subject ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
	
	write_log ( "管理员 $CURUSER[username] 解冻了候选 $offid ($arr[name])", 'normal' );
	
	/* 分别处理冻结前为允许和待定的情况 开始 */
	$rs = sql_query ( "SELECT yeah, against, allowed FROM offers WHERE id=" . sqlesc ( $offid ) ) or sqlerr ( __FILE__, __LINE__ );
	$ya_arr = mysql_fetch_assoc ( $rs );
	$yeah = $ya_arr ["yeah"];
	$against = $ya_arr ["against"];
	$allowed = $ya_arr ["allowed"];
	$finishtime = date ( "Y-m-d H:i:s" );
	
	if (($yeah - $against) >= $minoffervotes || $allowed == "allowed") {
		// 处理解冻后直接变为允许
		if ($offeruptimeout_main) {
			$timeouthour = floor ( $offeruptimeout_main / 3600 );
			$timeoutnote = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_you_must_upload_in'] . $timeouthour . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_hours_otherwise'];
		} else
			$timeoutnote = "";
		
		sql_query ( "UPDATE offers SET allowed='allowed', allowedtime=" . sqlesc ( $finishtime ) . " WHERE id=" . sqlesc ( $offid ) ) or sqlerr ( __FILE__, __LINE__ );
		$msg = $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_offer_voted_on'] . "[b][url=offers.php?id=$offerid&off_details=1]" . $arr [name] . "[/url][/b]." . $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_find_offer_option'] . $timeoutnote;
		$subject = $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_your_offer_allowed'];
		sql_query ( "INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $arr[userid], " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ", " . sqlesc ( $msg ) . ", " . sqlesc ( $subject ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		write_log ( "系统通过了候选 $offid ($arr[name])", 'normal' );
	} else {
		// 处理解冻后变为待定
		sql_query ( "UPDATE offers SET allowed = 'pending' WHERE id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	}
	/* 分别处理冻结前为允许和待定的情况 结束 */
	
	header ( "Refresh: 0; url=offers.php?id=$offid&off_details=1" );
}
// === end thaw the offer

// === allow offer by vote
if ($_GET ["finish_offer"]) {
	
	if (get_user_class () < $offermanage_class)
		stderr ( $lang_offers ['std_access_denied'], $lang_offers ['std_have_no_permission'] );
	
	$finish_offer = 0 + $_GET ["finish_offer"];
	if ($finish_offer != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$offid = 0 + $_POST ["finish"];
	if (! is_valid_id ( $offid ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$res = sql_query ( "SELECT users.username, offers.userid, offers.name FROM offers inner join users on offers.userid = users.id where offers.id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	$arr = mysql_fetch_assoc ( $res );
	
	$voteresyes = sql_query ( "SELECT COUNT(*) from offervotes where vote='yeah' and offerid=$offid" );
	$arryes = mysql_fetch_row ( $voteresyes );
	$yes = $arryes [0];
	$voteresno = sql_query ( "SELECT COUNT(*) from offervotes where vote='against' and offerid=$offid" );
	$arrno = mysql_fetch_row ( $voteresno );
	$no = $arrno [0];
	
	if (($yes - $no) >= $minoffervotes) {
		if ($offeruptimeout_main) {
			$timeouthour = floor ( $offeruptimeout_main / 3600 );
			$timeoutnote = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_you_must_upload_in'] . $timeouthour . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_hours_otherwise'];
		} else
			$timeoutnote = "";
		$msg = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_offer_voted_on'] . "[b][url=offers.php?id=$offid&off_details=1]" . $arr [name] . "[/url][/b]." . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_find_offer_option'] . $timeoutnote;
		sql_query ( "UPDATE offers SET allowed = 'allowed',allowedtime ='" . $finishvotetime . "' WHERE id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	} else if (($no - $yes) >= $minoffervotes) {
		$msg = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_offer_voted_off'] . "[b][url=offers.php?id=$offid&off_details=1]" . $arr [name] . "[/url][/b]." . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_offer_deleted'];
		sql_query ( "UPDATE offers SET allowed = 'denied' WHERE id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	} else {
		sql_query ( "UPDATE offers SET allowed = 'pending' WHERE id = $offid" ) or sqlerr ( __FILE__, __LINE__ );
	}
	
	// ===use this line if you DO HAVE subject in your PM system
	$subject = $lang_offers_target [get_user_lang ( $arr [userid] )] ['msg_your_offer'] . $arr [name] . $lang_offers_target [get_user_lang ( $arr [userid] )] ['msg_voted_on'];
	sql_query ( "INSERT INTO messages (sender, subject, receiver, added, msg) VALUES(0, " . sqlesc ( $subject ) . ", $arr[userid], '" . $finishvotetime . "', " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
	// ===use this line if you DO NOT subject in your PM system
	// sql_query("INSERT INTO messages (sender, receiver, added, msg) VALUES(0,
	// $arr[userid], '" . date("Y-m-d H:i:s") . "', " . sqlesc($msg) . ")") or
	// sqlerr(__FILE__, __LINE__);
	// write_log("$CURUSER[username] $arr[name]",'normal');
	
	header ( "Refresh: 0; url=offers.php?id=$offid&off_details=1" );
	die ();
}
// ===end allow offer by vote

// === edit offer

if ($_GET ["edit_offer"]) {
	
	$edit_offer = 0 + $_GET ["edit_offer"];
	if ($edit_offer != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$id = 0 + $_GET ["id"];
	
	$res = sql_query ( "SELECT * FROM offers WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
	$num = mysql_fetch_array ( $res );
	
	$timezone = $num ["added"];
	
	$s = $num ["name"];
	$id2 = $num ["category"];
	
	if ($CURUSER ["id"] != $num ["userid"] && get_user_class () < $offermanage_class)
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_cannot_edit_others_offer'] );
	
	$body = htmlspecialchars ( unesc ( $num ["descr"] ) );
	$s2 = "<select name=\"category\" id=\"oricat\" onChange=\"getcategory('class1','oricat')\" >\n";
	
	$cats = genrelist ( $browsecatmode );
	
	foreach ( $cats as $row )
		$s2 .= "<option value=\"" . $row ["id"] . "\" " . ($row ['id'] == $id2 ? " selected=\"selected\"" : "") . ">" . htmlspecialchars ( $row ["name"] ) . "</option>\n";
	$s2 .= "</select><div id=\"class1\" ></div><div id=\"editclass\" ></div>\n";
	
	stdhead ( $lang_offers ['head_edit_offer'] . ": $s" );
	$title = htmlspecialchars ( trim ( $s ) );
	
	print ("<form id=\"compose\" method=\"post\" name=\"compose\" action=\"?id=" . $id . "&amp;take_off_edit=1\" onsubmit=\"return validate('oricat')\">" . "<table width=\"940\" cellspacing=\"0\" cellpadding=\"3\"><tr><td class=\"colhead\" align=\"center\" colspan=\"2\">" . $lang_offers ['text_edit_offer'] . "</td></tr>") ;
	tr ( $lang_offers ['row_type'] . "<font color=\"red\">*</font>", $s2, 1 );
	// tr($lang_offers['row_title']."<font color=\"red\">*</font>", "<input
	// type=\"text\" style=\"width: 650px\" name=\"name\" value=\"".$title."\"
	// />", 1);
	// tr($lang_offers['row_post_or_photo'], "<input type=\"text\"
	// name=\"picture\" style=\"width: 650px\" value='' /><br
	// />".$lang_offers['text_link_to_picture'], 1);
	print ("<tr><td class=\"rowhead\" align=\"right\" valign=\"top\"><b>" . $lang_offers ['row_description'] . "<font color=\"red\">*</font></b></td><td class=\"rowfollow\" align=\"left\">") ;
	textbbcode ( "compose", "body", $body, false );
	print ("</td></tr>") ;
	print ("<tr><td class=\"toolbox\" style=\"vertical-align: middle; padding-top: 10px; padding-bottom: 10px;\" align=\"center\" colspan=\"2\"><input id=\"qr\" type=\"submit\" value=\"" . $lang_offers ['submit_edit_offer'] . "\" class=\"btn\" /></td></tr></table></form><br />\n") ;
	stdfoot ();
	die ();
}
// === end edit offer

// ==== take offer edit
if ($_GET ["take_off_edit"]) {
	
	$take_off_edit = 0 + $_GET ["take_off_edit"];
	if ($take_off_edit != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$id = 0 + $_GET ["id"];
	
	$res = sql_query ( "SELECT userid FROM offers WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
	$num = mysql_fetch_array ( $res );
	
	if ($CURUSER [id] != $num [userid] && get_user_class () < $offermanage_class)
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_access_denied'] );
		
		// $name = $_POST["name"];
	
	if (! empty ( $_POST ['picture'] )) {
		$picture = unesc ( $_POST ["picture"] );
		if (! preg_match ( "/^http:\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $picture ))
			stderr ( $lang_offers ['std_error'], $lang_offers ['std_wrong_image_format'] );
		$pic = "[img]" . $picture . "[/img]\n";
	}
	$descr = "$pic";
	$descr .= unesc ( $_POST ["body"] );
	/*
	 * if (!$name) bark($lang_offers['std_must_enter_name']);
	 */
	if (! $descr)
		bark ( $lang_offers ['std_must_enter_description'] );
	$cat = (0 + $_POST ["category"]);
	if (! is_valid_id ( $cat ))
		bark ( $lang_offers ['std_must_select_category'] );
		
		// $name = sqlesc($name);
	$descr = sqlesc ( $descr );
	$cat = sqlesc ( $cat );
	
	sql_query ( "UPDATE offers SET category=$cat, descr=$descr where id=" . sqlesc ( $id ) );
	
	/**
	 * *************************接收用户输入分类信息及保存分类信息***************************************
	 */
	$catid = $cat;
	$updateinfoset [] = "category = " . mysql_escape_string ( $catid );
	$cname = unesc ( trimcomma ( $_POST ["cname"] ) );
	$updateinfoset [] = "cname = '" . mysql_escape_string ( $cname ) . "'";
	$ename = unesc ( trimcomma ( $_POST ["ename"] ) );
	$updateinfoset [] = "ename = '" . mysql_escape_string ( $ename ) . "'";
	$specificcat = unesc ( trimcomma ( $_POST ["specificcat"] ) );
	$updateinfoset [] = "specificcat = '" . mysql_escape_string ( $specificcat ) . "'";
	$issuedate = unesc ( trimcomma ( $_POST ["issuedate"] ) );
	$updateinfoset [] = "issuedate = '" . mysql_escape_string ( $issuedate ) . "'";
	$subsinfo = unesc ( trimcomma ( $_POST ["subsinfo"] ) );
	$updateinfoset [] = "subsinfo = '" . mysql_escape_string ( $subsinfo ) . "'";
	$district = unesc ( trimcomma ( $_POST ["district"] ) );
	$updateinfoset [] = "district = '" . mysql_escape_string ( $district ) . "'";
	$format = unesc ( trimcomma ( $_POST ["format"] ) );
	$updateinfoset [] = "format = '" . mysql_escape_string ( $format ) . "'";
	$language = unesc ( trimcomma ( $_POST ["language"] ) );
	$updateinfoset [] = "language = '" . mysql_escape_string ( $language ) . "'";
	if (is_banned_title ( $cname, $catid )) {
	bark ( $lang_takeupload ['std_banned_title1'] . $cname . $lang_takeupload ['std_banned_title2'] . $lang_takeupload ['std_banned_title_hit'] );
} else if (is_banned_title ( $ename, $catid )) {
	bark ( $lang_takeupload ['std_banned_title1'] . $ename . $lang_takeupload ['std_banned_title2'] . $lang_takeupload ['std_banned_title_hit'] );
}
	if ($catid == 401) {
		$imdbnum = unesc ( trimcomma ( $_POST ["imdbnum"] ) );
		$updateinfoset [] = "imdbnum = '" . mysql_escape_string ( $imdbnum ) . "'";
		if ($district != "")
			$nameset .= "[" . $district . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
	}
	if ($catid == 402) {
		$tvalias = unesc ( trimcomma ( $_POST ["tvalias"] ) );
		$updateinfoset [] = "tvalias = '" . mysql_escape_string ( $tvalias ) . "'";
		$tvseasoninfo = unesc ( trimcomma ( $_POST ["tvseasoninfo"] ) );
		$updateinfoset [] = "tvseasoninfo = '" . mysql_escape_string ( $tvseasoninfo ) . "'";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($tvseasoninfo != "")
			$nameset .= "[" . $tvseasoninfo . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($language != "")
			$nameset .= "[" . $language . "]";
		if ($subsinfo != 0) {
			$result = sql_query ( "SELECT * FROM subsinfo WHERE id = " . $subsinfo );
			$result_ = mysql_fetch_array ( $result );
			$nameset .= "[" . $result_ [name] . "]";
		}
	}
	if ($catid == 403) {
		$tvshowscontent = unesc ( trimcomma ( $_POST ["tvshowscontent"] ) );
		$updateinfoset [] = "tvshowscontent = '" . mysql_escape_string ( $tvshowscontent ) . "'";
		$tvshowsguest = unesc ( trimcomma ( $_POST ["tvshowsguest"] ) );
		$updateinfoset [] = "tvshowsguest = '" . mysql_escape_string ( $tvshowsguest ) . "'";
		$tvshowsremarks = unesc ( trimcomma ( $_POST ["tvshowsremarks"] ) );
		$updateinfoset [] = "tvshowsremarks = '" . mysql_escape_string ( $tvshowsremarks ) . "'";
		if ($district != "")
			$nameset .= "[" . $district . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($tvshowscontent != "")
			$nameset .= "[" . $tvshowscontent . "]";
	}
	if ($catid == 404) {
		$version = unesc ( trimcomma ( $_POST ["version"] ) );
		$updateinfoset [] = "version = '" . mysql_escape_string ( $version ) . "'";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
	}
	if ($catid == 405) {
		$animenum = unesc ( trimcomma ( $_POST ["animenum"] ) );
		$updateinfoset [] = "animenum = '" . mysql_escape_string ( $animenum ) . "'";
		$substeam = unesc ( trimcomma ( $_POST ["substeam"] ) );
		$updateinfoset [] = "substeam = '" . mysql_escape_string ( $substeam ) . "'";
		$resolution = unesc ( trimcomma ( $_POST ["resolution"] ) );
		$updateinfoset [] = "resolution = '" . mysql_escape_string ( $resolution ) . "'";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($substeam != "")
			$nameset .= "[" . $substeam . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($resolution != "")
			$nameset .= "[" . $resolution . "]";
		if ($animenum != "")
			$nameset .= "[" . $animenum . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
	}
	if ($catid == 406) {
		$hqname = unesc ( trimcomma ( $_POST ["hqname"] ) );
		$updateinfoset [] = "hqname = '" . mysql_escape_string ( $hqname ) . "'";
		$artist = unesc ( trimcomma ( $_POST ["artist"] ) );
		$updateinfoset [] = "artist = '" . mysql_escape_string ( $artist ) . "'";
		$hqtone = unesc ( trimcomma ( $_POST ["hqtone"] ) );
		$updateinfoset [] = "hqtone = '" . mysql_escape_string ( $hqtone ) . "'";
		if ($hqname != "")
			$nameset .= "[" . $hqname . "]";
		if ($artist != "")
			$nameset .= "[" . $artist . "]";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
	}
	if ($catid == 407) {
		$resolution = unesc ( trimcomma ( $_POST ["resolution"] ) );
		$updateinfoset [] = "resolution = '" . mysql_escape_string ( $resolution ) . "'";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($language != "")
			$nameset .= "[" . $language . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
	}
	if ($catid == 408) {
		$version = unesc ( trimcomma ( $_POST ["version"] ) );
		$updateinfoset [] = "version = '" . mysql_escape_string ( $version ) . "'";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($issuedate != "")
			$nameset .= "[" . $issuedate . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
	}
	if ($catid == 409) {
		$company = unesc ( trimcomma ( $_POST ["company"] ) );
		$updateinfoset [] = "company = '" . mysql_escape_string ( $company ) . "'";
		$platform = unesc ( trimcomma ( $_POST ["platform"] ) );
		$updateinfoset [] = "platform = '" . mysql_escape_string ( $platform ) . "'";
		$tvshowsremarks = unesc ( trimcomma ( $_POST ["tvshowsremarks"] ) );
		$updateinfoset [] = "tvshowsremarks = '" . mysql_escape_string ( $tvshowsremarks ) . "'";
		if ($platform != "")
			$nameset .= "[" . $platform . "]";
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($language != "")
			$nameset .= "[" . $language . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($tvshowsremarks != "")
			$nameset .= "[" . $tvshowsremarks . "]";
	}
	if ($catid == 410) {
		$tvshowsremarks = unesc ( trimcomma ( $_POST ["tvshowsremarks"] ) );
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($tvshowsremarks != "")
			$nameset .= "[" . $tvshowsremarks . "]";
	}
	if ($catid == 411) {
		if ($specificcat != "")
			$nameset .= "[" . $specificcat . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($format != "")
			$nameset .= "[" . $format . "]";
		if ($subsinfo != 0) {
			$result = sql_query ( "SELECT * FROM subsinfo WHERE id = " . $subsinfo );
			$result_ = mysql_fetch_array ( $result );
			$nameset .= "[" . $result_ [name] . "]";
		}
	}
	if ($catid == 412) {
		if ($district != "")
			$nameset .= "[" . $district . "]";
		if ($cname != "")
			$nameset .= "[" . $cname . "]";
		if ($ename != "")
			$nameset .= "[" . $ename . "]";
		if ($language != "")
			$nameset .= "[" . $language . "]";
	}
	// echo $updateinfoset[0].'99'.$updateinfoset[1].'99'.$updateinfoset[2];
	sql_query ( "UPDATE offersinfo SET " . join ( ",", $updateinfoset ) . " WHERE offerid = $id" ) or sqlerr ( __FILE__, __LINE__ );
	
	if ($nameset != "") {
		sql_query ( "UPDATE offers SET name = " . sqlesc ( $nameset ) . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
	}
	
/*	if ($catid == 410 || $catid == 4013)
		if ($cname != "")
			sql_query ( "UPDATE offers SET name = " . sqlesc ( $cname ) . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );*/

/**
 * *********************************************************************************************
 */
	
	// header("Refresh: 0; url=offers.php?id=$id&off_details=1");
}
// ======end take offer edit

// === offer votes list
if ($_GET ["offer_vote"]) {
	
	$offer_vote = 0 + $_GET ["offer_vote"];
	if ($offer_vote != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$offerid = 0 + htmlspecialchars ( $_GET [id] );
	
	$res2 = sql_query ( "SELECT COUNT(*) FROM offervotes WHERE offerid = " . sqlesc ( $offerid ) ) or sqlerr ( __FILE__, __LINE__ );
	$row = mysql_fetch_array ( $res2 );
	$count = $row [0];
	
	$offername = get_single_value ( "offers", "name", "WHERE id=" . sqlesc ( $offerid ) );
	stdhead ( $lang_offers ['head_offer_voters'] . " - \"" . $offername . "\"" );
	
	print ("<h1 align=center>" . $lang_offers ['text_vote_results_for'] . " <a  href=offers.php?id=$offerid&off_details=1><b>" . htmlspecialchars ( $offername ) . "</b></a></h1>") ;
	
	$perpage = 20;
	list ( $pagertop, $pagerbottom, $limit ) = pager ( $perpage, $count, $_SERVER ["PHP_SELF"] . "?id=" . $offerid . "&offer_vote=1&" );
	$res = sql_query ( "SELECT * FROM offervotes WHERE offerid=" . sqlesc ( $offerid ) . " " . $limit ) or sqlerr ( __FILE__, __LINE__ );
	
	if (mysql_num_rows ( $res ) == 0)
		print ("<p align=center><b>" . $lang_offers ['std_no_votes_yet'] . "</b></p>\n") ;
	else {
		echo $pagertop;
		print ("<table border=1 cellspacing=0 cellpadding=5><tr><td class=colhead>" . $lang_offers ['col_user'] . "</td><td class=colhead align=left>" . $lang_offers ['col_vote'] . "</td>\n") ;
		
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			if ($arr [vote] == 'yeah')
				$vote = "<b><font color=green>" . $lang_offers ['text_for'] . "</font></b>";
			elseif ($arr [vote] == 'against')
				$vote = "<b><font color=red>" . $lang_offers ['text_against'] . "</font></b>";
			else
				$vote = "unknown";
			
			print ("<tr><td class=rowfollow>" . get_username ( $arr ['userid'] ) . "</td><td class=rowfollow align=left >" . $vote . "</td></tr>\n") ;
		}
		print ("</table>\n") ;
		echo $pagerbottom;
	}
	
	stdfoot ();
	die ();
}
// === end offer votes list

// === offer votes
if ($_GET ["vote"]) {
	$offerid = 0 + htmlspecialchars ( $_GET ["id"] );
	$vote = htmlspecialchars ( $_GET ["vote"] );
	if ($vote == 'against' && get_user_class () < $againstoffer_class)
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	if ($vote == 'yeah' || $vote == 'against') {
		$userid = 0 + $CURUSER ["id"];
		$res = sql_query ( "SELECT * FROM offervotes WHERE offerid=" . sqlesc ( $offerid ) . " AND userid=" . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
		$arr = mysql_fetch_assoc ( $res );
		$voted = $arr;
		$offer_userid = get_single_value ( "offers", "userid", "WHERE id=" . sqlesc ( $offerid ) );
		if ($offer_userid == $CURUSER ['id']) {
			stderr ( $lang_offers ['std_error'], $lang_offers ['std_cannot_vote_youself'] );
		} elseif ($voted) {
			stderr ( $lang_offers ['std_already_voted'], $lang_offers ['std_already_voted_note'] . "<a  href=offers.php?id=$offerid&off_details=1>" . $lang_offers ['std_back_to_offer_detail'], false );
		} else {
			sql_query ( "UPDATE offers SET $vote = $vote + 1 WHERE id=" . sqlesc ( $offerid ) ) or sqlerr ( __FILE__, __LINE__ );
			
			$res = sql_query ( "SELECT users.username, offers.userid, offers.name FROM offers LEFT JOIN users ON offers.userid = users.id WHERE offers.id = " . sqlesc ( $offerid ) ) or sqlerr ( __FILE__, __LINE__ );
			$arr = mysql_fetch_assoc ( $res );
			
			$rs = sql_query ( "SELECT yeah, against, allowed FROM offers WHERE id=" . sqlesc ( $offerid ) ) or sqlerr ( __FILE__, __LINE__ );
			$ya_arr = mysql_fetch_assoc ( $rs );
			$yeah = $ya_arr ["yeah"];
			$against = $ya_arr ["against"];
			$finishtime = date ( "Y-m-d H:i:s" );
			// allowed and send offer voted on message
			if (($yeah - $against) >= $minoffervotes && $ya_arr ['allowed'] == "pending") {
				if ($offeruptimeout_main) {
					$timeouthour = floor ( $offeruptimeout_main / 3600 );
					$timeoutnote = $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_you_must_upload_in'] . $timeouthour . $lang_offers_target [get_user_lang ( $arr ["userid"] )] ['msg_hours_otherwise'];
				} else
					$timeoutnote = "";
				sql_query ( "UPDATE offers SET allowed='allowed', allowedtime=" . sqlesc ( $finishtime ) . " WHERE id=" . sqlesc ( $offerid ) ) or sqlerr ( __FILE__, __LINE__ );
				$msg = $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_offer_voted_on'] . "[b][url=offers.php?id=$offerid&off_details=1]" . $arr [name] . "[/url][/b]." . $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_find_offer_option'] . $timeoutnote;
				$subject = $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_your_offer_allowed'];
				sql_query ( "INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $arr[userid], " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ", " . sqlesc ( $msg ) . ", " . sqlesc ( $subject ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
				write_log ( "系统通过了候选 $offerid ($arr[name])", 'normal' );
			}
			// denied and send offer voted off message
			if (($against - $yeah) >= $minoffervotes && $ya_arr ['allowed'] != "denied") {
				sql_query ( "UPDATE offers SET allowed='denied' WHERE id=" . sqlesc ( $offerid ) ) or sqlerr ( __FILE__, __LINE__ );
				$msg = $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_offer_voted_off'] . "[b][url=offers.php?id=$offid&off_details=1]" . $arr [name] . "[/url][/b]." . $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_offer_deleted'];
				$subject = $lang_offers_target [get_user_lang ( $arr ['userid'] )] ['msg_offer_deleted'];
				sql_query ( "INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $arr[userid], " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ", " . sqlesc ( $msg ) . ", " . sqlesc ( $subject ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
				write_log ( "系统拒绝了候选 $offerid ($arr[name])", 'normal' );
			}
			
			sql_query ( "INSERT INTO offervotes (offerid, userid, vote) VALUES($offerid, $userid, " . sqlesc ( $vote ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
			KPS ( "+", $offervote_bonus, $CURUSER ["id"] );
			stdhead ( $lang_offers ['head_vote_for_offer'] );
			print ("<h1 align=center>" . $lang_offers ['std_vote_accepted'] . "</h1>") ;
			print ($lang_offers ['std_vote_accepted_note'] . "<a  href=offers.php?id=$offerid&off_details=1>" . $lang_offers ['std_back_to_offer_detail']) ;
			stdfoot ();
			die ();
		}
	} else
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
}
// === end offer votes

// === delete offer
if ($_GET ["del_offer"]) {
	
	$del_offer = 0 + $_GET ["del_offer"];
	if ($del_offer != '1')
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$offer = 0 + $_GET ["id"];
	
	$userid = 0 + $CURUSER ["id"];
	if (! is_valid_id ( $userid ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	
	$res = sql_query ( "SELECT * FROM offers WHERE id = $offer" ) or sqlerr ( __FILE__, __LINE__ );
	$num = mysql_fetch_array ( $res );
	
	$name = $num ["name"];
	
	if ($userid != $num ["userid"] && get_user_class () < $offermanage_class)
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_cannot_delete_others_offer'] );
	
	if ($_GET ["sure"]) {
		$sure = $_GET ["sure"];
		if ($sure == '0' || $sure == '1')
			$sure = 0 + $_GET ["sure"];
		else
			stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
	}
	
	if ($sure == 0)
		stderr ( $lang_offers ['std_delete_offer'], $lang_offers ['std_delete_offer_note'] . "<br /><form method=post action=offers.php?id=$offer&del_offer=1&sure=1>" . $lang_offers ['text_reason_is'] . "<input type=text style=\"width: 200px\" name=reason><input type=submit value=\"" . $lang_offers ['submit_confirm'] . "\"></form>", false );
	elseif ($sure == 1) {
		$reason = $_POST ["reason"];
		sql_query ( "DELETE FROM offers WHERE id=$offer" );
		sql_query ( "DELETE FROM offervotes WHERE offerid=$offer" );
		sql_query ( "DELETE FROM comments WHERE offer=$offer" );
		
		// ===add karma //=== use this if you use the karma mod
		// sql_query("UPDATE users SET seedbonus = seedbonus-10.0 WHERE id =
		// $num[userid]") or sqlerr(__FILE__, __LINE__);
		// ===end
		
		if ($CURUSER ["id"] != $num ["userid"]) {
			$added = sqlesc ( date ( "Y-m-d H:i:s" ) );
			$subject = sqlesc ( $lang_offers_target [get_user_lang ( $num ["userid"] )] ['msg_offer_deleted'] );
			$msg = sqlesc ( $lang_offers_target [get_user_lang ( $num ["userid"] )] ['msg_your_offer'] . $num [name] . $lang_offers_target [get_user_lang ( $num ["userid"] )] ['msg_was_deleted_by'] . "[url=userdetails.php?id=" . $CURUSER ['id'] . "]" . $CURUSER ['username'] . "[/url]" . $lang_offers_target [get_user_lang ( $num ["userid"] )] ['msg_blank'] . ($reason != "" ? $lang_offers_target [get_user_lang ( $num ["userid"] )] ['msg_reason_is'] . $reason : "") );
			sql_query ( "INSERT INTO messages (sender, receiver, msg, added, subject) VALUES(0, $num[userid], $msg, $added, $subject)" ) or sqlerr ( __FILE__, __LINE__ );
		}
		write_log ( ($CURUSER ["id"] != $num ["userid"] ? "管理员 " : "发布者 ") . "$CURUSER[username] 删除了候选 $offer ($num[name])。" . ($reason != "" ? " 理由：" . $reason . "" : ""), 'normal' );
		header ( "Refresh: 0; url=offers.php" );
		die ();
	} else
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
}
// == end delete offer

// === prolly not needed, but what the hell... basically stopping the page
// getting screwed up
if ($_GET ["sort"]) {
	$sort = $_GET ["sort"];
	if ($sort == 'cat' || $sort == 'name' || $sort == 'added' || $sort == 'comments' || $sort == 'yeah' || $sort == 'against' || $sort == 'v_res')
		$sort = $_GET ["sort"];
	else
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
}
// === end of prolly not needed, but what the hell :P

$categ = 0 + $_GET ["category"];

if ($_GET ["offerorid"]) {
	$offerorid = 0 + htmlspecialchars ( $_GET ["offerorid"] );
	if (preg_match ( "/^[0-9]+$/", ! $offerorid ))
		stderr ( $lang_offers ['std_error'], $lang_offers ['std_smell_rat'] );
}

$search = ($_GET ["search"]);

if ($search) {
	$search = " AND offers.name like " . sqlesc ( "%$search%" );
} else {
	$search = "";
}

$cat_order_type = "desc";
$name_order_type = "desc";
$added_order_type = "desc";
$comments_order_type = "desc";
$v_res_order_type = "desc";

/*
 * if ($cat_order_type == "") { $sort = " ORDER BY added " . $added_order_type;
 * $cat_order_type = "asc"; } // for torrent name if ($name_order_type == "") {
 * $sort = " ORDER BY added " . $added_order_type; $name_order_type = "desc"; }
 * if ($added_order_type == "") { $sort = " ORDER BY added " .
 * $added_order_type; $added_order_type = "desc"; } if ($comments_order_type ==
 * "") { $sort = " ORDER BY added " . $added_order_type; $comments_order_type =
 * "desc"; } if ($v_res_order_type == "") { $sort = " ORDER BY added " .
 * $added_order_type; $v_res_order_type = "desc"; }
 */

if ($sort == "cat") {
	if ($_GET ['type'] == "desc")
		$cat_order_type = "asc";
	$sort = " ORDER BY category " . $cat_order_type;
} else if ($sort == "name") {
	if ($_GET ['type'] == "desc")
		$name_order_type = "asc";
	$sort = " ORDER BY name " . $name_order_type;
} else if ($sort == "added") {
	if ($_GET ['type'] == "desc")
		$added_order_type = "asc";
	$sort = " ORDER BY added " . $added_order_type;
} else if ($sort == "comments") {
	if ($_GET ['type'] == "desc")
		$comments_order_type = "asc";
	$sort = " ORDER BY comments " . $comments_order_type;
} else if ($sort == "v_res") {
	if ($_GET ['type'] == "desc")
		$v_res_order_type = "asc";
	$sort = " ORDER BY (yeah - against) " . $v_res_order_type;
}

if ($offerorid != NULL) {
	if (($categ != NULL) && ($categ != 0))
		$categ = "WHERE offers.category = " . $categ . " AND offers.userid = " . $offerorid;
	else
		$categ = "WHERE offers.userid = " . $offerorid;
} 

else if ($categ == 0)
	$categ = '';
else
	$categ = "WHERE offers.category = " . $categ;

$res = sql_query ( "SELECT count(offers.id) FROM offers inner join categories on offers.category = categories.id inner join users on offers.userid = users.id  $categ $search" ) or sqlerr ( __FILE__, __LINE__ );
$row = mysql_fetch_array ( $res );
$count = $row [0];

$perpage = 20;

list ( $pagertop, $pagerbottom, $limit ) = pager ( $perpage, $count, "offers.php" . "?" . "category=" . $_GET ["category"] . "&sort=" . $_GET ["sort"] . "&" );

// stderr("", $sort);
if ($sort == "")
	$sort = "ORDER BY added desc ";

$res = sql_query ( "SELECT offers.id, offers.userid, offers.name, offers.added, offers.allowedtime, offers.comments, offers.yeah, offers.against, offers.category as cat_id, offers.allowed, categories.image, categories.name as cat FROM offers inner join categories on offers.category = categories.id $categ $search $sort $limit" ) or sqlerr ( __FILE__, __LINE__ );
$num = mysql_num_rows ( $res );

stdhead ( $lang_offers ['head_offers'] );
begin_main_frame ();
begin_frame ( $lang_offers ['text_offers_section'], true, 10, "100%", "center" );

print ("<p align=\"left\"><b><font size=\"5\">" . $lang_offers ['text_rules'] . "</font></b></p>\n") ;
print ("<div align=\"left\"><ul>") ;
print ("<li>" . $lang_offers ['text_rule_one_one'] . get_user_class_name ( $upload_class, false, true, true ) . $lang_offers ['text_rule_one_two'] . get_user_class_name ( $addoffer_class, false, true, true ) . $lang_offers ['text_rule_one_three'] . "</li>\n") ;
print ("<li>" . $lang_offers ['text_rule_two_one'] . "<b>" . $minoffervotes . "</b>" . $lang_offers ['text_rule_two_two'] . "</li>\n") ;
if ($offervotetimeout_main)
	print ("<li>" . $lang_offers ['text_rule_three_one'] . "<b>" . ($offervotetimeout_main / 3600) . "</b>" . $lang_offers ['text_rule_three_two'] . $lang_offers ['text_rule_four_one'] . "<b>" . ($offeruptimeout_main / 3600) . "</b>" . $lang_offers ['text_rule_four_two'] . "</li>\n") ;
	// if ($offeruptimeout_main)
print ("<li>" . $lang_offers ['text_rule_six'] . "</li>\n") ;
print ("<li>" . $lang_offers ['text_rule_five'] . "</li>\n") ;
print ("<li>" . $lang_offers ['text_rule_seven'] . "</li>\n") ;
print ("</ul></div>") ;
if (get_user_class () >= $addoffer_class)
{
	print ("<div align=\"center\" style=\"margin-bottom: 8px;\"><a href=\"?add_offer=1\">" . "<b>" . $lang_offers ['text_add_offer'] . "</b></a></div>") ;
	print ("<div align=\"center\" style=\"color: red\">务必上传文件目录截图，要求能完整的看到文件名，否则一律删除候选!</div>");
}
print ("<div align=\"center\"><form method=\"get\" action=\"?\">" . $lang_offers ['text_search_offers'] . "&nbsp;&nbsp;<input type=\"text\" id=\"specialboxg\" name=\"search\" />&nbsp;&nbsp;") ;
$cats = genrelist ( $browsecatmode );
$catdropdown = "";
foreach ( $cats as $cat ) {
	$catdropdown .= "<option value=\"" . $cat ["id"] . "\"";
	$catdropdown .= ">" . htmlspecialchars ( $cat ["name"] ) . "</option>\n";
}
print ("<select name=\"category\"><option value=\"0\">" . $lang_offers ['select_show_all'] . "</option>" . $catdropdown . "</select>&nbsp;&nbsp;<input type=\"submit\" class=\"btn\" value=\"" . $lang_offers ['submit_search'] . "\" /></form></div>") ;
end_frame ();
print ("<br /><br />") ;

$last_offer = strtotime ( $CURUSER ['last_offer'] );
if (! $num)
	stdmsg ( $lang_offers ['text_nothing_found'], $lang_offers ['text_nothing_found'] );
else {
	$catid = $_GET [category];
	print ("<table class=\"torrents\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">") ;
	print ("<tr><td class=\"colhead\" style=\"padding: 0px\"><a href=\"?category=" . $catid . "&amp;sort=cat&amp;type=" . $cat_order_type . "\">" . $lang_offers ['col_type'] . "</a></td>" . "<td class=\"colhead\" width=\"100%\"><a href=\"?category=" . $catid . "&amp;sort=name&amp;type=" . $name_order_type . "\">" . $lang_offers ['col_title'] . "</a></td>" . "<td colspan=\"3\" class=\"colhead\"><a href=\"?category=" . $catid . "&amp;sort=v_res&amp;type=" . $v_res_order_type . "\">" . $lang_offers ['col_vote_results'] . "</a></td>" . "<td class=\"colhead\"><a href=\"?category=" . $catid . "&amp;sort=comments&amp;type=" . $comments_order_type . "\"><img class=\"comments\" src=\"pic/trans.gif\" alt=\"comments\" title=\"" . $lang_offers ['title_comment'] . "\" />" . $lang_offers ['col_comment'] . "</a></td>" . "<td class=\"colhead\"><a href=\"?category=" . $catid . "&amp;sort=added&amp;type=" . $added_order_type . "\"><img class=\"time\" src=\"pic/trans.gif\" alt=\"time\" title=\"" . $lang_offers ['title_time_added'] . "\" /></a></td>") ;
	if ($offervotetimeout_main > 0 && $offeruptimeout_main > 0)
		print ("<td class=\"colhead\">" . $lang_offers ['col_timeout'] . "</td>") ;
	print ("<td class=\"colhead\">" . $lang_offers ['col_offered_by'] . "</td>" . (get_user_class () >= $offermanage_class ? "<td class=\"colhead\">" . $lang_offers ['col_act'] . "</td>" : "") . "</tr>\n") ;
	for($i = 0; $i < $num; ++ $i) {
		$arr = mysql_fetch_assoc ( $res );
		
		$addedby = get_username ( $arr ['userid'] );
		$comms = $arr ['comments'];
		if ($comms == 0)
			$comment = "<a href=\"comment.php?action=add&amp;pid=" . $arr [id] . "&amp;type=offer\" title=\"" . $lang_offers ['title_add_comments'] . "\">0</a>";
		else {
			if (! $lastcom = $Cache->get_value ( 'offer_' . $arr [id] . '_last_comment_content' )) {
				$res2 = sql_query ( "SELECT user, added, text FROM comments WHERE offer = $arr[id] ORDER BY added DESC LIMIT 1" );
				$lastcom = mysql_fetch_array ( $res2 );
				$Cache->cache_value ( 'offer_' . $arr [id] . '_last_comment_content', $lastcom, 1855 );
			}
			$timestamp = strtotime ( $lastcom ["added"] );
			$hasnewcom = ($lastcom ['user'] != $CURUSER ['id'] && $timestamp >= $last_offer);
			if ($CURUSER ['showlastcom'] != 'no') {
				if ($lastcom) {
					$title = "";
					if ($CURUSER ['timetype'] != 'timealive')
						$lastcomtime = $lang_offers ['text_at_time'] . $lastcom ['added'];
					else
						$lastcomtime = $lang_offers ['text_blank'] . gettime ( $lastcom ["added"], true, false, true );
					$counter = $i;
					$lastcom_tooltip [$counter] ['id'] = "lastcom_" . $counter;
					$lastcom_tooltip [$counter] ['content'] = ($hasnewcom ? "<b>(<font class='new'>" . $lang_offers ['text_new'] . "</font>)</b> " : "") . $lang_offers ['text_last_commented_by'] . get_username ( $lastcom ['user'] ) . $lastcomtime . "<br />" . format_comment ( mb_substr ( $lastcom ['text'], 0, 100, "UTF-8" ) . (mb_strlen ( $lastcom ['text'], "UTF-8" ) > 100 ? " ......" : ""), true, false, false, true, 600, false, false );
					$onmouseover = "onmouseover=\"domTT_activate(this, event, 'content', document.getElementById('" . $lastcom_tooltip [$counter] ['id'] . "'), 'trail', false, 'delay', 500,'lifetime',3000,'fade','both','styleClass','niceTitle','fadeMax', 87,'maxWidth', 400);\"";
				}
			} else {
				$title = " title=\"" . ($hasnewcom ? $lang_offers ['title_has_new_comment'] : $lang_offers ['title_no_new_comment']) . "\"";
				$onmouseover = "";
			}
			$comment = "<b><a" . $title . " href=\"?id=" . $arr [id] . "&amp;off_details=1#startcomments\" " . $onmouseover . ">" . ($hasnewcom ? "<font class='new'>" : "") . $comms . ($hasnewcom ? "</font>" : "") . "</a></b>";
		}
		
		// ==== if you want allow deny for offers use this next bit
		if ($arr ["allowed"] == 'allowed')
			$allowed = "&nbsp;<b>[<font color=\"green\">" . $lang_offers ['text_allowed'] . "</font>]</b>";
		elseif ($arr ["allowed"] == 'denied')
			$allowed = "&nbsp;<b>[<font color=\"red\">" . $lang_offers ['text_denied'] . "</font>]</b>";
		elseif ($arr ["allowed"] == 'freeze')
			$allowed = "&nbsp;<b>[<font color=\"red\">" . $lang_offers ['text_freeze'] . "</font>]</b>";
		else
			$allowed = "&nbsp;<b>[<font color=\"orange\">" . $lang_offers ['text_pending'] . "</font>]</b>";
			// ===end
		
		if ($arr ["yeah"] == 0)
			$zvote = $arr [yeah];
		else
			$zvote = "<b><a href=\"?id=" . $arr [id] . "&amp;offer_vote=1\">" . $arr [yeah] . "</a></b>";
		if ($arr ["against"] == 0)
			$pvote = "$arr[against]";
		else
			$pvote = "<b><a href=\"?id=" . $arr [id] . "&amp;offer_vote=1\">" . $arr [against] . "</a></b>";
		
		if ($arr ["yeah"] == 0 && $arr ["against"] == 0) {
			$v_res = "0";
		} else {
			
			$v_res = "<b><a href=\"?id=" . $arr [id] . "&amp;offer_vote=1\" title=\"" . $lang_offers ['title_show_vote_details'] . "\"><font color=\"green\">" . $arr [yeah] . "</font> - <font color=\"red\">" . $arr [against] . "</font> = " . ($arr [yeah] - $arr [against]) . "</a></b>";
		}
		$addtime = gettime ( $arr ['added'], false, true );
		$dispname = $arr [name];
		$count_dispname = mb_strlen ( $arr [name], "UTF-8" );
		$max_length_of_offer_name = 70;
		if ($count_dispname > $max_length_of_offer_name)
			$dispname = mb_substr ( $dispname, 0, $max_length_of_offer_name - 2, "UTF-8" ) . "..";
		print ("<tr><td class=\"rowfollow\" style=\"padding: 0px\"><a href=\"?category=" . $arr ['cat_id'] . "\">" . return_category_image ( $arr ['cat_id'], "" ) . "</a></td><td style='text-align: left'><a href=\"?id=" . $arr [id] . "&amp;off_details=1\" title=\"" . htmlspecialchars ( $arr [name] ) . "\"><b>" . htmlspecialchars ( $dispname ) . "</b></a>" . ($CURUSER ['appendnew'] != 'no' && strtotime ( $arr ["added"] ) >= $last_offer ? "<b> (<font class='new'>" . $lang_offers ['text_new'] . "</font>)</b>" : "") . $allowed . "</td><td class=\"rowfollow nowrap\" style='padding: 5px' align=\"center\">" . $v_res . "</td><td class=\"rowfollow nowrap\" " . (get_user_class () < $againstoffer_class ? " colspan=\"2\" " : "") . " style='padding: 5px'><a href=\"?id=" . $arr [id] . "&amp;vote=yeah\" title=\"" . $lang_offers ['title_i_want_this'] . "\"><font color=\"green\"><b>" . $lang_offers ['text_yep'] . "</b></font></a></td>" . (get_user_class () >= $againstoffer_class ? "<td class=\"rowfollow nowrap\" align=\"center\"><a href=\"?id=" . $arr [id] . "&amp;vote=against\" title=\"" . $lang_offers ['title_do_not_want_it'] . "\"><font color=\"red\"><b>" . $lang_offers ['text_nah'] . "</b></font></a></td>" : "")) ;
		
		print ("<td class=\"rowfollow\">" . $comment . "</td><td class=\"rowfollow nowrap\">" . $addtime . "</td>") ;
		if ($offervotetimeout_main > 0 && $offeruptimeout_main > 0) {
			if ($arr ["allowed"] == 'allowed') {
				$futuretime = strtotime ( $arr ['allowedtime'] ) + $offeruptimeout_main;
				$timeout = gettime ( date ( "Y-m-d H:i:s", $futuretime ), false, true, true, false, true );
			} elseif ($arr ["allowed"] == 'pending') {
				$futuretime = strtotime ( $arr ['added'] ) + $offervotetimeout_main;
				$timeout = gettime ( date ( "Y-m-d H:i:s", $futuretime ), false, true, true, false, true );
			} elseif ($arr ["allowed"] == 'freeze') {
				$futuretime = strtotime ( $arr ['added'] ) + $offervotetimeout_main;
				$timeout = gettime ( date ( "Y-m-d H:i:s", $futuretime ), false, true, true, false, true );
			}
			if (! $timeout)
				$timeout = "N/A";
			print ("<td class=\"rowfollow nowrap\">" . $timeout . "</td>") ;
		}
		print ("<td class=\"rowfollow\">" . $addedby . "</td>" . (get_user_class () >= $offermanage_class ? "<td class=\"rowfollow\"><a href=\"?id=" . $arr [id] . "&amp;del_offer=1\"><img class=\"staff_delete\" src=\"pic/trans.gif\" alt=\"D\" title=\"" . $lang_offers ['title_delete'] . "\" /></a><br /><a href=\"?id=" . $arr [id] . "&amp;edit_offer=1\"><img class=\"staff_edit\" src=\"pic/trans.gif\" alt=\"E\" title=\"" . $lang_offers ['title_edit'] . "\" /></a></td>" : "") . "</tr>") ;
	}
	print ("</table>\n") ;
	echo $pagerbottom;
	if (! isset ( $CURUSER ) || $CURUSER ['showlastcom'] == 'yes')
		create_tooltip_container ( $lastcom_tooltip, 400 );
}
end_main_frame ();
$USERUPDATESET [] = "last_offer = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
stdfoot ();
?>
