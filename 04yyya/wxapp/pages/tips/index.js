//获取应用实例
var app = getApp()
var that;
Page({
  data: {
    category:null,
    lists:[],
    page : 1,
    tipsData : []
  },
  onLoad:function(option){
    console.log('加载完成！');
    this.setData({
        category:option.category,
    });
  },
  onShow: function(){},
  onReady: function () {
    console.log('渲染完成！');
    that = this;
    if(that.data.category == -4){
         wx.setNavigationBarTitle({
            title: '社交饭局'
         });
    }else if(that.data.category == -5){
        wx.setNavigationBarTitle({
            title: '大咖饭局'
         });
    }
   
    that.loadtips();
  },
  loadtips : function(p){
    var p = p||1;
    //加载活动列表
    app.ajax('Goods/Tips/getList', {get: {page:p}, post:{category:that.data.category}}, function(d){
      if(!d.info){
        for(var i in d){
          d[i].date = d[i].date.split(' - ')[0];
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
    setCollect:function(e){
      var that = this;
      var tips_id = e.currentTarget.dataset.id;
      var iscollect = e.currentTarget.dataset.iscollect;
      app.setcollect(tips_id,iscollect, function(collect){
        var data = that.data.tipsData;
        for(var i in data){
          if(data[i].id == tips_id)data[i].is_collect = collect;
        }
        that.setData({tipsData:data});
      });
  },
});
