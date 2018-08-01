var app = getApp();
var storage = require('../../utils/storage.js');
var faces = require('../../utils/faces.js');
var tool = require('../../utils/tool.js');
var that,lasttime,_left;
Page({
    adminpath:'http://img.'+ app.domain +'/images/yamijiang.jpg',
    cursor:0,
    data: {
        domain:app.domain,
        goodsData:[],
        msgData:[],
        power:0,
        scrollTop:0,
        faceData:[],
        facePosition:-375,
        hsPosition:-375,
        message:''
    },
    onShow: function(){
        //判断是否登录
        if(app.member == null){
            wx.redirectTo({
              url: '../ucenter/login'
            });
            return;
        }
        that = this;
    },
    onReady : function(){
        wx.setNavigationBarTitle({
            title: '在线客服'
        });
        that = this;
        this.setData({power:app.ws.power});

        if(this.data.power == 0){
            app.alert('客服系统尚未开启!', function(){
                wx.navigateBack();
            });
            return;
        }
        lasttime = lasttime||0;
        if(this.data.msgData.length == 0){
            if(storage.get('historyMessages')){
                var data = JSON.parse(storage.get('historyMessages'));
                if(data.admin){
                    data = data.admin;
                    var pretime = 0;
                    for(var i in data){
                        if(data[i].datetime > pretime + 300)
                            data[i].date = tool.timeFormat('Y-m-d H:i', data[i].datetime);
                        pretime = parseInt(data[i].datetime);
                        this.ws_add(data[i], 0);
                        if(typeof(pretime) == 'number')lasttime = pretime;
                    }
                }
            }
        }
        var data = {
            lasttime : lasttime
        }
        app.ws.send(data, 'create');
        console.log(app.member);
    },
    faceShow : function(){
        if(this.data.faceData.length == 0){
            var data = [];
            for(var i in faces.data){
                if(i % 36 == 0){
                    data[Math.floor(i / 36)] = [];
                }
                data[Math.floor(i / 36)].push({
                    number : 100 + parseInt(i),
                    code : faces.data[i][0],
                    name : faces.data[i][1]
                });
            }
            this.setData({faceData:data});
        }
        if(this.data.facePosition == 100)
            this.setData({facePosition:-375});
        else
            this.setData({facePosition:100});
    },
    faceInput : function(event){
        var name = event.currentTarget.dataset.name;
        var msg = this.data.message.slice(0, this.cursor) + '['+ name +']' + this.data.message.slice(this.cursor);
        this.setData({message:msg});
    },
    inputText : function(e){
        this.data.message = e.detail.value;
        this.cursor = e.detail.cursor;
    },
    goodslist : function(){
        if(this.data.goodsData.length == 0 && storage.get('yummyhistory')){
			var hs = JSON.parse(storage.get('yummyhistory'));
			this.setData({goodslist:hs});
		}
		if(this.data.hsPosition == 100)
            this.setData({hsPosition:-375});
        else
            this.setData({hsPosition:100});
    },
    ws_power : function(d){
        this.setData({power:d});
        if(!d)app.alert('客服系统尚未开启!', function(){wx.navigateBack()});
    },
    ws_add : function(d, isnew){
        var data = {};
        if(d.date)data.date = d.date;
        else data.date = false;

        if(d.from_id == 'admin'){
            data.classname = 'li';
            data.path = this.adminpath;
		}else{
            data.classname = 'li right';
            data.path = app.member.path;
		}

        data.type = d.type;
		if(d.type == 1) {
			data.content = d.content;
		}else if(d.type == 2){
            var dt = d.content;
			if(typeof(dt) != 'object')dt = JSON.parse(dt);
            if(dt.type != 0)return;
            data.tips = {
                id : dt.id,
                path : dt.path,
                title : dt.title
            };
		}else{
            data.content = faces.decode(d.content);
		}
        
		if(isnew == 1){
			var historyMessages = {};
			if(storage.get('historyMessages')){
				historyMessages = JSON.parse(storage.get('historyMessages'));
			}
			if(!historyMessages.admin)historyMessages.admin = [];
			historyMessages.admin.push(d);
			storage.set('historyMessages', JSON.stringify(historyMessages));
		}

        this.data.msgData.push(data);
        this.setData({msgData : this.data.msgData});
        setTimeout(function(){
            that.setData({scrollTop : 999999});
        }, 500);
    },
    sendGoods : function(e){
        var id = e.currentTarget.dataset.id;
        app.ws.sendGoods(id, 0, 'admin');
        this.setData({hsPosition:-375});
    },
    send : function(){
        app.ws.sendText(this.data.message, 'admin');
        this.setData({message:''});
        this.setData({facePosition:-375});
    }
});