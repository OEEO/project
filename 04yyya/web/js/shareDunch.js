var shareDunchObject= {
    sign_id : null,
    onload : function(){
        shareDunchObject.sign_id = win.get.sign_id;
        ajax('Home/Sign/GetEnjoySign', {sign_id:shareDunchObject.sign_id}, function(d){
            if(d.status == 0){
                $('.page_shareDunch.wrapper').css('background','#fff');
                $('.page_shareDunch .you').remove();
                $('.page_shareDunch .nopunch').show();
                if(shareDunchObject.sign_id == -1){
                    $('.page_shareDunch .nopunch').attr('src','images/pic1.png');
                }else{
                    $('.page_shareDunch .nopunch').attr('src','images/nopunch.png');
                }
            }else{
                var desc = '这是我坚持好好吃早餐的第'+d.open_member.serial_count+'天！一起加入吗？';
                var url = win.host + '?page=shareDunch&sign_id='+d.sign.sign_id;
                share(d.open_member.nickname+'的早餐日志', desc, url, d.sign.sign_path);
                $('.page_shareDunch .nopunch').hide();
                $('.page_shareDunch .headimg').attr('src',d.open_member.head_path);
                $('.page_shareDunch .symbolbg .punch_time').text(d.open_member.nickname);
                $('.page_shareDunch .intro .ileft font[name="series"]').text(d.open_member.serial_count);
                $('.page_shareDunch .punch_item .iteml').html('<i></i>'+d.sign.datetime.timeFormat('Y-m-d W H:i'));
                $('.page_shareDunch .punch_item .itemtitle').html('<i></i>'+d.sign.title);
                $('.page_shareDunch .punch_item .punch_img').attr('src',d.sign.sign_path);
            }
            //判断是否是用户自己进入自己分享的早餐打卡页面
            if(d.is_wxUser == 1){
                $('.page_shareDunch.footer').text('进入我的主页').off('click').on('click', function(){
                    jump('breakfastDiary');
                });
            }
        }, 2);
        $('.page_shareDunch.footer').on('click', function(){
            $.alert('<div class="title_top">长按二维码关注公众号加入早餐打卡</div><img src="images/nv.jpg">', 'puncherror');
        })
    }
};
