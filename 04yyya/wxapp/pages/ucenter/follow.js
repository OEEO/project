var app = getApp()
var that;
Page({
  data: {
      lists:[],
      page:1,
      followdata:[],
  },
  onShow: function(){
    console.log('首页加载完成！');
  },
  onReady: function () {
    console.log('首页渲染完成！');
     wx.setNavigationBarTitle({
          title: '关注'
      });
    that = this;
    this.loadlist();
  },
  loadlist : function(p){
    var p = p||1;
    app.ajax('Member/Follow/getlist', {get: {page:p},post:{type:1}}, function(d){
      if(!d.info){
        for(var i in d){
          that.data.lists.push(d[i]);
        }
        that.setData({
          followdata : that.data.lists
        });
      }
    },false);
  },
  follow:function(e){
    
      //取消关注
      var that = this;
      var daren_id=e.currentTarget.dataset.id;
      app.ajax('Member/Follow/changeFollow', {member_id:daren_id, type:0}, function(d){
			if(d.status == 1){
          app.alert('取消成功', function(){
              var datas = that.data.followdata;
              for(var i in datas){
                if(datas[i].member_id == daren_id){
                    datas.splice(i,1);
                }
              }
              that.setData({followdata:datas});
           });
			}else{
				  app.alert('操作失败', 'error');
			}
		});
  },
  loadMore:function(e) {
        console.log(e);
        that.setData( {
            page: that.data.page + 1
        })
        that.loadlist(that.data.page);
    },
});
