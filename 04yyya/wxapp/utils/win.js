var config = require('config.js');
var storage = require('storage.js');
var sha1 = require('sha1.js');

module.exports = {
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
	},
	//获取token
	getToken : function(fn){
		this.loading();
        var that = this;
        var time = (new Date()).getTime();
		//和服务器第一次握手
        wx.request({
            url: 'https://api.' + config.domain + '/',
            data: {key: sha1.hex(config.key + time + config.domain), wxapp_version: config.version, time: time},
            method: 'GET',
            success: function(res){
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
                        if(d.city.id == storage.get('city_id') && d.city.name == storage.get('city_name')){
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
                                        if(d.status == 1){
                                            that.city.id = d.info.city_id;
                                            that.city.name = d.info.city_name;
                                            storage.set('city_id', that.city.id);
                                            storage.set('city_name', that.city.name);
                                        }else{
                                            that.alert(d.info, 'error');
                                        }
                                    }, false);
                                }
                                if(typeof(fn) == 'function')fn();
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
		if(this.token != null){
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
            var that = this;
			wx.request({
                url: url,
                data: postdata,
                method: 'POST',
                header: {
                    'content-type' : "application/x-www-form-urlencoded; charset=utf-8"
                },
                success: function(res){
                    if(typeof(fn) == 'function')fn(res.data);
                },
                complete: function() {
                    that.close_loading();
                }
            });
		}
	},
    alert : function(msg, fn, style, sec){
        style = style||'success';
        if(typeof(fn) == 'string'){
            style = fn;
        }
        if(!sec)sec = 1.5;
        if(style == 'error'){
            wx.showModal({
                title: "温馨提示",
                content: msg,
                showCancel: true,
                success: function(res){
                    if(typeof(fn) == 'function')fn();
                }
            });
        }else{
            wx.showToast({
                title: msg,
                icon: 'success',
                duration: sec * 1000,
                success : function(){
                    if(typeof(fn) == 'function')fn();
                }
            });
        }
    },
    dialog : function(msg, fn, is_lock, classname){
        // is_lock = is_lock||true;
        // classname = classname||'';
        // var box = $('<view>').addClass('resourceBox page_' + page.names[page.num] + ' ' + classname).attr('id', 'dialogBox');
        // box.html('<view class="context">' + msg + '</view>');
        // box.appendTo('body');
        // var h = win.width / 360 * 100;
        // box.css({'opacity':1, 'margin-top':-1 * (box.height()+h)/2});
        // if(is_lock){
        //     var alertBoxLay = $('<view>').addClass('alertBoxLay').appendTo('body');
        // }
        // var btns = $('<view>').addClass('btns').appendTo(box);
        // var agree = $('<button>').addClass('agree').appendTo(btns).text('是');
        // $('<button>').addClass('closeBtn').appendTo(btns).text('否');
        // agree.click(function(){
        //     box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
        //     alertBoxLay.css('opacity', 0);
        //     setTimeout(function(){
        //         box.remove();
        //         alertBoxLay.remove();
        //     }, 500);
        //     if(typeof(fn) == 'function')fn();
        // });
        // $('#dialogBox button.closeBtn, .alertBoxLay').click(function(){
        //     box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
        //     alertBoxLay.css('opacity', 0);
        //     setTimeout(function(){
        //         box.remove();
        //         alertBoxLay.remove();
        //     }, 500);
        // });
    }
};