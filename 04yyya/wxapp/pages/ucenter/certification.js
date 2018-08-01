// pages/ucenter/certification.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    surname: '',
    identity: '',
    hasBeingCertificate: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.setNavigationBarTitle({
      title: '实名认证',
    });
    this.setData({
      surname: options.surname,
      identity: options.identity
    });
    if(this.data.surname){
      this.setData({
        hasBeingCertificate: true
      })
    }
  },
  //绑定input的值到数据
  changeInput(e){
    let id = e.currentTarget.dataset.id;
    if (id === 'surname') {
      this.setData({
        surname: e.detail.value
      });
    }
    if (id === 'identity') {
      this.setData({
        identity: e.detail.value
      });
    }
  },
  // 提交实名认证
  toCertificate(){
    let reStr = /(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}$)/;
    let surname = this.data.surname;
    let identity = this.data.identity;
    if (!surname) {
      wx.showToast({
        title: '名字不能为空',
        icon: 'none',
        duration: 1000
      });
      return;
    }
    if (!identity) {
      wx.showToast({
        title: '身份证号不能为空',
        icon: 'none',
        duration: 1000
      });
      return;
    }
    if (!reStr.test(identity)) {
      wx.showToast({
        title: '身份证号格式不对',
        icon: 'none',
        duration: 1000
      });
      return;
    }
    wx.showToast({
      title: '实名认证成功',
      icon: 'success',
      duration: 1000
    });
    wx.setStorage({
      key: 'certification',
      data: {
          surname: surname,
          identity: identity
      },
      success: function() {
          wx.navigateBack({
              url: '../raise/pay'
          })
      }
  });
    // wx.request({
    //   url: 'https://URL',
    //   data: {},
    //   method: 'GET', // OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
    //   // header: {}, // 设置请求的 header
    //   success: function(res){
    //     // success
    //   },
    //   fail: function() {
    //     // fail
    //   }
    // })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})