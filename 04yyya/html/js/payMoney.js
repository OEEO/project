$(function(){
	var XX=GetHrefParameter();
	if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='order_id')order_id = XX[i][1];
		}
		
/*	if(window.location.href.indexOf('?order_id=') == -1){
		$('body').html('非法访问！');
		return;
	}
	order_id = window.location.href.split('?order_id=')[1];
	*/
	ajax('Order/Pay/index', {'order_id':order_id}, function(d){
		if(typeof(d.status) == 'number' && d.status == 0){
			timeout(0);
		} else {
			$('.total span').text(d.price);
			timeout(d.remaining);
			
			$('.goodsOrderBottom').click(function(){
				ajax('Order/Pay/submit', {'order_id':order_id}, function(d){
					if(d.status == 1){
						parent.wechat.chooseWXPay({
							timestamp: d.info.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
							nonceStr: d.info.nonceStr, // 支付签名随机串，不长于 32 位
							package: d.info.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
							signType: d.info.signType, // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
							paySign: d.info.sign, // 支付签名
							success: function (res) {
								if(res.errMsg == 'chooseWXPay:ok'){
									alert('支付成功！');
									parent.page.jump('ucenter-myOrder-orderDetail.html?order_id=' + order_id);
								}
							}
						});
						
						/*parent.WeixinJSBridge.invoke(
							'getBrandWCPayRequest', {
								"appId" ： d.info.appId,     //公众号名称，由商户传入     
								"timeStamp"：d.info.timeStamp,         //时间戳，自1970年以来的秒数     
								"nonceStr" ： d.info.nonceStr, //随机串     
								"package" ： d.info.package,     
								"signType" ： d.info.signType,         //微信签名方式：     
								"paySign" ： d.info.sign //微信签名 
							},
							function(res){     
								if(res.err_msg == "get_brand_wcpay_request：ok" ) {
									alert('支付成功！');
								}else{
									alert(res.err_msg);
								}
							}
						); */
					}
				});
			});
		}
	}, false);
});

var order_id = null;

//倒计时
function timeout(t){
	if(t <= 0){
		$('.times').html('订单已失效').addClass('none');
		$('.aboutPay, .goodsOrderBottom').remove();
	}else{
		var m = Math.floor(t / 60);
		var s = t % 60;
		var code = '';
		if(m > 0){
			code += m + '<small>分</small>';
		}
		if(s > 9){
			code += s + '<small>秒</small>';
		}else{
			code += '0' + s + '<small>秒</small>';
		}
		$('.times').html(code);
		
		window.setTimeout(function(){
			timeout(t - 1);
		}, 1000);
	}
}
