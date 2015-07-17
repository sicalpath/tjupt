<?php
require "include/bittorrent.php";
dbconn ();
require_once (get_langfile_path ( "imdb_top_250.php", "", "" ));
loggedinorreturn ();
parked ();

stdhead ( "IMDb Top 250" );
echo '<center><span style="font-size: 40px"><strong>IMDb Charts: IMDb Top 250 </strong></span></center><br/>';
if (get_user_class () >= UC_MODERATOR) {
	print ("<input type=\"button\" value=\"更新\" onclick=\"window.location='imdb_top_250.php?action=update';\" style=\"color: red; font-weight: bold\"/><input type=\"button\" value=\"编辑\" onclick=\"window.location='imdb_top_250.php?action=edit';\" style=\"color: blue; font-weight: bold\"/>") ;
}

switch ($_GET ["action"]) {
	case "update" :
		if (get_user_class () >= UC_MODERATOR) {
			update_imdb ();
		} else {
			echo '<br/><center><span style="font-size: 30px"><strong>更新这种小事就交给鹳狸猿吧！<br/>
		<a href=imdb_top_250.php>点击这里返回</a></strong></span></center>';
			stdfoot ();
			break;
		}
	
	case "edit" :
		if ($_GET ["rank"] >= 1 && $_GET ["rank"] <= 250) {
			if (get_user_class () >= UC_MODERATOR) {
				begin_main_frame ();
				print ("<form id=edit method=post name=edit action=imdb_top_250.php?action=takeedit&rank=" . $_GET ["rank"] . " >\n
			<input type=hidden name=action  value=takeedit >
			") ;
				$first_row_rank = ($page - 1) * $row_per_page + 1;
				print ("<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td class=\"colhead\">" . $lang_imdb_top_250 ['rank'] . "</td><td class=\"colhead\" align=\"center\" width=\"40%\">" . $lang_imdb_top_250 ['translate_title'] . "</td><td class=\"colhead\" align=\"center\" width=\"60%\">" . $lang_imdb_top_250 ['title'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['imdb_id'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['year'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['rating'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['votes'] . "</td></tr>") ;
				$result = sql_query ( "SELECT * FROM imdb WHERE rank =" . $_GET ["rank"] . " " ) or sqlerr ( __FILE__, __LINE__ );
				while ( $row = mysql_fetch_array ( $result ) ) {
					$row ['imdb_id'] = str_pad ( $row ['imdb_id'], 7, '0', STR_PAD_LEFT );
					$imdb_url = build_imdb_url ( $row ['imdb_id'] );
					print ("<tr><td>" . $row ['rank'] . "</td><td><input name=translate_title size=50 value=\"" . $row ['translate_title'] . "\"></td><td><input name=title size=50 value=\"" . $row ['title'] . "\"></td></a><td><a href=\"" . $imdb_url . "\" target=\"_blank\"><b>" . tt . $row ['imdb_id'] . "</b></td><td align=\"center\">" . $row ['year'] . "</td><td align=\"center\">" . $row ['rating'] . "</td><td align=\"center\">" . $row ['votes'] . "</td></tr>") ;
				}
				print "</table>";
				end_main_frame ();
				print ("<center><input type=\"submit\" value=\"确定\" /><input type=\"reset\" value=\"重置\" /></center>") ;
				stdfoot ();
				break;
			} else {
				echo '<br/><center><span style="font-size: 30px"><strong>你又不是鹳狸猿，来这凑什么热闹。。。<br/>
			<a href=imdb_top_250.php>点击这里返回</a></strong></span></center>';
				stdfoot ();
				break;
			}
		} elseif ($_GET ["rank"]) {
			echo '<br/><center><span style="font-size: 30px"><strong>你在搞什么啊。。。<br/>
		<a href=imdb_top_250.php>点击这里返回</a></strong></span></center>';
			stdfoot ();
			break;
		}
		
		if (get_user_class () >= UC_MODERATOR) {
			begin_main_frame ();
			$first_row_rank = ($page - 1) * $row_per_page + 1;
			print ("<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td class=\"colhead\"></td><td class=\"colhead\">" . $lang_imdb_top_250 ['rank'] . "</td><td class=\"colhead\" align=\"center\" width=\"40%\">" . $lang_imdb_top_250 ['translate_title'] . "</td><td class=\"colhead\" align=\"center\" width=\"60%\">" . $lang_imdb_top_250 ['title'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['imdb_id'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['year'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['rating'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['votes'] . "</td></tr>") ;
			$result = sql_query ( "SELECT * FROM imdb WHERE rank>= $first_row_rank ORDER BY rank " ) or sqlerr ( __FILE__, __LINE__ );
			while ( $row = mysql_fetch_array ( $result ) ) {
				$row ['imdb_id'] = str_pad ( $row ['imdb_id'], 7, '0', STR_PAD_LEFT );
				$imdb_url = build_imdb_url ( $row ['imdb_id'] );
				print ("<tr><td><input type=\"button\" value=\"编辑\" onclick=\"window.location='imdb_top_250.php?action=edit&rank=" . $row ['rank'] . "';\" style=\"color: blue; font-weight: bold\"/></td><td>" . $row ['rank'] . "</td><td><b>" . $row ['translate_title'] . "</b></td><td><b>" . $row ['title'] . "</b></td></a><td><a href=\"" . $imdb_url . "\" target=\"_blank\"><b>" . tt . $row ['imdb_id'] . "</b></td><td align=\"center\">" . $row ['year'] . "</td><td align=\"center\">" . $row ['rating'] . "</td><td align=\"center\">" . $row ['votes'] . "</td></tr>") ;
			}
			
			print "</table>";
			print ("<tr><td class=toolbox align=center colspan=2><input type=\"button\" value=\"返回\" onclick=\"window.location='imdb_top_250.php';\" ></td></tr></table></form><br />\n") ;
			
			end_main_frame ();
			
			stdfoot ();
		} else {
			echo '<br/><center><span style="font-size: 30px"><strong>你不是鹳狸猿吧？北洋媛是不会让你乱来的！<br/>
		<a href=imdb_top_250.php>点击这里返回</a></strong></span></center>';
			stdfoot ();
		}
		break;
	
	case "takeedit" :
		{
			if (get_user_class () >= 13) {
				sql_query ( "UPDATE imdb SET translate_title = " . sqlesc ( $_POST ["translate_title"] ) . " , title = " . sqlesc ( $_POST ["title"] ) . " WHERE rank = " . sqlesc ( $_GET ["rank"] ) . " " ) or sqlerr ( __FILE__, __LINE__ );
				echo '<br/><center><span style="font-size: 30px"><strong>编辑成功！<br/>
			<a href=imdb_top_250.php?action=edit>点击这里返回</a></strong></span></center><br/>';
				stdfoot ();
			} else {
				echo '<br/><center><span style="font-size: 30px"><strong>干嘛呢？谁让你来这里的？<br/>
		<a href=imdb_top_250.php>点击这里返回</a></strong></span></center><br/>';
				stdfoot ();
			}
			break;
		}
	
	default :
		begin_main_frame ();
		
		$first_row_rank = ($page - 1) * $row_per_page + 1;
		print ("<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td class=\"colhead\">" . $lang_imdb_top_250 ['rank'] . "</td><td class=\"colhead\" align=\"center\" width=\"40%\">" . $lang_imdb_top_250 ['translate_title'] . "</td><td class=\"colhead\" align=\"center\" width=\"60%\">" . $lang_imdb_top_250 ['title'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['imdb_id'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['year'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['rating'] . "</td><td class=\"colhead\" align=\"center\">" . $lang_imdb_top_250 ['votes'] . "</td></tr>") ;
		$result = sql_query ( "SELECT * FROM imdb WHERE rank>= $first_row_rank ORDER BY rank " ) or sqlerr ( __FILE__, __LINE__ );
		while ( $row = mysql_fetch_array ( $result ) ) {
			$row ['imdb_id'] = str_pad ( $row ['imdb_id'], 7, '0', STR_PAD_LEFT );
			$imdb_url = build_imdb_url ( $row ['imdb_id'] );
			$row ['search_title'] = preg_replace ( '/[[:punct:]]/', ' ', html_entity_decode ( $row ['title'], ENT_COMPAT, "UTF-8" ) );
			$row ['search_translate_title'] = str_replace ( array (
					"(",
					")",
					"：",
					"·" 
			), " ", $row ['translate_title'] );
			print ("<tr><td>" . $row ['rank'] . "</td><td><a href=\"torrents.php?search=" . $row ['search_translate_title'] . "&notnewword=1 \"target=\"_blank\"><b>" . $row ['translate_title'] . "</b></td><td><a href=\"torrents.php?search=" . $row ['search_title'] . "&notnewword=1 \"target=\"_blank\"><b>" . $row ['title'] . "</b></td></a><td><a href=\"" . $imdb_url . "\" target=\"_blank\"><b>" . tt . $row ['imdb_id'] . "</b></td><td align=\"center\">" . $row ['year'] . "</td><td align=\"center\">" . $row ['rating'] . "</td><td align=\"center\">" . $row ['votes'] . "</td></tr>") ;
		}
		
		print "</table>";
		
		end_main_frame ();
		
		stdfoot ();
}

?>

