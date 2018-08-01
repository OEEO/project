var tool = require('../../utils/tool.js')
var WxParse = require('../../wxParse/wxParse.js')
var app = getApp()
var that = this;

Page({
    data: {
        video_url: '',
        totaled: 0,//已认筹
        total: 0,//目标金额
        title: '',//详情页导航栏标题
        introduction: '',//众筹简介
        id: '',//判断点击的是哪个众筹跳转到详情页的
        headpath: '',//内容中的第一张图片
        end_time: '',
        content: '',
        nickname: '',
        path: '',//顶部图片
        start_time: 0,
        end_time: 0,
        sum: 0,//认筹人数
        city_name: '',
        time: '',
        progress: 0,
        less_day: '未开始',
        cur_index: 0,
        is_preview: -1,
        selectorItems: [
            { name: '我的项目', key: 0 },
            { name: '项目回报', key: 1 },
            { name: '联系我们', key: 2 },
            { name: '风险提示', key: 3 }
        ],
        status: -1,// 0表示未开始，其它表示已开始，或结束
        status_title: '',
        countDown: ['00', '00', '00', '00'],
        isCollect: '0',
        timeId: -1
    },

    onLoad: function (option) {
        console.log('详情页id', option.raise_id)
        this.setData({
            id: option.raise_id
            //id: 82
        });
        that = this; // 后面ajax回调用到
        this.loadDetail();
    },

    onShow: function () {
        if (this.data.start_time > Date.now() / 1000) {
            that.data.timeId = setInterval(function () {
                that.setData({
                    countDown: tool.countDown(Date.now() / 1000, that.data.start_time)
                });
            }, 1000)
        }
    },
    onReady: function () {
        console.log('详情渲染完成！');
        wx.setNavigationBarTitle({
            title: '众筹详情'
        });
    },

    getRaiseContent: function (data) {
      console.log(data.content1);
      data.content =  data.content.replace(/\<style type="text\/css"\>[^<]*\<\/style\>/mg, '');
      data.content1 = data.content1.replace(/\<style type="text\/css"\>[^<]*\<\/style\>/mg, '');
      data.content2 = data.content2.replace(/\<style type="text\/css"\>[^<]*\<\/style\>/mg, '');
      data.content3 = data.content3.replace(/\<style type="text\/css"\>[^<]*\<\/style\>/mg, '');
      data.content4 = data.content4.replace(/\<style type="text\/css"\>[^<]*\<\/style\>/mg, '');
      data.content5 = data.content5.replace(/\<style type="text\/css"\>[^<]*\<\/style\>/mg, '');

        var content = '';
        content += '<div class="raise-tag">我的自述</div>';
        if(data.title1){
          content += '<p class="raise-content-title">' + data.title1 + '</p>';
        }
        content += '<div>' + data.content1 + '</div>';

        content += '<div class="raise-tag">我的项目</div>';
        if (data.title2) {
          content += '<p class="raise-content-title">' + data.title2 + '</p>';
        }
        content += '<div>' + data.content2 + '</div>';

        content += '<div class="raise-tag">为何众筹</div>';
        if (data.title3) {
          content += '<p class="raise-content-title">' + data.title3 + '</p>';
        }
        content += '<div>' + data.content3 + '</div>';

        content += '<div class="raise-tag">项目进度</div>';
        if (data.title5) {
          content += '<p class="raise-content-title">' + data.title5 + '</p>';
        }
        content += '<div>' + data.content5 + '</div>';
        return content;
    },

    loadDetail: function () {
        app.loading();
        app.ajax('Goods/Raise/getDetail', { raise_id: that.data.id }, function (d) {
            app.close_loading();
            if (d.status != 0) {
            //存在众筹
                if (tool.calLessDay(Date.now() / 1000, d.end_time, d.start_time, null, +d.totaled - +d.total) == '已成功') {
                //众筹已成功
                    that.setData({
                        selectorItems: [
                            { name: '我的项目', key: 0 },
                            { name: '风险提示', key: 3 }
                        ]
                    });
                }
                if (d.is_preview == '1') {
                    that.setData({
                        video_url: d.video_url,
                        totaled: d.totaled,
                        total: d.total,
                        title: d.title,
                        introduction: d.introduction,
                        headpath: d.headpath || 'http://mat1.gtimg.com/sports/sportAppWeb/douyuthirdparty/static/unmen.png',
                        content: d.content,
                        nickname: d.nickname,
                        path: d.path,
                        start_time: d.start_time,
                        end_time: tool.timeFormat('Y年m月d日', d.end_time),
                        sum: d.sum,
                        city_name: d.city_name,
                        time: tool.timeFormat('Y年m月d日', d.start_time),
                        progressTitle: ((+d.totaled / +d.total) * 100).toFixed(0) + '%',
                        progress: Math.min(100, ((+d.totaled / +d.total) * 100)) + '%',
                        less_day: tool.calLessDay(Date.now() / 1000, d.end_time, d.start_time, null, +d.totaled - +d.total),
                        status: d.start_time > Date.now() / 1000
                            ? 0
                            : (d.start_time <= Date.now() / 1000 && d.end_time >= Date.now() / 1000) ? 1 : 2,
                        status_title: '支持TA',
                        isCollect: d.isCollect, // 是否已关注，0 未关注，1 已关注
                        is_preview: d.is_preview,
                    });
                } else {
                    that.setData({
                        video_url: d.video_url,
                        totaled: d.totaled,
                        total: d.total,
                        title: d.title,
                        introduction: d.introduction,
                        headpath: d.headpath || 'http://mat1.gtimg.com/sports/sportAppWeb/douyuthirdparty/static/unmen.png',
                        content: d.content,
                        nickname: d.nickname,
                        path: d.path,
                        start_time: d.start_time,
                        end_time: tool.timeFormat('Y年m月d日', d.end_time),
                        sum: d.sum,
                        city_name: d.city_name,
                        time: tool.timeFormat('Y年m月d日', d.start_time),
                        progressTitle: ((+d.totaled / +d.total) * 100).toFixed(0) + '%',
                        progress: Math.min(100, ((+d.totaled / +d.total) * 100)) + '%',
                        less_day: tool.calLessDay(Date.now() / 1000, d.end_time, d.start_time, null, +d.totaled - +d.total),
                        status: d.start_time > Date.now() / 1000
                            ? 0
                            : (d.start_time <= Date.now() / 1000 && d.end_time >= Date.now() / 1000) ? 1 : 2,
                        status_title: '支持TA',
                        isCollect: d.isCollect, // 是否已关注，0 未关注，1 已关注
                        is_preview: 0
                    });
                }

                if (d.start_time > Date.now() / 1000) {
                    that.setData({
                        countDown: tool.countDown(Date.now() / 1000, d.start_time)
                    });

                    that.data.timeId = setInterval(function () {
                        that.setData({
                            countDown: tool.countDown(Date.now() / 1000, d.start_time)
                        });
                    }, 1000)
                }

                WxParse.wxParse('article', 'html', that.getRaiseContent(d), that, 10);
                WxParse.wxParse('awards', 'html', d.content4, that, 10);
            } else {

            }
        });
    },

    onHide: function () {
        console.log('页面隐藏');
        clearInterval(this.data.timeId);
    },

    onUnload: function() {
        console.log('页面卸载');
        clearInterval(this.data.timeId);
    },

    nofind: function (e) {
        console.log(e);
        that.setData({
            headpath: 'http://mat1.gtimg.com/sports/sportAppWeb/douyuthirdparty/static/unmen.png'
        });//注意这里的赋值方式...
    },

    switchItem(e) {
        var index = e.target.dataset.index;
        this.setData({
            cur_index: index
        });
    },

    showToast: function (title, type) {
        wx.showToast({
            title: title,
            icon: type || 'success',
            duration: 1000
        })
    },

    copyWX: function () {
        wx.setClipboardData({
            data: 'yami194',
            success: function (res) {
                wx.getClipboardData({
                    success: function (res) {
                        that.showToast('复制成功')
                    }
                })
            },

        })
    },

    scroll: function (e) {
        console.log(e.detail.scrollTop)
    },

    gotoReturnPage: function () {
        if (this.data.status === 2) {
            return;
        } else {
            wx.navigateTo({
                url: 'return?raise_id=' + this.data.id + '&end_time=' + this.data.end_time + '&total=' + this.data.total
            });
        }
    },

    changeCollect: function () {
        app.setcollect(this.data.id, this.data.isCollect, function (d) {
            that.setData({
                isCollect: d + ''
            });
        }, '2');
    }
})