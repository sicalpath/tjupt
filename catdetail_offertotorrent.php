<?php
/*
此文件在client.js中调用。当候选被通过后发布种子时，当选中某一被通过的候选时，会在发布页面（upload.php）弹出候选的详细信息页面，即调用此文件。
*/
require "include/bittorrent.php";
dbconn(true);

function gbkToUtf8 ($value) {
   return iconv("gbk","UTF-8", $value);
}

function tr_text($x) {	
	$text = "<tr class=\"rowfollow\" ><td class=\"no_border\" valign=\"top\" align=\"right\">".$x."</td></tr><div class=\"clear1\"></div>";
	print($text);
}

$offerid = 0 + $_GET["offerid"];

tr_text("种子文件<font color=\"red\">*</font><input type=\"file\" class=\"file\" id=\"torrent\" name=\"file\" onchange=\"getname()\" />\n");

/**********************************************显示候选详细信息**************************************************/
		$res =  sql_query("SELECT * FROM offers WHERE id = ".$offerid." LIMIT 1")
		or sqlerr();
		$rowrow = mysql_fetch_array($res);
		$descr = $rowrow[descr];
		$descr__ = format_comment($descr);
		$descr_ = "<b>简介：</b><br /> ".$descr__;
		
		$ret = sql_query("SELECT * FROM offersinfo WHERE offerid = ".$offerid." LIMIT 1")
		or sqlerr();
		$row_ = mysql_fetch_array($ret);
		$catid = $row_[category];
		$detailsinfo = "<b>候选详细信息：</b><br /><br />";
		if($catid != "407"&&$catid!="410")
		{		
			if ($row_["cname"]!="")
				$cname = "<b>中文名:</b>".$row_[cname]."<br /><br />";
			if ($row_["ename"]!="")
				$ename = "<b>英文名:</b>".$row_[ename]."<br /><br />";
			if ($row_["issuedate"]!="")
				$issuedate = "<b>发行时间:</b>".$row_[issuedate]."<br /><br />";
			if ($row_["subsinfo"]!=0){
				$result = sql_query("SELECT * FROM subsinfo WHERE id = ".$row_["subsinfo"]);
				$result_ = mysql_fetch_array($result);
				$subsinfo = "<b>字幕情况:</b>".$result_[name]."<br /><br />";
			}
		}
		
		if($catid == 401){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."电影"."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>电影类别:</b>".$row_[specificcat]."<br /><br />";
			if($row_["district"]!="")
				$district = "<b>制片国家/地区:</b>".$row_[district]."<br /><br />";
			if($row_["format"]!="")
				$format = "<b>电影文件格式:</b>".$row_[format]."<br /><br />";
			if($row_["language"]!="")
				$language = "<b>电影语言:</b>".$row_[language]."<br /><br />";
			if($row_["imdbnum"]!="")
				$imdbnum = "<b>IMDb编号:</b>".$row_[imdbnum]."<br /><br />";
			tr_text($detailsinfo.$category.$cname.$ename.$issuedate.$subsinfo.$specificcat.$district.$format.$language.$imdbnum.$descr_);
		}
		if($catid == 402){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."剧集"."<br /><br />";
			if($row_["tvalias"]!="")
				$tvalias = "<b>别名:</b>".$row_[tvalias]."<br /><br />";
			if ($row_["format"]!="")
				$format = "<b>剧集文件格式:</b>".$row_[format]."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>剧集类型:</b>".$row_[specificcat]."<br /><br />";
			if($row_["language"]!="")
				$language = "<b>剧集语言:</b>".$row_[language]."<br /><br />";
			if($row_["tvseasoninfo"]!="")
				$tvseasoninfo = "<b>剧集季度信息:</b>".$row_[tvseasoninfo]."<br /><br />";
			tr_text($detailsinfo.$category.$cname.$ename.$issuedate.$specificcat.$format.$language.$subsinfo.$tvalias.$tvseasoninfo.$descr_);
		}
		if($catid == 403){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."综艺"."<br /><br />";
			if($row_["district"]!="")
				$district = "<b>国家/地区:</b>".$row_[district]."<br /><br />";
			if($row_["tvshowscontent"]!="")
				$tvshowscontent = "<b>节目内容:</b>".$row_[tvshowscontent]."<br /><br />";
			if($row_["tvshowsguest"]!="")
				$tvshowsguest = "<b>节目嘉宾:</b>".$row_[tvshowsguest]."<br /><br />";
			if($row_["language"]!="")
				$language = "<b>节目语言:</b>".$row_[language]."<br /><br />";
			if($row_["tvshowsremarks"]!="")
				$tvshowsremarks = "<b>备注:</b>".$row_[tvshowsremarks]."<br /><br />";
			if($row_["format"]!="")
				$format = "综艺节目格式".$row_[format]."<br /><br />";
			tr_text($detailsinfo.$category.$cname.$ename.$issuedate.$subsinfo.$district.$tvshowscontent.$tvshowsguest.$language.$format.$tvshowsremarks.$descr_);
		}
		if($catid == 404){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."资料"."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>资料类别:</b>".$row_[specificcat]."<br /><br />";
			if ($row_["format"]!="")
				$format = "<b>资料文件格式:</b>".$row_[format]."<br /><br />";
			if($row_["version"]!="")
				$version = "<b>版本:</b>".$row_[version]."<br /><br />";
			tr_text($detailsinfo.$category.$cname.$ename.$issuedate.$specificcat.$format.$version.$descr_);
		}
		if($catid == 405){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."动漫"."<br /><br />";
			if($row_["animenum"]!="")
				$animenum = "<b>动漫集数:</b>".$row_[animenum]."<br /><br />";
			if($row_["substeam"]!="")
				$substeam = "<b>字幕组:</b>".$row_[substeam]."<br /><br />";
			if($row_["resolution"]!="")
				$resolution = "<b>画面分辨率:</b>".$row_[resolution]."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>动漫类别:</b>".$row_[specificcat]."<br /><br />";
			if($row_["district"]!="")
				$district = "<b>动漫国别:</b>".$row_[district]."<br /><br />";
			if ($row_["format"]!="")
				$format = "<b>动漫文件格式:</b>".$row_[format]."<br /><br />";
			tr_text($detailsinfo.$category.$cname.$ename.$issuedate.$specificcat.$format.$animenum.$substeam.$resolution.$district.$descr_);
		}
		if($catid == 406){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."音乐"."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>音乐类型:</b>".$row_[specificcat]."<br /><br />";
			if($row_["hqname"]!="")
				$hqname = "<b>专辑名:</b>".$row_[hqname]."<br /><br />";
			if($row_["artist"]!="")
				$artist = "<b>艺术家:</b>".$row_[artist]."<br /><br />";
			if($row_["hqtone"]!="")
				$hqtone = "<b>音质/码率:</b>".$row_[hqtone]."<br /><br />";
			if($row_["format"]!="")
				$format = "<b>音乐文件格式:</b>".$row_[format]."<br /><br />";
			if($row_["language"]!="")
				$language = "<b>音乐语言:</b>".$row_[language]."<br /><br />";
			tr_text($detailsinfo.$category.$hqname.$issuedate.$artist.$specificcat.$format.$language.$hqtone.$descr_);
		}
		if($catid == 407){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."体育"."<br /><br />";
			if ($row_["cname"]!="")
				$cname = "<b>赛事类别:</b>".$row_[cname]."<br /><br />";
			if ($row_["ename"]!="")
				$ename = "<b>对阵双方:</b>".$row_[ename]."<br /><br />";
			if ($row_["issuedate"]!="")
				$issuedate = "<b>比赛日期:</b>".$row_[issuedate]."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>体育类型:</b>".$row_[specificcat]."<br /><br />";
			if($row_["language"]!="")
				$language = "<b>电视台及语言:</b>".$row_[language]."<br /><br />";
			if($row_["format"]!="")
				$format = "<b>体育文件格式:</b>".$row_[format]."<br /><br />";
			if($row_["resolution"]!="")
				$resolution = "<b>录像分辨率:</b>".$row_[resolution]."<br /><br />";
			tr_text($detailsinfo.$category.$cname.$issuedate.$specificcat.$format.$resolution.$language.$descr_);
		}
		if($catid == 408){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."软件"."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>软件类型:</b>".$row_[specificcat]."<br /><br />";
			if($row_["language"]!="")
				$language = "<b>软件语言:</b>".$row_[language]."<br /><br />";
			if($row_["format"]!="")
				$format = "<b>软件格式:</b>".$row_[format]."<br /><br />";
			if($row_["version"]!="")
				$version = "<b>版本:</b>".$row_[version]."<br /><br />";			
			tr_text($detailsinfo.$category.$cname.$ename.$issuedate.$specificcat.$format.$language.$version.$descr_);
		}
		if($catid == 409){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."游戏"."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>游戏类型:</b>".$row_[specificcat]."<br /><br />";
			if($row_["language"]!="")
				$language = "<b>游戏语言:</b>".$row_[language]."<br /><br />";
			if($row_["format"]!="")
				$format = "<b>游戏文件格式:</b>".$row_[format]."<br /><br />";
			if($row_["platform"]!="")
				$platform = "<b>平台/类别:</b>".$row_[platform]."<br /><br />";
			if($row_["company"]!="")
				$company = "<b>制作公司:</b>".$row_[company]."<br /><br />";
			if($row_["tvshowsremarks"]!="")
				$tvshowsremarks = "<b>版本信息</b>".$row_[tvshowsremarks]."<br /><br />";
			tr_text($detailsinfo.$category.$cname.$ename.$specificcat.$format.$platform.$language.$company.$tvshowsremarks.$descr_);
		}
		if($catid == 410){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."其他"."<br /><br />";
			if($row_["specificcat"]!="")
				$specificcat = "<b>类型:</b>".$row_[specificcat]."<br /><br />";
			if($row_["cname"]!=""){
				$cname_ = "<b>名称</b>".$row_["cname"]."<br /><br />";
			if($row_["format"]!="")
				$format = "<b>资源格式:</b>".$row_[format]."<br /><br />";
			if($row_["tvshowsremarks"]!="")
				$tvshowsremarks = "<b>其他信息</b>".$row_[tvshowsremarks]."<br /><br />";
				tr_text($detailsinfo.$category.$specificcat.$cname_.$format.$descr_);
			}
				
		}
		if($catid == 4013){			
			if($row_["cname"]!=""){
				$cname_ = "<b>名称</b>".$row_["cname"]."<br /><br />";
				tr_text($detailsinfo.$cname_.$descr_);
			}
				
		}
		if($catid == 412){
			if ($row_["category"]!="")
				$category = "<b>资源类型:</b>"."移动视频"."<br /><br />";
			if($row_["district"]!="")
				$district = "<b>制作国家/地区:</b>".$row_[district]."<br /><br />";
			if($row_["language"]!="")
				$language = "<b>语言:</b>".$row_[language]."<br /><br />";
			tr_text($detailsinfo.$category.$cname.$ename.$language.$subsinfo.$district.$descr_);
		}
		/****************************************************************************************************************/

if ($enablenfo_main=='yes')
	tr_text("NFO文件<input type=\"file\" class=\"file\" name=\"nfo\" /><br /><font class=\"medium\">(不允许".get_user_class_name($viewnfo_class,false,true,true)."以下用户查看。请上传.nfo文件) ");
	
$source_select = torrent_selection($lang_upload['text_source'],"source_sel","sources");
tr_text("来源：".$source_select);

$team_select = torrent_selection($lang_upload['text_team'],"team_sel","teams");
tr_text("地区：".$team_select);

tr_text("匿名发布：<input type=\"checkbox\" name=\"uplver\" value=\"yes\" />不要在发布者项目中显示我的用户名");

tr_text("<div align=\"center\" ><b>我已经阅读过规则</b> <input id=\"qr\" type=\"submit\" class=\"btn\" value=\"发布\"></div>");

?>
