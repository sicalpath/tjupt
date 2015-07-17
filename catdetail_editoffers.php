<?php
/*
此文件在editonload.js中调用。当编辑种子时，offers.php会弹出该候选的详细信息页面，即调用此文件。
*/
require "include/bittorrent.php";
dbconn(true);

function gbkToUtf8 ($value) {
   return iconv("gbk","UTF-8", $value);
}

function tr_select($x,$y,$z,$star=""){
	$listarray = sql_query("SELECT * FROM ".$y);
	$hint = "请选择";
	$show = "<tr class=\"rowfollow\" ><td class=\"no_border\" ><strong>$x</strong>";
	
	if($z==0){
		$show .= "<select name=$y ><option value=\"0\">".$hint."</option>";
		while($row = mysql_fetch_array($listarray)){
			$show .= "<option value=\"$row[id]\">".$row[name]."</option>";
		}
	}
	else{
		$show .="<select name=$y >";
		while($row = mysql_fetch_array($listarray)){
			if($row[id]==$z)
				$show .= "<option value=".$row[id]." selected=\"selected\">".$row[name]."</option>";
			else
				$show .= "<option value=".$row[id].">".$row[name]."</option>";
		}
	}

	$show .="</select>$star</td></tr><div class=\"clear1\"></div>";
	
	print($show);
}

function tr_text($x,$y,$z="",$star="") {	
	$text = "<tr class=\"rowfollow\" ><td class=\"no_border\" valign=\"top\" align=\"right\"><label><strong>".$x."</strong><input type=\"text\" size=\"50\" maxlength=\"200\" name=\"" .$y. "\" value=\"".$z."\"></label>".$star."</td></tr><div class=\"clear1\"></div>";
	print($text);
}

function tr_textcheckbox($x,$y,$listname,$z="",$star="") {
	$listarray = searchbox_item_list($listname);
	$num = count($listarray);	
	$show = "<tr class=\"rowfollow\" ><td class=\"no_border\" valign=\"top\" align=\"right\"><label><strong>".$x."</strong><input type=\"text\" size=\"50\" maxlength=\"100\" id=\"".$y."\" name=\"" .$y. "\"  value=\"".$z."\"></label>$star<br>";
	foreach ($listarray as $row){
		$id = $y.$row['id'];
		$show .= "<label><INPUT id=".$id ." type=checkbox value=".$row['name']." name=".$row['name']. " onClick=getcheckboxvalue('".$y."',".$num.")>".$row['name']."</label>";
	}
	$show .= "</td></tr><div class=\"clear1\"></div>";
	
	print($show);

}

function tr_textradio($x,$y,$listname,$z="",$star="") {
	$listarray = searchbox_item_list($listname);
	$num = count($listarray);	
	$show = "<tr class=\"rowfollow\" ><td class=\"no_border\" valign=\"top\" align=\"right\"><label><strong>".$x."</strong><input type=\"text\" size=\"50\" maxlength=\"100\" id=\"".$y."\" name=\"" .$y. "\"  value=".$z."></label>$star<br>";
	$radioname = $y."radio";

	foreach ($listarray as $row)
	{
		if (substr_count($row['name'], '<text>'))
			$show .= str_replace('<text>', '', $row['name']);
		elseif ($row['name'] == '<linebreak>')
			$show .= "<br />";
		else
		{
			$id = $y.$row['id'];	
			$show .= "<label><INPUT id=".$id ." type=radio value=".$row['name']." name=".$radioname. " onClick=getradiovalue('".$y."','".$id."')>".$row['name']."</label>";
		}
	}

	//$otherid = $y."other";
	//$show .= "<INPUT id=".$otherid ." type=radio value=\"其他\" name=".$radioname. " onClick=getradiovalue('".$y."','".$otherid."')><LABEL for=".$row['id'].">"."其他(如果选择此选项，请在上面的文本框中填写具体的".$info."信息)</LABEL>";
	$show .= "</td></tr><div class=\"clear1\"></div>";
	
	print($show);

}
$star = "<b><font color=red>*</font></b>";
$offerid = 0 + $_GET["offerid"];

$res = sql_query("SELECT * FROM offersinfo WHERE offerid = ".$offerid);
$row = mysql_fetch_array($res);

$catid = $row['category'];
$cname = $row['cname'];
$ename = $row['ename'];
$issuedate = $row['issuedate'];
$subsinfo = $row['subsinfo'];
$language = $row['language'];
$format = $row['format'];
$specificcat = $row['specificcat'];
$district = $row['district'];
$version = $row['version'];
$resolution = $row['resolution'];

if($catid == 401){
	$imdbnum = $row['imdbnum'];
	
	tr_text("中文名","cname",$cname,$star);
	tr_text("英文名","ename",$ename,$star."英文名为0day名，如：The.Kings.Speech.2010.BDRip.XviD-AMIABLE");
	//tr_text("Imdb编号","imdbnum",$imdbnum,"如：tt0120815");
	tr_text("发行时间","issuedate",$issuedate,$star);
	tr_textcheckbox("电影语言","language","langtvseries",$language,$star);
	
	//tr_textcheckbox("电影类别","specificcat","catmovie",$specificcat,$star);
	tr_textradio("电影文件格式","format","formatmovie",$format,$star);
	tr_select("字幕情况","subsinfo",$subsinfo,$star);
	tr_textcheckbox("制作国家/地区","district","districtmovie",$district,$star);	
}
elseif($catid == "402"){
	$tvalias = $row['tvalias'];
	$tvseasoninfo = $row['tvseasoninfo'];
	
	tr_text("中文名","cname",$cname,$star);
	tr_text("英文名","ename",$ename);
	tr_text("别名","tvalias",$tvalias);
	tr_text("剧集季度信息","tvseasoninfo",$tvseasoninfo);
	
	tr_textcheckbox("剧集类型","specificcat","catseries",$specificcat,$star);
	tr_textcheckbox("剧集文件格式","format","formattvseries",$format);
	tr_select("字幕情况","subsinfo",$subsinfo);
	tr_textcheckbox("剧集语言","language","langtvseries",$language,$star);	
}
elseif($catid == "403"){
	$tvshowscontent = $row['tvshowscontent'];
	$tvshowsguest = $row['tvshowsguest'];
	$tvshowsremarks = $row['tvshowsremarks'];
	
	tr_text("中文名","cname",$cname);
	tr_text("英文名","ename",$ename);
	tr_text("发行时间","issuedate",$issuedate);
	tr_text("节目内容","tvshowscontent",$tvshowscontent);
	tr_text("节目嘉宾","tvshowsguest",$tvshowsguest);
	
	tr_textcheckbox("国家/地区","district","districttvshows",$district);
	tr_select("字幕情况","subsinfo",$subsinfo);
	tr_textcheckbox("节目语言","language","langtvshows",$language);
	tr_textradio("节目格式","format","formattvshows",$format,"格式");
	
	tr_text("备注","tvshowsremarks",$tvshowsremarks);
}
elseif($catid == "404"){
	tr_text("中文名","cname",$cname);
	tr_text("英文名","ename",$ename);
	tr_text("发行时间","issuedate",$issuedate);
	tr_text("版本","version",$version);
	
	tr_textcheckbox("资料类别","specificcat","catdocum",$specificcat);	
	tr_textcheckbox("资料文件格式","format","formatdocum",$format);	
}
elseif($catid == "405"){
	$animenum = $row['animenum'];
	$substeam = $row['substeam'];
	
	tr_text("中文名","cname",$cname);
	tr_text("英文名","ename",$ename,$star);
	tr_text("发行时间","issuedate",$issuedate);
	tr_text("动漫集数","animenum",$animenum);
	tr_text("字幕组/漫画作者/专辑艺术家","substeam",$substeam,$star);
	
	tr_textcheckbox("动漫类别","specificcat","catanime",$specificcat,$star);	
	tr_textcheckbox("动漫文件格式","format","formatanime",$format,$star);	
	tr_textcheckbox("画面分辨率","resolution","resolutionanime",$resolution);	
	tr_textcheckbox("动漫国别","district","districtanime",$district);	
}
elseif($catid == "406"){
	$hqname = $row['hqname'];
	$artist = $row['artist'];
	$hqtone = $row['hqtone'];
	
	tr_text("专辑名","hqname",$hqname,$star);
	tr_text("艺术家","artist",$artist,$star);
	tr_text("发行时间","issuedate",$issuedate);
	
	tr_textcheckbox("音乐类别","specificcat","cathq",$specificcat);	
	tr_textcheckbox("音乐文件格式","format","formathqaudio",$format,$star);	
	tr_textcheckbox("音乐语言","language","langhq",$langguage);	
	tr_textcheckbox("音质/码率","hqtone","hqtone",$hqtone);	
}
elseif($catid == "407"){
	tr_text("比赛日期","issuedate",$issuedate);
	tr_text("赛事类别","cname",$cname,$star);
	tr_text("对阵双方","ename",$ename);
	tr_text("电视台及语言","language",$language);
	
	tr_textcheckbox("体育类型","specificcat","catsports",$specificcat,$star);
	tr_textcheckbox("体育节目格式","format","formatsports",$format,$star);
	tr_textcheckbox("录像分辨率","resolution","resolutionsports",$resolution);
}
elseif($catid == "408"){
	tr_text("中文名","cname",$cname);
	tr_text("英文名","ename",$ename);
	tr_text("发行时间","issuedate",$issuedate);
	tr_text("版本","version",$version);
	
	tr_textcheckbox("软件类型","specificcat","catsoftware",$specificcat);
	tr_textcheckbox("软件文件格式","format","formatsoftware",$format);
	tr_textcheckbox("软件语言","language","langsoftware",$language);
}
elseif($catid == "409"){
	$company = $row['company'];
	$platform = $row['platform'];
	$tvshowsremarks = $row['tvshowsremarks'];
	
	tr_text("中文名","cname",$cname,$star);
	tr_text("英文名","ename",$ename,"如为0day发布，请填写0day名，如：NBA.2K12-RELOADED");
	tr_text("制作公司","company",$company);
	
	tr_textradio("平台/类别","platform","catplatform",$platform,$star."DotA视频请选择“视频”");
	tr_textradio("游戏类型","specificcat","catgame",$specificcat);
	tr_textradio("游戏语言","language","langgame",$language,"竞技视频请填写解说名");
	tr_textradio("文件格式","format","formatgame",$format,$star);
	
	tr_text("其他信息","tvshowsremarks",$tvshowsremarks,"（选填，如版本信息、破解小组、视频清晰度等其他说明）");	
}
elseif($catid == "410"){
	$tvshowsremarks = $row['tvshowsremarks'];
	tr_textradio("类别","specificcat","catothers",$specificcat,$star);
	tr_text("资源名称","cname",$cname,$star);
	tr_textcheckbox("资源格式","format","formatdocum",$format,"图片请填数目如18P；其他请填写文件格式");
	tr_text("其他信息","tvshowsremarks",$tvshowsremarks,"（选填，其他说明）");	
}
elseif($catid == "411"){
	$tvalias = $row['tvalias'];
	$tvseasoninfo = $row['tvseasoninfo'];
	
	tr_text("中文名","cname",$cname,$star);
	tr_text("英文名","ename",$ename);
	
	tr_textcheckbox("纪录片类型","specificcat","catnewsreel",$specificcat,$star);
	tr_textradio("纪录片文件格式","format","formatnewsreel",$format);
	tr_select("字幕情况","subsinfo",$subsinfo,$star);
	tr_textcheckbox("纪录片语言","language","langtvseries",$language);	
}
elseif($catid == "4013"){
	tr_text("标题","cname",$cname);
}
elseif($catid == "412"){
	tr_text("中文名","cname",$cname,$star);
	tr_text("英文名","ename",$ename,$star."英文名为0day名，如：Pearl.Harbor.2001.BluRay.iPADpad.720p.AAC.x264-CHDPAD");
	tr_textcheckbox("语言","language","langtvseries",$language,$star);	
	tr_select("字幕情况","subsinfo",$subsinfo,$star);
	tr_textcheckbox("制作国家/地区","district","districtmovie",$district,$star);	
	
}



?>
