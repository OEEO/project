var specialTipsObject = {
	theme_id : null,
	page : 1,
	sock : false,
	tips_load : function (){
		if($('.page_specialTips .the_end').html() == 'The End!!!')return;
		ajax('Home/Index/tips', {get : {'page' : specialTipsObject.page}, post : {'theme_id' : specialTipsObject.theme_id}}, function(d){
			$('.the_end').remove();
			if(d.length > 0){
				var code = '';
				for(var i in d) {
					code += '<div class="userHeadImg" style="height:4rem;">';
					code += '<img class="imgPortrait" src="'+ d[i].headpic.pathFormat() +'">';
					code += '<div class="userHeadName">' + d[i].nickname + '</div>';
					code += '</div>';
					code += '<a class="actImgLink" href="javascript:jump(\'tipsDetail\',{tips_id:'+d[i].id+'});">';
					code += '<img width="100%" src="' + d[i].path.pathFormat() + '" alt="Heineken"/>';
					code += '<div class="orderLast">剩余' + d[i].amount + '份</div>';
					code += '</a>';
					code += '<div class="actBottom">';
					code += '<div class="actTitle">' + d[i].title + '</div>';
					code += '<div class="actAdd">' + d[i].address + '</div>';
					code += '<div class="actTime">' + d[i].start_time.timeFormat('m.d W H:i') + '</div>';
					code += '<div class="clearfix"></div>';
					code += '<div class="bottom">'
					code += '<div class="mtags">';
					for (var j in d[i].tags) {
						code += '<span>' + d[i].tags[j] + '</span>';
					}
					code += '</div>';
					code += '<div class="actPrice">￥'+d[i].price+'</div>';
					code += '</div>';
					code += '</div>';
					code += '<div class="the_blank"></div>';
					code += '<div class="the_end"></div>';
				}
				$('.page_specialTips .content').append(code);
			}
			if(d.length == 5){
				$('.page_specialTips .content').append('<div class="the_end">loading...</div>');
			} else {
				$('.page_specialTips .content').append('<div class="the_end">The End!!!</div>');
			}
			specialTipsObject.sock = false;
		});
	},
	onload : function(){
		specialTipsObject.theme_id = win.get.theme_id;
		$('.page_specialTips.header .title').html(win.get.title);

		specialTipsObject.tips_load();

		$('.page_specialTips.wrapper').scroll(function(){
			//判断是否滚动到底部
			if($(this).scrollTop() >= this.scrollHeight - $(this).height() && !specialTipsObject.sock) {
				specialTipsObject.sock = true;
				specialTipsObject.page++;
				specialTipsObject.tips_load();
			}
		});
	}
};


