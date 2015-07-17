var reobj = null;
var http_request = false;

if(window.XMLHttpRequest){
http_request=new XMLHttpRequest();
    if(http_request.overrideMimeType){
    http_request.overrideMimeType("text/xml");
    }
}
else if(window.ActiveXObject){
    try{
    http_request=new ActiveXObject("Msxml2.XMLHttp");
    }catch(e){
      try{
      http_request=new ActiveXobject("Microsoft.XMLHttp");
      }catch(e){}
      }
}


function send_request(url){


    if(!http_request){
    window.alert("创建XMLHttp对象失败！");
    return false;
    }
    http_request.open("GET",url,true);
    http_request.onreadystatechange=processrequest;
    http_request.send(null);
}

//处理返回信息的函数
function processrequest(){
   if(http_request.readyState==4){ //判断对象状态
     if(http_request.status==200){ //信息已成功返回，开始处理信息
     document.getElementById(reobj).innerHTML=http_request.responseText;
   }
   else{
     alert("您所请求的页面不正常！");
   }
}
}
   
function getcategory(obj,selectid){

   var catid=document.getElementById(selectid).value;

   document.getElementById(obj).innerHTML="<option>loading...</option>";
   reobj=obj;   
   send_request("showcategorydetail.php?catid="+catid);
}

function getcheckboxvalue(textid,number) {
	var Q = document.getElementById(textid);
	Q.value = "";

	for (var i=1;i<=number;i++) {
		id = textid + i;
		var e = document.getElementById(id);
		if ( e.checked == true) {
			Q.value = Q.value + e.value + "/"
		}
	}
}

function getradiovalue(textid,radioid) {
	
	var Q = document.getElementById(textid);
	
	var e = document.getElementById(radioid);
	otherid = textid + "other";
	
	if(radioid == otherid)
		Q.value = "";
	else
		Q.value = e.value

}

function getexpirevalue() {
	
	var Q = document.getElementById("reason");
	
	var e = document.getElementById("expire");
	
	Q.value = e.value
}

function QueryString()  
{  
     //构造参数对象并初始化   
     var name,value,i;   
     var str=location.href;//获得浏览器地址栏URL串   
     var num=str.indexOf("?")   
     str=str.substr(num+1);//截取“?”后面的参数串   
     var arrtmp=str.split("&");//将各参数分离形成参数数组   
     for(i=0;i < arrtmp.length;i++)  
     {   
         num=arrtmp[i].indexOf("=");   
         if(num>0)  
         {   
             name=arrtmp[i].substring(0,num);//取得参数名称   
             value=arrtmp[i].substr(num+1);//取得参数值   
            this[name]=value;//定义对象属性并初始化   
        }   
     }   
}  
    
function getuploadinfo(obj,selectid){

   var offerid=document.getElementById(selectid).value;

   document.getElementById(obj).innerHTML="<option>loading...</option>";
   reobj=obj;   
   send_request("catdetail_offertotorrent.php?offerid="+offerid);
}

function validate(selectid){
	 	var catid = document.getElementById(selectid).value;
		
		if(catid==401){
			var cname = document.getElementsByName("cname")[0].value;
			var ename = document.getElementsByName("ename")[0].value;
			var issuedate = document.getElementsByName("issuedate")[0].value;
			var language = document.getElementById("language").value;
			var format = document.getElementById("format").value;
			var subsinfo = document.getElementsByName("subsinfo")[0].value;
			var district = document.getElementById("district").value;

			if(cname==""||ename==""||issuedate==""||language==""||format==""||subsinfo==0||district==""){
				alert("missing data!");
				return false;
			}
		}
		if(catid==402){
			var cname = document.getElementsByName("cname")[0].value;
			var language = document.getElementById("language").value;
			var specificcat = document.getElementById("specificcat").value;
			if(cname==""||language==""||specificcat==""){
				alert("missing data!");
				return false;
			}
		}
		if(catid==405){
			var cname = document.getElementsByName("cname")[0].value;
			var ename = document.getElementsByName("ename")[0].value;
			var specificcat = document.getElementById("specificcat").value;
			var format = document.getElementById("format").value;
			var substeam = document.getElementsByName("substeam")[0].value;
			if(ename==""||specificcat==""||format==""||substeam==""){
				alert("missing data!");
				return false;
			}
		}
		if(catid==406){
			var hqname = document.getElementsByName("hqname")[0].value;
			var artist = document.getElementsByName("artist")[0].value;
			var format = document.getElementById("format").value;
			if(hqname==""||artist==""||format==""){
				alert("missing data!");
				return false;
			}
		}
		if(catid==407){
			var cname = document.getElementsByName("cname")[0].value;
			var specificcat = document.getElementById("specificcat").value;
			var format = document.getElementById("format").value;
			if(cname==""||specificcat==""||format==""){
				alert("missing data!");
				return false;
			}
		}
		if(catid==409){
			var cname = document.getElementsByName("cname")[0].value;
			var platform = document.getElementById("platform").value;
			var format = document.getElementById("format").value;
			if(cname==""||platform==""||format==""){
				alert("missing data!");
				return false;
			}
		}
		if(catid==410){
			var cname = document.getElementsByName("cname")[0].value;
			var specificcat = document.getElementById("specificcat").value;
			if(cname==""||specificcat==""){
				alert("missing data!");
				return false;
			}
		}
		if(catid==411){	
			var cname = document.getElementsByName("cname")[0].value;		
			var subsinfo = document.getElementsByName("subsinfo")[0].value;
			var specificcat = document.getElementById("specificcat").value;
			if(cname==""||subsinfo==0||specificcat==""){
				alert("missing data!");
				return false;
			}
		}
		if(catid==412){
			var cname = document.getElementsByName("cname")[0].value;
			var ename = document.getElementsByName("ename")[0].value;
			var language = document.getElementById("language").value;
			var subsinfo = document.getElementsByName("subsinfo")[0].value;
			var district = document.getElementById("district").value;

			if(cname==""||ename==""||language==""||subsinfo==0||district==""){
				alert("missing data!");
				return false;
			}
		}
}



















