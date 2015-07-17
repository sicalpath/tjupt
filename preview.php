<?php
require_once ("include/bittorrent.php");
dbconn ();
require_once (get_langfile_path ());
loggedinorreturn ();

$action = $_POST ['action'];
$body = $_POST ['body'];

if ($action == 'light') {
	print ('<div id="kdescr" style="background-color: #E7E7E7;color:black;text-align:left;font-size: 10pt;">') ;
	print ("<table align=center>\n") ;
	print ("<tr><td align=left>" . format_comment ( $body ) . "<br /><br /></td></tr></table>") ;
	print ('</div>') ;
} else {
	print ("<table width=100% border=1 cellspacing=0 cellpadding=10 align=left>\n") ;
	print ("<tr><td align=left>" . format_comment ( $body ) . "<br /><br /></td></tr></table>") ;
}
?>
