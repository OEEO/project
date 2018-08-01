var app = getApp();
var that = null;

Page({
    data: {
        addressList: [],
        activeAddressId: '0',
        type: 'none'
    },

    getAddressList: function () {
        app.loading();
        app.ajax('Member/Address/getList', (d) => {
            app.close_loading();
            if (!d.info) {
                console.log(d.length)
                this.setData({
                    addressList: d
                });
            }
        });
    },

    onShow: function () {
        this.getAddressList();
    },

    onLoad: function (option) {
        if (option.type && option.type === 'select') {
            this.setData({
                type: 'select'
            });
        }
    },

    onReady: function () {
        that = this;
    },

    selectAddress: function (e) {
        if (this.data.type !== 'select') {
            return;
        }

        var addressId = e.currentTarget.dataset.id;
        var address = '';

        for (var i = 0, num = this.data.addressList.length; i < num; i++) {
            if (this.data.addressList[i].id == addressId) {
                address = this.data.addressList[i].province_name + this.data.addressList[i].city_name + this.data.addressList[i].area_name + this.data.addressList[i].address
                break;
            }
        }

        wx.setStorage({
            key: 'address',
            data: {
                addressId: addressId,
                address: address
            },
            success: function() {
                wx.navigateBack({
                    url: '../raise/pay'
                })
            }
        });
        
    },

    addressEdit: function (e) {
        var id = e.target.dataset.id;

        if (id) {
            for (var i = 0, num = this.data.addressList.length; i < num; i++) {
                var item = this.data.addressList[i];
                if (item.id === id) {
                    wx.setStorage({
                        key: 'edit-address',
                        data: item
                    });
                    break;
                }
            }
        }
        wx.navigateTo({
            url: 'addressEdit?id=' + id
        })
    }
});