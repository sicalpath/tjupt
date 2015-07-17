<?php
ob_start();
require_once("include/bittorrent.php");
dbconn();

if (get_user_class() < UC_SYSOP) {
die('forbidden');
}
echo "<html><head><title>清理memcache</title></head><body>";
echo "<p>";
echo "清理工作正在进行中...请稍候<br />";
$tstart = getmicrotime();
$Cache->flush();
$tend = getmicrotime();
$totaltime = ($tend - $tstart);
printf ("一共耗时:  %f 秒<br />", $totaltime);
echo "清理完成<br />";
echo "</body></html>";
