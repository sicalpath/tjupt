<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
//require_once(get_langfile_path("",true));
loggedinorreturn();
//if (get_user_class() < UC_SYSOP)
global $CURUSER;
if(get_user_class()<14&&$CURUSER[jc_manager]!='yes'){
		permissiondenied();
}

function bark($msg)
{   
		global $lang_subdelete;
		stdhead();
		stdmsg($lang_subdelete['std_delete_failed'],$msg);
		stdfoot();
		exit;
}

function deletesubject($id)
{
	$res=sql_query("select * from jc_record WHERE subject_id =$id");
	$row=mysql_fetch_array(sql_query("select * from jc_subjects where id =$id"));
	if ($row[state]!=5){
	while($arr=mysql_fetch_array($res))
	{

		$subject="竞猜被删除";
		$msg="您参与的竞猜“".$row['subject']."”被管理员删除，您下注的$arr[user_total]魔力值已经被返还。";
		$added = sqlesc ( date ( "Y-m-d H:i:s" ) );
		sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) VALUES(0, '" . $arr ['user_id'] . "', '".$subject."', '".$msg."', $added)" ) or sqlerr ( __FILE__, __LINE__ );
		sql_query ( "UPDATE users SET seedbonus = seedbonus + " . $arr['user_total'] . " WHERE id = " . $arr ['user_id'] );
}}
		sql_query("DELETE FROM jc_subjects WHERE id =".sqlesc($id));
		sql_query("DELETE FROM jc_options WHERE parent_id =".sqlesc($id));
		sql_query("DELETE FROM jc_record  WHERE subject_id=".sqlesc($id));
}

//$handle = mysql_connect("localhost","byr","byr123");
//mysql_select_db("nexusphp",$handle);
//mysql_set_charset('utf8');
updatestate();

if(!mkglobal("subid"))
bark($lang_subdelete['std_missing_form_data']);

$subid = $subid+0;
int_check($subid);
$sure = $_GET["sure"];

$res = sql_query("SELECT * FROM jc_subjects WHERE id =".sqlesc($subid));
$row = mysql_fetch_array($res);
if(!$row)
{
		bark('Be careful!');
}

if(!$sure)
		stderr($lang_subdelete['std_delete_subject'], $lang_subdelete['std_delete_subject_note']
						."<a class=altlink href=jc_subdelete.php?subid=$subid&sure=1>".$lang_subdelete['std_here_if_sure'],false);
		//When deleted,there is a blank website,not fine!
		else 
{ 
		deletesubject($subid);
		header("location:jc_manage.php");
}
?>
