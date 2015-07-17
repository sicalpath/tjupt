<?php
$zerodayServerUsername="wall";
$zerodayServerPassword="wall";
$seedServer="219.243.47.169";
$seedServerUsername="walle";
$seedServerPassword="nscb?Z229LPePJy";
$ftpscriptpath = "/scripts/";
$tempfile="temp_torrent/script";
$workpath="/app/autoseed/";
$ftptorrentpath="/torrents/";
$torrentpath=$workpath."ftp/torrents/";
$downloadpath=$workpath."downloads/";
$logpath=$workpath."logs/";
$bakfile=$workpath."bak/";
$scriptpath=$workpath."ftp/scripts/";

require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if (get_user_class() < UC_MODERATOR)
stderr("Sorry", "Access denied.");

stdhead("0day发布");
if ($_SERVER["REQUEST_METHOD"] == "POST" && get_user_class() >= UC_MODERATOR)
{
$type = array(
'/apps/' => "catid=408&ename=".$file,
'/mp3/' => "catid=406",
'/pcgame/' => "catid=409&ename=".$file,
'/private/' => "",
'/psp/' => "catid=409&ename=".$file,
'/tv/' => "catid=402&ename=".$file,
'/tv.x264/' => "catid=402&ename=".$file,
'/x264/' => "catid=401&ename=".$file,
'/xvid/' => "catid=401&ename=".$file,
'/mp3/epop/' => "catid=406&ename=".$file,
'/mp3/cpop/'.date("Y-m").'/' => "catid=406&ename=".$file,
'/mp3/cpop/'.date("Y-m",strtotime("-1 month")).'/' => "catid=406&ename=".$file,
'/anime/' => "catid=405&ename=".$file,
'/anime.rmvb/' => "catid=405&ename=".$file,
);

$file = $_POST["filename"];
$dir = $_POST["dir"];
$datedir=$_POST["datedir"];
if($file =="*"||$file ==""||(!isset($type[$dir]))){
stdmsg("警告！", "危险的行为");
stdfoot();
die;
}
$input=$type[$dir];
switch($dir)
{
case "/anime/":
	$datedir=date("m-d",strtotime($datedir))."/";
	break;
case "/anime.rmvb/":
	$datedir=date("m.d",strtotime($datedir))."/";
	break;
case "/mp3/epop/":
	$datedir=date("md",strtotime($datedir))."/";
	break;
default:
	$datedir="";
}
$dir.=$datedir;

if(substr($dir,0,6)=="/anime")$script ="#!/bin/bash
#从0day服务器下载资源
cd ".$downloadpath."
echo \"从0day服务器下载资源\">>\"".$logpath.$file."\"
wget -nH -t0 -T15 -w3 --retry-connrefused --cut-dirs=2 -r -N -l inf -nv -R sfv -a \"".$logpath.$file."\" \"ftp://wall:wall@202.113.13.166:2121".$dir.$file."\" >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
wget -nH -t0 -T15 -w3 --retry-connrefused --cut-dirs=2 -r -N -l inf -nv -R sfv -a \"".$logpath.$file."\" \"ftp://wall:wall@202.113.13.166:2121".$dir.$file."\" >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"

echo \"\" >> \"".$logpath.$file."\"
echo \"发布前的操作：\" >> \"".$logpath.$file."\"
cd ".$workpath."

#删除所有空子目录
echo \"删除所有空子目录:\">>\"".$logpath.$file."\"
find ".$downloadpath.$file." -type d -empty -exec rm -rfv {} \; >>\"".$logpath.$file."\"

echo \"\">>\"".$logpath.$file."\"
echo \"做种文件列表：\" >>\"".$logpath.$file."\"
ls -lR ".$downloadpath.$file." >>\"".$logpath.$file."\"

#制作种子
echo \"\">>\"".$logpath.$file."\"
echo \"制作种子:\">>\"".$logpath.$file."\"
#计算分块大小
i=\$(du -s \"".$downloadpath.$file."\" | cut  -f1)
l=0
while [ \$i -ge 1  ]
do
((i=i/2));((l=l+1));
done
if [ \$l -gt 22 ];then ((l=22));
elif [ \$l -lt 16 ];then ((l=16));
fi
mktorrent -l \$l -a http://pttracker.tju.edu.cn -c \"Wall-E@TJUPT seeding (0day)\" -o ".$workpath.$file.".torrent ".$downloadpath.$file." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"

#根据nfo文件存在与否分类发布
echo \"\">>\"".$logpath.$file."\"
echo \"发布种子:\">>\"".$logpath.$file."\"

if [ -f ".$downloadpath.$file."/*.nfo ];then
nohup curl -o \"".$torrentpath.$file.".torrent\" -F \"file=@".$workpath.$file.".torrent\" -F \"nfo=@`ls ".$downloadpath.$file."/*.nfo`\" -F \"".$input."\" http://202.113.13.170/autoupload0day.php >> \"".$logpath.$file."\" ;
else
nohup curl -o \"".$torrentpath.$file.".torrent\" -F \"file=@".$workpath.$file.".torrent\" -F \"".$input."\" http://202.113.13.170/autoupload0day.php >>\"".$logpath.$file."\" ;
fi

#删除临时文件
echo \"\">>\"".$logpath.$file."\"
echo \"删除制作的种子:\" >>\"".$logpath.$file."\"
rm -rfv ".$workpath.$file.".torrent >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
echo \"删除脚本文件:\" >>\"".$logpath.$file."\"
rm -rfv ".$workpath."running/".$file." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
";
elseif(substr($dir,0,10)=="/mp3/epop/"||substr($dir,0,10)=="/mp3/cpop/")
{

$file2=str_replace("(","\\(",str_replace(")","\\)",$file));
$script ="#!/bin/bash
#从0day服务器下载资源
cd ".$downloadpath."
echo \"从0day服务器下载资源\">>\"".$logpath.$file."\"
wget -nH -t0 -T15 -w3 --retry-connrefused --cut-dirs=3 -r -N -l inf -nv -R sfv -a \"".$logpath.$file."\" ftp://wall:wall@202.113.13.166:2121".$dir.$file2." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
wget -nH -t0 -T15 -w3 --retry-connrefused --cut-dirs=3 -r -N -l inf -nv -R sfv -a \"".$logpath.$file."\" ftp://wall:wall@202.113.13.166:2121".$dir.$file2." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"

echo \"\" >> \"".$logpath.$file."\"
echo \"发布前的操作：\" >> \"".$logpath.$file."\"
cd ".$workpath."

#删除所有空子目录
echo \"删除所有空子目录:\">>\"".$logpath.$file."\"
find ".$downloadpath.$file2." -type d -empty -exec rm -rfv {} \; >>\"".$logpath.$file."\"

echo \"\">>\"".$logpath.$file."\"
echo \"做种文件列表：\" >>\"".$logpath.$file."\"
ls -lR ".$downloadpath.$file2." >>\"".$logpath.$file."\"

#制作种子
echo \"\">>\"".$logpath.$file."\"
echo \"制作种子:\">>\"".$logpath.$file."\"
#计算分块大小
i=\$(du -s \"".$downloadpath.$file."\" | cut  -f1)
l=0
while [ \$i -ge 1  ]
do
((i=i/2));((l=l+1));
done
if [ \$l -gt 22 ];then ((l=22));
elif [ \$l -lt 16 ];then ((l=16));
fi
mktorrent -l \$l -a http://pttracker.tju.edu.cn -c \"Wall-E@TJUPT seeding (0day)\" -o ".$workpath.$file2.".torrent ".$downloadpath.$file2." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"

#根据nfo文件存在与否分类发布
echo \"\">>\"".$logpath.$file."\"
echo \"发布种子:\">>\"".$logpath.$file."\"

if [ -f ".$downloadpath.$file2."/*.nfo ];then
nohup curl -o \"".$torrentpath.$file.".torrent\" -F \"file=@".$workpath.$file.".torrent\" -F \"nfo=@`ls ".$downloadpath.$file2."/*.nfo`\" -F \"".$input."\" http://202.113.13.170/autoupload0day.php >> \"".$logpath.$file."\" ;
else
nohup curl -o \"".$torrentpath.$file.".torrent\" -F \"file=@".$workpath.$file.".torrent\" -F \"".$input."\" http://202.113.13.170/autoupload0day.php >>\"".$logpath.$file."\" ;
fi

#删除临时文件
echo \"\">>\"".$logpath.$file."\"
echo \"删除制作的种子:\" >>\"".$logpath.$file."\"
rm -rfv ".$workpath.$file2.".torrent >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
echo \"删除脚本文件:\" >>\"".$logpath.$file."\"
rm -rfv ".$workpath."running/".$file2." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
";
}
elseif(in_array($dir,array("/apps/","/mp3/","/pcgame/","/private/","/psp/","/tv/","/tv.x264/","/x264/","/xvid/")))
$script ="#!/bin/bash
#从0day服务器下载资源
cd ".$downloadpath."
echo \"从0day服务器下载资源\">>\"".$logpath.$file."\"
wget -nH -t0 -T15 -w3 --retry-connrefused --cut-dirs=1 -r -N -l inf -nv -R sfv -a \"".$logpath.$file."\" ftp://wall:wall@202.113.13.166:2121".$dir.$file." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
wget -nH -t0 -T15 -w3 --retry-connrefused --cut-dirs=1 -r -N -l inf -nv -R sfv -a \"".$logpath.$file."\" ftp://wall:wall@202.113.13.166:2121".$dir.$file." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"

echo \"\" >> \"".$logpath.$file."\"
echo \"发布前的操作：\" >> \"".$logpath.$file."\"
mv -v ".$downloadpath.$file." ".$downloadpath.$file."bak  >> \"".$logpath.$file."\"
mkdir -v ".$downloadpath.$file." >> \"".$logpath.$file."\"
cd ".$workpath."
#筛选需要发布的文件
echo \"筛选需要发布的文件：\" >> \"".$logpath.$file."\"
if [ -f ".$downloadpath.$file."bak/*.nfo ];then mv -v ".$downloadpath.$file."bak/*.nfo ".$downloadpath.$file."/; fi 
if [ -f ".$downloadpath.$file."bak/*.mkv ];then mv -v ".$downloadpath.$file."bak/*.mkv ".$downloadpath.$file."/; fi 
if [ -f ".$downloadpath.$file."bak/*.avi ];then mv -v ".$downloadpath.$file."bak/*.avi ".$downloadpath.$file."/; fi 
if [ -d ".$downloadpath.$file."bak/*ample ];then mv -v ".$downloadpath.$file."bak/*ample ".$downloadpath.$file."/; fi 
if [ -d ".$downloadpath.$file."bak/*ubs ];then mv -v ".$downloadpath.$file."bak/*ubs ".$downloadpath.$file."/; fi 
find ".$downloadpath.$file."bak/ -name \"*.rar\" -exec unrar x -o- -y {} ".$downloadpath.$file."/ \; >>\"".$logpath.$file."\"
find ".$downloadpath.$file."bak/ -name \"*.001\" -exec unrar x -o- -y {} ".$downloadpath.$file."/ \; >>\"".$logpath.$file."\"

#删除所有空子目录
echo \"删除所有空子目录:\">>\"".$logpath.$file."\"
find ".$downloadpath.$file." -type d -empty -exec rm -rfv {} \; >>\"".$logpath.$file."\"
#删除分卷压缩包及不需要发布的文件
echo \"删除分卷压缩包及不需要发布的文件:\">>\"".$logpath.$file."\"
rm -rfv ".$downloadpath.$file."bak >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"

echo \"\">>\"".$logpath.$file."\"
echo \"做种文件列表：\" >>\"".$logpath.$file."\"
ls -lR ".$downloadpath.$file." >>\"".$logpath.$file."\"

#制作种子
echo \"\">>\"".$logpath.$file."\"
echo \"制作种子:\">>\"".$logpath.$file."\"
#计算分块大小
i=\$(du -s ".$downloadpath.$file." | cut  -f1)
l=0
while [ \$i -ge 1  ]
do
((i=i/2));((l=l+1));
done
if [ \$l -gt 22 ];then ((l=22));
elif [ \$l -lt 16 ];then ((l=16));
fi
mktorrent -l \$l -a http://pttracker.tju.edu.cn -c \"Wall-E@TJUPT seeding (0day)\" -o ".$workpath.$file.".torrent ".$downloadpath.$file." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"

#根据nfo文件存在与否分类发布
echo \"\">>\"".$logpath.$file."\"
echo \"发布种子:\">>\"".$logpath.$file."\"

if [ -f ".$downloadpath.$file."/*.nfo ];then
nohup curl -o \"".$torrentpath.$file.".torrent\" -F \"file=@".$workpath.$file.".torrent\" -F \"nfo=@`ls ".$downloadpath.$file."/*.nfo`\" -F \"".$input."\" http://202.113.13.170/autoupload0day.php >> \"".$logpath.$file."\" ;
else
nohup curl -o \"".$torrentpath.$file.".torrent\" -F \"file=@".$workpath.$file.".torrent\" -F \"".$input."\" http://202.113.13.170/autoupload0day.php >>\"".$logpath.$file."\" ;
fi

#删除临时文件
echo \"\">>\"".$logpath.$file."\"
echo \"删除制作的种子:\" >>\"".$logpath.$file."\"
rm -rfv ".$workpath.$file.".torrent >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
echo \"删除脚本文件:\" >>\"".$logpath.$file."\"
rm -rfv ".$workpath."running/".$file." >>\"".$logpath.$file."\" 2 >>\"".$logpath.$file."\"
";



	//ftp操作。
	$conn_0day = ftp_connect("202.113.13.166",2121,90) or stdmsg("连接失败", "无法连接到202.113.13.166");
	ftp_login($conn_0day, $zerodayServerUsername, $zerodayServerPassword)or stdmsg("登录失败", "无法登录到202.113.13.166");
	if(substr($dir,0,6)=="/anime"){ftp_chdir($conn_0day, $dir );$temp=ftp_size($conn_0day, $file);}
	else {ftp_chdir($conn_0day, $dir.$file );$temp = ftp_pwd($conn_0day);}
	ftp_close($conn_0day);
	if($temp==$dir.$file||(substr($dir,0,6)=="/anime"&&$temp!=-1)) {	
		$handle=fopen($tempfile,'w');//将脚本写入临时文件
		if(!fwrite($handle , $script )){ stdmsg("写入文件失败", "无法将脚本写入临时文件"); stdfoot(); die;}
		fclose($handle);				
		$conn_seeding = ftp_connect($seedServer,21,30) or stdmsg("连接失败", "无法连接到$seedServer");//将脚本上传到服务器
		ftp_login($conn_seeding, $seedServerUsername, $seedServerPassword) or stdmsg("登录失败", "无法登录到$seedServer");
		ftp_pasv($conn_seeding,true);
		if(ftp_put($conn_seeding, $ftpscriptpath.$file, $tempfile, FTP_BINARY)) {
			stdmsg("成功", "资源已交由机器人发布，请在发布完成后编辑简介信息");
			write_log("$file is AutoUploaded by ".$CURUSER[username],'mod');
		}else {
			stdmsg("上传失败", "脚本文件".$file."未能传送到$seedServer");
		}
		ftp_close($conn_seeding);
		
		if(file_exists($tempfile))unlink($tempfile);  
		else {
		stdmsg("删除临时文件失败", "文件似乎不存在");
		}
	}
	else {
	stdmsg("错误", "貌似202.113.13.166上不存在你刚刚输入的资源(".$dir.$file.")");
	}
	echo "	<input type=\"button\" value=\"返回\" alt=\"返回\" onclick=\"javascript:window.location.href ='0day.php'\" /> </input>";
	stdfoot();
}
else{

$remove = (int)$_GET['remove'];
if (is_valid_id($remove))
{
	$res = sql_query("SELECT * FROM autoseeding WHERE id = ".$remove ) or sqlerr(__FILE__, __LINE__);;
	$row = mysql_fetch_assoc($res);
	$conn_seeding = ftp_connect($seedServer,21,30) or stdmsg("连接失败", "无法连接到$seedServer");//将服务器上的种子文件删除
	ftp_login($conn_seeding, $seedServerUsername, $seedServerPassword) or stdmsg("登录失败", "无法登录到$seedServer");
	if(ftp_delete($conn_seeding, $ftptorrentpath.$row['filename'])) {
		stdmsg("成功", "撤种成功");
		sql_query("DELETE FROM peers WHERE userid = 99 AND torrent = ".$row['torrentid']) or sqlerr(__FILE__, __LINE__);
		sql_query("UPDATE torrents SET seeders = seeders - 1 WHERE id = ".$row['torrentid']) or sqlerr(__FILE__, __LINE__);
		sql_query("DELETE FROM autoseeding WHERE id = ".$remove ) or sqlerr(__FILE__, __LINE__);
	}elseif(ftp_pwd($conn_seeding)!="" && ftp_size($conn_seeding, $ftptorrentpath.$row['filename'])==-1){
	stdmsg("失败", "貌似种子早就撤走了");
	sql_query("DELETE FROM autoseeding WHERE id = ".$remove ) or sqlerr(__FILE__, __LINE__);
	}else{
	stdmsg("失败", "撤种失败，请稍候再试");
	}
	ftp_close($conn_seeding);
	echo "	<input type=\"button\" value=\"返回\" alt=\"返回\" onclick=\"javascript:window.location.href ='0day.php'\" /> </input>";
	stdfoot();
	die;
}

print("<h1>已发布资源</h1>\n");

$res = sql_query("select autoseeding.id ,autoseeding.filename ,torrents.category as catid,torrents.id as torrentid,torrents.times_completed as completed, torrents.name, torrents.leechers,torrents.seeders,torrents.size,torrents.added from autoseeding left join torrents on  autoseeding.torrentid=torrents.id WHERE autoseeding.remark = 'upload' ORDER BY autoseeding.id ASC") or sqlerr();

if (mysql_num_rows($res) == 0)
  print("<p align=center><b>列表为空！</b></p>\n");
else
{
$cat=array( 401=>"电影",402=>"剧集",403=>"综艺",404=>"资料",405=>"动漫",406=>"音乐",407=>"体育",408=>"软件",409=>"游戏",410=>"其他",411=>"纪录片",4013=>"试种");
  print("<table border=1 cellspacing=0 cellpadding=5>\n");
  print("<tr><td class=colhead align=center>类型</td><td class=colhead align=left>文件名</td><td class=colhead align=center>发布日期</td><td class=colhead align=center>大小</td><td class=colhead align=left>上传</td><td class=colhead align=center>下载</td>".
    "<td class=colhead align=center>完成</td><td class=colhead>撤种</td></tr>\n");

  while ($arr = mysql_fetch_assoc($res))
  {
 	  	if($arr[catid])print("<tr><td>".$cat[$arr[catid]]."</td><td align=left><a href=details.php?id=".$arr[torrentid]."&hit=1 >".($arr[name]==""?$arr[filename]:$arr[name])."</a></td><td align=center>".$arr[added]."</td><td align=center>".mksize($arr[size])."</td><td align=center><a href=details.php?id=".$arr[torrentid]."&hit=1&dllist=1#seeders >".$arr[seeders]."</a></td><td align=center><a href=details.php?id=".$arr[torrentid]."&hit=1&dllist=1#leechers >".$arr[leechers]."</a></td><td align=center><a href=viewsnatches.php?id=".$arr[torrentid]." >".$arr['completed']."</a></td><td><a href=0day.php?remove=$arr[id]>撤种</a></td></tr>\n");
		else print("<tr><td>X</td><td align=center>种子已被删除</td><td align=center>-</td><td align=center>-</td><td align=center>-</td><td align=center>-</td><td align=center>-</td><td><a href=0day.php?remove=$arr[id]>撤种</a></td></tr>\n");
  }
  print("</table>\n");
  print("<br/>\n");
}
print("<h1>发布新资源</h1>\n");
print("<table border=1 cellspacing=0 cellpadding=5>\n");
print ("<tr><td class=colhead align=left>输入需要做种的文件夹目录：</td></tr>
<tr><td align=center><form action=\"0day.php\" method=\"post\">
<select name=dir>
<option value=\"/apps/\">/apps/</option>
<option value=\"/pcgame/\">/pcgame/</option>
<!--<option value=\"/private/\">/private/</option>-->
<option value=\"/psp/\">/psp/</option>
<option value=\"/tv/\">/tv/</option>
<option value=\"/tv.x264/\">/tv.x264/</option>
<option value=\"/x264/\" selected >/x264/</option>
<option value=\"/xvid/\">/xvid/</option>
<!--
<option value=\"/anime/\">/anime/</option>
<option value=\"/anime.rmvb/\">/anime.rmvb/</option>
-->
<option value=\"/mp3/epop/\">/mp3/epop/</option>
<option value=\"/mp3/cpop/".date("Y-m")."/\">/mp3/cpop/".date("Y-m")."/</option>
<option value=\"/mp3/cpop/".date("Y-m")."/\">/mp3/cpop/".date("Y-m",strtotime("-1 month"))."/</option>
</select>
<select name=datedir>
<option value=\"\" selected></option>
<option value=\"-0 day\">".date("m-d",strtotime("-0 day"))."/</option>
<option value=\"-1 day\">".date("m-d",strtotime("-1 day"))."/</option>
<option value=\"-2 day\">".date("m-d",strtotime("-2 day"))."/</option>
<option value=\"-3 day\">".date("m-d",strtotime("-3 day"))."/</option>
<option value=\"-4 day\">".date("m-d",strtotime("-4 day"))."/</option>
</select>
<input type=\"text\" name=\"filename\" size=\"80\" /></input>/
<input type=\"submit\"  value=\"提交\" /></form></input></td></tr>
<tr><td class=colhead align=left><h1>发布资源注意事项：</h1></td></tr>
<tr><td align=left><b></br>
1.待发布资源须为完整资源，不得缺少分卷压缩包；<br/>
2.输入的路径须准确，特别是所属文件夹；<br/>
3.路径中绝对不得含有“\$”、“*”、“?”等特殊字符；<br/>
4.如果遇到发布失败，请自行下载资源做种发布。不要使用机器人进行二次发布。<br/>
5.可以到<a class=faqlink href=http://219.243.47.169/walle-log.php><b>http://219.243.47.169/walle-log.php</b></a>查看发布日志，如果是路径错误可以重新发布。<br/>
6.暂时不要发布文件名中带有括号“（）”的资源。<br/>
</br></b></td></tr></table>
");
stdfoot();
}
?>
