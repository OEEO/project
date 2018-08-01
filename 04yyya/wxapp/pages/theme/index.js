var app = getApp()
var that;
Page({
    data:{
        lists:[],
        themelist : []
    },
    onLoad:function(){
        that = this;
    },
    onShow:function(){
        console.log("加载完成");
    },
    onReady: function () {
        console.log('渲染完成！');
        wx.setNavigationBarTitle({
            title: '精品列表'
        });
        that.loadtheme();
    },
    loadtheme : function(){
        app.ajax('Home/Theme/getlist', {}, function(d){
            console.log(d);
            if(!d.info){
                if(d.list.length > 0){
                    for(var i in d.list){
                        d.list[i].title = d.list[i].title.split('|');
                        if(d.list[i].url == ''){
                            that.data.lists.push(d.list[i]);
                        }
                    }
                that.setData({
                    themelist : that.data.lists
                });
                }
                
            }
            
        },false);
        
    },
})
