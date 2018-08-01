var app = getApp()
var that;
Page({
  data: {
    tips_id:'',
    tags:[],
  },
  onShow: function(){
    console.log('首页加载完成！');
  },
  onReady: function () {
    console.log('首页渲染完成！');
    that = this;
    that.loadtag();
  },
  loadtag:function(){
      app.ajax('Home/Index/official_tags', {}, function(d){
        console.log(d);
        that.setData({
            tags : d
        })
      },false);
  },
  getid:function(e){
      that.setData({
        tips_id: e.detail.value
      })
  },
  go:function(){
      if(that.data.tips_id == ''){
          app.alert('请输入活动ID','error');
      }else{
          wx.navigateTo({
             url: '../tips/detail?tips_id='+that.data.tips_id,
          });
      }
      
  },
  tagurl:function(e){
      wx.navigateTo({
          url: '../tips/index?category='+e.currentTarget.dataset.id,
      });
  },
});
