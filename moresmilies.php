<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
$number=852;//表情总数
$eachpage=300;//每页表情数

?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $lang_moresmilies['head_more_smilies']?></title>
<style type="text/css">
img {border: none;}
body {color: #000000; background-color: #ffffff}
</style>
</head>
<body>
<script type="text/javascript">
function SmileIT(smile,form,text){
   window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+" "+smile+" ";
   window.opener.document.forms[form].elements[text].focus();
   window.close();
}
</script>

<table class="lista" width="100%" cellpadding="1" cellspacing="1">
<?php
$page=$_GET['page'];
if($page>=0){
$count = 0;
for($i=1+$eachpage*$page; $i<=$eachpage+$eachpage*$page&&$i<=$number; $i++) {
  if ($count % 3==0)
     print("\n<tr>");

     print("\n\t<td class=\"lista\" align=\"center\"><a href=\"javascript: SmileIT('[em$i]','".$_GET["form"]."','".$_GET["text"]."')\"><img src=\"pic/smilies/$i.gif\" alt=\"\" ></a></td>");
     $count++;

  if ($count % 3==0)
     print("\n</tr>");
}
}
?>
</table>
<div align="center">
<?php 
$lastpage=$page-1;
if($page>0){
if($page==1){
echo "<a href=\"moresmilies.php?form=compose&text=body\">";
}
else{
echo "<a href=\"moresmilies.php?form=compose&text=body&page=$lastpage\">";
}
echo $lang_moresmilies['text_last'];
echo "</a>";
}
?>
&nbsp;<a href="javascript: window.close()"><?php echo $lang_moresmilies['text_close']?></a>&nbsp;
<?php 
$nextpage=$page+1;
if($eachpage*$nextpage<$number){
echo "<a href=\"moresmilies.php?form=compose&text=body&page=$nextpage\">";
echo $lang_moresmilies['text_next'];
echo "</a>";
}
?>
</div>
</body>
</html>
