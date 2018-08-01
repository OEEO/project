var win = {
    host : 'http://'+ DOMAIN +'/',
    width : $(window).width(),
    height : $(window).height(),
    city : {id:224, name:'广州'},
    location : [0,0],
    version : '2.8.30',
    publicGet : {},
    get : {},
    token : null,
    ws : {},
    body : ['header', 'main', 'footer','fixed'],
    paytimes :0,
    //捕捉改变窗口大小事件
    reset : function(fn){
        $('.wrapper').width(this.width);
        $('.wrapper').height(this.height);
        $('#main').width(this.width * (page.num + 1));
        $('#main').height(this.height);
        $('html').attr('style', 'font-size:' + 10 * (this.width / 360) +'px !important');
        if(typeof(fn) == 'function')fn();
    },
    //loading弹出层
    loading : function(){
        if(this.overlay){
            this.overlay.remove();
            this.overlay = null;
        }
        this.overlay = $('<div>').css({
            'position':'fixed',
            'width':'100%',
            'height':'100%',
            'background':'rgba(255,255,255,0.7)',
            'z-index':110000
        }).appendTo('body');

        if(this.loadingLay){
            this.loadingLay.remove();
            this.loadingLay = null;
        }
        var code = '<div class="spinner">';
        code += '	<div class="spinner-container container1">';
        code += '	<div class="circle1"></div>';
        code += '	<div class="circle2"></div>';
        code += '	<div class="circle3"></div>';
        code += '	<div class="circle4"></div>';
        code += '	</div>';
        code += '	<div class="spinner-container container2">';
        code += '	<div class="circle1"></div>';
        code += '	<div class="circle2"></div>';
        code += '	<div class="circle3"></div>';
        code += '	<div class="circle4"></div>';
        code += '	</div>';
        code += '	<div class="spinner-container container3">';
        code += '	<div class="circle1"></div>';
        code += '	<div class="circle2"></div>';
        code += '	<div class="circle3"></div>';
        code += '	<div class="circle4"></div>';
        code += '	</div>';
        code += '</div>';
        this.loadingLay = $(code).appendTo('body');
    },
    //关闭loading层
    close_loading : function(){
        if(this.overlay){
            this.overlay.remove();
            this.overlay = null;
        }
        if(this.loadingLay){
            this.loadingLay.remove();
            this.loadingLay = null;
        }
    },
    //页面载入
    load : function(pagename, get, fn, isBack){
        if(pagename == null)pagename = page.names[page.num];
        console.log(pagename + ' 页面载入...');
        this.loading();
        this.get = JSON.parse(JSON.stringify(this.publicGet));
        for(var i in get){
            this.get[i] = get[i];
        }
        if(win.debug)
            var pagePath = pagename + '.html?v=' + win.version;
        else
            var pagePath = 'code/' + pagename + '.html?v=' + win.version;
        $.ajax({
            url : pagePath,
            // async : false,
            success : function(code){
                if(win.debug){
                    var reg = code.match(/<body.*>([\S\s]*)<\/body>/);
                    code = reg[1];
                }else{
                    var reg = code.match(/([\S\s]*)(<script>[\S\s]*<\/script>)/);
                    var code = reg[1];
                    var js = reg[2];
                }
                if(!isBack){
                    page.wrapper[page.num] = $('<div class="wrapper page_'+ pagename +'">').html(code).appendTo('#main');
                    page.header[page.num] = page.wrapper[page.num].find('.header').appendTo('#header').addClass('page_'+ pagename);
                    if(page.wrapper[page.num].find('.footer').size() > 0)
                        page.footer[page.num] = page.wrapper[page.num].find('.footer');
                    else
                        page.footer[page.num] = $('<div class="footer">');
                    page.footer[page.num].appendTo('#footer').addClass('page_'+ pagename);

                    if(page.wrapper[page.num].find('.fixed').size() > 0){
                        page.fixed[page.num] = page.wrapper[page.num].find('.fixed');
                    }else{
                        page.fixed[page.num] = $('<div class="fixed">');
                    }
                    page.fixed[page.num].appendTo('#fixed').addClass('page_'+ pagename);
                }else{
                    page.wrapper[page.num] = $('<div class="wrapper page_'+ pagename +'">').html(code).prependTo('#main');
                    page.header[page.num] = page.wrapper[page.num].find('.header').prependTo('#header').addClass('page_'+ pagename);
                    page.footer[page.num] = page.wrapper[page.num].find('.footer').prependTo('#footer').addClass('page_'+ pagename);
                    page.fixed[page.num] = page.wrapper[page.num].find('.fixed').prependTo('#fixed').addClass('page_'+ pagename);
                }
                if(win.debug){
                    page.css[page.num] = $("<link rel='stylesheet'>").attr('href', 'css/' + pagename + '.css?_=' + Math.random()).appendTo('html');
                    page.script[page.num] = $("<script>").attr('src', 'js/' + pagename + '.js?_=' + Math.random()).appendTo('html');
                }else{
                    $('.wrapper.page_'+ pagename).append(js);
                }

                $('.resourcesBox').appendTo('body');

                if(!$('#footer .page_' + pagename).html()){
                    $('#footer').hide();
                }else{
                    $('#footer').show();
                }
                // if($('#fixed .page_' + pagename).children().length == 0)
                //     $('#fixed').hide();
                // else
                //     $('#fixed').show();
                $('#fixed').hide();

                //判断页面容器
                if(win.body.indexOf('header') == -1 || (get && get.body && get.body.indexOf('header') == -1)){
                    $('#header').remove();
                    delete page;
                }
                if(win.body.indexOf('main') == -1 || (get && get.body && get.body.indexOf('main') == -1)){
                    $('#main').remove();
                    delete page;
                }
                if(win.body.indexOf('footer') == -1 || (get && get.body && get.body.indexOf('footer') == -1)){
                    $('#footer').remove();
                    delete page;
                }
                if(win.body.indexOf('fixed') == -1 || (get && get.body && get.body.indexOf('fixed') == -1)){
                    $('#fixed').remove();
                    delete page;
                }

                var pstr = '';
                if(get){
                    for(var i in get){
                        pstr += '&' + i + '=' + get[i];
                    }
                }
                _hmt.push(['_trackPageview', '/?page=' + pagename + pstr]);

                $('.wrapper').scroll(function(){
                    if($('.page_' + page.names[page.num] + '.changeStyle').size() > 0){
                        if($(this).scrollTop() > win.height / 2){
                            $('.page_' + page.names[page.num] + '.changeStyle').addClass('afterScroll');
                        }else{
                            $('.page_' + page.names[page.num] + '.changeStyle').removeClass('afterScroll');
                        }
                    }
                });
                if(window[pagename + 'Object'].onload && typeof(window[pagename + 'Object'].onload) == 'function'){
                    page.load[pagename] = 0;
                    window[pagename + 'Object'].onload();
                    (function LoadTimming(){
                        if(page.load[pagename]){
                            setTimeout(LoadTimming, 100);
                            return;
                        }
                        if(typeof(fn) == 'function'){
                            fn();
                        }else{
                            $('#header,#footer,#main').find('.page_' + pagename).show();
                            win.close_loading();
                            if(window[page.names[page.num] + 'Object'].onshow && typeof(window[page.names[page.num] + 'Object'].onshow) == 'function')window[page.names[page.num] + 'Object'].onshow();
                        }
                    })();
                    return;
                }
			
                if(typeof(fn) == 'function'){
                    fn();
                }else{
                    $('#header,#main').find('.page_' + pagename).show();
					console.log(pagename, $('#footer .page_' + pagename).html());
					if(!$('#footer .page_' + pagename).html()){
						$('#footer').hide();
					}else{
						$('#footer').show();
					}
                    win.close_loading();
                    if(window[page.names[page.num] + 'Object'].onshow && typeof(window[page.names[page.num] + 'Object'].onshow) == 'function')window[page.names[page.num] + 'Object'].onshow();
                }
            },
            error: function (e) {
                console.error(e);
            }
        });
    },
    //存储skey，便于下次自动登录
    saveSkey : function(id, skey){
        if(window.localStorage){
            // window.localStorage.autologin = id + '|' + skey;
            storage.set('autologin', {id:id, skey:skey});
        }
        //连接websocket
        script.load('plugins/websocket');
    },
    //Ajax上传前的数据打包
    FormData : function(mydata, fn){
        var $form = $('<form name="upload">').appendTo('body').css({'position':'absolute', 'width':0, 'height':0});
        var $file = $('<input type="file" accept="image/*" name="file" />').appendTo($form);
        if(typeof(mydata) == 'object'){
            var code = '';
            for(var i in mydata){
                code += '<input type="text" name="'+ i +'" value="'+ mydata[i] +'">';
            }
            $form.append(code);
        }else if(typeof(mydata) == 'function'){
            fn = mydata;
        }
        $file.change(function(){
            if(typeof(fn) == 'function'){
                fn(new FormData($('form[name="upload"]')[0]));
            }
            $('form[name="upload"]').remove();
        });
        $file.blur(function(){
            if(typeof(fn) == 'function'){
                fn();
            }
            $('form[name="upload"]').remove();
        });
        $file.click();
    },
    //获取token
    getToken : function(fn){
        var url = 'http://api.' + DOMAIN + '/';

        win.loading();
        //和服务器第一次握手
        $.ajax({
            url : url,
            async : false,
            cache: false,
            data : {url:location.href},
            cache:false,
            complete: function(){
                win.close_loading();
            },
            success : function(d){
                if(typeof(d.token) == 'string'){
                    if(location.href.indexOf('token=') > 0){
                        win.token = location.href.split('token=')[1].split('&')[0];
                        win.ajax('member/index/info', {}, function(d){
                            if(!d.info){
                                storage.rm('autologin');
                                // window.localStorage.removeItem('autologin');
                                member = d;
                            }
                        }, false);
                    }else{
                        win.token = d.token;
                    }

                    //赋值版本号
                    win.version = d.api_version;
                    win.city.id = d.city.id;
                    win.city.name = d.city.name;
                    win.appid = d.appId;

                    if(!isWeiXin()){
                        loadWechat(d.appId, d.timestamp, d.nonceStr, d.signature);
                        if(typeof(fn) == 'function')fn();
                        return;
                    }

                    loadWechat(d.appId, d.timestamp, d.nonceStr, d.signature, function(){
                        var cityarray = ['北京','上海','广州','深圳'];
                        if($.inArray(storage.get('city_name'),cityarray) == -1){
                            storage.rm('city_name');
                            storage.rm('city_id');
                        }
                        //判断是否储存了城市选择
                        if(storage.get('city_id') && storage.get('city_name')){
                            //如果选择了城市则执行城市跳转
                            if(d.city.id == storage.get('city_id') && d.city.name == storage.get('city_name')){
                                if(typeof(fn) == 'function')fn();
                            }else{
                                win.ajax('Home/Index/changeCity', {city_id: storage.get('city_id')}, function(d){
                                    if(d.status == 1){
                                        win.city.id = storage.get('city_id');
                                        win.city.name = storage.get('city_name');
                                    }
                                    if(typeof(fn) == 'function')fn();
                                }, false);
                            }
                        }else{
                            if(wechat.getLocation){
                                wechat.getLocation(function(res){
                                    if(res){
                                        win.location = [res.latitude, res.longitude];
                                        win.ajax('Home/Index/getAddress', {latitude:res.latitude, longitude:res.longitude, is_location:1}, function(d){
                                            if(d.status == 1){
                                                win.city.id = d.info.city_id;
                                                win.city.name = d.info.city_name;
                                                if(window.localStorage){
                                                    storage.set('city_id', win.city.id);
                                                    storage.set('city_name', win.city.name);
                                                }
                                            }else{
                                                $.alert(d.info, 'error');
                                            }
                                        }, false);
                                    }
                                    if(typeof(fn) == 'function')fn();
                                });
                            }else{
                                if(typeof(fn) == 'function')fn();
                            }
                        }
                    });
                }else{
                    console.warn('非法访问');
                    // fn()
                }
            },
            
            error : function(){
                $('body').html('<div class="connErr">服务器连接失败！请检查网络连接是否正常！<br /><button onclick="window.location.reload()">刷新页面</button></div>');
            }
            /*
            error : function(XMLHttpRequest, textStatus, errorThrown) {
                 alert(XMLHttpRequest.status);
                 alert(XMLHttpRequest.readyState);
                 alert(textStatus);
            },
            */
        });
    },
    //ajax封装
    ajax : function(path, data, fn, type){
        var url = 'http://api.' + DOMAIN + '/';
        var async = type === false ? false : true;
        if(typeof(data) == 'function'){
            fn = data;
            data = {};
        }
        var arr = window.location.href.substr(url.length).split('/');
        https = [
            arr[0] ? arr[0] : 'home',
            arr[1] ? arr[1] : 'index',
            arr[2] ? arr[2] : 'index'
        ];
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
        if(win.token != null){
            url += "?token=" + win.token + "&v=" + win.version;
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
            _hmt.push(['_trackPageview', url]);
            $.ajax({
                url : url,
                type : 'POST',
                async : async,
                contentType : "application/x-www-form-urlencoded; charset=utf-8",
                dataType : "json",
                data : postdata,
                timeout : (type == 3 ? 1000000 : 30000),
                beforeSend : function(){
                    if(!async || type == 2){
                        try {if(page.load[page.names[page.num]] >= 0)page.load[page.names[page.num]]++;}catch(e){}
                        win.loading();
                    }
                },
                complete : function(){
                    if(!async || type == 2){
                        try{
                            if(page.load[page.names[page.num]] > 0)page.load[page.names[page.num]]--;
                            else delete page.load[page.names[page.num]];
                        }catch(e){}
                    }
                    if(!page.load[page.names[page.num]])win.close_loading();
                },
                success : function(d){
                    if(typeof(fn) == 'function')fn(d);
                    //记录浏览历史
                    if(/tips\/getdetail/i.test(url)){
                        page.setHistory('tips-' + win.get.tips_id, {
                            type : 0,
                            id : win.get.tips_id,
                            title : d.title,
                            path : d.mainpic
                        });
                        console.log('记录活动页!');
                    }
                    if(/goods\/getdetail/i.test(url)){
                        page.setHistory('goods-' + win.get.goods_id, {
                            type : 1,
                            id : win.get.goods_id,
                            title : d.title,
                            path : d.mainpic
                        });
                        console.log('记录商品页!');
                    }
                    if(/raise\/getdetail/i.test(url)){
                        page.setHistory('raise-' + win.get.raise_id, {
                            type : 2,
                            id : win.get.raise_id,
                            title : d.title,
                            path : d.path
                        });
                        console.log('记录众筹页!');
                    }
                },
                error : function(XMLHttpRequest, textStatus, errorThrown){
                    console.log('当前请求的页面是:', url);
                    console.log(XMLHttpRequest);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            });
        }
    },
    login: function(fn){
        if(member == null) {
            page.backName = page.names[page.num];
            page.backData = win.get;
            page.backFun = fn;
            page.reload('login');
        }
    }
};

var script = {
    data : [],
    load : function(name, fn){
        if(this.data.indexOf(name) == -1){
            this.data.push(name);
            $.getScript('js/' + name + '.js?v=' + win.version, function(d){
                if(typeof(fn) == 'function')fn();
            });
        }else{
            if(typeof(fn) == 'function')fn();
        }
    }
};

var page = {
    fn: {},
    names: ['choice'],
    //页面onload方法中的异步请求中数量
    load: {},
    header: [],
    wrapper: [],
    footer: [],
    fixed: [],
    num: 0,
    script: [],
    css: [],
    ready: function (f) {
        if (typeof(f) == 'function') {
            var name = page.names[page.num];
            page.fn[name] = f;
        }
    },
    //跳转下一页
    jump: function (pagename, get, fn) {
        if (pagename.indexOf('-') == -1) {
            history.pushState({title: pagename}, pagename, window.location.href);

            $('#filterMenu').remove();
            page.names.push(pagename);
            page.num++;
            win.load(pagename, get, function () {
                page.move(-1, function () {
                    if (typeof(fn) == 'function')fn();
                });
            });
            //$('#header, #main, #footer, #fixed').hide();
        } else {
            var arr = pagename.split('-');
            page.names = [];
            page.clear();
            for (var i in arr) {
                history.pushState({title: arr[i]}, arr[i], location.href);
                page.names.push(arr[i]);
            }
            page.num = arr.length - 1;
            win.load(arr[page.num], get, function () {
                page.move(-1, function () {
                    $('#header, #main, #footer, #fixed').children().not('.page_' + page.names[page.num]).remove();
                    if (typeof(fn) == 'function')fn();
                });
            });
            //$('#header, #main, #footer, #fixed').hide();
        }
    },
    //返回上一页
    back: function (fn, callback) {
        if (page.num > 0) {
            page.num--;
            if ($('.page_' + page.names[page.num]).size() > 0) {
                $('#header, #main, #footer').find('.page_' + page.names[page.num]).show();
                $('.shareBox').hide();
                page.move(1, function () {
                    var name = page.names[page.num];
                    if (page.fn[name] && typeof(page.fn[name]) == 'function') {
                        page.fn[name]();
                    }
                    if (typeof(fn) == 'function')fn();
                    page.clear();
                });
            } else if (!win.get.backUrl) {
			
                win.load(page.names[page.num], win.get, function () {
                    page.move(1, function () {
                        $('.shareBox').hide();
                        var name = page.names[page.num];
						
                        if (page.fn[name] && typeof(page.fn[name]) == 'function') {
							console.log(page.fn[name], name);
                            page.fn[name]();
                        }

                        if (typeof(fn) == 'function')fn();
                        page.clear();
                    });
                }, true);
            } else {
                location.href = decodeURIComponent(win.get.backUrl);
            }
        }
    },
    //刷新页面
    reload: function (pagename, get, fn) {
        eval("delete " + page.names[page.num] + "Object;");
        if (win.debug) {
            if (page.script[page.num])page.script[page.num].remove();
            page.script = removeArr(page.script, page.num);
            if (page.css[page.num])page.css[page.num].remove();
            page.css = removeArr(page.css, page.num);
        }
        if (page.header[page.num])page.header[page.num].remove();
        page.header = removeArr(page.header, page.num);
        if (page.wrapper[page.num])page.wrapper[page.num].remove();
        page.wrapper = removeArr(page.wrapper, page.num);
        if (page.footer[page.num])page.footer[page.num].remove();
        page.footer = removeArr(page.footer, page.num);

        if (page.fixed[page.num])page.fixed[page.num].remove();
        page.fixed = removeArr(page.fixed, page.num);

        if (pagename) {
            if (pagename.indexOf('-') == -1)
                page.names[page.num] = pagename;
        }
        get = get || win.get;
        history.replaceState({title: pagename}, pagename, location.href);
        win.load(page.names[page.num], get, function () {
            setTimeout(function(){
                win.close_loading();
                if (typeof(fn) == 'function')fn();
                if(window[page.names[page.num] + 'Object'].onshow && typeof(window[page.names[page.num] + 'Object'].onshow) == 'function')window[page.names[page.num] + 'Object'].onshow();
            }, 500);
            $('.page_' + page.names[page.num]).show();
        });
        $('#main .page_' + page.names[page.num]).scrollTop(0);
    },
    //清理不用的页面
    clear: function () {
        if (page.names.length > page.num + 1) {
            for (var i = page.num + 1; i < page.names.length; i++) {
                eval("delete " + page.names[i] + "Object;");
                page.names = removeArr(page.names, i);
                if (win.debug) {
                    if (page.script[i])page.script[i].remove();
                    page.script = removeArr(page.script, i);
                    if (page.css[i])page.css[i].remove();
                    page.css = removeArr(page.css, i);
                }
                if (page.header[i])page.header[i].remove();
                page.header = removeArr(page.header, i);
                if (page.wrapper[i])page.wrapper[i].remove();
                page.wrapper = removeArr(page.wrapper, i);
                if (page.footer[i])page.footer[i].remove();
                page.footer = removeArr(page.footer, i);
                if (page.fixed[i])page.fixed[i].remove();
                page.fixed = removeArr(page.fixed, i);
            }
        }
    },
    move: function (way, fn) {
		
        $('#header, #main, #footer').show().children().hide();
        setTimeout(function () {
            $('#header, #main, #footer').find(".page_" + page.names[page.num]).show();
            setTimeout(function () {
                win.close_loading();
                if(window[page.names[page.num] + 'Object'] && window[page.names[page.num] + 'Object'].onshow && typeof(window[page.names[page.num] + 'Object'].onshow) == 'function')window[page.names[page.num] + 'Object'].onshow();
                $('.resourcesBox.page_' + page.names[page.num]).show();
                if (typeof(fn) == 'function')fn();
            }, 500);
            //判断有空样式，则隐藏底部，否则会遮挡其他元素
			console.log(!$('#footer .page_' + page.names[page.num]).html());
            if (!$('#footer .page_' + page.names[page.num]).html())$('#footer').hide();
            if ($('#fixed .page_' + page.names[page.num]).children().length == 0)$('#fixed').hide();
            //将资源盒子元素隐藏
            $('.resourcesBox').not('.page_' + page.names[page.num]).hide();
        }, 500);
    },
    setHistory: function (key, data) {
        if (localStorage.yummyhistory) {
            var hs = JSON.parse(localStorage.yummyhistory);
        } else {
            localStorage.yummyhistory = '{}';
            var hs = {};
        }
        if (hs[key]) {
            delete hs[key];
        }
        hs[key] = data;
        localStorage.yummyhistory = JSON.stringify(hs);
    },
    //上传图片
    uploadimg: function (file, fn) {
        var cvs = $('<canvas>').width(640).attr('width', 640);
        var url = window.URL.createObjectURL(file);
        var img = $('<img/>').attr('src', url);
        img.load(function () {
            var height = 640 / this.width * this.height;
            cvs.height(height).attr('height', height);
            var context = cvs[0].getContext('2d');
            context.drawImage(this, 0, 0, 640, height);
            var data = cvs[0].toDataURL('image/jpeg');
            data = data.replace('data:image/jpeg;base64,', '');
            win.ajax('Home/Sign/upload', {file: data}, function (d) {
                if (d.status == 1) {
                    $.alert('上传成功', function () {
                        if (typeof(fn) == 'function')fn(d.info.path, d.info.pic_id);
                    });
                } else {
                    $.alert(d.info, 'error');
                }
            }, 2);
        });
    }
};

//会员对象
var member = null;

//框架由此处开始
(function(href){
    //判断是否为微信授权回调
    if(href.indexOf('code') > 0 && href.indexOf('state') > 0){
        var arr = href.split('?')[1].split('&');
        var param = {};
        for(var i in arr){
            var a = arr[i].split('=');
            param[a[0]] = decodeURI(a[1]);
        }
        win.token = param.state;
        win.token = win.token.split('|')[0];

        win.ajax('Home/Wx/index',{get:{code : param.code}},function(d){
            if(d.status == 1){
                storage.set('punch', {id: d.info.id, skey: d.info.skey});
                if(storage.get('href'))
                    location.href = storage.get('href');
                else
                    location.href = win.host;
            }
        });
        return;
    }

    //客户端首次与服务器握手
    win.getToken(function(){
        //判断属于微信浏览器则主动授权
        if(isWeiXin()){
            // 判断是否首次进入,首次进入则需要主动授权
            if(storage.get('punch') === false){
                storage.set('href', location.href);
                //跳转到微信主动授权
                location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='+ win.appid +'&redirect_uri=http%3A%2F%2F'+ DOMAIN +'&response_type=code&scope=snsapi_userinfo&state=' + win.token;
            }else{
                var id = storage.get('punch.id');
                var skey = storage.get('punch.skey');
                //微信登录
                win.ajax('Home/Wx/Login', {id: id, skey: skey}, function(d){
                    if(d.status == 1){
                        win.wxMember = d.info.info;
                        storage.set('punch.id', d.info.info.id);
                        storage.set('punch.skey', d.info.skey);
                        //判断是否同时登录了会员
                        if(d.info.member){
                            member = d.info.member;
                            //连接websocket
                            script.load('plugins/websocket');
                            if(member.coupon){
                                var box = $('<div>').addClass('get_coupon_box').appendTo('body');
                                var code = '<div class="box">';
                                code += '<div class="price"><small>￥</small>'+ parseFloat(info.coupon.value).priceFormat(2) +'</div>';
                                code += '<p class="t">太好了! O(∩_∩)O</p>';
                                code += '<p class="b">吖咪酱已经把'+ info.coupon.value +'元代金券放进了您的个人账户中..</p>';
                                code += '<a href="javascript:jump(\'myCoupon\');">前往我的优惠券</a>';
                                code += '</div>';
                                box.html(code).click(function(){
                                    $(this).remove();
                                });
                            }
                        }
                        //进入下一步:路由
                        url_load();
                    }else{
                        storage.rm('punch');
                        storage.set('href', location.href);
                        location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='+ win.appid +'&redirect_uri=http%3A%2F%2F'+ DOMAIN +'&response_type=code&scope=snsapi_userinfo&state=' + win.token;
                    }
                });
            }
            // if(storage.get('autologin')){
            //     autoLogin();
            // }else{
            //     url_load();
            // }
        }else{
            if(storage.get('autologin')){
                autoLogin();
            }else{
                url_load();
            }
        }
    });
})(window.location.href);

win.reset();
$(window).resize(function(){
    win.width = $(window).width();
    win.height = $(window).height();
    win.reset();
});

function autoLogin(){
    var autologinData = {};
    autologinData.id = storage.get('autologin.id');
    autologinData.skey = storage.get('autologin.skey');

    //Ajax数据调用
    win.ajax('member/index/autologin', autologinData, function(d){
        if(d.status == 1){
            var skey = d.info.skey;
            var info = d.info.info;
            if(info.coupon){
                var box = $('<div>').addClass('get_coupon_box').appendTo('body');
                var code = '<div class="box">';
                code += '<div class="price"><small>￥</small>'+ parseFloat(info.coupon.value).priceFormat(2) +'</div>';
                code += '<p class="t">太好了! O(∩_∩)O</p>';
                code += '<p class="b">吖咪酱已经把'+ info.coupon.value +'元代金券放进了您的个人账户中..</p>';
                code += '<a href="javascript:jump(\'myCoupon\');">前往我的优惠券</a>';
                code += '</div>';
                box.html(code).click(function(){
                    $(this).remove();
                });
            }
            member = info;
            win.saveSkey(info.id, skey);
        }else{
            if(d.info == 'no_telephone'){
                $.alert('您没有手机号,请重新登录', 'error');
            }
            storage.rm('autologin');
        }
        url_load();
    }, false);
}

//框架路由
function url_load(){
    var location_href = window.location.href.split('#')[0];
    if(location_href.indexOf('?') > 0){
        var urlArr = location_href.split('?')[1];
        var urlArr = urlArr.split('&');
        var commands = {};
        for(var i in urlArr){
            var arr = urlArr[i].split('=');
            commands[arr[0]] = decodeURI(arr[1]);
        }
        if(commands.invitecode){
            win.invitecode = commands.invitecode;
            win.ajax('member/index/invitecode', {'invitecode':commands.invitecode}, function(d){
                if(d.status == 0){
                    win.invitecode = null;
                }
            });
            delete commands.invitecode;
        }
        if(commands.debug){
            win.debug = commands.debug;
            delete commands.debug;
        }
        if(commands.body){
            win.body = commands.body.split('|');
            delete commands.body;
        }
        if(commands.page && commands.page.indexOf('-') > 0){
            var p = commands.page;
            delete commands.page;
            win.publicGet = commands;
            page.jump(p, win.get);
        }else{
            if(commands.page){
                page.names = [commands.page];
                delete commands.page;
            }
            win.publicGet = commands;
            win.load();
        }
    }else{
        win.load();
    }
}

var ajax = win.ajax;
var $$ = page.ready;
var jump = page.jump;

$(window).scroll(function(){
    if($(window).scrollLeft() > 0)$(window).scrollLeft(0);
    return false;
});

window.addEventListener("popstate", function(e) {
    console.log(e.state);
    if($('#main').size() > 0)
        page.back();
    else
        location.reload(true);
});

//判断是否为微信浏览器
function isWeiXin() {
    var ua = window.navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == 'micromessenger') {
        return true;
    } else {
        return false;
    }
}

function getQueryString(url, name) {
    var reg = new RegExp("(\\?|&)" + name + "=([^&]*)(&|$)", "i");
    var r = url.match(reg);
    if (r != null) return decodeURIComponent(r[2]); return null;
}
