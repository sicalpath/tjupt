<?php
if(isset($_POST["text"]))
{
print("文本<br/>\n".htmlspecialchars($_POST["text"])."<br/>\n使用MD5函数加密后的结果是：<br/>\n");
print(md5($_POST["text"])."<br/><br/>\n");
}
?>

<form action="<?php print (str_replace("/","",$_SERVER['PHP_SELF']));?>" method="post">请输入需要使用MD5函数加密的文本：<br/>
<textarea name="text" cols="50" rows="5"></textarea>
<br/>
<input type="submit" value="加密" />
</form>