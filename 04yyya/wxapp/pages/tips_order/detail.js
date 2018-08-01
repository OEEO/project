var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data:{
        shareshow:true,
        dec:'',
        tips_id:null,
        hide:true,
        member:{},
        order_id:'',
        check_codes : [],
        orderlist : [],
        num : 0,
        number:1,//二维码序号
        num_i:0,//二维码总数
        getcode:[],
        currenturl:null,
        current:0,
    },
    onLoad:function(option){
        console.log("加载完成");
        this.setData({
          order_id:option.order_id,
          member:app.member
        })
    },
    onShow:function(){},
    onReady: function () {
        console.log('渲染完成！');
        wx.setNavigationBarTitle({
            title: '订单详情'
        });
        that=this;
        this.loadorder();
    },
    cancelOrder: function(e){
        //取消订单
        app.alert('是否确定要取消该订单?',function(){
            app.ajax('Member/Order/cancel', {order_id : e.currentTarget.dataset.id}, function(d){
                    if(d.status == 1){
                        app.alert('取消成功', function(){
                            wx.switchTab({
                                url: '../order/index?q='+Math.random()*100
                            })
                        });
                    }
                },false) 
        },'error', true)
       
    },
    cancelRefund:function(e){
        //取消退款
        app.ajax('Member/Order/cancelRefund', {order_id : e.currentTarget.dataset.id}, function(d){
			if(d.status == 1){
				app.alert('取消成功', function(){
					app.reload('../order/detail?order_id'+that.data.order_id);
				});
			}else{
				app.alert(d.info, 'error');
			}
		},false);
    },
    wxpay : function(e){
        var order_id = e.currentTarget.dataset.id;
        app.wxpay(order_id);
    },
    loadorder : function(p){
        var p = p || 1;
        app.ajax('Member/Order/getDetail', {order_id:that.data.order_id}, function(d){
            console.log(d);
            if(!d.info){
				var types = ['活动', '商品'];
				var status = ['待付款', ['待参加', '未发货'], ['已参加', '已发货'], '未确认', '已完成', '退款中', '已退款', '已取消', '退款中'];
                
                    d.statu = (typeof(status[d.act_status])=='string'?status[d.act_status]:status[d.act_status][d.type]);
                    d.start_time = tool.timeFormat('Y-m-d（W） H:i',d.start_time);
                    d.end_time = tool.timeFormat('H:i',d.end_time);
                    d.create_time = tool.timeFormat('Y-m-d H:i:s',d.create_time);
                    if(d.type == 0 && d.act_status > 0 && d.act_status < 7){
                        var check_code = [];
                        for(var n in d.check_code){
							if(check_code.indexOf(d.check_code[n]) == -1){
                                 check_code.push(d.check_code[n]);
                            }
						}
                        var code = '';
                        var codelist = [];
                        for(var i in check_code){
                            if(code.indexOf(d.check_code[i].code) > 0)continue;
                            if(d.check_code[i].status == 0){
                                codelist.push(d.check_code[i].code);
                            }
                        }
                       that.setData({
                            check_codes : codelist,
                        }); 
                        
                    }
                
                that.setData({
                    dec : d.title,
                    tips_id:d.id,
                    orderlist : d
                });
            }
            
        },false);
    },
    // share:function(){
    //     that.setData({
    //         shareshow : !that.data.shareshow
    //     })
    // },
    // onShareAppMessage: function () {
    //     return {
    //         title: that.data.dec,
    //         desc: that.data.dec,
    //         path: '/pages/tips/detail?tips_id='+that.data.tips_id
    //     }
    // },
    geturl:function(e){
        that.setData({
            currenturl : e.currentTarget.dataset.url,
        })
    },
    checkpic:function(e){
        var pics = e.currentTarget.dataset.pics;
        wx.previewImage({
            current: that.data.currenturl, 
            urls: pics,
        })
    },
    showCode:function(){
        console.log(that.data.check_codes);
        var size = app.setCanvasSize();
        var checkcode = [];
        for(var i in that.data.check_codes){
                checkcode.push(that.data.check_codes[i]);
                app.createQrCode(that.data.check_codes[i],"mycanvas"+i,size.w,size.h);
				
		}
        console.log(checkcode);
        if(checkcode.length <= 0){
			app.alert('没有未参加的活动消费码', 'error');
			return;
		}
        that.setData({
            getcode :checkcode,
            hide : false
        });
    },
    hideCode:function(){
        that.setData({
            hide : true
        });
    },
    changenum:function(e){
        that.setData({
            current:e.detail.current
        })
    }
})
