<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path("jc_bet.php","",""));
loggedinorreturn();
updatestate();
function bark($msg)
{
    global $lang_jc_bet;
    stdmsg($lang_jc_bet['sorry'],$msg);
    exit();
}
function page($page_size,$action){
    global $CURUSER;
    global $lang_jc_bet;
    function diy_page(){
        print("<select name=page_size><option>5</option><option>10</option><option>15</option><option>20</option><option>25></option><option>30</option></select>");
        if(isset($_GET['page_size'])){
            $page_size=$_GET['page_size'];
        }}
        if(isset($_GET['page'])){
            $page=$_GET['page'];
        }
        else{
            $page=1;
        }
        switch($action){
            case "historical_bet":
                $res=sql_query("SELECT * FROM jc_subjects  WHERE state=3 OR state=5 ");
                $amount=mysql_num_rows($res);
                break;
            case "my_delivered_bet":
                $res=sql_query("SELECT * FROM jc_subjects WHERE `creater_id` = ".$CURUSER['id']);
                $amount=mysql_num_rows($res);
                break;
            case "my_bet":
                $res=sql_query("SELECT * FROM jc_record WHERE  user_id='{$CURUSER['id']}'");
                $amount=mysql_num_rows($res);
                break;
            default:

                $res=sql_query("SELECT * FROM jc_subjects WHERE state=2");
                $amount=mysql_num_rows($res);
        }
        //if(isset($_COOKIE['page_size'])){$page_size=$_COOKIE['page_size'];}

        if($amount>0){
            if($amount<=$page_size)
            {
                $page_amount=1;}
            else {
                $page_amount = $amount/$page_size;
                if($amount%$page_size)
                {
                    $page_amount=(int)($amount/$page_size+1);
                }
                else{$page_amount=$amount/$page_size;}
            }}
        $page_string="";
        if(isset($_GET['sort']) && isset($_GET['type'])){
            $add="&sort=".$_GET['sort']."&type=".$_GET['type'];
        }else{
            $add="";
        }

        if($page_amount==1){
            print("");
        }else if($amount==0){
            stdmsg("不好意思","暂时没有竞猜");
            stdfoot();
            exit();

        }
        else{
            if((!isset($page))||($page==1)){
                $page_string.="<a href=\"?action=".$action."&page=1".$add."\">|".$lang_jc_bet['page_1']."|</a><a href=\"?action=".$action."&page=".($page+1).$add."\">|".$lang_jc_bet['next_page']."|</a><a>...</a><a>|".$lang_jc_bet['previous_page']."|</a><a href=\"?action=".$action."&page=".$page_amount.$add."\">|".$lang_jc_bet['last_page']."|</a>";
            }
            else if($page==$page_amount){
                $page_string.="<a href=\"?action=".$action."&page=1".$add."\">|".$lang_jc_bet['page_1']."|</a><a>|".$lang_jc_bet['next_page']."|</a><a>...</a><a href=\"?action=".$action."&page=".($page-1).$add."\">|".$lang_jc_bet['previous_page']."|</a><a href=\"?action=".$action."&page=".$page_amount.$add."\">|".$lang_jc_bet['last_page']."|</a>";
            }else{
                $page_string.="<a href=\"?action=".$action."&page=1".$add."\">|".$lang_jc_bet['page_1']."|</a><a href=\"?action=".$action."&page=".($page+1).$add."\">|".$lang_jc_bet['next_page']."|</a><a>...</a><a href=\"?action=".$action."&page=".($page-1).$add."\">|".$lang_jc_bet['previous_page']."|</a><a href=\"?action=".$action."&page=".$page_amount.$add."\">|".$lang_jc_bet['last_page']."|</a>";
            }}
        print("<div style='float:left'><ul style=\"list-style-type:none;padding-left:0px ;\"><li style=\"float:left;color:#7A7A7A;font-weight:bold;padding:0px;margin:10px 5px 0px 10px\">".$page_string."</li><li style=\"float:left;padding:0px;margin:10px 5px 0px 9px\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$lang_jc_bet['current_page'].$page.$lang_jc_bet['total_page'].$page_amount.$lang_jc_bet['page']."</li>");
        //        diy_page();
        print("</ul></div>");
        if($action=="my_bet"){

            global $CURUSER;
            $res_option=sql_query("SELECT * FROM jc_record WHERE `user_id` = ".$CURUSER['id']);
            $ying_kui=0;$total=0;
            while($row_option=mysql_fetch_assoc($res_option))
            {
                $ying_kui+=$row_option['yin_kui'];
                $total+=$row_option['user_total'];
            }


            print "<div style='list-style-type:none;float:right; width:400px;margin-right:20px;font-weight:bold;font-family:\"\"' ><li style='float:right'>总投注".$total."·总盈利".$ying_kui."</li></div>";
        }
        $res_valid=sql_query("SELECT * FROM jc_sub_users WHERE user_id = ".sqlesc($CURUSER['id']));
        if($action=="my_delivered_bet" &&  mysql_num_rows($res_valid)==1)
        {
            $valid_row=mysql_fetch_assoc($res_valid);
            $last_modify_time=date("Y-m-d");
            if(strtotime($valid_row['last_modify_time'])!=strtotime($last_modify_time)){
                sql_query("UPDATE jc_sub_users SET deliver_count = 0 WHERE user_id=".sqlesc($CURUSER['id']));
            }
            print "<div style='list-style-type:none;float:right; width:400px;margin-right:20px;font-weight:bold;font-family:\"\"' ><li style='float:right'>"."今天发布".$valid_row['deliver_count']."条，共发布".$valid_row['total_deliver']."条</li></div>";
        }
        $tep=array();
        $tep[]=$page;
        $tep[]=$amount;
        return $tep;
}

function pagination($pagerlink,$orderby,$action,$my_bet="false",$start="false",$bet_state,$page_size){
    global $lang_jc_bet;
    if($bet_state==3){$bet_state_temp=5;}else{$bet_state_temp=0;}
    if($action=="my_delivered_bet")
    {
        global $CURUSER;
        $tep=page($page_size,$action);
        $page=$tep[0];
        if($tep[1]>0)
            print_table_title($action,$pagerlink);
        $res=sql_query("SELECT * FROM jc_subjects WHERE `creater_id` = ".$CURUSER['id']." ".$orderby." LIMIT ".($page-1)*$page_size.",".$page_size);
        while($row=mysql_fetch_assoc($res))
        {
            print("<tr style=\"text-align:center\"><td><img src=\"logo/logo_".$row['type'].".png\" width=40 height=40 alt=\"".$lang_jc_bet["bet_type_".$row[type]]."\" title=\"".$lang_jc_bet["bet_type_".$row[type]]."\"/></td><td class=\"jc_heading\"><a href=\"".$SERVER['SCRIPT_NAME']."?action=display&subid=".$row['id']."\" title=\"".$row['description']."\">".$row['subject']."</a></td>");
            print("<td>".date('Y-m-d H:i',strtotime($row['start']))."</td>");
            print("<td>".date('Y-m-d H:i',strtotime($row['end']))."</td><td>".$row['players']."</td><td>".$row['total']."</td>");
            $bonus=10+round((((-250000)/($row['players']+500))+500)/3,2);
            print("<td>".$bonus."</td><td>".$lang_jc_bet["bet_state_".$row['state']]."</td></tr>");
        }
        print("</table>");
    }else if($my_bet){
        global $CURUSER;
        $tep=page($page_size,$action);
        $page=$tep[0];
        if($tep[1]>0)
            print_table_title($action,$pagerlink);
        $res_n=sql_query("SELECT * FROM jc_record LEFT JOIN jc_subjects ON jc_record.subject_id = jc_subjects.id WHERE jc_record.user_id = ".$CURUSER['id'].$orderby." LIMIT ".($page-1)*$page_size.",".$page_size);
        while($row_n=mysql_fetch_assoc($res_n)){

            // $res_record=sql_query("SELECT * FROM jc_record WHERE user_id={$CURUSER['id']} ORDER BY last_time DESC LIMIT ".($page-1)*$page_size.",".$page_size);
            // while($row_record=mysql_fetch_assoc($res_record)){
            //    $res_temp=sql_query("SELECT * FROM jc_subjects WHERE id={$row_record['subject_id']}");
            //   $row_temp=mysql_fetch_assoc($res_temp);
            print("<tr style=\"text-align:center\"><td><img src=\"logo/logo_".$row_n['type'].".png\" width=40 height=40 alt=\"".$langjc_bet["bet_type_".$row_n[type]]."\" title=\"".$lang_jc_bet["bet_type_".$row_n[type]]."\"/></td><td class=\"jc_heading\"><a href=\"jc_details.php?subid=".$row_n['id']."\" title=\"".$row_n['description']."\">".$row_n['subject']."</a></td>");
            print("<td>".date('Y-m-d H:i',strtotime($row_n['start']))."</td>");
            print("<td>".date('Y-m-d H:i',strtotime($row_n['end']))."</td><td>".$row_n['limit']."</td><td>".$row_n['players']."</td><td>".$row_n['total']."</td>");
            print("<td>".$row_n['user_total']."</td><td>".$row_n['yin_kui']."</td><td>".$lang_jc_bet["bet_state_".$row_n['state']]."</td></tr>");
        }


        //die();
        }
        else{
            $tep=page($page_size,$action);
            $page=$tep[0];
            if($tep[1]>0)
                print_table_title($action,$pagerlink);
            print("<tbody>");
            $res=sql_query("SELECT * FROM jc_subjects WHERE state=$bet_state OR state=$bet_state_temp ".$orderby." LIMIT ".($page-1)*$page_size.",".$page_size);
            while($row=mysql_fetch_assoc($res)){
                print("<tr style=\"text-align:center\"><td>"."<img src=\"logo/logo_".$row['type'].".png\" width=\"40\" height=\"40\" alt=\"".$lang_jc_bet["bet_type_".$row[type]]."\" title=\"".$lang_jc_bet["bet_type_".$row[type]]."\"/></td><td class=\"jc_heading\">".  "<a  class=\"\" href=\"jc_details.php?subid=".$row['id']."\" title=\"".$row['description']."\">". $row[subject]."</a></td>");
                if($start){
                    print("<td width=\"135\">".date('Y-m-d H:i',strtotime($row['start']))."</td>");
                }
                print("<td width=\"135\">".date('Y-m-d H:i',strtotime($row['end']))."</td><td width=\"27\">".$row['limit']."</td><td width=\"27\">".$row['players']."</td><td  width=\"27\">".$row['total']."</td>");
                if($bet_state){
                    print("<td width=\"60\">".$lang_jc_bet["bet_state_".$row['state']]."</td></tr>");
                }
            }
            print("</tbody");
        }}

        function print_table_title($bet_type,$pagerlink){
            global $lang_jc_bet;
            print ("<tr><td align=center>");
            if(isset($_GET['page'])){
                $add="&page=".$_GET['page'];
            }else{
                $add="";
            }
            switch($bet_type){
                case "historical_bet":
                    print ("<table  id=\"tblSort\" width=940px style='table-layout:fixed;margin:0px,auto' border=1 cellspacing=0 cellpadding=5 ><thead><tr class=\"sub_colhead\" style=\"text-align:center\"><td class=\"heading\" width=\"52\" ><a href='jc_currentbet_L.php?action=historical_bet".$add."&sort=1&".$pagerlink."'>".
                            $lang_jc_bet['bet_class']."</a></td><td class=\"jc_heading\" width='200px'><a href='jc_currentbet_L.php?action=historical_bet".$add."&sort=2&".$pagerlink."'>".
                            $lang_jc_bet['bet_subject']."</a></td><td class=\"heading\" width=\"135\" ><a href='jc_currentbet_L.php?action=historical_bet".$add."&sort=3&".$pagerlink."'>".
                            $lang_jc_bet['bet_start']."</a></td><td class=\"heading\" width=\"135\"><a href='jc_currentbet_L.php?action=historical_bet".$add."&sort=4&".$pagerlink."'>".
                            $lang_jc_bet['bet_endtime']."</a></td><td class=\"heading\" width=\"100\"><a href='jc_currentbet_L.php?action=historical_bet".$add."&sort=8&".$pagerlink."'>".
                            $lang_jc_bet['bet_limit']."</a></td><td class=\"heading\" width=\"100\"><a href='jc_currentbet_L.php?action=historical_bet".$add."&sort=5&".$pagerlink."'>".
                            $lang_jc_bet['bet_players']."</a></td><td class=\"heading\" width=\"70\"><a href='jc_currentbet_L.php?action=historical_bet".$add."&sort=6&".$pagerlink."'>".
                            $lang_jc_bet['bet_current']."</a></td><td class=\"heading\" width=\"100\"><a href='jc_currentbet_L.php?action=historical_bet".$add."&sort=7&".$pagerlink."'>".
                            $lang_jc_bet['bet_state']."</a></td></tr></thead>"
                          );
                    break;
                case "my_bet":
                    print (
                            "<table width=940px style='table-layout:fixed' border=1 cellspacing=0 cellpadding=5 width=940><tr class=\"sub_colhead\" style=\"text-align:center\"><td class=\"heading\" width=\"52\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=1&".$pagerlink."'>".
                            $lang_jc_bet['bet_class']."</a></td><td class=\"jc_heading\" width='300px'><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=2&".$pagerlink."'>".
                            $lang_jc_bet['bet_subject']."</a></td><td class=\"heading\" width=\"135\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=3&".$pagerlink."'>".
                            $lang_jc_bet['bet_start']."</a></td><td class=\"heading\" width=\"135\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=4&".$pagerlink."'>".
                            $lang_jc_bet['bet_endtime']."</a></td><td class=\"heading\" width=\"27\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=8&".$pagerlink."'>".
                            $lang_jc_bet['bet_limit']."</a></td><td class=\"heading\" width=\"27\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=5&".$pagerlink."'>".
                            $lang_jc_bet['bet_players']."</a></td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=6&".$pagerlink."'>".
                            $lang_jc_bet['bet_current']."</a></td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=10&".$pagerlink."'>".
                            $lang_jc_bet['bet_mine']."</a></td><td class=\"heading\" width=\"50\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=9&".$pagerlink."'>".
                            $lang_jc_bet['bet_pandl']."</a></td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=my_bet".$add."&sort=7&".$pagerlink."'>".
                            $lang_jc_bet['bet_state']."</a></td></tr>");
                    break;
                case "my_delivered_bet":
                    print (
                            "<table width=940px style='table-layout:fixed' border=1 cellspacing=0 cellpadding=5 width=940><tr class=\"sub_colhead\" style=\"text-align:center\"><td class=\"heading\" width=\"50\"><a href='jc_currentbet_L.php?action=my_delivered_bet".$add."&sort=1&".$pagerlink."'>".
                            $lang_jc_bet['bet_class']."</a></td><td class=\"jc_heading\" width='300px'><a href='jc_currentbet_L.php?action=my_delivered_bet".$add."&sort=2&".$pagerlink."'>".
                            $lang_jc_bet['bet_subject']."</a></td><td class=\"heading\" width=\"135\"><a href='jc_currentbet_L.php?action=my_delivered_bet".$add."&sort=3&".$pagerlink."'>".
                            $lang_jc_bet['bet_start']."</a></td><td class=\"heading\" width=\"135\"><a href='jc_currentbet_L.php?action=my_delivered_bet".$add."&sort=4&".$pagerlink."'>".
                            $lang_jc_bet['bet_endtime']."</a></td><td class=\"heading\" width=\"27\"><a href='jc_currentbet_L.php?action=my_delivered_bet".$add."&sort=5&".$pagerlink."'>".
                            $lang_jc_bet['bet_players']."</a></td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=my_delivered_bet".$add."&sort=6&".$pagerlink."'>".
                            $lang_jc_bet['bet_current']."</a></td><td class=\"heading\" width=\"60\">".
                            $lang_jc_bet['bet_bonus']."</td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=my_delivered_bet".$add."&sort=7&".$pagerlink."'>".
                            $lang_jc_bet['bet_state']."</a></td></tr>"
                          );
                    break;
                case "cur_bet":
                    print ("<table width=940px style='table-layout:fixed' order=1 cellspacing=0 cellpadding=5 width=940><tr class=\"sub_colhead\" style=\"text-align:center\"><td class=\"heading\" width=\"40\"><a href='jc_currentbet_L.php?action=cur_bet".$add."&sort=1&".$pagerlink."'>".
                            $lang_jc_bet['bet_class']."</td><td class=\"jc_heading\" width='250px'><a href='jc_currentbet_L.php?action=cur_bet".$add."&sort=2&".$pagerlink."'>".
                            $lang_jc_bet['bet_subject']."</td><td class=\"heading\" width=\"135\"><a href='jc_currentbet_L.php?action=cur_bet".$add."&sort=4&".$pagerlink."'>".
                            $lang_jc_bet['bet_endtime']."</td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=cur_bet".$add."&sort=8&".$pagerlink."'>".
                            $lang_jc_bet['bet_limit']."</td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=cur_bet".$add."&sort=5&".$pagerlink."'>".
                            $lang_jc_bet['bet_players']."</td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=cur_bet".$add."&sort=6&".$pagerlink."'>".
                            $lang_jc_bet['bet_current']."</td><td class=\"heading\" width=\"60\"><a href='jc_currentbet_L.php?action=cur_bet".$add."&sort=7&".$pagerlink."'>".
                            $lang_jc_bet['bet_state']."</td></tr>"
                          );
                    break;
            }
            print ("</td></tr>");
        }
        //setcookie(page_size,2,time()+3600*24*30);

        $action=isset($_POST['action'])?htmlspecialchars($_POST['action']):(isset($_GET['action'])?htmlspecialchars($_GET['action']):"cur_bet");
        $allowedaction=array("historical_bet","my_bet","manage","cur_bet","my_delivered_bet","deliver_bet","edit","display","delete");
        global $lang_jc_bet;
        if(!in_array($action,$allowedaction)) {
            stderr($lang_jc_bet['std_err'], $lang_jc_bet['invaild_action']);
        }
        else {
            if($_GET['sort'] && $_GET['type']){
                $column = '';
                $ascdesc = '';

                switch($_GET['sort']){
                    case '1':$column = ' `type` ';break;
                    case '2':$column = ' `subject` ';break;
                    case '3':$column = ' `start` ';break;
                    case '4':$column = ' `end` ';break;
                    case '5':$column = ' `players` ';break;
                    case '6':$column = ' `total` ';break;
                    case '7':$column = ' jc_subjects.state ';break;
                    case '8':$column = ' `limit` ';break;
                    case '9':$column = ' `yin_kui` ';break;
                    case '10':$column = ' `user_total` ';break;
                }

                switch($_GET['type']){
                    case 'asc':$ascdesc = " ASC ";$linkascdesc= "desc";break;
                    case 'desc':$ascdesc = " DESC ";$linkascdesc = "asc";break;
                    default:$ascdesc = " DESC ";$linkascdesc = "asc";break;
                }

                if($column == "type")
                {
                    $orderby=" ORDER BY type ,start DESC ";
                }else {
                    $orderby=" ORDER BY  ".$column.$ascdesc.",jc_subjects.state ";
                }
                $pagerlink = "type=". $linkascdesc ;
            }else
            {
                $orderby = " ORDER BY jc_subjects.state ,end DESC";
                $pagerlink = "type=desc";
            }


            stdhead("竞猜大厅");
            switch ($action){
                case "historical_bet":
                    jc_usercpmenu("historical_bet");
                    //  print_table_title("historical_bet");
                    pagination($pagerlink,$orderby,$action,"","true","3","20");
                    print("<br /></table>");
                    break;
                case "my_bet":
                    jc_usercpmenu("my_bet");
                    // print_table_title("my_bet");
                    /*global $CURUSER;
                      $res=sql_query("SELECT * FROM jc_record WHERE `user_id` = ".$CURUSER['id']);
                      $ying_kui=0;$total=0;
                      while($row=mysql_fetch_assoc($res))
                      {
                      $ying_kui+=$row['yin_kui'];
                      $total+=$row['user_total'];
                      }

                      echo mysql_error();
                      print "<div style='list-style-type:none;float:right; width:400px;margin-right:20px;font-weight:bold;font-family:\"\"' ><li style='float:right'>总投注".$total."·总盈利".$ying_kui."</li></div>";*/
                    pagination($pagerlink,$orderby,$action,"true","","","20");
                    print("<br/ ></table>");
                    break;
                case "cur_bet":
                    jc_usercpmenu();
                    // print_table_title("cur_bet");
                    pagination($pagerlink,$orderby,$action,"","","2","20");
                    print("<br /></table>");
                    break;
                case "deliver_bet":
                    jc_usercpmenu("deliver_bet");

                    if(!isset($_POST['submit']))
                    {

                        $last_modify_time=date("Y-m-d");
                        $a=date("Y-m-d H:i:s",strtotime(date("Y-m-d 00:00:00",time())));
                        $res=sql_query("SELECT * FROM jc_subjects WHERE `creater_id` =  ".$CURUSER['id']." AND  `create_time` > '".$a."'");

                        $valid_res=sql_query("SELECT * FROM jc_sub_users WHERE `user_id` = ".sqlesc($CURUSER['id']));
                        if(mysql_num_rows($valid_res)==1){
                            $valid_row=mysql_fetch_assoc($valid_res);

                            if(strtotime($valid_row['last_modify_time'])!=strtotime($last_modify_time)){
                                sql_query("UPDATE jc_sub_users SET deliver_count = 0 WHERE user_id=".sqlesc($CURUSER['id']));
                            }
                            if($valid_row['deliver_count']>=$valid_row['limit_deliver'])
                            {
                                bark("已达到每日发布上限");
                            }else{
                                print("<div style=\" height:20px;margin:10px auto 0px auto ; background-color:#C6E3C6\">");
                                print("您已经发布".$valid_row['deliver_count']."条竞猜，今日剩余发布竞猜数为".($valid_row['limit_deliver']-$valid_row['deliver_count'])."条");
                                print("</div>");
                            }
                        }else{
                            if(mysql_num_rows($res)!=0){
                                bark($lang_jc_bet['deliver_only_once_aday']);

                            }
                        }

                        if(isset($_POST['option_num'])){
                            if($_POST['option_num']=="")
                                bark($lang_jc_bet['missingof_optionnum']);
                            if(!is_numeric($_POST['option_num']))
                                bark($lang_jc_bet['number_only']);
                        }

                        $notice = "<h1 align=\"center\"></h1><table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\">
                            <font color=\"white\">" .$lang_jc_bet['addmanage_explain']. "</font></td></tr>";
                        global $CURUSER;
                        //$row = mysql_fetch_array(sql_query("SELECT * FROM users WHERE id = '{$CURUSER['id']}'"));
                        $current_time = date ( "Y-m-d H:i:s" );

                        $sc = <<<JA
                            <script type="text/javascript">
                            function zOpenInner(){
                        		var content = "<font color=red><h1>发布竞猜须知</h1></font><br/>";
                        		content = content + "<font color=white>1）请保证你所发的竞猜内容的正确性。<br/><br/>2）竞猜至少在比赛开始前15分钟结束，竞猜时间至少持续1天。<br/></br>3）提交竞猜后尚需审核，请在竞猜开始前至少一天提交竞猜。<br/><br/>4）普通用户每天允许成功发布一次竞猜，请珍惜机会。<br/><br/>5）请在提交理由中附上竞猜的相关链接，方便管理员审核。<br/><br/>6）如果不能提交竞猜，请使用非IE内核的浏览器操作。<br/><br/>7)  如有其它不明白之处，请先移步常见问题,仍未解决的直接联系管理员（站内）<br/><br/><br/>【声明】该细则解释权归北洋园PT管理组，有任何问题请<a href=\"sendmessage.php?receiver=31029\" class=\"altlink\" target=\"_blank\">联系管理员</a></font>";
                        		$('#lightbox').css({"zoom":"100%"});
								$('#lightbox').html(content);
								$('#curtain').fadeIn();
								$('#lightbox').fadeIn();	
                    		}

</script>
<style type="text/css">

</style>
JA;
print $sc;
print "<form method='post'>";
print ($notice);
tr($lang_jc_bet['creater_id'],$CURUSER['id'],1);
tr($lang_jc_bet['creater_name'],"<div style=\"list-style-type:none\"><li style=\"float:left\">".$CURUSER['username']."</li><li style=\"float:right; margin-right:20px\"><input type='button' onclick='javascript:zOpenInner();' style =\"height:30px;width:100px;color:#FF0000;\" value='".$lang_jc_bet['newbet_notice']."'></li></div>",1);
tr($lang_jc_bet['type_num'],"<form methed='post'><input type='text' name='option_num' value='".$_POST['option_num']."'><input type='submit' value='".$lang_jc_bet['submit_once']."'></form>".$lang_jc_bet['type_num_explain'],1);
tr($lang_jc_bet['jc_subject'],"<input type='text' style='width:500px' name='subject' value='".$_POST['subject']."'/><br />".$lang_jc_bet['description_explain'],1);
tr($lang_jc_bet['jc_description'],"<input type='text'style='width:500px' name='description' value='".$_POST['description']."'/><br />".$lang_jc_bet['jc_description_explain'],1);

print "<tr>";
print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">" .$lang_jc_bet['jc_option']. "</td>";
print "<td>";
print '<style type="text/css">ul {padding:0;margin:0;} </style>';
print "<div class=\"LJ\" style=\"width:350px; float:left;\">";
for($option_id = 1; $option_id <= $_POST['option_num']; $option_id++)
{
    print "<ul>";
    print $lang_jc_bet['option'].$option_id."<input type='text'style='width:300px' name='option" .$option_id."' value='".$_POST['option'.$option_id]."'/>";
    print "</ul>";
}
print "</div>";
print "<p style=\"float:left;\">" .$lang_jc_bet['jc_option_explain']. "</p>";
print "</td>";
print "</tr>";
tr($lang_jc_bet['jc_type'],"<select name='type' value='".$_POST['type']."'><option value='1'>football</option><option value='2'>basketball</option><option value='3'>tennis</option><option value='4'>tabletennis</option><option value='5'>others</option></select>".$lang_jc_bet['jc_type_explain'], 1 );
tr($lang_jc_bet['jc_limit'],"<input type='text' name='limit' value='".($_POST['limit']?$_POST['limit']:100)."'/>".$lang_jc_bet['jc_limit_explain'],1);
tr($lang_jc_bet['jc_start'],"<input type='text' id='time1' name='start' value='".(isset($_POST['start']) ? $_POST['start'] :date ( "Y-m-d H:i:s"))."'/>".$lang_jc_bet['time_explain'],1);
tr($lang_jc_bet['jc_end'],"<input type='text' id='time2' name='end' value='".(isset($_POST['end']) ? $_POST['end'] :date ( "Y-m-d H:i:s", time () + 24 * 3600 ))."'/>".$lang_jc_bet['time_explain'],1);
tr($lang_jc_bet['reason'],"<textarea rows=\"2\" cols=\"100\"  name='reason' value='".$_POST['reason']."'></textarea><br />".$lang_jc_bet['reason_explain'],1);
tr($lang_jc_bet['jc_submit'],"<input type='submit' name='submit' value='{$lang_jc_bet['submit']}'>".$lang_jc_bet['jc_submit_explain'],1);

print "<input type='hidden' name='local_option_num' value='{$_POST['option_num']}'>";

print "</table>" ;
print "</form>";
}

else
{
    $last_modify_time=date("Y-m-d");
    $a=date("Y-m-d H:i:s",strtotime(date("Y-m-d 00:00:00",time())));
    $res=sql_query("SELECT * FROM jc_subjects WHERE `creater_id` =  ".$CURUSER['id']." AND  `create_time` > '".$a."'");

    $valid_res=sql_query("SELECT * FROM jc_sub_users WHERE `user_id` = ".sqlesc($CURUSER['id']));
    if(mysql_num_rows($valid_res)==1){
        $valid_row=mysql_fetch_assoc($valid_res);
        if($valid_row['last_modify_time']!=$last_modify_time){
            sql_query("UPDATE jc_sub_users SET deliver_count=0 WHERE user_id=".sqlesc($CURUSER['id']));
        }


        if($valid_row['deliver_count']>=$valid_row['limit_deliver'])
        {
            bark("已达到每日发布上限");
        }
    }else{
        if(mysql_num_rows($res)!=0){
            bark($lang_jc_bet['deliver_only_once_aday']);

        }

    }

    while($row=mysql_fetch_assoc($res)){
        if($row['subject']==$_POST['subject']){
            bark("不允许发布相同竞猜");
        }
    }
    if($_POST['subject']=='' || $_POST['type']=='' || $_POST['limit']=='' ||  $_POST['start']=='' || $_POST['end']=='' || $_POST['option2']=='')
        bark($lang_jc_bet['fill_all']);
    $current_start=(time()+3600*24);
    $current_end=(time()+3600*24*2);
    if(strtotime($_POST['start'])<$current_start || strtotime($_POST['end'])<$current_end)
        bark($lang_jc_bet['time']);
    if(strtotime($_POST['start'])>strtotime($_POST['end']))
        bark($lang_jc_bet['time_error']);

    if(!is_numeric($_POST['limit']))
        bark($lang_jc_bet['number_only_limit']);
    if(strlen($_POST['subject']>150 || strlen($_POST['description'])>300))
        bark('Subtitle or description is too long,please try again.');
    $op=array();
    for($option_id =1; $option_id<=$_POST['local_option_num']; $option_id++)
    {
        $temp='option'.$option_id;
        $op[$option_id]=$_POST[$temp];
        if($op[$option_id]=='')
            bark($lang_jc_bet['fill_all']);
        if(strlen($op[$option_id])>60)
            bark('The Option is too long,please try again.');
    }
    sql_query("INSERT INTO jc_subjects (creater_id,creater_name,subject,description,`type`,`limit`,`start`,`end`,options,`state`,`remark`)  VALUES (".sqlesc($CURUSER['id']).",".sqlesc($CURUSER['username']).",".htmlspecialchars(sqlesc($_POST['subject'])).",".htmlspecialchars(sqlesc($_POST['description'])).",".sqlesc($_POST['type']).",".sqlesc($_POST['limit']).",".sqlesc($_POST['start']).",".sqlesc($_POST['end']).",".sqlesc($_POST['local_option_num']).",1,".htmlspecialchars(sqlesc($_POST['reason'])).")");
    $parent_id = mysql_insert_id();
    for($option_id =1; $option_id <= $_POST['local_option_num']; $option_id++){
        sql_query("INSERT INTO jc_options (parent_id,option_id,option_name) VALUES ('$parent_id','$option_id',".htmlspecialchars(sqlesc($op[$option_id])).")");
    }
    if($CURUSER['id']==$valid_row['user_id']){
        sql_query("UPDATE jc_sub_users SET deliver_count=deliver_count+1 ,total_deliver=total_deliver+1 ,last_modify_time=".sqlesc($last_modify_time)." WHERE user_id=".$CURUSER['id']);
    }

    stdmsg($lang_jc_bet['success_submit'],$lang_jc_bet['new_jc_success']."<a class=\"altlink\"  href=\"jc_currentbet_L.php?action=my_delivered_bet\">".$lang_jc_bet['here']."</a>".$lang_jc_bet['backto']);
}
break;
case 'my_delivered_bet':
jc_usercpmenu("my_delivered_bet");
pagination($pagerlink,$orderby,$action,"","","","10");
print("</table>");
break;
case 'display':

if(!mkglobal("subid"))
bark($lang_jc_bet['std_missing_form_data']);
if(!is_numeric($subid))
    bark("hey!Don't play any tricks on me!");

    mkglobal("subid");
    $subid=$subid+0;
    int_check($subid);

    $res=sql_query("SELECT * FROM jc_subjects WHERE id=".sqlesc($subid));
if(mysql_num_rows($res)==0)
    bark("The subject doesn't exist or has been deleted! Please get touch with the top-admin!");
    $row=mysql_fetch_array($res);
    if($row['creater_id']==$CURUSER['id']){
        $res_option=sql_query("SELECT * FROM jc_options WHERE `parent_id` = ".sqlesc($subid)." ORDER BY `option_id`");

        $creater_id=$row['creater_id'];
        $creater_name=$row['creater_name'];
        $subject=$row['subject'];
        $description=$row['description'];
        $type=$row['type'];
        $start=$row['start'];
        $end=$row['end'];
        $limit=$row['limit'];
        $state=$row['state'];
        $reason=$row['remark'];
        //print $reason;
        if($state=='1'){
            $disabled='';
        }else{
            $disabled=" disabled='disabled'";
        }
        $notice = "<h1 align=\"center\"><a href=jc_currentbet_L.php?action=my_delivered_bet>".$lang_jc_bet['subedit_text']."</a></h1><table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\"><font color=\"white\">".$lang_jc_bet['warning_text']."</font></td></tr>";
        //print("<table>");
        print($notice);
        tr($lang_jc_bet['id_text'], "<label>".$row['id']."</label>", 1);
        tr($lang_jc_bet['createrid_text'], $creater_id, 1);
        tr($lang_jc_bet['creatername_text'], $creater_name, 1);
        tr($lang_jc_bet['subject_text'], $subject, 1);
        tr($lang_jc_bet['description_text'], $description, 1);
        tr($lang_jc_bet['type_text'],$lang_jc_bet["bet_type_". $type], 1);
        tr($lang_jc_bet['start_text'],$start,1);
        tr($lang_jc_bet['end_text'],$end,1);
        tr($lang_jc_bet['limit_text'],$limit,1);
        print "<tr>";
        print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">".$lang_jc_bet['options_text']."</td>";
        print "<td>";
        print '<style type="text/css">ul {padding:0;margin:0;} </style>';
        print "<div class=\"zmh\" style=\"width:350px; float:left;\">";
        while($row_option=mysql_fetch_array($res_option))
        {
            print "<ul>";
            print $lang_jc_bet['option_label'].$row_option['option_id'].": &nbsp&nbsp ".$row_option['option_name'];
            print "</ul>";
        }
        print "</div>";
        print "</td>";
        print "</tr>";
        tr($lang_jc_bet['reason'],$reason,1);
        tr($lang_jc_bet['manipulation'],"<div><ul style=\"list-style-type:none\">
                <li style=\"float:left\"><form method='POST' action'".$_SERVER['SCRIPT_NAME']."'=><input type='hidden' name='subid' value='".$subid."'><input type='hidden' name='action' value='edit'/><input type='submit' value='".$lang_jc_bet['edit']."' ".$disabled."'/></form></li>
                <li style=\"float:left\"><form method='POST' action='".$_SERVER['SCRIPT_NAME']."'><input type='hidden' name='subid' value='".$subid."'><input type='hidden' name='action' value='delete'/><input type='submit' value='".$lang_jc_bet['delete']."' ".$disabled."'/></form></li>",1);
        print("</table");
        print("</td></tr></table>");}
        else{
            bark("无效操作");
        }
break;
case 'edit':

if(!isset($_POST['submit']))
{
    if(!mkglobal("subid"))
        bark($lang_jc_bet['std_missing_form_data']);
    if(!is_numeric($subid))
        bark("hey!Don't play any tricks on me!");

    mkglobal("subid");
    $subid=$subid+0;
    int_check($subid);

    $res=sql_query("SELECT * FROM jc_subjects WHERE id=".sqlesc($subid));
    if(mysql_num_rows($res)==0)
        bark("The subject doesn't exist or has been deleted! Please get touch with the top-admin!");
    $row=mysql_fetch_array($res);
    $res_option=sql_query("SELECT * FROM jc_options WHERE `parent_id` = ".sqlesc($subid)." ORDER BY `option_id`");

    $creater_id=$row['creater_id'];
    $creater_name=$row['creater_name'];
    $subject=$row['subject'];
    $description=$row['description'];
    $type=$row['type'];
    $start=$row['start'];
    $end=$row['end'];
    $limit=$row['limit'];
    $state=$row['state'];
    $reason=$row['remark'];
    $options=$row['options'];
    /*    if(isset($_POST['option_num'])){
          if($_POST['option_num']=="")
          bark($lang_jc_bet['missingof_optionnum']);
          if(!is_numeric($_POST['option_num']))
          bark($lang_jc_bet['number_only']);
          }*/

    $notice = "<h1 align=\"center\"></h1><table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\">
        <font color=\"white\">" .$lang_jc_bet['edit_explain']. "</font></td></tr>";
    global $CURUSER;
    //$row = mysql_fetch_array(sql_query("SELECT * FROM users WHERE id = '{$CURUSER['id']}'"));
    $current_time = date ( "Y-m-d H:i:s" );

    print "<form method='post'>";
    print ($notice);
    tr($lang_jc_bet['creater_id'],$CURUSER['id'],1);
    tr($lang_jc_bet['creater_name'],$CURUSER['username'],1);
    //    tr($lang_jc_bet['type_num'],"<form methed='post'><input type='text' name='option_num' value='".$options."'></form>",1);
    print "<input type='hidden' name='option_num' value='".$options.".'/>";
    tr($lang_jc_bet['jc_subject'],"<input type='text' style='width:500px' name='subject' value='".$subject."'/><br />".$lang_jc_bet['description_explain'],1);
    tr($lang_jc_bet['jc_description'],"<input type='text'style='width:500px' name='description' value='".$description."'/><br />".$lang_jc_bet['jc_description_explain'],1);

    print "<tr>";
    print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">" .$lang_jc_bet['jc_option']. "</td>";
    print "<td>";
    print '<style type="text/css">ul {padding:0;margin:0;} </style>';
    print "<div class=\"LJ\" style=\"width:350px; float:left;\">";
    while($row_options=mysql_fetch_assoc($res_option))
    {
        print "<ul>";
        print $lang_jc_bet['option'].$row_options['option_id']."<input type='text'style='width:300px' name='option" .$row_options['option_id']."' value='".$row_options['option_name']."'/>";
        print "</ul>";
    }
    print "</div>";
    print "<p style=\"float:left;\">" .$lang_jc_bet['jc_option_explain']. "</p>";
    print "</td>";
    print "</tr>";
    tr($lang_jc_bet['jc_type'],"<select name='type' value='".$type."'><option value='1'>football</option><option value='2'>basketball</option><option value='3'>tennis</option><option value='4'>tabletennis</option><option value='5'>others</option></select>".$lang_jc_bet['jc_type_explain'], 1 );
    tr($lang_jc_bet['jc_limit'],"<input type='text' name='limit' value='".$limit."'/>".$lang_jc_bet['jc_limit_explain'],1);
    tr($lang_jc_bet['jc_start'],"<input type='text' id='time1' name='start' value='".$start."'/>".$lang_jc_bet['time_explain'],1);
    tr($lang_jc_bet['jc_end'],"<input type='text' id='time2' name='end' value='".$end."'/>".$lang_jc_bet['time_explain'],1);
    tr($lang_jc_bet['reason'],"<input type='text' style='width:700px' name='reason' value='".$reason."'/><br />".$lang_jc_bet['reason_explain'],1);
    tr($lang_jc_bet['jc_submit'],"<input type='submit' name='submit' value='{$lang_jc_bet['submit']}'>".$lang_jc_bet['jc_submit_explain'],1);

    //   print "<input type='hidden' name='local_option_num' value='{$_POST['option_num']}'>";
    print "<input type='hidden' name='action' value='edit'/>";
    print "<input type='hidden' name='subid' value='".$subid."'/>";
    print "</table>" ;
    print "</form>";
}

else
{
    if(!is_numeric($_POST['limit']))
        bark($lang_jc_bet['number_only_limit']);
    if($_POST['subject']=='' || $_POST['type']=='' || $_POST['limit']=='' ||  $_POST['start']=='' || $_POST['end']=='')
        bark($lang_jc_bet['fill_all']);
    if(strlen($_POST['subject']>98 || strlen($_POST['description'])>195))
        bark('Subtitle or description is too long,please try again.');
    $op=array();
    for($option_id =1; $option_id<=$_POST['option_num']; $option_id++)
    {
        $temp='option'.$option_id;
        $op[$option_id]=$_POST[$temp];
        if($op[$option_id]=='')
            bark($lang_jc_bet['fill_all']);
        if(strlen($op[$option_id])>38)
            bark('The Option is too long,please try again.');
    }

    sql_query("UPDATE jc_subjects SET `subject` =".sqlesc($_POST['subject']).",`description` = ".sqlesc($_POST['description']).",`type` = ".sqlesc($_POST['type']).",`start` = ".sqlesc($_POST['start']).",`end`= ".sqlesc($_POST['end']).",`limit` = ".sqlesc($_POST['limit']).",`remark` = ".sqlesc($_POST['reason'])." WHERE `id`=".$_POST['subid']."" );
    $parent_id = mysql_insert_id();
    for($option_id =1; $option_id <= $_POST['option_num']; $option_id++)
        sql_query("UPDATE jc_options SET `option_name`= '".$op[$option_id]."' WHERE `parent_id` = ".$_POST['subid']." AND `option_id` = ".$option_id." ");

    stdmsg($lang_jc_bet['success_submit'],$lang_jc_bet['new_jc_success']."<a class=\"altlink\"  href=\"jc_currentbet_L.php?action=my_delivered_bet\">".$lang_jc_bet['here']."</a>".$lang_jc_bet['backto_submenu']);
}

break;
case 'delete':
if(!mkglobal("subid"))
bark($lang_jc_bet['std_missing_form_data']);
if(!is_numeric($subid))
    bark("Hey,Don't play any tricks on me!");
    $subid=$subid+0;
    int_check($subid);
    $res=sql_query("SELECT * FROM jc_subjects WHERE `id` = ".$subid);
    $be_sure=isset($_POST['be_sure']) ? $_POST['be_sure'] : false;
    if(!$be_sure){
        if(!mysql_fetch_assoc($res))
            bark("The subject you wanna manipulate doesn't exist now.Please contact with the admin!");
        stdmsg($lang_jc_bet['be_careful'],"<form method='POST' action='".$SERVER['SCRIPT_NAME']."'><input type='hidden' name='action' value='delete'/><input type='hidden' name='be_sure' value='1'/><input type='hidden' name='subid' value='".$subid."'/><input type='submit' name=submit' value='".$lang_jc_bet['sure_to_delete']."'/></form>");
    }else{
        if(!mysql_fetch_assoc($res))
            bark("The subject you wanna manipulate doesn't exist now.Please contact with the admin!");
        sql_query("DELETE FROM jc_subjects WHERE `id` = ".$subid);
        sql_query("DELETE FROM jc_options WHERE `parent_id` = ".$subid);
        $res_valid=sql_query("SELECT * FROM jc_sub_users WHERE user_id=".sqlesc($CURUSER['id']));
        if(mysql_num_rows($res_valid)==1)
        {
            sql_query("UPDATE jc_sub_users SET deliver_count=deliver_count-1,total_deliver=total_deliver-1 WHERE user_id=".sqlesc($CURUSER['id']));
        }
        stdmsg($lang_jc_bet['delete_success'],"<div><li><a href=jc_currentbet_L.php?action=my_delivered_bet>".$lang_jc_bet['return_to_previous_page']."</a></li></div>");
    }
break;
}
}


stdfoot();
?>
<script type="text/javascript">
$(document).ready(function(){
    	$("#time1").datetimepicker({dateFormat: "yy-mm-dd", showSecond: true, timeFormat:"hh:mm:ss", minDate: new Date()});
    	$("#time2").datetimepicker({dateFormat: "yy-mm-dd", showSecond: true, timeFormat:"hh:mm:ss", minDate: new Date(new Date().getTime() + 86400*1000)});            
        });
</script>
