<?php
require_once ("include/bittorrent.php");
dbconn ();
require_once (get_langfile_path ());
loggedinorreturn ();
function bark($msg) {
	global $lang_takeedit;
	genbark ( $msg, $lang_takeedit ['std_edit_failed'] );
}

if (! mkglobal ( "id:descr:type" )) {
	global $lang_takeedit;
	bark ( $lang_takeedit ['std_missing_form_data'] );
}

$id = 0 + $id;
if (! $id)
	die ();

$res = sql_query ( "SELECT category, owner, pos_state, sp_state, filename, save_as, anonymous, picktype, picktime, name, added FROM torrents WHERE id = " . mysql_real_escape_string ( $id ) );
$row = mysql_fetch_array ( $res );
if (! $row)
	die ();

if ($CURUSER ["id"] != $row ["owner"] && get_user_class () < $torrentmanage_class)
	bark ( $lang_takeedit ['std_not_owner'] );
$oldcatmode = get_single_value ( "categories", "mode", "WHERE id=" . sqlesc ( $row ['category'] ) );

$torrentAddedTimeString = $row ['added'];

$updateset = array ();

// $fname = $row["filename"];
// preg_match('/^(.+)\.torrent$/si', $fname, $matches);
// $shortfname = $matches[1];
// $dname = $row["save_as"];

$url = parse_imdb_id ( $_POST ['url'] );

if ($enablenfo_main == 'yes') {
	$nfoaction = $_POST ['nfoaction'];
	if ($nfoaction == "update") {
		$nfofile = $_FILES ['nfo'];
		if (! $nfofile)
			die ( "No data " . var_dump ( $_FILES ) );
		if ($nfofile ['size'] > 65535)
			bark ( $lang_takeedit ['std_nfo_too_big'] );
		$nfofilename = $nfofile ['tmp_name'];
		if (@is_uploaded_file ( $nfofilename ) && @filesize ( $nfofilename ) > 0)
			$updateset [] = "nfo = " . sqlesc ( str_replace ( "\x0d\x0d\x0a", "\x0d\x0a", file_get_contents ( $nfofilename ) ) );
		$Cache->delete_value ( 'nfo_block_torrent_id_' . $id );
	} elseif ($nfoaction == "remove") {
		$updateset [] = "nfo = ''";
		$Cache->delete_value ( 'nfo_block_torrent_id_' . $id );
	}
}

$catid = (0 + $type);
if (! is_valid_id ( $catid ))
	bark ( $lang_takeedit ['std_missing_form_data'] );
if (! $descr)
	bark ( $lang_takeedit ['std_missing_form_data'] );
$newcatmode = get_single_value ( "categories", "mode", "WHERE id=" . sqlesc ( $catid ) );
if ($enablespecial == 'yes' && get_user_class () >= $movetorrent_class)
	$allowmove = true; // enable moving torrent to other section
else
	$allowmove = false;
if ($oldcatmode != $newcatmode && ! $allowmove)
	bark ( $lang_takeedit ['std_cannot_move_torrent'] );
$updateset [] = "anonymous = '" . ($_POST ["anonymous"] ? "yes" : "no") . "'";
$updateset [] = "name = " . sqlesc ( $name );
$updateset [] = "descr = " . sqlesc ( $descr );
$updateset [] = "url = " . sqlesc ( $url );
$updateset [] = "small_descr = " . sqlesc ( $_POST ["small_descr"] );
// $updateset[] = "ori_descr = " . sqlesc($descr);
$updateset [] = "category = " . sqlesc ( $catid );
$updateset [] = "source = " . sqlesc ( 0 + $_POST ["source_sel"] );
$updateset [] = "medium = " . sqlesc ( 0 + $_POST ["medium_sel"] );
$updateset [] = "codec = " . sqlesc ( 0 + $_POST ["codec_sel"] );
$updateset [] = "standard = " . sqlesc ( 0 + $_POST ["standard_sel"] );
$updateset [] = "processing = " . sqlesc ( 0 + $_POST ["processing_sel"] );
$updateset [] = "team = " . sqlesc ( 0 + $_POST ["team_sel"] );
$updateset [] = "audiocodec = " . sqlesc ( 0 + $_POST ["audiocodec_sel"] );

if (get_user_class () >= $torrentmanage_class) {
	if ($_POST ["banned"]) {
		$updateset [] = "banned = 'yes'";
		$_POST ["visible"] = 0;
	} else
		$updateset [] = "banned = 'no'";
}
$updateset [] = "visible = '" . ($_POST ["visible"] ? "yes" : "no") . "'";
if (get_user_class () >= $torrentsticky_class) {
	if (! isset ( $_POST ["sel_spstate"] ) || $_POST ["sel_spstate"] == 1) {
		$updateset [] = "sp_state = 1";
		if ($row ["sp_state"] != 1)
			$spstate = "取消促销 ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 2) {
		$updateset [] = "sp_state = 2";
		if ($row ["sp_state"] != 2)
			$spstate = "免费 ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 3) {
		$updateset [] = "sp_state = 3";
		if ($row ["sp_state"] != 3)
			$spstate = "2X ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 4) {
		$updateset [] = "sp_state = 4";
		if ($row ["sp_state"] != 4)
			$spstate = "2X 免费 ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 5) {
		$updateset [] = "sp_state = 5";
		if ($row ["sp_state"] != 5)
			$spstate = "50% ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 6) {
		$updateset [] = "sp_state = 6";
		if ($row ["sp_state"] != 6)
			$spstate = "2X 50% ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 7) {
		$updateset [] = "sp_state = 7";
		if ($row ["sp_state"] != 7)
			$spstate = "30% ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 8) {
		$updateset [] = "sp_state = 8";
		if ($row ["sp_state"] != 8)
			$spstate = "永久免费 ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 9) {
		$updateset [] = "sp_state = 9";
		if ($row ["sp_state"] != 9)
			$spstate = "永久2X ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 10) {
		$updateset [] = "sp_state = 10";
		if ($row ["sp_state"] != 10)
			$spstate = "永久2X 免费";
	} elseif ((0 + $_POST ["sel_spstate"]) == 11) {
		$updateset [] = "sp_state = 11";
		if ($row ["sp_state"] != 11)
			$spstate = "永久50%";
	} elseif ((0 + $_POST ["sel_spstate"]) == 12) {
		$updateset [] = "sp_state = 12";
		
		if ($row ["sp_state"] != 12)
			$spstate = "永久2X 50% ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 13) {
		$updateset [] = "sp_state = 13";
		if ($row ["sp_state"] != 13)
			$spstate = "永久30% ";
	}
	if ($spstate)
		$updateset [] = "sp_time = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
		
		// promotion expiration type
/*	if ((0 + $_POST ["sel_spstate"]) > 7) {
		$updateset [] = "promotion_time_type = 1";
		$updateset [] = "promotion_until = '0000-00-00 00:00:00'";
	} elseif (! isset ( $_POST ["promotion_time_type"] ) || (0 + $_POST ["promotion_time_type"]) == 0) {
		$updateset [] = "promotion_time_type = 0";
		$updateset [] = "promotion_until = '0000-00-00 00:00:00'";
	} elseif ((0 + $_POST ["promotion_time_type"]) == 1) {
		$updateset [] = "promotion_time_type = 1";
		$updateset [] = "promotion_until = '0000-00-00 00:00:00'";
	} elseif ((0 + $_POST ["promotion_time_type"]) == 2) {
		if ($_POST ["promotionuntil"] && strtotime ( $torrentAddedTimeString ) <= strtotime ( $_POST ["promotionuntil"] )) {
			$updateset [] = "promotion_time_type = 2";
			$updateset [] = "promotion_until = " . sqlesc ( $_POST ["promotionuntil"] );
		} else {
			$updateset [] = "promotion_time_type = 0";
			$updateset [] = "promotion_until = '0000-00-00 00:00:00'";
		}
	}
*/	
	
	if ((0 + $_POST ["sel_spstate"]) > 7 && (0 + $_POST ["sel_spstate"]) < 14) {
		$updateset [] = "promotion_time_type = 1";
		$updateset [] = "promotion_until = '0000-00-00 00:00:00'";
	} else {
		if ($_POST ["promotionuntil"] && strtotime ( $torrentAddedTimeString ) <= strtotime ( $_POST ["promotionuntil"] )) {
			$updateset [] = "promotion_time_type = 2";
			$updateset [] = "promotion_until = " . sqlesc ( $_POST ["promotionuntil"] );
		} else {
			$updateset [] = "promotion_time_type = 0";
			$updateset [] = "promotion_until = '0000-00-00 00:00:00'";
		}	
	}
	
	$posstate = "";
	if ((0 + $_POST ["sel_posstate"]) == 0) {
		$updateset [] = "pos_state = 'normal'";
		$updateset [] = "pos_state_until = '0000-00-00 00:00:00'";
		if ($row ["pos_state"] != 'normal')
			$posstate = "取消置顶 ";
	} elseif ((0 + $_POST ["sel_posstate"]) == 1) {
		if ($_POST ["posstateuntil"] && strtotime ( $torrentAddedTimeString ) <= strtotime ( $_POST ["posstateuntil"] )) {
			$updateset [] = "pos_state = 'sticky'";
			$updateset [] = "pos_state_until = " . sqlesc ( $_POST ["posstateuntil"] );
			if ($row ["pos_state"] != 'sticky')
				$posstate = "置顶 ";
		} else {
			$updateset [] = "pos_state = 'normal'";
			$updateset [] = "pos_state_until = '0000-00-00 00:00:00'";
		}
	}
}

$pick_info = "";
// if(get_user_class()>=$torrentmanage_class && $CURUSER['picker'] == 'yes')
if (get_user_class () >= $torrentmanage_class || $CURUSER ['picker'] == 'yes') {
	if ((0 + $_POST ["sel_recmovie"]) == 0) {
		if ($row ["picktype"] != 'normal')
			$pick_info = "取消推荐！ ";
		$updateset [] = "picktype = 'normal'";
		$updateset [] = "picktime = '0000-00-00 00:00:00'";
	} elseif ((0 + $_POST ["sel_recmovie"]) == 1) {
		if ($row ["picktype"] != 'hot')
			$pick_info = "设置成 热门 ";
		$updateset [] = "picktype = 'hot'";
		$updateset [] = "picktime = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
	} elseif ((0 + $_POST ["sel_recmovie"]) == 2) {
		if ($row ["picktype"] != 'classic')
			$pick_info = "设置成 经典 ";
		$updateset [] = "picktype = 'classic'";
		$updateset [] = "picktime = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
	} elseif ((0 + $_POST ["sel_recmovie"]) == 3) {
		if ($row ["picktype"] != 'recommended')
			$pick_info = "设置成 推荐 ";
		$updateset [] = "picktype = 'recommended'";
		$updateset [] = "picktime = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
	} elseif ((0 + $_POST ["sel_recmovie"]) == 4) {
		if ($row ["picktype"] != '0day')
			$pick_info = ", 设置成 0day ";
		$updateset [] = "picktype = '0day'";
		$updateset [] = "picktime = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
	} elseif ((0 + $_POST ["sel_recmovie"]) == 5) {
		if ($row ["picktype"] != '0day')
			$pick_info = "设置成 IMDB TOP 250 ";
		$updateset [] = "picktype = 'IMDB'";
		$updateset [] = "picktime = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
	}
}
sql_query ( "UPDATE torrents SET " . join ( ",", $updateset ) . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );

/**
 * *************************接收用户输入分类信息及保存分类信息***************************************
 */
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

$nameset = "";

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
	if ($ename != "")
		$nameset .= "[" . $ename . "]";
	if ($tvshowscontent != "")
		$nameset .= "[" . $tvshowscontent . "]";
	if ($format != "")
		$nameset .= "[" . $format . "]";
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
	if ($specificcat != "")
		$nameset .= "[" . $specificcat . "]";
	if ($cname != "")
		$nameset .= "[" . $cname . "]";
	if ($ename != "")
		$nameset .= "[" . $ename . "]";
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
	$updateinfoset [] = "tvshowsremarks = '" . mysql_escape_string ( $tvshowsremarks ) . "'";
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
if ($catid == 4013) {
	$nameset .= $cname;
}

if ($nameset == "") {
	bark ( $lang_takeupload ['std_missing_form_data'] );
}

sql_query ( "UPDATE torrentsinfo SET " . join ( ",", $updateinfoset ) . " WHERE torid = $id" ) or sqlerr ( __FILE__, __LINE__ );

if ($nameset != "") {
	sql_query ( "UPDATE torrents SET name = " . sqlesc ( $nameset ) . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
}

// if($catid==410||$catid==4013)
// if($cname!="")
// sql_query("UPDATE torrents SET name = " . sqlesc($cname) . " WHERE id = $id")
// or sqlerr(__FILE__, __LINE__);

if ($catid == 4013)
	sql_query ( "UPDATE torrents SET sp_state = 1 where id = $id" );

/**
 * *********************************************************************************************
 */
$Cache->delete_value ( 'torrent_' . $id . '_seed_name' );

if ($CURUSER ["id"] == $row ["owner"]) {
	if ($row ["anonymous"] == 'yes') {
		write_log ( "匿名发布者编辑了资源 $id ($row[name]) " . $pick_info . $place_info );
	} else {
		write_log ( "发布者 $CURUSER[username] 编辑了资源 $id ($row[name]) " . $pick_info . $place_info );
	}
} else {
	write_log ( "管理员 $CURUSER[username] 编辑了资源 $id ($row[name]) " . $spstate . $posstate . $pick_info . $place_info );
}
$returl = "details.php?id=$id&edited=1";
if (isset ( $_POST ["returnto"] ))
	$returl = $_POST ["returnto"];

if ((0 + $_POST ["sel_posstate"]) == 1 && $row ["pos_state"] != 'sticky') {
	$pre_to_shoutbox ['text'] = "[b][color=red]" . ($nameset ? $nameset : $row ['name']) . "[/color][/b]被置顶啦：[url=details.php?id=" . mysql_real_escape_string ( $id ) . "&hit=1]大家这里戳戳戳[/url]";
	$pre_to_shoutbox ['type'] = "sb";
	$pre_to_shoutbox ['ip'] = "北洋媛隐身啦～啦啦啦～";
	sql_query ( "INSERT INTO shoutbox (userid, date, text, type, ip) VALUES (0, " . sqlesc ( time () ) . ", " . sqlesc ( $pre_to_shoutbox ['text'] ) . ", " . sqlesc ( $pre_to_shoutbox ['type'] ) . ", '$pre_to_shoutbox[ip]' )" ) or sqlerr ( __FILE__, __LINE__ );
}

header ( "Refresh: 0; url=$returl" );
