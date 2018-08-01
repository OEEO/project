var themeDetailObject = {
	//winScrollSock : false,
	_top : 0,
	theme_id : 0,
	tips_data : {},
	tips_page : 1,
	onload : function(){
		if(win.get.theme_id){
			themeDetailObject.theme_id = win.get.theme_id;
		}
		if(!win.get.theme_id){
			$('.page_themeDetail').text('非法访问!');
			return;
		};
		ajax('Home/Theme/getDetail', {theme_id:themeDetailObject.theme_id}, function(d){
			//分享绑定
			console.log(d)
			var desc = d.theme.content;
			var url = win.host + '?page=choice-themeDetail&theme_id=' + themeDetailObject.theme_id;
			if(member && member.invitecode){
				url += '&invitecode=' + member.invitecode;
			}
			share(d.theme.title, desc, url, d.theme.path);

			$('.page_themeDetail .u_shares').click(function(){
				showShareBox();
			});

			script.load('plugins/scrollByJie', function(){

				var sol = new myScroll();
				sol.speed = 3;
				sol.div = ".page_themeDetail .top_img";
				for(var i in d.theme.groupPath){
					sol.src.push(d.theme.groupPath[i]);
				}
				sol.start();

			});

			//$('.page_themeDetail .bodyTop img').attr('src',d.theme.path);
			$('.page_themeDetail .bodyTop .center_t .ct_time').text(d.theme.datetime.timeFormat('Y.m.d H:i'));
			$('.page_themeDetail .bodyTop .center_t .ct_content').html(d.theme.html_content);
			var arr = [];
			arr = d.theme.title.split('|');
			$('.page_themeDetail .bodyTop .center_t .ct_title').text(arr[0]);
			$('.page_themeDetail .bodyTop .center_t .subct').text(arr[1]);

			var code = '';
			for(var i in d.tips){
				code += '<li>';
				code += '	<div class="pro_top">'+ d.tips[i].start_time.timeFormat('Y-m-d') +' '+  d.tips[i].catname +'</div>';
				code += '	<a href="javascript:jump(\'tipsDetail\',{tips_id:'+ d.tips[i].id +'})">';
				code += '		<div class="pro_bottom">';
				code += '			<div class="ac_img"><img src="'+ d.tips[i].path +'"/></div>';
				code += '			<div class="ac_title">';
				code += '				<div class="ac_t">'+ d.tips[i].title +'</div>';
				code += '				<div class="ac_p"><div class="ad">'+ d.tips[i].simpleaddress +'</div><span>￥'+ parseFloat(d.tips[i].price).priceFormat() +'/份</span></div>';
				code += '			</div>';
				code += '   	 </div>';
				code += ' 	</a>';
				code += '</li>';
			}
			if(d.tipsPass && d.tipsPass.length > 0){
				code += '<div class="split"> ---------- 已售罄 ---------- </div>';
				for(var i in d.tipsPass){
					code += '<li class="passed">';
					code += '	<div class="pro_top">'+ d.tipsPass[i].start_time.timeFormat('Y-m-d') +' '+  d.tipsPass[i].catname +'</div>';
					code += '	<a href="javascript:jump(\'tipsDetail\',{tips_id:'+ d.tipsPass[i].id +'})">';
					code += '		<div class="pro_bottom">';
					code += '			<div class="ac_img"><img src="'+ d.tipsPass[i].path +'"/></div>';
					code += '			<div class="ac_title">';
					code += '				<div class="ac_t">'+ d.tipsPass[i].title +'</div>';
					code += '				<div class="ac_p"><div class="ad">'+ d.tipsPass[i].simpleaddress +'</div><span>￥'+ parseFloat(d.tipsPass[i].price).priceFormat() +'/份</span></div>';
					code += '			</div>';
					code += '   	 </div>';
					code += ' 	</a>';
					code += '</li>';
				}
			}

			if(d.tips && d.tips.length == 0){
				code = '<li class="the_end"><button onclick="$(\'.page_themeDetail.wrapper\').animate({\'scrollTop\':0})">回到顶部</button></li>';
				//themeDetailObject.winScrollSock = true;
			}
			$('.page_themeDetail .pro_list .product_list').html(code);
		}, 2);

		//屏幕滚动事件
		//$('.page_themeDetail.wrapper').scroll(function(){
		//	//滚动加载内容
		//	if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !themeDetailObject.winScrollSock){
		//		themeDetailObject.winScrollSock = true;
		//		themeDetailObject.loadtips({page:Math.ceil($('.page_themeDetail .product_list>li').size() / 5 + 1)});
		//	}
		//});

		// 加载默认筛选列表
		//themeDetailObject.loadtips();
	}
};




