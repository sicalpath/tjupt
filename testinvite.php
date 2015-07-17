<?php
require_once('include/bittorrent.php');
dbconn();

$totalalive = get_row_count("users", "WHERE status!='pending' AND class > 0 AND enabled='yes'");
$totalbonus=get_single_value("users WHERE enabled='yes'","sum(seedbonus) ");
$totalinvites=get_single_value("users WHERE enabled='yes'","sum(invites)");

$invite_bonus1=$oneinvite_bonus*exp(($totalalive+$totalinvites-$maxusers)/8000);
$invite_bonus2=$oneinvite_bonus*exp(($totalalive+$totalinvites/8-$maxusers)/800);
$invite_bonus3=$oneinvite_bonus*exp(($totalalive+$totalinvites-$maxusers)/$maxusers*1.618);
$invite_bonus4=$oneinvite_bonus*exp(($totalalive+$totalinvites/10-$maxusers)/10000);

$invite_bonus5 = $oneinvite_bonus * exp(pow(log($totalbonus / $totalalive * 5), 2) * pow(0.2 * log($totalinvites + 1) + 0.8 * log($totalalive + 1), 2) / pow(log($maxusers - $totalalive + 1895), 4) / (log(log($maxusers + 1)) + pi() / 10));

$invite_bonus6 = $oneinvite_bonus / 100000000 * $totalbonus * exp($totalalive / 20000) * (log($totalinvites + 1) + log($maxusers / ($maxusers - $totalalive))) / 25;

echo "oneinvite_bonus=$oneinvite_bonus";
echo "</p>";
echo "totalalive=$totalalive";
echo "</p>";
echo "totalinvites=$totalinvites";
echo "</p>";
echo "maxusers=$maxusers";
echo "</p>";
echo "totalbonus=$totalbonus";
echo "</p>";
echo "</p>";
echo "</p>";
echo "invite_bonus1=$invite_bonus1";
echo "</p>";
echo "invite_bonus2=$invite_bonus2";
echo "</p>";
echo "invite_bonus3=$invite_bonus3";
echo "</p>";
echo "invite_bonus4=$invite_bonus4";
echo "</p>";
echo "invite_bonus5=$invite_bonus5";
echo "</p>";
echo "invite_bonus6=$invite_bonus6";
echo "</p>";
?>