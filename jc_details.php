<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
require_once("lang/chs/lang_jc_bet.php");
require_once("lang/chs/lang_jc_confirm.php");
loggedinorreturn();
parked();
updatestate();
global $bettopper;
$bettopper = 10000;
function bark($msg)
{
     global $lang_jc_bet;
    stdmsg($lang_details['sorry'],$msg);
    exit();
}

if(!isset($_GET['subid']))
{
		stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['not_exist'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_currentbet_L.php\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back1'],false);
}
$sub_id=$_GET['subid'];//get from yu_le's page
sub_is_unexist($sub_id);
$subject=fetch_from_jc_subjects($sub_id);
$sub_state=$subject['state']; //the state of this subject
$correct_id=$subject['win_options'];//get the correct options


stdhead("竞猜详情");

//main page

//show_jc_details($sub_state,$correct_id,$sub_id);
begin_main_frame();
jc_usercpmenu();
//create the frame of the jc system
begin_frame($lang_jc_details['the result'], true, 10, "100%", "center");
show_main_page($sub_state, $correct_id, $sub_id);
end_frame();
end_main_frame();
print'</table>';
stdfoot();

//functions used in the main page


//show the main content of the page
function show_main_page($sub_state, $correct_id, $sub_id)
{
		$is_selected = is_selected($sub_id);
		if(!$is_selected)
		{
				switch($sub_state)
				{
                case 1:
                    bark("Hey!Don't play any trick on me");
                    break;
						case 4:
								page_not_open();
								break;
						case 2:
								jc_content(NULL, "touzhu", NULL, $correct_id, $sub_id);
								break;
						case 3:
								jc_content("finished", NULL, "no_jc", $correct_id, $sub_id);
								break;
						case 5:
								jc_content("finished", "finished", "no_jc", $correct_id, $sub_id);
								break;
				}
		}
		else
		{
				switch($sub_state)
				{
                        case 1:
                                bark("Don't play tricks on me!");
						case 2:
								jc_content(NULL, "jiazhu", "line1", $correct_id, $sub_id);
								break;
						case 3:
								jc_content("finished", NULL, "line2", $correct_id, $sub_id);
								break;
						case 5:
								jc_content("finished", "finished", "line3", $correct_id, $sub_id);
								break;
				}
		}

}

//show the page that the jc is not open,this state is not existed
function page_not_open()
{
		global $lang_jc_details;
		print "bet is not open now";
}

//show the content
function jc_content($showspan, $button, $underline, $correct_id, $sub_id)
{
		global $lang_jc_details,$bettopper;
		//get the start time and end time from the database

		$subject=fetch_from_jc_subjects($sub_id);
		$starttime=$subject['start'];
		$endtime=$subject['end'];
		$limit=$subject['limit'];
		$ret=sql_query("SELECT note FROM jc_subjects WHERE id=".sqlesc($sub_id)."") or sqlerr(__FILE__, __LINE__);
		$note=mysql_fetch_array ($ret);

		print "<div align =\"center\">\n";
		show_title($sub_id);
		print "<p><font size=\"2\">". $lang_jc_details['start_time'] ." $starttime ". $lang_jc_details['end_time'] ." $endtime ";
		//whether show the kuo kao
		if ($showspan == NULL)
				print "</font>  ".$lang_jc_details['limit']." $limit".$lang_jc_details['bettopper']."<font color='red'> $bettopper</font></p>";
		elseif ($showspan == "finished")
				print "<span><font color=\"red\">(". $lang_jc_details['closed'] .")</font></span></font></p>";


		show_sub_title($sub_id);
		show_bottom_line($underline, $correct_id, $sub_id);
		print "<br/><br/><table  cellpadding = \"0\" cellsapcing=\"0\">";
		show_options($button, $sub_id,$correct_id,$selected_id);
		print "</table>";
		print "<br/><br/><table  cellpadding = \"0\" cellsapcing=\"0\">";
		if($note['note'])
		{print "<td><font size=\"3\">备注：</font></td><td><font size=\"3\">".$note['note']."</font></td>";
	print "</table>";}
}


//show the title
function show_title($sub_id)
{
		$subject=fetch_from_jc_subjects($sub_id);
		$jc_subject=$subject['subject'];
		$jc_subject_type=sub_type($subject['type']);

		print "<p><font size=\"5\" color=\"blue\" ><b>$jc_subject</b></font><span><font size=\"3\">(". $jc_subject_type .")</font></span></p>\n";
		if($subject['type'] == 1 ) {    
				$sc = <<<JA
		<script type="text/javascript">
                        function zOpenInner(){
                       		var content = "<div id=notes_football align=left>" + "<font color=red><h1>足球竞猜说明</h1></font><br/>";
                       		content = content + "<font color=white>1）    所谓“让球”，就是指博彩公司根据对阵两支球队联赛排名、实力对比、主客场因素、以往战绩比较、伤停情况等进行客观分析，列出强向弱队让球的比例。“让球比例”很大程度上反映了对阵双方实力的强弱。以AC米兰与切沃之战为例，AC米兰让0．5，即半球，也就是说，如果双方打平，买AC米兰者算输，因为0<0．5。。如果AC米兰净胜一球以上，买AC米兰的人才算赢，因为1>0．5。而切沃接受AC米兰的让球，就构成了“受让”。让球比例会随着对阵双方实力强弱而具体变化。<br />仍以AC米兰与切沃之战为为例: <br /><br />1．平手 <br /> 意思是两队中哪支获胜，买它的人就赢钱，买到负方球队的人就输钱。<br /><br /> 2．半球 <br />表示AC米兰输或平，买它的人输掉全部钱；AC米兰赢一个球以上（包括1球），全赢。<br /><br /> 3.一球  <br /> AC米兰输或平，买它的人全输；AC米兰若只胜一球，则算平局。AC米兰胜两球以上（包括两球），买它的人全赢。<br /><br /> 4．一球半 <br /> AC米兰胜一球以内或者平或者输，买它的人全输；AC米兰胜两球，买它的人全赢。<br /><br /> 5．两球 <br /> AC米兰输、平和赢一球，买它的人全输，若AC米兰赢两球，则走盘，若AC米兰赢三球以上（包括三球），买它的人全赢。 <br /><br /> 请谨慎下注！ </font>";
							contnet = content + "</div>";
							$('#lightbox').css({"zoom":"100%"});
							$('#lightbox').html(content);
							$('#curtain').fadeIn();
							$('#lightbox').fadeIn();	
                		}   
</script>
JA;
print $sc;
print "<button onclick='javascript:zOpenInner();' style =\"height:30px;width:200px;color:#FF0000;\">足球竞猜必看说明";
print "</button>";
}
}

//show the sub title
function show_sub_title($sub_id)
{
		$subject=fetch_from_jc_subjects($sub_id);
		$jc_subtitle=$subject['description'];
		print "<div><font size=\"2\">$jc_subtitle</font></div>";
}

//show the option choices
function show_options($button, $sub_id,$correct_id)
{
		global $lang_jc_details;
		global $CURUSER;

		$ret=sql_query("SELECT * FROM jc_record WHERE user_id=".sqlesc($CURUSER['id'])." AND subject_id=".sqlesc($sub_id)."")or sqlerr(__FILE__, __LINE__);

		if($record=mysql_fetch_array($ret))
				$selected_id=$record['option_id'];
		else $selected_id=0;


		$ret=sql_query("SELECT * FROM jc_options WHERE parent_id=".sqlesc($sub_id)." ORDER BY option_id") or sqlerr(__FILE__, __LINE__);
		while($option=mysql_fetch_array($ret))
		{
				$opt=$option['option_id'];

				$opt_total=$option['option_total'];
				print "<tr><td style='padding:5px;border-width:2px' height=\"70px\"> <b>{$option['option_name']}:</b></td><td style='border-width:2px' width=\"420px\">";
				show_percent_panel($opt,$opt_total, $sub_id);
				print "</td>";
				print "<td style='border-width:2px' width=\"150px\">".$lang_jc_details['text1']."$opt_total"."</td>";
				if ($button =="jiazhu" && $selected_id==$opt)
				{
						print "<td style='border-width:2px;padding:5px'>". $lang_jc_details['text3'];
						print "<form method=\"post\" action=\"jc_confirm.php?subid=".$sub_id."\"><input style='width:90px' type =\"text\" name=\"molizhi\"/>";
						button("jiazhu",$opt);
						print "</form></td>";
				}
				elseif($button =="touzhu")
				{
						print "<td style='border-width:2px;padding:5px'>". $lang_jc_details['text2'] ."";
						print "<form method=\"post\" action=\"jc_confirm.php?subid=".$sub_id."\"><input style='width:90px' type =\"text\" name=\"molizhi\"/>";
						button("touzhu",$opt);
						print "</form></td>";
				}
				elseif($button == "finished")
				{

						if($opt== $correct_id)
								print "<td style='border-width:2px;padding:5px'><font color=\"green\">".$lang_jc_details['jc_result']."</font>"."</td>";
						elseif($opt == $selected_id)
								print "<td style='border-width:2px;padding:5px'><font color=\"red\">".$lang_jc_details['your_result']."</font>"."</td>";
						else
								print "<td style='border-width:2px;padding:5px'></td>";
				}
				elseif($selected_id == $opt) {
						print "<td style='border-width:2px;padding:5px'><font color=\"red\">".$lang_jc_details['your_result']."</font></td>";
				}
				else {
						print "<td style='border-width:2px;padding:5px'></td>";
				}
				print "</tr>";
		}
}




//show the bottom line
function show_bottom_line($underline, $correct_id, $sub_id)
{
		global $lang_jc_details;
		global $CURUSER;

		print "<br/><div><font size=\"2\">";
		$ret=sql_query("SELECT * FROM jc_record WHERE user_id=".sqlesc($CURUSER['id'])." AND subject_id=".sqlesc($sub_id)."") or sqlerr(__FILE__, __LINE__);
		$user_record=mysql_fetch_array($ret);
		$user_selected_id=$user_record['option_id'];
		$user_total = $user_record['user_total'];

		$ret=sql_query("SELECT * FROM jc_options WHERE parent_id=".sqlesc($sub_id)." AND option_id=".sqlesc($user_selected_id)."") or sqlerr(__FILE__, __LINE__);
		$option=mysql_fetch_array($ret);
		$selected_option_total=$option['option_total'];


		$ret=sql_query("SELECT * FROM jc_options WHERE parent_id=".sqlesc($sub_id)." AND option_id=".sqlesc($correct_id)."") or sqlerr(__FILE__, __LINE__);
		$option=mysql_fetch_array($ret);
		$correct_name=$option['option_name'];

		$total=0;
		$ret=sql_query("SELECT * FROM jc_record WHERE subject_id=".sqlesc($sub_id)."") or sqlerr(__FILE__, __LINE__);
		while($var=mysql_fetch_array($ret))
				$total += $var['user_total'];


		$ret=sql_query("SELECT * FROM jc_record WHERE user_id=".sqlesc($CURUSER['id'])." AND subject_id=".sqlesc($sub_id)."")or sqlerr(__FILE__, __LINE__);

		if($record=mysql_fetch_array($ret))
				$selected_id=$record['option_id'];
		else $selected_id=0;

		$shou_yi = shouyi($user_total, $selected_option_total, $total, $correct_id, $selected_id);

		switch ($underline)
		{
				case NULL:
						print "";
						break;
						case "no_jc":
								print "". $lang_jc_details['ke_yong'] ."<font color=\"red\">{$CURUSER['seedbonus']}</font>,". $lang_jc_details['please_bet']. "";
						break;
						case "line1":
								print "". $lang_jc_details['ke_yong'] ."<font color=\"red\">{$CURUSER['seedbonus']}</font>,". $lang_jc_details['yi_xia_zhu']. "<font color=\"red\">$user_total</font>". $lang_jc_details['yu_ji'] ."";
						print "<font color=\"red\">$shou_yi</font>,". $lang_jc_details['dian_jia_zhu'] ."";
						break;
						case "line2":
								print "". $lang_jc_details['yi_xia_zhu'] ."<font color=\"red\">$user_total</font>". $lang_jc_details['wait'] ."";
						break;
						case "line3":
								print "". $lang_jc_details['ci_ci_xia_zhu'] ."<font color=\"red\">$user_total</font>". $lang_jc_details['jieguo'] . $correct_name . ", ". $lang_jc_details['final_score'] ." <font color=\"red\">$shou_yi</font>";
						break;
		}
		print "</font></div></div>";


}

//some calculation functions and judge functions
//use the key and value to show the subject type
function sub_type($sub_type_num)
{
		global $lang_jc_details;
		switch($sub_type_num)
		{
				case 1:
						return $lang_jc_details['football'];
						break;
				case 2:
						return $lang_jc_details['basketball'];
						break;
				case 3:
						return $lang_jc_details['tennis'];
						break;
				case 4:
						return $lang_jc_details['table_tennis'];
						break;
				case 5:
						return $lang_jc_details['others'];
						break;
		}
}

function shouyi($tou_zhu, $selected_option_total, $total, $correct_id, $selected_id)
{
		if ($selected_option_total != 0 && $total != 0)
		{

				if ($correct_id==0)
				{
						$sy = $tou_zhu*$total/$selected_option_total - $tou_zhu;
						$sy=intval($sy);
						return $sy;
				}
				elseif($correct_id==$selected_id)
				{
						$sy = $tou_zhu*$total/$selected_option_total;
						$sy=intval($sy);
						return $sy;
				}
				else return -$tou_zhu;
		}
		else return 0;

}


function button($flag,$opt)
{
		global $lang_jc_details;
		print "<div style='float:right'>";

		if($flag == "touzhu")
		{
				print "<input type='hidden' name=\"touzhu\" value=\"$opt\" />";
				print "<input type=\"submit\" class = \"btn\" value='".$lang_jc_details['touzhu']."'  name=\"touzhu1\"/>";
		}
		elseif($flag == "jiazhu")
		{
				print "<input type='hidden' name=\"touzhu\"  value=\"$opt\" />";
				print "<input type=\"submit\" class = \"btn\"   value = '".$lang_jc_details['jiazhu']."' name=\"jiazhu1\"/>";
		}
		print "</div>";
}


function show_percent_panel($opt_id,$opt_total,$sub_id)
{
		$percent_length=percent($opt_total,$sub_id)*400;
		print "<div id=\"box$opt_id\"   style=\"background:url('pic/panel.png');margin-left:10px;margin-right:10px;height:30px;width:0px;position:relative\"></div>";

		//-webkit-gradient(linear, left top, right top,from(#FFFFFF),to(#00BB25));background:-moz-linear-gradient(left top, #FFFFFF, #00BB25);filter:progid:DXImageTransForm.Microsoft.Gradient(GradientType=1,startColorstr='#FFFFFF',endColorstr='#00BB25');
		print "<script type=\"text/javascript\">";
		print "var length=";
		print $percent_length . ";";
		print "$(\"#box$opt_id\").animate({width:length},3000);</script> ";

}

function percent($opt_total, $sub_id)
{

		$subject=fetch_from_jc_subjects($sub_id);
		$sub_total=$subject['total'];
		if($sub_total!=0)$a= $opt_total/$sub_total;
		else $a=0;
		return $a;
}

//public functions (used in jc_confirm.php too)
function fetch_from_jc_subjects($sub_id)
{
		$subject=mysql_fetch_array(sql_query("SELECT * FROM jc_subjects WHERE id=".sqlesc($sub_id)."")) or sqlerr (__FILE__,__LINE__);
		return $subject;
}


//judge whether the current user has done the selection
function is_selected($sub_id)
{
		global $CURUSER;
		$ret=sql_query("SELECT  * FROM jc_record WHERE user_id=".sqlesc($CURUSER['id'])." AND subject_id=".sqlesc($sub_id)."") or sqlerr(__FILE__,__LINE__);
		$row=mysql_num_rows($ret);
		if($row == 0) return false;
		else return true;
}


function sub_is_unexist($sub_id)
{
		global $lang_jc_details;
		if(is_numeric($sub_id))
		{
				$ret=sql_query("SELECT * FROM jc_subjects WHERE id=".sqlesc($sub_id));
				$row=mysql_num_rows($ret);
				if($row == 0)
						stderr($lang_jc_details['wrong_head'],$lang_jc_details['not_exist'].$lang_jc_details['click']."<a class=altlink href=\"jc_currentbet_L.php\">".$lang_jc_details['here']."</a>".$lang_jc_details['back1'],false);
		}
		else {

				stderr($lang_jc_details['wrong_head'],$lang_jc_details['not_exist'].$lang_jc_details['click']."<a class=altlink href=\"jc_currentbet_L.php\">".$lang_jc_details['here']."</a>".$lang_jc_details['back1'],false);
		}
}
?>

