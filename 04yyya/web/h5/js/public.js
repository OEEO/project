/**
 * Created by fyt on 2016/10/26.
 */
$(function(){
    $('html').attr('style', 'font-size:' + ($(window).width() / 7.5) + 'px');
    $(window).resize(function(){
        $('html').attr('style', 'font-size:' + ($(window).width() / 7.5) + 'px');
    });
    var url = document.referrer;
    if(url == 'http://yummy194.cn/' || url =='http://youfanapp.com/'){
        var b =window.location.href.split('#');
        window.location.href = b[0] +'#webbanner';
    }
    if(url == 'http://yummy194.cn/?page=themeList' || url == 'http://youfanapp.com/?page=themeList'){
        var t =window.location.href.split('#');
        window.location.href = t[0] +'#webtheme';
    }

    $.ajax({
        url : 'http://api.yummy194.cn/',
        // url : 'http://api.m.yami.ren/',
        async : false,
        data : {url:location.href},
        success : function(d){
            // loadWechat(d.appId, d.timestamp, d.nonceStr, d.signature);
            wx.config({
                debug: false,
                appId: d.appId,
                timestamp: d.timestamp,
                nonceStr: d.nonceStr,
                signature: d.signature,
                jsApiList: [
                    'onMenuShareTimeline',
                    'onMenuShareAppMessage',
                    'onMenuShareQQ',
                    'onMenuShareWeibo',
                    'onMenuShareQZone'
                ]
            });
            wx.error(function (res) {
                //alert(res.errMsg);
            });
            wx.ready(function () {
                wx.onMenuShareTimeline({
                    title: datas.title, // 分享标题
                    link: datas.link, // 分享链接
                    imgUrl: datas.image, // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                wx.onMenuShareAppMessage({
                    title: datas.title,
                    desc: datas.des,
                    link: datas.link,
                    imgUrl: datas.image,
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
            });
        }
    });
    // sharer(datas.title,datas.des,datas.link,datas.image);
});
function share(){
    return JSON.stringify(datas);
}
function alert(msg, fn, style, sec){
	style = style||'success';
	if(typeof(fn) == 'string'){
		style = fn;
	}
	if(!sec){
		if(style == 'error' || style == 'puncherror'){
			sec = 9;
			// sec = 0;
		}else{
			sec = 0;
		}
	}
	var box = $('<div>').addClass('resourceBox ' + ' ' + style).attr('id', 'alertBox');
	box.html('<div class="context">' + msg + '</div>');
	box.appendTo('body');
	var h = document.body.offsetWidth / 360 * 100;
	// box.css({'opacity':1, 'margin-top':-1 * (box.height()+h)/2});
	if(sec >= 9){
		var alertBoxLay = $('<div>').addClass('alertBoxLay').appendTo('body');
		$('<a>').attr('href', 'javascript:void(0);').addClass('closed').appendTo(box).text('我知道了');
		$('#alertBox a.closed, .alertBoxLay').click(function(){
			box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
			alertBoxLay.css('opacity', 0);
			setTimeout(function(){
				box.remove();
				alertBoxLay.remove();
				if(typeof(fn) == 'function')fn();
			}, 1000);
		});
	}else{
		setTimeout(function(){
			// box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
			box.remove();
			if(typeof(fn) == 'function')fn();
		}, 1400);
	}
}
// function sharer(title, desc, link, imgUrl){
//     console.log(datas.title);
//     wechat.onMenuShareTimeline({
//         title: title, // 分享标题
//         link: link, // 分享链接
//         imgUrl: imgUrl
//     });
//     wechat.onMenuShareAppMessage({
//         title : title,
//         desc : desc,
//         link : link,
//         imgUrl : imgUrl
//     });
// }

