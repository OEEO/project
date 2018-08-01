var raiseDetail2Object = {
    raise_id : null,
    times : 0,
    time : Math.round((new Date()).getTime() / 1000),
    timeoutId: null,
    data: {},
    DecTimes:function(){
        clearInterval(win.raiseDetail2Interval);
        win.raiseDetail2Interval = setInterval(function(){
            try {
                var days = Math.floor(raiseDetail2Object.times / 24 / 3600);
                var hours = Math.floor(raiseDetail2Object.times % (24 * 3600) / 3600);
                var mins = Math.floor((raiseDetail2Object.times % (24 * 3600) % 3600) / 60);
                var secs = Math.floor((raiseDetail2Object.times % (24 * 3600) % 3600) % 60);

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
                    $('.page_raiseDetail2 .timebox .downtime').html(str);
                    raiseDetail2Object.times --;
                } else {
                    clearInterval(win.raiseDetail2Interval);
                    page.reload();
                }
            }catch(e){
                clearInterval(win.raiseDetail2Interval);
            }
        }, 1000);
    },
    shareSuccess: function (item_id) {

        return function(target) {
            ajax('Home/Index/shareSuccess', {type: 3, item_id: item_id, target: target, platform: 0}, function(d) {
                if (d.status == 1) {
                    console.log('分享成功');
                } else {
                    console.log(d.info);
                }
            });
        };

    },
    getLastTime: function (current, end, start, format, successed) {
        // if (current < start) {
        //     return '即将上线';
        // }
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
            format = (_f['d+'] + 1) + '天';
        } else if (_f['h+'] > 0) {
            format = _f['h+'] + '小时'
        } else if (_f['m+'] > 0) {
            format = _f['m+'] + '分钟'
        } else if (_f['s+'] > 0) {
            format = _f['s+'] + '秒'
        }

        return format;
    },

    onload : function(){
        var code ='';
        if(!win.get.raise_id){
            $.alert('众筹页面不存在','error');
            return;
        }
        raiseDetail2Object.raise_id = win.get.raise_id;
		$('#fixed').show();

        $('.page_raiseDetail2 .status_check a').click(function(){
            // var str = $(this).attr('data');
            // var div = $('.page_raiseDetail2 div[name="'+str+'"]');
            // $('.page_raiseDetail2.wrapper').animate({'scrollTop':div.position().top - 75}, 'fast', function(){
            //     $('.page_raiseDetail2 .status_check a').removeClass('add_hei');
            //     $('.page_raiseDetail2 .status_check a[data="'+ str +'"]').addClass('add_hei');
            // });
//            $('.page_raiseDetail2.wrapper').animate({'scrollTop':div.position().top - 25}, 'fast', function(){
            var str = $(this).attr('data');
            $('.page_raiseDetail2 .status_check a').removeClass('add_hei');
            $('.page_raiseDetail2 .status_check a[data="'+ str +'"]').addClass('add_hei');
            $('.page_raiseDetail2 .dec>.item').hide();
            $('.page_raiseDetail2 .dec>.' + str).show();
        });

        $('#header .header.line.page_raiseDetail2 .turnBack .arrow').css('border-left', '0.15rem solid white')
                .css('border-top', '0.15rem solid white');


        //滚动条判断
        $('.page_raiseDetail2.wrapper').scroll(function(){
            if($('#fixed').css('display','none')) {
                $('#fixed').css('display','block');
            }
            //判断并悬浮菜单按钮
            if($(this).scrollTop() > $('.page_raiseDetail2 .status_check').position().top - 45){
                $('.fixed_top.page_raiseDetail2 .statusbox').show();
            }
            //判断并清除菜单按钮
            if($(this).scrollTop() < $('.page_raiseDetail2 .status_check').position().top - 45){
                $('.fixed_top.page_raiseDetail2 .statusbox').hide();
            }

            if($(this).scrollTop() > $('.page_raiseDetail2 .c_video').height()) {
                $('#header .header.line.page_raiseDetail2').css('background', 'white').css('border-bottom', '0.1rem solid #eee');
                $('.shares').css('background', "url('../images/raiseshare_black.png') no-repeat center center / 2.2rem 2.2rem");
                $('.clicklogo').css('background', "url('../images/yami_black.png') no-repeat center center / 7rem 3.4rem");
                $('#header .header.line.page_raiseDetail2 .turnBack .arrow').css('border-left', '0.15rem solid #000')
                .css('border-top', '0.15rem solid #000');
                $('.page_raiseDetail2 .userMsg').css('background', "url('../images/homepage_user_icon@3x.png') no-repeat center center / 2.2rem 2.2rem");
            }
            if($(this).scrollTop() < $('.page_raiseDetail2 .c_video').height()) {
                $('#header .header.line.page_raiseDetail2').css('background', 'none').css('border-bottom', '0rem');
                $('.shares').css('background', "url('../images/raiseshare_white.png') no-repeat center center / 2.2rem 2.2rem");
                $('.clicklogo').css('background', "url('../images/yami_white.png') no-repeat center center / 7rem 3.4rem");
                $('#header .header.line.page_raiseDetail2 .turnBack .arrow').css('border-left', '0.15rem solid white')
                .css('border-top', '0.15rem solid white');
                $('.page_raiseDetail2 .userMsg').css('background', "url('../images/homepage_user_icon_w@3x.png') no-repeat center center / 2.2rem 2.2rem");
            }
        });


//         //滚动条判断
//         $('.page_raiseDetail2.wrapper').scroll(function(){
//             /*
//             if($('#fixed').css('display','none')) {
//                 $('#fixed').css('display','block');
//             }
//             //判断并悬浮菜单按钮
//             if($(this).scrollTop() > ($('.detail').position().top - 80)){
//                 if($('.statusbox').css('display') == 'none') {
//                     $('.fixed_top.page_raiseDetail2 .statusbox').fadeIn();
//                     if ($('.statusbox').css('display') == 'block') {
//                         clearTimeout(raiseDetail2Object.timeoutId);
//                         raiseDetail2Object.timeoutId = setTimeout("$('.statusbox').fadeOut()",2000);
//                     }
//                     /*
//                     if($('.statusbox').css('display') == 'block') {
//                         $('.statusbox').delay(4000).fadeOut(0);
//                     }
//                     */
//              /*   }
//                 // raiseDetail2Object.menuClone = $('.page_raiseDetail2 .status_check').clone(true, true).appendTo('.fixed_top');
//                 // raiseDetail2Object.menuClone.addClass('menu_lay');
//             }
//             if($(this).scrollTop() < ($('.detail').position().top - 80)){
//                 $('.fixed_top.page_raiseDetail2 .statusbox').fadeOut();
//             }
//             */
//             /*
//             //判断并清除菜单按钮
//             if($(this).scrollTop() < $('.page_raiseDetail2 .status_check').position().top - 40){
//                 $('.fixed_top.page_raiseDetail2 .statusbox').hide();
//                 // raiseDetail2Object.menuClone.remove();
//                 // raiseDetail2Object.menuClone = false;
//             }
// */
//             //判断并悬浮菜单按钮
//             if($(this).scrollTop() > $('.page_raiseDetail2 .status_check').position().top - 45){
//                 $('.fixed_top.page_raiseDetail2 .statusbox').show();
//                 // raiseDetail2Object.menuClone = $('.page_raiseDetail2 .status_check').clone(true, true).appendTo('.fixed_top');
//                 // raiseDetail2Object.menuClone.addClass('menu_lay');
//             }
//             //判断并清除菜单按钮
//             if($(this).scrollTop() < $('.page_raiseDetail2 .status_check').position().top - 45){
//                 $('.fixed_top.page_raiseDetail2 .statusbox').hide();
//                 // raiseDetail2Object.menuClone.remove();
//                 // raiseDetail2Object.menuClone = false;
//             }

//             if($(this).scrollTop() > $('.page_raiseDetail2 .c_video').height()) {
//                 $('#header .header.line.page_raiseDetail2').css('background', 'white').css('border-bottom', '0.1rem solid #eee');
//                 $('.shares').css('background', "url('../images/raiseshare_black.png') no-repeat center center / 2.2rem 2.2rem");
//                 $('.clicklogo').css('background', "url('../images/yami_black.png') no-repeat center center / 7rem 3.4rem");
//                 $('#header .header.line.page_raiseDetail2 .turnBack .arrow').css('border-left', '0.15rem solid #000')
//                 .css('border-top', '0.15rem solid #000');
//                 $('.page_raiseDetail2 .userMsg').css('background', "url('../images/homepage_user_icon@3x.png') no-repeat center center / 2.2rem 2.2rem");
//             }
//             if($(this).scrollTop() < $('.page_raiseDetail2 .c_video').height()) {
//                 $('#header .header.line.page_raiseDetail2').css('background', 'none').css('border-bottom', '0rem');
//                 $('.shares').css('background', "url('../images/raiseshare_white.png') no-repeat center center / 2.2rem 2.2rem");
//                 $('.clicklogo').css('background', "url('../images/yami_white.png') no-repeat center center / 7rem 3.4rem");
//                 $('#header .header.line.page_raiseDetail2 .turnBack .arrow').css('border-left', '0.15rem solid white')
//                 .css('border-top', '0.15rem solid white');
//                 $('.page_raiseDetail2 .userMsg').css('background', "url('../images/homepage_user_icon_w@3x.png') no-repeat center center / 2.2rem 2.2rem");
//             }


//             //判断并转移菜单按钮
//             // if($('.page_raiseDetail2 div[name="title_item_1"]').position() && $(this).scrollTop() > $('.page_raiseDetail2 .status_check').position().top - 45 && $(this).scrollTop() < $('.page_raiseDetail2 div[name = "title_item_2"]').position().top - 100 && !$('.page_raiseDetail2 .status_check a[data="title_item_1"]').hasClass('add_hei')){
//             //     $('.page_raiseDetail2 .status_check a.add_hei').removeClass('add_hei');
//             //     $('.page_raiseDetail2 .status_check a[data="title_item_1"]').addClass('add_hei');
//             // }
//             // else if($('.page_raiseDetail2 div[name="title_item_2"]').position() && $(this).scrollTop() > $('.page_raiseDetail2 div[name="title_item_2"]').position().top - 100 && $(this).scrollTop() < $('.page_raiseDetail2 div[name="title_item_3"]').position().top - 100 && !$('.page_raiseDetail2 .status_check a[data="title_item_2"]').hasClass('add_hei')){
//             //     $('.page_raiseDetail2 .status_check a.add_hei').removeClass('add_hei');
//             //     $('.page_raiseDetail2 .status_check a[data="title_item_2"]').addClass('add_hei');
//             // }
//             // else if( $('.page_raiseDetail2 div[name="title_item_3"]').position() && $(this).scrollTop() > $('.page_raiseDetail2 div[name="title_item_3"]').position().top - 100 && $(this).scrollTop() < $('.page_raiseDetail2 div[name="title_item_4"]').position().top - 100 && !$('.page_raiseDetail2 .status_check a[data="title_item_3"]').hasClass('add_hei')){
//             //     $('.page_raiseDetail2 .status_check a.add_hei').removeClass('add_hei');
//             //     $('.page_raiseDetail2 .status_check a[data="title_item_3"]').addClass('add_hei');
//             // }
//             // else if($('.page_raiseDetail2 div[name="title_item_5"]').position() && ($(this).scrollTop() > $('.page_raiseDetail2 div[name="title_item_4"]').position().top - 100 && $(this).scrollTop() < $('.page_raiseDetail2 div[name="title_item_5"]').position().top - 100 && !$('.page_raiseDetail2 .status_check a[data="title_item_4"]').hasClass('add_hei'))){
//             //     $('.page_raiseDetail2 .status_check a.add_hei').removeClass('add_hei');
//             //     $('.page_raiseDetail2 .status_check a[data="title_item_4"]').addClass('add_hei');
//             // }
//             // else if($('.page_raiseDetail2 div[name="title_item_5"]').position() && $(this).scrollTop() > $('.page_raiseDetail2 div[name="title_item_5"]').position().top - 100 && !$('.page_raiseDetail2 .status_check a[data="title_item_5"]').hasClass('add_hei')){
//             //     $('.page_raiseDetail2 .status_check a.add_hei').removeClass('add_hei');
//             //     $('.page_raiseDetail2 .status_check a[data="title_item_5"]').addClass('add_hei');
//             // }
//         });

        //复制微信号

        $('.contact .wx-copy-btn').click(function(){
            var inputText = $('.contact .wx-account');
            inputText.select();
            document.execCommand('copy', false);
            $.alert('复制成功', 'success');
        });


        ajax('Goods/Raise/getDetail', {'raise_id':raiseDetail2Object.raise_id}, function(d){
            console.log(d)
            if(d.info){
                $.alert(d.info, 'error');
                return;
            }
            var desc = d.introduction;
            var url = win.host + '?page=choice-raiseDetail2&raise_id='+d.id;

            // 设置 times_id 和 id 参数
            // raiseDetail2Object.data.raise_id = raiseDetail2Object.raise_id
            // d.times.some(function (val) {
            //     if (Math.floor(val.price) === 499) {
            //         raiseDetail2Object.data.times_id = val.times_id
            //         return true
            //     }
            // })
            //
            // raiseDetail2Object.member_privilege_map = d.tips_privilege
            // if (raiseDetail2Object.member_privilege_map) {
            //     if (raiseDetail2Object.member_privilege_map) {
            //         raiseDetail2Object.data.id = raiseDetail2Object.member_privilege_map['' + raiseDetail2Object.data.times_id];
            //     }
            // }

            var isEnd = parseInt(d.end_time) < raiseDetail2Object.time ? true : false;
            var isPreview = d.is_preview === '1' ? true : false;
            var isCollect = d.isCollect == 1 ? true : false;
            var isReminder = d.isReminder == '1';

            if(member && member.invitecode){
                url += '&invitecode=' + member.invitecode;
            }
            share(d.title, desc, url, d.path, raiseDetail2Object.shareSuccess(raiseDetail2Object.raise_id));
            //尚未开始倒计时
//            if(d.isPrivilege=="0"&&parseInt(d.start_time) > raiseDetail2Object.time){
            if(parseInt(d.start_time) > raiseDetail2Object.time && !isPreview){
                //未开始，且非预告，显示倒计时
                $('#message').css('bottom','9rem');
                $('.page_raiseDetail2 .subleft').hide();
                $('.page_raiseDetail2 .substop').hide();
                if (page.names[page.names.length - 2] != 'receiveVIP') {
                    $('.page_raiseDetail2 .timebox').show();
                }
                $('.page_raiseDetail2 .ready').show();
                raiseDetail2Object.times = parseInt(d.start_time) - raiseDetail2Object.time;
                raiseDetail2Object.DecTimes();
            }else{
                $('#message').css('bottom','4.5rem');
                $('.page_raiseDetail2 .subleft').show();
                $('.page_raiseDetail2 .substop').hide();
                $('.page_raiseDetail2 .timebox').hide();
                $('.page_raiseDetail2 .ready').hide();
            }

            $('#message').hide();

            // var total = Math.round((d.totaled/d.total)*100);
            var total = ((d.totaled/d.total)*100).toFixed(0);
            if(d.totaled == 0 || (total.split('.')[0] <=0 && total.split('.')[1] == 0)){total = 0;}
            var days = '';
            /*
            if(parseInt(d.end_time) < raiseDetail2Object.time){
                days = '已结束';
            }else if(d.isPrivilege==0&&parseInt(d.start_time) > raiseDetail2Object.time){
                console.log(d.isPrivilege);
//              days = '未开始';
                days = '优先认筹中';
            }else{
                var t = parseInt(d.end_time) - raiseDetail2Object.time;
                days = '<font>剩余' + raiseDetail2Object.getLastTime(raiseDetail2Object.time, d.end_time, d.start_time) + '</font>';
            }*/
            console.log('isPreview = ', isPreview);
            console.log('isCollect = ', isCollect);
            if(parseInt(d.end_time) < raiseDetail2Object.time){
                days = '已结束';
                $('.page_raiseDetail2 .suport-btn').attr({'disabled':true});
            }else if(parseInt(d.start_time) > raiseDetail2Object.time){
                $('.page_raiseDetail2 .collected-count').show().text('关注人数：' + d.collected_count);
                if (isPreview) {
                    $('.page_raiseDetail2 .suport-btn').remove();
                    $('.page_raiseDetail2 .follow-btn').show();
                    days = '上线时间待公布';
                }

                if (isReminder) {
                    $('.page_raiseDetail2 .follow-btn').addClass('is-collect').text('已关注');
                }
                console.log(d.isPrivilege);
//              days = '未开始';
                days = '预约中';
            }else{
                var t = parseInt(d.end_time) - raiseDetail2Object.time;
                days = '<font>' + raiseDetail2Object.getLastTime(raiseDetail2Object.time, d.end_time, d.start_time) + '</font>';
            }
            $('.page_raiseDetail2 [name="totaled"]').text(d.totaled);
            $('.page_raiseDetail2 [name="total"]').text(d.total);
            $('.page_raiseDetail2 [name="percent"]').text(total);
            $('.page_raiseDetail2 [name="sum"]').text(d.sum);
            $('.page_raiseDetail2 [name="days"]').html(days);
            $('.page_raiseDetail2 .nickname').text(d.nickname);
            $('.page_raiseDetail2 .city_name').text(d.city_name);
            $('.page_raiseDetail2 .c_time').text(d.start_time.timeFormat("Y年m月d日"));
            $('.page_raiseDetail2 .raise_end_time').text(d.end_time.timeFormat("Y年m月d日"));
            if(d.headpath == ''){
                $('.page_raiseDetail2 .headimg').attr('src','http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg');
            }else{
                $('.page_raiseDetail2 .headimg').attr('src',d.headpath);
            }

            if (d.video_url) {
                $('<video>').attr({
                    'controls':'controls',
                    'src':d.video_url,
                    'poster':d.path,
                    'webkit-playsinline':true,
                    'playsinline':true,
                    'x5-video-player-type':'h5',
                    'x5-video-player-fullscreen':true,
                }).addClass('video').appendTo('.page_raiseDetail2 .c_video');
            } else {
                $('<img>').attr({
                    'src': d.path
                }).appendTo('.page_raiseDetail2 .c_video');
            }


            var myRaise = '';
            var raiseReturn = '';
            var contentAppend = '';
            myRaise += '<div class="raise-tag-wrap"><div class="raise-tag">我的自述</div></div>';
            if(d.title1){
              myRaise += '<p class="raise-content-title">' + d.title1 + '</p>';
            }
            myRaise += '<div class="raise-content">' + d.content1 + '</div>';

            myRaise += '<div class="raise-tag-wrap"><div class="raise-tag">我的项目</div></div>';
            if (d.title2) {
              myRaise += '<p class="raise-content-title">' + d.title2 + '</p>';
            }
            myRaise += '<div class="raise-content">' + d.content2 + '</div>';

            myRaise += '<div class="raise-tag-wrap"><div class="raise-tag">为何众筹</div></div>';
            if (d.title3) {
              myRaise += '<p class="raise-content-title">' + d.title3 + '</p>';
            }
            myRaise += '<div class="raise-content">' + d.content3 + '</div>';

            myRaise += '<div class="raise-tag-wrap"><div class="raise-tag">项目进度</div></div>';
            if (d.title5) {
              myRaise += '<p class="raise-content-title">' + d.title5 + '</p>';
            }
            myRaise += '<div class="raise-content">' + d.content5 + '</div>';

            myRaise = '<div class="item myRaise">' + myRaise + '</div>';

            raiseReturn = '<div class="item raiseReturn" style="display: none;">' + d.content4 + '</div>';

            contentAppend = myRaise + raiseReturn;

            $('.page_raiseDetail2 h1').text(d.title);
            $('.page_raiseDetail2 h2').text(d.introduction);
            $('.page_raiseDetail2 .dec').append(contentAppend);


            if(total >= 100){
                $('.page_raiseDetail2 .c_list .c_line span').css('width','100%');
            }else{
                $('.page_raiseDetail2 .c_list .c_line span').css('width',total+'%');
            }
            //如果是预告或已过期的项目，则导航菜单删除第2、3项
            if (isEnd || isPreview) {
                $('.page_raiseDetail2 .status_check a.contact, .page_raiseDetail2 .status_check a.raiseReturn').remove();
            }

            //预告
            if (isPreview) {
                $('.page_raiseDetail2 .c_list').remove();
            }

            // if(d.isCollect !=0){
            //     code += '     			<button data="'+ d.id +'" class="Collected" onclick="setCollect(this,2)"></button>';
            // }else{
            //     code += '     			<button data="'+ d.id +'" onclick="setCollect(this,2)"></button>';
            // }
            // $('.page_raiseDetail2.crowdMenu .info').before(code);
            //分享绑定
            $('.page_raiseDetail2 .shares').click(function(){
                if (Yami.platform() === 'android') {
                    Yami.share({
                        title: d.title,
                        desc: desc,
                        link: url,
                        imgUrl: d.path
                    });
                } else {
                    showShareBox();
                }
            });
            $('.page_raiseDetail2 .substop').click(function () {
                if(d.isPrivilege == 0){
                    $.alert('您暂无优先认筹权如需优先认筹请联系项目发起人', 'error');
                }
                // }else{
                //     jump('myRaisePriority');
                // }
            });
            $('.page_raiseDetail2 .ready').click(function () {
 //               $.alert('即将开始');
            });

            function reminder(type, id, d, callback) {
                type = type || 2;
                d = d || 0;
                ajax('Home/Index/OpenReminder',{type: type, type_id: id, d: d}, callback);
            }
            //提醒
            if(d.isReminder == 0 || !isCollect){
                $('.page_raiseDetail2 .remider').on('click',function () {
                    reminder(2, raiseDetail2Object.raise_id, 0, function (d) {
                        if(d.status == 1){
                            $('.page_raiseDetail2 .remider').html('<i></i>已开启提醒');
                            $('.page_raiseDetail2 .remider').css('background','#ccc');
                            $('.page_raiseDetail2 .remider').off('click');
                            $.alert(d.info);
                        } else {
                            $.alert(d.info,'error');
                        }
                    });
                })
            }else{
                $('.page_raiseDetail2 .remider').html('<i></i>已开启提醒');
                $('.page_raiseDetail2 .remider').css('background','#ccc');
                // $('.page_raiseDetail2 .remider').off('click');
                $('.page_raiseDetail2 .remider').on('click',function () {
                    ajax('Home/Index/OpenReminder',{type:2,type_id:raiseDetail2Object.raise_id, d: 1},function (d) {
                        if(d.status == 1){
                            $('.page_raiseDetail2 .remider').html('<i></i>已开启提醒');
                            $('.page_raiseDetail2 .remider').css('background','#ccc');
                            $('.page_raiseDetail2 .remider').off('click');
                            $.alert(d.info);
                        } else {
                            $.alert(d.info,'error');
                        }
                    });
                });
            }

            var collectedCount = +d.collected_count;
            //关注按钮
            $('.page_raiseDetail2 .follow-btn').on('click',function () {
                var $followBtn = $('.page_raiseDetail2 .follow-btn');
                function setcollect(id, iscollect, fn, type){
                    type = type || '0';
                    //判断是否登录
                    if(!member){
                        win.login();
                        return;
                    }else{
                        if(iscollect == 1){
                            $.dialog('您确定要取消关注吗?', function () {
                                ajax('Member/Follow/ChangeCollect', {type: type ,type_id:id,operate:0}, function(d){
                                if(d.status == 1){
                                    if(typeof(fn) == 'function')fn(0);
                                    $.alert('取消成功');
                                }else{
                                    $.alert('操作失败', 'error');
                                }
                            });
                            },true,'',true)
                        }else{
                            ajax('Member/Follow/ChangeCollect', {type: type,type_id:id,operate:1}, function(d){
                                if(d.status == 1){
                                    if(typeof(fn) == 'function')fn(1);
                                }else{
                                    $.alert('操作失败', 'error');
                                }
                            });
                        }
                    }
                }
                // setcollect(raiseDetail2Object.raise_id, isCollect, function (isCollectChange) {
                //     isCollect = isCollectChange;
                //     if (isCollectChange === 1) {
                //         $followBtn.text('取消关注');
                //         $followBtn.addClass('is-collect');
                //         collectedCount += 1;
                //         $('.page_raiseDetail2 .collected-count').text('关注人数：' + collectedCount);
                //     } else {
                //         $followBtn.text('关注');
                //         $followBtn.removeClass('is-collect');
                //         collectedCount -= 1;
                //         collectedCount < 0
                //             ? 0
                //             : collectedCount;
                //         $('.page_raiseDetail2 .collected-count').text('关注人数：' + collectedCount);
                //     }
                // }, '2');

                var remNum = isReminder
                    ? 1
                    : 0;
                reminder(2, raiseDetail2Object.raise_id, remNum, function (data) {
                    if(!member) {
                        // login 跳转回来，自动关注
                        var clickCollectBtn = function () {
                            ajax('Goods/Raise/getDetail', {'raise_id':raiseDetail2Object.raise_id}, function(d) {
                                // 本来就已经关注了
                                if (d.isReminder == '1') {
                                    $.alert('您已关注成功，项目正式上线前您将收到短信提醒', 'success', 'success', 9);
                                    $('.page_raiseDetail2 .follow-btn').text('已关注');
                                    $('.page_raiseDetail2 .follow-btn').addClass('is-collect');
                                    return;
                                }
                                // 本来没关注
                                $('.page_raiseDetail2 .follow-btn').eq(0).click();
                            })
                        }

                        win.login(clickCollectBtn);
                        return;
                    }
                    if (data.status == 1) {
                        isReminder = !isReminder;
                        if (remNum == 1) {
                            // 取消关注
                            $followBtn.text('关注');
                            $followBtn.removeClass('is-collect');
                            collectedCount -= 1;
                            collectedCount < 0
                                ? 0
                                : collectedCount;
                            $('.page_raiseDetail2 .collected-count').text('关注人数：' + collectedCount);
                        } else {
                            $followBtn.text('取消关注');
                            $followBtn.addClass('is-collect');
                            collectedCount += 1;
                            $('.page_raiseDetail2 .collected-count').text('关注人数：' + collectedCount);
                            $.alert('您已关注成功，项目正式上线前您将收到短信提醒', 'success', 'success', 9)
                            console.log('成功关注', isReminder)
                        }
                    } else {
                        $.alert(data.info, 'error');
                    }
                });
            });


            // $("div[name='title_item_1'] :first-child").css({"line-height" : "3rem","height" : "3rem", "font-size" : "12px",
            //                                                 "border-width" : "0.1rem 0.1rem 0.1rem 1.2rem"});
            // $("div[name='title_item_2'] :first-child").css({"line-height" : "3rem","height" : "3rem", "font-size" : "12px",
            //                                                 "border-width" : "0.1rem 0.1rem 0.1rem 1.2rem"});
            // $("div[name='title_item_3'] :first-child").css({"line-height" : "3rem","height" : "3rem", "font-size" : "12px",
            //                                                 "border-width" : "0.1rem 0.1rem 0.1rem 1.2rem"});
            // $("div[name='title_item_4'] :first-child").css({"line-height" : "3rem","height" : "3rem", "font-size" : "12px",
            //                                                 "border-width" : "0.1rem 0.1rem 0.1rem 1.2rem"});
            // $("div[name='title_item_5'] :first-child").css({"line-height" : "3rem","height" : "3rem", "font-size" : "12px",
            //                                                 "border-width" : "0.1rem 0.1rem 0.1rem 1.2rem"});

            //滚动条判断，当isEnd 或 isPreview 时删除其中两个按钮
            $('.page_raiseDetail2.wrapper').scroll(function(){
                if($('#fixed').css('display','none')) {
                    $('#fixed').css('display','block');
                }
                //判断并悬浮菜单按钮
                if($(this).scrollTop() > $('.page_raiseDetail2 .status_check').position().top - 45){
                    //如果是预告或已过期的项目，则导航菜单删除第2、3项
                    if (isEnd || isPreview) {
                        $('.page_raiseDetail2 .status_check a.contact, .page_raiseDetail2 .status_check a.raiseReturn').remove();
                    }
                    $('.fixed_top.page_raiseDetail2 .statusbox').show();
                }
                //判断并清除菜单按钮
                if($(this).scrollTop() < $('.page_raiseDetail2 .status_check').position().top - 45){
                    $('.fixed_top.page_raiseDetail2 .statusbox').hide();
                }

                if($(this).scrollTop() > $('.page_raiseDetail2 .c_video').height()) {
                    $('#header .header.line.page_raiseDetail2').css('background', 'white').css('border-bottom', '0.1rem solid #eee');
                    $('.shares').css('background', "url('../images/raiseshare_black.png') no-repeat center center / 2.2rem 2.2rem");
                    $('.clicklogo').css('background', "url('../images/yami_black.png') no-repeat center center / 7rem 3.4rem");
                    $('#header .header.line.page_raiseDetail2 .turnBack .arrow').css('border-left', '0.15rem solid #000')
                    .css('border-top', '0.15rem solid #000');
                    $('.page_raiseDetail2 .userMsg').css('background', "url('../images/homepage_user_icon@3x.png') no-repeat center center / 2.2rem 2.2rem");
                }
                if($(this).scrollTop() < $('.page_raiseDetail2 .c_video').height()) {
                    $('#header .header.line.page_raiseDetail2').css('background', 'none').css('border-bottom', '0rem');
                    $('.shares').css('background', "url('../images/raiseshare_white.png') no-repeat center center / 2.2rem 2.2rem");
                    $('.clicklogo').css('background', "url('../images/yami_white.png') no-repeat center center / 7rem 3.4rem");
                    $('#header .header.line.page_raiseDetail2 .turnBack .arrow').css('border-left', '0.15rem solid white')
                    .css('border-top', '0.15rem solid white');
                    $('.page_raiseDetail2 .userMsg').css('background', "url('../images/homepage_user_icon_w@3x.png') no-repeat center center / 2.2rem 2.2rem");
                }
            });

            $('.clicklogo').click(function() {
                if ($('.page_raiseDetail2.wrapper').scrollTop() < $('.page_raiseDetail2 .c_video').height()) {
                    if ($('.userCenter').css('display') == 'block') {
                        $('.userCenter').slideUp();
                        $('.clicklogo').css('background', "url('../images/yami_white.png') no-repeat center center / 7rem 3.4rem");
                        $('#header .header.line.page_raiseDetail2').css('background', 'none').css('border-bottom', '0rem');
                        $('.shares').css('display','block');
                    } else {
                        $('.userCenter').slideDown();
                        $('.clicklogo').css('background', "url('../images/yami_black.png') no-repeat center center / 7rem 3.4rem");
                        $('#header .header.line.page_raiseDetail2').css('background', 'white').css('border-bottom', '0.1rem solid #eee');
                        $('.shares').css('display','none');
                    }
                } else {
                    if ($('.userCenter').css('display') == 'block') {
                        $('.userCenter').slideUp();
                        $('.shares').css('display','block');
                    } else {
                        $('.userCenter').slideDown();
                        $('.shares').css('display','none');
                    }
                }
            });

            //被邀请弹出
            if(d.inviter && location.href.indexOf('&alert=0') == -1){
                var code = '<div class="raiseDetail2_invitebox">'+
                    '<div class="vipbeform">'+
                    '<img class="headpic" src="'+ d.inviter.path +'">'+
                    '<p class="nickname">'+ d.inviter.nickname +'</p>'+
                    '<p class="intro">邀请您参与众筹</p>'+
                    '<p class="title">立即参与</p>'+
                    '</div>'+
                    '</div>';
                $(code).appendTo(document.body).fadeIn('fast').mousedown(function(){
                    $(this).fadeOut('fast', function(){
                        $(this).remove();
                    });
                });
            }
        }, 2);
    }
};

