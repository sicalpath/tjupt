<?php
/** 
  * 官方例子修改版 
  */

// define your token
define ( "TOKEN", "woshibeiyangyuan" );
$wechatObj = new wechatCallbackapiTest ();
// wechatObj->valid();//验证完成后可将此行代码注释掉
$wechatObj->responseMsg ();
class wechatCallbackapiTest {
	public function valid() {
		$echoStr = $_GET ["echostr"];
		
		// valid signature , option
		if ($this->checkSignature ()) {
			echo $echoStr;
			exit ();
		}
	}
	public function responseMsg() {
		// get post data, May be due to the different environments
		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
		
		// extract post data
		if (! empty ( $postStr )) {
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			$msgType = $postObj->MsgType;
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			
			if ($msgType == "text") {
				$keyword = trim ( $postObj->Content );
				$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
				$time = time ();
				
				if (! empty ( $keyword )) {
					$msgType = "text";
					if ($keyword == "h" || $keyword == "help" || $keyword == "帮助") { // 你说的这个，我懂，不劳小黄鸡烦心了，直接返回自定义结果
						$contentStr = "有什么需要媛媛帮你的吗？常见问题请说1，爱媛媛请说2，关注媛媛请说3。\n\n发送\"种子\"+空格+关键词（如\"种子 笑傲江湖 全\"），返回最近发布的、标题含有关键词的种子简要信息。\n更多玩法探索中……";
					} elseif ($keyword == "1") {
						$contentStr = "到论坛看看吧 http://goo.gl/WLQPw";
					} elseif ($keyword == "2") {
						$contentStr = "你才2呢，哇哈哈～～～";
					} elseif ($keyword == "3") {
						$contentStr = "微信: beiyangyuan, 微博: @北洋园PT, 人人主页: http://page.renren.com/601396265";
					} elseif (strpos($keyword, "种子") === 0) {
						$q = trim(mb_substr ( $keyword, 3, 40, 'utf-8' ));
						if( trim(mb_substr ( $keyword, 2, 1, 'utf-8' )) != "" ){
							$q = trim(mb_substr ( $keyword, 2, 40, 'utf-8' ));							
						}
						$qarr = explode(' ', $q);
						$q = implode('+', $qarr);
						$qurl = 'http://pt.tju6.edu.cn/wx_search_torrents.php?s=' . $q;
						$ch = curl_init ();
						curl_setopt ( $ch, CURLOPT_URL, $qurl );
						curl_setopt ( $ch, CURLOPT_HEADER, false );
						curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
						$result = curl_exec ( $ch );
						curl_close ( $ch );
						$message = json_decode ( $result, true );
						$contentStr = "最近发布：";
						foreach ($message as $t) {
							$contentStr = $contentStr . "\n\n" . $t['time'] . ", " . $t['cat'] . ", " . $t['name'] . ", " . $t['size'];
						}
					} else {
						$contentStr = simsim ( $keyword ); // 小黄鸡，你怎么看？（调用小黄鸡）
					}
					if ($contentStr == "") {
						$contentStr = $contentStr . "北洋媛就是我！说点我能听懂的吧！[常见问题请说1，爱媛媛请说2，关注媛媛请说3。\n\n查询种子信息请发送\"种子\"+空格+关键词（如\"种子 黑镜\"）。\n更多玩法请说h]";
					}
					// $contentStr = $contentStr."（自动回复，查看帮助请发help）";
					
					$resultStr = sprintf ( $textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr );
					echo $resultStr;
				} else {
					echo "大爷，您还是说点什么吧...";
				}
			} elseif ($msgType == "image") {
				$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
				$time = time ();
				$msgType = "text";
				$contentStr = "该死的程序猿，居然不教媛媛识图，呜呜呜~~~(需要帮助请说h，常见问题请说1，爱媛媛请说2，关注媛媛请说3。)";
				$resultStr = sprintf ( $textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr );
				echo $resultStr;
			} elseif ($msgType == "location") {
				$locationX = $postObj->Location_X;
				$locationY = $postObj->Location_Y;
				$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
				$time = time ();
				$msgType = "text";
				$contentStr = "经度：" . $locationY . "，纬度：" . $locationX . "，更多信息开发中……";
				$contentStr = $contentStr . "(需要帮助请说h，常见问题请说1，爱媛媛请说2，关注媛媛请说3。)";
				$resultStr = sprintf ( $textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr );
				echo $resultStr;
			} elseif ($msgType == "link") {
			} elseif ($msgType == "event") {
			}
		} else {
			echo "";
			exit ();
		}
	}
	private function checkSignature() {
		$signature = $_GET ["signature"];
		$timestamp = $_GET ["timestamp"];
		$nonce = $_GET ["nonce"];
		
		$token = TOKEN;
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		sort ( $tmpArr );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		
		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}
}

// 拿出cookie，否则会回复 "hi"
function simsim($keyword) {
	if ($keyword != '') {
		$header = array ();
		$header [] = 'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, text/html, * ' . '/* ';
		$header [] = 'Accept-Language: zh-cn ';
		$header [] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1';
		$header [] = 'Host: www.simsimi.com';
		$header [] = 'Connection: Keep-Alive ';
		// $header[]= 'Cookie: JSESSIONID=2D96E7F39FBAB9B28314607D0328D35F';
		
		$ch0 = curl_init ( 'http://www.simsimi.com/talk.htm' );
		curl_setopt ( $ch0, CURLOPT_RETURNTRANSFER, 1 );
		// get headers too with this line
		curl_setopt ( $ch0, CURLOPT_HEADER, 1 );
		$res = curl_exec ( $ch0 );
		curl_close ( $ch0 );
		// get cookie
		preg_match ( '/^Set-Cookie:\s*([^;]*)/mi', $res, $m );
		$header [] = 'Cookie: ' . $m [1];
		
		$ref = "http://www.simsimi.com/talk.htm?lc=ch";
		$ch = curl_init ();
		$options = array (
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_URL => 'http://www.simsimi.com/func/req?msg=' . $keyword . '&lc=ch',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_REFERER => $ref 
		);
		curl_setopt_array ( $ch, $options );
		$message = json_decode ( curl_exec ( $ch ), true );
		curl_close ( $ch );
		if ($message ['result'] == '100' && $message ['response'] != 'hi') {
			return $message ['response'];
		} else {
			echo '北洋媛(<A href="http://pt.tju.edu.cn">http://pt.tju.edu.cn</A>)服务器异常，请联系管理员';
		}
	}
}
?>