//时间戳格式化
String.prototype.timeFormat = function(format){

	var time = this.toString();
	if(/^\d+$/.test(time)){
		var myDate = new Date(time * 1000);
	}else{
		time = time.replace(/\-/g, '/');
		var myDate = new Date(time);
	}
	var _date = {};
	_date.Y = myDate.getFullYear();
	_date.m = (myDate.getMonth() + 1).toString();
	if(_date.m.length == 1)_date.m = '0' + _date.m;
	_date.d = myDate.getDate().toString();
	if(_date.d.length == 1)_date.d = '0' + _date.d;
	_date.H = myDate.getHours();
	_date.i = myDate.getMinutes().toString();
	if(_date.i.length == 1)_date.i = '0' + _date.i;
	_date.s = myDate.getSeconds().toString();
	if(_date.s.length == 1)_date.s = '0' + _date.s;
	_date.w = myDate.getDay().toString();
	weekday = ['周日','周一','周二','周三','周四','周五','周六'];
	_date.W = weekday[myDate.getDay()];
	for(var i in _date){
		format = format.replace(i, _date[i]);
	}
	return format;
};
//var date = time.timeFormat('Y-m-d H:i:s w');

//将url参数转为json
String.prototype.decodeURL = function(){
	var url = this.toString();
	if(url.indexOf('?') > 0){
		url = url.split('?')[1];
	}
	var arr = url.split('&');
	var params = {};
	for(var i in arr){
		var a = arr[i].split('=');
		if(a.length == 2){
			params[a[0]] = a[1];
		}
	}
	return params;
};

//图片路径格式化
String.prototype.pathFormat = function(){
	var path = this.toString();
	if(path.indexOf('http://') == 0)return path;
	if(path.substr(0,1) == '/')path = path.substr(1);
	if(path.indexOf('uploads/') >= 0){
		path = 'http://yummy194.cn/' + path;
	}else{
		path = 'http://img.m.yami.ren/' + path;
	}
	return path;
};

//价格格式化
Number.prototype.priceFormat = function(point){
	if(this.toString().indexOf('.') == -1)return this;
	var point = point||2;
	var num = Math.round(this * 100) + '';
	if(point > 0){
		var a = num.substr(0, num.length - point);
		if(a == '')a = '0';
		return a + '.<small>' + num.substr(num.length - point) + '</small>';
	}else
		return Math.round(this);
};

//删除数组对象的指定元素
function removeArr(o, n){
	var arr = [];
	for(var i in o){
		if(i != n)
			arr[i] = o[i];
	}
	o = arr;
	return arr;
};

var btnSubmit = {
	btnLoadTimer : null,
	btnLoadem : null,
	beforeLoadWords : null,
	isLoading : function(){
		if(this.btnLoadTimer != null)return true;
		return false;
	},
	loading : function($em, msg){
		var msg = msg||'提交中';
		this.btnLoadem = $em;
		$em.addClass('disabled');
		this.beforeLoadWords = $em.val();
		var s = 0;
		this.btnLoadTimer = window.setInterval(function (){
			s ++;
			if(s > 3)s = 0;
			var m = msg;
			for(var i=0; i<s; i++){
				m += '.';
			}
			$em.val(m);
		}, 300);
	},
	//关闭loading层
	close : function(){
		window.clearInterval(this.btnLoadTimer);
		this.btnLoadem.removeClass('disabled');
		this.btnLoadem.val(this.beforeLoadWords);
		this.btnLoadTimer = null;
		this.btnLoadem = null;
		this.beforeLoadWords = null;
	}
};

$.fn.bubble = function(){
	var parentBox = $(this);
	this.find('img').click(function(){
		var _w = $(window).width();
		var index = $(this).index();
		var size = parentBox.find('img').size();
		var box = $('<div>').css({'position':'fixed', 'left':0, 'top':0, 'right':0, 'bottom':0, 'z-index':999, 'display':'none'}).addClass('zz').appendTo('body').fadeIn('fast');
		var lay = $('<div>').css({'position':'absolute', 'left':0, 'top':0, 'right':0, 'bottom':0, 'background':'rgba(0,0,0,0.5)'}).appendTo(box);
		var ems = $("<div>").css({'position':'absolute', 'left':'0', 'top':'50%','margin-top':'-'+_w/2+'px','max-height':_w}).width(_w).appendTo(box);
		var emtop = $("<div>").css({'position':'absolute', 'left':'-'+index*_w+'px', 'top':'0'}).append(parentBox.find('img').clone().css({'width':_w, 'max-height':_w, 'float':'left'})).width(size * _w).appendTo(ems);
		var embottom = $('<div>').css({'color':'#fff','position':'absolute', 'left':'0', 'bottom':'0','background':'rgba(0,0,0,0.5)','height':'2.5rem','line-height':'2.5rem','width':_w,'text-align':'center'}).appendTo(ems);
		ems.css('height',emtop.height());
		emtop.addClass('e');
		var n = index+1;
		embottom.html(n+'/'+size);
		lay.click(function(){
			box.fadeOut('fast', function(){
				box.remove();
			});
		});
		$('.e').touchwipe({
			'wipeLeft' : function(){
				if(index == size-1){
					n = size;
					$('.e').animate({'left':'-'+(size-1)*_w+'px'}, 'fast');
				}else{
					index++;
					n = index+1;
					$('.e').animate({'left':'-'+index*_w+'px'}, 'fast');
				}
				embottom.html(n+'/'+size);
			},
			'wipeRight' : function(){
				if(index ==0){
					n = 1;
					$('.e').animate({'left':0}, 'fast');
				}else{
					n = index;
					index--;
					$('.e').animate({'left':'-'+index*_w+'px'}, 'fast');
				}
				embottom.html(n+'/'+size);
			}
		});
		var l = $('.zz').size();
		if(l>1){
			for(var i = 1; i < l; i++){
				$('.zz').eq(i).remove();
			}
		}
	});
};

// select自定义封装
$.fn.selectFormat = function(opt, placeholder){
	if(this.attr('data')){
		var dataNum = this.attr('data');
		$('ul[data="'+ dataNum +'"]').remove();
	}else{
		var dataNum = Math.round(Math.random() * 10000);
		this.attr('data', dataNum);
	}
	var listBox = $('<ul>').addClass('page_' + page.names[page.num]).addClass('selectListBox').attr('data', dataNum).appendTo('body');
	var listLay = $('<dd>').addClass('page_' + page.names[page.num]).addClass('selectListBoxLay').attr('data', dataNum).appendTo('body');
	var value = null;
	for(var i in opt){
		var em = $('<button>').text(opt[i].name).attr('value', opt[i].value).appendTo(listBox);
		if(opt[i].selected){
			em.addClass('selected');
			placeholder = opt[i].name;
			value = opt[i].value;
		}
	}
	if(value == null){
		this.css('color','#e7e7e7');
	}else{
		this.attr('value', value);
	}
	if(placeholder){
		this.text(placeholder);
	}

	//添加完成按钮
	this.click(function(){
		$(this).css('border-color', '#999');
		var dataNum = $(this).attr('data');
		$('dd[data="'+ dataNum +'"]').fadeIn('fast');
		var listBox = $('ul[data="'+ dataNum +'"]');
		//获取行高
		var lineHeight = listBox.find('button').height();
		//获取列表高
		var listHeight = listBox.height();
		listBox.show().animate({bottom:0}, 'fast', function(){
			$(this).focus();
		});
		//自动滚动到默认位置
		var selectLi = listBox.find('.selected');
		if(selectLi.size() > 0){
			var top = selectLi.position().top;
			listBox.scrollTop(top - (listHeight - lineHeight) / 2);
		}
	});
	//listBox.attr('contentEditable', 'true');
	//listBox.keydown(function(){return false;});
	//listBox.attr('tabindex', '-1');
	listLay.click(function(){
		var dataNum = $(this).attr('data');
		var listBox = $('ul[data="'+ dataNum +'"]');
		listBox.animate({bottom:-1*listBox.height() - 10}, 'fast', function(){
			var dataNum = $(this).attr('data');
			$('[data="'+ dataNum +'"]').not('ul,dd').css('border-color', '#ddd');
			$('dd[data="'+ dataNum +'"]').fadeOut('fast');
			$(this).hide();
		});
	});
	listBox.find('button').click(function(){
		$(this).siblings().removeClass('selected');
		$(this).addClass('selected');
		var v = $(this).attr('value');
		var n = $(this).text();
		var dataNum = $(this).parent().attr('data');
		$('[data="'+ dataNum +'"]').not('ul').attr('value', v).text(n).change().css('color','inherit');
		$(this).parent().animate({bottom:-1*$(this).parent().height() - 10}, 'fast', function(){
			var dataNum = $(this).attr('data');
			$('[data="'+ dataNum +'"]').not('ul').css('border-color', '#ddd');
			$(this).hide();
			$('dd[data="'+ dataNum +'"]').fadeOut('fast');
		});
	});
	listBox.hide();
	listLay.hide();
	return this;
}

var selection; //申明range 对象
if (window.getSelection) {
	//主流的浏览器，包括mozilla，chrome，safari
	selection = window.getSelection();
} else if (document.selection) {
	selection = document.selection.createRange();//IE浏览器下的处理，如果要获取内容，需要在selection 对象上加上text 属性
}

/**
 * @params json opt: 参数
 opt = {
	 url : '',//提交目标地址
	 type : 'post',//默认值
	 dataType : 'text',
	 data : {
		 name : {
			 type : 'text',//单行文本框（text|select|context|images|radio|checkbox|email|number）
			 options : {name:value,...}, //select|radio|checkbox专用
			 class : '',//表单元素的class，有此属性则丢失默认样式
			 placeholder : '',//表单元素的placeholder属性
			 required : '0',//必填字段
			 test : ''//正则验证，如^\d+$
		 },
		 ......
	 },
	 success : function(d){
		 //Ajax提交成功返回
	 }，
	 error : function(d){
		 //Ajax提交失败返回
	 }
 }
 */
$.fn.DeanEditor = function(opt){
	//布置表单元素
	if(!opt)return false;
	var $form = $('<form>').attr('name', 'diyform').css({'position':'relative'}).appendTo(this);
	for(var i in opt){
		if(!opt[i].type)opt[i].type = 'text';
		var o = opt[i];
		var attr = {name:i};
		if(o.class)attr.class = o.class;
		if(o.placeholder)attr.placeholder = o.placeholder;
		switch(o.type){
			case 'select':
				var _option = [];
				if(o.placeholder){
					_option.push('<option value="" selected="selected">'+ o.placeholder +'</option>');
				}
				for(var j in o.options){
					_option.push('<option value="'+ j +'">'+ o.options[j] +'</option>');
				}
				var em = $('<select>').attr(attr).html(_option.join(''));
				if(!o.class){
					em.css({'display':'block','line-height':'28px','font-size':'14px','padding':'0 10px','color':'#666','margin':'10px 0'});
				}
				if(o.label){
					em.css({'flex':'3', 'margin':'0'}).attr('id', 'form_' + i);
					var label = $('<label>').html(o.label + '：').attr('for', 'form_' + i).css({'flex':'1', 'line-height':'28px', 'font-size':'14px', 'text-align':'center'});
					em = $('<div class="'+ i +'Box">').css({'display':'flex'}).append(label).append(em);
				}
				break
			case 'radio':
			case 'checkbox':
				var em = $('<div>');
				if(!o.class){
					em.css({'display':'flex','flex-flow': 'row wrap','line-height':'18px','font-size':'14px','padding':'0 8px','color':'#666','margin':'10px 0'});
				}else{
					em.attr('class', o.class);
				}
				for(var j in o.options){
					$('<label>').html('<input type="'+ o.type +'" name="'+ i +'" value="'+ j +'">' + o.options[j]).appendTo(em);
				}
				if(o.label){
					em.css({'flex':'3', 'margin':'0'});
					var label = $('<div>').html(o.label + '：').css({'flex':'1', 'line-height':'28px', 'font-size':'14px', 'text-align':'center'});
					em = $('<div class="'+ i +'Box">').css({'display':'flex'}).append(label).append(em);
				}
				break;
			case 'context':
				attr.contenteditable = true;
				if(o.placeholder)delete attr.placeholder;
				var em = $('<div>').attr(attr);
				if(!o.class){
					em.css({'width':'100%','height':'200px','box-sizing':'border-box','line-height':'22px','font-size':'14px','padding':'5px','color':'#666','border':'solid 1px #ddd','border-top':'none','background':'#fff'});
					em.find('p').css({'margin':0, 'padding':0});
				}
				if(o.placeholder){
					em.html(o.placeholder).css({'color':'#999'}).attr('placeholder', o.placeholder);
					em.focusin(function(){
						if($(this).html() == $(this).attr('placeholder')){
							$(this).css('color', '#444').empty();
						}
					});
					em.focusout(function(){
						if($(this).text() == ''){
							$(this).css('color', '#999').html($(this).attr('placeholder'));
						}
					});
					em.keydown(function(event){
						if(event.keyCode==13 || event.charCode == 13){
							if($(this).find('p').size() == 0){
								$(this).find('br').remove();
								var sel = selection;
								sel.selectAllChildren(this);
								sel.getRangeAt(0).surroundContents($('<p>')[0]);
								sel.collapseToEnd();
								sel.modify('move', 'left', 'character');
								sel.modify('move', 'right', 'character');
								return false;
							}
						}
					});
				}
				break;
			case 'images':
				var w = this.width();
				var em = $('<div>').html('+').attr('name', i);
				if(!o.class){
					em.css({'width':w*0.25, 'height':w*0.25, 'border':'solid 1px #ddd', 'background':'#f3f3f3', 'line-height':w*0.25 + 'px', 'font-size':w*0.12+'px', 'color':'#ddd','text-align':'center','overflow':'hidden'});
				}else{
					em.attr('class', o.class);
				}
				if(o.url){

				}

				if(o.label){
					var div = $('<div>').css({'flex':'3', 'margin':'0'}).append(em);
					var label = $('<div>').html(o.label + '：').css({'flex':'1', 'line-height':'28px', 'font-size':'14px', 'text-align':'center'});
					em = $('<div class="'+ i +'Box">').css({'display':'flex'}).append(label).append(div);
				}
				break;
			default :
				attr.type = o.type;
				var em = $('<input>').attr(attr);
				if(!o.class){
					em.css({'display':'block','line-height':'28px','font-size':'14px','padding':'0 8px','color':'#666','border':'solid 1px #ddd','background':'#fff','margin':'10px 0','box-sizing':'border-box','width':'100%'});
				}
				if(o.label){
					em.css({'flex':'3', 'margin':'0'}).attr('id', 'form_' + i);
					var label = $('<label>').html(o.label + '：').attr('for', 'form_' + i).css({'flex':'1', 'line-height':'28px', 'font-size':'14px', 'text-align':'center'});
					em = $('<div class="'+ i +'Box">').css({'display':'flex'}).append(label).append(em);
				}
		}
		$form.append(em);
	}
};

function updateImg(path){
	if(!path || typeof(path) == 'function'){
		if(!win.defaultPics){
			ajax('home/index/getDefaultHeadPics', function(d){
				win.defaultPics = d;
				path();
				$('img').each(function(){
					var path = this.src;
					if(!this.hasAttribute('onerror')){
						if(win.defaultPics)
							var _path = win.defaultPics[Math.floor(Math.random()*9)].path;
						else
							var _path = 'images/personaldata_eadportrait_icon@2x.png';
						$(this).on('error', function(){
							this.src = _path;
						});
					}
				});
			});
		}else{
			path();
			$('img').each(function(){
				var path = this.src;
				if(!this.hasAttribute('onerror')){
					if(win.defaultPics)
						var _path = win.defaultPics[Math.floor(Math.random()*9)].path;
					else
						var _path = 'images/personaldata_eadportrait_icon@2x.png';
					$(this).on('error', function(){
						this.src = _path;
					});
				}
			});
		}
	}else{
		var _path = path.replace(/^(.+?)_\d+x\d+(\..+)$/, '$1$2');
		return _path;
	}
}

//分享
function share(title, desc, link, imgUrl,success, fail){
	if(wechat && wechat.onMenuShareTimeline){
		wechat.onMenuShareTimeline({
			title: title, // 分享标题
			link: link, // 分享链接
			imgUrl: imgUrl,
            success: function () {
                success(0);
            }, // 成功分享的回调函数
            fail: fail, // 分享是的回调函数
		});
		wechat.onMenuShareAppMessage({
			title : title,
			desc : desc,
			link : link,
			imgUrl : imgUrl,
            success: function() {
                success(1);
            }, // 成功分享的回调函数
            fail: fail, // 分享是的回调函数
		});
		wechat.onMenuShareQQ({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl,
            success: function () {
                success(4);
            }, // 成功分享的回调函数
            fail: fail, // 分享是的回调函数
		});
		wechat.onMenuShareWeibo({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl,
            success: function () {
                success(5);
            }, // 成功分享的回调函数
            fail: fail, // 分享是的回调函数
		});
		wechat.onMenuShareQZone({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl,
            success: function() {
                success(6);
            }, // 成功分享的回调函数
            fail: fail, // 分享是的回调函数
		});
	}
	if(win.get.android == 1){
		win.shareData = {
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl
		};
	}
}

//消息模块
/**
 * 自定义弹窗
 * @param msg 消息文本
 * @param fn 回调函数
 * @param style 样式(success|error)
 * @param sec 持续显示时间(success默认0, error默认9) 如果大于等于9则为固定弹窗,点击关闭按钮才能关闭
 */
$.alert = function(msg, fn, style, sec){
	style = style||'success';
	if(typeof(fn) == 'string'){
		style = fn;
	}
	if(!sec){
		if(style == 'error' || style == 'puncherror'){
			sec = 9;
			// sec = 0;
		}else{
			sec = 0;
		}
	}
	var box = $('<div>').addClass('resourceBox page_' + page.names[page.num] + ' ' + style).attr('id', 'alertBox');
	box.html('<div class="context">' + msg + '</div>');
	box.appendTo('body');
	var h = win.width / 360 * 100;
	box.css({'opacity':1, 'margin-top':-1 * (box.height()+h)/2});
	if(sec >= 9){
		var alertBoxLay = $('<div>').addClass('alertBoxLay').appendTo('body');
		$('<a>').attr('href', 'javascript:void(0);').addClass('closed').appendTo(box).text('我知道了');
		$('#alertBox a.closed, .alertBoxLay').click(function(){
			box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
			alertBoxLay.css('opacity', 0);
			setTimeout(function(){
				box.remove();
				alertBoxLay.remove();
				if(typeof(fn) == 'function')fn();
			}, 500);
		});
	}else{
		setTimeout(function(){
			box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
			setTimeout(function(){
				box.remove();
				if(typeof(fn) == 'function')fn();
			}, 500);
		}, 1000 + sec * 1000);
	}
}

/**
 * 自定义对话框
 * @param msg 消息文本
 * @param fn 回调函数
 * @param is_lock 是否锁定
 * @param classname 自定义样式
 */
$.dialog = function(msg, fn, is_lock, classname){
    is_lock = is_lock || true;
    if(typeof(fn) != 'function')return;
    classname = classname||'';
    var box = $('<div>').addClass('resourceBox page_' + page.names[page.num] + ' ' + classname).attr('id', 'dialogBox');
    var sb = $('<div>').addClass('sbox').appendTo(box);
    sb.html('<div class="context">' + msg + '</div>');
    box.appendTo('body');
    var h = win.width / 360 * 100;
    box.css({'opacity':1, 'margin-top':-1 * (box.height()+h)/2});
    if(is_lock){
        var dialogBoxLay = $('<div>').addClass('dialogBoxLay').appendTo('body');
    }
    var btns = $('<div>').addClass('btns').appendTo(sb);
    $('<button>').addClass('closeBtn').appendTo(btns).text('否');
    var agree = $('<button>').addClass('agree').appendTo(btns).text('是');
    agree.click(function(){
        if(fn() !== false){
            box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
            if(is_lock)dialogBoxLay.css('opacity', 0);
            setTimeout(function(){
                box.remove();
                if(is_lock)dialogBoxLay.remove();
            }, 500);
        }
    });
    $('#dialogBox button.closeBtn, .dialogBoxLay, .clearpsd, .noticeid').click(function(){
        box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
        dialogBoxLay.css('opacity', 0);
        setTimeout(function(){
            box.remove();
            dialogBoxLay.remove();
        }, 500);
    });
};

var Yami = {
    platform: function () {
        var ua = navigator.userAgent.toLowerCase();
        if (ua.indexOf('yami') !== -1 && ua.indexOf('android') !== -1) {
            return 'android';
        } else {
            return 'web';
        }
    }
    ,
    successShare: function () {},
    failShare: function() {},
    cancelShare: function () {},
    share: function (param) {
        var successCallback = param.success || function () {};
        var failCallback = param.fail || function () {};
        var cancelCallback = param.cancel || function () {};
        this.successShare = successCallback;
        this.failShare = failCallback;
        this.cancelShare = cancelCallback;
        console.log(this.successShare);
        window.android && window.android.showshare && window.android.showshare(JSON.stringify({
            title: param.title || '',
            desc: param.desc || '',
            imgUrl: param.imgUrl || '',
            link: param.link || '',
            platform: param.platform || 0
        }));
    }
};