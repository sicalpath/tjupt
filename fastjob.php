<?php

require_once ("include/bittorrent.php");

dbconn ();

require_once (get_langfile_path ( "fastdelete.php", true ));

require_once (get_langfile_path ( "delete.php", true ));

loggedinorreturn ();
function bark($msg) {
	global $lang_fastdelete;
	
	stdhead ();
	
	stdmsg ( $lang_fastdelete ['std_delete_failed'], $msg );
	
	stdfoot ();
	
	exit ();
}

$checked_torrent = $_POST ['checked_torrent'];

foreach ( $checked_torrent as $val ) 

{
	
	if (! is_numeric ( $val )) 

	{
		
		bark ( '无效输入' );
	}
}

$res = sql_query ( "SELECT id,name,owner,seeders,anonymous,pulling_out FROM torrents WHERE id IN (" . implode ( $checked_torrent, "," ) . ")" );

while ( $row = mysql_fetch_array ( $res ) ) 

{
	
	$id = $row ['id'];
	
	if ($_POST ['job'] != '恢复') 

	{
		
		if (! $row ['pulling_out']) 

		{
			
			$rt = 0 + $_POST ["reasontype"];
			
			if (! is_int ( $rt ) || $rt < 1 || $rt > 6)
				
				bark ( '原因无效' . "$rt." );
			
			$reason = $_POST ["reason"];
			
			if ($rt == 1)
				
				$reasonstr = "断种";
			
			elseif ($rt == 2)
				
				$reasonstr = "重复" . ($reason [0] ? (" - " . trim ( $reason [0] )) : "");
			
			elseif ($rt == 3)
				
				$reasonstr = "劣质" . ($reason [1] ? (" - " . trim ( $reason [1] )) : "");
			
			elseif ($rt == 4) 

			{
				
				if (! $reason [2])
					
					bark ( '请填写违规理由' );
				
				$reasonstr = " 违规 - " . trim ( $reason [2] );
			} 
			
			elseif ($rt == 5)
						
			{$reasonstr = "合集已出，删除单集和小合集，感谢您对北洋园PT的贡献";
				
			sql_query("UPDATE uploaders SET deleted_torrents = deleted_torrents + 1 WHERE uid = ".$row["owner"] );}

			else 

			{
				
				if (! $reason [3])
					
					bark ( '请输入理由' );
				
				$reasonstr = trim ( $reason [3] );
			}
			
			if ($row ['anonymous'] == 'yes' && $CURUSER ["id"] == $row ["owner"]) {
				
				write_log ( "匿名发布者删除了资源 $id ($row[name])。理由： $reasonstr", 'normal' );
			} else {
				
				write_log ( ($CURUSER ["id"] == $row ["owner"] ? "发布者" : "管理员") . " $CURUSER[username] 删除了资源 $id ($row[name]) 。理由： $reasonstr", 'normal' );
			}
			
			if ($CURUSER ["id"] != $row ["owner"]) {
				
				$dt = sqlesc ( date ( "Y-m-d H:i:s" ) );
				
				$subject = sqlesc ( $lang_fastdelete_target [get_user_lang ( $row ["owner"] )] ['msg_torrent_deleted'] );
				
				$msg = sqlesc ( $lang_fastdelete_target [get_user_lang ( $row ["owner"] )] ['msg_the_torrent_you_uploaded'] . $row ['name'] . $lang_fastdelete_target [get_user_lang ( $row ["owner"] )] ['msg_was_deleted_by'] . "[url=userdetails.php?id=" . $CURUSER ['id'] . "]" . $CURUSER ['username'] . "[/url]" . $lang_delete_target [get_user_lang ( $row ["owner"] )] ['msg_reason_is'] . $reasonstr );
				
				sql_query ( "INSERT INTO messages (sender, receiver, subject, added, msg) VALUES(0, $row[owner], $subject, $dt, $msg)" ) or sqlerr ( __FILE__, __LINE__ );
			}
			
			deletetorrent($id,$reasonstr);
		} 

		else if (get_user_class () >= UC_ADMINISTRATOR) 

		{
			
			deletetorrent($id,$reasonstr);
			write_log ( "管理员" . " $CURUSER[username] 删除了资源 $id ($row[name]) (回收站)", 'normal' );
		}
	} 

	else 

	{
		
		if (get_user_class () >= UC_ADMINISTRATOR && $row ['pulling_out']) 

		{
			
			sql_query ( "UPDATE torrents SET pulling_out = '0' WHERE id = '$id'" );
			
			write_log ( ($CURUSER ["id"] == $row ["owner"] ? "发布者" : "管理员") . " $CURUSER[username] 恢复了资源 $id ($row[name]) ", 'normal' );
		}
	}
}

stdhead ();

stdmsg ( '操作成功', '成功操作 ' . count ( $checked_torrent ) . ' 个种子' );

stdfoot ();

?>
