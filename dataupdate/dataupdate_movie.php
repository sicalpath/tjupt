<?php
ob_start();
set_time_limit(0);
header("Content-Type:text/html; charset=utf-8");
header("Connection: close");
ob_end_flush();
flush();

require_once("include/bittorrent.php");
require ("imdb/imdb2.class.php");
dbconn();
if (ob_get_level() == 0) ob_start();

	$query = "SELECT id, url FROM torrents WHERE category=401";
	//$query = "SELECT id, url FROM torrents WHERE id=45755";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	$i = 1;
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset =array();
		$sql = "";
		if($arr["url"]!=0){
			if(strlen($arr["url"]) == 5)
				$url = "00".$arr["url"];
			elseif(strlen($arr["url"]) == 6)
				$url = "0".$arr["url"];
			else
				$url = $arr["url"];
			$movie = new imdb ($url);
			$movie->get_movie();
		
			$query1 = "select specificcat, language from torrentsinfo where torid = ".$arr["id"];
			$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
			while($arr1 = mysql_fetch_assoc($res1)){
			
				if(strpos($arr1["specificcat"],'?')===0 && $movie->get_data('genre') != NULL)
					$updateinfoset[] = "specificcat = '".mysql_escape_string(str_replace(", ","/",$movie->get_data('genre')))."'";
				if(strpos($arr1["language"],'?')===0 && $movie->get_data('language') != NULL)
					$updateinfoset[] = "language = '".mysql_escape_string(str_replace(", ","/",$movie->get_data('language')))."'";
						
				$sql=join(",",$updateinfoset);
				if($sql!="")
					//print($sql);
					sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
					//print($arr1["specificcat"]);				
				

				//print($url."   电影类型：".$movie->genre()."<br/>"); //_r
				//print("电影语言：".$movie->language()."<br/>");

				print($i."<br/>");
				$i++;
			}
		}
		ob_flush();
		flush();
	}

?>