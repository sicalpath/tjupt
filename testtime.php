<?php
require "include/bittorrent.php";

function mkprettytime2($s) {
	global $lang_functions;
	if ($s < 0)
	$s = 0;
	$t = array();
	foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
		$y = explode(":", $x);
		if ($y[0] > 1) {
			$v = fmod($s, $y[0]);
			$s = floor($s / $y[0]);
			echo($y[0]."***".$v."***".$s."<br/>");
		}
		else
		$v = $s;
		$t[$y[1]] = $v;
	}

	if ($t["day"])
	return $t["day"] . $lang_functions['text_day'] . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
	if ($t["hour"])
	return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
	//    if ($t["min"])
	return sprintf("%d:%02d", $t["min"], $t["sec"]);
	//    return $t["sec"] . " secs";
}

$t = 2147483649;
echo($t."***".(int)($t));
echo('<br />');
echo(mkprettytime($t)."===".mkprettytime2($t));
echo('<br />');
echo(floatval(2147483649));
?>
