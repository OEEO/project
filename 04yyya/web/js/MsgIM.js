var MsgIMObject = {
	page : 1,
	adminpath : 'http://img.yummy194.cn/images/yamijiang.jpg',
	facePage : 0,
	add : function(d, isnew){
		//在客服界面
		var code = '';
		if(d.date)
			code += '<small>'+ d.date +'</small>';
		if(d.from_id == 'admin'){
			code += '<li>';
			code += '    <img src="'+ this.adminpath +'">';
		}else{
			code += '<li class="right">';
			code += '    <img src="'+ member.path +'">';
		}
		if(d.type == 2)
			code += '<i class="goods"></i>';
		else
			code += '<i></i>';
		if(d.type == 1) {
			code += '    <span><img src="http://img.' + DOMAIN + '/' + d.content + '"></span>';
		}else if(d.type == 2){
			var types = ['饭局', '商品', '众筹'];
			var pages = ['tipsDetail', 'goodsDetail', 'raiseDetail'];
			var data = d.content;
			if(typeof(data) != 'object')data = JSON.parse(data);
			var content = '<a href="javascript:jump(\''+ pages[parseInt(data.type)] +'\', {tips_id:'+ data.id +'});">';
			content += '<img src="'+ data.path +'">';
			content += '<div><b>'+ types[parseInt(data.type)] +'：</b>' + data.title + '</div>';
			content += '</a>';
			code += '    <span class="goods">'+ content +'</span>';
		}else{
			code += '    <span>'+ faces.decode(d.content) +'</span>';
		}
		code += '    <div style="clear: both;"></div>';
		code += '</li>';

		if(isnew == 1){
			var historyMessages = {};
			if(localStorage.historyMessages){
				historyMessages = JSON.parse(localStorage.historyMessages);
			}
			if(!historyMessages.admin)historyMessages.admin = [];
			historyMessages.admin.push(d);
			localStorage.historyMessages = JSON.stringify(historyMessages);
		}
		$('.page_MsgIM .msgList').append(code);

		$('.page_MsgIM.wrapper').scrollTop($('.page_MsgIM .msgList').height());
	},
	facesCreate : function(){
		var code = faces.create();
		var em = $(code).prependTo('.page_MsgIM .facesBox');

		$('.page_MsgIM .facesBtn').on('click', function(){
			if($('.page_MsgIM .facesBox').hasClass('show')){
				$('.page_MsgIM .facesBox').removeClass('show');
			}else{
				$('.page_MsgIM .facesBox').addClass('show');
				$('.page_MsgIM .writer').focus();
			}
		});

		$('.page_MsgIM .facesBox').touchwipe({
			'wipeLeft' : function(){
				if(MsgIMObject.facePage < 2){
					$('.page_MsgIM .facesBox').find('.facelist').eq(MsgIMObject.facePage).css('left', '-36rem');
					MsgIMObject.facePage ++;
					$('.page_MsgIM .facesBox').find('.facelist').eq(MsgIMObject.facePage).css('left', '1rem');
					$('.page_MsgIM .facesBox').find('.dian span').removeClass('now');
					$('.page_MsgIM .facesBox').find('.dian span').eq(MsgIMObject.facePage).addClass('now');
				}
			},
			'wipeRight' : function(){
				if(MsgIMObject.facePage > 0){
					$('.page_MsgIM .facesBox').find('.facelist').eq(MsgIMObject.facePage).css('left', '36rem');
					MsgIMObject.facePage --;
					$('.page_MsgIM .facesBox').find('.facelist').eq(MsgIMObject.facePage).css('left', '1rem');
					$('.page_MsgIM .facesBox').find('.dian span').removeClass('now');
					$('.page_MsgIM .facesBox').find('.dian span').eq(MsgIMObject.facePage).addClass('now');
				}
			}
		});

		em.find('td').on('click', MsgIMObject.facesInput);
	},
	facesInput : function(){
		if(selection.containsNode($('.page_MsgIM .writer')[0], true)){
			var img = $(this).find('img').clone();
			selection.getRangeAt(0).collapse(false);
			selection.getRangeAt(0).insertNode(img[0]);
			selection.getRangeAt(0).collapse(false);
		}
	},
	change : function(em){
		if(em.files.length > 0){
			win.loading();
			var data = '';
			var file = em.files[0];
			var cvs = $('<canvas>').width(640).attr('width', 640);
			var url = window.URL.createObjectURL(file);
			var img = $('<img/>').attr('src', url);
			img.load(function(){
				var imgWidth = this.width;
				var imgHeight = this.height;
				var imgR = imgWidth / imgHeight;
				var r = 640 / imgWidth;
				var height = 640 / imgWidth * imgHeight;
				var context = cvs[0].getContext('2d');
				cvs.height(height).attr('height', height);
				//居中裁剪
				context.drawImage(this, 0, 0, 640, height);

				data = cvs[0].toDataURL('image/jpeg');
				data = data.replace('data:image/jpeg;base64,', '');

				ajax('Member/Mypic/upload', {file:[data]}, function(d){
					if(d.status == 1){
						var path = d.info.path;
						path = path.replace('http://img.'+ DOMAIN +'/', '');
						win.ws.send({to_id:'admin', msg:path, type:1}, 'add');
					}else
						$.alert(d.info,'error');
				}, 2);
			});
		}
	},
	sendGoods : function(id, type){
		win.ws.sendGoods(id, type, 'admin');
		$('.page_MsgIM .hs').removeClass('show');
	},
	myfocu:function(){
		// document.activeElement.scrollIntoViewIfNeeded();
		var top = $('.page_MsgIM.wrapper').get(0).scrollHeight - $('.page_MsgIM.wrapper').height();
		$('.page_MsgIM.wrapper').scrollTop(top);
	},
	onload : function(){
		if(!member){
			win.login();
			return;
		}
		if(win.ws.power == 0){
			$.alert('客服系统尚未开启', function(){
				page.back();
			},'error');
			return;
		}
		win.lasttime = win.lasttime||'';
		if($('.page_MsgIM .msgList li').size() == 0){
			if(localStorage.historyMessages){
				var data = JSON.parse(localStorage.historyMessages);
				if(data.admin){
					data = data.admin;
					var pretime = 0;
					for(var i in data){
						if(data[i].datetime > pretime + 300)
							data[i].date = data[i].datetime.toString().timeFormat('Y-m-d H:i');
						pretime = parseInt(data[i].datetime);
						MsgIMObject.add(data[i], 0);
						win.lasttime = data[i].datetime;
					}
				}
			}
		}
		var data = {
			lasttime : win.lasttime
		}

		$('.page_MsgIM .writer').on('blur', function(){
			$('.page_MsgIM .facesBox').removeClass('show');
		});
		$('.page_MsgIM .writer').on('keyup focus', function(){
			if($(this).text() == '' && $(this).find('img').size() == 0){
				$('.page_MsgIM .sendBtn').hide();
				$('.page_MsgIM .imageBtn').show();
			}else{
				$('.page_MsgIM .sendBtn').show();
				$('.page_MsgIM .imageBtn').hide();
			}
		});
		$('.page_MsgIM .sendBtn').on('click', function(){
			var content = $('.page_MsgIM .writer').html();
			if(win.ws.sendText(content, 'admin')){
				$('.page_MsgIM .writer').empty().focus();
			}
		});
		$('.page_MsgIM .imageBtn').on('click', function(){
			$('#myMsgImUploadBtn').click();
		});
		$('.page_MsgIM .goodslist').on('click', function(){
			if($('.page_MsgIM .history a').size() == 0){
				var code = '';
				var hs = JSON.parse(localStorage.yummyhistory);
				var types = ['饭局', '商品', '众筹'];
				for(var i in hs){
					code += '<a href="javascript:void(0);" onclick="MsgIMObject.sendGoods('+ hs[i].id +', '+ hs[i].type +')">';
					code += '<img src="'+ hs[i].path +'">';
					code += '<div class="text"><b>'+ types[hs[i].type] +'：</b>'+ hs[i].title +'</div>';
					code += '</a>';
				}
				$(code).appendTo('.page_MsgIM .history');
			}
			if($('.page_MsgIM .hs').hasClass('show'))
				$('.page_MsgIM .hs').removeClass('show');
			else
				$('.page_MsgIM .hs').addClass('show');
		});

		setTimeout(function(){
			win.ws.send(data, 'create');
			MsgIMObject.facesCreate();
		}, 300);
	}
};
