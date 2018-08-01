var webEditorObject = {
	id : 0,
	name : 'color',
	range : null,
	css : function(css, value){
		if(webEditorObject.range == null)
			webEditorObject.range = selection.getRangeAt(0);
		var em = $('<span>');
		if(typeof(css) == 'string')
			em.css(css, value);
		else
			em.css(css);
		webEditorObject.range.surroundContents(em.get(0));
		webEditorObject.range = null;
	},
	setCss : function(css, value){
		if(webEditorObject.range == null)
			webEditorObject.range = selection.getRangeAt(0);
		$('.page_webEditor .context p').each(function(){
			if(selection.containsNode(this, true)){
				var em = $(this);
				if(typeof(css) == 'string')
					em.css(css, value);
				else
					em.css(css);
			}
		});
	},
	setColor : function(color){
		if(color.indexOf('rgb') == -1 && color.indexOf('#') == -1)color = '#'+color;
		this.css(this.name, color);
		if(localStorage){
			var colors = [];
			if(localStorage.latelyColors)colors = localStorage.latelyColors.split('|');
			colors.push(color);
			$('.page_webEditor.tools .setColor .lately button:not(.empty)').remove();
			for(var i in colors){
				$('<button>').css('background-color',colors[i]).appendTo('.page_webEditor.tools .setColor .lately').on('click', function(){
					var color = $(this).css('background-color');
					webEditorObject.css(webEditorObject.name, color);
					$('.page_webEditor.tools .setColor').slideUp('fast');
				});
			}
			localStorage.latelyColors = colors.join('|');
		}
		$('.page_webEditor.tools .setColor').slideUp('fast');
	},
	removeCss : function(css, replaceWith){
		var thisReturn = false;
		if(typeof(css) != 'string'){
			for(var i in css){
				if(typeof(css[i]) == 'string'){
					if(!thisReturn && this.removeCss(css[i], replaceWith))thisReturn = true;
				}
			}
		}else{
			$('.page_webEditor .context span').each(function(){
				if(selection.containsNode(this, true)){
					if($(this).attr('style').indexOf(css) != -1){
						if(replaceWith){
							if(replaceWith === true)
								$(this).replaceWith($(this).html());
							else if(typeof(replaceWith) == 'object')
								$(this).replaceWith(replaceWith);
							else if(typeof(replaceWith) == 'function')
								$(this).replaceWith(replaceWith($(this).html()));
							else if(typeof(replaceWith) == 'string')
								$(this).attr('style', replaceWith);
						}else
							$(this).removeClass(css);
						thisReturn = true;
					}
				}
			});
		}
		return thisReturn;
	}
};

(function(){
	if(!member){
		script.load('member');
		return;
	}

	$('.page_webEditor.resources .power').click(function(){
		if($('.page_webEditor.resources').hasClass('hide')){
			$('.page_webEditor.resources').removeClass('hide');
			$('.page_webEditor .footerBlank').height('15rem');
		}else{
			$('.page_webEditor.resources').addClass('hide');
			$('.page_webEditor .footerBlank').height('1.5rem');
		}
	});
	ajax('daren/article/catlist', function(d){
		var code = '';
		for(var i in d){
			code += '<option value="'+ d[i].id +'">'+ d[i].name +'</option>';
		}
		$('.page_webEditor .editerBox .category').append(code);
	});
	$('.editerBox').DeanEditor({
		'title' : {
			type : 'text',//单行文本框（text|select|context|images|radio|checkbox|email|number）
			placeholder : '请在这里输入标题',//表单元素的placeholder属性
			class : 'title'
		},
		'author' : {
			type : 'text',//单行文本框（text|select|context|images|radio|checkbox|email|number）
			placeholder : '请输入作者(忽略则为原创)',//表单元素的placeholder属性
			class : 'author'
		},
		'category_id' : {
			type : 'select',
			placeholder : '文章分类',
			class : 'category'
		},
		'context' : {
			type : 'context',
			class : 'context',
			placeholder : '从这里开始写正文'
		},
		'pic_id' : {
			label : '封面',
			type : 'images',
			class : 'picture'
		}
	});

	//获取编辑信息
	if(win.get.article_id){
		ajax('goods/article/getDetail', {'article_id':win.get.article_id}, function(d){
			if(!d.info){
				webEditorObject.id = win.get.article_id;
				$('.page_webEditor .editerBox .title').val(d.title);
				$('.page_webEditor .editerBox .author').val(d.author);
				$('.page_webEditor .editerBox .category').val(d.category_id);
				$('.page_webEditor .editerBox .context').html(d.content).removeAttr('style');
				$('.page_webEditor.resources .picture').attr('value', d.pic_id);
				$('.page_webEditor.resources .picture').html('<img src="'+ d.path +'">');
			}else{
				$.alert(d.info, 'error');
			}
		});
	}
	
	$('.pic_idBox').prependTo('.page_webEditor.resources');
	//字体大小列表
	for(var i=0; i<8; i++){
		var str = i*2 + 10 + '';
		str = str.substr(0,1)+'.'+str.substr(1) + 'rem';
		$('<button>').css('font-size',str).html(str).click(function(){
			var size = $(this).html();
			webEditorObject.css('font-size','20px');
			$('.page_webEditor.tools .setFontsize').slideUp('fast');
		}).appendTo('.page_webEditor.tools .setFontsize');
	}
	//字体颜色
	var colorData = ['ffffff','ffd7d5','ffdaa9','fffed5','d4fa00','73fcd6','a5c8ff','ffacd5','ff7faa','d6d6d6','ffacaa','ffb995','fffb00','73fa79','00fcff','78acfe','d84fa9','ff4f79','b2b2b2','d7aba9','ff6827','ffda51','00d100','00d5ff','0080ff','ac39ff','ff2941','888888','7a4442','ff4c00','ffa900','3da742','3daad6','0052ff','7a4fd6','d92142','000000','7b0c00','ff4c41','d6a841','407600','007aaa','021eaa','797baa','ab1942'];
	for(var i in colorData){
		$('<button>').css('background-color','#'+colorData[i]).appendTo('.page_webEditor.tools .setColor .choose').click(function(){
			var str = $(this).css('background-color');
			webEditorObject.setColor(str);
		});
	}
	if(window.localStorage && localStorage.latelyColors){
		var colors = localStorage.latelyColors.split('|');
		for(var i in colors){
			$('<button>').css('background-color',colors[i]).appendTo('.page_webEditor.tools .setColor .lately').on('click', function(){
				var color = $(this).css('background-color');
				webEditorObject.css(webEditorObject.name, color);
				$('.page_webEditor.tools .setColor').slideUp('fast');
			});
		}
	}
	$('.page_webEditor.tools .setColor .empty').click(function(){
		webEditorObject.removeCss(webEditorObject.name, true)
	});
	$('.page_webEditor.tools .inputColor input[type="text"]').on('focusout keyup', function(){
		$(this).prev('button').css('background-color', '#'+$(this).val());
	});
	$('.page_webEditor.tools .inputColor input[type="button"]').on('click', function(){
		var color = $(this).prev('input').val();
		webEditorObject.setColor(color);
	});
	
	$('.page_webEditor .context').on('focusin', function(){
		$('.page_webEditor.tools .laybox').slideUp('fast');
	});
	
	// 加粗surroundContent
	$('.page_webEditor.tools .bold').click(function(){
		webEditorObject.css('font-weight','bold');
	});

	// 斜体
	$('.page_webEditor.tools .italic').click(function(){
		webEditorObject.css('font-style', 'italic');
	});

	// 下划线
	$('.page_webEditor.tools .underline').click(function(){
		webEditorObject.css('text-decoration','underline');
	});

	// 字体大小
	$('.page_webEditor.tools .fontsize').click(function(){
		if($('.page_webEditor.tools .setFontsize:visible').size() > 0)
			$('.page_webEditor.tools .setFontsize').slideUp('fast');
		else
			$('.page_webEditor.tools .setFontsize').slideDown('fast');
	});
	//字体颜色
	$('.page_webEditor.tools .color').click(function(){
		if($('.page_webEditor.tools .setColor:visible').size() > 0)
			$('.page_webEditor.tools .setColor').slideUp('fast');
		else{
			webEditorObject.name = 'color';
			webEditorObject.range = selection.getRangeAt(0);
			$('.page_webEditor.tools .setColor').slideDown('fast');
		}
	});
	
	//字体背景色
	$('.page_webEditor.tools .bgcolor').click(function(){
		if($('.page_webEditor.tools .setColor:visible').size() > 0)
			$('.page_webEditor.tools .setColor').slideUp('fast');
		else{
			webEditorObject.name = 'background-color';
			webEditorObject.range = selection.getRangeAt(0);
			$('.page_webEditor.tools .setColor').slideDown('fast');
		}
	});
	
	// 内缩进
	$('.page_webEditor.tools .indent').click(function(){
		//webEditorObject.css('text-indent', '2em');
		webEditorObject.setCss('text-indent', '2em');
	});
	
	// 左对齐
	$('.page_webEditor.tools .justifyleft').click(function(){
		webEditorObject.setCss('text-align', 'left');
	});
	// 右对齐
	$('.page_webEditor.tools .justifyright').click(function(){
		webEditorObject.setCss('text-align', 'right');
	});
	// 居中对齐
	$('.page_webEditor.tools .justifycenter').click(function(){
		webEditorObject.setCss('text-align', 'center');
	});
	//行高
	$('.page_webEditor.tools .lineheight').click(function(){
		if($('.page_webEditor.tools .setLineheight:visible').size() > 0)
			$('.page_webEditor.tools .setLineheight').slideUp('fast');
		else
			$('.page_webEditor.tools .setLineheight').slideDown('fast');
	});
	$('.page_webEditor.tools .setLineheight button').click(function(){
		var em = $(this).text();
		webEditorObject.setCss('line-height', em);
	});
	//插入图片
	$('.page_webEditor.resources .resources .images').click(function(){
		webEditorObject.range = selection.getRangeAt(0);
		page.jump('myPictures', {'backFun':function(pics){
			for(var i in pics){
				webEditorObject.range.insertNode($('<img>').attr('src', pics[i].path).attr('pic_id', pics[i].pic_id)[0]);
			}
		}});
	});
	//选择封面图
	$('.page_webEditor.resources .picture[name="pic_id"]').click(function(){
		page.jump('myPictures', {'count':1, 'size':[640,420], 'backFun':function(pics){
			var pic = pics[0];
			$('.page_webEditor.resources .picture[name="pic_id"]').attr('value', pic.pic_id).html('<img src="'+ pic.path +'">');
		}});
	});
	//插入链接
	$('.page_webEditor.resources .resources .linkline').click(function(){
		webEditorObject.range = selection.getRangeAt(0);
		var linkline = window.prompt('链接网址', 'http://...');
		if(linkline){
			var em = $('<a>').attr('href', linkline);
			webEditorObject.range.surroundContents(em[0]);
		}
	});
	//插入视频
	$('.page_webEditor.resources .resources .video').click(function(){
		webEditorObject.range = selection.getRangeAt(0);
		var video = window.prompt('视频代码', 'HTML代码...');
		if(video){
			var em = $('<p>').html(video);
			webEditorObject.range.insertNode(em[0]);
		}
	});

	$('.page_webEditor .complete').click(function(){
		var data = {};
		if(webEditorObject.id > 0){
			data.id = webEditorObject.id;
		}
		data.title = $('.page_webEditor .editerBox .title').val();
		data.author = $('.page_webEditor .editerBox .author').val();
		data.category_id = $('.page_webEditor .editerBox .category').val();
		data.context = $('.page_webEditor .editerBox .context').html();
		data.pic_id = $('.page_webEditor .pic_idBox [name="pic_id"]').attr('value');
		if(data.pic_id == undefined)data.pic_id = '';

		ajax('daren/article/save', data, function(d){
			if(d.status == 1){
				webEditorObject.id = d.info.id;
				$.alert('保存成功');
			}
		});
	});
})();