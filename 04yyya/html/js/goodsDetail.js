$(function(){
/*	if(window.location.href.indexOf('#') == -1){
		$('body').html('非法访问！');
	}
	goods_id = window.location.href.split('#')[1];*/
	var XX=GetHrefParameter();
	if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='goods_id')goods_id = XX[i][1];
		}
	//goods_id = 145;// 测试 tips 的 id
	ajax('Goods/Goods/getDetail', {'goods_id':goods_id}, function(d){
		//设置是否已经关注了
		if(d.isfollow==1)$('.header .values').addClass('valued');
		//主图
		if(d.mainpic)$('.picTop .mainGoodsPic').attr('src', d.mainpic.pathFormat());
		//会员头像
		if(d.headpic)$('.goodsInf .picTop .imgPortrait').attr('src', d.headpic.pathFormat());
		//活动星级
		for(var i=0; i<5; i++){
			if(i < d.stars){
				$('.goodsInf .starsGrade').append('<span></span>');
			}else{
				$('.goodsInf .starsGrade').append('<span class="empty"></span>');
			}
		}
		//活动标题
		$('.goodsTitle').text(d.title);
		//会员昵称
		$('.proTit').text(d.nickname);
		//价格
		$('.priceMenu .price').html('￥' + d.price);
		
		//限时折扣
		if(d.marketing){
			$('.priceMenu .price').html('<del>'+'￥'+ d.price+'</del>');
			flash_buy(d.marketing, $('.actLastTime'));
		}else{
			$('.actLastTime').remove();
		}
		
		//标签
		for(var i in d.tags){
			$('.mtags').append('<span>'+ d.tags[i] +'</span>');
		}
		
		$('.sumTable .cell').eq(0).html("运费￥"+d.shipping);
		$('.sumTable .cell').eq(1).html("剩余 "+d.surplus+" 件");
		$('.sumTable .cell').eq(2).html("月销 "+d.month_selled+" 件");
		//最小成局
		//$('.min_num').text(d.min_num);
		//最大数量
		//$('.restrict_num').text(d.restrict_num);
		//截止购买时间
		
		//活动详情
		$('.productDetails').html(d.content);
		
		//评论列表
		var code = '';

		console.log("一共有"+d.comment.length+"条评论");
		//var num = count(d.comment);
		//console.log("\n\t 获取的"+num);
		if(d.comment.length>0){
			for(var i in d.comment){
				code += '<div class="comments">';
				code += '<img class="imgPortrait" src="'+ d.comment[i].path.pathFormat() +'" />';
				code += '<div class="right">';
				code += '<div class="name">'+ d.comment[i].nickname +'<span>'+ d.comment[i].datetime +'</span></div>';
				
				code +='<div class="starsGrade">'
				//商品评分星级
				for(var j=0; j<5; j++){
					if(j < d.comment[i].stars){
						code +='<span></span>';
					}else{
						code +='<span class="empty"></span>';
					}
				}
				code +='</div>';//商品评分星级结束
				code +='</div>';//right结束
				code +='<div class="clearfix"></div>';
				code += '<p>'+ d.comment[i].content +'</p>';
				
				if(d.comment[i].pics.length > 0){
					code += '<div class="imgList" style="overflow:hidden;">';
					for(var j in d.comment[i].pics){
						code += '<img src="'+ d.comment[i].pics[j] +'">';
					}
					code += '</div>';
				}
				code += '</div>';
			}
			code +='<a href="javascript:parent.page.jump(\'evaluationList.html?goods_id='+goods_id+'\')" class="allEvaluation">查看所有评论</a>';
			}else{	
				code +='<a href="javascript:void(0);" class="allEvaluation">暂时没有评论</a>';
			}
		$('.commentList').html(code);
	}, false);
});

var goods_id = null;

//闪购  	flash_buy(d.marketing, $('.actLastTime'));
function flash_buy(d, $em){
	$em.find('.title font').text(d.title);
	$em.find('.sy span').text(d.limit);
	var time = Math.round(new Date().getTime()/1000);
	if(time < d.start_time){
		$em.find('.w').text('距离开抢:');
		countdown($em, d.start_time, flash_buy);
	}else if(time < d.end_time){
		$em.find('.w').text('距离结束:');
		//$('.priceMenu .price').html('<span class="l">￥'+ d.price +'</span><span class="r">' + $('.priceMenu .price').html() + '</span>');
		$('.priceMenu .price').html('￥'+ d.price + $('.priceMenu .price').html());
		//$em.find('span').css({'background':'#f1da72', 'color':'#F30'});
		countdown($em, d.end_time, flash_buy);
	}else {
		$em.remove();
		//$('.priceMenu .price').html($('.priceMenu .price .r').html());
		$('.priceMenu .price').html($('.priceMenu .price del').html());
	}
}

//闪购倒计时函数
function countdown($em, stime, fn){
	var time = Math.round(new Date().getTime()/1000);
	if(stime - time <= 0){
		fn();
		return;
	}
	$em.find('.h').text(Math.floor((stime - time) / 3600)+':');
	$em.find('.i').text(Math.floor((stime - time) % 3600 / 60)+':');
	$em.find('.s').text(Math.floor((stime - time) % 3600 % 60));
	
	window.setTimeout(function(){
		countdown($em, stime, fn);
	}, 1000);
}

//提交订单
function submitOrder(){
	jump('confirmBuy.html?goods_id=' + goods_id);
}

function showTimes(){
	$('.timesLay').css('display','block');
	$('#mainContent').addClass('G_content');
	
	var top = ($(window).height()-$('.timesLay .views').height())/40;
	var wid = ($(window).width()-$('.timesLay .views').width())/2;
	$('.views').css({'margin-top':($(window).height()-$('.timesLay .views').height())/3,'margin-left':($(window).width()-$('.timesLay .views').width())/2});

	document.body.style.overflow='hidden';
	document.ontouchmove = function(e){e.preventDefault();} //文档禁止 touchmove事件
}
function hideCode(){
	$('.timesLay').css('display','none');
	$('#mainContent').removeClass('G_content');
	document.body.style.overflow='visible';
	document.ontouchmove = function(e){} //文档禁止 touchmove事件
}