window.onload=function()//用window的onload事件，窗体加载完毕的时候
{
   var requestobj = new QueryString();  
   document.getElementById('class1').innerHTML="<option>loading...</option>";
   reobj="class1"; 
   var a = Math.random();
   
   if(document.getElementById("editclass"))
   	send_request("catdetail_editoffers.php?offerid="+requestobj.id+"&ref="+a);
   else
   	send_request("catdetail_edittorrents.php?torid="+requestobj.id+"&ref="+a);
}