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
	gid:null,
	now_price : null,
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
	//改变价格
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

		price = price;
		_price = _price;
		// confirmEnrollingObject.buy_price = price;
		$('.page_confirmEnrolling #buy_price').html(_price);
		$('.page_confirmEnrolling #number_price font').html(price);
	},
	
	selectTime : function(em){
		if($(em).hasClass("yes")){
			$(em).addClass('select').removeClass('yes');
			$(em).parent().siblings().children('.select').addClass('yes').removeClass('select');
		}else{
			$(em).addClass('yes').removeClass('select');
			// $('.isOR').hide();
			// $('.center_border').hide();
			// $('#is_book').addClass('off').removeClass('up');
		}
	},
	//开团类型
	selecttype : function(em){
		if($(em).hasClass("y")){
			$(em).addClass('selected').removeClass('y');
			$(em).parent().siblings().children('.selected').addClass('y').removeClass('selected');
			$('.page_confirmEnrolling .b').text(1);
			confirmEnrollingObject.buy_num = 1;//购买数量改变
			confirmEnrollingObject.groups_id = $(em).attr('gid');//开团id
			confirmEnrollingObject.buy_price = $(em).attr('groups_price');//开团价格
			confirmEnrollingObject.limit = $(em).attr('count');//限制人数
			confirmEnrollingObject.changePrice();
		}
	},
	onload : function(){
		confirmEnrollingObject.tips_id = win.get.tips_id;
		confirmEnrollingObject.time_id = win.get.time_id;
		// confirmEnrollingObject.catname = win.get.catname;
		if(win.get.catname == '课程' || win.get.catname == '私房菜'){
			$('.page_confirmEnrolling .more_time').off('click');
			$('.page_confirmEnrolling .Times').remove();
		}
		//判断是否开团
		if(typeof(win.get.groups_id) != 'undefined'){
			// if(typeof(win.get.piece_oid) != 'undefined'){
			// 	$('.page_confirmEnrolling .more_time').off('click');
			// 	$('.page_confirmEnrolling .Times').remove();
			// }
			confirmEnrollingObject.piece_oid = win.get.piece_oid;
			confirmEnrollingObject.groups_id = win.get.groups_id;
			$('.page_confirmEnrolling .open_group').show();
		}else{
			$('.page_confirmEnrolling .open_group').hide();
		}

		if(!win.get.time_id){$.alert('请您选择时间','error');}

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
		$('.page_confirmEnrolling .more_time').on('click',function(){
			$('.page_confirmEnrolling .Times').show();
		});
		//时间选择
		$('.time_but').click(function(){
			//开团状态
			if($(".page_confirmEnrolling .select").attr("groups_id")){
				$('.page_confirmEnrolling .open_group').show();
				var gs = JSON.parse($(".page_confirmEnrolling .select").attr("groups_id"));
				var group_code = '';
				group_code += '<div class="group_title">';
				group_code += '	<span></span>';
				group_code += '	<span>拼团价格</span>';
				group_code += '	<span>成团人数</span>';
				group_code += '	<span>拼团时限</span>';
				group_code += '	</div>';
				var n = 0;
				for(var g in gs){
					if(gs[g].can_buy == 1){
						group_code += '<div class="groups">';
						if(n == 0 ){
							//默认开团id
							confirmEnrollingObject.groups_id = gs[g].id;
							confirmEnrollingObject.limit = gs[g].limit_num;
							confirmEnrollingObject.buy_price = gs[g].price;
							group_code += '	<span class="group_select selected" gid="'+gs[g].id+'" groups_price="'+gs[g].price+'" count="'+gs[g].limit_num+'" onclick="confirmEnrollingObject.selecttype(this)"></span>';
							n++;
						}else{
							group_code += '	<span class="group_select y" gid="'+gs[g].id+'" groups_price="'+gs[g].price+'" count="'+gs[g].limit_num+'" onclick="confirmEnrollingObject.selecttype(this)"></span>';
						}
					}else{
						group_code += '<div class="groups op">';
						group_code += '	<span class="group_select empty" gid="'+gs[g].id+'"></span>';
					}
					group_code += '	<span>'+gs[g].price+'元</span>';
					group_code += '<span>'+gs[g].count+'人</span>';
					group_code += '<span>'+gs[g].limit_time+'小时</span>';
					group_code += '</div>';
					group_code += '<div class="groups_line"></div>';

				}
				$('.page_confirmEnrolling .open_group .li_item').html(group_code);
			}else{
				confirmEnrollingObject.groups_id = 0;
				confirmEnrollingObject.buy_price = confirmEnrollingObject.now_price;
				$('.page_confirmEnrolling .open_group').hide();
			}
			//已报名人数
			var nu = parseInt($('.page_confirmEnrolling .select').siblings().children().children('font').text());
			if($('.page_confirmEnrolling .select').size()==0)return false;
			var limit_num = parseInt($('.page_confirmEnrolling .select').attr('limit_num'));
			var max_num = parseInt($('.page_confirmEnrolling .select').attr('max'));
			if(nu == 0){
				if(max_num == -1){
					if(limit_num >0){
						confirmEnrollingObject.limit = limit_num;
					}else{
						confirmEnrollingObject.limit = 100000;
					}
				}else{
					if(limit_num >0 && limit_num <max_num)
						confirmEnrollingObject.limit = limit_num;
					else
						confirmEnrollingObject.limit = max_num;
				}

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
				$('.page_confirmEnrolling .kill_all .cao_dan .buy_all').attr('no_book','no');
				confirmEnrollingObject.min_num = 0;
				if(max_num == -1){
					if(limit_num >0){
						confirmEnrollingObject.limit = limit_num;
					}else{
						confirmEnrollingObject.limit = 100000;
					}
				}else{
					if(limit_num >0 && limit_num <max_num)
						confirmEnrollingObject.limit = limit_num;
					else
						confirmEnrollingObject.limit = max_num;
				}
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
			//原本价格
			confirmEnrollingObject.now_price = parseFloat(d.price);
			//插入主图
			$('.page_confirmEnrolling .mainpic').html('<img src="'+ d.mainpic +'">');
			//插入标题
			$('.page_confirmEnrolling .pageTitleMenu .title').text(d.title);
			//插入价格
			$('.page_confirmEnrolling .pageTitleMenu .price').html('<font>' + parseFloat(d.price) + '元/</font>份');
			//插入限购
			// $('#is_book').click(function(){
			// 	if($(this).hasClass("up")){
			// 		$(this).addClass('off').removeClass('up');
			// 		$(this).attr('is_book','0');
			// 		confirmEnrollingObject.min_num = 0;
			// 		confirmEnrollingObject.changePrice();
			// 	}else{
			// 		$(this).addClass('up').removeClass('off');
			// 		$(this).attr('is_book','1');
			// 		confirmEnrollingObject.min_num = confirmEnrollingObject.two_min_num;
			// 		$('.page_confirmEnrolling .b').text(confirmEnrollingObject.two_min_num);
			// 		confirmEnrollingObject.buy_num = confirmEnrollingObject.two_min_num;
			// 		confirmEnrollingObject.changePrice();
			// 	}
			// });

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
							//判断是否有拼团
							if(typeof(confirmEnrollingObject.groups_id) != 'undefined'){
								var group_code = '';
								group_code += '<div class="group_title">';
								group_code += '	<span></span>';
								group_code += '	<span>拼团价格</span>';
								group_code += '	<span>成团人数</span>';
								group_code += '	<span>拼团时限</span>';
								group_code += '	</div>'
								var m =0;
								//开团人，从活动详情进入状态
								if(confirmEnrollingObject.groups_id == 0){
									for(var j in d.times[i].piece){
										if(d.times[i].piece[j].can_buy == 1){
											group_code += '<div class="groups">';
											if(m == 0){
												//默认开团id
												confirmEnrollingObject.groups_id = d.times[i].piece[j].id;
												//默认限制开团人数
												confirmEnrollingObject.limit = d.times[i].piece[j].limit_num;
												//默认开团价格
												confirmEnrollingObject.buy_price = d.times[i].piece[j].price;
												group_code += '	<span class="group_select selected" gid="'+d.times[i].piece[j].id+'" groups_price="'+d.times[i].piece[j].price+'" count="'+d.times[i].piece[j].limit_num+'" onclick="confirmEnrollingObject.selecttype(this)"></span>';
												m++;
											}else{
												group_code += '	<span class="group_select y" gid="'+d.times[i].piece[j].id+'" groups_price="'+d.times[i].piece[j].price+'" count="'+d.times[i].piece[j].limit_num+'" onclick="confirmEnrollingObject.selecttype(this)"></span>';
											}
										}else{
											group_code += '<div class="groups op">';
											group_code += '	<span class="group_select empty" gid="'+d.times[i].piece[j].id+'"></span>';
										}
										group_code += '	<span>'+d.times[i].piece[j].price+'元</span>';
										group_code += '<span>'+d.times[i].piece[j].count+'人</span>';
										group_code += '<span>'+d.times[i].piece[j].limit_time+'小时</span>';
										group_code += '</div>';
										group_code += '<div class="groups_line"></div>';
									}
								}else{
									//参团人，从拼团详情页进入
									for(var j in d.times[i].piece){
										if(d.times[i].piece[j].id == confirmEnrollingObject.groups_id){
												//默认限制开团人数
												confirmEnrollingObject.limit = d.times[i].piece[j].limit_num;
												//默认开团价格
												confirmEnrollingObject.buy_price = d.times[i].piece[j].price;
												group_code += '<div class="groups op">';
												group_code += '	<span class="group_select selected" gid="'+d.times[i].piece[j].id+'" groups_price="'+d.times[i].piece[j].price+'" count="'+d.times[i].piece[j].limit_num+'"></span>';
										}else{
											group_code += '<div class="groups op">';
											group_code += '	<span class="group_select empty" gid="'+d.times[i].piece[j].id+'"></span>';
										}
										group_code += '	<span>'+d.times[i].piece[j].price+'元</span>';
										group_code += '<span>'+d.times[i].piece[j].count+'人</span>';
										group_code += '<span>'+d.times[i].piece[j].limit_time+'小时</span>';
										group_code += '</div>';
										group_code += '<div class="groups_line"></div>';
									}
								}

								$('.page_confirmEnrolling .open_group .li_item').html(group_code);
							}else{
								if(d.times[i].stock == -1){
									if(parseInt(d.times[i].limit_num) !=0)
										confirmEnrollingObject.limit = d.times[i].limit_num;
									else
										confirmEnrollingObject.limit = 100000;
								}else{
									if(parseInt(d.times[i].limit_num) !=0 && parseInt(d.times[i].limit_num) < parseInt(d.times[i].stock)){
										confirmEnrollingObject.limit = d.times[i].limit_num;
									}else{
										confirmEnrollingObject.limit = d.times[i].stock;
									}
								}
							}
							if(d.times[i].limit_num != 0){
								$('.page_confirmEnrolling .Right .limit').html('(限购<font>'+d.times[i].limit_num+'</font>份)');
							}
							if(typeof(confirmEnrollingObject.groups_id) != 'undefined' && d.times[i].piece.length > 0){
								// var ps = [];
								// for(var a in d.times[i].piece){
								// 	ps.push({ d.times[i].piece[a]: d.times[i].piece[a].id,price: d.times[i].piece[a].price, count: d.times[i].piece[a].count, limit_time:d.times[i].piece[a].limit_time, can_buy:d.times[i].piece[a].can_buy})
								// }
								code += '<div class="t_right select" min="' + d.times[i].min_num + '" max="' + d.times[i].stock + '" start_time="' + d.times[i].start_time + '" end_time="' + d.times[i].end_time + '" times_id="' + d.times[i].id + '" groups_id=' + JSON.stringify(d.times[i].piece) + ' limit_num="' + d.times[i].limit_num + '" i="' + i + '" onclick="confirmEnrollingObject.selectTime(this)"></div>';
							}else {
								code += '<div class="t_right select" min="' + d.times[i].min_num + '" max="' + d.times[i].stock + '" start_time="' + d.times[i].start_time + '" end_time="' + d.times[i].end_time + '" times_id="' + d.times[i].id + '" limit_num="' + d.times[i].limit_num + '" i="' + i + '" onclick="confirmEnrollingObject.selectTime(this)"></div>';
							}
							var first = d.times[i].start_time.timeFormat("W <br>Y-m-d H:i")+'-'+d.times[i].end_time.timeFormat("H:i");
							$('.page_confirmEnrolling #times span').html(first);
							$('.page_confirmEnrolling #times span').attr('times_id',d.times[i].id);
							if(d.times[i].count==0){
								if(d.buy_status==2){
									$('.page_confirmEnrolling .b').text(d.times[i].min_num);
									confirmEnrollingObject.buy_num = d.times[i].min_num;
									confirmEnrollingObject.min_num = d.times[i].min_num;
								}
								if(d.times[i].lowest_num != 0){
									$('.page_confirmEnrolling .b').text(d.times[i].lowest_num);
									confirmEnrollingObject.buy_num = d.times[i].lowest_num;
									confirmEnrollingObject.min_num = d.times[i].lowest_num;
								}
								//$('.page_confirmEnrolling .isOR').show();
								//$('.page_confirmEnrolling .center_border').show();
								confirmEnrollingObject.two_min_num = d.times[i].min_num;
							}else{
								$('.page_confirmEnrolling .kill_all .cao_dan .buy_all').attr('no_book','no');
							}
						}else{
							if(typeof(confirmEnrollingObject.groups_id) != 'undefined' && d.times[i].piece.length > 0){
								code += '<div class="t_right yes" min="' + d.times[i].min_num + '" max="' + d.times[i].stock + '" start_time="' + d.times[i].start_time + '" end_time="' + d.times[i].end_time + '" times_id="' + d.times[i].id + '" groups_id=' + JSON.stringify(d.times[i].piece) + ' limit_num="' + d.times[i].limit_num + '" i="' + i + '" onclick="confirmEnrollingObject.selectTime(this)"></div>';
							}else {
								code += '<div class="t_right yes" min="' + d.times[i].min_num + '" max="' + d.times[i].stock + '" start_time="' + d.times[i].start_time + '" end_time="' + d.times[i].end_time + '" times_id="' + d.times[i].id + '" limit_num="' + d.times[i].limit_num + '" i="' + i + '" onclick="confirmEnrollingObject.selectTime(this)"></div>';
							}

						}
					}
					code +='</div>';
					$('.page_confirmEnrolling .center_list').html(code);
				}
			}

			//插入单价
			if(confirmEnrollingObject.groups_id){
				confirmEnrollingObject.buy_price = confirmEnrollingObject.buy_price
			}else{
				confirmEnrollingObject.buy_price = d.buy_price;
			}
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
				if(typeof(confirmEnrollingObject.groups_id) != 'undefined' && confirmEnrollingObject.groups_id != 0)data.type_piece_id = confirmEnrollingObject.groups_id;
				if(typeof(confirmEnrollingObject.piece_oid) != 'undefined' && confirmEnrollingObject.piece_oid != 0)data.piece_originator_id = confirmEnrollingObject.piece_oid;
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
                        case 'groupmessage':
                            data.from = 1;
                            break;
                        default:
                            data.from = 2;
                            break;
                    }
                    data.platform = 0;
                }

				data.context = $(".page_confirmEnrolling .leaveWords .words").val();
				var buy_price = $('.page_confirmEnrolling #buy_price').text();
				//提交订单
				ajax('Order/Index/create', data, function(d){
					if(d.status == 1){
						//jump('payMoney', {order_id : d.info.order_id});
						confirmEnrollingObject.order_id = d.info.order_id;
						if(win.get.android){
							window.location.href = 'http://' + DOMAIN + '/order/pay/submitAlipay.do?token='+ win.token +'&order_id=' + d.info.order_id;
						}else if(d.info.piece_originator_id){
							if(d.info.is_member_piece == 1){
								//参团
								wxpay(d.info.order_id,buy_price,d.info.limit_pay_time,3,d.info.piece_originator_id);
							}else{
								//开团
								wxpay(d.info.order_id,buy_price,d.info.limit_pay_time,3,d.info.piece_originator_id,d.info.surplus_num);
							}
						}else{
							wxpay(d.info.order_id,buy_price,d.info.limit_pay_time);
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
