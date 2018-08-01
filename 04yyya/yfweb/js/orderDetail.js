var orderDetailObject = {
	order_id : null,
	check_code : [],
	num : 0,
	number:1,//二维码序号
	num_i:0,//二维码总数
	showCode : function (){
		if(orderDetailObject.check_code.length <= 0){
			$.alert('没有未参加的活动消费码', 'error');
			return;
		}

		$('.page_orderDetail .QRcode').fadeIn('fast');
		$('.page_orderDetail #mainContent').addClass('mask');

		var top = ($(window).height()-$('.page_orderDetail .QRcode .views').height())/3;
		var closedTop =$('.page_orderDetail .QRcode .views').height() + top;
		$('.page_orderDetail .QRcode .closed').css("top",closedTop);
		$('.page_orderDetail .QRcode .views').css({'margin-top':top});

		var code = '<ul>';
		var check_code = [];
		for(var i in orderDetailObject.check_code){
			code += '<li><p>'+ orderDetailObject.check_code[i] +'</p><span data="'+ orderDetailObject.check_code[i] +'"></span></li>';
			check_code.push(orderDetailObject.check_code[i]);
			orderDetailObject.num_i++;
		}
		code += '</ul>';
		code +='<div class="num"><font>'+ orderDetailObject.number +'</font>/'+ orderDetailObject.num_i +'</div>';
		$('.page_orderDetail .views .d').html(code);

		$('.page_orderDetail .QRcode .views ul').width($('.page_orderDetail .QRcode .views .d').width() * orderDetailObject.check_code.length);

		//制作二维码
		$(".page_orderDetail .views .d span").each(function(){
			$(this).qrcode({
				render: 'canvas',
				minVersion: 1,
				maxVersion: 40,
				ecLevel: 'L',
				left: 0,
				top: 0,
				size: $(this).width()*5,
				fill: '#000',
				text: $(this).attr('data'),
				radius: 0,
				quiet: 0,
				mode: 0,
				mSize: 0.1,
				mPosX: 0.5,
				mPosY: 0.5
			});
		});

		//左右滑动事件
		$(".page_orderDetail .views .d").touchwipe({
			'wipeLeft' : function(){
				if(orderDetailObject.check_code.length > orderDetailObject.num + 1){
					orderDetailObject.num ++;
					$('.page_orderDetail .views ul').animate({left : -1 * orderDetailObject.num * $('.page_orderDetail .views').width()}, 'fast');
				}
			},
			'wipeRight' : function(){
				if(0 < orderDetailObject.num){
					orderDetailObject.num --;
					$('.page_orderDetail .views ul').animate({left : -1 * orderDetailObject.num * $('.page_orderDetail .views').width()}, 'fast');
				}
			}
		});
		$('.page_orderDetail .L_niu').click(function(e){
			if(orderDetailObject.check_code.length > orderDetailObject.num + 1){
				orderDetailObject.num ++;
				$('.page_orderDetail .views ul').animate({left : -1 * orderDetailObject.num * $('.page_myOrder .views').width()}, 'fast');
				orderDetailObject.number = orderDetailObject.number+1;
				$('.page_orderDetail .views .num font').text(orderDetailObject.number);
			}
			$(this).addClass('L_y');
			$('.page_orderDetail .R_niu').removeClass('R_y');
			e.stopPropagation();
		});
		$('.page_orderDetail .R_niu').click(function(e){
			if(0 < orderDetailObject.num){
				orderDetailObject.num --;
				$('.page_orderDetail .views ul').animate({left : -1 * orderDetailObject.num * $('.page_myOrder .views').width()}, 'fast');
				orderDetailObject.number = orderDetailObject.number - 1;
				$('.page_orderDetail .views .num font').text(orderDetailObject.number);
			}
			$(this).addClass('R_y');
			$('.page_orderDetail .L_niu').removeClass('L_y');
			e.stopPropagation();
		});
		orderDetailObject.number = 1;
		orderDetailObject.num_i = 0;
		document.body.style.overflow='hidden';
		document.ontouchmove = function(e){ e.preventDefault();} //文档禁止 touchmove事件

	},

	hideCode : function (){
		$('.page_orderDetail .QRcode').fadeOut('fast');
		$('.page_orderDetail #mainContent').removeClass('mask');
		document.body.style.overflow='visible';
		document.ontouchmove = function(e){} //文档禁止 touchmove事件
	},
    shareSuccess: function (id) {
        return function(target) {
            ajax('Home/Index/shareSuccess', {type: 0, item_id: id, target: target, platform: 1});
        }
    },
	onload : function(){
		if(!member){
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
			return;
		}

		orderDetailObject.order_id = win.get.order_id;

		$('.page_orderDetail .views').click(function(e){
			e.stopPropagation();
		});

		ajax('Member/Order/getDetail', {order_id : orderDetailObject.order_id}, function(d){
			var $em = $('.page_orderDetail');
			var status = ['待付款', ['待参加', '待发货'], ['已参加', '已发货'], '未确认', '已完成', '退款中', '已退款', '已取消'];
			//分享绑定
			var desc = d.title;
			var url = win.host + '?page=choice-tipsDetail&tips_id=' + d.id;
			if(member && member.invitecode){
				url += '&invitecode=' + member.invitecode;
			}
			share(d.title, desc, url, d.path, orderDetailObject.shareSuccess(d.id));
			if(d.act_status == 0){
				$em.find('.orderOperation').html('取消订单').click(function(){
					ajax('Member/Order/cancel', {order_id : orderDetailObject.order_id}, function(d){
						if(d.status == 1){
							$.alert('成功取消订单', function(){
								page.reload();
							});
						}else{
							$.alert(d.info, 'error');
						}
					});
				});

				$('.page_orderDetail.orderBottom').html('立即付款').click(function(){
					if(win.get.android){
						window.location.href = 'http://' + DOMAIN + '/order/pay/submitAlipay.do?token='+ win.token +'&order_id=' + orderDetailObject.order_id;
					}else{
						wxpay(orderDetailObject.order_id,d.price,d.limit_time);
					}
				});

			}else if(d.act_status == 1){
				$em.find('.orderOperation').empty();

				$('.page_orderDetail.orderBottom').html('<div class="sbtn"><span class="sharefriend"><i class="sicon"></i><font>推荐给好友</font></span><i class="sl"></i><span class="sendinvite"><i class="iicon"></i><font>生成邀请函</font></span></div>');
				$('.page_orderDetail.orderBottom .sharefriend').click(function(){
					showShareBox();
				});
				$('.page_orderDetail.orderBottom .sendinvite').click(function(){
					jump('invitationInfo', {order_id : orderDetailObject.order_id});
				});
			}else if(d.act_status == 2){
				$('.page_orderDetail.orderBottom').empty().addClass('orderBottom2');
				if(d.type == 0){
					$('.page_orderDetail.orderBottom').html('立即评价').click(function(){
						jump('evaluationStar', {order_id : orderDetailObject.order_id});
					});
				}else{
					$('#footer .orderBottom2').html('查看物流');
				}
			}else if(d.act_status == 3){
				$('.page_orderDetail.orderBottom').empty().addClass('orderBottom2');
				if(d.type == 0){
					$('#footer .orderBottom').html('立即评价').click(function(){
						jump('evaluationStar', {order_id : orderDetailObject.order_id});
					});
				}else{
					$('#footer .orderBottom2').html('确认收货');
				}
			}else if(d.act_status == 4){
				$('.page_orderDetail.orderBottom').hide().empty().addClass('empty');
				//评论列表
				var code = '';
				if(d.comment && d.comment.id){
					code += '<div class="com_list">';
					code += '	<div class="h_pic">';
					code += '		<img src="'+ member.path +'" />';
					code += '	</div>';
					code += '	<div class="pic_right">';
					code += '		<div class="name_title">';
					code += '			<div class="names">'+ member.nickname +'</div>';
					code += '			<span>'+d.comment.datetime+'</span>';
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
					$('.page_orderDetail .commentList').html(code).show();
					$('.page_orderDetail .bb_liuyan').hide();
				}
			}else if(d.act_status == 5){
				$em.find('.refund').html('取消退款').click(function(){
					ajax('Member/Order/cancelRefund', {order_id : orderDetailObject.order_id}, function(d){
						if(d.status == 1){
							$.alert('操作成功', function(){
								page.reload();
							});
						}else{
							$.alert(d.info, 'error');
						}
					});
				}).show();
				$('.page_orderDetail.orderBottom').hide().empty().addClass('empty');
			}else{
				$('.page_orderDetail.orderBottom').hide().empty().addClass('empty');
			}


			//查看消费码
			if(d.type == 0 && d.act_status > 0 && d.act_status < 7){
				var check_code = [];
				for(var n in d.check_code){
					if(check_code.indexOf(d.check_code[n]) == -1)check_code.push(d.check_code[n]);
				}
				var code = '';
				var nick = '';
				for(var i in check_code){
					if(code.indexOf(d.check_code[i].code) > 0)continue;
					if(d.check_code[i].nickname === null){
						nick = '';
					}else{
						nick = '(<font class="ni">'+d.check_code[i].nickname+'</font>)';
					}
					if(d.check_code[i].status == 0){
						orderDetailObject.check_code.push(d.check_code[i].code);
						code += '<li>'+ d.check_code[i].code +'<span style="color:#888;">'+nick+'<font class="yz">未验证</font></span></li>';
					} else
						code += '<li><del>'+ d.check_code[i].code +'</del><span>'+nick+'<font class="yz">已验证</font></span></li>';
				}
				$em.find('.consumerCode .consumerCodeList').html(code);
				//$em.find('.consumerCodeInfDate').html(d.end_time.timeFormat('Y年m月d日 H:i'));
				$em.find('.consumerCode').show();
			}

			//达人信息
			$em.find('.actInfDaren span').text(d.nickname);
			$em.find('.actInfconnect').text(d.catname);
			/*$em.find('.actInfconnect').click(function(){
			 if(window.confirm('要电话联系达人吗？')){
			 window.location.href = "tel:" + d.tel;
			 }
			 });*/

			//商品信息
			$('<img>').attr('src', d.path.pathFormat()).appendTo($em.find('.orderListLeft')).click(function(){
				if(d.type == 0){
					jump('tipsDetail',{tips_id:d.id});
				}else if(d.type == 2){
					jump('raiseDetail',{raise_id:d.id});
				}else{
					jump('goodsDetail',{goods_id:d.id});
				}
			});
			$em.find('.orderListRight .orderTitle').text(d.title);
			$em.find('.orderListRight .number').html(d.count+'份');
			$em.find('.orderListRight .total font').html('￥'+ d.price);
			$em.find('.orderListRight .status').text(typeof(status[d.act_status]) == 'object' ? status[d.act_status][d.type] : status[d.act_status]);

			var code = '';
			//活动地点、时间、手机
			code += '<li>食客<span>' + member.nickname + '</span></li>';
			code += '<li>订单总额<span class="add_color">￥'+ d.total +'</span></li>';
			//优惠金额，订单总额，留言
			if(d.coupon){
				if(d.coupon.type == 0)
					code += '<li>优惠金额<span>- ￥'+ d.coupon.value +'</span></li>';
				else if(d.coupon.type == 1)
					code += '<li>优惠折扣<span>'+ parseFloat(d.coupon.value) * 10 +'折</span></li>';
			}
			code += '<li>下单手机<span>'+ member.telephone +'</span></li>';
			code += '<li>订单编号<span>'+ d.sn +'</span></li>';
			if(d.invite_nickname != ''){
				code += '<li>邀请人<span>'+ d.invite_nickname +'</span></li>';
			}
			code += '<li>就餐时间<span>' + d.start_time.timeFormat('Y-m-d W H:i') + '-' + d.end_time.timeFormat('H:i') + '</span></li>';
			code += '<li>下单时间<span>'+ d.create_time.timeFormat('Y-m-d H:i:s') +'</span></li>';
			code += '<li class="orderAddress">地点<span>' + d.address +'</span></li>';
			code += '<div class="clearfix"></div>';
			$em.find('.orderDetail').html(code);

			var codece = '<div class="the_blankb">留言</div>';
			codece += '<div class="liuyan">'+ d.context +'</div>';
			$('.page_orderDetail .bb_liuyan').html(codece);

			//联系客服
			/*$em.find('.services').click(function(){
			 if(window.confirm('要电话联系达人吗？')){
			 window.location.href = "tel:" + d.tel;
			 }
			 });*/
			$('.page_orderDetail .imges').each(function(){
				$(this).bubble();
			});
		});
	}
};



