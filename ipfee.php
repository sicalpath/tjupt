<?php
require_once("include/bittorrent.php");

function IsIPv6($ip)
{
	if (!ip2long($ip)) { //IPv6
		return true;
	}

	return false;
}

$user_ip = getip();

if(IsIPv6($user_ip)) {
	$user_ip_arr = array();
} else {
	$user_ip_arr = explode(".", $user_ip);
}
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="itolssy" />
<meta name="revised" content="itolssy, 2011-07-15" />
<title>天津大学IPV4用户流量查询</title>
<style type="text/css">
body, button, input{
	font-size: 16px;
	font-family: "ff-tisa-web-pro-1","ff-tisa-web-pro-2","Lucida Grande","Hiragino Sans GB","Hiragino Sans GB W3";
	-webkit-font-smoothing: antialiased;
	vertical-align: middle;
}

.txt {
	vertical-align: middle;
}

.box {
	height: 28px;
	padding: 4px;
	border: 1px solid #CDCDCD;
	border-color: #9A9A9A #CDCDCD #CDCDCD #9A9A9A;
	outline: none;
	vertical-align: middle;
}

.btn {
	width: 95px;
	height: 32px;
	padding: 0;
	border: 0;
	background: url(/styles/img/btn-bg.png) no-repeat;
	background-position: 0 -35px;
	background-color: #DDD;
	cursor: pointer;
	vertical-align: middle;
}

.btn:hover {
	color: #ff0000;
}
</style>
</head>

<body>
<div>
<form name="queryfee" method="post" action="http://nc.tju.edu.cn/query/feeQuery.asp">
<p>
	<span class="txt">您当前的IP地址是：<?php echo $user_ip; ?></span>
	<input name="ip" type=hidden value="<?php echo $user_ip;?>">
	<input class="btn" type=submit value="余额查询">
	<input class="btn" type="button" value="注销外网" onClick="if(window.confirm('确定注销')){window.location.href='http://211.68.233.251/F.htm'}">
</p>
</form>
</div>

<div>
<form name="queryflow" method="post" action="http://nc.tju.edu.cn/query/ipFlow.asp"> 
<p>
	<input name="year1" class="box"size="5" maxlength="4"  type=text value="<?php 
		if (date('d')<=14 && date('n')==1) {
			echo date('Y')-1; 
		} else { 
			echo date('Y');
		}
	?>"><span class="txt">年</span>
	<input name="month1" class="box"size="2" maxlength="2" type=text value="<?php 
		if (date('d')>14) {
			echo date('n');
		} else if (date('n')==1) {
			echo '12'; 
		} else {
			echo date('n')-1;
		}
	?>"><span class="txt">月，天津大学IP</span>
	<input name="ip1" class="box" size="4" maxlength="3" type=text value="<?php echo $user_ip_arr[0]; ?>"> 
	<input name="ip2" class="box"size="4" maxlength="3" type=text value="<?php echo $user_ip_arr[1]; ?>"> 
	<input name="ip3" class="box"size="4" maxlength="3" type=text value="<?php echo $user_ip_arr[2]; ?>"> 
	<input name="ip4" class="box"size="4" maxlength="3" type=text value="<?php echo $user_ip_arr[3]; ?>">
	<input class="btn" type=submit value="已用流量"> 
</p>
</form>
</div>

</body>
</html>