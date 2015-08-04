<?php
/**
 * Created by PhpStorm.
 * User: zcqian
 * Date: 15/7/26
 * Time: 上午12:40
 */

require "include/bittorrent.php";
dbconn();
loggedinorreturn();
$ref = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);

if(!in_array($ref, array('pt.tju.edu.cn', 'pt.tju6.edu.cn')))
{
    stderr("错误", "页面打开的方式不正确");
}

if(!isset($_GET['ext_url']))
{
    stderr("错误", "访问的参数不正确");
}

$external_url = urldecode($_GET['ext_url']);

stdhead("跳转到外部网址");
$msg = "您将跳转到外部网址：" . htmlspecialchars($external_url) . "<br>" . "点击<a class=\"faqlink\" href=\"$external_url\">这里</a>进行跳转";
stdmsg("跳转到外部网址", $msg);
stdfoot();