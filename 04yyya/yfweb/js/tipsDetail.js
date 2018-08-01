var tipsDetailObject = {
	tips_id : null,
	can_buy_time:1,
	times : 0,
	//闪购
	flash_buy : function (d, $em){
		$em.find('.title font').text(d.title);
		$em.find('.sy span').text(d.limit);
		var time = Math.round(new Date().getTime()/1000);
		if(time < d.start_time){
			$em.find('.w').text('距离开抢');
			tipsDetailObject.countdown($em, d.start_time, tipsDetailObject.flash_buy);
		}else if(time < d.end_time){
			$em.find('.w').text('距离结束');
			$('.page_tipsDetail.priceMenu .price').html('<span class="l">￥'+ parseFloat(d.price).priceFormat() +'</span><span class="r">' + $('.page_tipsDetail.priceMenu .price').html() + '</span>');
			$em.find('span').css({'background':'#f1da72', 'color':'#F30'});
			tipsDetailObject.countdown($em, d.end_time, tipsDetailObject.flash_buy);
		}else{
			$em.remove();
			$('.page_tipsDetail.priceMenu .price').html($('.page_tipsDetail.priceMenu .price .r').html());
		}
	},

	//闪购倒计时函数
	//countdown : function($em, stime, fn){
	//	var time = Math.round(new Date().getTime()/1000);
	//	if(stime - time <= 0){
	//		fn();
	//		return;
	//	}
	//	$em.find('.h').text(Math.floor((stime - time) / 3600));
	//	$em.find('.i').text(Math.floor((stime - time) % 3600 / 60));
	//	$em.find('.s').text(Math.floor((stime - time) % 3600 % 60));
	//
	//	window.setTimeout(function(){
	//		tipsDetailObject.countdown($em, stime, fn);
	//	}, 1000);
	//},

	//提交订单
	submitOrder : function(){
		if(tipsDetailObject.can_buy_time == 0){$.alert('名额已满', 'error'); return false;}
		if(tipsDetailObject.can_buy_time == -1){$.alert('已截止报名', 'error'); return false;}
		var time_id = $('.page_tipsDetail .b_start').attr('time_id');
		jump('confirmEnrolling', {tips_id : tipsDetailObject.tips_id,time_id:time_id});
	},

	//提交包场订单
	submitAllOrder : function(){
		jump('confirmEnrolling', {tips_id : tipsDetailObject.tips_id, bugAll : 1});
	},

	showTimes : function(){
		$('.page_tipsDetail .timesLay').fadeIn('fast');
		$('.page_tipsDetail #mainContent').addClass('G_content');
		document.body.style.overflow = 'hidden';
		document.ontouchmove = function(e){e.preventDefault();} //文档禁止 touchmove事件
	},
	hideCode : function(){
		$('.page_tipsDetail .timesLay').css('display','none');
		$('#mainContent').removeClass('G_content');
		document.body.style.overflow='visible';
		document.ontouchmove = function(e){} //文档禁止 touchmove事件
	},
	selectTime : function(em){
		if($(em).hasClass("yes")){
			$(em).addClass('select').removeClass('yes');
			$(em).parent().siblings().children('.select').addClass('yes').removeClass('select');
		}else{
			$(em).addClass('yes').removeClass('select');
		}
	},
	//菜单转义函数
	menuStrToBase:function(str){
		str = str.replace('[_maohao_]', ':');
		str = str.replace('，', ',');
		str = str.replace('[_aite_]', '@');
		return str;
	},
	DecTimes:function(){
		clearInterval(win.tipsDetailInterval);
		win.tipsDetailInterval = setInterval(function(){
			try {
				var days = Math.floor(tipsDetailObject.times / 24 / 3600);
				var hours = Math.floor(tipsDetailObject.times % (24 * 3600) / 3600);
				var mins = Math.floor((tipsDetailObject.times % (24 * 3600) % 3600) / 60);
				var secs = Math.floor((tipsDetailObject.times % (24 * 3600) % 3600) % 60);

				var str = '';
				if(days > 0){
					if(parseInt(days) < 10){
						str += '<font>0</font><font>'+days + '</font>天';
					}else{
						var d = days.toString().split('');
						str += '<font>'+d[0]+'</font><font>'+d[1] + '</font>天';
					}
				}
				if(days > 0 || hours > 0){
					if(parseInt(hours) < 10){
						str += '<font>0</font><font>'+hours + '</font>时';
					}else{
						var h = hours.toString().split('');
						str += '<font>'+h[0]+'</font><font>'+h[1] + '</font>时';
					}
				}
				if(days > 0 || hours > 0 || mins > 0){
					if(parseInt(mins) < 10){
						str += '<font>0</font><font>'+mins + '</font>分';
					}else{
						var m = mins.toString().split('');
						str += '<font>'+m[0]+'</font><font>'+m[1] + '</font>分';
					}
				}
				if(days > 0 || hours > 0 || mins > 0 || secs > 0){
					if(parseInt(secs) < 10){
						str += '<font>0</font><font>'+secs + '</font>秒';
					}else{
						var s = secs.toString().split('');
						str += '<font>'+s[0]+'</font><font>'+s[1] + '</font>秒';
					}
					$('.page_tipsDetail .timebox .downtime').html(str);
					tipsDetailObject.times --;
				} else {
					clearInterval(win.tipsDetailInterval);
					$('.page_tipsDetail .timebox').hide();
					$('.page_tipsDetail .submitBtn').css({'background':'#b39851'}).html('JOIN').on('click', function(){
						tipsDetailObject.submitOrder();
					});
				}
			}catch(e){
				clearInterval(win.tipsDetailInterval);
			}
		}, 1000);
	},
    shareSuccess: function (id) {
        return function(target) {
            ajax('Home/Index/shareSuccess', {type: 0, item_id: id, target: target, platform: 1});
        }
    },
	onload : function(){
		tipsDetailObject.tips_id = win.get.tips_id;

		var doAsk = $('<a class="query" onclick="javascript:jump(\'ask\', {tips_id:'+ tipsDetailObject.tips_id +'})">提问</a>').appendTo('.page_tipsDetail .questionAndAnswer .actInfoMenu');
		$('.xxniu').click(function(){
			$('.Times').hide();
		});
		$('.more_time').click(function(){
			$('.Times').show();
		});
		$('.chat').click(function(){
			if(win.ws.power == 0){
				$.alert('在线客服系统已关闭。请添加我有饭客服微信:woyoufan-beijing','error');
				return;
			}else{
				jump('MsgIM');
			}
		});
		$('.page_tipsDetail .menu_lists span').click(function(){
			var str = $(this).attr('data');
			var div = $('.page_tipsDetail .'+ str);
			$('.page_tipsDetail.wrapper').animate({'scrollTop':div.position().top - 90}, 'fast', function(){
				$('.page_tipsDetail .menu_lists span.b_yellow').removeClass('b_yellow');
				$('.page_tipsDetail .menu_lists span[data="'+ str +'"]').addClass('b_yellow');
			});
		});

		//滚动条判断
		$('.page_tipsDetail.wrapper').scroll(function(){
			//判断并悬浮菜单按钮
			if($(this).scrollTop() > $('.page_tipsDetail .menues .menu_lists').position().top - 45){
				$('.fixed.page_tipsDetail .menu_lists').show();
			}
			//判断并清除菜单按钮
			if($(this).scrollTop() < $('.page_tipsDetail .menues .menu_lists').position().top - 45){
				$('.fixed.page_tipsDetail .menu_lists').hide();
			}
			//判断并转移菜单按钮
			if($(this).scrollTop() > $('.page_tipsDetail .menues .menu_lists').position().top - 45 && $(this).scrollTop() < $('.page_tipsDetail .daren_menu').position().top - 92 && !$('.page_tipsDetail .menu_lists span[data="daren_pic"]').hasClass('b_yellow')){
				$('.page_tipsDetail .menu_lists span.b_yellow').removeClass('b_yellow');
				$('.page_tipsDetail .menu_lists span[data="daren_pic"]').addClass('b_yellow');
			}else if($(this).scrollTop() > $('.page_tipsDetail .daren_menu').position().top - 92 && $(this).scrollTop() < $('.page_tipsDetail .daren_ment').position().top - 92 && !$('.page_tipsDetail .menu_lists span[data="daren_menu"]').hasClass('b_yellow')){
				$('.page_tipsDetail .menu_lists span.b_yellow').removeClass('b_yellow');
				$('.page_tipsDetail .menu_lists span[data="daren_menu"]').addClass('b_yellow');
			}else if($(this).scrollTop() > $('.page_tipsDetail .daren_ment').position().top - 92 && !$('.page_tipsDetail .menu_lists span[data="daren_ment"]').hasClass('b_yellow')){
				$('.page_tipsDetail .menu_lists span.b_yellow').removeClass('b_yellow');
				$('.page_tipsDetail .menu_lists span[data="daren_ment"]').addClass('b_yellow');
			}
		});
		ajax('Goods/Tips/getDetail', {'tips_id':tipsDetailObject.tips_id}, function(d){
			if((d.address && d.price!=null && d.time && d.times && d.title && d.min_num!=null && d.restrict_num!=null) || location.href.indexOf('tipsDetail') > 0){

				var desc = '';
				for(var i in d.edge){
					desc += d.edge[i] + ' ';
				}
				var url = win.host + '?page=choice-tipsDetail&tips_id=' + tipsDetailObject.tips_id;
				if(member && member.invitecode){
					url += '&type=1&invitecode=' + member.invitecode;
				}
				share(d.title, desc, url, d.mainpic, tipsDetailObject.shareSuccess(tipsDetailObject.id));

				tipsDetailObject.defaultPics = d.defaultPics;
				//分享绑定
				$('.page_tipsDetail .share').click(function(){
					showShareBox();
				});
				$('.header.page_tipsDetail .title').text(d.catname);

				//活动标题
				$('.page_tipsDetail .activityTitle').text(d.title);
				//会员昵称
				$('.page_tipsDetail .userNm').text(d.nickname);

				script.load('plugins/scrollByJie', function(){
					//主图
					if(d.pics_group && d.pics_group.length > 0){
						var sol = new myScroll();
						sol.speed = 3;
						sol.div = ".page_tipsDetail .bodyTop";
						for(var i in d.pics_group){
							sol.src.push(d.pics_group[i]);
						}
						sol.start();
					}else{
						$('.page_tipsDetail .bodyTop').html('<img src="images/actImg.jpg">');
					}

					//主图
					if(d.menu_pics_group && d.menu_pics_group.length > 0){
						var se = new myScroll();
						se.speed = 3;
						se.div = ".page_tipsDetail .menu_b";
						for(var i in d.menu_pics_group){
							se.src.push(d.menu_pics_group[i]);
						}
						se.start();
					}else{
						$('.page_tipsDetail .menu_b').html('<img src="images/Group 6@2x.png"/>');
					}
				});

				//顶部标题
				//$('.header .title').text(d.catname);
				//主标题
				$('.page_tipsDetail .them_title .title_t').text(d.title);
				//副标题
				//if(d.title_sub){
				//	$('.page_tipsDetail .title_c').text(d.title_sub).hide();
				//}
				//亮点
				var edges='';
				// var i = $('.page_tipsDetail .select').attr('i');
				for(var i in d.edge){
					edges +='<p class="t_b"><font>◆</font>'+ d.edge[i] +'</p>';
				}
				$('.page_tipsDetail .edges').html(edges);
				//成局
				$('.page_tipsDetail .b_model').text('（'+ d.time.min_num +'人成局，最多接待'+ d.time.max_num +'人）');
				//地点
				$('.page_tipsDetail .b_adress').text(d.address);
				//时间
				$('.page_tipsDetail .b_start').text(d.time.start_time.timeFormat('Y-m-d（W） H:i') + '-' + d.time.end_time.timeFormat('H:i'));
				$('.page_tipsDetail .b_start').attr('time_id',d.time.id);
				$('.page_tipsDetail .b_end').text('报名截止时间：' + d.time.stop_buy_time.timeFormat('Y-m-d H:i'));

				//达人头像
				if(d.cover_path){
					$('.page_tipsDetail .zhu_pic').attr('src', d.cover_path).attr('onerror', "javascript:this.src='"+ d.cover_path.replace(/_.+?\./, '.') +"'");
				}else{
					$('.page_tipsDetail .zhu_pic').attr('src', 'images/actImg.jpg');
				}
				$('.page_tipsDetail .daren_headpic').click(function(){
					jump('daRen', {member_id : d.daRen_id});
				});
				if(d.headpic){
					$('.page_tipsDetail .daren_headpic img').attr('src', d.headpic);
				}else{
					$('.page_tipsDetail .daren_headpic img').attr('src', 'images/head.jpg');
				}
				//判断是否已关注达人
				if(d.isfollow)$('.page_tipsDetail .followBtn button').addClass('valued');
				$('.page_tipsDetail .followBtn button').attr('data', d.daRen_id).click(function(){
					setFollow(this, function(d){
						if(d){
							$('.page_tipsDetail .fans').text(parseInt($('.page_tipsDetail .fans').text()) + 1);
						}else{
							$('.page_tipsDetail .fans').text(parseInt($('.page_tipsDetail .fans').text()) - 1);
						}
					});
				});
				//判断是否已经收藏
				if(d.isCollect)$('.page_tipsDetail .collect').addClass('Collected');
				$('.page_tipsDetail .collect').attr('data', tipsDetailObject.tips_id).click(function(){
					setCollect(this);
				});

				$('.page_tipsDetail .daren_headpic span').text(d.nickname);

				var strs = '<span class="act_left">活动</span><span class="act_right">'+ d.tips +'</span><span class="dian"></span><span class="act_left">赏味</span><span class="act_right">'+ d.shangwei +'</span><span class="dian"></span><span class="act_left">粉丝</span><span class="act_right fans">'+ d.follow_num*3 +'</span>';
				$('.page_tipsDetail .activity_list').html(strs);
				//简介
				var context = d.introduce;
				$('.page_tipsDetail .intro_content').html(context);

				//菜单
				var menues = '';
				if(d.menu){
					for(var i in d.menu){
						if(d.menu[i].value == '' || (d.menu[i].value[0] && d.menu[i].value[0] == '')){
							continue;
						}
						if(d.menu[i].name.toLowerCase() == 'tips'){
							$('<p align="center" class="menu_btitle"><img src="images/tips_icon@2x.png"/><span>'+ d.menu[i].value +'</span></p>').appendTo('.page_tipsDetail .daren_menu');
						}else{
							menues +='<p align="center" class="menu_yellow">- '+ d.menu[i].name +' -</p>';
							for(var j in d.menu[i].value){
								menues +='<p align="center" class="me_li">'+ tipsDetailObject.menuStrToBase(d.menu[i].value[j]) +'</p>';
							}
						}
					}
					$('.page_tipsDetail .menu_es').html(menues);
				}
				if(d.environment_pics_group_id && d.environment_pics_group_id.length > 0){
					var picess = '';
					for(var i in d.environment_pics_group_id){
						picess +='<p align="center" class="ment_b"><img src="'+ d.environment_pics_group_id[i] +'"/></p>';
					}
					$('.page_tipsDetail .picess').html(picess);
				}else{
					$('.page_tipsDetail .picess').html('<p align="center" class="ment_b"><img src="images/Group 6@2x.png"/></p>');
				}

				//价格
				$('.page_tipsDetail .price').html(d.price+'<font>元/份</font>');

				for(var i in d.times){
					if(d.times[i].start_time + d.stop_buy_time > (new Date()).getTime()/1000 && $('.page_tipsDetail .times').text() == ''){
						$('.page_tipsDetail .times').addClass('multiple');
						$('.page_tipsDetail .times').html(d.times[i].start_time.timeFormat('m-d W H:i') + '~' + d.times[i].end_time.timeFormat('H:i') + '<span class="arrowRight"></span>');
					}
					$('.page_tipsDetail .timesLay .b').append('<div><p>'+ d.times[i].start_time.timeFormat('Y-m-d W') + '</p><p>'+ d.times[i].start_time.timeFormat('H:i ~ ') + d.times[i].end_time.timeFormat('H:i') +'</p>' +'<p>已报名'+ d.times[i].count +'人</p></div>');
				}
				//查看地图
				$('.page_tipsDetail a.showMap').click(function(){
					//var u = navigator.userAgent;
					//if(!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/)){
					jump('map', {latitude: d.latitude, longitude: d.longitude, name: d.simpleaddress});
					//}else{
					//	wechat.openLocation({
					//		latitude: d.latitude,
					//		longitude: d.longitude,
					//		name: d.simpleaddress, // 位置名
					//		address: d.address, // 地址详情说明
					//		scale: 15 // 地图缩放级别,整形值,范围从1~28。默认为最大
					//		//infoUrl: '' // 在查看位置界面底部显示的超链接,可点击跳转
					//	});
					//}
				});
				//活动详情
				//$('.page_tipsDetail .content').html(d.content);

				//更多时间
				var code ='';
				var n = -1;
				for(var i in d.times){
					code +='<div class="list_t">';
					if(d.times[i].stock <= 0 || d.times[i].stop_buy_time < (new Date()).getTime()/1000){
						code +='<div class="t_left none">';
					}else{
						if(n == -1)n = i;
						code +='<div class="t_left">';
					}
					code +='   <span class="top">'+ d.times[i].start_time.timeFormat("Y-m-d (W)") +'</span>';
					if(d.times[i].stock <= 0){
						code +='   <span>'+ d.times[i].start_time.timeFormat("H:i")+ '-' + d.times[i].end_time.timeFormat("H:i") +'  名额已满</span>';
					}else if(d.times[i].stop_buy_time < (new Date()).getTime()/1000){
						code +='   <span>'+ d.times[i].start_time.timeFormat("H:i")+ '-' + d.times[i].end_time.timeFormat("H:i") +'  已截止报名</span>';
					}else{
						code +='   <span>'+d.times[i].start_time.timeFormat("H:i")+ '-' + d.times[i].end_time.timeFormat("H:i") +'  已经报名<font>'+ d.times[i].count+'</font>人</span>';
					}
					code +='</div>';
					if(d.times[i].stock <= 0 || d.times[i].stop_buy_time < (new Date()).getTime()/1000){
						code +='<div class="t_right empty"></div>';
					}else{
						code +='<div class="t_right yes" start_time="'+ d.times[i].start_time +'" end_time="'+ d.times[i].end_time +'" i="'+ i +'" onclick="tipsDetailObject.selectTime(this)" stop_buy_time="'+ d.times[i].stop_buy_time +'" start_buy_time="'+ d.times[i].start_buy_time +'" time_id="'+ d.times[i].id +'"></div>';
					}
					code +='</div>';
				}

				$('.page_tipsDetail .center_list').html(code);
				//选择时间
				$('.page_tipsDetail .time_but').click(function(){
					var start_time = $('.page_tipsDetail .select').attr('start_time');
					var start_buy_time = $('.page_tipsDetail .select').attr('start_buy_time');
					var stop_buy_time = $('.page_tipsDetail .select').attr('stop_buy_time');
					var end_time = $('.page_tipsDetail .select').attr('end_time');
					var i = $('.page_tipsDetail .select').attr('i');
					var time_id = $('.page_tipsDetail .select').attr('time_id');
					if(start_time==null){
						$('.page_tipsDetail .Times').hide();
						return false;
					}
					if(parseInt(start_buy_time) > (new Date()).getTime() / 1000){
						$('.page_tipsDetail .timebox').show();
						$('.page_tipsDetail .submitBtn').removeAttr('onClick').css({'background':'#aaa'});
						$('.page_tipsDetail .submitBtn').off('click');
						tipsDetailObject.times = parseInt(start_buy_time) - Math.round((new Date()).getTime() / 1000);
						tipsDetailObject.DecTimes();
					}else{
						$('.page_tipsDetail .timebox').hide();
						$('.page_tipsDetail .submitBtn').css({'background':'#b39851'}).html('JOIN').on('click', function(){
							tipsDetailObject.submitOrder();
						});
					}
					//时间
					$('.page_tipsDetail .b_start').text(start_time.timeFormat('Y-m-d（W） H:i') + '-' + end_time.timeFormat('H:i'));
					$('.page_tipsDetail .b_start').attr('time_id',time_id);
					$('.page_tipsDetail .b_end').text('报名截止时间：' + stop_buy_time.timeFormat('Y-m-d H:i'));
					$('.page_tipsDetail .b_model').text('（'+ d.times[i].min_num +'人成局，最多接待'+ d.times[i].max_num +'人）');
					if(d.times[i].member_info.length > 0){
						$('.page_tipsDetail .have_man .entered').text( d.times[i].count);
						var number_str ='';
						for(var j in d.times[i].member_info){
							number_str += '<div class="header_pics">';
							if(d.times[i].member_info[j].member_id == d.daRen_id){
								number_str +='	<img src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg"/>';
							}else{
								if(d.times[i].member_info[j].path){
									number_str +='	<img src="'+ d.times[i].member_info[j].path +'"/>';

								}else{
									number_str +='	<img src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg"/>';
								}
							}
							number_str +='	<span>×'+ d.times[i].member_info[j].count+'</span>';
							number_str +='<p>'+d.times[i].member_info[j].nickname+'</p>';
							number_str +='</div>';
							if(j == 3){
								number_str +='<span class="more_head">•••</span>';
							}
						}
						$('.page_tipsDetail .pices').html(number_str);
						$('.page_tipsDetail .pices').removeClass('more').show();
						$('.page_tipsDetail .pices .more_head').nextAll().hide();
						$('.page_tipsDetail .pices .more_head').one('click', function(){
							$(this).remove();
							$('.page_tipsDetail .pices').addClass('more').children().show();
						});

					}else{
						$('.page_tipsDetail .have_man .entered').text(0);
						$('.page_tipsDetail .pices').hide();
					}
					$('.page_tipsDetail .Times').hide();
				});
				if($('.page_tipsDetail .center_list .t_left').not('.none').size() > 0){
					if(n < 0)n = 0;
					$('.page_tipsDetail .center_list .t_right:eq('+ n +')').click();
					$('.page_tipsDetail .time_but').click();
					tipsDetailObject.can_buy_time = 1;
					//if(d.times.length > 5){
					//	var m = d.times.length - n - 1;
					//	if(m >= 4){
					//		var i = n;
					//		var s = i + 4;
					//	}else{
					//		var i = n - 5 + m + 1;
					//		if(i < 0)i=0;
					//		var s = i + 4;
					//	}
					//	$('.page_tipsDetail .center_list .list_t').each(function(){
					//		var index = $(this).index();
					//		if(index < i || index > s)$(this).hide();
					//	});
					//}
				} else {
					//已报名人数
					if(d.time.member_info.length > 0){
						$('.page_tipsDetail .have_man').text('已报名'+ d.time.count +'人');
						var number_str ='';
						for(var i in d.time.member_info){
							number_str += '<div class="header_pics">';
							if(d.time.member_info[i].member_id == d.daRen_id){
								number_str +='	<img src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg"/>';
							}else{
								if(d.time.member_info[i].path){
									number_str +='	<img src="'+ d.time.member_info[i].path +'"/>';
								}else{
									number_str +='	<img src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg"/>';
								}
							}
							number_str +='	<span>×'+ d.time.member_info[i].count+'</span>';
							number_str +='<p>'+d.time.member_info[i].nickname+'</p>';
							number_str +='</div>';
							if(i == 3){
								number_str +='<span class="more_head">•••</span>';
							}
						}
						$('.page_tipsDetail .pices').html(number_str);
						$('.page_tipsDetail .pices .more_head').nextAll().hide();
						$('.page_tipsDetail .pices .more_head').one('click', function(){
							$(this).remove();
							$('.page_tipsDetail .pices').addClass('more').children().show();
						});
					}else{
						$('.page_tipsDetail .pices').hide();
					}
					if(d.time.stock <= 0){
						tipsDetailObject.can_buy_time = 0;
						$('.page_tipsDetail .submitBtn').removeAttr('onClick').css('background','#aaa');
					}else if(d.time.stop_buy_time < (new Date()).getTime()/1000){
						tipsDetailObject.can_buy_time = -1;
						$('.page_tipsDetail .submitBtn').removeAttr('onClick').css('background','#aaa');
					}

				}

				//评论列表
				var code = '';
				if(d.comment.length>0){
					for(var i in d.comment){
						if(i==1){
							code += '<div class="com_list no_border_bottom">';
						}else{
							code += '<div class="com_list">';
						}
						code += '	<div class="h_pic">';
						if(d.comment[i].head_path != ''){
							code += '		<img src="'+ d.comment[i].head_path +'" />';
						}else {
							code += '		<img src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg" />';
						}
						code += '	</div>';
						code += '	<div class="pic_right">';
						code += '		<div class="name_title">';
						code += '			<div class="names">'+ d.comment[i].nickname +'</div>';
						code += '				<div class="starGrade">';
						for(var j=0; j<5; j++){
							if(j < d.comment[i].stars)
								code += '<span></span>';
							else
								code += '<span class="empty"></span>';
						}
						code += '				</div>';
						code += '		</div>';
						code += '<p align="center" class="t_content">'+ d.comment[i].content +'</p>';
						if(d.comment[i].pics.length > 0){
							code += '<div class="imges">';
							for(var j in d.comment[i].pics){
								code += '<img src="'+ d.comment[i].pics[j] +'">';
							}
							code += '</div>';
						}
						code += '<p class="report_time">';
						code += '			<span>'+ d.comment[i].datetime +'</span>';
						if(d.comment[i].is_report == 1)
							code += '			<span class="report">[已举报]</span>';
						else
							code += '			<span class="report" onclick="report('+d.comment[i].id+', this)">[举报]</span>';
						code += '</p>';
						code += '</div>';
						code += '</div>';
					}
					code +='<p align="center" class="more_com"><a href="javascript:jump(\'commentList\', {member_id:'+d.daRen_id+'})" class="allEvaluation"><span>查看更多</span></a></p>';
				}else{
					code +='<p align="center"><a href="javascript:void(0);" class="allEvaluation">暂时没有评价</a></p>';
				}
				$('.page_tipsDetail .commentList').html(code);
				//须知列表
				var code = '';
				for(var i in d.notice){
					code +='<div>'+ (parseInt(i)+1) +'、'+ d.notice[i] +'</div>';
				}
				$('.page_tipsDetail .tell_List').html(code);

				//尚未开始购买倒计时
				if(parseInt(d.time.start_buy_time) > (new Date()).getTime() / 1000){
					$('.page_tipsDetail .timebox').show();
					$('.page_tipsDetail .submitBtn').removeAttr('onClick').css({'background':'#aaa'});
					tipsDetailObject.times = parseInt(d.time.start_buy_time) - Math.round((new Date()).getTime() / 1000);
					tipsDetailObject.DecTimes();
				}

				//临时更改
				if(d.isFree == 1){
					$('.footer.priceMenu.page_tipsDetail .submitBtn').text('FREE JOIN');
				}
			}else{
				$.alert('数据不完整，无法正常访问', function(){
					page.back();
				}, 'error');
			}
			$('.page_tipsDetail .imges').each(function(){
				$(this).bubble();
			});
		}, 2);

	},
};



