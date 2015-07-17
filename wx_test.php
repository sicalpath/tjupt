<?php
define('BACKEND_HOST', '219.243.47.169/mp');

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = file_get_contents('php://input');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://' . BACKEND_HOST . '/mp/push');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', 'Accept: text/xml'));
	$content = curl_exec($ch);
	curl_close($ch);
}
else
{
	$content = file_get_contents('http://' . BACKEND_HOST . '/mp/push?' . $_SERVER["QUERY_STRING"]);
}

header('Content-Type: text/xml');
echo $content;
?>