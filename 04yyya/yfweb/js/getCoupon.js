var getCouponObject = {
	//加载活动列表
	loadtips : function (){
		ajax('Home/Theme/getDetail', {theme_id:51}, function(d){
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
			if(d.tipsPass.length > 0){
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

			if(d.tips.length == 0){
				code = '<li class="the_end"><button onclick="$(\'.page_themeDetail.wrapper\').animate({\'scrollTop\':0})">回到顶部</button></li>';
			}
			$('.page_getCoupon .pro_list .product_list').html(code);
		}, 2);
	},
	onload : function(){
		//if(!member && !win.get.open_id){
		//	sessionStorage.jumpUrl = 'page=getCoupon';
		//	if(isWeiXin()){
		//		ajax('Home/Wx/getOauthUrl', function(d){
		//			if(typeof(d) == 'string'){
		//				window.location.href = d;
		//			}
		//		});
		//	}else{
		//		$.alert('请使用微信打开本网址!', 'error');
		//	}
		//	return;
		//}
		if(!member){
			// sessionStorage.jumpUrl = 'page=getCoupon';
			win.login();
			return;
		}
		if(member && member.invitecode)
			var url = win.host + '?page=getCoupon&invitecode=' + member.invitecode + '&target=' + win.get.target;
		else
			var url = win.host + '?page=getCoupon&target=' + win.get.target;
		share('K11专属福利限量领!!', 'K11*吖咪 专属福利限量领！ 广州K11邀请海内外星级匠人精心打造 Klass 11系列匠人课程 贝克汉姆的晚宴私厨 与您分享食材的故事 米其林三星主厨亲自教您制作顶级美味 体验传承意大利匠心的皮革艺术 最炫酷的3D打印艺术释放您的奇思妙想', url, win.host + 'images/k11_logo.png');
		//var open_id = '';
		//if(member){
		//	open_id = member.openid;
		//}else if(win.get.open_id){
		//	open_id = win.get.open_id;
		//}
		//ajax('member/index/auth', {open_id:open_id}, function(d){
		ajax('member/index/auth', {}, function(d){
			if(d.status == 1){
				if(d.info == 1){
					//已经关注过
					$('.page_getCoupon .page2').show();
					$('.page_getCoupon.header').show();
					getCouponObject.loadtips();
				}else{
					//尚未关注过
					$('.page_getCoupon .page1').show();
					$('.page_getCoupon.header').hide();
				}
			}else{
				$.alert(d.info, 'error');
			}
		}, 2);
	}
};