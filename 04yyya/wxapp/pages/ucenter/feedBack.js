var app = getApp()
var that;
Page({
    data:{
        hidden:true,
        textvalue:'',
    },
    onShow:function(){
        console.log('加载完成');
    },
    onReady:function(){
        console.log('渲染完成');
        that = this;
        wx.setNavigationBarTitle({
            title: '客服中心'
        });
    },
    calling:function(){
        wx.makePhoneCall({
            phoneNumber: '020-23336323' 
        })
    },
    showCode:function(){
        this.setData({
            hidden: !that.data.hidden
        })
    },
    formSubmit:function(e){
        // that.setData({
        //     textvalue:e.detail.value.feed,
        // })
        app.ajax('Member/Index/feedback', {content:e.detail.value.feed}, function(d){
            if(d.status == 1){
                app.alert(d.info, function(){
                   app.reload('feedBack');
                });
            }else{
                app.alert(d.info, 'error');
            }
        });
        
    },
})