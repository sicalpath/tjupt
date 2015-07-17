<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
print("<title>".$lang_banneduser['title']."</title>");
registration_check('invitesystem', true, false);
failedloginscheck ();
cur_user_check () ;
//global $lang_banuser;

stdhead();
print("<table width=700 class=main border=0 cellspacing=0 cellpadding=0>\n<tr><td class=embedded><h2>".$lang_banneduser['welcome']."</h2>\n<table width=\"100%\"><tr><td class=\"text\">\n".$lang_banneduser['readme']);
print("</td></tr></table></td></tr></table>");

stdfoot();
?>