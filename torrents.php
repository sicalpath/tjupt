<?php
require_once("include/bittorrent.php");
require_once("include/tjuip_helper.php");
dbconn(true);
require_once(get_langfile_path("torrents.php"));
loggedinorreturn();
parked();

assert_tjuip_or_mod();

if ($showextinfo['imdb'] == 'yes')
	require_once ("imdb/imdb.class.php");
//check searchbox
$sectiontype = $browsecatmode;
$showsubcat = get_searchbox_value($sectiontype, 'showsubcat');//whether show subcategory (i.e. sources, codecs) or not
$showsource = get_searchbox_value($sectiontype, 'showsource'); //whether show sources or not
$showmedium = get_searchbox_value($sectiontype, 'showmedium'); //whether show media or not
$showcodec = get_searchbox_value($sectiontype, 'showcodec'); //whether show codecs or not
$showstandard = get_searchbox_value($sectiontype, 'showstandard'); //whether show standards or not
$showprocessing = get_searchbox_value($sectiontype, 'showprocessing'); //whether show processings or not
$showteam = get_searchbox_value($sectiontype, 'showteam'); //whether show teams or not
$showaudiocodec = get_searchbox_value($sectiontype, 'showaudiocodec'); //whether show audio codec or not
$catsperrow = get_searchbox_value($sectiontype, 'catsperrow'); //show how many cats per line in search box
$catpadding = get_searchbox_value($sectiontype, 'catpadding'); //padding space between categories in pixel
/*********************取出非试种***********************/
$ret = array();
$res = sql_query("SELECT id, mode, name, image FROM categories WHERE mode = ".sqlesc($sectiontype)." AND id<413 AND id NOT IN (406) ORDER BY sort_index, id")or die(mysql_error());
while ($row = mysql_fetch_array($res))
	$ret[] = $row;
$cats = $ret;
/******************************************************/
if ($showsubcat){
	if ($showsource) $sources = searchbox_item_list("sources");
	if ($showmedium) $media = searchbox_item_list("media");
	if ($showcodec) $codecs = searchbox_item_list("codecs");
	if ($showstandard) $standards = searchbox_item_list("standards");
	if ($showprocessing) $processings = searchbox_item_list("processings");
	if ($showteam) $teams = searchbox_item_list("teams");
	if ($showaudiocodec) $audiocodecs = searchbox_item_list("audiocodecs");
}

$searchstr_ori = htmlspecialchars(trim($_GET["search"]));
$searchstr = mysql_real_escape_string(trim($_GET["search"]));
if (empty($searchstr))
	unset($searchstr);

// sorting by MarkoStamcar
if ($_GET['sort'] && $_GET['type']) {

	$column = '';
	$ascdesc = '';

	switch($_GET['sort']) {
		case '1': $column = "name"; break;
		case '2': $column = "numfiles"; break;
		case '3': $column = "comments"; break;
		case '4': $column = "added"; break;
		case '5': $column = "size"; break;
		case '6': $column = "times_completed"; break;
		case '7': $column = "seeders"; break;
		case '8': $column = "leechers"; break;
		case '9': $column = "owner"; break;
		default: $column = "id"; break;
	}

	switch($_GET['type']) {
		case 'asc': $ascdesc = "ASC"; $linkascdesc = "asc"; break;
		case 'desc': $ascdesc = "DESC"; $linkascdesc = "desc"; break;
		default: $ascdesc = "DESC"; $linkascdesc = "desc"; break;
	}

	if($column == "owner")
	{
		$orderby = "ORDER BY pos_state DESC, torrents_c.anonymous, users.username " . $ascdesc;
	}
	else
	{
		$orderby = "ORDER BY pos_state DESC, torrents_c." . $column . " " . $ascdesc;
	}

	$pagerlink = "sort=" . intval($_GET['sort']) . "&type=" . $linkascdesc . "&";

} else {

	$orderby = "ORDER BY pos_state DESC, torrents_c.id DESC";
	$pagerlink = "";

}

$addparam = "";
$wherea = array();
$wherecatina = array();
if ($showsubcat){
	if ($showsource) $wheresourceina = array();
	if ($showmedium) $wheremediumina = array();
	if ($showcodec) $wherecodecina = array();
	if ($showstandard) $wherestandardina = array();
	if ($showprocessing) $whereprocessingina = array();
	if ($showteam) $whereteamina = array();
	if ($showaudiocodec) $whereaudiocodecina = array();
}
//----------------- start whether show torrents from all sections---------------------//
if ($_GET)
	$allsec = 0 + $_GET["allsec"];
else $allsec = 0;
if ($allsec == 1)		//show torrents from all sections
{
	$addparam .= "allsec=1&";
}
// ----------------- end whether ignoring section ---------------------//
// ----------------- start bookmarked ---------------------//
if ($_GET)
	$inclbookmarked = 0 + $_GET["inclbookmarked"];
elseif ($CURUSER['notifs']){
	if (strpos($CURUSER['notifs'], "[inclbookmarked=0]") !== false)
		$inclbookmarked = 0;
	elseif (strpos($CURUSER['notifs'], "[inclbookmarked=1]") !== false)
		$inclbookmarked = 1;
	elseif (strpos($CURUSER['notifs'], "[inclbookmarked=2]") !== false)
		$inclbookmarked = 2;
}
else $inclbookmarked = 0;

if (!in_array($inclbookmarked,array(0,1,2)))
{
	$inclbookmarked = 0;
	write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking inclbookmarked field in" . $_SERVER['SCRIPT_NAME'], 'mod');
}
if ($inclbookmarked == 0)  //all(bookmarked,not)
{
	$addparam .= "inclbookmarked=0&";
}
elseif ($inclbookmarked == 1)		//bookmarked
{
	$addparam .= "inclbookmarked=1&";
	if(isset($CURUSER))
	$wherea[] = "torrents_c.id IN (SELECT torrentid FROM bookmarks WHERE userid=" . $CURUSER['id'] . ")";
}
elseif ($inclbookmarked == 2)		//not bookmarked
{
	$addparam .= "inclbookmarked=2&";
	if(isset($CURUSER))
	$wherea[] = "torrents_c.id NOT IN (SELECT torrentid FROM bookmarks WHERE userid=" . $CURUSER['id'] . ")";
}
// ----------------- end bookmarked ---------------------//

if (!isset($CURUSER) || get_user_class() < $seebanned_class)
	// modified by noyle, qiushenghua should check this.
	//$wherea[] = "( banned != 'yes' AND owner != ".$CURUSER["id"].")";
	$wherea[] = "( ( banned != 'yes' ) OR ( banned = 'yes' AND owner = '".$CURUSER["id"]."' ) )";
// ----------------- start include dead ---------------------//
if (isset($_GET["incldead"]))
	$include_dead = 0 + $_GET["incldead"];
elseif ($CURUSER['notifs']){
	if (strpos($CURUSER['notifs'], "[incldead=0]") !== false)
		$include_dead = 0;
	elseif (strpos($CURUSER['notifs'], "[incldead=1]") !== false)
		$include_dead = 1;
	elseif (strpos($CURUSER['notifs'], "[incldead=2]") !== false)
		$include_dead = 2;
	else $include_dead = 0;
}
else $include_dead = 0;

if (!in_array($include_dead,array(0,1,2)))
{
	$include_dead = 0;
	write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking incldead field in" . $_SERVER['SCRIPT_NAME'], 'mod');
}
if ($include_dead == 0)  //all(active,dead)
{
	$addparam .= "incldead=0&";
}
elseif ($include_dead == 1)		//active
{
	$addparam .= "incldead=1&";
	$wherea[] = "visible = 'yes'";
}
elseif ($include_dead == 2)		//dead
{
	$addparam .= "incldead=2&";
	$wherea[] = "visible = 'no'";
}
// ----------------- end include dead ---------------------//
// ----------------- start picktype ---------------------//
if (isset($_GET["picktype"]))
{
	$picktype = 0 + $_GET["picktype"];
}
else $picktype = 0;

if ($picktype == 0)  //all
{
	$addparam .= "picktype=0&";
}
elseif ($picktype == 1)		//normal
{
	$addparam .= "picktype=1&";
	$wherea[] = "picktype = 'normal'";
}
elseif ($picktype == 2)		//hot
{
	$addparam .= "picktype=2&";
	$wherea[] = "picktype = 'hot'";
}
elseif ($picktype == 3)		//classic
{
	$addparam .= "picktype=3&";
	$wherea[] = "picktype = 'classic'";
}
elseif ($picktype == 4)		//recommended
{
	$addparam .= "picktype=4&";	
	$wherea[] = "picktype = 'recommended'";
}
elseif ($picktype == 5)		//0day
{
	$addparam .= "picktype=5&";
	$wherea[] = "picktype = '0day'";
}
elseif ($picktype == 6)		//IMDB
{
	$addparam .= "picktype=6&";
	$wherea[] = "picktype = 'IMDB'";
}
// ----------------- end picktype ---------------------//
if ($_GET)
	$special_state = 0 + $_GET["spstate"];
elseif ($CURUSER['notifs']){
	if (strpos($CURUSER['notifs'], "[spstate=0]") !== false)
		$special_state = 0;
	elseif (strpos($CURUSER['notifs'], "[spstate=1]") !== false)
		$special_state = 1;
	elseif (strpos($CURUSER['notifs'], "[spstate=2]") !== false)
		$special_state = 2;
	elseif (strpos($CURUSER['notifs'], "[spstate=3]") !== false)
		$special_state = 3;
	elseif (strpos($CURUSER['notifs'], "[spstate=4]") !== false)
		$special_state = 4;
	elseif (strpos($CURUSER['notifs'], "[spstate=5]") !== false)
		$special_state = 5;
	elseif (strpos($CURUSER['notifs'], "[spstate=6]") !== false)
		$special_state = 6;
	elseif (strpos($CURUSER['notifs'], "[spstate=7]") !== false)
		$special_state = 7;
	elseif (strpos($CURUSER['notifs'], "[spstate=8]") !== false)
		$special_state = 8;
}
else $special_state = 0;

if (!in_array($special_state,array(0,1,2,3,4,5,6,7,8)))
{
	$special_state = 0;
	write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking spstate field in " . $_SERVER['SCRIPT_NAME'], 'mod');
}
if($special_state == 0)	//all
{
	$addparam .= "spstate=0&";
}
elseif ($special_state == 1)	//normal
{
	$addparam .= "spstate=1&";

	$wherea[] = "sp_state = 1";

	if(get_global_sp_state() == 1)
	{
		$wherea[] = "sp_state = 1";
	}
}
elseif ($special_state == 2)	//free
{
	$addparam .= "spstate=2&";

	if(get_global_sp_state() == 1)
	{
		$wherea[] = "sp_state = 2 OR sp_state = 8";
	}
	else if(get_global_sp_state() == 2)
	{
		;
	}
}
elseif ($special_state == 3)	//2x up
{
	$addparam .= "spstate=3&";
	if(get_global_sp_state() == 1)	//only sp state
	{
		$wherea[] = "sp_state = 3 OR sp_state = 9";
	}
	else if(get_global_sp_state() == 3)	//all
	{
		;
	}
}
elseif ($special_state == 4)	//2x up and free
{
	$addparam .= "spstate=4&";

	if(get_global_sp_state() == 1)	//only sp state
	{
		$wherea[] = "sp_state = 4 OR sp_state = 10";
	}
	else if(get_global_sp_state() == 4)	//all
	{
		;
	}
}
elseif ($special_state == 5)	//half down
{
	$addparam .= "spstate=5&";

	if(get_global_sp_state() == 1)	//only sp state
	{
		$wherea[] = "sp_state = 5 OR sp_state = 11";
	}
	else if(get_global_sp_state() == 5)	//all
	{
		;
	}
}
elseif ($special_state == 6)	//half down
{
	$addparam .= "spstate=6&";

	if(get_global_sp_state() == 1)	//only sp state
	{
		$wherea[] = "sp_state = 6 OR sp_state = 12";
	}
	else if(get_global_sp_state() == 6)	//all
	{
		;
	}
}
elseif ($special_state == 7)	//30% down
{
	$addparam .= "spstate=7&";

	if(get_global_sp_state() == 1)	//only sp state
	{
		$wherea[] = "sp_state = 7 OR sp_state = 13";
	}
	else if(get_global_sp_state() == 7)	//all
	{
		;
	}
}
elseif ($special_state == 8)	//30% down
{
	$addparam .= "spstate=8&";

	if(get_global_sp_state() == 1)	//only sp state
	{
		$wherea[] = "sp_state > 1 ";
	}
}

$category_get = 0 + $_GET["cat"];
if($category_get == 406)
{
	$category_get = 0;
}
if ($showsubcat){
if ($showsource) $source_get = 0 + $_GET["source"];
if ($showmedium) $medium_get = 0 + $_GET["medium"];
if ($showcodec) $codec_get = 0 + $_GET["codec"];
if ($showstandard) $standard_get = 0 + $_GET["standard"];
if ($showprocessing) $processing_get = 0 + $_GET["processing"];
if ($showteam) $team_get = 0 + $_GET["team"];
if ($showaudiocodec) $audiocodec_get = 0 + $_GET["audiocodec"];
}

$all = 0 + $_GET["all"];

if (!$all)
{
	if (!$_GET && $CURUSER['notifs'])
	{
		$all = true;
		foreach ($cats as $cat)
		{
			$all &= $cat[id];
			$mystring = $CURUSER['notifs'];
			$findme  = '[cat'.$cat['id'].']';
			$search = strpos($mystring, $findme);
			if ($search === false)
			$catcheck = false;
			else
			$catcheck = true;

			if ($catcheck)
			{
				$wherecatina[] = $cat[id];
				$addparam .= "cat$cat[id]=1&";
			}
		}
		if ($showsubcat){
		if ($showsource)
		foreach ($sources as $source)
		{
			$all &= $source[id];
			$mystring = $CURUSER['notifs'];
			$findme  = '[sou'.$source['id'].']';
			$search = strpos($mystring, $findme);
			if ($search === false)
			$sourcecheck = false;
			else
			$sourcecheck = true;

			if ($sourcecheck)
			{
				$wheresourceina[] = $source[id];
				$addparam .= "source$source[id]=1&";
			}
		}
		if ($showmedium)
		foreach ($media as $medium)
		{
			$all &= $medium[id];
			$mystring = $CURUSER['notifs'];
			$findme  = '[med'.$medium['id'].']';
			$search = strpos($mystring, $findme);
			if ($search === false)
			$mediumcheck = false;
			else
			$mediumcheck = true;

			if ($mediumcheck)
			{
				$wheremediumina[] = $medium[id];
				$addparam .= "medium$medium[id]=1&";
			}
		}
		if ($showcodec)
		foreach ($codecs as $codec)
		{
			$all &= $codec[id];
			$mystring = $CURUSER['notifs'];
			$findme  = '[cod'.$codec['id'].']';
			$search = strpos($mystring, $findme);
			if ($search === false)
			$codeccheck = false;
			else
			$codeccheck = true;

			if ($codeccheck)
			{
				$wherecodecina[] = $codec[id];
				$addparam .= "codec$codec[id]=1&";
			}
		}
		if ($showstandard)
		foreach ($standards as $standard)
		{
			$all &= $standard[id];
			$mystring = $CURUSER['notifs'];
			$findme  = '[sta'.$standard['id'].']';
			$search = strpos($mystring, $findme);
			if ($search === false)
			$standardcheck = false;
			else
			$standardcheck = true;

			if ($standardcheck)
			{
				$wherestandardina[] = $standard[id];
				$addparam .= "standard$standard[id]=1&";
			}
		}
		if ($showprocessing)
		foreach ($processings as $processing)
		{
			$all &= $processing[id];
			$mystring = $CURUSER['notifs'];
			$findme  = '[pro'.$processing['id'].']';
			$search = strpos($mystring, $findme);
			if ($search === false)
			$processingcheck = false;
			else
			$processingcheck = true;

			if ($processingcheck)
			{
				$whereprocessingina[] = $processing[id];
				$addparam .= "processing$processing[id]=1&";
			}
		}
		if ($showteam)
		foreach ($teams as $team)
		{
			$all &= $team[id];
			$mystring = $CURUSER['notifs'];
			$findme  = '[tea'.$team['id'].']';
			$search = strpos($mystring, $findme);
			if ($search === false)
			$teamcheck = false;
			else
			$teamcheck = true;

			if ($teamcheck)
			{
				$whereteamina[] = $team[id];
				$addparam .= "team$team[id]=1&";
			}
		}
		if ($showaudiocodec)
		foreach ($audiocodecs as $audiocodec)
		{
			$all &= $audiocodec[id];
			$mystring = $CURUSER['notifs'];
			$findme  = '[aud'.$audiocodec['id'].']';
			$search = strpos($mystring, $findme);
			if ($search === false)
			$audiocodeccheck = false;
			else
			$audiocodeccheck = true;

			if ($audiocodeccheck)
			{
				$whereaudiocodecina[] = $audiocodec[id];
				$addparam .= "audiocodec$audiocodec[id]=1&";
			}
		}
		}	
	}
	// when one clicked the cat, source, etc. name/image
	elseif ($category_get)
	{
		int_check($category_get,true,true,true);
		$wherecatina[] = $category_get;
		$addparam .= "cat=$category_get&";
	}
	elseif ($medium_get)
	{
		int_check($medium_get,true,true,true);
		$wheremediumina[] = $medium_get;
		$addparam .= "medium=$medium_get&";
	}
	elseif ($source_get)
	{
		int_check($source_get,true,true,true);
		$wheresourceina[] = $source_get;
		$addparam .= "source=$source_get&";
	}
	elseif ($codec_get)
	{
		int_check($codec_get,true,true,true);
		$wherecodecina[] = $codec_get;
		$addparam .= "codec=$codec_get&";
	}
	elseif ($standard_get)
	{
		int_check($standard_get,true,true,true);
		$wherestandardina[] = $standard_get;
		$addparam .= "standard=$standard_get&";
	}
	elseif ($processing_get)
	{
		int_check($processing_get,true,true,true);
		$whereprocessingina[] = $processing_get;
		$addparam .= "processing=$processing_get&";
	}
	elseif ($team_get)
	{
		int_check($team_get,true,true,true);
		$whereteamina[] = $team_get;
		$addparam .= "team=$team_get&";
	}
	elseif ($audiocodec_get)
	{
		int_check($audiocodec_get,true,true,true);
		$whereaudiocodecina[] = $audiocodec_get;
		$addparam .= "audiocodec=$audiocodec_get&";
	}
	else	//select and go
	{
		$all = True;
		foreach ($cats as $cat)
		{
			$all &= $_GET["cat$cat[id]"];
			if ($_GET["cat$cat[id]"])
			{
				$wherecatina[] = $cat[id];
				$addparam .= "cat$cat[id]=1&";
			}
		}
		if ($showsubcat){
		if ($showsource)
		foreach ($sources as $source)
		{
			$all &= $_GET["source$source[id]"];
			if ($_GET["source$source[id]"])
			{
				$wheresourceina[] = $source[id];
				$addparam .= "source$source[id]=1&";
			}
		}
		if ($showmedium)
		foreach ($media as $medium)
		{
			$all &= $_GET["medium$medium[id]"];
			if ($_GET["medium$medium[id]"])
			{
				$wheremediumina[] = $medium[id];
				$addparam .= "medium$medium[id]=1&";
			}
		}
		if ($showcodec)
		foreach ($codecs as $codec)
		{
			$all &= $_GET["codec$codec[id]"];
			if ($_GET["codec$codec[id]"])
			{
				$wherecodecina[] = $codec[id];
				$addparam .= "codec$codec[id]=1&";
			}
		}
		if ($showstandard)
		foreach ($standards as $standard)
		{
			$all &= $_GET["standard$standard[id]"];
			if ($_GET["standard$standard[id]"])
			{
				$wherestandardina[] = $standard[id];
				$addparam .= "standard$standard[id]=1&";
			}
		}
		if ($showprocessing)
		foreach ($processings as $processing)
		{
			$all &= $_GET["processing$processing[id]"];
			if ($_GET["processing$processing[id]"])
			{
				$whereprocessingina[] = $processing[id];
				$addparam .= "processing$processing[id]=1&";
			}
		}
		if ($showteam)
		foreach ($teams as $team)
		{
			$all &= $_GET["team$team[id]"];
			if ($_GET["team$team[id]"])
			{
				$whereteamina[] = $team[id];
				$addparam .= "team$team[id]=1&";
			}
		}
		if ($showaudiocodec)
		foreach ($audiocodecs as $audiocodec)
		{
			$all &= $_GET["audiocodec$audiocodec[id]"];
			if ($_GET["audiocodec$audiocodec[id]"])
			{
				$whereaudiocodecina[] = $audiocodec[id];
				$addparam .= "audiocodec$audiocodec[id]=1&";
			}
		}
		}
	}
}

if ($all)
{
	//stderr("in if all","");
	$wherecatina = array();
	if ($showsubcat){
	$wheresourceina = array();
	$wheremediumina = array();
	$wherecodecina = array();
	$wherestandardina = array();
	$whereprocessingina = array();
	$whereteamina = array();
	$whereaudiocodecina = array();}
	$addparam .= "";
}
//stderr("", count($wherecatina)."-". count($wheresourceina));

if (count($wherecatina) > 1)
$wherecatin = implode(",",$wherecatina);
elseif (count($wherecatina) == 1)
$wherea[] = "category = $wherecatina[0]";

if ($showsubcat){
if ($showsource){
if (count($wheresourceina) > 1)
$wheresourcein = implode(",",$wheresourceina);
elseif (count($wheresourceina) == 1)
$wherea[] = "source = $wheresourceina[0]";}

if ($showmedium){
if (count($wheremediumina) > 1)
$wheremediumin = implode(",",$wheremediumina);
elseif (count($wheremediumina) == 1)
$wherea[] = "medium = $wheremediumina[0]";}

if ($showcodec){
if (count($wherecodecina) > 1)
$wherecodecin = implode(",",$wherecodecina);
elseif (count($wherecodecina) == 1)
$wherea[] = "codec = $wherecodecina[0]";}

if ($showstandard){
if (count($wherestandardina) > 1)
$wherestandardin = implode(",",$wherestandardina);
elseif (count($wherestandardina) == 1)
$wherea[] = "standard = $wherestandardina[0]";}

if ($showprocessing){
if (count($whereprocessingina) > 1)
$whereprocessingin = implode(",",$whereprocessingina);
elseif (count($whereprocessingina) == 1)
$wherea[] = "processing = $whereprocessingina[0]";}
}
if ($showteam){
if (count($whereteamina) > 1)
$whereteamin = implode(",",$whereteamina);
elseif (count($whereteamina) == 1)
$wherea[] = "team = $whereteamina[0]";}

if ($showaudiocodec){
if (count($whereaudiocodecina) > 1)
$whereaudiocodecin = implode(",",$whereaudiocodecina);
elseif (count($whereaudiocodecina) == 1)
$wherea[] = "audiocodec = $whereaudiocodecina[0]";}

$wherebase = $wherea;

if (isset($searchstr))
{
	if (!$_GET['notnewword']){
	if (mysql_num_rows(sql_query("SELECT * FROM bannedkeywords WHERE instr('".$searchstr."', keywords )!=0")) > 0)
		stderr($lang_torrents['std_banned_keywords1'].$searchstr.$lang_torrents['std_banned_keywords2'],$lang_torrents['std_change_keywords']);
	else{
			insert_suggest($searchstr, $CURUSER['id']);
			$notnewword="";
		}
	}
	else{
		$notnewword="notnewword=1&";
	}
	$search_mode = 0 + $_GET["search_mode"];
	if (!in_array($search_mode,array(0,1,2)))
	{
		$search_mode = 0;
		write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking search_mode field in" . $_SERVER['SCRIPT_NAME'], 'mod');
	}

	$search_area = 0 + $_GET["search_area"];

/*	if ($search_area == 4) {
		$searchstr = (int)parse_imdb_id($searchstr);
	}*/
	$like_expression_array =array();
	unset($like_expression_array);

		function big2gbk($str){
		$count = mb_strlen($str, "UTF-8");
		$gbks = "";
		for($i = 0; $i < $count; $i++)
		{	
			global $big,$gbk;
			$char = mb_substr($str, $i, 1, "UTF-8");
			$gbks .= togbk($char);}
			return $gbks;
		}
		function togbk($char){
		$big= '皚藹礙愛翺襖奧壩罷擺敗頒辦絆幫綁鎊謗剝飽寶報鮑輩貝鋇狽備憊繃筆畢斃閉邊編貶變辯辮鼈癟瀕濱賓擯餅撥缽鉑駁蔔補參蠶殘慚慘燦蒼艙倉滄廁側冊測層詫攙摻蟬饞讒纏鏟産闡顫場嘗長償腸廠暢鈔車徹塵陳襯撐稱懲誠騁癡遲馳恥齒熾沖蟲寵疇躊籌綢醜櫥廚鋤雛礎儲觸處傳瘡闖創錘純綽辭詞賜聰蔥囪從叢湊竄錯達帶貸擔單鄲撣膽憚誕彈當擋黨蕩檔搗島禱導盜燈鄧敵滌遞締點墊電澱釣調叠諜疊釘頂錠訂東動棟凍鬥犢獨讀賭鍍鍛斷緞兌隊對噸頓鈍奪鵝額訛惡餓兒爾餌貳發罰閥琺礬釩煩範販飯訪紡飛廢費紛墳奮憤糞豐楓鋒風瘋馮縫諷鳳膚輻撫輔賦複負訃婦縛該鈣蓋幹趕稈贛岡剛鋼綱崗臯鎬擱鴿閣鉻個給龔宮鞏貢鈎溝構購夠蠱顧剮關觀館慣貫廣規矽歸龜閨軌詭櫃貴劊輥滾鍋國過駭韓漢閡鶴賀橫轟鴻紅後壺護滬戶嘩華畫劃話懷壞歡環還緩換喚瘓煥渙黃謊揮輝毀賄穢會燴彙諱誨繪葷渾夥獲貨禍擊機積饑譏雞績緝極輯級擠幾薊劑濟計記際繼紀夾莢頰賈鉀價駕殲監堅箋間艱緘繭檢堿鹼揀撿簡儉減薦檻鑒踐賤見鍵艦劍餞漸濺澗漿蔣槳獎講醬膠澆驕嬌攪鉸矯僥腳餃繳絞轎較稭階節莖驚經頸靜鏡徑痙競淨糾廄舊駒舉據鋸懼劇鵑絹傑潔結誡屆緊錦僅謹進晉燼盡勁荊覺決訣絕鈞軍駿開凱顆殼課墾懇摳庫褲誇塊儈寬礦曠況虧巋窺饋潰擴闊蠟臘萊來賴藍欄攔籃闌蘭瀾讕攬覽懶纜爛濫撈勞澇樂鐳壘類淚籬離裏鯉禮麗厲勵礫曆瀝隸倆聯蓮連鐮憐漣簾斂臉鏈戀煉練糧涼兩輛諒療遼鐐獵臨鄰鱗凜賃齡鈴淩靈嶺領餾劉龍聾嚨籠壟攏隴樓婁摟簍蘆盧顱廬爐擄鹵虜魯賂祿錄陸驢呂鋁侶屢縷慮濾綠巒攣孿灤亂掄輪倫侖淪綸論蘿羅邏鑼籮騾駱絡媽瑪碼螞馬罵嗎買麥賣邁脈瞞饅蠻滿謾貓錨鉚貿麽黴沒鎂門悶們錳夢謎彌覓綿緬廟滅憫閩鳴銘謬謀畝鈉納難撓腦惱鬧餒膩攆撚釀鳥聶齧鑷鎳檸獰甯擰濘鈕紐膿濃農瘧諾歐鷗毆嘔漚盤龐國愛賠噴鵬騙飄頻貧蘋憑評潑頗撲鋪樸譜臍齊騎豈啓氣棄訖牽扡釺鉛遷簽謙錢鉗潛淺譴塹槍嗆牆薔強搶鍬橋喬僑翹竅竊欽親輕氫傾頃請慶瓊窮趨區軀驅齲顴權勸卻鵲讓饒擾繞熱韌認紉榮絨軟銳閏潤灑薩鰓賽傘喪騷掃澀殺紗篩曬閃陝贍繕傷賞燒紹賒攝懾設紳審嬸腎滲聲繩勝聖師獅濕詩屍時蝕實識駛勢釋飾視試壽獸樞輸書贖屬術樹豎數帥雙誰稅順說碩爍絲飼聳慫頌訟誦擻蘇訴肅雖綏歲孫損筍縮瑣鎖獺撻擡攤貪癱灘壇譚談歎湯燙濤縧騰謄銻題體屜條貼鐵廳聽烴銅統頭圖塗團頹蛻脫鴕馱駝橢窪襪彎灣頑萬網韋違圍爲濰維葦偉僞緯謂衛溫聞紋穩問甕撾蝸渦窩嗚鎢烏誣無蕪吳塢霧務誤錫犧襲習銑戲細蝦轄峽俠狹廈鍁鮮纖鹹賢銜閑顯險現獻縣餡羨憲線廂鑲鄉詳響項蕭銷曉嘯蠍協挾攜脅諧寫瀉謝鋅釁興洶鏽繡虛噓須許緒續軒懸選癬絢學勳詢尋馴訓訊遜壓鴉鴨啞亞訝閹煙鹽嚴顔閻豔厭硯彥諺驗鴦楊揚瘍陽癢養樣瑤搖堯遙窯謠藥爺頁業葉醫銥頤遺儀彜蟻藝億憶義詣議誼譯異繹蔭陰銀飲櫻嬰鷹應纓瑩螢營熒蠅穎喲擁傭癰踴詠湧優憂郵鈾猶遊誘輿魚漁娛與嶼語籲禦獄譽預馭鴛淵轅園員圓緣遠願約躍鑰嶽粵悅閱雲鄖勻隕運蘊醞暈韻雜災載攢暫贊贓髒鑿棗竈責擇則澤賊贈紮劄軋鍘閘詐齋債氈盞斬輾嶄棧戰綻張漲帳賬脹趙蟄轍鍺這貞針偵診鎮陣掙睜猙幀鄭證織職執紙摯擲幟質鍾終種腫衆謅軸皺晝驟豬諸誅燭矚囑貯鑄築駐專磚轉賺樁莊裝妝壯狀錐贅墜綴諄濁茲資漬蹤綜總縱鄒詛組鑽緻鐘麼為隻兇準啟闆裡靂餘鍊洩';
		$gbk = '皑蔼碍爱翱袄奥坝罢摆败颁办绊帮绑镑谤剥饱宝报鲍辈贝钡狈备惫绷笔毕毙闭边编贬变辩辫鳖瘪濒滨宾摈饼拨钵铂驳卜补参蚕残惭惨灿苍舱仓沧厕侧册测层诧搀掺蝉馋谗缠铲产阐颤场尝长偿肠厂畅钞车彻尘陈衬撑称惩诚骋痴迟驰耻齿炽冲虫宠畴踌筹绸丑橱厨锄雏础储触处传疮闯创锤纯绰辞词赐聪葱囱从丛凑窜错达带贷担单郸掸胆惮诞弹当挡党荡档捣岛祷导盗灯邓敌涤递缔点垫电淀钓调迭谍叠钉顶锭订东动栋冻斗犊独读赌镀锻断缎兑队对吨顿钝夺鹅额讹恶饿儿尔饵贰发罚阀珐矾钒烦范贩饭访纺飞废费纷坟奋愤粪丰枫锋风疯冯缝讽凤肤辐抚辅赋复负讣妇缚该钙盖干赶秆赣冈刚钢纲岗皋镐搁鸽阁铬个给龚宫巩贡钩沟构购够蛊顾剐关观馆惯贯广规硅归龟闺轨诡柜贵刽辊滚锅国过骇韩汉阂鹤贺横轰鸿红后壶护沪户哗华画划话怀坏欢环还缓换唤痪焕涣黄谎挥辉毁贿秽会烩汇讳诲绘荤浑伙获货祸击机积饥讥鸡绩缉极辑级挤几蓟剂济计记际继纪夹荚颊贾钾价驾歼监坚笺间艰缄茧检碱硷拣捡简俭减荐槛鉴践贱见键舰剑饯渐溅涧浆蒋桨奖讲酱胶浇骄娇搅铰矫侥脚饺缴绞轿较秸阶节茎惊经颈静镜径痉竞净纠厩旧驹举据锯惧剧鹃绢杰洁结诫届紧锦仅谨进晋烬尽劲荆觉决诀绝钧军骏开凯颗壳课垦恳抠库裤夸块侩宽矿旷况亏岿窥馈溃扩阔蜡腊莱来赖蓝栏拦篮阑兰澜谰揽览懒缆烂滥捞劳涝乐镭垒类泪篱离里鲤礼丽厉励砾历沥隶俩联莲连镰怜涟帘敛脸链恋炼练粮凉两辆谅疗辽镣猎临邻鳞凛赁龄铃凌灵岭领馏刘龙聋咙笼垄拢陇楼娄搂篓芦卢颅庐炉掳卤虏鲁赂禄录陆驴吕铝侣屡缕虑滤绿峦挛孪滦乱抡轮伦仑沦纶论萝罗逻锣箩骡骆络妈玛码蚂马骂吗买麦卖迈脉瞒馒蛮满谩猫锚铆贸么霉没镁门闷们锰梦谜弥觅绵缅庙灭悯闽鸣铭谬谋亩钠纳难挠脑恼闹馁腻撵捻酿鸟聂啮镊镍柠狞宁拧泞钮纽脓浓农疟诺欧鸥殴呕沤盘庞国爱赔喷鹏骗飘频贫苹凭评泼颇扑铺朴谱脐齐骑岂启气弃讫牵扦钎铅迁签谦钱钳潜浅谴堑枪呛墙蔷强抢锹桥乔侨翘窍窃钦亲轻氢倾顷请庆琼穷趋区躯驱龋颧权劝却鹊让饶扰绕热韧认纫荣绒软锐闰润洒萨鳃赛伞丧骚扫涩杀纱筛晒闪陕赡缮伤赏烧绍赊摄慑设绅审婶肾渗声绳胜圣师狮湿诗尸时蚀实识驶势释饰视试寿兽枢输书赎属术树竖数帅双谁税顺说硕烁丝饲耸怂颂讼诵擞苏诉肃虽绥岁孙损笋缩琐锁獭挞抬摊贪瘫滩坛谭谈叹汤烫涛绦腾誊锑题体屉条贴铁厅听烃铜统头图涂团颓蜕脱鸵驮驼椭洼袜弯湾顽万网韦违围为潍维苇伟伪纬谓卫温闻纹稳问瓮挝蜗涡窝呜钨乌诬无芜吴坞雾务误锡牺袭习铣戏细虾辖峡侠狭厦锨鲜纤咸贤衔闲显险现献县馅羡宪线厢镶乡详响项萧销晓啸蝎协挟携胁谐写泻谢锌衅兴汹锈绣虚嘘须许绪续轩悬选癣绚学勋询寻驯训讯逊压鸦鸭哑亚讶阉烟盐严颜阎艳厌砚彦谚验鸯杨扬疡阳痒养样瑶摇尧遥窑谣药爷页业叶医铱颐遗仪彝蚁艺亿忆义诣议谊译异绎荫阴银饮樱婴鹰应缨莹萤营荧蝇颖哟拥佣痈踊咏涌优忧邮铀犹游诱舆鱼渔娱与屿语吁御狱誉预驭鸳渊辕园员圆缘远愿约跃钥岳粤悦阅云郧匀陨运蕴酝晕韵杂灾载攒暂赞赃脏凿枣灶责择则泽贼赠扎札轧铡闸诈斋债毡盏斩辗崭栈战绽张涨帐账胀赵蛰辙锗这贞针侦诊镇阵挣睁狰帧郑证织职执纸挚掷帜质钟终种肿众诌轴皱昼骤猪诸诛烛瞩嘱贮铸筑驻专砖转赚桩庄装妆壮状锥赘坠缀谆浊兹资渍踪综总纵邹诅组钻致钟么为只凶准启板里雳余链泄';
		$count = mb_strlen($big);
		for($i = 0; $i < $count; $i++)
			{
			if( mb_substr($big, $i, 1, "UTF-8") == $char)
				{	
					return mb_substr($gbk, $i, 1, "UTF-8");
					break;
				}
			}
		return $char;
		}
		function gbk2big($str){
		$count = mb_strlen($str, "UTF-8");
		$bigs = "";
		for($i = 0; $i < $count; $i++)
		{	
			global $big,$gbk;
			$char = mb_substr($str, $i, 1, "UTF-8");
			$bigs .= tobig($char);}
			return $bigs;
		}
		function tobig($char){
		$big= '皚藹礙愛翺襖奧壩罷擺敗頒辦絆幫綁鎊謗剝飽寶報鮑輩貝鋇狽備憊繃筆畢斃閉邊編貶變辯辮鼈癟瀕濱賓擯餅撥缽鉑駁蔔補參蠶殘慚慘燦蒼艙倉滄廁側冊測層詫攙摻蟬饞讒纏鏟産闡顫場嘗長償腸廠暢鈔車徹塵陳襯撐稱懲誠騁癡遲馳恥齒熾沖蟲寵疇躊籌綢醜櫥廚鋤雛礎儲觸處傳瘡闖創錘純綽辭詞賜聰蔥囪從叢湊竄錯達帶貸擔單鄲撣膽憚誕彈當擋黨蕩檔搗島禱導盜燈鄧敵滌遞締點墊電澱釣調叠諜疊釘頂錠訂東動棟凍鬥犢獨讀賭鍍鍛斷緞兌隊對噸頓鈍奪鵝額訛惡餓兒爾餌貳發罰閥琺礬釩煩範販飯訪紡飛廢費紛墳奮憤糞豐楓鋒風瘋馮縫諷鳳膚輻撫輔賦複負訃婦縛該鈣蓋幹趕稈贛岡剛鋼綱崗臯鎬擱鴿閣鉻個給龔宮鞏貢鈎溝構購夠蠱顧剮關觀館慣貫廣規矽歸龜閨軌詭櫃貴劊輥滾鍋國過駭韓漢閡鶴賀橫轟鴻紅後壺護滬戶嘩華畫劃話懷壞歡環還緩換喚瘓煥渙黃謊揮輝毀賄穢會燴彙諱誨繪葷渾夥獲貨禍擊機積饑譏雞績緝極輯級擠幾薊劑濟計記際繼紀夾莢頰賈鉀價駕殲監堅箋間艱緘繭檢堿鹼揀撿簡儉減薦檻鑒踐賤見鍵艦劍餞漸濺澗漿蔣槳獎講醬膠澆驕嬌攪鉸矯僥腳餃繳絞轎較稭階節莖驚經頸靜鏡徑痙競淨糾廄舊駒舉據鋸懼劇鵑絹傑潔結誡屆緊錦僅謹進晉燼盡勁荊覺決訣絕鈞軍駿開凱顆殼課墾懇摳庫褲誇塊儈寬礦曠況虧巋窺饋潰擴闊蠟臘萊來賴藍欄攔籃闌蘭瀾讕攬覽懶纜爛濫撈勞澇樂鐳壘類淚籬離裏鯉禮麗厲勵礫曆瀝隸倆聯蓮連鐮憐漣簾斂臉鏈戀煉練糧涼兩輛諒療遼鐐獵臨鄰鱗凜賃齡鈴淩靈嶺領餾劉龍聾嚨籠壟攏隴樓婁摟簍蘆盧顱廬爐擄鹵虜魯賂祿錄陸驢呂鋁侶屢縷慮濾綠巒攣孿灤亂掄輪倫侖淪綸論蘿羅邏鑼籮騾駱絡媽瑪碼螞馬罵嗎買麥賣邁脈瞞饅蠻滿謾貓錨鉚貿麽黴沒鎂門悶們錳夢謎彌覓綿緬廟滅憫閩鳴銘謬謀畝鈉納難撓腦惱鬧餒膩攆撚釀鳥聶齧鑷鎳檸獰甯擰濘鈕紐膿濃農瘧諾歐鷗毆嘔漚盤龐國愛賠噴鵬騙飄頻貧蘋憑評潑頗撲鋪樸譜臍齊騎豈啓氣棄訖牽扡釺鉛遷簽謙錢鉗潛淺譴塹槍嗆牆薔強搶鍬橋喬僑翹竅竊欽親輕氫傾頃請慶瓊窮趨區軀驅齲顴權勸卻鵲讓饒擾繞熱韌認紉榮絨軟銳閏潤灑薩鰓賽傘喪騷掃澀殺紗篩曬閃陝贍繕傷賞燒紹賒攝懾設紳審嬸腎滲聲繩勝聖師獅濕詩屍時蝕實識駛勢釋飾視試壽獸樞輸書贖屬術樹豎數帥雙誰稅順說碩爍絲飼聳慫頌訟誦擻蘇訴肅雖綏歲孫損筍縮瑣鎖獺撻擡攤貪癱灘壇譚談歎湯燙濤縧騰謄銻題體屜條貼鐵廳聽烴銅統頭圖塗團頹蛻脫鴕馱駝橢窪襪彎灣頑萬網韋違圍爲濰維葦偉僞緯謂衛溫聞紋穩問甕撾蝸渦窩嗚鎢烏誣無蕪吳塢霧務誤錫犧襲習銑戲細蝦轄峽俠狹廈鍁鮮纖鹹賢銜閑顯險現獻縣餡羨憲線廂鑲鄉詳響項蕭銷曉嘯蠍協挾攜脅諧寫瀉謝鋅釁興洶鏽繡虛噓須許緒續軒懸選癬絢學勳詢尋馴訓訊遜壓鴉鴨啞亞訝閹煙鹽嚴顔閻豔厭硯彥諺驗鴦楊揚瘍陽癢養樣瑤搖堯遙窯謠藥爺頁業葉醫銥頤遺儀彜蟻藝億憶義詣議誼譯異繹蔭陰銀飲櫻嬰鷹應纓瑩螢營熒蠅穎喲擁傭癰踴詠湧優憂郵鈾猶遊誘輿魚漁娛與嶼語籲禦獄譽預馭鴛淵轅園員圓緣遠願約躍鑰嶽粵悅閱雲鄖勻隕運蘊醞暈韻雜災載攢暫贊贓髒鑿棗竈責擇則澤賊贈紮劄軋鍘閘詐齋債氈盞斬輾嶄棧戰綻張漲帳賬脹趙蟄轍鍺這貞針偵診鎮陣掙睜猙幀鄭證織職執紙摯擲幟質鍾終種腫衆謅軸皺晝驟豬諸誅燭矚囑貯鑄築駐專磚轉賺樁莊裝妝壯狀錐贅墜綴諄濁茲資漬蹤綜總縱鄒詛組鑽緻鐘麼為隻兇準啟闆裡靂餘鍊洩';
		$gbk = '皑蔼碍爱翱袄奥坝罢摆败颁办绊帮绑镑谤剥饱宝报鲍辈贝钡狈备惫绷笔毕毙闭边编贬变辩辫鳖瘪濒滨宾摈饼拨钵铂驳卜补参蚕残惭惨灿苍舱仓沧厕侧册测层诧搀掺蝉馋谗缠铲产阐颤场尝长偿肠厂畅钞车彻尘陈衬撑称惩诚骋痴迟驰耻齿炽冲虫宠畴踌筹绸丑橱厨锄雏础储触处传疮闯创锤纯绰辞词赐聪葱囱从丛凑窜错达带贷担单郸掸胆惮诞弹当挡党荡档捣岛祷导盗灯邓敌涤递缔点垫电淀钓调迭谍叠钉顶锭订东动栋冻斗犊独读赌镀锻断缎兑队对吨顿钝夺鹅额讹恶饿儿尔饵贰发罚阀珐矾钒烦范贩饭访纺飞废费纷坟奋愤粪丰枫锋风疯冯缝讽凤肤辐抚辅赋复负讣妇缚该钙盖干赶秆赣冈刚钢纲岗皋镐搁鸽阁铬个给龚宫巩贡钩沟构购够蛊顾剐关观馆惯贯广规硅归龟闺轨诡柜贵刽辊滚锅国过骇韩汉阂鹤贺横轰鸿红后壶护沪户哗华画划话怀坏欢环还缓换唤痪焕涣黄谎挥辉毁贿秽会烩汇讳诲绘荤浑伙获货祸击机积饥讥鸡绩缉极辑级挤几蓟剂济计记际继纪夹荚颊贾钾价驾歼监坚笺间艰缄茧检碱硷拣捡简俭减荐槛鉴践贱见键舰剑饯渐溅涧浆蒋桨奖讲酱胶浇骄娇搅铰矫侥脚饺缴绞轿较秸阶节茎惊经颈静镜径痉竞净纠厩旧驹举据锯惧剧鹃绢杰洁结诫届紧锦仅谨进晋烬尽劲荆觉决诀绝钧军骏开凯颗壳课垦恳抠库裤夸块侩宽矿旷况亏岿窥馈溃扩阔蜡腊莱来赖蓝栏拦篮阑兰澜谰揽览懒缆烂滥捞劳涝乐镭垒类泪篱离里鲤礼丽厉励砾历沥隶俩联莲连镰怜涟帘敛脸链恋炼练粮凉两辆谅疗辽镣猎临邻鳞凛赁龄铃凌灵岭领馏刘龙聋咙笼垄拢陇楼娄搂篓芦卢颅庐炉掳卤虏鲁赂禄录陆驴吕铝侣屡缕虑滤绿峦挛孪滦乱抡轮伦仑沦纶论萝罗逻锣箩骡骆络妈玛码蚂马骂吗买麦卖迈脉瞒馒蛮满谩猫锚铆贸么霉没镁门闷们锰梦谜弥觅绵缅庙灭悯闽鸣铭谬谋亩钠纳难挠脑恼闹馁腻撵捻酿鸟聂啮镊镍柠狞宁拧泞钮纽脓浓农疟诺欧鸥殴呕沤盘庞国爱赔喷鹏骗飘频贫苹凭评泼颇扑铺朴谱脐齐骑岂启气弃讫牵扦钎铅迁签谦钱钳潜浅谴堑枪呛墙蔷强抢锹桥乔侨翘窍窃钦亲轻氢倾顷请庆琼穷趋区躯驱龋颧权劝却鹊让饶扰绕热韧认纫荣绒软锐闰润洒萨鳃赛伞丧骚扫涩杀纱筛晒闪陕赡缮伤赏烧绍赊摄慑设绅审婶肾渗声绳胜圣师狮湿诗尸时蚀实识驶势释饰视试寿兽枢输书赎属术树竖数帅双谁税顺说硕烁丝饲耸怂颂讼诵擞苏诉肃虽绥岁孙损笋缩琐锁獭挞抬摊贪瘫滩坛谭谈叹汤烫涛绦腾誊锑题体屉条贴铁厅听烃铜统头图涂团颓蜕脱鸵驮驼椭洼袜弯湾顽万网韦违围为潍维苇伟伪纬谓卫温闻纹稳问瓮挝蜗涡窝呜钨乌诬无芜吴坞雾务误锡牺袭习铣戏细虾辖峡侠狭厦锨鲜纤咸贤衔闲显险现献县馅羡宪线厢镶乡详响项萧销晓啸蝎协挟携胁谐写泻谢锌衅兴汹锈绣虚嘘须许绪续轩悬选癣绚学勋询寻驯训讯逊压鸦鸭哑亚讶阉烟盐严颜阎艳厌砚彦谚验鸯杨扬疡阳痒养样瑶摇尧遥窑谣药爷页业叶医铱颐遗仪彝蚁艺亿忆义诣议谊译异绎荫阴银饮樱婴鹰应缨莹萤营荧蝇颖哟拥佣痈踊咏涌优忧邮铀犹游诱舆鱼渔娱与屿语吁御狱誉预驭鸳渊辕园员圆缘远愿约跃钥岳粤悦阅云郧匀陨运蕴酝晕韵杂灾载攒暂赞赃脏凿枣灶责择则泽贼赠扎札轧铡闸诈斋债毡盏斩辗崭栈战绽张涨帐账胀赵蛰辙锗这贞针侦诊镇阵挣睁狰帧郑证织职执纸挚掷帜质钟终种肿众诌轴皱昼骤猪诸诛烛瞩嘱贮铸筑驻专砖转赚桩庄装妆壮状锥赘坠缀谆浊兹资渍踪综总纵邹诅组钻致钟么为只凶准启板里雳余链泄';
$count = mb_strlen($gbk);
for($i = 0; $i < $count; $i++)
	{
	if( mb_substr($gbk, $i, 1, "UTF-8") == $char)
		{	
			return mb_substr($big, $i, 1, "UTF-8");
			break;
		}
	}
return $char;
}

	switch ($search_mode)
	{
		case 0:	// AND, OR
		case 1:
			{
				if ($search_area != 4)$searchstr = str_replace(".", " ", $searchstr);
				$searchstr_exploded = explode(" ", $searchstr);
				$searchstr_exploded_count= 0;
				foreach ($searchstr_exploded as $searchstr_element)
				{
					$searchstr_element = trim($searchstr_element);	// furthur trim to ensure that multi space seperated words still work			
					if(!$searchstr_element)
					continue;
					if($searchstr_exploded_count >= 10)	// 每次最多查询10个关键字
					break;
					$searchstr_exploded_count++;
					if ($search_area == 4) {
					$searchstr_element = (int)parse_imdb_id($searchstr_element);
					$like_expression_array[] = " = " . ($searchstr_element?$searchstr_element:"'-1'");
					}
					else
					$like_expression_array[] = ( substr($searchstr_element,0,1)=='!'?" NOT":"")." LIKE '%" .( substr($searchstr_element,0,1)=='!'?substr($searchstr_element ,1,strlen($searchstr_element)):$searchstr_element). "%'";

				}
				if(!$like_expression_array)$like_expression_array[] = " LIKE '%'";
				break;
			}
		case 2	:	// exact
		{	if ($search_area == 4) 
				$like_expression_array[] = " = " . (int)parse_imdb_id($searchstr);
			else 
				$like_expression_array[] = " LIKE '%" . $searchstr. "%'";
			break;
		}
		case 3 :	// parsed
		{
		$like_expression_array[] = $searchstr;
		break;
		}
	}
	$ANDOR = ($search_mode == 0 ? " AND " : " OR ");	// only affects mode 0 and mode 1

	switch ($search_area)
	{
		case 0   :	// torrent name
		{
			foreach ($like_expression_array as &$like_expression_array_element)
			$like_expression_array_element = "(torrents_c.name" . gbk2big($like_expression_array_element).(gbk2big($like_expression_array_element)==big2gbk($like_expression_array_element)?"":" ".( strstr($like_expression_array_element,"NOT LIKE")?"AND":"OR")." torrents_c.name" . big2gbk($like_expression_array_element) )." )";
			
			//我们没有副标题
//			$like_expression_array_element = "(torrents.name" . gbk2big($like_expression_array_element)." OR torrents.small_descr". gbk2big($like_expression_array_element)." OR torrents.name" . big2gbk($like_expression_array_element)." OR torrents.small_descr". big2gbk($like_expression_array_element)." )";
			$wherea[] = implode($ANDOR, $like_expression_array);
			break;
		}
		case 1	:	// torrent description
		{
			foreach ($like_expression_array as &$like_expression_array_element)
			$like_expression_array_element = "( torrents_c.descr". big2gbk($like_expression_array_element).(gbk2big($like_expression_array_element)==big2gbk($like_expression_array_element)?"":( substr($searchstr_element,0,1)=='!'?" AND ":" OR ")."torrents_c.descr". gbk2big($like_expression_array_element) ).")";
			$wherea[] = implode($ANDOR,  $like_expression_array);
			break;
		}
		/*case 2	:	// torrent small description
		{
			foreach ($like_expression_array as &$like_expression_array_element)
			$like_expression_array_element =  "torrents.small_descr". $like_expression_array_element;
			$wherea[] =  implode($ANDOR, $like_expression_array);
			break;
		}*/
		case 3	:	// torrent uploader
		{
			foreach ($like_expression_array as &$like_expression_array_element)
			$like_expression_array_element =  "users.username". $like_expression_array_element;

			if(!isset($CURUSER))	// not registered user, only show not anonymous torrents
			{
				$wherea[] =  "(".implode($ANDOR, $like_expression_array).") AND torrents_c.anonymous = 'no' ";
			}
			else
			{
				if(get_user_class() > $torrentmanage_class)	// moderator or above, show all
				{
					$wherea[] = implode($ANDOR, $like_expression_array);
				}
				else // only show normal torrents and anonymous torrents from hiself
				{
					$wherea[] =   "( (" .implode($ANDOR, $like_expression_array)." ) AND torrents_c.anonymous = 'no' ) OR ( ( ". implode($ANDOR, $like_expression_array)." ) AND torrents_c.anonymous = 'yes' AND users.id=" . $CURUSER["id"] . " )";
				}
			}
			break;
		}
		case 4  :  //imdb url
		{
			foreach ($like_expression_array as &$like_expression_array_element)
			$like_expression_array_element = "torrents_c.url". $like_expression_array_element;
			$wherea[] = implode($ANDOR,  $like_expression_array);
			break;
		}
		default :	// unkonwn
		{
			$search_area = 0;
			$wherea[] =  "torrents_c.name LIKE '%" . $searchstr . "%'";
			write_log("User " . $CURUSER["username"] . "," . $CURUSER["ip"] . " is hacking search_area field in" . $_SERVER['SCRIPT_NAME'], 'mod');
			break;
		}
	}
	$addparam .= "search_area=" . $search_area . "&";
	$addparam .= "search=" . rawurlencode($searchstr) . "&".$notnewword;
	$addparam .= "search_mode=".$search_mode."&";
}

//$where = implode(" AND ", $wherea);
if($wherea)$where ="( ". implode(" ) AND ( ", $wherea)." )";

if ($wherecatin)
$where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";
if ($showsubcat){
if ($wheresourcein)
$where .= ($where ? " AND " : "") . "source IN(" . $wheresourcein . ")";
if ($wheremediumin)
$where .= ($where ? " AND " : "") . "medium IN(" . $wheremediumin . ")";
if ($wherecodecin)
$where .= ($where ? " AND " : "") . "codec IN(" . $wherecodecin . ")";
if ($wherestandardin)
$where .= ($where ? " AND " : "") . "standard IN(" . $wherestandardin . ")";
if ($whereprocessingin)
$where .= ($where ? " AND " : "") . "processing IN(" . $whereprocessingin . ")";
if ($whereteamin)
$where .= ($where ? " AND " : "") . "team IN(" . $whereteamin . ")";
if ($whereaudiocodecin)
$where .= ($where ? " AND " : "") . "audiocodec IN(" . $whereaudiocodecin . ")";
}
if (get_user_class() >= UC_MODERATOR && $_GET['recycle'])
{
	$where .= ($where ? " AND " : "") . "pulling_out = '1'";
}
else
{
	$where .= ($where ? " AND " : "") . "pulling_out = '0'";
}
if ($allsec == 1 || $enablespecial != 'yes')//取出非试种数
{
	if ($where != "")
		$where = "WHERE $where ";//AND torrents.category <413commented bt pirateutopia
	//else $where = "WHERE torrents.category <413";  
	$sql = "SELECT COUNT(*) FROM torrents_c " . ($search_area == 3 || $column == "owner" ? "LEFT JOIN users ON torrents_c.owner = users.id " : "") . $where;
}
else
{
	if ($where != "")
		$where = "WHERE $where AND categories.mode = '$sectiontype'";
	else $where = "WHERE categories.mode = '$sectiontype'";
	$sql = "SELECT COUNT(*), categories.mode FROM torrents_c LEFT JOIN categories ON category = categories.id " . ($search_area == 3 || $column == "owner" ? "LEFT JOIN users ON torrents_c.owner = users.id " : "") . $where." GROUP BY categories.mode";//取出非试种数
}
$res = sql_query($sql) or die(mysql_error());
$count = 0;
while($row = mysql_fetch_array($res))
	$count += $row[0];

if ($CURUSER["torrentsperpage"])
$torrentsperpage = (int)$CURUSER["torrentsperpage"];
elseif ($torrentsperpage_main)
	$torrentsperpage = $torrentsperpage_main;
else $torrentsperpage = 50;
if ($count)
{
	if ($addparam != "")
	{
		if ($pagerlink != "")
		{
			if ($addparam{strlen($addparam)-1} != ";")
			{ // & = &amp;
				$addparam = $addparam . "&" . $pagerlink;
			}
			else
			{
				$addparam = $addparam . $pagerlink;
			}
		}
	}
	else
	{
		//stderr("in else","");
		$addparam = $pagerlink;
	}
	//stderr("addparam",$addparam);
	//echo $addparam;
	
	if ($_GET['recycle'])
	{
		$addparam .= 'recycle=1&';
	}

	list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "?" . $addparam);
if ($allsec == 1 || $enablespecial != 'yes'){
	$query = "SELECT torrents_c.id, torrents_c.sp_state, torrents_c.promotion_time_type, torrents_c.promotion_until, torrents_c.banned, torrents_c.picktype, torrents_c.pos_state, torrents_c.category, torrents_c.source, torrents_c.medium, torrents_c.codec, torrents_c.standard, torrents_c.processing, torrents_c.team, torrents_c.audiocodec, torrents_c.leechers, torrents_c.seeders, torrents_c.name, torrents_c.small_descr, torrents_c.times_completed, torrents_c.size, torrents_c.added,torrents_c.sp_time, torrents_c.comments,torrents_c.anonymous,torrents_c.owner,torrents_c.url,torrents_c.cache_stamp, torrents_c.needkeepseed, torrents_c.pos_state_until, torrents_c.imdb_rating FROM torrents_c ".($search_area == 3 || $column == "owner" ? "LEFT JOIN users ON torrents_c.owner = users.id " : "")." $where $orderby $limit";//取出非试 AND torrents.category <413种
}
else{
	$query = "SELECT torrents_c.id, torrents_c.sp_state, torrents_c.promotion_time_type, torrents_c.promotion_until, torrents_c.banned, torrents_c.picktype, torrents_c.pos_state, torrents_c.category, torrents_c.source, torrents_c.medium, torrents_c.codec, torrents_c.standard, torrents_c.processing, torrents_c.team, torrents_c.audiocodec, torrents_c.leechers, torrents_c.seeders, torrents_c.name, torrents_c.small_descr, torrents_c.times_completed, torrents_c.size, torrents_c.added,torrents_c.sp_time, torrents_c.comments,torrents_c.anonymous,torrents_c.owner,torrents_c.url,torrents_c.cache_stamp, torrents_c.pos_state_until, torrents_c.imdb_rating FROM torrents_c ".($search_area == 3 || $column == "owner" ? "LEFT JOIN users ON torrents_c.owner = users.id " : "")." LEFT JOIN categories ON torrents_c.category=categories.id $where $orderby $limit";
}//取出非试种 AND torrents.category <413

	$res = sql_query($query) or die(mysql_error());
}
else
	unset($res);
	
if (isset($searchstr))
	stdhead($lang_torrents['head_search_results_for'].$searchstr_ori);
elseif ($sectiontype == $browsecatmode)
	stdhead($lang_torrents['head_torrents']);
else stdhead($lang_torrents['head_music']);
print("<table width=\"97%\" class=\"main\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"embedded\">");
if ($allsec != 1 || $enablespecial != 'yes'){ //do not print searchbox if showing bookmarked torrents from all sections;
?>

<!--badly needed-->
<IFRAME frameBorder=0 id=hiddenframe src="about:blank" style="HEIGHT: 0; LEFT: 0; POSITION: absolute; TOP: 0; WIDTH: 0">
</IFRAME>
<table border="1" class="searchbox" cellspacing="0" cellpadding="5" width="100%">
 </table>
 <br />
 <?php 
$closesearchbox=(strpos($CURUSER['notifs'], "[closesearchbox]") !== false ? "none" : "");
if($closesearchbox=="")
	$picclosesearchbox = "minus";
else
	$picclosesearchbox = "plus";
?>
<!--badly needed-->
<?php
if (get_user_class() >= UC_MODERATOR && $_GET['recycle'])
{
echo '<center><span style="font-size: 20px"><strong>回收站模式</strong></span><br/><small>您在这里查看到的种子为回收站中内容，如有需要可以恢复，再次删除将彻底删除</small></center>';
}?>
<form method="get" name="searchbox" action="?">
	<table border="1" class="searchbox" cellspacing="0" cellpadding="5" width="100%">
		<tbody>
		<tr>
		<td class="colhead" align="center" colspan="2"><a href="javascript: klappe_news('searchboxmain')"><img class=<?php echo($picclosesearchbox);?> src="pic/trans.gif" id="picsearchboxmain" alt="Show/Hide" /><?php echo $lang_torrents['text_search_box'] ?></a></td>
		</tr></tbody>
		<tbody id="ksearchboxmain" style="display:<?php echo($closesearchbox);?>">
		<tr>
			<td class="rowfollow" align="left">
				<table>	
					<?php
						function printcat($name, $listarray, $cbname, $wherelistina, $btname, $showimg = false)
						{
							global $catpadding,$catsperrow,$lang_torrents,$CURUSER,$CURLANGDIR,$catimgurl;

							print("<tr><td class=\"embedded\" colspan=\"".$catsperrow."\" align=\"left\"><b>".$name."</b></td></tr><tr>");
							$i = 0;
							foreach($listarray as $list){
								if ($i && $i % $catsperrow == 0){
									print("</tr><tr>");
								}
								print("<td align=\"left\" class=\"bottom\" style=\"padding-bottom: 4px; padding-left: ".$catpadding."px;\"><input type=\"checkbox\" id=\"".$cbname.$list[id]."\" name=\"".$cbname.$list[id]."\"" . (in_array($list[id],$wherelistina) ? " checked=\"checked\"" : "") . " value=\"1\" />".($showimg ? return_category_image($list[id], "?") : "<a title=\"" .$list[name] . "\" href=\"?".$cbname."=".$list[id]."\">".$list[name]."</a>")."</td>\n");
								$i++;
							}
							$checker = "<input name=\"".$btname."\" value='" .  $lang_torrents['input_check_all'] . "' class=\"btn medium\" type=\"button\" onclick=\"javascript:SetChecked('".$cbname."','".$btname."','". $lang_torrents['input_check_all'] ."','" . $lang_torrents['input_uncheck_all'] . "',-1,10)\" />";
							print("<td colspan=\"2\" class=\"bottom\" align=\"left\" style=\"padding-left: 15px\">".$checker."</td>\n");
							print("</tr>");
						}
					printcat($lang_torrents['text_category'],$cats,"cat",$wherecatina,"cat_check",true);
					if ($showsubcat){
						if ($showsource)
							printcat($lang_torrents['text_source'], $sources, "source", $wheresourceina, "source_check");
						if ($showmedium)
							printcat($lang_torrents['text_medium'], $media, "medium", $wheremediumina, "medium_check");
						if ($showcodec)
							printcat($lang_torrents['text_codec'], $codecs, "codec", $wherecodecina, "codec_check");
						if ($showaudiocodec)
							printcat($lang_torrents['text_audio_codec'], $audiocodecs, "audiocodec", $whereaudiocodecina, "audiocodec_check");
						if ($showstandard)
							printcat($lang_torrents['text_standard'], $standards, "standard", $wherestandardina, "standard_check");
						if ($showprocessing)
							printcat($lang_torrents['text_processing'], $processings, "processing", $whereprocessingina, "processing_check");
						if ($showteam)
							printcat($lang_torrents['text_team'], $teams, "team", $whereteamina, "team_check");
					}
					?>
				</table>
			</td>
			
			<td class="rowfollow" valign="middle">
				<table>
					<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<font class="medium"><?php echo $lang_torrents['text_show_dead_active'] ?></font>
						</td>
				 	</tr>				
					<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<select class="med" name="incldead" style="width: 100px;">
								<option value="0"<?php print($include_dead == 0 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_including_dead'] ?></option>
								<option value="1"<?php print($include_dead == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_active'] ?> </option>
								<option value="2"<?php print($include_dead == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_dead'] ?></option>
							</select>
						</td>
				 	</tr>
				 	<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<br />
						</td>
				 	</tr>
					<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<font class="medium"><?php echo $lang_torrents['text_show_special_torrents'] ?></font>
						</td>
				 	</tr>
				 	<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<select class="med" name="spstate" style="width: 100px;">
								<option value="0"><?php echo $lang_torrents['select_all'] ?></option>
								<option value="8"><?php echo $lang_torrents['all_special'] ?></option>
<?php echo promotion_selection($special_state, 8)?>
							</select>
						</td>
					</tr>
				 	<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<br />
						</td>
					</tr>
					<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<font class="medium"><?php echo $lang_torrents['text_show_recommed'] ?></font>
						</td>
				 	</tr>				
					<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<select class="med" name="picktype" style="width: 100px;">
								<option value="0"><?php echo $lang_torrents['select_all'] ?></option>
								<option value="1"<?php print($picktype == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['normal'] ?> </option>
								<option value="2"<?php print($picktype == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['hot'] ?></option>
								<option value="3"<?php print($picktype == 3 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['classic'] ?> </option>
								<option value="4"<?php print($picktype == 4 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['recommended'] ?></option>
								<option value="5"<?php print($picktype == 5 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['0day'] ?> </option>
								<option value="6"<?php print($picktype == 6 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['IMDB'] ?> </option>
							</select>
						</td>
				 	</tr>
				 	<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<br />
						</td>
					</tr>
					<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<font class="medium"><?php echo $lang_torrents['text_show_bookmarked'] ?></font>
						</td>
				 	</tr>
				 	<tr>
						<td class="bottom" style="padding: 1px;padding-left: 10px">
							<select class="med" name="inclbookmarked" style="width: 100px;">
								<option value="0"><?php echo $lang_torrents['select_all'] ?></option>
								<option value="1"<?php print($inclbookmarked == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_bookmarked'] ?></option>
								<option value="2"<?php print($inclbookmarked == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_bookmarked_exclude'] ?></option>
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		</tbody>
		<tbody>
		<tr>
			<td class="rowfollow" align="center">
				<table>
					<tr>
						<td class="embedded">
							<?php echo $lang_torrents['text_search'] ?>&nbsp;&nbsp;
						</td>
						<td class="embedded">
							<table>
								<tr>
									<td class="embedded">
										<input id="searchinput" name="search" type="text" value="<?php echo  $searchstr_ori ?>" autocomplete="off" style="width: 200px" ondblclick="suggest(event.keyCode,this.value);" onkeyup="suggest(event.keyCode,this.value);" onkeypress="return noenter(event.keyCode);"/>
										<script src="js/suggest.js" type="text/javascript"></script>
										<div id="suggcontainer" style="text-align: left; width:100px;  display: none;">
											<div id="suggestions" style="width:204px; border: 1px solid rgb(119, 119, 119); cursor: default; position: absolute; color: rgb(0,0,0); background-color: rgb(255, 255, 255);"></div>
										</div>
									</td>
								</tr>
							</table>
						</td>
						<td class="embedded">
							<?php echo "&nbsp;" . $lang_torrents['text_in'] ?>

							<select name="search_area">
								<option value="0"><?php echo $lang_torrents['select_title'] ?></option>
								<option value="1"<?php print($_GET["search_area"] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_description'] ?></option>
								<?php
								/*if ($smalldescription_main == 'yes'){
								?>
								<option value="2"<?php print($_GET["search_area"] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_small_description'] ?></option>
								<?php
								}*/
								?>
								<option value="3"<?php print($_GET["search_area"] == 3 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_uploader'] ?></option>
								<option value="4"<?php print($_GET["search_area"] == 4 ? " selected=\"selected\"" : ""); ?>><?php echo $lang_torrents['select_imdb_url'] ?></option>
							</select>

							<?php echo $lang_torrents['text_with'] ?>

							<select name="search_mode" style="width: 60px;">
								<option value="0"><?php echo $lang_torrents['select_and'] ?></option>
								<option value="1"<?php echo $_GET["search_mode"] == 1 ? " selected=\"selected\"" : "" ?>><?php echo $lang_torrents['select_or'] ?></option>
								<option value="2"<?php echo $_GET["search_mode"] == 2 ? " selected=\"selected\"" : "" ?>><?php echo $lang_torrents['select_exact'] ?></option>
							</select>
							
							<?php echo $lang_torrents['text_mode'] ?>
<?php
if (get_user_class() >= UC_MODERATOR && $_GET['recycle'])
{
	print("<input type=\"hidden\" name=\"recycle\" value=\"1\">");
}
?>
						</td>
					</tr>
<?php
$Cache->new_page('hot_search', 3670, true);
if (!$Cache->get_page()){
	$secs = 3*24*60*60;
	$dt = sqlesc(date("Y-m-d H:i:s",(TIMENOW - $secs)));
	$dt2 = sqlesc(date("Y-m-d H:i:s",(TIMENOW - $secs*2)));
	sql_query("DELETE FROM suggest WHERE adddate <" . $dt2) or sqlerr();
	$searchres = sql_query("SELECT keywords, COUNT(DISTINCT userid) as count FROM suggest WHERE adddate >" . $dt . " GROUP BY keywords ORDER BY count DESC LIMIT 15") or sqlerr();
	$hotcount = 0;
	$hotsearch = "";
	while ($searchrow = mysql_fetch_assoc($searchres))
	{
		$hotsearch .= "<a href=\"".htmlspecialchars("?search=" . rawurlencode($searchrow["keywords"]) . "&notnewword=1")."\"><u>" . $searchrow["keywords"] . "</u></a>&nbsp;&nbsp;";
		$hotcount += mb_strlen($searchrow["keywords"],"UTF-8");
		if ($hotcount > 60)
			break;
	}
	$Cache->add_whole_row();
	if ($hotsearch)
	print("<tr><td class=\"embedded\" colspan=\"3\">&nbsp;&nbsp;".$hotsearch."</td></tr>");
	$Cache->end_whole_row();
	$Cache->cache_page();
}
echo $Cache->next_row();
?>
				</table>
			</td>
			<td class="rowfollow" align="center">
				<input type="submit" class="btn" value="<?php echo $lang_torrents['submit_go'] ?>" />
			</td>
		</tr>
		</tbody>
	</table>
	</form>
<?php
}
	if ($Advertisement->enable_ad()){
			$belowsearchboxad = $Advertisement->get_ad('belowsearchbox');
			echo "<div align=\"center\" style=\"margin-top: 10px\" id=\"ad_belowsearchbox\">".$belowsearchboxad[0]."</div>";
	}
if($inclbookmarked == 1)
{
	print("<h1 align=\"center\">" . get_username($CURUSER['id']) . $lang_torrents['text_s_bookmarked_torrent'] . "</h1>");
}
elseif($inclbookmarked == 2)
{
	print("<h1 align=\"center\">" . get_username($CURUSER['id']) . $lang_torrents['text_s_not_bookmarked_torrent'] . "</h1>");
}

if ($count) {
	print($pagertop);
	if ($sectiontype == $browsecatmode)
		torrenttable($res, "torrents");
	elseif ($sectiontype == $specialcatmode) 
		torrenttable($res, "music");
	else torrenttable($res, "bookmarks");
	print($pagerbottom);
}
else {
	if (isset($searchstr)) {
		print("<br />");
		stdmsg($lang_torrents['std_search_results_for'] . $searchstr_ori . "\"",$lang_torrents['std_try_again']);
	}
	else {
		stdmsg($lang_torrents['std_nothing_found'],$lang_torrents['std_no_active_torrents']);
	}
}
if ($CURUSER){
	if ($sectiontype == $browsecatmode)
		$USERUPDATESET[] = "last_browse = ".TIMENOW;
	else	$USERUPDATESET[] = "last_music = ".TIMENOW;
}
print("</td></tr></table>");
stdfoot();
?>

