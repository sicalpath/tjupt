<?php
ini_set('memory_limit', '64M');

require_once("include/benc.php");
require_once("include/bittorrent.php");

//print_r($_POST);exit;
ini_set("upload_max_filesize",$max_torrent_size);
dbconn();
require_once(get_langfile_path("takeupload.php"));
require(get_langfile_path("takeupload.php",true));
set_time_limit(120);
function bark($msg) {
	global $lang_takeupload;
	genbark($msg, "upload_failed");
	die;
}
if(getip()!="219.243.47.169")bark("ip not allowed");
if (!isset($_FILES["file"]))
bark("missing_form_data");

$f = $_FILES["file"];
$fname = unesc($f["name"]);
if (empty($fname))
bark("empty_filename");

	$anonymous = "no";
	$anon = "Wall-E";

$url = parse_imdb_id($_POST['url']);

$nfo = '';
if ($enablenfo_main=='yes'){//NFO文件
	$nfofile = $_FILES['nfo'];
	if ($nfofile['name'] != '') {
	
		if ($nfofile['size'] == 0)
		bark($lang_takeupload['std_zero_byte_nfo']);
		$nfofilename = $nfofile['tmp_name'];
	
		if (@!is_uploaded_file($nfofilename))
		bark($lang_takeupload['std_nfo_upload_failed']);
		$nfo = str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename));
		
		if ($nfofile['size'] > 65535)
		$nfo = '';}
}

$small_descr = unesc("");
$descr = unesc($lang_takeupload['autoseed0day']);
$catid = "410";
if($_POST['catid'])$catid = $_POST['catid'];


if (!validfilename($fname))
bark($lang_takeupload['std_invalid_filename']);
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
bark($lang_takeupload['std_filename_not_torrent']);
$shortfname = $torrent = $matches[1];
if (!empty($_POST["name"]))
$torrent = unesc($_POST["name"]);
if ($f['size'] > $max_torrent_size)
bark($lang_takeupload['std_torrent_file_too_big'].number_format($max_torrent_size).$lang_takeupload['std_remake_torrent_note']);
$tmpname = $f["tmp_name"];
if (!is_uploaded_file($tmpname))
bark("eek");
if (!filesize($tmpname))
bark($lang_takeupload['std_empty_file']);
$dict = bdec_file($tmpname, $max_torrent_size);
if (!isset($dict))
bark($lang_takeupload['std_not_bencoded_file']);
function dict_check($d, $s) {
	global $lang_takeupload;
	if ($d["type"] != "dictionary")
	bark($lang_takeupload['std_not_a_dictionary']);
	$a = explode(":", $s);
	$dd = $d["value"];
	$ret = array();
	foreach ($a as $k) {
		unset($t);
		if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
			$k = $m[1];
			$t = $m[2];
		}
		if (!isset($dd[$k]))
		bark($lang_takeupload['std_dictionary_is_missing_key']);
		if (isset($t)) {
			if ($dd[$k]["type"] != $t)
			bark($lang_takeupload['std_invalid_entry_in_dictionary']);
			$ret[] = $dd[$k]["value"];
		}
		else
		$ret[] = $dd[$k];
	}
	return $ret;
}

function dict_get($d, $k, $t) {
	global $lang_takeupload;
	if ($d["type"] != "dictionary")
	bark($lang_takeupload['std_not_a_dictionary']);
	$dd = $d["value"];
	if (!isset($dd[$k]))
	return;
	$v = $dd[$k];
	if ($v["type"] != $t)
	bark($lang_takeupload['std_invalid_dictionary_entry_type']);
	return $v["value"];
}

list($ann, $info) = dict_check($dict, "announce(string):info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");


if (strlen($pieces) % 20 != 0)
bark($lang_takeupload['std_invalid_pieces']);

$filelist = array();
$totallen = dict_get($info, "length", "integer");
if (isset($totallen)) {
	$filelist[] = array($dname, $totallen);
	$type = "single";
}
else {
	$flist = dict_get($info, "files", "list");
	if (!isset($flist))
	bark($lang_takeupload['std_missing_length_and_files']);
	if (!count($flist))
	bark("no files");
	$totallen = 0;
	foreach ($flist as $fn) {
		list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
		$totallen += $ll;
		$ffa = array();
		foreach ($ff as $ffe) {
			if ($ffe["type"] != "string")
			bark($lang_takeupload['std_filename_errors']);
			$ffa[] = $ffe["value"];
		}
		if (!count($ffa))
		bark($lang_takeupload['std_filename_errors']);
		$ffe = implode("/", $ffa);
		$filelist[] = array($ffe, $ll);
	}
	$type = "multi";
}

$dict['value']['announce']=bdec(benc_str( get_protocol_prefix() . $announce_urls[0]));  // change announce url to local
$dict['value']['info']['value']['private']=bdec('i1e');  // add private tracker flag
$dict['value']['info']['value']['source']=bdec(benc_str( "[$BASEURL] $SITENAME"));
$dict['value']['created by']=bdec(benc_str( "Wall-E@TJUPT"));  // change created by
unset($dict['value']['announce-list']); // remove multi-tracker capability
unset($dict['value']['nodes']); // remove cached peers (Bitcomet & Azareus)
$dict=bdec(benc($dict)); // double up on the becoding solves the occassional misgenerated infohash
list($ann, $info) = dict_check($dict, "announce(string):info");

$infohash = pack("H*", sha1($info["string"]));

function hex_esc2($matches) {
	return sprintf("%02x", ord($matches[0]));
}

//die(phpinfo());

//die("magic:" . get_magic_quotes_gpc());

//die("\\' pos:" . strpos($infohash,"\\") . ", after sqlesc:" . (strpos(sqlesc($infohash),"\\") == false ? "gone" : strpos(sqlesc($infohash),"\\")));

//die(preg_replace_callback('/./s', "hex_esc2", $infohash));


// Replace punctuation characters with spaces

$torrent = str_replace("_", " ", $torrent);

if ($largesize_torrent && $totallen > ($largesize_torrent * 1073741824)) //Large Torrent Promotion
{
	switch($largepro_torrent)
	{
		case 2: //Free
		{
			$sp_state = 2;
			break;
		}
		case 3: //2X
		{
			$sp_state = 3;
			break;
		}
		case 4: //2X Free
		{
			$sp_state = 4;
			break;
		}
		case 5: //Half Leech
		{
			$sp_state = 5;
			break;
		}
		case 6: //2X Half Leech
		{
			$sp_state = 6;
			break;
		}
		case 7: //30% Leech
		{
			$sp_state = 7;
			break;
		}case 8: //forever Free
		{
			$sp_state = 8;
			break;
		}
		default: //normal
		{
			$sp_state = 1;
			break;
		}
	}
}
elseif($middlesize_torrent && $totallen > ($middlesize_torrent * 1073741824))
{
	switch($middlepro_torrent)
	{
		case 2: //Free
		{
			$sp_state = 2;
			break;
		}
		case 3: //2X
		{
			$sp_state = 3;
			break;
		}
		case 4: //2X Free
		{
			$sp_state = 4;
			break;
		}
		case 5: //Half Leech
		{
			$sp_state = 5;
			break;
		}
		case 6: //2X Half Leech
		{
			$sp_state = 6;
			break;
		}
		case 7: //30% Leech
		{
			$sp_state = 7;
			break;
		}
		case 8: //Free Forever
		{
			$sp_state = 8;
			break;
		}
		case 9: //2X Forever
		{
			$sp_state = 9;
			break;
		}
		case 10: //2X Free Forever
		{
			$sp_state = 10;
			break;
		}
		case 11: //Half Leech Forever
		{
			$sp_state = 11;
			break;
		}
		case 12: //2X Half Leech Forever
		{
			$sp_state = 12;
			break;
		}
		case 13: //30% Leech Forever
		{
			$sp_state = 13;
			break;
		}
		default: //normal
		{
			$sp_state = 1;
			break;
		}
	}		
}
else{ //ramdom torrent promotion
	$sp_id = mt_rand(1,100);
	if($sp_id <= ($probability = $randomfree_torrent)) //Free
		$sp_state = 2;
	elseif($sp_id <= ($probability += $randomtwoup_torrent)) //2X
		$sp_state = 3;
	elseif($sp_id <= ($probability += $randomtwoupfree_torrent)) //2X Free
		$sp_state = 4;
	elseif($sp_id <= ($probability += $randomhalfleech_torrent)) //Half Leech
		$sp_state = 5;
	elseif($sp_id <= ($probability += $randomtwouphalfdown_torrent)) //2X Half Leech
		$sp_state = 6;
	elseif($sp_id <= ($probability += $randomthirtypercentdown_torrent)) //30% Leech
		$sp_state = 7;
	elseif($sp_id <= ($probability += $randomfreeforever_torrent)) //Free Forever
		$sp_state = 8;
	elseif($sp_id <= ($probability += $randomtwoupforever_torrent)) //2X Forever
		$sp_state = 9;
	elseif($sp_id <= ($probability += $randomtwoupfreeforever_torrent)) //2X Free Forever
		$sp_state = 10;
	elseif($sp_id <= ($probability += $randomhalfleechforever_torrent)) //Half Leech Forever
		$sp_state = 11;
	elseif($sp_id <= ($probability += $randomtwouphalfdownforever_torrent)) //2X Half Leech Forever
		$sp_state = 12;
	elseif($sp_id <= ($probability += $randomthirtypercentdownforever_torrent)) //30% Leech Forever
		$sp_state = 13;
	else
		$sp_state = 1; //normal
}
if($catid==404)$sp_state=11;//资料类特殊优惠

if ($altname_main == 'yes'){
$size_part = str_replace(" ", "", mksize($totallen));
$date_part = date("m.d.y");
$category_part = get_single_value("categories","name","WHERE id = ".sqlesc($catid));
$torrent = "【".$date_part."】".($_POST["name"] ? "[".$_POST["name"]."]" : "").($cnname_part ? "[".$cnname_part."]" : "");
}

$ret = sql_query("INSERT INTO torrents (filename, owner, visible, anonymous, name, size, numfiles, picktype, type, url, small_descr, descr, ori_descr, category, source, medium, codec, audiocodec, standard, processing, team, save_as, sp_state, added, sp_time, picktime, last_action, nfo, info_hash) VALUES (".sqlesc($fname).", '99', 'yes', ".sqlesc($anonymous).", ".sqlesc($torrent).", ".sqlesc($totallen).", ".count($filelist).", '0day', ".sqlesc($type).", ".sqlesc($url).", ".sqlesc($small_descr).", ".sqlesc($descr).", ".sqlesc($descr).", ".sqlesc($catid).", ".sqlesc($sourceid).", ".sqlesc($mediumid).", ".sqlesc($codecid).", ".sqlesc($audiocodecid).", ".sqlesc($standardid).", ".sqlesc($processingid).", ".sqlesc($teamid).", ".sqlesc($dname).", ".sqlesc($sp_state) .
", " . sqlesc(date("Y-m-d H:i:s")) . ", " . sqlesc(date("Y-m-d H:i:s")) . ", " . sqlesc(date("Y-m-d H:i:s")) . ", " . sqlesc(date("Y-m-d H:i:s")) . ", ".sqlesc($nfo).", " . sqlesc($infohash). ")");
if (!$ret) {
	if (mysql_errno() == 1062)
 $res = mysql_fetch_assoc(sql_query("SELECT id FROM torrents WHERE info_hash =" . sqlesc($infohash)));

$id = $res["id"];
	//bark("mysql puked: ".preg_replace_callback('/./s', "hex_esc2", mysql_error()));
}
else {
$id = mysql_insert_id();

/***************************接收用户输入分类信息及保存分类信息****************************************/
$ename = unesc(trimcomma($_POST["ename"]));
$specificcat = unesc(trimcomma($_POST["specificcat"]));
$issuedate = unesc(trimcomma($_POST["issuedate"]));
$subsinfo = unesc(trimcomma($_POST["subsinfo"]));
$district = unesc(trimcomma($_POST["district"]));
$format = unesc(trimcomma($_POST["format"]));
$language = unesc(trimcomma($_POST["language"]));

$nameset = "";
	if($ename!="")
		$nameset .= "[".$ename."]";
	
$torinfo = sql_query("INSERT INTO torrentsinfo (torid,category,cname,ename,issuedate,subsinfo,format,imdbnum,specificcat,language,district,version,substeam,animenum,resolution,tvalias,tvseasoninfo,tvshowscontent,tvshowsguest,tvshowsremarks,company,platform,artist,hqname,hqtone) VALUES (".sqlesc($id).",".sqlesc($catid).",".sqlesc($cname).",".sqlesc($ename).",".sqlesc($issuedate).",".sqlesc($subsinfo).",".sqlesc($format).",".sqlesc($imdbnum).",".sqlesc($specificcat).",".sqlesc($language).",".sqlesc($district).",".sqlesc($version).",".sqlesc($substeam).",".sqlesc($animenum).",".sqlesc($resolution).",".sqlesc($tvalias).",".sqlesc($tvseasoninfo).",".sqlesc($tvshowscontent).",".sqlesc($tvshowsguest).",".sqlesc($tvshowsremarks).",".sqlesc($company).",".sqlesc($platform).",".sqlesc($artist).",".sqlesc($hqname).",".sqlesc($hqtone).")");

if (!$torinfo) {
	if (mysql_errno() == 1062)
	bark($lang_takeupload['std_torrent_existed']);
	bark("mysql puked: ".mysql_error());
	//bark("mysql puked: ".preg_replace_callback('/./s', "hex_esc2", mysql_error()));
}

if($nameset!=""){
	sql_query("UPDATE torrents SET name = " . sqlesc($nameset) . " WHERE id = $id") or sqlerr(__FILE__, __LINE__);
}


/************************************************************************************************/

@sql_query("DELETE FROM files WHERE torrent = $id");
foreach ($filelist as $file) {
	@sql_query("INSERT INTO files (torrent, filename, size) VALUES ($id, ".sqlesc($file[0]).",".$file[1].")");
}

//move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");
$fp = fopen("$torrent_dir/$id.torrent", "w");
if ($fp)
{
	@fwrite($fp, benc($dict), strlen(benc($dict)));
	fclose($fp);
}

write_log("机器人 $anon 上传了资源 $id ");
}

$base_announce_url = $announce_urls[0];
$res = sql_query("SELECT name, filename, save_as,  size, owner,banned FROM torrents WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_assoc($res);
$fn = "$torrent_dir/$id.torrent";
$dict = bdec_file($fn, $max_torrent_size);
$dict['value']['announce']['value'] = "http://pttracker4.tju.edu.cn/walleannounce.php?passkey=6f5dcb625c5f3ee971d9cdf590b0f554";
$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);

header("Content-Type: application/x-bittorrent");

if ( str_replace("Gecko", "", $_SERVER['HTTP_USER_AGENT']) != $_SERVER['HTTP_USER_AGENT'])
{
	header ("Content-Disposition: attachment; filename=\"$torrentnameprefix.".$row["save_as"].".torrent\" ; charset=utf-8");
}
else if ( str_replace("Firefox", "", $_SERVER['HTTP_USER_AGENT']) != $_SERVER['HTTP_USER_AGENT'] )
{
	header ("Content-Disposition: attachment; filename=\"$torrentnameprefix.".$row["save_as"].".torrent\" ; charset=utf-8");
}
else if ( str_replace("Opera", "", $_SERVER['HTTP_USER_AGENT']) != $_SERVER['HTTP_USER_AGENT'] )
{
	header ("Content-Disposition: attachment; filename=\"$torrentnameprefix.".$row["save_as"].".torrent\" ; charset=utf-8");
}
else if ( str_replace("IE", "", $_SERVER['HTTP_USER_AGENT']) != $_SERVER['HTTP_USER_AGENT'] )
{
	header ("Content-Disposition: attachment; filename=".str_replace("+", "%20", rawurlencode("$torrentnameprefix." . $row["save_as"] .".torrent")));
}
else
{
	header ("Content-Disposition: attachment; filename=".str_replace("+", "%20", rawurlencode("$torrentnameprefix." . $row["save_as"] .".torrent")));
}

print(benc($dict));
sql_query("INSERT INTO autoseeding (torrentid , filename , completed , remark ) VALUES (".sqlesc($id)." , '".$fname."' , 'yes' , 'upload')") or sqlerr(__FILE__, __LINE__);
?>
