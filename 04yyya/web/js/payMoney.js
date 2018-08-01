var payMoneyObject = {
	order_id : null,
	//倒计时
	timeout : function (t){
		if(t <= 0){
			$('.page_payMoney .times').html('订单已失效').addClass('none');
			$('.page_payMoney .aboutPay, .page_payMoney .goodsOrderBottom').remove();
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
			$('.page_payMoney .times').html(code);
			
			window.setTimeout(function(){
				if(payMoneyObject)payMoneyObject.timeout(t - 1);
			}, 1000);
		}
	},
	submitPay : function(){},
	
};

script.load('member', function(){
	payMoneyObject.order_id = win.get.order_id;
	
	ajax('Order/Pay/index', {'order_id':payMoneyObject.order_id}, function(d){
		if(typeof(d.status) == 'number' && d.status == 0){
			payMoneyObject.timeout(0);
		} else {
			$('.page_payMoney .total span').text(d.price);
			payMoneyObject.timeout(d.remaining);
			payMoneyObject.submitPay = function (){
				ajax('Order/Pay/submit', {'order_id':payMoneyObject.order_id}, function(d){
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
											jump('choice-ucenter-myOrder-orderDetail', {order_id:payMoneyObject.order_id});
										});
									}
								}
							});
						}else{
							$.alert('支付成功', function(){
								jump('choice-ucenter-myOrder-orderDetail', {order_id:payMoneyObject.order_id});
							});
						}
					}else{
						$.alert(d.info, 'error');
					}
				});
			};
		}
	}, 2);
});

