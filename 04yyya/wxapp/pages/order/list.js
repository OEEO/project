var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data: {
        number: 1,//二维码序号
        act_status: 'all',
        check_datas: [],
        check_codes: [],
        codes: [],
        page: 1,
        lists: [],
        orderlist: [],
        hide: true,
        getcode: [],
        autoplay: false,
        current: 0,
        nav: [
            { name: '全部订单', key: 'all' },
            { name: '支持项目', key: '1' },
            { name: '退款订单', key: '5' }
        ],
        finish: false
    },
    onLoad: function () {

        this.loadorder();
    },
    onShow: function () {
        //判断是否登录
        // if(app.member == null){
        //     app.jump('ucenter/login');
        //     return;
        // }
        this.setData({
            page: 1,
            lists: []
        });
    },
    onReady: function () {
        that = this;
        wx.setNavigationBarTitle({
            title: '我的订单'
        });
    },
    changeStatus: function (e) {
        if (this.data.act_status === e.currentTarget.dataset.status) {
            return false;
        } else {
            this.setData({
                finish: false,
                act_status: e.currentTarget.dataset.status
            })
        }
        this.setData({
            page: 1,
            lists: []
        });
        this.loadorder();
    },
    wxpay: function (e) {
        var order_id = e.currentTarget.dataset.id;
        app.wxpay(order_id);
    },
    loadorder: function (p) {
        var ps = p || 1;
        var date = {
            get: { page: ps }
        };
        date.post = {
            type: 2
        };
        that = this;
        date.post.act_status = this.data.act_status;
        app.ajax('Member/Order/index', date, function (d) {
           console.log('列表',d);
            if (!d.info) {
                if (d.length === 0) {
                    that.setData({
                        finish: true
                    });
                }

                var act_status = {
                    '0': '待付款',
                    '1': '已付款',
                    '5': '退款中'
                };

                for (var i = 0, num = d.length; i < num; i++) {
                    var item = d[i];
                    item.create_time = tool.timeFormat('Y-m-d H:i', item.create_time);
                    item.act_status_title = act_status[item.act_status];
                    item.can_pay = item.limit_time > Date.now() / 1000;
                    item.end_time = tool.timeFormat('Y-m-d H:i', item.end_time);
                    if (item.act_status == '0' || item.act_status == '1' || item.act_status == '5') {
                        that.data.lists.push(item);
                    }

                }
                that.setData({
                    orderlist: that.data.lists
                });
            }
        }, false);
    },
    loadMore: function (e) {
        if (this.data.finish) {
            return;
        }
        that.setData({
            page: that.data.page + 1
        })
        that.loadorder(that.data.page);
    },
    cancelOrder: function (e) {
        //取消订单
        app.alert('是否确定要取消该订单?', function () {
            app.ajax('Member/Order/cancel', { order_id: e.currentTarget.dataset.id }, function (d) {
                if (d.status == 1) {
                    app.alert('取消成功', function () {
                        that.loadorder();
                    });
                }
            }, false);
        }, 'error', true)
    },
    cancelRefund: function (e) {
        //取消退款
        app.ajax('Member/Order/cancelRefund', { order_id: e.currentTarget.dataset.id }, function (d) {
            if (d.status == 1) {
                app.alert('取消成功', function () {
                    that.loadorder();
                });
            } else {
                app.alert(d.info, 'error');
            }
        }, false);
    },
})
