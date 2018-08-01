var raiseDetailObject = {
    raise_id : null,
    times : 0,
    time : Math.round((new Date()).getTime() / 1000),
    DecTimes:function(){
        clearInterval(win.raiseDetailInterval);
        win.raiseDetailInterval = setInterval(function(){
            try {
                var days = Math.floor(raiseDetailObject.times / 24 / 3600);
                var hours = Math.floor(raiseDetailObject.times % (24 * 3600) / 3600);
                var mins = Math.floor((raiseDetailObject.times % (24 * 3600) % 3600) / 60);
                var secs = Math.floor((raiseDetailObject.times % (24 * 3600) % 3600) % 60);

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
                    $('.page_raiseDetail .timebox .downtime').html(str);
                    raiseDetailObject.times --;
                } else {
                    clearInterval(win.raiseDetailInterval);
                    page.reload();
                }
            }catch(e){
                clearInterval(win.raiseDetailInterval);
            }
        }, 1000);
    },
    shareSuccess: function (item_id) {

        return function(target) {
            ajax('Home/Index/shareSuccess', {type: 3, item_id: item_id, target: target, platform: 1}, function(d) {
                if (d.status == 1) {
                    console.log('分享成功');
                } else {
                    console.error(d.info);
                }
            });
        };

    },
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
            format = _f['d+'] + '天';
        } else if (_f['h+'] > 0) {
            format = _f['d+'] + '小时'
        } else if (_f['m+'] > 0) {
            format = _f['d+'] + '分钟'
        } else if (_f['s+'] > 0) {
            format = _f['d+'] + '秒'
        }

        return format;
    },
    onload : function(){
        var code ='';
        if(!win.get.raise_id){
            $.alert('众筹页面不存在','error');
            return;
        }
        raiseDetailObject.raise_id = win.get.raise_id;

        $('.page_raiseDetail .status_check a').click(function(){
            var str = $(this).attr('data');
            var div = $('.page_raiseDetail div[name="'+str+'"]');
            $('.page_raiseDetail.wrapper').animate({'scrollTop':div.position().top - 75}, 'fast', function(){
                $('.page_raiseDetail .status_check a').removeClass('add_hei');
                $('.page_raiseDetail .status_check a[data="'+ str +'"]').addClass('add_hei');
            });
        });

        //滚动条判断
        $('.page_raiseDetail.wrapper').scroll(function(){
            //判断并悬浮菜单按钮
            if($(this).scrollTop() > $('.page_raiseDetail .status_check').position().top - 45){
                $('.fixed_top.page_raiseDetail .statusbox').show();
                // raiseDetailObject.menuClone = $('.page_raiseDetail .status_check').clone(true, true).appendTo('.fixed_top');
                // raiseDetailObject.menuClone.addClass('menu_lay');
            }
            //判断并清除菜单按钮
            if($(this).scrollTop() < $('.page_raiseDetail .status_check').position().top - 45){
                $('.fixed_top.page_raiseDetail .statusbox').hide();
                // raiseDetailObject.menuClone.remove();
                // raiseDetailObject.menuClone = false;
            }
            //判断并转移菜单按钮
            if($('.page_raiseDetail div[name="title_item_1"]').position() && $(this).scrollTop() > $('.page_raiseDetail .status_check').position().top - 45 && $(this).scrollTop() < $('.page_raiseDetail div[name = "title_item_2"]').position().top - 100 && !$('.page_raiseDetail .status_check a[data="title_item_1"]').hasClass('add_hei')){
                $('.page_raiseDetail .status_check a.add_hei').removeClass('add_hei');
                $('.page_raiseDetail .status_check a[data="title_item_1"]').addClass('add_hei');
            }
            else if($('.page_raiseDetail div[name="title_item_2"]').position() && $(this).scrollTop() > $('.page_raiseDetail div[name="title_item_2"]').position().top - 100 && $(this).scrollTop() < $('.page_raiseDetail div[name="title_item_3"]').position().top - 100 && !$('.page_raiseDetail .status_check a[data="title_item_2"]').hasClass('add_hei')){
                $('.page_raiseDetail .status_check a.add_hei').removeClass('add_hei');
                $('.page_raiseDetail .status_check a[data="title_item_2"]').addClass('add_hei');
            }
            else if( $('.page_raiseDetail div[name="title_item_3"]').position() && $(this).scrollTop() > $('.page_raiseDetail div[name="title_item_3"]').position().top - 100 && $(this).scrollTop() < $('.page_raiseDetail div[name="title_item_4"]').position().top - 100 && !$('.page_raiseDetail .status_check a[data="title_item_3"]').hasClass('add_hei')){
                $('.page_raiseDetail .status_check a.add_hei').removeClass('add_hei');
                $('.page_raiseDetail .status_check a[data="title_item_3"]').addClass('add_hei');
            }
            else if($('.page_raiseDetail div[name="title_item_5"]').position() && ($(this).scrollTop() > $('.page_raiseDetail div[name="title_item_4"]').position().top - 100 && $(this).scrollTop() < $('.page_raiseDetail div[name="title_item_5"]').position().top - 100 && !$('.page_raiseDetail .status_check a[data="title_item_4"]').hasClass('add_hei'))){
                $('.page_raiseDetail .status_check a.add_hei').removeClass('add_hei');
                $('.page_raiseDetail .status_check a[data="title_item_4"]').addClass('add_hei');
            }
            else if($('.page_raiseDetail div[name="title_item_5"]').position() && $(this).scrollTop() > $('.page_raiseDetail div[name="title_item_5"]').position().top - 100 && !$('.page_raiseDetail .status_check a[data="title_item_5"]').hasClass('add_hei')){
                $('.page_raiseDetail .status_check a.add_hei').removeClass('add_hei');
                $('.page_raiseDetail .status_check a[data="title_item_5"]').addClass('add_hei');
            }
        });

        ajax('Goods/Raise/getDetail', {'raise_id':raiseDetailObject.raise_id}, function(d){
            if(d.info){
                $.alert(d.info, 'error');
                return;
            }
            var desc = d.introduction;
            var url = win.host + '?page=choice-raiseDetail&raise_id='+d.id;
            if(member && member.invitecode){
                url += '&invitecode=' + member.invitecode;
            }
            share(d.title, desc, url, d.path, raiseDetailObject.shareSuccess(raiseDetailObject.raise_id));
            //尚未开始倒计时
            if(parseInt(d.start_time) > raiseDetailObject.time){
                $('#message').css('bottom','9rem');
                $('.page_raiseDetail .subleft').hide();
                $('.page_raiseDetail .substop').hide();
                $('.page_raiseDetail .timebox').show();
                $('.page_raiseDetail .ready').show();
                raiseDetailObject.times = parseInt(d.start_time) - raiseDetailObject.time;
                raiseDetailObject.DecTimes();
            }else{
                $('#message').css('bottom','4.5rem');
                $('.page_raiseDetail .subleft').show();
                $('.page_raiseDetail .substop').hide();
                $('.page_raiseDetail .timebox').hide();
                $('.page_raiseDetail .ready').hide();
            }

            $('#message').hide();

            // var total = Math.round((d.totaled/d.total)*100);
            var total = ((d.totaled/d.total)*100).toFixed(1);
            if(d.totaled == 0 || (total.split('.')[0] <=0 && total.split('.')[1] == 0)){total = 0;}
            var days = '';
            if(parseInt(d.end_time) < raiseDetailObject.time){
                days = '已结束';
            }else if(parseInt(d.start_time) > raiseDetailObject.time){
                days = '未开始';
            }else{
                var t = parseInt(d.end_time) - raiseDetailObject.time;
                days = '剩余<font style="font-weight: 700;">' + raiseDetailObject.getLastTime(raiseDetailObject.time, d.end_time, d.start_time) + '</font>';
            }
            $('.page_raiseDetail [name="totaled"]').text(d.totaled);
            $('.page_raiseDetail [name="total"]').text(d.total);
            $('.page_raiseDetail [name="percent"]').text(total);
            $('.page_raiseDetail [name="sum"]').text(d.sum);
            $('.page_raiseDetail [name="days"]').html(days);
            $('.page_raiseDetail .subdec .nickname').text(d.nickname);
            $('.page_raiseDetail .subdec .c_time').text(d.start_time.timeFormat("Y-m-d") + ' 发布');
            if(d.headpath == ''){
                $('.page_raiseDetail .subdec .headimg').attr('src','http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg');
            }else{
                $('.page_raiseDetail .subdec .headimg').attr('src',d.headpath);
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
                }).addClass('video').appendTo('.page_raiseDetail .c_video');
            } else {
                $('<img>').attr({
                    'src': d.path
                }).appendTo('.page_raiseDetail .c_video');
            }

            $('.page_raiseDetail .subdec h1').text(d.title);
            $('.page_raiseDetail .dec').html(d.content);

            if(total >= 100){
                $('.page_raiseDetail .c_list .c_line span').css('width','100%');
                $('.page_raiseDetail .c_list .shell .stone').css('left','94%');
            }else{
                $('.page_raiseDetail .c_list .c_line span').css('width',total+'%');
                if(total > 4){
                    $('.page_raiseDetail .c_list .shell .stone').css('left',(total - 6) +'%');
                }
            }
            // if(d.isCollect !=0){
            //     code += '     			<button data="'+ d.id +'" class="Collected" onclick="setCollect(this,2)"></button>';
            // }else{
            //     code += '     			<button data="'+ d.id +'" onclick="setCollect(this,2)"></button>';
            // }
            // $('.page_raiseDetail.crowdMenu .info').before(code);
            //分享绑定
            $('.page_raiseDetail.crowdMenu .shares').click(function(){
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
            $('.page_raiseDetail .substop').click(function () {
                if(d.isPrivilege == 0){
                    $.alert('您暂无优先认筹权如需优先认筹请联系项目发起人', 'error');
                }else{
                    jump('myRaisePriority');
                }
            });
            $('.page_raiseDetail .ready').click(function () {
                $.alert('即将开始');
            });
            if(d.isReminder == 0){
                $('.page_raiseDetail .remider').on('click',function () {
                    ajax('Home/Index/OpenReminder',{type:2,type_id:raiseDetailObject.raise_id},function (d) {
                        if(d.status == 1){
                            $('.page_raiseDetail .remider').html('<i></i>已开启提醒');
                            $('.page_raiseDetail .remider').css('background','#ccc');
                            $('.page_raiseDetail .remider').off('click');
                            $.alert(d.info);
                        }else{
                            $.alert(d.info,'error');
                        }

                    })
                })
            }else{
                $('.page_raiseDetail .remider').html('<i></i>已开启提醒');
                $('.page_raiseDetail .remider').css('background','#ccc');
                $('.page_raiseDetail .remider').off('click');
            }

            //被邀请弹出
            if(d.inviter && location.href.indexOf('&alert=0') == -1){
                var code = '<div class="raiseDetail_invitebox">'+
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

