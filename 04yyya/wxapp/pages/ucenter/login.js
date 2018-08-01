var app = getApp();
var storage = require('../../utils/storage.js');
var that,tel = '';
Page({
    data : {
        agreement : false,
        smsSending : false,
        smsSendingSecond : 60,
        submitWords : "确 定",
        submitLoading : false,
        Introductory : false,
        actname : '登录'
    },
    onLoad : function(){
        that = this;
    },
    onReady : function(){
        wx.setNavigationBarTitle({
            title: '会员登录'
        });
    },
    showAgreement : function(){
        this.setData({agreement:true});
    },
    hideAgreement : function(){
        this.setData({agreement:false});
    },
    setTel : function(e){
        tel = e.detail.value;
    },
    getsms : function(e){
        if(!/^1\d{10}$/.test(tel)){
            app.alert('手机号格式不正确', 'error');
            return;
        }
        app.ajax('Member/Index/sendSMS', {telephone:tel}, function(d){
            if(d.status == 1){
                that.setData({smsSending: true});
                (function timejump(s){
                    s --;
                    that.setData({smsSendingSecond: s});
                    if(s <= 0){
                        tel = '';
                        that.setData({smsSending: false});
                    }else{
                        setTimeout(function(){
                            timejump(s);
                        }, 1000);
                    }
                })(60);
            }else{
                app.alert(d.info, 'error');
            }
        });
    },
    submit : function(e){
        if(this.data.submitLoading)return false;
        var data = {};
        data.telephone = e.detail.value.telephone;
        data.smsverify = e.detail.value.verifycode;
        this.setData({submitLoading:true, submitWords:'提交中..'});
        wx.login({
            success: function (res) {
                if (res.errMsg != "login:ok") {
                    app.alert(res.errMsg, 'error');
                    return;
                }
                data.code = res.code;
                app.ajax("Member/Index/register", data, function (d) {
                    that.setData({ submitLoading: false, submitWords: '确 定' });
                    if (d.status == 1) {
                        app.member = d.info.info;
                        app.saveSkey(d.info.info.id, d.info.skey);
                        app.alert('登录成功', function () {
                            /*
                            if (d.info.isRegister == 1) {
                                that.setData({ Introductory: true });
                            } else {
                                that.goBack();
                            }
                            */
                            that.goBack();
                        });
                    } else {
                        if (d.info == 'Double_account') {
                            app.alert('该手机号和授权微信已各自注册了一个账号,是否合并?', function () {
                                app.ajax('Member/index/MergeAccount', function (d) {
                                    if (d.status == 1) {
                                        app.saveSkey(d.info.info.id, d.info.skey);
                                        app.alert('合并成功', function () {
                                            that.goBack();
                                        });
                                    }
                                });
                            }, 'error');
                        } else
                            app.alert(d.info, 'error', false);
                    }
                });
            },
        });

        return false;
    },
    wxlogin : function(){
        wx.login({
            success : function(res){
                if(res.errMsg != "login:ok"){
                    app.alert(res.errMsg, 'error');
                    return;
                }
                var code = res.code;
                wx.getUserInfo({
                    success: function(res) {
                        console.log(res);
                        app.ajax('Home/Wx/getOauthLogin', {get:{code:code, isapp:1}, post:{encryptedData:res.encryptedData, iv:res.iv}}, function(d){
                            if(d.status == 1){
                                if(d.info.skey) {
                                    app.member = d.info.info;
                                    app.saveSkey(d.info.info.id, d.info.skey);
                                    if (!d.info.logined) {
                                        app.alert('登录成功', function () {
                                            that.goBack();
                                        });
                                    } else {
                                        app.alert('授权成功', function () {
                                            that.goBack();
                                        });
                                    }
                                // }else if(d.info.open_id){
                                //     var jumpUrl = sessionStorage.jumpUrl;
                                //     window.sessionStorage.removeItem('jumpUrl');
                                //     window.location.href = win.host + '?' + jumpUrl + '&open_id=' + d.info.open_id;
                                }else{
                                    app.alert(d.info, function(){
                                        that.setData({
                                            actname : '注册',
                                            submitWords : '提交绑定手机'
                                        });
                                    }, 'error');
                                }
                            } else {
                                app.alert(d.info);
                            }
                        });
                    },
                    fail : function (res) {
                        console.log(res);
                    }
                });
            },
        });
    },
    goBack : function(){
        that.setData({Introductory:false});
        if(storage.get('jumpUrl')){
            var jumpUrl = storage.get('jumpUrl');
            // storage.rm('jumpUrl');
            // wx.switchTab({
            //     url: jumpUrl,
            //     success : function(){
            //         wx.redirectTo({
            //             url: jumpUrl
            //         });
            //     }
            // });
            wx.navigateBack();
        }else{
            wx.switchTab({
                url: 'index'
            });
        }
    },
    closes:function(){
        wx.switchTab({
            url: '../index/index'
        });
    }
});