var myOrderObject = {
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
	//显示消费码
	showCode : function (order_id){
		myOrderObject.check_code = [];
		for(var i in myOrderObject.check_data[order_id]){
			if(myOrderObject.check_data[order_id][i].status == 0)
				myOrderObject.check_code.push(myOrderObject.check_data[order_id][i].code);
		}

		if(myOrderObject.check_code.length <= 0){
			$.alert('没有未参加的活动消费码', 'error');
			return;
		}

		$('.page_myOrder .QRcode').fadeIn('fast');
		$('.page_myOrder .content').addClass('mask');

		var top = ($(window).height()-$('.page_myOrder .QRcode .views').height())/3;
		var closedTop =$('.page_myOrder .QRcode .views').height() + top;
		$('.page_myOrder .QRcode .closed').css("top",closedTop);
		$('.page_myOrder .QRcode .views').css({'margin-top':top});

		var code = '<ul>';
		var check_code = [];
		for(var i in myOrderObject.check_code){
			code += '<li><p>'+ myOrderObject.check_code[i] +'</p><span data="'+ myOrderObject.check_code[i] +'"></span></li>';
			check_code.push(myOrderObject.check_code[i]);
			myOrderObject.num_i++;
		}
		code += '</ul>';
		code +='<div class="num"><font>'+ myOrderObject.number +'</font>/'+ myOrderObject.num_i +'</div>';

		$('.page_myOrder .views .d').html(code);

		$('.page_myOrder .QRcode .views ul').width($('.page_myOrder .QRcode .views .d').width() * myOrderObject.check_code.length);

		//长连接验证消费码
		//ajax('Member/Order/checkCode', {'code':check_code.join(',')}, function(d){
		//	if(d.status == 1) {
		//		alert('核验成功!');
		//		page.reload();
		//	}else if(d.status == 2){
		//		$('.page_myOrder .QRcode').fadeOut('fast');
		//		$('.page_myOrder .content').removeClass('mask');
		//		document.body.style.overflow='visible';
		//		document.ontouchmove = function(e){} //文档禁止 touchmove事件
		//	}else{
		//		alert(d.info);
		//		$('.page_myOrder .QRcode').fadeOut('fast');
		//		$('.page_myOrder .content').removeClass('mask');
		//		document.body.style.overflow='visible';
		//		document.ontouchmove = function(e){} //文档禁止 touchmove事件
		//	}
		//}, 3);

		//制作二维码
		$(".page_myOrder .views .d span").each(function(){
			$(this).qrcode({
				render: 'canvas',
				minVersion: 1,
				maxVersion: 40,
				ecLevel: 'L',
				left: 0,
				top: 0,
				size: $(this).width()*5,
				fill: '#000',
				//background: null,
				text: $(this).attr('data'),
				radius: 0,
				quiet: 0,
				mode: 0,
				mSize: 0.1,
				mPosX: 0.5,
				mPosY: 0.5,
				//label: 'no label',
				//fontname: 'sans',
				//fontcolor: '#000',
				//image: null
			});
		});

		//左右滑动事件
		$(".page_myOrder .views .d").touchwipe({
			'wipeLeft' : function(){
				if(myOrderObject.check_code.length > myOrderObject.num + 1){
					myOrderObject.num ++;
					$('.page_myOrder .views ul').animate({left : -1 * myOrderObject.num * $('.page_myOrder .views').width()}, 'fast');
					myOrderObject.number = myOrderObject.number+1;
					$('.page_myOrder .views .num font').text(myOrderObject.number);
				}
			},
			'wipeRight' : function(){
				if(0 < myOrderObject.num){
					myOrderObject.num --;
					$('.page_myOrder .views ul').animate({left : -1 * myOrderObject.num * $('.page_myOrder .views').width()}, 'fast');
					myOrderObject.number = myOrderObject.number - 1;
					$('.page_myOrder .views .num font').text(myOrderObject.number);
				}
			}
		});
		$('.page_myOrder .L_niu').click(function(e){
			if(myOrderObject.check_code.length > myOrderObject.num + 1){
				myOrderObject.num ++;
				$('.page_myOrder .views ul').animate({left : -1 * myOrderObject.num * $('.page_myOrder .views').width()}, 'fast');
				myOrderObject.number = myOrderObject.number+1;
				$('.page_myOrder .views .num font').text(myOrderObject.number);
			}
			$(this).addClass('L_y');
			$('.page_myOrder .R_niu').removeClass('R_y');
			e.stopPropagation();
		});
		$('.page_myOrder .R_niu').click(function(e){
			if(0 < myOrderObject.num){
				myOrderObject.num --;
				$('.page_myOrder .views ul').animate({left : -1 * myOrderObject.num * $('.page_myOrder .views').width()}, 'fast');
				myOrderObject.number = myOrderObject.number - 1;
				$('.page_myOrder .views .num font').text(myOrderObject.number);
			}
			$(this).addClass('R_y');
			$('.page_myOrder .L_niu').removeClass('L_y');
			e.stopPropagation();
		});
		myOrderObject.number = 1;
		myOrderObject.num_i = 0;

		document.body.style.overflow='hidden';
		document.ontouchmove = function(e){ e.preventDefault();} //文档禁止 touchmove事件

	},
	//隐藏消费码
	hideCode : function (){
		$('.page_myOrder .QRcode').fadeOut('fast');
		$('.page_myOrder .content').removeClass('mask');
		document.body.style.overflow='visible';
		document.ontouchmove = function(e){} //文档禁止 touchmove事件
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

	logistics : function(order_id){

	},

	ConfirmReceipt : function(order_id){

	},
	loadOrder : function(page){
		var data = {
			get:{page:page||myOrderObject.page}
		};
		data.post = {};
		if(myOrderObject.act_status){
			data.post.act_status = myOrderObject.act_status;
		}
		$('.page_myOrder center').show();
		ajax('Member/Order/index', data, function(d){
			$('.page_myOrder center').hide();
			win.close_loading();
			if(d.length > 0){
				var code = '';
				var types = ['活动', '商品'];
				var status = ['待付款', ['待参加', '未发货'], ['已参加', '已发货'], '未确认', '已完成', '退款中', '已退款', '已取消', '退款中'];
				for(var i in d){
					code += '<li>';
					code += '	<a class="top" href="javascript:jump(\'orderDetail\',{order_id:'+ d[i].id +'})">';
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
						code += '			<div class="c">邮费：￥'+ d[i].postage + '</div>';
					}
					code += '			<div class="b">'+ d[i].count +'份<div class="cc"></div>总价：￥<font>'+ d[i].price +'</font></div>';
					code += '		</div>';
					code += '	</a>';
					code += '	<div class="bottom">';
					code += '		<div class="status">'+ (typeof(status[d[i].act_status])=='string'?status[d[i].act_status]:status[d[i].act_status][d[i].type]) +'</div>';
					if(d[i].type == 0){
						if(d[i].act_status == 0){
							code += '		<a href="javascript:wxpay('+ d[i].id +','+d[i].price+','+d[i].limit_time+')" class="btn">立即付款</a>';
							code += '		<a href="javascript:myOrderObject.cancelOrder('+ d[i].id +')" class="btn">取消订单</a>';
						}else if(d[i].act_status == 1){
							var check_code = [];
							for(var n in d[i].check_code){
								if(check_code.indexOf(d[i].check_code[n]) == -1)check_code.push(d[i].check_code[n]);
							}
							myOrderObject.check_data[d[i].id] = check_code;
							code += '		<a href="javascript:myOrderObject.showCode('+ d[i].id +');" class="btn">我的消费码</a>';
						}else if((d[i].act_status == 2 || d[i].act_status == 3) && d[i].comment_id == null){
							code += '		<a href="javascript:myOrderObject.comment('+ d[i].id +');" class="btn">待评论</a>';
						}else if(d[i].act_status == 5){
							code += '		<a href="javascript:myOrderObject.cancelRefund('+ d[i].id +');" class="btn">取消退款</a>';
						}
					}else{
						if(d[i].act_status == 0){
							code += '		<a href="javascript:wxpay('+ d[i].id +','+d[i].price+','+d[i].limit_time+')" class="btn">立即付款</a>';
							code += '		<a href="javascript:myOrderObject.cancelOrder('+ d[i].id +')" class="btn">取消订单</a>';
						}else if(d[i].act_status == 1){
							code += '		<a href="javascript:myOrderObject.modifyOrder('+ d[i].id +');" class="btn">修改备注</a>';
						}else if(d[i].act_status == 2){
							code += '		<a href="javascript:myOrderObject.logistics('+ d[i].id +');" class="btn">查看物流</a>';
						}else if(d[i].act_status == 3){
							code += '		<a href="javascript:myOrderObject.ConfirmReceipt('+ d[i].id +');" class="btn">确认收货</a>';
						}else if(d[i].act_status == 4 && d[i].comment_id == null){
							code += '		<a href="javascript:myOrderObject.comment('+ d[i].id +');" class="btn">待评论</a>';
						}else if(d[i].act_status == 5){
							code += '		<a href="javascript:myOrderObject.cancelRefund('+ d[i].id +');" class="btn">取消退款</a>';
						}
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
				$('.page_myOrder .content').html(code);
			else
				$('.page_myOrder .content').append(code);
			myOrderObject.locked = false;
		});
	},
	onload : function(){
		$('.page_myOrder .views').click(function(e){
			e.stopPropagation();
		});

		$('.page_myOrder .statu').click(function(){
			$(this).addClass('add_hei').siblings().removeClass('add_hei');
			if($(this).attr('act_status')){
				myOrderObject.act_status = $(this).attr('act_status');
			}else{
				myOrderObject.act_status = null;
			}
			$('.page_myOrder .content').empty();
			myOrderObject.loadOrder(1);
		});
	},
	onshow:function(){
		$('.page_myOrder .content').empty();
		$('.page_myOrder').on('scroll', function(){
			var pagenum = Math.ceil($('.page_myOrder .content > li').length / 5) + 1;
			if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10
				&& $('.page_myOrder center:visible').length == 0){
				myOrderObject.loadOrder(pagenum);
			}
		});
		myOrderObject.loadOrder();
	}
};