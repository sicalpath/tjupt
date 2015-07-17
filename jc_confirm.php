<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
updatestate();
global $CURUSER;
$bettopper = 10000;

if(!isset($_GET['subid']))
{
		stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['not_exist'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_currentbet_L.php\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back1'],false);
}
$sub_id=$_GET['subid'];
sub_is_unexist($sub_id);



//check the subid whether exists
$subject=fetch_from_jc_subjects($sub_id);
$endtime=$subject['end'];
$limit=$subject['limit'];

$mydate = getdate();
$m=$_POST['molizhi'];
$o=$_POST['touzhu'];
$t="$mydate[year]-$mydate[mon]-$mydate[mday] $mydate[hours]:$mydate[minutes]:$mydate[seconds]";
$res=sql_query("SELECT * FROM jc_subjects WHERE id=".sqlesc($sub_id));
$arr=mysql_fetch_array($res);
if($arr[state]!=2)
{
		stderr($lang_jc_comfirm['wrong_head'],$lang_jc_confirm['finished'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_currentbet_L.php\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back1'],false);
}


if(isset($m))
{
		if(!is_numeric($m))
		{
				stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['enter_the_number'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_details.php?subid=".$sub_id."\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back'],false);
		}
		if($m<=$CURUSER['seedbonus'])
		{
				global $bettopper;
				$sqlt=sql_query("SELECT user_total FROM jc_record WHERE user_id=".sqlesc($CURUSER['id'])." AND subject_id=".sqlesc($sub_id) ) ;    
				$rowt=mysql_num_rows($sqlt);
				if($rowt>0)
				{   
						$recordt=mysql_fetch_array($sqlt);
						$tot=$recordt['user_total'];
				}   
				else   {   
						$tot=0;
				}   



				if($m>=$limit && (($m+$tot)<=$bettopper ) && $m>0)
				{	
						stdhead("确认下注");
						if(!is_selected($sub_id))
						{
								sql_query("INSERT INTO jc_record (user_id, subject_id, user_total, option_id, last_time,yin_kui) VALUES (".sqlesc($CURUSER['id']).",".sqlesc($sub_id).",".sqlesc($m).",".sqlesc($o).",".sqlesc($t).",-".sqlesc($m).")") ;
								sql_query("UPDATE jc_subjects SET players=players+1 WHERE id=".sqlesc($sub_id)."");
								stdmsg($lang_jc_confirm['success_head'],$lang_jc_confirm['success_content'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_details.php?subid=".$sub_id."\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back'],false);
						}
						else
						{
								//合成一条
								$ret=sql_query("UPDATE jc_record SET user_total = user_total +".sqlesc($m).", last_time=".sqlesc($t).",yin_kui=yin_kui-$m WHERE user_id=".sqlesc($CURUSER['id'])." AND subject_id=".sqlesc($sub_id)."") or sqlerr(__FILE__, __LINE__);
								//$ret=sql_query("UPDATE jc_record SET last_time=".sqlesc($t)." WHERE user_id=".sqlesc($CURUSER['id'])." AND subject_id=".sqlesc($sub_id)."") or sqlerr(__FILE__, __LINE__);
								//$ret=sql_query("UPDATE jc_record SET yin_kui = yin_kui-$m WHERE user_id=".sqlesc($CURUSER['id'])." AND subject_id=".sqlesc($sub_id)." ") or sqlerr(__FILE__, __LINE__);
								//show jiazhu chenggong!
								stdmsg($lang_jc_confirm['success_head'],$lang_jc_confirm['success_content2'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_details.php?subid=".$sub_id."\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back'],false);
						}
						$ret=sql_query("UPDATE jc_subjects SET total = total + ".sqlesc($m)." WHERE id=".sqlesc($sub_id)."") or sqlerr(__FILE__, __LINE__);
						$ret=sql_query("UPDATE jc_options SET option_total = option_total + ".sqlesc($m)." WHERE option_id=".sqlesc($o)." AND parent_id=".sqlesc($sub_id)."") or sqlerr(__FILE__, __LINE__);
						$ret=sql_query("UPDATE users SET seedbonus=seedbonus-".sqlesc($m)." WHERE id=".sqlesc($CURUSER['id']."")) or sqlerr(__FILE__,__LINE__);

						stdfoot();

				}
				elseif($m>0 && $m<$limit)
				{
						//please tou zhu above the limit!
						stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['lower_than_limit'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_details.php?subid=".$sub_id."\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back'],false);
				}
				else if(($m+$tot)>$bettopper)
				{
						stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['higher_than_toper'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_details.php?subid=".$sub_id."\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back'],false);
				}
				else
				{
						stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['below_the_zero'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_details.php?subid=".$sub_id."\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back'],false);
				}
		}
		else
		{
				stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['no_fund'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_details.php?subid=".$sub_id."\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back'],false);
		}

}
else
{
		//please enter tou zhu e!;
		stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['zero_tou_zhu'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_details.php?subid=".$sub_id."\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back'],false);
}


function sub_is_unexist($sub_id)
{
		global $lang_jc_confirm;
		if(is_numeric($sub_id))
		{
				$ret=sql_query("SELECT * FROM jc_subjects WHERE id=".sqlesc($sub_id));
				$row=mysql_num_rows($ret);
				if($row == 0)
						stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['not_exist'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_currentbet_L.php\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back1'],false);
		}
		else
				stderr($lang_jc_confirm['wrong_head'],$lang_jc_confirm['not_exist'].$lang_jc_confirm['click']."<a class=altlink href=\"jc_currentbet_L.php\">".$lang_jc_confirm['here']."</a>".$lang_jc_confirm['back1'],false);
}


function fetch_from_jc_subjects($sub_id)
{
		$subject=mysql_fetch_array(sql_query("SELECT * FROM jc_subjects WHERE id=".sqlesc($sub_id).""));
		return $subject;
}


//judge whether the current user has done the selection
function is_selected($sub_id)
{
		global $CURUSER;
		$sql="SELECT  * FROM jc_record WHERE user_id={$CURUSER['id']} AND subject_id=".sqlesc($sub_id);
		$ret=sql_query("$sql");
		$row=mysql_num_rows($ret);
		if($row == 0) return false;
		else return true;
}
?>

