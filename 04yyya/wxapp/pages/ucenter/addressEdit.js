var app = getApp();
var that = null;

var provinces = require('../../utils/province');

Page({
    data: {
        region: [],
        customItem: '其他区',
        default: false,
        id: 0,
        address: null,
        telephone: null,
        linkman: null,
        zipcode: null,
        citys_id: 0
    },

    onReady: function () {
        that = this;
    },

    onLoad: function (option) {
        this.setData({
            id: +option.id
        })

        if (!isNaN(+option.id)) {
            wx.getStorage({
                key: 'edit-address',
                success: (res) => {
                    var data = res.data;
                    this.setData({
                        address: data.address,
                        telephone: data.telephone,
                        linkman: data.linkman,
                        region: [data.province_name + data.province_alt, data.city_name + '市', data.area_name],
                        default: data.is_default === '1',
                        zipcode: data.zipcode,
                        citys_id: data.area_id
                    });
                }
            })
        } else {
            this.setData({
                address: null,
                telephone: null,
                linkman: null,
                region: [],
                default: false,
                zipcode:  null,
                citys_id: 0
            });
        }
    },

    getCityList: function (pid, callback) {
        // if (pid) {}
        app.ajax('Home/Index/getCityList', {pid: pid}, function (d) {
            if (!d.info) {
                typeof callback === 'function' && callback(d);
            }
        });
    },

    bindRegionChange: function (e) {
        console.log(e)
        this.setData({
            region: e.detail.value
        });

        var province = e.detail.value[0];
        var city = e.detail.value[1];
        var area = e.detail.value[2];

        for (var i = 0, num = provinces.length; i < num; i++) {
            // 检查是哪个省
            if (province.indexOf(provinces[i].name) !== -1) {
                // 获取相关省对应的市列表
                this.getCityList(provinces[i].id, function (citys) {
                    // 检查是哪个市
                    for (var j = 0, num2 = citys.length; j < num2; j++) {
                        // 获取相关市对应的地区列表
                        if (city.indexOf(citys[j].name) !== -1) {
                            
                            that.getCityList(citys[j].id, function (areas) {
                                // 设置citys_id
                                for (var k = 0, num3 = areas.length; k < num3; k++) {
                                    if (area.indexOf(areas[k].name) !== -1) {
                                        that.setData({
                                            citys_id: areas[k].id
                                        });
                                        console.log('citys_id = ', areas[k].id);
                                        break;
                                    }
                                }
                            });
                        }
                    }
                })
                break;
            }
        }
    },

    changeDefault: function () {
        this.setData({
            default: !this.data.default
        })
    },

    confirmInput: function (e) {
        var type = e.target.dataset.type;

        if (type === 'linkman') {
            this.setData({
                linkman: e.detail.value
            })
        } else if (type === 'telephone') {
            this.setData({
                telephone: e.detail.value
            })
        } else if (type === 'zipcode') {
            this.setData({
                zipcode: e.detail.value
            })
        } else if (type === 'address') {
            this.setData({
                address: e.detail.value
            })
        }
        console.log(type, e.detail.value);
    },

    save: function () {
        if (!this.data.citys_id) {
            app.alert('请选择省市区', 'none');
            return;
        } else if (!this.data.address) {
            app.alert('请填写详细地址', 'none');
            return;
        } else if (!this.data.telephone) {
            app.alert('请填写手机号码', 'none');
            return;
        }

        var data = {
            citys_id: this.data.citys_id,
            address: this.data.address,
            zipcode: this.data.zipcode,
            linkman: this.data.linkman,
            telephone: this.data.telephone,
            is_default: +this.data.default
        };

        if (!isNaN(+this.data.id)) {
            data.address_id = +this.data.id;
        }

        app.ajax('Member/Address/save', data, function (d) {
            if (d.status == 1) {
                app.alert(d.info);
                wx.navigateBack();
            } else {
                app.alert(d.info, 'none');
            }
        });
    },

    delete: function () {
        app.ajax('Member/Address/delete', {
            address_id: +this.data.id
        }, function (d) {
            if (d.status == 1) {
                wx.navigateBack();
            } else {
                app.alert(d.info, 'none')
            }
        })
    }
});