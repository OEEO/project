var tool = require('../../utils/tool.js')
var app = getApp()
var that;
Page({
    data:{
        tips_id:'',
        goods_id:'',
        coupon_id:'',
        min_price:99999,
        inputValue: '',
        code1:[],
        code2:[],
        code3:[],
        couponlist:[]
    },
    onLoad:function(option){
        console.log('加载完成');
        this.setData({
            tips_id : option.tips_id,
            goods_id : option.goods_id,
            coupon_id : option.coupon_id,
            min_price : option.min_price,
        })
    },
    onShow:function(){},
    onReady:function(){
        console.log('渲染完成');
        wx.setNavigationBarTitle({
            title: '我的优惠券'
        });
        that = this;
        that.loadcoupon();
    },
    bindKeyInput: function(e) {
        this.setData({
            inputValue: e.detail.value
        })
     },
    seach:function(){
        var reg = /^\d{12}$/;
        if(!reg.test(that.data.inputValue)){
            console.log(1);
            app.alert('优惠券是一串12位的数字');
            return;
		}else{
            app.ajax('Member/Coupon/getCoupon',{sn:that.data.inputValue},function(d){
                if(d.status == 1){
                    app.alert('兑换成功',function(){
                        app.reload('../ucenter/myCoupon');
                    });
                }
            },2)
        }
    },
    loadcoupon:function(){
        app.ajax('Member/Coupon/getlist',{tips_id:that.data.tips_id,goods_id:that.data.goods_id},function(d){
            if(d && d.length > 0){
                for(var i in d){
				    d[i].value = d[i].value.split('.');
                    d[i].end_time = tool.timeFormat('Y年m月d日',d[i].end_time);
                    if(d[i].end_time < (new Date()).getTime() / 1000) {
                        that.data.code3.push(d[i]);
                        
                    }else {
                        if(d[i].can_use == 1 && d[i].min_amount <= myCouponObject.min_price){
                            that.data.code1.push(d[i]);
                        }else{
                            that.data.code2.push(d[i]);
                        }
				    }
                }
                that.setData({
                    couponlist : d
                });
            }
        },false)
    }
})