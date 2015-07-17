<?php

require"include/bittorrent.php";
dbconn();
require_once(get_langfile_path("jc_bet.php","",""));
loggedinorreturn();

parked();



function bark($msg)
{
    stdmsg("对不起",$msg);
    stdfoot();
    exit();
}

function rank_ls($rank_sort="yin_kui",$page=1,$pagesize=35)
{
    global $CURUSER;   
    
    $all=mysql_num_rows(sql_query("SELECT * FROM jc_rank WHERE total_times>9"));
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
    paging($rank_sort,$page,$page_amount);
    find_my_rank($rank_sort);

   
              

        print ("<tr><td align=center>");
    
    print ("<table  id=\"tblSort\" width=940px style='table-layout:fixed;margin:0px,auto' border=1 cellspacing=0 cellpadding=5 ><thead><tr class=\"sub_colhead\" style=\"text-align:center\"><td class=\"heading\" width=\"52\" >英雄榜</td><td class=\"heading\" width=\"52\">用户名</td><td class=\"heading\" width=\"52\"><a href='?rank_sort=win_times'>凯旋/次</td></a><td class=\"heading\" width=\"52\"><a href='?rank_sort=lose_times'>铩羽/次</td></a><td class=\"heading\" width=\"52\"><a href='?rank_sort=total_times'  >征战/次</td></a><td class=\"heading\" width=\"52\"><a href='?rank_sort=win_percent' >胜率</td></a><td class=\"heading\" width=\"52\"><a href='?rank_sort=yin_kui'>总盈亏</td><td class=\"heading\" width=\"52\">江湖人称</td></tr></thead>");
    
    
    switch($rank_sort)
    {case  win_times:
    $res_2=sql_query("SELECT * FROM jc_rank WHERE total_times>9 ORDER BY  win_times DESC LIMIT"." ". ((sqlesc($page)-1)*sqlesc($pagesize)).",".sqlesc($pagesize));

        break;
    case lose_times:
        $res_2=sql_query("SELECT * FROM jc_rank WHERE total_times>9 ORDER BY lose_times ASC  LIMIT"." ". ((sqlesc($page)-1)*sqlesc($pagesize)).",".sqlesc($pagesize));


        break;
    case total_times:
        $res_2=sql_query("SELECT * FROM jc_rank WHERE total_times>9 ORDER BY total_times DESC LIMIT"." ". ((sqlesc($page)-1)*sqlesc($pagesize)).",".sqlesc($pagesize));


        break;
    case win_percent:
        $res_2=sql_query("SELECT * FROM jc_rank WHERE total_times>9 ORDER BY win_percent DESC LIMIT"." ". ((sqlesc($page)-1)*sqlesc($pagesize)).",".sqlesc($pagesize));

        break;
     
    case yin_kui:
       $res_2=sql_query("SELECT * FROM jc_rank WHERE total_times>9 ORDER BY yin_kui  DESC  LIMIT"." ". ((sqlesc($page)-1)*sqlesc($pagesize)).",".sqlesc($pagesize));


        break;
    default :
        $res_2=sql_query ("SELECT * FROM jc_rank WHERE total_times>9 ORDER BY  yin_kui  DESC LIMIT"." ".((sqlesc($page)-1)*sqlesc($pagesize)).",".sqlesc($pagesize));

    }

    
   
    
        $index_num = (($page-1)*$pagesize)+1;
    
     while( $res_a=mysql_fetch_array($res_2))
     {   
         print ("<tr>
        <td align=\"center\"  width=\"52\">". 
         
        $index_num."</td><td align=\"center\" width=\"52\">".
        get_username($res_a['user_id'])."</td><td align=\"center\"      width=\"52\">".
        $res_a['win_times']."</td><td  align=\"center\"    width=\"52\">".
        $res_a['lose_times']."</td><td  align=\"center\"   width=\"52\">".
        $res_a['total_times']."</td><td  align=\"center\"  width=\"52\">".
        $res_a['win_percent']."%</td><td  align=\"center\"  width=\"52\">".
        $res_a['yin_kui']."</td><td  align=\"center\"      width=\"52\">");
        
         
   
        switch($rank_sort)
        {
        case  yin_kui:
            $ho_n=$res_a['yin_kui'];
            $ho=find_honor($ho_n,'yin_kui');  
            break;
    case win_percent:
            $ho_n=$res_a['win_percent'];
            $ho=find_honor($ho_n,'win_percent');
            break;
    case win_times:
            $ho_n=$res_a['win_times'];
            $ho=find_honor($ho_n,'win_times');
            break;
    case total_times:
            $ho_n=$res_a['total_times'];
            $ho=find_honor($ho_n,'total_times');
            break;
    case lose_times:
            $ho_n=$res_a['lose_times'];
            $ho=find_honor($ho_n,'lose_times');
            break;
    default :
            $ho_n=$res_a['yin_kui'];
            $ho=find_honor($ho_n,'yin_kui');

                }
         print($ho."</td></tr>");
        $index_num++;
     }
   
    print "</table>";




}   //英雄榜列表函数结束



function paging($rank_sort,$page,$page_amount )
{   
    global $lang_jc_rank;
    if($page!=1&&$page!=$page_amount)
    {  
        //print("<div style=\"float:left\"");
        print("<a href=\"?rank_sort=".$rank_sort."&page=1\">|首页</a><a href=\"?rank_sort=".$rank_sort."&page=".($page+1)."\">|下页|</a><a>......</a><a href=\"?rank_sort=".$rank_sort."&page=".($page-1)."\">|上页</a><a href=\"?rank_sort=".$rank_sort."&page=".$page_amount."\">|尾页|</a>");
        //print("</div>");
    } 
    else if ($page==1 && $page_amount == 1)
    {
        //print("<div style=\"float:left\"");
        print("<a href=\"?rank_sort=".$rank_sort."&page=1\">|首页</a><a href=\"#\">|下页|</a><a>......</a><a href=\"#\">|上页|</a><a href=\"?rank_sort=".$rank_sort."&page=".$page_amount."\">|尾页|</a>");
        //print("</div>");
    }
    else if($page==1){
        print("<a href=\"?rank_sort=".$rank_sort."&page=1\">|首页</a><a href=\"?rank_sort=".$rank_sort."&page=".($page+1)."\">|下页|</a><a>......</a><a>|上页|</a><a href=\"?rank_sort=".$rank_sort."&page=".$page_amount."\">|尾页|</a>");
    }
    else 
    { 
        //print("<div style=\"float:left\"");
        print("<a href=\"?rank_sort=".$rank_sort."&page=1\">|首页</a><a>|下页|</a><a>......</a><a href=\"?rank_sort=".$rank_sort."&page=".($page-1). "\">|上页|</a><a href=\"?rank_sort=".$rank_sort."&page=".$page_amount."\">|尾页|</a>");

        // print("</div>");
    }

    print(" 当前".$page."页 共".$page_amount."页</br>"  );



} //分页页面选择函数结束


function  find_honor($honor_num,$type)
{
	$w=mysql_num_rows(sql_query("select id from jc_subjects"));
	if ($type=='yin_kui')
	{if ($honor_num >= 500000.0)
		return "赌神";
	elseif ($honor_num >= 100000.0&&$honor_num < 500000.0)
		return "赌圣";
	elseif ($honor_num >= 50000.0&&$honor_num < 100000.0)
		return "赌王";
	elseif ($honor_num >= 10000.0&&$honor_num < 50000.0)
		return "赌豪";
	elseif ($honor_num >= 5000.0&&$honor_num < 10000.0)
		return "赌侠";
	elseif ($honor_num >= 1000.0&&$honor_num < 5000.0)
		return "赌客";
	elseif ($honor_num >= 0.0&&$honor_num < 1000.0)
		return "赌徒";
	elseif ($honor_num >= -1000.0&&$honor_num < 0.0)
		return "怡情";
	elseif ($honor_num >= -10000.0&&$honor_num < -1000.0)
		return "伤身";
	elseif ($honor_num <= -10000.0)
		return "灰飞烟灭";}
	
	if ($type=='win_percent')
	{
		$honor_num=$honor_num/100;
		if ($honor_num >= 0.95)
		return "百战不殆";
	elseif ($honor_num >= 0.9&&$honor_num < 0.95)
		return "十战九胜";
	elseif ($honor_num >= 0.8&&$honor_num < 0.9)
		return "笑傲群雄";
	elseif ($honor_num >= 0.7&&$honor_num < 0.8)
		return "小胜一时";
	elseif ($honor_num >= 0.5&&$honor_num < 0.7)
		return "未见输赢";
	elseif ($honor_num >= 0.3&&$honor_num < 0.5)
		return "略输一筹";
	elseif ($honor_num >= 0.2&&$honor_num < 0.3)
		return "久赌必输";
	elseif ($honor_num >= 0.1&&$honor_num < 0.2)
		return "元气大伤";
	elseif ($honor_num <= 0.1)
		return "灰飞烟灭";}
		
	if ($type=='win_times')
	{$s=0.5*$w;
		if ($honor_num >= $s)
		return "常胜将军";
	elseif ($honor_num >= 0.8*$s&&$honor_num < $s)
		return "所向披靡";
	elseif ($honor_num >= 0.6*$s&&$honor_num < 0.8*$s)
		return "驰骋赌场";
	elseif ($honor_num >= 0.4*$s&&$honor_num < 0.6*$s)
		return "赌绩卓著";
	elseif ($honor_num >= 0.3*$s&&$honor_num < 0.4*$s)
		return "赌坛英杰";
	elseif ($honor_num >= 0.2*$s&&$honor_num < 0.3*$s)
		return "一代赌客";
	elseif ($honor_num >= 0.15*$s&&$honor_num < 0.2*$s)
		return "偶露锋芒";
	elseif ($honor_num >= 0.1*$s&&$honor_num < 0.15*$s)
		return "初出茅庐";
	elseif ($honor_num <= 0.1*$s)
		return "不见经传";}
		
	if ($type=='lose_times')
	{$s=0.5*$w;
		if ($honor_num >= $s)
		return "灰飞烟灭";
	elseif ($honor_num >= 0.8*$s&&$honor_num < $s)
		return "虽败犹荣";
	elseif ($honor_num >= 0.6*$s&&$honor_num < 0.8*$s)
		return "兵败山倒";
	elseif ($honor_num >= 0.4*$s&&$honor_num < 0.6*$s)
		return "败绩斑斑";
	elseif ($honor_num >= 0.2*$s&&$honor_num < 0.4*$s)
		return "丢盔卸甲";
	elseif ($honor_num >= 0.1*$s&&$honor_num < 0.2*$s)
		return "人有失手";
	elseif ($honor_num >= 0.05*$s&&$honor_num < 0.1*$s)
		return "鲜有败绩";
	elseif ($honor_num >= 0.01*$s&&$honor_num < 0.05*$s)
		return "百战不殆";
	elseif ($honor_num <= 0.01*$s)
		return "独孤求败";}
		
	if ($type=='total_times')
	{$s=$w;
		if ($honor_num >= $s)
		return "赌场神话";
	elseif ($honor_num >= 0.8*$s&&$honor_num < $s)
		return "赌坛老将";
	elseif ($honor_num >= 0.6*$s&&$honor_num < 0.8*$s)
		return "征战多年";
	elseif ($honor_num >= 0.5*$s&&$honor_num < 0.6*$s)
		return "每日一赌";
	elseif ($honor_num >= 0.4*$s&&$honor_num < 0.5*$s)
		return "经验丰富";
	elseif ($honor_num >= 0.3*$s&&$honor_num < 0.4*$s)
		return "赌坛熟客";
	elseif ($honor_num >= 0.2*$s&&$honor_num < 0.3*$s)
		return "赌坛新手";
	elseif ($honor_num >= 0.1*$s&&$honor_num < 0.2*$s)
		return "偶然为之";
	elseif ($honor_num <= 0.1*$s)
		return "戒撸戒赌";}
	}


function find_my_rank($rank_sort)
{   
     global $lang_jc_rank;
     global $CURUSER;   



     $curu_s=sql_query("SELECT * FROM jc_rank WHERE total_times>9 AND user_id=".sqlesc($CURUSER['id']));
     if(!mysql_num_rows($curu_s))
     {print('您的历史竞猜不足10个，还不能登上英雄榜，仍需努力哦！');}
     else
     {      
     $curu=mysql_fetch_array($curu_s);   
      
       switch($rank_sort)
       {
       case yin_kui:

         $yk=sql_query ("SELECT yin_kui FROM jc_rank  WHERE total_times>9 AND yin_kui>".sqlesc($curu['yin_kui']) );
         $yk_n=mysql_num_rows($yk);
       $rank=$yk_n+1;
       print("您的总盈亏为".$curu['yin_kui']." 排名第".$rank." 江湖人称：".find_honor($curu['yin_kui'],'yin_kui'));
       
       break;
       
       case win_percent:

       $wp=sql_query("SELECT win_percent FROM jc_rank   WHERE total_times>9 AND win_percent>".sqlesc($curu['win_percent']));  
       $wp_n=mysql_num_rows($wp); 
       $rank=$wp_n+1;
       print("您的胜率为".$curu['win_percent']." 排名第".$rank." 江湖人称：".find_honor($curu['win_percent'],'win_percent'));


       break;

       case win_times:

       $wt=sql_query("SELECT win_times FROM jc_rank  WHERE total_times>9 AND win_times>".sqlesc($curu['win_times']));  
       $wt_n=mysql_num_rows($wt); 
       $rank=$wt_n+1;
       print("您的凯旋次数为".$curu['win_times']." 排名第".$rank." 江湖人称：".find_honor($curu['win_times'],'win_times'));

       break;
   
       case total_times:

       $tt=sql_query("SELECT total_times  FROM jc_rank   WHERE total_times>9 AND total_times>".sqlesc($curu['totoal_times']));  
       $tt_n=mysql_num_rows($tt); 
       $rank=$tt_n+1;
       print("您的征战次数为".$curu['total_times']." 排名第".$rank." 江湖人称：".find_honor($curu['total_times'],'total_times'));

       break;

       case lose_times:

       $lt=sql_query("SELECT lose_times  FROM jc_rank   WHERE total_times>9 AND lose_times<".sqlesc($curu['lose_times']));  
       $lt_n=mysql_num_rows($lt); 
       $rank=$lt_n+1;
       print("您的铩羽次数为".$curu['lose_times']." 排名第".$rank." 江湖人称：".find_honor($curu['lose_times'],'lose_times'));

       break;

       }
      }
        


}




     if(isset($_GET['rank_sort']))
        { $rank_sort=$_GET['rank_sort'];}
     else
        {$rank_sort="yin_kui";}
     if (isset($_GET['page']))
        {  $page = 0+$_GET['page'];}
     else
        {$page=1;}   
    
 
    stdhead("竞猜英雄榜");
    jc_usercpmenu(rank); 
    rank_ls($rank_sort,$page);
    stdfoot();
    
?>







