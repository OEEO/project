$(function(){
	var url = window.location.href;
	var arr = url.split('?');
	var data = {};
	if(arr.length > 1){
		var get = arr[1].decodeURL();
		if(get.coupon_id){
			coupon_id = get.coupon_id;
		}
		if(get.tips_id){
			tips_id = get.tips_id;
			data.tips_id = tips_id;
		}
		if(get.goods_id){
			goods_id = get.goods_id;
			data.goods_id = goods_id;
		}
		if(get.price){
			min_price = parseFloat(get.price);
		}
	}
	
	ajax('Member/Coupon/getlist', data, function(d){
		//加入结构数据
		if(d.list && d.list.length > 0){
			for(var i in d.list){
				var allow = true;
				if(tips_id){
					if(d.list[i].tips_tags == '')allow = false;
					else if(d.list[i].tips_tags != '*'){
						allow = false;
						var tips_tags = d.list[i].tips_tags.split(',');
						for(var x in tips_tags){
							for(var y in d.tips_tags){
								if(tips_tags[x] == d.tips_tags[y])allow = true;
							}
						}
					}
				}else if(goods_id){
					if(d.list[i].goods_tags == '')allow = false;
					else if(d.list[i].goods_tags != '*'){
						allow = false;
						var goods_tags = d.list[i].goods_tags.split(',');
						for(var x in goods_tags){
							for(var y in d.goods_tags){
								if(goods_tags[x] == d.goods_tags[y])allow = true;
							}
						}
					}
				}
				
				if(d.list[i].min_amount > min_price)allow = false;
				
				if(d.list[i].type == 0){
					var value = '￥' + d.list[i].value.split('.')[0];
				}else if(d.list[i].type == 1){
					var value = d.list[i].value.split('.')[0] + '%';
				}else if(d.list[i].type == 2){
					var value = '<small>赠品：' + d.list[i].value + '</small>';
				}
				
				var code = '<div class="coupon'+ (allow?'':' unavailable') +'" coupon_id="'+ d.list[i].id +'" type="'+ d.list[i].type +'">';
				code += '	<div class="couponLeft" data="'+ d.list[i].value +'">'+ value +'</div>';
				code += '	<div class="couponRight">';
				code += '		<p>'+ d.list[i].name +'</p>';
				code += '		<div>单价大于'+ d.list[i].min_amount +'元的活动可用</div>';
				code += '		<div>有效期至：</div>';
				code += '		<i>'+ d.list[i].end_time.timeFormat('Y年m月d日') +'</i>';
				code += '	</div>';
				if(d.list[i].end_time < (new Date()).getTime() / 1000)
					code += '	<div class="overdue true"></div>';
				else
					code += '	<div class="overdue"></div>';
				if(d.list[i].id == coupon_id)
					code += '	<div class="icon choosed"></div>';
				else
					code += '	<div class="icon"></div>';
				code += '</div>';
				
				var em = $(code).appendTo('.content');
				if(allow && d.list[i].end_time > (new Date()).getTime() / 1000 && arr.length > 1){
					em.click(function(){
						$('.icon').removeClass('choosed');
						$(this).children('.icon').addClass('choosed');
						if(!parent.win.coupon)parent.win.coupon = {};
						parent.win.coupon.id = $(this).attr('coupon_id');
						parent.win.coupon.value = $(this).find('.couponLeft').attr('data');
						parent.win.coupon.type = $(this).attr('type');
						parent.page.back();
					});
				}
			}
		}
	}, false);
	
});

var tips_id = null,goods_id = null,coupon_id = null,min_price = 99999;
