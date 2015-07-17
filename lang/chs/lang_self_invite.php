<?php
$domains = array(
				"tju.edu.cn",
				"mail.nankai.edu.cn",
				"nankai.edu.cn",
				"tijmu.edu.cn",
				"tmu.cn",
				"hebut.edu.cn",
				"nwsuaf.edu.cn",
				"my.swjtu.edu.cn",
				"home.swjtu.edu.cn",
				"live.xauat.edu.cn"
				
);

$lang_self_invite = array
(
	'title' => "自助邀请",
	'std_error' => "出错啦！",
	'std_wrong_code' => "使用了错误的激活码",
	'invite' => "获得一个帐号",
	'revive' => "重新激活一个被禁帐号并获得".$revive_bonus."魔力值",
	'addbonus' => "为一个既有帐号增加".$add_bonus."魔力值",
	'you_can_use_email' => "您可以使用email地址",
	'username' => "请输入需要激活/增加魔力值的用户名：",
	'enter' => "确定",
	'code_be_used' => "激活码已经被使用过了！",
	'account_not_exists' => "帐号不存在<br/>点击<a class=altlink href=self_invite.php?code=",
	'account_not_exists2' => ">这里</a>返回",
	'text_account_not_disabled' => "帮你查过了，你输入的用户没有被封禁啊！<br />点击<a class=altlink href=self_invite.php?code=",
	'text_account_disabled' => "你输入的用户已经被封禁，请先复活！<br />点击<a class=altlink href=self_invite.php?code=",
	'successful' => "成功",
	'text_account' =>  "帐户",
	'text_success_enable_account' => "已经成功恢复使用。",
	'text_no_permission' => "<b>错误！</b>你没有该权限。",
	'text_banned_by_admin' => "该账户因为违规被禁用，不允许解禁！",
	'add_bonus_for' => "成功为帐号",
	'is_success' => "增加".$add_bonus."魔力值",
	'std_is_in_use' => "已经被使用，需要重新发送邀请信息请点击<a href=\"JAVAscript:document.sendagain.submit();\"><b>这里</b></a><br />注意：发送垃圾邮件的行为将被严格禁止。<br />如果你没有收到我们的邀请邮件，请与ptadmin(at)tju.edu.cn联系。",
	//'std_is_in_use' => "已经被使用，需要重新填写邮箱请点击<a href=\"self_invite.php\"><b>这里</b></a><br />",
	'mail_one' => "您好，<br /><br />您正在使用".$SITENAME."的自助邀请系统申请加入本社区。<br />如果你有意加入，请在阅读网站规则后确认本申请。<br /><br />请点击以下链接确认申请：<br />",
	'mail_two' => $SITENAME."真诚欢迎你加入我们的社区！<br /><br />本邀请由IP为 ",
	'mail_three' => " 的用户主动申请。如果这不是您本人所为，请将此邮件转发至".$REPORTMAIL."(在主题里标明“垃圾邮件举报”)<br /><br />------<br />".$SITENAME."管理组",
	'email_to' => "邀请已成功发送至",
	'successfully_sent' => "，请前往您的邮箱查收。",
	'email_address' => "E-mail地址",
	'email_address_error' => "E-mail地址不正确！",
	'domain_not_permission' => "该邮件域不能用于自助邀请。你是怎么来到这里的？",
	'email_address_banned' => "E-mail地址被禁用，请与管理组联系！",
	'input_email_address' => "请输入你的E-mail地址：",
	'welcome' => "欢迎来到北洋园PT自助邀请系统。<br />",
	'readme' => "如果你是天津大学、南开大学、天津医科大学、河北工业大学、西南交通大学、西北农林科技大学、西安建筑科技大学的用户，在这里你可以凭特定的邮箱获得一个本站帐号。<br />当然，你也可以放弃这一权利。作为补偿，你可以得到一定的魔力值，并且还可以使一个被禁用的帐号恢复使用。<br /><a target=_blank class=faqlink href=http://candle.tju.edu.cn>点击这里注册天大邮箱</a>",
	'warning' => "<br/><b>注意</b>:<br/>每个邮箱只能申请一次账号，账号、魔力值和复活一个被禁用户三者只能选其一。<br/>如果你已经用你的邮箱申请过一次账号，那么不论此账号是否仍在使用，你都不能通过此邮箱获得第二个账号。请向您的朋友索要邀请码加入本站。",
	'do_not_treat_us' => "不要试图从非法途径获得邀请。",
	'testing' => "该功能尚未开放！",
	'notice' => "注意：tju邮箱用户如果无法收到邮件请检查<b>邮件网关</b>查看<b>拦截记录</b>！"

);

?>
