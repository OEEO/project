var courseDetailObject = {
    tips_id : null,
    can_buy_time:1,
    times : 0,
    selectarray : [],
    //提交订单
    submitOrder : function(){
        if(courseDetailObject.can_buy_time == 0){$.alert('名额已满', 'error'); return false;}
        if(courseDetailObject.can_buy_time == -1){$.alert('已截止报名', 'error'); return false;}
        var time_id = $('.page_courseDetail .select').attr('time_id');
        jump('confirmEnrolling', {tips_id : courseDetailObject.tips_id,time_id:time_id,catname:'课程'});
    },
    //提交开团订单
    groupsOrder : function(){
        var time_id = $('.page_courseDetail .select').attr('time_id');
        jump('confirmEnrolling', {tips_id : courseDetailObject.tips_id,time_id:time_id, groups_id:0,catname:'课程'});
    },
    showTimes : function(){
        $('.page_courseDetail .timesLay').fadeIn('fast');
        $('.page_courseDetail #mainContent').addClass('G_content');
        document.body.style.overflow = 'hidden';
        document.ontouchmove = function(e){e.preventDefault();} //文档禁止 touchmove事件
    },
    hideCode : function(){
        $('.page_courseDetail .timesLay').css('display','none');
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
                var days = Math.floor(courseDetailObject.times / 24 / 3600);
                var hours = Math.floor(courseDetailObject.times % (24 * 3600) / 3600);
                var mins = Math.floor((courseDetailObject.times % (24 * 3600) % 3600) / 60);
                var secs = Math.floor((courseDetailObject.times % (24 * 3600) % 3600) % 60);

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
                    $('.page_courseDetail .timebox .downtime').html(str);
                    courseDetailObject.times --;
                } else {
                    clearInterval(win.tipsDetailInterval);
                    $('.page_courseDetail .timebox').hide();
                    if($(".page_courseDetail .select").attr("groups_id")){
                        $('.page_courseDetail.footer .coursebox .original_price').css({'background':'#bea76b'}).on('click', function(){
                            courseDetailObject.submitOrder();
                        });
                        $('.page_courseDetail.footer .coursebox .groups_price').css({'background':'#b39851'}).on('click', function(){
                            courseDetailObject.groupsOrder();
                        });
                    }else{
                        $('.page_courseDetail.footer .coursebox .original_price').off('click');
                        $('.page_courseDetail.footer .coursebox .groups_price').css({'background':'#b39851'}).on('click', function(){
                            courseDetailObject.submitOrder();
                        });
                    }
                }
            }catch(e){
                clearInterval(win.tipsDetailInterval);
            }
        }, 1000);
    },
    onload : function(){
        courseDetailObject.tips_id = win.get.tips_id;
        $('.page_courseDetail .chat').click(function(){
            if(win.ws.power == 0){
                $.alert('在线客服系统已关闭。请添加吖咪客服微信:yami194','error');
                return;
            }else{
                jump('MsgIM');
            }
        });
        // $('.page_courseDetail .timeboxbg').click(function(){
        //     $('.Times').hide();
        // });
        $('.page_courseDetail .timeicon').click(function(){
            $('.page_courseDetail .Times').show();
        });
        $('.page_courseDetail .menu_lists span').click(function(){
            var str = $(this).attr('data');
            var div = $('.page_courseDetail .'+ str);
            $('.page_courseDetail.wrapper').animate({'scrollTop':div.position().top - 90}, 'fast', function(){
                $('.page_courseDetail .menu_lists span.b_yellow').removeClass('b_yellow');
                $('.page_courseDetail .menu_lists span[data="'+ str +'"]').addClass('b_yellow');
            });
        });

        //滚动条判断
        $('.page_courseDetail.wrapper').scroll(function(){
            if($(this).scrollTop() > $('.page_courseDetail .bodyTop').height()){
                $('.page_courseDetail.header').removeClass('changeStyle');
                $('.page_courseDetail.header').addClass('line');
                $('.page_courseDetail.header a.collect.c2').show();
                $('.page_courseDetail.header a.collect.c1').hide();
                $('.page_courseDetail.header a.shared').css('background','url("../images/course-detail-page_share1@2x.png") no-repeat 0 0 / 1.728rem 1.728rem');
            }else{
                $('.page_courseDetail.header').addClass('changeStyle');
                $('.page_courseDetail.header').removeClass('line');
                $('.page_courseDetail.header a.collect.c2').hide();
                $('.page_courseDetail.header a.collect.c1').show();
                $('.page_courseDetail.header a.shared').css('background','url("../images/course-detail-page_share@2x.png") no-repeat 0 0 / 1.728rem 1.728rem');
            }
            //判断并悬浮菜单按钮
            if($(this).scrollTop() > $('.page_courseDetail .menues .menu_lists').position().top - 45){
                $('.fixed.page_courseDetail .menu_lists').show();
                // courseDetailObject.menuClone = $('.page_courseDetail .menu_lists').clone(true, true).appendTo('.fixed');
                // courseDetailObject.menuClone.addClass('menu_lay');
            }
            //判断并清除菜单按钮
            if($(this).scrollTop() < $('.page_courseDetail .menues .menu_lists').position().top - 45){
                $('.fixed.page_courseDetail .menu_lists').hide();
            }
            //判断并转移菜单按钮
            if($(this).scrollTop() > $('.page_courseDetail .menues .menu_lists').position().top - 45 && $(this).scrollTop() < $('.page_courseDetail .daren_pic').position().top - 92 && !$('.page_courseDetail .menu_lists span[data="daren_menu"]').hasClass('b_yellow')){
                $('.page_courseDetail .menu_lists span.b_yellow').removeClass('b_yellow');
                $('.page_courseDetail .menu_lists span[data="daren_menu"]').addClass('b_yellow');
            }else if($(this).scrollTop() > $('.page_courseDetail .daren_pic').position().top - 92 && $(this).scrollTop() < $('.page_courseDetail .daren_ment').position().top - 92 && !$('.page_courseDetail .menu_lists span[data="daren_pic"]').hasClass('b_yellow')){
                $('.page_courseDetail .menu_lists span.b_yellow').removeClass('b_yellow');
                $('.page_courseDetail .menu_lists span[data="daren_pic"]').addClass('b_yellow');
            }else if($(this).scrollTop() > $('.page_courseDetail .daren_ment').position().top - 92 && !$('.page_courseDetail .menu_lists span[data="daren_ment"]').hasClass('b_yellow')){
                $('.page_courseDetail .menu_lists span.b_yellow').removeClass('b_yellow');
                $('.page_courseDetail .menu_lists span[data="daren_ment"]').addClass('b_yellow');
            }
        });
        ajax('Goods/Tips/getDetail', {'tips_id':courseDetailObject.tips_id}, function(d){
            if((d.address && d.price!=null && d.time && d.times && d.title && d.min_num!=null && d.restrict_num!=null) || location.href.indexOf('tipsDetail') > 0){

                var desc = '';
                for(var i in d.edge){
                    desc += d.edge[i] + ' ';
                }
                var url = win.host + '?page=choice-courseDetail&tips_id=' + courseDetailObject.tips_id;
                if(member && member.invitecode){
                    url += '&type=1&invitecode=' + member.invitecode;
                }
                share(d.title, desc, url, d.mainpic);

                courseDetailObject.defaultPics = d.defaultPics;
                //分享绑定
                $('.page_courseDetail .shared').click(function(){
                    showShareBox();
                });
                $('.header.page_courseDetail .title').text(d.catname);

                //活动标题
                $('.page_courseDetail .activityTitle').text(d.title);
                //会员昵称
                $('.page_courseDetail .userNm').text(d.nickname);

                script.load('plugins/scrollByJie', function(){
                    //主图
                    if(d.all_pics && d.all_pics.length > 0){
                        var sol = new myScroll();
                        sol.speed = 3;
                        sol.div = ".page_courseDetail .bodyTop";
                        for(var i in d.all_pics){
                            sol.src.push(d.all_pics[i]);
                        }
                        sol.start();
                    }else{
                        $('.page_courseDetail .bodyTop').html('<img src="images/actImg.jpg">');
                    }

                    //主图
                    if(d.menu_pics_group && d.menu_pics_group.length > 0){
                        var se = new myScroll();
                        se.speed = 3;
                        se.div = ".page_courseDetail .menu_b";
                        for(var i in d.menu_pics_group){
                            se.src.push(d.menu_pics_group[i]);
                        }
                        se.start();
                    }
                });

                //顶部标题
                //$('.header .title').text(d.catname);
                //主标题
                $('.page_courseDetail .them_title .title_t').text(d.title);
                //副标题
                //if(d.title_sub){
                //	$('.page_courseDetail .title_c').text(d.title_sub).hide();
                //}
                //亮点
                var edges='';
                // var i = $('.page_courseDetail .select').attr('i');
                for(var i in d.edge){
                    edges +='<p class="t_b"><font>◆</font>'+ d.edge[i] +'</p>';
                }
                $('.page_courseDetail .edges').html(edges);
                //是否拼团
                if(d.time.piece.length > 0){
                    $('.page_courseDetail.footer .coursebox .original_price').html('<big>￥<font>'+d.price+'</font></big><small>原价购买</small>');
                    $('.page_courseDetail.footer .coursebox .groups_price').html('<big>￥<font>'+d.time.piece[0].price+'</font></big><small>拼团购买</small>');
                }else{
                    $('.page_courseDetail.footer .coursebox .original_price').html('<font>'+d.price+'</font>元');
                    $('.page_courseDetail.footer .coursebox .groups_price').html('JOIN');
                }
                //地点
                $('.page_courseDetail .b_adress').text(d.address);
                // //时间
                // $('.page_courseDetail .b_start').text(d.time.start_time.timeFormat('Y-m-d（W） H:i') + '-' + d.time.end_time.timeFormat('H:i'));
                // $('.page_courseDetail .b_start').attr('time_id',d.time.id);
                // if(d.time.piece.length > 0){
                //     $('.page_courseDetail .b_start').attr('groups_id',d.time.piece[0].id);
                // }
                // $('.page_courseDetail .b_end').text('报名截止时间：' + d.time.stop_buy_time.timeFormat('Y-m-d H:i'));

                $('.page_courseDetail .daren_headpic').click(function(){
                    jump('daRen', {member_id : d.daRen_id});
                });
                //达人头像
                if(d.headpic){
                    $('.page_courseDetail .daren_headpic img').attr('src', d.headpic);
                }else{
                    $('.page_courseDetail .daren_headpic img').attr('src', 'images/head.jpg');
                }
                //判断是否已关注达人
                if(d.isfollow)$('.page_courseDetail .followBtn button').addClass('valued');
                $('.page_courseDetail .followBtn button').attr('data', d.daRen_id).click(function(){
                    setFollow(this, function(d){
                        if(d){
                            $('.page_courseDetail .fans').text(parseInt($('.page_courseDetail .fans').text()) + 1);
                        }else{
                            $('.page_courseDetail .fans').text(parseInt($('.page_courseDetail .fans').text()) - 1);
                        }
                    });
                });
                //判断是否已经收藏
                if(d.isCollect)$('.page_courseDetail .collect').addClass('Collected');
                $('.page_courseDetail .collect').attr('data', courseDetailObject.tips_id).click(function(){
                    setCollect(this);
                });
                //达人昵称
                $('.page_courseDetail .daren_headpic span').text(d.nickname);

                var strs = '<span class="act_left">活动</span><span class="act_right">'+ d.tips +'</span><span class="dian"></span><span class="act_left">赏味</span><span class="act_right">'+ d.shangwei +'</span><span class="dian"></span><span class="act_left">粉丝</span><span class="act_right fans">'+ d.follow_num*3 +'</span>';
                $('.page_courseDetail .activity_list').html(strs);
                //简介
                var context = d.introduce;
                $('.page_courseDetail .intro_content').html(context);

                //体验详情
                var menues = '';
                // if(d.menu){
                //     for(var i in d.menu){
                //         if(d.menu[i].value == '' || (d.menu[i].value[0] && d.menu[i].value[0] == '')){
                //             continue;
                //         }
                //         if(d.menu[i].name.toLowerCase() == 'tips'){
                //             $('<p align="center" class="menu_btitle"><img src="images/tips_icon@2x.png"/><span>'+ d.menu[i].value +'</span></p>').appendTo('.page_courseDetail .daren_menu');
                //         }else{
                //             menues +='<p align="center" class="menu_yellow">- '+ d.menu[i].name +' -</p>';
                //             for(var j in d.menu[i].value){
                //                 menues +='<p align="center" class="me_li">'+ courseDetailObject.menuStrToBase(d.menu[i].value[j]) +'</p>';
                //             }
                //         }
                //     }
                    $('.page_courseDetail .menu_es').html(d.content);
                // }
                //温馨提示
                // if(d.environment_pics_group_id && d.environment_pics_group_id.length > 0){
                //     var picess = '';
                //     for(var i in d.environment_pics_group_id){
                //         picess +='<p align="center" class="ment_b"><img src="'+ d.environment_pics_group_id[i] +'"/></p>';
                //     }
                //     $('.page_courseDetail .picess').html(picess);
                // }else{
                //     $('.page_courseDetail .picess').html('<p align="center" class="ment_b"><img src="images/Group 6@2x.png"/></p>');
                // }

                //查看地图
                $('.page_courseDetail a.showMap').click(function(){
                    jump('map', {latitude: d.latitude, longitude: d.longitude, name: d.simpleaddress});
                });

                //更多时间
                var code ='';
                var n = 1;
                for(var i in d.times){
                    if(d.times[i].stock > 0 && d.times[i].stop_buy_time >= (new Date()).getTime()/1000){
                        courseDetailObject.selectarray.push(d.times[i].start_time.timeFormat("Y-m-d"));
                        if(d.time.start_time.timeFormat("Y-m-d") === d.times[i].start_time.timeFormat("Y-m-d")){
                            code += '<div class="list_t">';
                            if(n == 1){
                                var g = 0;
                                if(d.times[i].piece.length > 0){
                                    for(var m in d.times[i].piece){
                                        if(d.times[i].piece[m].can_buy == 1){
                                            if(g == 0){
                                                code +='<div class="t_right select" start_time="'+ d.times[i].start_time +'" end_time="'+ d.times[i].end_time +'" i="'+ i +'" onclick=" courseDetailObject.selectTime(this)" stop_buy_time="'+ d.times[i].stop_buy_time +'" start_buy_time="'+ d.times[i].start_buy_time +'" time_id="'+ d.times[i].id +'" groups_id="'+ d.times[i].piece[m].id +'" groups_price="'+ d.times[i].piece[m].price +'"></div>';
                                                g++;
                                            }
                                        }
                                    }
                                }else{
                                    code +='<div class="t_right select" start_time="'+ d.times[i].start_time +'" end_time="'+ d.times[i].end_time +'" i="'+ i +'" onclick=" courseDetailObject.selectTime(this)" stop_buy_time="'+ d.times[i].stop_buy_time +'" start_buy_time="'+ d.times[i].start_buy_time +'" time_id="'+ d.times[i].id +'"></div>';
                                }
                                n++;
                            }else{
                                var g = 0;
                                if(d.times[i].piece.length > 0){
                                    for(var m in d.times[i].piece){
                                        if(d.times[i].piece[m].can_buy == 1){
                                            if(g == 0){
                                                code +='<div class="t_right yes" start_time="'+ d.times[i].start_time +'" end_time="'+ d.times[i].end_time +'" i="'+ i +'" onclick=" courseDetailObject.selectTime(this)" stop_buy_time="'+ d.times[i].stop_buy_time +'" start_buy_time="'+ d.times[i].start_buy_time +'" time_id="'+ d.times[i].id +'" groups_id="'+ d.times[i].piece[m].id +'" groups_price="'+ d.times[i].piece[m].price +'"></div>';
                                                g++;
                                            }
                                        }
                                    }
                                }else{
                                    code +='<div class="t_right yes" start_time="'+ d.times[i].start_time +'" end_time="'+ d.times[i].end_time +'" i="'+ i +'" onclick=" courseDetailObject.selectTime(this)" stop_buy_time="'+ d.times[i].stop_buy_time +'" start_buy_time="'+ d.times[i].start_buy_time +'" time_id="'+ d.times[i].id +'"></div>';
                                }
                            }
                            code += '    <span class="subtime">'+d.times[i].start_time.timeFormat("H:i")+'-'+d.times[i].end_time.timeFormat("H:i")+'</span>';
                            code += '<span class="muchp">剩 '+d.times[i].stock+' 位</span>';
                            code += '</div>';
                        }
                    }
                }
                $('.page_courseDetail .time_but').html(code);
                //选择时间
                $('.page_courseDetail .timeboxbg').click(function(){
                    $('.page_courseDetail.footer .coursebox .original_price').off('click');
                    $('.page_courseDetail.footer .coursebox .groups_price').off('click');
                    var start_time = $('.page_courseDetail .time_but .select').attr('start_time');
                    var start_buy_time = $('.page_courseDetail .time_but .select').attr('start_buy_time');
                    var stop_buy_time = $('.page_courseDetail .time_but .select').attr('stop_buy_time');
                    var end_time = $('.page_courseDetail .time_but .select').attr('end_time');
                    var i = $('.page_courseDetail .time_but .select').attr('i');
                    var time_id = $('.page_courseDetail .time_but .select').attr('time_id');
                    //判断是否有开团id
                    if(typeof($('.page_courseDetail .time_but .select').attr("groups_id")) != "undefined"){
                        var groups_id = $('.page_courseDetail .time_but .select').attr('groups_id');
                        var groups_price = $('.page_courseDetail .time_but .select').attr('groups_price');
                        // $('.page_courseDetail .b_start').attr('groups_id',groups_id);
                    }
                    if(start_time==null){
                        $('.page_courseDetail .Times').hide();
                        return false;
                    }
                    if(parseInt(start_buy_time) > (new Date()).getTime() / 1000){
                        $('.page_courseDetail .timebox').show();
                        if(groups_id){
                            $('.page_courseDetail.footer .coursebox .original_price').css({'background':'#ccc'}).html('<big>￥<font>'+d.price+'</font></big><small>原价购买</small>');
                            $('.page_courseDetail.footer .coursebox .groups_price').css({'background':'#bbb'}).html('<big>￥<font>'+groups_price+'</font></big><small>拼团购买</small>');
                            $('.page_courseDetail.footer .coursebox .original_price').off('click');
                            $('.page_courseDetail.footer .coursebox .groups_price').off('click');
                        }else{
                            $('.page_courseDetail.footer .coursebox .original_price').css({'background':'#ccc'}).html('<font>'+d.price+'</font>元');
                            $('.page_courseDetail.footer .coursebox .groups_price').css({'background':'#bbb'}).html('JOIN');
                            $('.page_courseDetail.footer .coursebox .original_price').off('click');
                            $('.page_courseDetail.footer .coursebox .groups_price').off('click');
                        }
                        courseDetailObject.times = parseInt(start_buy_time) - Math.round((new Date()).getTime() / 1000);
                        courseDetailObject.DecTimes();
                    }else{
                        $('.page_courseDetail .timebox').hide();
                        if(groups_id){
                            $('.page_courseDetail.footer .coursebox .original_price').css({'background':'#bea76b'}).html('<big>￥<font>'+d.price+'</font></big><small>原价购买</small>').on('click', function(){
                                courseDetailObject.submitOrder();
                            });
                            $('.page_courseDetail.footer .coursebox .groups_price').css({'background':'#b39851'}).html('<big>￥<font>'+groups_price+'</font></big><small>拼团购买</small>').on('click', function(){
                                courseDetailObject.groupsOrder();
                            });
                        }else{
                            $('.page_courseDetail.footer .coursebox .original_price').off('click');
                            $('.page_courseDetail.footer .coursebox .original_price').css({'background':'#bea76b'}).html('<font>'+d.price+'</font>元');
                            $('.page_courseDetail.footer .coursebox .groups_price').css({'background':'#b39851'}).html('JOIN').on('click', function(){
                                courseDetailObject.submitOrder();
                            });
                        }
                    }
                    //时间
                    $('.page_courseDetail .b_start').text(start_time.timeFormat('Y-m-d（W） H:i') + '-' + end_time.timeFormat('H:i'));
                    $('.page_courseDetail .b_start').attr('time_id',time_id);
                    $('.page_courseDetail .b_end').text('报名截止时间：' + stop_buy_time.timeFormat('Y-m-d H:i'));
                    $('.page_courseDetail .b_model').text('（'+ d.times[i].min_num +'人成局，最多接待'+ d.times[i].max_num +'人）');
                    if(d.times[i].member_info.length > 0){
                        $('.page_courseDetail .have_man .entered').text( d.times[i].count);
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
                        $('.page_courseDetail .pices').html(number_str);
                        $('.page_courseDetail .pices').removeClass('more').show();
                        $('.page_courseDetail .pices .more_head').nextAll().hide();
                        $('.page_courseDetail .pices .more_head').one('click', function(){
                            $(this).remove();
                            $('.page_courseDetail .pices').addClass('more').children().show();
                        });
                    }else{
                        $('.page_courseDetail .have_man .entered').text(0);
                        $('.page_courseDetail .pices').hide();
                    }
                    $('.page_courseDetail .Times').hide();
                });
                var c = '';
                $('.page_courseDetail .Times .time_title').datepicker({
                    time : d.time.start_time,
                    days : courseDetailObject.selectarray,
                    onselect : function(y, m, dd){
                        c = '';
                        n = 1;
                        if(m < 10){
                            m = '0'+m;
                        }
                        if(dd < 10){
                            dd = '0'+dd;
                        }
                        var timetext = y+ '-'+m+'-'+dd;
                        for(var j in d.times){
                            if(d.times[j].stock > 0 && d.times[j].stop_buy_time >= (new Date()).getTime()/1000){
                                if(timetext == d.times[j].start_time.timeFormat("Y-m-d")){
                                    c += '<div class="list_t">';
                                    if(n == 1){
                                        var g = 0;
                                        if(d.times[j].piece.length > 0){
                                            for(var m in d.times[j].piece){
                                                if(d.times[j].piece[m].can_buy == 1){
                                                    if(g == 0){
                                                        c +='<div class="t_right select" start_time="'+ d.times[j].start_time +'" end_time="'+ d.times[j].end_time +'" i="'+ j +'" onclick="courseDetailObject.selectTime(this)" stop_buy_time="'+ d.times[j].stop_buy_time +'" start_buy_time="'+ d.times[j].start_buy_time +'" time_id="'+ d.times[j].id +'" groups_id="'+ d.times[j].piece[0].id +'" groups_price="'+ d.times[j].piece[m].price +'"></div>';
                                                        g++;
                                                    }
                                                }
                                            }
                                        }else{
                                            c +='<div class="t_right select" start_time="'+ d.times[j].start_time +'" end_time="'+ d.times[j].end_time +'" i="'+ j +'" onclick="courseDetailObject.selectTime(this)" stop_buy_time="'+ d.times[j].stop_buy_time +'" start_buy_time="'+ d.times[j].start_buy_time +'" time_id="'+ d.times[j].id +'"></div>';
                                        }
                                        n++;
                                    }else{
                                        var g = 0;
                                        if(d.times[j].piece.length > 0){
                                            for(var m in d.times[j].piece){
                                                if(d.times[j].piece[m].can_buy == 1){
                                                    if(g == 0){
                                                        c +='<div class="t_right yes" start_time="'+ d.times[j].start_time +'" end_time="'+ d.times[j].end_time +'" i="'+ j +'" onclick="courseDetailObject.selectTime(this)" stop_buy_time="'+ d.times[j].stop_buy_time +'" start_buy_time="'+ d.times[j].start_buy_time +'" time_id="'+ d.times[j].id +'" groups_id="'+ d.times[j].piece[0].id +'" groups_price="'+ d.times[j].piece[m].price +'"></div>';
                                                        g++;
                                                    }
                                                }
                                            }
                                        }else{
                                            c +='<div class="t_right yes" start_time="'+ d.times[j].start_time +'" end_time="'+ d.times[j].end_time +'" i="'+ j +'" onclick="courseDetailObject.selectTime(this)" stop_buy_time="'+ d.times[j].stop_buy_time +'" start_buy_time="'+ d.times[j].start_buy_time +'" time_id="'+ d.times[j].id +'"></div>';
                                        }
                                    }
                                    c += '    <span class="subtime">'+d.times[j].start_time.timeFormat("H:i")+'-'+d.times[j].end_time.timeFormat("H:i")+'</span>';
                                    c += '<span class="muchp">剩 '+d.times[j].stock+' 位</span>';
                                    c += '</div>';
                                }
                            }
                        }
                        $('.page_courseDetail .time_but').html(c);
                    }
                });
                $('.page_courseDetail .timeboxbg').click();
                if($('.page_courseDetail .center_list .t_left').not('.none').size() > 0){
                    // if(n < 0)n = 0;
                    // $('.page_courseDetail .center_list .t_right:eq('+ n +')').click();
                    courseDetailObject.can_buy_time = 1;
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
                    //	$('.page_courseDetail .center_list .list_t').each(function(){
                    //		var index = $(this).index();
                    //		if(index < i || index > s)$(this).hide();
                    //	});
                    //}
                } else {
                    //已报名人数
                    if(d.time.member_info.length > 0){
                        $('.page_courseDetail .have_man').text('已报名'+ d.time.count +'人');
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
                        $('.page_courseDetail .pices').html(number_str);
                        $('.page_courseDetail .pices .more_head').nextAll().hide();
                        $('.page_courseDetail .pices .more_head').one('click', function(){
                            $(this).remove();
                            $('.page_courseDetail .pices').addClass('more').children().show();
                        });
                    }else{
                        $('.page_courseDetail .pices').hide();
                    }
                    if(d.time.stock <= 0){
                        courseDetailObject.can_buy_time = 0;
                        if(d.time.piece.length > 0){
                            $('.page_courseDetail.footer .coursebox .original_price').off('click').css('background','#ccc');
                            $('.page_courseDetail.footer .coursebox .groups_price').off('click').css('background','#bbb');
                        }else{
                            $('.page_courseDetail.footer .coursebox .original_price').off('click').css('background','#ccc');
                            $('.page_courseDetail.footer .coursebox .groups_price').off('click').css('background','#bbb');
                        }
                    }else if(d.time.stop_buy_time < (new Date()).getTime()/1000){
                        courseDetailObject.can_buy_time = -1;
                        if(d.time.piece.length > 0){
                            $('.page_courseDetail.footer .coursebox .original_price').off('click').css('background','#ccc');
                            $('.page_courseDetail.footer .coursebox .groups_price').off('click').css('background','#bbb');
                        }else{
                            $('.page_courseDetail.footer .coursebox .original_price').off('click').css('background','#ccc');
                            $('.page_courseDetail.footer .coursebox .groups_price').off('click').css('background','#bbb');
                        }
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
                $('.page_courseDetail .commentList').html(code);
                //须知列表
                var code = '';
                for(var i in d.notice){
                    code +='<div>● '+ d.notice[i] +'</div>';
                }
                $('.page_courseDetail .tell_List').html(code);

                //尚未开始购买倒计时
                if(parseInt(d.time.start_buy_time) > (new Date()).getTime() / 1000){
                    $('.page_courseDetail .timebox').show();
                    if(d.time.piece.length > 0){
                        $('.page_courseDetail.footer .coursebox .original_price').off('click').css('background', '#ccc');
                        $('.page_courseDetail.footer .coursebox .groups_price').off('click').css('background', '#bbb');
                    }else{
                        $('.page_courseDetail.footer .coursebox .original_price').off('click').css('background', '#ccc');
                        $('.page_courseDetail.footer .coursebox .groups_price').off('click').css('background', '#bbb');
                    }
                    courseDetailObject.times = parseInt(d.time.start_buy_time) - Math.round((new Date()).getTime() / 1000);
                    courseDetailObject.DecTimes();
                }
            }else{
                $.alert('数据不完整，无法正常访问', function(){
                    page.back();
                }, 'error');
            }
            $('.page_courseDetail .imges').each(function(){
                $(this).bubble();
            });
        }, 2);

    },
    onshow:function(){
        console.log(courseDetailObject.selectarray);

    },
};



