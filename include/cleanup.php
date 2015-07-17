<?php
// IMPORTANT: Do not edit below unless you know what you are doing!
if (! defined ( 'IN_TRACKER' ))
	die ( 'Hacking attempt!' );
require_once ($rootpath . '/lang/_target/lang_cleanup.php');
function printProgress($msg) {
	echo $msg . '...完成<br />';
	ob_flush ();
	flush ();
}
function docleanup($forceAll = 0, $printProgress = false) {
	
	// require(get_langfile_path("cleanup.php",true));
	global $lang_cleanup_target;
	global $torrent_dir, $signup_timeout, $max_dead_torrent_time, $autoclean_interval_one, $autoclean_interval_two, $autoclean_interval_three, $autoclean_interval_four, $autoclean_interval_five, $SITENAME, $bonus, $invite_timeout, $offervotetimeout_main, $offeruptimeout_main, $iniupload_main;
	global $donortimes_bonus, $perseeding_bonus, $maxseeding_bonus, $tzero_bonus, $nzero_bonus, $bzero_bonus, $l_bonus;
	global $expirehalfleech_torrent, $expirefree_torrent, $expiretwoup_torrent, $expiretwoupfree_torrent, $expiretwouphalfleech_torrent, $expirethirtypercentleech_torrent, $expirenormal_torrent, $hotdays_torrent, $hotseeder_torrent, $halfleechbecome_torrent, $freebecome_torrent, $twoupbecome_torrent, $twoupfreebecome_torrent, $twouphalfleechbecome_torrent, $thirtypercentleechbecome_torrent, $normalbecome_torrent, $deldeadtorrent_torrent;
	global $neverdelete_account, $neverdeletepacked_account, $deletepacked_account, $deleteunpacked_account, $deletenotransfer_account, $deletenotransfertwo_account, $deletepeasant_account, $psdlone_account, $psratioone_account, $psdltwo_account, $psratiotwo_account, $psdlthree_account, $psratiothree_account, $psdlfour_account, $psratiofour_account, $psdlfive_account, $psratiofive_account, $putime_account, $pudl_account, $puprratio_account, $puderatio_account, $eutime_account, $eudl_account, $euprratio_account, $euderatio_account, $cutime_account, $cudl_account, $cuprratio_account, $cuderatio_account, $iutime_account, $iudl_account, $iuprratio_account, $iuderatio_account, $vutime_account, $vudl_account, $vuprratio_account, $vuderatio_account, $exutime_account, $exudl_account, $exuprratio_account, $exuderatio_account, $uutime_account, $uudl_account, $uuprratio_account, $uuderatio_account, $nmtime_account, $nmdl_account, $nmprratio_account, $nmderatio_account, $getInvitesByPromotion_class;
	global $enablenoad_advertisement, $noad_advertisement;
	global $Cache;
	
	set_time_limit ( 0 );
	ignore_user_abort ( 1 );
	$now = time ();
	
	// Priority Class 1: cleanup every 15 mins
	// 2.update peer status
	$deadtime = deadtime ();
	$deadtime = date ( "Y-m-d H:i:s", $deadtime );
	sql_query ( "DELETE FROM peers WHERE last_action < " . sqlesc ( $deadtime ) ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( '删除冗余peers信息' );
	}
	// 11.calculate seeding bonus
	$res = sql_query ( "SELECT DISTINCT userid FROM peers WHERE seeder = 'yes'" ) or sqlerr ( __FILE__, __LINE__ );
	if (mysql_num_rows ( $res ) > 0) {
		$sqrtof2 = sqrt ( 2 );
		$logofpointone = log ( 0.1 );
		$valueone = $logofpointone / $tzero_bonus;
		$pi = 3.141592653589793;
		$valuetwo = $bzero_bonus * (2 / $pi);
		$valuethree = $logofpointone / ($nzero_bonus - 1);
		$timenow = TIMENOW;
		$sectoweek = 7 * 24 * 60 * 60;
		while ( $arr = mysql_fetch_assoc ( $res ) ) 		// loop for different users
		{
			$A = 0;
			$count = 0;
			$all_bonus = 0;
			$torrentres = sql_query ( "select torrents.added, torrents.size,torrents.needkeepseed, torrents.seeders from torrents LEFT JOIN peers ON peers.torrent = torrents.id WHERE peers.userid = $arr[userid] AND peers.seeder ='yes'" ) or sqlerr ( __FILE__, __LINE__ );
			while ( $torrent = mysql_fetch_array ( $torrentres ) ) {
				$weeks_alive = ($timenow - strtotime ( $torrent [added] )) / $sectoweek;
				$gb_size = ($torrent [needkeepseed]=='yes'?($torrent [size] / 214748365):($torrent [size] / 1073741824));
				$temp = (1 - exp ( $valueone * $weeks_alive )) * $gb_size * (1 + $sqrtof2 * exp ( $valuethree * ($torrent [seeders] - 1) ));
				$A += $temp;
				$count ++;
			}
			if ($count > $maxseeding_bonus)
				$count = $maxseeding_bonus;
			$all_bonus = ($valuetwo * atan ( $A / $l_bonus ) + ($perseeding_bonus * $count)) / (3600 / $autoclean_interval_one);
			$is_donor = get_single_value ( "users", "donor", "WHERE id=" . $arr ['userid'], TRUE );
			if ($is_donor == 'yes' && $donortimes_bonus > 0)
				$all_bonus = $all_bonus * $donortimes_bonus;
			KPS ( "+", $all_bonus, $arr ["userid"] );
		}
	}
	
	if ($printProgress) {
		printProgress ( '发放做种所得魔力值' );
	}
	// Priority Class 2: cleanup every 30 mins
	$res = sql_query ( "SELECT value_u FROM avps WHERE arg = 'lastcleantime2'" );
	$row = mysql_fetch_array ( $res );
	if (! $row) {
		sql_query ( "INSERT INTO avps (arg, value_u) VALUES ('lastcleantime2'," . sqlesc ( $now ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		return;
	}
	
	// 2.2. write to statistics
	$today_timestamp = strtotime('today');
	$registered = get_row_count ( "users" );
	$totalonlinetoday = get_row_count ( "users", "WHERE last_access >= " . sqlesc ( date ( "Y-m-d H:i:s", $today_timestamp ) ) );
	$registered_male =  get_row_count ( "users", "WHERE gender='Male'" );
	$registered_female = get_row_count ( "users", "WHERE gender='Female'" );
	$torrents = get_row_count ( "torrents" );
	$dead = get_row_count ( "torrents", "WHERE visible='no'" );
	$totaltorrentssize = mksize ( get_row_sum ( "torrents", "size" ) );
	$totalbonus = get_single_value ( "users WHERE enabled='yes'", "sum(seedbonus) " );
	sql_query ( "REPLACE INTO statistics 
		(`date`, `registered`, `totalonlinetoday`, `registered_male`, `registered_female`, `torrents`, `dead`, `totaltorrentssize`, `totalbonus`)
		VALUES
		('$today_timestamp', '$registered', '$totalonlinetoday', '$registered_male', '$registered_female', '$torrents', '$dead', '$totaltorrentssize', '$totalbonus')
	");
	
	printProgress ( "写入统计数据" );
	
	$ts = $row [0];
	if ($ts + $autoclean_interval_two > $now && ! $forceAll) {
		return '一级清理完成';
	} else {
		sql_query ( "UPDATE avps SET value_u = " . sqlesc ( $now ) . " WHERE arg='lastcleantime2'" ) or sqlerr ( __FILE__, __LINE__ );
	}
	// 2.5.update torrents' visibility
	$deadtime = deadtime () - $max_dead_torrent_time;
	sql_query ( "UPDATE torrents SET visible='no' WHERE visible='yes' AND last_seed < FROM_UNIXTIME($deadtime) AND seeders=0" ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( "更新断种显示" );
	}
	
	// 幸运抽奖
	open_luckydraw ();
	if ($printProgress) {
		printProgress ( "更新幸运抽奖开奖" );
	}
		
	// Priority Class 3: cleanup every 60 mins
	$res = sql_query ( "SELECT value_u FROM avps WHERE arg = 'lastcleantime3'" );
	$row = mysql_fetch_array ( $res );
	if (! $row) {
		sql_query ( "INSERT INTO avps (arg, value_u) VALUES ('lastcleantime3',$now)" ) or sqlerr ( __FILE__, __LINE__ );
		return;
	}
	$ts = $row [0];
	if ($ts + $autoclean_interval_three > $now && ! $forceAll) {
		return '二级清理完成';
	} else {
		sql_query ( "UPDATE avps SET value_u = " . sqlesc ( $now ) . " WHERE arg='lastcleantime3'" ) or sqlerr ( __FILE__, __LINE__ );
	}
	
	// 4.update count of seeders, leechers, comments for torrents
	$torrents = array ();
	$res = sql_query ( "SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder" ) or sqlerr ( __FILE__, __LINE__ );
	while ( $row = mysql_fetch_assoc ( $res ) ) {
		if ($row ["seeder"] == "yes")
			$key = "seeders";
		else
			$key = "leechers";
		$torrents [$row ["torrent"]] [$key] = $row ["c"];
	}
	
	$res = sql_query ( "SELECT torrent, COUNT(*) AS c FROM comments GROUP BY torrent" ) or sqlerr ( __FILE__, __LINE__ );
	while ( $row = mysql_fetch_assoc ( $res ) ) {
		$torrents [$row ["torrent"]] ["comments"] = $row ["c"];
	}
	
	$fields = explode ( ":", "comments:leechers:seeders" );
	$res = sql_query ( "SELECT id, seeders, leechers, comments FROM torrents" ) or sqlerr ( __FILE__, __LINE__ );
	while ( $row = mysql_fetch_assoc ( $res ) ) {
		$id = $row ["id"];
		$torr = $torrents [$id];
		foreach ( $fields as $field ) {
			if (! isset ( $torr [$field] ))
				$torr [$field] = 0;
		}
		$update = array ();
		foreach ( $fields as $field ) {
			if ($torr [$field] != $row [$field])
				$update [] = "$field = " . $torr [$field];
		}
		if (count ( $update ))
			sql_query ( "UPDATE torrents SET " . implode ( ",", $update ) . " WHERE id = $id" ) or sqlerr ( __FILE__, __LINE__ );
	}
	if ($printProgress) {
		printProgress ( "更新种子的做种、下载、评论数目" );
	}
	
	// set no-advertisement-by-bonus time out
	sql_query ( "UPDATE users SET noad='no' WHERE noaduntil < " . sqlesc ( date ( "Y-m-d H:i:s" ) ) . ($enablenoad_advertisement == 'yes' ? " AND class < " . sqlesc ( $noad_advertisement ) : "") );
	if ($printProgress) {
		printProgress ( "不显示广告到期后重新显示" );
	}
	// 12. update forum post/topic count
	$forums = sql_query ( "select id from forums" ) or sqlerr ( __FILE__, __LINE__ );
	while ( $forum = mysql_fetch_assoc ( $forums ) ) {
		$postcount = 0;
		$topiccount = 0;
		$topics = sql_query ( "select id from topics where forumid=$forum[id]" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $topic = mysql_fetch_assoc ( $topics ) ) {
			$res = sql_query ( "select count(*) from posts where topicid=$topic[id]" ) or sqlerr ( __FILE__, __LINE__ );
			$arr = mysql_fetch_row ( $res );
			$postcount += $arr [0];
			++ $topiccount;
		}
		sql_query ( "update forums set postcount=$postcount, topiccount=$topiccount where id=$forum[id]" ) or sqlerr ( __FILE__, __LINE__ );
	}
	$Cache->delete_value ( 'forums_list' );
	if ($printProgress) {
		printProgress ( "更新论坛主题/帖子数目" );
	}
	// 14.cleanup offers
	// Delete offers if not voted on after some time
	if ($offervotetimeout_main) {
		$secs = ( int ) $offervotetimeout_main;
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - ($offervotetimeout_main)) ) );
		$res = sql_query ( "SELECT id, name FROM offers WHERE added < $dt AND allowed <> 'allowed'" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			sql_query ( "DELETE FROM offers WHERE id=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			sql_query ( "DELETE FROM offervotes WHERE offerid=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			sql_query ( "DELETE FROM comments WHERE offer=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			write_log ( "系统删除了候选 $arr[id] ($arr[name]) (投票超时)", 'normal' );
		}
	}
	if ($printProgress) {
		printProgress ( "删除投票超期的候选" );
	}
	
	// Delete offers if not uploaded after being voted on for some time.
	if ($offeruptimeout_main) {
		$secs = ( int ) $offeruptimeout_main;
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - ($secs)) ) );
		$res = sql_query ( "SELECT id, name FROM offers WHERE allowedtime < $dt AND allowed = 'allowed'" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			sql_query ( "DELETE FROM offers WHERE id=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			sql_query ( "DELETE FROM offervotes WHERE offerid=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			sql_query ( "DELETE FROM comments WHERE offer=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			write_log ( "系统删除了候选 $arr[id] ($arr[name]) (上传超时)", 'normal' );
		}
	}
	if ($printProgress) {
		printProgress ( "删除上传超期的候选." );
	}
	
	// 15.cleanup torrents
	// Start: expire torrent promotion
	function torrent_promotion_expire($days, $type = 2, $targettype = 1) {
		$secs = ( int ) ($days * 86400); // XX days
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - ($secs)) ) );
		$res = sql_query ( "SELECT id, name FROM torrents WHERE sp_time < $dt AND sp_state = " . sqlesc ( $type ) . " AND promotion_time_type=0" ) or sqlerr ( __FILE__, __LINE__ );
		global $expirenormal_torrent, $expirefree_torrent, $expiretwoup_torrent, $expiretwoupfree_torrent, $expirehalfleech_torrent, $expiretwouphalfleech_torrent, $expirethirtypercentleech_torrent;
		switch ($targettype) {
			case 1 : // normal
				{
					$sp_state = 1;
					$become = "normal";
					$becomedays = $expirenormal_torrent;
					break;
				}
			case 2 : // Free
				{
					$sp_state = 2;
					$become = "Free";
					$becomedays = $expirefree_torrent;
					break;
				}
			case 3 : // 2X
				{
					$sp_state = 3;
					$become = "2X";
					$becomedays = $expiretwoup_torrent;
					break;
				}
			case 4 : // 2X Free
				{
					$sp_state = 4;
					$become = "2X Free";
					$becomedays = $expiretwoupfree_torrent;
					break;
				}
			case 5 : // Half Leech
				{
					$sp_state = 5;
					$become = "50%";
					$becomedays = $expirehalfleech_torrent;
					break;
				}
			case 6 : // 2X Half Leech
				{
					$sp_state = 6;
					$become = "2X 50%";
					$becomedays = $expiretwouphalfleech_torrent;
					break;
				}
			case 7 : // 30%
				{
					$sp_state = 7;
					$become = "30%";
					$becomedays = $expirethirtypercentleech_torrent;
					break;
				}
			default : // normal
				{
					$sp_state = 1;
					$become = "normal";
					$becomedays = $expirenormal_torrent;
					break;
				}
		}
		$becomeseconds = time () - ( int ) (43200 * $becomedays);
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			sql_query ( "UPDATE torrents SET sp_state = " . sqlesc ( $sp_state ) . ", sp_time = " . sqlesc ( date ( "Y-m-d H:i:s", $becomeseconds ) ) . " WHERE id=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			if ($sp_state == 1)
				write_log ( "系统取消了种子 $arr[id] ($arr[name]) 的促销 (促销时间已过)", 'normal' );
			else
				write_log ( "种子 $arr[id] ($arr[name]) 的促销类型更改为 " . $become . " (促销时间已过)", 'normal' );
		}
	}
	
	if ($expirehalfleech_torrent)
		torrent_promotion_expire ( $expirehalfleech_torrent, 5, $halfleechbecome_torrent );
	if ($expirefree_torrent)
		torrent_promotion_expire ( $expirefree_torrent, 2, $freebecome_torrent );
	if ($expiretwoup_torrent)
		torrent_promotion_expire ( $expiretwoup_torrent, 3, $twoupbecome_torrent );
	if ($expiretwoupfree_torrent)
		torrent_promotion_expire ( $expiretwoupfree_torrent, 4, $twoupfreebecome_torrent );
	if ($expiretwouphalfleech_torrent)
		torrent_promotion_expire ( $expiretwouphalfleech_torrent, 6, $twouphalfleechbecome_torrent );
	if ($expirethirtypercentleech_torrent)
		torrent_promotion_expire ( $expirethirtypercentleech_torrent, 7, $thirtypercentleechbecome_torrent );
	if ($expirenormal_torrent)
		torrent_promotion_expire ( $expirenormal_torrent, 1, $normalbecome_torrent );
		
		// expire individual torrent promotion
	$res = sql_query ( "SELECT id, name, pos_state_until FROM torrents WHERE sp_state < 8 AND promotion_time_type=2 AND promotion_until < " . sqlesc ( date ( "Y-m-d H:i:s", TIMENOW ) ) ) or sqlerr ( __FILE__, __LINE__ );
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		sql_query ( "UPDATE torrents SET sp_state = 1, promotion_time_type=0, promotion_until='0000-00-00 00:00:00' WHERE id=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
		write_log ( "系统取消了种子 $arr[id] ($arr[name]) 的促销 (促销时间已过)", 'normal' );
	}
	// End: expire torrent promotion
	
	// Start:expire sticky torrents
	$res = sql_query ( "SELECT id, name, pos_state_until FROM torrents WHERE pos_state = 'sticky' " ) or sqlerr ( __FILE__, __LINE__ );
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		if ($arr ["pos_state_until"] < date ( "Y-m-d H:i:s", TIMENOW )) {
			sql_query ( "UPDATE torrents SET pos_state = 'normal', pos_state_until='0000-00-00 00:00:00' WHERE id=$arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			write_log ( "系统取消了种子 $arr[id] ($arr[name]) 的置顶 (置顶时间已过)", 'normal' );
		}
	}
	// End: expire sticky torrents
	
	if ($printProgress) {
		printProgress ( "促销到期的操作" );
	}
	// automatically pick hot
	if ($hotdays_torrent) {
		$secs = ( int ) ($hotdays_torrent * 86400); // XX days
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - ($secs)) ) );
		sql_query ( "UPDATE torrents SET picktype = 'hot', picktime = '" . date ( "Y-m-d H:i:s" ) . "' WHERE added > $dt AND picktype = 'normal' AND seeders > " . sqlesc ( $hotseeder_torrent ) ) or sqlerr ( __FILE__, __LINE__ );
	}
	$secs = ( int ) (2 * 86400); // 2 days
	$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - ($secs)) ) );
	sql_query ( "UPDATE torrents SET picktype = 'normal' , picktime = '" . date ( "Y-m-d H:i:s" ) . "' WHERE picktime < $dt AND picktype = '0day' " ) or sqlerr ( __FILE__, __LINE__ );
	
	if ($printProgress) {
		printProgress ( "自动挑选热门资源" );
	}
	
	// **********************************add by because move to class 3
	// *************************
	// delete inactive user accounts, no transfer. Alt. 1: last access time
	$neverdelete_account = ($neverdelete_account <= UC_VIP ? $neverdelete_account : UC_VIP);
	if ($deletenotransfer_account) {
		$secs = $deletenotransfer_account * 24 * 60 * 60;
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - $secs) ) );
		$maxclass = $neverdelete_account;
		$res = sql_query ( "SELECT * FROM users WHERE parked='no' AND status='confirmed' AND class < $maxclass AND last_access < $dt AND (uploaded = 0 || uploaded = " . sqlesc ( $iniupload_main ) . ") AND downloaded = 0" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) )
			write_log ( "系统删除了帐号 $arr[id] ($arr[username]) (无流量帐号连续" . $deletenotransfer_account . "天未登录)", 'normal' );
		sql_query ( "DELETE FROM users WHERE parked='no' AND status='confirmed' AND class < $maxclass AND last_access < $dt AND (uploaded = 0 || uploaded = " . sqlesc ( $iniupload_main ) . ") AND downloaded = 0" ) or sqlerr ( __FILE__, __LINE__ );
	}
	if ($printProgress) {
		printProgress ( "删除无流量且连续N天不登录的帐号" );
	}
	
	// delete inactive user accounts, no transfer. Alt. 2: registering time
	if ($deletenotransfertwo_account) {
		$secs = $deletenotransfertwo_account * 24 * 60 * 60;
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - $secs) ) );
		$maxclass = $neverdelete_account;
		$res = sql_query ( "SELECT * FROM users WHERE parked='no' AND status='confirmed' AND class < $maxclass AND added < $dt AND (uploaded = 0 || uploaded = " . sqlesc ( $iniupload_main ) . ") AND downloaded = 0" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) )
			write_log ( "系统删除了帐号 $arr[id] ($arr[username]) (注册" . $deletenotransfertwo_account . "天后依然无流量的帐号)", 'normal' );
		sql_query ( "DELETE FROM users WHERE parked='no' AND status='confirmed' AND class < $maxclass AND added < $dt AND (uploaded = 0 || uploaded = " . sqlesc ( $iniupload_main ) . ") AND downloaded = 0" ) or sqlerr ( __FILE__, __LINE__ );
	}
	if ($printProgress) {
		printProgress ( "删除注册N天依旧无流量的帐号" );
	}
/*	
	// delete accounts disabled for 50 days
	if ($deleteunpacked_account) {
		$secs = 10 * 24 * 60 * 60;
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - $secs) ) );
		$maxclass = $neverdelete_account;
		$res = sql_query ( "SELECT * FROM users WHERE enabled='no' AND last_access < $dt" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) )
			write_log ( "系统删除了帐号 $arr[id] ($arr[username]) (被禁用帐号50天内未复活)", 'normal' );
		sql_query ( "DELETE FROM users WHERE enabled='no' AND last_access < $dt" ) or sqlerr ( __FILE__, __LINE__ );
	}
	if ($printProgress) {
		printProgress ( "删除禁用50天的帐号" );
	}
	*/

	
	// delete inactive user accounts, not parked
	if ($deleteunpacked_account) {
		$secs = $deleteunpacked_account * 24 * 60 * 60;
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - $secs) ) );
		$maxclass = $neverdelete_account;
		
		$res = sql_query ( "SELECT * FROM users WHERE parked='no' AND status='confirmed' AND enabled = 'yes' AND class < $maxclass AND last_access < $dt" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) ){
			write_log ( "系统禁用了帐号 $arr[id] ($arr[username]) (未封存帐号" . $deleteunpacked_account . "天内未登录)", 'normal' );
			writecomment ( $arr [id], "被禁用——未封存帐号" . $deleteunpacked_account . "天内未登录." );
		sql_query ( "UPDATE users SET enabled = 'no' WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
		}

}
	if ($printProgress) {
		printProgress ( "禁用未封存且N天未登录的帐号" );
	}
	
	// delete parked user accounts, parked
	if ($deletepacked_account) {
		$secs = $deletepacked_account * 24 * 60 * 60;
		$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - $secs) ) );
		$maxclass = $neverdeletepacked_account;
		$res = sql_query ( "SELECT * FROM users WHERE parked='yes' AND status='confirmed' AND enabled = 'yes' AND class < $maxclass AND last_access < $dt" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) ){
			write_log ( "系统禁用了帐号 $arr[id] ($arr[username]) (已封存帐号" . $deletepacked_account . "天内未登录)", 'normal' );
			sql_query ( "UPDATE users SET enabled = 'no' WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
		}
	}
	if ($printProgress) {
		printProgress ( "禁用已封存且N天未登录的帐号" );
	}
	
	// remove VIP status if time's up
	$res = sql_query ( "SELECT id, modcomment FROM users WHERE vip_added='yes' AND vip_until < NOW()" ) or sqlerr ( __FILE__, __LINE__ );
	if (mysql_num_rows ( $res ) > 0) {
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
			$subject = sqlesc ( $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_vip_status_removed'] );
			$msg = sqlesc ( $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_vip_status_removed_body'] );
			// /---AUTOSYSTEM MODCOMMENT---//
			$modcomment = htmlspecialchars ( $arr ["modcomment"] );
			$modcomment = date ( "Y-m-d" ) . " - VIP status removed by - AutoSystem.\n" . $modcomment;
			$modcom = sqlesc ( $modcomment );
			// /---end
			sql_query ( "UPDATE users SET class = '1', vip_added = 'no', vip_until = '0000-00-00 00:00:00', modcomment = $modcom WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			sql_query ( "INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $arr[id], $dt, $msg, $subject)" ) or sqlerr ( __FILE__, __LINE__ );
		}
	}
	if ($printProgress) {
		printProgress ( "取消到期的VIP资格" );
	}
	
	// promote peasant back to user
	function peasant_to_user($down_floor_gb, $down_roof_gb, $minratio) {
		global $lang_cleanup_target;
		
		if ($down_floor_gb) {
			$downlimit_floor = $down_floor_gb * 1024 * 1024 * 1024;
			$downlimit_roof = $down_roof_gb * 1024 * 1024 * 1024;
			$res = sql_query ( "SELECT id FROM users WHERE class = 0 AND downloaded >= $downlimit_floor " . ($downlimit_roof > $down_floor_gb ? " AND downloaded < $downlimit_roof" : "") . " AND uploaded / downloaded >= $minratio" ) or sqlerr ( __FILE__, __LINE__ );
			if (mysql_num_rows ( $res ) > 0) {
				$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
				while ( $arr = mysql_fetch_assoc ( $res ) ) {
					$subject = sqlesc ( $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_low_ratio_warning_removed'] );
					$msg = sqlesc ( $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_your_ratio_warning_removed'] );
					writecomment ( $arr [id], "Leech Warning removed by System." );
					sql_query ( "UPDATE users SET class = 1, leechwarn = 'no', leechwarnuntil = '0000-00-00 00:00:00' WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
					sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[id], $dt, $subject, $msg)" ) or sqlerr ( __FILE__, __LINE__ );
				}
			}
		}
	}
	
	peasant_to_user ( $psdlfive_account, 0, $psratiofive_account );
	peasant_to_user ( $psdlfour_account, $psdlfive_account, $psratiofour_account );
	peasant_to_user ( $psdlthree_account, $psdlfour_account, $psratiothree_account );
	peasant_to_user ( $psdltwo_account, $psdlthree_account, $psratiotwo_account );
	peasant_to_user ( $psdlone_account, $psdltwo_account, $psratioone_account );
	if ($printProgress) {
		printProgress ( "将符合条件的peasant升级" );
	}
	// end promote peasant back to user
	
	// start promotion
	function promotion($class, $down_floor_gb, $minratio, $time_week, $addinvite = 0) {
		global $lang_cleanup_target;
		$oriclass = $class - 1;
		
		if ($down_floor_gb) {
			$limit = $down_floor_gb * 1024 * 1024 * 1024;
			$maxdt = date ( "Y-m-d H:i:s", (TIMENOW - 86400 * 7 * $time_week) );
			$res = sql_query ( "SELECT id, max_class_once FROM users WHERE class = $oriclass AND downloaded >= $limit AND uploaded / downloaded >= $minratio AND added < " . sqlesc ( $maxdt ) ) or sqlerr ( __FILE__, __LINE__ );
			if (mysql_num_rows ( $res ) > 0) {
				$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
				while ( $arr = mysql_fetch_assoc ( $res ) ) {
					$subject = sqlesc ( $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_promoted_to'] . get_user_class_name ( $class, false, false, false ) );
					$msg = sqlesc ( $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_now_you_are'] . get_user_class_name ( $class, false, false, false ) . $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_see_faq'] );
					if ($class <= $arr [max_class_once])
						sql_query ( "UPDATE users SET class = $class WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
					else
						sql_query ( "UPDATE users SET class = $class, max_class_once=$class, invites=invites+$addinvite WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
					
					sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[id], $dt, $subject, $msg)" ) or sqlerr ( __FILE__, __LINE__ );
				}
			}
		}
	}
	// do not change the ascending order
	promotion ( UC_POWER_USER, $pudl_account, $puprratio_account, $putime_account, $getInvitesByPromotion_class [UC_POWER_USER] );
	promotion ( UC_ELITE_USER, $eudl_account, $euprratio_account, $eutime_account, $getInvitesByPromotion_class [UC_ELITE_USER] );
	promotion ( UC_CRAZY_USER, $cudl_account, $cuprratio_account, $cutime_account, $getInvitesByPromotion_class [UC_CRAZY_USER] );
	promotion ( UC_INSANE_USER, $iudl_account, $iuprratio_account, $iutime_account, $getInvitesByPromotion_class [UC_INSANE_USER] );
	promotion ( UC_VETERAN_USER, $vudl_account, $vuprratio_account, $vutime_account, $getInvitesByPromotion_class [UC_VETERAN_USER] );
	promotion ( UC_EXTREME_USER, $exudl_account, $exuprratio_account, $exutime_account, $getInvitesByPromotion_class [UC_EXTREME_USER] );
	promotion ( UC_ULTIMATE_USER, $uudl_account, $uuprratio_account, $uutime_account, $getInvitesByPromotion_class [UC_ULTIMATE_USER] );
	promotion ( UC_NEXUS_MASTER, $nmdl_account, $nmprratio_account, $nmtime_account, $getInvitesByPromotion_class [UC_NEXUS_MASTER] );
	// end promotion
	if ($printProgress) {
		printProgress ( "将用户升级到其他等级" );
	}
	
	// start demotion
	function demotion($class, $deratio) {
		global $lang_cleanup_target;
		
		$newclass = $class - 1;
		$res = sql_query ( "SELECT id FROM users WHERE class = $class AND uploaded / downloaded < $deratio" ) or sqlerr ( __FILE__, __LINE__ );
		if (mysql_num_rows ( $res ) > 0) {
			$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
			while ( $arr = mysql_fetch_assoc ( $res ) ) {
				$subject = $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_demoted_to'] . get_user_class_name ( $newclass, false, false, false );
				$msg = $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_demoted_from'] . get_user_class_name ( $class, false, false, false ) . $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_to'] . get_user_class_name ( $newclass, false, false, false ) . $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_because_ratio_drop_below'] . $deratio . ".\n";
				sql_query ( "UPDATE users SET class = $newclass WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
				sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[id], $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
			}
		}
	}
	// do not change the descending order
	demotion ( UC_NEXUS_MASTER, $nmderatio_account );
	demotion ( UC_ULTIMATE_USER, $uuderatio_account );
	demotion ( UC_EXTREME_USER, $exuderatio_account );
	demotion ( UC_VETERAN_USER, $vuderatio_account );
	demotion ( UC_INSANE_USER, $iuderatio_account );
	demotion ( UC_CRAZY_USER, $cuderatio_account );
	demotion ( UC_ELITE_USER, $euderatio_account );
	demotion ( UC_POWER_USER, $puderatio_account );
	if ($printProgress) {
		printProgress ( "将用户降级到其他等级" );
	}
	// end demotion
	
	// start demote users to peasant
	function user_to_peasant($down_floor_gb, $minratio) {
		global $lang_cleanup_target;
		global $deletepeasant_account;
		
		$length = $deletepeasant_account * 86400; // warn users until xxx days
		$until = date ( "Y-m-d H:i:s", (TIMENOW + $length) );
		$downlimit_floor = $down_floor_gb * 1024 * 1024 * 1024;
		$res = sql_query ( "SELECT id FROM users WHERE class = 1 AND downloaded > $downlimit_floor AND uploaded / downloaded < $minratio" ) or sqlerr ( __FILE__, __LINE__ );
		if (mysql_num_rows ( $res ) > 0) {
			$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
			while ( $arr = mysql_fetch_assoc ( $res ) ) {
				$subject = $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_demoted_to'] . get_user_class_name ( UC_PEASANT, false, false, false );
				$msg = $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_must_fix_ratio_within'] . $deletepeasant_account . $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_days_or_get_banned'];
				writecomment ( $arr [id], "Leech Warned by System - Low Ratio." );
				sql_query ( "UPDATE users SET class = 0 , leechwarn = 'yes', leechwarnuntil = " . sqlesc ( $until ) . " WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
				sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[id], $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
			}
		}
	}
	
	user_to_peasant ( $psdlone_account, $psratioone_account );
	user_to_peasant ( $psdltwo_account, $psratiotwo_account );
	user_to_peasant ( $psdlthree_account, $psratiothree_account );
	user_to_peasant ( $psdlfour_account, $psratiofour_account );
	user_to_peasant ( $psdlfive_account, $psratiofive_account );
	if ($printProgress) {
		printProgress ( "给予分享率低警告" );
	}
	// end Users to Peasant
	/*
	 * //send email to users will be banned in 3 days $to = sqlesc(date("Y-m-d
	 * H:i:s",(TIMENOW + 3*86400))); $from = sqlesc(date("Y-m-d H:i:s",(TIMENOW
	 * + 3*86400-$autoclean_interval_three))); $res = sql_query("SELECT
	 * id,email,username,leechwarnuntil FROM users WHERE enabled = 'yes' AND
	 * leechwarn = 'yes' AND leechwarnuntil >= $from AND leechwarnuntil < $to")
	 * or sqlerr(__FILE__, __LINE__); if (mysql_num_rows($res) > 0) { while
	 * ($arr = mysql_fetch_assoc($res)) { $emailaddress =
	 * safe_email($arr['email']); $title =
	 * $lang_cleanup_target['chs']['msg_email_leechwarning_subject'].$arr['username'].$lang_cleanup_target['chs']['msg_email_leechwarning_subject2'];
	 * $message
	 * =$lang_cleanup_target['chs']['msg_email_leechwarning_msg1'].$arr['username'].$lang_cleanup_target['chs']['msg_email_leechwarning_msg2'].$arr['leechwarnuntil'].$lang_cleanup_target['chs']['msg_email_leechwarning_msg3'];
	 * sent_mail($emailaddress,$SITENAME,$SITEEMAIL,change_email_encode("chs",
	 * $title),change_email_encode("chs",$message),"leechwarn",false,false,'',"gbk");
	 * } } if ($printProgress) { printProgress("send email to peasant will be
	 * banned in 3 days"); }
	 */
	// ban users with leechwarning expired
	$dt = sqlesc ( date ( "Y-m-d H:i:s" ) ); // take date time
	$res = sql_query ( "SELECT id ,username FROM users WHERE enabled = 'yes' AND leechwarn = 'yes' AND leechwarnuntil < $dt" ) or sqlerr ( __FILE__, __LINE__ );
	
	if (mysql_num_rows ( $res ) > 0) {
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			writecomment ( $arr [id], "Banned by System because of Leech Warning expired." );
			write_log ( "系统禁用了帐号 $arr[id] ($arr[username]) (分享率过低且在" . $deletepeasant_account . "天的宽限期内未能改善)", 'normal' );
			sql_query ( "UPDATE users SET enabled = 'no', leechwarnuntil = '0000-00-00 00:00:00' WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			
			// $emailaddress = safe_email($arr['email']);
			// $title =
			// $lang_cleanup_target['chs']['msg_email_leechwarning_subject'].$arr['username'].$lang_cleanup_target['chs']['msg_email_leechban_subject2'];
			// $message
			// =$lang_cleanup_target['chs']['msg_email_leechwarning_msg1'].$arr['username'].$lang_cleanup_target['chs']['msg_email_leechban_msg2'].$arr['leechwarnuntil']}{$lang_cleanup_target['chs']['msg_email_leechwarning_msg3'];
			// sent_mail($emailaddress,$SITENAME,$SITEEMAIL,change_email_encode("chs",
			// $title),change_email_encode("chs",$message),"leechwarn",false,false,'',get_email_encode("chs"));
		}
	}
	if ($printProgress) {
		printProgress ( "禁用分享率过低且在期限内未改善的用户" );
	}
	
	// Remove warning of users
	$dt = sqlesc ( date ( "Y-m-d H:i:s" ) ); // take date time
	$res = sql_query ( "SELECT id FROM users WHERE enabled = 'yes' AND warned = 'yes' AND warneduntil < $dt" ) or sqlerr ( __FILE__, __LINE__ );
	
	if (mysql_num_rows ( $res ) > 0) {
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			$subject = $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_warning_removed'];
			$msg = $lang_cleanup_target [get_user_lang ( $arr [id] )] ['msg_your_warning_removed'];
			writecomment ( $arr [id], "自动解除警告." );
			sql_query ( "UPDATE users SET warned = 'no', warneduntil = '0000-00-00 00:00:00' WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[id], $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		}
	}
	if ($printProgress) {
		printProgress ( "解除超期警告" );
	}
	
	// 移除被禁言用户
	$dt = sqlesc ( date ( "Y-m-d H:i:s" ) ); // take date time
	$res = sql_query ( "SELECT id FROM users WHERE enabled = 'yes' AND forumpost = 'no' AND forumbanuntil < $dt AND forumbanuntil !='0000-00-00 00:00:00'" ) or sqlerr ( __FILE__, __LINE__ );
	
	if (mysql_num_rows ( $res ) > 0) {
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			$subject = "您的论坛功能恢复";
			$msg = "由于期限已到，您的禁言被系统自动解除。我们希望你自此能好好表现。";
			writecomment ( $arr [id], "自动解除禁言." );
			sql_query ( "UPDATE users SET forumpost = 'yes', forumbanuntil = '0000-00-00 00:00:00' WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
			sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[id], $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		}
	}
	if ($printProgress) {
		printProgress ( "解除超期禁言" );
	}
	
	sql_query ( "DELETE FROM banipv6 WHERE until < $dt AND until > '0000-00-00 00:00:00'" ) or sqlerr ( __FILE__, __LINE__ );
	sql_query ( "DELETE FROM bans WHERE until < $dt AND until > '0000-00-00 00:00:00'" ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( "解除超期IP封禁" );
	}
	// 竞猜排行榜
	update_jc_rank ();
	if ($printProgress) {
		printProgress ( "更新竞猜排行榜" );
	}

	// 记录系统数据
	$dt = sqlesc ( date ( "Y-m-d H:i:s" ) ); // take date time
	$totaltorrentssize = sqlesc ( get_row_sum ( "torrents", "size" ) );
	$totaluploaded = sqlesc ( get_row_sum ( "users", "uploaded" ) );
	$totaldownloaded = sqlesc ( get_row_sum ( "users", "downloaded" ) );
	$totalbonus = sqlesc ( get_single_value ( "users WHERE enabled='yes'", "sum(seedbonus) " ) );
	$totalinvites = sqlesc ( get_single_value ( "users WHERE enabled='yes'", "sum(invites)" ) );
	sql_query ( "INSERT INTO sitelog_stats (statstime, totaltorrentssize, totaluploaded, totaldownloaded, totalbonus, totalinvites) VALUES ( $dt, $totaltorrentssize, $totaluploaded, $totaldownloaded, $totalbonus, $totalinvites)" ) or sqlerr ( __FILE__, __LINE__ );
	
	// ***********************************add end
	// ****************************************************
	
	// Priority Class 4: cleanup every 24 hours
	$res = sql_query ( "SELECT value_u FROM avps WHERE arg = 'lastcleantime4'" );
	$row = mysql_fetch_array ( $res );
	if (! $row) {
		sql_query ( "INSERT INTO avps (arg, value_u) VALUES ('lastcleantime4',$now)" ) or sqlerr ( __FILE__, __LINE__ );
		return;
	}
	$ts = $row [0];
	if ($ts + $autoclean_interval_four > $now && ! $forceAll) {
		return '三级清理完成';
	} else {
		sql_query ( "UPDATE avps SET value_u = " . sqlesc ( $now ) . " WHERE arg='lastcleantime4'" ) or sqlerr ( __FILE__, __LINE__ );
	}
	
	// 3.delete unconfirmed accounts
	$deadtime = time () - $signup_timeout;
	sql_query ( "DELETE FROM users WHERE status = 'pending' AND added < FROM_UNIXTIME($deadtime) AND last_login < FROM_UNIXTIME($deadtime) AND last_access < FROM_UNIXTIME($deadtime)" ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( "删除未确认帐号" );
	}
	
	// 5.delete old login attempts
	$secs = 12 * 60 * 60; // Delete failed login attempts per half day.
	$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - $secs) ) ); // calculate
	                                                            // date.
	sql_query ( "DELETE FROM loginattempts WHERE added < $dt" ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( "删除登录失败记录" );
	}
	
	// 6.delete old invite codes
	$secs = $invite_timeout * 24 * 60 * 60; // when?
	$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - $secs) ) ); // calculate
	                                                            // date.
	$res = sql_query("select * from invites WHERE time_invited < $dt" );
	while($row = mysql_fetch_array($res)){
	if(	$row[inviter] != 0 ){
	sql_query ( "update users set invites = invites + 1 WHERE id = $row[inviter]" ) or sqlerr ( __FILE__, __LINE__ );
				$invitee = sqlesc ( $row[invitee] );
				
				$added = sqlesc ( date ( "Y-m-d H:i:s" ) );
				
				$subject = sqlesc ( "邀请码回收" );
				
				$notifs = sqlesc ( "由于被邀请者没有在规定时间内注册，你发送给 $invitee 的邀请码已经过期并回收，你可以将其发给别人。" );
				
				sql_query ( "INSERT INTO messages (sender, receiver, subject, msg, added) VALUES(0, '" . $row[inviter] . "', $subject, $notifs, $added)" ) or sqlerr ( __FILE__, __LINE__ );}
	}
	sql_query ( "DELETE FROM invites WHERE time_invited < $dt" ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( "回收过期邀请码" );
	}
	
	//保种置顶
	$res=sql_query("select * from torrents WHERE needkeepseed = 'yes' and seeders < 3 and seeders>0 and pos_state=1" );
	if ($res){
	$num=mysql_num_rows($res)-1;
	mysql_data_seek($res,rand(0,$num));
	if($row=mysql_fetch_row($res)){
	$tid=$row[0];
	$tname=$row[2];
	$currenthour=date("H");
	$endtime= sqlesc ( date ( "Y-m-d H:i:s" , mktime($currenthour+24)));
	sql_query ( "update torrents set pos_state = 2, pos_state_until = $endtime WHERE id = $tid" ) or sqlerr ( __FILE__, __LINE__ );
		write_log ( "系统 编辑了资源 $tid (".$tname.") 置顶");}
	}
	if ($printProgress) 
		printProgress ( "保种置顶" );
//===========================================保种置顶==================================	
	// 7.delete regimage codes
	sql_query ( "TRUNCATE TABLE `regimages`" ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( "删除过期验证码" );
	}
	
			// 扣除被禁用户的魔力值
	if ($deleteunpacked_account) {
		$res = sql_query ( "SELECT * FROM users WHERE enabled='no' AND seedbonus<0" ) or sqlerr ( __FILE__, __LINE__ );
			while ( $arr = mysql_fetch_assoc ( $res ) )
			write_log ( "系统删除了帐号 $arr[id] ($arr[username]) (被禁用帐号魔力值小于0)", 'normal' );
		sql_query ( "DELETE FROM users WHERE enabled='no' AND seedbonus<=0" ) or sqlerr ( __FILE__, __LINE__ );
		sql_query ( "UPDATE users SET seedbonus = seedbonus - 200 WHERE enabled='no' AND seedbonus>0" ) or sqlerr ( __FILE__, __LINE__ );		
	}
	if ($printProgress) {
		printProgress ( "删除被禁用且魔力值小于0的帐号" );
	}
	
	// 10.clean up user accounts
	// make sure VIP or above never get deleted
	
	// **************************add zhushi by shang because this move
	// up***********************
	
	/*
	 * $neverdelete_account = ($neverdelete_account <= UC_VIP ?
	 * $neverdelete_account : UC_VIP); //delete inactive user accounts, no
	 * transfer. Alt. 1: last access time if ($deletenotransfer_account){ $secs
	 * = $deletenotransfer_account*24*60*60; $dt = sqlesc(date("Y-m-d
	 * H:i:s",(TIMENOW - $secs))); $maxclass = $neverdelete_account;
	 * sql_query("DELETE FROM users WHERE parked='no' AND status='confirmed' AND
	 * class < $maxclass AND last_access < $dt AND (uploaded = 0 || uploaded =
	 * ".sqlesc($iniupload_main).") AND downloaded = 0") or sqlerr(__FILE__,
	 * __LINE__); } if ($printProgress) { printProgress("delete inactive user
	 * accounts, no transfer. Alt. 1: last access time"); } //delete inactive
	 * user accounts, no transfer. Alt. 2: registering time if
	 * ($deletenotransfertwo_account){ $secs =
	 * $deletenotransfertwo_account*24*60*60; $dt = sqlesc(date("Y-m-d
	 * H:i:s",(TIMENOW - $secs))); $maxclass = $neverdelete_account;
	 * sql_query("DELETE FROM users WHERE parked='no' AND status='confirmed' AND
	 * class < $maxclass AND added < $dt AND (uploaded = 0 || uploaded =
	 * ".sqlesc($iniupload_main).") AND downloaded = 0") or sqlerr(__FILE__,
	 * __LINE__); } if ($printProgress) { printProgress("delete inactive user
	 * accounts, no transfer. Alt. 2: registering time"); } //delete inactive
	 * user accounts, not parked if ($deleteunpacked_account){ $secs =
	 * $deleteunpacked_account*24*60*60; $dt = sqlesc(date("Y-m-d
	 * H:i:s",(TIMENOW - $secs))); $maxclass = $neverdelete_account;
	 * sql_query("DELETE FROM users WHERE parked='no' AND status='confirmed' AND
	 * class < $maxclass AND last_access < $dt") or sqlerr(__FILE__, __LINE__);
	 * } if ($printProgress) { printProgress("delete inactive user accounts, not
	 * parked"); } //delete parked user accounts, parked if
	 * ($deletepacked_account){ $secs = $deletepacked_account*24*60*60; $dt =
	 * sqlesc(date("Y-m-d H:i:s",(TIMENOW - $secs))); $maxclass =
	 * $neverdeletepacked_account; sql_query("DELETE FROM users WHERE
	 * parked='yes' AND status='confirmed' AND class < $maxclass AND last_access
	 * < $dt") or sqlerr(__FILE__, __LINE__); } if ($printProgress) {
	 * printProgress("delete parked user accounts, parked"); } //remove VIP
	 * status if time's up $res = sql_query("SELECT id, modcomment FROM users
	 * WHERE vip_added='yes' AND vip_until < NOW()") or sqlerr(__FILE__,
	 * __LINE__); if (mysql_num_rows($res) > 0) { while ($arr =
	 * mysql_fetch_assoc($res)) { $dt = sqlesc(date("Y-m-d H:i:s")); $subject =
	 * sqlesc($lang_cleanup_target[get_user_lang($arr[id])]['msg_vip_status_removed']);
	 * $msg =
	 * sqlesc($lang_cleanup_target[get_user_lang($arr[id])]['msg_vip_status_removed_body']);
	 * ///---AUTOSYSTEM MODCOMMENT---// $modcomment =
	 * htmlspecialchars($arr["modcomment"]); $modcomment = date("Y-m-d") . " -
	 * VIP status removed by - AutoSystem.\n". $modcomment; $modcom =
	 * sqlesc($modcomment); ///---end sql_query("UPDATE users SET class = '1',
	 * vip_added = 'no', vip_until = '0000-00-00 00:00:00', modcomment = $modcom
	 * WHERE id = $arr[id]") or sqlerr(__FILE__, __LINE__); sql_query("INSERT
	 * INTO messages (sender, receiver, added, msg, subject) VALUES(0, $arr[id],
	 * $dt, $msg, $subject)") or sqlerr(__FILE__, __LINE__); } } if
	 * ($printProgress) { printProgress("remove VIP status if time's up"); } //
	 * promote peasant back to user function peasant_to_user($down_floor_gb,
	 * $down_roof_gb, $minratio){ global $lang_cleanup_target; if
	 * ($down_floor_gb){ $downlimit_floor = $down_floor_gb*1024*1024*1024;
	 * $downlimit_roof = $down_roof_gb*1024*1024*1024; $res = sql_query("SELECT
	 * id FROM users WHERE class = 0 AND downloaded >= $downlimit_floor
	 * ".($downlimit_roof > $down_floor_gb ? " AND downloaded < $downlimit_roof"
	 * : "")." AND uploaded / downloaded >= $minratio") or sqlerr(__FILE__,
	 * __LINE__); if (mysql_num_rows($res) > 0) { $dt = sqlesc(date("Y-m-d
	 * H:i:s")); while ($arr = mysql_fetch_assoc($res)) { $subject =
	 * sqlesc($lang_cleanup_target[get_user_lang($arr[id])]['msg_low_ratio_warning_removed']);
	 * $msg =
	 * sqlesc($lang_cleanup_target[get_user_lang($arr[id])]['msg_your_ratio_warning_removed']);
	 * writecomment($arr[id],"Leech Warning removed by System.");
	 * sql_query("UPDATE users SET class = 1, leechwarn = 'no', leechwarnuntil =
	 * '0000-00-00 00:00:00' WHERE id = $arr[id]") or sqlerr(__FILE__,
	 * __LINE__); sql_query("INSERT INTO messages (sender, receiver, added,
	 * subject, msg) VALUES(0, $arr[id], $dt, $subject, $msg)") or
	 * sqlerr(__FILE__, __LINE__); } } } } peasant_to_user($psdlfive_account,0,
	 * $psratiofive_account);
	 * peasant_to_user($psdlfour_account,$psdlfive_account,
	 * $psratiofour_account);
	 * peasant_to_user($psdlthree_account,$psdlfour_account,
	 * $psratiothree_account);
	 * peasant_to_user($psdltwo_account,$psdlthree_account,
	 * $psratiotwo_account); peasant_to_user($psdlone_account,$psdltwo_account,
	 * $psratioone_account); if ($printProgress) { printProgress("promote
	 * peasant back to user"); } //end promote peasant back to user // start
	 * promotion function promotion($class, $down_floor_gb, $minratio,
	 * $time_week, $addinvite = 0){ global $lang_cleanup_target; $oriclass =
	 * $class - 1; if ($down_floor_gb){ $limit = $down_floor_gb*1024*1024*1024;
	 * $maxdt = date("Y-m-d H:i:s",(TIMENOW - 86400*7*$time_week)); $res =
	 * sql_query("SELECT id, max_class_once FROM users WHERE class = $oriclass
	 * AND downloaded >= $limit AND uploaded / downloaded >= $minratio AND added
	 * < ".sqlesc($maxdt)) or sqlerr(__FILE__, __LINE__); if
	 * (mysql_num_rows($res) > 0) { $dt = sqlesc(date("Y-m-d H:i:s")); while
	 * ($arr = mysql_fetch_assoc($res)) { $subject =
	 * sqlesc($lang_cleanup_target[get_user_lang($arr[id])]['msg_promoted_to'].get_user_class_name($class,false,false,false));
	 * $msg =
	 * sqlesc($lang_cleanup_target[get_user_lang($arr[id])]['msg_now_you_are'].get_user_class_name($class,false,false,false).$lang_cleanup_target[get_user_lang($arr[id])]['msg_see_faq']);
	 * if($class<=$arr[max_class_once]) sql_query("UPDATE users SET class =
	 * $class WHERE id = $arr[id]") or sqlerr(__FILE__, __LINE__); else
	 * sql_query("UPDATE users SET class = $class, max_class_once=$class,
	 * invites=invites+$addinvite WHERE id = $arr[id]") or sqlerr(__FILE__,
	 * __LINE__); sql_query("INSERT INTO messages (sender, receiver, added,
	 * subject, msg) VALUES(0, $arr[id], $dt, $subject, $msg)") or
	 * sqlerr(__FILE__, __LINE__); } } } } //do not change the ascending order
	 * promotion(UC_POWER_USER, $pudl_account, $puprratio_account,
	 * $putime_account, $getInvitesByPromotion_class[UC_POWER_USER]);
	 * promotion(UC_ELITE_USER, $eudl_account, $euprratio_account,
	 * $eutime_account, $getInvitesByPromotion_class[UC_ELITE_USER]);
	 * promotion(UC_CRAZY_USER, $cudl_account, $cuprratio_account,
	 * $cutime_account, $getInvitesByPromotion_class[UC_CRAZY_USER]);
	 * promotion(UC_INSANE_USER, $iudl_account, $iuprratio_account,
	 * $iutime_account, $getInvitesByPromotion_class[UC_INSANE_USER]);
	 * promotion(UC_VETERAN_USER, $vudl_account, $vuprratio_account,
	 * $vutime_account, $getInvitesByPromotion_class[UC_VETERAN_USER]);
	 * promotion(UC_EXTREME_USER, $exudl_account, $exuprratio_account,
	 * $exutime_account, $getInvitesByPromotion_class[UC_EXTREME_USER]);
	 * promotion(UC_ULTIMATE_USER, $uudl_account, $uuprratio_account,
	 * $uutime_account, $getInvitesByPromotion_class[UC_ULTIMATE_USER]);
	 * promotion(UC_NEXUS_MASTER, $nmdl_account, $nmprratio_account,
	 * $nmtime_account, $getInvitesByPromotion_class[UC_NEXUS_MASTER]); // end
	 * promotion if ($printProgress) { printProgress("promote users to other
	 * classes"); } // start demotion function demotion($class,$deratio){ global
	 * $lang_cleanup_target; $newclass = $class - 1; $res = sql_query("SELECT id
	 * FROM users WHERE class = $class AND uploaded / downloaded < $deratio") or
	 * sqlerr(__FILE__, __LINE__); if (mysql_num_rows($res) > 0) { $dt =
	 * sqlesc(date("Y-m-d H:i:s")); while ($arr = mysql_fetch_assoc($res)) {
	 * $subject =
	 * $lang_cleanup_target[get_user_lang($arr[id])]['msg_demoted_to'].get_user_class_name($newclass,false,false,false);
	 * $msg =
	 * $lang_cleanup_target[get_user_lang($arr[id])]['msg_demoted_from'].get_user_class_name($class,false,false,false).$lang_cleanup_target[get_user_lang($arr[id])]['msg_to'].get_user_class_name($newclass,false,false,false).$lang_cleanup_target[get_user_lang($arr[id])]['msg_because_ratio_drop_below'].$deratio.".\n";
	 * sql_query("UPDATE users SET class = $newclass WHERE id = $arr[id]") or
	 * sqlerr(__FILE__, __LINE__); sql_query("INSERT INTO messages (sender,
	 * receiver, added, subject, msg) VALUES(0, $arr[id], $dt,
	 * ".sqlesc($subject).", ".sqlesc($msg).")") or sqlerr(__FILE__, __LINE__);
	 * } } } //do not change the descending order
	 * demotion(UC_NEXUS_MASTER,$nmderatio_account);
	 * demotion(UC_ULTIMATE_USER,$uuderatio_account);
	 * demotion(UC_EXTREME_USER,$exuderatio_account);
	 * demotion(UC_VETERAN_USER,$vuderatio_account);
	 * demotion(UC_INSANE_USER,$iuderatio_account);
	 * demotion(UC_CRAZY_USER,$cuderatio_account);
	 * demotion(UC_ELITE_USER,$euderatio_account);
	 * demotion(UC_POWER_USER,$puderatio_account); if ($printProgress) {
	 * printProgress("demote users to other classes"); } // end demotion //
	 * start demote users to peasant function user_to_peasant($down_floor_gb,
	 * $minratio){ global $lang_cleanup_target; global $deletepeasant_account;
	 * $length = $deletepeasant_account*86400; // warn users until xxx days
	 * $until = date("Y-m-d H:i:s",(TIMENOW + $length)); $downlimit_floor =
	 * $down_floor_gb*1024*1024*1024; $res = sql_query("SELECT id FROM users
	 * WHERE class = 1 AND downloaded > $downlimit_floor AND uploaded /
	 * downloaded < $minratio") or sqlerr(__FILE__, __LINE__); if
	 * (mysql_num_rows($res) > 0) { $dt = sqlesc(date("Y-m-d H:i:s")); while
	 * ($arr = mysql_fetch_assoc($res)) { $subject =
	 * $lang_cleanup_target[get_user_lang($arr[id])]['msg_demoted_to'].get_user_class_name(UC_PEASANT,false,false,false);
	 * $msg =
	 * $lang_cleanup_target[get_user_lang($arr[id])]['msg_must_fix_ratio_within'].$deletepeasant_account.$lang_cleanup_target[get_user_lang($arr[id])]['msg_days_or_get_banned'];
	 * writecomment($arr[id],"Leech Warned by System - Low Ratio.");
	 * sql_query("UPDATE users SET class = 0 , leechwarn = 'yes', leechwarnuntil
	 * = ".sqlesc($until)." WHERE id = $arr[id]") or sqlerr(__FILE__, __LINE__);
	 * sql_query("INSERT INTO messages (sender, receiver, added, subject, msg)
	 * VALUES(0, $arr[id], $dt, ".sqlesc($subject).", ".sqlesc($msg).")") or
	 * sqlerr(__FILE__, __LINE__); } } } user_to_peasant($psdlone_account,
	 * $psratioone_account); user_to_peasant($psdltwo_account,
	 * $psratiotwo_account); user_to_peasant($psdlthree_account,
	 * $psratiothree_account); user_to_peasant($psdlfour_account,
	 * $psratiofour_account); user_to_peasant($psdlfive_account,
	 * $psratiofive_account); if ($printProgress) { printProgress("demote Users
	 * to peasant"); } // end Users to Peasant //ban users with leechwarning
	 * expired $dt = sqlesc(date("Y-m-d H:i:s")); // take date time $res =
	 * sql_query("SELECT id FROM users WHERE enabled = 'yes' AND leechwarn =
	 * 'yes' AND leechwarnuntil < $dt") or sqlerr(__FILE__, __LINE__); if
	 * (mysql_num_rows($res) > 0) { while ($arr = mysql_fetch_assoc($res)) {
	 * writecomment($arr[id],"Banned by System because of Leech Warning
	 * expired."); sql_query("UPDATE users SET enabled = 'no', leechwarnuntil =
	 * '0000-00-00 00:00:00' WHERE id = $arr[id]") or sqlerr(__FILE__,
	 * __LINE__); } } if ($printProgress) { printProgress("ban users with
	 * leechwarning expired"); } //Remove warning of users $dt =
	 * sqlesc(date("Y-m-d H:i:s")); // take date time $res = sql_query("SELECT
	 * id FROM users WHERE enabled = 'yes' AND warned = 'yes' AND warneduntil <
	 * $dt") or sqlerr(__FILE__, __LINE__); if (mysql_num_rows($res) > 0) {
	 * while ($arr = mysql_fetch_assoc($res)) { $subject =
	 * $lang_cleanup_target[get_user_lang($arr[id])]['msg_warning_removed'];
	 * $msg =
	 * $lang_cleanup_target[get_user_lang($arr[id])]['msg_your_warning_removed'];
	 * writecomment($arr[id],"Warning removed by System."); sql_query("UPDATE
	 * users SET warned = 'no', warneduntil = '0000-00-00 00:00:00' WHERE id =
	 * $arr[id]") or sqlerr(__FILE__, __LINE__); sql_query("INSERT INTO messages
	 * (sender, receiver, added, subject, msg) VALUES(0, $arr[id], $dt,
	 * ".sqlesc($subject).", ".sqlesc($msg).")") or sqlerr(__FILE__, __LINE__);
	 * } } if ($printProgress) { printProgress("remove warning of users"); }
	 */
	
	// 17.update total seeding and leeching time of users
	$res = sql_query ( "SELECT * FROM users" ) or sqlerr ( __FILE__, __LINE__ );
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		$res2 = sql_query ( "SELECT SUM(seedtime) as st, SUM(leechtime) as lt FROM snatched where userid = " . $arr ['id'] . " LIMIT 1" ) or sqlerr ( __FILE__, __LINE__ );
		$arr2 = mysql_fetch_assoc ( $res2 ) or sqlerr ( __FILE__, __LINE__ );
		
		sql_query ( "UPDATE users SET seedtime = " . floatval ( $arr2 ['st'] ) . ", leechtime = " . floatval ( $arr2 ['lt'] ) . " WHERE id = " . $arr ['id'] ) or sqlerr ( __FILE__, __LINE__ );
	}
	if ($printProgress) {
		printProgress ( "更新用户总的做种时间和下载时间" );
	}
	
	// delete torrents that have been dead for a long time
	if ($deldeadtorrent_torrent > 0) {
		$length = $deldeadtorrent_torrent * 86400;
		$until = date ( "Y-m-d H:i:s", (TIMENOW - $length) );
		$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
		$res = sql_query ( "SELECT id, name, owner FROM torrents WHERE visible = 'no' AND last_action < " . sqlesc ( $until ) . " AND seeders = 0 AND leechers = 0 AND sp_state < '8' " ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			deletetorrent_meanit ( $arr ['id'] );
			$subject = $lang_cleanup_target [get_user_lang ( $arr [owner] )] ['msg_your_torrent_deleted'];
			$msg = $lang_cleanup_target [get_user_lang ( $arr [owner] )] ['msg_your_torrent'] . "[i]" . $arr ['name'] . "[/i]" . $lang_cleanup_target [get_user_lang ( $arr [owner] )] ['msg_was_deleted_because_dead'];
			sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[owner], $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
			write_log ( "系统删除了资源 $arr[id] ($arr[name]) (长期断种)", 'normal' );
		}
	}
	// 删除永久促销的长期断种（180天）
	$length = 180 * 86400;
	$until = date ( "Y-m-d H:i:s", (TIMENOW - $length) );
	$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
	$res = sql_query ( "SELECT id, name, owner FROM torrents WHERE visible = 'no' AND last_action < " . sqlesc ( $until ) . " AND seeders = 0 AND leechers = 0 AND sp_state > '8' " ) or sqlerr ( __FILE__, __LINE__ );
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		deletetorrent_meanit ( $arr ['id'] );
		$subject = $lang_cleanup_target [get_user_lang ( $arr [owner] )] ['msg_your_torrent_deleted'];
		$msg = $lang_cleanup_target [get_user_lang ( $arr [owner] )] ['msg_your_torrent'] . "[i]" . $arr ['name'] . "[/i]" . $lang_cleanup_target [get_user_lang ( $arr [owner] )] ['msg_was_deleted_because_dead'];
		sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[owner], $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		write_log ( "系统删除了资源 $arr[id] ($arr[name]) (永久促销长期断种)", 'normal' );
	}
	// 删除回收站资源（7天）
	$length = 7 * 86400;
	$until = date ( "Y-m-d H:i:s", (TIMENOW - $length) );
	$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
	$res = sql_query ( "SELECT id, name, owner FROM torrents WHERE pulling_out = 1 AND last_action < " . sqlesc ( $until ) . " AND seeders = 0 AND leechers = 0 " ) or sqlerr ( __FILE__, __LINE__ );
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		deletetorrent_meanit ( $arr ['id'] );
		write_log ( "系统删除了资源 $arr[id] ($arr[name]) (回收站)", 'normal' );
	}
	// 删除已被删除的种子的关联字幕
	$res = sql_query ( 'SELECT id, torrent_id, title, ext, filename FROM subs WHERE (SELECT COUNT(*) FROM torrents WHERE torrents.id=subs.torrent_id) = 0' );
	while ( $arr = mysql_fetch_assoc ( $res ) ) {
		if (file_exists ( "$SUBSPATH/$arr[torrent_id]/$arr[id].$arr[ext]" ) && ! unlink ( "$SUBSPATH/$arr[torrent_id]/$arr[id].$arr[ext]" )) {
			write_log ( "系统删除种子$arr[torrent_id] 的关联字幕$arr[id] ($arr[title]) 失败! (种子已被删除)", 'normal' );
		} else {
			write_log ( "系统删除了种子$arr[torrent_id] 的关联字幕$arr[id] ($arr[title]) (种子已被删除)", 'normal' );
		}
		sql_query ( "DELETE FROM subs WHERE id = $arr[id]" );
	}
	
	if ($printProgress) {
		printProgress ( "删除长期断种的资源" );
	}
	
	// 类管发工资
	$tju_salary_class = UC_MODERATOR;
	$tju_salary_bonus = 5000;
	$tju_no_salary_id = array (
			'74' 
	);
	$res = sql_query ( "SELECT salarytime FROM tju_autosalary ORDER BY salarytime DESC limit 1" ) or sqlerr ( __FILE__, __LINE__ );
	$arr = mysql_fetch_assoc ( $res );
	if (date ( 'w' ) == 0 && date ( "Y-m-d", strtotime ( $arr ['salarytime'] ) ) < date ( "Y-m-d" )) {
		$from = (date ( "w" ) != 0) ? strtotime ( "this week Sunday" ) - 14 * 86400 : strtotime ( "next week Sunday" ) - 14 * 86400;
		$to = (date ( "w" ) != 0) ? strtotime ( "this week Sunday" ) - 7 * 86400 : strtotime ( "next week Sunday" ) - 7 * 86400;
		$staffmsg = " [b]" . date ( "Y-m-d", $from ) . "[/b] 至 [b]" . date ( "Y-m-d", $to - 1 ) . "[/b] 的工资发放情况：\n";
		
		$res = sql_query ( "SELECT id, username, class, bonuscomment FROM users WHERE class=" . sqlesc ( $tju_salary_class ) . " ORDER BY class DESC, username" ) or sqlerr ( __FILE__, __LINE__ );
		while ( $arr = mysql_fetch_assoc ( $res ) ) {
			if (! in_array ( $arr ['id'], $tju_no_salary_id, TRUE )) {
				$bonuscomment = $arr ['bonuscomment'];
				$bonuscomment = date ( "Y-m-d" ) . " 工资收入" . $tju_salary_bonus . "个魔力值。\n" . htmlspecialchars ( $bonuscomment );
				
				sql_query ( "UPDATE users SET seedbonus = seedbonus + $tju_salary_bonus, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = $arr[id]" ) or sqlerr ( __FILE__, __LINE__ );
				
				$subject = "发工资啦~";
				$msg = "亲爱的 " . $arr ["username"] . " ，[b]" . date ( "Y-m-d", $from ) . "[/b] 至 [b]" . date ( "Y-m-d", $to - 1 ) . "[/b] 的工资 [b][color=Blue]" . $tju_salary_bonus . "[/color][/b] 个魔力值已发放，请笑纳~";
				$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
				sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[id], $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
				sql_query ( "INSERT INTO tju_autosalary (user_id, user_class, salary, salarytime) VALUES($arr[id], $arr[class], " . sqlesc ( $tju_salary_bonus ) . ", " . $dt . ")" ) or sqlerr ( __FILE__, __LINE__ );
				$Cache->delete_value ( 'user_' . $arr ['id'] . '_unread_message_count' );
				$Cache->delete_value ( 'user_' . $arr ['id'] . '_inbox_count' );
				
				$staffmsg = $staffmsg . $arr ["username"] . " : 魔力值[color=Blue]" . $tju_salary_bonus . "[/color]个" . "\n";
			}
		}
		$subject = "类管发工资啦~";
		$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
		sql_query ( "INSERT INTO staffmessages (sender, added, subject, msg) VALUES(0, $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $staffmsg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		$Cache->delete_value ( 'staff_message_count' );
		$Cache->delete_value ( 'staff_new_message_count' );
		
		if ($printProgress) {
			printProgress ( "发放雷管工资" );
		}
	}
		// 发布员发工资
	global $Cache;
	$log_uploader = $Cache->get_value ( 'log_uploader' );
	$salary_class = UC_UPLOADER;
	$res = sql_query ( "SELECT salarytime FROM uploader_autosalary ORDER BY salarytime DESC limit 1" ) or sqlerr ( __FILE__, __LINE__ );
	$arr = mysql_fetch_assoc ( $res );
	$standard = array (
					'num' => 20,
					'size' => 30 * 1024 * 1024 * 1024 
			);
	if (date ( 'j' ) == 1 && date ( "Y-m-d", strtotime ( $arr ['salarytime'] ) ) < date ( "Y-m-d" )) {
		$staffmsg = " 上月的发布员工资发放情况：\n";
		
		$res = sql_query ( "SELECT id, username, class, bonuscomment, stafffor FROM users WHERE class=" . sqlesc ( $salary_class ) . " ORDER BY username" ) or sqlerr ( __FILE__, __LINE__ );
		$last = strtotime ( date ( "Y-m-1 0:0:0", strtotime ( "-2 month " ) ) );
		$from = strtotime ( date ( "Y-m-1 0:0:0", strtotime ( "last month " ) ) );
		$to = strtotime ( date ( "Y-m-1 0:0:0" ) );
		while ( $row = mysql_fetch_array ( $res ) )
		$usernames [] = $row;
		if (! $log_uploader = $Cache->get_value ( 'log_uploader' )) {
			foreach ( $usernames as $user ) {
				if (strpos ( $user ['stafffor'], "(请假中)" ) !== false)
					$log_uploader [$user ['username']] ["thisnum"] = "请假";
				else {
						$wheres = "SELECT count(*) as num , sum(size) as size FROM torrents WHERE added >= '" . date ( "Y-m-d H:i:s", $from ) . "' AND added <'" . date ( "Y-m-d H:i:s", $to ) . "' AND owner = '" . $user ["id"] . "' ";
						$res = mysql_fetch_assoc ( sql_query ( $wheres ) );
						$log_uploader [$user ['username']] ["thisnum"] = $res ["num"];
						$log_uploader [$user ['username']] ["thissize"] = $res ["size"];
						$wheres = "SELECT count(*) as num , sum(size) as size FROM torrents WHERE added >= '" . date ( "Y-m-d H:i:s", $last ) . "' AND added <'" . date ( "Y-m-d H:i:s", $from ) . "' AND owner = '" . $user ["id"] . "' ";
						$res = mysql_fetch_assoc ( sql_query ( $wheres ) );
						$log_uploader [$user ['username']] ["lastnum"] = $res ["num"];
						$log_uploader [$user ['username']] ["lastsize"] = $res ["size"];
						$deleted="select deleted_last from uploaders where uid = ".$user ['id']."";
						$res = mysql_fetch_array ( sql_query ( $deleted ) );
						$log_uploader [$user ['username']] ["deleted"] = $res ["deleted_last"];
					}
				}
				$time = strtotime ( date ( "Y-m-1 0:0:0", strtotime ( "next month " ) ) ) - strtotime ( "now" );
				$log_uploader ['time'] ['at'] = strtotime ( "now" );
				$log_uploader ['time'] ['until'] = strtotime ( date ( "Y-m-1 0:0:0", strtotime ( "next month " ) ) );
				$Cache->cache_value ( 'log_uploader', $log_uploader, $time );
			}
	foreach ( $usernames as $user ) {
				if ($log_uploader [$user ['username']] ["thisnum"] == "请假") 
					$salary = 2000;
				else {
				$deleted=mysql_fetch_array (sql_query("select deleted_torrents from uploaders where uid = ".$user ['id'].""));
				$salary = salary ( $log_uploader [$user ['username']] ["thisnum"]+$deleted['deleted_torrents'], $log_uploader [$user ['username']] ["thissize"] / (1024 * 1024 * 1024), 20, 30 );
				}
				$bonuscomment = $user ['bonuscomment'];
				$bonuscomment = date ( "Y-m-d" ) . " 工资收入" . $salary . "个魔力值。\n" . htmlspecialchars ( $bonuscomment );
				
				sql_query ( "UPDATE users SET seedbonus = seedbonus + $salary, bonuscomment = " . sqlesc ( $bonuscomment ) . " WHERE id = $user[id]" ) or sqlerr ( __FILE__, __LINE__ );
				sql_query ( "UPDATE uploaders SET deleted_last = ".$deleted['deleted_torrents'].", deleted_torrents = 0  WHERE id = $user[id]" ) or sqlerr ( __FILE__, __LINE__ );
				$subject = "发工资啦~";
				$msg = "亲爱的 " . $user ["username"] . " ，上月的工资 [b][color=Blue]" . $salary . "[/color][/b] 个魔力值已发放，请笑纳~";
				$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
				sql_query ( "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $user[id], $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $msg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
				sql_query ( "INSERT INTO uploader_autosalary (user_id, user_class, salary, salarytime) VALUES($user[id], $user[class], " . sqlesc ( $salary ) . ", " . $dt . ")" ) or sqlerr ( __FILE__, __LINE__ );
				$Cache->delete_value ( 'user_' . $user ['id'] . '_unread_message_count' );
				$Cache->delete_value ( 'user_' . $user ['id'] . '_inbox_count' );
				
				$staffmsg = $staffmsg . $user ["username"] . " : 魔力值[color=Blue]" . $salary . "[/color]个" . "\n";
				sql_query ( "UPDATE uploaders SET deleted_last = ".$deleted['deleted_torrents'].", deleted_torrents = 0 WHERE uid = $user[id]" ) or sqlerr ( __FILE__, __LINE__ );
			}
		$subject = "发布员发工资啦~";
		$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
		sql_query ( "INSERT INTO staffmessages (sender, added, subject, msg) VALUES(9999, $dt, " . sqlesc ( $subject ) . ", " . sqlesc ( $staffmsg ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		$Cache->delete_value ( 'staff_message_count' );
		$Cache->delete_value ( 'staff_new_message_count' );
		
		if ($printProgress) {
			printProgress ( "发放发布员工资" );
		}
	}
	// Priority Class 5: cleanup every 15 days
	$res = sql_query ( "SELECT value_u FROM avps WHERE arg = 'lastcleantime5'" );
	$row = mysql_fetch_array ( $res );
	if (! $row) {
		sql_query ( "INSERT INTO avps (arg, value_u) VALUES ('lastcleantime5',$now)" ) or sqlerr ( __FILE__, __LINE__ );
		return;
	}
	$ts = $row [0];
	if ($ts + $autoclean_interval_five > $now && ! $forceAll) {
		return '四级清理完成';
	} else {
		sql_query ( "UPDATE avps SET value_u = " . sqlesc ( $now ) . " WHERE arg='lastcleantime5'" ) or sqlerr ( __FILE__, __LINE__ );
	}
	
	// update clients' popularity
	$res = sql_query ( "SELECT id FROM agent_allowed_family" );
	while ( $row = mysql_fetch_array ( $res ) ) {
		$count = get_row_count ( "users", "WHERE clientselect=" . sqlesc ( $row ['id'] ) );
		sql_query ( "UPDATE agent_allowed_family SET hits=" . sqlesc ( $count ) . " WHERE id=" . sqlesc ( $row ['id'] ) );
	}
	if ($printProgress) {
		printProgress ( "更新客户端使用数目统计" );
	}
	
	// delete old messages sent by system
	$length = 180 * 86400; // half a year
	$until = date ( "Y-m-d H:i:s", (TIMENOW - $length) );
	sql_query ( "DELETE FROM messages WHERE sender = 0 AND added < " . sqlesc ( $until ) );
	if ($printProgress) {
		printProgress ( "删除过旧的系统消息" );
	}
	/*
	 * //delete old readpost records $length = 180*86400; //half a year $until =
	 * date("Y-m-d H:i:s",(TIMENOW - $length)); $postIdHalfYearAgo =
	 * get_single_value('posts', 'id', 'WHERE added < ' . sqlesc($until).' ORDER
	 * BY added DESC'); if ($postIdHalfYearAgo) { sql_query("UPDATE users SET
	 * last_catchup = ".sqlesc($postIdHalfYearAgo)." WHERE last_catchup <
	 * ".sqlesc($postIdHalfYearAgo)); sql_query("DELETE FROM readposts WHERE
	 * lastpostread < ".sqlesc($postIdHalfYearAgo)); } if ($printProgress) {
	 * printProgress("delete old readpost records"); }
	 */
	// delete old ip log
	$length = 365 * 86400; // a year
	$until = date ( "Y-m-d H:i:s", (TIMENOW - $length) );
	sql_query ( "DELETE FROM iplog WHERE access < " . sqlesc ( $until ) );
	if ($printProgress) {
		printProgress ( "删除过旧的IP日志" );
	}
	
	// delete old general log
	$secs = 365 * 86400; // a year
	$until = date ( "Y-m-d H:i:s", (TIMENOW - $length) );
	sql_query ( "DELETE FROM sitelog WHERE added < " . sqlesc ( $until ) ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( "删除过旧的系统日志" );
	}
	
	// 1.delete torrents that doesn't exist any more
	do {
		$res = sql_query ( "SELECT id FROM torrents" ) or sqlerr ( __FILE__, __LINE__ );
		$ar = array ();
		while ( $row = mysql_fetch_array ( $res ) ) {
			$id = $row [0];
			$ar [$id] = 1;
		}
		
		if (! count ( $ar ))
			break;
		
		$dp = @opendir ( $torrent_dir );
		if (! $dp)
			break;
		
		$ar2 = array ();
		while ( ($file = readdir ( $dp )) !== false ) {
			if (! preg_match ( '/^(\d+)\.torrent$/', $file, $m ))
				continue;
			$id = $m [1];
			$ar2 [$id] = 1;
			if (isset ( $ar [$id] ) && $ar [$id])
				continue;
			$ff = $torrent_dir . "/$file";
			unlink ( $ff );
		}
		closedir ( $dp );
		
		if (! count ( $ar2 ))
			break;
		
		$delids = array ();
		foreach ( array_keys ( $ar ) as $k ) {
			if (isset ( $ar2 [$k] ) && $ar2 [$k])
				continue;
			$delids [] = $k;
			unset ( $ar [$k] );
		}
		if (count ( $delids ))
			sql_query ( "DELETE FROM torrents WHERE id IN (" . join ( ",", $delids ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		
		$res = sql_query ( "SELECT torrent FROM peers GROUP BY torrent" ) or sqlerr ( __FILE__, __LINE__ );
		$delids = array ();
		while ( $row = mysql_fetch_array ( $res ) ) {
			$id = $row [0];
			if (isset ( $ar [$id] ) && $ar [$id])
				continue;
			$delids [] = $id;
		}
		if (count ( $delids ))
			sql_query ( "DELETE FROM peers WHERE torrent IN (" . join ( ",", $delids ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
		
		$res = sql_query ( "SELECT torrent FROM files GROUP BY torrent" ) or sqlerr ( __FILE__, __LINE__ );
		$delids = array ();
		while ( $row = mysql_fetch_array ( $res ) ) {
			$id = $row [0];
			if ($ar [$id])
				continue;
			$delids [] = $id;
		}
		if (count ( $delids ))
			sql_query ( "DELETE FROM files WHERE torrent IN (" . join ( ",", $delids ) . ")" ) or sqlerr ( __FILE__, __LINE__ );
	} while ( 0 );
	if ($printProgress) {
		printProgress ( "删除种子文件不存在的资源" );
	}
	
	// 8.lock topics where last post was made more than x days ago
	$secs = 60 * 24 * 60 * 60;
	sql_query ( "UPDATE topics, posts SET topics.locked='yes' WHERE topics.lastpost = posts.id AND topics.sticky = 'no' AND UNIX_TIMESTAMP(posts.added) < " . TIMENOW . " - $secs" ) or sqlerr ( __FILE__, __LINE__ );
	
	if ($printProgress) {
		printProgress ( "锁住超过N天没有回复的论坛主题" );
	}
	// 将N天内无人回复的主题标记为已读
	$secs = 90 * 24 * 60 * 60;
	$res = sql_query ( "SELECT id FROM posts WHERE UNIX_TIMESTAMP(added) < " . TIMENOW . " - $secs ORDER BY added DESC LIMIT 1" ) or sqlerr ( __FILE__, __LINE__ );
	while ( $arr = mysql_fetch_array ( $res ) ) {
		sql_query ( "DELETE FROM readposts WHERE lastpostread <= " . $arr ["id"] );
		sql_query ( "UPDATE users SET last_catchup=" . $arr ["id"] . " WHERE last_catchup < " . $arr ["id"] );
	}
	
	if ($printProgress) {
		printProgress ( "将N天内无人回复的主题标记为已读" );
	}
	
	// 9.delete report items older than four week
	$secs = 4 * 7 * 24 * 60 * 60;
	$dt = sqlesc ( date ( "Y-m-d H:i:s", (TIMENOW - $secs) ) );
	sql_query ( "DELETE FROM reports WHERE dealtwith=1 AND added < $dt" ) or sqlerr ( __FILE__, __LINE__ );
	if ($printProgress) {
		printProgress ( "删除超过N周的已处理举报" );
	}
	
	return '全部清理完成，点击<a href="index.php">这里</a>返回首页';
}
?>
