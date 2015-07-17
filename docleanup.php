<?php
ob_start();
require_once("include/bittorrent.php");
dbconn();

if (get_user_class() < UC_SYSOP) {
die('forbidden');
}
echo "<html><head><title>清理系统</title></head><body>";
echo "<p>";
echo "清理工作正在进行中...请稍候<br />";
ob_flush();
flush();
if ($_GET['forceall']) {
	$forceall = 1;
} else {
	$forceall = 0;
echo "点击<a href=?forceall=1 >这里</a>做全局清理<br />";
}
echo "</p>";
$tstart = getmicrotime();
require_once("include/cleanup.php");
print("<p>".docleanup($forceall, 1)."</p>");
$tend = getmicrotime();
$totaltime = ($tend - $tstart);
printf ("一共耗时:  %f 秒<br />", $totaltime);
echo "清理完成<br />";
echo "</body></html>";
