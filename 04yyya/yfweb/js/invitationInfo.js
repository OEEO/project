/**
 * Created by fyt on 2016/11/11.
 */
var invitationInfoObject = {
    order_id: null,
    check_code :null,
    showCode : function (){
        //制作二维码
        var qrcode = '<div class="view"><span></span><p>'+invitationInfoObject.check_code.replace(/^(\d{4})/, '$1 ')+'</p></div>';
        $.dialog(qrcode, null, true, 'codeBox');
        $('#dialogBox.codeBox .btns .agree').remove();
        $('#dialogBox.codeBox .btns .closeBtn').text('知道了');
        script.load('plugins/jquery.qrcode-0.12.0.min', function(){
            var width = $('#dialogBox.codeBox .view span').width();
            $('#dialogBox.codeBox .view span').qrcode({
                render: 'canvas',
                minVersion: 1,
                maxVersion: 40,
                ecLevel: 'L',
                left: 0,
                top: 0,
                size: width,
                fill: '#000',
                //background: null,
                text: invitationInfoObject.check_code,
                radius: 0,
                quiet: 0,
                mode: 0,
                mSize: 0.1,
                mPosX: 0.5,
                mPosY: 0.5,
                // label: invitationInfoObject.check_code,
                // fontname: 'sans',
                // fontcolor: '#b39851',
                //image: null
            });
        });

    },
    onload : function(){
        this.order_id = win.get.order_id;
        ajax('Home/Index/getBuyerOrder', {order_id : this.order_id}, function(d){
            //分享绑定
            if(window.location.href.indexOf('invitationInfo') == -1 ){
                var desc = d.tips_title;
                var url = win.host + '?page=invitationInfo&order_id=' + invitationInfoObject.order_id;
                share(d.tips_title, desc, url, d.tips_pic_path);

                showShareBox('发送邀请函');
            }

            if(d.info){
                $.alert(d.info, 'error');
                return;
            }

            $('.page_invitationInfo .invitebox .headpic').attr('src',d.member_pic_path);
            $('.page_invitationInfo .invitebox .nickname').html(d.nickname);
            $('.page_invitationInfo .invitebox .title').html(d.tips_title);
            $('.page_invitationInfo .invitebox .invitetime').html(d.start_time.timeFormat("Y.m.d H:i") +'-'+ d.end_time.timeFormat("Y.m.d H:i"));
            $('.page_invitationInfo .invitebox .iaddress').html(d.city_name+d.city_alt+d.area_name+d.area_alt+d.address);

            //查看地图
            $('.page_invitationInfo a.invitemap').click(function(){
                jump('map', {latitude: d.latitude, longitude: d.longitude, name: d.simpleaddress});
            });
            //查看菜单
            $('.page_invitationInfo .cmenu').click(function(){
                var cm = '';
                if(d.menu.length > 0){
                    cm += '<div class="menus">';
                    for(var i in d.menu){
                        if(d.menu[i].value == '' || (d.menu[i].value[0] && d.menu[i].value[0] == '')){
                            continue;
                        }
                        if(d.menu[i].name.toLowerCase() != 'tips'){
                            cm +='<p align="center" class="menu_yellow">- '+ d.menu[i].name +' -</p>';
                            for(var j in d.menu[i].value){
                                cm +='<p align="center" class="me_li">'+ d.menu[i].value[j] +'</p>';
                            }
                        }else{
                            cm +='<p align="center" class="menu_btitle"><img src="images/tips_icon@2x.png"/><span>'+ d.menu[i].value[0] +'</span></p>';

                        }
                    }
                    cm +='</div>';


                }
                $.dialog(cm, null, true, 'menuBox');
                $('#dialogBox.menuBox .btns .agree').remove();
                $('#dialogBox.menuBox .btns .closeBtn').text('知道了');
            });

            if(d.check_code == 'no'){
                $('.page_invitationInfo .ma font').html('已领取完');
                return;
            }else{
                if(d.check_code != ''){
                    invitationInfoObject.check_code = d.check_code;
                    $('.page_invitationInfo .ma font').html('查看消费码');
                }else{
                    $('.page_invitationInfo .ma font').html('获取消费码');
                }
            }
            $('.page_invitationInfo .ma').on('click', function(){
                //判断是否登录，没有登录则跳转登录
                if(!member){
                    win.login();
                    return;
                }else{
                    if(d.check_code != ''){
                        invitationInfoObject.showCode();
                    }else{
                        ajax('Home/Index/getOrderCode', {order_id : invitationInfoObject.order_id}, function(d){
                            if(d.info.code == 1){
                                $('.page_invitationInfo .ma font').html('已领取完');
                                $('.page_invitationInfo .ma').off('click');
                                return;
                            }else if(d.info.code == 2){
                                $.alert('领取成功');
                                invitationInfoObject.check_code = d.info.check_code;
                                $('.page_invitationInfo .ma font').html('查看消费码').attr('data',d.info.check_code);
                                $('.page_invitationInfo .ma').off('click').on('click', function(){
                                    invitationInfoObject.showCode();
                                });
                                return;
                            }else if(d.info.code == 4){
                                invitationInfoObject.check_code = d.info.check_code;
                                $('.page_invitationInfo .ma font').html('查看消费码').attr('data',d.info.check_code);
                                $('.page_invitationInfo .ma').off('click').on('click', function(){
                                    invitationInfoObject.showCode();
                                });
                                return;
                            }
                        });
                    }
                }
            });
        }, 2);
    }
};
