 <?php
/* give bonus from torrent */

require_once('include/bittorrent.php');
dbconn();
require_once(get_langfile_path());
require(get_langfile_path("",true));
loggedinorreturn();
parked();



 if ($_GET['torrentid'])
	stderr("Party is over!", "This trick doesn't work anymore. You need to click the button!");
	
$userid = 0 + $CURUSER["id"];
$torrentid = 0 + $_POST["torrentid"];







$tsql = sql_query("SELECT owner, name FROM torrents where id=" . $torrentid);
$arr = mysql_fetch_array($tsql);
/* if (!$arr)
	stderr("Error", "Invalid torrent id!");
	 */
$useridgift = $arr['owner'];    //这里取出来的是一个ID，种子所有者
$torrientfilename= $arr['name'];   //种子名称

$tsql = sql_query("SELECT id, username FROM users WHERE id=" . $useridgift);
$arr = mysql_fetch_array($tsql);




$ownername = $arr['username'];    //种子所有者名称

			$points_t = 0 + $_POST["bonus"];

			if ($points_t <=0 )die;
					
			if ($points_t >= 10000)$points_t = 10000;
			$points=$points_t;
		
			$res = sql_query("SELECT id,seedbonus,bonuscomment FROM users WHERE id=" . $useridgift);    //UserGift
			$arr = mysql_fetch_assoc($res);
			
			$userseedbonus = $arr['seedbonus'];
			$receiverbonuscomment = $arr['bonuscomment'];
			

			if($CURUSER['seedbonus'] >= $points) {
				$bonuscomment = $CURUSER['bonuscomment'];
				$points2= number_format($points,1);
				$bonuscomment = date("Y-m-d") . " 给 ".htmlspecialchars(trim($ownername))."赠送了" .$points2. "个魔力值。\n " .htmlspecialchars($bonuscomment);

	

				$aftertaxpoint = $points-(15+ $points*0.1);
				if ($aftertaxpoint < 0)
					$aftertaxpoint = 0;

				$points2receiver = number_format($aftertaxpoint,1);
				$newreceiverbonuscomment = date("Y-m-d") . " 从 ".($CURUSER["username"])." 那儿收到 " .$points2receiver. " 个魔力值(税后).\n " .htmlspecialchars($receiverbonuscomment);
				
		

					
				
/* 				if (!$useridgift){
					stdmsg($lang_givebonus['text_error'], $lang_givebonus['text_receiver_not_exists'], 0);
					stdfoot();
					die;
				} */


				//////update user's bonus

				sql_query("UPDATE users SET seedbonus = seedbonus - $points, bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
				sql_query("UPDATE users SET seedbonus = seedbonus + $aftertaxpoint, bonuscomment = ".sqlesc($newreceiverbonuscomment)." WHERE id = ".sqlesc($useridgift));
 
				
				
				
				/////////////update table givebonus (type=1 means bonus from torrent)
				
				
				$type=1;
				$res = sql_query("INSERT INTO givebonus (bonusfromuserid, bonustotorrentid, bonus, type) VALUES ($userid, $torrentid, $points, $type)");
				
				
				
				
				
				
				
				
				//===send message
				$subject = sqlesc($lang_givebonus_target[get_user_lang($useridgift)]['msg_someone_loves_you']);
				$added = sqlesc(date("Y-m-d H:i:s"));
				$msg = $lang_givebonus_target[get_user_lang($useridgift)]['msg_torrent']."[b][url=details.php?id=$torrentid]" .$torrientfilename. "[/url][/b]".$lang_givebonus_target[get_user_lang($useridgift)]['msg_you_have_been_given'].$points2.$lang_givebonus_target[get_user_lang($useridgift)]['msg_after_tax'].$points2receiver.$lang_givebonus_target[get_user_lang($useridgift)]['msg_karma_points_by'].$CURUSER['username'];
				if ($message)
					$msg .= "\n".$lang_givebonus_target[get_user_lang($useridgift)]['msg_personal_message_from'].$CURUSER['username'].$lang_givebonus_target[get_user_lang($useridgift)]['msg_colon'].$message;
				$msg = sqlesc($msg);
				sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES(0, $subject, $useridgift, $msg, $added)") or sqlerr(__FILE__, __LINE__);
				$usernamegift = unesc($_POST["username"]);
				redirect("" . get_protocol_prefix() . "$BASEURL/mybonus.php?do=transfer");
				
				
				
				$temp= $lang_givebonus_target[get_user_lang($useridgift)]['msg_successful'].$points.$lang_givebonus_target[get_user_lang($useridgift)]['msg_successful0']; 
				
				

$CURUSER['giveseedbonus']=$points;

	
      } 
  


	
?>
