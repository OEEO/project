var app = getApp();
var storage = require('../../utils/storage.js');

Page({
    data : {
        showQrCode : false
    },
    onShow : function(){

    },
    onReady : function(){
        wx.setNavigationBarTitle({
            title: '设置'
        });
    },
    showCode : function(){
        this.setData({showQrCode:true});
    },
    hideCode : function(){
        this.setData({showQrCode:false});
    },
    logout : function(){
        app.ajax('Member/Index/logout', function(d){
            if(d.status == 1){
                storage.rm('autologin');
                app.member = null;
                console.log(app.member);
                wx.switchTab({
                  url: '../index/index'
                });
            }
        });
    }
});