var confirmEnrollingObject = {
	tips_id : null,
	buy_num : 1,
	limit : 0,
	coupon_id : null,
	buy_price : null,
	type : 2,
	value : 0,
	min_num:0,//最低成局数
	two_min_num:0,//最低成局数缓存
	book_discount:100,
	buy_status:0,
	alert:function(){
		var code = '<p style="text-align:center; margin:0;">定制须知</p><p style="text-align:left; font-size:1.2rem; text-indent:2em;">定制活动一旦被购买，则不再接受他人购买；定制活动购买人数不得低于最低接待人数。</p>';
		$.alert(code,'error');
	},
	couponInput : function (id, type, value, content){
		confirmEnrollingObject.coupon_id = id;
		confirmEnrollingObject.type = type;
		confirmEnrollingObject.value = parseFloat(value);
		if(type == 0){
			$('.page_confirmEnrolling #coupon_price').html("- ￥" + value);
		}else if(type == 1){
			var val = value / 10 + '折';
			$('.page_confirmEnrolling #coupon_price').html(val);
		}else if(type == 2){
			$('.page_confirmEnrolling #coupon_price').html("赠品：" + content);
		}
		confirmEnrollingObject.changePrice();
	},
	cancelCoupon : function(){
		$('.page_confirmEnrolling #coupon_price').html('使用优惠券');
		$('.page_confirmEnrolling .li_item .coupon').css('margin-right','0');
		$('.page_confirmEnrolling .cancelCoupon').hide();
		this.couponInput(null,null,null,null);
	},
	vals : function (em){
		if(!/^\d+$/.test($(em).text())){
			$(em).text(1);
		}else{
			var currentVal = parseInt($(em).text());
			if(currentVal > confirmEnrollingObject.limit){
				$(em).text(confirmEnrollingObject.limit);
			}else if(currentVal < 1){
				$(em).text(1);
			}
		}
		confirmEnrollingObject.changePrice();
	},

	changeCopies : function (em, num){
		var currentVal = parseInt($(em).parent().children('.b').text());
		if(currentVal <= 1 && num == '-1')num=0;
		if(currentVal >= confirmEnrollingObject.limit && num == '1')num=0;
		if(confirmEnrollingObject.min_num != 0 && currentVal == confirmEnrollingObject.min_num && num == '-1')num = 0;
		currentVal += num;
		confirmEnrollingObject.buy_num = currentVal;
		$(em).parent().children('.b').text(currentVal);
		confirmEnrollingObject.changePrice();
	},
	changePrice : function(){
		confirmEnrollingObject.buy_price = parseFloat(confirmEnrollingObject.buy_price);
		var price = confirmEnrollingObject.buy_price;
		var num = confirmEnrollingObject.buy_num;
		//计算包场折扣
		if($('.page_confirmEnrolling .kill_all .cao_dan').attr('is_book') == 1){
			var discount = confirmEnrollingObject.book_discount / 100;
			price *= num * discount;
		}else{
			//var discount = 1;
			price *= num;
		}
		var _price = price;
		//计算优惠券
		if(confirmEnrollingObject.type == 0)
			_price = Math.round((price - confirmEnrollingObject.value) * 100) / 100;
		else if(confirmEnrollingObject.type == 1)
			_price = price * confirmEnrollingObject.value / 100;

		_price = _price > 0 ? _price : 0;

		price = price.priceFormat();
		_price = _price.priceFormat();
		// confirmEnrollingObject.buy_price = _price;
		$('.page_confirmEnrolling #buy_price').html(_price);
		$('.page_confirmEnrolling #number_price font').html(price);
	},

	selectTime : function(em){
		if($(em).hasClass("yes")){
			$(em).addClass('select').removeClass('yes');
			$(em).parent().siblings().children('.select').addClass('yes').removeClass('select');
		}else{
			$(em).addClass('yes').removeClass('select');
			$('.isOR').hide();
			$('.center_border').hide();
			$('#is_book').addClass('off').removeClass('up');
		}
	},
	/*未完成 食客数量下拉框
	 selectCount:function(mincount,maxcount,selectedcount){
	 var opt = [];
	 for(var j=mincount; j<=maxcount; j++){
	 opt[j][name]=j;
	 opt[j][value]=j;
	 }
	 //判断选中数
	 if(selectedcount){
	 for(var i in opt){
	 if(opt[i]['value']==selectedcount){
	 opt[i].selected = 1;
	 }
	 }
	 }
	 $('.page_confirmEnrolling #num').selectFormat(opt, "食客数量");
	 }*/
	onload : function(){
		confirmEnrollingObject.tips_id = win.get.tips_id;
		confirmEnrollingObject.time_id = win.get.time_id;
		if(!win.get.time_id){$.alert('请您选择时间');return false;}

		//判断是否登录，没有登录则跳转登录
		if(!member){
			win.login();
			return;
		}
		//文本清空
		$('.words').focus(function() {
			$(this).empty();
		});

		$('.xxniu').click(function(){
			$('.Times').hide();
		});
		$('.time_but').click(function(){
			//已报名人数
			var nu = parseInt($('.page_confirmEnrolling .select').siblings().children().children('font').text());
			if($('.page_confirmEnrolling .select').size()==0)return false;
			var limit_num = parseInt($('.page_confirmEnrolling .select').attr('limit_num'));
			var max_num = parseInt($('.page_confirmEnrolling .select').attr('max'));
			if(nu == 0){
				$('.page_confirmEnrolling .kill_all .cao_dan .buy_all').removeAttr('no_book');
				//$('.page_confirmEnrolling .isOR').show();
				//$('.page_confirmEnrolling .center_border').show();

				if(limit_num >0 && limit_num <max_num)
					confirmEnrollingObject.limit = limit_num;
				else
					confirmEnrollingObject.limit = max_num;
				confirmEnrollingObject.two_min_num =  parseInt($('.page_confirmEnrolling .select').attr('min'));

				if(confirmEnrollingObject.buy_status==2){
					$('.page_confirmEnrolling .b').text(confirmEnrollingObject.two_min_num);
					confirmEnrollingObject.buy_num = confirmEnrollingObject.two_min_num;
				}else{
					$('.page_confirmEnrolling .b').text(1);
					confirmEnrollingObject.buy_num = 1;
				}
				confirmEnrollingObject.changePrice();
			}else{
				//$('.page_confirmEnrolling .isOR').hide();
				//$('.page_confirmEnrolling .center_border').hide();
				$('.page_confirmEnrolling .kill_all .cao_dan .buy_all').attr('no_book','no');
				confirmEnrollingObject.min_num = 0;
				if(limit_num >0 && limit_num <max_num)
					confirmEnrollingObject.limit = limit_num;
				else
					confirmEnrollingObject.limit = max_num;
				//confirmEnrollingObject.limit = parseInt($('.page_confirmEnrolling .select').attr('max'));
				$('.page_confirmEnrolling .b').text(1);
				confirmEnrollingObject.buy_num = 1;
				confirmEnrollingObject.changePrice();
			}
			var start_time = $('.page_confirmEnrolling .select').attr('start_time');
			var end_time = $('.page_confirmEnrolling .select').attr('end_time');
			var times_id = $('.page_confirmEnrolling .select').attr('times_id');
			if(limit_num > 0){
				$('.page_confirmEnrolling .Right .limit').html('(限购<font>'+limit_num+'</font>份)');
			}else{
				$('.page_confirmEnrolling .Right .limit').html('');
			}
			$('#times span').html(start_time.timeFormat("W <br>Y-m-d H:i")+'-'+end_time.timeFormat("H:i"));
			$('#times span').attr('times_id',times_id);
			$('.page_confirmEnrolling .Times').hide();
		});

		$('.page_confirmEnrolling .more_time').click(function(){
			$('.page_confirmEnrolling .Times').show();
		});
		// //文本清空
		// $('.words').focus(function() {
		// 	$(this).empty();
		// });
		//
		// $('.xxniu').click(function(){
		// 	$('.Times').hide();
		// });
		// $('.time_but').click(function(){
		// 	//已报名人数
		// 	var nu = parseInt($('.page_confirmEnrolling .select').siblings().children().children('font').text());
		// 	if($('.page_confirmEnrolling .select').size()==0)return false;
		// 	var limit_num = parseInt($('.page_confirmEnrolling .select').attr('limit_num'));
		// 	var max_num = parseInt($('.page_confirmEnrolling .select').attr('max'));
		// 	if(nu == 0){
		// 		$('.page_confirmEnrolling .kill_all .cao_dan .buy_all').removeAttr('no_book');
		// 		//$('.page_confirmEnrolling .isOR').show();
		// 		//$('.page_confirmEnrolling .center_border').show();
		//
		// 		if(limit_num >0 && limit_num <max_num)
		// 			confirmEnrollingObject.limit = limit_num;
		// 		else
		// 			confirmEnrollingObject.limit = max_num;
		// 		confirmEnrollingObject.two_min_num =  parseInt($('.page_confirmEnrolling .select').attr('min'));
		//
		// 		if(confirmEnrollingObject.buy_status==2){
		// 			$('.page_confirmEnrolling .b').text(confirmEnrollingObject.two_min_num);
		// 			confirmEnrollingObject.buy_num = confirmEnrollingObject.two_min_num;
		// 		}else{
		// 			$('.page_confirmEnrolling .b').text(1);
		// 			confirmEnrollingObject.buy_num = 1;
		// 		}
		// 		confirmEnrollingObject.changePrice();
		// 	}else{
		// 		//$('.page_confirmEnrolling .isOR').hide();
		// 		//$('.page_confirmEnrolling .center_border').hide();
		// 		$('.page_confirmEnrolling .kill_all .cao_dan .buy_all').attr('no_book','no');
		// 		confirmEnrollingObject.min_num = 0;
		// 		if(limit_num >0 && limit_num <max_num)
		// 			confirmEnrollingObject.limit = limit_num;
		// 		else
		// 			confirmEnrollingObject.limit = max_num;
		// 		//confirmEnrollingObject.limit = parseInt($('.page_confirmEnrolling .select').attr('max'));
		// 		$('.page_confirmEnrolling .b').text(1);
		// 		confirmEnrollingObject.buy_num = 1;
		// 		confirmEnrollingObject.changePrice();
		// 	}
		// 	var start_time = $('.page_confirmEnrolling .select').attr('start_time');
		// 	var end_time = $('.page_confirmEnrolling .select').attr('end_time');
		// 	var times_id = $('.page_confirmEnrolling .select').attr('times_id');
		// 	if(limit_num > 0){
		// 		$('.page_confirmEnrolling .Right .limit').html('(限购<font>'+limit_num+'</font>份)');
		// 	}else{
		// 		$('.page_confirmEnrolling .Right .limit').html('');
		// 	}
		// 	$('#times span').html(start_time.timeFormat("W <br>Y-m-d H:i")+'-'+end_time.timeFormat("H:i"));
		// 	$('#times span').attr('times_id',times_id);
		// 	$('.page_confirmEnrolling .Times').hide();
		// });
		//
		// $('.page_confirmEnrolling .more_time').click(function(){
		// 	$('.page_confirmEnrolling .Times').show();
		// });

		ajax('Order/Index/getTips', {'tips_id':confirmEnrollingObject.tips_id}, function(d){
			if(d.info){
				$.alert(d.info, 'error', function(){
					page.back();
				});
				return;
			}
			//包场折扣
			if(d.book_discount > 0 && d.book_discount < 100){
				confirmEnrollingObject.book_discount = parseFloat(d.book_discount);
				$('.page_confirmEnrolling .isOR em').text('(享'+ (Math.round(d.book_discount*10)/100) +'折)');
			}
			//插入主图
			$('.page_confirmEnrolling .mainpic').html('<img src="'+ d.mainpic +'">');
			//插入标题
			$('.page_confirmEnrolling .pageTitleMenu .title').text(d.title);
			//插入价格
			$('.page_confirmEnrolling .pageTitleMenu .price').html('<font>' + parseFloat(d.price).priceFormat() + '元/</font>份');
			//插入限购
			$('#is_book').click(function(){
				if($(this).hasClass("up")){
					$(this).addClass('off').removeClass('up');
					$(this).attr('is_book','0');
					confirmEnrollingObject.min_num = 0;
					confirmEnrollingObject.changePrice();
				}else{
					$(this).addClass('up').removeClass('off');
					$(this).attr('is_book','1');
					confirmEnrollingObject.min_num = confirmEnrollingObject.two_min_num;
					$('.page_confirmEnrolling .b').text(confirmEnrollingObject.two_min_num);
					confirmEnrollingObject.buy_num = confirmEnrollingObject.two_min_num;
					confirmEnrollingObject.changePrice();
				}
			});

			//插入时间段
			var code = '';
			var numbers = 0;
			for(var i in d.times){
				if(d.times[i].stop_buy_time >= (new Date()).getTime()/1000){
					code +='<div class="list_t">';
					if(d.times[i].stock == 0){
						code +='<div class="t_left none">';
					}else{
						//numbers++;
						code +='<div class="t_left">';
					}
					code +='   <span class="top">'+ d.times[i].start_time.timeFormat("Y-m-d (W)") +'</span><br>';
					if(d.times[i].stock == 0){
						code +='   <span>'+ d.times[i].start_time.timeFormat("H:i")+ '-' + d.times[i].end_time.timeFormat("H:i") +'  名额已满</span>';
					}else{
						code +='   <span>'+d.times[i].start_time.timeFormat("H:i")+ '-' + d.times[i].end_time.timeFormat("H:i") +'  已经报名<font>'+ d.times[i].count+'</font>人</span>';
					}
					code +='</div>';
					if(d.times[i].stock == 0){
						code +='<div class="t_right empty"></div>';
					}else{
						if(confirmEnrollingObject.time_id==d.times[i].id){
							if(d.times[i].limit_num != 0){
								$('.page_confirmEnrolling .Right .limit').html('(限购<font>'+d.times[i].limit_num+'</font>份)');
							}
							code +='<div class="t_right select" min="'+ d.times[i].min_num +'" max="'+ d.times[i].stock +'" start_time="'+ d.times[i].start_time +'" end_time="'+ d.times[i].end_time +'" times_id="'+ d.times[i].id +'" limit_num="'+ d.times[i].limit_num +'" i="'+ i +'" onclick="confirmEnrollingObject.selectTime(this)"></div>';
							var first = d.times[i].start_time.timeFormat("W <br>Y-m-d H:i")+'-'+d.times[i].end_time.timeFormat("H:i");
							$('.page_confirmEnrolling #times span').html(first);
							$('.page_confirmEnrolling #times span').attr('times_id',d.times[i].id);
							if(d.times[i].count==0){
								if(d.buy_status==2){
									$('.page_confirmEnrolling .b').text(d.times[i].min_num);
									confirmEnrollingObject.buy_num = d.times[i].min_num;
									confirmEnrollingObject.min_num = d.times[i].min_num;
								}
								//$('.page_confirmEnrolling .isOR').show();
								//$('.page_confirmEnrolling .center_border').show();
								confirmEnrollingObject.two_min_num = d.times[i].min_num;
							}else{
								$('.page_confirmEnrolling .kill_all .cao_dan .buy_all').attr('no_book','no');
							}
							if(parseInt(d.times[i].limit_num) !=0 && parseInt(d.times[i].limit_num) < parseInt(d.times[i].stock)){
								confirmEnrollingObject.limit = d.times[i].limit_num;
							}else{
								confirmEnrollingObject.limit = d.times[i].stock;
							}
						}else{
							code +='<div class="t_right yes" min="'+ d.times[i].min_num +'" max="'+ d.times[i].stock +'" start_time="'+ d.times[i].start_time +'" end_time="'+ d.times[i].end_time +'" times_id="'+ d.times[i].id +'" limit_num="'+ d.times[i].limit_num +'" i="'+ i +'" onclick="confirmEnrollingObject.selectTime(this)"></div>';
						}
					}
					code +='</div>';
					$('.page_confirmEnrolling .center_list').html(code);
				}
			}

			//插入单价
			confirmEnrollingObject.buy_price = d.buy_price;
			confirmEnrollingObject.changePrice();

			//插入可用优惠券
			if(typeof(d.allow_coupon) == 'undefined' || d.allow_coupon == 1){
				if(d.coupon){
					confirmEnrollingObject.couponInput(d.coupon.id, d.coupon.type, d.coupon.value, d.coupon.content);
					//选择优惠券
					$$(function(){
						$('.page_confirmEnrolling .li_item .coupon').css('margin-right','3rem');
						$('.page_confirmEnrolling .cancelCoupon').show();
						if(parent.win.coupon){
							confirmEnrollingObject.couponInput(parent.win.coupon.id, parent.win.coupon.type, parent.win.coupon.value, parent.win.coupon.content);
							delete parent.win.coupon;
						}
					});
					$('.coupon').click(function(){
						var min_price = confirmEnrollingObject.buy_price * $(".page_confirmEnrolling #num").text();
						if($('#is_book').attr('is_book') == 1){
							min_price *= confirmEnrollingObject.book_discount / 100;
						}
						jump('myCoupon',{tips_id:confirmEnrollingObject.tips_id,coupon_id:confirmEnrollingObject.coupon_id,min_price:min_price});
					});
				}else{
					$('.page_confirmEnrolling #coupon_price').text('没有优惠券可用');
					$('.page_confirmEnrolling .li_item .coupon').css('margin-right','0');
					$('.page_confirmEnrolling .cancelCoupon').hide();
				}
			}else{
				$('.page_confirmEnrolling #coupon_price').parents('.li_item').remove();
			}
			//插入绑定手机
			$('.page_confirmEnrolling #telephone').text(d.telephone);

			//包桌折扣为0，包桌按钮去掉
			if(parseInt(d.book_discount)==0){
				$('.page_confirmEnrolling .isOR').remove();
				$('.page_confirmEnrolling .center_border').remove() ;
			}

			$('.page_confirmEnrolling .kill_all .buy_all').text('我要包桌（'+ d.times[0].min_num +'~'+ d.times[0].restrict_num +'/人）');

			$('.page_confirmEnrolling .kill_all .buy_cao_num').text(d.times[0].min_num +'~'+ d.times[0].restrict_num +'（人）');
			//判断包桌、定制等
			if(d.buy_status!=0){
				confirmEnrollingObject.buy_status = d.buy_status;
				//包桌
				if(d.buy_status==1){
					$('.page_confirmEnrolling .kill_all .cao_dan').show();
					$('.page_confirmEnrolling .kill_all .buy_cao').hide();
					$('.page_confirmEnrolling .kill_all .kill_bottom').hide();
				}else if(d.buy_status==2){//定制
					$('.page_confirmEnrolling .kill_all .cao_dan').hide();
					$('.page_confirmEnrolling .kill_all .buy_cao').show();
					$('.page_confirmEnrolling .kill_all .kill_bottom').show();
					$('.page_confirmEnrolling .kill_all .cao_dan').attr('is_book','2');
					$('.page_confirmEnrolling .kill_all .titlees').text('定制');
				}
			}else{
				$('.page_confirmEnrolling .kill_all').remove();
				$('.page_confirmEnrolling .kill_all .cao_dan').hide();
				$('.page_confirmEnrolling .kill_all .buy_cao').hide();
				$('.page_confirmEnrolling .kill_all .kill_bottom').hide();
				//$('.page_confirmEnrolling .kill_all .cao_dan .buy_all').attr('no_book','no');
			}

			//自选
			$('.page_confirmEnrolling .kill_all .no_book').click(function(){
				if($(this).hasClass("add_bg")){
					return;
				}else{
					$(this).addClass('add_bg');
					$(this).siblings().removeClass('add_bg');
					$(this).parent().attr('is_book','0');
					confirmEnrollingObject.min_num = 0;
					//confirmEnrollingObject.min_num = confirmEnrollingObject.two_min_num;
					$('.page_confirmEnrolling .b').text(1);
					confirmEnrollingObject.buy_num = 1;
					confirmEnrollingObject.changePrice();
				}
			});

			//包桌
			$('.page_confirmEnrolling .kill_all .buy_all').click(function(){
				if($(this).attr('no_book') == 'no'){
					$.alert('该时间段已产生订单无法包桌','error');
					return;
				}
				if($(this).hasClass("add_bg")){
					return;
				}else{
					$(this).addClass('add_bg');
					$(this).siblings().removeClass('add_bg');
					$(this).parent().attr('is_book','1');
					confirmEnrollingObject.min_num = confirmEnrollingObject.two_min_num;
					$('.page_confirmEnrolling .b').text(confirmEnrollingObject.min_num);
					confirmEnrollingObject.buy_num = confirmEnrollingObject.min_num;
					confirmEnrollingObject.changePrice();
				}
			});

			//提交按钮绑定时间
			$('.page_confirmEnrolling #submitBtn').click(function(){
				if($('.page_confirmEnrolling #times span').size() == 0){
					$.alert('请选择您要参与的时间段','error');
					return false;
				}
				var data = {};
				data.tips_id = confirmEnrollingObject.tips_id;
				data.times_id = $('.page_confirmEnrolling #times span').attr('times_id');
				data.is_book = $('.page_confirmEnrolling .cao_dan').attr('is_book')||0;
				data.num = confirmEnrollingObject.buy_num;

				if(confirmEnrollingObject.coupon_id != null)data.coupon_id = confirmEnrollingObject.coupon_id;
				//
				//if($('.page_confirmEnrolling #coupon_price font').size() <= 0){
				//	data.coupon_id = null;
				//}

                var from = getQueryString(location.href, 'from');
                if (from) {
                    switch (from) {
                        case 'singlemessage':
                            data.from = 1;
                            break;
                        case 'timeline':
                            data.from = 0;
                            break;
                        default:
                            data.from = 2;
                            break;
                    }
                    data.platform = 1;
                }

				data.context = $(".page_confirmEnrolling .leaveWords .words").val();
				//提交订单
				ajax('Order/Index/create', data, function(d){
					if(d.status == 1){
						//jump('payMoney', {order_id : d.info.order_id});
						confirmEnrollingObject.order_id = d.info.order_id;
						if(win.get.android){
							window.location.href = 'http://' + DOMAIN + '/order/pay/submitAlipay.do?token='+ win.token +'&order_id=' + d.info.order_id;
						}else{
							wxpay(d.info.order_id,confirmEnrollingObject.buy_price,d.info.limit_pay_time);
						}
					}else{
						if(d.info == 'open_id_is_null'){
							$.dialog('尚未获得授权!是否现在授权?', function(){
								ajax('Home/Wx/getOauthUrl', function(d){
									if(typeof(d) == 'string'){
										if(window.sessionStorage){
											window.sessionStorage.setItem('jumpUrl', 'page=choice-tipsDetail-confirmEnrolling&tips_id=' + confirmEnrollingObject.tips_id + '&time_id=' + confirmEnrollingObject.time_id);
										}
										window.location.href = d;
									}
								});
							});
						}else{
							$.alert(d.info,'error');
						}
					}
				}, 2);
			});
		}, 2);
	},
};
