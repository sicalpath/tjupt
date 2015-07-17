<?php
require_once("include/bittorrent.php");
dbconn();
$langid = 0 + $_GET['sitelanguage'];

if ($langid)
{
	$lang_folder = validlang($langid);
	if(get_langfolder_cookie() != $lang_folder)
	{
		set_langfolder_cookie($lang_folder);
		header("Location: " . $_SERVER['PHP_SELF']);
	}
}
require_once(get_langfile_path("login.php", false, $CURLANGDIR));


if(($_SERVER['REMOTE_ADDR'] =="121.193.130.230"||$_SERVER['REMOTE_ADDR'] =="2001:da8:a000:650::230")&&($_SERVER['HTTP_INSTITUTION']!=""&&$_SERVER['HTTP_USERNAME']!=""))
{
global $CURUSER;
unset($CURUSER);
setcookie("c_secure_uid", "", 0x7fffffff, "/");
	setcookie("c_secure_pass", "", 0x7fffffff, "/");
// setcookie("c_secure_ssl", "", 0x7fffffff, "/");
	setcookie("c_secure_tracker_ssl", "", 0x7fffffff, "/");
	setcookie("c_secure_login", "", 0x7fffffff, "/");
failedloginscheck ();
cur_user_check () ;
stdhead($lang_login['head_login']);

$s = "<select name=\"sitelanguage\" onchange='submit()'>\n";

$langs = langlist("site_lang");

foreach ($langs as $row)
{
	if ($row["site_lang_folder"] == get_langfolder_cookie()) $se = "selected=\"selected\""; else $se = "";
	$s .= "<option value=\"". $row["id"] ."\" ". $se. ">" . htmlspecialchars($row["lang_name"]) . "</option>\n";
}
$s .= "\n</select>";
?>
<form method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<?php
//print("<div align=\"right\">".$lang_login['text_select_lang']. $s . "</div>");
?>
</form>
<?php

unset($returnto);
if (!empty($_GET["returnto"])) {
	$returnto = $_GET["returnto"];
	if (!$_GET["nowarn"]) {
		print("<h1>" .$lang_login['h1_not_logged_in']. "</h1>\n");
		print("<p><b>" . $lang_login['p_error']. "</b> " . $lang_login['p_after_logged_in']. "</p>\n");
	}
}

$username = "'".$_SERVER['HTTP_USERNAME']."'";
$institution = "'".$_SERVER['HTTP_INSTITUTION']."'";
$res = sql_query("SELECT * FROM carsimapping WHERE username = $username and institution = $institution ORDER by id ASC") or sqlerr();
$try = sql_query("SELECT * FROM carsi_schools WHERE idp = '".$_SERVER['HTTP_INSTITUTION']."' ");
$schools = mysql_fetch_assoc($try);
if(!$schools)$idp = $lang_login['unknown_org'];
else $idp = $schools['school'];
if(mysql_num_rows($res) > 0){
?>
	<p><?php echo $lang_login['carsilogin3'].$idp.$lang_login['carsilogin4'].$_SERVER['HTTP_USERNAME']."</b><br />"?>
	<p><?php echo $lang_login['carsilogin1']?></p>
	<form method="post" action="takecarsilogin.php">
		<select name="carsiselect" >
			<?php 
				while($row = mysql_fetch_assoc($res)){
					$ret = sql_query("SELECT * FROM users WHERE id = $row[tjuptid]");
					$row_ = mysql_fetch_assoc($ret);
					if(!$row_){sql_query("DELETE FROM carsimapping WHERE tjuptid = $row[tjuptid]");header("Location: " . $_SERVER['PHP_SELF']);}//删除不存在的绑定关系。
					else $show .= "<option value=\"$row[tjuptid]\">".$row_[username]."</option>";
				}
				echo($show);
			?>
		</select><br />
		<?php if (isset($returnto))print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($returnto) . "\" />\n");?>
		<input type="submit" value="<?php echo $lang_login['button_login']?>" class="btn" />
	</form>
<?php	
}
else{
?>

<form method="post" action="takelogincarsi.php">
<table border="0" frame="void"><tr><td bordercolor="#55aa55" align="center" bgcolor="#55aa55">
<p><?php echo $lang_login['p_need_cookies_enables']?><br /> [<b><?php echo $maxloginattempts;?></b>] <?php echo $lang_login['p_fail_ban']?></p>
</td></tr><tr><td bordercolor="#55aa55" align="left" bgcolor="#55aa55"><p><?php echo $lang_login['carsilogin3'].$idp.$lang_login['carsilogin4'].$_SERVER['HTTP_USERNAME']."</b>";?><?php echo $lang_login['carsilogin2']?></p></td></tr><tr><td bordercolor="#55aa55" align="center" bgcolor="#55aa55">
<p><?php echo $lang_login['p_you_have']?> <b><?php echo remaining ();?></b> <?php echo $lang_login['p_remaining_tries']?></p>
</td></tr></table>
<table border="0" cellpadding="5">
<tr><td class="rowhead"><?php echo $lang_login['rowhead_username']?></td><td class="rowfollow" align="left"><input type="text" name="username" style="width: 180px; border: 1px solid gray" /></td></tr>
<tr><td class="rowhead"><?php echo $lang_login['rowhead_password']?></td><td class="rowfollow" align="left"><input type="password" name="password" style="width: 180px; border: 1px solid gray"/></td></tr>

<?php
//show_image_code ();
if ($securelogin == "yes") 
	$sec = "checked=\"checked\" disabled=\"disabled\"";
elseif ($securelogin == "no")
	$sec = "disabled=\"disabled\"";
elseif ($securelogin == "op")
	$sec = "";

if ($securetracker == "yes") 
	$sectra = "checked=\"checked\" disabled=\"disabled\"";
elseif ($securetracker == "no")
	$sectra = "disabled=\"disabled\"";
elseif ($securetracker == "op")
	$sectra = "";
?>

<tr><td class="toolbox" colspan="2" align="left"><?php echo $lang_login['text_advanced_options']?></td></tr>
<tr><td class="rowhead"><?php echo $lang_login['text_auto_logout']?></td><td class="rowfollow" align="left"><select name="logout"><option value="off" ><?php echo $lang_login['off']?></option><option value="1day" ><?php echo $lang_login['1day']?><option value="7days" selected ><?php echo $lang_login['7days']?></option><option value="14days" ><?php echo $lang_login['14days']?></option><!--<option value="30days" ><?php echo $lang_login['30days']?></option><option value="365days" ><?php echo $lang_login['365days']?></option><option value="forever" ><?php echo $lang_login['forever']?></option>--></select></td></tr>
<tr><td class="rowhead"><?php echo $lang_login['text_restrict_ip']?></td><td class="rowfollow" align="left"><input class="checkbox" type="checkbox" name="securelogin" value="yes" /><?php echo $lang_login['checkbox_restrict_ip']?></td></tr>
<tr><td class="rowhead"><?php echo $lang_login['text_ssl']?></td><td class="rowfollow" align="left"><input class="checkbox" type="checkbox" name="ssl" value="yes" <?php echo $sec?> /><?php echo $lang_login['checkbox_ssl']?><br /><input class="checkbox" type="checkbox" name="trackerssl" value="yes" <?php echo $sectra?> /><?php echo $lang_login['checkbox_ssl_tracker']?></td></tr>
<tr><td class="toolbox" colspan="2" align="right"><input type="submit" value="<?php echo $lang_login['button_login']?>" class="btn" /> <input type="reset" value="<?php echo $lang_login['button_reset']?>" class="btn" /></td></tr>
</table>
<?php

if (isset($returnto))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($returnto) . "\" />\n");

?>
</form>
<p><?php } echo $lang_login['p_no_account_signup']?></p>
<?php
if ($smtptype != 'none'){
?>
<p><?php echo $lang_login['p_forget_pass_recover']?></p>
<p><?php echo $lang_login['p_resend_confirm']?></p>
<?php
}
if ($showhelpbox_main != 'no'){?>
<table width="700" class="main" border="0" cellspacing="0" cellpadding="0"><tr><td class="embedded">
<h2><?php echo $lang_login['text_helpbox'] ?><font class="small"> - <?php echo $lang_login['text_helpbox_note'] ?><font id= "waittime" color="red"></font></h2>
<?php
print("<table width='100%' border='1' cellspacing='0' cellpadding='1'><tr><td class=\"text\">\n");
print("<iframe src='" . get_protocol_prefix() . $BASEURL . "/shoutbox.php?type=helpbox' width='650' height='180' frameborder='0' name='sbox' marginwidth='0' marginheight='0'></iframe><br /><br />\n");
print("<form action='" . get_protocol_prefix() . $BASEURL . "/shoutbox.php' id='helpbox' method='post' target='sbox' name='shbox'>\n");
print($lang_login['text_message']."<input type='text' id=\"hbtext\" name='shbox_text' autocomplete='off' style='width: 500px; border: 1px solid gray' ><input type='submit' id='hbsubmit' class='btn' name='shout' value=\"".$lang_login['sumbit_shout']."\" /><input type='reset' class='btn' value=".$lang_login['submit_clear']." /> <input type='hidden' name='sent' value='yes'><input type='hidden' name='type' value='helpbox' />\n");
print("<div id=sbword style=\"display: none\">".$lang_login['sumbit_shout']."</div>");
print(smile_row("shbox","shbox_text"));
print("</td></tr></table></form></td></tr></table>");
}


stdfoot();
}
else
	stderr($lang_login['uncarsi'],$lang_login['not_from_carsi'],false);

?>