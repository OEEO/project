
var theme_id = null, page = 1, sock = false;

$(function(){
	var arr = window.location.href.split('?');
	if(arr.length > 1){
		arr = arr[1].split('|');
		if(arr.length > 1){
			theme_id = arr[0].split('=')[1];
			$('.header .title').html(decodeURI(arr[1]));
			if(/^\d+$/.test(theme_id)){
				tips_load();
			}
		}else{
			$('body').html('非法访问！');
		}
	}else{
		$('body').html('非法访问！');
	}
	
});

$(window).scroll(function(){
	//判断是否滚动到底部
	if($(document).scrollTop()>=$(document).height()-$(window).height() && !sock) {
		sock = true;
		page++;
		tips_load();
	}
});

function tips_load(){
	if($('.the_end').html() == 'The End!!!')return;
	ajax('Home/Index/tips', {get : {'page' : page}, post : {'theme_id' : theme_id}}, function(d){
		$('.the_end').remove();
		if(d.length > 0){
			var code = '';
			for(var i in d) {
				code += '<div class="userHeadImg" style="height:4rem;">';
				code += '<img class="imgPortrait" src="'+ d[i].headpic.pathFormat() +'">';
				code += '<div class="userHeadName">' + d[i].nickname + '</div>';
				code += '</div>';
				code += '<a class="actImgLink" href="javascript:void(0);" data-url="">';
				code += '<img width="100%" src="' + d[i].path.pathFormat() + '" alt="Heineken"/>';
				code += '<div class="orderLast">剩余' + d[i].amount + '份</div>';
				code += '</a>';
				code += '<div class="actBottom">';
				code += '<div class="actTitle" data-url="#">' + d[i].title + '</div>';
				code += '<div class="actAdd">' + d[i].address + '</div>';
				code += '<div class="actTime">' + d[i].start_time.timeFormat('m.d W H:i') + '</div>';
				code += '<div class="clearfix"></div>';
				code += '<div class="mtags">';
				for (var j in d[i].tags) {
					code += '<span>' + d[i].tags[j] + '</span>';
				}
				code += ' <div class="actPrice">'+d[i].price+'<i>元</i></div>';
				code += '</div>';
				code += '</div>';
				code += '<div class="the_blank"></div>';
				code += '<div class="the_end"></div>';
			}
			$('.content').append(code);
		}
		if(d.length == 5){
			$('.content').append('<div class="the_end">loading...</div>');
		} else {
			$('.content').append('<div class="the_end">The End!!!</div>');
		}
		sock = false;
	});
}