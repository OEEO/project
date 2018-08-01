var specialDetailObject = {
    tips_id : null,
    can_buy_time:1,
    times : 0,
    time_id : 0,
    //提交订单
    submitOrder : function(){
        if(specialDetailObject.can_buy_time == 0){$.alert('名额已满', 'error'); return false;}
        if(specialDetailObject.can_buy_time == -1){$.alert('已截止报名', 'error'); return false;};
        jump('confirmEnrolling', {tips_id : specialDetailObject.tips_id,time_id:specialDetailObject.time_id,catname:'私房菜'});
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
                var days = Math.floor(specialDetailObject.times / 24 / 3600);
                var hours = Math.floor(specialDetailObject.times % (24 * 3600) / 3600);
                var mins = Math.floor((specialDetailObject.times % (24 * 3600) % 3600) / 60);
                var secs = Math.floor((specialDetailObject.times % (24 * 3600) % 3600) % 60);

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
                    $('.page_specialDetail .timebox .downtime').html(str);
                    specialDetailObject.times --;
                } else {
                    clearInterval(win.tipsDetailInterval);
                    $('.page_specialDetail .timebox').hide();
                    page.reload();
                }
            }catch(e){
                clearInterval(win.tipsDetailInterval);
            }
        }, 1000);
    },
    onload : function(){
        specialDetailObject.tips_id = win.get.tips_id;
        $('.page_specialDetail .chat').click(function(){
            if(win.ws.power == 0){
                $.alert('在线客服系统已关闭。请添加吖咪客服微信:yami194','error');
                return;
            }else{
                jump('MsgIM');
            }
        });

        //滚动条判断
        $('.page_specialDetail.wrapper').scroll(function(){
            if($(this).scrollTop() > $('.page_specialDetail .bodyTop').height()){
                $('.page_specialDetail.header').removeClass('changeStyle');
                $('.page_specialDetail.header').addClass('line');
                $('.page_specialDetail.header a.collect.c2').show();
                $('.page_specialDetail.header a.collect.c1').hide();
                $('.page_specialDetail.header a.shared').css('background','url("../images/course-detail-page_share1@2x.png") no-repeat 0 0 / 1.728rem 1.728rem');
            }else{
                $('.page_specialDetail.header').addClass('changeStyle');
                $('.page_specialDetail.header').removeClass('line');
                $('.page_specialDetail.header a.collect.c2').hide();
                $('.page_specialDetail.header a.collect.c1').show();
                $('.page_specialDetail.header a.shared').css('background','url("../images/course-detail-page_share@2x.png") no-repeat 0 0 / 1.728rem 1.728rem');
            }

        });
        ajax('Goods/Tips/getDetail', {'tips_id':specialDetailObject.tips_id}, function(d){
            if((d.address && d.price!=null && d.time && d.times && d.title && d.min_num!=null && d.restrict_num!=null) || location.href.indexOf('tipsDetail') > 0){
                $('.page_specialDetail .timeicon').click(function(){
                    $.dialog("是否要拨打HOST电话？", function(){
                        window.location.href = 'tel:'+d.telephone+'#mp.weixin.qq.com';
                    });
                });
                specialDetailObject.time_id = d.time.id;
                var desc = '';
                for(var i in d.edge){
                    desc += d.edge[i] + ' ';
                }
                var url = win.host + '?page=choice-courseDetail&tips_id=' + specialDetailObject.tips_id;
                if(member && member.invitecode){
                    url += '&type=1&invitecode=' + member.invitecode;
                }
                share(d.title, desc, url, d.mainpic);

                specialDetailObject.defaultPics = d.defaultPics;
                //分享绑定
                $('.page_specialDetail .shared').click(function(){
                    showShareBox();
                });
                $('.header.page_specialDetail .title').text(d.catname);

                //活动标题
                $('.page_specialDetail .activityTitle').text(d.title);
                //会员昵称
                $('.page_specialDetail .userNm').text(d.nickname);

                script.load('plugins/scrollByJie', function(){
                    //主图
                    if(d.pics_group && d.pics_group.length > 0){
                        var sol = new myScroll();
                        sol.speed = 3;
                        sol.div = ".page_specialDetail .bodyTop";
                        for(var i in d.pics_group){
                            sol.src.push(d.pics_group[i]);
                        }
                        sol.start();
                    }else{
                        $('.page_specialDetail .bodyTop').html('<img src="images/actImg.jpg">');
                    }

                    //主图
                    if(d.menu_pics_group && d.menu_pics_group.length > 0){
                        var se = new myScroll();
                        se.speed = 3;
                        se.div = ".page_specialDetail .menu_b";
                        for(var i in d.menu_pics_group){
                            se.src.push(d.menu_pics_group[i]);
                        }
                        se.start();
                    }
                });

                //顶部标题
                //$('.header .title').text(d.catname);
                //主标题
                if (d.p_tags_id == 76) {
                    $('.page_specialDetail .them_title .title_t').html('<span class="red_tag">预约</span>' + d.title);
                } else {
                    $('.page_specialDetail .them_title .title_t').text(d.title);
                }
                
                //副标题
                //if(d.title_sub){
                //	$('.page_specialDetail .title_c').text(d.title_sub).hide();
                //}
                //亮点
                var edges='';
                // var i = $('.page_specialDetail .select').attr('i');
                for(var i in d.edge){
                    edges +='<p class="t_b"><font>◆</font>'+ d.edge[i] +'</p>';
                }
                $('.page_specialDetail .edges').html(edges);
                
                $('.page_specialDetail.footer .coursebox .original_price .price').html(d.price);
                if(d.time.lowest_num == 0){
                    $('.page_specialDetail.footer .coursebox .original_price').html('<font class="price">'+d.price+'</font>元/份');
                }else{
                    $('.page_specialDetail.footer .coursebox .original_price .num').html(d.time.lowest_num);
                }
                //地点
                $('.page_specialDetail .b_adress').text(d.address);

                $('.page_specialDetail .daren_headpic').click(function(){
                    jump('daRen', {member_id : d.daRen_id});
                });
                //达人头像
                if(d.headpic){
                    $('.page_specialDetail .daren_headpic img').attr('src', d.headpic);
                }else{
                    $('.page_specialDetail .daren_headpic img').attr('src', 'images/head.jpg');
                }
                //判断是否已关注达人
                if(d.isfollow)$('.page_specialDetail .followBtn button').addClass('valued');
                $('.page_specialDetail .followBtn button').attr('data', d.daRen_id).click(function(){
                    setFollow(this, function(d){
                        if(d){
                            $('.page_specialDetail .fans').text(parseInt($('.page_specialDetail .fans').text()) + 1);
                        }else{
                            $('.page_specialDetail .fans').text(parseInt($('.page_specialDetail .fans').text()) - 1);
                        }
                    });
                });
                //判断是否已经收藏
                if(d.isCollect)$('.page_specialDetail .collect').addClass('Collected');
                $('.page_specialDetail .collect').attr('data', specialDetailObject.tips_id).click(function(){
                    setCollect(this);
                });
                //达人昵称
                $('.page_specialDetail .daren_headpic span').text(d.nickname);

                var strs = '<span class="act_left">活动</span><span class="act_right">'+ d.tips +'</span><span class="dian"></span><span class="act_left">赏味</span><span class="act_right">'+ d.shangwei +'</span><span class="dian"></span><span class="act_left">粉丝</span><span class="act_right fans">'+ d.follow_num*3 +'</span>';
                $('.page_specialDetail .activity_list').html(strs);
                //简介
                var context = d.introduce;
                $('.page_specialDetail .intro_content').html(context);

                //菜单
                var menues = '';
                if(d.menu){
                    for(var i in d.menu){
                        if(d.menu[i].value == '' || (d.menu[i].value[0] && d.menu[i].value[0] == '')){
                            continue;
                        }
                        if(d.menu[i].name.toLowerCase() == 'tips'){
                            $('<p align="center" class="menu_btitle"><img src="images/tips_icon@2x.png"/><span>'+ d.menu[i].value +'</span></p>').appendTo('.page_specialDetail .daren_menu');
                        }else{
                            menues +='<p align="center" class="menu_yellow">- '+ d.menu[i].name +' -</p>';
                            if(d.menu[i].name == '活动流程'){
                                for(var j in d.menu[i].value){
                                    menues +='<p align="left" class="me_li">'+ specialDetailObject.menuStrToBase(d.menu[i].value[j]) +'</p>';
                                }
                            }else{
                                for(var j in d.menu[i].value){
                                    menues +='<p align="center" class="me_li">'+ specialDetailObject.menuStrToBase(d.menu[i].value[j]) +'</p>';
                                }
                            }
                        }
                    }
                    $('.page_specialDetail .menu_es').html(menues);
                }

                //查看地图
                $('.page_specialDetail a.showMap').click(function(){
                    jump('map', {latitude: d.latitude, longitude: d.longitude, name: d.simpleaddress});
                });

                if(d.time.stop_buy_time < (new Date()).getTime()/1000){
                    specialDetailObject.can_buy_time = -1;
                    $('.page_specialDetail.footer .coursebox .original_price').css('background','#ccc');
                    $('.page_specialDetail.footer .coursebox .groups_price').off('click').css('background','#bbb');
                }else{
                    $('.page_specialDetail.footer .coursebox .groups_price').click(function(){
                        specialDetailObject.submitOrder();
                    })
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
                $('.page_specialDetail .commentList').html(code);
                //须知列表
                var code = '';
                for(var i in d.notice){
                    code +='<div>● '+ d.notice[i] +'</div>';
                }
                $('.page_specialDetail .tell_List').html(code);

                //尚未开始购买倒计时
                if(parseInt(d.time.start_buy_time) > (new Date()).getTime() / 1000){
                    $('.page_specialDetail .timebox').show();
                    $('.page_specialDetail.footer .coursebox .original_price').css('background', '#ccc');
                    $('.page_specialDetail.footer .coursebox .groups_price').off('click').css('background', '#bbb');
                    specialDetailObject.times = parseInt(d.time.start_buy_time) - Math.round((new Date()).getTime() / 1000);
                    specialDetailObject.DecTimes();
                }
            }else{
                $.alert('数据不完整，无法正常访问', function(){
                    page.back();
                }, 'error');
            }
            $('.page_specialDetail .imges').each(function(){
                $(this).bubble();
            });
        }, 2);

    },
};



