var app = getApp();
var that;
Page({
    data: {
        msgData:[],
        power:0
    },
    onShow: function(){
        //判断是否登录
        if(app.member == null){
            wx.redirectTo({
              url: '../ucenter/login'
            });
            return;
        }
        that = this;
        this.loadmsg();
    },
    onReady : function(){
        wx.setNavigationBarTitle({
            title: '我的消息'
        });
        that = this;
        this.setData({power:app.ws.power});
    },
    loadmsg : function(p){
        var p = p||1;
        app.ajax('Member/Message/getList', {get:{page:p},post:{is_all:1}}, function(d){
            console.log(d);
            if(!d.info){
                for(var i in d.more){
                    d.more[i].datetime = d.more[i].datetime.split(' ')[0].split('-');
                }
                that.setData({
                    msgData : d
                });
                
            }
        },false);
    },
    ws_power:function(d){
        this.setData({power:d});
    }
});