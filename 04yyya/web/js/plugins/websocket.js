(function(ws){
    ws._ws = null, pp = null, isopen = 0;
    //储存来源
    ws.froms = [
        {
            from_id : 'admin',
            nickname : '吖咪酱',
            path : 'http://img.m.yami.ren/20160603/5546534882a0e4a7854196f518d0a0ecf9727182_320x320.jpg'
        }
    ];
    ws.callback = {
        //创建聊天场景
        create : function(d, from_id){
            pretime = 0;
            for(var i in d){
                if(d[i].datetime > pretime + 300)
                    d[i].date = d[i].datetime.toString().timeFormat('Y-m-d H:i');
                pretime = d[i].datetime;
                MsgIMObject.add(d[i], 1);
            }
        },
        //收到消息
        add : function(d, from_id){
            //不在客服界面
            if(page.names.indexOf('MsgIM') == -1){
                if(d.type == 1) {
                    var content = '[发送图片]';
                }else if(d.type == 2){
                    var content = '[推送商品]';
                }else{
                    var content = faces.decode(d.content.replace(/<[^>]+?>/g, ""));
                }
                $('#message span').html('<b>' + getFrom(from_id) + '</b>: ' + content);
                if (page.names.length != 0 && page.names[page.names.length - 1] === 'raiseDetail') {
                    $('#message').hide();
                } else {
                    $('#message').show().addClass('new').addClass('newmsg');
                }
                setTimeout(function(){
                    $('#message').removeClass('newmsg');
                }, 5000);
            }else{
                MsgIMObject.add(d, 1);
            }
        },
        newMsgCount : function(d){
            if(parseInt(d.count) > 0 && page.names.join('-').indexOf('Msg') == -1 && page.names.indexOf('raisePay') == -1 && page.names.indexOf('receiveVIP') == -1 && page.names.indexOf('breakfastDiary') == -1 && page.names.indexOf('shareDunch') == -1 && page.names.indexOf('userApply') == -1 && page.names.indexOf('diyimage') == -1){


                if (page.names.length != 0 && page.names[page.names.length - 1] === 'raiseDetail') {
                    $('#message').hide();
                } else {
                    $('#message').show().addClass('new');
                }
                if(d.count > 99){
                    $('#message .num_img').html('99');
                }else{
                    $('#message .num_img').html(d.count);
                }
            }else{
                $('#message').hide();
            }
        },
        power : function(d){
            ws.power = d;
            if(d == 0){
                if(page.names[page.num] == 'myMsgs'){
                    $('.page_myMsgs .customer').hide();
                }
                if(page.names[page.num] == 'MsgIM'){
                    $.alert('客服系统维护中..', function(){
                        page.back();
                    });
                }
            }else{
                if(page.names[page.num] == 'myMsgs'){
                    $('.page_myMsgs .customer').css('display', 'flex');
                }
            }
        }
    };

    ws.send = function(data, act){
        if(ws == null){
            console.warn('Websocket没有连接，无法进行操作！');
        }else{
            if(!isopen){
                setTimeout(function(){
                    ws.send(data, act);
                }, 200);
            }else{
                if(act){
                    var d = {};
                    d['data'] = data||'';
                    d['act'] = act;
                    d = JSON.stringify(d);
                    console.log(d);
                    ws._ws.send(d);
                }else{
                    ws._ws.send(data);
                }
            }
        }
    };

    //发送文字
    ws.sendText = function(content, to_id){
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
        ws.send({to_id:to_id, msg:msg.replace(/<[^>]+?>/g, ""), type:0}, 'add');
        return true;
    };

    //发送商品
    ws.sendGoods = function(id, type, to_id){
        var msg = type + '-' + id;
        ws.send({to_id:to_id, msg:msg, type:2}, 'add');
        return true;
    }

    ws.connect = function(){
        ws._ws = new WebSocket('ws://ws.'+ DOMAIN +'/?token=' + win.token);

        ws._ws.addEventListener('open', function(){
            console.info('Websocket 已连接!');
            isopen = 1;
            //心跳包
            clearInterval(pp);
            pp = setInterval(function(){
                if(ws._ws == null){
                    clearInterval(pp);
                }else{
                    ws._ws.send(0);
                }
            }, 10000);
        });

        ws._ws.addEventListener('message', function(d){
            if(d.data == 0)return;
            try {
                var d = JSON.parse(d.data);
                console.log(d);
                var act = d.act;
                var from_id = d.from_id||'';
                if(typeof(ws.callback[act]) == 'function')ws.callback[act](d.data, from_id);
            }catch(ev){
                if(ws.callback['auto'])
                    ws.callback['auto'](d);
                else
                    console.warn(ev);
            }
        });

        ws._ws.addEventListener('close', function(){
            console.info('Websocket 已断开!');
            ws._ws = null;
            setTimeout(function(){
                ws.connect();
            }, 5000);
        });
    };
//    ws.connect();

    function getFrom(from_id, which){
        var which = which||'nickname';
        console.log(from_id);
        for(var i in ws.froms){
            if(ws.froms[i].from_id == from_id)return ws.froms[i][which];
        }
    }
})(win.ws);

//显示表情
var faces = {
    data : [
        ["/::)", "微笑"],
        ["/::~", "伤心"],
        ["/::B", "美女"],
        ["/::|", "发呆"],
        ["/:8-)", "墨镜"],
        ["/::<", "哭"],
        ["/::$", "羞"],
        ["/::X", "哑"],
        ["/::Z", "睡"],
        ["/::'(", "哭"],
        ["/::-|", "囧"],
        ["/::@", "怒"],
        ["/::P", "调皮"],
        ["/::D", "笑"],
        ["/::O", "惊讶"],
        ["/::(", "难过"],
        ["/::+", "酷"],
        ["/:--b", "汗"],
        ["/::Q", "抓狂"],
        ["/::T", "吐"],
        ["/:,@P", "笑"],
        ["/:,@-D", "快乐"],
        ["/::d", "奇"],
        ["/:,@o", "傲"],
        ["/::g", "饿"],
        ["/:|-)", "累"],
        ["/::!", "吓"],
        ["/::L", "汗"],
        ["/::>", "高兴"],
        ["/::,@", "闲"],
        ["/:,@f", "努力"],
        ["/::-S", "骂"],
        ["/:?", "疑问"],
        ["/:,@x", "秘密"],
        ["/:,@@", "乱"],
        ["/::8", "疯"],
        ["/:,@!", "哀"],
        ["/:!!!", "鬼"],
        ["/:xx", "打击"],
        ["/:bye", "bye"],
        ["/:wipe", "汗"],
        ["/:dig", "抠"],
        ["/:handclap", "鼓掌"],
        ["/:&-(", "糟糕"],
        ["/:B-)", "恶搞"],
        ["/:<@", "什么"],
        ["/:@>", "什么"],
        ["/::-O", "累"],
        ["/:>-|", "看"],
        ["/:P-(", "难过"],
        ["/::'|", "难过"],
        ["/:X-)", "坏"],
        ["/::*", "亲"],
        ["/:@x", "吓"],
        ["/:8*", "可怜"],
        ["/:pd", "刀"],
        ["/:<W>", "水果"],
        ["/:beer", "酒"],
        ["/:basketb", "篮球"],
        ["/:oo", "乒乓"],
        ["/:coffee", "咖啡"],
        ["/:eat", "美食"],
        ["/:pig", "动物"],
        ["/:rose", "鲜花"],
        ["/:fade", "枯"],
        ["/:showlove", "唇"],
        ["/:heart", "爱"],
        ["/:break", "分手"],
        ["/:cake", "生日"],
        ["/:li", "电"],
        ["/:bome", "炸弹"],
        ["/:kn", "刀子"],
        ["/:footb", "足球"],
        ["/:ladybug", "瓢虫"],
        ["/:shit", "翔"],
        ["/:moon", "月亮"],
        ["/:sun", "太阳"],
        ["/:gift", "礼物"],
        ["/:hug", "抱抱"],
        ["/:strong", "拇指"],
        ["/:weak", "贬低"],
        ["/:share", "握手"],
        ["/:v", "剪刀手"],
        ["/:@)", "抱拳"],
        ["/:jj", "勾引"],
        ["/:@@", "拳头"],
        ["/:bad", "小拇指"],
        ["/:lvu", "拇指八"],
        ["/:no", "食指"],
        ["/:ok", "ok"],
        ["/:love", "情侣"],
        ["/:<L>", "爱心"],
        ["/:jump", "蹦哒"],
        ["/:shake", "颤抖"],
        ["/:<O>", "怄气"],
        ["/:circle", "跳舞"],
        ["/:kotow", "发呆"],
        ["/:turn", "背着"],
        ["/:skip", "伸手"],
        ["/:oY", "耍帅"]
    ],
    create : function(){
        var code = '',n = 100;
        for(var k = 0; k < 3; k++){
            code += '<table class="facelist">';
            for(var i = 0; i < 4; i++){
                code += '<tr>';
                for(var j = 0; j < 8; j++){
                    if(n < 200)
                        code += '<td><img class="faceimg" src="images/faces/'+ n +'.gif" alt="'+ this.data[n-100][1] +'" data="'+ this.data[n-100][0] +'"></td>';
                    else
                        code += '<td></td>';
                    n ++;
                }
                code += '</tr>';
            }
            code += '</table>';
        }
        return code;
    },
    //转换文本中的图片为表情符号
    encode : function(content){
        return content.replace(/<img.+?data="(.+?)".*?>/gm, '$1');
    },
    //转换文本中的表情符号为图片
    decode : function(content){
        for(var i in this.data){
            var arr = this.data[i][0].split('');
            var str = '';
            for(var j in arr){
                if(arr[j] == '/' || arr[j] == '?' || arr[j] == '.' || arr[j] == '[' || arr[j] == ']' || arr[j] == '(' || arr[j] == ')' || arr[j] == '{' || arr[j] == '}' || arr[j] == '+' || arr[j] == '*' || arr[j] == '-' || arr[j] == '|')
                    str += '\\' + arr[j];
                else
                    str += arr[j];
            }
            content = content.replace(new RegExp(str, 'gm'), '<img class="faceimg" src="images/faces/'+ (parseInt(i)+100) +'.gif" data="'+ this.data[i][0] +'">');
        }
        return content;
    }
};