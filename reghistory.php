<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();

$id = 0+$_GET['id'];
if(!$id)
stderr("出错了！","缺乏关键信息，无法完成查询！");


$query =("SELECT ip,type FROM shoutbox WHERE id = '".$id."' LIMIT 1");
$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_assoc($res);
if(!$arr)
stderr("出错了！","记录不存在！");

if($arr["type"]=="sb")
stderr("出错了！","只能查看游客的注册信息！");

else{

	if($arr["ip"]=="121.193.129.223"){$message="该用户是天津大学仁爱学院用户，建议发放邀请！<br/><br/>";}
	if(substr($arr["ip"],0,6)=="172.18")
	{
	$message="该用户是天津大学无线网用户<br/><br/>";
	$group="段";
	$partip=str_replace("172.18.","",$arr["ip"]);
	$partip=substr($partip,0,strpos($partip,"."));
	$iptype="like '172.18.".$partip.".%'";
	$selectip="172.18.".$partip.".*";
	}
	elseif(substr($arr["ip"],0,6)=="172.20")
	{
	$message="该用户是天津大学PPPOE拨号用户<br/><br/>";
	$group="段";
	$partip=str_replace("172.20.","",$arr["ip"]);
	$partip=substr($partip,0,strpos($partip,"."));
	$iptype="like '172.20.".$partip.".%'";
	$selectip="172.20.".$partip.".*";
	}
	elseif(substr($arr["ip"],0,10)=="2403:ac00:")
	{
	$message="该用户是天津大学IPv6用户<br/><br/>";
	$group="段";
	$partip=str_replace("2403:ac00:","",$arr["ip"]);
	$partip=substr($partip,0,strpos($partip,":"));
	$iptype="like '2403:ac00:".$partip.":%'";
	$selectip="2403:ac00:".$partip.":*";
	}
	elseif(substr($arr["ip"],0,13)=="2001:da8:a000")
	{
	$message="该用户是天津大学IPv6用户<br/><br/>";
	$group="段";
	$partip=str_replace("2001:da8:a000:","",$arr["ip"]);
	$partip=substr($partip,0,strpos($partip,":"));
	$iptype="like '2001:da8:a000:".$partip.":%'";
	$selectip="2001:da8:a000:".$partip.":*";
	}
	elseif(substr($arr["ip"],0,9)=="2001:da8:")
	{
	$message="该用户是中国教育网IPv6用户<br/><br/>";
	$group="段";
	$partip=str_replace("2001:da8:","",$arr["ip"]);
	$partip1=substr($partip,0,strpos($partip,":"));
	$partip=str_replace($partip1.":","",$partip);
	$partip2=substr($partip,0,strpos($partip,":"));
	$iptype="like '2001:da8:".$partip1.":".$partip2.":%'";
	$selectip="2001:da8:".$partip1.":".$partip2.":*";
	}
	elseif(substr($arr["ip"],0,9)=="2001:250:")
	{
	$message="该用户是中国教育网IPv6用户<br/><br/>";
	$group="段";
	$partip=str_replace("2001:250:","",$arr["ip"]);
	$partip1=substr($partip,0,strpos($partip,":"));
	$partip=str_replace($partip1.":","",$partip);
	$partip2=substr($partip,0,strpos($partip,":"));
	$iptype="like '2001:250:".$partip1.":".$partip2.":%'";
	$selectip="2001:250:".$partip1.":".$partip2.":*";
	}	
	else 
	{
	$iptype=" ='".$arr["ip"]."'";
	$selectip=$arr["ip"];
	}
	
	$res=sql_query("SELECT userid FROM iplog WHERE ip ".$iptype." GROUP BY userid");
	$all=mysql_num_rows($res);
	if($all)
	{ 	
		$sql0=sql_query("SELECT userid FROM iplog WHERE ip ".$iptype." GROUP BY userid");
		while($rows = mysql_fetch_assoc($sql0))
		$row[]=$rows["userid"];
		$still = mysql_fetch_assoc(sql_query("SELECT count(*) AS num FROM users WHERE ( id = '".join("' OR id = '",$row)."' )"));
		$banned = mysql_fetch_assoc(sql_query("SELECT count(*) AS num FROM users WHERE ( id = '".join("' OR id = '",$row)."' ) AND enabled = 'no'"));
		stderr("统计结果:",$message."该用户（IP".$group.(get_user_class() >= $sbmanage_class?":".$selectip:"")."）下有历史帐号 ".count($row)." 个，其中被删除帐号".(count($row)-$still["num"])."个，被禁用帐号 ".$banned["num"]." 个，当前存活帐号".($still["num"]-$banned["num"])."个。<br/><br/>帐号存活比例".(int)(100*($still["num"]-$banned["num"])/count($row))."%。根据该帐号存活率，".((100*($still["num"]-$banned["num"])/count($row))>50?"":"不")."建议发放邀请！",0);
	}
	stderr("统计结果:","该用户".(get_user_class() >= $sbmanage_class?"(".$selectip.")":"")."尚无本站使用记录,建议酌情发放邀请！");
	
	
	
	
	
	
}




?>
