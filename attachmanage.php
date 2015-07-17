<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path("userhistory.php"));
loggedinorreturn();

parked();
if (get_user_class() < UC_MODERATOR )
permissiondenied();


//-------- Global variables

$perpage = 100;

{
	$select_is = "COUNT(DISTINCT id)";

	$from_is = "attachments";
	
	$aweek=date("Y-m-d H:i:s",time()-7*86400);

	$where_is = " ( cache_at!='0000-00-00 00:00:00' AND added < '".$aweek."' AND forums='0' AND torrents='0' AND offers='0' AND messages='0' AND comments='0' AND  requests='0' AND others='no' )";
	

	$order_is = "id ASC";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	$arr = mysql_fetch_row($res) or stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_attach_found'],0);

	$postcount = $arr[0];

	//------ Make page menu

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount,  "attachmanage.php?");

	//------ Get user data

	//------ Get attachments

	$from_is = "attachments";

	$select_is = "id, added,userid, dlkey, location, cache_at, forums, torrents, offers, messages, comments, requests, others";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 0) stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_attach_found'],0);

	stdhead($lang_userhistory['head_attach_history']);

	print("<h1>".$lang_userhistory['text_attach_history_for'].$subject."</h1>\n");

	print($CURUSER["id"] != $arr["userid"] && get_user_class() < UC_MODERATOR?"":"<a href=\"javascript:document.delall.submit();\" ><h3>删除本页所有</h3></a>\n");

	if ($postcount > $perpage) echo $pagertop;

	//------ Print table

	begin_main_frame();

	

	begin_frame();
	
	
	$deleteall="";

	while ($arr = mysql_fetch_assoc($res))
	{
		$postid = $arr["id"];

		$dlkey = $arr["dlkey"];

		$added = gettime($arr["added"], true, false, false);
		
		print(
			"<form action=\"delattachment.php\" method=\"post\" id=\"del".$postid."\" name=\"del".$postid."\" >\n".
			"<input type=\"hidden\" name=\"id\" id=\"id\" value=\"".$postid."\" />\n".
			"<input type=\"hidden\" name=\"returnto\" id=\"returnto\" value=\"attachmanage.php?page=".(!empty($page)?$page:"0")."\" /></form>\n".
			"<form action=\"userhistory.php?action=cacheattach&id=".$userid."\" method=\"post\" id=\"id".$postid."\" name=\"id".$postid."\" >\n".
			"<input type=\"hidden\" name=\"attachid\" id=\"attachid\" value=\"".$postid."\" />\n".
			"<input type=\"hidden\" name=\"returnto\" id=\"returnto\" value=\"attachmanage.php?page=".(!empty($page)?$page:"0")."\" />"
			);
			
		$deleteall.="<input type=hidden name=deleteids[] value=\"".$postid."\">\n";
			
		if($arr["cache_at"]=="0000-00-00 00:00:00")
		{
			$page=0+$_GET["page"];
			print("<p class=sub><table width=100% border=0 cellspacing=0 cellpadding=0><tr>".
			"<td width=47% class=embedded>".$lang_userhistory['addtime'].$added."</td>".
			"<td width=50% class=embedded><a href=\"javascript:document.id".$postid.".submit();\">查看当前使用情况</a></td>".
			"<td width=3% class=embedded > ".($CURUSER["id"] != $arr["userid"] && get_user_class() < UC_MODERATOR ?"</td>":"<a href=\"javascript:document.del".$postid.".submit();\"><font color=black>删除</font></a></td>" ).
			"</tr></table></p>\n");
		}
		else
		{
			$space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$space1="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		print("<p class=sub><table width=100% border=0 cellspacing=0 cellpadding=0><tr>".
			"<td width=20% class=embedded>".$lang_userhistory['addtime']."<br/>".$added."</td>".
			"<td width=77% class=embedded>当前使用情况:".$space1."(缓存于".$arr["cache_at"].")".$space1."<a href=\"javascript:document.id".$postid.".submit();\"><font color=blue><b>更新使用情况</b></font></a><br/> 论坛-".$arr["forums"]."处".$space."种子简介-".$arr["torrents"]."处".$space."候选简介-".$arr["offers"]."处".$space."求种-".$arr["requests"]."处".$space."评论-".$arr["comments"]."处".$space."站内信-".$arr["messages"]."处".$space."<input type=checkbox name=\"others\" value=\"yes\" ".($arr["others"]=="yes"?"checked=checked":"").($CURUSER["id"] != $arr["userid"] && get_user_class() < UC_MODERATOR?"disabled":"").">其他</td>".
			"<td width=3% class=embedded >".($CURUSER["id"] != $arr["userid"] && get_user_class() < UC_MODERATOR?"</td>":"<a href=\"javascript:document.del".$postid.".submit();\"> <font color=".
			($arr["forums"]+$arr["torrents"]+$arr["offers"]+$arr["requests"]+$arr["comments"]+$arr["messages"]==0&&$arr["others"]=="no"?"red><b>建议<br/>删除</b>":"black>删除")."</font></a></td>").
			"</tr></table></p>\n</form>\n");
		}
		print("<table class=main width=100% border=1 cellspacing=0 cellpadding=5>\n");
		$body = format_comment("[code][attach]".$dlkey."[/attach][/code][attach]".$dlkey."[/attach]");
		print("<tr valign=top><td class=comment>$body</td></tr>\n");
		print("</td></tr></table>\n");
		print("<br />");print("<br />");
	}

	end_frame();

	end_main_frame();

	if ($postcount > $perpage) echo $pagerbottom;


		print(
			"<form action=\"delattachment.php\" method=post id=delall name=delall>\n".$deleteall.
			"<input type=hidden name=returnto value=\"attachmanage.php?page=".(!empty($page)?$page:"0")."\" />".
//			"<input type=hidden name=sure value=\"yes\" />".
			"</form>".
			($CURUSER["id"] != $arr["userid"] && get_user_class() < UC_MODERATOR?"":"<a href=\"javascript:document.delall.submit();\" ><h3>删除本页所有</h3></a>\n")
			);
	stdfoot();

	die;
}
?>
