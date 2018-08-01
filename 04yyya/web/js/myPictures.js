var myPicturesObject = {
	pics : [],
	count : 0,
	size : [],
	cutData : {},
	touch_state:'false',
	canvas : null,
	context : null,
	em:null,
	load : function(files){
		if(!files){
			ajax('member/mypic/getlist', function(d){
				if(typeof(d.status) != 'undefined' && d.status == 0){
					$.alert(d.info, 'error');
				}else{
					var code = '';
					if(d.length > 0){
						for(var i in d){
							code += '<div class="item">';
							code += '<div class="pic_datetime">'+ d[i][0].date +'</div>';
							code += '<div class="pic_item">';
							for(var j in d[i]){
								if(d[i][j].is_used == 1)
									code += '<a data="'+ d[i][j].id +'" class="used" onclick="myPicturesObject.select('+ d[i][j].id +');">';
								else
									code += '<a data="'+ d[i][j].id +'" onclick="myPicturesObject.select('+ d[i][j].id +');">';

								try {
									var json = eval('(' + d[i][j].size + ')');
									var arr = [];
									for(var n in json){
										arr.push(json[n][0] + 'x' + json[n][1]);
									}
									var size = arr.join('|');
									code += '<img src="'+ d[i][j].path +'" size="'+ size +'">';
								}catch(e){
									code += '<img src="'+ d[i][j].path +'">';
								}
								code += '</a>';
							}
							code += '</div></div>';
						}
					}else{
						code = '<div class="nopic">没有图片</div>';
					}
					$('.page_myPictures .content').html(code);
				}
			});
		}else{
			var code = '';
			for(var i in files){
				code += '<a href="javascript:void(0);" data="'+ files[i].pic_id +'" class="picitem">';
				code += '	<div class="left"></div>';
				code += '	<div class="right">';
				var arr = files[i].path.split('/');
				code += '		<div class="t">'+ arr[arr.length - 1] +'</div>';
				code += '		<div class="b"></div>';
				code += '	</div>';
				code += '</a>';
			}
			$('.page_myPictures .content').append(code);
			for(var i in files) {
				$('<img>').attr('data', files[i].pic_id).attr('src', files[i].path).load(function () {
					var id = $(this).attr('data');
					var path = this.src;
					var width = this.width;
					var height = this.height;
					var t = this;
					$('.page_myPictures .picitem').each(function () {
						if (path.indexOf($(this).find('.t').text()) > 0) {
							$(this).find('.left').append(t);
							$(this).find('.b').html(width + '<small>px</small> × ' + height + '<small>px</small>');
							//if (myPicturesObject.size.length == 0 || (width == myPicturesObject.size[0] && height == myPicturesObject.size[1])) {
							//	$(this).addClass('selected');
							//}
							$(this).click(function () {
								myPicturesObject.select(id);
							});
							$(this).click();
						}
					});
				});
			}

		}
	},
	change : function(em){
		win.loading();
		var datas = [];
		var files = em.files;
		var cvs = $('<canvas>').width(640).attr('width', 640);
		for(var i=0; i<files.length; i++){
			var file = files[i];
			var url = window.URL.createObjectURL(file);
			var img = $('<img/>').attr('src', url);
			img.load(function(){
				var height = 640 / this.width * this.height;
				cvs.height(height).attr('height', height);
				var context = cvs[0].getContext('2d');
				context.drawImage(this, 0, 0, 640, height);
				var data = cvs[0].toDataURL('image/jpeg');
				data = data.replace('data:image/jpeg;base64,', '');
				datas.push(data);
			});
		}
		myPicturesObject.ppp = window.setInterval(function(){
			if(datas.length == files.length){
				window.clearInterval(myPicturesObject.ppp);
				ajax('Member/Mypic/upload', {file:datas}, function(d){
					win.close_loading();
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
						if(sucNum > 0)$.alert(sucNum + ' 张图片上传成功');
						if(err.length > 0){
							$.alert(err.length + " 张上传失败\n" + err.join("\n"),'error');
						}
						if(sucNum > 0)myPicturesObject.load(file);
					}else{
						if(d.status == 1){
							myPicturesObject.load([d.info]);
						}else{
							$.alert('上传失败', 'error');
						}
					}
				}, 2);
			}
		}, 200);
	},
	upload : function(){
		if(win.get.android == 1){
			win.loading();
			android.upload(win.token);
			return;
		}
		$('#myPicUploadBtn').click();
	},
	select : function(id){
		var $em = $('.page_myPictures .content a[data="'+ id +'"]');
		if($em.hasClass('selected')){
			$em.removeClass('selected');
			var v = [];
			for(var i in this.pics){
				if(this.pics[i].pic_id != id)v.push(this.pics[i]);
			}
			this.pics = v;
		}else{
			if(this.count > 0 && this.count <= this.pics.length){
				if(this.pics.length == 1){
					this.pics = [];
					$('.page_myPictures .content a.selected').removeClass('selected');
				}else{
					$.alert('您最多能同时选中'+ this.count +'张图片', 'error');
					return;
				}
			}
			if(this.size.length == 2){
				var size = $em.find('img').attr('size');
				var src = $em.find('img').attr('src');
				var path = '';
				if(!!size){
					var arr = size.split('|');
					for(var i in arr){
						var _arr = arr[i].split('x');
						if(_arr[0] / _arr[1] == this.size[0] / this.size[1]){
							if(/_{0,1}\d+x\d+/.test(src))
								path = src.replace(/_{0,1}\d+x\d+/, '_' + _arr[0] + 'x' + _arr[1]);
							else
								path = src.replace(/(\.\w{2,4})$/, '_' + _arr[0] + 'x' + _arr[1] + '$1');
						}
					}
				}
				if(path == ''){
					$.dialog('当前图片的尺寸比例与您所需图片比例不对称，可能会造成变形，是否进行裁切？', function(){
						path = src.replace(/_{0,1}\d+x\d+/, '');
						myPicturesObject.cut(id, path, $em);
					});
				}else{
					$em.addClass('selected');
					this.pics.push({
						pic_id : id,
						path : path
					});
				}
			}else{
				$em.addClass('selected');
				this.pics.push({
					pic_id : id,
					path : $em.find('img').attr('src')
				});
			}
		}
	},
	cut : function(pic_id, rotate){
		$('html,body').css('overflow','hidden');
		//创建切图场景
		var cutBox = $('<div>').addClass('page_myPictures').addClass('cutBox').appendTo('body');
		$('<canvas>').addClass('canvas').attr('width', $(window).width()).attr('height', $(window).height()).appendTo('.cutBox');
		myPicturesObject.canvas = $('.canvas')[0];
		myPicturesObject.context = myPicturesObject.canvas.getContext('2d');
		//获取图片地址
		var src = $('.page_myPictures .content a[data="'+ pic_id +'"] img').attr('src');

		//获取最终要剪切的尺寸
		var cut_size = myPicturesObject.size;
		//创建并加载图片
		$('<img>').attr('src', src).load(function(){
			//获取图片的原始宽高+比例
			var img_width = this.width;
			var img_height = this.height;
			var img_scale = this.width / this.height;
			//剪切：算出图片按比例缩放后的宽高及坐标和大小比例
			if(img_scale > cut_size[0] / cut_size[1]){
				var img_h = cut_size[1];
				var img_w = img_h * img_scale;
				var x = (cut_size[0] - img_w) / 2;
				var y = 0;
			}else{
				var img_w = cut_size[0];
				var img_h = img_w / img_scale;
				var y = (cut_size[1] - img_h) / 2;
				var x = 0;
			}
			//剪切：将图片转为base64加入到同步层
			ajax('Member/Mypic/toBase64', {'pic_id':pic_id}, function(d){
				if(d.status == 1){
					$('<img>').attr('src', d.info).width(img_w).height(img_h).load(function(){
						myPicturesObject._img = this;
						myPicturesObject.draw(x, y, img_w, img_h);
					});
				}
			});

			//创建切图遮罩框
			var cover = $('<div>').addClass('cover').appendTo(cutBox);
			$('<div>').addClass('line-scale').append('<span></span>').appendTo(cutBox);
			//如果剪切的固定宽高图片的宽大于高，遮罩的高等于遮罩宽度除于固定宽高的比例
			//否则遮罩的宽等于遮罩高度乘于固定宽高的比例
			if(cut_size[0] / cut_size[1] > 1){
				var overWidth = Math.round(win.width*0.833);
				var overHeight = Math.round(overWidth / (cut_size[0] / cut_size[1]));
			}else{
				var overHeight = Math.round(win.width*0.833);//300px
				var overWidth = Math.round(overHeight * (cut_size[0] / cut_size[1]));//300px
			}
			//如果原始图片的宽高比例小于剪切图片的固定宽高比例
			if(img_scale < cut_size[0] / cut_size[1]){
				var showWidth = overWidth;//300px
				var showHeight = Math.round(showWidth / img_scale);//405px
			}else{
				var showHeight = overHeight;
				var showWidth = Math.round(showHeight * img_scale);
			}
			//算出可视层和同步层的换算比例
			var scale = img_w / showWidth;//1.06
			//调整遮罩
			cover.css('border-width', ((win.height - overHeight) / 2) + 'px ' + ((win.width - overWidth) / 2) + 'px');
			//将原始数据放入数据列表
			myPicturesObject.cutData = {
				img_width : img_width, //原始图片宽
				img_height : img_height,//原始图片高
				img_scale : img_scale,//图片缩放比例
				cut_size : cut_size,//目标图片宽高
				img_w : img_w,//加载进来的图片宽
				img_h : img_h,//加载进来的图片高
				x : x,//图片的x坐标
				y : y,//图片的x坐标
				overWidth : overWidth,//遮盖y
				overHeight : overHeight,//遮盖高
				showWidth : showWidth,//显示区域宽
				showHeight : showHeight,//显示区域高
				imgw:showWidth,
				imgh:showHeight,
				overLeft : (win.width - overWidth) / 2,//30px
				overTop : (win.height - overHeight) / 2,//170px
				imgLeft : (win.width-showWidth) / 2,//30px
				imgTop : (win.height-showHeight) / 2,//117.5px
				scale : scale,//1.06 可视层和同步层的比例
				rotate : 0
			};
			myPicturesObject.cvsimg = {
				x : 0,
				y : 0,
				Left : (win.width-showWidth) / 2,
				Top : (win.height-showHeight) / 2,
				w : showWidth,
				h : showHeight,
				size : 0,
				touchx : 0,
				touchy : 0,
			};
			$('<div>').addClass('cvsmove').css({
				width:myPicturesObject.cutData.overWidth,
				height:myPicturesObject.cutData.overHeight,
				position:'absolute',
				left : myPicturesObject.cutData.overLeft,
				top : myPicturesObject.cutData.overTop,
				'z-index' : '111111',
			}).appendTo(cutBox);
			myPicturesObject.em = $(this);
			myPicturesObject.drawcvs();
			//加入交互事件
			$('.cvsmove')[0].addEventListener('touchstart', function(event){
				event.preventDefault();
				var ev = event.touches;
				if(ev.length == 1){
					myPicturesObject.cvsimg.touchx = ev[0].pageX;
					myPicturesObject.cvsimg.touchy = ev[0].pageY;
					myPicturesObject.touch_state = true;
				}

			},false);
			$('.cvsmove')[0].addEventListener('touchmove', function(event){
				event.preventDefault();
				var ev = event.touches;
				if(ev.length == 1){
					//拖动处理
					if(myPicturesObject.touch_state){
						myPicturesObject.cvsimg.x += ev[0].pageX - myPicturesObject.cvsimg.touchx;
						myPicturesObject.cvsimg.y += ev[0].pageY - myPicturesObject.cvsimg.touchy;
						myPicturesObject.cutData.imgLeft += ev[0].pageX - myPicturesObject.cvsimg.touchx;
						myPicturesObject.cutData.imgTop += ev[0].pageY - myPicturesObject.cvsimg.touchy;
						myPicturesObject.drawcvs();
						myPicturesObject.draw();

					}
					myPicturesObject.cvsimg.touchx = ev[0].pageX;
					myPicturesObject.cvsimg.touchy = ev[0].pageY;

				}
			},false);
			$('.cvsmove')[0].addEventListener('touchend', function(event){
				event.preventDefault();
				myPicturesObject.touch_state = false;
			}, false);
			//缩小放大函数
			$('.line-scale')[0].addEventListener('touchstart',function(event){
				var ev = event.touches;
				if(ev.length == 1) {
					if(ev[0].pageX > $(this).offset().left + ($(this).children().width()/2) && ev[0].pageX < $(this).offset().left + $(this).width() - ($(this).children().width()/2)){
						$(this).children().css('left',ev[0].pageX - $(this).offset().left - ($(this).children().width()/2));
					}else if(ev[0].pageX > $(this).offset().left + $(this).width() - ($(this).children().width()/2)){
						$(this).children().css('left',$(this).width() + 1 - $(this).children().width());
					}
					else{
						$(this).children().css('left','0');
					}
					myPicturesObject.cvsimg.size = Math.round($(this).children().position().left / ($(this).width()-$(this).children().width()) * 100 );
					//改变后的宽高
					var img_w = myPicturesObject.cutData.imgw + myPicturesObject.cutData.imgw * (myPicturesObject.cvsimg.size / 100);
					var img_h = myPicturesObject.cutData.imgh + myPicturesObject.cutData.imgh * (myPicturesObject.cvsimg.size / 100);
					//位置居中改变
					myPicturesObject.cutData.imgLeft = myPicturesObject.cutData.imgLeft - (img_w - myPicturesObject.cutData.showWidth)/2;
					myPicturesObject.cutData.imgTop = myPicturesObject.cutData.imgTop - (img_h - myPicturesObject.cutData.showHeight)/2;
					myPicturesObject.cvsimg.x = myPicturesObject.cvsimg.x - (img_w - myPicturesObject.cvsimg.w)/2;
					myPicturesObject.cvsimg.y = myPicturesObject.cvsimg.y - (img_h - myPicturesObject.cvsimg.h)/2;

					myPicturesObject.cutData.showWidth = img_w;
					myPicturesObject.cutData.showHeight = img_h;
					myPicturesObject.cvsimg.w = img_w;
					myPicturesObject.cvsimg.h = img_h;
					myPicturesObject.drawcvs();
					myPicturesObject.draw();
				}
			},false);
			$('.line-scale')[0].addEventListener('touchmove',function(event){
				var ev = event.touches;
				if(ev.length == 1){
					if(myPicturesObject.touch_state == true){
						var tleft = ev[0].pageX - $(this).offset().left - ($(this).children().width()/2);
						if(tleft <= 0){
							$(this).children().css('left','0');
						}else if(tleft >= $(this).width() - $(this).children().width()){
							$(this).children().css('left',$(this).width() - $(this).children().width());
						}else{
							$(this).children().css('left',tleft);
						}
						myPicturesObject.cvsimg.size = Math.round($(this).children().position().left / ($(this).width()-$(this).children().width()) * 100 );
						//改变后的宽高
						var img_w = myPicturesObject.cutData.imgw + myPicturesObject.cutData.imgw * (myPicturesObject.cvsimg.size / 100);
						var img_h = myPicturesObject.cutData.imgh + myPicturesObject.cutData.imgh * (myPicturesObject.cvsimg.size / 100);
						//位置居中改变
						myPicturesObject.cutData.imgLeft = myPicturesObject.cutData.imgLeft - (img_w - myPicturesObject.cutData.showWidth)/2;
						myPicturesObject.cutData.imgTop = myPicturesObject.cutData.imgTop - (img_h - myPicturesObject.cutData.showHeight)/2;
						myPicturesObject.cvsimg.x = myPicturesObject.cvsimg.x - (img_w - myPicturesObject.cvsimg.w)/2;
						myPicturesObject.cvsimg.y = myPicturesObject.cvsimg.y - (img_h - myPicturesObject.cvsimg.h)/2;

						myPicturesObject.cutData.showWidth = img_w;
						myPicturesObject.cutData.showHeight = img_h;
						myPicturesObject.cvsimg.w = img_w;
						myPicturesObject.cvsimg.h = img_h;
						myPicturesObject.drawcvs();
						myPicturesObject.draw();
					}
				}
				myPicturesObject.cutData.touchLeft = ev[0].pageX;
				myPicturesObject.touch_state = true;
			},false);
			$('.line-scale')[0].addEventListener('touchend',function(event){
				event.preventDefault();
				myPicturesObject.touch_state = false;
			},false);
			//添加按钮
			$('.line-scale').css({'top':(win.height + overHeight) / 2 - 10 + 'px', 'margin-left':'8rem'});
			$('<button>').text('剪切').css({'top':(win.height + overHeight) / 2 + 70 + 'px', 'margin-left':'-12rem'}).click(function(){
				var data = myPicturesObject.cvs.toDataURL('image/jpeg');
				data = data.replace('data:image/jpeg;base64,', '');
				ajax('Member/Mypic/upload', {'pic_id':pic_id, 'file':[data], 'size':myPicturesObject.size.join('x')}, function(d){
					if(d.status == 1){
						$.alert('剪切成功', function(){
							var $em = $('.page_myPictures .content a[data="'+ pic_id +'"]').addClass('selected');
							var path = $em.find('img').attr('src');
							path = path.replace(/^(.+?)(_\d+?x\d+?){0,1}(\.\w{2,4})$/, '$1_'+ myPicturesObject.size[0] +'x'+ myPicturesObject.size[1] +'$3');
							$em.find('img').attr('src', path);
							myPicturesObject.pics.push({
								pic_id : pic_id,
								path :path
							});
							$em.find('img').attr('size', $em.find('img').attr('size') + '|' + myPicturesObject.size[0] +'x'+ myPicturesObject.size[1]);
							myPicturesObject.cutData = {};
							$(document).off('touchstart touchmove');
							$('.page_myPictures.cutBox').remove();
						});
					}else{
						$.alert(d.info, 'error');
					}
				});
			}).appendTo(cutBox);
			$('<button>').text('取消').css({'top':(win.height + overHeight) / 2 + 70 + 'px', 'margin-left':'4rem'}).click(function(){
				myPicturesObject.cutData = {};
				$(document).off('touchstart touchmove');
				$('.page_myPictures.cutBox').remove();
			}).appendTo(cutBox);
			$('<button>').addClass('rotate').css({'top':(win.height + overHeight) / 2 + 70 + 'px', 'margin-left':'-1rem'}).click(function(){
				myPicturesObject.cutData.rotate += 90;
				$('.page_myPictures.cutBox .img').css('transform', 'rotate('+ myPicturesObject.cutData.rotate +'deg)');
				myPicturesObject.drawcvs();
				myPicturesObject.draw();
			}).appendTo(cutBox);
		});
	},
	//绘制canvas
	drawcvs : function(){
		myPicturesObject.context.clearRect(0,0,myPicturesObject.canvas.width,myPicturesObject.canvas.height);
		myPicturesObject.context.save();//保存当前的绘制状态
		var w = myPicturesObject.cvsimg.w;
		var h = myPicturesObject.cvsimg.h;
		switch((myPicturesObject.cutData.rotate / 90) % 4){
			case 0:
				var x = myPicturesObject.cvsimg.x + myPicturesObject.cvsimg.Left;
				var y = myPicturesObject.cvsimg.y + myPicturesObject.cvsimg.Top;
				break;
			case 1:
				var x = myPicturesObject.cvsimg.y + myPicturesObject.cvsimg.Top;
				var y = -1 * (myPicturesObject.cvsimg.x + myPicturesObject.cvsimg.Left);
				break;
			case 2:
				var x = -1 * (myPicturesObject.cvsimg.x + myPicturesObject.cvsimg.Left);
				var y = -1 * (myPicturesObject.cvsimg.y + myPicturesObject.cvsimg.Top);
				break;
			case 3:
				var x = -1 * (myPicturesObject.cvsimg.y + myPicturesObject.cvsimg.Top);
				var y = myPicturesObject.cvsimg.x + myPicturesObject.cvsimg.Left;
				break;
		}
		var r = myPicturesObject.cutData.rotate * Math.PI / 180;
		myPicturesObject.context.translate(w/2, h/2);
		myPicturesObject.context.rotate(r);
		myPicturesObject.context.translate(-(w/2), -1*(h/2));
		myPicturesObject.context.drawImage(myPicturesObject.em[0],x,y,w, h);
		myPicturesObject.context.restore();//还原状态
	},
	draw : function(x, y, w, h){
		if(!myPicturesObject.cvs){
			//创建同步canvas
			var cvs = $('<canvas>').width(myPicturesObject.size[0]).height(myPicturesObject.size[1]).attr('width',myPicturesObject.size[0]).attr('height',myPicturesObject.size[1]);
			myPicturesObject.cvs = cvs[0];
			myPicturesObject.canvasData = myPicturesObject.cvs.getContext('2d');
		}
		myPicturesObject.canvasData.clearRect(0, 0, myPicturesObject.size[0], myPicturesObject.size[1]);
		myPicturesObject.canvasData.save();
		var w = w||myPicturesObject.cutData.showWidth * myPicturesObject.cutData.scale;
		var h = h||myPicturesObject.cutData.showHeight * myPicturesObject.cutData.scale;
		switch((myPicturesObject.cutData.rotate / 90) % 4){
			case 0:
				var x = x||(myPicturesObject.cutData.imgLeft - myPicturesObject.cutData.overLeft) * myPicturesObject.cutData.scale;
				var y = y||(myPicturesObject.cutData.imgTop - myPicturesObject.cutData.overTop) * myPicturesObject.cutData.scale;
				break;
			case 1:
				var x = x||(myPicturesObject.cutData.imgTop - myPicturesObject.cutData.overTop) * myPicturesObject.cutData.scale;
				var y = y||-1*(myPicturesObject.cutData.imgLeft - myPicturesObject.cutData.overLeft) * myPicturesObject.cutData.scale;
				break;
			case 2:
				var x = x||-1*(myPicturesObject.cutData.imgLeft - myPicturesObject.cutData.overLeft) * myPicturesObject.cutData.scale;
				var y = y||-1*(myPicturesObject.cutData.imgTop - myPicturesObject.cutData.overTop) * myPicturesObject.cutData.scale;
				break;
			case 3:
				var x = x||-1*(myPicturesObject.cutData.imgTop - myPicturesObject.cutData.overTop) * myPicturesObject.cutData.scale;
				var y = y||(myPicturesObject.cutData.imgLeft - myPicturesObject.cutData.overLeft) * myPicturesObject.cutData.scale;
				break;
		}
		var r = myPicturesObject.cutData.rotate * Math.PI / 180;
		myPicturesObject.canvasData.translate(w/2, h/2);
		myPicturesObject.canvasData.rotate(r);
		myPicturesObject.canvasData.translate(-(w/2), -1*h/2);

		$(myPicturesObject._img).css('transform', 'rotate('+ myPicturesObject.cutData.rotate +'deg)');
		myPicturesObject.canvasData.drawImage(myPicturesObject._img, x, y, w, h);
		myPicturesObject.canvasData.restore();//还原状态
	},
	//安卓访问回调
	aupload : function(ds){
		win.close_loading();
		myPicturesObject.load([ds]);
	},
	onload : function(){
		if(member === null){
			win.login();
			return;
		}
		if(typeof(win.get.backFun) == 'function')myPicturesObject.backFun = win.get.backFun;
		myPicturesObject.count = win.get.count ? win.get.count : 0;
		myPicturesObject.size = win.get.size||[];
		for(var i in win.get.choosed){
			myPicturesObject.select(win.get.choosed[i]);
		}
		$('.page_myPictures.header .turnBack, .page_myPictures.header .submit').click(function(){
			if(myPicturesObject.pics.length > 0) {
				if (typeof(myPicturesObject.backFun) == 'function')
					myPicturesObject.backFun(myPicturesObject.pics);
			}
			page.back();
		});
		myPicturesObject.upload();

		$('.page_myPictures .upBtn').click(function(){
			myPicturesObject.upload();
		});
	}
};
