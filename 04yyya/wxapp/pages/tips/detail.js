var tool = require('../../utils/tool.js')
var app = getApp();
var that;
Page({
    data:{
        tips_id : '',
        can_buy_time:1,
        times : 0,
        sumbittext:'JOIN',
        selectdata:[],
        banners : [],
        detailData : [],
        hidden:true,
        shareshow:true,
        timeshow:true,
        changid:0,
        toView:'',
        join:true,
        cinx:[1,2,3,4,5],
        day:null,
        hous:null,
        mins:null,
        secs:null,
        is_follow:null,
        is_collect:0,
        follow_num:0,
        currenturl:null,
        isshow:false,
        report_id:null,
        report_context:null,
    },
    onLoad:function(option){
        console.log("详情加载完成");
        this.setData({
            tips_id:option.tips_id,
        });
    },
    onShow:function(){
        that = this;
    },
    onReady: function () {
        console.log('详情渲染完成！');
        wx.setNavigationBarTitle({
            title: '饭局详情'
        });
        that = this;
        this.loaddetail();
    },
    DecTimes:function(){
        //倒计时函数
		clearInterval(app.tipsDetailInterval);
		app.tipsDetailInterval = setInterval(function(){
			try {
				var days = Math.floor(that.data.times / 24 / 3600);
				var hours = Math.floor(that.data.times % (24 * 3600) / 3600);
				var mins = Math.floor((that.data.times % (24 * 3600) % 3600) / 60);
				var secs = Math.floor((that.data.times % (24 * 3600) % 3600) % 60);
                var d = null;
                var h = null;
                var m = null;
                var s = null;
				if(days > 0){
					if(parseInt(days) < 10){
                        d = [0,days];
					}else{
						d = days.toString().split('');
					}
				}
				if(days > 0 || hours > 0){
					if(parseInt(hours) < 10){
                        h = [0,hours];
					}else{
						h = hours.toString().split('');
					}
				}
				if(days > 0 || hours > 0 || mins > 0){
					if(parseInt(mins) < 10){
                        m = [0,mins];
					}else{
						m = mins.toString().split('');
					}
				}
				if(days > 0 || hours > 0 || mins > 0 || secs > 0){
					if(parseInt(secs) < 10){
                        s = [0,secs];
					}else{
						s = secs.toString().split('');
					}
                    var ctime = that.data.times - 1;
                    that.setData({
                        timeshow : false,
                        day : d,
                        hous : h,
                        mins : m,
                        secs : s,
                        times:ctime,
                    })
				} else {
					clearInterval(app.tipsDetailInterval);
					that.setData({
                        timeshow : true,
                        join:true,
                    });
				}
			}catch(e){
				clearInterval(app.tipsDetailInterval);
			}
		}, 1000);
	},
    loaddetail : function(){
        app.ajax('Goods/Tips/getDetail', {tips_id:that.data.tips_id}, function(d){
            if(!d.info){
                if(d.time.stock <= 0){
                    that.setData({
                        can_buy_time : 0,
                        join:false,
                    });
				}else if(d.time.stop_buy_time < (new Date()).getTime()/1000){
					that.setData({
                        can_buy_time : -1,
                        join:false,
                    });
				}
                if(parseInt(d.time.start_buy_time) > (new Date()).getTime() / 1000){
                    that.setData({
                        times : parseInt(d.time.start_buy_time) - Math.round((new Date()).getTime() / 1000),
                        join:false,
                    });
				    that.DecTimes();
			    }
                if(d.isFree == 1){
                    that.setData({
                        sumbittext:'FREE JOIN',
                    });
                }
                d.time.start_time = tool.timeFormat('Y-m-d (W) H:i',d.time.start_time);
                d.time.end_time = tool.timeFormat('H:i',d.time.end_time);
                d.time.stop_buy_time = tool.timeFormat('Y-m-d H:i',d.time.stop_buy_time);
                for(var i in d.times){
                    if(d.times[i].stock <= 0){
                        d.times[i].signtext = '名额已满';
                        d.times[i].none = ' none';
                    }else if(d.times[i].stop_buy_time < (new Date()).getTime()/1000){
                        d.times[i].signtext = '已截止报名';
                        d.times[i].none = ' none';
                    }else{
                        d.times[i].signtext = '已报名'+d.times[i].count+'人';
                        d.times[i].none = '';
                    }
                    d.times[i].start_time = tool.timeFormat('Y-m-d (W) H:i',d.times[i].start_time).split(' ');
                    d.times[i].end_time = tool.timeFormat('H:i',d.times[i].end_time);
                    d.times[i].stop_buy_time = tool.timeFormat('Y-m-d H:i',d.times[i].stop_buy_time);
                }
                var desc = '';
                for(var i in d.edge){
                    desc += d.edge[i] + ' ';
                }
                that.setData({
                    is_follow:d.isfollow,
                    is_collect:d.is_collect,
                    follow_num:parseInt(d.follow_num)*3,
                    dec:desc,
                    selectdata:d.time,
                    detailData : d
                });
                app.setHistory('tips-' + that.data.tips_id, {
                    type : 0,
                    id : that.data.tips_id,
                    title : d.title,
                    path : d.mainpic
                });
            }
        }, false);
        
    },
    moretime:function(){
        //更多时间弹框的显示、隐藏
        that.setData({
            hidden : !that.data.hidden
        })
    },
    changview:function(e){
        //HOST、菜单、环境瞄点
        that.setData({
            toView : e.currentTarget.dataset.menu,
            changid : e.currentTarget.dataset.id
        })
    },
    submitOrder:function(){
        //提交订单
        if(that.data.can_buy_time == 0){
            app.alert('名额已满', 'error'); return false;
        }else if(that.data.can_buy_time == -1){
            app.alert('已截止报名', 'error'); return false;
        }
        wx.navigateTo({
            url: 'confirmEnrolling?tips_id='+that.data.tips_id+'&time_id='+that.data.selectdata.id
        });
    },
    lookmap:function(){
        //查看地图
        wx.openLocation({
            longitude: parseFloat(that.data.detailData.longitude),
            latitude: parseFloat(that.data.detailData.latitude),
            name: that.data.detailData.simpleaddress,
            address: '我在这里',
            scale:1
        }) 
    },
    selectTime:function(e){
        //时间选择
        var idx = e.currentTarget.dataset.index;
        var start_buy_time = e.currentTarget.dataset.starttime;
        var stop_buy_time = tool.stamp(e.currentTarget.dataset.stoptime);
        var stock = e.currentTarget.dataset.stock;
        if(stock <= 0){
            that.setData({
                can_buy_time : 0,
                join:false,
            });
        }else if(stop_buy_time < (new Date()).getTime()/1000){
            that.setData({
                can_buy_time : -1,
                join:false,
            });
        }
        if(parseInt(start_buy_time) > (new Date()).getTime() / 1000){
            that.setData({
                times : parseInt(start_buy_time) - Math.round((new Date()).getTime() / 1000),
                join:false,
            });
            that.DecTimes();
        }
            that.data.detailData.times[idx].start_time = that.data.detailData.times[idx].start_time[0]+' '+that.data.detailData.times[idx].start_time[1]+' '+that.data.detailData.times[idx].start_time[2];
            that.setData({
                selectdata:that.data.detailData.times[idx],
            });
           
    },
    follow:function(e){
        //关注达人
        var that = this;
        var daren_id=e.currentTarget.dataset.id;
        app.follow(this.data.is_follow,daren_id,this.data.follow_num,function(f,n){
            that.setData({
                is_follow:f,
                follow_num:n
            })
        }
        )
    },
    // share:function(){
    //     //分享提示框显示隐藏
    //     that.setData({
    //         shareshow : !that.data.shareshow
    //     })
    // },
    // onShareAppMessage: function () {
    //     return {
    //         title: that.data.detailData.title,
    //         desc: that.data.dec,
    //         path: '/pages/tips/detail?tips_id='+that.data.tips_id
    //     }
    // },
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
                    var das = that.data.detailData;
                    for(var i in das.comment){
                    if(das.comment[i].id == reportdata.type_id)das.comment[i].is_report = 1;
                    }
                    that.setData({detailData:das});
				}
				app.alert(d.info,'error');
			});
    },
    setCollect:function(){
      var that = this;
      app.setcollect(this.data.tips_id,this.data.is_collect, function(collect){
        that.setData({is_collect:collect});
      });
  },
})
