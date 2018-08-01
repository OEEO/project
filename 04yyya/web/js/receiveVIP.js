/**
 * Created by fyt on 2017/2/18.
 */
var receiveVIPObject = {
    tips_times_id:null,
    type:null,
    type_id:null,
    privilege_id:null,
    member_privilege_id:null,
    times_type: '1',
    tourl:function(){
        var data = {};

        // if (receiveVIPObject.times_type === '1') {
        //     data.raise_id = receiveVIPObject.type_id;
        //     data.times_id = receiveVIPObject.tips_times_id;
        //     data.id = receiveVIPObject.member_privilege_id;
        //     jump('raisePay', data);
        // } else {
            data.raise_id = receiveVIPObject.type_id;
            //data.member_privilege_id = receiveVIPObject.member_privilege_id;
            jump('raiseDetail', data);
       // }


    },
    onload : function(){
        receiveVIPObject.type = win.get.type;
        receiveVIPObject.type_id = win.get.type_id;
        receiveVIPObject.privilege_id = win.get.privilege_id;
        receiveVIPObject.tips_times_id = win.get.tips_times_id;
        ajax('Goods/Raise/GetPrivilege', {type:receiveVIPObject.type,type_id:receiveVIPObject.type_id,privilege_id:receiveVIPObject.privilege_id,tips_times_id:receiveVIPObject.tips_times_id}, function(d){
            receiveVIPObject.member_privilege_id = d.member_privilege_id;
            // var desc = d.title;
            var url = win.host + '?page=receiveVIP&'+'privilege_id='+receiveVIPObject.privilege_id+'&type='+receiveVIPObject.type+'&type_id='+receiveVIPObject.type_id+'&tips_times_id='+receiveVIPObject.tips_times_id;
//            share('您有一份「青梅祝酒」的优先认筹权待领取', '百年匠心，三代匠人，许你一坛时光醇酿', url, d.pic_path);
//             share('您有一份小隐农场的优先认筹权待领取','900天研发，九年原生态种植，这颗身价逾200万的无花果还是亚运会特供产品，深受众多运动员和五星级名厨的喜爱。', url, 'http://wx1.sinaimg.cn/mw690/0060lm7Tly1fss2lf2qkwj30dw0dwacg.jpg');
            var title = '您有一份【Duira猫山王榴莲】的优先认筹权待领取';
            var desc = '等待它“瓜熟蒂落”，把世界公认榴莲最佳产地的猫山王带到你面前。';
            var stallTitle = 'Duira猫山王榴莲';
            share(title, desc, url, 'http://yummy194.cn/images/receiveVIP-share-img.jpg');
            // $('.page_receiveVIP .vipbeform .headpic').attr('src',d.head_pic_path);
            // $('.page_receiveVIP .vipbeform .nickname').html(d.nickname);
            $('.page_receiveVIP .vipbeform .title').html(d.title);
            receiveVIPObject.times_type = d.times_type;
            if(d.is_member == 0){
                $('.page_receiveVIP .centerbox .vipbeform').show();
            }else{
                if((d.is_receive == 1 || d.is_receive == 3) && d.times_type === '1'){
                    $('.page_receiveVIP .vipafter .stall_title').html( stallTitle);
                    $('.page_receiveVIP .vipafter .stall_price').html('￥'+ d.raise_times_price);
                    $('.page_receiveVIP .vipafter .stall_des').html(d.raise_times_content);
                    $('.page_receiveVIP .centerbox .vipbeform').hide();
                    $('.page_receiveVIP .centerbox .novip').hide();
                    $('.page_receiveVIP .centerbox .vipafter').show();
                    return;
                }else if(d.is_receive == 1 || d.is_receive == 3) {
                    $('.page_receiveVIP .vipafter .stall_title').html( stallTitle);
                    $('.page_receiveVIP .vipafter .stall_price').hide();
                    $('.page_receiveVIP .vipafter .stall_des').hide();
                    $('.page_receiveVIP .centerbox .vipbeform').hide();
                    $('.page_receiveVIP .centerbox .novip').hide();
                    $('.page_receiveVIP .centerbox .vipafter').show();
                    return;
                }else if(d.is_receive == 2){
                    $('.page_receiveVIP .centerbox .vipbeform').hide();
                    $('.page_receiveVIP .centerbox .vipafter').hide();
                    $('.page_receiveVIP .centerbox .novip').show();
                }else if(d.is_receive == 4){
                    page.reload('orderRaiseDetail',{order_id:d.order_id});
                    return;
                }else if(d.is_receive == 6){
                    $('.page_receiveVIP .receivebox').remove();
                    $.alert('众筹已经开始，可以直接认筹',function(){
                        page.reload('raiseDetail',{raise_id:receiveVIPObject.type_id});
                    },'error');
                    return;
                }else if(d.is_receive == 7){
                    $('.page_receiveVIP .receivebox').remove();
                    $.alert('该众筹不存在','error');
                    return;
                }else{
                    $('.page_receiveVIP .receivebox').remove();
                    $.alert('领取失败','error');
                    return;
                }
            }

        },2);
    },
    onshow:function () {
        //发送登录短信
        $('.page_receiveVIP .centerbox .sendcode').click(function(){
            var tel = document.vipform.phone.value;
            if(!/^1\d{10}$/.test(tel)){
                $.alert('手机号格式不正确', 'error');
                return;
            }
            ajax('Member/Index/sendSMS', {telephone:tel}, function(d){
                if(d.status == 1){
                    (function timejump(s){
                        s --;
                        $('.page_receiveVIP .centerbox .sendcode').css('color','#eee').html('短信已发送('+ s +')');
                        if(s <= 0){
                            $('.page_receiveVIP .centerbox .sendcode').css('color','#9c855c').html('获取验证码');
                        }else{
                            window.setTimeout(function(){
                                timejump(s);
                            }, 1000);
                        }
                    })(60);
                }else{
                    $.alert(d.info, 'error');
                }
            });
        });
        //账户登录
        $(document.vipform).submit(function(){
            if(btnSubmit.isLoading())return false;
            var data = {};
            data.telephone = this.phone.value;
            data.smsverify = this.code.value;
            console.log(data);
            btnSubmit.loading($(this).find('.vipsubmit'),'领取中');
            ajax("Member/Index/register", data, function(d){
                btnSubmit.close();
                if(d.status == 1){
                    member = d.info.info;
                    win.saveSkey(d.info.info.id, d.info.skey);
                    page.reload();
                }else{
                    $.alert(d.info, 'error');
                }
            });
            return false;
        });
    }
};
