<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();

stdhead("Stats");
?>
<div id="msg_ajax"></div>
<script type="text/javascript">
var msg_start = 0;
function msg_ajax_close(id)
{
	$.get('messages.php?action=ajaxread&id=' + id, '', function(json){}, 'json');
	$('#msg-ajax-' + id).fadeOut();
}
function msg_ajax_read()
{
	$.get('messages.php?action=ajaxtick&start=' + msg_start, '', function(json){
		for (var i = 0; i < json.length; i ++)
		{
			if(json[i].sender_uid > 0) {
				$('<div id="msg-ajax-' + json[i].id + '" class="ajax-msg-node" msgid="' + json[i].id + '" style="background-color: #FDFDC1; border: 1px solid #CCCCCC; color: #000000; text-align: left; padding: 5px; display: none"><div style="float: left"><strong>来自 <a href="userdetails.php?id=' + json[i].sender_uid + '" style="color: #000000">' + json[i].sender_name + '</a> 的新短讯: <a href="messages.php?action=viewmessage&id=' + json[i].id + '" style="color: #000000">' + json[i].subject + '</a></strong> ' + json[i].added + '<p>' + json[i].msg + '</p></div><div style="float:right; text-align: right"><a href="#" onclick="msg_ajax_close(\'' + json[i].id + '\'); return false;" style="color: blue">标记为已读</a><br/><a href="messages.php?action=viewmessage&id=' + json[i].id + '" style="color: blue">查看详情</a></div><div style="clear:both"></div></div>').appendTo('#msg_ajax').fadeIn();
			}
			else {
				$('<div id="msg-ajax-' + json[i].id + '" class="ajax-msg-node" msgid="' + json[i].id + '" style="background-color: #FDFDC1; border: 1px solid #CCCCCC; color: #000000; text-align: left; padding: 5px; display: none"><div style="float: left"><strong>来自 系统 的新短讯: <a href="messages.php?action=viewmessage&id=' + json[i].id + '" style="color: #000000">' + json[i].subject + '</a></strong> ' + json[i].added + '<p>' + json[i].msg + '</p></div><div style="float:right; text-align: right"><a href="#" onclick="msg_ajax_close(\'' + json[i].id + '\'); return false;" style="color: blue">标记为已读</a><br/><a href="messages.php?action=viewmessage&id=' + json[i].id + '" style="color: blue">查看详情</a></div><div style="clear:both"></div></div>').appendTo('#msg_ajax').fadeIn();				
			}
			if (parseInt(json[i].id) > msg_start) msg_start = parseInt(json[i].id);
		}
		setTimeout('msg_ajax_read()', 60000);
	}, 'json');
}
$(document).ready(
	function(){
		$('#msg_ajax').html('');
		msg_ajax_read();
	}
);
</script>
<?php
stdfoot();
