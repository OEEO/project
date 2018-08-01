var goodsDetailObject ={
	goods_id : null,
	stocks : 0,
	//提交订单
	submitOrder : function(){
		if(goodsDetailObject.stocks == 0){$.alert('名额已满', 'error'); return false;}
		jump('confirmBuy', {goods_id : goodsDetailObject.goods_id});
	},
	//闪购  	flash_buy(d.marketing, $('.actLastTime'));
	//flash_buy : function (d, $em){
	//	$em.find('.title font').text(d.title);
	//	$em.find('.sy span').text(d.limit);
	//	var time = Math.round(new Date().getTime()/1000);
	//	if(time < d.start_time){
	//		$em.find('.w').text('距离开抢:');
	//		countdown($em, d.start_time, flash_buy);
	//	}else if(time < d.end_time){
	//		$em.find('.w').text('距离结束:');
	//		//$('.priceMenu .price').html('<span class="l">￥'+ d.price +'</span><span class="r">' + $('.priceMenu .price').html() + '</span>');
	//		$('.priceMenu .price').html('￥'+ d.price + $('.priceMenu .price').html());
	//		//$em.find('span').css({'background':'#f1da72', 'color':'#F30'});
	//		countdown($em, d.end_time, flash_buy);
	//	}else {
	//		$em.remove();
	//		//$('.priceMenu .price').html($('.priceMenu .price .r').html());
	//		$('.priceMenu .price').html($('.priceMenu .price del').html());
	//	}
	//},
	//
	////闪购倒计时函数
	//countdown:function ($em, stime, fn){
	//	var time = Math.round(new Date().getTime()/1000);
	//	if(stime - time <= 0){
	//		fn();
	//		return;
	//	}
	//	$em.find('.h').text(Math.floor((stime - time) / 3600)+':');
	//	$em.find('.i').text(Math.floor((stime - time) % 3600 / 60)+':');
	//	$em.find('.s').text(Math.floor((stime - time) % 3600 % 60));
	//
	//	window.setTimeout(function(){
	//		countdown($em, stime, fn);
	//	}, 1000);
	//},
	//
	//提交订单
	submitOrder : function (){
		jump('confirmBuy', {goods_id:goodsDetailObject.goods_id});
	},
	//提交拼团
	submitPiece : function(){
		jump('confirmBuy', {goods_id:goodsDetailObject.goods_id, piece:1});
	},

	back: function () {
		if (win.get.backUrl) {
			location.href = decodeURIComponent(win.get.backUrl);
		} else {
			page.back();
		}
	},
	pieceStartTime: 0,
	pieceEndTime: 0,

	//showTimes:function(){
	//	$('.timesLay').css('display','block');
	//	$('#mainContent').addClass('G_content');
	//
	//	var top = ($(window).height()-$('.timesLay .views').height())/40;
	//	var wid = ($(window).width()-$('.timesLay .views').width())/2;
	//	$('.views').css({'margin-top':($(window).height()-$('.timesLay .views').height())/3,'margin-left':($(window).width()-$('.timesLay .views').width())/2});
	//
	//	document.body.style.overflow='hidden';
	//	document.ontouchmove = function(e){e.preventDefault();} //文档禁止 touchmove事件
	//},
	//
	//hideCode:function (){
	//	$('.timesLay').css('display','none');
	//	$('#mainContent').removeClass('G_content');
	//	document.body.style.overflow='visible';
	//	document.ontouchmove = function(e){} //文档禁止 touchmove事件
	//},

	showPiecePsBox : function(){
		if($('.page_goodsDetail.piecePsBox').size() == 0){
			var code = '<div class="page_goodsDetail piecePsBox">';
			code += '<div class="box">';
			code += '<div class="ps_title">拼团规则</div>';
			code += '<div class="ps_content"><div class="rule_list"><p>1.新老用户均可选择您心仪的商品开团，完成支付即可开团，开团后您需要邀请好友参与拼团。</p><p>2.在限定时间内邀请到规定人数的好友参团，才可以享受拼团优惠。</p><p>3.若限定时间内参团人数不足、库存不足或商品下架，则拼团失败，系统将在3天内退款。</p><p>4.订单信息可在吖咪APP或吖咪yummy公众号（yamiyummy194）查看。</p></div><button>确定</button></div>';
			code += '</div>';
			code += '</div>';
			$(code).appendTo('#fixed').find('button').click(function(){
				goodsDetailObject.showPiecePsBox();
			});
			$('#fixed').show();
		}else{
            $('#fixed').hide();
			$('.page_goodsDetail.piecePsBox').remove();
		}
	},
    shareSuccess: function (id) {
        return function(target) {
            ajax('Home/Index/shareSuccess', {type: 1, item_id: id, target: target, platform: 0});
        }
    },
	followCollection: function (type_id, operate) {
		ajax('Member/Follow/changeCollect', {type_id: type_id, operate: operate}, function (d) {
			if (d.status == 1) {
				operate == 1
					? ($('.page_goodsDetail .follow-img').addClass('active') , $('.page_goodsDetail [data-btn="like"]').data('operate', '0'))
					: ($('.page_goodsDetail .follow-img').removeClass('active') , $('.page_goodsDetail [data-btn="like"]').data('operate', '1'));
			} else {
				$.alert(d.info);
			}
		});
	},
	nofound: function () {
        var img=event.srcElement;
		img.src = '/images/blank.jpg';
        img.onerror = null;
	},
	getPieceLimitTime: function(startTime, endTime, curTime, notShowDay) {
		var timediff = endTime - curTime;
        if (timediff <= 0) {
            return '已结束';
        }

        var _f = {
            'd+': ('00' + Math.floor(timediff / 86400)).slice(-2),
            'h+': ('00' + Math.floor(timediff % 86400 / 3600)).slice(-2),
            'm+': ('00' + Math.floor(timediff % 86400 % 3600 / 60)).slice(-2),
            's+': ('00' + Math.floor(timediff % 86400 % 60)).slice(-2)
		};

		if (notShowDay && _f['d+'] == '00') {
			return _f['h+'] + ':' + _f['m+'] + ':' + _f['s+'];
		} else {
			return _f['d+'] + '天 ' + _f['h+'] + ':' + _f['m+'] + ':' + _f['s+'];
		}

        
	},
	qrcodeBox: function (src,src2,title,price){
		if(win.get.android == 1){
			console.log(JSON.stringify(win.shareData));
			console.log(android);
			console.log(android.showshare);
			var zz = android.showshare(JSON.stringify(win.shareData));
			console.log(zz);
			return;
		}
//		var texts = '推荐给好友';
		var tip = '';
		/*
		if(text){
			var tip = '<span class="tip">Tips：请定向分享给想要邀请的对象，一个消费码只对应一位客人哦.</span>';
		}
		*/
		/*$('<div>').addClass('shareBox').html('<h3>点击右上角分享，发送本活动邀请函:</h3><p><span class="left">微信好友</span><span class="right">微信朋友圈</span></p>').appendTo('body').click(function(){
			$(this).remove();
		});*/
		/*<img style="width:31rem;height:38rem;" src="'+ bg +'" id="canvas">*/
		$('<div>').addClass('qrcodeBox').html('<div><div> ' +
													'<div style="text-align:center;"><img id="img" src="" alt="" crossorigin="anonymous" width="100%" height="100%"></div>' +
												'</div>' +
												'<span class="qrcodeBox_know">长按图片保存到手机</span>' +
											'</div>').appendTo('body').click(function(){
			// $(this).remove();
			$('.qrcodeBox').click(function(){
				$('.qrcodeBox').remove();
			});
			function stopPropagation(e) {
		        e = e || window.event;
		        if(e.stopPropagation) { //W3C阻止冒泡方法
		            e.stopPropagation();
		        } else {
		            e.cancelBubble = true; //IE阻止冒泡方法
		        }
		    }
		    document.getElementById('img').onclick = function(e) {
		        stopPropagation(e);
		    }
		});
		var canvas=document.getElementById('cv');
		var ctx=canvas.getContext('2d');

		var img=new Image();
		img.setAttribute('crossOrigin', 'anonymous');
		var img2=new Image();
		img2.setAttribute('crossOrigin', 'anonymous');
		var img3=new Image();
		img3.setAttribute('crossOrigin', 'anonymous');
//    	img.src="http://www.baidu.com/img/bd_logo1.png";
//    	img.src="http://img.m.yami.ren/20171205/b5e823c4000d6d103c211108e459899d9d80d336.jpg";
//    	img.src="http://api.m.yami.ren/Member/Verify/curl";
//		img3.src='http://api.m.yami.ren/Member/Verify/curl2?mainpic='+d.mainpic;
		img3.src=src2;
//		console.log(src2);
//    	img.src="http://yamiimg.oss-cn-shenzhen.aliyuncs.com/20171205/b5e823c4000d6d103c211108e459899d9d80d336.jpg";
//    	img.src="http://m.yami.ren/images/share_bg.jpg";
		img.src="http://test.yummy194.cn/images/share_bg.jpg";
		img2.src=src;
		if (img.complete) {
	    	ctx.drawImage(img,0,0,266*3,366*3);
	    	ctx.font = 9*3 + "px Courier New";
		    ctx.fillStyle = "#A9A9A9";
		    ctx.fillText("长按扫描二维码", 192*3, 283*3);
		   	ctx.font = 12*3 + "px Courier New";
		    ctx.fillStyle = "#A9A9A9";
		    ctx.fillText("推荐你参与", 20*3, 205*3);
		    ctx.font = 20*3 + "px 黑体";
		    ctx.fillStyle = "black";
		    ctx.fillText("“", 8*3, 235*3);
		    ctx.fillText("”", 240*3, 270*3);
		    ctx.font = 14*3 + "px Courier New";
		    ctx.fillStyle = "black";
		    if (title.length < 16) {
		    	ctx.fillText(title, 30*3, 245*3);
		    } else {
		    	var t1 = title.substring(0,16);
		    	var t2 = title.substring(16);
		    	if (t2.length > 14) t2 = t2.substring(0, 14) + '...';
		    	ctx.fillText(t1, 27*3, 235*3);
		    	ctx.fillText(t2, 27*3, 255*3);
		    }
		    ctx.font = 14*3 + "px Courier New";
		    ctx.fillStyle = "red";
		    ctx.fillText("￥", 20*3, 350*3);
		    ctx.font = 20*3 + "px 微软雅黑";
		    ctx.fillStyle = "red";
		    ctx.fillText(price, 35*3, 350*3);
		    if (img3.complete) {
	    		ctx.drawImage(img3,0,0,266*3,185*3);
	    		if (img2.complete) {
		    			ctx.drawImage(img2,185*3,285*3,75*3,75*3);
			    	var imgData =canvas.toDataURL("image/png");
					$("#img").attr("src", imgData);
	    		} else {
	    			img2.onload = function ()
					{
		    			ctx.drawImage(img2,185*3,285*3,75*3,75*3);
			    	var imgData =canvas.toDataURL("image/png");
					$("#img").attr("src", imgData);
					}
	    		}
		    } else {
			   	img3.onload = function ()
				{
		    		ctx.drawImage(img3,0,0,266*3,185*3);
		    		if (img2.complete) {
		    			ctx.drawImage(img2,185*3,285*3,75*3,75*3);
				    	var imgData =canvas.toDataURL("image/png");
						$("#img").attr("src", imgData);
		    		} else {
		    			img2.onload = function ()
						{
		    			ctx.drawImage(img2,185*3,285*3,75*3,75*3);
				    	var imgData =canvas.toDataURL("image/png");
						$("#img").attr("src", imgData);
						}
		    		}
				}
		    }
		} else {
			img.onload = function ()
			{
	    		ctx.drawImage(img,0,0,266*3,366*3);
		    	ctx.font = 9*3 + "px Courier New";
			    ctx.fillStyle = "#A9A9A9";
			    ctx.fillText("长按扫描二维码", 192*3, 283*3);
			   	ctx.font = 12*3 + "px Courier New";
			    ctx.fillStyle = "#A9A9A9";
			    ctx.fillText("推荐你参与", 20*3, 205*3);
			    ctx.font = 20*3 + "px 黑体";
		    	ctx.fillStyle = "black";
		    	ctx.fillText("“", 8*3, 235*3);
		    	ctx.fillText("”", 240*3, 270*3);
			    ctx.font = 14*3 + "px Courier New";
			    ctx.fillStyle = "black";
			    if (title.length < 16) {
			    	ctx.fillText(title, 30*3, 245*3);
			    } else {
			    	var t1 = title.substring(0,16);
			    	var t2 = title.substring(16);
			    	if (t2.length > 14) t2 = t2.substring(0, 14) + '...';
			    	ctx.fillText(t1, 27*3, 235*3);
			    	ctx.fillText(t2, 27*3, 255*3);
			    }
			    ctx.font = 14*3 + "px Courier New";
			    ctx.fillStyle = "red";
			    ctx.fillText("￥", 20*3, 350*3);
			    ctx.font = 20*3 + "px 微软雅黑";
			    ctx.fillStyle = "red";
			    ctx.fillText(price, 35*3, 350*3);
			    if (img3.complete) {
		    		ctx.drawImage(img3,0,0,266*3,185*3);
		    		if (img2.complete) {
		    			ctx.drawImage(img2,185*3,285*3,75*3,75*3);
				    	var imgData =canvas.toDataURL("image/png");
						$("#img").attr("src", imgData);
		    		} else {
		    			img2.onload = function ()
						{
		    			ctx.drawImage(img2,185*3,285*3,75*3,75*3);
				    	var imgData =canvas.toDataURL("image/png");
						$("#img").attr("src", imgData);
						}
		    		}
			    } else {
				   	img3.onload = function ()
					{
			    		ctx.drawImage(img3,0,0,266*3,185*3);
			    		if (img2.complete) {
		    			ctx.drawImage(img2,185*3,285*3,75*3,75*3);
					    	var imgData =canvas.toDataURL("image/png");
							$("#img").attr("src", imgData);
			    		} else {
			    			img2.onload = function ()
							{
		    			ctx.drawImage(img2,185*3,285*3,75*3,75*3);
					    	var imgData =canvas.toDataURL("image/png");
							$("#img").attr("src", imgData);
							}
			    		}
					}
			    }
			}
		}
/*
		var canvas=document.getElementById('cv');
		var ctx=canvas.getContext('2d');
		var img=new Image();
//		img.crossOrigin = "Anonymous";
		var img2=new Image();
//		img2.crossOrigin = "Anonymous";
		img.onload = function () //确保图片已经加载完毕
		{
    		ctx.drawImage(img,0,0);
		   	//设置字体样式
		    ctx.font = "15px Courier New";
		    //设置字体填充颜色
		    ctx.fillStyle = "black";
		    //从坐标点(50,50)开始绘制文字
		    ctx.fillText("推荐您参与", 30, 60);

//		    ctx.fillText("111", 30, 80);
		    ctx.font = "10px Courier New";
		    ctx.fillStyle = "#A9A9A9";
		    ctx.fillText("长按扫描二维码", 195, 248);

		    ctx.font = "10px Courier New";
		    ctx.fillStyle = "red";
		    ctx.fillText("￥199", 30, 248);
 //   		ctx.fillStyle='#FF0000';
//			ctx.fillRect(0,0,80,100);

			img2.onload = function () //确保图片已经加载完毕
			{
	    		ctx.drawImage(img2,190,250,80,80);
	    		var imgData =canvas.toDataURL("image/png");
				$("#img").attr("src", imgData);
			}

		}
		//var img=document.getElementById("header");
//		ctx.drawImage(img,0,0);
//		img.src=src;
//		img.src="http://m.yami.ren/images/share_bg.jpg";
//		img.src="http://img.m.yami.ren/20171205/b5e823c4000d6d103c211108e459899d9d80d336.jpg";
//		img.src="http://www.baidu.com/img/bd_logo1.png";
//		img2.src="http://www.w3school.com.cn/i/eg_tulip.jpg";
		img2.src=src;

*/

		/*
		if(text){
			$('.qrcodeBox div').css('height','auto');
			$('.qrcodeBox p').css('background','#fff');
		}else{
//			$('.profitBox div').css('height','6rem');
			$('.qrcodeBox p').css('background','none');
		}
		*/
		var l = $('.qrcodeBox').size();
		if(l>1){
			for(var i = 1; i < l; i++){
				$('.qrcodeBox').eq(i).remove();
			}
		}
	},

	getOtherPiece: function (goods_id) {
		console.log('goods_id = ', goods_id);
		ajax('Goods/Goods/getOtherPiece', { goods_id: goods_id }, function (d) {
			console.log(d);
		});
	},

	thumbName: function (name) {
		if (!name || name.length < 3) {
			return '***';
		} else {
			return name[0] + '***' + name[name.length - 1];
		}
	},

	onload : function(){
		if (win.get.r === 'y') {
			location.href = location.origin + '?page=choice-goodsDetail&goods_id=' + win.get.goods_id + '&t=' + Date.now();
		}


		if (page.names[page.names.length - 2] == 'myProfit') {
			$('.page_goodsDetail .title_s').css('display', 'block');
			$('.page_goodsDetail .recommend').css('display', 'none');
		}
		ajax('Member/Profit/getShareMoney', {'goods_id': win.get.goods_id}, function(d) {
			$('.share_money font').text(d);
		});
		$('.page_goodsDetail .qrcode').click(function () {
//			$.alert('二维码');
			ajax('Member/Profit/getShareImg', {'goods_id': win.get.goods_id}, function(d) {
				goodsDetailObject.qrcodeBox(d.src1,d.src2,d.title,d.price);
				console.log(d);
				/*
				ajax('Goods/Goods/getDetail', {'goods_id':goodsDetailObject.goods_id}, function(d){
					console.log(d.mainpic);
					console.log(img3.src);
				});
				*/
			});
		});
		$('.page_goodsDetail .profit_share').click(function () {
			showShareBox();
		});

		if (window.location.href.indexOf('?') > 0) {
			var CusUrlArr = window.location.href.split('?')[1];
			var CusUrlArr = CusUrlArr.split('&');
			var Cuscommands = {};
		    for (var i in CusUrlArr) {
		        var arr = CusUrlArr[i].split('=');
		        Cuscommands[arr[0]] = decodeURI(arr[1]);
		    }
		    if (Cuscommands.page && Cuscommands.invitecode) {
		    	if (Cuscommands.page.indexOf('goodsDetail') > 0) console.log(Cuscommands);
		    	ajax('Member/Profit/CustomerVisit', {'invitecode':Cuscommands.invitecode,'type':1,'project_id':Cuscommands.goods_id}, function(d) {

				});
		    }

		}


		if(win.get.goods_id){
			goodsDetailObject.goods_id = win.get.goods_id;
		}else{
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
		}

		ajax('Goods/Goods/getDetail', {'goods_id':goodsDetailObject.goods_id}, function(d){

			if (d.status == 0) {
				$.alert(d.info);
				jump('choice');
				return;
			}


			// for(var i in d.edge){
			// 	desc += d.edge[i] + ' ';
			// }
			var url = win.host + '?page=choice-goodsDetail&goods_id=' + goodsDetailObject.goods_id;
			if(member && member.invitecode){
				url += '&invitecode=' + member.invitecode;
			}
			// if (desc == ' ' || desc == ''){
			// 	desc = d.edge[0]
			// }

			var desc = d.edge[0];
			var title = d.title;

			// 如果是拼团
			if (d.piece) {
				title = title + '限时团购中！拼团只需' + d.piece.price + '元!';
				desc = d.edge[0];
			}
			share(title, desc, url, d.mainpic);

			goodsDetailObject.defaultPics = d.defaultPics;
			//分享绑定
			$('.page_goodsDetail .share').click(showShareBox);
			$('.header.page_goodsDetail .title').text(d.catname);

			script.load('plugins/scrollByJie', function(){
				//主图
				if(d.pics_group && d.pics_group.length > 0){
					var sol = new myScroll();
					sol.speed = 3;
					sol.div = ".page_goodsDetail .bodyTop";
					for(var i in d.pics_group){
						sol.src.push(d.pics_group[i]);
					}
					sol.start();
				}else{
					$('.page_goodsDetail .bodyTop').html('<img src="images/actImg.jpg">');
				}
			});

			//主标题
			$('.page_goodsDetail .title_t').text(d.title);
			var edges='';
			for(var i in d.edge){
				edges +='<li class="t_b">'+ d.edge[i] +'</li>';
			}
			$('.page_goodsDetail .edges').html(edges);

			//判断是否已经收藏
			if(d.isCollect) {
                $('.page_goodsDetail [data-btn="like"]').data('operate', '0');
                $('.page_goodsDetail .follow-img').addClass('active');
            } else {
                $('.page_goodsDetail [data-btn="like"]').data('operate', '1');
                $('.page_goodsDetail .follow-img').removeClass('active');
			}
			$('.page_goodsDetail .collect').attr('data', goodsDetailObject.goods_id).click(function(){
				setCollect(this, 1);
			});

			//库存数量
			$('.page_goodsDetail .data i').text(d.stocks);
			goodsDetailObject.stocks = d.stocks;
			if(d.stocks == 0){
				$('.page_goodsDetail .submitBtn').css('background', '#C0C0C9').removeAttr('onclick');
			}

			$('.page_goodsDetail .data .selled').text('已售' + d.selled + '份');

			// fake selled

			if (goodsDetailObject.goods_id == 68) {
				$('.page_goodsDetail .data .selled').text('已售' + ((+d.selled) + 29) + '份');
			}

			if (goodsDetailObject.goods_id == 67) {
				$('.page_goodsDetail .data .selled').text('已售' + ((+d.selled) + 21) + '份');
			}

			//是否包邮
			if(parseFloat(d.shipping) > 0)$('.page_goodsDetail .data .tag').remove();

			//达人信息
			//$('.page_goodsDetail .darenDetail').attr('href', 'javascript:jump(\'daRen\', {member_id:'+ d.member_id +'});');
			$('.page_goodsDetail .darenDetail img').attr('src', d.headpic);
			$('.page_goodsDetail .darenDetail .t').text(d.nickname);
			$('.page_goodsDetail .darenDetail .b').text(d.introduce);

			//商品规格
			var code = '';
			for(var i in d.attrs){
				code += '<tr>';
				code += '	<td class="left">'+ d.attrs[i].name +'</td>';
				code += '	<td class="right">'+ d.attrs[i].value +'</td>';
				code += '</tr>';
			}
			$('.page_goodsDetail .attr_list').html(code);

			$('.page_goodsDetail #goods_content').html(d.content);

			//贴心提示
			var code = '';
			for(var i in d.notice){
				code += '<li>'+ d.notice[i] +'</li>';
			}
			$('.page_goodsDetail .tell_List').html(code);

			//价格
			if (d.price.match(/\d+\.00/ig)) {
                $('.page_goodsDetail #price').html((+d.price).toFixed(0));
			} else {
                $('.page_goodsDetail #price').html((+d.price).toFixed(2));
			}

			//查看详情
			$('.page_goodsDetail .more').click(function(){
				jump('goodsContent', {goods_id:goodsDetailObject.goods_id});
			});

			//拼团判断
			if(d.piece){
				var em = $('.page_goodsDetail .pieceBox');
				em.show();
				em.find('.count').text(d.piece.count);
				em.find('.limit_time').text(d.piece.limit_time);
				if (d.piece.limit_num == 0) {
					$('.limit_num_text').css('display', 'none');
				}
				em.find('.limit_num').text(d.piece.limit_num);
				em.find('button').click(goodsDetailObject.showPiecePsBox);

				$('.page_goodsDetail .data').addClass('is_piece');
                //
                // var code = '<div class="price" onclick="goodsDetailObject.submitOrder();"><span>'+ parseFloat(d.price).priceFormat() +'</span>元直接购买</div>';
                // code += '<div class="piece" onclick="goodsDetailObject.submitPiece();"><span>'+ parseFloat(d.piece.price).priceFormat() +'</span>元拼团</div>';
                // $('.page_goodsDetail.priceMenu').html(code).addClass('pieceMenu');
				$('.page_goodsDetail .submitLeft span').html('元单独购');

                $('.page_goodsDetail .submitBtn').html('<i>' + (+d.piece.price) + '</i>元拼团');
				$('.page_goodsDetail .submitLeft').data('submit', 'order');
				$('.page_goodsDetail .submitBtn').data('submit', 'piece');

                if (d.status == 1) {
                    $('.page_goodsDetail .submitLeft').click(function () {
                        goodsDetailObject.submitOrder();
                    });
				}

                $('.page_goodsDetail .limit_time_tip').show();
				$('.page_goodsDetail .limit_time_tip .time').text(goodsDetailObject.getPieceLimitTime(d.piece.start_time, d.piece.end_time, Date.now() / 1000));

				goodsDetailObject.pieceStartTime = d.piece.start_time;
				goodsDetailObject.pieceEndTime = d.piece.end_time;
				win.goodsDetailInterval = setInterval(function () {
					try {
                        if (d.end_time < Date.now() / 1000) {
                            clearInterval(win.goodsDetailInterval);
                            $('.page_goodsDetail .submitBtn').addClass('disabled');
                        } else {
                            $('.page_goodsDetail .limit_time_tip .time').text(goodsDetailObject.getPieceLimitTime(goodsDetailObject.pieceStartTime, goodsDetailObject.pieceEndTime, Date.now() / 1000));
                        }
					} catch(e) {
                        clearInterval(win.goodsDetailInterval);
					}
				}, 1000);

				if (d.piece.reward) {
					$('.page_goodsDetail .group-head-welfare-wrap').show();
					$('.page_goodsDetail .group-head-welfare').text(d.piece.reward);
				}
			} else {
                $('.page_goodsDetail .limit_time_tip').hide();
			}


			// 拼团广场模块
			if (!d.piece || !d.piece_group || d.piece_group.pieces_people_count == 0) {
				$('.page_goodsDetail .group-booking-box').hide();
			} else {
				$('.page_goodsDetail .group-booking-box').show();

				var $list = $('.page_goodsDetail .group-booking-box .group-booking-list');
				var $item = $('.page_goodsDetail .group-booking-box .group-booking-item');
				var $newItemClone = $item.clone();
				$item.remove();

				$('.page_goodsDetail .group-booking-title .title-num').text(d.piece_group.pieces_people_count);

				for (var i = 0; i < d.piece_group.list.length; i++) {
					(function (k) {
						var item = d.piece_group.list[k];
						var id = i;
						var $itemClone = $newItemClone.clone();
						// 多少人在拼团
						$itemClone.find('.title-num').text();

						if (!item.headpath) {
							$itemClone.find('.img-wrap').attr('src', 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg');
						} else {
							$itemClone.find('.img-wrap').attr('src', item.headpath);
						}
						
						$itemClone.find('.name').text(goodsDetailObject.thumbName(item.nickname));
						// 还差多少人成团
						$itemClone.find('.short-of-num').text(item.remain);
						$itemClone.find('.left-time').text(item.piece_limit_time);
						$itemClone.find('.take-in-btn').attr('data-id', item.id);
						// 
						$itemClone.attr('data-end-time', item.end_time);
						$itemClone.find('.left-time').text(goodsDetailObject.getPieceLimitTime(0, item.end_time, Date.now() / 1000, true));
						$list.append($itemClone);
					})(i);
				}

				// 倒计时
				var _timeId;
				var pieceGroupListTimeId;

				pieceGroupListTimeId = setInterval(function () {
					$('.page_goodsDetail .group-booking-item').each(function () {
						var $this = $(this);
						var endTime = $this.data('end-time');

						if (endTime < Date.now() / 1000) {
							$this.remove();
						} else {
							$this.find('.left-time').text(goodsDetailObject.getPieceLimitTime(0, endTime, Date.now() / 1000, true));
						}
					});
					
				}, 1000);

				//事件绑定

				// 查看更多
				$('.page_goodsDetail .group-booking-title .title-more').click(function(e) {
					$('.page_goodsDetail .more-modal-wrap').show();
					var $list = $('.page_goodsDetail .group-booking-box .group-booking-list');
					$('.page_goodsDetail .more-modal .main').html($list.clone(true));
				});

				

				var $lefTime = $('.page_goodsDetail .take-in-modal-wrap .left-time'); // 某个团的倒计时

				// 弹出拼团页
				$('.page_goodsDetail .group-booking-item .take-in-btn').click(function(e) {
					var id = $(this).attr('data-id');
					$('.page_goodsDetail .more-modal-wrap').hide();
					$('.page_goodsDetail .take-in-modal-wrap').find('.take-in-btn').attr('data-id', id);


					for (var i = 0, num = d.piece_group.list.length; i < num; i++) {
						var item = d.piece_group.list[i];

						if (item.id == id) {
							$('.page_goodsDetail .take-in-modal-wrap .short-of-num').text(item.remain);
							$('#piece-group-host').text(goodsDetailObject.thumbName(item.nickname));
							$lefTime.text(goodsDetailObject.getPieceLimitTime(0, item.end_time, Date.now() / 1000, true));
							
							// 倒计时
							_timeId = setInterval(function () {
								console.log(item.end_time, Date.now() / 1000);
								$lefTime.text(goodsDetailObject.getPieceLimitTime(0, item.end_time, Date.now() / 1000, true));
							}, 1000);

							var fragment = document.createDocumentFragment();

							item.joiner.map(function (men) {
								// <img class="img-wrap" src="./images/avatar-placeholder.png" alt="头像" />
								var img = document.createElement('img');
								img.className = 'img-wrap';

								if (!men.joiner_path) {
									img.setAttribute('src', 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg');
								} else {
									img.setAttribute('src', men.joiner_path);
								}
								
								fragment.appendChild(img);
							});

							for (var j = 0; j < item.remain; j++) {
								var img = document.createElement('img');
								img.setAttribute('src', './images/avatar-placeholder.png');
								img.className = 'img-wrap';
								fragment.appendChild(img);
							}

							$('.page_goodsDetail .imgs').empty();
							$('.page_goodsDetail .imgs')[0].appendChild(fragment);

							// $('.page_goodsDetail .imgs .img-wrap').eq(0).attr('src', item.headpath);
						}
					}

					$('.page_goodsDetail .take-in-modal-wrap').show();
				});

				// 真正参加拼团
				$('.page_goodsDetail .take-in-modal .take-in-btn').click(function(e) {
					$('.page_goodsDetail .take-in-modal-wrap').hide();
					var id = $(this).attr('data-id');
					clearInterval(_timeId);
					jump('groupsDetail', { groups_id: id, type: 1 });
				});

				// cancel按钮
				$('.page_goodsDetail .modal .cancel-icon').click(function(e) {
					$(this).parents('.modal-wrap').hide();
					clearInterval(_timeId);
				});
			}

			//评论列表
			var code = '';
			if(d.comment.length>0){
				for(var i in d.comment){
					if(i==1){
						code += '<div class="com_list no_border_bottom">';
					}else{
						code += '<div class="com_list">';
					}
					code += '	<div class="h_pic">';
					code += '		<img src="'+ d.comment[i].head_path +'" />';
					code += '	</div>';
					code += '	<div class="pic_right">';
					code += '		<div class="name_title">';
					code += '			<div class="names">'+ d.comment[i].nickname +'</div>';
					code += '			<span>'+ d.comment[i].datetime +'</span>';
					code += '		</div>';
					code += '<p align="center" class="t_content">'+ d.comment[i].content +'</p>';
					if(d.comment[i].pics.length > 0){
						code += '<div class="imges">';
						for(var j in d.comment[i].pics){
							code += '<img src="'+ d.comment[i].pics[j] +'">';
						}
						code += '</div>';
					}
					code += '</div>';
					code += '<div class="clearfix"></div>';
					code += '</div>';
				}
				code +='<p align="center" class="more_com"><a href="javascript:jump(\'commentList\', {goods_id:'+goodsDetailObject.goods_id+'})" class="allEvaluation"><span>查看更多评论</span></a></p>';
			}else{
				code +='<p align="center"><a href="javascript:void(0);" class="allEvaluation">暂时没有评价</a></p>';
			}
			$('.page_goodsDetail .commentList').html(code);

			//相关活动
			// if(d.tips && d.tips.length > 0){
			// 	var code = '';
			// 	for(var i in d.tips){
			// 		code += '<a href="javascript:jump(\'tipsDetail\', {tips_id:'+ d.tips[i].id +'});">';
			// 		code += '	<div class="t"><img src="'+ d.tips[i].path +'"><span>'+ parseFloat(d.tips[i].price).priceFormat() +'<small>元/份</small></span></div>';
			// 		code += '	<div class="b">'+ d.tips[i].title +'</div>';
			// 		code += '</a>';
			// 	}
			// 	$('.page_goodsDetail .recommend .tips_list').html(code);
			// }else{
			// 	$('.page_goodsDetail .recommend').remove();
			// }

			// 推荐好物
            if(d.other && d.other.length > 0){
                var code = '';
                for(var i in d.other){
                	var item = d.other[i];
                    code += '<a href="javascript:;" class="item" data-goods-id="' + item.id + '"><img src="' + item.path + '" alt="" onerror="goodsDetailObject.nofound();">';
                    code += '<p class="tips_title">' + item.title + '</p>';
                    code += '<div class="item_sub">';
                    if (item.isTuan === 1) {
                    	code += '<span class="tuan">拼</span>';
                        code += '<span class="price">' + (+item.piece_price) + '<i>元</i></span>';
					} else {
                        code += '<span class="price">' + (+item.price) + '<i>元</i></span>';
					}
                  // fake selled
                  if (item.id == 68) {
                    item.count = (+item.count + 29)
                  }

                  if (item.id == 67) {
                    item.count = (+item.count + 21)
                  }


                    code += '<span class="sub_title">已售' + item.count + '份</span>';
                    code += '</div></a>';
                }
                $('.page_goodsDetail .recommend .tips_list').html(code);
            }else{
                $('.page_goodsDetail .recommend').remove();
            }

            var url = win.host + '?page=choice-goodsDetail&goods_id=' + goodsDetailObject.goods_id;
            if(member && member.invitecode){
                url += '&type=2&invitecode=' + member.invitecode;
            }


			var desc = d.edge[0];
			var title = d.title;
			// 如果是拼团
			if (d.piece) {
				title = title + '限时团购中！拼团只需' + d.piece.price + '元!';
				desc = d.edge[0];
			}
            share(title, desc, url, d.mainpic, goodsDetailObject.shareSuccess(goodsDetailObject.goods_id));

            $('.page_goodsDetail').on('click', '[data-btn]', function(){
            	var type = $(this).data('btn');
                if (type === 'share') {
                    if (Yami.platform() === 'android') {
                        Yami.share({
                            title: title,
                            desc: desc,
                            link: url,
                            imgUrl: d.mainpic
                        });
                    } else {
                        showShareBox();
                    }
                } else if (type === 'like') {
					goodsDetailObject.followCollection(goodsDetailObject.goods_id, $(this).data('operate') || '1');
				} else if (type === 'chat') {
                    $.alert('在线客服系统已关闭。请添加吖咪客服微信:yami194','error');
				}
            });

            $('.page_goodsDetail').on('click', '[data-goods-id]', function () {
            	var id = $(this).data('goods-id');
            	page.reload('goodsDetail', {goods_id: id});
			});

            if (d.status == 1) {
                $('.page_goodsDetail .submitBtn').click(function () {

                    if ($(this).data('submit') === 'piece') {
                        goodsDetailObject.submitPiece();
                    } else {
                        goodsDetailObject.submitOrder();
                    }

                });
			} else {
            	$('.page_goodsDetail .fr').addClass('disabled');
			}
		}, 2);
	}
};
