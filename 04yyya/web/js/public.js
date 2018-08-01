
/***************************收藏、关注 算法 之 关注*******************************/
function setFollow(em, fn){
	if(member == null){
		var p = page.names.join('-');
		// for(var i=page.names.length-1; i >= 0; i++){
		// 	if(page.names[i] == 'tipsDetail'){
		// 		window.sessionStorage.setItem('jumpUrl', 'page='+p +'&tips_id=' + win.get.tips_id);
		// 		break;
		// 	}
		// 	if(page.names[i] == 'daRen'){
		// 		window.sessionStorage.setItem('jumpUrl', 'page='+p +'&member_id=' + win.get.member_id);
		// 		break;
		// 	}
		// }

		$.dialog("尚未登录！您要现在登录吗？", function(){
			win.login(function(){
				setFollow(em, fn);
			});
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
function setCollect(em, fn,tp){
	if(typeof(fn) == 'number')tp = fn;
	if(member == null){
		// var p = page.names.join('-');
		// if(page.names.indexOf('tipsDetail') > 0)
		// 	window.sessionStorage.setItem('jumpUrl', 'page=tipsDetail&tips_id=' + win.get.tips_id);
		// else if(page.names.indexOf('goodsDetail') > 0)
		// 	window.sessionStorage.setItem('jumpUrl', 'page=goodsDetail&goods_id=' + win.get.goods_id);
		// else if(page.names.indexOf('raiseDetail') > 0)
		// 	window.sessionStorage.setItem('jumpUrl', 'page=raiseDetail&raise_id=' + win.get.raise_id);
		$.dialog("尚未登录！您要现在登录吗？", function(){
			win.login(function(){
				setCollect(em, fn,tp);
			});
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
						$(em).siblings().removeClass('Collected');
					}
					if(typeof(fn) == 'function')fn($(em).hasClass('Collected')?1:0);
				}else{
					$.alert('操作失败', 'error');
				}
			});
		});
	}else{
		ajax('Member/Follow/ChangeCollect', {type:tp ,type_id:id, operate:type}, function(d){
			if(d.status == 1){
				if(!$(em).hasClass('Collected')){
					$(em).addClass('Collected');
					$(em).siblings().addClass('Collected');
				}
				if(typeof(fn) == 'function')fn($(em).hasClass('Collected')?1:0);
			}else{
				$.alert('操作失败', 'error');
			}
		});
	}

}

/******************↓↓ 根据屏幕分辨率，自动调整页面大小 ↓↓*********************/
$('html').attr('style', 'font-size:' + 10 * ($(window).width() / 360) +'px !important');
$(window).resize(function(){
	$('html').attr('style', 'font-size:' + 10 * ($(window).width() / 360) +'px !important');
});

$(function(){
	$('a').each(function(){
		if(this.href.indexOf('javascript:') < 0)
			this.href = 'javascript:parent.page.jump("'+ this.href +'")';
	});
});

/***************当前页面 只有一个选择框的时候****************/
function showShareBox(text){
	if(win.get.android == 1){
		console.log(JSON.stringify(win.shareData));
		console.log(android);
		console.log(android.showshare);
		var zz = android.showshare(JSON.stringify(win.shareData));
		console.log(zz);
		return;
	}
	var texts = text || '推荐给好友';
	var tip = '';
	if(text){
		var tip = '<span class="tip">Tips：请定向分享给想要邀请的对象，一个消费码只对应一位客人哦.</span>';
	}
	/*$('<div>').addClass('shareBox').html('<h3>点击右上角分享，发送本活动邀请函:</h3><p><span class="left">微信好友</span><span class="right">微信朋友圈</span></p>').appendTo('body').click(function(){
		$(this).remove();
	});*/
	$('<div>').addClass('shareBox').html('<div><p>点击右上角'+texts+'</p>'+tip+'</div><span class="share_know">我知道了</span>').appendTo('body').click(function(){
		// $(this).remove();
		$(this).children('.share_know').click(function(){
			$('.shareBox').remove();
		});
	});
	if(text){
		$('.shareBox div').css('height','auto');
		$('.shareBox p').css('background','#fff');
	}else{
		$('.shareBox div').css('height','6rem');
		$('.shareBox p').css('background','none');
	}
	var l = $('.shareBox').size();
	if(l>1){
		for(var i = 1; i < l; i++){
			$('.shareBox').eq(i).remove();
		}
	}
}
function punchShareBox(){
	$('<div>').addClass('punchShare').html('<div><p>点击右上角分享给好友</p></div><span class="punch_know">我知道了</span>').appendTo('body')
		.on('click', function(){
			$(this).remove();
		});
}
//倒计时
function DecTime(){
	clearInterval(win.payInterval);
	win.payInterval = setInterval(function(){
		try {
			var days = Math.floor(win.paytimes / 24 / 3600);
			var hours = Math.floor(win.paytimes % (24 * 3600) / 3600);
			var mins = Math.floor((win.paytimes % (24 * 3600) % 3600) / 60);
			var secs = Math.floor((win.paytimes % (24 * 3600) % 3600) % 60);

			var str = '';
			if(days > 0){
				if(parseInt(days) < 10){
					str += '0'+days + ' <small>天</small> ';
				}else{
					var d = days.toString().split('');
					str += d[0]+d[1] + ' <small>天</small> ';
				}
			}
			if(days > 0 || hours > 0){
				if(parseInt(hours) < 10){
					str += '0'+hours + ' <small>时</small> ';
				}else{
					var h = hours.toString().split('');
					str += h[0]+h[1] + ' <small>时</small> ';
				}
			}
			if(days > 0 || hours > 0 || mins > 0){
				if(parseInt(mins) < 10){
					str += '0'+mins + ' <small>分</small> ';
				}else{
					var m = mins.toString().split('');
					str += m[0]+m[1] + ' <small>分</small> ';
				}
			}
			if(days > 0 || hours > 0 || mins > 0 || secs > 0){
				if(parseInt(secs) < 10){
					str += '0'+secs + ' <small>秒</small> ';
				}else{
					var s = secs.toString().split('');
					str += s[0]+s[1] + ' <small>秒</small> ';
				}
				$('#dialogBox.payBox .remaintime').html('支付剩余时间 <font>'+str+'</font>');
				win.paytimes --;
			} else {
				clearInterval(win.payInterval);
				$('#dialogBox.payBox .remaintime').html('支付时间已过，请重新下单');
				$('#dialogBox.payBox .btns button').attr('disabled','disabled');
				$('#dialogBox.payBox .btns').css('background','#ccc');
				// page.reload();
			}
		}catch(e){
			clearInterval(win.payInterval);
		}
	}, 1000);
}

function queryStringify(param) {
	var str = '';
	for (var key in param) {
		if (param.hasOwnProperty(key)) {
			str += '&' + key + '=' + param[key];
		}
	}
	return str.slice(1);
}

//微信支付
function wxpay(order_id, price, limit_time, type, gid, snum, param){
	type = type||0;
	var groups_id = gid || '';
	var snumber = snum || '';
	var code ='';
	
	code += '<i class="clearpsd"></i>';
	
	code += '<div class="paymethod">';
	code += '<div class="paytitle">支付金额：￥<font>'+price+'</font></div>';
	code += '<div class="choicepay">选择支付方式</div>';
    code += '<div class="paytype">';
    if (isWeiXin()) {
        code += '<a class="wx" href="javascript:void(0);">';
        code += '	<span class="text">微信<small>（金额不超过2000可用）</small></span>';
        code += '	<span class="selected on"></span>';
        code += '</a>';

        code += '<a class="alipay" href="javascript:void(0);">';
		code += '	<span class="text">支付宝</span>';
		code += '	<span class="selected"></span>';
		code += '</a></div>';
    } else {
    	code += '<a class="alipay" href="javascript:void(0);">';
		code += '	<span class="text">支付宝</span>';
		code += '	<span class="selected on"></span>';
		code += '</a></div>';
    }

	code += '<div class="paytiem">';
	code += '<p class="remaintime">支付剩余时间</p>';
	// code += '<p class="overtime">(到截止时间未支付成功的订单将被取消，优先认筹权及优惠券会返还至你的账户)</p>';
	code += '</div></div>';


	//在这里传入了dialog的第四个参数，为了方便在众筹回报页弹窗关闭时会跳转至相关的订单详情页。
	$.dialog(code, function(){
		if($('#dialogBox.payBox .paytype .alipay .selected').hasClass('on')){
			var url = 'http://' + DOMAIN + '/order/pay/submitAlipay.do?token='+ win.token +'&order_id=' + order_id + '&type=' + type;
			if (param) {
				Object.prototype.toString.call(param) === '[object String]' && (param = JSON.parse(param));
				url += '&' + queryStringify(param);
			}
			window.location.href = url;
		}else if($('#dialogBox.payBox .paytype .wx .selected').hasClass('on')){
			ajax('Order/Pay/submit', {'order_id':order_id, 'type': 2}, function(d){
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
										if(type == 0){
											jump('choice-ucenter-myOrder-orderDetail', {order_id:order_id});
										} else if(type == 1){
                                            gid
												? jump('choice-ucenter-groupsDetail', {groups_id: groups_id})
												: jump('choice-ucenter-myGoodsOrder-orderGoodsDetail', {order_id:order_id});
										} else if(type == 2){
											jump('choice-ucenter-myRaiseOrder-orderRaiseDetail', {order_id: order_id, isFromPaySuccess: true});
										} else {
											if(snumber != ''){
												//开团
												jump('choice-ucenter-groupsDetail', {groups_id:groups_id,surplus_num:snumber});
											}else{
												//参团
												jump('choice-ucenter-groupsDetail', {groups_id:groups_id});
											}
										}
									});
								}
							}
						});
					}else{
						$.alert('支付成功', function(){
							if(type == 0) {
								jump('choice-ucenter-myOrder-orderDetail', {order_id: order_id});
							}
							else if(type == 1) {
								jump('choice-ucenter-myGoodsOrder-orderGoodsDetail', {order_id: order_id});
							}
							else if(type == 2) {
								jump('choice-ucenter-myRaiseOrder-orderRaiseDetail', {order_id: order_id, isFromPaySuccess: true});
							}else{
								if(snumber != ''){
									//开团
									jump('choice-ucenter-groupsDetail', {groups_id:groups_id,surplus_num:snumber});
								}else{
									//参团
									jump('choice-ucenter-groupsDetail', {groups_id:groups_id});
								}
							}
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
										else if(type == 1)
											window.sessionStorage.setItem('jumpUrl', 'page=choice-ucenter-myGoodsOrder-orderGoodsDetail&order_id=' + order_id);
										else
											window.sessionStorage.setItem('jumpUrl', 'page=choice-ucenter-myRaiseOrder-orderRaiseDetail&order_id=' + order_id);
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
	}, true, 'payBox', false);
	$('#dialogBox.payBox .btns .closeBtn').remove();
	$('#dialogBox.payBox .btns .agree').text('支付');
	//倒计时
	if(parseInt(limit_time) > Math.round((new Date()).getTime() / 1000)){
		win.paytimes = parseInt(limit_time) - Math.round((new Date()).getTime() / 1000);
		DecTime();
	}else{
		$('#dialogBox.payBox .remaintime').html('支付时间已过，请重新下单');
		$('#dialogBox.payBox .btns button').attr('disabled','disabled');
		$('#dialogBox.payBox .btns').css('background','#ccc');
	}
	//当支付金额超过2000,则引导至支付宝支付
	if(parseFloat(price) > 2000){
		$('#dialogBox.payBox .paytype a.wx').addClass('disable');
		$('#dialogBox.payBox .paytype a.alipay .selected').addClass('on');
		$('#dialogBox.payBox .paytype a.alipay ').click();
	}
	//支付方式
	$('#dialogBox.payBox .paytype a').on('click', function(){
		if(!$(this).hasClass('disable')){
			$('#dialogBox.payBox .paytype a .selected').removeClass('on');
			$(this).find('.selected').addClass('on');
		}
	});
}

//退出登录
function logout(){
	ajax('Member/Index/logout', function(d){
		if(d.status == 1){
			storage.rm('autologin');
			location.href = win.host + '?refresh=' + (new Date()).getTime();
		}
	});
}
function report(type_id, em){
	var code = '<label>举报内容</label><textarea placeholder="输入您要举报的内容…"></textarea>';
	$.dialog(code, function(){
		var data = {};
		data.type = 3;
		data.type_id = type_id;
		data.content = $('#dialogBox.reportBox textarea').val();

		ajax('home/index/exception', data, function(d){
			if(d.status == 1){
				$(em).html('[已举报]').removeAttr("onclick");
			}
			$.alert(d.info);
		});
	}, true, 'reportBox');
}