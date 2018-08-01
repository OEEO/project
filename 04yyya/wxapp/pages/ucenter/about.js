var that;
Page({
    data:{},
    onShow:function(){
        console.log('加载完成');
    },
    onReady:function(){
        console.log('渲染完成');
        wx.setNavigationBarTitle({
            title: '关于吖咪'
        });
    },

})