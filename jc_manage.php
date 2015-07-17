<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
global $CURUSER;
//if (get_user_class() < UC_SYSOP)//class:15
if(get_user_class()<14&&$CURUSER[jc_manager]!='yes')
permissiondenied();

//$handle = mysql_connect("localhost","byr","byr123");
//mysql_select_db("nexusphp",$handle);
//mysql_set_charset('utf8');
updatestate();

//read all configuration files
require('config/allconfig.php');

function return_type_image($type)
{  
    global $lang_manage;
    switch($type)
    {
        case 1:
            return "<img width='40' height='40' alt='Football' src='logo/logo_1.png' title='{$lang_manage['football']}'>";
            break;
        case 2:
            return "<img width='40' height='40' alt='Basketball' src='logo/logo_2.png' title='{$lang_manage['basketball']}'>";
            break;
        case 3:
            return "<img width='40' height='40' alt='Tennis' src='logo/logo_3.png' title='{$lang_manage['tennis']}'>";
            break;
        case 4:
            return "<img width='40' height='40' alt='Tabletennis' src='logo/logo_4.png' title='{$lang_manage['tabletennis']}'>";
            break;
        case 5:
            return  "<img width='40' height='40' alt='Others' src='logo/logo_5.png' title='{$lang_manage['others']}'>";
            break;
        default:
            return  "****";

    }
}

function return_subject_state($state)
{  
    global $lang_manage; 
    switch($state)
    {
        case 1:
            return $lang_manage['state1'];
            break;
        case 2:
            return $lang_manage['state2'];
            break;
        case 3:
            return $lang_manage['state3'];
            break;
        case 4:
            return $lang_manage['state4'];
            break;
        case 5:
            return $lang_manage['state5'];
            break;
        default:
            return "****";
    }
}

function paging($pagesize,$action)
{   
    global $lang_manage;

    if(isset($_POST['page']))
        $page = intval($_POST['page']);
    else
        $page = 1;       

    if($action =='subject_manage') 
        $sql = "SELECT * FROM jc_subjects";
    if($action =='answer_manage')
        $sql = "SELECT * FROM jc_subjects WHERE state=3";
    if($action == 'check_manage')
        $sql="SELECT * FROM jc_subjects WHERE `state` = \"1\"";

    $res = sql_query($sql);
    $amount = mysql_num_rows($res);
    if($amount)
    {     
        if($amount<$pagesize)
            $page_count=1;
        if($amount%$pagesize)
            $page_count=(int)($amount/$pagesize)+1;
        else
            $page_count=$amount/$pagesize;  
    }
    else
    {
        stdmsg("Sorry","No data now!");
        stdfoot();
        exit();
    }
    if($page==1 && $page_count!=1)
        $page_string= "<table style='border:0px'>"
            ."<tr>".$lang_manage['current_page'].$page.$lang_manage['total_page'].$page_count."</tr>"
            ."<tr><td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='submit' name='pageup' value='{$lang_manage['pageup']}' disabled/></form></td>"    
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='submit' name='firstpage' value='{$lang_manage['first']}' disabled/></form></td>" 
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='page' value='$page_count'/>
            <input type='hidden' name='action' value='$action'/><input type='submit' name='lastpage' value='{$lang_manage['last']}'/></form></td>"
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='page' value='".($page+1)."'/>
            <input type='hidden' name='action' value='$action'/><input type='submit' name='pagedown' value='{$lang_manage['pagedown']}'/></form></td></tr>"
            ."</table>";       

    else if($page==$page_count && $page_count!=1)
        $page_string= "<table style='border:0px'>"
            ."<tr>".$lang_manage['current_page'].$page.$lang_manage['total_page'].$page_count."</tr>"
            ."<tr><td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='page' value='".($page-1)."'/>
            <input type='hidden' name='action' value='$action'/><input type='submit' name='pageup' value='{$lang_manage['pageup']}'></form></td>"    
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='page' value='1'/>
            <input type='hidden' name='action' value='$action'/><input type='submit' name='firstpage' value='{$lang_manage['first']}'/></form></td>" 
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='submit' name='lastpage' value='{$lang_manage['last']}' disabled/></form></td>"
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='submit' name='pagedown' value='{$lang_manage['pagedown']}' disabled/></form></td></tr>"
            ."</table>";        

    else if($page==$page_count && $page_count==1)
        $page_string= "<table style='border:0px'>"
            ."<tr>".$lang_manage['current_page'].$page.$lang_manage['total_page'].$page_count."</tr>"
            ."<tr><td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='submit' name='pageup' value='{$lang_manage['pageup']}' disabled/></form></td>"    
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='submit' name='firstpage' value='{$lang_manage['first']}' disabled/></form></td>" 
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='submit' name='lastpage' value='{$lang_manage['last']}' disabled/></form></td>"
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='submit' name='pagedown' value='{$lang_manage['pagedown']}' disabled/></form></td></tr>"
            ."</table>";        


    else
        $page_string= "<table style='border:0px'>"
            ."<tr>".$lang_manage['current_page'].$page.$lang_manage['total_page'].$page_count."</tr>"
            ."<tr><td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='page' value='".($page-1)."'/>
            <input type='hidden' name='action' value='$action'/><input type='submit' name='pageup' value='{$lang_manage['pageup']}'></form></td>"    
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='page' value='1'/>
            <input type='hidden' name='action' value='$action'/><input type='submit' name='firstpage' value='{$lang_manage['first']}'></form></td>" 
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='page' value='$page_count'/>
            <input type='hidden' name='action' value='$action'/><input type='submit' name='lastpage' value='{$lang_manage['last']}'></form></td>"
            ."<td><form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='page' value='".($page+1)."'/>
            <input type='hidden' name='action' value='$action'/><input type='submit' name='pagedown' value='{$lang_manage['pagedown']}'/></form></td></tr>"
            ."</table>";       
    print $page_string;
    return $page;

}

function subjecttable($pagesize)
{   
    global $lang_manage;

    $currentpage=paging($pagesize,"subject_manage");
    //The head of subject table
    print "<table class=\"torrents\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">";
    print "<tr height=\"40px\">";
    print "<td class=\"colhead\" style=\"padding: 0px\" >".$lang_manage['jc_type']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\" >".$lang_manage['creater_name']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['jc_subject']."</td>";
    //print "<td class=\"colhead\" style=\"padding: 0px\">Start Time</td>";
    //print "<td class=\"colhead\" style=\"padding: 0px\">End Time</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['state_sub']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['delete_sub']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['edit_sub']."</td>";
    print "</tr>";

    $res="SELECT * FROM jc_subjects ORDER BY state LIMIT ".($currentpage-1)*$pagesize.",$pagesize";
    //print "SELECT * FROM jc_subjects ORDER BY state LIMIT ".($currentpage-1)*$pagesize.",$pagesize";

    $sql= sql_query($res);

    while ($row = mysql_fetch_array($sql))
    {

        print("<tr height=\"40px\">");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print(return_type_image($row["type"]));
        print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print($row["creater_name"]);
        print("</td>\n");

        print("<td class=\"heading\"  align=\"center\" valign=\"middle\" style='padding: 5px'>");
        print($row["subject"]);
        print("</td>\n");

        // print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>");
        // print($row["start"]);
        // print("</td>\n");

        // print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>");
        //print($row["end"]);
        // print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print(return_subject_state($row["state"]));
        print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print("<a href=\"jc_subdelete.php?subid=".$row['id']."\"  title='Delete this subject now'><img width='40' height='40' alt='Delete' src='logo/delete.png'></a>");
        print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print("<a href=\"jc_subedit.php?subid=".$row['id']."\" title='Edit this subject now'><img width='40' height='40' alt='Edit' src='logo/edit.png'></a>");
        print("</td>\n");

        print("</tr>");
    }
    print "</table>";
}

function answertable($pagesize)
{
    global $lang_manage; 
    $currentpage= paging($pagesize,"answer_manage");
    //The head of subject table
    print "<table class=\"torrents\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">";
    print "<tr height=\"40px\">";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['jc_type']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['creater_name']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['jc_subject']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['answer_setting']."</td>";
    print "</tr>";

    $res="SELECT * FROM jc_subjects WHERE state=3 ORDER BY id  LIMIT ".($currentpage-1)*$pagesize.",$pagesize";
    $sql = sql_query($res);
    while ($row = mysql_fetch_array($sql))
    {

        print("<tr height=\"40px\">");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>");
        print(return_type_image($row["type"]));
        print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>");
        print($row["creater_name"]);
        print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>");
        print($row["subject"]);
        print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>");
        print("<form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='action' value='answer_save'><input type='hidden' name='answer_sub_id' value='$row[id]'><input type='submit' value='{$lang_manage['answer_setting']}'></form>");
        print("</td>\n");

        print("</tr>");
    }
    print "</table>";
}

//count the profit of every user 
function distributebonus($subject_total,$option_total,$subject_id,$option_id)
{  
    global $lang_manage; 
    $res=sql_query("SELECT * FROM jc_record WHERE subject_id=".sqlesc($subject_id));
    $sub=sql_query("SELECT * FROM jc_subjects WHERE id=".sqlesc($subject_id));
    $subrow=mysql_fetch_array($sub);
    while($row=mysql_fetch_array($res))
    {   
        $mydate =getdate(); 
        $current_time = "$mydate[year]-$mydate[mon]-$mydate[mday] $mydate[hours]:$mydate[minutes]:$mydate[seconds]";

        if($row['option_id']==$option_id)
        {    
            $shouyi =$row['user_total']/$option_total*$subject_total;
            $add = round($row['user_total'] + ($shouyi - $row['user_total'])*0.9,1);
            $discount =round(($shouyi - $row['user_total'])*0.1,1);
            $profit =round($row['yin_kui']+$add,1);
            sql_query("UPDATE jc_record SET yin_kui=".sqlesc($profit).", state=2 WHERE subject_id=".sqlesc($subject_id)." AND user_id=".sqlesc($row['user_id']));
            sql_query("UPDATE users SET seedbonus=seedbonus+".sqlesc($add)."  WHERE id='".sqlesc($row['user_id'])."'");
            sql_query("UPDATE users SET seedbonus=seedbonus+".sqlesc($discount)." WHERE `id` = 24314");
            $msg = $lang_manage['congratulation'].$lang_manage['you']."[url=jc_details.php?subid=".$subrow[id]."]".$subrow['subject']."[/url]".$lang_manage['get'].$profit;
            // print $msg;
            sql_query("INSERT INTO messages(sender,receiver,added,subject,msg) VALUES ('0',".sqlesc($row['user_id']).",".sqlesc($current_time).",".sqlesc($lang_manage['pm_subject']).",".sqlesc($msg).")");
        }
        else
        { 
            sql_query("UPDATE jc_record SET state=1 WHERE subject_id=".sqlesc($subject_id)." AND user_id=".sqlesc($row['user_id']));
            $msg = $lang_manage['pity'].$lang_manage['you']."[url=jc_details.php?subid=".$subrow[id]."]".$subrow['subject']."[/url]".$lang_manage['lose'].abs($row['yin_kui']);
            // print $msg;
            sql_query("INSERT INTO messages(sender,receiver,added,subject,msg) VALUES ('0',".sqlesc($row['user_id']).",".sqlesc($current_time).",".sqlesc($lang_manage['pm_subject']).",".sqlesc($msg).")"); 
        }

    }
        
}

function bark($msg) 
{
    global $lang_manage;
    stdhead ();
    jc_usercpmenu(manage);
    stdmsg ($lang_manage['new_sub_failed'], $msg );
    stdfoot ();
    exit ();
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'showmenu');//should be showmenu;
$allowed_actions = array('showmenu','subject_manage','answer_manage','add_manage','answer_save','check_manage');
if (!in_array($action, $allowed_actions))
    $action = 'showmenu';
    
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
            case '9':$column = ' `creater_id` ';break;
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

    if ($action == 'showmenu')
{   
    $notice = "<br/><table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\">
        <font color=\"white\">".$lang_manage['mainmenu_explain']."</font></td></tr>";

    stdhead("竞猜管理");
    jc_usercpmenu(manage);
    print ($notice);
    tr($lang_manage['jc_manage'], "<form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='action' value='subject_manage'><input type='submit' value=\"".$lang_manage['jc_manage_begin']."\"> ".$lang_manage['jc_manage_explain']."</form>", 1);
    tr($lang_manage['set_answer'], "<form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='action' value='answer_manage'><input type='submit' value=\"".$lang_manage['set_answer']."\"> ".$lang_manage['set_answer_explain']."</form>", 1);
    tr($lang_manage['subcheck'], "<form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='action' value='check_manage'><input type='submit' value=\"".$lang_manage['subcheck']."\" >".$lang_manage['subcheck_explain']."</form>", 1);
    tr($lang_manage['newsubject'],"<form method='post' action='".$_SERVER["SCRIPT_NAME"]."'><input type='hidden' name='action' value='add_manage'><input type='submit' value=\"".$lang_manage['newsubject']."\">".$lang_manage['option_num']."<input type='text' name='option_num' value=2>".$lang_manage['newsubject_explain']."</form>", 1);
}

if ($action == 'subject_manage')
{
    stdhead("竞猜管理");
    jc_usercpmenu(manage);

    subjecttable(20);
}

if($action == 'answer_manage')
{
    stdhead("竞猜管理");
    jc_usercpmenu(manage);
    answertable(10);
    
}

if($action == 'answer_save')
{
    if(isset($_POST['save_answer']))
    {
        $right_answer = $_POST['right_answer'];
        $answer_sub_id = $_POST['answer_sub_id'];
        sql_query("UPDATE jc_subjects SET note=".sqlesc ( $_POST ["note"] )." WHERE id='$answer_sub_id '");
        sql_query("UPDATE jc_subjects SET win_options=".sqlesc($right_answer).",state=5 WHERE id=".sqlesc($answer_sub_id));
        //sql_query("UPDATE jc_subjects SET state=4 WHERE id=".sqlesc($answer_sub_id));

        //$row_subject = mysql_fetch_array(sql_query("SELECT * FROM jc_subjects WHERE id='$answer_sub_id'"));
        //$row_option = mysql_fetch_array(sql_query("SELECT * FROM jc_options WHERE parent_id='$answer_sub_id' AND option_id='$right_answer'"));
        $row_n = mysql_fetch_array(sql_query("select * from jc_options as A left join jc_subjects as B on A.parent_id=B.id where B.id='".sqlesc($answer_sub_id)."' AND option_id='".sqlesc($right_answer)."'"));
        distributebonus($row_n['total'],$row_n['option_total'],$answer_sub_id,$right_answer);
        $seed_add_bonus=10+round((((-250000)/($row_n['players']+500))+500)/3,2);
        sql_query("UPDATE users SET seedbonus=seedbonus+".($seed_add_bonus-10)." WHERE `id` = '{$row_n['creater_id']}'");
        $msg=$lang_manage['gongxi'].$seed_add_bonus.$lang_manage['struggle'];
        $mydate=getdate();
        $current_time = "$mydate[year]-$mydate[mon]-$mydate[mday] $mydate[hours]:$mydate[minutes]:$mydate[seconds]";
        sql_query("INSERT INTO messages(sender,receiver,added,subject,msg) VALUES ('0','{$row_n['creater_id']}',".sqlesc($current_time).",".sqlesc($lang_manage['thanks']).",".sqlesc($msg).")");
        stdhead("竞猜管理");
        jc_usercpmenu(manage);
        stdmsg($lang_manage['answersave_finish'],$lang_manage['click']."<a class=\"altlink\" href=\"jc_manage.php\">".$lang_manage['here']."</a>".$lang_manage['backto_menu']);
    }

    else
    {
        $answer_sub_id=$_POST['answer_sub_id'];
        $res_option = sql_query("SELECT * FROM jc_options WHERE parent_id='$answer_sub_id' ORDER BY option_id");
        $row = mysql_fetch_array(sql_query("SELECT * FROM jc_subjects WHERE id='$answer_sub_id'"));
        $notice = "<table cellspacing=\"0\" cellpadding=\"15\" width=\"800\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\"><font color=\"white\">".$lang_manage['besureof_answer']."</font></td></tr>";
        stdhead("竞猜管理");
        jc_usercpmenu(manage);

        print "<br><h2>设置答案需谨慎哦！</h2><table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><form method='post' action='" . $_SERVER ["SCRIPT_NAME"] . "'>";
        print "<input type='hidden' name='action' value='answer_save'>";
        print "<input type='hidden' name='answer_sub_id' value='$answer_sub_id'>";
        tr($lang_manage['jc_subject'],$row['subject'],1);

        print "<tr>";
        print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"\">".$lang_manage['set_answer']."</td>";
        print "<td>";
        print "<select name='right_answer'>";
        while($row_option=mysql_fetch_array($res_option))
        {
            $temp = $row_option['option_id'];
            print "<option value='$temp'>".$temp.":".$row_option['option_name']."</option>";
        }
        print "</select>";
        print "</td>";
        print "</tr>";
        print "<tr>";
        print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"\">".$lang_manage['note']."</td>";
        print "<td>";
        print "<textarea name='note' cols='90' rows='5'></textarea>";
        print "</td>";
        print "</tr>";
        tr($lang_manage['asure_answer'],"<input type='submit' name='save_answer' value='{$lang_manage['sure']}'/>",1);
        print("</table>");
        print "</form>";
    }
}//end of if answer_save

if($action == 'check_manage')
{

    global $lang_manage;

    $res="SELECT * FROM jc_subjects WHERE `state` = \"1\" ORDER BY `type` ,`start` DESC ";//LIMIT ".($currentpage-1)*$pagesize.",$pagesize";
    //print "SELECT * FROM jc_subjects ORDER BY state LIMIT ".($currentpage-1)*$pagesize.",$pagesize";

    $sql= sql_query($res); 
    if(!mysql_num_rows($sql)){
       bark($lang_manage['no_data']);
    }else{
    stdhead("竞猜管理");
    jc_usercpmenu(manage);
    //$currentpage=paging(10,"check_manage");
    //$pagesize=10;
    //The head of subject table
    print "<table class=\"torrents\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">";
    print "<tr height=\"40px\">";
    print "<td class=\"colhead\" style=\"padding: 0px\" >".$lang_manage['jc_type']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\" >".$lang_manage['creater_name']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['jc_subject']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['start_time']."</td>";
    //print "<td class=\"colhead\" style=\"padding: 0px\">End Time</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['state_sub']."</td>";
    //print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['delete_sub']."</td>";
    //print "<td class=\"colhead\" style=\"padding: 0px\">".$lang_manage['edit_sub']."</td>";
    print "<td class=\"colhead\" style=\"padding: 0px\">"."操作"."</td>";
    print "</tr>";
    while ($row = mysql_fetch_array($sql))
    {   

        print("<tr height=\"40px\">");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print(return_type_image($row["type"]));
        print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print($row["creater_name"]);
        print("</td>\n");

        print("<td class=\"heading\"  align=\"center\" valign=\"middle\" style='padding: 5px'>");
        print("<a href=jc_examin.php?subid=".$row['id'].">".$row["subject"]."</a>");
        print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>");
        print($row["start"]);
        print("</td>\n");

        // print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 0px'>");
        //print($row["end"]);
        // print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print(return_subject_state($row["state"]));
        print("</td>\n");

        //print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        //print("<a href=\"jc_subdelete.php?subid=".$row['id']."\"  title='Delete this subject now'><img width='40' height='40' alt='Delete' src='logo/delete.png'></a>");
        //print("</td>\n");

        //print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        //print("<a href=\"jc_subedit.php?subid=".$row['id']."\" title='Edit this subject now'><img width='40' height='40' alt='Edit' src='logo/edit.png'></a>");
        //print("</td>\n");

        print("<td class=\"rowfollow nowrap\" valign=\"middle\" style='padding: 5px'>");
        print("<a href=jc_examin.php?subid=".$row['id'].">审核</a></td>\n");
        print("</tr>");
    }
    print "</table>";
}}
if($action == 'add_manage')
{
    if(!isset($_POST['submit']))
    {

        if($_POST['option_num']=="")
            bark($lang_manage['missingof_optionnum']);	

        $notice = "<table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\">
            <font color=\"white\">" .$lang_manage['addmanage_explain']. "</font></td></tr>";
        global $CURUSER;
        //$row = mysql_fetch_array(sql_query("SELECT * FROM users WHERE id = '{$CURUSER['id']}'"));
        $mydate = getdate();
        $current_time = "$mydate[year]-$mydate[mon]-$mydate[mday] $mydate[hours]:$mydate[minutes]:$mydate[seconds]";

        stdhead("竞猜管理");
        jc_usercpmenu(manage);
        print "<form method='post' action='" . $_SERVER ["SCRIPT_NAME"] . "'>";
        print ($notice);
        //tr($lang_manage['creater_id'],"<input type='text' name='creater_id' value='{$CURUSER['id']}'/>".$lang_manage['creater_id_explain'], 1 );
        tr($lang_manage['creater_id'],$CURUSER['id'],1);
        //tr($lang_manage['creater_name'],"<input type='text' name='creater_name' value='{$row['username']}'>".$lang_manage['creater_name_explain'],1);
        tr($lang_manage['creater_name'],$CURUSER['username'],1);
        tr($lang_manage['jc_subject'],"<input type='text' style='width:500px' name='subject'/><br />".$lang_manage['jc_subject_explain'],1);
        tr($lang_manage['jc_description'],"<input type='text'style='width:500px' name='description'/><br />".$lang_manage['jc_description_explain'],1);

        print "<tr>";
        print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">" .$lang_manage['jc_option']. "</td>";
        print "<td>";
        print '<style type="text/css">ul {padding:0;margin:0;} </style>';
        print "<div class=\"LJ\" style=\"width:350px; float:left;\">";
        for($option_id = 1; $option_id <= $_POST['option_num']; $option_id++) 
        {
            print "<ul>";
            print $lang_manage['option'].$option_id."<input type='text'style='width:300px' name='option" .$option_id."'/>";
            print "</ul>";
        }
        print "</div>";
        print "<p style=\"float:left;\">" .$lang_manage['jc_option_explain']. "</p>";
        print "</td>";
        print "</tr>";

        tr($lang_manage['jc_type'],"<select name='type' value='".$_POST['type']."'><option value='1'>football</option><option value='2'>basketball</option><option value='3'>tennis</option><option value='4'>tabletennis</option><option value='5'>others</option></select>".$lang_manage['jc_type_explain'], 1 );
        tr($lang_manage['jc_limit'],"<input type='text' value=100 name='limit'>".$lang_manage['jc_limit_explain'],1);
        tr($lang_manage['jc_start'],"<input type='text' id='time1' name='start' value='$current_time'/>".$lang_manage['time_explain'],1);
        tr($lang_manage['jc_end'],"<input type='text' id='time2' name='end' value='$current_time'/>".$lang_manage['time_explain'],1);
        tr($lang_manage['jc_submit'],"<input type='submit' name='submit' value='{$lang_manage['submit']}'>".$lang_manage['jc_submit_explain'],1);
        print "<input type='hidden' name='local_option_num' value='{$_POST['option_num']}'>";
        print "<input type='hidden' name='action' value='add_manage'>";

        print "</table>" ;
        print "</form>";
    }

    else
    {
        if($_POST['subject']=='' || $_POST['type']=='' || $_POST['limit']=='' ||  $_POST['start']=='' || $_POST['end']=='')
            bark($lang_manage['fill_all']);
        if(strlen($_POST['subject']>150 || strlen($_POST['description'])>300))
            bark('Subtitle or description is too long,please try again.');
        $op=array();
        for($option_id =1; $option_id<=$_POST['local_option_num']; $option_id++)
        {
            $temp='option'.$option_id;
            $op[$option_id]=$_POST[$temp];
            if($op[$option_id]=='')
                bark($lang_manage['fill_all']);
            if(strlen($op[$option_id])>60)
                bark('The Option is too long,please try again.');
        }     
        stdhead("竞猜管理");
        jc_usercpmenu(manage);
        sql_query("INSERT INTO jc_subjects (state,creater_id,creater_name,subject,description,`type`,`limit`,`start`,`end`,options)  VALUES (4,".sqlesc($CURUSER['id']).",".sqlesc($CURUSER['username']).",".sqlesc($_POST['subject']).",".sqlesc($_POST['description']).",".sqlesc($_POST['type']).",".sqlesc($_POST['limit']).",".sqlesc($_POST['start']).",".sqlesc($_POST['end']).",".sqlesc($_POST['local_option_num']).")");
        $parent_id = mysql_insert_id();
        for($option_id =1; $option_id <= $_POST['local_option_num']; $option_id++)	    	
            sql_query("INSERT INTO jc_options (parent_id,option_id,option_name) VALUES (".sqlesc($parent_id).",".sqlesc($option_id).",".sqlesc($op[$option_id]).")");

        stdmsg($lang_manage['success_submit'],$lang_manage['new_jc_success'].$lang_manage['click']."<a class=\"altlink\"  href=\"jc_manage.php\">".$lang_manage['here']."</a>".$lang_manage['backto_menu']);
    }    
}//end of if add_manage

print "</table>";
stdfoot();
?>

<script type="text/javascript">
$(function(){
        $("#time1").datetimepicker({timeFormat: 'hh:mm:ss',showSecond:true,dateFormat:'yy-mm-dd'});
        $("#time2").datetimepicker({timeFormat: 'hh:mm:ss',showSecond:true,dateFormat:'yy-mm-dd'});
        });
</script>
