<?php
require_once ('include/bittorrent.php');
dbconn ();
require_once (get_langfile_path ());
require (get_langfile_path ( "", true ));
loggedinorreturn ();
parked ();

if (! $invite_bonus = $Cache->get_value ( 'invite_bonus' )) {
	$totalalive = get_row_count ( "users", "WHERE status!='pending' AND class > 0 AND enabled='yes'" );
	$totalbonus = get_single_value ( "users WHERE enabled='yes'", "sum(seedbonus) " );
	$totalinvites = get_single_value ( "users WHERE enabled='yes'", "sum(invites)" );
	// $invite_bonus=$oneinvite_bonus*exp(($totalalive+$totalinvites/8-$maxusers)/800);
	$invite_bonus = 0.75 * $oneinvite_bonus / 100000000 * $totalbonus * exp ( $totalalive / 20000 ) * (log ( $totalinvites + 1 ) + log ( $maxusers / ($maxusers - $totalalive) )) / 25 - (rand ( 0, 1895 ) > time () % 1895 ? time () % 1895 : rand ( 0, 1895 ));
	$Cache->cache_value ( 'invite_bonus', $invite_bonus, 300 );
}
function bonusarray($option) {
	global $onegbupload_bonus, $fivegbupload_bonus, $tengbupload_bonus, $hundredgbupload_bonus, $oneinvite_bonus, $customtitle_bonus, $vipstatus_bonus, $basictax_bonus, $taxpercentage_bonus, $bonusnoadpoint_advertisement, $bonusnoadtime_advertisement, $enablebonusnoad_advertisement, $invite_bonus, $custumcolor_bonus, $rename_bonus;
	global $lang_mybonus;
	global $Cache, $CURUSER;
	$bonus = array ();
	switch ($option) {
		case 1 :
			{ // 1.0 GB Uploaded
				$bonus ['points'] = $onegbupload_bonus;
				$bonus ['art'] = 'traffic';
				$bonus ['menge'] = 1073741824;
				$bonus ['name'] = $lang_mybonus ['text_uploaded_one'];
				$bonus ['description'] = $lang_mybonus ['text_uploaded_note'];
				break;
			}
		// case 2 :
		// { // 5.0 GB Uploaded
		// $bonus ['points'] = $fivegbupload_bonus;
		// $bonus ['art'] = 'traffic';
		// $bonus ['menge'] = 5368709120;
		// $bonus ['name'] = $lang_mybonus ['text_uploaded_two'];
		// $bonus ['description'] = $lang_mybonus ['text_uploaded_note'];
		// break;
		// }
		case 2 :
			{ // 10.0 GB Uploaded
				$bonus ['points'] = $tengbupload_bonus;
				$bonus ['art'] = 'traffic';
				$bonus ['menge'] = 10737418240;
				$bonus ['name'] = $lang_mybonus ['text_uploaded_three'];
				$bonus ['description'] = $lang_mybonus ['text_uploaded_note'];
				break;
			}
		case 3 :
			{ // 100.0 GB Uploaded
				$bonus ['points'] = $hundredgbupload_bonus;
				$bonus ['art'] = 'traffic';
				$bonus ['menge'] = 107374182400;
				$bonus ['name'] = $lang_mybonus ['text_uploaded_four'];
				$bonus ['description'] = $lang_mybonus ['text_uploaded_note'];
				break;
			}
		case 4 :
			{ // Invite
				$bonus ['points'] = ( int ) $invite_bonus;
				$bonus ['art'] = 'invite';
				$bonus ['menge'] = 1;
				$bonus ['name'] = $lang_mybonus ['text_buy_invite'];
				$bonus ['description'] = $lang_mybonus ['text_buy_invite_note'];
				break;
			}
		case 5 :
			{ // Custom Title
				$bonus ['points'] = $customtitle_bonus;
				$bonus ['art'] = 'title';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonus ['text_custom_title'];
				$bonus ['description'] = $lang_mybonus ['text_custom_title_note'];
				break;
			}
		case 6 :
			{ // VIP Status
			  // $bonus ['points'] = $vipstatus_bonus / 10 * (get_user_class () >=
			  // 10 ? 0 : 10 - get_user_class ());
				$bonus ['points'] = get_user_class () >= 10 ? 0 : $vipstatus_bonus;
				$bonus ['art'] = 'class';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonus ['text_vip_status'];
				$bonus ['description'] = $lang_mybonus ['text_vip_status_note'];
				break;
			}
		case 7 :
			{ // Bonus Gift
				$bonus ['points'] = 25;
				$bonus ['art'] = 'gift_1';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonus ['text_bonus_gift'];
				$bonus ['description'] = $lang_mybonus ['text_bonus_gift_note'];
				if ($basictax_bonus || $taxpercentage_bonus) {
					$onehundredaftertax = 100 - $taxpercentage_bonus - $basictax_bonus;
					$bonus ['description'] .= "<br /><br />" . $lang_mybonus ['text_system_charges_receiver'] . "<b>" . ($basictax_bonus ? $basictax_bonus . $lang_mybonus ['text_tax_bonus_point'] . add_s ( $basictax_bonus ) . ($taxpercentage_bonus ? $lang_mybonus ['text_tax_plus'] : "") : "") . ($taxpercentage_bonus ? $taxpercentage_bonus . $lang_mybonus ['text_percent_of_transfered_amount'] : "") . "</b>" . $lang_mybonus ['text_as_tax'] . $onehundredaftertax . $lang_mybonus ['text_tax_example_note'];
				}
				break;
			}
		case 8 :
			{
				$bonus ['points'] = $bonusnoadpoint_advertisement * $bonusnoadtime_advertisement;
				$bonus ['art'] = 'noad';
				$bonus ['menge'] = $bonusnoadtime_advertisement * 86400;
				$bonus ['name'] = $bonusnoadtime_advertisement . $lang_mybonus ['text_no_advertisements'];
				$bonus ['description'] = $lang_mybonus ['text_no_advertisements_note'];
				break;
			}
		case 9 :
			{
				$bonus ['points'] = 1000;
				$bonus ['art'] = 'gift_2';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonus ['text_charity_giving'];
				$bonus ['description'] = $lang_mybonus ['text_charity_giving_note'];
				break;
			}
		case 10 :
			{ //复活
				$bonus ['points'] = ( int ) (0.6 * $invite_bonus);
				$bonus ['art'] = 'enableaccount';
				$bonus ['menge'] = 0;
				$bonus ['name'] = $lang_mybonus ['text_enable_account'];
				$bonus ['description'] = $lang_mybonus ['text_enable_account_note'];
				break;
			}
		case 11 :
			{ //出售邀请
				$bonus ['points'] = - ( int ) (0.8 * $invite_bonus);
				$bonus ['art'] = 'invite_for_sale';
				$bonus ['menge'] = 1;
				$bonus ['name'] = "出售邀请";
				$bonus ['description'] = "您可以在这里将当前拥有的邀请资格兑换成魔力值。";
				break;
			}
		case 12 :
			{ //变色龙
				$bonus ['points'] = $custumcolor_bonus;
				$bonus ['art'] = 'custom_color';
				$bonus ['menge'] = 1;
				$bonus ['name'] = "变色龙";
				$bonus ['description'] = $lang_mybonus ['text_custom_color_note'];
				break;
			}
		case 13 :
			{ //
				$bonus ['points'] = $rename_bonus * pow ( 2, 0 + $CURUSER ['renamenum'] );
				$bonus ['art'] = 'rename';
				$bonus ['menge'] = 1;
				$bonus ['name'] = "园道&bull;轮回天生";
				$bonus ['description'] = '获得新的用户名，同时原用户名将可被其他园友使用。每用一次，都会使你下次使用的成本加倍。<br/>有效字符：字母、数字、汉字，至多12个字符。<b>查看<a class="faqlink" href="./app_renamelog.php">使用记录</a></b><br/><br/><b>注意</b>：包含色情、暴力、反动等内容的用户名将被禁止。';
				break;
			}
		case 20 :
			{ //
				$bonus ['points'] = 100;
				$bonus ['art'] = 'enableaccount';
				$bonus ['menge'] = 0;
				$bonus ['name'] = "21点";
				$bonus ['description'] = "传统的21点游戏,您要抓足够接近21点，和对手对抗。<br />A在总分不超过21时作11，总分超过21则作1。J,Q,K作为10。";
				break;
			}
		
		default :
			break;
	}
	return $bonus;
}

if ($bonus_tweak == "disable" || $bonus_tweak == "disablesave")
	stderr ( $lang_mybonus ['std_sorry'], $lang_mybonus ['std_karma_system_disabled'] . ($bonus_tweak == "disablesave" ? "<b>" . $lang_mybonus ['std_points_active'] . "</b>" : ""), false );

$action = htmlspecialchars ( $_GET ['action'] );
$do = htmlspecialchars ( $_GET ['do'] );
unset ( $msg );
if (isset ( $do )) {
	$msg = '';
}
stdhead ( $CURUSER ['username'] . $lang_mybonus ['head_karma_page'] );

$bonus = number_format ( $CURUSER ['seedbonus'], 1 );
if (! $action) {
	print ("<table align=\"center\" width=\"940\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n") ;
	print ("<tr><td class=\"colhead\" colspan=\"4\" align=\"center\"><font class=\"big\">" . $SITENAME . $lang_mybonus ['text_karma_system'] . "</font></td></tr>\n") ;
	if ($msg)
		print ("<tr><td align=\"center\" colspan=\"4\"><font class=\"striking\">" . $msg . "</font></td></tr>") ;
	?>
<tr>
	<td class="text" align="center" colspan="4"><?php echo $lang_mybonus['text_exchange_your_karma']?><?php echo $bonus?><?php echo $lang_mybonus['text_for_goodies']?>
<br /> <b><?php echo $lang_mybonus['text_no_buttons_note'] ?></b></td>
</tr>
<?php
	
	print ("<tr><td class=\"colhead\" align=\"center\">" . $lang_mybonus ['col_option'] . "</td>" . "<td class=\"colhead\" align=\"left\">" . $lang_mybonus ['col_description'] . "</td>" . "<td class=\"colhead\" align=\"center\">" . $lang_mybonus ['col_points'] . "</td>" . "<td class=\"colhead\" align=\"center\">" . $lang_mybonus ['col_trade'] . "</td>" . "</tr>") ;
	for($i = 1, $j = 0; $i < 20; $i ++) {
		$bonusarray = bonusarray ( $i );
		if (($i == 7 && $bonusgift_bonus == 'no') || ($i == 8 && ($enablead_advertisement == 'no' || ! ($enablebonusnoad_advertisement == 'yes'))) || ($i >= 14 && $i < 20))
			continue; // 11到19为预留待开发项目
		if ($i == 20)
			print ("<tr><td class=\"colhead\" colspan=\"4\" align=\"center\"><font class=\"big\">注意：以下内容属于趣味游戏，不是兑换项目。</font></td></tr>\n") ;
		$j ++;
		print ("<tr>") ;
		print ("<form action=\"?action=exchange\" method=\"post\">") ;
		print ("<td class=\"rowhead_center\"><input type=\"hidden\" name=\"option\" value=\"" . $i . "\" /><b>" . $j . "</b></td>") ;
		if ($i == 5) { // for Custom Title!
			$otheroption_title = "<input type=\"text\" name=\"title\" style=\"width: 200px\" maxlength=\"30\" />";
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "<br /><br />" . $lang_mybonus ['text_enter_titile'] . $otheroption_title . $lang_mybonus ['text_click_exchange'] . "</td><td class=\"rowfollow\" align='center'>" . number_format ( $bonusarray ['points'] ) . "</td>") ;
		} elseif ($i == 10) { // for Custom Title!
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "<br /><br />" . $lang_mybonus ['text_enter_enable_account_name'] . "<br/><b>" . $lang_mybonus ['text_username'] . "</b><input type=\"text\" name=\"username\" style=\"width: 200px\" maxlength=\"30\" /></td><td class=\"rowfollow\" align='center'>" . number_format ( $bonusarray ['points'] ) . "</td>") ;
		} elseif ($i == 13) { // for Rename!
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "<br /><br />" . "<b>请输入新的" . $lang_mybonus ['text_username'] . "</b><input type=\"text\" name=\"newusername\" style=\"width: 200px\" maxlength=\"30\" /></td><td class=\"rowfollow\" align='center'>" . number_format ( $bonusarray ['points'] ) . "</td>") ;
		} elseif ($i == 7) { // for Give A Karma Gift
			$otheroption = "<table width=\"100%\"><tr><td class=\"embedded\"><b>" . $lang_mybonus ['text_username'] . "</b><input type=\"text\" name=\"username\" style=\"width: 200px\" maxlength=\"24\" /></td><td class=\"embedded\"><b>" . $lang_mybonus ['text_to_be_given'] . "</b><select name=\"bonusgift\" id=\"giftselect\" onchange=\"customgift();\"> <option value=\"25\"> 25</option><option value=\"50\"> 50</option><option value=\"100\"> 100</option> <option value=\"200\"> 200</option> <option value=\"300\"> 300</option> <option value=\"400\"> 400</option><option value=\"500\"> 500</option><option value=\"1000\" selected=\"selected\"> 1,000</option><option value=\"5000\"> 5,000</option><option value=\"10000\"> 10,000</option><option value=\"0\">" . $lang_mybonus ['text_custom'] . "</option></select><input type=\"text\" name=\"bonusgift\" id=\"giftcustom\" style='width: 80px' disabled=\"disabled\" />" . $lang_mybonus ['text_karma_points'] . "</td></tr><tr><td class=\"embedded\" colspan=\"2\"><b>" . $lang_mybonus ['text_message'] . "</b><input type=\"text\" name=\"message\" style=\"width: 400px\" maxlength=\"100\" /></td></tr></table>";
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "<br /><br />" . $lang_mybonus ['text_enter_receiver_name'] . "<br />$otheroption</td><td class=\"rowfollow nowrap\" align='center'>" . $lang_mybonus ['text_min'] . "25<br />" . $lang_mybonus ['text_max'] . "10,000</td>") ;
		} elseif ($i == 9) { // charity giving
			$otheroption = "<table width=\"100%\"><tr><td class=\"embedded\">" . $lang_mybonus ['text_ratio_below'] . "<select name=\"ratiocharity\"> <option value=\"0.1\"> 0.1</option><option value=\"0.2\"> 0.2</option><option value=\"0.3\" selected=\"selected\"> 0.3</option> <option value=\"0.4\"> 0.4</option> <option value=\"0.5\"> 0.5</option> <option value=\"0.6\"> 0.6</option><option value=\"0.7\"> 0.7</option><option value=\"0.8\"> 0.8</option></select>" . $lang_mybonus ['text_and_downloaded_above'] . " 10 GB</td><td class=\"embedded\"><b>" . $lang_mybonus ['text_to_be_given'] . "</b><select name=\"bonuscharity\" id=\"charityselect\" > <option value=\"1000\"> 1,000</option><option value=\"2000\"> 2,000</option><option value=\"3000\" selected=\"selected\"> 3000</option> <option value=\"5000\"> 5,000</option> <option value=\"8000\"> 8,000</option> <option value=\"10000\"> 10,000</option><option value=\"20000\"> 20,000</option><option value=\"50000\"> 50,000</option></select>" . $lang_mybonus ['text_karma_points'] . "</td></tr></table>";
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "<br /><br />" . $lang_mybonus ['text_select_receiver_ratio'] . "<br />$otheroption</td><td class=\"rowfollow nowrap\" align='center'>" . $lang_mybonus ['text_min'] . "1,000<br />" . $lang_mybonus ['text_max'] . "50,000</td>") ;
		} elseif ($i == 20) {
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "<br /><br /></td><td class=\"rowfollow nowrap\" align='center'>每局100</td>") ;
			print ("<td class=\"rowfollow\" align=\"center\"><input type=button value=\"" . ($CURUSER ["class"] < UC_USER ? "User" . $lang_mybonus ['text_plus_only'] . "\" disabled" : ($CURUSER ['seedbonus'] < 100 ? $lang_mybonus ['text_more_points_needed'] . "\" disabled" : "我要玩\" onclick=\"location.href='blackjack.php'\"")) . "/></td>") ;
			continue;
		} else { // for VIP or Upload
			print ("<td class=\"rowfollow\" align='left'><h1>" . $bonusarray ['name'] . "</h1>" . $bonusarray ['description'] . "</td><td class=\"rowfollow\" align='center'>" . number_format ( $bonusarray ['points'] ) . "</td>") ;
		}
		
		if ($CURUSER ['seedbonus'] >= $bonusarray ['points']) {
			if ($i == 7) {
				print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_karma_gift'] . "\" /></td>") ;
			} elseif ($i == 8) {
				if ($enablenoad_advertisement == 'yes' && get_user_class () >= $noad_advertisement)
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_class_above_no_ad'] . "\" disabled=\"disabled\" /></td>") ;
				elseif (strtotime ( $CURUSER ['noaduntil'] ) >= TIMENOW)
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_already_disabled'] . "\" disabled=\"disabled\" /></td>") ;
				elseif (get_user_class () < $bonusnoad_advertisement)
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . get_user_class_name ( $bonusnoad_advertisement, false, false, true ) . $lang_mybonus ['text_plus_only'] . "\" disabled=\"disabled\" /></td>") ;
				else
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_exchange'] . "\" /></td>") ;
			} elseif ($i == 9) {
				print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_charity_giving'] . "\" /></td>") ;
			} elseif ($i == 4) {
				if (get_user_class () < $buyinvite_class)
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . get_user_class_name ( $buyinvite_class, false, false, true ) . $lang_mybonus ['text_plus_only'] . "\" disabled=\"disabled\" /></td>") ;
				else
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"hidden\" name=\"ori_points\" value=\"" . $bonusarray ['points'] . "\" /><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_exchange'] . "\" /></td>") ;
			} elseif ($i == 11) {
				if ($CURUSER ["invites"] < 1)
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"您没有邀请名额出售\" disabled=\"disabled\" /></td>") ;
				else
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"hidden\" name=\"ori_points\" value=\"" . $bonusarray ['points'] . "\" /><input type=\"submit\" name=\"submit\" value=\"出售\" /></td>") ;
			} elseif ($i == 6) {
				if (get_user_class () >= UC_VIP)
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['std_class_above_vip'] . "\" disabled=\"disabled\" /></td>") ;
				else
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_exchange'] . "\" /></td>") ;
			} elseif ($i == 5) {
				print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_exchange'] . "\" /></td>") ;
			} elseif ($i == 12) {
				print ("<td class=\"rowfollow\" align=\"center\"><input size=\"7\" maxlength=\"7\" class=\"color\" name=\"color\" value=\"" . $CURUSER ['color'] . "\" /><input type=\"submit\" name=\"submit\" value=\"马上变色\" /></td>") ;
			} elseif ($i == 10) {
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"hidden\" name=\"ori_points\" value=\"" . $bonusarray ['points'] . "\" /><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['text_enable_account'] . "\" /></td>") ;
			} elseif ($i == 13) {
				print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"使用\" /></td>") ;
			} else {
				if ($CURUSER ['downloaded'] > 0) {
					if ($CURUSER ['uploaded'] > $dlamountlimit_bonus * 1073741824) // Uploaded
					                                                               // amount
					                                                               // reach
					                                                               // limit
						$ratio = $CURUSER ['uploaded'] / $CURUSER ['downloaded'];
					else
						$ratio = 0;
				} else
					$ratio = $ratiolimit_bonus + 1; // Ratio always above limit
				if ($ratiolimit_bonus > 0 && $ratio > $ratiolimit_bonus) {
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['text_ratio_too_high'] . "\" disabled=\"disabled\" /></td>") ;
				} else
					print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['submit_exchange'] . "\" /></td>") ;
			}
		} else {
			print ("<td class=\"rowfollow\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"" . $lang_mybonus ['text_more_points_needed'] . "\" disabled=\"disabled\" /></td>") ;
		}
		print ("</form>") ;
		print ("</tr>") ;
	}
	
	print ("</table><br />") ;
	?>

<table width="940" cellpadding="3">
	<tr>
		<td class="colhead" align="center"><font class="big"><?php echo $lang_mybonus['text_what_is_karma'] ?></font></td>
	</tr>
	<tr>
		<td class="text" align="left">
<?php
	print ("<h1>" . $lang_mybonus ['text_get_by_seeding'] . "</h1>") ;
	print ("<ul>") ;
	if ($perseeding_bonus > 0)
		print ("<li>" . $perseeding_bonus . $lang_mybonus ['text_point'] . add_s ( $perseeding_bonus ) . $lang_mybonus ['text_for_seeding_torrent'] . $maxseeding_bonus . $lang_mybonus ['text_torrent'] . add_s ( $maxseeding_bonus ) . ")</li>") ;
	print ("<li>" . $lang_mybonus ['text_bonus_formula_one'] . $tzero_bonus . $lang_mybonus ['text_bonus_formula_two'] . $nzero_bonus . $lang_mybonus ['text_bonus_formula_three'] . $bzero_bonus . $lang_mybonus ['text_bonus_formula_four'] . $l_bonus . $lang_mybonus ['text_bonus_formula_five'] . "</li>") ;
	if ($donortimes_bonus)
		print ("<li>" . $lang_mybonus ['text_donors_always_get'] . $donortimes_bonus . $lang_mybonus ['text_times_of_bonus'] . "</li>") ;
	print ("</ul>") ;
	
	$sqrtof2 = sqrt ( 2 );
	$logofpointone = log ( 0.1 );
	$valueone = $logofpointone / $tzero_bonus;
	$pi = 3.141592653589793;
	$valuetwo = $bzero_bonus * (2 / $pi);
	$valuethree = $logofpointone / ($nzero_bonus - 1);
	$timenow = strtotime ( date ( "Y-m-d H:i:s" ) );
	$sectoweek = 7 * 24 * 60 * 60;
	$A = 0;
	$count = 0;
	$torrentres = sql_query ( "select torrents.id, torrents.added, torrents.needkeepseed, torrents.size, torrents.seeders from torrents LEFT JOIN peers ON peers.torrent = torrents.id WHERE peers.userid = $CURUSER[id] AND peers.seeder ='yes' GROUP BY torrents.id" ) or sqlerr ( __FILE__, __LINE__ );
	while ( $torrent = mysql_fetch_array ( $torrentres ) ) {
		$weeks_alive = ($timenow - strtotime ( $torrent [added] )) / $sectoweek;
		$gb_size = ($torrent [needkeepseed]=='yes'?($torrent [size] / 214748365):($torrent [size] / 1073741824));
		$temp = (1 - exp ( $valueone * $weeks_alive )) * $gb_size * (1 + $sqrtof2 * exp ( $valuethree * ($torrent [seeders] - 1) ));
		$A += $temp;
		$count ++;
	}
	if ($count > $maxseeding_bonus)
		$count = $maxseeding_bonus;
	$all_bonus = $valuetwo * atan ( $A / $l_bonus ) + ($perseeding_bonus * $count);
	$percent = $all_bonus * 100 / ($bzero_bonus + $perseeding_bonus * $maxseeding_bonus);
	print ("<div align=\"center\">" . $lang_mybonus ['text_you_are_currently_getting'] . round ( $all_bonus, 3 ) . $lang_mybonus ['text_point'] . add_s ( $all_bonus ) . $lang_mybonus ['text_per_hour'] . " (A = " . round ( $A, 1 ) . ")</div><table align=\"center\" border=\"0\" width=\"400\"><tr><td class=\"loadbarbg\" style='border: none; padding: 0px;'>") ;
	
	if ($percent <= 30)
		$loadpic = "loadbarred";
	elseif ($percent <= 60)
		$loadpic = "loadbaryellow";
	else
		$loadpic = "loadbargreen";
	$width = $percent * 4;
	print ("<img class=\"" . $loadpic . "\" src=\"pic/trans.gif\" style=\"width: " . $width . "px;\" alt=\"" . $percent . "%\" /></td></tr></table>") ;
	
	print ("<h1>" . $lang_mybonus ['text_other_things_get_bonus'] . "</h1>") ;
	print ("<ul>") ;
	if ($uploadtorrent_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_upload_torrent'] . $uploadtorrent_bonus . $lang_mybonus ['text_point'] . add_s ( $uploadtorrent_bonus ) . "</li>") ;
	if ($uploadsubtitle_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_upload_subtitle'] . $uploadsubtitle_bonus . $lang_mybonus ['text_point'] . add_s ( $uploadsubtitle_bonus ) . "</li>") ;
	if ($starttopic_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_start_topic'] . $starttopic_bonus . $lang_mybonus ['text_point'] . add_s ( $starttopic_bonus ) . "</li>") ;
	if ($makepost_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_make_post'] . $makepost_bonus . $lang_mybonus ['text_point'] . add_s ( $makepost_bonus ) . "</li>") ;
	if ($addcomment_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_add_comment'] . $addcomment_bonus . $lang_mybonus ['text_point'] . add_s ( $addcomment_bonus ) . "</li>") ;
	if ($pollvote_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_poll_vote'] . $pollvote_bonus . $lang_mybonus ['text_point'] . add_s ( $pollvote_bonus ) . "</li>") ;
	if ($offervote_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_offer_vote'] . $offervote_bonus . $lang_mybonus ['text_point'] . add_s ( $offervote_bonus ) . "</li>") ;
	if ($funboxvote_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_funbox_vote'] . $funboxvote_bonus . $lang_mybonus ['text_point'] . add_s ( $funboxvote_bonus ) . "</li>") ;
	if ($ratetorrent_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_rate_torrent'] . $ratetorrent_bonus . $lang_mybonus ['text_point'] . add_s ( $ratetorrent_bonus ) . "</li>") ;
	if ($saythanks_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_say_thanks'] . $saythanks_bonus . $lang_mybonus ['text_point'] . add_s ( $saythanks_bonus ) . "</li>") ;
	if ($receivethanks_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_receive_thanks'] . $receivethanks_bonus . $lang_mybonus ['text_point'] . add_s ( $receivethanks_bonus ) . "</li>") ;
	if ($adclickbonus_advertisement > 0)
		print ("<li>" . $lang_mybonus ['text_click_on_ad'] . $adclickbonus_advertisement . $lang_mybonus ['text_point'] . add_s ( $adclickbonus_advertisement ) . "</li>") ;
	print ("<li>" . $lang_mybonus ['text_get_reseed'] . "</li>") ;
	if ($prolinkpoint_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_promotion_link_clicked'] . $prolinkpoint_bonus . $lang_mybonus ['text_point'] . add_s ( $prolinkpoint_bonus ) . "</li>") ;
	if ($funboxreward_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_funbox_reward'] . "</li>") ;
	print ($lang_mybonus ['text_howto_get_karma_four']) ;
	if ($ratiolimit_bonus > 0)
		print ("<li>" . $lang_mybonus ['text_user_with_ratio_above'] . $ratiolimit_bonus . $lang_mybonus ['text_and_uploaded_amount_above'] . $dlamountlimit_bonus . $lang_mybonus ['text_cannot_exchange_uploading'] . "</li>") ;
	print ($lang_mybonus ['text_howto_get_karma_five'] . $uploadtorrent_bonus . $lang_mybonus ['text_point'] . add_s ( $uploadtorrent_bonus ) . $lang_mybonus ['text_howto_get_karma_six']) ;
	?>
</td>
	</tr>
</table>
<?php
}

// Bonus exchange
if ($action == "exchange") {
	if ($_POST ["userid"] || $_POST ["points"] || $_POST ["bonus"] || $_POST ["art"]) {
		write_log ( "User " . $CURUSER ["username"] . "," . $CURUSER ["ip"] . " is trying to cheat at bonus system", 'mod' );
		die ( $lang_mybonus ['text_cheat_alert'] );
	}
	$option = ( int ) $_POST ["option"];
	$bonusarray = bonusarray ( $option );
	
	$points = $bonusarray ['points'];
	$userid = $CURUSER ['id'];
	$art = $bonusarray ['art'];
	
	$bonuscomment = $CURUSER ['bonuscomment'];
	$seedbonus = $CURUSER ['seedbonus'] - $points;
	
	if (! $_POST ['passwd']) {
		switch ($option) {
			case 1 :
				{
					$confirm = "你正在使用" . $bonusarray ['points'] . "个魔力值兑换1G上传量。";
					break;
				}
			case 2 :
				{
					$confirm = "你正在使用" . $bonusarray ['points'] . "个魔力值兑换10G上传量。";
					break;
				}
			case 3 :
				{
					$confirm = "你正在使用" . $bonusarray ['points'] . "个魔力值兑换100G上传量。";
					break;
				}
			case 4 :
				{
					$confirm = "你正在使用" . $_POST ['ori_points'] . "个魔力值兑换1个邀请名额。<input type=hidden name=ori_points value=\"" . $_POST ["ori_points"] . "\">";
					break;
				}
			case 5 :
				{
					$confirm = "你正在使用" . $bonusarray ['points'] . "个魔力值兑换自定义头衔。旧的头衔是:&nbsp;&nbsp;" . $CURUSER ["title"] . "&nbsp;&nbsp;,新的头衔是:&nbsp;&nbsp;" . $_POST ["title"] . "&nbsp;&nbsp;<input type=hidden name=title value=\"" . $_POST ["title"] . "\"><br />";
					break;
				}
			case 6 :
				{
					$confirm = "你正在使用" . $bonusarray ['points'] . "个魔力值兑换1个月的贵宾待遇。";
					break;
				}
			case 7 :
				{
					$confirm = "<input type=hidden name=username value=\"" . $_POST ['username'] . "\" /><input type=hidden name=bonusgift value=\"" . $_POST ['bonusgift'] . "\">你正在向" . $_POST ['username'] . "赠送" . $_POST ['bonusgift'] . "个魔力值。<br />你的留言是：&nbsp;&nbsp;" . $_POST ['message'] . "<input type=hidden name=message value=\"" . $_POST ['message'] . "\">";
					break;
				}
			case 8 :
				{
					$confirm = "你正在使用" . $bonusarray ['points'] . "个魔力值兑换" . $bonusnoadtime_advertisement . "天不显示广告特权。";
					break;
				}
			case 9 :
				{
					$confirm = "<input type=hidden name=ratiocharity value=\"" . $_POST ['ratiocharity'] . "\"><input type=hidden name=bonuscharity value=\"" . $_POST ['bonuscharity'] . "\">你正在向分享率低于" . $_POST ['ratiocharity'] . "的用户捐赠" . $_POST ['bonuscharity'] . "个魔力值。";
					break;
				}
			case 10 :
				{
					$confirm = "<input type=hidden name=username value=\"" . $_POST ['username'] . "\" />你正在使用" . $_POST ['ori_points'] . "个魔力值复活用户 " . $_POST ['username'] . " 。<input type=hidden name=ori_points value=\"" . $_POST ["ori_points"] . "\">";
					break;
				}
			case 11 :
				{
					$confirm = "你正在以" . - $_POST ['ori_points'] . "个魔力值的价格出售1个邀请名额。<input type=hidden name=ori_points value=\"" . $_POST ["ori_points"] . "\">";
					break;
				}
			case 12 :
				{
					$confirm = "你正在以" . $bonusarray ['points'] . "个魔力值的价格将ID的颜色改变为" . $_POST ["color"] . "。<input type=hidden name=color value=\"" . ($_POST ["color"] [0] == "#" ? substr ( $_POST ["color"], 1 ) : $_POST ["color"]) . "\">";
					break;
				}
			case 13 :
				{
					$confirm = "你正在以" . $bonusarray ['points'] . "个魔力值的价格将ID的用户名改变为" . $_POST ["newusername"] . "。<input type=hidden name=newusername value=\"" . $_POST ["newusername"] . "\">";
					break;
				}
			default :
				die ();
		}
		stdmsg ( "确认操作", "<form action=\"?action=exchange\" method=\"post\"><input type=\"hidden\" name=\"option\" value=" . $option . " />" . $confirm . "<br />请输入你的密码确认上述操作：<input type=password name=passwd /><input type=submit value=\"确定\"> &nbsp;<input type=button value=\"返回\" onclick=\"location.href='javascript:history.go(-1)'\" /></form>", 0 );
		die ();
	} elseif ($CURUSER ["passhash"] != md5 ( $CURUSER ["secret"] . $_POST ['passwd'] . $CURUSER ["secret"] )) {
		stdmsg ( "出错了！", "密码不正确<input type=button value=\"返回上一页\" onclick=\"location.href='javascript:history.go(-1)'\" />", 0 );
		die ();
	} elseif (($option == 4 || $option == 10 || $option == 11) && $_POST ['ori_points'] != $bonusarray ['points']) {
		stdmsg ( "兑换价格有变化", "由于物价变动，当前价格产生了变化。新的价格是 " . $bonusarray ['points'] . " 个魔力值。<form action=\"?action=exchange\" method=\"post\"><input type=hidden name=ori_points value=\"" . $bonusarray ['points'] . "\"><input type=\"hidden\" name=\"option\" value=" . $option . " />" . ($option == 10 ? "<input type=hidden name=username value=\"" . $_POST ['username'] . "\" />" : "") . "<br /><input type=hidden name=passwd value=\"" . $_POST ["passwd"] . "\" /><input type=submit value=\"继续兑换\"> &nbsp;<input type=button value=\"取消兑换\" onclick=\"location.href='mybonus.php'\" /></form>", 0 );
		die ();
	} else if ($CURUSER ['seedbonus'] >= $points) {
		// === trade for upload
		if ($art == "traffic") {
			if ($CURUSER ['uploaded'] > $dlamountlimit_bonus * 1073741824) // uploaded
			                                                               // amount
			                                                               // reach
			                                                               // limit
				$ratio = $CURUSER ['uploaded'] / $CURUSER ['downloaded'];
			else
				$ratio = 0;
			if ($ratiolimit_bonus > 0 && $ratio > $ratiolimit_bonus)
				die ( $lang_mybonus ['text_cheat_alert'] );
			else {
				$upload = $CURUSER ['uploaded'];
				$up = $upload + $bonusarray ['menge'];
				$bonuscomment = date ( "Y-m-d" ) . " 使用 " . $points . " 个魔力值兑换上传。\n" . $bonuscomment;
				sql_query ( "UPDATE users SET uploaded = " . sqlesc ( $up ) . ", seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
				stdmsg ( $lang_mybonus ['successful'], $lang_mybonus ['text_success_upload'] );
			}
		} 		// === trade for one month VIP status ***note "SET class = '10'" change
		  // "10" to whatever your VIP class number is
		elseif ($art == "class") {
			if (get_user_class () >= UC_VIP) {
				stdmsg ( $lang_mybonus ['text_no_permission'], $lang_mybonus ['std_class_above_vip'], 0 );
				stdfoot ();
				die ();
			}
			$vip_until = date ( "Y-m-d H:i:s", (strtotime ( date ( "Y-m-d H:i:s" ) ) + 28 * 86400) );
			$bonuscomment = date ( "Y-m-d" ) . " 使用 " . $points . " 个魔力值兑换一个月的贵宾权限。\n" . htmlspecialchars ( $bonuscomment );
			sql_query ( "UPDATE users SET class = '" . UC_VIP . "', vip_added = 'yes', vip_until = " . sqlesc ( $vip_until ) . ", seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
			$msg = $lang_mybonus ['text_success_vip'] . "<b>" . get_user_class_name ( UC_VIP, false, false, true ) . "</b>" . $lang_mybonus ['text_success_vip_two'];
			stdmsg ( $lang_mybonus ['successful'], $msg );
		} 		// === trade for invites
		elseif ($art == "invite") {
			if (get_user_class () < $buyinvite_class)
				die ( get_user_class_name ( $buyinvite_class, false, false, true ) . $lang_mybonus ['text_plus_only'] );
			$invites = $CURUSER ['invites'];
			$inv = $invites + $bonusarray ['menge'];
			$bonuscomment = date ( "Y-m-d" ) . " 使用 " . $points . " 购买了一个邀请名额。\n" . htmlspecialchars ( $bonuscomment );
			sql_query ( "UPDATE users SET invites = " . sqlesc ( $inv ) . ", seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
			stdmsg ( $lang_mybonus ['successful'], $lang_mybonus ['text_success_invites'] );
		} elseif ($art == "invite_for_sale") {
			if ($CURUSER ["invites"] < 1)
				die ( "您没有可以用于售卖的邀请资格！" );
			if ($CURUSER ["invites"] >= 30)
				die ( "您没有可以用于售卖的邀请资格！（您持有的邀请总数不少于30个，系统禁止套现。）" );
			$invites = $CURUSER ['invites'];
			$inv = $invites - $bonusarray ['menge'];
			$bonuscomment = date ( "Y-m-d" ) . " 通过出售一个邀请名额获得了 " . - $points . "个魔力值。\n" . htmlspecialchars ( $bonuscomment );
			sql_query ( "UPDATE users SET invites = " . sqlesc ( $inv ) . ", seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
			stdmsg ( $lang_mybonus ['successful'], "祝贺你，你成功售出了<b>1</b>个邀请名额！" );
		} 		// === trade for enableaccount
		elseif ($art == "enableaccount") {
			$usernameenable = sqlesc ( trim ( $_POST ["username"] ) );
			$res = sql_query ( "SELECT id, enabled, modcomment,downloadpos FROM users WHERE username=" . $usernameenable );
			$arr = mysql_fetch_assoc ( $res );
			$useridenable = $arr ['id'];
			$userenabled = $arr ['enabled'];
			$modcomment = $arr ['modcomment'];
			$downloadpos = $arr ['downloadpos'];
			$modcomment = date ( "Y-m-d" ) . " 被 " . $CURUSER [username] . " 复活。\n" . htmlspecialchars ( $modcomment );
			$bonuscomment = date ( "Y-m-d" ) . " 花费 " . $points . " 个魔力值复活用户 " . $usernameenable . " 。\n" . htmlspecialchars ( $bonuscomment );
			if (! $useridenable) {
				stdmsg ( $lang_mybonus ['text_error'], $lang_mybonus ['text_receiver_not_exists'], 0 );
				stdfoot ();
				die ();
			}
			if ($userenabled == 'yes') {
				stdmsg ( $lang_mybonus ['text_huh'], $lang_mybonus ['text_account_not_disabled'], 0 );
				stdfoot ();
				die ();
			}
			if ($downloadpos == 'no') {
				stdmsg ( $lang_mybonus ['text_no_permission'], $lang_mybonus ['text_banned_by_admin'], 0 );
				stdfoot ();
				die ();
			}
			
			sql_query ( "UPDATE users SET enabled = 'yes', class = '1',leechwarn='no', modcomment = " . sqlesc ( $modcomment ) . " WHERE id = " . sqlesc ( $useridenable ) ) or sqlerr ( __FILE__, __LINE__ );
			
			sql_query ( "UPDATE users SET seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
			stdmsg ( $lang_mybonus ['successful'], $lang_mybonus ['text_account'] . $usernameenable . $lang_mybonus ['text_success_enable_account'] );
		} 		// === trade for special title
		/**
		 * ** the $words array are words that you DO NOT want the user to
		 * have...
		 * use to filter "bad words" & user class...
		 * the user class is just for show, but what the hell tongue.gif Add
		 * more or edit to your liking.
		 * note if they try to use a restricted word, they will recieve the
		 * special title "I just wasted my karma" ****
		 */
		elseif ($art == "title") {
			// ===custom title
			$title = $_POST ["title"];
			$title = sqlesc ( $title );
			// $title = str_replace ( $words, $lang_mybonus
			// ['text_wasted_karma'], $title );
			$patterns = $words;
			for($i = 0; $i < count ( $patterns ); $i ++) {
				$patterns [$i] = '/' . $patterns [$i] . '/i';
			}
			$title = preg_replace ( $patterns, $lang_mybonus ['text_wasted_karma'], $title );
			$bonuscomment = date ( "Y-m-d" ) . " 花费 " . $points . " 个魔力值兑换自定义头衔。旧的头衔是 " . htmlspecialchars ( trim ( $CURUSER ["title"] ) ) . " ，新的头衔是 $title 。\n" . htmlspecialchars ( $bonuscomment );
			sql_query ( "UPDATE users SET title = $title, seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
			$Cache->delete_value ( 'user_' . $userid . '_title' );
			stdmsg ( $lang_mybonus ['successful'], $lang_mybonus ['text_success_custom_title1'] . $title . $lang_mybonus ['text_success_custom_title2'] );
		} elseif ($art == "custom_color") {
			// ===custom color
			$color = $_POST ["color"];
			$bonuscomment = date ( "Y-m-d" ) . " - " . $points . " Points for custom color. Old color is " . htmlspecialchars ( trim ( $CURUSER ["color"] ) ) . " and new color is " . $color . ".\n" . htmlspecialchars ( $bonuscomment );
			sql_query ( "UPDATE users SET color = \"" . $color . "\", seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
			$Cache->delete_value ( 'user_' . $userid . '_color' );
			stdmsg ( $lang_mybonus ['successful'], $lang_mybonus ['text_success_custom_color'] );
		} elseif ($art == "rename") {
			// ===rename
			$newusername = trim ( $_POST ["newusername"] );
			if (empty ( $newusername )) {
				stdmsg ( $lang_mybonus ['text_error'], '对不起，用户名不能为空！' );
				stdfoot ();
				die ();
			}
			if (mb_strlen($newusername,'UTF-8') > 12) {
				stdmsg ( $lang_mybonus ['text_error'], '对不起，用户名过长（至多12个字符）！' );
				stdfoot ();
				die ();
			}
			if (! validusername ( $newusername )) {
				stdmsg ( $lang_mybonus ['text_error'], '无效的用户名！' );
				stdfoot ();
				die ();
			}
			$newusername = sqlesc ( $newusername );
			$res_check_user = sql_query ( "SELECT * FROM users WHERE username = " . $newusername );
			
			if (mysql_num_rows ( $res_check_user ) > 0) {
				stdmsg ( $lang_mybonus ['text_error'], '用户名已经存在！' );
				stdfoot ();
				die ();
			}
			
			global $rename_bonus;
			if ($points != $rename_bonus * pow ( 2, 0 + $CURUSER ['renamenum'] )) {
				stdmsg ( $lang_mybonus ['text_error'], '想作弊？没门！' );
				stdfoot ();
				die ();
			}
			
			$bonuscomment = date ( "Y-m-d" ) . " 花费 " . $points . " 个魔力值将用户名从  '" . htmlspecialchars ( trim ( $CURUSER ["username"] ) ) . "' 改为 '" . htmlspecialchars ( trim ( $_POST ["newusername"] ) ) . "'.\n" . htmlspecialchars ( $bonuscomment );
			sql_query ( "UPDATE users SET username = " . $newusername . ", seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . ", renamenum = renamenum + 1 WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
			$Cache->delete_value ( 'user_' . $userid . '_content' );
			stdmsg ( $lang_mybonus ['successful'], '你成功将ID的用户名改为 <b>' . trim ( $_POST ["newusername"] ) . '</b> ！' );
			sql_query ( "INSERT INTO app_rename (userid, oldname, newname, timerename, paybonus) VALUES (" . sqlesc ( $CURUSER ['id'] ) . ", " . sqlesc ( htmlspecialchars ( trim ( $CURUSER ["username"] ) ) ) . ", " . sqlesc ( htmlspecialchars ( trim ( $_POST ["newusername"] ) ) ) . ", " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ", " . sqlesc ( $points ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		} elseif ($art == "noad" && $enablead_advertisement == 'yes' && $enablebonusnoad_advertisement == 'yes') {
			if (($enablenoad_advertisement == 'yes' && get_user_class () >= $noad_advertisement) || strtotime ( $CURUSER ['noaduntil'] ) >= TIMENOW || get_user_class () < $bonusnoad_advertisement)
				die ( $lang_mybonus ['text_cheat_alert'] );
			else {
				$noaduntil = date ( "Y-m-d H:i:s", (TIMENOW + $bonusarray ['menge']) );
				$bonuscomment = date ( "Y-m-d" ) . " 花费 " . $points . " 兑换 " . $bonusnoadtime_advertisement . " 天不显示广告资格。\n" . htmlspecialchars ( $bonuscomment );
				sql_query ( "UPDATE users SET noad='yes', noaduntil='" . $noaduntil . "', seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id=" . sqlesc ( $userid ) );
				stdmsg ( $lang_mybonus ['successful'], $lang_mybonus ['text_success_no_ad'] );
			}
		} elseif ($art == 'gift_2') 		// charity giving
		{
			$points = 0 + $_POST ["bonuscharity"];
			if ($points < 1000 || $points > 50000) {
				stdmsg ( $lang_mybonus ['text_error'], $lang_mybonus ['bonus_amount_not_allowed_two'], 0 );
				stdfoot ();
				die ();
			}
			$ratiocharity = 0.0 + $_POST ["ratiocharity"];
			if ($ratiocharity < 0.1 || $ratiocharity > 0.8) {
				stdmsg ( $lang_mybonus ['text_error'], $lang_mybonus ['bonus_ratio_not_allowed'] );
				stdfoot ();
				die ();
			}
			if ($CURUSER ['seedbonus'] >= $points) {
				$points2 = number_format ( $points, 1 );
				$bonuscomment = date ( "Y-m-d" ) . " 捐献 " . $points2 . " 个魔力值给分享率低于 " . htmlspecialchars ( trim ( $ratiocharity ) ) . " 的用户。\n" . htmlspecialchars ( $bonuscomment );
				$charityReceiverCount = get_row_count ( "users", "WHERE enabled='yes' AND 10737418240 < downloaded AND $ratiocharity > uploaded/downloaded" );
				if ($charityReceiverCount) {
					sql_query ( "UPDATE users SET seedbonus = seedbonus - $points, charity = charity + $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
					$charityPerUser = $points / $charityReceiverCount;
					sql_query ( "UPDATE users SET seedbonus = seedbonus + $charityPerUser WHERE enabled='yes' AND 10737418240 < downloaded AND $ratiocharity > uploaded/downloaded" ) or sqlerr ( __FILE__, __LINE__ );
					stdmsg ( $lang_mybonus ['successful'], $lang_mybonus ['text_success_charity'] );
				} else {
					stdmsg ( $lang_mybonus ['std_sorry'], $lang_mybonus ['std_no_users_need_charity'] );
					stdfoot ();
					die ();
				}
			}
		} elseif ($art == "gift_1" && $bonusgift_bonus == 'yes') {
			// === trade for giving the gift of karma
			$points = 0 + $_POST ["bonusgift"];
			$message = $_POST ["message"];
			// ==gift for peeps with no more options
			$usernamegift = sqlesc ( trim ( $_POST ["username"] ) );
			$res = sql_query ( "SELECT id, seedbonus, bonuscomment FROM users WHERE username=" . $usernamegift );
			$arr = mysql_fetch_assoc ( $res );
			$useridgift = $arr ['id'];
			$userseedbonus = $arr ['seedbonus'];
			$receiverbonuscomment = $arr ['bonuscomment'];
			if ($points < 25 || $points > 10000) {
				// write_log("User " . $CURUSER["username"] . "," .
				// $CURUSER["ip"] . " is hacking bonus system",'mod');
				stdmsg ( $lang_mybonus ['text_error'], $lang_mybonus ['bonus_amount_not_allowed'] );
				stdfoot ();
				die ();
			}
			if ($CURUSER ['seedbonus'] >= $points) {
				$points2 = number_format ( $points, 1 );
				$bonuscomment = date ( "Y-m-d" ) . " 给 " . htmlspecialchars ( trim ( $_POST ["username"] ) ) . " 赠送了 " . $points2 . " 个魔力值。\n" . htmlspecialchars ( $bonuscomment );
				
				$aftertaxpoint = $points;
				if ($taxpercentage_bonus)
					$aftertaxpoint -= $aftertaxpoint * $taxpercentage_bonus * 0.01;
				if ($basictax_bonus)
					$aftertaxpoint -= $basictax_bonus;
				
				$points2receiver = number_format ( $aftertaxpoint, 1 );
				$newreceiverbonuscomment = date ( "Y-m-d" ) . " 从 " . ($CURUSER ["username"]) . " 那儿收到 " . $points2receiver . " 个魔力值(税后)。\n" . htmlspecialchars ( $receiverbonuscomment );
				if ($userid == $useridgift) {
					stdmsg ( $lang_mybonus ['text_huh'], $lang_mybonus ['text_karma_self_giving_warning'], 0 );
					stdfoot ();
					die ();
				}
				if (! $useridgift) {
					stdmsg ( $lang_mybonus ['text_error'], $lang_mybonus ['text_receiver_not_exists'], 0 );
					stdfoot ();
					die ();
				}
				if ($userseedbonus + $aftertaxpoint >= $CURUSER ['seedbonus'] - $points) {
					$bonusofreceiver = $userseedbonus + $aftertaxpoint;
					$bonusofsend = $CURUSER ['seedbonus'] - $points;
					stdmsg ( $lang_mybonus ['text_error'], $lang_mybonus ['bonus_amount_of_receiver'] . $bonusofreceiver . $lang_mybonus ['large_then_yours'] . $bonusofsend . $lang_mybonus ['your_gift_is_unallowed'] );
					stdfoot ();
					die ();
				}
				sql_query ( "UPDATE users SET seedbonus = seedbonus - $points, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = " . sqlesc ( $userid ) ) or sqlerr ( __FILE__, __LINE__ );
				sql_query ( "UPDATE users SET seedbonus = seedbonus + $aftertaxpoint, bonuscomment = " . sqlesc ( $newreceiverbonuscomment ) . " WHERE id = " . sqlesc ( $useridgift ) );
				
				// ===send message
				$subject = sqlesc ( $lang_mybonus_target [get_user_lang ( $useridgift )] ['msg_someone_loves_you'] );
				$added = sqlesc ( date ( "Y-m-d H:i:s" ) );
				$msg = $lang_mybonus_target [get_user_lang ( $useridgift )] ['msg_you_have_been_given'] . $points2 . $lang_mybonus_target [get_user_lang ( $useridgift )] ['msg_after_tax'] . $points2receiver . $lang_mybonus_target [get_user_lang ( $useridgift )] ['msg_karma_points_by'] . $CURUSER ['username'];
				if ($message)
					$msg .= "\n" . $lang_mybonus_target [get_user_lang ( $useridgift )] ['msg_personal_message_from'] . $CURUSER ['username'] . $lang_mybonus_target [get_user_lang ( $useridgift )] ['msg_colon'] . $message;
				$msg = sqlesc ( $msg );
				sql_query ( "INSERT INTO messages (sender, subject, receiver, msg, added) VALUES(0, $subject, $useridgift, $msg, $added)" ) or sqlerr ( __FILE__, __LINE__ );
				$usernamegift = unesc ( $_POST ["username"] );
				stdmsg ( $lang_mybonus ['successful'], $lang_mybonus ['text_success_gift'] );
			} else {
				print ("<table width=\"940\"><tr><td class=\"colhead\" align=\"left\" colspan=\"2\"><h1>" . $lang_mybonus ['text_oups'] . "</h1></td></tr>") ;
				print ("<tr><td align=\"left\"></td><td align=\"left\">" . $lang_mybonus ['text_not_enough_karma'] . "<br /><br /></td></tr></table>") ;
			}
		}
	} else if ($CURUSER ['seedbonus'] < $points) {
		stdmsg ( $lang_mybonus ['text_error'], '想作弊？没门！' );
		stdfoot ();
		die ();
	}
}

print ('<script type="text/javascript" src="js/jscolor/jscolor.js"></script>') ;

stdfoot ();
?>
