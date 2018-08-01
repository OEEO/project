var tool = require('../../utils/tool.js')
var app = getApp();
var that;
Page({
    data:{
        tips_id : null,
        time_id : null,
        buy_num : 1,
        limit : 0,
        limit_num : 0,
        lc:1,
        coupon_id : null,
        buy_price : null,
        last_price : null,
        type : 2,
        value : 0,
        min_num:0,
        two_min_num:0,
        book_discount:100,
        buy_status:0,
        detailData:[],
        selectdata:[],
        hidden:true,
        no_book:'',
        liuyan:'',
        is_book:'',
    },
    onLoad:function(option){
         console.log("详情加载完成");
         this.setData({
            tips_id:option.tips_id,
            time_id:option.time_id,
        });
    },
    onShow:function(){
        //判断是否登录
        if(app.member == null){
            wx.redirectTo({
              url: '../ucenter/login'
            });
            return;
        }
    },
    onReady: function () {
        console.log('详情渲染完成！');
        wx.setNavigationBarTitle({
            title: '填写订单'
        });
        that = this;
        that.loaddetail();
    },
    loaddetail:function(){
        app.ajax('Order/Index/getTips',{'tips_id':that.data.tips_id},function(d){
            if(d.info){
                app.alert(d.info, 'error', function(){
                    app.reload('detail');
                });
                return;
		    }
                //包场折扣
                if(d.book_discount > 0 && d.book_discount < 100){
                    that.setData({
                        book_discount :parseFloat(d.book_discount)
                    });
                }
                for(var i in d.times){
                    d.times[i].start_time = tool.timeFormat('Y-m-d (W) H:i',d.times[i].start_time).split(' ');
                    d.times[i].end_time = tool.timeFormat('H:i',d.times[i].end_time);
                    if(d.times[i].stock <= 0){
                        d.times[i].signtext = '名额已满';
                        d.times[i].none = ' none';
                    }else if(d.times[i].stop_buy_time < (new Date()).getTime()/1000){
                        d.times[i].signtext = '已截止报名';
                        d.times[i].none = ' none';
                    }else{
                        d.times[i].signtext = '已报名'+d.times[i].count+'人';
                        if(that.data.time_id == d.times[i].id){
                            if(d.times[i].limit_num != 0){
                                that.setData({
                                    limit_num : d.times[i].limit_num
                                });
                            }
                            if(d.times[i].count==0){
                                if(d.buy_status==2){
                                    that.setData({
                                        lc : d.times[i].min_num,
                                        buy_num : d.times[i].min_num,
                                        min_num : d.times[i].min_num
                                    });
                                }
                                that.setData({
                                    two_min_num : d.times[i].min_num,
                                });
                            }else{
                                that.setData({
                                    no_book : 'no'
                                });
                            }
                            if(parseInt(d.times[i].limit_num) !=0 && parseInt(d.times[i].limit_num) < parseInt(d.times[i].stock)){
                                that.setData({
                                    limit : d.times[i].limit_num,
                                });
                            }else{
                                that.setData({
                                    limit : d.times[i].stock,
                                });
                            }
                            that.setData({
                                time_id : d.times[i].id,
                                selectdata : d.times[i]
                            });
                        }
                        d.times[i].none = '';
                    }
                }

            that.setData({
                buy_price : d.buy_price,
                last_price : d.buy_price,
                detailData : d
            });
            // that.changePrice();
            
        },false)
    },
    moretime:function(){
        that.setData({
            hidden : !that.data.hidden
        })
    },
    selectTime:function(e){
        var nu = e.currentTarget.dataset.count;
        var idx = e.currentTarget.dataset.index;
        var max_num = e.currentTarget.dataset.max;
        var limit_num = e.currentTarget.dataset.limitnum;
        var min_num = e.currentTarget.dataset.min;
        if(nu == 0){
            if(limit_num >0 && limit_num <max_num){
                that.setData({
                    limit:limit_num,
                }); 
            }else{
                that.setData({
                    limit:max_num,
                }); 
            }
            that.setData({
                two_min_num:min_num,
            }); 

			if(that.data.buy_status==2){
                that.setData({
                    lc:that.data.two_min_num,
                    buy_num : that.data.two_min_num
                }); 
			}else{
                that.setData({
                    lc:1,
                    buy_num : 1
                }); 
			}
			that.changePrice();
        }else{
            that.setData({
                min_num:0,
            }); 
			if(limit_num >0 && limit_num <max_num){
                that.setData({
                    limit:limit_num,
                }); 
            }else{
                that.setData({
                    limit:max_num,
                }); 
            }
            that.setData({
                lc:1,
                buy_num : 1
            });  
			that.changePrice();
        }
        var start_ime = e.currentTarget.dataset.starttime;
        var end_time = e.currentTarget.dataset.endtime;
		var times_id = e.currentTarget.dataset.timeid;
        that.setData({
            selectdata:that.data.detailData.times[idx],
        });   

    },
    bindTextAreaBlur:function(e){
        that.setData({
            liuyan : e.detail.value,
        })
    },
    bindKey:function(e){
        var n = e.detail.value;
    },
    changePrice:function(){
        var price = parseFloat(that.data.buy_price);
		var num = that.data.buy_num;
        //计算包场折扣
		if(that.data.is_book == 1){
			var discount = that.data.book_discount / 100;
            price = price * num * discount;
		}else{
            price = price * num;
		}
        that.setData({
            last_price : Math.round(price * 100) / 100,
        })
    },
    changeCopies:function(e){
        var currentVal = that.data.lc;
        var num = parseInt(e.currentTarget.dataset.num);
        if(currentVal <= 1 && num == '-1')num=0;
		if(currentVal >= that.data.limit && num == '1')num=0;
        if(that.data.min_num != 0 && currentVal == that.data.min_num && num == '-1')num = 0;
		currentVal += num;
        that.setData({
            buy_num : currentVal,
            lc : currentVal
        })
        that.changePrice();
    },
    submitdata:function(){
        var ds = {};
        ds.tips_id = that.data.tips_id;
        ds.times_id = that.data.time_id;
        // ds.is_book = $('.page_confirmEnrolling .cao_dan').attr('is_book')||0;
        ds.num = that.data.buy_num;
        if(that.data.coupon_id != null)ds.coupon_id = that.data.coupon_id;
        ds.context = that.data.liuyan;
        //提交订单
        app.ajax('Order/Index/create', ds, function(d){
    //        console.log(d);
    //        console.log('333');
            if(d.status == 1){
                app.wxpay(d.info.order_id);
            }else{
                console.log('123');
                if(d.info == 'open_id_is_null'){
                    wx.login({
                        success : function(res){
                            if(res.errMsg != "login:ok"){
                                app.alert(res.errMsg, 'error');
                                return;
                            }
                            var code = res.code;
                            wx.getUserInfo({
                                success: function(res) {
                                    app.ajax('Home/Wx/getOauthLogin', {get:{code:code, isapp:1}, post:{encryptedData:res.encryptedData, iv:res.iv}}, function(d){
                                        if(d.status == 1){
                                            app.member = d.info.info;
                                            app.saveSkey(d.info.info.id, d.info.skey);
                                            app.alert('授权成功');
                                        } else {
                                            app.alert(d.info);
                                        }
                                    });
                                }
                            });
                        }
                    });
                }else{
                    console.log(d.info);
                    app.alert(d.info);
                }
            }
        }, 2);
    }
})