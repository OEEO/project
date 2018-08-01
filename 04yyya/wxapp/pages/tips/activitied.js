//获取应用实例
var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
  data: {
    title : '',
    member_id : null,
	follow : null,
    lists : [],	
	page : 1,
	is_overdue:0,
    tipsData : []
  },
  onLoad: function(option){
    console.log('加载完成！');
    this.setData({
        is_overdue:option.is_overdue,
        member_id:option.member_id,
    });
        
  },
  onShow: function(){},
  onReady: function () {
    console.log('渲染完成！');
    if(this.data.is_overdue == 0){
        wx.setNavigationBarTitle({
            title: '即将开始的活动'
        });
    }else{
        wx.setNavigationBarTitle({
            title: '举办过的活动'
        });
    }
    
    that = this;
    this.loadtips();
  },
  loadtips : function(p){
    var p = p||1;
    //加载活动列表
    app.ajax('Goods/Tips/getlist', {get: {page:p},post:{member_id:that.data.member_id,is_overdue:that.data.is_overdue}}, function(d){
      if(!d.info){
        for(var i in d){
          d[i].start_time = tool.timeFormat('m月d日 W H:i',d[i].start_time);
          d[i].end_time = tool.timeFormat('H:i',d[i].end_time);
          that.data.lists.push(d[i]);
        }
        that.setData({
          tipsData : that.data.lists
        });
      }
    },false);
  },
  loadMore:function(e) {
        console.log(e);
        that.setData( {
            page: that.data.page + 1
        })
        that.loadtips(that.data.page);
    },
});
