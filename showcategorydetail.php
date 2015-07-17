<?php
/*
此文件在client.js中调用。当发布种子或候选时，当选中某一分类时，发布页面（upload.php或offers.php）弹出填写详细信息页面，即调用此文件。
*/
require "include/bittorrent.php";
dbconn(true);
function gbkToUtf8 ($value) {
   return iconv("gbk","UTF-8", $value);
}

function tr_select($x,$y,$z,$star=""){
	$listarray = sql_query("SELECT * FROM ".$y . " ORDER BY sort_index ASC");
	
	$hint = "请选择";
	$show = "<tr class=\"rowfollow\" ><td class=\"no_border\" ><strong>".$x."</strong><select name=".$z."><option value=\"0\">".$hint."</option>";
	while($row = mysql_fetch_array($listarray)){
		$show .= "<option value=".$row[id].">".$row[name]."</option>";
	}
	$show .="</select>$star</td></tr><div class=\"clear1\"></div>";
	
	print($show);
}

function tr_text($x,$y,$z="") {	
	print("<tr class=\"rowfollow\" height=\"36\"><td class=\"no_border\" valign=\"top\" align=\"right\"><label><strong>".$x."</strong><input type=\"text\" size=\"50\" maxlength=\"200\"  name=\"" .$y. "\"  id=\"" .$y. "\" /></label>".$z."</td></tr><div class=\"clear1\"></div>");
}

function tr_textcheckbox($x,$y,$z,$star="") {
	$listarray = searchbox_item_list($z);
	$num = count($listarray);	
	$show = "<tr class=\"rowfollow\" ><td class=\"no_border\" valign=\"top\" align=\"right\"><label><strong>".$x."</strong><input type=\"text\" size=\"50\" maxlength=\"100\" id=\"".$y."\" name=\"" .$y. "\"></label>$star<br>";
	foreach ($listarray as $row){
		$id = $y.$row['id'];
		$show .= "<label><input id=".$id ." type=checkbox value=".$row['name']." name=".$row['name']. " onClick=getcheckboxvalue('".$y."',".$num.") />".$row['name']."</label>";
	}
	$show .= "</td></tr><div class=\"clear1\"></div>";
	
	print($show);

}

function tr_textradio($x,$y,$listname,$star="") {
	$listarray = searchbox_item_list($listname);
	$num = count($listarray);	
	$show = "<tr class=\"rowfollow\" ><td class=\"no_border\" valign=\"top\" align=\"right\"><label><strong>".$x."</strong><input type=\"text\" size=\"50\" maxlength=\"100\" id=\"".$y."\" name=\"" .$y. "\" /></label>$star<br>";
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
			$show .= "<label><input id=".$id ." type=radio value=".$row['name']." name=".$radioname. " onClick=getradiovalue('".$y."','".$id."') />".$row['name']."</label>";
		}
	}

	//$otherid = $y."other";
	//$show .= "<INPUT id=".$otherid ." type=radio value=\"其他\" name=".$radioname. " onClick=getradiovalue('".$y."','".$otherid."')><LABEL for=".$row['id'].">"."其他(如果选择此选项，请在上面的文本框中填写具体的".$info."信息)</LABEL>";
	$show .= "</td></tr><div class=\"clear1\"></div>";
	
	print($show);

}

function tr_uploadinfo($x,$y){
	$show = "<tr class=\"rowfollow\" ><td class=\"no_border\" valign=\"top\" align=\"right\">您选择的是".$x."类资源，请查看<a href=\"". $y."\"><font color=\"#FF0000\">".$x."类资源发布标准细则</font></a>";
	
	$show .= "</td></tr><div class=\"clear1\"></div>";
	print($show);
}

//ajax采用utf编码，从数据库获得的中文字符为gbk编码，iconv函数将从数据库取得的中文字符先编码为utf-8,
//再交给ajax处理echo输出，解决显示ajax乱码问题.
$star = "<b><font color=red>*</font></b>";
$catid = 0 + $_GET["catid"];

if($catid == 401){
	tr_uploadinfo("电影","forums.php?action=viewtopic&forumid=5&topicid=56");
	tr_text("中文名","cname",$star);
	tr_text("英文名","ename",$star."英文名为0day名，如：The.Kings.Speech.2010.BDRip.XviD-AMIABLE");
	//tr_text("Imdb编号","imdbnum","如：tt0120815");
	tr_text("发行时间","issuedate",$star);
	
	tr_textcheckbox("电影语言","language","langtvseries",$star);	
	//tr_textcheckbox("电影类别","specificcat","catmovie",$star);
	tr_textradio("电影文件格式","format","formatmovie",$star);
	tr_select("字幕情况","subsinfo","subsinfo",$star);
	tr_textcheckbox("制作国家/地区","district","districtmovie",$star);	
	
}
elseif($catid == "402"){
	tr_uploadinfo("剧集","forums.php?action=viewtopic&forumid=5&topicid=57");
	tr_text("中文名","cname",$star);
	tr_text("英文名","ename");
	tr_text("别名","tvalias");
	tr_text("集数","tvseasoninfo");
	
	tr_textcheckbox("剧集类型","specificcat","catseries",$star);
	tr_textcheckbox("剧集文件格式","format","formattvseries");
	tr_select("字幕情况","subsinfo","subsinfo");
	tr_textcheckbox("剧集语言","language","langtvseries",$star);	
}
elseif($catid == "403"){
	tr_uploadinfo("综艺","forums.php?action=viewtopic&forumid=5&topicid=70");
	tr_text("中文名","cname");
	tr_text("英文名","ename");
	tr_text("发行时间","issuedate");
	tr_text("节目内容","tvshowscontent");
	tr_text("节目嘉宾","tvshowsguest");
	
	tr_textcheckbox("国家/地区","district","districttvshows");
	tr_select("字幕情况","subsinfo","subsinfo");
	tr_textcheckbox("节目语言","language","langtvshows");
	tr_textradio("节目格式","format","formattvshows","格式");
	
	tr_text("备注","tvshowsremarks");
}
elseif($catid == "404"){
	tr_uploadinfo("资料","forums.php?action=viewtopic&forumid=5&topicid=72");
	tr_text("中文名","cname");
	tr_text("英文名","ename");
	tr_text("发行时间","issuedate");
	tr_text("版本","version");
	
	tr_textcheckbox("资料类别","specificcat","catdocum");	
	tr_textcheckbox("资料文件格式","format","formatdocum");	
}
elseif($catid == "405"){
	tr_uploadinfo("动漫","forums.php?action=viewtopic&forumid=5&topicid=55");
	tr_text("中文名","cname");
	tr_text("英文名","ename",$star);
	tr_text("发行时间","issuedate");
	tr_text("动漫集数","animenum");
	tr_text("字幕组/漫画作者/专辑艺术家","substeam",$star);
	
	tr_textcheckbox("动漫类别","specificcat","catanime",$star);	
	tr_textcheckbox("动漫文件格式","format","formatanime",$star);	
	tr_textcheckbox("画面分辨率","resolution","resolutionanime");	
	tr_textcheckbox("动漫国别","district","districtanime");	
}
elseif($catid == "406"){
	tr_uploadinfo("音乐","forums.php?action=viewtopic&forumid=5&topicid=69");
	tr_text("专辑名","hqname",$star);
	tr_text("艺术家","artist",$star);
	tr_text("发行时间","issuedate");
	
	tr_textcheckbox("音乐类别","specificcat","cathq");	
	tr_textcheckbox("音乐文件格式","format","formathqaudio",$star);	
	tr_textcheckbox("音乐语言","language","langhq");	
	tr_textcheckbox("音质/码率","hqtone","hqtone");	
}	
elseif($catid == "407"){
	tr_uploadinfo("体育","forums.php?action=viewtopic&forumid=5&topicid=59");
	tr_text("比赛日期","issuedate");
	tr_text("赛事类别","cname",$star);
	tr_text("对阵双方","ename");
	tr_text("电视台及语言","language");
	
	tr_textcheckbox("体育类型","specificcat","catsports",$star);
	tr_textcheckbox("体育节目格式","format","formatsports",$star);
	tr_textcheckbox("录像分辨率","resolution","resolutionsports");	
}
elseif($catid == "408"){
	tr_uploadinfo("软件","forums.php?action=viewtopic&forumid=5&topicid=71");
	tr_text("中文名","cname");
	tr_text("英文名","ename","<b>请注意填写操作系统和位数，如 Win 64bit 或 Mac 等</b>");
	tr_text("发行时间","issuedate");
	tr_text("版本","version");
	
	tr_textcheckbox("软件类型","specificcat","catsoftware");
	tr_textcheckbox("软件文件格式","format","formatsoftware");
	tr_textcheckbox("软件语言","language","langsoftware");
}
elseif($catid == "409"){
	tr_uploadinfo("游戏","forums.php?action=viewtopic&forumid=5&topicid=58");
	tr_text("中文名","cname",$star);
	tr_text("英文名","ename","如为0day发布，请填写0day名，如：NBA.2K12-RELOADED");
	tr_text("制作公司","company");
	
	tr_textradio("平台/类别","platform","catplatform",$star."DotA视频请选择“视频”");
	tr_textradio("游戏类型","specificcat","catgame");	
	tr_textradio("游戏语言","language","langgame","竞技视频请填写解说");
	tr_textradio("文件格式","format","formatgame",$star);
	
	tr_text("其他信息","tvshowsremarks","（选填，如版本信息、破解小组、视频清晰度等其他说明）");	
}
elseif($catid == "410"){
	tr_uploadinfo("其他","forums.php");
	tr_textradio("类别","specificcat","catothers",$star);
	tr_text("资源名称","cname",$star);
	tr_textcheckbox("资源格式","format","formatdocum","图片请填数目如18P；其他请填写文件格式");
	tr_text("其他信息","tvshowsremarks","（选填，其他说明）");	
}
elseif($catid == "411"){
	tr_uploadinfo("纪录片","forums.php?action=viewtopic&forumid=5&topicid=7035");
	tr_text("中文名","cname",$star);
	tr_text("英文名","ename",$ename);
	
	tr_textcheckbox("纪录片类型","specificcat","catnewsreel",$star);
	tr_textradio("纪录片文件格式","format","formatnewsreel");
	tr_select("字幕情况","subsinfo","subsinfo",$star);
	tr_textcheckbox("纪录片语言","language","langtvseries");	
}
elseif($catid == "4013"){
	tr_text("标题","cname");
}
elseif($catid == "412"){
	tr_uploadinfo("移动视频","forums.php?action=viewtopic&forumid=5&topicid=9952");
	tr_text("中文名","cname",$star);
	tr_text("英文名","ename",$star."英文名为0day名，如：Pearl.Harbor.2001.BluRay.iPADpad.720p.AAC.x264-CHDPAD");
	tr_textcheckbox("语言","language","langtvseries",$star);	
	tr_select("字幕情况","subsinfo","subsinfo",$star);
	tr_textcheckbox("制作国家/地区","district","districtmovie",$star);	
	
}
?>
