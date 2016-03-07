<?php 
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
//if (get_user_class() < UC_UPLOADER) stderr("Error", "Permission denied.");


if ($_SERVER["REQUEST_METHOD"] == "POST")
{

$url=urldecode($_POST['body']);
if(preg_match( '/[^\w\s\{\}\.\-\:\;\,\#\%\*\/\"\'\(\)\=]/i',$url))stderr("CSS代码中包含非法字符", preg_replace('[\=|\'|\"|\w|\s|\{|\}|\.|\-|\:|\;|\,|\#|\%|\*|\/|\(|\)]','',$url));






if($_POST['test'])
							$Cache->cache_value('user_'.$CURUSER["id"].'_css', "<style type='text/css'>$url</style>",180 );
elseif($_POST['delete']){
							sql_query('DELETE FROM  usercss WHERE  userid ='.sqlesc($CURUSER["id"]));
							$url='';
							$Cache->delete_value('user_'.$CURUSER["id"].'_css');
							}
elseif($_POST['ok']&&$_POST['body']){
							sql_query("delete FROM usercss WHERE   userid  =".sqlesc($CURUSER["id"]));
							sql_query('INSERT INTO usercss (userid, css ,time) VALUES ( '.sqlesc($CURUSER["id"]).', '.sqlesc($url).', '.sqlesc(TIMENOW).') ON DUPLICATE KEY update css='.sqlesc($url));
							sql_query("delete FROM  usercss WHERE  css =  '' ");
							$Cache->delete_value('user_'.$CURUSER["id"].'_css');
}


}



if($_GET['useridcss'])$res = mysql_fetch_array(sql_query('SELECT css FROM  usercss WHERE  userid  ='.sqlesc(0+$_GET['useridcss']).' LIMIT 1 '));
else if($url)$res[css]=$url; 
else $res = mysql_fetch_array(sql_query('SELECT css FROM  usercss WHERE  userid  ='.sqlesc($CURUSER["id"]).' LIMIT 1 '));
$textarea=$res[css];



$res = sql_query("SELECT userid FROM  usercss ORDER BY time DESC ");
while ($postsx = mysql_fetch_assoc($res))
$useridcssall[] = "<a href='addcss.php?useridcss=".$postsx['userid']."'>".get_username($postsx['userid'],false,false,true,true,false,false, "", false , true,false)."</a>";


stdhead("自定义CSS样式");

 ?>
 <h1>添加个性化CSS</h1>
 <?php
begin_main_frame("",false);
?>
<table width="100%"><tr><td class="text" align="center"> 
<form method="post" action="addcss.php">
<textarea name='body' style="width: 98%;height:300px"><?php echo $textarea ?></textarea><br />
<input type="submit" name="ok" value="修改" /><input type="submit" name="test" value="测试三分钟"><input type="submit" name="delete" value="删除"><br />
<?php /*<input type="text" name="useridcss" /><input type="submit" name="find" value="查看左侧ID用户的CSS样式列表">*/?>

</form>


</td><tr><td class="text" align="left">

1:图片链接尽可能使用相对地址,上传到论坛的图片请使用原图地址(地址末尾没有'thumb'),请不要添加诸如'file:///C:/test.jpg'这样脑残的代码.<br/>
2:所有代码都是基于用户默认风格进行修改.<br/>
3:直接使用其他用户样式表的时候请确保安全性和兼容性.如果代码出现问题,我们不对此负责.<br/>
4:不支持中文字符.<br/>
5:禁止低俗主题.<br/>
6:感谢西南交大蚂蚁PT提供程序代码.<br/>
7:没有了,接下来就看各位的技术了,优秀的作品会挑选出来制作成网站主题,<a href="http://www.w3cschool.cn/index-7.html"><b>CSS参考手册</b></a>.<br/>
<br/></td> </tr><tr><td class="text" align="left">
示例代码(自适应屏幕的固定背景):<br/><br/>
.candoit {background:center;}<br/>
body {background:url('/usercss/body.jpg') fixed top center;background-size:cover;}<br/>
</td> </tr><tr><td class="text" align="left">
示例代码(自定义logo):<br/><br/>
table.headwide {background:url('/usercss/logo.png') no-repeat left center; height:200px}<br/>
table.head {background:url('/usercss/logo.png') no-repeat left center; height:200px}<br/>
.logo{display:none;}<br/>
.slogan{display:none;}<br/>
</td> </tr><tr><td class="text" align="left">自定义CSS的用户: <?php print join(" , ",$useridcssall);?></td></tr></table>
<?php
end_main_frame();
stdfoot();
?>

 
  
 