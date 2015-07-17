<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR)
	stderr("Error", "Permission denied.");

$action = isset($_POST['action']) ? htmlspecialchars($_POST['action']):'main';
if ($action == 'setallfree')
{
	sql_query("UPDATE torrents_state SET global_sp_state = 2");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set free..');
}
elseif ($action == 'setall2up')
{
	sql_query("UPDATE torrents_state SET global_sp_state = 3");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set 2x up..');
}
elseif ($action == 'setall2up_free')
{
	sql_query("UPDATE torrents_state SET global_sp_state = 4");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set 2x up and free..');
}
elseif ($action == 'setallhalf_down')
{
	sql_query("UPDATE torrents_state SET global_sp_state = 5");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set half down..');
}
elseif ($action == 'setall2up_half_down')
{
	sql_query("UPDATE torrents_state SET global_sp_state = 6");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set 2X and half down..');
}
elseif ($action == 'setallnormal') 
{
	sql_query("UPDATE torrents_state SET global_sp_state = 1");
	$Cache->delete_value('global_promotion_state');
	stderr('Success','All torrents have been set normal..');
}
elseif ($action == 'main')
{
	stderr('Select action','<form action=freeleech.php id=setallfree name=setallfree method=post><input type=hidden name=action value=setallfree>Click <a class=faqlink href=javascript:document.setallfree.submit();>here</a> to set all torrents free..</form><br /><form action=freeleech.php id=setall2up name=setall2up method=post><input type=hidden name=action value=setall2up>Click <a class=faqlink href=javascript:document.setall2up.submit();>here</a> to set all torrents 2x up..</form><br /><form action=freeleech.php id=setall2up_free name=setall2up_free method=post><input type=hidden name=action value=setall2up_free> Click <a class=faqlink href=javascript:document.setall2up_free.submit();>here</a> to set all torrents 2x up and free..</form><br /><form action=freeleech.php id=setallhalf_down name=setallhalf_down method=post><input type=hidden name=action value=setallhalf_down>Click <a class=faqlink href=javascript:document.setallhalf_down.submit();>here</a> to set all torrents half down..</form><br /><form action=freeleech.php id=setall2up_half_down name=setall2up_half_down method=post><input type=hidden name=action value=setall2up_half_down>Click <a class=faqlink href=javascript:document.setall2up_half_down.submit();>here</a> to set all torrents 2x up and half down..</form><br /><form action=freeleech.php id=setallnormal name=setallnormal method=post><input type=hidden name=action value=setallnormal>Click <a class=faqlink href=javascript:document.setallnormal.submit();>here</a> to set all torrents normal..</form>', false);
}
?>
