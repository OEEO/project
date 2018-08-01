var app = getApp();
var tool = require('../../utils/tool.js')
var that = null;

var pay_status = {
    '0': '待付款',
    '1': '已付款',
    '2': '已支付',
    '3': '已支付',
    '4': '已支付',
    '5': '退款中',
    '6': '已退款',
    '7': '已取消',
    '8': ''
};

var steps = function (step, index) {
    if (index === 0) {
        if (step === '1') {
            return '（进行中）';
        } else {
            return '（已完成）';
        }
    } else if (index === 1) {
        if (step === '1' || step === '2') {
            return '（未开始）';
        } else if (step === '3' || step === '5') {
            return '（进行中）';
        } else if (step === '4' || step === '6') {
            return '（已完成）';
        } else if (step === '7') {
            return '（已失效）';
        }
    }
}

Page({
    data: {
        "order_id": '',
        "sn": "",
        "create_time": "",
        "total": "",
        "context": "",
        "act_status": "",
        "count": "",
        "comment_id": "",
        "pay_type": "",
        "piece_originator_id": "",
        "invite_nickname": "",
        "type": "",
        "ware_type": "",
        "id": "69",
        "title": "",
        "status": "",
        "category_id": "",
        "start_time": "",
        "end_time": "",
        "limit_time": "",
        "datetime": "",
        "content": "",
        "raise_times_content": "",
        "introduction": "",
        "path": "",
        "catname": "",
        "times_id": "",
        "raise_times_title": "",
        "price": "",
        "prepay": "", // 预付款
        "stock": "",
        "quota": "",
        "nickname": "",
        "weixincode": "",
        "pay_price": "", //该众筹订单该付的金额
        "order_wares_count": "",
        "retainage": "", // 尾款
        "step": "",
        "order_pid": "",
        "address": "",
        "totaled": "",
        "lottery": null,
        "less_day": '',
        telephone: '',
        create_time_title: '',
        progressTitle: '',
        progress: '',
        status_title: '',
        step_title: '',
        agreement: false,
        "is_scroll" : true,
    },
    showAgreement: function () {
        this.setData({
            agreement: true,
            is_scroll: false,

         });
    },
    hideAgreement: function () {
        this.setData({
            agreement: false,
            is_scroll: true,
        });
    },
    onReady: function () {
        wx.setNavigationBarTitle({
            title: '订单详情'
        });
        that = this;
    },

    onLoad: function (option) {
        this.setData({
            order_id: option.order_id || 42125
        })
        this.getDetail(this.data.order_id)
    },

    getDetail: function (id) {
        that = this;
        app.ajax('Member/Order/getDetail', { order_id: id }, function (d) {
            if (!d.info) {
                that.setData({
                   // "order_id": d.order_id,
                    "sn": d.sn,
                    "create_time": d.create_time,
                    "total": d.total,
                    "context": d.context,
                    "act_status": d.act_status,
                    "count": d.count,
                    "comment_id": d.comment_id,
                    "pay_type": d.pay_type,
                    "piece_originator_id": d.piece_originator_id,
                    "invite_nickname": d.invite_nickname,
                    "type": d.type,
                    "ware_type": d.ware_type,
                    "id": d.id,
                    "title": d.title,
                    "status": d.status,
                    "category_id": d.category_id,
                    "start_time": d.start_time,
                    "end_time": d.end_time,
                    "limit_time": d.limit_time,
                    "datetime": d.datetime,
                    "content": d.content,
                    "raise_times_content": d.raise_times_content,
                    // "introduction": "",
                    "path": d.path,
                    "catname": d.catname,
                    "times_id": d.times_id,
                    "raise_times_title": d.raise_times_title,
                    "price": d.price,
                    "prepay": d.prepay,
                    "stock": d.stock,
                    "quota": d.quota,
                    "nickname": d.nickname,
                    "weixincode": d.weixincode,
                    "pay_price": d.pay_price,
                    "order_wares_count": d.order_wares_count,
                    "retainage": d.retainage,
                    "step": d.step,
                    "order_pid": d.order_pid,
                    "address": d.address,
                    "totaled": d.totaled,
                    "lottery": d.lottery,
                //    telephone: d.telephone,
                    progressTitle: ((+d.totaled / +d.total) * 100).toFixed(0) + '%',
                    progress: Math.min(100, ((+d.totaled / +d.total) * 100)) + '%',
                    less_day: tool.calLessDay(Date.now() / 1000, d.end_time, d.start_time,  null, +d.totaled - +d.total),
                    status_title: pay_status[d.act_status],
                    create_time_title: tool.timeFormat('Y-m-d H:i:s', d.create_time),
                //    step_title: steps(d.step, 1),
                    total_price: (d.price * d.order_wares_count).toFixed(2)
                })
            }
        })
    },

    submit: function (e) {
        app.wxpay(e.target.dataset.orderId)
    },

    gotoLottery: function () {
        var url = this.data.lottery
            ? 'https://yummy194.cn/?page=lotteryRule&noHeader=1' // this.data.lottery.url
            : '';

        if (!url) {
            return;
        }
        url = 'http://yummy194.cn/h5/yami-crowdfunding.html?_ijt=du25g8hj5jq5bbk6977t6s331v';
        app.openWebView(url);
    },
})