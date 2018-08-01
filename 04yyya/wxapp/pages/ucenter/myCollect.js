var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data:{
        lists:[],
        page : 1,
        collectlist:[]
    },
    onShow:function(){
        console.log('加载完成');
    },
    onReady:function(){
        console.log('渲染完成');
        that = this;
        that.loadcollect();
    },
    cancel:function(e){
        app.alert('您确定要取消收藏此活动吗?',function(){
            app.ajax('Member/Follow/ChangeCollect',{type:0,type_id:e.currentTarget.dataset.id,operate:0},function(d){
                if(d.status == 1){
                    app.alert('取消成功',function(){
                        app.reload('../ucenter/myCollect');
                    });
                }
            }, false);
        },'error', true);
    },
    loadcollect:function(p){
        var p = p || 1;
        app.ajax('member/follow/getCollectList',{get:{page:p}},function(d){
            console.log(d);
            if(!d.info){
                for(var i in d){
                    d[i].times.start_time = tool.timeFormat('Y-m-d（W） H:i',d[i].times.start_time);
                    d[i].times.end_time = tool.timeFormat('H:i',d[i].times.end_time);
                    that.data.lists.push(d[i]);
                }
                that.setData({
                    collectlist:that.data.lists
                })
            }
        },false)
    },
    loadMore:function(e) {
        console.log(e);
        that.setData( {
            page: that.data.page + 1
        })
        that.loadcollect(that.data.page);
    },
    
})