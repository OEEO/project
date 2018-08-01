var myProfitObject = {
	winScrollSock: false,
	_top: 0,
	preLiNum: -1,//值 -1 表示 隐藏
	scrollNum: [0, 0, 0],
	tips_data: {},
	tips_page: 1,
	tips_length: null,
	tips_now_num: 1,
	theme_position: null,
	theme_list: null,
	theme_num: 0,
	theme_length: 0,
	is_finish: false,
	profit_mark_height: '',
	profitarea_height: '',

	onload: function () {
		if ($('.page_choice.location_list').size() > 0) {
			$('.page_choice.location_list a').removeClass('yellow');
			$('.page_choice.location_list a[data="' + win.city.id + '"]').addClass('yellow');
		}

		$('.page_choice .ui-loader').remove();

		script.load('plugins/scrollByJie', function () {
			/***********ajax请求页面头部bander数据**************/
			ajax('Home/Index/banner', { type: 0 }, function (d) {
				var sol = new myScroll();
				sol.speed = 3;
				//sol.height = win.width * 0.4;
				sol.div = ".pageHead2";
				for (var i in d) {
					sol.src.push(d[i].path);
					sol.link.push(d[i].url);
				}
				sol.start();
			});
		});

		$('<div class="back-to-top resourcesBox"><img src="images/back_top.png"/></div>').appendTo('body');
		ajax('Home/theme/getlist', { 'type': 1, 'url': 1 }, function (d) {
			myProfitObject.tips_length = d.tips_count;
			myProfitObject.theme_position = d.num;
			myProfitObject.theme_list = d.list;
			myProfitObject.theme_length = d.theme_count;
		});

		/*屏幕滚动事件*/
		$('.page_choice.wrapper').scroll(function () {
			//滚动加载内容
			// if ($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !choiceObject.winScrollSock) {
			// 	choiceObject.winScrollSock = true;
			// 	choiceObject.loadtips({ page: Math.ceil($('.page_choice .product_list>li').size() / 5 + 1) });
			// }
			if (!myProfitObject.is_finish) {

			}

			if ($('.page_choice.wrapper').scrollTop() > win.height * 2) {
				$(".back-to-top").fadeIn(500);
			} else {
				$(".back-to-top").fadeOut(500);
			}

		});
		$('.back-to-top').click(function () {
			$('.page_choice.wrapper').animate({ scrollTop: 0 }, 100);
			return false;
		});


		myProfitObject.loadOrder(1);
		$('.page_myProfit .statu').click(function(){
//			$.alert();
			$(this).addClass('add_hei').siblings().removeClass('add_hei');
			if($(this).attr('act_status')){
				myProfitObject.act_status = $(this).attr('act_status');
			}else{
				myProfitObject.act_status = null;
			}
//			$('.page_myProfit .content').empty();
			myProfitObject.loadOrder(1);
		});

//			$('#footer').css('display','none');
//			$('#footer').hide();
//			$('.page_myProfit .profitarea').click();
		myProfitObject.profitarea_height = $('.content').height() + 'px';
		console.log(myProfitObject.profitarea_height);
		$('.page_myProfit .profitarea').click(function () {
//			$('.page_myProfit .orderBottom').css('display','none');
			$('.content').css('height', myProfitObject.profitarea_height);
					console.log(myProfitObject.profitarea_height);
			$('#footer').css('display','none');
			$('.content').show();
            $('.balance').hide();
            $('.customer').hide();
//            $('.profit_blank').hide();
//			$('.orderBottom').html('');

		});
		$('.page_myProfit .myprofit').click(function () {
//			$('#footer').css('display','block');
//			$('.orderBottom').html('关于收益');
            $('.content').hide();
            $('.balance').show();
            myProfitObject.profit_mark_height = $('.profit_mark').height() + 'px';
            $('.customer').hide();
 //           $('.profit_blank').show();

//			$.alert();
		});
		$('.page_myProfit .mycustomer').click(function () {
//			$('#footer').css('display','block');
//			$('.orderBottom').html('关于收益');
            $('.content').hide();
            $('.balance').hide();
            $('.customer').show();
 //           $('.profit_blank').show();

//			$.alert();
		});
		$('.rule').click(function () {
//            $('.profit_info').show();
//			showShareBox();
			myProfitObject.profitBox();
//			$.alert('关于收益');
        });

        /*点击隐藏用户协议*/
        $('.profit_info .knowe').click(function () {
//            $('.profit_info').hide();
        });
/*
        $('.page_myProfit .context').click(function() {
        	$.alert('还不能提现哦!');
        });
*/
/*
        $('.page_myProfit .balance_all').click(function() {
        	$('.page_myProfit .balance_all').css('background', 'white');
        	$('.page_myProfit .balance_income').css('background', 'none');
        	$('.page_myProfit .balance_payment').css('background', 'none');

        	$('.page_myProfit .all_profit_mark').show();
        	$('.page_myProfit .profit_mark_none').hide();

        });
*/
        $('.page_myProfit .balance_income').click(function() {
        	$('.page_myProfit .balance_income').css('background', 'white');
        	$('.page_myProfit .balance_all').css('background', 'none');
        	$('.page_myProfit .balance_payment').css('background', 'none');

        	$('.page_myProfit .profit_mark').show();
 //       	$('.page_myProfit .profit_mark_none').hide();
        	$('.page_myProfit .withdraw_mark').hide();
        });

        $('.page_myProfit .balance_payment').click(function() {
        	$('.page_myProfit .balance_payment').css('background', 'white');
        	$('.page_myProfit .balance_all').css('background', 'none');
        	$('.page_myProfit .balance_income').css('background', 'none');

        	$('.page_myProfit .profit_mark').hide();
//        	$('.page_myProfit .profit_mark_none').css('display','block').css('height', myProfitObject.profit_mark_height);
        	$('.page_myProfit .withdraw_mark').show();


        });

		ajax('Member/Profit/getProfitOrder', {}, function(d) {
			if (!d.no_rs) {
				var count_share = 0.00;
				count_share = parseFloat(count_share);
				for(var i in d){
					count_share += parseFloat(d[i].share_money);
				}
				count_share = count_share.toFixed(2);
	//			$('.p_a').text(count_share);
	//			$('.p_b').text(count_share);
				$('.p_c').text(count_share);
			} else {
	//			$('.p_a').text('0.00');
	//			$('.p_b').text(count_share);
				$('.p_c').text('0.00');
			}
		});

		ajax('Member/Profit/getCanWithdraw', {}, function(d) {
			if (!d.no_rs) {
				$('.p_a').text(d.profit);
			} else {
				$('.p_a').text('0.00');
			}
		});


		ajax('Member/Profit/getTodayProfit', {}, function(d) {
			if (!d.no_rs) {
				var count_share = 0.00;
				count_share = parseFloat(count_share);
				for(var i in d){
					count_share += parseFloat(d[i].share_money);
				}
				count_share = count_share.toFixed(2);
				$('.p_b').text(count_share);				
			} else {
				$('.p_b').text('0.00');				
			}
		});

		ajax('Member/Profit/getCustomer', {}, function(d) {
			console.log(d);

			var code = '';
			if(d.length > 0){
				for(var i in d){
					if(d[i].title.length > 12) {
						d[i].title = d[i].title.substring(0,12);
						d[i].title = d[i].title + '...';
					}
					if(!d[i].path) d[i].path = '20160608/faa0807f970657b79b7b1bfcade9a5349f66e210.jpg'
					code += '<li>';
					code += '<li>';
					code += '	<div style="height:8rem;padding-left: 1rem;">';
					code += '		<div class="left">';
					code += '			<img style="height:6rem;width:6rem;border-radius:50%;float:right;margin: 1.2rem 3rem 0 0;" src="'+ d[i].path.pathFormat() +'">';
					code += '		</div>';
					code += '		<div class="right">';
					code += '			<div class="t" style="color:black;font-size:1.4rem;padding: 1rem 0 0.5rem 1rem;"><span style="font-weight:bold;">项目：</span>'+ d[i]['title'] +'</div>';
//					code += '			<div class="c"></div>';
					code += '			<div class="b">';
					code += '				<p style="padding-left:1rem; color:#b39851;margin:0;padding-bottom: 0.5rem;">'+ d[i].nickname + '</p>'
					code += '				<p style="padding-left:1rem; color:#A0A0A0;margin:0;">订单：'+ d[i].count +'  成交金额   '+ d[i].price + '</p>'
					code += '			</div>'					
//					code += '				<div class="r">￥<span><font>'+ d[i].price +'</font></span></div>';
					code += '			</div>';
					code += '		</div>'; // end of class bx
					code += '	</div>';
//					code += '	<div class="bottom">';
//					code += '	</div>';
					code += '</li>';
				}
			}else{
				if(page==1)
//				var code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
				var code = '<div class="no_msgs">暂无数据</div>';
				else
				var code = '<div class="no_more">暂无数据</div>';
			}
			if(page == 1)
				$('.page_myProfit .customer_list').html(code);
			else
				$('.page_myProfit .customer_list').append(code);
			myProfitObject.locked = false;
		})


		console.log(window.location.href);

		$('.content_list').on('click','.qrcode',function () {
			ajax('Member/Profit/getShareImg', {'goods_id': $(this).attr('goods_id')}, function(d) {
				myProfitObject.qrcodeBox(d.src1,d.src2,d.title,d.price);
				console.log(d.src1);
				console.log(d.src2);				
			});
		});

		$('.header.line.page_myProfit').css('border-bottom', '0rem');
	},
	onshow: function () {

	},



	profitBox: function (text){
		if(win.get.android == 1){
			console.log(JSON.stringify(win.shareData));
			console.log(android);
			console.log(android.showshare);
			var zz = android.showshare(JSON.stringify(win.shareData));
			console.log(zz);
			return;
		}
		var texts = text || '推荐给好友';
		var tip = '';
		if(text){
			var tip = '<span class="tip">Tips：请定向分享给想要邀请的对象，一个消费码只对应一位客人哦.</span>';
		}
		/*$('<div>').addClass('shareBox').html('<h3>点击右上角分享，发送本活动邀请函:</h3><p><span class="left">微信好友</span><span class="right">微信朋友圈</span></p>').appendTo('body').click(function(){
			$(this).remove();
		});*/
		$('<div>').addClass('profitBox').html('<div><div> Q：如何获得收益奖励呢？<br />'+
			'A：分享指定的众筹项目/活动/商品，每成功邀请一位好友付费可获得相应的现金收益作为奖励。只要被邀请人成功登录下单、并无取消订单或退款的状态，你就可以获得该订单产生的收益。<br /><br />'+
			'Q：如何分享及操作呢？<br />' +
'A：关注吖咪yummy 服务号，点击菜单栏-个人中心-登录或注册-我的收益，可以查看到收益专区，只要在专区内的项目，通过分享链接/生成二维码图片，分享给朋友/群/朋友圈。<br /><br />'+

'Q：我分享出去了，但怎么才能知道是否有人通过我的链接购买呢？<br />'+
'A：在“我的收益”中“访客记录”，这里可以记录到访客信息及成交订单（访问用户没有再吖咪平台登录的话，是不会被记录的。）这里显示到有谁通过你的专属链接/图片成功下单。<br /><br />'+

'Q：我所获得的收益奖励，什么时候到账呢？<br />'+
'A：有用户通过你分享的商品/活动/项目链接登录下成后，没有发生退款或关闭交易，且项目成功进行结算后，该订单所获得的收益奖励就会体现到你的“可提现余额”中了。<br /><br />'+

'Q：收益奖励有哪些状态？<br />'+
'A：1.奖励未到账：当用户通过你分享的商品/活动/项目链接登录下单后，你将会获得奖励，金额会体现在“今日收益”及“历史收益”中；<br />'+
'2.奖励扣除：当用户取消订单、交易关闭、退货退款，或者因为出现订单异常等状况，则会口出相应的收益奖励，你将会受到奖励扣除的提醒；<br />'+
'3.奖励到账：有用户通过你分享的商品/活动/项目链接登录下成后，没有发生退款或关闭交易，且项目成功进行结算后，收益奖励就会到账了，体现在“可提现收益”中，你可以提现到微信或支付宝中。<br /><br />'+

'Q：吖咪平台上所有的商品/活动/项目，都是有收益奖励的吗？奖励计算是怎么算的呢？<br />'+
'A：只有出现在收益专区中的商品/活动/项目，才是有奖励的（其中众筹项目中，只有某个档位才有奖励）。专区中的每个商品/活动/项目，奖励都是不同的，以专区中的显示为准。<br /><br />'+

'Q：我可以通过我自己分享的商品/活动/项目链接进去购买，获得相应的收益奖励吗？<br />'+
'A：不可以。<br /><br />'+

'如需帮忙请联系客服（微信号：yami194）</div><span class="profitBox_know">我知道了</span></div>').appendTo('body').click(function(){
			// $(this).remove();
			$('.profitBox_know').click(function(){
				$('.profitBox').remove();
			});
		});
		if(text){
			$('.profitBox div').css('height','auto');
			$('.profitBox p').css('background','#fff');
		}else{
//			$('.profitBox div').css('height','6rem');
			$('.profitBox p').css('background','none');
		}
		var l = $('.profitBox').size();
		if(l>1){
			for(var i = 1; i < l; i++){
				$('.profitBox').eq(i).remove();
			}
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
		var tip = '';
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
/*
            var c=document.getElementById("cv");  
            var cxt=c.getContext("2d");  
            var img=new Image()  
            img.src="http://www.baidu.com/img/bd_logo1.png";
            img.onload = function () //确保图片已经加载完毕  
            {  
                cxt.drawImage(img,100,100);  
            }  
*/
		var canvas=document.getElementById('cv');
		var ctx=canvas.getContext('2d');
		/*
		var img=new Image();
		img.setAttribute('crossOrigin', 'anonymous');
//		img.src="http://test.yummy194.cn/images/share_bg.jpg";
				img.src=src2;

//		img.src="http://www.baidu.com/img/bd_logo1.png";
        img.onload = function () //确保图片已经加载完毕  
           	{  
                ctx.drawImage(img,0,0,500,500);  
            }
        var imgData =canvas.toDataURL("image/png");  
		$("#img").attr("src", imgData);

		*/
/*
		img.setAttribute('crossOrigin', 'anonymous');
		img.onload = function () {
				ctx.drawImage(img,0,0,266,366);
				var imgData =canvas.toDataURL("image/png");  
		}
		*/
		
		var img=new Image();
		img.setAttribute('crossOrigin', 'anonymous');
		var img2=new Image();
		img2.setAttribute('crossOrigin', 'anonymous');
		var img3=new Image();
		img3.setAttribute('crossOrigin', 'anonymous');
		img3.src=src2;
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

		var l = $('.qrcodeBox').size();
		if(l>1){
			for(var i = 1; i < l; i++){
				$('.qrcodeBox').eq(i).remove();
			}
		}
	},



	loadOrder : function(page){
		var data = {
			get:{page:page||myProfitObject.page}
		};
		data.post = {type:1};
		if(myProfitObject.act_status){
			data.post.act_status = myProfitObject.act_status;
		}

		ajax('Member/Profit/getProfitOrder', {}, function(d) {
//			console.log(d);
			var code = '';

			if(d.length > 0){
				for(var i in d){
					if (d[i].success_pay_time) {
						if(d[i].title.length > 12) {
						d[i].title = d[i].title.substring(0,12);
						d[i].title = d[i].title + '...';
						}
						if (d[i].reason) {
							code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
	//						code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].datetime +'<span style="float:right;">'+ d[i].reason +'</span></p>';
							code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].success_pay_time.timeFormat('Y-m-d H:i:s') +'<span style="float:right;">'+ d[i].reason +'</span></p>';
							code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:red;">奖励扣除 <span style="font-weight:bold;">"'+ d[i].title +'"</span><span style="text-align: right;float:right;color:red;">-'+ d[i].share_money +'元</span></p>';
							code += '</div>';
							code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
							code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].success_pay_time.timeFormat('Y-m-d H:i:s') +'<span style="float:right;">收入</span></p>';
							code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:black;">邀请奖励 <span style="font-weight:bold;">"'+ d[i].title +'"</span><span style="text-align: right;float:right;color:#b39851;">+'+ d[i].share_money +'元</span></p>';
							code += '</div>';
						} else if (d[i].act_status == 6 && !d[i].refund_num) {
							code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
	//						code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].datetime +'<span style="float:right;">'+ d[i].reason +'</span></p>';
							code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].success_pay_time.timeFormat('Y-m-d H:i:s') +'<span style="float:right;">退款</span></p>';
							code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:red;">奖励扣除 <span style="font-weight:bold;">"'+ d[i].title +'"</span><span style="text-align: right;float:right;color:red;">-'+ d[i].share_money +'元</span></p>';
							code += '</div>';
							code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
							code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].success_pay_time.timeFormat('Y-m-d H:i:s') +'<span style="float:right;">收入</span></p>';
							code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:black;">邀请奖励 <span style="font-weight:bold;">"'+ d[i].title +'"</span><span style="text-align: right;float:right;color:#b39851;">+'+ d[i].share_money +'元</span></p>';
							code += '</div>';
						} else if (d[i].act_status == 6 && d[i].refund_num) {
							code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
	//						code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].datetime +'<span style="float:right;">'+ d[i].reason +'</span></p>';
							code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].success_pay_time.timeFormat('Y-m-d H:i:s') +'<span style="float:right;">部分退款</span></p>';
							code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:red;">奖励扣除 <span style="font-weight:bold;">"'+ d[i].title +'"</span><span style="text-align: right;float:right;color:red;">-'+ (d[i].unit_share_money * d[i].refund_num).toFixed(2) +'元</span></p>';
							code += '</div>';
							code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
							code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].success_pay_time.timeFormat('Y-m-d H:i:s') +'<span style="float:right;">收入</span></p>';
							code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:black;">邀请奖励 <span style="font-weight:bold;">"'+ d[i].title +'"</span><span style="text-align: right;float:right;color:#b39851;">+'+ d[i].share_money +'元</span></p>';
							code += '</div>';
						} else {
							code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
							code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].success_pay_time.timeFormat('Y-m-d H:i:s') +'<span style="float:right;">收入</span></p>';
							code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:black;">邀请奖励 <span style="font-weight:bold;">"'+ d[i].title +'"</span><span style="text-align: right;float:right;color:#b39851;">+'+ d[i].share_money +'元</span></p>';
							code += '</div>';						
						}						
					}
				}
			}else{
				if(page==1)
//				var code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
				var code = '<div class="no_msgs">暂无数据</div>';
				else
				var code = '<div class="no_more">暂无数据</div>';
			}
			if(page == 1)
				$('.page_myProfit .profit_mark').html(code);
			else
				$('.page_myProfit .profit_mark').append(code);
			myProfitObject.locked = false;
		});
/*原提现支付列表
		ajax('Member/Profit/getWithdrawList', {}, function(d) {
//			console.log(d);
			var code = '';

			if(d.length > 0){
				for(var i in d){
					if (d[i].is_balance == 1) {
						code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
						code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">'+ d[i].start_time.timeFormat('Y-m-d H:i:s') +'<span style="float:right;">支出</span></p>';
						code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:black;">提现支付 <span style="text-align: right;float:right;color:black;">-'+ d[i].price +'元</span></p>';
						code += '</div>';						
					}
				}
			}else{
				if(page==1)
//				var code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
				var code = '<div class="no_msgs">暂无数据</div>';
				else
				var code = '<div class="no_more">暂无数据</div>';
			}
			if(page == 1)
				$('.page_myProfit .withdraw_mark').html(code);
			else
				$('.page_myProfit .withdraw_mark').append(code);
			myProfitObject.locked = false;
		});
*/
		ajax('Member/Profit/getWithdrawList', {}, function(d) {
//			console.log(d);
			var code = '';

			if(d.length > 0){
				for(var i in d){
					if (d[i].is_balance == '0') {
						var is_balance = '申请中';
					} else if (d[i].is_balance == '1') {
						var is_balance = '已成功';
					} else if (d[i].is_balance == '2' || d[i].is_balance == '3') {
						var is_balance = '提现失败'
					}
					code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
					code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:black;">申请提现'+d[i].price+'元 ('+ is_balance+')<span style="text-align: right;float:right;font-size: 1.2rem;color:#A0A0A0;">'+ d[i].start_time.timeFormat('Y-m-d H:i:s') +'</span></p>';
					if (d[i].is_balance == '2' || d[i].is_balance == '3') {
						code += '	<p style="color:red;margin: 0.2rem 0 0.8rem 0;">'+ d[i].reason +'</p>';
					} else {
						code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">扣除手续费0.6%</p>';
					}
					code += '</div>';
				}
			}else{
				if(page==1)
//				var code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
				var code = '<div class="no_msgs">暂无数据</div>';
				else
				var code = '<div class="no_more">暂无数据</div>';
			}
			if(page == 1)
				$('.page_myProfit .withdraw_mark').html(code);
			else
				$('.page_myProfit .withdraw_mark').append(code);
		});


		ajax('Member/Profit/getProfit', {}, function(d) {
//			win.close_loading();
				var code = '';

			if(d.length > 0){
				for(var i in d){
					code += '<li>';
					code += '	<div><a class="top" href="javascript:jump(\'goodsDetail\',{goods_id:'+ d[i].id +'})">';
					code += '		<div style="text-align:left;padding:1.4rem 0 0.7rem;color: #A0A0A0;">商品</div>';
					code += '		<div class="left">';
					code += '			<img src="'+ d[i].path.pathFormat() +'">';
					code += '		</div>';
					code += '		<div class="right">';
					code += '			<div class="t">'+ d[i]['title'] +'</div>';
//					code += '			<div class="c"></div>';
					code += '			<div class="b">';
					code += '				<div class="s"><div style="background:#ff192f;color:white;float:left;padding: 0 0.6rem;font-size: 1.1rem;border-radius: 2px;width:3.5rem;text-align: center;height:2rem;line-height: 2rem;">分享赚</div>';
					code += '								<span style="padding-left:1rem;">￥'+ d[i].share_money + '</span>'
					code += '				</div>'					
//					code += '				<div class="r">￥<span><font>'+ d[i].price +'</font></span></div>';
					code += '			</div>';
					code += '		</div>'; // end of class bx
					code += '	</a></div>';
					code += '	<div class="qrcode" goods_id="'+ d[i].id +'">立刻分享</div>';
//					code += '	<div class="bottom">';
//					code += '	</div>';
					code += '</li>';
				}
			}else{
				if(page==1)
//				var code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
				var code = '<div class="no_msgs"></div>';
				else
				var code = '<div class="no_more"></div>';
			}
			if(page == 1)
				$('.page_myProfit .content_list').html(code);
			else
				$('.page_myProfit .content_list').append(code);
			myProfitObject.locked = false;
		});



/*
		ajax('Member/Order/index', data, function(d){

			win.close_loading();
			if(d.length > 0){
				var code = '';
				var status = ['待付款', '待发货', '待收货', '待评价', '已完成', '退款中', '已退款', '已取消', '退款中'];
				for(var i in d){
					code += '<li>';
					code += '	<a class="top" href="javascript:jump(\'orderGoodsDetail\',{order_id:'+ d[i].id +'})">';
					code += '		<div class="left">';
					code += '			<div class="type">'+ (d[i].catname||'商品') +'</div>';
					code += '			<img src="'+ d[i].path.pathFormat() +'">';
					code += '		</div>';
					code += '		<div class="right">';
					code += '			<div class="t">'+ d[i]['title'] +'</div>';
					if(d[i].type == 0){
						if(d[i].start_time)
							code += '			<div class="c">'+ d[i].start_time.timeFormat('m-d W H:i') +'-'+ d[i].end_time.timeFormat('H:i') +'</div>';
					}else{
						if(d[i].postage == 0){
							code += '			<div class="c"></div>';
						}else{
							code += '			<div class="c"></div>';
						}
					}
					code += '			<div class="b">';
					d[i].is_piece === 1
						? code += '<span class="tag">拼团</span>'
						: code += '<span class="tag">商品</span>';
					code += '				<div class="float-right"><div class="l"><span>'+ d[i].count +'</span>份</div><div class="cc"></div>';
					code += '				<div class="r">实付：<span><font>'+ d[i].price +'</font>元</span></div>';
					code += '			</div>';
					code += '		</div></div>'; // end of class bx
					code += '	</a>';
					code += '	<div class="bottom">';
					code += '		<div class="status">'+ (typeof(status[d[i].act_status])=='string'?status[d[i].act_status]:status[d[i].act_status][d[i].type]) +'</div>';

					code += '	</div>';
					code += '</li>';
				}
			}else{
				if(page==1)
//				var code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
				var code = '<div class="no_msgs"></div>';
				else
				var code = '<div class="no_more"></div>';
			}
			if(page == 1)
				$('.page_myProfit .content').html(code);
			else
				$('.page_myProfit .content').append(code);
			myProfitObject.locked = false;
		}, 2);
*/
	},
};




