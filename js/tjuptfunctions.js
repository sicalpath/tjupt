function tjuGetPageTitle() {
    var my_doc_title = document.title;
    if (my_doc_title == null) {
        var t_titles = document.getElementByTagName("title");
        if (t_titles && t_titles.length > 0) {
            my_doc_title = t_titles[0];
        } else {
            my_doc_title = "";
        }
    }
    return my_doc_title;
}

function tjuShare(m, a) {
    a = (typeof(a) == "undefined") ? "" : a;

    var my_title = tjuGetPageTitle();
    var to_url = "";
    var param = null;
    if (m == "weibo") {
        param = {
            url: location.href,
            pic: a,
            title: my_title,
            appkey: "1402694843",
            ralateUid: "2308910882"
        },
        to_url = "http://service.weibo.com/share/share.php?";
    } else if (m == "tqq") {
        param = {
            url: location.href,
            pic: a,
            title: my_title,
            appkey: "801200967",
            site: "http://pt.tju.edu.cn"
        },
        to_url = "http://v.t.qq.com/share/share.php?";
    } else if (m == "renren") {
        param = {
            url: location.href,
            pic: a,
            title: my_title
        },
        to_url = "http://widget.renren.com/dialog/share?";
    }

    var temp = [];
    for (var p in param) {
        temp.push(p + "=" + encodeURIComponent(param[p] || ""))
    }

    to_url = to_url + temp.join("&");
    window.open(to_url, "_blank", "width=700, height=680, toolbar=no, menubar=no, scrollbars=no, location=yes, resizable=no, status=no");

}

$(document).ready(function() {

    $(window).scroll(function() { //只要窗口滚动,就触发下面代码
        var scrollt = document.documentElement.scrollTop + document.body.scrollTop; //获取滚动后的高度
        if (scrollt > 200) { //判断滚动后高度超过200px,就显示
            $("#gotop").fadeIn(400); //淡出
            $(".navbar").stop().fadeTo(400, 0.2);
        } else {
            $("#gotop").fadeOut(400); //如果返回或者没有超过,就淡入.必须加上stop()停止之前动画,否则会出现闪动
            $(".navbar").stop().fadeTo(400, 1);
        }
    });
    $("#gotop").click(function() { //当点击标签的时候,使用animate在200毫秒的时间内,滚到顶部
        $("html,body").animate({
            scrollTop: "0px"
        }, 200);
    });
    $(".navbar").mouseenter(function() {
        $(".navbar").fadeTo(100, 1);
    });
    $(".navbar").mouseleave(function() {
        var scrollt = document.documentElement.scrollTop + document.body.scrollTop;
        if (scrollt > 200) {
            $(".navbar").fadeTo(100, 0.2);
        }
    });
    //******************************************************
    setTimeout(function() {
        $(".j-ad-close").parent().slideUp(function() {
            $(this).parent().slideUp();
        });
    }, 1600);

    $(".j-ad-close").click(function() {
        $(this).parent().slideUp(function() {
            $(this).parent().slideUp();
        });
    })
});
