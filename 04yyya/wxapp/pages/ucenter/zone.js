var app = getApp()
var that;
Page({
    data:{
        shareshow:true,
        member_id : '',
        dec:'',
        tipstitle:'',
        is_follow:null,
        follow_num:0,
        darenData : []
    },
    onLoad:function(option){
        console.log("首页加载完成"+option.member_id);
        this.setData({
            member_id:option.member_id,
        });
    },
    onShow:function(){},
    onReady: function () {
        console.log('首页渲染完成！');
        wx.setNavigationBarTitle({
            title: '个人空间'
        });
        that = this;
        this.loaddaren();
    },
    loaddaren : function(){
        app.ajax('Home/Daren/darenZone', {member_id:that.data.member_id}, function(d){
            console.log(d);
            if(!d.info){
                that.setData({
                    is_follow:d.base_info.isfollow,
                    follow_num:parseInt(d.base_info.fans)*3,
                    tipstitle:d.base_info.daren_info.nickname,
                    dec:d.base_info.daren_info.introduce,
                    darenData : d
                });
            }
        },false);
        
    },
    follow:function(e){
        //关注达人
        var that = this;
        var daren_id=this.data.member_id;
        app.follow(this.data.is_follow,daren_id,this.data.follow_num,function(f,n){
            that.setData({
                is_follow:f,
                follow_num:n
            })
        }
        )
    },
    // share:function(){
    //     //分享提示框显示隐藏
    //     that.setData({
    //         shareshow : !that.data.shareshow
    //     })
    // },
    // onShareAppMessage: function () {
    //     return {
    //         title: that.data.tipstitle,
    //         desc: that.data.dec,
    //         path: '/pages/ucenter/zone?member_id='+that.data.member_id
    //     }
    // }
})
