//获取应用实例
var app = getApp()
var that;
Page({
  data: {
      order_id : null,
      stars : [1,1,1,1,1],
      message : '',
      pic_ids : []
  },
  onLoad: function(option){
    console.log('首页加载完成！');
    this.setData({
        order_id:option.order_id,
    });
  },
  onShow:function(){},
  onReady: function () {
    console.log('首页渲染完成！');
    wx.setNavigationBarTitle({
        title: '评价'
    });
    that = this;
  },
  change:function(e){
      var idx = e.currentTarget.dataset.index;
      var star = that.data.stars;
      for(var i = 0 ; i<5 ; i ++){
          if(i == idx){
             if(star[idx] == 0){
                star[idx]=1 
             }else{
                 star[idx]=0
             } 
          }
      }
      console.log(star);
      that.setData({
        stars: star
      })
  },
  bindvalue:function(e){
      that.setData({
        message: e.detail.value
      })
  },
  submits:function(){
      var content = that.data.message;
      if(content.length <= 0){
          app.alert('评论内容必须填写，且不能超过500字', 'error');
          return;
      }
      var num = 0;
      for(var i = 0 ; i<5 ; i ++){
          if(that.data.stars[i] == 1){
              num = num + 1;
          }
      }
      app.ajax('Home/Comment/add', {order_id : that.data.order_id, content : content, stars : num}, function(d){
				if(d.status == 1){
					app.alert('评论成功', function(){
					    wx.switchTab({
                            url: '../order/index'
                        })
					});
				}else{
					app.alert(d.info, 'error');
				}
			});
  },
  tishi:function(){
      app.alert('该功能正在开发中');
  }
});
