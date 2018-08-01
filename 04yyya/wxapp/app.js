var config = require('utils/config.js');
var storage = require('utils/storage.js');
var sha1 = require('utils/sha1.js');
var QR = require('utils/qrcode.js');
var url,that;

var app = {
    domain : config.domain,
    city : {id:35, name:'北京'},
	location : [0,0],
	version : '2.3.0',
	token : null,
    member: null,
    tipsDetailInterval : null,
    ws : require('utils/websocket.js'),
    initRem: function () {
        (function (doc, win) {
            var docEl = doc.documentElement,
              resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
              recalc = function () {
                var clientWidth = docEl.clientWidth;
                if (!clientWidth) return;
                docEl.style.fontSize = 10 * (clientWidth / 320) + 'px';
              };
      
            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
            doc.addEventListener('DOMContentLoaded', recalc, false);
      })(document, window);
    },
    start: function(){
        // this.initRem();
        this.getToken(function(){
            if(storage.get('autologin')){
                var autologinData = {};
                var autologin = storage.get('autologin').split('|');
                autologinData.id = autologin[0];
                autologinData.skey = autologin[1];

                //Ajax数据调用
                that.ajax('member/index/autologin', autologinData, function(d){
                    if(d.status == 1){
                        var skey = d.info.skey;
                        var info = d.info.info;
                        // if(info.coupon){
                        //     var box = $('<div>').addClass('get_coupon_box').appendTo('body');
                        //     var code = '<div class="box">';
                        //     code += '<div class="price"><small>￥</small>'+ parseFloat(info.coupon.value).priceFormat(2) +'</div>';
                        //     code += '<p class="t">太好了! O(∩_∩)O</p>';
                        //     code += '<p class="b">吖咪酱已经把'+ info.coupon.value +'元代金券放进了您的个人账户中..</p>';
                        //     code += '<a href="javascript:jump(\'myCoupon\');">前往我的优惠券</a>';
                        //     code += '</div>';
                        //     box.html(code).click(function(){
                        //         $(this).remove();
                        //     });
                        // }
                        that.member = info;
                        that.saveSkey(info.id, skey);
                    }else{
                        if(d.info == 'no_telephone'){
                            that.alert('您没有手机号,请重新登录', 'error');
                        }
                        storage.rm('autologin');
                    }
                }, false);
            }
            console.log('获取Token');
        });
        console.log('启动应用！');
    },
    onLaunch: function () {
        //监听小程序初始化
        that = this;
        this.start();
    },
    onShow: function(){
        //监听小程序初显示
        if(that != this)this.start();
    },
    //页面跳转
    jump : function(pagename, get, fn){
        url = '../' + pagename;
        var arr = [];
        if(typeof(get) == 'object'){
            for(var i in get){
                arr.push(i + '=' + get[i]);
            }
            if(arr.length > 0)url += '?' + arr.join('&');
        }
        if(pagename.indexOf('index/index') == -1 && pagename.indexOf('message/index') == -1 && pagename.indexOf('order/index') == -1 && pagename.indexOf('ucenter/index') == -1){
            wx.navigateTo({
                url: url,
                success: function(){
                    if(typeof(fn) == 'function')fn(get);
                }
            });
        }else{
            wx.switchTab({
                url: url,
                success: function(){
                    if(typeof(fn) == 'function')fn(get);
                }
            });
        }
    },
    //页面返回
    back : function(fn){
        wx.navigateBack({
            success : function(){
                if(typeof(fn) == 'function')fn();
            }
        });
    },
    //页面刷新
    reload : function(url){
        wx.redirectTo({
            url: url
        });
    },
    //loading弹出层
	loading : function(){
		wx.showToast({
            title: '加载中',
            icon: 'loading',
            duration: 10000
        });
	},
	//关闭loading层
	close_loading : function(){
		wx.hideToast();
	},
	//存储skey，便于下次自动登录
	saveSkey : function(id, skey){
		storage.set('autologin', id + '|' + skey);
        // this.ws.connect(this.token);
	},
	//获取token
	getToken : function(fn){
		this.loading();
        var time = Math.round((new Date()).getTime() / 1000);
		//和服务器第一次握手
        wx.request({
            url: 'https://api.' + config.domain + '/',
            data: {key: sha1.hex(config.key + time + config.domain), wxapp_version: config.version, time: time},
            method: 'GET',
            success: function(res){
                console.log(config.domain);
                console.log(res);
                var d = res.data;
                if(typeof(d.token) == 'string'){
                    that.token = d.token;
                    //赋值版本号
                    that.version = d.api_version;
                    that.city.id = d.city.id;
                    that.city.name = d.city.name;

                    //判断是否储存了城市选择
                    if(storage.get('city_id') && storage.get('city_name')){
                        //如果选择了城市则执行城市跳转
                        if(that.city.id == storage.get('city_id') && that.city.name == storage.get('city_name')){
                            if(typeof(fn) == 'function')fn();
                        }else{
                            that.ajax('Home/Index/changeCity', {city_id: storage.get('city_id')}, function(d){
                                if(d.status == 1){
                                    that.city.id = storage.get('city_id');
                                    that.city.name = storage.get('city_name');
                                }
                                if(typeof(fn) == 'function')fn();
                            }, false);
                        }
                    }else{
                        wx.getLocation({
                            success : function(res){
                                if(res){
                                    that.location = [res.latitude, res.longitude];
                                    that.ajax('Home/Index/getAddress', {latitude:res.latitude, longitude:res.longitude, is_location:1}, function(d){
                                        if(d.status == 1 && d.info.inset == 1){
                                            that.city.id = d.info.city_id;
                                            that.city.name = d.info.city_name;
                                            storage.set('city_id', that.city.id);
                                            storage.set('city_name', that.city.name);
                                        }
                                        if(typeof(fn) == 'function')fn();
                                    }, false);
                                }
                            }
                        });
                    }
                }else{
                    console.warn('非法访问');
                }
            },
            fail: function() {
                wx.showModal({
                    title: "请求失败",
                    content: "服务器连接失败！请检查网络连接是否正常！",
                    showCancel: false,
                    confirmText: "重新连接",
                    success: function(res){
                        that.getToken();
                    }
                });
            },
            complete: function() {
                that.close_loading();
            }
        });
	},
	//ajax封装
	ajax : function(path, data, fn, type){
        if(this.token == null){
            setTimeout(function(){
                that.ajax(path, data, fn, type);
            }, 100);
            return;
        }
        var url = 'https://api.' + config.domain + '/';
        var async = type === false ? false : true;
        if(typeof(data) == 'function'){
            fn = data;
            data = {};
        }
        var https = ['home','index','index'];
        var arr = path.split('/');
        switch(arr.length){
            case 3:
                https[2] = arr[2];
            case 2:
                https[1] = arr[1];
            case 1:
                https[0] = arr[0];
        }
        
        url += https.join('/') + '.html';
        url += "?token=" + this.token + "&v=" + this.version;
        var postdata = {};
        var getdata = [];
        if(data){
            if(data.get){
                if(data.post)postdata = data.post;
                for(i in data.get){
                    getdata.push(i + '=' + encodeURIComponent(data.get[i]));
                }
                url += '&' + getdata.join('&');
            }else{
                postdata = data;
            }
        }
        var arr = [];
        for(var i in postdata){
            if(postdata[i] instanceof Array){
                for(var j in postdata[i]){
                    arr.push(i+'[]='+encodeURIComponent(postdata[i][j]));
                }
            }else if(typeof(postdata[i]) == 'number' || typeof(postdata[i]) == 'string'){
                arr.push(i+'='+encodeURIComponent(postdata[i]));
            }
        }
        postdata = arr.join('&');
        if(!async || type == 2)this.loading();
        wx.request({
            url: url,
            data: postdata,
            method: 'POST',
 //           method: 'GET',
            header: {
                'content-type' : "application/x-www-form-urlencoded; charset=utf-8"
            },
            success: function(res){
                console.log(res);
                console.log(url);
                console.log(postdata);
                if(typeof(fn) == 'function')fn(res.data);
            },
            complete: function() {
                that.close_loading();
            }
        });
	},
    alert : function(msg, fn, style, sec){
        if(typeof(fn) == 'string'){
            if(typeof(style) == 'boolean')sec = style;
            style = fn;
        }
        style = style||'success';
        if(style=='success'){
            if(!sec)sec = 2;
        }else
            style = 'error'
        if(style == 'error'){
            wx.showModal({
                title: "温馨提示",
                content: msg,
                showCancel: sec ? true : false,
                confirmText: sec ? '确定' : '关闭',
                confirmColor: '#999999',
                cancelColor: '#999999',
                success: function(res){
                    if (res.confirm){
                        if(typeof(fn) == 'function')fn();
                    }
                }
            });
        }else{
            setTimeout(function(){
                wx.showToast({
                    title: msg,
                    icon: 'success',
                    duration: sec * 1000,
                    success : function(){
                        if(typeof(fn) == 'function')setTimeout(fn, sec * 1000 + 500);
                    }
                });
            }, 300);
        }
    },
    setcollect:function(id, iscollect, fn, type){
        type = type || '0'
        //判断是否登录
        if(this.member == null){
            wx.redirectTo({
              url: '../ucenter/login'
            });
            return;
        }else{
            if(iscollect == 1){
                this.alert('您确定要取消关注吗?', function () {
                    app.ajax('Member/Follow/ChangeCollect', {type: type ,type_id:id,operate:0}, function(d){
                    if(d.status == 1){
                        if(typeof(fn) == 'function')fn(0);
                        app.alert('取消成功');
                    }else{
                        app.alert('操作失败', 'error');
                    }
                });
                },'error',true)
            }else{
                this.ajax('Member/Follow/ChangeCollect', {type:0,type_id:id,operate:1}, function(d){
                    if(d.status == 1){
                        if(typeof(fn) == 'function')fn(1);
                    }else{
                        app.alert('操作失败', 'error');
                    }
                });
            }
        }
    },
    follow:function(isfollow,daren_id,num,fn){
        //关注达人
        //判断是否登录
        if(this.member == null){
            wx.redirectTo({
              url: '../ucenter/login'
            });
            return;
        }
        if(isfollow == 1){
            this.alert('您确定要取消关注这位主厨达人吗?', function () {
                app.ajax('Member/Follow/changeFollow', {member_id:daren_id, type:0}, function(d){
				if(d.status == 1){
                    app.alert('取消成功');
                    num = num - 1;
                    if(typeof(fn) == 'function')fn(0,num)
				}else{
					app.alert(' 操作失败', 'error');
				}
			});
            },'error',true)
        }else{
            this.ajax('Member/Follow/changeFollow', {member_id:daren_id, type:1}, function(d){
				if(d.status == 1){
                    num = num + 1;
					if(typeof(fn) == 'function')fn(1,num)
				}else{
					app.alert(' 操作失败', 'error');
				}
			});
        }
    },
    //适配不同屏幕大小的canvas
    setCanvasSize:function(){
        var size={};
        try {
            var res = wx.getSystemInfoSync();
            var scale = 750/490;
            var width = res.windowWidth/scale;
            var height = width;
            size.w = width;
            size.h = height;
        } catch (e) {
            console.log("获取设备信息失败"+e);
        } 
        return size;
    } ,
    createQrCode:function(url,canvasId,cavW,cavH){
        //调用插件中的draw方法，绘制二维码图片
        QR.qrApi.draw(url,canvasId,cavW,cavH);
    },
    setHistory : function(key, data){
		if(storage.get('yummyhistory')){
			var hs = JSON.parse(storage.get('yummyhistory'));
		}else{
			storage.set('yummyhistory', '{}');
			var hs = {};
		}
		if(hs[key]){
			delete hs[key];
		}
		hs[key] = data;
		storage.set('yummyhistory', JSON.stringify(hs));
	},
    //小程序支付
    wxpay : function(order_id){

        app.ajax('Order/Pay/submit', {'order_id':order_id, 'type':4}, function(d){
            if(d.status == 1){
                if(d.info.sign){
                    wx.requestPayment({
                        //timeStamp: d.info.timeStamp.toString(),
                        timeStamp: d.info.timeStamp.toString(),
                        nonceStr: d.info.nonceStr,
                        package: d.info.package,
     //                   package: 'prepay_id=' + d.info.prepayid,
                        signType: d.info.signType,
                        paySign: d.info.sign,
                        success: function(res){
                            console.log(res);
   //                         if(res.errMsg == 'chooseWXPay:ok'){
                            if (res.errMsg == 'requestPayment:ok') {
                                app.alert('支付成功', function(){
                                    wx.redirectTo({
                                        url: '/pages/order/detail?order_id=' + order_id
                                    });
                                });
                            }
                        },
                        fail: function(res) {
                            console.log(res);
                        },
                        complete: function(res) {
                            console.log(res);
                        }
                    });
                }else{
                    app.alert('支付成功', function(){
                        app.jump('order/detail', {order_id:order_id});
                    });
                }
            }else{
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
                                            app.alert('授权成功', function(){
                                                app.wxpay(order_id);
                                            });
                                        } else {
                                            app.alert(d.info);
                                        }
                                    });
                                }
                            });
                        }
                    });
                }else{
                    app.alert(d.info);
                }
            }
        });
    },

    openWebView: function (url) {
        url = url.replace(/^http:/, 'https:');
        url = decodeURIComponent(url);
        if (url.match(/\?/)) {
            url += '&token=' + this.token;
        } else {
            url += '?token=' + this.token;
        }
        console.log('url = ', url)
        wx.navigateTo({
            url: '/pages/common/webview?url=' + url
        });
    }
};

App(app);