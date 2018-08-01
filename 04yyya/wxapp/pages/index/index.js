//获取应用实例
var app = getApp()
var tool = require("../../utils/tool.js")
var storage = require("../../utils/storage.js");
var that;
Page({
    data: {
        city: {
            name: app.city.name,
            id: app.city.id
        },
        cityArr: [],
        cityLayShowed: false,
        lists: [],
        lists1: [],
        lists2: [],
        page: 1,
        banners: [],
        tipsData: [],
        selectid: null,
        preview: 1,
        cur_index: 0,
        selectorItems: [
            { name: '全部项目', key: 0 },
            { name: '过往项目', key: 1 },
            { name: '预告', key: 2 }
        ],
        //banner中的图片跳转 webview 的地址数组
        bannerUrl:[
          'http://yummy194.cn/h5/yami-crowdfunding.html?_ijt=du25g8hj5jq5bbk6977t6s331v',
          'http://yummy194.cn/h5/summit_meeting.html'
        ]
    },
    onShow: function () {
        console.log('首页加载完成！');
    },
    onReady: function () {
        console.log('首页渲染完成！');
        that = this;
        this.setData({
            city: {
                name: app.city.name,
                id: app.city.id
            }
        });
        //加载banner
        app.ajax('Home/Index/banner', { type: 0 }, function (d) {
            for (var i in d) {
                if (d[i].url.indexOf('tips_id') > 0) {
                    d[i].url = '../tips/detail?tips_id=' + tool.abs(d[i].url, 'tips_id=');
                } else if (d[i].url.indexOf('theme_id') > 0) {
                    d[i].url = '../theme/detail?theme_id=' + tool.abs(d[i].url, 'theme_id=');
                } else {
                    d[i].url = '';
                }
                d[i].url = that.data.bannerUrl[i];
            }
            that.setData({
                banners: d
            });
        });
        this.loadtips();
    },
    loadtips: function (p) {
        var p = p || 1;
        //加载活动列表
        /*
        app.ajax('Goods/Tips/getList', { get: { page: p } }, function (d) {
            if (!d.info) {
                for (var i in d) {
                    d[i].date = d[i].date.split(' - ')[0];
                    that.data.lists.push(d[i]);
                }
                that.setData({
                    tipsData: that.data.lists
                });
            }
        });
        */
        app.ajax('Goods/Raise/getlist', { get: { page: p } }, function (d) {
            
            if (!d.info) {
                for (var i in d) {
                    d[i].datetime = d[i].datetime.split(' - ')[0];
                    d[i].isRaiseBegin = new Date().getTime() / 1000 - d[i].start_time > 0;
                    d[i].start_time = tool.timeFormat('Y年m月d日', d[i].start_time);
                    d[i].progress =  Math.min(100, (+d[i].totaled / +d[i].total) * 100) + '%';
                    d[i].progressTitle = ((+d[i].totaled / +d[i].total) * 100).toFixed(2) + '%';
                    d[i].less_day = tool.calLessDayIndex(Date.now() / 1000, d[i].end_time, d[i].start_time, null, +d[i].totaled - +d[i].total);
                    if (d[i].is_preview == '0') {
                        that.data.lists.push(d[i]);
                    }

                }
                that.setData({
                    raiseData: that.data.lists,

//                    time: tool.timeFormat('Y年m月d日', that.data.lists.start_time)
                });
            }
            
        });

        app.ajax('Goods/Raise/getlist', { get: { page: p } }, function (d) {

            if (!d.info) {
                for (var i in d) {
                    d[i].datetime = d[i].datetime.split(' - ')[0];
                    d[i].start_time = tool.timeFormat('Y年m月d日', d[i].start_time);
                    d[i].progress = Math.min(100, (+d[i].totaled / +d[i].total) * 100) + '%';
                    d[i].progressTitle = ((+d[i].totaled / +d[i].total) * 100).toFixed(2) + '%';
                    d[i].less_day = tool.calLessDayIndex(Date.now() / 1000, d[i].end_time, d[i].start_time, null, +d[i].totaled - +d[i].total);
                    if (d[i].less_day == '已成功') {
                        that.data.lists1.push(d[i]);
                    }
                }
                that.setData({
                    raiseData1: that.data.lists1,

                    //                    time: tool.timeFormat('Y年m月d日', that.data.lists.start_time)
                });
            }

        });


        app.ajax('Goods/Raise/getlist', { get: { page: p } }, function (d) {

            if (!d.info) {
                for (var i in d) {
                    d[i].datetime = d[i].datetime.split(' - ')[0];
                    d[i].start_time = tool.timeFormat('Y年m月d日', d[i].start_time);
                    d[i].progress = Math.min(100, (+d[i].totaled / +d[i].total) * 100) + '%';
                    d[i].progressTitle = ((+d[i].totaled / +d[i].total) * 100).toFixed(2) + '%';
                    d[i].less_day = tool.calLessDayIndex(Date.now() / 1000, d[i].end_time, d[i].start_time, null, +d[i].totaled - +d[i].total);
                    if (d[i].is_preview == '1') {
                        that.data.lists2.push(d[i]);
                    }
                }
                console.log(that.data.lists2);
                if (that.data.lists2 === undefined || that.data.lists2.length === 0) {
                    that.setData({
                        preview: 0,
                    });
                }
                that.setData({
                    raiseData2: that.data.lists2,

                    //                    time: tool.timeFormat('Y年m月d日', that.data.lists.start_time)
                });
            }
        });
    },
    changeCity: function (event) {
        if (this.data.cityArr.length == 0) {
            //加载经营城市
            app.ajax('Home/Index/citys', function (d) {
                var arr = [];
                for (var i in d) {
                    arr.push({
                        id: i,
                        name: d[i]
                    });
                }
                that.setData({
                    cityArr: arr
                });
                that.changeCity();
            });
        } else {
            this.setData({
                cityLayShowed: !this.data.cityLayShowed
            });
        }
    },
    isBegin: function (chDate){
        console.log('55555', chDate);
        var startTime = chDate.replace(/[年月日]/g, '-').slice(0,-1);
        console.log(startTime);
        return new Date().getTime() - new Date(startTime).getTime() > 0;
    },
    changeCityOpt: function (e) {
        app.ajax('Home/Index/ChangeCity', { city_id: e.currentTarget.dataset.id }, function (d) {
            if (d.status == 1) {
                storage.set('city_id', d.info.id);
                storage.set('city_name', d.info.name);
                app.alert('切换成功', function () {
                    app.city.id = d.info.id;
                    app.city.name = d.info.name;
                    that.setData({
                        city: {
                            name: app.city.name,
                            id: app.city.id
                        }
                    });
                    that.setData({
                        lists: [],
                        tipsData: [],
                        banners: []
                    });
                    that.onReady();
                });
            } else {
                $.alert(d.info, 'error');
            }
        });
    },
    setCollect: function (e) {
        var that = this;
        var tips_id = e.currentTarget.dataset.id;
        var iscollect = e.currentTarget.dataset.iscollect;
        app.setcollect(tips_id, iscollect, function (collect) {
            var data = that.data.tipsData;
            for (var i in data) {
                if (data[i].id == tips_id) data[i].is_collect = collect;
            }
            that.setData({ tipsData: data });
        });
    },
    loadMore: function (e) {
        that.setData({
            page: that.data.page + 1
        })
        that.loadtips(that.data.page);
    },
    openWebview: function (e) {
        console.log(e.target)
        app.openWebView('http://yummy194.cn/h5/yami-release.html')
    },
    switchItem(e) {
        var index = e.target.dataset.index;
        this.setData({
            cur_index: index
        });
    },
    gotoWebview: function (e) {
      let url = e.currentTarget.dataset.url;
      console.log(url);
      app.openWebView(url);
      // var url = this.data.lottery
      //   ? 'https://yummy194.cn/?page=lotteryRule&noHeader=1' // this.data.lottery.url
      //   : '';

      // if (!url) {
      //   return;
      // }
      // url = 'http://yummy194.cn/h5/yami-crowdfunding.html?_ijt=du25g8hj5jq5bbk6977t6s331v';
      // app.openWebView(url);
    }
    
});

/*
    "pages/message/index",
    "pages/message/detail",
    "pages/message/MsgIM",

    "pages/tips/index",
    "pages/tips/detail",
    "pages/tips/confirmEnrolling",
    "pages/tips/activitied",
    "pages/theme/index",
    "pages/theme/detail",


    "pages/tips_order/index",
    "pages/tips_order/detail",

    "pages/search/index"

    "pages/ucenter/setup",
    "pages/ucenter/zone",
    "pages/ucenter/myCollect",
    "pages/ucenter/myCoupon",
    "pages/ucenter/feedBack",
    "pages/ucenter/about",
    "pages/ucenter/changeUserinfo",
    "pages/ucenter/resume",
    "pages/ucenter/follow",
    

    "pages/comment/index",
    "pages/comment/evaluation",

    
    "pages/tips_index/index",
     */
