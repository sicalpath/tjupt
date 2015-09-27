<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if (!$questions = $Cache->get_value('questions'))//查询题库容量
{
	$questions=mysql_num_rows(sql_query("SELECT * FROM questions"));
	$Cache->cache_value('questions', $questions, 3600);
}

if($_POST["change"])
{
if(strpos($CURUSER['notifs'], "[changequestion]") !== false)
	$notice="<h1>您已更换过新题了，不能再次更换！</h1><br/>";
	else{
	sql_query("UPDATE users SET notifs='".$CURUSER["notifs"]."[changequestion]', seedbonus=seedbonus - 100 WHERE id = ".$CURUSER["id"]);
	header("refresh:0;url=".str_replace("/","",$_SERVER['PHP_SELF'])."?change=true");
	}
}

$res=mysql_fetch_assoc(sql_query("SELECT answer FROM users WHERE id = '".$CURUSER["id"]."'"));//查询答题次数
$already=$res["answer"];
$number=hexdec(substr(md5($CURUSER["username"].$CURUSER["id"].$res["answer"].((strpos($CURUSER['notifs'], "[changequestion]") !== false)?"change":"")),0,10))%$questions; //随机抽题，允许换一次题目
if($number<0)$number+=$questions;
$res=mysql_fetch_assoc(sql_query("SELECT * FROM questions WHERE 1 LIMIT ".$number.",1"));//根据随机数取出题目
if($already<10) $notice.="<h3>您已回答了".$already."题，还有".(10-$already)."次答题机会！</h3><br/>";

if($ans=$_POST["choice"]&&!$_POST["change"])
{
$answer=0;
if(is_array($_POST["choice"]))foreach($_POST["choice"] as $ans)$answer+=$ans;
else $answer=0+$_POST["choice"];

	if($answer==$res["answer"] && $_POST["id"]==$res["id"])
	{
			sql_query("UPDATE users SET notifs=replace(notifs,'[changequestion]','') , seedbonus=seedbonus + 200 ,answer=answer+1 WHERE id = ".$CURUSER["id"]);
			stderr("回答正确","恭喜您，回答正确。您已经获得了我们送出的200个魔力值，请注意查收！<a href=\"".str_replace("/","",$_SERVER['PHP_SELF'])."\" target=_self>点这里刷新</a>",0);

	}
	elseif($_POST["id"]!=$res["id"]){
		stderr("数据错误","请不要尝试自行更换题目！<a href=\"".str_replace("/","",$_SERVER['PHP_SELF'])."\" target=_self>点这里刷新</a>
",0);
	}
	else{
		sql_query("UPDATE users SET seedbonus=seedbonus-50 WHERE id = ".$CURUSER["id"]);
		stderr("回答错误","很抱歉，您的回答不正确。您被扣除了50个魔力值！<a href=\"".str_replace("/","",$_SERVER['PHP_SELF'])."\" target=_self>点这里刷新</a>
",0);

	}
}
if($already>=10)stderr("恭喜您！","您已经完成了所有新手任务");
stdhead("会员考核");
$type=(str_replace("0","",decbin($res["answer"]))=="1")?"radio":"checkbox";//确定题目类型
for ($i=1; $i<=8; $i*=2)
if($res["answer".$i]!="")$choices[]="<tr><td class=\"text\"  align=\"left\" width=\"100%\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"".$type."\" name=\"choice".(($type=="checkbox")?"[]":"")."\" value=\"".$i."\" >".$res["answer".$i]."</td></tr>\n";//将选项存入数组
shuffle($choices); //乱序排列
if($notice)print($notice);

?><form action="<?php echo str_replace("/","",$_SERVER['PHP_SELF']); ?>" method="post">
<table width="60%" border="1" cellspacing="0" cellpadding="10">
<tr><td class="text"  align="left" width="100%">请问：<? echo $res["question"]; ?></td></tr>
<input type="hidden" name="id" value="<? echo $res["id"];?>" />
<? echo $choices[0];?>
<? echo $choices[1];?>
<? echo $choices[2];?>
<? echo $choices[3];?>
<tr><td class="text"  align="center" width="100%"><input type="submit" name="submit" value="提交" /> <input type="submit" name="change" value="换题" /></td></tr>
<tr><td class="text"  align="center" width="100%"><font color="#FF0000"><b>温馨提醒：换题需要花费100个魔力值，且每道题只能更换一次！</b></font></td></tr>
</table></form>
<? stdfoot();

