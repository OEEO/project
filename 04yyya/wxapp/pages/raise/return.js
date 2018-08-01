var app = getApp();
var that = null;

Page({
    data: {
        times: [],
        id: 0,
        hiddenRiskTipsModal:true,
        end_time:0,
        total:0,
        popupStopScroll:'',//弹出对话框时，阻止底部页面滚动
        orderUnpaid:[],//未付款订单
    },
    onReady: function () {
        wx.setNavigationBarTitle({
            title: '回报详情'
        });
    },
    loadList: function () {
        app.loading();
        app.ajax('Goods/Raise/getDetail', { raise_id: this.data.id }, function (d) {
            app.close_loading();
            if (d.status !== 0) {
                var times = d.times.map(item => {
                    item.cal_count = item.stock == -1
                        ? '无限制'
                        : item.stock == 0 ? '已售罄' : '剩余名额' + item.stock + '人';
                    item.cal_content = item.content.replace(/[\r|\n]/ig, '\r\n');
                    item.cal_status = d.end_time < Date.now() / 1000
                        ? '已结束'
                        : item.is_buy ? '我要支持' : '已支持';

                    if (d.end_time < Date.now() / 1000) {
                        item.status = 3;
                    } else if (d.start_time > (Date.now() / 1000) && (d.isPrivilege == 0 || !d.tips_privilege[d.times[i].times_id + ''])) {
                        item.status = 2;
                    } else if (+item.limit_num > 0 && d.start_time <= Date.now() / 1000 && d.end_time > Date.now() / 1000) {
                        if (item.limit_buy_times == 0) {
                            item.status = -1;
                        } else {
                            item.status = 1;
                        }
                    } else if (d.start_time <= Date.now() / 1000 && d.end_time > Date.now() / 1000) {
                        item.status = 1;
                    } else {
                        item.status = 2;
                    }

                    return item;
                });
                that.setData({
                    times: times
                });
            }
        });
    },
    riskTipsModalToggle: function(){
      this.setData({
        hiddenRiskTipsModal: !this.data.hiddenRiskTipsModal,
      })
    },
    onLoad: function (option) {
        that = this;

        this.setData({
            id: option.raise_id || 105,
            end_time: option.end_time,
            total: option.total
        });

        this.loadList();
    },
    onShow: function () { },
    gotoPayPage: function (e) {
        var status = e.target.dataset.status;
        var timesId = e.target.dataset.timesId;
        var price = e.target.dataset.timesPrice;

        let getOrderUnpaid = new Promise(resolve => {
            app.ajax('Member/Order/findUnpaid', {}, function (d) {
                let lists = d.order_id;
                console.log(lists);
                let id = '';
                for (let i = 0, len = lists.length; i < len; i++) {
                    let item = lists[i];
                    if (item.ware_id == that.data.id && item.tips_times_id == timesId) {
                        id = item.id;
                    }
                }
                resolve(id);
            }, false);
        });
        getOrderUnpaid.then((id) => {
            if (id) {
                wx.navigateTo({
                    url: '/pages/order/detail?order_id=' + id
                })
            } else {

                if (status === -1) {
                    wx.showToast({
                        title: '您已购买超过限额',
                        icon: 'none',
                        duration: 1000
                    })
                } else if (status === 1) {
                    wx.navigateTo({
                        url: 'pay?raise_id=' + that.data.id + '&times_id=' + timesId
                    })

                } else if (status === 2) {
                    wx.showToast({
                        title: '该类目当前时间还未开放，敬请期待',
                        icon: 'none',
                        duration: 1000
                    })
                } else if (status === 3) {
                    wx.showToast({
                        title: '项目已结束',
                        icon: 'none',
                        duration: 1000
                    })
                }
            }
        });
    }
})