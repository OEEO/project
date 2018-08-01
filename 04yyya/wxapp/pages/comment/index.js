//获取应用实例
var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
  data: {
      ds:{},
    member_id : '',
    cinx : [1,2,3,4,5],
    commentData : [],
    currenturl:null,
    isshow:false,
    report_id:null,
    report_context:null,
  },
  onLoad: function(option){
    console.log('加载完成！');
    this.setData({
        ds : option
    });
  },
  onShow:function(){},
  onReady: function () {
    console.log('渲染完成！');
    wx.setNavigationBarTitle({
        title: '评论'
    });
    that = this;
    that.loadcomment();
  },
  loadcomment : function(p){
    var p = p||1;
    //加载活动列表
    app.ajax('home/comment/getlist', {get:{page:p}, post:that.data.ds}, function(d){
      console.log(d);
      if(!d.info){
        for(var i in d){
          d[i].datetime = tool.timeFormat('Y.m.d H:i',d[i].datetime);
          if(d[i].reply && d[i].reply.length > 0){
            d[i].reply[0].datetime = tool.timeFormat('Y.m.d H:i',d[i].reply[0].datetime);
          }
        }
        that.setData({
          commentData : d
        });
      }
    });
  },
  geturl:function(e){
        that.setData({
            currenturl : e.currentTarget.dataset.url,
        })
    },
    checkpic:function(e){
        var pics = e.currentTarget.dataset.pics;
        wx.previewImage({
            current: that.data.currenturl, 
            urls: pics,
        })
    },
    report:function(e){
        that.setData({
            report_id:e.currentTarget.dataset.id,
            isshow : !that.data.isshow,
            report_context : '',
        })
    },
    reporttext:function(e){
        that.setData({
            report_context:e.detail.value
        })
    },
    isreport:function(){
        var reportdata = {};
			reportdata.type = 3;
			reportdata.type_id = that.data.report_id;
			reportdata.content = that.data.report_context;
			app.ajax('home/index/exception', reportdata, function(d){
				if(d.status == 1){
					var das = that.data.commentData;
                    for(var i in das){
                        if(das[i].id == reportdata.type_id)
                            das[i].is_report = 1;
                    }
                    that.setData({commentData:das});
				}
				app.alert(d.info,'error');
			});
    }
});
