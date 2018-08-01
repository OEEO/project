var myGoodsOrderObject = {
	check_data : [],
	check_code : [],
	num : 0,
	page : 1,
	number:1,//二维码序号
	num_i:0,//二维码总数
	act_status : null,
	locked : false,
	cancelOrder : function(order_id){
		$.dialog('是否确定要取消该订单?', function(){
			ajax('Member/Order/cancel', {order_id : order_id}, function(d){
				if(d.status == 1){
					$.alert('成功取消订单', function(){
						page.reload();
					});
				}else{
					$.alert(d.info, 'error');
				}
			});
		});
	},

	modifyOrder : function(order_id){

	},
	//追加评论
	comment : function(order_id){
		jump('evaluationStar', {order_id : order_id});
	},
	//取消退款
	cancelRefund : function(order_id){
		ajax('Member/Order/cancelRefund', {order_id : order_id}, function(d){
			if(d.status == 1){
				$.alert('操作成功', function(){
					page.reload();
				});
			}else{
				$.alert(d.info, 'error');
			}
		});
	},
	//查看物流
	logistics : function(order_id){
		jump('getLogistics', {order_id: order_id});
	},
	//确认收货
	ConfirmReceipt : function(order_id){
		ajax('Member/Order/ConfirmReceipt', {order_id : order_id}, function(d){
			if(d.status == 1){
				$.alert('确认成功', function(){
					myGoodsOrderObject.loadOrder(1);
				});
			}else{
				$.alert(d.info, 'error');
			}
		}, 2);
	},
	loadOrder : function(page){
		var data = {
			get:{page:page||myGoodsOrderObject.page}
		};
		data.post = {type:1};
		if(myGoodsOrderObject.act_status){
			data.post.act_status = myGoodsOrderObject.act_status;
		}
		$('.page_myGoodsOrder center').show();
		ajax('Member/Order/index', data, function(d){
			$('.page_myGoodsOrder center').hide();
			win.close_loading();
			if(d.length > 0){
				var code = '';
				var status = ['待付款', '待发货', '待收货', '待评价', '已完成', '退款中', '已退款', '已取消', '退款中'];
				for(var i in d){
					code += '<li>';
					code += '	<a class="top" href="javascript:jump(\'orderGoodsDetail\',{order_id:'+ d[i].id +'})">';
					code += '		<div class="left">';
					code += '			<div class="type">'+ (d[i].catname||'商品') +'</div>';
					code += '			<img src="'+ d[i].path.pathFormat() +'">';
					code += '		</div>';
					code += '		<div class="right">';
					code += '			<div class="t">'+ d[i]['title'] +'</div>';
					if(d[i].type == 0){
						if(d[i].start_time)
							code += '			<div class="c">'+ d[i].start_time.timeFormat('m-d W H:i') +'-'+ d[i].end_time.timeFormat('H:i') +'</div>';
					}else{
						if(d[i].postage == 0){
							code += '			<div class="c">邮费：免运费</div>';
						}else{
							code += '			<div class="c">邮费：￥'+ d[i].postage + '</div>';
						}
					}
					code += '			<div class="b">';
					code += '				<div class="l"><span>'+ d[i].count +'</span>份</div><div class="cc"></div>';	
					code += '				<div class="r">总价：<span><font class="caodan">￥</font><font>'+ d[i].price +'</font></span></div>';
					code += '			</div>';
					code += '		</div>';
					code += '	</a>';
					code += '	<div class="bottom">';
					code += '		<div class="status">'+ (typeof(status[d[i].act_status])=='string'?status[d[i].act_status]:status[d[i].act_status][d[i].type]) +'</div>';

					if(d[i].act_status == 0){
						if(parseInt(d[i].price) > 2000 || win.get.android){
							var url = 'http://' + DOMAIN + '/order/pay/submitAlipay.do?token='+ win.token +'&order_id=' + d[i].id;
							code += '		<a href="'+url+'" class="btn">立即付款</a>';
						}else{
							code += '		<a href="javascript:wxpay('+ d[i].id +',' + d[i].price + ',' + d[i].limit_time + ',' + d[i].type + ')" class="btn">立即付款</a>';
						}
						code += '		<a href="javascript:myGoodsOrderObject.cancelOrder('+ d[i].id +')" class="btn">取消订单</a>';
					}else if(d[i].act_status == 1){
						//code += '		<a href="javascript:myGoodsOrderObject.modifyOrder('+ d[i].id +');" class="btn">修改备注</a>';
						if (d[i].is_piece == 1) {
							code += '<a href="javascript:jump(\'groupsDetail\',{type: 1, groups_id:' + d[i].piece_info.piece_originator_id + '});" class="btn">组团详情</a>';
						}
					}else if(d[i].act_status == 2){
						code += '		<a href="javascript:myGoodsOrderObject.ConfirmReceipt('+ d[i].id +');" class="btn blue">确认收货</a>';
						code += '		<a href="javascript:myGoodsOrderObject.logistics('+ d[i].id +');" class="btn">查看物流</a>';
					}else if(d[i].act_status == 3 && d[i].comment_id == null){
						code += '		<a href="javascript:myGoodsOrderObject.comment('+ d[i].id +');" class="btn primary">立即评论</a>';
                        code += '		<a href="javascript:myGoodsOrderObject.logistics('+ d[i].id +');" class="btn">查看物流</a>';
					}else if(d[i].act_status == 5){
						code += '		<a href="javascript:myGoodsOrderObject.cancelRefund('+ d[i].id +');" class="btn">取消退款</a>';
					}

					code += '	</div>';
					code += '</li>';
				}
			}else{
				if(page==1)
				var code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
				else
				var code = '<div class="no_more"></div>';
			}
			if(page == 1)
				$('.page_myGoodsOrder .content').html(code);
			else
				$('.page_myGoodsOrder .content').append(code);
			myGoodsOrderObject.locked = false;
		}, 2);
	},
	onload : function(){
		$('.page_myGoodsOrder .views').click(function(e){
			e.stopPropagation();
		});

		$('.page_myGoodsOrder').scroll(function(){
			if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !myGoodsOrderObject.locked){
				myGoodsOrderObject.locked = true;
				var page = Math.floor($('.page_myGoodsOrder .content > li').size() / 5) + 1;
				if(page > myGoodsOrderObject.page && $('.page_myGoodsOrder .content center').size() == 0){
					myGoodsOrderObject.page = page;
					myGoodsOrderObject.loadOrder();
				}
			}
		});

		myGoodsOrderObject.loadOrder(1);
		$('.page_myGoodsOrder .statu').click(function(){
			$(this).addClass('add_hei').siblings().removeClass('add_hei');
			if($(this).attr('act_status')){
				myGoodsOrderObject.act_status = $(this).attr('act_status');
			}else{
				myGoodsOrderObject.act_status = null;
			}
			$('.page_myGoodsOrder .content').empty();
			myGoodsOrderObject.loadOrder(1);
		});
	}
};
