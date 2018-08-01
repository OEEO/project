
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

/*****************************************************
函数功能：在线编辑系统。
创建时间：2009年6月30日
修改时间：2011年3月18日
创建人员：李俊杰
*****************************************************/
var contentHTML = false;
var writeDiv = $("#text");
var mainpic_num = 0;

function exeCommand(command, value){
	document.execCommand(command, false, value);
}

// 加粗
function fontb(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('Bold', '');
}

// 斜体
function fonti(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('Italic', '');
}

// 下划线
function fontu(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('Underline', '');
}

//字体
function FontFamily(a){
	writeDiv.focus();
	exeCommand('FontName', a);
}
//字号
function FontSize(a){
	writeDiv.focus();
	exeCommand('FontSize', a);
}
//字体颜色
function FontColor(a){
	writeDiv.focus();
	exeCommand('ForeColor', a);
}
// 居左
function fontleft(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('JustifyLeft', '');
}

// 居中
function fontcenter(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('JustifyCenter', '');
}

// 居右
function fontright(){
	if(contentHTML)change_write();
	writeDiv.focus();
	exeCommand('JustifyRight', '');
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
	URLPath = window.prompt('请输入链接地址：', '');
	if(URLPath)exeCommand("CreateLink", URLPath, false);
}

// 插入图片
function image(){
	if(contentHTML)change_write();
	$("body").append("<div id='message' style='width:300px;'><div class='inTitle'>◆ 插入图片：</div><div class='inside'></div></div>");
	$("#message > .inside").append("<input id='up_choose' name='up_choose' type='radio' checked='checked' />本地上传：<input id='uploadBtn' type='button' value='选择文件'><span id='uploadsrc'></span><br /><input name='up_choose' type='radio' />外部链接：<input id='message_input' type='text' style='width:190px; height:14px;' />");
	$("#message > .inside").after("<br /><br /><center><span class='btn' onclick=\"pic()\">确 定</span>&nbsp;&nbsp;<span class='btn' onclick='$(\"#message\").remove();'>取 消</span></center>");
	$("#message > .inside").after("&nbsp;&nbsp;&nbsp;Width:<input id='message_width' type='text' style='width:60px; height:14px;' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Height:<input id='message_height' type='text' style='width:60px; height:14px;' />");
	$("#message").css("top",(windowHeight() - $("#message").height()) / 2 + scrollY());
	$("#message").css("left",(windowWidth() - $("#message").width()) / 2);
}

// 插入主图片
function upload_mainpic(n){
	mainpic_num = n;
	loadOverlay();
	$("body").append("<div id='message' style='width:300px;'><div class='inTitle'>◆ 插入主图"+ (n+1) +"：</div><div class='inside'></div></div>");
	$("#message > .inside").append("上传：<iframe id='upfiles' name='upfiles' src='/upfiles.php?type=mainpic' frameborder=0 style='border:1px; margin:0px; padding:0px; width:195px; height:22px; overflow:hidden;'></iframe>");
	$("#message > .inside").after("<br /><br /><center><span class='btn' onclick=\"upfiles.document.forms[0].submit();\">确 定</span>&nbsp;&nbsp;<span class='btn' onclick='$(\"#message\").remove(); cleanOverlay();'>取 消</span></center>");
	$("#message").css("top",(windowHeight() - $("#message").height()) / 2 + scrollY());
	$("#message").css("left",(windowWidth() - $("#message").width()) / 2);
}

function pic(){
	if($("#up_choose").attr("checked")){
		var src = $('#uploadsrc').html();
		if(src == '')return;
		$("#text").focus();
		$("#text").append("[img src=\"" + src + "\" width=" + (($("#message_width").val() != "" && $("#message_width").val() > 0 && $("#message_width").val() < 800) ? $("#message_width").val() : 640) + " height=" + (($("#message_height").val() != "" && $("#message_height").val() > 0) ? $("#message_height").val() : 480) + " /]");
		$("#message").remove();
		cleanOverlay();
	}else{
		$("#text").focus();
		$("#text").append("[img src=\"" + $("#message_input").val() + "\" width=" + (($("#message_width").val() != "" && $("#message_width").val() > 0 && $("#message_width").val() < 800) ? $("#message_width").val() : 640) + " height=" + (($("#message_height").val() != "" && $("#message_height").val() > 0) ? $("#message_height").val() : 480) + " /]");
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
function change_write(){
	if (contentHTML){
		$("#textDiv2").hide();
		$("#textDiv1").show();
		$("#text").html($("#htmlText").text());
		writeDiv.focus();
		document.getElementById("changeText").src = "images/HTML.gif";
		contentHTML = false;
	}else{
		$("#textDiv2").show();
		$("#textDiv1").hide();
		$("#htmlText").text($("#text").html());
		$("#htmlText").focus();
		document.getElementById("changeText").src = "images/TEXT.gif";
		contentHTML = true;
	}
}