<?php
require_once("include/benc.php");
require_once("include/bittorrent.php");

ini_set("upload_max_filesize",$max_torrent_size);
//ini_set("display_errors", 0);
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

dbconn();
//require_once(get_langfile_path());
//require(get_langfile_path("",true));

//$_POST=$_GET;
$passkey = mysql_escape_string($_POST['passkey']);
$sql = "select * from users where passkey = '$passkey' limit 1";
$res = mysql_query($sql);
if (!$res) 
{
	die('error: wrong passkey!');
}
	$CURUSER = mysql_fetch_assoc($res);
	//print_r($CURUSER);
	//die();
//loggedinorreturn();

function bark($msg) {
	global $lang_takeupload;
	genbark ( $msg, $lang_takeupload ['std_upload_failed'] );
	die ();
}

if ($CURUSER ["uploadpos"] == 'no')
	die ();

/*
foreach ( explode ( ":", "type" ) as $v ) {
	if (! isset ( $_POST [$v] ))
		bark ( $lang_takeupload ['std_missing_form_data'] . "-" . $_POST [$v] );
}
*/

if (! isset ( $_FILES ["file"] ))
	bark ( $lang_takeupload ['std_missing_form_data'] . "-种子文件" );

$f = $_FILES ["file"];
$fname = unesc ( $f ["name"] );
if (empty ( $fname ))
	bark ( $lang_takeupload ['std_empty_filename'] );
if (get_user_class () >= $beanonymous_class && $_POST ['uplver'] == 'yes') { // $_POST['uplver']
                                                                             // ==
                                                                             // 'yes'匿名发布;
	$anonymous = "yes";
	$anon = "匿名用户";
} else {
	$anonymous = "no";
	$anon = "用户 " . $CURUSER ["username"];
}

$url = parse_imdb_id ( $_POST ['url'] );

$nfo = '';
if ($enablenfo_main == 'yes') { // NFO文件
	$nfofile = $_FILES ['nfo'];
	if ($nfofile ['name'] != '') {
		
		if ($nfofile ['size'] == 0)
			bark ( $lang_takeupload ['std_zero_byte_nfo'] );
		
		if ($nfofile ['size'] > 65535)
			bark ( $lang_takeupload ['std_nfo_too_big'] );
		
		$nfofilename = $nfofile ['tmp_name'];
		
		if (@! is_uploaded_file ( $nfofilename ))
			bark ( $lang_takeupload ['std_nfo_upload_failed'] );
		$nfo = str_replace ( "\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents ( $nfofilename ) );
	}
}

$small_descr = unesc ( $_POST ["small_descr"] );

$descr = unesc ( $_POST ["descr"] );
/*if (! $descr)
	bark ( $lang_takeupload ['std_blank_description'] );*/

$catid = (0 + $_POST ["type"]);
$sourceid = (0 + $_POST ["source_sel"]);
$mediumid = (0 + $_POST ["medium_sel"]);
$codecid = (0 + $_POST ["codec_sel"]);
$standardid = (0 + $_POST ["standard_sel"]);
$processingid = (0 + $_POST ["processing_sel"]);
$teamid = (0 + $_POST ["team_sel"]);
$audiocodecid = (0 + $_POST ["audiocodec_sel"]);

if (! is_valid_id ( $catid ))
	bark ( $lang_takeupload ['std_category_unselected'] );

if (! validfilename ( $fname ))
	bark ( $lang_takeupload ['std_invalid_filename'] );
if (! preg_match ( '/^(.+)\.torrent$/si', $fname, $matches ))
	bark ( $lang_takeupload ['std_filename_not_torrent'] );
$shortfname = $torrent = $matches [1];
if (! empty ( $_POST ["name"] ))
	$torrent = unesc ( $_POST ["name"] );
if ($f ['size'] > $max_torrent_size)
	bark ( $lang_takeupload ['std_torrent_file_too_big'] . number_format ( $max_torrent_size ) . $lang_takeupload ['std_remake_torrent_note'] );
$tmpname = $f ["tmp_name"];
if (! is_uploaded_file ( $tmpname ))
	bark ( "eek" );
if (! filesize ( $tmpname ))
	bark ( $lang_takeupload ['std_empty_file'] );
$dict = bdec_file ( $tmpname, $max_torrent_size );
if (! isset ( $dict ))
	bark ( $lang_takeupload ['std_not_bencoded_file'] );
function dict_check($d, $s) {
	global $lang_takeupload;
	if ($d ["type"] != "dictionary")
		bark ( $lang_takeupload ['std_not_a_dictionary'] );
	$a = explode ( ":", $s );
	$dd = $d ["value"];
	$ret = array ();
	foreach ( $a as $k ) {
		unset ( $t );
		if (preg_match ( '/^(.*)\((.*)\)$/', $k, $m )) {
			$k = $m [1];
			$t = $m [2];
		}
		if (! isset ( $dd [$k] ))
			bark ( $lang_takeupload ['std_dictionary_is_missing_key'] );
		if (isset ( $t )) {
			if ($dd [$k] ["type"] != $t)
				bark ( $lang_takeupload ['std_invalid_entry_in_dictionary'] );
			$ret [] = $dd [$k] ["value"];
		} else
			$ret [] = $dd [$k];
	}
	return $ret;
}
function dict_get($d, $k, $t) {
	global $lang_takeupload;
	if ($d ["type"] != "dictionary")
		bark ( $lang_takeupload ['std_not_a_dictionary'] );
	$dd = $d ["value"];
	if (! isset ( $dd [$k] ))
		return;
	$v = $dd [$k];
	if ($v ["type"] != $t)
		bark ( $lang_takeupload ['std_invalid_dictionary_entry_type'] );
	return $v ["value"];
}

list ( $ann, $info ) = dict_check ( $dict, "announce(string):info" );
list ( $dname, $plen, $pieces ) = dict_check ( $info, "name(string):piece length(integer):pieces(string)" );

/*
 * if (!in_array($ann, $announce_urls, 1)) { $aok=false; foreach($announce_urls
 * as $au) { if($ann=="$au?passkey=$CURUSER[passkey]") $aok=true; } if(!$aok)
 * bark("Invalid announce url! Must be: " . $announce_urls[0] .
 * "?passkey=$CURUSER[passkey]"); }
 */

if (strlen ( $pieces ) % 20 != 0)
	bark ( $lang_takeupload ['std_invalid_pieces'] );

$filelist = array ();
$totallen = dict_get ( $info, "length", "integer" );
if (isset ( $totallen )) {
	$filelist [] = array (
			$dname,
			$totallen 
	);
	$type = "single";
} else {
	$flist = dict_get ( $info, "files", "list" );
	if (! isset ( $flist ))
		bark ( $lang_takeupload ['std_missing_length_and_files'] );
	if (! count ( $flist ))
		bark ( "no files" );
	$totallen = 0;
	foreach ( $flist as $fn ) {
		list ( $ll, $ff ) = dict_check ( $fn, "length(integer):path(list)" );
		$totallen += $ll;
		$ffa = array ();
		foreach ( $ff as $ffe ) {
			if ($ffe ["type"] != "string")
				bark ( $lang_takeupload ['std_filename_errors'] );
			$ffa [] = $ffe ["value"];
		}
		if (! count ( $ffa ))
			bark ( $lang_takeupload ['std_filename_errors'] );
		$ffe = implode ( "/", $ffa );
		$filelist [] = array (
				$ffe,
				$ll 
		);
	}
	$type = "multi";
}

$dict ['value'] ['announce'] = bdec ( benc_str ( get_protocol_prefix () . $announce_urls [0] ) ); // change
                                                                                                  // announce
                                                                                                  // url
                                                                                                  // to
                                                                                                  // local
$dict ['value'] ['info'] ['value'] ['private'] = bdec ( 'i1e' ); // add private
                                                                 // tracker
                                                                 // flag
                                                                 // 删除其他站点添加的标签
if ($dict ['value'] ['created by'] == bdec ( benc_str ( "hdchina.org" ) )) {
	$dict ['value'] ['created by'] = bdec ( benc_str ( "[$BASEURL]" ) );
	$dict ['value'] ['comment'] = bdec ( benc_str ( "Torrent From TJUPT" ) );
}
if ($dict ['value'] ['created by'] == bdec ( benc_str ( "http://cgbt.org" ) )) {
	$dict ['value'] ['created by'] = bdec ( benc_str ( "[$BASEURL]" ) );
	$dict ['value'] ['comment'] = bdec ( benc_str ( "Torrent From TJUPT" ) );
}
if (isset ( $dict ['value'] ['info'] ['value'] ['ttg_tag'] )) {
	unset ( $dict ['value'] ['info'] ['value'] ['ttg_tag'] );
	$dict ['value'] ['created by'] = bdec ( benc_str ( "[$BASEURL]" ) );
	$dict ['value'] ['comment'] = bdec ( benc_str ( "Torrent From TJUPT" ) );
}
// The following line requires uploader to re-download torrents after uploading
// even the torrent is set as private and with uploader's passkey in it.
$dict ['value'] ['info'] ['value'] ['source'] = bdec ( benc_str ( "[$BASEURL] $SITENAME" ) );
unset ( $dict ['value'] ['announce-list'] ); // remove multi-tracker capability
unset ( $dict ['value'] ['nodes'] ); // remove cached peers (Bitcomet & Azareus)
$dict = bdec ( benc ( $dict ) ); // double up on the becoding solves the
                                 // occassional
                                 // misgenerated infohash
list ( $ann, $info ) = dict_check ( $dict, "announce(string):info" );

$infohash = pack ( "H*", sha1 ( $info ["string"] ) );
function hex_esc2($matches) {
	return sprintf ( "%02x", ord ( $matches [0] ) );
}

// die(phpinfo());

// die("magic:" . get_magic_quotes_gpc());

// die("\\' pos:" . strpos($infohash,"\\") . ", after sqlesc:" .
// (strpos(sqlesc($infohash),"\\") == false ? "gone" :
// strpos(sqlesc($infohash),"\\")));

// die(preg_replace_callback('/./s', "hex_esc2", $infohash));

// ------------- start: check upload authority ------------------//
$allowtorrents = user_can_upload ( "torrents" );
$allowspecial = user_can_upload ( "music" );
/*
$catmod = get_single_value ( "categories", "mode", "WHERE id=" . sqlesc ( $catid ) );
$offerid = $_POST ['offer'];
$is_offer = false;
if ($browsecatmode != $specialcatmode && $catmod == $specialcatmode) { // upload
                                                                       // to
                                                                       // special
                                                                       // section
	if (! $allowspecial)
		bark ( $lang_takeupload ['std_unauthorized_upload_freely'] );
} elseif ($catmod == $browsecatmode) { // upload to torrents section
	if ($offerid) { // it is a offer
		$allowed_offer_count = get_row_count ( "offers", "WHERE allowed='allowed' AND userid=" . sqlesc ( $CURUSER ["id"] ) );
		if ($allowed_offer_count && $enableoffer == 'yes') {
			$allowed_offer = get_row_count ( "offers", "WHERE id=" . sqlesc ( $offerid ) . " AND allowed='allowed' AND userid=" . sqlesc ( $CURUSER ["id"] ) );
			if ($allowed_offer != 1) // user uploaded torrent that is not an
			                         // allowed offer
				bark ( $lang_takeupload ['std_uploaded_not_offered'] );
			else
				$is_offer = true;
		} else
			bark ( $lang_takeupload ['std_uploaded_not_offered'] );
	} elseif (! $allowtorrents)
		bark ( $lang_takeupload ['std_unauthorized_upload_freely'] );
} else // upload to unknown section
	die ( "Upload to unknown section." );*/
	// ------------- end: check upload authority ------------------//
	
// Replace punctuation characters with spaces
	
// $torrent = str_replace("_", " ", $torrent);

if ($largesize_torrent && $totallen > ($largesize_torrent * 1073741824)) // Large
                                                                         // Torrent
                                                                         // Promotion
{
	switch ($largepro_torrent) {
		case 2 : // Free
			{
				$sp_state = 2;
				break;
			}
		case 3 : // 2X
			{
				$sp_state = 3;
				break;
			}
		case 4 : // 2X Free
			{
				$sp_state = 4;
				break;
			}
		case 5 : // Half Leech
			{
				$sp_state = 5;
				break;
			}
		case 6 : // 2X Half Leech
			{
				$sp_state = 6;
				break;
			}
		case 7 : // 30% Leech
			{
				$sp_state = 7;
				break;
			}
		case 8 : // Free Forever
			{
				$sp_state = 8;
				break;
			}
		case 9 : // 2X Forever
			{
				$sp_state = 9;
				break;
			}
		case 10 : // 2X Free Forever
			{
				$sp_state = 10;
				break;
			}
		case 11 : // Half Leech Forever
			{
				$sp_state = 11;
				break;
			}
		case 12 : // 2X Half Leech Forever
			{
				$sp_state = 12;
				break;
			}
		case 13 : // 30% Leech Forever
			{
				$sp_state = 13;
				break;
			}
		default : // normal
			{
				$sp_state = 1;
				break;
			}
	}
} elseif ($middlesize_torrent && $totallen > ($middlesize_torrent * 1073741824)) {
	switch ($middlepro_torrent) {
		case 2 : // Free
			{
				$sp_state = 2;
				break;
			}
		case 3 : // 2X
			{
				$sp_state = 3;
				break;
			}
		case 4 : // 2X Free
			{
				$sp_state = 4;
				break;
			}
		case 5 : // Half Leech
			{
				$sp_state = 5;
				break;
			}
		case 6 : // 2X Half Leech
			{
				$sp_state = 6;
				break;
			}
		case 7 : // 30% Leech
			{
				$sp_state = 7;
				break;
			}
		case 8 : // Free Forever
			{
				$sp_state = 8;
				break;
			}
		case 9 : // 2X Forever
			{
				$sp_state = 9;
				break;
			}
		case 10 : // 2X Free Forever
			{
				$sp_state = 10;
				break;
			}
		case 11 : // Half Leech Forever
			{
				$sp_state = 11;
				break;
			}
		case 12 : // 2X Half Leech Forever
			{
				$sp_state = 12;
				break;
			}
		case 13 : // 30% Leech Forever
			{
				$sp_state = 13;
				break;
			}
		default : // normal
			{
				$sp_state = 1;
				break;
			}
	}
} else { // ramdom torrent promotion
	$sp_id = mt_rand ( 1, 100 );
	if ($sp_id <= ($probability = $randomfree_torrent)) // Free
		$sp_state = 2;
	elseif ($sp_id <= ($probability += $randomtwoup_torrent)) // 2X
		$sp_state = 3;
	elseif ($sp_id <= ($probability += $randomtwoupfree_torrent)) // 2X Free
		$sp_state = 4;
	elseif ($sp_id <= ($probability += $randomhalfleech_torrent)) // Half Leech
		$sp_state = 5;
	elseif ($sp_id <= ($probability += $randomtwouphalfdown_torrent)) // 2X Half
	                                                                  // Leech
		$sp_state = 6;
	elseif ($sp_id <= ($probability += $randomthirtypercentdown_torrent)) // 30%
	                                                                      // Leech
		$sp_state = 7;
	elseif ($sp_id <= ($probability += $randomfreeforever_torrent)) // Free
	                                                                // Forever
		$sp_state = 8;
	elseif ($sp_id <= ($probability += $randomtwoupforever_torrent)) // 2X
	                                                                 // Forever
		$sp_state = 9;
	elseif ($sp_id <= ($probability += $randomtwoupfreeforever_torrent)) // 2X
	                                                                     // Free
	                                                                     // Forever
		$sp_state = 10;
	elseif ($sp_id <= ($probability += $randomhalfleechforever_torrent)) // Half
	                                                                     // Leech
	                                                                     // Forever
		$sp_state = 11;
	elseif ($sp_id <= ($probability += $randomtwouphalfdownforever_torrent)) // 2X
	                                                                         // Half
	                                                                         // Leech
	                                                                         // Forever
		$sp_state = 12;
	elseif ($sp_id <= ($probability += $randomthirtypercentdownforever_torrent)) // 30%
	                                                                             // Leech
	                                                                             // Forever
		$sp_state = 13;
	else
		$sp_state = 1; // normal
}
if ($catid == 404)
	$sp_state = 11; // 资料类特殊优惠

if ($altname_main == 'yes') {
	$cnname_part = unesc ( trim ( $_POST ["cnname"] ) );
	$size_part = str_replace ( " ", "", mksize ( $totallen ) );
	$date_part = date ( "m.d.y" );
	$category_part = get_single_value ( "categories", "name", "WHERE id = " . sqlesc ( $catid ) );
	$torrent = "[" . $date_part . "]" . ($_POST ["name"] ? "[" . $_POST ["name"] . "]" : "") . ($cnname_part ? "[" . $cnname_part . "]" : "");
}

// some ugly code of automatically promoting torrents based on some rules
if ($prorules_torrent == 'yes') {
	foreach ( $promotionrules_torrent as $rule ) {
		if (! array_key_exists ( 'catid', $rule ) || in_array ( $catid, $rule ['catid'] ))
			if (! array_key_exists ( 'sourceid', $rule ) || in_array ( $sourceid, $rule ['sourceid'] ))
				if (! array_key_exists ( 'mediumid', $rule ) || in_array ( $mediumid, $rule ['mediumid'] ))
					if (! array_key_exists ( 'codecid', $rule ) || in_array ( $codecid, $rule ['codecid'] ))
						if (! array_key_exists ( 'standardid', $rule ) || in_array ( $standardid, $rule ['standardid'] ))
							if (! array_key_exists ( 'processingid', $rule ) || in_array ( $processingid, $rule ['processingid'] ))
								if (! array_key_exists ( 'teamid', $rule ) || in_array ( $teamid, $rule ['teamid'] ))
									if (! array_key_exists ( 'audiocodecid', $rule ) || in_array ( $audiocodecid, $rule ['audiocodecid'] ))
										if (! array_key_exists ( 'pattern', $rule ) || preg_match ( $rule ['pattern'], $torrent ))
											if (is_numeric ( $rule ['promotion'] )) {
												$sp_state = $rule ['promotion'];
												break;
											}
	}
}
// end of my codes
// add by itolssy
$cname = unesc ( trimcomma ( $_POST ["small_descr"] ) );
$ename = unesc ( trimcomma ( $_POST ["name"] ) );
if (is_banned_title ( $cname, $catid )) {
	bark ( $lang_takeupload ['std_banned_title1'] . $cname . $lang_takeupload ['std_banned_title2'] . $lang_takeupload ['std_banned_title_hit'] );
} else if (is_banned_title ( $ename, $catid )) {
	bark ( $lang_takeupload ['std_banned_title1'] . $ename . $lang_takeupload ['std_banned_title2'] . $lang_takeupload ['std_banned_title_hit'] );
}

if (! count ( $errfile )) {
	/**
	 * ****************************************************************
	 */
	if (is_array ( mysql_fetch_row ( sql_query ( "select info_hash from torrents where pulling_out = '0' AND " . hash_where ( "info_hash", $infohash ) ) ) )) {
		bark ( $lang_takeupload ['std_torrent_existed'] );
	}
	$ret = sql_query ( "INSERT INTO torrents (filename, owner, visible, anonymous, name, size, numfiles, type, url, small_descr, descr, ori_descr, category, source, medium, codec, audiocodec, standard, processing, team, save_as, sp_state, added, sp_time, last_action, nfo, info_hash) VALUES (" . sqlesc ( $fname ) . ", " . sqlesc ( $CURUSER ["id"] ) . ", 'yes', " . sqlesc ( $anonymous ) . ", " . sqlesc ( $torrent ) . ", " . sqlesc ( $totallen ) . ", " . count ( $filelist ) . ", " . sqlesc ( $type ) . ", " . sqlesc ( $url ) . ", " . sqlesc ( $small_descr ) . ", " . sqlesc ( $descr ) . ", " . sqlesc ( $descr ) . ", " . sqlesc ( $catid ) . ", " . sqlesc ( $sourceid ) . ", " . sqlesc ( $mediumid ) . ", " . sqlesc ( $codecid ) . ", " . sqlesc ( $audiocodecid ) . ", " . sqlesc ( $standardid ) . ", " . sqlesc ( $processingid ) . ", " . sqlesc ( $teamid ) . ", " . sqlesc ( $dname ) . ", " . sqlesc ( $sp_state ) . ", " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ", " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ", " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ", " . sqlesc ( $nfo ) . ", " . sqlesc ( $infohash ) . ")" );
	if (! $ret) {
		if (mysql_errno () == 1062)
			bark ( '(2)' . $lang_takeupload ['std_torrent_existed'] );
		// bark("mysql puked: ".mysql_error());
		// bark("mysql puked: ".preg_replace_callback('/./s', "hex_esc2",
		// mysql_error()));
	}
	$id = mysql_insert_id ();
	if ($sp_state > 7 && $sp_state < 14) {
		sql_query ( "UPDATE torrents SET promotion_time_type = 1 WHERE id=" . $id );
	}
} else {
	if(!$is_offer&&!$notoffer) {
		sql_query ( "INSERT INTO offers (userid, name, descr, category, added, allowedtime, allowed) VALUES (" . sqlesc ( $CURUSER ["id"] ) . " , " . sqlesc ( $torrent ) . ", " . sqlesc ( $descr ) . " , " . sqlesc ( $catid ) . " , '" . date ( "Y-m-d H:i:s" ) . "' , '" . date ( "Y-m-d H:i:s" ) . "', 'allowed')" );
		$offerid = mysql_insert_id ();
		$addoffer = true;
	}
	stdfoot ();
}
/**
 * *************************接收用户输入分类信息及保存分类信息***************************************
 */
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
	if ($ename != "")
		$nameset .= "[" . $ename . "]";
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
if (count ( $errfile )) {
	if (! $is_offer) {
		if ($nameset == "") {
			sql_query ( "DELETE FROM offers WHERE id = " . $offerid ) or sqlerr ( __FILE__, __LINE__ );
			bark ( $lang_takeupload ['std_missing_form_data'] . "-offers" );
		}
		
		$offerinfo = sql_query ( "INSERT INTO offersinfo (offerid,category,cname,ename,issuedate,subsinfo,format,imdbnum,specificcat,language,district,version,substeam,animenum,resolution,tvalias,tvseasoninfo,tvshowscontent,tvshowsguest,tvshowsremarks,company,platform,artist,hqname,hqtone) VALUES (" . sqlesc ( $offerid ) . "," . sqlesc ( $catid ) . "," . sqlesc ( $cname ) . "," . sqlesc ( $ename ) . "," . sqlesc ( $issuedate ) . "," . sqlesc ( $subsinfo ) . "," . sqlesc ( $format ) . "," . sqlesc ( $imdbnum ) . "," . sqlesc ( $specificcat ) . "," . sqlesc ( $language ) . "," . sqlesc ( $district ) . "," . sqlesc ( $version ) . "," . sqlesc ( $substeam ) . "," . sqlesc ( $animenum ) . "," . sqlesc ( $resolution ) . "," . sqlesc ( $tvalias ) . "," . sqlesc ( $tvseasoninfo ) . "," . sqlesc ( $tvshowscontent ) . "," . sqlesc ( $tvshowsguest ) . "," . sqlesc ( $tvshowsremarks ) . "," . sqlesc ( $company ) . "," . sqlesc ( $platform ) . "," . sqlesc ( $artist ) . "," . sqlesc ( $hqname ) . "," . sqlesc ( $hqtone ) . ")" );
		
		if (! $offerinfo) {
			if (mysql_errno () == 1062)
				bark ( $lang_takeupload ['std_torrent_existed'] );
			bark ( "mysql puked: " . mysql_error () );
			// bark("mysql puked: ".preg_replace_callback('/./s', "hex_esc2",
			// mysql_error()));
		}
		sql_query ( "UPDATE offers SET name = " . sqlesc ( $nameset ) . " WHERE id = $offerid" ) or sqlerr ( __FILE__, __LINE__ );
		die ();
	}
}

if ($nameset == "") {
	sql_query ( "DELETE FROM torrents WHERE id = " . $id ) or sqlerr ( __FILE__, __LINE__ );
	bark ( $lang_takeupload ['std_missing_form_data'] );
}

$torinfo = sql_query ( "INSERT INTO torrentsinfo (torid,category,cname,ename,issuedate,subsinfo,format,imdbnum,specificcat,language,district,version,substeam,animenum,resolution,tvalias,tvseasoninfo,tvshowscontent,tvshowsguest,tvshowsremarks,company,platform,artist,hqname,hqtone) VALUES (" . sqlesc ( $id ) . "," . sqlesc ( $catid ) . "," . sqlesc ( $cname ) . "," . sqlesc ( $ename ) . "," . sqlesc ( $issuedate ) . "," . sqlesc ( $subsinfo ) . "," . sqlesc ( $format ) . "," . sqlesc ( $imdbnum ) . "," . sqlesc ( $specificcat ) . "," . sqlesc ( $language ) . "," . sqlesc ( $district ) . "," . sqlesc ( $version ) . "," . sqlesc ( $substeam ) . "," . sqlesc ( $animenum ) . "," . sqlesc ( $resolution ) . "," . sqlesc ( $tvalias ) . "," . sqlesc ( $tvseasoninfo ) . "," . sqlesc ( $tvshowscontent ) . "," . sqlesc ( $tvshowsguest ) . "," . sqlesc ( $tvshowsremarks ) . "," . sqlesc ( $company ) . "," . sqlesc ( $platform ) . "," . sqlesc ( $artist ) . "," . sqlesc ( $hqname ) . "," . sqlesc ( $hqtone ) . ")" );

if (! $torinfo) {
	if (mysql_errno () == 1062)
		bark ( $lang_takeupload ['std_torrent_existed'] );
	bark ( "mysql puked: " . mysql_error () );
	// bark("mysql puked: ".preg_replace_callback('/./s', "hex_esc2",
	// mysql_error()));
}

if ($nameset != "") {
	if ($addoffer)
		sql_query ( "UPDATE offers SET name = " . sqlesc ( $nameset ) . " WHERE id = $offerid" ) or sqlerr ( __FILE__, __LINE__ );
	if ($include_torrent) {
		sql_query ( "DELETE FROM torrents WHERE id = " . $offerid ) or sqlerr ( __FILE__, __LINE__ );
		bark ( $lang_takeupload ['std_include_torrent'] );
	}
	
	/*
	 * /* tag a 0day/scene torrent uploaded by account '0day' with a '0day'
	 * label /* changed by qiushenghua @ 2011/10/30 /* start /
	 */
	if ($CURUSER ["username"] == '0day') {
		$pick = " , picktype = '0day'";
		$pick .= " , picktime = " . sqlesc ( date ( "Y-m-d H:i:s" ) );
	}
	/*
	 * /* end /
	 */
	
	sql_query ( "UPDATE torrents SET name = " . sqlesc ( $nameset ) . $pick . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
}

/*
 * /* tag a 0day/scene torrent uploaded by account '0day' with a '0day' label /*
 * added by noyle @ 2011/7/9 /* start / if ( $CURUSER["username"] == '0day' ) {
 * $updatepick = array(); $updatepick[] = "picktype = '0day'"; $updatepick[] =
 * "picktime = ". sqlesc(date("Y-m-d H:i:s")); sql_query("UPDATE torrents SET "
 * . join(",", $updatepick) . " WHERE id = $id") or sqlerr(__FILE__, __LINE__);
 * } /* /* end /
 */

// if($catid==410||$catid==4013)
// if($cname!="")
// sql_query("UPDATE torrents SET name = " . sqlesc($cname) . " WHERE id = $id")
// or sqlerr(__FILE__, __LINE__);

/**
 * *********************************************************************************************
 */

@sql_query ( "DELETE FROM files WHERE torrent = $id" );
foreach ( $filelist as $file ) {
	@sql_query ( "INSERT INTO files (torrent, filename, size) VALUES ($id, " . sqlesc ( $file [0] ) . "," . $file [1] . ")" );
}

// move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");
$fp = fopen ( "$torrent_dir/$id.torrent", "w" );
if ($fp) {
	@fwrite ( $fp, benc ( $dict ), strlen ( benc ( $dict ) ) );
	fclose ( $fp );
}

// ===add karma
KPS ( "+", $uploadtorrent_bonus, $CURUSER ["id"] );
// ===end
if ($_POST ["quote"] && is_numeric ( $_POST ["quote"] ))
	$quote = "(引用 " . $_POST ["quote"] . ")";

write_log ( "$anon 上传了资源 $id $quote (" . $nameset . ") " );

// ===notify people who voted on offer thanks CoLdFuSiOn :)
if ($is_offer) {
	$res = sql_query ( "SELECT `userid` FROM `offervotes` WHERE `userid` != " . $CURUSER ["id"] . " AND `offerid` = " . sqlesc ( $offerid ) . " AND `vote` = 'yeah'" ) or sqlerr ( __FILE__, __LINE__ );
	
	while ( $row = mysql_fetch_assoc ( $res ) ) {
		$pn_msg = $lang_takeupload_target [get_user_lang ( $row ["userid"] )] ['msg_offer_you_voted'] . $torrent . $lang_takeupload_target [get_user_lang ( $row ["userid"] )] ['msg_was_uploaded_by'] . $CURUSER ["username"] . $lang_takeupload_target [get_user_lang ( $row ["userid"] )] ['msg_you_can_download'] . "[url=details.php?id=$id&hit=1]" . $lang_takeupload_target [get_user_lang ( $row ["userid"] )] ['msg_here'] . "[/url]";
		
		// === use this if you DO have subject in your PMs
		$subject = $lang_takeupload_target [get_user_lang ( $row ["userid"] )] ['msg_offer'] . $torrent . $lang_takeupload_target [get_user_lang ( $row ["userid"] )] ['msg_was_just_uploaded'];
		// === use this if you DO NOT have subject in your PMs
		// $some_variable .= "(0, $row[userid], '" . date("Y-m-d H:i:s") . "', "
		// . sqlesc($pn_msg) . ")";
		
		// === use this if you DO have subject in your PMs
		sql_query ( "INSERT INTO messages (sender, subject, receiver, added, msg) VALUES (0, " . sqlesc ( $subject ) . ", $row[userid], " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ", " . sqlesc ( $pn_msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		// === use this if you do NOT have subject in your PMs
		// sql_query("INSERT INTO messages (sender, receiver, added, msg) VALUES
		// ".$some_variable."") or sqlerr(__FILE__, __LINE__);
		// ===end
	}
	// === delete all offer stuff
	sql_query ( "DELETE FROM offers WHERE id = " . $offerid );
	sql_query ( "DELETE FROM offervotes WHERE offerid = " . $offerid );
	sql_query ( "DELETE FROM comments WHERE offer = " . $offerid );
	sql_query ( "DELETE FROM offersinfo WHERE offerid=$offer" );
}
// === end notify people who voted on offer

/* Email notifs */
if ($emailnotify_smtp == 'yes' && $smtptype != 'none') {
	$cat = get_single_value ( "categories", "name", "WHERE id=" . sqlesc ( $catid ) );
	$res = sql_query ( "SELECT id, email, lang FROM users WHERE enabled='yes' AND parked='no' AND status='confirmed' AND notifs LIKE '%[cat$catid]%' AND notifs LIKE '%[email]%' ORDER BY lang ASC" ) or sqlerr ( __FILE__, __LINE__ );
	
	$uploader = $anon;
	
	$size = mksize ( $totallen );
	
	$description = format_comment ( $descr );
	
	// dirty code, change later
	
	$langfolder_array = array (
			"en",
			"chs",
			"cht",
			"ko",
			"ja" 
	);
	$body_arr = array (
			"en" => "",
			"chs" => "",
			"cht" => "",
			"ko" => "",
			"ja" => "" 
	);
	$i = 0;
	foreach ( $body_arr as $body ) {
		$body_arr [$langfolder_array [$i]] = <<<EOD
{$lang_takeupload_target[$langfolder_array[$i]]['mail_hi']}

{$lang_takeupload_target[$langfolder_array[$i]]['mail_new_torrent']}

{$lang_takeupload_target[$langfolder_array[$i]]['mail_torrent_name']}$torrent
{$lang_takeupload_target[$langfolder_array[$i]]['mail_torrent_size']}$size
{$lang_takeupload_target[$langfolder_array[$i]]['mail_torrent_category']}$cat
{$lang_takeupload_target[$langfolder_array[$i]]['mail_torrent_uppedby']}$uploader

{$lang_takeupload_target[$langfolder_array[$i]]['mail_torrent_description']}
-------------------------------------------------------------------------------------------------------------------------
$description
-------------------------------------------------------------------------------------------------------------------------

{$lang_takeupload_target[$langfolder_array[$i]]['mail_torrent']}<b><a href=http://$BASEURL/details.php?id=$id&hit=1 >{$lang_takeupload_target[$langfolder_array[$i]]['mail_here']}</a></b><br />
http://$BASEURL/details.php?id=$id&hit=1

------{$lang_takeupload_target[$langfolder_array[$i]]['mail_yours']}
{$lang_takeupload_target[$langfolder_array[$i]]['mail_team']}
EOD;
		
		$body_arr [$langfolder_array [$i]] = str_replace ( "<br />", "<br />", nl2br ( $body_arr [$langfolder_array [$i]] ) );
		$i ++;
	}
	
	while ( $arr = mysql_fetch_array ( $res ) ) {
		$current_lang = $arr ["lang"];
		$to = $arr ["email"];
		
		sent_mail ( $to, $SITENAME, $SITEEMAIL, change_email_encode ( validlang ( $current_lang ), $lang_takeupload_target [validlang ( $current_lang )] ['mail_title'] . $torrent ), change_email_encode ( validlang ( $current_lang ), $body_arr [validlang ( $current_lang )] ), "torrent upload", false, false, '', get_email_encode ( validlang ( $current_lang ) ), "eYou" );
	}
}

//header ( "Location: details.php?id=" . htmlspecialchars ( $id ) . "&uploaded=1" );
?>
