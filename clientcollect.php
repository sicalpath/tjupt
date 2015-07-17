<?php
require_once ('include/bittorrent_announce.php');
require_once ('include/benc.php');

dbconn_announce ();
$agent = $_SERVER ["HTTP_USER_AGENT"];
// block_browser();
foreach ( array (
		"info_hash",
		"peer_id",
		"event" 
) as $x ) {
	if (isset ( $_GET ["$x"] ))
		$GLOBALS [$x] = $_GET [$x];
}
// get integer type port, downloaded, uploaded, left from client
foreach ( array (
		"port",
		"compact",
		"no_peer_id" 
) as $x ) {
	$GLOBALS [$x] = 0 + $_GET [$x];
}
// check info_hash, peer_id and passkey
foreach ( array (
		"passkey",
		"info_hash",
		"peer_id",
		"port" 
) as $x )
	if (! isset ( $x ))
		err ( "丢失数据: $x" );
foreach ( array (
		"info_hash",
		"peer_id" 
) as $x )
	if (strlen ( $GLOBALS [$x] ) != 20)
		err ( "非法的 $x (" . strlen ( $GLOBALS [$x] ) . " - " . rawurlencode ( $GLOBALS [$x] ) . ")" );
	// 4. GET IP AND CHECK PORT
$ip = getip (); // avoid to get the spoof ip from some agent
if (! $port || $port > 0xffff)
	err ( "端口号错误" );
	// 3. CHECK IF CLIENT IS ALLOWED
$clicheck_res = check_client ( $peer_id, $agent, &$client_familyid );
if (! $clicheck_res) {
	err ( "你所使用的客户端是允许使用的客户端" );
}

$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );

if ($event == "started") {
	$msg = "IP 为" . $ip . "的用户提交未知客户端。信息如下:
agent: " . $agent . "
client_familyid: " . $client_familyid . "
peer_id: " . $peer_id . "";
	$subject = "提交客户端";
	$msg = sqlesc ( $msg );
	$subject = sqlesc ( $subject );
	
	if ($ip != $Cache->get_value ( 'clientcollect_' . md5 ( $ip ) . '_msg' )) {
		sql_query ( "INSERT INTO staffmessages (sender, added, msg, subject) VALUES('0', $dt, $msg, $subject)" ) or err ( SL1 );
		
		$Cache->delete_value ( 'staff_message_count' );
		$Cache->delete_value ( 'staff_new_message_count' );
		$Cache->cache_value ( 'clientcollect_' . md5 ( $ip ) . '_msg', $ip, 86400 );
	} else {
		err ( "您已经提交过该客户端，管理组将根据分析情况决定是否对该客户端添加支持。" );
	}
}

$resp = "d" . benc_str ( "interval" ) . "i86400e" . benc_str ( "min interval" ) . "i86400e" . benc_str ( "complete" ) . "i0e" . benc_str ( "incomplete" ) . "i0e" . benc_str ( "peers" ) . "lee";
benc_resp_raw ( $resp );
/* 与下载客户端交互 */

/*与下载客户端交互*/
?>
