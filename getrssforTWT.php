<?php
require "include/bittorrent.php";
dbconn();
function hex_esc($matches) {
	return sprintf("%02x", ord($matches[0]));
}
$query="select id,subject from topics where forumid=17 ORDER BY id desc limit 8";
$res = sql_query($query) or die(mysql_error());
$url="http://pt.tju.edu.cn";
header ("Content-type: text/xml");
print("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
//The commented version passed feed validator at http://www.feedvalidator.org
/*print('
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">');*/
print('
<rss version="2.0">');
print('
	<channel>
		<title>' . addslashes($SITENAME.' Torrents'). '</title>
		<link><![CDATA[' . $url . ']]></link>
		<description><![CDATA[' . addslashes('Latest torrents from '.$SITENAME.' - '.htmlspecialchars($SLOGAN)) . ']]></description>
		<language>zh-cn</language>
		<copyright>'.$copyright.'</copyright>
		<managingEditor>'.$SITEEMAIL.' ('.$SITENAME.' Admin)</managingEditor>
		<webMaster>'.$SITEEMAIL.' ('.$SITENAME.' Webmaster)</webMaster>
		<pubDate>'.date('r').'</pubDate>
		<generator>'.PROJECTNAME.' RSS Generator</generator>
		<docs><![CDATA[http://www.rssboard.org/rss-specification]]></docs>
		<ttl>60</ttl>
		<image>
			<url><![CDATA[' . $url.'/pic/rss_logo.jpg'. ']]></url>
			<title>' . addslashes($SITENAME.' Torrents') . '</title>
			<link><![CDATA[' . $url . ']]></link>
			<width>100</width>
			<height>100</height>
			<description>' . addslashes($SITENAME.' Torrents') . '</description>
		</image>');
/*print('
		<atom:link href="'.$url.$_SERVER['REQUEST_URI'].'" rel="self" type="application/rss+xml" />');*/
print('
');
while ($row = mysql_fetch_array($res))
{


	
		$itemurl = $url."/forums.php?action=viewtopic&amp;forumid=17&amp;topicid=".$row['id'];
	
	$title = $row['subject'];
	print('		<item>
			<title><![CDATA['.$title.']]></title>
			<link>'.$itemurl.'</link>
			<description></description>');
//print('			<dc:creator>'.$author.'</dc:creator>');
print('
		</item>
');
}
print('	</channel>
</rss>');
?>
