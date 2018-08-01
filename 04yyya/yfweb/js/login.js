var loginObject = {
    onload : function(){
        if(win.get.android){
            $('.page_login .weixin_log').remove();
        }
        $(window).resize(function () {
            $('.loginBox').height(win.height);
        });
        $('.loginBox').height(win.height);
        /*点击显示用户协议*/
        $('.agreement font').click(function () {
            $('.agreement_pages').show();
        });
        /*点击隐藏用户协议*/
        $('.agreement_pages .knowe').click(function () {
            $('.agreement_pages').hide();
        });

        $('.loginBox .pw').click(function () {
            $('.loginBox').children(':visible').animate({left: -1 * $(this).width(), opacity: 0}, 'fast', function () {
                $(this).css({'left': $(this).width(), opacity: 1}).hide();
            });
            $('.loginBox .pw_box').show();
            $('.loginBox .pw_box').animate({left: 0}, 'fast');
        });

        $('.loginBox .dx').click(function () {
            $('.loginBox').children(':visible').animate({left: -1 * $(this).width(), opacity: 0}, 'fast', function () {
                $(this).css({'left': $(this).width(), opacity: 1}).hide();
            });
            $('.loginBox .dx_box').show();
            $('.loginBox .dx_box').animate({left: 0}, 'fast');
        });
    },
    onshow:function(){

        //发送登录短信
        $('.loginBox .sendcode').click(function () {
            var tel = document.dx_box.telephone.value;
            if (!/^1\d{10}$/.test(tel)) {
                $.alert('手机号格式不正确', 'error');
                return;
            }
            ajax('Member/Index/sendSMS', {telephone: tel}, function (d) {
                if (d.status == 1) {
                    (function timejump(s) {
                        s--;
                        $('.loginBox .sendcode').css('color', '#eee').html('短信已发送(' + s + ')');
                        if (s <= 0) {
                            $('.loginBox .sendcode').css('color', '#fff').html('获取验证码');
                        } else {
                            window.setTimeout(function () {
                                timejump(s);
                            }, 1000);
                        }
                    })(60);
                } else {
                    $.alert(d.info, 'error');
                }
            });
        });

        //账户登录
        $(document.dx_box).submit(function () {
            if (btnSubmit.isLoading())return false;
            var data = {};
            data.telephone = this.telephone.value;
            data.smsverify = this.verifycode.value;
            btnSubmit.loading($(this).find('.submit'));
            ajax("Member/Index/register", data, function (d) {
                btnSubmit.close();
                if (d.status == 1) {
                    member = d.info.info;
                    win.saveSkey(d.info.info.id, d.info.skey);
                    $.alert('登录成功', function () {
                        var obj = {};
                        _hmt.push(['_setCustomVar', 1, 'Login', d.info.info.id]);
                        if (d.info.isRegister == 1) {
                            $('.Introductory_pages').show();
                            $('.Introductory_pages .xxniu').click(function () {
                                page.reload(page.backName, page.backData, page.backFun);
                            });
                        } else {
                            page.reload(page.backName, page.backData, page.backFun);
                        }
                    });
                } else {
                    $.alert(d.info, 'error');
                }
            });
            return false;
        });
    }
};



