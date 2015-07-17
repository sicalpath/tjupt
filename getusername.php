<?php
function getusername($id, $big = false, $link = true, $bold = true, $target = false, $bracket = false, $withtitle = false, $link_ext = "", $underline = false)
{
	static $usernameArray = array();
	global $lang_functions;
	$id = 0+$id;

	if (func_num_args() == 1 && $usernameArray[$id]) {  //One argument=is default display of username. Get it directly from static array if available
		return $usernameArray[$id];
	}
	$arr = get_user_row($id);
	if ($arr){
		if ($big)
		{
			$donorpic = "starbig";
			$leechwarnpic = "leechwarnedbig";
			$warnedpic = "warnedbig";
			$disabledpic = "disabledbig";
			$style = "style='margin-left: 4pt'";
		}
		else
		{
			$donorpic = "star";
			$leechwarnpic = "leechwarned";
			$warnedpic = "warned";
			$disabledpic = "disabled";
			$style = "style='margin-left: 2pt'";
		}
		$pics = $arr["donor"] == "yes" ? "<img class=\"".$donorpic."\" src=\"pic/trans.gif\" alt=\"Donor\" ".$style." />" : "";

		if ($arr["enabled"] == "yes")
			$pics .= ($arr["leechwarn"] == "yes" ? "<img class=\"".$leechwarnpic."\" src=\"pic/trans.gif\" alt=\"Leechwarned\" ".$style." />" : "") . ($arr["warned"] == "yes" ? "<img class=\"".$warnedpic."\" src=\"pic/trans.gif\" alt=\"Warned\" ".$style." />" : "");
		else
			$pics .= "<img class=\"".$disabledpic."\" src=\"pic/trans.gif\" alt=\"Disabled\" ".$style." />\n";

		$username = ($underline == true ? "<u>" . $arr['username'] . "</u>" : $arr['username']);
		$username = ($bold == true ? "<b>" . $username . "</b>" : $username);
		$username = ($link == true ? "<a ". $link_ext . " target=_blank href=\"userdetails.php?id=" . $id . "\"" . ($target == true ? " target=\"_blank\"" : "") . " class='". get_user_class_name($arr['class'],true) . "_Name'>" . $username . "</a>" : $username) . $pics . ($withtitle == true ? " (" . ($arr['title'] == "" ?  get_user_class_name($arr['class'],false,true,true) : "<span class='".get_user_class_name($arr['class'],true) . "_Name'><b>".htmlspecialchars($arr['title'])) . "</b></span>)" : "");

		$username = "<span class=\"nowrap\">" . ( $bracket == true ? "(" . $username . ")" : $username) . "</span>";
	}
	else
	{
		$username = "<i>".$lang_functions['text_orphaned']."</i>";
		$username = "<span class=\"nowrap\">" . ( $bracket == true ? "(" . $username . ")" : $username) . "</span>";
	}
	if (func_num_args() == 1) { //One argument=is default display of username, save it in static array
		$usernameArray[$id] = $username;
	}
	return $username;
}
?>