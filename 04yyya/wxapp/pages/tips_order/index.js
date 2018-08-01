var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data:{
        number:1,//二维码序号
        act_status:'',
        currentTab:0,
        check_datas : [],
        check_codes : [],
        codes : [],
        page : 1,
        lists : [],
        orderlist : [],
        hide:true,
        getcode:[],
        autoplay: false,
        current:0,
    },
    onLoad:function(){
        that=this;
    },
    onShow:function(){
        //判断是否登录
        if(app.member == null){
            wx.redirectTo({
              url: '../ucenter/login'
            });
            return;
        }
        that.loadorder();
    },
    onReady: function () {
        wx.setNavigationBarTitle({
            title: '我的订单'
        });
    },
    status : function(e){
        if( that.data.currentTab === e.currentTarget.dataset.current ) {  
            return false;  
        } else {  
            that.setData( {  
                currentTab: e.currentTarget.dataset.current  
            })  
        }  
        that.data.act_status = e.currentTarget.dataset.statu;
        that.setData({
            page : 1,
            lists : []
        });
        that.loadorder();
    },
    wxpay : function(e){
        var order_id = e.currentTarget.dataset.id;
        app.wxpay(order_id);
    },
    loadorder : function(p){
        var ps = p || 1;
        var date = {
			get:{page:ps}
		};
       date.post={};
       if(that.data.act_status != null && that.data.act_status != ''){
			date.post.act_status = that.data.act_status;
		}
        app.ajax('Member/Order/index', date, function(d){
            if(!d.info){
				var types = ['活动', '商品'];
				var status = ['待付款', ['待参加', '未发货'], ['已参加', '已发货'], '未确认', '已完成', '退款中', '已退款', '已取消', '退款中'];
                if(ps == 1){
                    that.data.lists = [];
                    that.data.codes = [];
                }
                for(var i in d){
                    d[i].statu = (typeof(status[d[i].act_status])=='string'?status[d[i].act_status]:status[d[i].act_status][d[i].type]);
                    if(d[i].start_time){
                        d[i].start_time = tool.timeFormat('Y-m-d（W） H:i',d[i].start_time);
                        d[i].end_time = tool.timeFormat('H:i',d[i].end_time);
                    }
                    if(d[i].act_status == 1){
                        var check_code = [];
                        // var check_data = [];
                        var check_data = new Object(); 
                        for(var n in d[i].check_code){
							if(check_code.indexOf(d[i].check_code[n]) == -1){
                                 check_code.push(d[i].check_code[n]);
                            }
						}
                        check_data = check_code;
                        that.data.codes[d[i].id] = check_data;
                    }
                    that.data.lists.push(d[i]);
                }
                that.setData({
                    check_datas : that.data.codes,
                    orderlist : that.data.lists
                });
            }    
        },false); 
    },
    loadMore:function(e) {
        console.log(e);
        that.setData( {
            page: that.data.page + 1
        })
        that.loadorder(that.data.page);
    },
    
    showCode:function(e){
        var size = app.setCanvasSize();
        var order_id = e.currentTarget.dataset.id;
        var checkcode = [];
        for(var i in that.data.check_datas[order_id]){
			if(that.data.check_datas[order_id][i].status == 0){
                checkcode.push(that.data.check_datas[order_id][i].code);
                app.createQrCode(that.data.check_datas[order_id][i].code,"mycanvas"+i,size.w,size.h);
            }
				
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
    },
    cancelOrder: function(e){
        //取消订单
        app.alert('是否确定要取消该订单?',function(){
            app.ajax('Member/Order/cancel', {order_id : e.currentTarget.dataset.id}, function(d){
                if(d.status == 1){
                    app.alert('取消成功', function(){
                        that.loadorder();
                    });
                }
            },false);
        },'error', true)
    },
    cancelRefund:function(e){
        //取消退款
        app.ajax('Member/Order/cancelRefund', {order_id : e.currentTarget.dataset.id}, function(d){
            if(d.status == 1){
				app.alert('取消成功', function(){
					that.loadorder();
				});
			}else{
				app.alert(d.info, 'error');
			}
		},false);
    },
})
