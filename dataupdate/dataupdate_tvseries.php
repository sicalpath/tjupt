<?
ob_start();
set_time_limit(0);
ignore_user_abort(TRUE);
header("Content-Type:text/html; charset=utf-8");
header("Connection: close");
ob_end_flush();
flush();

require_once("include/bittorrent.php");
dbconn();
if (ob_get_level() == 0) ob_start();

	$query = "SELECT id, name FROM torrents WHERE category=402 limit 100";
	//$query = "SELECT name,id FROM torrents WHERE id=243";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	
	while($arr = mysql_fetch_assoc($res))
	{	
		$updateinfoset =array();
		
		$fullname=$arr["name"];
		$specificcat = substr($fullname ,1,strpos($fullname,"]")-1);	
		$secondname = str_replace("[".$specificcat."]","",$fullname);
		$cname = substr($secondname ,1,strpos($secondname,"]")-1);
		$thirdname = str_replace("[".$cname."]","",$secondname);
		
		$query1 = "select ename, tvseasoninfo,format from torrentsinfo where torid = ".$arr["id"];
		$res1 = sql_query($query1) or sqlerr(__FILE__, __LINE__);
		while($arr1 = mysql_fetch_assoc($res1)){
			if($arr1['ename']!=""){				
				$ename = substr($thirdname ,1,strpos($thirdname,"]")-1);
				$forthname = str_replace("[".$ename."]","",$thirdname);
						
				if($arr1['tvseasoninfo']!=""){
					$tvseasoninfo = substr($forthname ,1,strpos($forthname,"]")-1);	
					$fifthname = str_replace("[".$tvseasoninfo."]","",$forthname);
					
					if($arr1['format']!=""){
						$format = substr($fifthname ,1,strpos($fifthname,"]")-1);
						$sixthname = str_replace("[".$format."]","",$fifthname);
						$language = substr($sixthname ,1,strpos($sixthname,"]")-1);
					}
					else
						$language = substr($fifthname ,1,strpos($fifthname,"]")-1);
				}
				else{
					if($arr1['format']!=""){
						$format = substr($forthname ,1,strpos($forthname,"]")-1);
						$fifthname = str_replace("[".$format."]","",$forthname);
						$language = substr($fifthname ,1,strpos($fifthname,"]")-1);
					}
					else
						$language = substr($forthname ,1,strpos($forthname,"]")-1);
				}
				
			}
			else{
				if($arr1['tvseasoninfo']!=""){
					$tvseasoninfo = substr($thirdname ,1,strpos($thirdname,"]")-1);	
					$forthname = str_replace("[".$tvseasoninfo."]","",$thirdname);
					
					if($arr1['format']!=""){
						$format = substr($forthname ,1,strpos($forthname,"]")-1);
						$fifthname = str_replace("[".$format."]","",$forthname);
						$language = substr($fifthname ,1,strpos($fifthname,"]")-1);
					}
					else
						$language = substr($forthname ,1,strpos($forthname,"]")-1);
				}
				else{
					if($arr1['format']!=""){
						$format = substr($thirdname ,1,strpos($thirdname,"]")-1);
						$forthname = str_replace("[".$format."]","",$thirdname);
						$language = substr($forthname ,1,strpos($forthname,"]")-1);
					}
					else
						$language = substr($thirdname ,1,strpos($thirdname,"]")-1);
				}
			}
				
		}
		
		$updateinfoset[] = "specificcat = '".mysql_escape_string($specificcat)."'";
		$updateinfoset[] = "cname = '".mysql_escape_string($cname)."'";
		$updateinfoset[] = "tvseasoninfo = '".mysql_escape_string($tvseasoninfo)."'";
		$updateinfoset[] = "language = '".mysql_escape_string($language)."'";
		
		//$sql=join(",",$updateinfoset);
		//$sql = "language = '".mysql_escape_string($language)."'";
		$sql=join(",",$updateinfoset);
		print($arr[id]."  ");
		print("类型：".$specificcat."  ");
		print("中文名：".$cname."  ");
		print("剧集季度信息：".$tvseasoninfo."  ");
		print("语言：".$language."  ");
		//sql_query("UPDATE torrentsinfo SET " . $sql . " WHERE torid = $arr[id]") or sqlerr(__FILE__, __LINE__);
		
		print("<br/>");
	
		ob_flush();
		flush();
	}
?>