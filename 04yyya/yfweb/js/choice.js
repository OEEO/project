var choiceObject = {
    winScrollSock: false,
    _top: 0,
    preLiNum: -1,//值 -1 表示 隐藏
    scrollNum: [0, 0, 0],
    tips_data: {},
    tips_page: 1,
    tips_length: null,
    tips_now_num: 1,
    theme_position: null,
    theme_list: null,
    theme_num: 0,
    theme_length: 0,
    is_finish: false,
    getLastTime: function (current, end, start, format, successed) {
        if (current < start) {
            return '即将上线';
        }
        var timediff = +end - +current;

        if (timediff <= 0 && successed >= 0) {
            return '已成功';
        } else if (timediff <= 0) {
            return '未成功';
        }

        var _f = {
            'd+': Math.floor(timediff / 86400),
            'h+': Math.floor(timediff % 86400 / 3600),
            'm+': Math.floor(timediff % 86400 % 3600 / 60),
            's+': Math.floor(timediff % 86400 % 60)
        };

        format = format || '';

        if (format.length > 0) {
            for (var k in _f) {
                if (new RegExp('(' + k + ')').test(format)) format = format.replace(RegExp.$1, (RegExp.$1.length === 1) ? (_f[k]) : (('00' + _f[k]).substr(('' + _f[k]).length)));
            }
        } else if (_f['d+'] > 0) {
            format = '剩余' + _f['d+'] + '天';
        } else if (_f['h+'] > 0) {
            format = '剩余' + _f['d+'] + '小时'
        } else if (_f['m+'] > 0) {
            format = '剩余' +  _f['d+'] + '分钟'
        } else if (_f['s+'] > 0) {
            format = '剩余' + _f['d+'] + '秒'
        }

        return format;
    },
    tempRaise: function () {
        var data = null;

        return data;
    },
    buildRaiseItem: function (d) {
        if (!d) {
            return '';
        }
        var code = '';
        var cur = parseInt(Date.now() / 1000);

        if (d.status === '2') {
            code += '<div class="items" onclick="$.alert(\'即将上线，敬请期待\')">';
        } else {
            code += '<div class="items" onclick="javascript:jump(\'raiseDetail\', {raise_id:' + d.id + '})">';
        }

        code += '<img class="raiseimg" src="' + d.path + '"/>';
        code += '<div class="items-top">';
        code += '<div class="raise-city"><img src="../images/row_button.png" />' + d.city_name + '</div>';
        code += '<span class="name">' + d.nickname + '</span>';
        // code +=         '<span class="address">广州</span>';
        code += '</div>';
        code += '<div class="raise-title">' + d.title + '</div>';
        code += '<p class="dec">' + d.introduction + '</p>';

        var per = +d.total === 0 ? 0 : (+d.totaled / +d.total) * 100;
        per = per.toFixed(2);
        code += '<div class="shell">';
        code += '<div class="c_line">';
        code += '<span style="width: ' + per + '%;"></span></div></div>';
        code += '<div class="sublist">';


        code += '<span><font name="percent">' + per + '</font>%</span>';
        code += '<span><font name="sum">' + (d.buyer_num || 0) + '</font>人认筹</span>';

        var end = +d.end_time;
        var time = choiceObject.getLastTime(cur, end, +d.start_time, null, d.totaled - d.total);
        code += '<span name="days">' + time + '</span></div>';

        code += '</div>';
        code += '<div class="the_blank"></div>';

        return code;
    },
    buildTipsItem: function (d) {
        var code = '';

        code += '<li>';
        code += '	<div class="pro_top">';
        code += '		<a href="javascript:jump(\'daRen\', {member_id:'+ d.member_id +'})" class="User_Img">';
        if(d.headpic !=''){
            code += '			<img class="imgPortrait" src="'+ d.headpic +'" />';
        }else{
            code += '		<img class="imgPortrait" src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg" />';
        }
        code += '		</a>';
        code += '	<div class="User_Conten">';
        code += '		<div class="Contens">';
        code += '			<div class="qUserName">'+ d.nickname +'</div>';
        code += '        		<div class="msgDetail">';
        code += '           		<div class="song_mi b_right">';
        code += '               		<font>粉丝:&nbsp;</font><span class="fanes">'+ d.follow_num*3 +'</span>人';
        code += '               	</div>';
        code += '           		<div class="song_mi">';
        code += '           			<font>赏味:&nbsp;</font>'+ d.customers +'人';
        code += '           		</div>';
        code += '        	 	</div>';
        code += '     		</div>';
        code += '		</div>';
        code += '	</div>';
        code += '   <div class="pro_center">';
        if(d.is_collect)
            code += '     			<button data="'+ d.id +'" class="Collected" onclick="setCollect(this)"></button>';
        else
            code += '     			<button data="'+ d.id +'" onclick="setCollect(this)"></button>';
        code += '   	<span>'+ d.catname +'</span>';
        if(d.sellout==1)
            code += '		<div class="sellout"><img src ="images/sellout.png" /></div>';
        if(d.catname == '课程'){
            code += '   	<a href="javascript:jump(\'courseDetail\', {tips_id:'+ d.id +'})">';
        }else if(d.p_tags_id == 76){
            code += '   	<a href="javascript:jump(\'specialDetail\', {tips_id:'+ d.id +'})">';
        }else{
            code += '   	<a href="javascript:jump(\'tipsDetail\', {tips_id:'+ d.id +'})">';
        }
        code += '   	<img src="'+ d.mainpic +'" /></a>';
        code += '	</div>';
        if(d.catname == '课程'){
            code += '	<div class="pro_title" onclick="javascript:jump(\'courseDetail\', {tips_id:'+ d.id +'});">';
        }else if(d.p_tags_id == 76){
            code += '	<div class="pro_title" onclick="javascript:jump(\'specialDetail\', {tips_id:'+ d.id +'});">';
        }else {
            code += '	<div class="pro_title" onclick="javascript:jump(\'tipsDetail\', {tips_id:' + d.id + '});">';
        }
        if(d.buy_status != 2){
            code += '		<span class="title_left">'+ d.title +'</span>';
        }else{
            code += '		<span class="title_left"><img src="images/buy_status.png" />&nbsp;'+ d.title +'</span>';
        }
        code += '	</div>';
        if(d.catname == '课程'){
            code += '	<div class="pro_buttom" onclick="javascript:jump(\'courseDetail\', {tips_id:'+ d.id +'});">';
        }else if(d.p_tags_id == 76){
            code += '	<div class="pro_buttom" onclick="javascript:jump(\'specialDetail\', {tips_id:'+ d.id +'});">';
        }else {
            code += '	<div class="pro_buttom" onclick="javascript:jump(\'tipsDetail\', {tips_id:' + d.id + '});">';
        }
        if(d.buy_status != 2)
            code += '		<span class="pro_time">'+ d.start_time.timeFormat('m-d W H:i') +'</span>';
        else
            code += '		<span class="pro_num">适合:'+ d.min_num +'-'+ d.restrict_num +'/人</span>';
        code += '		<span class="pro_adress">'+ (d.city) +'</span>';
        code += '  		<span class="price_right"><small>￥</small>'+ parseFloat(d.price) +'<small>/份</small></span>';
        code += '	</div>';
        code += '</li>';
        if(choiceObject.tips_now_num % choiceObject.theme_position == 0 && choiceObject.theme_num <= choiceObject.theme_length - 1){
            if(choiceObject.theme_list[choiceObject.theme_num]['url'].indexOf('http') == -1 ){
                code += '<a href="javascript:jump(\'themeDetail\',{theme_id:'+choiceObject.theme_list[choiceObject.theme_num]['id']+'})" class="p_banner"><img src="'+choiceObject.theme_list[choiceObject.theme_num]['path']+'"></a>';
            }else{
                code += '<a href="'+choiceObject.theme_list[choiceObject.theme_num]['url']+'" class="p_banner"><img src="'+choiceObject.theme_list[choiceObject.theme_num]['path']+'"></a>';
            }
            choiceObject.theme_num += 1;
        }

        if(choiceObject.tips_now_num == choiceObject.tips_length && choiceObject.theme_num <= choiceObject.theme_length -1){
            while(choiceObject.theme_num <= choiceObject.theme_length -1){
                if(choiceObject.theme_list[choiceObject.theme_num]['url'].indexOf('http') == -1 ){
                    code += '<a href="javascript:jump(\'themeDetail\',{theme_id:'+choiceObject.theme_list[choiceObject.theme_num]['id']+'})" class="p_banner"><img src="'+choiceObject.theme_list[choiceObject.theme_num]['path']+'"></a>';
                }else{
                    code += '<a href="'+choiceObject.theme_list[choiceObject.theme_num]['url']+'" class="p_banner"><img src="'+choiceObject.theme_list[choiceObject.theme_num]['path']+'"></a>';
                }
                choiceObject.theme_num += 1;
            }
        }
        choiceObject.tips_now_num += 1;

        return code;
    },
    //加载活动列表
    loadtips: function (data) {
        if (!data) data = {};
        else if (data.page) choiceObject.tips_page = data.page;

        //判断是否属于新的筛选
        if (choiceObject.tips_page == 1) {
            $('.page_choice .nothing').hide();
            for (var i in data) {
                if (i != 'page') {
                    if (data[i] == null)
                        delete choiceObject.tips_data[i];
                    else
                        choiceObject.tips_data[i] = data[i];
                }
            }
            $('#actList').empty();
        } else if ($('.page_choice .pro_list .the_end').size() > 0) {
            return;
        }
        $('.page_choice .tips_loading').show();
        (function tipsLoading(num) {
            var text = 'loading';
            for (var i = 0; i < num; i++) {
                text += '.';
            }
            $('.page_choice .tips_loading').html(text);
            if ($('.page_choice .tips_loading:visible').size() > 0) {
                window.setTimeout(function () {
                    tipsLoading(num >= 3 ? 0 : num + 1);
                }, 300);
            }
        })(1);
        $('.page_choice center').show();
        // setTimeout(function () {
        // 	ajax('Goods/Raise/getlist', { get: { page: choiceObject.tips_page } }, function (d) {
        // 		$('.page_choice center').hide();
        // 		if (d.info) {
        // 			$.alert(d.info, 'error');
        // 			return;
        // 		}

        // 		if (d.length > 0) {
        // 			var code = '';
        // 			var cur = parseInt(Date.now() / 1000);
        // 			for (var i in d) {
        // 				code += '<div class="items" onclick="javascript:jump(\'raiseDetail\', {raise_id:' + d[i].id + '})">';
        // 				code += '<img class="raiseimg" src="' + d[i].path + '"/>';
        // 				code += '<div class="items-top">';
        // 				// if (parseInt(d[i].start_time) > Math.round((new Date()).getTime() / 1000)) {
        // 				// 	code += '<span class="status">未开始</span>';
        // 				// } else if (parseInt(d[i].end_time) < Math.round((new Date()).getTime() / 1000)) {
        // 				// 	code += '<span class="status">已结束</span>';
        // 				// } else {
        // 				// 	code += '<span class="status">众筹中</span>';
        // 				// }
        // 				code += '<div class="raise-city"><img src="../images/row_button.png" />' + d[i].city_name + '</div>';
        // 				code += '<span class="name">' + d[i].nickname + '</span>';
        // 				// code +=         '<span class="address">广州</span>';
        // 				code += '</div>';
        // 				code += '<div class="raise-title">' + d[i].title + '</div>';
        // 				code += '<p class="dec">' + d[i].introduction + '</p>';

        // 				var per = +d[i].total === 0 ? 0 : (+d[i].totaled / +d[i].total).toFixed(2) * 100;
        // 				code += '<div class="shell">';
        // 				code += '<div class="c_line">';
        // 				code += '<span style="width: ' + per + '%;"></span></div></div>';
        // 				code += '<div class="sublist">';


        // 				code += '<span><font name="percent">' + per + '</font>%</span>';
        // 				code += '<span><font name="sum">' + (d[i].buyer_num || 0) + '</font>人认筹</span>';

        // 				var end = +d[i].end_time;
        // 				var time = choiceObject.getLastTime(cur, end, +d[i].start_time);
        // 				code += '<span name="days">' + time + '</span></div>';

        // 				code += '</div>';
        // 				code += '<div class="the_blank"></div>';
        // 			}

        // 			if (choiceObject.tips_page == 1)
        // 				$('.product_list').html(code);
        // 			else {
        // 				console.log('end');
        // 				$('.product_list').append(code);
        // 			}

        // 		} else {

        // 			if (choiceObject.tips_page == 1) {
        // 				$('.product_list').html('<div class="no_msgs"><img src="images/category_over.png" /><span>暂时还没有' + categoryObject.catg + '众筹哦~</span></div>');
        // 			} else {
        // 				$('.product_list').append('<li class="the_end"><div class="no_more"></div></li>');
        // 			}
        // 		}
        // 		choiceObject.winScrollSock = false;
        // 	}, false);
        // }, 200);
        setTimeout(function(){ajax('Goods/Home/getHomeList', {get:{page:choiceObject.tips_page}, post:choiceObject.tips_data}, function(d){
            $('.page_choice center').hide();
            var code = '';
            var isFirstRaise = true;

            for(var i in d){
                if (d[i].type === 0) {
                    code += choiceObject.buildTipsItem(d[i]);
                } else if (d[i].type === 1 && isFirstRaise) {
                    code += choiceObject.buildRaiseItem(choiceObject.tempRaise());
                    code += choiceObject.buildRaiseItem(d[i]);
                    isFirstRaise = false;
                } else {
                    code += choiceObject.buildRaiseItem(d[i]);
                }
            }
            // if(code == ''){
            // 	code = '<li class="the_end"><div class="no_more"></div></li>';
            // }
            code += '<li class="the_end"><div class="no_more"></div></li>';
            if(choiceObject.tips_page == 1)
                $('.page_choice .pro_list .product_list').html(code);
            else
                $('.page_choice .pro_list .product_list').append(code);
            choiceObject.winScrollSock = false;
            choiceObject.is_finish = true;
        })},200);
    },
    notice: function () {
        var ncode = '';
        ajax('Member/Index/getData', {}, function (d) {
            if (d.status == 0) {
                $.alert(d.info, 'error');
                return;
            }
            if (d.raise_order.order_status == 2 && d.raise_order.order_id != null && d.raise_order.order_id != '') {
                ncode += '<p class="noticetext">请您于' + d.raise_order.limit_pay_time.timeFormat('Y-m-d H:i') + '前支付《' + d.raise_order.raise_title + '》众筹项目的尾款' + d.raise_order.raise_times_retainage + '，过期视为自动放弃，名额将释放给其他候选人，谢谢您的支持！</p></div>';
                $.dialog(ncode, function () {
                    jump('orderRaiseDetail', { order_id: d.raise_order.order_id });
                }, 'true', 'noticeBox');
                $('#dialogBox.noticeBox .btns .closeBtn').text('');
                $('#dialogBox.noticeBox .btns .agree').text('立即支付');
                $('#dialogBox .context').before('<div class="noticepic"></div>');
            }
        });
    },
    onload: function () {
        // $('.page_choice .location').text(win.city.name);
        if ($('.page_choice.location_list').size() > 0) {
            $('.page_choice.location_list a').removeClass('yellow');
            $('.page_choice.location_list a[data="' + win.city.id + '"]').addClass('yellow');
        }

        $('.page_choice .ui-loader').remove();

        script.load('plugins/scrollByJie', function () {
            /***********ajax请求页面头部bander数据**************/
            ajax('Home/Index/banner', { type: 0 }, function (d) {
                var sol = new myScroll();
                sol.speed = 3;
                //sol.height = win.width * 0.4;
                sol.div = ".pageHead";
                for (var i in d) {
                    sol.src.push(d[i].path);
                    sol.link.push(d[i].url);
                }
                sol.start();
            });
        });

        $('<div class="back-to-top resourcesBox"><img src="images/back_top.png"/></div>').appendTo('body');
        ajax('Home/theme/getlist', { 'type': 1, 'url': 1 }, function (d) {
            choiceObject.tips_length = d.tips_count;
            choiceObject.theme_position = d.num;
            choiceObject.theme_list = d.list;
            choiceObject.theme_length = d.theme_count;
        });
        // $('.page_choice .location').click(function () {
        // 	if ($(this).hasClass('on')) {
        // 		$('.page_choice.location_list').fadeOut('fast');
        // 		$(this).removeClass('on');
        // 	} else {
        // 		if ($('.page_choice.location_list').size() == 0) {
        // 			$('<div class="page_choice location_list resourcesBox" onclick="$(\'.page_choice .location\').click();"><div class="list_name"></div></div>').appendTo('body');
        // 			ajax('Home/Index/citys', function (d) {
        // 				var num = 0;
        // 				var now_city = '';
        // 				var code = '';
        // 				for (var i in d) {
        // 					if (d[i] == win.city.name)
        // 						now_city = ' yellow';
        // 					else
        // 						now_city = '';
        // 					if (num % 3 == 1) {
        // 						code += '<a class="l_cen' + now_city + '" href="javascript:void(0);" data="' + i + '">' + d[i] + '</a>';
        // 					} else {
        // 						code += '<a class="' + now_city + '" href="javascript:void(0);" data="' + i + '">' + d[i] + '</a>';
        // 					}
        // 					num++;
        // 				}
        // 				$('.page_choice.location_list .list_name').html(code);
        // 				$('.page_choice.location_list').fadeIn('fast');
        // 				$('.page_choice.location_list .list_name a').click(function () {
        // 					ajax('Home/Index/ChangeCity', { 'city_id': $(this).attr('data') }, function (d) {
        // 						if (d.status == 1) {
        // 							if (window.localStorage) {
        // 								storage.set('city_id', d.info.id);
        // 								storage.set('city_name', d.info.name);
        // 							}
        // 							$.alert('切换成功', function () {
        // 								$('.page_choice .location').text(d.info.name);
        // 								$('.page_choice.location_list').remove();
        // 								win.city.id = d.info.id;
        // 								win.city.name = d.info.name;
        // 								page.reload();
        // 							});
        // 						} else {
        // 							$.alert(d.info, 'error');
        // 						}
        // 					});
        // 				});
        // 			});
        // 		} else {
        // 			$('.page_choice.location_list').fadeIn('fast');
        // 		}
        // 		$(this).addClass('on');
        // 	}
        // });
		/*屏幕滚动事件*/
        $('.page_choice.wrapper').scroll(function () {
            //滚动加载内容
            // if ($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !choiceObject.winScrollSock) {
            // 	choiceObject.winScrollSock = true;
            // 	choiceObject.loadtips({ page: Math.ceil($('.page_choice .product_list>li').size() / 5 + 1) });
            // }
            if (!choiceObject.is_finish) {

            }

            if ($('.page_choice.wrapper').scrollTop() > win.height * 2) {
                $(".back-to-top").fadeIn(500);
            } else {
                $(".back-to-top").fadeOut(500);
            }

        });
        $('.back-to-top').click(function () {
            $('.page_choice.wrapper').animate({ scrollTop: 0 }, 100);
            return false;
        });

        //加载商品列表
        //ajax('goods/goods/getlist', {}, function(d){
        //	if(d.info){
        //		$.alert(d.info, 'error');
        //	}else{
        //		var code = '';
        //		for(var i in d){
        //			code += '<a href="javascript:jump(\'goodsDetail\', {goods_id:'+ d[i].id +'});" class="item">';
        //			code += '	<img src="'+ d[i].path +'">';
        //			code += '	<div class="bottom">';
        //			code += '		<div class="t">'+ d[i].title +'</div>';
        //			code += '		<div class="b">';
        //			if(d[i].shipping == 0){
        //				code += '			<span class="post">包邮</span>';
        //			}else{
        //				code += '			<span class="left">已售<font>'+ d[i].cell_count +'</font>份</span>';
        //			}
        //			code += '			<div class="price">'+ d[i].price +'<small>元/份</small></div>';
        //			code += '		</div>';
        //			code += '	</div>';
        //			if(d[i].isCollect)
        //				code += '   <button data="'+ d[i].id +'" class="Collected" onclick="setCollect(this, 1)"></button>';
        //			else
        //				code += '   <button data="'+ d[i].id +'" onclick="setCollect(this, 1)"></button>';
        //			code += '</a>';
        //		}
        //
        //		$('.page_choice .goods_list .list').html(code).css('width', 27 * d.length + 'rem');
        //		var box = $('.page_choice .goods_list .plist');
        //		box.on('touchstart', function(event){
        //			var ev = event.originalEvent.targetTouches;
        //			//判断触摸数量
        //			if(ev.length == 1){
        //				//拖动处理
        //				choiceObject.itemMoveRange = box.scrollLeft();
        //				choiceObject.touchLeft = ev[0].pageX;
        //			}
        //		});
        //		box.on('touchmove', function(event){
        //			var ev = event.originalEvent.targetTouches;
        //			if(ev.length == 1){
        //				//拖动处理
        //				ev = ev[0];
        //				var x = ev.pageX;
        //				var left = choiceObject.itemMoveRange - x + choiceObject.touchLeft;
        //				box.scrollLeft(left);
        //			}
        //		});
        //	}
        //}, false);
    },
    onshow: function () {
        if (member) {
            //弹出刷新推送
            choiceObject.notice();
        }
        // 加载默认筛选列表
        choiceObject.loadtips();
    }
};




