var diyimageObject = {
	diy_id : null,
	allowUpload : 0,
	imgData : {
		rotate : 0,
		x : 0,
		y : 0,
		left : 0,
		top : 0,
		width : 0,
		height : 0
	},
	drayData : [0, 0, 0, 0],
	imgCanvas : null,
	imgCtx : null,
	boxImg : null,
	textDatas : {},
	onload : function(){
		if((!win.get.allowUpload && !win.get.pic_id) || !win.get.diy_id){
			$.alert('非法访问!', function(){
				page.back();
			});
			return;
		}

		this.allowUpload = win.get.allowUpload;
		this.diy_id = win.get.diy_id;

		ajax('member/mypic/toBase64', {pic_id:win.get.pic_id}, function(d){
			if(d.status == 1)
				diyimageObject.getdata(d.info);
			else
				$.alert('要合成的图片不存在!');
		});
	},
	onshow : function(){},
	getdata : function(path){
		ajax('goods/tools/diyimage', {diy_id:this.diy_id}, function(d){
			$('.page_diyimage .content').css({
				'background-color' : d.bg_color
			});
			var scale = $('.page_diyimage .content').width() / 750;
			//加载背景
			$('<img>').addClass('bgpic').attr('src', d.bg_path).load(function(){
				$(this).appendTo('.page_diyimage .content');
			});

			var boxEm = $('<div>').addClass('userbox').appendTo('.page_diyimage .content');
			boxEm.css({
				width : d.box_width * scale,
				height : d.box_height * scale,
				left : d.box_left * scale,
				top : d.box_top * scale,
				zIndex : d.box_depth
			});
			if(path)diyimageObject.input(path);
			if(diyimageObject.allowUpload){
				diyimageObject.upload(function(path){
					diyimageObject.input(path);
				});
			}

			if(d.textdatas && d.textdatas.length > 0){
				for(var i in d.textdatas){
					var t = d.textdatas[i];
					if(typeof win.get[t.name] != undefined){
						$('<font>').text(win.get[t.name]).css({
							fontSize : t.fontsize * scale + 'px',
							color : t.fontcolor,
							left : t.pos[0] * scale + 'px',
							top : t.pos[1] * scale + 'px',
							zIndex : t.pos[2]
						}).attr({
							'data-fontsize' : t.fontsize,
							'data-color' : t.fontcolor,
							'data-x' : t.pos[0],
							'data-y' : t.pos[1],
							'data-z' : t.pos[2]
						}).appendTo('.page_diyimage .content');
					}
				}
			}

			for(var i in d.datas){
				var dt = d.datas[i];
				$('<img>').attr({
					'src' : dt.path,
					'data-left' : dt.pos[0]*scale,
					'data-top' : dt.pos[1]*scale,
					'data-depth' : dt.depth
				}).load(function(){
					$(this).css({
						width : this.width * scale,
						height : this.height * scale,
						left : $(this).data('left') + 'px',
						top : $(this).data('top') + 'px',
						zIndex : $(this).data('depth')
					}).appendTo('.page_diyimage .content');
				});
			}
		}, 2);
	},
	//引入图片
	input : function(path){
		$('.page_diyimage .userbox').empty();
		this.boxImg = $('<img>').attr('src', path);
		var scale = $('.page_diyimage .content').width() / 750;
		this.boxImg.load(function(){
			diyimageObject.imgData.width = this.width * scale;
			diyimageObject.imgData.height = this.height * scale;
			diyimageObject.drayData = [0, 0, diyimageObject.imgData.width, diyimageObject.imgData.height];
			diyimageObject.drag();
		});
		var imgBtn = $('<img>').appendTo('.page_diyimage .userbox');
		var sizeBtn = $('<div>').addClass('size').html('<span></span>').appendTo('.page_diyimage .userbox');
		var rotateBtn = $('<div>').addClass('rotate').appendTo('.page_diyimage .userbox');

		//移动位置
		imgBtn[0].addEventListener('touchstart', function(event){
			event.preventDefault();
			var ev = event.touches;
			if(ev.length == 1){
				$(this).attr('data-touch', 1);
				diyimageObject.imgData.x = parseInt(ev[0].pageX);
				diyimageObject.imgData.y = parseInt(ev[0].pageY);
			}
		}, false);
		imgBtn[0].addEventListener('touchmove', function(event){
			event.preventDefault();
			if($(this).data('touch') == 1){
				var ev = event.touches;
				if(ev.length == 1){
					var x = parseInt(ev[0].pageX) - diyimageObject.imgData.x;
					var y = parseInt(ev[0].pageY) - diyimageObject.imgData.y;
					diyimageObject.drayData[0] = x + diyimageObject.imgData.left;
					diyimageObject.drayData[1] = y + diyimageObject.imgData.top;
					diyimageObject.drag();
				}
			}
		}, false);
		imgBtn[0].addEventListener('touchend', function(event){
			event.preventDefault();
			$(this).attr('data-touch', 0);
			diyimageObject.imgData.x = 0;
			diyimageObject.imgData.y = 0;
			diyimageObject.imgData.left = diyimageObject.drayData[0];
			diyimageObject.imgData.top = diyimageObject.drayData[1];
		}, false);

		//大小变换
		sizeBtn.find('span')[0].addEventListener('touchstart', function(event){
			event.preventDefault();
			var ev = event.touches;
			if(ev.length == 1){
				$(this).attr('data-touch', 1);
				$(this).attr('data-x', ev[0].pageX);
				$(this).attr('data-left', $(this).position().left);
			}
		}, false);
		sizeBtn.find('span')[0].addEventListener('touchmove', function(event){
			event.preventDefault();
			if($(this).data('touch') == 1){
				var ev = event.touches;
				if(ev.length == 1){
					var x = ev[0].pageX - parseInt($(this).data('x'));
					var left = x + parseInt($(this).data('left'));
					if(left >= 0 && left <= $(this).parent().width()){
						diyimageObject.drayData[2] = diyimageObject.imgData.width * (left / $(this).parent().width() + 0.5);
						diyimageObject.drayData[3] = diyimageObject.imgData.height * (left / $(this).parent().width() + 0.5);
						diyimageObject.drag();
						$(this).css('left', left);
					}
				}
			}
		}, false);
		sizeBtn.find('span')[0].addEventListener('touchend', function(event){
			event.preventDefault();
			$(this).attr('data-touch', 0);
			$(this).attr('data-x', 0);
			$(this).attr('data-left', 0);
		}, false);

		//方向旋转
		rotateBtn.click(function(){
			diyimageObject.imgData.rotate ++;
			if(diyimageObject.imgData.rotate >= 4)diyimageObject.imgData.rotate = 0;
			diyimageObject.drag();
		});
	},
	//绘制用户图片
	drag : function(){
		var scale = 750 / $('.page_diyimage .content').width();
		var width = $('.page_diyimage .userbox').width();
		var height = $('.page_diyimage .userbox').height();
		var _width = width * scale;
		var _height = height * scale;
		if(this.imgCanvas == null){
			this.imgCanvas = $('<canvas>').width(width).height(height).attr({
				width : _width,
				height : _height
			});
			this.imgCtx = this.imgCanvas[0].getContext('2d');
		}
		this.imgCtx.clearRect(0, 0, _width, _height);
		this.imgCtx.save();
		switch(diyimageObject.imgData.rotate){
			case 0:
				var x = diyimageObject.drayData[0];
				var y = diyimageObject.drayData[1];
				break;
			case 1:
				var x = diyimageObject.drayData[1];
				var y = -diyimageObject.drayData[0];
				break;
			case 2:
				var x = -diyimageObject.drayData[0];
				var y = -diyimageObject.drayData[1];
				break;
			case 3:
				var x = -diyimageObject.drayData[1];
				var y = diyimageObject.drayData[0];
				break;
		}
		diyimageObject.imgCtx.translate(diyimageObject.drayData[2] * scale/2, diyimageObject.drayData[3] * scale/2);
		diyimageObject.imgCtx.rotate(diyimageObject.imgData.rotate * Math.PI / 2);
		diyimageObject.imgCtx.translate(-(diyimageObject.drayData[2] * scale/2), -1*diyimageObject.drayData[3] * scale/2);
		this.imgCtx.drawImage(this.boxImg[0], x, y, diyimageObject.drayData[2] * scale, diyimageObject.drayData[3] * scale);
		this.imgCtx.restore();//还原状态
		$('.page_diyimage .userbox img').attr('src', this.imgCanvas[0].toDataURL('image/jpeg'));
	},
	//上传图片
	upload : function(fn){},
	//生成图片
	create : function(){
		var width = $('.page_diyimage .content').width();
		var height = $('.page_diyimage .content').height();
		var scale = 750 / width;
		var cvs = $('<canvas>').width(750).height(height * scale).attr({width:750, height:height*scale})[0];
		var ctx = cvs.getContext('2d');

		//绘制背景
		ctx.fillStyle = $('.page_diyimage .content').css('background-color');
		ctx.rect(0, 0, 750, height * scale);
		ctx.drawImage($('.page_diyimage .content .bgpic').clone(true)[0], 0, 0, 640, height);

		var data = [];
		$('.page_diyimage .content img').each(function(){
			var d = {
				em : $(this).clone()[0],
				width : $(this).width() * scale,
				height : $(this).height() * scale,
				left : $(this).position().left * scale,
				top : $(this).position().top * scale,
				depth : $(this).css('z-index')
			};
			if($(this).parent().hasClass('userbox')){
				d.width = $(this).parent().width() * scale;
				d.height = $(this).parent().height() * scale;
				d.left = $(this).parent().position().left * scale;
				d.top = $(this).parent().position().top * scale;
				d.depth = $(this).parent().css('z-index');
			}
			data.push(d);
		});

		$('.page_diyimage .content font').each(function(){
			data.push({
				em : $(this).text(),
				fontsize : $(this).data('fontsize'),
				color : $(this).data('color'),
				left : $(this).data('x'),
				top : $(this).data('y'),
				depth : $(this).data('z')
			});

			//var code = '<svg xmlns="http://www.w3.org/2000/svg">' +
			//	'<foreignObject width="100%" height="100%">' +
			//	//'<div xmlns="http://www.w3.org/1999/xhtml">' + $(this).text() + '</div>' +
			//	'<div xmlns="http://www.w3.org/1999/xhtml">xxxxxxx</div>' +
			//	'</foreignObject>' +
			//	'</svg>';
			//var svg = $(code).width($(this).data('width')).height($(this).data('height')).attr('width', $(this).data('width')).attr('height', $(this).data('height'));
			//svg.find('div').css({
			//	width : $(this).data('width') + 'px',
			//	height : $(this).data('height') + 'px',
			//	fontSize : $(this).data('fontsize') + 'px',
			//	color : $(this).data('color'),
			//	lineHeight : $(this).data('lineheight') + 'px',
			//	overflow : 'hidden'
			//});
			//var that = $(this);
			//var DOMURL = window.URL || window.webkitURL || window;
			//var img = new Image();
			//var svg = new Blob([data], {type: 'image/svg+xml;charset=utf-8'});
			//var url = DOMURL.createObjectURL(svg);
			////var url = 'data:image/svg+xml;charset=utf-8,' + svg[0].outerHTML;
			//alert(url);
			//var img = $('<img crossOrigin="anonymous">').attr('src', url).load(function(){
			//	data.push({
			//		em : this,
			//		width : that.data('width'),
			//		height : that.data('height'),
			//		left : that.data('x'),
			//		top : that.data('y'),
			//		depth : that.data('z')
			//	});
			//	alert(this);
			//	diyimageObject.imgLoaded --;
			//});
            //
			//diyimageObject.imgLoaded ++;
		});

		//diyimageObject.data = data;
        //
		//(function timer(){
		//	if(diyimageObject.imgLoaded > 0){
		//		setTimeout(timer, 100);
		//		return;
		//	}
		//	alert(diyimageObject.data);
		//	diyimageObject.data = diyimageObject.data.sort(function(a, b){
		//		return parseInt(a.depth) - parseInt(b.depth);
		//	});
		//	for(var i in diyimageObject.data){
		//		var d = diyimageObject.data[i];
		//		diyimageObject.ctx.drawImage(d.em, d.left, d.top, d.width, d.height);
		//	}
		//	alert(diyimageObject.data);
		//	try {
		//		var data = diyimageObject.cvs.toDataURL('image/jpeg');
		//	}catch(e){
		//		alert(e.message);
		//		return;
		//	}
        //
		//	data = data.replace('data:image/jpeg;base64,', '');
        //
		//	var datas = {
		//		x : diyimageObject.drayData[0],
		//		y : diyimageObject.drayData[1],
		//		w : diyimageObject.drayData[2],
		//		h : diyimageObject.drayData[3],
		//		r : diyimageObject.imgData.rotate,
		//	};
        //
		//	ajax('goods/tools/DIYImageSave', {diy_id:win.get.diy_id, pic_id:win.get.pic_id, code:data, datas:datas}, function(d){
		//		if(d.status == 1){
		//			if(isWeiXin()){
		//				jump('image', {path: d.info.path});
		//			}else
		//				location.href = win.host + 'goods/tools/DIYImageDownload.do?token='+ win.token +'&id=' + d.info.diyimage_id;
		//		}else
		//			$.alert(d.info, 'error');
		//	}, 2);
		//})();

		data = data.sort(function(a, b){
			return parseInt(a.depth) - parseInt(b.depth);
		});
		for(var i in data){
			var d = data[i];
			if(typeof(d.em) == 'string'){
				ctx.font = d.fontsize + 'px "Microsoft YaHei"';
				ctx.fillStyle = d.color;
				ctx.fillText(d.em, d.left, d.top + d.fontsize);
			}else
				ctx.drawImage(d.em, d.left, d.top, d.width, d.height);
		}

		var data = cvs.toDataURL('image/jpeg');

		data = data.replace('data:image/jpeg;base64,', '');

		var datas = {
			x : diyimageObject.drayData[0],
			y : diyimageObject.drayData[1],
			w : diyimageObject.drayData[2],
			h : diyimageObject.drayData[3],
			r : diyimageObject.imgData.rotate,
		};

		ajax('goods/tools/DIYImageSave', {diy_id:win.get.diy_id, pic_id:win.get.pic_id, code:data, datas:datas}, function(d){
			if(d.status == 1){
				if(isWeiXin()){
					jump('image', {path: d.info.path});
				}else
					location.href = win.host + 'goods/tools/DIYImageDownload.do?token='+ win.token +'&id=' + d.info.diyimage_id;
			}else
				$.alert(d.info, 'error');
		}, 2);
	}
};