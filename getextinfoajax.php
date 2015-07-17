<?php
require "include/bittorrent.php";
require_once ("imdb/imdb2.class.php");
dbconn();
//Send some headers to keep the user's browser from caching the response.
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");
$imdblink = $_GET['url'];
$mode = $_GET['type'];
$cache_stamp = $_GET['cache'];
$imdb_id = parse_imdb_id($imdblink);
echo getimdb($imdb_id, $cache_stamp, $mode);
?>
