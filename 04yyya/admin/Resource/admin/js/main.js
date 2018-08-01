//全选
function clickAll(e){
	$(e).parents('form').find(':checkbox').prop('checked',e.checked);
}

// 删除/批量删除
function datasDelete(){
	var $that = $('.am-form');
	var actionLink = $that.attr('action');
	var datas = 'method=delete&'+$that.serialize();
	$.ajax({
		type: "POST",
		url: actionLink,
		data: datas,
		async: false,
		dataType: "json",
		success: function(data) {
			if(data.status == 1){
				$that.find(':checkbox').prop('checked',false);
				updateAlert(data.info,'am-alert-success');
			}else{
				updateAlert(data.info,'am-alert-danger');
			}
			setTimeout(function(){
				$('#top-alert').find('button').click(); // 隐藏提示框
				if(data.status == 1){ // 操作数据库成功
					if (data.url) {
						location.href=data.url;
					}else{
						location.reload();
					}
				}
			},1500);
		}
	});
}

//页面跳转
function jump(url, opt){
	var href = location.href.split('?')[0];
	var params = location.href.split('?')[1];
	if(params){
		var arr = params.split('&');
		params = {};
		for(var i in arr){
			var ar = arr[i].split('=');
			params[ar[0]] = ar[1];
		}
	}else{
		params = {};
	}

	if(typeof(url) == 'string'){
		var urlArr = url.split('/');
		if(urlArr.length == 1){
			href.repalce(/\w+\.html/, urlArr[0] + ".html");
		}else if(urlArr.length == 2){
			href.repalce(/\w+\/\w+\.html/, urlArr[0] + '/' + urlArr[1] + ".html");
		}
	}else{
		opt = url;
	}
	for(var i in opt){
		params[i] = opt[i];
	}
	console.log(params);
	if(params.length > 0){
		var pm = [];
		for(var i in params){
			pm.push(i + '=' + params[i]);
		}
		href += '?' + pm.join('&');
	}
	location.href = href;
}

/*
* 消息提示js
* string text 	：提示文本
* string c 		：提示样式
*	成功：绿色	am-alert-success
*	警告：橙色	am-alert-warning
*	危险：红色	am-alert-danger
*	次要：白色	am-alert-secondary
*/
window.updateAlert = function (text,c) {
	/**顶部警告栏*/
	var content = $('#main');
	var top_alert = $('#top-alert');
	top_alert.find('.close').on('click', function () {
		top_alert.removeClass('block').slideUp(200);
		// content.animate({paddingTop:'-=55'},200);
	});
	text = text||'default';
	c = c||false;
	if ( text!='default' ) {
		top_alert.find('.alert-content').text(text);
		if (top_alert.hasClass('block')) {
		} else {
			top_alert.addClass('block').slideDown(200);
			// content.animate({paddingTop:'+=55'},200);
		}
	} else {
		if (top_alert.hasClass('block')) {
			top_alert.removeClass('block').slideUp(200);
			// content.animate({paddingTop:'-=55'},200);
		}
	}
	if ( c!=false ) {
		top_alert.removeClass('am-alert-success am-alert-warning am-alert-danger am-alert-secondary').addClass(c);
	}
};

/*
* 提示错误
* string text 	：提示文本
*/
function alertMsg(text,c){
	c = c||'am-alert-danger';
	updateAlert(text , c);
	setTimeout(function(){
		$('#top-alert').find('button').click(); // 隐藏提示框
	},1500);
}


/**
 * 去掉文件路径，返回文件名
 * @param {String} str1
 */
function basename(str1)
{
   str2="/"
   
   var s = str1.lastIndexOf(str2);
   if (s==-1) {
   str2="\\"
   var s = str1.lastIndexOf(str2);
   }
   if (s==-1) return str1;
   else{
   	return(str1.substring(s+1,str1.length));
   }
   return ""
}

function pic_upload(em, size, fn, multiple){
    multiple = multiple!=false?true:false;
    if(multiple){
        var fileInput = $('<input>').attr('type', 'file').attr('multiple', multiple);
    }else{
        var fileInput = $('<input>').attr('type', 'file');
    }

	fileInput.change(function(){
		if(this.files.length > 0){
			$(em).addClass('uploading');
			var datas = [];
			var files = this.files;
			if(size[1] == 0)
				var cvs = $('<canvas>').width(size[0]).attr('width', size[0]);
			else
				var cvs = $('<canvas>').width(size[0]).height(size[1]).attr('width', size[0]).attr('height', size[1]);
			for(var i=0; i<files.length; i++){
				var file = files[i];
				var url = window.URL.createObjectURL(file);
				var img = $('<img/>').attr('src', url);
				img.load(function(){
					var imgWidth = this.width;
					var imgHeight = this.height;
					var imgR = imgWidth / imgHeight;
					var r = size[0] / imgWidth;
					var height = size[0] / imgWidth * imgHeight;
					var context = cvs[0].getContext('2d');
					if(size[1] == 0){
						cvs.height(height).attr('height', height);
						//居中裁剪
						context.drawImage(this, 0, 0, size[0], height);
					} else {
						//居中裁剪
						if(imgR > size[0] / size[1]){
							var x = (size[0] - imgWidth * r) / 2;
							var y = 0;
							var h = size[1];
							var w = h * imgR;
						}else{
							var x = 0;
							var y = (size[1] - imgHeight * r) / 2;
							var w = size[0];
							var h = w / imgR;
						}
						context.drawImage(this, x, y, w, h);
					}
					var data = cvs[0].toDataURL('image/jpeg');
					data = data.replace('data:image/jpeg;base64,', '');
					datas.push(data);
				});
			}
			window.ppp = window.setInterval(function(){
				if(datas.length == files.length){
					window.clearInterval(window.ppp);
					var arr = [];
					for(var j in datas){
						arr.push('file[]='+encodeURIComponent(datas[j]));
					}
					datas = arr.join('&');
					$.ajax({
						url:'ajaxUpload.html',
						type : 'POST',
						contentType : "application/x-www-form-urlencoded; charset=utf-8",
						dataType : "json",
						data : datas,
						complete : function(d){
							$(em).removeClass('uploading');
						},
						success : function(d){
							if(!d.info){
								var sucNum = 0;
								var err = [];
								var file = [];
								for(var i in d){
									if(d[i].status == 1){
										sucNum ++;
										file.push(d[i].info);
									}else{
										err.push(d[i].info);
									}
								}
								if(sucNum > 0)alert(sucNum + ' 张图片上传成功!');
								if(err.length > 0){
									alert(err.length + " 张上传失败!\n" + err.join("\n"));
								}
								if(sucNum > 0){
									if(typeof(fn) == 'function')fn(file);
								}
							}else
								alert(d.info);
						}
					});
				}
			}, 200);
		}
	});
	fileInput.click();
}

function png_upload(em, fn, multiple){
	multiple = multiple!=false?true:false;
	if(multiple){
		var fileInput = $('<input>').attr('type', 'file').attr('multiple', multiple);
	}else{
		var fileInput = $('<input>').attr('type', 'file');
	}

	fileInput.change(function(){
		if(this.files.length > 0){
			$(em).addClass('uploading');
			var datas = [];
			var files = this.files;
			var cvs = $('<canvas>');
			for(var i=0; i<files.length; i++){
				var file = files[i];
				var url = window.URL.createObjectURL(file);
				var img = $('<img/>').attr('src', url);
				img.load(function(){
					cvs.width(this.width).attr('width', this.width);
					cvs.height(this.height).attr('height', this.height);
					cvs[0].getContext('2d').drawImage(this, 0, 0, this.width, this.height);
					var data = cvs[0].toDataURL('image/png');
					data = data.replace('data:image/png;base64,', '');
					datas.push(data);
				});
			}
			window.ppp = window.setInterval(function(){
				if(datas.length == files.length){
					window.clearInterval(window.ppp);
					var arr = [];
					for(var j in datas){
						arr.push('file[]='+encodeURIComponent(datas[j]));
					}
					datas = arr.join('&');
					datas += '&type=png';
					$.ajax({
						url:'ajaxUpload.html',
						type : 'POST',
						contentType : "application/x-www-form-urlencoded; charset=utf-8",
						dataType : "json",
						data : datas,
						complete : function(d){
							$(em).removeClass('uploading');
						},
						success : function(d){
							if(!d.info){
								var sucNum = 0;
								var err = [];
								var file = [];
								for(var i in d){
									if(d[i].status == 1){
										sucNum ++;
										file.push(d[i].info);
									}else{
										err.push(d[i].info);
									}
								}
								if(sucNum > 0)alert(sucNum + ' 张图片上传成功!');
								if(err.length > 0){
									alert(err.length + " 张上传失败!\n" + err.join("\n"));
								}
								if(sucNum > 0){
									if(typeof(fn) == 'function')fn(file);
								}
							}else
								alert(d.info);
						}
					});
				}
			}, 200);
		}
	});
	fileInput.click();
}

//放大图片
function imgEnlarge(em){
	var imgBox = $('<div>').addClass('enlarge').appendTo('body').html('<i></i>');
	$('<img>').attr('src', $(em).attr('src')).appendTo(imgBox);
	imgBox.show().click(function(){
		$(this).remove();
	});
}

//菜单转义函数
function menuStrToBase(str){
	str = str.replace(/:/g, '[_maohao_]');
	str = str.replace(/,/g, '，');
	str = str.replace(/@/g, '[_aite_]');
	return str;
}

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

$('.wechatToolsBox button').on('click', function(){
	$(this).html('<i class="am-icon-spinner am-icon-spin"></i> 同步中..').attr('disabled', 'disabled');
	$.get('material.html', function(d){
		$('.wechatToolsBox button').text('同步图文素材').removeAttr('disabled');
		if(d.status == 1){
			alert('同步图文素材 ' + d.info.sum + ' 条(其中文章 '+ d.info.count +' 篇)!');
			window.location.reload();
		}else{
			alert(d.info);
		}
	});
});

$('.wechatToolsBox select').on('change', function(){
	$.ajax({
		url : 'change.html',
		dataType : 'json',
		data : {channel : $(this).val()},
		success: function(d){
			if(d.status == 1){
				alert(d.info);
				window.location.reload();
			}else{
				alert(d.info);
			}
		}
	});
});

//封装鼠标拖动事件
$.fn.drag = function(opt){
	var parentEm = $(opt.parent||document.body);
	var startFun = opt.start||{};
	var moveFun = opt.move||{};
	var endFun = opt.end||{};
	var outFun = opt.moveout||{};
	var isMoving = false;
	var pos = [0,0];
	var em = this;
	//鼠标按下
	em.on('mousedown', function(event){
		event.preventDefault();
		isMoving = true;
		pos = [event.pageX, event.pageY];
		if(typeof startFun == 'function')startFun(em, pos);
	});
	//鼠标移动
	parentEm.on('mousemove', function(event){
		if(isMoving){
			event.preventDefault();
			var left = event.pageX - pos[0] + em.position().left;
			var top = event.pageY - pos[1] + em.position().top;
			pos = [event.pageX, event.pageY];
			if(outFun === false){
				if(left >= 0 && left <= parentEm.width() - em.width()){
					em.css('left', left);
				}
				if(top >= 0 && top <= parentEm.height() - em.height()){
					em.css('top', top);
				}
			}else{
				em.css('left', left);
				em.css('top', top);
			}
			if(typeof moveFun == 'function')moveFun(em, [left, top]);
		}
	});
	//鼠标弹起
	em.on('mouseup', function(event){
		isMoving = false;
		pos = [0, 0];
		var left = em.position().left;
		var top = em.position().top;
		if(typeof endFun == 'function')endFun(em, [left, top]);
		if(typeof outFun == 'function'){
			if(left < -em.width() || left > parentEm.width() || top < -em.height() || top > parentEm.height())
			outFun(em);
		}
	});
	return this;
};

