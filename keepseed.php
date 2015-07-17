<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();

if (!$_POST['action'])
{
	stdhead();
	stdmsg("信息不完整","重要数据丢失，请稍后重试。".(isset($_POST['torrentid'])?"<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>":""));
	stdfoot();
	die;
}

if (!in_array($_POST['action'],array("needseed","unneedseed","idontwantkeepseed","iwantkeepseed")))
{
	stdhead();
	stdmsg("出错了！！！","不要试图构造不存在的选项");
	stdfoot();
	die;
}
stdhead();

if(!$_POST['torrentid'])
{
	stdmsg("出错了！！！","丢失种子id。");
	break;
}

$res=sql_query("SELECT * FROM torrents WHERE id =".$_POST['torrentid']);
if(!mysql_affected_rows())
{	
	stdmsg("出错了！","种子不存在。");
	break;
}
else $arr=mysql_fetch_assoc($res);

switch ($_POST['action'])
	{
	case "needseed" :
		{
			if (get_user_class() < UC_MODERATOR)
			{
				stdmsg("出错了！！！","你没有该权限");
				break;
			}
			if($arr['needseed']=='yes')
			{	
				stdmsg("出错了！","已经有其他管理员将该种子设置成需要保种。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
				break;
			}
			sql_query("UPDATE torrents SET needkeepseed = 'yes' WHERE id =".$_POST['torrentid']);
			stdmsg("成功","已经成功将该种子设置为需要保种。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
			break;
		}
	
	case "unneedseed" :
		{	
			if (get_user_class() < UC_MODERATOR)
			{
				stdmsg("出错了！！！","你没有该权限");
				break;
			}
			if($arr['needseed']=='no')
			{	
				stdmsg("出错了！","已经有其他管理员取消了对该种子的保种需求。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
				break;
			}
			sql_query("UPDATE torrents SET needkeepseed = 'no' , seedkeeper=0 WHERE id =".$_POST['torrentid']);
			sql_query("DELETE FROM keepseed where torrentid =".$_POST['torrentid']);
			stdmsg("成功","已经成功取消对该种子的保种需求。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
			break;
		}
	case "idontwantkeepseed":
		{
			$res=sql_query("SELECT * FROM keepseed WHERE torrentid = ".$_POST['torrentid'] ." AND userid = " .$CURUSER['id'] );
			if(!mysql_affected_rows())
			{	
				stdmsg("出错了！！！","你并没有对该资源设置保种。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
				break;
			}
			else sql_query("DELETE FROM keepseed WHERE torrentid = ".$_POST['torrentid'] ." AND userid = " .$CURUSER['id'] );;
			if(mysql_affected_rows())
			{	
				sql_query("UPDATE torrents SET seedkeeper = seedkeeper - 1 WHERE id = '".$_POST['torrentid']."'") or sqlerr(__FILE__, __LINE__);
				stdmsg("成功！","你已成功取消对该资源的保种设置。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
				break;
			}
			else
			{	
				stdmsg("出错了！","取消保种失败，请稍候再尝试。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
				break;
			} 
		}
	case "iwantkeepseed":
		{
			if($arr['needseed']=='no')
			{	
				stdmsg("出错了！","该资源暂时不需要保种。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
				break;
			}
			$res=sql_query("SELECT * FROM keepseed WHERE torrentid = ".$_POST['torrentid'] ." AND userid = " .$CURUSER['id'] );
			if(!mysql_affected_rows())
			{
				sql_query("SELECT * FROM snatched  WHERE torrentid = ".$_POST['torrentid'] ." AND userid = " .$CURUSER['id']." AND to_go = 0 AND last_action > ".sqlesc(date("Y-m-d H:i:s",strtotime("last month "))));
				if(!mysql_affected_rows())
				{	
					stdmsg("出错了！","你当前未对该资源做种，不能设置保种。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
				break;
				}
				else 
				{
					sql_query("INSERT INTO keepseed	(userid , torrentid) VALUES ( '".$CURUSER[id]."' , '".$_POST['torrentid']."')") or sqlerr(__FILE__, __LINE__);
					sql_query("UPDATE torrents SET seedkeeper = seedkeeper + 1 WHERE id = '".$_POST['torrentid']."'") or sqlerr(__FILE__, __LINE__);
					stdmsg("成功！","你已成功对该资源设置了保种。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
					break;					
				}
				
			}
			else 
			{	
				stdmsg("出错了！","你已经对该资源设置过保种了。<a href=\"details.php?id=".$_POST['torrentid']."&hit=1\">点击这里返回种子介绍页</a>");
				break;
			};
			break;
		}
	}
	stdfoot();