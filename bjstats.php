<?php
//$_NO_COMPRESS = true; //== For pdq's improvements mods
ob_start("ob_gzhandler");
require_once "include/bittorrent.php";

dbconn(false);
loggedinorreturn();

//$lang = array_merge( load_language('global') );

if ($CURUSER['class'] < UC_USER)
{
        stderr("抱歉...", "你的等级必须高于USER.");
        exit;
}

function begin_table2($fullwidth = false, $padding = 5)
{
	$width = "";

	if ($fullwidth)
	$width .= " width=50%";
	return ("<table class=\"main".$width."\" border=\"1\" cellspacing=\"0\" cellpadding=\"".$padding."\">");
}

function end_table2()
{
	return ("</table>\n");
}


function begin_frame2($caption = "", $center = false, $padding = 10, $width="100%", $caption_center="left")
{
	$tdextra = "";

	if ($center)
	$tdextra .= " align=\"center\"";

	return(($caption ? "<h2 align=\"".$caption_center."\">".$caption."</h2>" : "") . "<table width=\"".$width."\" border=\"1\" cellspacing=\"0\" cellpadding=\"".$padding."\">" . "<tr><td class=\"text\" $tdextra>\n");

}

function end_frame2()
{
	return("</td></tr></table>\n");
}



function bjtable($res, $frame_caption)
{
        $htmlout='';
        $htmlout .= begin_frame2($frame_caption, true);
        $htmlout .= begin_table2();
        $htmlout .="<tr>
        <td class='colhead'>名次</td>
        <td class='colhead' align='left'>用户名</td>
        <td class='colhead' align='right'>赢局</td>
        <td class='colhead' align='right'>输局</td>
        <td class='colhead' align='right'>总局数</td>
        <td class='colhead' align='right'>胜率</td>
        <td class='colhead' align='right'>得失</td>
        </tr>";

        $num = 0;
        while ($a = mysql_fetch_assoc($res))
        {
                ++$num;
                //==Calculate Win %
                $win_perc = number_format(($a['wins'] / $a['games']) * 100, 1);
                //==Add a user's +/- statistic
                $plus_minus = 0.9*$a['wins'] - $a['losses'];
                if ($plus_minus >= 0)
                {
                $plus_minus = (0.9*$a['wins'] - $a['losses']) * 100;
                }
                else
                {
                        $plus_minus = "-";
                        $plus_minus .= ($a['losses'] - 0.9*$a['wins']) * 100;
                }
                
                $htmlout .="<tr><td>$num</td><td align='left'>".
                "<b><a href='userdetails.php?id=".$a['id']."'>".get_username($a["id"])."</a></b></td>".
                "<td align='right'>".number_format($a['wins'], 0)."</td>".
                "<td align='right'>".number_format($a['losses'], 0)."</td>".
                "<td align='right'>".number_format($a['games'], 0)."</td>".
                "<td align='right'>$win_perc</td>".
                "<td align='right'>".round($plus_minus)."</td>".
                "</tr>\n";
        }
        $htmlout .= end_table2();
        $htmlout .= end_frame2();
        return $htmlout;
}



     $cachefile = "cache/bjstats.txt";
    $cachetime = 60 * 30; // 30 minutes
   //$cachetime = 10 * 3;
$Cache->new_page('bjstats', $cachetime, true);
	if (!$Cache->get_page()){
	$Cache->add_whole_row();
     
$mingames = 100;
$HTMLOUT='';
$HTMLOUT .="<h1>21点游戏排行榜</h1>";
$arr = mysql_fetch_assoc(sql_query("SELECT sum(bjwins + bjlosses) AS games FROM users WHERE 1 ") )or sqlerr(__FILE__, __LINE__);
$game=$arr['games']/2;
$HTMLOUT .="<p>排名数据每30分钟更新一次，只有游戏局数至少为${mingames}的用户才会被收入榜单。</p>截至".date("Y-m-d H:i:s")."，系统共进行游戏".$game."局。<br/>(已删除用户数据将不纳入统计，出现\"半局\"不要惊讶。)";
$HTMLOUT .="<br />";
//==Most Games Played
$res = sql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games FROM users WHERE bjwins + bjlosses >= $mingames ORDER BY games DESC LIMIT 20") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "游戏次数排名","Users");
$HTMLOUT .="<br /><br />";
//==Most Games Played
//==Highest Win %
$res = sql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins / (bjwins + bjlosses) AS winperc FROM users WHERE bjwins + bjlosses >= $mingames ORDER BY winperc DESC LIMIT 20") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "胜率排名","Users");
$HTMLOUT .="<br /><br />";
//==Highest Win %
//==Most Credit Won
$res = sql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, 0.9*bjwins - bjlosses AS winnings FROM users WHERE bjwins + bjlosses >= $mingames ORDER BY winnings DESC LIMIT 20") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "赢家排名","Users");
$HTMLOUT .="<br /><br />";
//==Most Credit Won
//==Most Credit Lost
$res = sql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjlosses - 0.9*bjwins AS losings FROM users WHERE bjwins + bjlosses >= $mingames ORDER BY losings DESC LIMIT 20") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "输家排名","Users");
//==Most Credit Lost
$HTMLOUT .="<br /><b><div align=\"center\"><a href=\"blackjack.php\">返回游戏</a></div></b>";
$HTMLOUT .="<br />";
print  $HTMLOUT ;


	$Cache->end_whole_row();
	$Cache->cache_page();
	}
	
	
	stdhead('21点游戏排行榜'); 
	echo $Cache->next_row();
	stdfoot();
?>