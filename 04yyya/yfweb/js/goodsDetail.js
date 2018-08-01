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
			code += '<div class="ps_content"><p>1.新老用户均可开团、参团，选择心仪商品，完成支付，立即开团</p><p>2.分享邀请好友加入拼团，在限定时间内，邀请到规定拼团好友数开拼，立享拼单折扣</p><p>3.限制时间内未邀满拼团人数，或库存不足，则拼单失效，系统将即时自动退款</p><p>4.拼单成功的订单可在吖咪平台上查看</p><button>确定</button></div>';
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
            ajax('Home/Index/shareSuccess', {type: 1, item_id: id, target: target, platform: 1});
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
	onload : function(){
		if(win.get.goods_id){
			goodsDetailObject.goods_id = win.get.goods_id;
		}else{
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
		}

		ajax('Goods/Goods/getDetail', {'goods_id':goodsDetailObject.goods_id}, function(d){

			var desc = '';
			for(var i in d.edge){
				desc += d.edge[i] + ' ';
			}
			var url = win.host + '?page=choice-goodsDetail&goods_id=' + goodsDetailObject.goods_id;
			if(member && member.invitecode){
				url += '&invitecode=' + member.invitecode;
			}
			share(d.title, desc, url, d.mainpic);

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

			//是否包邮
			if(parseFloat(d.shipping) > 0)$('.page_goodsDetail .data span').remove();

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
			if (d.price.match(/\d+\.00/)) {
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
				em.find('.limit_num').text(d.piece.limit_num);
				em.find('button').click(goodsDetailObject.showPiecePsBox);
                //
                // var code = '<div class="price" onclick="goodsDetailObject.submitOrder();"><span>'+ parseFloat(d.price).priceFormat() +'</span>元直接购买</div>';
                // code += '<div class="piece" onclick="goodsDetailObject.submitPiece();"><span>'+ parseFloat(d.piece.price).priceFormat() +'</span>元拼团</div>';
                // $('.page_goodsDetail.priceMenu').html(code).addClass('pieceMenu');
				$('.page_goodsDetail .submitLeft span').html('元单独购');
				$('.page_goodsDetail .submitBtn').html(d.piece.price + '元拼团');
				$('.page_goodsDetail .submitLeft').data('submit', 'order');
				$('.page_goodsDetail .submitBtn').data('submit', 'piece');

                $('.page_goodsDetail .submitLeft').click(function () {
					goodsDetailObject.submitOrder();
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

			// //相关活动
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

            var url = win.host + '?page=choice-goodsDetail&goods_id=' + goodsDetailObject.goods_id;
            if(member && member.invitecode){
                url += '&type=2&invitecode=' + member.invitecode;
            }
            var desc = d.introduce;

            share(d.title, desc, url, d.mainpic, goodsDetailObject.shareSuccess(goodsDetailObject.goods_id));

            $('.page_goodsDetail').on('click', '[data-btn]', function(){

            	var type = $(this).data('btn');
                if (type === 'share') {
                    if (Yami.platform() === 'android') {
                        Yami.share({
                            title: d.title,
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

            $('.page_goodsDetail .submitBtn').click(function () {

            	if ($(this).data('submit') === 'piece') {
            		goodsDetailObject.submitPiece();
				} else {
                    goodsDetailObject.submitOrder();
				}

			});
		}, 2);
	}
};
