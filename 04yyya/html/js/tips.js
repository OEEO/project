$(function(){
	var XX=GetHrefParameter();
	if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='tips_id')tips_id = XX[i][1];
		}
	console.log("获取到的 tips_id= \t"+tips_id);	
	
/*	if(window.location.href.indexOf('#') == -1){
		$('body').html('非法访问！');
	}
	//tips_id = window.location.href.split('#')[1];
	*/
	var doAsk = '<a class="query" onclick="javascript:parent.page.jump(\'q&a.html?tips_id='+tips_id+'\')">提问</a>';
	$('.questionAndAnswer .actInfoMenu').html($('.questionAndAnswer .actInfoMenu').html()+doAsk);//添加提问按钮
	
	//tips_id = 4254; 测试 tips 的 id
	ajax('Goods/Tips/getDetail', {'tips_id':tips_id}, function(d){
		//判断是否已经收藏
		 if(d.isfollow==1)$('.header .values').addClass('valued');
		//活动标题
		$('.activityTitle').text(d.title);
		//会员昵称
		$('.userNm').text(d.nickname);
		//主图
		$('.picTop').html('<img src="'+ d.mainpic.pathFormat() +'">');
		//会员头像
		if(d.headpic)
			$('.imgPortrait').attr('src', d.headpic.pathFormat());
		//活动星级
		for(var i=0; i<5; i++){
			if(i < d.stars){
				$('.StarsGrade').append('<span></span>');
			}else{
				$('.StarsGrade').append('<span class="empty"></span>');
			}
		}
		//价格
		$('.priceMenu .price').html('￥' + d.price);
		
		//限时折扣
		if(d.marketing){
			flash_buy(d.marketing, $('.actLastTime'));
		}else{
			$('.actLastTime').remove();
		}
		 
		//标签
		for(var i in d.tags){
			$('.mtags').append('<span>'+ d.tags[i] +'</span>');
		}
		//最小成局
		$('.min_num').text(d.min_num);
		//最大数量
		$('.restrict_num').text(d.restrict_num);
		//截止购买时间
		$('.stop_buy_time').text(d.stop_buy_time);
		//时间节点
		
		//$('.times').html(d.min_start_time.timeFormat('Y-m-d') + ' ~ ' + d.max_end_time.timeFormat('Y-m-d') +  '<span class="arrowRight"></span>');
		
		if(d.times.length > 1){//时间节点
			$('.times').addClass('multiple');
			$('.times').html(d.min_start_time.timeFormat('Y-m-d') + ' ~ ' + d.max_end_time.timeFormat('Y-m-d') + '<span class="arrowRight"></span>');
			for(var i in d.times)
				$('.timesLay .b').append('<div>'+ d.times[i].start_time.timeFormat('m.d W H:i') +'<p>已报名'+ d.times[i].count +'人</p></div>');
		}else{
			$('.times').html(d.min_start_time.timeFormat('Y-m-d W H:i'));
			$('.timesLay').remove();
			$('.times').attr('href', 'javascript:void(0);');
		}
		//活动地点
		$('.address').html(d.address + '<span class="arrowRight"></span>');
		//活动详情
		$('.content').html(d.content);
		
		//评论列表
		var code = '';
	if(d.comment.length>0){
		for(var i in d.comment){
			code += '<div class="comments">';
			code += '<img class="imgPortrait" src="'+ d.comment[i].path.pathFormat() +'" />';
			code += '<div class="right">';
			code += '<div class="name">'+ d.comment[i].nickname +'<span>'+ d.comment[i].datetime +'</span></div>';
			code += '<p>'+ d.comment[i].content +'</p>';
			if(d.comment[i].pics.length > 0){
				code += '<div class="imgList" style="overflow:hidden;">';
				for(var j in d.comment[i].pics){
					code += '<img src="'+ d.comment[i].pics[j] +'">';
				}
				code += '</div>';
			}
			code += '</div>';
			code += '</div>';
			code += '<div class="clear"></div>';
		}
		code +='<a href="javascript:parent.page.jump(\'evaluationList.html?tips_id='+tips_id+'\')" class="allEvaluation">查看所有评论</a>';
	}else{	
			code +='<a href="javascript:void(0);" class=" allEvaluation">暂时没有评论</a>';
			}
		$('.commentList').html(code);
		
		//答疑列表
		var code = '';
	if(d.feedback.length>0){
		for(var i in d.feedback){
			code += '<li>';
			code += '<img class="imgPortrait" src="'+ d.feedback[i].path.pathFormat() +'" />';
			code += '<div style="float:left;width:78%">';
			code += '<div class="qUserName">'+ d.feedback[i].nickname +'</div>';
			code += '<div class="commitTime">'+ d.feedback[i].datetime +'</div>';
			code += '</div>';
			code += '<div class="question">'+ d.feedback[i].content +'</div>';
			code += '<div class="clearfix"></div>';
			if(d.feedback[i].answer != null)
				code += '<div class="answer">'+ d.feedback[i].answer +'</div>';
			code += '</li>';
		}
			code +='<a href="javascript:parent.page.jump(\'q&a.html?tips_id='+tips_id+'\')" class="allQuestion">查看所有答疑</a>';
	}else{	
			code +='<a href="javascript:void(0);" class="allQuestion">暂时没有答疑</a>';
			}
			
		$('.questionList').html(code);
	
	/*	
		$('.questionAndAnswer .query').click(function(){
			/*var $ask = $('<div class="askBox"><textarea placeholder="输入您要提问的内容"></textarea><button class="submit">提交</button>&nbsp;&nbsp;<button class="closed">关闭</button></div>').prependTo('body');
			$('#mainContent').addClass('G_content');
			$ask.find('.submit').click(function(){
				var answer = $(this).prev().val();
				ajax('');
				$('#mainContent').removeClass('G_content');
			});
			$ask.find('.closed').click(function(){
				$('#mainContent').removeClass('G_content');
			});*
			jump('q&a.html?tips_id=' + tips_id);
		});
		*/
	}, false);
});

var tips_id = null;

//闪购
function flash_buy(d, $em){
	$em.find('.title font').text(d.title);
	$em.find('.sy span').text(d.limit);
	var time = Math.round(new Date().getTime()/1000);
	if(time < d.start_time){
		$em.find('.w').text('距离开抢');
		countdown($em, d.start_time, flash_buy);
	}else if(time < d.end_time){
		$em.find('.w').text('距离结束');
		$('.priceMenu .price').html('<span class="l">￥'+ d.price +'</span><span class="r">' + $('.priceMenu .price').html() + '</span>');
		$em.find('span').css({'background':'#f1da72', 'color':'#F30'});
		countdown($em, d.end_time, flash_buy);
	}else {
		$em.remove();
		$('.priceMenu .price').html($('.priceMenu .price .r').html());
	}
}

//闪购倒计时函数
function countdown($em, stime, fn){
	var time = Math.round(new Date().getTime()/1000);
	if(stime - time <= 0){
		fn();
		return;
	}
	$em.find('.h').text(Math.floor((stime - time) / 3600));
	$em.find('.i').text(Math.floor((stime - time) % 3600 / 60));
	$em.find('.s').text(Math.floor((stime - time) % 3600 % 60));
	
	window.setTimeout(function(){
		countdown($em, stime, fn);
	}, 1000);
}

//提交订单
function submitOrder(){
	jump('confirmEnrolling.html?tips_id=' + tips_id);
}

function showTimes(){
	$('.timesLay').css('display','block');
	$('#mainContent').addClass('G_content');
	
	var top = ($(window).height()-$('.timesLay .views').height())/3;
	var wid = ($(window).width()-$('.timesLay .views').width())/1.6;
	var closedTop =$('.timesLay .views').height()+top;
	$('.views').css({'margin-top':top,'margin-left':wid});
	$('.timesLay .closed').css("top",closedTop);
	document.body.style.overflow='hidden';
	document.ontouchmove = function(e){e.preventDefault();} //文档禁止 touchmove事件
}
function hideCode(){
	$('.timesLay').css('display','none');
	$('#mainContent').removeClass('G_content');
	document.body.style.overflow='visible';
	document.ontouchmove = function(e){} //文档禁止 touchmove事件
}