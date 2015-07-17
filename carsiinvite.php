<?php
require_once("include/bittorrent.php");
require_once(get_langfile_path("", false));
print("<title>TJUPT ".$lang_carsiinvite['title']." - Powered by NexusPHP</title>");
//stderr($lang_carsiinvite['std_error'], $lang_carsiinvite['testing'],false);//测试期
if($_SERVER['REMOTE_ADDR'] !="121.193.130.230"&&$_SERVER['REMOTE_ADDR'] !="2001:da8:a000:650::230"||$_SERVER['HTTP_INSTITUTION']==""||$_SERVER['HTTP_USERNAME']=="")
stderr($lang_carsiinvite['uncarsi'],$lang_carsiinvite['not_from_carsi'],false);
dbconn();
$second=5;//倒计时的秒数
$langid = 0 + $_GET['sitelanguage'];
if ($langid)
{
	$lang_folder = validlang($langid);
	if(get_langfolder_cookie() != $lang_folder)
	{
		set_langfolder_cookie($lang_folder);
		header("Location: " . $_SERVER['PHP_SELF']);
	}
}
failedloginscheck ();
cur_user_check () ;
$username = "'".$_SERVER['HTTP_USERNAME']."'";
$institution = "'".$_SERVER['HTTP_INSTITUTION']."'";
if($institution=="'tju'")//屏蔽本校用户
stderr($lang_carsiinvite['noway'],$lang_carsiinvite['tjuuser'],false);
$try=sql_query("SELECT * FROM carsi_schools WHERE idp = $institution ");
$schools = mysql_fetch_assoc($try);
if(!$schools)
stderr($lang_carsiinvite['noway'],$lang_carsiinvite['unknowschools'],false);
elseif($schools['allow_reg']=='no')
stderr($lang_carsiinvite['noway'],$lang_carsiinvite['yourschool'].$schools['school'].$lang_carsiinvite['notallowed'],false);

$res = sql_query("SELECT * FROM carsi_invite WHERE username = $username and institution = $institution") or sqlerr();
if(mysql_num_rows($res) > 0)
{
$arr = mysql_fetch_assoc($res);
if(mysql_num_rows(sql_query("SELECT * FROM invites WHERE hash = ".sqlesc($arr['invite_code'])))>0)
header("Location: " . get_protocol_prefix() . "sp.tju6.edu.cn/signup.php?type=invite&invitenumber=".$arr['invite_code']);
else
stderr($lang_carsiinvite['registered'],$lang_carsiinvite['already_registered'],false);
die;
}
?>
<?php
if($_POST['register'])
{
$invitecode =md5(mt_rand(1,10000).$_SERVER['REMOTE_ADDR'].TIMENOW.$emailaddress);
sql_query("INSERT INTO carsi_invite (username, institution, invite_code) VALUES ($username, $institution , '".mysql_real_escape_string($invitecode)."')")or sqlerr(__FILE__, __LINE__);
sql_query("INSERT INTO invites (inviter, invitee, hash, time_invited) VALUES ('9', '', '".mysql_real_escape_string($invitecode)."', " . sqlesc(date("Y-m-d H:i:s")) . ")")or sqlerr(__FILE__, __LINE__);
header("Location: " . get_protocol_prefix() . "sp.tju6.edu.cn/signup.php?type=invite&invitenumber=".$invitecode);
}
stdhead();
?>

<?php
stdmsg($lang_carsiinvite['notice'], $lang_carsiinvite['msg']."
<form action=carsiinvite.php method=post>
<input type=hidden name=\"register\" value=true />
<input type=button id=getinvite name=getinvite value=\"".$lang_carsiinvite['submit']."\" onClick=\"submit()\" />
</form>
"
);?>
<?php stdfoot();?>
<script>
num=<?php echo $second;?> ;
function change(){
getinvite.value="<?php echo $lang_carsiinvite['submit'];?>("+num+")";
if(num==0)
{ 
getinvite.disabled=false;
getinvite.value="<?php echo $lang_carsiinvite['submit'];?>";
}
else
{
setTimeout("change()",1000);
getinvite.disabled=true;
}
num--;
}
window.onload=change;
</script>

