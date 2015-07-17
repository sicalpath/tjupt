<?php
require_once("include/bittorrent.php"); 
function bark($msg) { 
 stdhead(); 
   stdmsg("Failed", $msg); 
 stdfoot(); 
 exit; 
} 
dbconn(); 
loggedinorreturn(); 
if (get_user_class() < $staffmem_class)
       permissiondenied();
if ($_POST['bonus']){
	if($_POST['bonus'] > 1000 )
	$_POST['bonus']=1000;
	if($_POST['bonus']< -1000 )
	$_POST['bonus']=-1000;
	$reportres = sql_query("SELECT * FROM reports WHERE dealtwith=0 AND id IN (" . implode(", ", $_POST[delreport]) . ")");
	while ($row = mysql_fetch_array($reportres)){
		$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
		if ($_POST['bonus']>0)
		{$msg = "感谢举报，系统为您增加了".$_POST['bonus']."个魔力值。";
		$subject="感谢举报";}
		else
		{$msg = "举报内容不属实，".$_POST['bonus']."个魔力值。";
		$subject="举报不属实";}
		sql_query("update users set seedbonus = seedbonus + ".$_POST[bonus] ." where id = ".$row['addedby']."") or sqlerr ( __FILE__, __LINE__ );
		sql_query ( "INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, ".$row['addedby'].", $dt, '".$msg."', '".$subject."')" ) or sqlerr ( __FILE__, __LINE__ );
}}
if ($_POST['setdealt']){
$res = sql_query ("SELECT id FROM reports WHERE dealtwith=0 AND id IN (" . implode(", ", $_POST[delreport]) . ")");
while ($arr = mysql_fetch_assoc($res))
	sql_query ("UPDATE reports SET dealtwith=1, dealtby = $CURUSER[id] WHERE id = $arr[id]") or sqlerr();
	$Cache->delete_value('staff_new_report_count');
}
elseif ($_POST['delete']){
$res = sql_query ("SELECT id FROM reports WHERE id IN (" . implode(", ", $_POST[delreport]) . ")");
while ($arr = mysql_fetch_assoc($res))
	sql_query ("DELETE from reports WHERE id = $arr[id]") or sqlerr();
	$Cache->delete_value('staff_new_report_count');
	$Cache->delete_value('staff_report_count');
} 



header("Refresh: 0; url=reports.php"); 
