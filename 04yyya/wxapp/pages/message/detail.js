var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data: {
        origin_id : '',
        page : 1,
        codes:[],
        scrollTop : 0,
        scrollId : null,
        msgdetail:[]
    },
    onLoad: function(option){
        this.setData({
            origin_id:option.origin_id,
        });
    },
    onShow:function(){},
    onReady : function(){
        that = this;
        this.loadmsg();
    },
    loadmsg : function(p){
        var p = p||1;
        app.ajax('Member/Message/getDetail', {get:{page:p},post:{origin_id:that.data.origin_id}}, function(d){
            console.log(d);
            if(!d.info){
                wx.setNavigationBarTitle({
                    title: d[0].nickname
                });
                for(var i in d){
                    d[i].datetime = tool.timeFormat('Y-m-d  H:i',d[i].datetime);
                    that.data.codes.unshift(d[i]);
                }
                that.setData({
                    msgdetail : that.data.codes,
                });
                if(p == 1){
                    setTimeout(function(){
                        that.setData({scrollTop : 999999});
                    }, 100);
                }
            }
        },false);
        
    },
    upper:function(e){
        that.setData({
            scrollId:'toview',
            page: that.data.page + 1
        })
        that.loadmsg(that.data.page);
    }
});