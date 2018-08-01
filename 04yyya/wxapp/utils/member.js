
/***************************收藏、关注 算法 之 关注*******************************/
function setFollow(em, fn){
	if(member == null){
		var p = page.names.join('-');
		for(var i=page.names.length-1; i >= 0; i++){
			if(page.names[i] == 'tipsDetail'){
				window.sessionStorage.setItem('jumpUrl', 'page='+ p +'&tips_id=' + win.get.tips_id);
				break;
			}
			if(page.names[i] == 'daRen'){
				window.sessionStorage.setItem('jumpUrl', 'page='+ p +'&daRen_id=' + win.get.member_id);
				break;
			}
		}

		$.dialog("尚未登录！您要现在登录吗？", function(){
			script.load('member');
		});
		return;
	}

	var id = $(em).attr('data');
	var type = $(em).hasClass('valued') ? 0 : 1;
	if(!type){
		$.dialog('您确定要取消关注这位主厨达人吗?', function(){
			ajax('Member/Follow/changeFollow', {member_id:id, type:type}, function(d){
				if(d.status == 1){
					if(!$(em).hasClass('valued')){
						$(em).addClass('valued');
						var num = parseInt($(em).parent().siblings().find('.fanes').text());
						$(em).parent().siblings().find('.fanes').text(num+1);
					}else{
						var num = parseInt($(em).parent().siblings().find('.fanes').text());
						$(em).removeClass('valued');
						$(em).parent().siblings().find('.fanes').text(num-1);
					}
					if(typeof(fn) == 'function')fn($(em).hasClass('valued')?1:0);
				}else{
					$.alert(' 操作失败', 'error');
				}
			});
		});
	}else{
		ajax('Member/Follow/changeFollow', {member_id:id, type:type}, function(d){
			if(d.status == 1){
				if(!$(em).hasClass('valued')){
					$(em).addClass('valued');
					var num = parseInt($(em).parent().siblings().find('.fanes').text());
					$(em).parent().siblings().find('.fanes').text(num+1);
				}else{
					var num = parseInt($(em).parent().siblings().find('.fanes').text());
					$(em).removeClass('valued');
					$(em).parent().siblings().find('.fanes').text(num-1);
				}
				if(typeof(fn) == 'function')fn($(em).hasClass('valued')?1:0);
			}else{
				$.alert(' 操作失败', 'error');
			}
		});
	}

}

/***************************收藏、关注 算法 之 收藏*******************************/
function setCollect(em, fn, tp){
	if(typeof(fn) == 'number')tp = fn;
	if(member == null){
		var p = page.names.join('-');
		if(page.names.indexOf('tipsDetail') > 0)
			window.sessionStorage.setItem('jumpUrl', 'page='+ p +'&tips_id=' + win.get.tips_id);
		else if(page.names.indexOf('goodsDetail') > 0)
			window.sessionStorage.setItem('jumpUrl', 'page='+ p +'&goods_id=' + win.get.goods_id);
		else if(page.names.indexOf('raiseDetail') > 0)
			window.sessionStorage.setItem('jumpUrl', 'page='+ p +'&raise_id=' + win.get.raise_id);
		$.dialog("尚未登录！您要现在登录吗？", function(){
			script.load('member');
		});
		return;
	}
	var id = $(em).attr('data');
	var type = $(em).hasClass('Collected') ? 0 : 1;
	if(!type){
		$.dialog('您确定要取消收藏此活动吗?', function(){
			ajax('Member/Follow/ChangeCollect', {type:tp ,type_id:id, operate:type}, function(d){
				if(d.status == 1){
					if($(em).hasClass('Collected')){
						$(em).removeClass('Collected');
					}
					if(typeof(fn) == 'function')fn($(em).hasClass('Collected')?1:0);
				}else{
					$.alert(' 操作失败', 'error');
				}
			});
		});
	}else{
		ajax('Member/Follow/ChangeCollect', {type:tp ,type_id:id, operate:type}, function(d){
			if(d.status == 1){
				if(!$(em).hasClass('Collected')){
					$(em).addClass('Collected');
				}
				if(typeof(fn) == 'function')fn($(em).hasClass('Collected')?1:0);
			}else{
				$.alert(' 操作失败', 'error');
			}
		});
	}

}

/***************当前页面 只有一个选择框的时候****************/
function showShareBox(){
	$('<div>').addClass('shareBox').html('<p>点击右上角<i class="share_timg"></i>分享给朋友</p><span class="share_know">我知道了</span>').appendTo('body').click(function(){
		$(this).remove();
		$(this).children('font').click(function(){
			$('.shareBox').remove();
		});
	});
}

//微信支付
function wxpay(order_id, type){
	type = type||0;
	ajax('Order/Pay/submit', {'order_id':order_id, 'type':2}, function(d){
		if(d.status == 1){
			if(d.info.sign){
				wechat.chooseWXPay({
					timestamp: d.info.timeStamp,
					nonceStr: d.info.nonceStr,
					package: d.info.package,
					signType: d.info.signType,
					paySign: d.info.sign,
					success: function (res) {
						if(res.errMsg == 'chooseWXPay:ok'){
							$.alert('支付成功', function(){
								if(type == 0)
									jump('choice-ucenter-myOrder-orderDetail', {order_id:order_id});
								else if(type == 1)
									jump('choice-ucenter-myGoodsOrder-orderGoodsDetail', {order_id:order_id});
								else if(type == 2)
									jump('myRaiseOrder-orderRaiseDetail', {order_id:order_id});
							});
						}
					}
				});
			}else{
				$.alert('支付成功', function(){
					if(type == 0)
						jump('choice-ucenter-myOrder-orderDetail', {order_id:order_id});
					else if(type == 1)
						jump('choice-ucenter-myGoodsOrder-orderGoodsDetail', {order_id:order_id});
					else if(type == 2)
						jump('myRaiseOrder-orderRaiseDetail', {order_id:order_id});
				});
			}
		}else{
			if(d.info == 'open_id_is_null'){
				$.dialog('尚未获得授权!是否现在授权?', function(){
					ajax('Home/Wx/getOauthUrl', function(d){
						if(typeof(d) == 'string'){
							if(window.sessionStorage){
								if(type == 0)
									window.sessionStorage.setItem('jumpUrl', 'page=choice-ucenter-myOrder-orderDetail&order_id=' + order_id);
								else
									window.sessionStorage.setItem('jumpUrl', 'page=choice-ucenter-myGoodsOrder-orderGoodsDetail&order_id=' + order_id);
							}
							window.location.href = d;
						}
					});
				});
			}else{
				$.alert(d.info, 'error');
			}
		}
	});
}

function report(type_id, em){
	var code = '<label>举报内容</label><textarea placeholder="输入您要举报的内容…"></textarea>';
	$.dialog(code, function(){
		alert($('#dialogBox.reportBox textarea').val());
		var data = {};
		data.type = 3;
		data.type_id = type_id;
		data.content = $('#dialogBox.reportBox textarea').val();

		ajax('home/index/exception', data, function(d){
			if(d.status == 1){
				$(em).html('[已举报]').removeAttr("onclick");
			}
			alert(d.info);
		});
	}, true, 'reportBox');
}
module.exports = {
	login: login,
	setFollow: setFollow,
	setCollect: setCollect,
	wxpay: wxpay,
	showShareBox: showShareBox
}
