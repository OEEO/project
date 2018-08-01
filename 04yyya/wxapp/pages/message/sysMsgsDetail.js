var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data: {
        type : '',
        page : 1,
        codes:[],
        // scrollTop : 0,
        // scrollId : null,
        sysdetail:[],
        finish: false,
        message: 1,
        curIndex:'',//消息列表当前点击
    },
    onLoad: function(option){
        //判断是否登录
        if (app.member == null) {
            wx.redirectTo({
                url: '../ucenter/login'
            });
            return;
        }
        console.log('加载完成');
        this.setData({
 //           type:option.type,
            type:0,
        });
    },
    onShow:function(){},
    onReady : function(){
        console.log('渲染完成');
        wx.setNavigationBarTitle({
            title: '消息'
        });
        that = this;
        this.loadsysmsg();
    },
    loadsysmsg : function(p){
        if (this.data.finish) {
            return;
        }
        var p = p||1;
        app.ajax('Member/Message/getDetail', {get:{page:p},post:{type:that.data.type}}, function(d){
            console.log(d);
            if(!d.info){
                if (d.length === 0) {
                    that.setData({
                        finish: true
                    })
                    that.setData({
                        message: 0,
                    });
                }
                for(var i in d){
                    if(!d[i].content)continue;
                    d[i].datetime = tool.timeFormat('Y-m-d  H:i',d[i].datetime);
                    that.data.codes.push(d[i]);
                }
                that.setData({
                    sysdetail : that.data.codes,
                });
                // if(p == 1){
                //     setTimeout(function(){
                //         that.setData({scrollTop : 99999});
                //     }, 100);
                // }
            } 
        },false);
        
    },
    lower:function(e){
        if (this.data.finish) {
            return;
        }
        that.setData({
            // scrollId:'toview',
            page: that.data.page + 1
        })
        that.loadsysmsg(that.data.page);
    },
    toggleClassActive(e){
      let i = e.currentTarget.dataset.index;
      this.setData({
        curIndex: i
      });
    },
});