<?php
$ip='['.$_GET['ip'].']';
$port=$_GET['port'];;	
$sockres = @pfsockopen($ip, $port, $errno, $errstr, 5);
	if (!$sockres)
	{
		$connectable = "no";
	}
	else
	{
		$connectable = "yes";
		@fclose($sockres);
	}
echo $connectable;
?>