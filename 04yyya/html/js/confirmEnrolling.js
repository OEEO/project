$(function(){
	var XX=GetHrefParameter();
	if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='tips_id')tips_id = XX[i][1];
		}
/*	if(window.location.href.indexOf('?tips_id=') == -1){
		$('body').html('非法访问！');
		return;
	};
	var tips_id = window.location.href.split('?tips_id=')[1];*/
	ajax('Order/Index/getTips', {'tips_id':tips_id}, function(d){
		//插入主图
		$('#mainpic').html('<img src="'+ d.mainpic +'">');
		//插入标题
		$('#title').text(d.title);
		//插入价格
		$('#price').text(d.price);
		//插入时间段
		for(var i in d.times){
			if(d.times[i].start_time - d.times[i].stop_buy_time < (new Date()).getTime()/1000){
				$('<div class="pass">'+ d.times[i].start_time.timeFormat('Y-m-d<br>W H:i') +'<p>已过期</p></div>').appendTo('#times');
			}else if(d.times[i].surplus == 0){
				$('<div class="pass">'+ d.times[i].start_time.timeFormat('Y-m-d<br>W H:i') +'<p>已售罄</p></div>').appendTo('#times');
			}else{
				if(typeof(cy_times) == 'undefined'){
					var cy_times = ' class="choose"';
					//限制数量
					limit = d.times[i].surplus;
				}else{
					cy_times = ' ';
				}
				$('<div'+ cy_times +' data="'+ d.times[i].id +'" surplus="'+ d.times[i].surplus +'">'+ d.times[i].start_time.timeFormat('Y-m-d<br>W H:i') +'<p>已报名'+ d.times[i].count +'人</p></div>').click(function(){
					limit = parseInt($(this).attr('surplus'));
					$('.copies').find('.b').text(1);
					$(this).siblings('.choose').removeClass('choose');
					$(this).addClass('choose');
				}).appendTo('#times');
			}
		}
		
		//插入最终价格
		$('#buy_price').text(d.buy_price);
		buy_price = d.buy_price;
		
		//插入可用优惠券
		if(typeof(d.allow_coupon) == 'undefined' || d.allow_coupon == 1){
			if(d.coupon){
				couponInput(d.coupon.id, d.coupon.type, d.coupon.value, d.coupon.content);
				//选择优惠券
				$$(function(){
					if(parent.win.coupon){
						couponInput(parent.win.coupon.id, parent.win.coupon.type, parent.win.coupon.value, parent.win.coupon.content);
						delete parent.win.coupon;
					}
				});
				$('.coupon').click(function(){
					jump('myCoupon.html?tips_id=' + tips_id + '&coupon_id=' + coupon_id + '&price=' + buy_price);
				});
			}else{
				$('#coupon_price').text('没有可用优惠券');
			}
		}else{
			$('#coupon_price').parents('.li_item').remove();
		}
		//插入绑定手机
		$('#telephone').text(d.telephone);
		//插入客服手机号
		$('.contactCustomerService').click(function(){
			alert('客服电话：' + d.tel);
		});
		
		//提交按钮绑定时间
		$('#submitBtn').click(function(){
			if($('#times .choose').size() == 0){
				alert('请选择您要参与的时间段！');
				return false;
			}
			var data = {};
			data.tips_id = tips_id;
			data.times_id = $('#times .choose').attr('data');
			if(coupon_id != null)data.coupon_id = coupon_id;
			
			//提交订单
			ajax('Order/Index/create', data, function(d){
				if(d.status == 1){
					jump('payMoney.html?order_id=' + d.info.order_id);
				}else{
					alert(d.info);
				}
			}, false);
		});
	});
});

var tips_id = null;
var limit = 0;
var coupon_id = null;
var buy_price = null;

function couponInput(id, type, value, content){
	coupon_id = id;
	value = parseFloat(value);
	buy_price = parseFloat(buy_price);
	if(type == 0){
		$('#coupon_price').text("- ￥" + value);
		$('#buy_price').text(Math.round((buy_price - value) * 100) / 100);
	}else if(type == 1){
		$('#coupon_price').text(value / 10 + '折');
		$('#buy_price').text(buy_price * value / 100);
	}else if(type == 2){
		$('#coupon_price').text("赠品：" + content);
	}
}


function vals(em){
	if(!/^\d+$/.test($(em).text())){
		$(em).text(1);
		return;
	}
	var currentVal = parseInt($(em).text());
	if(currentVal > limit){
		$(em).text(limit);
	}else if(currentVal < 1){
		$(em).text(1);
	}
}

function changeCopies(em,num){
	var currentVal=parseInt($(em).parent().children('.b').text());
	if(currentVal<=1&&num=='-1')num=0;
	if(currentVal>=limit&&num=='1')num=0;
	currentVal+=num;
	$(em).parent().children('.b').text(currentVal);
}