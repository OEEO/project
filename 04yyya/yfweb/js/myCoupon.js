var myCouponObject = {
	tips_id : null,
	goods_id : null,
	coupon_id : null,
	min_price : 99999,
	onload : function(){
		for(var i in win.get){
			myCouponObject[i] = win.get[i];
		}

		ajax('Member/Coupon/getlist', {tips_id:myCouponObject.tips_id, goods_id:myCouponObject.goods_id}, function(d){
			if(d.info){
				$.alert(d.info, 'error');
				return;
			}
			//加入结构数据
			if(d && d.length > 0){
				var code1 = [],code2 = [],code3 = [];
				for(var i in d){
					var arr = d[i].value.split('.');
					if(d[i].type == 0){
						var value = '<font>￥</font>' + arr[0] + '.<small>'+ arr[1] +'</small>';
					}else if(d[i].type == 1){
						var value = d[i].value.split('.')[0] + '.<small>'+ arr[1] +'</small>' + '%';
					}else if(d[i].type == 2){
						var value = '<small>赠品：' + d[i].value + '</small>';
					}

					var codes = '<div class="'+ (d[i].id == myCouponObject.coupon_id?' choosed':'') +'" coupon_id="'+ d[i].id +'" type="'+ d[i].type +'">';
					codes += '	<div class="couponLeft" data="'+ d[i].value +'">'+ value +'</div>';
					codes += '	<p class="type_title">'+ d[i].name +'</p>';
					codes += '	<div class="couponRight">';
					codes += '		<div>订单金额高于'+ d[i].min_amount +'元可用</div>';
					codes += '		<i>'+ d[i].end_time.timeFormat('Y年m月d日') +' 到期</i>';
					codes += '	</div>';

					if(d[i].id == myCouponObject.coupon_id)
						codes += '	<div class="icon choosed"></div>';
					else
						codes += '	<div class="icon"></div>';
					codes += '</div>';
					if(d[i].end_time < (new Date()).getTime() / 1000) {
						code3.push(codes);
					}else {
						if(d[i].can_use == 1 && d[i].min_amount <= myCouponObject.min_price){
							code1.push(codes);
						}else{
							code2.push(codes);
						}
					}
				}
				$('.page_myCoupon .content .Not_expired .canUser font').text(code1.length);
				$('.page_myCoupon .content .Not_expired .list').html(code1.join(''));
				$('.page_myCoupon .content .Not_expired .list > div').addClass('coupon').click(function(){
					$('.page_myCoupon .icon').removeClass('choosed');
					$(this).children('.icon').addClass('choosed');
					if(!parent.win.coupon)parent.win.coupon = {};
					parent.win.coupon.id = $(this).attr('coupon_id');
					parent.win.coupon.value = $(this).find('.couponLeft').attr('data');
					parent.win.coupon.type = $(this).attr('type');
					parent.win.coupon.name = $(this).find('.type_title').text();
					parent.page.back();
				});

				$('.page_myCoupon .content .canot_use .canUser font').text(code2.length);
				$('.page_myCoupon .content .canot_use .list').html(code2.join(''));
				$('.page_myCoupon .canot_use .list > div').addClass('coupon');

				$('.page_myCoupon .content .timeout .canUser font').text(code3.length);
				$('.page_myCoupon .content .timeout .list').html(code3.join(''));
				$('.page_myCoupon .content .timeout .list > div').addClass('overdue');
			}else{
				$('.page_myCoupon .content').text('抱歉！您还没有优惠券~').addClass('empty');
			}
		}, 2);
	},
	onshow:function(){
		$('.page_myCoupon .serch_buttom').click(function(){
			var code = $(this).prev('.catch').find('input').val();
			if(!/^\d{12}$/){
				$.alert('优惠券是一串12位的数字', 'error');
				return;
			}
			ajax('Member/Coupon/getCoupon', {sn : code}, function(d){
				if(d.status == 1){
					$.alert('兑换成功', function(){
						page.reload();
					});
				}else{
					$.alert(d.info, 'error');
				}
			}, 2);
		});
	}
};
