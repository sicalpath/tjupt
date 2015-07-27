<?php
require "include/bittorrent.php";
dbconn();

		stdhead("帐号封禁、删除记录");
		$query = mysql_real_escape_string(trim($_GET["query"]));
		$search = $_GET["search"];
		$addparam = "";
		$wherea=" WHERE security_level = 'normal' AND (txt LIKE '%删除了帐号%' OR txt LIKE '%禁用了帐号%')";

		if($query){
				$wherea .= " AND txt LIKE '%".$query."%'";
				$addparam .= "query=".rawurlencode($query)."&";
		}
		
		print("<table border=1 cellspacing=0 width=940 cellpadding=5><tr>\n");
		print("<form method=\"get\" action='" . $_SERVER['PHP_SELF'] . "'>");
		print("<td class=colhead align=left>可以输入用户名查询帐号封禁、删除记录：");
        print("当前查询：" . htmlspecialchars($_GET['query']) ."<br>");
		print("<input type=\"text\" name=\"query\" style=\"width:100px\">\n");
		print("<input type=submit value=搜索></form>\n");
		print("</td></tr></table><br />\n");
		

		$res = sql_query("SELECT COUNT(*) FROM sitelog".$wherea);
		$row = mysql_fetch_array($res);
		$count = $row[0];

		$perpage = 50;

		list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "punishment.php?".$addparam);

		$res = sql_query("SELECT added, txt FROM sitelog $wherea ORDER BY added DESC $limit") or sqlerr(__FILE__, __LINE__);
		if (mysql_num_rows($res) == 0)
		print("没有封禁/删除帐号的记录");
		else
		{

		//echo $pagertop;

			print("<table width=940 border=1 cellspacing=0 cellpadding=5>\n");
			print("<tr><td class=colhead align=center><img class=\"time\" src=\"pic/trans.gif\" alt=\"time\" title=\"时间\" /></td><td class=colhead align=left>封禁/删除记录</td></tr>\n");
			while ($arr = mysql_fetch_assoc($res))
			{
				$color = "";
				if (strpos($arr['txt'],'删除了')) $color = "red";
				if (strpos($arr['txt'],'禁用了')) $color = "gray";
				print("<tr><td class=\"rowfollow nowrap\" align=center>".gettime($arr['added'],true,false)."</td><td class=rowfollow align=left><font color='".$color."'>".htmlspecialchars($arr['txt'])."</font></td></tr>\n");
			}
			print("</table>");
	
			echo $pagerbottom;
		}


		stdfoot();

?>
