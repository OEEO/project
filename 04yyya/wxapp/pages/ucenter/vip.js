var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data: {
        hasRaisePriority: false,
        prioritys:''
    },
    onShow: function () {
        //判断是否登录
        if (app.member == null) {
            wx.redirectTo({
                url: 'login'
            });
            return;
        }
        that = this;

        this.setData({
            path: app.member.path,
            member_id: app.member.id
        });

        app.ajax('Member/Index/privilege', function (d) {
            var arr = [];//保存不重复的 type_id,去重
            d = d.filter(val => {
                var typeId = val.type_id;
                if (arr.indexOf(typeId) === -1) {
                    val.isPriorityEnd = new Date().getTime() / 1000 - val.end_time > 0;//权益是否已到期
                    val.end_time = tool.timeFormat('Y年m月d日 H:i', val.end_time);
                    //判断认筹是否开始

                    arr.push(typeId);
                    return true;
                };
            });
            console.log(d);
            that.setData({
                prioritys: d,
                hasRaisePriority: true
            });
        }, false);
    },
    toRaiseDetail: function (e) {
        var id = e.currentTarget.dataset.raiseid;
        console.log(id); 
    },
    onReady: function () {
        wx.setNavigationBarTitle({
            title: '我的权益'
        });
    }
});