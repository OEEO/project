$(function(){
/*	function PicPath(oldPath,contains,source){
		if(contains==undefined||contains=='')contains="uploads/";
		if(source==undefined||source=='')source="http://yummy194.cn/";
		if(oldPath.indexOf(contains) >= 0){
		/*	if(oldPath.indexOf("/"+contains) >= 0){
				console.log(1+'\t'+oldPath);
				source=source.substring(0,source.Length-1);
				oldPath = source+oldPath;
			}else{oldPath = source+oldPath;	console.log(2+'\t'+oldPath);}
		// /
			oldPath = source+oldPath;			
		}
		return oldPath;
	}*/
	
	
	var screenWidth = $(window).width();/*获取设备宽度*/
	
	/***********ajax请求页面头部bander数据**************/	
	ajax('Home/Index/banner', function(d){	
		var sol = new myScroll();
		sol.speed = 3;
		sol.height = screenWidth*0.4;
		sol.div = ".pageHead";
		for(var i in d){
			sol.src.push(d[i].path.pathFormat());
			sol.link.push(d[i].url);
		}
		sol.start();
	});
	
	/****************八个按钮*******************/
	ajax('Home/Theme/index',function(d){
		var  code = '';
		for(var i in d.theme){			
			code += '<li><a href="javascript:parent.page.jump(\'specialTips.html?tips_id='+d.theme[i].id+'|'+ d.theme[i].title +'\')"><img src="'+d.theme[i].path.pathFormat()+'"/><br />'+d.theme[i].title+'</a></li>';
			}
		code +='<li><a href="javascript:parent.page.jump(\'calendar\')"><img src="images/diary.png"/><br />日历</a></li><li><a href="#"><img src="images/searchActivity.png"/><br />找活动</a></li>';
		$('.discoverMenuDetail').html(code);
	});
		
		
		
	/************ajax获取Top5 对应数据 **************/
	ajax('Home/Index/top5', function(d){	
		var code = '';
		for(var i in d){
/*
			if(d[i].path.indexOf("uploads/") >= 0){
				d[i].path="http://yummy194.cn/"+d[i].path;
			}
			*/
			code += '<li>';
			code += '<div class="top5activitiesListLeft"><a href="javascript:parent.page.jump(\'tips.html?tips_id='+ d[i].id+'\')"><img src="'+ d[i].path.pathFormat() +'"></a>';
			code += '<div class="top5ActivityPrice"><span>'+'￥'+d[i].price+'</span></div></div>';
			code += '<div class="top5activitiesListRight">';
			code += '<ul class="top5ActList">';
			code += '<li>'+ d[i].title +'</li>';
			code += '<li>'+ d[i].city_name +'</li>';
			code += '<li>'+ d[i].start_time.timeFormat("m.d W H:i") +'</li>';
			code += '<li>Sammi Hoo</li>';
			code += '</ul>';
			code += '</div>';
			//code += '<div class="top5ActivityPrice"><span>'+d[i].price+'<small>元</small></span></div>';
			code += '</li>';
		}
		//console.log(code);
		$('.top5activitiesList').html(code);
	});
	
	/*ajax设置 推荐 模块内容*/
	ajax('Home/Index/recommend', function(d){
		var iScroll = new myScroll(2);
		iScroll.div = ".recommendClassPics";
		for(var i in d){
			iScroll.src.push(d[i].path.pathFormat());
			iScroll.link.push('tips.html?tips_id=' + d[i].id);
		}
		iScroll.start();
	});

	/*ajax设置明星达人 模块内容*/
	ajax('Home/Daren/index', function(d){	
		var sol = new myScroll(2);
		sol.div = ".starsPics";
		for(var i in d){
			sol.src.push(d[i].path.pathFormat());
			sol.link.push('daRen.html?daRen=' + d[i].id);
		}
		sol.start();
		for(var i in d){
			var $em = $('<div class="floatLay"><p class="name">'+ d[i].name +'</p><p class="introduce">'+ d[i].introduce +'</p></div>');
			$em.appendTo($('.starsPics a').eq(i));
		}
	});
	
	/* ajax设置热门商品列表 */
	ajax('Home/Goods/hots', function(d){	
		var sol = new myScroll(2);
		sol.div = ".hotSaleList";
		for(var i in d){
			sol.src.push(d[i].mainpic.pathFormat());
			sol.link.push('goodsDetail.html?goods_id=' + d[i].id);
		}
		sol.start();
		window.setTimeout(function(){
			for(var i in d){
				if(d[i].market_price){
					var $em = $('<div class="floatLay"><p class="title">'+ d[i].title +'</p><p class="price"><span>￥'+ d[i].market_price +'</span><small>￥'+ d[i].price +'</small></p></div>');
				}else{
					var $em = $('<div class="floatLay"><p class="title">'+ d[i].title +'</p><p class="price">￥'+ d[i].price +'</p></div>');
				}
				$em.appendTo($('.hotSaleList a').eq(i).height('100%'));
			}
		}, 500);
	});

	
/************ajax获取 标签 对应数据 **************/
	ajax('Home/Index/tags', function(d){	
		var code ='<a href="javascript:loadtips({page:1, tag_id:null})">全部</a>';
		for(var i in d){
			code += '<a href="javascript:loadtips({page:1, tag_id:'+d[i].id+'})">'+d[i].name+'</a>';
		}

		$('.nav_1').html(code);
	});
/********************************************/

/************ajax获取 地区 对应数据 **************/
	ajax('Home/Index/area', function(d){	
		var code = '<a href="javascript:loadtips({page:1, city:null})">全'+ parent.win.city.name +'</a>';
		for(var i in d){
			code+='<a href="javascript:loadtips({page:1, city:'+ d[i].area_id +'})">'+ d[i].area_name +'</a>';
		}
		$('.nav_3').html(code);
	});
	
	// 今日闪购
	flash_buy();
	
	// 加载默认筛选列表
	loadtips();
});

var tips_data = null;
var tips_page = 1;
//加载活动列表
function loadtips(data){
	/*data.tag_id = tag_id||'';
	data.week = week||'';
	data.city_id||'';
	data.price||'';*/
	
	if(!data) data = {};
	else if(data.page) tips_page = data.page;
	
	//判断是否属于新的筛选
	if(tips_page == 1){
		$('.nothing').hide();
		for(var i in data){
			if(tips_data[i] && i != 'page' && data[i] != tips_data[i])tips_data[i] = data[i];
		}
	}else if($('.nothing:visible').size() > 0){
		return;
	}
	$('.tips_loading').show();
	(function tipsLoading(num){
		var text = 'loading';
		for(var i=0; i<num; i++){
			text += '.';
		}
		$('.tips_loading').html(text);
		if($('.tips_loading:visible').size() > 0){
			window.setTimeout(function(){
				tipsLoading(num >= 3 ? 0 : num+1);
			}, 300);
		}
	})(1);
	ajax('Home/Index/tips', {get:{page:tips_page}, post:tips_data}, function(d){
		$('.tips_loading').hide();
		if(d.length > 0){
			var code = '';
			for(var i in d){
				code += '<li>';
				code += '	<div class="userHeadImg" style="height:6.5rem;">';
				code += '		<div class="userHeadpic"data-url=""></div>';
				code += '		<div class="userHeadName">'+ d[i].nickname +'</div>';
				code += '	</div>';
				code += '	<div class="clearfix"></div>';
				code += '	<div width="100%">';
				code += '		<div class="order_last_bg"></div>';
				code += '		<div class="order_last"> <span>剩余'+ d[i].amount +'份</span>';
				code += '	</div>';
				code += '	<div>';
				code += '		<a class="actImgLink" href="javascript:parent.page.jump(\'tips.html?tips_id='+ d[i].id +'\')">';
				code += '			<img width="100%" src="'+ d[i].path.pathFormat() +'" alt="Heineken"/>';
				code += '		</a>';
				code += '	</div>';
				code += '	<div class="activityList_bottom">';
				code += '		<div class="introTitle" data-url="#">'+ d[i].title +'</div>';
				code += '		 <div class="intro_inf">';
				code += '			<ul class="intro_inf_p-t">';
				code += '				<li><span>'+ d[i].address.substr(0, 8) +'</span></li>';
				code += '				<li><span>'+ d[i].start_time.timeFormat('m.d W H:i') +'</span></li>';
				code += '			</ul><div class="clearfix"></div>';
				code += '			<div class="tags">';
				for(var j in d[i].tags){
					code += '					<span>'+ d[i].tags[j] +'</span>';
				}
				code += '			</div>';
				code += '			<div class="intro_inf_price">'+ d[i].price +'<i>元</i></div>';
				code += '		</div>';
				code += '	</div><div class="clearfix"></div>';
				code += '</li>';
			}
			if(tips_data == 1)
				$('#actList').html(code);
			else
				$('#actList').append(code);
		}else{
			$('.nothing').show();
		}
		winScrollSock = false;
	});
}

//闪购
function flash_buy(){
	ajax('Home/Index/flash', {}, function(d){
		if(d.length == 0){
			$('.todayFlash').remove();
			return;
		}
		var $em = $('.todayFlashList');
		$em.find('.t').text(d.title);
		$em.find('img').attr('src', d.mainpic.pathFormat());
		$em.find('.price1').html('￥<big>' + d.market_price.split('.')[0] + '</big>.' + d.market_price.split('.')[1]);
		$em.find('.price2').text('￥' + d.price);
		$em.find('.l').text("剩余"+ d.stocks +"份");
		var time = Math.round(new Date().getTime()/1000);
		if(time < d.start_time){
			$em.find('.w').text('距离开抢');
			countdown(d.start_time, flash_buy);
		}else if(time < d.end_time){
			$em.find('.w').text('距离结束');
			$em.find('.r').addClass('hot');
		
			countdown(d.end_time, flash_buy);
		}else {
			$em.find('.r').text('已结束').css({'text-align':'center', 'color':'#999'});
		}
	}, false);
}
//闪购倒计时函数
function countdown(stime, fn){
	var time = Math.round(new Date().getTime()/1000);
	if(stime - time <= 0){
		fn();
		return;
	}
	$('.todayFlashList .h').text(Math.floor((stime - time) / 3600));
	$('.todayFlashList .i').text(Math.floor((stime - time) % 3600 / 60));
	$('.todayFlashList .s').text(Math.floor((stime - time) % 3600 % 60));
	
	window.setTimeout(function(){
		countdown(stime, fn);
	}, 1000);
}
