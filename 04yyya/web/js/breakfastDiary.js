var breakfastDiaryObject={
    title : null,
    pic_id : null,
    is_sign : 0,
    serial_count : 1,
    delid : null,
    winScrollSock:false,
    rotatenum:0,
    rotatepath:null,
    //加载早餐列表
    loadlist:function(page){
        var page = page||1;
        //加载中
        $('.page_breakfastDiary center').show();
        ajax('Home/Sign/GetSignList', {get:{page:page}}, function(d){
            //加载完成
            $('.page_breakfastDiary center').hide();
            if(!d.info) {
                var codes = '';
                if (d.list.length > 0){
                    var stitle = win.wxMember.nickname+'的早餐日志';
                    var desc = '这是我坚持好好吃早餐的第'+d.list[0].serial_sign+'天！要一起加入吗？';
                    var url = win.host + '?page=shareDunch&sign_id=' + d.list[0].sign_id;
                    var spath = d.list[0].sign_path;
                    for (var i in d.list) {
                        codes += '<div class="punch_item" data-id="'+ d.list[i].sign_id +'">';
                        codes += '  <span class="g1"></span>';
                        codes += '  <span class="g2"></span>';
                        codes += '  <div class="item_head">';
                        codes += '      <div class="iteml"><i></i>' + d.list[i].datetime.timeFormat('Y-m-d W H:i') + '</div>';
                        codes += '      <img src="images/punch_more.png" class="itemr" onclick="breakfastDiaryObject.boxshow(' + d.list[i].sign_id + ',' + i + ')" />';
                        codes += '  </div>';
                        codes += '  <div class="item_context">';
                        codes += '      <div class="itemtitle">' + d.list[i].title + '</div>';
                        codes += '      <img src="' + d.list[i].sign_path + '" data-id="' + d.list[i].pic_id + '" class="punch_img" onclick="breakfastDiaryObject.enlarge(this)"/>';
                        codes += '  </div>';
                        codes += '</div>';
                    }
                    if(page == 1)
                        $('.page_breakfastDiary .punch_content').html(codes);
                    else
                        $(codes).appendTo('.page_breakfastDiary .punch_content');
                }else{
                    if(page == 1){
                        var stitle = '我的早餐日志';
                        var desc = '你有多久没有好好吃早餐了？一起加入早餐打卡吧';
                        var spath = 'http://yummy194.cn/images/pic1.png';
                        var url = win.host + '?page=shareDunch&sign_id=-1';
                        $('.page_breakfastDiary .punch_content').html('<div class="no_msgs"><img src="images/punch_none.png" /><p>目前还没有打卡记录哦</p><p style="padding-top: 0.5rem;">点击上面的 <img src="images/punch_btn2.png"/> 记录下今天的早餐吧</p></div>');
                    }else{
                        $('.page_breakfastDiary .punch_content').append('<div class="no_more"></div>');
                    }
                }
                share(stitle, desc, url, spath);
            }else{
                $.alert(d.info, 'error');
            }
            breakfastDiaryObject.winScrollSock = false;
        });
    },
    //打卡以及打卡成功提示
    punch:function () {
        if(breakfastDiaryObject.is_sign == 0) {
            var code = '';
            code += '<i class="clearpsd"></i>';
            code += '<div class="punch_text">';
            code += '<textarea class="punchtitle" placeholder="分享今日早餐..."></textarea>';
            code += '<div class="uppic">'
            code += '<img class="punchpic" src="images/punch_add picture.png"/>';
            code += '<input id="upload-file" type="file" onchange="breakfastDiaryObject.upload(this.files[0])"/>';
            code += '</div>';
            code += '<div class="protate" onclick="breakfastDiaryObject.picrotate()"><img class="pr" src="images/punch_rotate.png"/></div>'
            code += '</div>';
            $.dialog(code, function () {
                breakfastDiaryObject.title = $('#dialogBox.page_breakfastDiary .punchtitle').val();
                if(breakfastDiaryObject.title == '' || breakfastDiaryObject.pic_id == null){
                    $.alert('标题或早餐图不能为空!');
                    return false;
                }
                ajax('Home/Sign/PostSign', {
                    title : breakfastDiaryObject.title,
                    pic_id : breakfastDiaryObject.pic_id,
                    rotate : breakfastDiaryObject.rotatenum,
                }, function (d) {
                    if (d.status == 1) {
                        breakfastDiaryObject.is_sign = 1;
                        code = '';
                        code += '<i class="clearpsd"></i>';
                        code += '<div class="punch_success">';
                        code += '<img src="images/punch_finish.png"/ class="punch_finish">';
                        code += '<p class="finish_text">打卡成功</p>';
                        code += '</div>';
                        code += '<button class="createBtn" type="button">生成早餐日志</button>';
                        $.dialog(code, function () {
                            punchShareBox();
                        }, true, 'punchshareBox');
                        $('#dialogBox.page_breakfastDiary .createBtn').click(function(){
                            breakfastDiaryObject.create(breakfastDiaryObject.pic_id);
                            $('.dialogBoxLay').click();
                        });
                        $('#dialogBox.page_breakfastDiary .btns .closeBtn').remove();
                        $('#dialogBox.page_breakfastDiary .btns .agree').text('分享');

                        breakfastDiaryObject.is_sign = 1;
                        breakfastDiaryObject.serial_count ++;
                        breakfastDiaryObject.total_count ++;
                        breakfastDiaryObject.sign_datetime = (parseInt((new Date()).getTime()/1000)).toString();
                        breakfastDiaryObject.change();
                        breakfastDiaryObject.loadlist();
                    } else {
                        $.alert(d.info, 'error');
                    }
                });
            }, true, 'punchBox');
            $('#dialogBox.page_breakfastDiary .btns .closeBtn').remove();
            $('#dialogBox.page_breakfastDiary .btns .agree').text('晒早餐完成打卡');
            setTimeout(function(){
                $('#dialogBox.page_breakfastDiary .punchtitle').focus();
            }, 1000);
        }else{
            $.alert('你今日已打卡','puncherror');
        }
    },
    //上传图片
    upload:function (file) {
        page.uploadimg(file,function(path,pic_id){
            breakfastDiaryObject.rotatepath = path;
            breakfastDiaryObject.pic_id = pic_id;
            $('#dialogBox.page_breakfastDiary .punchpic').attr('src',path);
            $('#dialogBox.page_breakfastDiary .protate').show();
        })
    },
    boxshow:function(id,num){
        breakfastDiaryObject.delid = id;
        if(num == 0){
            $('.page_breakfastDiary .fadebox .sharet').show();
        }else{
            $('.page_breakfastDiary .fadebox .sharet').hide();
        }
        $('.page_breakfastDiary .fadebox').show();
    },
    boxhide:function(){
        $('.page_breakfastDiary .fadebox').hide();
    },
    del:function(){
        ajax('Home/Sign/DelSign',{sign_id:breakfastDiaryObject.delid},function (d) {
            if (d.status == 1) {
                $.alert(d.info,function(){
                    $('.page_breakfastDiary .fadebox').hide();
                    page.reload();
                })
            }else{
                $.alert(d.info,'error');
            }
        });
    },
    punchshare:function(){
        $('.page_breakfastDiary .fadebox').hide();
        punchShareBox();
    },
    //图片旋转
    picrotate:function(){
        breakfastDiaryObject.rotatenum = parseInt(breakfastDiaryObject.rotatenum) + 1;
        if(breakfastDiaryObject.rotatenum > 3){
            breakfastDiaryObject.rotatenum = 0;
        }
        $('#dialogBox.page_breakfastDiary .punchpic').attr('src',breakfastDiaryObject.rotatepath+'?x-oss-process=image/rotate,'+parseInt(breakfastDiaryObject.rotatenum)*90);
    },
    //点击图片放大预览
    enlarge:function(em){
        var imgsrc = $(em).attr('src');
        $('.page_breakfastDiary .imgbox img').attr('src',imgsrc);
        $('.page_breakfastDiary .imgbox').show();
    },
    //关闭预览图片
    closeimg:function(){
        $('.page_breakfastDiary .imgbox').hide();
    },
    onload : function(){
        document.title="我的早餐日志";
        if (!isWeiXin()) {
            $.alert('请使用微信打开本网址', 'error');
            return;
        }

        if(!win.wxMember || !win.wxMember.nickname){
            $('.page_breakfastDiary .er_wei_ma').show();
            return;
        }else{
            $('.page_breakfastDiary .er_wei_ma').hide();
        }

        $('.page_breakfastDiary .intro .ileft .headimg').attr('src', win.wxMember.head_path);
        $('.page_breakfastDiary .intro .ileft .punch_user').text(win.wxMember.nickname);

        /*屏幕滚动事件*/
        $('.page_breakfastDiary.wrapper').on('scroll', function(){
            if($('.page_breakfastDiary .punch_content .no_more').length > 0){
                $(this).off('scroll');
                return;
            }
            //滚动加载内容
            if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !breakfastDiaryObject.winScrollSock){
                breakfastDiaryObject.winScrollSock = true;
                breakfastDiaryObject.loadlist(Math.ceil($('.page_breakfastDiary .punch_content .punch_item').size() / 5) + 1);
            }
        });

        ajax('Home/Sign/GetSigner', {}, function(d){
            breakfastDiaryObject.is_sign = d.is_sign;
            breakfastDiaryObject.serial_count = d.serial_count;
            breakfastDiaryObject.join_count = d.join_count;
            breakfastDiaryObject.total_count = d.total_count;
            breakfastDiaryObject.sign_datetime = d.sign_datetime;
            breakfastDiaryObject.change();
        }, 2);

        script.load('plugins/scrollByJie', function(){
            /***********ajax请求页面头部bander数据**************/
            ajax('Home/Index/banner', {type:4}, function(d){
                if(d.length > 0){
                    var sol = new myScroll();
                    sol.speed = 3;
                    sol.div = ".page_breakfastDiary .banner";
                    for(var i in d){
                        sol.src.push(d[i].path);
                        sol.link.push(d[i].url);
                    }
                    sol.start();
                }else
                    $('.page_breakfastDiary .banner').remove();
            });
        });
    },
    onshow : function(){
        breakfastDiaryObject.loadlist();
    },
    change : function(){
        if (breakfastDiaryObject.is_sign == 0) {
            $('.page_breakfastDiary .punch_head .punch_btn').attr('src', 'images/punch_btn.png');
            $('.page_breakfastDiary .punch_head .punch_time').text('今天还没有打卡哦!');
        } else {
            $('.page_breakfastDiary .punch_head .punch_btn').attr('src', 'images/punch_btn1.png');
            $('.page_breakfastDiary .punch_head .punch_time').text('今日打卡已完成!');
        }
        $('.page_breakfastDiary .intro .iright font[name="join"]').text(breakfastDiaryObject.join_count);
        $('.page_breakfastDiary .intro .iright font[name="series"]').text(breakfastDiaryObject.serial_count);
        $('.page_breakfastDiary .intro .iright font[name="total"]').text(breakfastDiaryObject.total_count);
    },
    //生成主题美图
    create : function(pic_id){
        if(!pic_id){
            var em = $('.page_breakfastDiary').find("[data-id='" + breakfastDiaryObject.delid + "']");
            var pic_id = em.find('.punch_img').data('id');
        }
        var data = {
            diy_id : 2,
            pic_id : pic_id,
            nickname : 'by: ' + win.wxMember.nickname,
            datetime : Math.round((new Date()).getTime()/1000).toString().timeFormat('Y.m.d')
        };
        jump('diyimage', data);
    }
};
