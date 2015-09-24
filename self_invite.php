<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
print("<title>" . $lang_self_invite['title'] . "</title>");
registration_check('invitesystem', true, false);
failedloginscheck();
cur_user_check();
global $lang_takesignup;
global $oneinvite_bonus;
$revive_bonus = 3000;
$add_bonus = 7000;
$getcode = $_GET['code'];
$postcode = $_POST['code'];
$email = $_POST['email'];
$send_again = $_POST['sendagain'];
if ($getcode) {
    $code = $getcode;
    $sq = sprintf("SELECT * FROM self_invite WHERE code ='%s'", mysql_real_escape_string($getcode));
    $res = sql_query($sq) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    if (!$arr['email'])
        stderr($lang_self_invite['std_error'], $lang_self_invite['std_wrong_code'], 0);
    if ($arr['used_type'] == 'none') {
        stdhead($lang_self_invite['title']);
        $emailaddress = $arr['email'];
        print("<table width=700 class=main border=0 cellspacing=0 cellpadding=0>\n<tr><td class=embedded><h2>" . $lang_self_invite['welcome'] . "</h2>\n<table width=\"100%\"><tr><td class=\"text\">\n<form method=\"post\" action=\"self_invite.php\" />\n<input type=\"hidden\" name=\"code\" value=\"" . $code . "\" />\n" . $lang_self_invite[you_can_use_email] . $emailaddress . "\n<select name=\"type\"> <option value=\"invite\">" . $lang_self_invite['invite'] . "</option><option value=\"revive\" selected=\"selected\">" . $lang_self_invite['revive'] . "</option><option value=\"addbonus\">" . $lang_self_invite['addbonus'] . "</option></select><br/>\n" . $lang_self_invite[username] . "<input name=\"username\" value=\"\" /><input type=\"submit\" name=\"submit\" value=\"" . $lang_self_invite[enter] . "\" /></form>");
        stdfoot();
        die();
    } elseif ($arr['used_type'] == 'invite') {
        $a = (@mysql_fetch_row(@sql_query("SELECT count(*) FROM invites WHERE hash ='" . $arr['invite_code'] . "'"))) or die(mysql_error());
        if ($a[0] != 0) header("Location: signup.php?type=invite&invitenumber=" . $arr['invite_code']);
    }
    stderr($lang_self_invite['std_error'], $lang_self_invite['code_be_used'], 0);
} elseif ($postcode) {
    $sq = sprintf("SELECT * FROM self_invite WHERE code ='%s'", mysql_real_escape_string($postcode)) or sqlerr(__FILE__, __LINE__);
    $res = sql_query($sq) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    $emailaddress = $arr['email'];
    $used_type = $arr['used_type'];
    if (!$emailaddress) stderr($lang_self_invite['std_error'], $lang_self_invite['std_wrong_code'], 0);
    if ($used_type != 'none') stderr($lang_self_invite['std_error'], $lang_self_invite['code_be_used'], 0);

    $type = $_POST["type"];
    if ($type == 'invite') {
        $invitecode = md5(mt_rand(1, 10000) . $_SERVER['REMOTE_ADDR'] . TIMENOW . $emailaddress);
        sql_query("INSERT INTO invites (inviter, invitee, hash, time_invited) VALUES ('0', '" . mysql_real_escape_string($emailaddress) . "', '" . mysql_real_escape_string($invitecode) . "', " . sqlesc(date("Y-m-d H:i:s")) . ")") or sqlerr(__FILE__, __LINE__);
        sql_query("UPDATE self_invite SET used_type ='invite', invite_code ='" . mysql_real_escape_string($invitecode) . "' WHERE email ='" . mysql_real_escape_string($emailaddress) . "'") or sqlerr(__FILE__, __LINE__);
        header("Location: signup.php?type=invite&invitenumber=" . $invitecode);
    } else {
        $username = sqlesc(trim($_POST["username"]));
        $res = sql_query("SELECT id, enabled, modcomment, downloadpos, bonuscomment FROM users WHERE username=" . $username) or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res);
        $userid = $arr['id'];
        if (!$userid) {
            stderr($lang_self_invite['std_error'], $lang_self_invite['account_not_exists'] . $postcode . $lang_self_invite['account_not_exists2'], 0);
        }
        $enabled = $arr['enabled'];
        if ($type == 'revive') {
            $modcomment = $arr['modcomment'];
            $modcomment = date("Y-m-d") . " - enabled by " . $emailaddress . ".\n" . htmlspecialchars($modcomment);
            $bonuscomment = $arr['bonuscomment'];
            $bonuscomment = date("Y-m-d") . " + $revive_bonus Points added by " . $emailaddress . ".\n" . htmlspecialchars($bonuscomment);
            $downloadpos = $arr['downloadpos'];
            if ($enabled == 'yes') {
                stderr($lang_self_invite['std_error'], $lang_self_invite['text_account_not_disabled'] . $postcode . $lang_self_invite['account_not_exists2'], 0);
            }
            if ($downloadpos == 'no') {
                stderr($lang_self_invite['text_no_permission'], $lang_self_invite['text_banned_by_admin'], 0);
            }
            sql_query("UPDATE users SET enabled = 'yes', class =1, leechwarn='no', seedbonus = seedbonus +$revive_bonus, modcomment = " . sqlesc($modcomment) . " , bonuscomment = " . sqlesc($bonuscomment) . " WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE self_invite SET used_type = 'revive' WHERE email ='" . mysql_real_escape_string($emailaddress) . "'") or sqlerr(__FILE__, __LINE__);
            stderr($lang_self_invite['successful'], $lang_self_invite['text_account'] . $username . $lang_self_invite['text_success_enable_account']);
        } elseif ($type == 'addbonus') {
            if ($enabled == 'no')
                stderr($lang_self_invite['std_error'], $lang_self_invite['text_account_disabled'] . $postcode . $lang_self_invite['account_not_exists2'], 0);
            $bonuscomment = $arr['bonuscomment'];
            $bonuscomment = date("Y-m-d") . " + $add_bonus Points added by " . $emailaddress . ".\n" . htmlspecialchars($bonuscomment);
            sql_query("UPDATE users SET seedbonus = seedbonus +$add_bonus, bonuscomment = " . sqlesc($bonuscomment) . " WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE self_invite SET used_type = 'addbonus' WHERE email ='" . mysql_real_escape_string($emailaddress) . "'") or sqlerr(__FILE__, __LINE__);
            stderr($lang_self_invite['successful'], $lang_self_invite['add_bonus_for'] . $username . $lang_self_invite['is_success']);


        }
    }
} elseif ($email || $send_again) {
    $domain = $_POST['domain'];
    if (!in_array($domain, $domains) && !$send_again)
        stderr($lang_self_invite['std_error'], $lang_self_invite['domain_not_permission']);
    $emailaddress = $email . "@" . $domain;
    if ($send_again) $emailaddress = $send_again;
    $emailaddress = safe_email($emailaddress);
    $a = (@mysql_fetch_row(@sql_query("select count(*) from self_invite where email=" . sqlesc($emailaddress)))) or die(mysql_error());
    if ($a[0] != 0 && !$send_again) {
        stderr($lang_self_invite['std_error'], "<form method=\"post\" id=\"sendagain\" name=\"sendagain\" action=\"self_invite.php\" />\n<input type=\"hidden\" name=\"sendagain\" value=\"" . $emailaddress . "\" />" . $lang_self_invite['email_address'] . $emailaddress . $lang_self_invite['std_is_in_use'] . "</form>", 0);
    }
    if ($a[0] == 0 && $send_again) stderr($lang_self_invite['std_error'], $lang_self_invite['do_not_treat_us']);
    if (!check_email($emailaddress)) stderr($lang_self_invite['std_error'], $lang_self_invite['email_address_error']);
    if (EmailBanned($emailaddress)) stderr($lang_self_invite['std_error'], $lang_self_invite['email_address_banned']);
    $ip = getip();
    $code = md5(mt_rand(1, 10000) . $ip . TIMENOW . $emailaddress);
    $title = $SITENAME . $lang_self_invite['title'];
    $message = <<<EOD
{$lang_self_invite['mail_one']}
<b><a href="http://$BASEURL/self_invite.php?code=$code" target="_blank">http://$BASEURL/self_invite.php?code=$code</a></b><br />
{$lang_self_invite['mail_two']}{$ip}{$lang_self_invite['mail_three']}
<br />
EOD;
    sent_mail($emailaddress, $SITENAME, $SITEEMAIL, change_email_encode(get_langfolder_cookie(), $title), change_email_encode(get_langfolder_cookie(), $message), "invitesignup", false, false, '', get_email_encode(get_langfolder_cookie()));
    if ($send_again) sql_query("UPDATE self_invite SET code = '" . mysql_real_escape_string($code) . "' WHERE email = '" . mysql_real_escape_string($emailaddress) . "'");
    else sql_query("INSERT INTO self_invite (email, used_type, code) VALUES ('" . mysql_real_escape_string($emailaddress) . "', 'none', '" . mysql_real_escape_string($code) . "')");
    stderr($lang_self_invite['successful'], $lang_self_invite['email_to'] . htmlspecialchars($emailaddress) . $lang_self_invite['successfully_sent']);

} else {
    stdhead($title);
    print("<table width=700 class=main border=0 cellspacing=0 cellpadding=0>\n<tr><td class=embedded><h2>" . $lang_self_invite['welcome'] . "</h2>\n<table width=\"100%\"><tr><td class=\"text\">\n" . $lang_self_invite['readme'] . "\n" . $lang_self_invite['warning'] . "\n<form method=\"post\" action=\"self_invite.php\" /><br />\n" . $lang_self_invite['input_email_address'] . "<input name=\"email\" value=\"\" />\n@<select name=\"domain\">");
    foreach ($domains as $getdomain) print("<option value=\"" . $getdomain . "\">" . $getdomain . "</option>");
    print("</select>\n<input type=\"submit\" name=\"submit\" value=\"" . $lang_self_invite[enter] . "\" /></form></td></tr></table></td></tr>");
    print("<tr><td><h2>" . $lang_self_invite['notice'] . "</h2></td></tr></table>");
}
stdfoot();
?>