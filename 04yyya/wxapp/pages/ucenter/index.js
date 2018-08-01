var app = getApp()
var that;
Page({
    data: {
        path: 'http://m.yami.ren/images/portrait.jpg',
        follow: 0,
        fans: 0,
        member_id: app.member==null?null:app.member.id,
        sexIcon: '/images/male-icon.png',
        addBorderBian: ''
    },
    changeBorderColor() {
      if (this.data.addBorderBian){
        this.setData({
          addBorderBian: 'add_border_bian'
        });
      } else {
        this.setData({
          addBorderBian: ''
        });
      }
    },
    onShow: function(){
        //判断是否登录
        if(app.member == null){
            wx.redirectTo({
              url: 'login'
            });
            return;
        }
        that = this;
        if (app.member.path == '') {
            this.setData({
                path: 'http://m.yami.ren/images/portrait.jpg',
                member_id: app.member.id,
            });
        } else {
            this.setData({
                path: app.member.path,
                member_id: app.member.id,
            });
        }
        //性别图标
        this.setData({
          sexIcon: app.member.sex == '2' ? '/images/female-icon.png' : '/images/male-icon.png' 
        })

        app.ajax('Member/Index/getData', function(d){
            that.setData({fans:d.fans, follow:d.follow, nickname:d.nickname});
        },false);
    },
    onReady : function(){
        wx.setNavigationBarTitle({
            title: '我的吖咪'
        });
        this.setData({
          addBorder: 'add_border'
        });
    }
});