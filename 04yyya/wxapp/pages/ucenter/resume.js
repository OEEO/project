var app = getApp()
var that;
Page({
    data:{
        textvalue:'',
        introduce:'',
        lastnum:20,
        num:20,
    },
    onLoad : function(){
        that = this;
    },
    onShow:function(){
        console.log('加载完成');
    },
    onReady:function(){
        console.log('渲染完成');
        that.setData({
            num:that.data.lastnum - app.member.dr_introduce.length,
          introduce:app.member.dr_introduce
        })
        wx.setNavigationBarTitle({
            title: '个人简介'
        });
    },
    sizeNum:function(e){
        var l = parseInt(e.detail.value.length);
        var n = 0;
        if(l >= that.data.lastnum){n = 0;}
        else{n = that.data.lastnum - l;}
        that.setData({
            num:n
        })
    },
    formSubmit:function(e){
        if(e.detail.value.intro == ''){app.alert('请输入你的简介', 'error');}else{
            that.setData({introduce:e.detail.value.intro});
            app.ajax('Member/Index/modifyInfo', {signature:e.detail.value.intro}, function(d){
                if(d.status == 1){
                    app.alert('提交成功',function(){
                        app.member.dr_introduce = d.info.dr_introduce;
                        wx.navigateBack({
                            url:'../ucenter/changeUserinfo'
                        });
                    });
                }
            });
        }
    }
})