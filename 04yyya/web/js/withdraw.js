var withdrawObject = {
	canWithdraw : 0,

	submit: function () {
		var data = {};
		data.money = $('.page_withdraw .money').val();
		data.alipay = $('.page_withdraw .alipay').val();
		data.realname = $('.page_withdraw .realname').val();
		ajax('Member/Profit/checkWeekend', {}, function(d) {
			if (d == '1') {
				$.alert('周六周日不处理提现');
			} else {
				if (data.money == '' || data.alipay == '' || data.realname == '') {
					$.alert('请完善相关提现信息！');
				} else {
					if (+data.money > +withdrawObject.canWithdraw) {
						$.alert('输入金额不得大于可提现金额！');
					} else {
						ajax('Member/Profit/sendWithdraw', data, function(d){

						});
						/*
						page.back(function(){
							page.reload();
						});
						*/
						$.alert('提现申请已提交，提现成功后将于0-3个工作日内到账');
						ajax('Member/Profit/getCanWithdraw', {}, function(d) {
							if (!d.no_rs) {
								$('.p_a').text(d.profit);
								withdrawObject.canWithdraw = d.profit;
							} else {
								$('.p_a').text('0.00');
							}
							$('.page_withdraw .money').val('');
							$('.page_withdraw .alipay').val('');
							$('.page_withdraw .realname').val('');
						});					
					}
				}
			}
		});

	},


	onload: function () {
//		$.alert('dfdfdfdfdsfsdfsdf');
		ajax('Member/Profit/getCanWithdraw', {}, function(d) {
			if (!d.no_rs) {
				$('.p_a').text(d.profit);
				withdrawObject.canWithdraw = d.profit;
			} else {
				$('.p_a').text('0.00');
			}
		});
		$('.page_withdraw .wxWithdraw').click(function() {
			/*
			$('.page_withdraw .wxWithdraw').css('background','#f8f8f8');
			$('.page_withdraw .alipayWithdraw').css('background','white');
			$('.page_withdraw .ali').css('display','none');
			*/
			$.alert('微信提现升级中，请使用支付宝提现');
		});
		$('.page_withdraw .alipayWithdraw').click(function() {
			$('.page_withdraw .wxWithdraw').css('background','white');
			$('.page_withdraw .alipayWithdraw').css('background','#f8f8f8');
			$('.page_withdraw .ali').css('display','block');

		});
		$('.page_withdraw .alipayWithdraw').click();
	},

};