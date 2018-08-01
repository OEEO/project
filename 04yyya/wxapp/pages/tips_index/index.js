//获取应用实例
var app = getApp()
var tool = require("../../utils/tool.js")
var storage = require("../../utils/storage.js");
var that;
Page({
  data: {
    city : {
      name : app.city.name,
      id : app.city.id
    },
    cityArr : [],
    cityLayShowed: false,
    lists:[],
    page : 1,
    banners : [],
    tipsData : [],
    selectid:null,
  },
  onShow: function(){
    console.log('首页加载完成！');
  },
  onReady: function () {
    console.log('首页渲染完成！');
    that = this;
    this.setData({city:{
      name : app.city.name,
      id : app.city.id
    }});
    //加载banner
    app.ajax('Home/Index/banner', {type:0}, function(d){
      for(var i in d){
        if(d[i].url.indexOf('tips_id') > 0){
            d[i].url = '../tips/detail?tips_id='+tool.abs(d[i].url,'tips_id=');
        }else if(d[i].url.indexOf('theme_id') > 0){
            d[i].url = '../theme/detail?theme_id='+tool.abs(d[i].url,'theme_id=');
        }else{
            d[i].url = '';
        }
        
      }
      that.setData({
        banners : d
      });
		});
    this.loadtips();
  },
  loadtips : function(p){
    var p = p||1;
    //加载活动列表
    app.ajax('Goods/Tips/getList', {get: {page:p}}, function(d){
      if(!d.info){
        for(var i in d){
          d[i].date = d[i].date.split(' - ')[0];
          that.data.lists.push(d[i]);
        }
        that.setData({
          tipsData : that.data.lists
        });
      }
    });
  },
  changeCity : function(event){
    if(this.data.cityArr.length == 0){
      //加载经营城市
      app.ajax('Home/Index/citys', function(d){
        var arr = [];
        for(var i in d){
          arr.push({
            id : i,
            name : d[i]
          });
        }
        that.setData({
          cityArr : arr
        });
        that.changeCity();
      });
    }else{
      this.setData({
        cityLayShowed : !this.data.cityLayShowed
      });
    }
  },
  changeCityOpt : function(e){
    app.ajax('Home/Index/ChangeCity', {city_id : e.currentTarget.dataset.id}, function(d){
      if(d.status == 1){
        storage.set('city_id', d.info.id);
        storage.set('city_name', d.info.name);
        app.alert('切换成功', function(){
          app.city.id = d.info.id;
          app.city.name = d.info.name;
          that.setData({
            city : {
              name : app.city.name,
              id : app.city.id
            }
          });
          that.setData({
            lists:[],
            tipsData : [],
            banners : []
          });
          that.onReady();
        });
      }else{
        $.alert(d.info, 'error');
      }
    });
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
  loadMore:function(e) {
        that.setData( {
            page: that.data.page + 1
        })
        that.loadtips(that.data.page);
    },
});
