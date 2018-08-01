//显示表情
module.exports = {
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
    //转换文本中的图片为表情符号
    encode : function(content){
        for(var i in this.data){
            content = content.replace(new RegExp('\\\[' + this.data[i][1] + '\\\]', 'gm'), this.data[i][0]);
        }
        return content;
    },
    //转换文本中的表情符号为图片
    decode : function(content){
        var faces = [];
        var _fs = [];
        for(var i in this.data){
            _fs[this.data[i][0]] = parseInt(i) + 100;
            var arr = this.data[i][0].split('');
            var str = '';
            for(var j in arr){
                if(arr[j] == '/' || arr[j] == '?' || arr[j] == '.' || arr[j] == '[' || arr[j] == ']' || arr[j] == '(' || arr[j] == ')' || arr[j] == '{' || arr[j] == '}' || arr[j] == '+' || arr[j] == '*' || arr[j] == '-' || arr[j] == '|' || arr[j] == '$')
                    str += '\\' + arr[j];
                else
                    str += arr[j];
            }
            faces.push(str);
        }
        faces.push('<a.+?>.+?<\/a>');
        var reg = '('+ faces.join(')|(') +')';
        var code = [];
        content = content.split(new RegExp(reg, 'm'));
        for(var i=0; i<content.length; i++)
        {
            if(content[i] != "" && typeof(content[i]) != "undefined"){
                if(_fs[content[i]])code.push({t:1, d:_fs[content[i]]});
                else if(/<a.+?>.+?<\/a>/.test(content[i])){
                    var arr = content[i].match('<a.+?>(.+?)<\/a>');
                    code.push({t:2, d:arr[1]});
                }else code.push({t:0, d:content[i]});
            }
        }
        return code;
    }
};