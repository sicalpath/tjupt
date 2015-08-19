<?php
require_once ("include/bittorrent.php");
dbconn ();
require_once (get_langfile_path ());
if (isset ( $_GET ['del'] )) {
	if (is_valid_id ( $_GET ['del'] )) {
		if ((get_user_class () >= $sbmanage_class)) {
			sql_query ( "DELETE FROM shoutbox WHERE id=" . mysql_real_escape_string ( $_GET ['del'] ) );
		}
	}
}
$where = ($_POST ["type"] ? $_POST ["type"] : $_GET ["type"]);
$refresh = ($CURUSER ['sbrefresh'] ? $CURUSER ['sbrefresh'] : 120)?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Refresh"
	content="<?php echo $refresh?>; url=shoutbox.php?type=<?php echo $where?>">
<link rel="stylesheet" href="<?php echo get_font_css_uri()?>"
	type="text/css">
<link rel="stylesheet" href="<?php echo get_css_uri()."theme.css"?>"
	type="text/css">
<link rel="stylesheet" href="styles/sprites.css" type="text/css">
<link rel="stylesheet" href="styles/curtain_imageresizer.css"
	type="text/css">
<script src="js/curtain_imageresizer.js" type="text/javascript"></script>
<style type="text/css">
body {
	overflow-y: scroll;
	overflow-x: hidden
}
</style>
<script type="text/javascript" src="js/curtain_imageresizer.js"></script>
<script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>

<?php
print (get_style_addicode ()) ;
$startcountdown = "startcountdown(" . $CURUSER ['sbrefresh'] . ")";
?>
<script type="text/javascript">
//<![CDATA[
var t;
function startcountdown(time)
{
parent.document.getElementById('countdown').innerHTML=time;
time=time-1;
t=setTimeout("startcountdown("+time+")",1000);
}
function countdown(time)
{
	if (time <= 0){
	parent.document.getElementById("hbtext").disabled=false;
	parent.document.getElementById("hbsubmit").disabled=false;
	parent.document.getElementById("hbsubmit").value=parent.document.getElementById("sbword").innerHTML;
	}
	else {
	parent.document.getElementById("hbsubmit").value=time;
	time=time-1;
	setTimeout("countdown("+time+")", 1000);
	}
}
function hbquota(){
parent.document.getElementById("hbtext").disabled=true;
parent.document.getElementById("hbsubmit").disabled=true;
var time=10;
countdown(time);
//]]>
}
function response(username){
parent.document.getElementById("shbox_text").value = "@" + username + " ";
parent.document.forms['shbox'].shbox_text.focus();
}
function copy_code(txt) {
     if(window.clipboardData) {
             window.clipboardData.clearData();
             window.clipboardData.setData("Text", txt);
			 alert("已复制,成功与否取决于浏览器设置！");
     } else if(navigator.userAgent.indexOf("Opera") != -1) {
          window.location = txt;
		  alert("已复制,成功与否取决于浏览器设置！");
     } else if (window.netscape) {
          try {
               netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
          } catch (e) {
               alert("被浏览器拒绝！\n请在浏览器地址栏输入'about:config'并回车\n然后将'signed.applets.codebase_principal_support'设置为'true'");
          }
          var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
          if (!clip)
               return;
          var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
          if (!trans)
               return;
          trans.addDataFlavor('text/unicode');
          var str = new Object();
          var len = new Object();
          var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
          var copytext = txt;
          str.data = copytext;
          trans.setTransferData("text/unicode",str,copytext.length*2);
          var clipid = Components.interfaces.nsIClipboard;
          if (!clip)
               return false;
          clip.setData(trans,null,clipid.kGlobalClipboard);
          alert("复制成功！")
     }
}

$(function(){
	$(".shoutrow").hover(function(){
		$(this).css("background-color","#e4e2dd");
		$s="<p class=\"reply\" style=\"display:inline; color:#a7a7a7\"  >&nbsp;&nbsp;<不许双击啦~啦啦啦~></p>";
		$(this).append($s);
		},function(){
		$(this).css("background","none");
		$s="<p style=\"display:none;\">I would like to say: </p>";
		$(".reply").remove();})

	$(".shoutrow").dblclick(function(){
		$user=$(this).attr('ocr');
		parent.document.forms['shbox'].shbox_text.value="@"+$user+" "+parent.document.forms['shbox'].shbox_text.value;
		parent.document.forms['shbox'].shbox_text.focus();
		})
	});
</script>

</head>
<body class='inframe'
	<?php if ($_POST["type"] != "helpbox" && $_GET["type"] != "helpbox"){?>
	onload="<?php echo $startcountdown?>" <?php } else {?>
	onload="hbquota()" <?php } ?>>
<?php
if ($_POST ["sent"] == "yes") {
	if (check_emotion($_POST ["shbox_text"])) {
		$userid = 0 + $CURUSER ["id"];
		print "<script type=\"text/javascript\">parent.document.forms['shbox'].shbox_text.value='';parent.document.forms['shbox'].shbox_text.placeholder='输入无效';</script>";
	} else {
		if ($_POST ["type"] == "helpbox" || $_GET ["type"] == "helpbox") {
			if ($showhelpbox_main != 'yes') {
				// write_log("Someone is hacking shoutbox. - IP :
				// ".getip(),'mod');
				die ( $lang_shoutbox ['text_helpbox_disabled'] );
			}
			$userid = 0;
			$type = 'hb';
			$ip = getip ();
		} elseif ($_POST ["type"] == 'shoutbox' || $_GET ["type"] == 'shoutbox') {
			$userid = 0 + $CURUSER ["id"];
			if (! $userid || $CURUSER ["forumpost"] == 'no') {
				// write_log("Someone is hacking shoutbox. - IP :
				// ".getip(),'mod');
				die ( $lang_shoutbox ['text_no_permission_to_shoutbox'] );
			}
			if ($_POST ["toguest"])
				$type = 'hb';
			else
				$type = 'sb';
		}
		$date = sqlesc ( time () );
		$text = trim ( $_POST ["shbox_text"] );
		$text = str_replace ( "[img", "[ img", $text );
		$text = str_replace ( "[flv", "[ flv", $text );
		$text = str_replace ( "[flash", "[ flash", $text );
		$text = str_replace ( "[music", "[ music", $text );

		if (preg_match_all ( '/@游客[^\x{4e00}-\x{9fa5}]/iu', sqlesc ( $text ), $matches )) {
			$type = 'hb';
		}
		if ($userid === 10) {
			$userid = 0;
			$ip = "北洋媛隐身啦～啦啦啦～";
		}
		sql_query ( "INSERT INTO shoutbox (userid, date, text, type, ip) VALUES (" . sqlesc ( $userid ) . ", $date, " . sqlesc ( $text ) . ", " . sqlesc ( $type ) . ", '$ip' )" ) or sqlerr ( __FILE__, __LINE__ );
		// sql_query("INSERT INTO shoutbox (userid, date, text, type) VALUES ("
		// . sqlesc($userid) . ", $date, " . sqlesc($text) . ",
		// ".sqlesc($type)." )") or sqlerr(__FILE__, __LINE__);

		// *******************
		// PM被@的用户
		if ($CURUSER ["id"]) {
			if (0 + $CURUSER ["id"] === 10) {
				$msg = '[b]' . $CURUSER ["username"] . '[/b]说：' . $text;
			} else {
				$msg = '[b][url=userdetails.php?id=' . $CURUSER ["id"] . ']' . $CURUSER ["username"] . '[/url][/b]说：' . $text;
			}
		} else {
			$msg = '[b]游客[/b]说：' . $text;
		}
		pm_at_users ( $text, $msg, "shoutbox" );
		// *******************

		print "<script type=\"text/javascript\">parent.document.forms['shbox'].shbox_text.value='';</script>";
	}
}

$limit = ($CURUSER ['sbnum'] ? $CURUSER ['sbnum'] : 70);

if ($where == "helpbox") {
	$sql = "SELECT * FROM shoutbox WHERE type='hb' ORDER BY date DESC LIMIT " . $limit;
} elseif ($CURUSER ['hidehb'] == 'yes' || $showhelpbox_main != 'yes') {
	$sql = "SELECT * FROM shoutbox WHERE type='sb' ORDER BY date DESC LIMIT " . $limit;
} elseif ($CURUSER) {
	$sql = "SELECT * FROM shoutbox ORDER BY date DESC LIMIT " . $limit;
} else {
	die ( "<h1>" . $lang_shoutbox ['std_access_denied'] . "</h1>" . "<p>" . $lang_shoutbox ['std_access_denied_note'] . "</p></body></html>" );
}
$res = sql_query ( $sql ) or sqlerr ( __FILE__, __LINE__ );
if (mysql_num_rows ( $res ) == 0)
	print ("\n") ;
else {
	print ("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='left'>\n") ;

	if ($where == "helpbox") {
		$ip = getip ();
		$nip = ip2long ( $ip );
		if ($nip) {
			$nontjuip = sql_query ( "SELECT * FROM nontjuip WHERE $nip >= first AND $nip <= last" ) or sqlerr ( __FILE__, __LINE__ );
			if (mysql_num_rows ( $nontjuip ) > 0 && $where == "helpbox") {
				print ("校外IPv4用户需要登录之后才能看到群聊区") ;
				die ();
			}
		}

	}
		print ("<font size=4 color=#FF0000><b>禁止刷屏、求种、求魔、拉票、广告及发布纯表情等无实质意义内容，严禁粗口，否则严惩！</b>(<a class='faqlink' href='faq.php' target='_blank'>常见问题</a>,&nbsp;<a class='faqlink' href='rules.php' target='_blank'>本站规则</a>,&nbsp;<a class='faqlink' href='invitebox.php' target='_blank'>申请邀请码</a>)</font>\n") ;
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		if (get_user_class () >= $sbmanage_class) {
			$del = "[<a href=\"shoutbox.php?del=" . $arr [id] . "\">" . $lang_shoutbox ['text_del'] . "</a>]";
		}
		if ($arr ["userid"]) {
			$username = get_username ( $arr ["userid"], false, true, true, true, false, false, "", true );
			$arr_user = get_user_row ( $arr ["userid"] );
			$usernamesb = $arr_user ['username'];
			if ($_POST ["type"] != 'helpbox' && $_GET ["type"] != 'helpbox' && $arr ["userid"])
				// $username = "<a href=\"javascript: response('" . $usernamesb
				// . "')\">[@]</a> " . $username;
				$username = $username . '：';
			// if ($_POST ["type"] != 'helpbox' && $_GET ["type"] != 'helpbox'
			// && $arr ["type"] == 'hb')
			// $username .= " " . $lang_shoutbox ['text_to_guest'] . "：";
		} elseif (get_user_class () >= $sbmanage_class && $arr ["type"] == 'hb' && $arr ["ip"] != "北洋媛隐身啦～啦啦啦～") {
			$username = "<a title=\"IP: " . $arr ["ip"] . " \" href=\"reghistory.php?id=" . $arr ["id"] . "\" target=\"_blank\" >" . $lang_shoutbox ['text_guest'] . "</a>：";
			$usernamesb = '游客';
		} elseif ($arr ["type"] == 'hb' && $arr ["ip"] != "北洋媛隐身啦～啦啦啦～") {
			$username = "<a title=\"IP End With: " . str_replace ( ".", "", strrchr ( $arr ["ip"], "." ) ) . str_replace ( ":", "", strrchr ( $arr ["ip"], ":" ) ) . " \"  href=\"reghistory.php?id=" . $arr ["id"] . "\" target=\"_blank\" >" . $lang_shoutbox ['text_guest'] . "</a>：";
			$usernamesb = '游客';
		} else {
			if ($CURUSER ['id']) {
				$username = "<a title=\"IP: " . $arr ["ip"] . " \" href=\"userdetails.php?id=" . $CURUSER ['id'] . "\" target=\"_top\"><strong><u><font color='FF00FF'>北洋媛</font></u></strong></a>：";
			} else {
				$username = "<strong><u><font color='FF00FF'>北洋媛</font></u></strong>：";
			}
			$usernamesb = '北洋媛';
		}
		if ($CURUSER ['timetype'] != 'timealive')
			$time = strftime ( "%m.%d %H:%M", $arr ["date"] );
		else
			$time = get_elapsed_time ( $arr ["date"] ) . $lang_shoutbox ['text_ago'];
		print ("<tr><td class=\"shoutrow\" ocr=\"$usernamesb\">" . $del . " " . "<span class='date'>[" . $time . "]</span> " . "$username" . " " . str_replace ( "[ ", "[", format_comment ( $arr ["text"], true, false, true, true, 600, true, false, -1, 0, 0, false ) ) . "
</td></tr>\n") ;
	}
	print ("</table>") ;
}
?>
</body>
</html>
