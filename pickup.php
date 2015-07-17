<?php
require_once ("include/bittorrent.php");
dbconn ();
require_once (get_langfile_path ());
loggedinorreturn ();
function bark($msg) {
	global $lang_pickup;
	genbark ( $msg, $lang_pickup ['std_edit_failed'] );
}

$id = 0 + $id;
if (! $id)
	die ();

if (get_user_class () < $torrentmanage_class)
	bark ( $lang_pickup ['std_not_owner'] );

$res = sql_query ( "SELECT  sp_state, picktype, pos_state, picktime, name, id, added FROM torrents WHERE id = " . mysql_real_escape_string ( $id ) );
$row = mysql_fetch_array ( $res );
if (! $row)
	die ();

$torrentAddedTimeString = $row ['added'];

$updateset = array ();

$spstate = "";
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
			$spstate = "永久2X 免费 ";
	} elseif ((0 + $_POST ["sel_spstate"]) == 11) {
		$updateset [] = "sp_state = 11";
		if ($row ["sp_state"] != 11)
			$spstate = "永久50% ";
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
if (get_user_class () >= $torrentmanage_class) {
	if ((0 + $_POST ["sel_recmovie"]) == 0) {
		if ($row ["picktype"] != 'normal')
			$pick_info = "取消推荐！";
		$updateset [] = "picktype = 'normal'";
	} elseif ((0 + $_POST ["sel_recmovie"]) == 1) {
		if ($row ["picktype"] != 'hot')
			$pick_info = "设置成 热门";
		$updateset [] = "picktype = 'hot'";
	} elseif ((0 + $_POST ["sel_recmovie"]) == 2) {
		if ($row ["picktype"] != 'classic')
			$pick_info = "设置成 经典";
		$updateset [] = "picktype = 'classic'";
	} elseif ((0 + $_POST ["sel_recmovie"]) == 3) {
		if ($row ["picktype"] != 'recommended')
			$pick_info = "设置成 推荐";
		$updateset [] = "picktype = 'recommended'";
	} elseif ((0 + $_POST ["sel_recmovie"]) == 4) {
		if ($row ["picktype"] != '0day')
			$pick_info = "设置成 0day";
		$updateset [] = "picktype = '0day'";
	} elseif ((0 + $_POST ["sel_recmovie"]) == 5) {
		if ($row ["picktype"] != 'IMDB')
			$pick_info = "设置成 IMDB TOP 250";
		$updateset [] = "picktype = 'IMDB'";
	}
	if ($pick_info)
		$updateset [] = "picktime = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
}
sql_query ( "UPDATE torrents SET " . join ( ",", $updateset ) . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );

/**
 * *********************************************************************************************
 */
if ($spstate != "" || $posstate != "" || $pick_info != "") {
	write_log ( "管理员 $CURUSER[username] 编辑了资源 $id ($row[name]) " . $spstate . $posstate . $pick_info );
}
$returl = "details.php?id=$id";
if (isset ( $_POST ["returnto"] ))
	$returl = $_POST ["returnto"];

if ((0 + $_POST ["sel_posstate"]) == 1 && $row ["pos_state"] != 'sticky') {
	$pre_to_shoutbox ['text'] = "[b][color=red]" . $row ['name'] . "[/color][/b]被置顶啦：[url=details.php?id=" . mysql_real_escape_string ( $id ) . "&hit=1]大家这里使劲戳[/url]";
	$pre_to_shoutbox ['type'] = "sb";
	$pre_to_shoutbox ['ip'] = "北洋媛隐身啦～啦啦啦～";
	sql_query ( "INSERT INTO shoutbox (userid, date, text, type, ip) VALUES (0, " . sqlesc ( time () ) . ", " . sqlesc ( $pre_to_shoutbox ['text'] ) . ", " . sqlesc ( $pre_to_shoutbox ['type'] ) . ", '$pre_to_shoutbox[ip]' )" ) or sqlerr ( __FILE__, __LINE__ );
}

header ( "Refresh: 0; url=$returl" );
