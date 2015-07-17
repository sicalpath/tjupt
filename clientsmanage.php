<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
if (get_user_class() < $clientsmanage_class) 
    permissiondenied();
	
// DELETE FORUM ACTION
if ($_GET['action'] == "del") {
	$id = 0 + $_GET['id'];
	if (!$id) {
		header("Location: clientsmanage.php");
		die();
	}
	sql_query ("DELETE FROM agent_allowed_family where id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	header("Location: clientsmanage.php");
	die();
}

//EDIT FORUM ACTION
elseif ($_POST['action'] == "editclients") {
	$family=$_POST['family'];
	$start_name=$_POST['start_name'];
	$agent_pattern=$_POST['agent_pattern'];
	$agent_start=$_POST['agent_start'];
	$id = $_POST['id'];
	if (!$family && !$start_name && !$id && !$agent_pattern && !$agent_start) {
		header("Location: " . get_protocol_prefix() . "$BASEURL/clientsmanage.php");
		die();
	}
	sql_query("UPDATE agent_allowed_family SET family = '" . $_POST['family'] .
													    "', start_name = '".$_POST['start_name']. 
													    "', peer_id_pattern = '".$_POST['peer_id_pattern']. 
														"', peer_id_match_num = ".sqlesc($_POST['peer_id_match_num']). 
														", peer_id_matchtype = '".$_POST['peer_id_matchtype']. 
														"', peer_id_start = '".$_POST['peer_id_start']. 
														"', agent_pattern = '".$_POST['agent_pattern']. 
														"', agent_match_num = ".sqlesc($_POST['agent_match_num']). 
														", agent_matchtype = '".$_POST['agent_matchtype']. 
														"', agent_start = '".$_POST['agent_start']. 
														"', exception = '".$_POST['exception']. 
														"', allowhttps = '".$_POST['allowhttps']. 
														"', comment = '".$_POST['comment'].
														"', hits = ".sqlesc($_POST['hits']).
	                                                    " where id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	header("Location: clientsmanage.php");
	die();
}

//ADD FORUM ACTION
elseif ($_POST['action'] == "newclients") {
	$family=$_POST['family'];
	$start_name=$_POST['start_name'];
	$agent_pattern=$_POST['agent_pattern'];
	$agent_start=$_POST['agent_start'];
	$id = $_POST['id'];
	if (!$family && !$start_name && !$id && !$agent_pattern && !$agent_start) {
		header("Location: " . get_protocol_prefix() . "$BASEURL/clientsmanage.php");
		die();
	}
	sql_query("INSERT INTO agent_allowed_family (id, family, start_name, peer_id_pattern,  peer_id_match_num, peer_id_matchtype, peer_id_start, agent_pattern, agent_match_num, agent_matchtype, agent_start, exception, allowhttps, comment, hits) VALUES ( '',"
																  . sqlesc($_POST['family']) .","
													   .sqlesc($_POST['start_name']). ","
													  .sqlesc($_POST['peer_id_pattern']). ","
														.sqlesc($_POST['peer_id_match_num']).","
														.sqlesc($_POST['peer_id_matchtype']). ","
														.sqlesc($_POST['peer_id_start']). ","
														.sqlesc($_POST['agent_pattern']). ","
														.sqlesc($_POST['agent_match_num']).","
														.sqlesc($_POST['agent_matchtype']). ","
														.sqlesc($_POST['agent_start']). ","
														.sqlesc($_POST['exception']). ","
														.sqlesc($_POST['allowhttps']). ","
														.sqlesc($_POST['comment']).","
														.sqlesc($_POST['hits']).")") or sqlerr(__FILE__, __LINE__);
	header("Location: clientsmanage.php");
	die();
}

// SHOW FORUMS WITH FORUM MANAGMENT TOOLS
stdhead($lang_clientsmanage['head_clients_management']);
begin_main_frame();
if ($_GET['action'] == "editclients") {
	//EDIT PAGE FOR THE FORUMS
	$id = 0 + ($_GET["id"]);
	$result = sql_query ("SELECT * FROM agent_allowed_family where id = ".sqlesc($id));
	if ($row = mysql_fetch_array($result)) {
		do {
?>
<h1 align=center><a class=faqlink href=clientsmanage.php><?php echo $lang_clientsmanage['text_clients_management']?></a><b>--></b><?php echo $lang_clientsmanage['text_edit_clients']?></h2>
<br />
<form method=post action="<?php echo $_SERVER["PHP_SELF"];?>">
<table width="100%"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=colhead><?php echo $lang_clientsmanage['text_edit_clients']?></td>
  </tr>
   <tr>
    <td><b><?php echo $lang_clientsmanage['col_family']?></td>
    <td><input name="family" type="text" style="width: 200px" maxlength="60" value="<?php echo $row["family"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_start_name']?></td>
    <td><input name="start_name" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["start_name"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_peer_id_pattern']?></td>
    <td><input name="peer_id_pattern" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["peer_id_pattern"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_peer_id_match_num']?></td>
    <td><input name="peer_id_match_num" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["peer_id_match_num"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_peer_id_matchtype']?></td>
    <td><select name="peer_id_matchtype" ><option value="dec" <?php echo $row['peer_id_matchtype']=='dec'?"selected=\"selected\"":'';?>>dec</option><option value="hex" <?php echo $row['peer_id_matchtype']=='hex'?"selected=\"selected\"":'';?>>hex</option></select></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_peer_id_start']?></td>
    <td><input name="peer_id_start" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["peer_id_start"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_agent_pattern']?></td>
    <td><input name="agent_pattern" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["agent_pattern"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_agent_match_num']?></td>
    <td><input name="agent_match_num" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["agent_match_num"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_agent_matchtype']?></td>
    <td><select name="agent_matchtype"><option value="dec" <?php echo $row['agent_matchtype']=='dec'? "selected=\"selected\"":''; ?>>dec</option><option value="hex" <?php echo $row['agent_matchtype']=='hex'? "selected=\"selected\"":''; ?>>hex</option></select></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_agent_start']?></td>
    <td><input name="agent_start" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["agent_start"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_exception']?></td>
    <td><select name="exception"><option value="yes" <?php echo $row['exception']=='yes'?"selected=\"selected\"":'';?>>yes</option><option value="no" <?php echo $row['exception']=='no'?"selected=\"selected\"":'';?>>no</option></select></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_allowhttps']?></td>
    <td><select name="allowhttps"><option value="yes" <?php echo $row['allowhttps']=='yes'?"selected=\"selected\"":'';?>>yes</option><option value="no" <?php echo $row['allowhttps']=='no'?"selected=\"selected\"":'';?>>no</option></select></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_comment']?></td>
    <td><input name="col_comment" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["comment"];?>"></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_hits']?></td>
    <td><input name="hits" type="text" style="width: 400px" maxlength="200" value="<?php echo $row["hits"];?>"></td>
  </tr>


  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="editclients"><input type="hidden" name="id" value="<?php echo $id;?>"><input type="submit" name="Submit" value="<?php echo $lang_clientsmanage['submit_edit_clients']?>" class="btn"></td>
  </tr>
</table>

<?php
		} while($row = mysql_fetch_array($result));
	} 
	else 
	{
	print ($lang_clientsmanage['text_no_records_found']);
	}
}
//
elseif ($_GET['action'] == "newclients"){
	$result = sql_query ("SELECT * FROM agent_allowed_family ");
	$row = mysql_fetch_array($result);
?>
<h2 class=transparentbg align=center><a class=faqlink href=clientsmanage.php><?php echo $lang_clientsmanage['text_clients_management']?></a><b>--></b><?php echo $lang_clientsmanage['text_add_clients']?></h2>
<br />
<form method=post action="<?php echo $_SERVER["PHP_SELF"];?>">
<table width="100%"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=colhead><?php echo $lang_clientsmanage['text_add_clients']?></td>
  </tr>
   <tr>
    <td><b><?php echo $lang_clientsmanage['col_family']?></td>
    <td><input name="family" type="text" style="width: 200px" maxlength="60" value=""><?php echo '例如：'.$row['family'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_start_name']?></td>
    <td><input name="start_name" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['start_name'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_peer_id_pattern']?></td>
    <td><input name="peer_id_pattern" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['peer_id_pattern'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_peer_id_match_num']?></td>
    <td><input name="peer_id_match_num" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['peer_id_match_num'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_peer_id_matchtype']?></td>
    <td><select name="peer_id_matchtype" ><option value="dec" >dec</option><option value="hex" >hex</option></select><?php echo '例如：'.$row['peer_id_matchtype'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_peer_id_start']?></td>
    <td><input name="peer_id_start" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['peer_id_start'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_agent_pattern']?></td>
    <td><input name="agent_pattern" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['agent_pattern'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_agent_match_num']?></td>
    <td><input name="agent_match_num" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['agent_match_num'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_agent_matchtype']?></td>
    <td><select name="agent_matchtype"><option value="dec" >dec</option><option value="hex" >hex</option></select><?php echo '例如：'.$row['agent_matchtype'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_agent_start']?></td>
    <td><input name="agent_start" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['agent_start'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_exception']?></td>
    <td><select name="exception"><option value="yes" >yes</option><option value="no" >no</option></select><?php echo '例如：'.$row['exception'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_allowhttps']?></td>
    <td><select name="allowhttps"><option value="yes" >yes</option><option value="no" >no</option></select><?php echo '例如：'.$row['allowhttps'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_comment']?></td>
    <td><input name="comment" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['comment'];?></td>
  </tr>
  <tr>
    <td><b><?php echo $lang_clientsmanage['col_hits']?></td>
    <td><input name="hits" type="text" style="width: 400px" maxlength="200" value=""><?php echo '例如：'.$row['hits'];?></td>
  </tr>


  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="newclients"><input type="hidden" name="id" value="<?php echo $id;?>"><input type="submit" name="Submit" value="<?php echo $lang_clientsmanage['submit_edit_clients']?>" class="btn"></td>
  </tr>
</table>
<?php
}
else {
?>
<h2 class=transparentbg align=center><?php echo $lang_clientsmanage['text_clients_management']?></h2>
<table border=0 class=main cellspacing=0 cellpadding=5 width=1%><tr>
<td class=embedded align=left><form method="get" action="clientsmanage.php"><input type=hidden name="action" value="newclients"><input type="submit" value="<?php echo $lang_clientsmanage['text_add_clients']?>" class="btn"></form></td>
</tr></table>
<?php
echo '<table width="100%"  border="0" align="center" cellpadding="2" cellspacing="0">';
echo "<tr><td class=colhead align=left>".$lang_clientsmanage['col_id']."</td><td class=colhead>".$lang_clientsmanage['col_family']."</td><td class=colhead>".$lang_clientsmanage['col_start_name']."</td><td class=colhead>".$lang_clientsmanage['col_peer_id_start']."</td><td class=colhead>".$lang_clientsmanage['col_agent_start']."</td><td class=colhead>".$lang_clientsmanage['col_allowhttps']."</td><td class=colhead>".$lang_clientsmanage['col_hits']."</td><td class=colhead>".$lang_clientsmanage['col_modify']."</td></tr>";
$result = sql_query ("SELECT * FROM agent_allowed_family ");
if ($row = mysql_fetch_array($result)) {
do {
echo "<tr><td >".$row['id']."</td><td >".$row['family']."</td><td >".htmlspecialchars($row['start_name'])."</td><td >".htmlspecialchars($row['peer_id_start'])."</td><td >".htmlspecialchars($row['agent_start'])."</td><td >".htmlspecialchars($row['allowhttps'])."</td><td >".htmlspecialchars($row['hits'])."</td><td><b><a href=\"".$PHP_SELF."?action=editclients&id=".$row["id"]."\">".htmlspecialchars($lang_clientsmanage['text_edit'])."</a>&nbsp;|&nbsp;<a href=\"javascript:confirm_delete('".$row["id"]."', '".htmlspecialchars($lang_clientsmanage['js_sure_to_delete_forum'])."', '');\"><font color=red>".htmlspecialchars($lang_clientsmanage['text_delete'])."</font></a></b></td></tr>";
} while($row = mysql_fetch_array($result));
} else {print "<tr><td colspan=15>".htmlspecialchars($lang_clientsmanage['text_no_records_found'])."</td></tr>";}
echo "</table>";
}

end_main_frame();
stdfoot();
