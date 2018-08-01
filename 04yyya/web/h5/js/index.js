
var cvs,cvs2,cvs3,context,context2,context3,cvs_width,touch_state = false,nickname,month,day;

var img = {x:0,y:0,w:0,h:0,em:null,size:0,left:0,top:0,r:0};
var touch = {x:0,y:0};

$(function(){
	$('html').attr('style', 'font-size:' + ($(window).width() / 7.5) + 'px');
	$(window).resize(function(){
		$('html').attr('style', 'font-size:' + ($(window).width() / 7.5) + 'px');
	});
	$('.container').height($(window).height());//页面主体的高度
	cvs_width = $('.canvaslay').width();//6.4rem
	$('.makeimg').attr('width', 1200).attr('height', 2138); //最后生成出来图片宽高
	$('.img').attr('width', $(window).width()).attr('height', $(window).height());
	$('.canvas').attr('width', cvs_width).attr('height', cvs_width);
	cvs = $('.img')[0];
	cvs2 = $('.makeimg')[0];//获取canvas
	cvs3 =$('.canvas')[0];
	context = cvs.getContext('2d');
	context2 = cvs2.getContext('2d'); //获取canvas画笔
	context3 = cvs3.getContext('2d'); //获取canvas画笔
	img.left = parseInt($('.canvaslay').css('border-left-width').replace('px', ''));//canvas的left
	img.top = parseInt($('.canvaslay').css('border-top-width').replace('px', ''));//canvas的top
//拖动函数
	$('.canvaslay')[0].addEventListener('touchstart', function(event){
		event.preventDefault();
	}, false);

	$('.canvaslay')[0].addEventListener('touchmove', function(event){
		event.preventDefault();
		var ev = event.touches;
		if(ev.length == 1) {
			if(touch_state){
				img.x += ev[0].pageX - touch.x;
				img.y += ev[0].pageY - touch.y;
				draw();
			}
			touch.x = ev[0].pageX;
			touch.y = ev[0].pageY;
			touch_state = true;
		}
	}, false);

	$('.canvaslay')[0].addEventListener('touchend', function(event){
		event.preventDefault();
		touch_state = false;
	}, false);
//缩小放大函数
	$('.line')[0].addEventListener('touchstart', function(event){
		event.preventDefault();
		var ev = event.touches;
		if(ev.length == 1) {
			if(ev[0].pageX > $(this).offset().left && ev[0].pageX < $(this).offset().left + $(this).width()){
				$(this).children().css('left', ev[0].pageX - $(this).offset().left - ($(this).children().width()));
				img.size = Math.round($(this).children().position().left / ($(this).width() - $(this).children().width()) * 100);
				draw();
			}
		}
	}, false);

	$('.line')[0].addEventListener('touchmove', function(event){
		event.preventDefault();
		var ev = event.touches;
		if(ev.length == 1) {
			if(touch_state){
				var left = ev[0].pageX - $(this).offset().left - ($(this).children().width());
				if(left < 0){
					$(this).children().css('left', 0);
				}else if(left > $(this).width() - $(this).children().width()){
					$(this).children().css('left', $(this).width() - $(this).children().width());
				}else{
					$(this).children().css('left', left);
				}
				img.size = Math.round($(this).children().position().left / ($(this).width() - $(this).children().width()) * 100);
				draw();
			}
			touch.x = ev[0].pageX;
			touch_state = true;
		}
	}, false);

	$('.line')[0].addEventListener('touchend', function(event){
		event.preventDefault();
		touch_state = false;
	}, false);
});
//提交信息
function save(){
	nickname = $('#nickname').val();
	month = Math.abs(parseInt($('#month').val()));
	day = Math.abs(parseInt($('#day').val()));
	if(nickname=='' || month=='' || day==''){
		alert('请将信息填写完整!');
		return;
	}
	if(xingzuo(month, day) == 'no'){
		alert('日期输入错误!');
		return;
	}
	$('.container.index').hide();
	$('.container.upload').fadeIn('fast');
}
//上传函数
function upload(file){
	var url = window.URL.createObjectURL(file);
	img.em = $('<img/>').attr('src', url);
	img.em.load(function(){
		var img_width = this.width; //图片本身宽度
		var img_height = this.height;//图片本身高度
		if(img_width > img_height){
			img.h = cvs_width;
			img.w = cvs_width / img_height * img_width;
		}else{
			img.w = cvs_width;
			img.h = cvs_width / img_width * img_height;
		}
	});
}

function rotating(){
	img.r += 90;
	draw();
}

function draw(){
	context.clearRect(0,0,cvs.width,cvs.height);
	context.save();//保存当前的绘制状态
	switch((img.r / 90) % 4){
		case 0:
			var x = img.x + img.left;
			var y = img.y + img.top;
			break;
		case 1:
			var x = img.y + img.top;
			var y = -1 * (img.x + img.left);
			break;
		case 2:
			var x = -1 * (img.x + img.left);
			var y = -1 * (img.y + img.top);
			break;
		case 3:
			var x = -1 * (img.y + img.top);
			var y = img.x + img.left;
			break;
	}
	var r = img.r * Math.PI / 180;
	// context.translate(cvs.width/2, img.top + cvs_width/2);
	context.translate(img.x+(img.w/2), img.y + (img.h/2));
	context.rotate(r);
	context.translate(-(img.x+(img.w/2)), -1*(img.y + (img.h/2)));
	// context.translate(-cvs.width/2, -1*(img.top + cvs_width/2));
	context.drawImage(img.em[0], x, y, img.w + img.w*(img.size/100), img.h + img.h*(img.size/100));
	context.restore();//还原状态
}

function create(){
	// var r = $(window).width() / 7.5;
	//加入黄底
	var imgData = context.getImageData(0,0,$(window).width(),$(window).height());
	context3.putImageData(imgData,0,0);
	var main = $('<img/>').attr('src', cvs3.toDataURL('image/jpeg'));
	context2.save();
	context2.fillStyle="#fedc00";
	context2.fillRect(0,0,cvs2.width,50);
	context2.fillRect(0,0,100,cvs2.height);
	context2.fillRect(1100,0,cvs2.width,cvs2.height);
	context2.fillRect(0,1050,cvs2.width,cvs2.height);
	context2.save();
	//context.globalCompositeOperation = 'source-atop';
	var num = 0;

	//加入各种图片
	$('<img>').css({width:'1000px',height:'100px'}).attr('src',cvs3.toDataURL('image/jpeg')).load(function(){
		context2.drawImage(this, 100, 50, 1000, 1000);
		num ++;
	});
	$('<img>').attr('src', 'img/3/leftimg.png?v=1.1').load(function(){
		context2.drawImage(this, 35, 10, 458, 208);
		num ++;
	});
	$('<img>').attr('src', 'img/3/right.png?v=1.1').load(function(){
		context2.drawImage(this, 766, 680, 390, 272);
		num ++;
	});
	$('<img>').attr('src', 'img/3/namebg.png?v=1.1').load(function(){
		context2.drawImage(this, 365, 948, 760, 133);
		num ++;
	});
	$('<img>').attr('src', 'img/3/code.png?v=1.1').load(function(){
		context2.drawImage(this, 90, 1840, 1020, 283);
		num ++;
	});

	//星座图片
	var a = Math.ceil(Math.random() * 10);
	$('<img>').attr('src', 'img/' + xingzuo(month, day) + '/' + a + '.png?v=1.1').load(function(){
		context2.drawImage(this, 100, 1160, 850, 130);
		num ++;
	});
	a = a == 10 ? 1 : a+1;
	$('<img>').attr('src', 'img/' + xingzuo(month, day) + '/' + a + '.png?v=1.1').load(function(){
		context2.drawImage(this, 100, 1374, 850, 130);
		num ++;
	});
	a = a == 10 ? 1 : a+1;
	$('<img>').attr('src', 'img/' + xingzuo(month, day) + '/' + a + '.png?v=1.1').load(function(){
		context2.drawImage(this, 100, 1588, 850, 130);
		num ++;
	});

	var p = setInterval(function(){
		if(num >= 8){
			clearInterval(p);
			//绘制名字
			context2.font=50 + "px 微软雅黑";
			context2.fillStyle='#333';
			context2.textAlign='center';//文本水平对齐方式
			context2.textBaseline='middle';//文本垂直方向，基线位置
			context2.fillText(nickname,760,1025);

			var data = cvs2.toDataURL('image/jpeg');
			$('<img>').css({width:'100%',height:'100%'}).attr('src', data).appendTo($('body').empty());

			$('<div>').addClass('layBox').appendTo('body')
				.html('<div class="contentBox">长按保存您的专属吃货照！<br><small>(点击关闭提示)</div>')
				.click(function(){
					$(this).remove();
				});
		}
	}, 100);
}

function xingzuo(month,date){
	var value = 'no';
	if (month == 1 && date >=20 || month == 2 && date <=18) {value = "水瓶";}
	if (month == 1 && date > 31) {value = "no";}
	if (month == 2 && date >=19 || month == 3 && date <=20) {value = "双鱼";}
	if (month == 2 && date > 29) {value = "no";}
	if (month == 3 && date >=21 || month == 4 && date <=19) {value = "白羊";}
	if (month == 3 && date > 31) {value = "no";}
	if (month == 4 && date >=20 || month == 5 && date <=20) {value = "金牛";}
	if (month == 4 && date > 30) {value = "no";}
	if (month == 5 && date >=21 || month == 6 && date <=21) {value = "双子";}
	if (month == 5 && date > 31) {value = "no";}
	if (month == 6 && date >=22 || month == 7 && date <=22) {value = "巨蟹";}
	if (month == 6 && date > 30) {value = "no";}
	if (month == 7 && date >=23 || month == 8 && date <=22) {value = "狮子";}
	if (month == 7 && date > 31) {value = "no";}
	if (month == 8 && date >=23 || month == 9 && date <=22) {value = "处女";}
	if (month == 8 && date > 31) {value = "no";}
	if (month == 9 && date >=23 || month == 10 && date <=22) {value = "天秤";}
	if (month == 9 && date > 30) {value = "no";}
	if (month == 10 && date >=23 || month == 11 && date <=21) {value = "天蝎";}
	if (month == 10 && date > 31) {value = "no";}
	if (month == 11 && date >=22 || month == 12 && date <=21) {value = "射手";}
	if (month == 11 && date > 30) {value = "no";}
	if (month == 12 && date >=22 || month == 1 && date <=19) {value = "摩羯";}
	if (month == 12 && date > 31) {value = "no";}
	return value;
}
