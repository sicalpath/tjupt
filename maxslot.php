<?php
require "include/bittorrent.php";
dbconn();
//require_once(get_langfile_path());
loggedinorreturn();
parked();

if (get_user_class() < UC_ADMINISTRATOR)
permissiondenied();

//read all configuration files
require('config/allconfig.php');


function go_back()
{
	stdmsg("信息", "点击"."<a class=\"altlink\" href=\"maxslot.php\">"."这里"."</a>"."返回");
}

$action = isset($_POST['action']) ? $_POST['action'] : 'showlist';

$maxarrayslotsql = "SELECT * FROM maxslots ";
$result = sql_query($maxarrayslotsql);

if ($action == 'maxslot')
{
	stdhead("Maxslot Manage");
	$updatemaxslotsql1 = "UPDATE maxslots SET maxslot = ";
	$updatemaxslotsql2 = " WHERE id =";

	for($i=0;$i<14;$i++){
		sql_query($updatemaxslotsql1.sqlesc($_REQUEST[$i]).$updatemaxslotsql2.sqlesc($i));
	}
	go_back();	
}

elseif($action == 'showlist')
{
	stdhead("Maxslot Manage");
	print ("<h1>Maxslot Manage</h1>");
	print ("<form method='post' action='maxslot.php'><input type='hidden' name='action' value='maxslot' /><table border=1 cellspacing=0 cellpadding=5>");

	while($maxarray = mysql_fetch_array($result))
	{
		print("<tr><td class=\"rowhead nowrap\" valign=\"top\" align=\"right\">");
		echo $maxarray['name'];
		print("</td><td class=\"rowfollow\" valign=\"top\" align=\"left\"><input type='text' style=\"width: 300px\" name= ");
		echo $maxarray['id'];
		print(" value=");
		echo $maxarray['maxslot'];
		print("></td></tr>\n");
    }
	
tr("保持设定","<input type='submit' style=\"width: 300px\" value='Okay'> ", 1);
print("</table></form>");
}
stdfoot();
?>
