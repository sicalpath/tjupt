<?php
require "include/bittorrent.php";
dbconn ();
// Send some headers to keep the user's browser from caching the response.
header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . "GMT" );
header ( "Cache-Control: no-cache, must-revalidate" );
header ( "Pragma: no-cache" );
header ( "Content-Type: text/xml; charset=utf-8" );

$tjupt_famousname = array (
		"北洋媛" => $CURUSER ["username"],
		"游客" => "游客",
		"4舰" => "4thfleet",
		"螃蟹姐" => "crazicrab",
		"k学姐" => "kaiser",
		"学姐" => "kaiser",
		"卡卡龙" => "kakalong2010",
		"2k" => "lied2k",
		"李姐" => "小五",
		"烧鸡" => "sking"
);

if (isset ( $_POST ['str'] ) && $_POST ['str'] != '') {
	$searchstr = unesc ( trim ( $_POST ['str'] ) );
	
	global $Cache;
	$result = $Cache->get_value ( 'suggest_at_users_' . $searchstr );
	if (! $result) {
		
		$suggest_query = sql_query ( "SELECT username, title, class FROM users WHERE username LIKE " . sqlesc ( $searchstr . "%" ) . " or title LIKE " . sqlesc ( $searchstr . "%" ) . " ORDER BY username LIMIT 5" );
		unset ( $result );
		
		while ( $suggest = mysql_fetch_array ( $suggest_query ) ) {
			if ($suggest [title] != "") {
				$result [] = array (
						'username' => $suggest [username],
						'usertitle' => $suggest [title] 
				);
			} else {
				switch ($suggest ['class']) {
					case UC_PEASANT :
						{
							$class_name = 'Peasant';
							break;
						}
					case UC_USER :
						{
							$class_name = 'User';
							break;
						}
					case UC_POWER_USER :
						{
							$class_name = 'Power User';
							break;
						}
					case UC_ELITE_USER :
						{
							$class_name = 'Elite User';
							break;
						}
					case UC_CRAZY_USER :
						{
							$class_name = 'Crazy User';
							break;
						}
					case UC_INSANE_USER :
						{
							$class_name = 'Insane User';
							break;
						}
					case UC_VETERAN_USER :
						{
							$class_name = 'Veteran User';
							break;
						}
					case UC_EXTREME_USER :
						{
							$class_name = 'Extreme User';
							break;
						}
					case UC_ULTIMATE_USER :
						{
							$class_name = 'Ultimate User';
							break;
						}
					case UC_NEXUS_MASTER :
						{
							$class_name = 'Nexus Master';
							break;
						}
					case UC_VIP :
						{
							$class_name = '贵宾';
							break;
						}
					case UC_UPLOADER :
						{
							$class_name = '发布员';
							break;
						}
					case UC_RETIREE :
						{
							$class_name = '养老族';
							break;
						}
					case UC_FORUM_MODERATOR :
						{
							$class_name = '论坛版主';
							break;
						}
					case UC_MODERATOR :
						{
							$class_name = '类管理员';
							break;
						}
					case UC_ADMINISTRATOR :
						{
							$class_name = '管理员';
							break;
						}
					case UC_SYSOP :
						{
							$class_name = '维护开发员';
							break;
						}
					case UC_STAFFLEADER :
						{
							$class_name = '主管';
							break;
						}
				}
				$result [] = array (
						'username' => $suggest [username],
						'usertitle' => $class_name 
				);
			}
		}
		
		if (! $result || count ( $result ) < 5) {
			if ($tjupt_famousname [$searchstr]) {
				$result [] = array (
						'username' => $tjupt_famousname [$searchstr],
						'usertitle' => $_POST ['str'] . "被推倒了~" 
				);
			}
		}
		
		$Cache->cache_value ( 'suggest_at_users_' . $searchstr, $result, 900 );
	}
}
if (! $result) {
	$result [] = array (
			'username' => $_POST ['str'],
			'usertitle' => "北洋媛找不到你的TA" 
	);
}
echo json_encode ( $result );
?>
