var myCollectObject = {
	winScrollSock : false,
	//加载收藏
	load : function(page){
		var page = page||1;
		$('.page_myCollect center').show();
		ajax('member/follow/getCollectList', {get:{page:page}, post:{}}, function(d){
			if(d.info){
				$.alert(d.info, 'error');
				return;
			}else{
				if(d.length > 0){
					var code = '';
					for(var i in d){
						code += '<a class="item" href="javascript:jump(\'tipsDetail\', {tips_id:'+ d[i].id +'})">';
						code += '<div class="left"><img src="'+ d[i].path +'"><div class="category">'+ ['活动', '商品'][d[i].type] +'</div></div>';
						code += '<div class="right">';
						code += '<div class="t">['+ d[i].catname +']'+ d[i].title +'</div>';
						if(d[i].type == 0){
							code += '<div class="c">'+ d[i].times.start_time.timeFormat('Y-m-d W H:i') +'-'+ d[i].times.end_time.timeFormat('H:i') +'</div>';
							code += '<div class="b">'+ d[i].simpleaddress +'</div>';
						}else{
							code += '<div class="c"></div><div class="b">已售 '+ d[i].cell_count +' 份';
							if(d[i].shipping == 0)code += '<span>包邮</span>';
							code += '</div>';
						}
						code += '</div>';
						code += '</a>';
						code += '<button onclick="myCollectObject.cancel('+ d[i].type +', '+ d[i].id +')">取消收藏</button>';
					}
				}else{
					if(page==1)
						code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有收藏活动！</span></div>';
					else
						code = '<div class="no_more"></div>';
					$('.page_myCollect').off('scroll');
				}
				if(page == 1)
					$('.page_myCollect .content').html(code);
				else
					$('.page_myCollect .content').append(code);
				$('.page_myCollect center').hide();
			}

		});
	},
	cancel : function(type, type_id){
		$.dialog('您确定要取消收藏此活动吗?', function(){
			ajax('Member/Follow/ChangeCollect', {type:type, type_id:type_id, operate:0}, function(d){
				if(d.status == 1){
					$.alert('操作成功', function(){
						page.reload();
					});
				}else{
					$.alert(d.info, 'error');
				}
			});
		});
	},
	onload : function(){},
	onshow:function(){
		$('.page_myCollect .content').empty();
		/*屏幕滚动事件*/
		$('.page_myCollect.wrapper').on('scroll', function(){
			var pagenum = Math.ceil($('.page_myCollect .content>a').length / 10) + 1;
			if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10
				&& $('.page_myCollect center:visible').length == 0){
				myCollectObject.load(pagenum);
			}
		});
		myCollectObject.load();
	}
};
