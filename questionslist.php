<?php
require_once('include/bittorrent.php');
dbconn();
loggedinorreturn();
if (get_user_class() < UC_MODERATOR)
    permissiondenied();
if($_POST["question"])
{
if(mysql_num_rows(sql_query("SELECT * FROM questions WHERE question = '".mysql_real_escape_string($_POST["question"])."'"))){print("<h1>该题已存在</h1>");
die;}
sql_query("INSERT questions ( question,answer1,answer2,answer4,answer8,answer ) values ( '".mysql_real_escape_string($_POST["question"])."','".mysql_real_escape_string($_POST["question1"])."','".mysql_real_escape_string($_POST["question2"])."','".mysql_real_escape_string($_POST["question4"])."','".mysql_real_escape_string($_POST["question8"])."',".join("+",$_POST["answer"])." )");
}
stdhead("题库");
$res=sql_query("SELECT * FROM questions");

?>
<table class="main" width="940" border="0" cellspacing="0" cellpadding="0"><tr>
<td class="colhead" width="2%">题号</td>
<td class="colhead" align="left" width="60%"> 题目 </td>
<td class="colhead" align="left" width="10%"> A </td>
<td class="colhead" align="left" width="10%"> B </td>
<td class="colhead" align="left" width="10%"> C </td>
<td class="colhead" align="left" width="10%"> D </td>
</tr>
<?
$res=sql_query("SELECT * FROM questions");
while($arr=mysql_fetch_assoc($res))
{
print("<tr>
<td class=\"rowfollow\">".$arr[id]."</td>
<td class=\"rowfollow\" align=\"left\">". $arr["question"] ."</td>
<td class=\"rowfollow\" align=\"left\">". $arr["answer1"] ."</td>
<td class=\"rowfollow\" align=\"left\">". $arr["answer2"] ."</td>
<td class=\"rowfollow\" align=\"left\">". $arr["answer4"] ."</td>
<td class=\"rowfollow\" align=\"left\">". $arr["answer8"] ."</td>
</tr>");
}
print("</table>\n<br/><br/><h1>录入新题目</h1>");
?>
<table width="80%" border="1" cellspacing="0" cellpadding="10">
<tr><form action="<? echo str_replace("/","",$_SERVER['PHP_SELF']); ?>" method="post"><td class="text"  align="right" width="7%">题目</td><td class="text"  align="left" width="93%"><input type="text" name="question" size="120"></td></tr>
<td class="text"  align="right">A</td><td class="text"  align="left"><input type="text" name="question1"><input type="checkbox" name="answer[]" value="1"></td></tr>
<td class="text"  align="right">B</td><td class="text"  align="left"><input type="text" name="question2"><input type="checkbox" name="answer[]" value="2"></td></tr>
<td class="text"  align="right">C</td><td class="text"  align="left"><input type="text" name="question4"><input type="checkbox" name="answer[]" value="4"></td></tr>
<td class="text"  align="right">D</td><td class="text"  align="left"><input type="text" name="question8"><input type="checkbox" name="answer[]" value="8"></td></tr></table><table width="80%" border="1" cellspacing="0" cellpadding="10">
<tr align="center"><td>
<input type="submit" name="submit" value="确定" /></td></tr></form>

</table>

<?php
stdfoot();



