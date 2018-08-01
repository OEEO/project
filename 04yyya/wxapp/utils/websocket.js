var config = require('config.js');
var faces = require('faces.js');
var tool = require('tool.js');
var isopen = 0;
var pp = null;

var ws = {
    power : 0,
    callback : {
        //创建聊天场景
        create : function(d, from_id){
            var p = getCurrentPages();
            p = p[p.length - 1];
            if(p && typeof(p.ws_add) == 'function'){
                var pretime = 0;
                for(var i in d){
                    if(d[i].datetime > pretime + 300)
                        d[i].date = tool.timeFormat('Y-m-d H:i', d[i].datetime);
                    pretime = d[i].datetime;
                    p.ws_add(d[i], 1);
                }
            }
        },
        //收到消息
        add : function(d, from_id){
            var p = getCurrentPages();
            p = p[p.length - 1];
            if(p && typeof(p.ws_add) == 'function'){
                p.ws_add(d, 1);
            }
        },
        newMsgCount : function(d){
        },
        power : function(d){
            ws.power = d;
            var p = getCurrentPages();
            p = p[p.length - 1];
            if(p && typeof(p.ws_power) == 'function'){
                p.ws_power(d);
            }
        }
    },

    send : function(data, act){
        var that = this;
        if(!isopen){
            console.warn('Websocket没有连接，无法进行操作！');
        }else{
            if(act){
                var d = {};
                d['data'] = data||'';
                d['act'] = act;
                d = JSON.stringify(d);
                console.log(d);
                wx.sendSocketMessage({data:d});
            }else{
                wx.sendSocketMessage({data:data});
            }
        }
    },

    //发送文字
    sendText : function(content, to_id){
        var msg = content;
        //将表情转换为字符
        msg = faces.encode(msg);
        msg = msg.replace(/<[^>]+?>/g, "");
        msg = msg.replace('&amp;', '&');
        msg = msg.replace('&nbsp;', ' ');
        if(msg.length > 100){
            alert('消息字数不能过百!');
            return;
        }
        this.send({to_id:to_id, msg:msg.replace(/<[^>]+?>/g, ""), type:0}, 'add');
        return true;
    },

    //发送商品
    sendGoods : function(id, type, to_id){
        var msg = type + '-' + id;
        this.send({to_id:to_id, msg:msg, type:2}, 'add');
        return true;
    },

    connect : function(token){
        var that = this;
        wx.connectSocket({
            url : 'wss://ws.'+ config.domain +'/?token=' + token
        });

        wx.onSocketError(function(res){
            console.log('WebSocket连接打开失败，请检查！')
        })

        wx.onSocketOpen(function(res) {
            console.info('Websocket 已连接!');
            isopen = 1;
            //心跳包
            clearInterval(pp);
            pp = setInterval(function(){
                if(!isopen){
                    clearInterval(pp);
                }else{
                    that.send(0);
                }
            }, 10000);
        });

        wx.onSocketMessage(function(d) {
            if(d.data == 0)return;
            var d = JSON.parse(d.data);
 //           console.log(d);
            var act = d.act;
            var from_id = d.from_id||'';
            if(typeof(that.callback[act]) == 'function')that.callback[act](d.data, from_id);
        });

        wx.onSocketClose(function(res) {
            console.info('Websocket 已断开!');
            isopen = 0;
            setTimeout(function(){
                that.connect(token);
            }, 5000);
        });
    }
};

module.exports = ws;