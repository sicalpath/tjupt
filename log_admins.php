<?php	
require "include/bittorrent.php";
dbconn();
require_once("lang/chs/lang_log.php");
loggedinorreturn();

if (get_user_class() < $log_class)
{
stderr($lang_log['std_sorry'],$lang_log['std_permission_denied_only'].get_user_class_name($log_class,false,true,true).$lang_log['std_or_above_can_view'],false);
}

function getUsernames($class, $searchclass)
{
	switch($searchclass)
	{
		case "above": $sql = "select username from users where class>='$class'";
		break;
		case "exact":$sql = "select username from users where class='$class'";
		break;
		case "below":$sql = "select username from users where class<='$class'";
		break;
	}
	
	$res = mysql_query($sql) or sqlerr(__FILE__, __LINE__);
	while($row = mysql_fetch_array( $res ))
	{
	
		$usernames[] = $row[0];
	}
	return $usernames;


}

function adminlog($user, $res, $weektime)
{
			print("<table width=940 border=1 cellspacing=0 cellpadding=5>\n");
			print("<tr><td class=colhead align=left>".$user."</td><td class=colhead ></td></tr>\n");
			print("<tr><td class=colhead align=center><img class=\"time\" src=\"pic/trans.gif\" alt=\"time\" title=\"time\" /></td><td class=colhead align=left>event </td></tr>\n");
			while ($arr = mysql_fetch_assoc($res))
			{
				$color = "";
				if (strpos($arr['txt'],'was uploaded by')||strpos($arr['txt'],'上传了')) $color = "green";
				if (strpos($arr['txt'],'新增了')||strpos($arr['txt'],'was added by'))$color="teal";
				if (strpos($arr['txt'],'was deleted by')||strpos($arr['txt'],'删除了')) $color = "red";
				if (strpos($arr['txt'],'确认了')) $color = "purple";
				if (strpos($arr['txt'],'was edited by')||strpos($arr['txt'],'编辑了')) $color = "blue";
				if (strpos($arr['txt'],'settings updated by')) $color = "darkred";
				print("<tr><td class=\"rowfollow nowrap\" align=center>".gettime($arr['added'],true,false)."</td><td class=rowfollow align=left><font color='".$color."'>".htmlspecialchars($arr['txt'])."</font></td></tr>\n");
			}
			print("</table> <br>");
}	
	
	
if($_GET['queryclass'])
{
	$queryclass = (trim($_GET['queryclass']));
	$searchclass= (trim($_GET["searchclass"]));

	if ( ! eregi("^[0-9]*$", $queryclass) ) 
	{
			$queryclass=""; 
			$_GET["queryclass"]="";
			stderr('Input error','numbers only',false);

			
	}
	else if(!empty($queryclass) )
	{
		$currenttime=date("Y-m-d H:i:s");
		$time = strtotime($currenttime);
		$timeweek = $time - 3600*24*7;
		$weektime = date("Y-m-d H:i:s",$timeweek);
		$usernames = getUsernames($queryclass, $searchclass);
		$sql = "select * from sitelog";
		stdhead("admin logs ");
		foreach($usernames as $user)
		{
			$wheres =" WHERE txt like '%$user%' AND added > '".$weektime."'  ORDER BY added DESC"; 
			$res = mysql_query($sql.$wheres);			
			adminlog($user, $res, $weektime);
		}
		
		stdfoot();
	}

}

?>
