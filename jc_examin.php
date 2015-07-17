<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();

function bark($msg)
{
    global $lang_examin;
    stdhead();
    stdmsg($lang_subdelete['std_edit_failed'],$msg);
    stdfoot();
    exit;
}

updatestate();
$action=isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : "display");
$allowedaction=array("display","perfect","edit","delete");
if(!in_array($action,$allowedaction)){
    stderr($lang_jc_bet['std_err'],$lang_jc_bet['invalid_action']);
}else{
    switch($action){
        case 'display':

            if(!mkglobal("subid"))
                bark($lang_examin['std_missing_form_data']);
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
            $reason=$row['remark'];

            $notice = "<table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\"><font color=\"white\">".$lang_examin['warning_text']."</font></td></tr>";
            stdhead("竞猜管理");
        		jc_usercpmenu(manage);
            print($notice);
            tr($lang_examin['id_text'], "<label>".$row['id']."</label>", 1);
            tr($lang_examin['createrid_text'], $creater_id, 1);
            tr($lang_examin['creatername_text'], $creater_name, 1);
            tr($lang_examin['subject_text'], $subject, 1);
            tr($lang_examin['description_text'], $description, 1);
            tr($lang_examin['type_text'],$lang_examin["bet_type_". $type], 1);
            tr($lang_examin['start_text'],$start,1);
            tr($lang_examin['end_text'],$end,1);
            tr($lang_examin['limit_text'],$limit,1);
            print "<tr>";
            print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">".$lang_examin['options_text']."</td>";
            print "<td>";
            print '<style type="text/css">ul {padding:0;margin:0;} </style>';
            print "<div class=\"zmh\" style=\"width:350px; float:left;\">";
            while($row_option=mysql_fetch_array($res_option))
            {
                print "<ul>";
                print $lang_examin['option_label'].$row_option['option_id'].": &nbsp&nbsp ".$row_option['option_name'];
                print "</ul>";
            }
            print "</div>";
            print "</td>";
            print "</tr>";
            tr($lang_examin['reason'],$reason,1);
            tr($lang_examin['manipulation'],"<div><ul style=\"list-style-type:none\"><li style=\"float:left\"><form method='POST' action='jc_suboption.php'><input type='hidden' name='subid' value='".$subid."'><input type='hidden' name='action' value='perfect'/><input type='submit' value='".$lang_examin['perfect']."'/></form></li>
                    <li style=\"float:left\"><form method='POST' action'".$_SERVER['SCRIPT_NAME']."'=><input type='hidden' name='subid' value='".$subid."'><input type='hidden' name='action' value='edit'/><input type='submit' value='".$lang_examin['edit']."'/></form></li>
                    <li style=\"float:left\"><form method='POST' action='jc_suboption.php'><input type='hidden' name='subid' value='".$subid."'><input type='hidden' name='action' value='delete'/><input type='submit' value='".$lang_examin['delete']."'/></form></li>",1); 
            break;
        case 'edit':
            echo $subid;
            if(!mkglobal("subid"))
                bark($lang_examin['std_missing_form_data']);
            if(!is_numeric($subid))
                bark("Hey! Don't play any tricks on me! ");

            $subid = $subid+0;
            int_check($subid);

            $res = sql_query("SELECT * FROM jc_subjects WHERE id =".sqlesc($subid));
            if(mysql_num_rows($res)==0)
                bark("The subject doesn't exist or has been deleted! Please get touch with the administrator!");
            $row = mysql_fetch_array($res);
            $res_option = sql_query("SELECT * FROM jc_options WHERE parent_id =".sqlesc($subid)." ORDER BY option_id");
            $creater_id = $row['creater_id'];
            $creater_name = $row['creater_name'];
            $subject = $row['subject'];
            $description = $row['description'];
            $start = $row['start'];
            $end = $row['end'];
            $limit =$row['limit'];
            $reason=$row['remark'];

            $notice = "<h1 align=\"center\"><a class=\"faqlink\" href=\"jc_manage.php?action=check_manage\">".$lang_examin['subedit_text']."</a></h1><table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\">
                <font color=\"white\">".$lang_examin['warning_text2']."</font></td></tr>";

            stdhead();
            print "<form method='post' action='jc_suboption.php'>";
            print "<input type='hidden' name='action' value='edit'/><input type='hidden' name='subid' value='".$subid."'/>";
            print($notice);
            print "<input type='hidden' name='edit_sub_id' value={$row['id']}>";
            tr($lang_examin['id_text'], "<label>".$row['id']."</label>", 1);
            tr($lang_examin['createrid_text'], $creater_id, 1);
            tr($lang_examin['creatername_text'], $creater_name, 1);
            tr($lang_examin['subject_text'], "<input type='text'style='width:500px;' name='subject' value='$subject'/><br />".$lang_examin['subject_explain'], 1);
            tr($lang_examin['description_text'], "<input type='text' style='width:500px' name='description' value='$description'/><br />".$lang_examin['description_explain'], 1);
            tr($lang_examin['type_text'], "<select name='type' id='default'><option value='1'>football</option><option value='2'>basketball</option><option value='3'>car</option><option value='4'>esport</option><option value='5'>tennis</option><option value='6'>tabletennis</option><option value='7'>Olimpics</option><option value='8'>others</option></select>", 1);
            tr($lang_examin['start_text'],"<input type='text' id='time1' name='start' value='$start'>".$lang_examin['start_explain'],1);
            tr($lang_examin['end_text'],"<input type='text' id='time2'  name='end' value='$end'>".$lang_examin['end_explain'],1);
            tr($lang_examin['limit_text'],"<input type='text' name='limit' value='$limit'/>".$lang_examin['limit_explain'],1);
            print "<tr>";
            print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">".$lang_examin['options_text']."</td>";
            print "<td>";
            print '<style type="text/css">ul {padding:0;margin:0;} </style>';
            print "<div class=\"zmh\" style=\"width:350px; float:left;\">";
            print "<ul>增加选项数目<input id='add_rows' type='text'  name='add_rows'/>
            <input type='button' value='".$lang_examin['click_once']."'
            onclick=\"javascript:location.href='jc_examin.php?subid=".$subid."&action=edit&add_rows='+getElementById('add_rows').value;\" /></ul>";
            while($row_option=mysql_fetch_array($res_option))
            {
                print "<ul>";
                print $lang_examin['option_label'].$row_option['option_id']."<input type='text'style='width:300px' name='option" .$row_option['option_id']."'  value='".$row_option['option_name']."'/>";
                print "</ul>";
            }
            $row_option_row=$row['options'];
            for($new_row=$row['options'];$new_row<($row['options']+$_GET['add_rows']);$new_row++)
            {
                print "<ul>";
                print "选项".($new_row+1)."<input type='text' style='width:300px'  name='option".($row_option_row+=1)."'/>";
                print "</ul>";
            }
            print "<input type='hidden' name='row_option_row' value='".$row_option_row."'";
            print "</div>";
            print "<p style=\"float:left;\">".$lang_examin['options_explain']."</p>";
            print "</td>";
            print "</tr>";
            tr($lang_examin['save_text'], "<input type='submit' name='save' value='{$lang_examin['save_explain']}'/>", 1);
            print "<input type='hidden' name='add_rows' value='".$_GET['add_rows']."'/>";
            print "</form>";
            print "</table>";
            break;
        default:
            print("************************");
    }
    }
    ?>


        <script type="text/javascript">
        document.getElementById("default").selectedIndex = <?php echo $row['type']-1 ?>;
    $(function(){
            $("#time1").datetimepicker({timeFormat: 'hh:mm:ss',showSecond:true,dateFormat:'yy-mm-dd'});
            $("#time2").datetimepicker({timeFormat: 'hh:mm:ss',showSecond:true,dateFormat:'yy-mm-dd'});
            });
    </script>

