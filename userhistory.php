<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();

parked();
$userid = 0+$_GET["id"];
int_check($userid,true);

if ($CURUSER["id"] != $userid && get_user_class() < $viewhistory_class)
permissiondenied();
$users=sql_query("SELECT * FROM users WHERE id = '".$userid."'") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($users) == 1)
$user = mysql_fetch_assoc($users);
if (($user["privacy"] == "strong") && (get_user_class() < $prfmanage_class) && $CURUSER[id] != $user[id])//隐私等级高
permissiondenied();

$action = htmlspecialchars($_GET["action"]);

//-------- Global variables

$perpage = 15;

//-------- Action: View posts

if ($action == "viewposts")
{
	$select_is = "COUNT(DISTINCT p.id)";

	$from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id";

	$where_is = "p.userid = $userid AND f.minclassread <= " . $CURUSER['class'];

	$order_is = "p.id DESC";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	$arr = mysql_fetch_row($res) or stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_posts_found']);

	$postcount = $arr[0];

	//------ Make page menu

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount, "userhistory.php?action=viewposts&id=$userid&");

	//------ Get user data

	$res = sql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 1)
	{
		$arr = mysql_fetch_assoc($res);

		$subject = get_username($userid);
	}
	else
	$subject = "unknown[$userid]";

	//------ Get posts

	$from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id LEFT JOIN readposts as r ON p.topicid = r.topicid AND p.userid = r.userid";

	$select_is = "f.id AS f_id, f.name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.*";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 0) stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_posts_found']);

	stdhead($lang_userhistory['head_posts_history']);

	print("<h1>".$lang_userhistory['text_posts_history_for'].$subject."</h1>\n");

	if ($postcount > $perpage) echo $pagertop;

	//------ Print table

	begin_main_frame();

	begin_frame();

	while ($arr = mysql_fetch_assoc($res))
	{
		$postid = $arr["id"];

		$posterid = $arr["userid"];

		$topicid = $arr["t_id"];

		$topicname = $arr["subject"];

		$forumid = $arr["f_id"];

		$forumname = $arr["name"];

		$newposts = ($arr["lastpostread"] < $arr["lastpost"]) && $CURUSER["id"] == $userid;

		$added = gettime($arr["added"], true, false, false);

		print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>
	    $added&nbsp;--&nbsp;".$lang_userhistory['text_forum'].
	    "<a href=forums.php?action=viewforum&forumid=$forumid>$forumname</a>
	    &nbsp;--&nbsp;".$lang_userhistory['text_topic'].
	    "<a href=forums.php?action=viewtopic&topicid=$topicid>$topicname</a>
      &nbsp;--&nbsp;".$lang_userhistory['text_post'].
      "<a href=forums.php?action=viewtopic&topicid=$topicid&page=p$postid#pid$postid>#$postid</a>" .
      ($newposts ? " &nbsp;<b>(<font class=new>".$lang_userhistory['text_new']."</font>)</b>" : "") .
      "</td></tr></table></p>\n");

      print("<br />");
      
      print("<table class=main width=100% border=1 cellspacing=0 cellpadding=5>\n");

      $body = format_comment($arr["body"]);

      if (is_valid_id($arr['editedby']))
      {
      	$subres = sql_query("SELECT username FROM users WHERE id=$arr[editedby]");
      	if (mysql_num_rows($subres) == 1)
      	{
      		$subrow = mysql_fetch_assoc($subres);
      		$body .= "<p><font size=1 class=small>".$lang_userhistory['text_last_edited'].get_username($arr['editedby']).$lang_userhistory['text_at']."$arr[editdate]</font></p>\n";
      	}
      }

      print("<tr valign=top><td class=comment>$body</td></tr>\n");

      print("</td></tr></table>\n");
      print("<br />");
	}

	end_frame();

	end_main_frame();

	if ($postcount > $perpage) echo $pagerbottom;

	stdfoot();

	die;
}

//-------- Action: View comments

if ($action == "viewcomments")
{
	$select_is = "COUNT(*)";

	// LEFT due to orphan comments
	$from_is = "comments AS c LEFT JOIN torrents as t
	            ON c.torrent = t.id";

	$where_is = "c.user = $userid";
	$order_is = "c.id DESC";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	$arr = mysql_fetch_row($res) or stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_comments_found']);

	$commentcount = $arr[0];

	//------ Make page menu

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $commentcount, "userhistory.php?action=viewcomments&id=$userid&");

	//------ Get user data

	$res = sql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 1)
	{
		$arr = mysql_fetch_assoc($res);

		$subject = get_username($userid);
	}
	else
	$subject = "unknown[$userid]";

	//------ Get comments

	$select_is = "t.name, c.torrent AS t_id, c.id, c.added, c.text";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 0) stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_comments_found']);

	stdhead($lang_userhistory['head_comments_history']);

	print("<h1>".$lang_userhistory['text_comments_history_for']."$subject</h1>\n");

	if ($commentcount > $perpage) echo $pagertop;

	//------ Print table

	begin_main_frame();

	begin_frame();

	while ($arr = mysql_fetch_assoc($res))
	{

		$commentid = $arr["id"];

		$torrent = $arr["name"];

		// make sure the line doesn't wrap
		if (strlen($torrent) > 55) $torrent = substr($torrent,0,52) . "...";

		$torrentid = $arr["t_id"];

		//find the page; this code should probably be in details.php instead

		$subres = sql_query("SELECT COUNT(*) FROM comments WHERE torrent = $torrentid AND id < $commentid")
		or sqlerr(__FILE__, __LINE__);
		$subrow = mysql_fetch_row($subres);
		$count = $subrow[0];
		$comm_page = floor($count/20);
		$page_url = $comm_page?"&page=$comm_page":"";

		$added = gettime($arr["added"], true, false, false);

		print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>".
		"$added&nbsp;---&nbsp;".$lang_userhistory['text_torrent'].
		($torrent?("<a href=details.php?id=$torrentid&tocomm=1&hit=1>$torrent</a>"):" [Deleted] ").
		"&nbsp;---&nbsp;".$lang_userhistory['text_comment']."</b>#<a href=details.php?id=$torrentid&tocomm=1&hit=1$page_url>$commentid</a>
	  </td></tr></table></p>\n");
		print("<br />");
		
		print("<table class=main width=100% border=1 cellspacing=0 cellpadding=5>\n");

		$body = format_comment($arr["text"]);

		print("<tr valign=top><td class=comment>$body</td></tr>\n");

		print("</td></tr></table>\n");
		
		print("<br />");
	}

	end_frame();

	end_main_frame();

	if ($commentcount > $perpage) echo $pagerbottom;

	stdfoot();

	die;
}

if ($action == "viewattach")
{
	$select_is = "COUNT(DISTINCT id)";

	$from_is = "attachments";

	$where_is = "userid = $userid";
	
	if($_GET["unused"])$where_is .= " AND ( cache_at!='0000-00-00 00:00:00' AND forums='0' AND torrents='0' AND offers='0' AND messages='0' AND comments='0' AND  requests='0' AND others='no' )";
	

	$order_is = "id DESC";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	$arr = mysql_fetch_row($res) or stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_attach_found']."，点击<a class=faqlink href=userdetails.php?id=$id >这里</a>返回个人信息页。",0);

	$postcount = $arr[0];

	//------ Make page menu

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount,  "userhistory.php?action=viewattach&id=$userid&".($_GET["unused"]?"unused=".$_GET["unused"]."&":""));

	//------ Get user data

	$res = sql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 1)
	{
		$arr = mysql_fetch_assoc($res);

		$subject = get_username($userid);
	}
	else
	$subject = "unknown[$userid]";

	//------ Get attachments

	$from_is = "attachments";

	$select_is = "id, added,userid, dlkey, location, cache_at, forums, torrents, offers, messages, comments, requests, others";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) == 0) stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_attach_found']."，点击<a class=faqlink href=userdetails.php?id=$id >这里</a>返回个人信息页。",0);

	stdhead($lang_userhistory['head_attach_history']);

	print("<h1>".$lang_userhistory['text_attach_history_for'].$subject."</h1>\n");

if($_GET["unused"])print($CURUSER["id"] != $arr["userid"] && get_user_class() < UC_MODERATOR?"":"<a href=\"javascript:document.delall.submit();\" ><h3>删除本页所有</h3></a>\n");

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
			"<input type=\"hidden\" name=\"returnto\" id=\"returnto\" value=\"userhistory.php?action=viewattach&id=".$userid."&page=".(!empty($page)?$page:"0").($_GET["unused"]?"&unused=".$_GET["unused"]:"")."\" /></form>\n".
			"<form action=\"userhistory.php?action=cacheattach&id=".$userid."\" method=\"post\" id=\"id".$postid."\" name=\"id".$postid."\" >\n".
			"<input type=\"hidden\" name=\"attachid\" id=\"attachid\" value=\"".$postid."\" />\n".
			"<input type=\"hidden\" name=\"returnto\" id=\"returnto\" value=\"userhistory.php?action=viewattach&id=".$userid."&page=".(!empty($page)?$page:"0").($_GET["unused"]?"&unused=".$_GET["unused"]:"")."\" />"
			);
			
		if($_GET["unused"])$deleteall.="<input type=hidden name=deleteids[] value=\"".$postid."\">\n";
			
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
	
	if($_GET["unused"])
		print(
			"<form action=\"delattachment.php\" method=post id=delall name=delall>\n".$deleteall.
			"<input type=hidden name=returnto value=\"userhistory.php?action=viewattach&id=".$userid."&page=".(!empty($page)?$page:"0").($_GET["unused"]?"&unused=".$_GET["unused"]:"")."\" />".
			"</form>".
			($CURUSER["id"] != $arr["userid"] && get_user_class() < UC_MODERATOR?"":"<a href=\"javascript:document.delall.submit();\" ><h3>删除本页所有</h3></a>\n")
			);
	stdfoot();

	die;
}

if($action=="cacheattach")
{
	
	$id=0+$_POST["attachid"];
	$query = "SELECT id, added, userid, dlkey, location, others FROM attachments WHERE id = $id ";
	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) == 0) stderr($lang_userhistory['std_error'], $lang_userhistory['std_no_attach_found']."，点击<a class=faqlink href=userdetails.php?id=$id >这里</a>返回个人信息页。",0);
	$arr = mysql_fetch_array($res);
	
	if ($_POST["others"]=="yes" && $arr["others"]=="no")$others=" ,others='yes' ";
	else if (!$_POST["others"] && $arr["others"]=="yes")$others=" ,others='no' ";
	else $others="";
	
	if($CURUSER["id"] != $arr["userid"] && get_user_class() < UC_MODERATOR )$others="";

	$keyword1="'%".$arr["location"]."%'";
	$keyword2="'%[attach]".$arr["dlkey"]."[/attach]%'";
	$keyword3="'%getattachment.php%dlkey=".$arr["dlkey"]."%'";
	
	
	$forums1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM posts WHERE body like ".$keyword1." OR body like ".$keyword2." OR body like ".$keyword3 ));
	$forums=$forums1[0];
	
	$torrents1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM torrents WHERE descr like ".$keyword1." OR descr like ".$keyword2." OR descr like ".$keyword3));
	$torrents=$torrents1[0];
	
	$offers1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM offers WHERE descr like ".$keyword1." OR descr like ".$keyword2." OR descr like ".$keyword3));
	$offers=$offers1[0];
	
	$messages1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM messages WHERE msg like ".$keyword1." OR msg like ".$keyword2." OR msg like ".$keyword3));
	$messages=$messages1[0];
	
	$comments1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM comments WHERE text like ".$keyword1." OR text like ".$keyword2." OR text like ".$keyword3));
	$comments=$comments1[0];
	
	$requests1 =  mysql_fetch_row(sql_query("SELECT count(*) FROM req WHERE introduce like ".$keyword1." OR introduce like ".$keyword2." OR introduce like ".$keyword3));
	$requests=$requests1[0];
	
	$query = "UPDATE attachments SET cache_at ='".date("Y-m-d H:i:s")."',forums ='".$forums."', torrents = '".$torrents."', offers ='".$offers."', messages ='".$messages."', comments ='".$comments."', requests ='".$requests."'".$others." WHERE id = $id ";
	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
		
	if (!empty($_POST["returnto"]))
		header("Location: $_POST[returnto]");
	else
		header("Location: userhistory.php?action=viewattach&id=".$userid);
	die;
}

//-------- Handle unknown action

if ($action != "")
stderr($lang_userhistory['std_history_error'], $lang_userhistory['std_unkown_action']);

//-------- Any other case

stderr($lang_userhistory['std_history_error'], $lang_userhistory['std_invalid_or_no_query']);

?>
