var orderGoodsDetailObject = {
	order_id : null,
	check_code : [],
	num : 0,
	number:1,//二维码序号
	num_i:0,//二维码总数
	//确认收货
	ConfirmReceipt : function(){
		ajax('Member/Order/ConfirmReceipt', {order_id : orderGoodsDetailObject.order_id}, function(d){
			if(d.status == 1){
				$.alert('确认成功', function(){
					page.reload();
				});
			}else{
				$.alert(d.info, 'error');
			}
		}, 2);
	},
	//返回
	back : function(){
		page.back(function(){
			myGoodsOrderObject.loadOrder(1);
		});
	},
	onload : function(){
		if(!member){
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
			return;
		}

		orderGoodsDetailObject.order_id = win.get.order_id;

		ajax('Member/Order/getDetail', {order_id : orderGoodsDetailObject.order_id}, function(d){
			var $em = $('.page_orderGoodsDetail');
			var status = ['待付款', '待发货', '已发货', '待评价', '已完成', '退款中', '已退款', '已取消'];

			if(d.act_status == 0){
				$em.find('.orderOperation').text('取消订单').click(function(){
					ajax('Member/Order/cancel', {order_id : orderGoodsDetailObject.order_id}, function(d){
						if(d.status == 1){
							$.alert('成功取消订单', function(){
								page.reload();
							});
						}else{
							$.alert(d.info, 'error');
						}
					});
				});

				$('.page_orderGoodsDetail.orderBottom').text('立即付款').click(function(){
					wxpay(orderGoodsDetailObject.order_id, 1);
				});

			}else if(d.act_status == 1){
				$em.find('.orderOperation').empty();

				$('.page_orderGoodsDetail.orderBottom').text('邀请好友').click(function(){
					//分享绑定
					var desc = d.title;
					var url = win.host + '?page=choice-goodsDetail&goods_id=' + d.id;
					if(member && member.invitecode){
						url += '&invitecode=' + member.invitecode;
					}
					share(d.title, desc, url, d.path);

					showShareBox();
				});
			}else if(d.act_status == 2){
				$('.page_orderGoodsDetail.orderBottom').text('确认收货').click(function(){
					orderGoodsDetailObject.ConfirmReceipt();
				});
			}else if(d.act_status == 3){
				$('.page_orderGoodsDetail.orderBottom').text('立即评价').click(function(){
					jump('evaluationStar', {order_id : orderGoodsDetailObject.order_id});
				});
			}else if(d.act_status == 4){
				$('.page_orderGoodsDetail.orderBottom').hide().empty().addClass('empty');
				//评论列表
				var code = '';
				if(d.comment){
					code += '<div class="com_list">';
					code += '	<div class="h_pic">';
					code += '		<img src="'+ member.path +'" />';
					code += '	</div>';
					code += '	<div class="pic_right">';
					code += '		<div class="name_title">';
					code += '			<div class="names">'+ member.nickname +'</div>';
					code += '			<span>'+ d.comment.datetime +'</span>';
					code += '		</div>';
					code += '<p align="center" class="t_content">'+ d.comment.content +'</p>';
					if(d.comment.pics && d.comment.pics.length > 0){
						code += '<div class="imges">';
						for(var j in d.comment.pics){
							code += '<img src="'+ d.comment.pics[j] +'">';
						}
						code += '</div>';
					}
					code += '</div>';
					code += '</div>';
					$('.page_orderGoodsDetail .commentList').html(code).show();
				}
			}else if(d.act_status == 5){
				$em.find('.refund').text('取消退款').click(function(){
					ajax('Member/Order/cancelRefund', {order_id : orderGoodsDetailObject.order_id}, function(d){
						if(d.status == 1){
							$.alert('操作成功', function(){
								page.reload();
							});
						}else{
							$.alert(d.info, 'error');
						}
					});
				}).show();
				$('.page_orderGoodsDetail.orderBottom').hide().empty().addClass('empty');
			}else{
				$('.page_orderGoodsDetail.orderBottom').hide().empty().addClass('empty');
			}


			//查看消费码
			if(d.type == 0 && d.act_status > 0 && d.act_status < 7){
				var check_code = [];
				for(var n in d.check_code){
					if(check_code.indexOf(d.check_code[n]) == -1)check_code.push(d.check_code[n]);
				}
				var code = '';
				for(var i in check_code){
					if(code.indexOf(d.check_code[i].code) > 0)continue;
					if(d.check_code[i].status == 0){
						orderGoodsDetailObject.check_code.push(d.check_code[i].code);
						code += '<li>'+ d.check_code[i].code +'<span style="color:#000;">未验证</span></li>';
					} else
						code += '<li><del>'+ d.check_code[i].code +'</del><span>已验证</span></li>';
				}
				$em.find('.consumerCode .consumerCodeList').html(code);
				$em.find('.consumerCode').show();
			}

			//达人信息
			$em.find('.actInfDaren span').text(d.nickname);
			$em.find('.actInfconnect').text(d.catname);

			//商品信息
			$('<img>').attr('src', d.path.pathFormat()).appendTo($em.find('.orderListLeft'));
			$em.find('.orderListRight .orderTitle').text(d.title);
			$em.find('.orderListRight .number font').html(d.count);
			$em.find('.orderListRight .total font').text('￥'+ d.price);
			$em.find('.orderListRight .status').text(typeof(status[d.act_status]) == 'object' ? status[d.act_status][d.type] : status[d.act_status]);

			var code = '';
			code += '<div class="split"></div>';
			code += '<li>订单编号<span>'+ d.sn +'</span></li>';
			code += '<li>下单时间<span>'+ d.create_time.timeFormat('Y-m-d H:i:s') +'</span></li>';
			code += '<li>订单总额<span class="add_color">￥'+ parseFloat(d.total).priceFormat() +'</span></li>';
			code += '<li>运费<span>￥'+ parseFloat(d.postage).priceFormat() +'</span></li>';
			if(d.coupon) {
				if (d.coupon.type == 0) {
					code += '<li>优惠<span>￥' + parseFloat(d.coupon.value).priceFormat() + '</span></li>';
				} else if (d.coupon.type == 1) {
					code += '<li>优惠<span>' + d.coupon.value / 100 + '%</span></li>';
				}
			}

			code += '<div class="split"></div>';
			code += '<li>收货人<span>'+ d.linkman +'</span></li>';
			code += '<li>收货手机<span>'+ d.telephone +'</span></li>';
			code += '<li>收货地址<span>'+ d.province_name + d.province_alt + d.city_name + d.city_alt + d.area_name + d.area_alt + d.address +'</span><div class="clearfix"></div></li>';
			code += '<div class="split"></div>';
			code += '<li>留言<span>'+ d.context +'</span><div class="clearfix"></div></li>';

			$em.find('.orderDetail').html(code);

            $('.page_orderGoodsDetail .orderInf').click(function () {
                jump('goodsDetail', {goods_id: d.goods_id});
            });

		});
	}
};


