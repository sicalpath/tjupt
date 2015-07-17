<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
//if (get_user_class() < UC_SYSOP)
global $CURUSER;
if(get_user_class()<14&&$CURUSER[jc_manager]!='yes')
permissiondenied();

function bark($msg)
{
		global $lang_subedit;
		stdhead();
		stdmsg($lang_subdelete['std_edit_failed'],$msg);
		stdfoot();
		exit;
}

//$handle = mysql_connect("localhost","byr","byr123");
//mysql_select_db("nexusphp",$handle);
//mysql_set_charset('utf8');
updatestate();

if(!isset($_POST['edit_sub_id']))
{  
		if(!mkglobal("subid"))
				bark($lang_subedit['std_missing_form_data']);
		if(!is_numeric($subid))
				bark("Hey! Don't play any tricks on me! ");

		$subid = $subid+0;
		int_check($subid);

		$res = sql_query("SELECT * FROM jc_subjects WHERE id =".sqlesc($subid));
		if(mysql_num_rows($res)==0)
				bark("The subject doesn't exist or has been deleted! Please get touch with the administrator!");
		$row = mysql_fetch_array($res);
		$res_option = sql_query("SELECT * FROM jc_options WHERE parent_id =".sqlesc($subid)." ORDER BY option_id");

		//some variables used following
		$creater_id = $row['creater_id'];
		$creater_name = $row['creater_name'];
		$subject = $row['subject'];
		$description = $row['description'];
		$start = $row['start'];
		$end = $row['end'];
		$limit =$row['limit'];

		$notice = "<table cellspacing=\"0\" cellpadding=\"15\" width=\"940\"><tr><td colspan=\"2\" style='padding: 10px; background: black' align=\"center\">
				<font color=\"white\">".$lang_subedit['warning_text']."</font></td></tr>";

		stdhead("竞猜管理");
    jc_usercpmenu(manage);
		print "<form method='post' action='".$_SERVER["SCRIPT_NAME"]."'>";
		print($notice);
		print "<input type='hidden' name='edit_sub_id' value={$row['id']}>";
		tr($lang_subedit['id_text'], "<label>".$row['id']."</label>", 1);
		tr($lang_subedit['createrid_text'], $creater_id, 1);
		tr($lang_subedit['creatername_text'], $creater_name, 1);
		tr($lang_subedit['subject_text'], "<input type='text'style='width:500px;' name='subject' value='$subject'/><br />".$lang_subedit['subject_explain'], 1);
		tr($lang_subedit['description_text'], "<input type='text' style='width:500px' name='description' value='$description'/><br />".$lang_subedit['description_explain'], 1);
		tr($lang_subedit['type_text'], "<select name='type' id='default'><option value='1'>football</option><option value='2'>basketball</option><option value='3'>tennis</option><option value='4'>tabletennis</option><option value='5'>others</option></select>", 1);
		//tr($lang_subedit['start_text'], "<input type='text' name='start' value='$start'/>".$lang_subedit['start_explain'], 1);
		tr($lang_subedit['start_text'],"<input type='text' id='time1' name='start' value='$start'>".$lang_subedit['start_explain'],1);
		//tr($lang_subedit['end_text'], "<input type='text' name='end' value='$end'/>".$lang_subedit['end_explain'], 1);
		tr($lang_subedit['end_text'],"<input type='text' id='time2'  name='end' value='$end'>".$lang_subedit['end_explain'],1);
		tr($lang_subedit['limit_text'],"<input type='text' name='limit' value='$limit'/>".$lang_subedit['limit_explain'],1);
		print "<tr>";
		print "<td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">".$lang_subedit['options_text']."</td>";
		print "<td>";
		print '<style type="text/css">ul {padding:0;margin:0;} </style>';
		print "<div class=\"zmh\" style=\"width:350px; float:left;\">";
		$option='';
		while($row_option=mysql_fetch_array($res_option))
		{
				print "<ul>";
				print $lang_subedit['option_label'].$row_option['option_id']."<input type='text'style='width:300px' name='option" .$row_option['option_id']."'  value='".$row_option['option_name']."'/>";
				print "</ul>";
		}
		print "</div>";
		print "<p style=\"float:left;\">".$lang_subedit['options_explain']."</p>";
		print "</td>";
		print "</tr>";
		if($row[state]==5)
		tr("撤销答案","<input type='checkbox' name='cancel' value='cancel'/>", 1);
		tr($lang_subedit['save_text'], "<input type='submit' name='save' value='{$lang_subedit['save_explain']}'/>", 1);
		print "</table>";
		print"</form>";

		?>

				<script type="text/javascript">
				document.getElementById("default").selectedIndex = <?php echo $row['type']-1 ?>;
		$(function(){
						$("#time1").datetimepicker({timeFormat: 'hh:mm:ss',showSecond:true,dateFormat:'yy-mm-dd'});
						$("#time2").datetimepicker({timeFormat: 'hh:mm:ss',showSecond:true,dateFormat:'yy-mm-dd'});
						});
		</script>
				<?php
}
else
{
		/*if(isset($_POST['creater_id']))
		  mysql_query("UPDATE jc_subjects SET creater_id='{$_POST['creater_id']}' WHERE id='$edit_sub_id'");
		  if(isset($_POST['creater_name']))
		  mysql_query("UPDATE jc_subjects SET creater_name='{$_POST['creater_name']}' WHERE id='$edit_sub_id'");
		  if(isset($_POST['subject']))
		  mysql_query("UPDATE jc_subjects SET subject='{$_POST['subject']}' WHERE id='$edit_sub_id'");
		  if(isset($_POST['description']))
		  mysql_query("UPDATE jc_subjects SET description='{$_POST['description']}' WHERE id='$edit_sub_id'");
		  if(isset($_POST['type']))
		  mysql_query("UPDATE jc_subjects SET type='{$_POST['type']}' WHERE id='$edit_sub_id'");
		  if(isset($_POST['start']))
		  mysql_query("UPDATE jc_subjects SET start='{$_POST['start']}' WHERE id='$edit_sub_id'");
		  if(isset($_POST['end']))
		  mysql_query("UPDATE jc_subjects SET end='{$_POST['end']}' WHERE id='$edit_sub_id'");
		 */
		// stdhead();

		$edit_sub_id = $_POST['edit_sub_id'];

		$res = sql_query("SELECT * FROM jc_subjects WHERE id =".sqlesc($edit_sub_id));
		$row = mysql_fetch_array($res);

		if($_POST['subject']=='' || $_POST['type']=='' ||  $_POST['start']=='' || $_POST['end']==''|| $_POST['limit']=='')
				bark($lang_subedit['std_missing_form_data']);
		$op=array();
		for($option_id =1; $option_id<=$row['options']; $option_id++)

		{
				/*echo $option_id;*/
				/*echo $_POST['option'.$option_id];*/
				$temp='option'.$option_id;
				$op[$option_id]=$_POST[$temp];
				if($op[$option_id]=='')
						bark($lang_subedit['std_missing_form_data']);
		}

		stdhead();
		// mysql_query("UPDATE jc_subjects SET creater_id='{$_POST['creater_id']}',creater_name='{$_POST['subject']}',subject='{$_POST['subject']}',description='{$_POST['description']}',type='{$_POST['type']}',start='{$_POST['start']}',end='{$_POST['end']}',limit='{$_POST['limit']}'  WHERE id='$edit_sub_id' ");
		if($_POST['cancel'])
		{
	$res2=sql_query("SELECT * from jc_record WHERE subject_id =".sqlesc($edit_sub_id));
	while($arr=mysql_fetch_array($res2))
	{
	$subject='竞猜被撤销';
	if ($arr['state'] == 2){
		$msg='您参与的竞猜[url=jc_details.php?subid='.$edit_sub_id.']'.$row['subject'].'[/url]结果有误，被管理员撤销，您获得的'.$arr['yin_kui'].'魔力值及下注的魔力值已经被返还入下注盘口。';
		}
	elseif ($arr['state'] == 1){
		$msg='您参与的竞猜[url=jc_details.php?subid='.$edit_sub_id.']'.$row['subject'].'[/url]结果有误，被管理员撤销，您失去的'.abs($arr['yin_kui']).'魔力值已经被返还入下注盘口。';
		}
		$added = sqlesc ( date ( "Y-m-d H:i:s" ) );
		sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) VALUES(0, '" . $arr ['user_id'] . "', '".$subject."', '".$msg."', $added)" ) or sqlerr ( __FILE__, __LINE__ );
		$bonus=$arr['yin_kui']+$arr['user_total'];
		sql_query ( "UPDATE users SET seedbonus = seedbonus - " . $bonus . " WHERE id = " . $arr ['user_id'] );
}
		sql_query("UPDATE jc_subjects SET state=3, win_options=0, subject=".sqlesc($_POST['subject']).",description=".sqlesc($_POST['description']).",type=".sqlesc($_POST['type']).",start=".sqlesc($_POST['start']).",end=".sqlesc($_POST['end']).",`limit`=".sqlesc($_POST['limit'])." WHERE id='$edit_sub_id'" ) or sqlerr ( __FILE__, __LINE__ );;
}
		else{
		sql_query("UPDATE jc_subjects SET subject=".sqlesc($_POST['subject']).",description=".sqlesc($_POST['description']).",type=".sqlesc($_POST['type']).",start=".sqlesc($_POST['start']).",end=".sqlesc($_POST['end']).",`limit`=".sqlesc($_POST['limit'])." WHERE id='$edit_sub_id'" );}
		// echo mysql_error();
		for($option_id =1; $option_id<=$row['options']; $option_id++)
				sql_query("UPDATE jc_options SET option_name=".sqlesc($op[$option_id])." WHERE parent_id=".sqlesc($edit_sub_id)." AND option_id=".sqlesc($option_id));
		stdmsg($lang_subedit['save_head_text'],$lang_subedit['save_body_text']);

}

stdfoot();
?>
