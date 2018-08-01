

var app = getApp();
var that = null;

var steps = function (step, index) {
    if (index === 0) {
        if (step == '1') {
            return '（进行中）';
        } else {
            return '（已完成）';
        }
    } else if (index === 1) {
        if (step == '1' || step == '2') {
            return '（未开始）';
        } else if (step == '3' || step == '5') {
            return '（进行中）';
        } else if (step == '4' || step == '6') {
            return '（已完成）';
        } else if (step == '7') {
            return '（已失效）';
        }
    }
}


Page({
    data: {
        surname: '',//名字
        identity: '',//身份证号
        imgCode: '',//图片验证码
        surnameShow: '',//用于页面显示
        identityShow: '',//用于页面显示
        is_address: '',
        address: '',
        is_realname: '',
        pay_price: '',
        prepay: '',
        retainage: '',
        step: '',
        title: '',
        weixincode: '',
        default_address: '',
        default_address_id: '',

        buy_count: 0,
        info: 'limit',
        limit: '',
        limit_buy_times: '',
        member_id: '',

        raise_id: '',
        raise_times_id: '',

        num: 1,
        pay_num: 0,//支付总额
        step_title: '',

        hiddenModal: true, //用于弹出框弹出时阻击底部滚动
        hiddenCertificationModal: true, //实名认证对话框
        hiddenAgreementModal: true,//同意协议对话框
        hiddenPayModal: true,//支付对话框
        imgCodeUrl: '',
        orderReturnId:'',//生成定单的返回值
        orderTimeLast: 600000, //定单有效时长 单位：毫秒
        orderTimer: null,//定单倒计时函数
        timeNumShow: '10 : 00',
        agree: false,//是否同意协议
        agreeIconColor: '#aaa',

    },
    onReady: function () {
        wx.setNavigationBarTitle({
            title: '支付详情'
        });
    },
    toggleAgree: function () {
        this.setData({
            agree: !this.data.agree,
            agreeIconColor: this.data.agreeIconColor === '#aaa' ? '#b39851' : '#aaa'
        });
    },
    certificationModalToggle: function () {
        if (this.data.surnameShow) {
            return;
        }
        this.setData({
            hiddenModal: !this.data.hiddenModal,
            hiddenCertificationModal: !this.data.hiddenCertificationModal,
            imgCodeUrl: 'http://' + app.domain + '/Home/Index/captcha.do?token=' + app.token + '&' + Math.random()
        })
    },
    agreementModalToggle: function () {
        this.setData({
            hiddenModal: !this.data.hiddenModal,
            hiddenAgreementModal: !this.data.hiddenAgreementModal
        });
    },
    payModalClose: function () {
        if (this.data.orderTimer) {
            clearInterval(that.data.orderTimer);
        }
        this.setData({
            hiddenModal: !this.data.hiddenModal,
            hiddenPayModal: !this.data.hiddenPayModal,
        });
        // 还可以支付，则跳转至相应的支付详情页
        if (this.data.timeNumShow) {
            var timesId = this.data.raise_times_id;
            let getOrderUnpaid = new Promise(resolve => {
                app.ajax('Member/Order/findUnpaid', {}, function (d) {
                    let lists = d.order_id;
                    let id = '';
                    for (let i = 0, len = lists.length; i < len; i++) {
                        let item = lists[i];
                        if (item.ware_id == that.data.raise_id && item.tips_times_id == timesId) {
                            id = item.id;
                        }
                    }
                    console.log('重要id：', id);
                    resolve(id);
                }, false);
            });
            getOrderUnpaid.then((id) => {
                //跳转详情页
                if (id) {
                    wx.redirectTo({
                        url: '/pages/order/detail?order_id=' + id
                    });
                }
            });
        }


    },
    //实名认证名字输入
    changeSurnameInput: function (e) {
        this.setData({
            surname: e.detail.value
        });
    },
    //实名认证身份证号输入
    changeIdentityInput: function (e) {
        this.setData({
            identity: e.detail.value
        });
    },
    //实名认证图片验证码输入
    changeImgCodeInput: function (e) {
        this.setData({
            imgCode: e.detail.value
        });
    },
    //更新验证码
    updateImgCode(){
        this.setData({
            imgCodeUrl: 'http://' + app.domain + '/Home/Index/captcha.do?token=' + app.token + '&' + Math.random()
        });
        console.log('更新图片验证码')
    },
    //提交实名认证
    toCertificate(){
        let reStr = /(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}$)/;
        let surname = this.data.surname;
        let identity = this.data.identity;
        let imgCode = this.data.imgCode;
        let data = {};
        if (!surname) {
          app.alert('名字不能为空','error');
          return;
        }
        if (!identity) {
          app.alert('身份证号不能为空','error');
          return;
        }
        if (!imgCode) {
          app.alert('验证码不能为空','error');
          return;
        }
        if (!reStr.test(identity)) {
          app.alert('身份证号格式不对','error');
          return;
        }
        data.surname = surname;
        data.identity = identity;
        data.captcha = imgCode;
        app.ajax('Goods/Raise/raise_real', data, function (d) {
            //认证失败
            if (d.status !== 1) {
                that.setData({
                    imgCodeUrl: 'http://' + app.domain + '/Home/Index/captcha.do?token=' + app.token + '&' + Math.random()
                });
                app.alert(d.info,'error');
                return;
            } else {
                app.alert('实名认证成功', 'success');
                setTimeout(() => {
                //     wx.setStorage({
                //         key: 'certification',
                //         data: {
                //             surname: surname,
                //             identity: identity
                //         },
                //         success: function() {
                //             that.setData({
                //                 hiddenModal: true,
                //                 surnameShow: surname,
                //                 identityShow: identity
                //             });
                //         }
                //   });
                  that.setData({
                    hiddenModal: true,
                    surnameShow: surname,
                    identityShow: identity
                });
                }, 2500);
            }
        });
    },
    //输入微信号时获取值
    changeWeixincode: function(e) {
        this.setData({
            weixincode: e.detail.value
        });
        console.log(this.data.weixincode);
    },
    getRaiseReal: function () {
        app.ajax('Goods/Raise/getRaiseReal', function (d) {
            if (d.status !== 0) {
                that.setData({
                    surname: d.surname,
                    identity: d.identity.replace(),
                    surnameShow: d.surname,
                    identityShow: d.identity.replace(),
                })
            }
        });
    },

    getRaiseOrder: function () {
        app.ajax('Goods/Raise/getRaiseOrder', { raise_id: this.data.raise_id, raise_times_id: this.data.raise_times_id }, function (d) {
            if (d.status !== 0) {
                that.setData({
                    is_address: d.info.data.is_address,
                    address: d.info.data.address,
                    is_realname: d.info.data.is_realname,
                    pay_price: d.info.data.pay_price,
                    prepay: d.info.data.prepay,
                    retainage: d.info.data.retainage,
                    step: d.info.data.step,
                    title: d.info.data.title,
                    pay_num: d.info.data.pay_price,
                    step_title: steps(d.info.data.step, 1),
                });
            }
        })
    },

    getInfo: function () {
        app.ajax('Member/Index/info', function (d) {
            if (d.status !== 0) {
                that.setData({
                    weixincode: d.weixincode,
                    default_address: d.default_address,
                    default_address_id: d.default_address_id
                })
            } else if (d.info === '尚未登录，无法访问！') {
                wx.setStorage({
                    key: 'jumpUrl',
                    data: '../raise/pay?raise_id=' + that.data.raise_id + '&times_id=' + that.data.raise_times_id
                })
                wx.navigateTo({
                    url: '../ucenter/login'
                })
            }
        })
    },

    getLimit: function () {
        app.ajax('Goods/Raise/getLimit', {
            raise_id: this.data.raise_id,
            times_id: this.data.raise_times_id
        }, function (d) {
            if (d.status !== 0) {
                that.setData({
                    buy_count: d.buy_count,
                    info: d.info,
                    limit: +d.limit || -1,
                    limit_buy_times: +d.limit_buy_times || -1,
                    member_id: d.member_id
                })
            }
        })
    },

    onShow: function (e) {
        wx.getStorage({
            key: 'address',
            success: function (res) {
                console.log(res)
                if (res && res.data && res.data.addressId) {
                    // res = JSON.parse(res
                    that.setData({
                        default_address_id: res.data.addressId,
                        default_address: res.data.address
                    })

                    wx.removeStorageSync('address')
                }
            }
        });
        // wx.getStorage({
        //     key: 'certification',
        //     success: function(res){
        //         console.log(res);
        //         if (res && res.data && res.data.surname) {
        //             that.setData({
        //                 surname: res.data.surname,
        //                 identity: res.data.identity,
        //                 surnameShow: that.data.surname,
        //                 identityShow: that.data.identity,
        //             })
        //         }
        //     }
        // });
    },

    onLoad: function (option) {
        console.log(option)
        that = this;
        this.setData({
            raise_id: option.raise_id || 107,
            raise_times_id: option.times_id || 617
        })

        if (option.addressId) {
            this.setData({
                default_address_id: option.addressId,
                default_address: option.address
            })
        }

        // if(option.surnameId){
        //     this.setData({
        //         surname: option.surname,
        //         identity: option.identity
        //     })
        // }

        this.getRaiseReal()
        this.getRaiseOrder()
        this.getInfo()
        this.getLimit()
    },

    changeNum: function (e) {


        var num = e.target.dataset.setNum;

        if (+num > 0 && this.data.limit_buy_times > 0 && this.data.num >= this.data.limit_buy_times) {
            return;
        }

        var originNum = this.data.num + +num;
        originNum = originNum < 0
            ? 0
            : originNum;
        var pay_num = +this.data.pay_price * originNum

        this.setData({
            num: originNum,
            pay_num: pay_num.toFixed(2)
        })
    },
    //判断定单数据是否有效，弹出同意协议
    createOrder: function () {
        if (this.data.num < 1){
            app.alert('物品数量不能小于1','none');
            return;
        }
        if (this.data.default_address_id == '' && this.data.address_id == ''){
            app.alert('请填写地址', 'none');
            return;
        }
        if (!this.data.weixincode){
            app.alert('请填写微信号', 'none');
            return;
        }
        if (this.data.is_realname === '1' && !this.data.surnameShow){
            app.alert('请先实名认证', 'none');
            return;
        }
        this.setData({
            hiddenAgreementModal: false,
            hiddenModal: false,
        });
    },
    //创建定单，跳转到支付页面
    goToPay: function () {
        this.setData({
            hiddenAgreementModal: true,
            hiddenPayModal: false,
            hiddenModal: false
        });

        app.ajax('Order/Index/create', {
            num: this.data.num,
            raise_id: this.data.raise_id,
            times_id: this.data.raise_times_id,
            address_id: this.data.default_address_id || this.data.address_id,
            weixincode: this.data.weixincode,
            oper_read: 1
        }, function (d) {
            if (d.status === 0) {
                app.alert(d.info, 'none');
            } else {
                console.log(d);
                that.setData({
                    orderReturnId: d.info.order_id
                });
                var endTime = new Date().getTime() + that.data.orderTimeLast;
                var prevTime = 0;
                that.data.orderTimer = setInterval(() => {
                    var curTime = new Date().getTime();
                    switch (true) {
                        case curTime - endTime > 0:
                            clearInterval(that.data.orderTimer);
                            that.setData({
                                timeNumShow: ''
                            })
                            break;
                        case curTime - endTime < 0 && curTime - prevTime >= 1000:
                            let m = Math.floor( (endTime - curTime) / 60000 );
                            let s = Math.floor( ((endTime - curTime) % 60000) / 1000 );
                            let showM = m > 9 ? m + '' : '0' + m;
                            let showS = s > 9 ? s + '' : '0' + s;
                            that.setData({
                                timeNumShow: `${showM} 分   ${showS} 秒`
                            })
                            prevTime = curTime;
                        default:
                            break;
                    }
                }, 200);
            }
        })
    },
    pay: function () {
        app.wxpay(this.data.orderReturnId);
    },

    gotoAddress: function () {
        wx.navigateTo({
            url: '../ucenter/address?type=select&id=' + this.data.default_address_id
        });
    }
});