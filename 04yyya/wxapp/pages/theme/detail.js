var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data: {
        theme_id:null,
        sharetitle:null,
        dec:null,
        shareshow:true,
        list:[],
        member_invitecode: app.member==null?null:app.member.invitecode,
        path:null,
        indicatorDots: true,
        autoplay: true,
        interval: 5000,
        duration: 1000
    },
    onLoad: function(option){
        this.setData({
          theme_id:option.theme_id,
        })
        that = this;
    },
    onShow:function(){},
    onReady : function(){
        wx.setNavigationBarTitle({
            title: '专题详情'
        });
        var url = null;
        if(that.data.member_invitecode){
            url = '/pages/theme/themeDetail?theme_id='+that.data.theme_id+'&invitecode'+that.data.member_invitecode
        }else{
            url = '/pages/theme/themeDetail?theme_id='+that.data.theme_id
        }
        that.setData({
          path:url,
        })
        that.loaddetail();
    },
    loaddetail:function(){
        app.ajax('Home/Theme/getDetail',{theme_id:that.data.theme_id}, function(d){
            console.log(d);
            d.theme.title = d.theme.title.split('|');
            d.theme.datetime = tool.timeFormat('Y.m.d H:i',d.theme.datetime);
            for(var i in d.tips){
                d.tips[i].start_time = tool.timeFormat('Y-m-d',d.tips[i].start_time);
            }
            for(var i in d.tipsPass){
                d.tipsPass[i].start_time = tool.timeFormat('Y-m-d',d.tipsPass[i].start_time);
            }
            that.setData({
                sharetitle:d.theme.title,
                dec:d.theme.content,
                list:d,
            })
        },false);
    },
    share:function(){
        that.setData({
            shareshow : !that.data.shareshow
        })
    },
    onShareAppMessage: function () {
        return {
            title: that.data.sharetitle,
            desc: that.data.dec,
            path: that.data.path
        }
    }
});