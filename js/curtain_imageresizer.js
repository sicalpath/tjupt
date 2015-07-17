if (navigator.appName == "Netscape") {
	document.write("<style type='text/css'>body {overflow-y:scroll;}<\/style>");
}
var userAgent = navigator.userAgent.toLowerCase();
var is_ie = (userAgent.indexOf('msie') != -1)
		&& userAgent.substr(userAgent.indexOf('msie') + 5, 3);

function $() {
	var elements = new Array();
	for ( var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
			element = document.getElementById(element);
		if (arguments.length == 1)
			return element;
		elements.push(element);
	}
	return elements;
}

function ScaleOld(image, max_width, max_height) {
	var tempimage = new Image();
	tempimage.src = image.src;
	var tempwidth = tempimage.width;
	var tempheight = tempimage.height;
	if (tempwidth > max_width) {
		image.height = tempheight = Math.round(((max_width) / tempwidth)
				* tempheight);
		image.width = tempwidth = max_width;
	}

	if (max_height != 0 && tempheight > max_height) {
		image.width = Math.round(((max_height) / tempheight) * tempwidth);
		image.height = max_height;
	}
}

function check_avatar(image, langfolder) {
	var tempimage = new Image();
	tempimage.src = image.src;
	var displayheight = image.height;
	var tempwidth = tempimage.width;
	var tempheight = tempimage.height;
	if (tempwidth > 250 || tempheight > 250 || displayheight > 250) {
		image.src = 'pic/forum_pic/' + langfolder + '/avatartoobig.png';
	}
}

function PreviewOld(image) {
	if (!is_ie || is_ie >= 7) {
		$('lightbox').innerHTML = "<a onclick=\"Return();\"><img src=\""
				+ image.src + "\" /></a>";
		$('curtain').style.display = "block";
		$('lightbox').style.display = "block";
	} else {
		window.open(image.src);
	}
}

function PreviewurlOld(url) {
	if (!is_ie || is_ie >= 7) {
		$('lightbox').innerHTML = "<a onclick=\"Return();\"><img src=\"" + url
				+ "\" /></a>";
		$('curtain').style.display = "block";
		$('lightbox').style.display = "block";
	} else {
		window.open(url);
	}
}

function Scale(image, max_width, max_height) { // From http://pt.antsoul.com

	var tempimage = new Image();
	image.className = '';
	tempimage.className = '';
	image.style.zoom = '100%';
	$(image).css({
		maxWidth : max_width + 'px'
	});
	if (max_height != 0)
		$(image).css({
			maxHeight : max_height + 'px'
		});
}
function Preview(image) { // From http://pt.antsoul.com
	if (!is_ie || is_ie >= 7) {
		$('#lightbox').css({
			"zoom" : "100%"
		});
		$('#lightbox').html(
				"<img  id=\"wrapDiv\" src=\"" + image.src
						+ "\" onmousewheel=\"return bbimg(this);\"/>");
		$('#curtain').fadeIn();
		$('#lightbox').fadeIn();
		dragimg();
	} else {
		window.open(image.src);
	}
}
function Previewurl(url) { // From http://pt.antsoul.com
	if (!is_ie || is_ie >= 7) {
		$('#lightbox').css({
			"zoom" : "100%"
		});
		$('#lightbox').html(
				"<img id=\"wrapDiv\" src=\"" + url
						+ "\" onmousewheel=\"return bbimg(this);\" />");
		$('#curtain').fadeIn();
		$('#lightbox').fadeIn();
		dragimg();

	} else {
		window.open(url);
	}
}

function findPosition(oElement) {
	if (typeof (oElement.offsetParent) != 'undefined') {
		for ( var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent) {
			posX += oElement.offsetLeft;
			posY += oElement.offsetTop;
		}
		return [ posX, posY ];
	} else {
		return [ oElement.x, oElement.y ];
	}
}

function Return() {
	// $('lightbox').style.display = "none";
	// $('curtain').style.display = "none";
	// $('lightbox').innerHTML = "";
	$('#curtain').fadeOut();
	$('#lightbox').fadeOut();
	// $('#lightbox').html("");
}

function bbimg(o) { // From http://pt.antsoul.com
	var zoom = parseInt(o.style.zoom, 10) || 100;

	if (zoom <= 10)
		zoom = 100;
	zoom += event.wheelDelta / 12;
	if (zoom > 10 && zoom < 500)
		o.style.zoom = zoom + '%';
	return false;
}

function dragimg() { // From http://pt.antsoul.com
	$('#lightbox img')
			.wrap(
					"<div id='wrapDiv' style='position:relative;top:0px;left:0px;visibility: visible;'></div>");
	$('#wrapDiv img').css({
		"cursor" : "move"
	});
	$("#wrapDiv").unbind();
	var _move = false;
	var _x, _y;
	$("#lightbox img")
			.bind(
					"mousedown",
					function(e) {
						_move = true;
						if (!document.all) {
							_x = e.pageX - parseInt($("#wrapDiv").css("left"));
							_y = e.pageY - parseInt($("#wrapDiv").css("top"));
						} else {
							var pagex = e.clientX
									+ (document.documentElement.scrollLeft ? document.documentElement.scrollLeft
											: document.body.scrollLeft);
							var pagey = e.clientY
									+ (document.documentElement.scrollTop ? document.documentElement.scrollTop
											: document.body.scrollTop);
							_x = pagex - parseInt($("#wrapDiv").css("left"));
							_y = pagey - parseInt($("#wrapDiv").css("top"));
						}
					});
	$("#lightbox").bind("mouseup", function(e) {
		_move = false;

	});

	$("#wrapDiv img").bind("click", function(e) {
		return false;
	});

	$("#lightbox")
			.bind(
					"mousemove",
					function(e) {
						if (_move) {
							if (!document.all) {
								var pagex = e.pageX;
								var pagey = e.pageY;
							} else {
								var pagex = e.clientX
										+ (document.documentElement.scrollLeft ? document.documentElement.scrollLeft
												: document.body.scrollLeft);
								var pagey = e.clientY
										+ (document.documentElement.scrollTop ? document.documentElement.scrollTop
												: document.body.scrollTop);
							}
							var x = pagex - _x;
							var y = pagey - _y;
							$("#wrapDiv").css("top", y);
							$("#wrapDiv").css("left", x);
						}
					});
}