var nearTipsObject = {
	theme_id : null,
	page : 1,
	sock : false,
	tips_load : function (){
		if($('.page_nearTips .the_end').html() == 'The End!!!')return;
		if(win.location[0] == 0){
			if(wechat.getLocation){
				wechat.getLocation(function(res){
					if(res){
						ajax('Home/Index/getAddress', {latitude:res.latitude, longitude:res.longitude, is_location:1}, function(d){
							if(d.status == 1 && d.info.city_name.indexOf(win.city.name) != -1){
								win.location = [res.latitude, res.longitude];
								nearTipsObject.load();
							}else{
								$('.page_nearTips.wrapper .content').html('<center style="line-height:10rem;">获取到的定位无法匹配到选中的城市！</center>');
							}
						}, 2);
					}else{
						$('.page_nearTips.wrapper').html('获取定位失败！');
					}
				});
			}else{
				$('.page_nearTips.wrapper').html('获取定位失败！');
			}
		}else{
			nearTipsObject.load();
		}
	},
	load : function(){
		ajax('Home/Index/gps', {get : {'page' : nearTipsObject.page}, post : {'latitude' : win.location[0], 'longitude' : win.location[1]}}, function(d){
			$('.the_end').remove();
			if(d.length > 0){
				var code = '';
				for(var i in d) {
					code += '<div class="userHeadImg" style="height:4rem;">';
					code += '<img class="imgPortrait" src="'+ d[i].headpic.pathFormat() +'">';
					code += '<div class="userHeadName">' + d[i].nickname + '</div>';
					code += '<div class="near">驾车距离：' + d[i].distance + '<br>耗时：约' + d[i].duration + '</div>';
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
				$('.page_nearTips .content').append(code);
			}
			if(d.length == 5){
				$('.page_nearTips .content').append('<div class="the_end">loading...</div>');
			} else {
				$('.page_nearTips .content').append('<div class="the_end">The End!!!</div>');
			}
			nearTipsObject.sock = false;
		});
	},
	onload : function(){
		nearTipsObject.theme_id = win.get.theme_id;
		$('.page_nearTips.header .title').html(win.get.title);
		nearTipsObject.tips_load();
		$('.page_nearTips.wrapper').scroll(function(){
			//判断是否滚动到底部
			if($(this).scrollTop() >= this.scrollHeight - $(this).height() && !nearTipsObject.sock) {
				nearTipsObject.sock = true;
				nearTipsObject.page++;
				nearTipsObject.tips_load();
			}
		});
	}
};




