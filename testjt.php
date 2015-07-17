<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
parked();
stdhead();
$school1="天津大学";
print $school1;
$school2=school_ip_location("2001:da8:a000:153:5050:30ac:323a:630a",0);
print $school2;
if (sqlesc($school1) == sqlesc($school2))
	echo "<font color='green'>完全一致</font>";
else 
	echo "<font color='red'><b>不一致</b></font>";
if ($school1 == $school2)
		echo "<font color='green'>完全一致</font>";
else 
		echo "<font color='red'><b>不一致</b></font>";
stdfoot();
?>