<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
updatestate();
function bark($msg){
    global $lang_suboption;
    stdhead();
    stdmsg($lang_suboption['sorry'],$msg);
    stdfoot();
    exit;
}
global $CURUSER;
$action=isset($_POST['action']) ? $_POST['action'] : '';
$allowedactions=array('perfect','edit','delete','fuck');
if(get_user_class()<14&&$CURUSER[jc_manager]!='yes'){
    bark("The system has already recorded this hackering action.Please get touch with the admin as soon as possible");
}else {
if(!in_array($action,$allowedactions)){
    stderr($lang_suboption['std_err'],$lang_suboption['invalid_action']);
}else{
    switch($action){
        case 'perfect':
            if(!mkglobal("subid"))
                bark($lang_subopration['std_missing_form_data']);
            if(!is_numeric($subid))
                bark("Hey!Don not play any tricks on me!");
            $subid=$subid+0;
            int_check($subid);
            $be_sure=isset($_POST['be_sure']) ? $_POST['be_sure'] : false;
            $res=sql_query("SELECT * FROM jc_subjects WHERE `id`= ".sqlesc($subid)." AND `state` = 1");
            if(!$be_sure){
                if(!mysql_fetch_array($res))
                    bark("The subject you wanna manipulate doesn't exist now.Please contact with the top-admin!");
                stdhead();
                stdmsg($lang_suboption['be_careful'],"<form method='POST' action=".$_SERVER['SCRIPT_NAME']."><input type='hidden' name='action' value='perfect'/><input type='hidden' name='be_sure' value='1'/><input type='hidden' name='subid' value='".$subid."'/><input type='submit' name='submit' value=' ".$lang_suboption['be_sure']."'");
            }
            else{
               $row=mysql_fetch_assoc($res);
                if($row=='')
                    bark("The subject you wanna manipulate doesn't exist now.Please contact with the top-admin!");
                stdhead();
               sql_query("UPDATE jc_subjects SET `state`= \"4\" WHERE `id` = ".sqlesc($subid));
                sql_query("UPDATE users SET seedbonus=seedbonus+10 WHERE `id` = ".sqlesc($row['creater_id']));
                $mydate=getdate();
                $current_time = "$mydate[year]-$mydate[mon]-$mydate[mday] $mydate[hours]:$mydate[minutes]:$mydate[seconds]";
                $msg=$lang_suboption['congratulation'].$row['subject'].$lang_suboption['give_you_bonus'];
                sql_query("INSERT INTO messages(sender,receiver,added,subject,msg) VALUES ('0',".sqlesc($row['creater_id']).",".sqlesc($current_time).",".sqlesc($lang_suboption['thanks']).",".sqlesc($msg).")");
                stdmsg($lang_suboption['success'],"<div><li>".$lang_suboption['cheer']."</li>
                        <li><a class='link' href=jc_manage.php?action=check_manage>".$lang_suboption['return_to_examin']."</a></li>
                        <li><a class='link' href=jc_manage.php>".$lang_suboption['return_to_manage']."</a></li></div>"); 
            }
            break;
        case 'edit':
            if(!mkglobal("subid"))
                bark($lang_examin['std_missing_form_data']);
            if(!is_numeric($subid))
                bark("hey!Don't play any tricks on me!");

            $res = sql_query("SELECT * FROM jc_subjects WHERE id =".sqlesc($subid));
            $row = mysql_fetch_array($res);

            if($_POST['subject']=='' || $_POST['type']=='' ||  $_POST['start']=='' || $_POST['end']==''|| $_POST['limit']=='')
                bark($lang_suboption['std_missing_form_data']);
            $op=array();
            for($option_id =1; $option_id<=($row['options']+$_POST['add_rows']); $option_id++)

            {
                $temp='option'.$option_id;
                $op[$option_id]=$_POST[$temp];
                if($op[$option_id]=='')
                    bark($lang_suboption['std_missing_form_data']);
            }

            stdhead();
            for($option_id =1; $option_id<=$row['options']; $option_id++){
                sql_query("UPDATE jc_options SET option_name=".sqlesc($op[$option_id])." WHERE parent_id=".sqlesc($subid)." AND option_id=".sqlesc($option_id));
            }
            for($option_id =($row['options']+1);$option_id<=$_POST['row_option_row'];$option_id++){
                sql_query("INSERT INTO jc_options (`parent_id`,`option_id`,`option_name`) VALUES (".sqlesc($subid).",".sqlesc($option_id).",".sqlesc($op[$option_id]).")");
                print "<br />";
            }
            print("<form method='POST' action='jc_examin.php'><input type='hidden' name='action' value='display'/><input type='hidden' name='subid' value='".$subid."'/>");
            stdmsg($lang_suboption['modification_success'],"<input type='submit' value='点击此处返回之前页面（注：此次操作仅为修改，并未审核。'/></form>");
            sql_query("UPDATE jc_subjects SET `options`= ".sqlesc($_POST['row_option_row'])." ,subject =".sqlesc($_POST['subject']).",description=".sqlesc($_POST['description']).",type=".sqlesc($_POST['type']).",start=".sqlesc($_POST['start']).",end=".sqlesc($_POST['end']).",`limit`=".sqlesc($_POST['limit'])." WHERE id=".sqlesc($subid) );
            break;
        case 'delete':
            if(!mkglobal("subid"))
                bark($lang_subopration['std_missing_form_data']);
            if(!is_numeric($subid))
                bark("Hey!Don not play any tricks on me!");
            $subid=$subid+0;
            int_check($subid);
            $res=sql_query("SELECT * FROM jc_subjects WHERE `id` = ".sqlesc($subid));
            $row=mysql_fetch_assoc($res);
            $be_sure=isset($_POST['be_sure']) ? $_POST['be_sure'] : false;
            if(!$be_sure){
                if($row=="")
                    bark("The subject you wanna manipulate doesn't exist now.Please contact with the top-admin!");
                stdhead();
                stdmsg($lang_suboption['be_careful'],"<form method='POST' action=".$_SERVER['SCRIPT_NAME']."><input type='hidden' name='action' value='delete'/><input type='hidden' name='be_sure' value='1'/><input type='hidden' name='subid' value='".$subid."'./>说点什么吧：<textarea  style='margin:2px; height:78px; width:492px;' name='warning'>".$lang_suboption['warning']."</textarea><input type='submit' name='submit' value='".$lang_suboption['sure_to_delete']."'/></form>");
            }
            else{
                if($row=="")
                    bark("The subject you wanna manipulate doesn't exist now,Please contact with the top-admin!");
                stdhead();
                sql_query("DELETE FROM jc_subjects WHERE `id` = ".$subid);
                sql_query("DELETE FROM jc_options WHERE `parent_id` = ".$subid);
                $mydate=getdate();
                $current_time = "$mydate[year]-$mydate[mon]-$mydate[mday] $mydate[hours]:$mydate[minutes]:$mydate[seconds]";
                $msg=$lang_suboption['ai'].$row['subject'].$lang_suboption['attention'].$_POST['warning'];
                sql_query("INSERT INTO messages(sender,receiver,added,subject,msg) VALUES ('0',".sqlesc($row['creater_id']).",".sqlesc($current_time).",".sqlesc($lang_suboption['failed_deliver']).",".sqlesc($msg).")");
                $res_valid=sql_query("SELECT * FROM jc_sub_users WHERE user_id=".sqlesc($row['creater_id']));
                if(mysql_num_rows($res_valid)!=0){
                sql_query("UPDATE jc_sub_users SET deliver_count=deliver_count-1,total_deliver=total_deliver-1 WHERE user_id=".$row['creater_id']);
            }
                print("<br/>");
                stdmsg($lang_suboption['delete_success'],"<div><li>".$lang_suboption['cheer']."</li>
                        <li><a href=jc_manage.php?action=check_manage>".$lang_suboption['return_to_examin']."</a></li>
                        <li><a href=jc_manage.php>".$lang_suboption['return_to_manage']."</a></li></div>"); 
            }
            break;
    }
}}
?>


