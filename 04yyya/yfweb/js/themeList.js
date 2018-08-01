var themeListObject = {
	onload : function(){
		/*if(win.get.theme_id && win.get.title){
		 themeObject.theme_id = win.get.theme_id;
		 $('.page_themeDetail.header .title').text(win.get.title);
		 }else{
		 $('.page_themeDetail').text('非法访问!');
		 return;
		 }*/

		ajax('Home/Theme/getlist', {}, function(d){
			if(d.info){
				$.alert(d.info, 'error');
				return;
			}

			if(d.list && d.list.length>0){
				var d = d.list;
				var code = '';
				var arr = [];
				for(var i in d){
					if(d[i].url){
						code += '<li><a href="'+ d[i].url +'">';
					}else{
						code += '<li><a href="javascript:jump(\'themeDetail\',{theme_id:'+ d[i].id +', title:\''+ d[i].title +'\'})">';
					}
					code += '	<img src="'+ d[i].path +'"/>';
					code += '	<div class="coveres">';
					code += '		<div class="t_time">'+ d[i].datetime.timeFormat('Y.m.d H:i') +'</div>';
					arr = d[i].title.split('|');
					code += '		<div class="t_titl">'+ arr[0] +'</div>';
					if(typeof(arr[1]) != "undefined"){
						code += '		<div class="t_subtitl">'+ arr[1] +'</div>';
					}
					code += '	</div>';
					code += '</a></li>';
				}
				$('.page_themeList .product_list').html(code);
			}else{
				$('.page_themeList .product_list').html('<div class="no_msgs"><img src="images/homepage_f4_rice_c.png" /><span>暂时还没有专题哦~</span></div>');
			}
		}, 2);

		/*屏幕滚动事件
		 $('.page_themeDetail.wrapper').scroll(function(){
		 //滚动加载内容
		 if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !themeObject.winScrollSock){
		 themeObject.winScrollSock = true;
		 themeObject.loadtips({page:Math.ceil($('.page_themeDetail .product_list>li').size() / 5 + 1)});
		 }
		 });

		 // 加载默认筛选列表
		 themeListObject.loadtips();*/
	}
};



