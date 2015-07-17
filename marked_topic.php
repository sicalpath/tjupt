<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
parked();
function paging($page,$page_amount )
{   
    global $lang_jc_rank;
    if($page!=1&&$page!=$page_amount)
    {  
        //print("<div style=\"float:left\"");
        print("<a href=\"?page=1\">|首页</a><a href=\"?rank_sort=".$rank_sort."&page=".($page+1)."\">|下页|</a><a>......</a><a href=\"?rank_sort=".$rank_sort."&page=".($page-1)."\">|上页</a><a href=\"?rank_sort=".$rank_sort."&page=".$page_amount."\">|尾页|</a>");
        //print("</div>");
    } 
    else if ($page==1 && $page_amount == 1)
    {
        //print("<div style=\"float:left\"");
        print("<a href=\"?page=1\">|首页</a><a href=\"#\">|下页|</a><a>......</a><a href=\"#\">|上页|</a><a href=\"?rank_sort=".$rank_sort."&page=".$page_amount."\">|尾页|</a>");
        //print("</div>");
    }
    else if($page==1){
        print("<a href=\"?page=1\">|首页</a><a href=\"?rank_sort=".$rank_sort."&page=".($page+1)."\">|下页|</a><a>......</a><a>|上页|</a><a href=\"?rank_sort=".$rank_sort."&page=".$page_amount."\">|尾页|</a>");
    }
    else 
    { 
        //print("<div style=\"float:left\"");
        print("<a href=\"?page=1\">|首页</a><a>|下页|</a><a>......</a><a href=\"?rank_sort=".$rank_sort."&page=".($page-1). "\">|上页|</a><a href=\"?rank_sort=".$rank_sort."&page=".$page_amount."\">|尾页|</a>");

        // print("</div>");
    }

    print(" 当前".$page."页 共".$page_amount."页</br>"  );



} //分页页面选择函数结束
function topiclist($page=1,$pagesize=20)
{
    global $CURUSER;   
    $all=mysql_num_rows(sql_query("SELECT * FROM marked_topic WHERE uid=$CURUSER[id]"));
    if($all>0)
    {    
            if($all<=$pagesize)
            {
                $page_amount=1;}
            else 
            {
                $temp = $all/$pagesize;
                if($all%$pagesize)
                {
                    $page_amount=(int)($all/$pagesize+1);
                }
                else{$page_amount=$all/$pagesize;}
            }  
    }
    else 
    {    stdmsg("对不起","暂无数据");
    			stdfoot();
         exit();
    }
    paging($page,$page_amount);

?>
<table border="0" cellspacing="0" cellpadding="5" width=940>
<?php
print("<td align=center class=tabletitle><b>收藏的主题</b></td>");
?>
</table>
<?php
print("<table border=0 cellspacing=0 cellpadding=3 width=940><tr>".
"<td class=colhead align=left width=80%>主题</td>".
"<td class=colhead align=center><nobr>回复/查看</nobr></td>".
"<td class=colhead align=center>发布者</td>".
"<td class=colhead align=center width=20%>最近回复</td>".
"<td class=colhead align=center>操作</td>".
"</tr>");
$res_topics = sql_query("SELECT * FROM marked_topic INNER JOIN topics ON topics.id = marked_topic.tid WHERE marked_topic.uid = ".$CURUSER[id]." ORDER BY marked_topic.id DESC LIMIT"." ". ((sqlesc($page)-1)*sqlesc($pagesize)).",".sqlesc($pagesize)) or sqlerr();
while ($topicarr = mysql_fetch_assoc($res_topics))
{
	$topicid = $topicarr["id"];
	$topic_title = $topicarr["subject"];
	$topic_userid = $topicarr["userid"];
	$topic_views = $topicarr["views"];
	$views = number_format($topic_views);

	/// GETTING TOTAL NUMBER OF POSTS ///
	global $Cache;
	if (!$posts = $Cache->get_value('topic_'.$topicid.'_post_count')){
		$posts = get_row_count("posts","WHERE topicid=".sqlesc($topicid));
		$Cache->cache_value('topic_'.$topicid.'_post_count', $posts, 3600);
	}
	$replies = max(0, $posts - 1);

	/// GETTING USERID AND DATE OF LAST POST ///
	$arr = get_post_row($topicarr['lastpost']);
	$postid = 0 + $arr["id"];
	$userid = 0 + $arr["userid"];
	$added = gettime($arr['added'],true,false);

	/// GET NAME OF LAST POSTER ///
	$username = get_username($userid);

	/// GET NAME OF THE AUTHOR ///
	$author = get_username($topic_userid);
	$subject = "<a href=forums.php?action=viewtopic&topicid=$topicid><b>" . htmlspecialchars($topicarr["subject"]) . "</b></a>";

	print("<tr class=tableb><td style='padding-left: 10px' align=left class=rowfollow>$subject</td>".
	"<td align=center class=rowfollow>".$replies."/".$views."</td>" .
	"<td align=center class=rowfollow>".$author."</td>" .
	"<td align=center class=rowfollow><nobr>".$added." | ".$username."</nobr></td>" .
	"<td align=center class=rowfollow><a href=\"" . htmlspecialchars ( "marked_topic.php?action=delete&topicid=" . $topicid ) . "\"><input class=\"btn\" type=\"button\" value=\"删除\" alt=\"Delete\"  /></a></td></tr>");
}
?>
  </table>
</td>
</tr>
<?php




}
		stdhead("收藏的帖子");
		global $CURUSER;
		if ($_GET["action"]=="add")
			{
				if ($tid=$_GET["topicid"])
				{
					$res=sql_query("SELECT * from topics where id = $tid");
					if ($row=mysql_fetch_array($res))
					{ $uid=$CURUSER[id];
				sql_query("insert INTO marked_topic (tid, uid) VALUES ($tid,$uid)") or sqlerr ( __FILE__, __LINE__ );}}}
		if ($_GET["action"]=="delete")
			{
				if ($tid=$_GET["topicid"])
				{ $uid=$CURUSER[id];
					$res=sql_query("SELECT * from marked_topic where tid = $tid and uid =$uid");
					if ($row=mysql_fetch_array($res))
					{ sql_query("DELETE FROM marked_topic WHERE tid = $tid and uid =$uid") or sqlerr(__FILE__,__LINE__);}}}
     if (isset($_GET['page']))
        {  $page = 0+$_GET['page'];}
     else
        {$page=1;} 
topiclist($page);
stdfoot();
?>