
/********************************** 预设函数（开始） ***********************************/
//取得视窗高
function windowHeight(){
	var de = document.documentElement;
	return self.innerHeight || ( de && de.clientHeight ) || document.body.clientHeight;
}

//取得视窗宽
function windowWidth(){
	var de = document.documentElement;
	return self.innerWidth || ( de && de.clientWidth) || document.body.clientWidth;
}

//取得垂直滚动条的位置
function scrollY(){
	var de = document.documentElement;
	return self.pageYOffset || (de && de.scrollTop) || document.body.scrollTop;
}

//显示黑幕
function loadOverlay(){
	if(document.getElementById("overlay"))$("#overlay").remove();
	$("body").prepend("<div id='overlay'></div>");
	$("#overlay").height((windowHeight() > $("body").height()) ? windowHeight() : $("body").height());
}

//清除黑幕
function cleanOverlay(){
	$("#overlay").remove();
}

function urlto(){
	if(functions.var2)cleanOverlay();
	if(typeof(functions.var1) == "function"){
		eval($('#message_input') ? functions.var1($('#message_input').val()) : functions.var1());
	}else{
		if(functions.var1 == "-1"){
			window.history.back();
		}else if(functions.var1 == ""){
		}else if(functions.var1 != "undefined"){
			document.location.href = functions.var1;
		}
	}
	$("#message").remove();
}

/*****************************************************
函数功能：客户触发消息框。
创建时间：2009年5月18日
修改时间：2009年5月18日
创建人员：李俊杰
参数说明：type指类型，overlay指是否显示黑罩
　　　　　text显示的字幕，myURL指转向的网址或运行函数
*****************************************************/
function message(type,overlay,text,myURL){
	if(document.getElementById("message"))$("#message").remove();
	switch(type){
		case 1:
			functions.var1 = myURL;
			functions.var2 = overlay;
			if(text == "")urlto();
			if(overlay)loadOverlay();
			$("body").append("<div id='message' style='width:300px;'><div class='inTitle'><b>友情提示:</b></div><div class='inside'></div></div>");
			$("#message > .inside").append(text);
			$("#message > .inside").after("<br /><center><span class='btn' onclick=\"urlto();\">明 白</span></center>");
			$("#message").css("top",(windowHeight() - $("#message").height()) / 2 + scrollY());
			$("#message").css("left",(windowWidth() - $("#message").width()) / 2);
		break;
		case 2:
			functions.var1 = myURL;
			functions.var2 = overlay;
			if(overlay)loadOverlay();
			$("body").append("<div id='message' style='width:300px;'><div class='inTitle'><b>友情提示:</b></div><div class='inside'></div></div>");
			$("#message > .inside").append(text);
			$("#message > .inside").after("<br /><center><span class='btn' onclick=\"urlto();\">是 的</span>&nbsp;&nbsp;<span class='btn' onclick='if("+ overlay +")cleanOverlay();$(\"#message\").remove();'>不 用</span></center>");
			$("#message").css("top",(windowHeight() - $("#message").height()) / 2 + scrollY());
			$("#message").css("left",(windowWidth() - $("#message").width()) / 2);
		break;
		case 3:
			functions.var1 = myURL;
			functions.var2 = overlay;
			if(overlay)loadOverlay();
			$("body").append("<div id='message' style='width:300px;'><div class='inTitle'>◆"+ text +"：</div><div class='inside'></div></div>");
			$("#message > .inside").append("<input id='message_input' type='text' class='input' />");
			$("#message > .inside").after("<br /><center><span class='btn' onclick=\"urlto();\">提 交</span>&nbsp;&nbsp;<span class='btn' onclick='if("+ overlay +")cleanOverlay();$(\"#message\").remove();'>取 消</span></center>");
			$("#message").css("top",(windowHeight() - $("#message").height()) / 2 + scrollY());
			$("#message").css("left",(windowWidth() - $("#message").width()) / 2);
		break;
	}
}

var selection; //申明range 对象
if (window.getSelection) {
	//主流的浏览器，包括mozilla，chrome，safari
	selection = window.getSelection();
} else if (document.selection) {
	selection = document.selection.createRange();//IE浏览器下的处理，如果要获取内容，需要在selection 对象上加上text 属性
}

$(function(){
	(function(colors){
		if(colors){
			colors = colors.split('|');
			$('.webediter .color_list .list').empty();
			for(var i in colors){
				$('<button type="button">').css('background-color', colors[i]).appendTo('.webediter .color_list .list').on('click', function(){
					var color = $(this).css('background-color');
					webEditorObject.css('color', color);
				});
			}
		}
	})(localStorage.latelyColors);
});

var webEditorObject = {
	id : 0,
	num : 0,
	name : 'color',
	range : null,
	style : {},
	css : function(css, value){
		var that = this;
		//判断开始节点和结束节点是否在同一节点
		webEditorObject.range = selection.getRangeAt(0);
		var bol = false;
		$('.webediter #text span').each(function(){
			//判断该节点是否全部被选中
			if(selection.containsNode(this, true) && $(this).text() == webEditorObject.range.toString()){
				var cls = $(this).attr('class').match(/style\-\d+/);
				//判断是否已经设置了该样式
				if(that.style[cls][css] && that.style[cls][css] == value){
					$(this).css(css, '');
					delete that.style[cls][css];
				}else{
					$(this).css(css, value);
					that.style[cls][css] = value;
				}
				bol = true;
			}
		});
		if(!bol){
			that.num ++;
			var cls = 'style-'+ that.num;
			var em = $('<span>').addClass(cls);
			em.css(css, value);
			if(!that.style[cls])that.style[cls] = {};
			that.style[cls][css] = value;
			webEditorObject.range.surroundContents(em.get(0));
		}
		webEditorObject.range == null;
	},
	setCss : function(css, value){
		webEditorObject.range = selection.getRangeAt(0);
		var that = this;
		$('.webediter #text p').each(function() {
			if(selection.containsNode(this, true)){
				if($(this).attr('class')){
					var cls = $(this).attr('class').match(/style\-\d+/);
				}
				if (cls && that.style[cls][css] && that.style[cls][css] == value) {
					$(this).css(css, '');
					delete that.style[cls][css];
				} else {
					if(!cls){
						that.num ++;
						var cls = 'style-'+ that.num;
						$(this).addClass(cls);
						that.style[cls] = {};
					}
					$(this).css(css, value);
					that.style[cls][css] = value;
				}
			}
		});
		webEditorObject.range == null;
	},
	addColor : function(c1, c2, c3){
		color = 'rgb(' + c1 + ',' + c2 + ',' + c3 + ')';
		var colors = ["rgb(51,51,51)", "rgb(153,153,153)", "rgb(255,0,0)", "rgb(255,102,0)", "rgb(255,204,0)", "rgb(51,153,0)", "rgb(0,204,255)", "rgb(0,51,255)", "rgb(255,0,255)"];
		if(localStorage.latelyColors)colors = localStorage.latelyColors.split('|');
		if(colors.indexOf(color) == -1){
			colors.push(color);
			$('.webediter .color_list .list').empty();
			for(var i in colors){
				$('<button type="button">').css('background-color', colors[i]).appendTo('.webediter .color_list .list').on('click', function(){
					var color = $(this).css('background-color');
					webEditorObject.css('color', color);
				});
			}
			localStorage.latelyColors = colors.join('|');
		}
	},
	colorUpdate : function(i, em){
		var color = [];
		$('.color_list .diy em').each(function(){
			color.push(parseInt($(this).text()));
		});
		var n = Math.floor(i/2);
		var i = (i % 2)*2 - 1;
		webEditorObject.p = setInterval(function(){
			if((i == 1 && color[n] < 255) || (i == -1 && color[n] > 0)){
				color[n] += i;
				$('.color_list .diy em').eq(n).text(color[n]);
				$('.color_list .color_point').css('background', 'rgb('+ color.join(',') +')');
				webEditorObject.css('color', 'rgb('+ color.join(',') +')');
			}
		}, 100);
		$(em).on('mouseout mouseup', function(){
			clearInterval(webEditorObject.p);
		});
	}
};

var contentHTML = false;
var writeDiv = $("#text");
var mainpic_num = 0;

//复制转换
// 干掉IE http之类地址自动加链接
try {
	document.execCommand("AutoUrlDetect", false, false);
} catch (e) {}

//$(function(){
//	$("#text").on('paste', function(e) {
//		e.preventDefault();
//		var code = (e.originalEvent || e).clipboardData.getData('text/html');
//		code = code.replace(/\d+(\.\d+){0,1} *px/g, function(match){
//			return Math.round(parseFloat(match) / (750 / 36) * 100) / 100 + 'rem'
//		});
//		if (document.body.createTextRange) {
//			if (document.selection) {
//				textRange = document.selection.createRange();
//			} else if (window.getSelection) {
//				sel = window.getSelection();
//				var range = sel.getRangeAt(0);
//
//				// 创建临时元素，使得TextRange可以移动到正确的位置
//				var tempEl = document.createElement("span");
//				tempEl.innerHTML = "&#FEFF;";
//				range.deleteContents();
//				range.insertNode(tempEl);
//				textRange = document.body.createTextRange();
//				textRange.moveToElementText(tempEl);
//				tempEl.parentNode.removeChild(tempEl);
//			}
//			textRange.htmlText = code;
//			textRange.collapse(false);
//			textRange.select();
//		} else {
//			// Chrome之类浏览器
//			document.execCommand("insertHTML", false, code);
//		}
//	});
//});

function exeCommand(command, value){
	document.execCommand(command, false, value);
}

// 向里缩进
function indent(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('Indent', '');
}

// 向外缩进
function outdent(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('Outdent', '');
}

// 无序列表
function unorderList(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('InsertUnorderedList', '');
}

// 有序列表
function orderList(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('InsertOrderedList', '');
}

//插入链接
function lineto(){
	if(contentHTML)change_write();
	writeDiv.focus();
	URLPath = window.prompt('请输入链接地址：', 'http://');
	if(URLPath)exeCommand("CreateLink", URLPath, false);
}

// 插入图片
function image(){
	if(contentHTML)change_write();
	$("body").append("<div id='message' style='width:300px;'><div class='inTitle'>◆ 插入图片：</div><div class='inside'></div></div>");
	$("#message > .inside").append("<input id='up_choose' name='up_choose' type='radio' checked='checked' />本地上传：<input id='uploadBtn' type='button' value='选择文件' onclick='upload_imgs()'><span id='uploadsrc'></span><br /><input name='up_choose' type='radio' />外部链接：<input id='message_input' type='text' style='width:160px; height:22px;' />");
	$("#message > .inside").after("<br /><br /><center><span class='btn' onclick=\"pic()\">确 定</span>&nbsp;&nbsp;<span class='btn' onclick='$(\"#message\").remove();'>取 消</span></center>");
	$("#message > .inside").after("&nbsp;&nbsp;&nbsp;Width:<input id='message_width' type='text' style='width:60px; height:22px;' />");
	$("#message").css("top",(windowHeight() - $("#message").height()) / 2 + scrollY());
	$("#message").css("left",(windowWidth() - $("#message").width()) / 2);
}

function upload_imgs(){
	pic_upload(this, [640, 0], function(files){
		$('#uploadsrc').html(files[0].path);
	}, false);
}

function pic(){
	if($("#up_choose").attr("checked")){
		var src = $('#uploadsrc').html();
		if(src == '')return;
		$("#text").focus();
		if($("#message_width").val() != '')
			$("#text").append('<img src="' + src + '" width="' + $("#message_width").val() + '"/>');
		else
			$("#text").append('<img src="' + src + '"/>');
		$("#message").remove();
		cleanOverlay();
	}else{
		$("#text").focus();
		$("#text").append("<img src=\"" + $("#message_input").val() + "\" width=" + (($("#message_width").val() != "" && $("#message_width").val() > 0 && $("#message_width").val() < 800) ? $("#message_width").val() : 640) + " height=" + (($("#message_height").val() != "" && $("#message_height").val() > 0) ? $("#message_height").val() : 480) + " />");
		$("#message").remove();
		cleanOverlay();
	};
}

function up_pic(str,size){
	if(str != "false"){
		if($("#up_pics").length > 0){
			$("#mypic").append("<option value='"+ $("#filepath").html() + str +"'>"+ str +"</option>");
			if($("#up_pics").val() != "")$("#up_pics").val($("#up_pics").val() + "|");
			$("#up_pics").val($("#up_pics").val() + str);
			$('#show_pic').attr('src',$("#mypic").val());
		}
		$("#text").append("[img src=\"file/" + str + "\" width=" + (($("#message_width").val() != "" && $("#message_width").val() > 0 && $("#message_width").val() < 800) ? $("#message_width").val() : 640) + " height=" + (($("#message_height").val() != "" && $("#message_height").val() > 0) ? $("#message_height").val() : 480) + " /]");
		$("#message").remove();
		cleanOverlay();
		$("#text").focus();
	}else{
		message(1,true,"上传失败！<br>请确保您的文件小于" + size + "KB，且属于jpg或gif格式。","");
	}
}

function up_mainpic(str,files,size){
	$("#message").remove();
	if(str != "false"){
		$(".mainpic:eq("+ mainpic_num +")").html("<img src='"+ files +"thumb/tb_"+ str +"' />");
		if($("[name=mainpic]").val() != ""){
			var str_ = $("[name=mainpic]").val();
			var mainpics = str_.split('|');
		}else{
			var mainpics = Array(5);
		}
		mainpics[mainpic_num] = str;
		$("[name=mainpic]").val(mainpics.join('|'));
		cleanOverlay();
	}else{
		message(1,true,"上传失败！<br>请确保您的文件小于" + size + "KB，且属于jpg或gif格式。","");
	}
}

// 查看编辑框里的HTML源代码
function change_write(em){
	if (contentHTML){
		$("#textDiv2").hide();
		$("#textDiv1").show();
		$("#text").html($("#htmlText").val());
		writeDiv.focus();
		$(em).removeClass('text');
		contentHTML = false;
	}else{
		$("#textDiv2").show();
		$("#textDiv1").hide();
		$("#htmlText").val($("#text").html());
		$("#htmlText").focus();
		$(em).addClass('text');
		contentHTML = true;
	}
}
