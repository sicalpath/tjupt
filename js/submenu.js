// 以下是HTML代码操作
// 在head里面添加样式表
var style = document.createElement('style');
style.innerHTML = [
// 这里要依据不同的样式重新编写
'#drop_menu a {',
'background: #FFC;',
'display: block;',
'padding: 2px 5px;',
'}',
'#drop_menu a:hover {',
'background: #FCC;',
'}',
// 下拉效果
'#drop_menu div {',
// max-height: IE 7 & Opera 7 & Chrome/Safari/Firefox 1
'transition: max-height 0.1s linear 0s;', // IE 10 & Firefox 16
'-o-transition: max-height 0.1s linear 0s;', // Opera 10.5
'-webkit-transition: max-height 0.1s linear 0s;', // Safari 3.2 & Chrome 1
'overflow: hidden;',
'}'
].join('');
document.querySelector('head').appendChild(style);

// 给要有下来菜单的按钮加id
var a = document.querySelector('a[href="mybonusapps.php"]');
a.id="mybonusapps";
var b = document.querySelector('a[href="usercp.php"]');
b.id="usercp";

// 在最后加一个#drop_menu的div，在里面放所有下拉菜单
var d = document.createElement('div'); d.id = "drop_menu";
d.innerHTML = [
'<div id="moliyuan_dropdown">',
'<a href="/blackjack.php"> 21点 </a>',
'<a href="/jc_currentbet_L.php"> 竞猜 </a>',
'</div>',
'<div id="ctrlpan_dropdown">',
'<a href="/usercp.php?action=personal">个人设定</a>',
'<a href="/usercp.php?action=tracker">网站设定</a>',
'<a href="/usercp.php?action=forum">论坛设定</a>',
'<a href="/usercp.php?action=security">安全设定</a>',
'<a href="/addcss.php">个性化CSS</a>',
'</div>'
].join('');
document.body.appendChild(d);


// 以下内容是JS代码…… 0.0
[
{ 'menu': 'mybonusapps', 'drop': 'moliyuan_dropdown'},
{ 'menu': 'usercp', 'drop': 'ctrlpan_dropdown'}
].map(function (p) {
var a = document.getElementById(p.menu);
var m = document.getElementById(p.drop);

var menu = (function (a, m) {
var state = false;
// 计算应当出现的位置
var calcXY = function () {
var ax = 0, ay = 0, i;
for (i = a; i; i = i.offsetParent) {
ax += i.offsetLeft;
ay += i.offsetTop;
};
return {'x': ax, 'y': ay};
};
// 定位对应的下来菜单
var spos = function () {
var pos = calcXY();
m.style.position = 'absolute';
m.style.left = pos.x + 'px';
m.style.top = pos.y + a.clientHeight + 'px';
};
// 显示下拉菜单
var show = function () {
state = true;
m.style.display = 'block';
spos();
var height = m.childNodes.length * m.firstChild.clientHeight;
m.style.maxHeight = height + 'px';
};
// 隐藏下拉菜单
var hide_do = function (im) {
if (state) return;
m.style.maxHeight = '0';
if (im) m.style.display = 'none';
else setTimeout(function () {
if (state) return;
m.style.display = 'none';
}, 100);
};
// 延时后隐藏下拉菜单
var hide = function () {
timer = setTimeout(hide_do, 100);
state = false;
};
// 初始化隐藏和位置
hide_do(true); spos();
return {
'show': show, 'hide': hide
};
}(a, m));

// 添加事件
[a, m].map(function (o) {
o.addEventListener('mouseover', function () { menu.show(); });
o.addEventListener('mouseout', function () { menu.hide(); });
});
});