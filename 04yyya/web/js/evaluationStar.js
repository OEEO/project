var evaluationStarObject = {
	order_id : null,
	evaluationStar : function(num){
		$('.page_evaluationStar .stars span').removeClass();
		for(var i=0; i<=num; i++){
			$('.page_evaluationStar .stars span:eq('+ i +')').addClass('linght');
		}
	},
	submit : function(){
		$.dialog('提交后无法修改，确定吗？', function(){
			var content = $('.page_evaluationStar .detailWords').val();
			if(content.length > 500 || content.length < 0){
				$.alert('评论内容必须填写，且不能超过500字', 'error');
				return;
			}
			var stars = $('.page_evaluationStar .stars .linght').size();
            var pic_ids = [];
            $('.page_evaluationStar .addImgs img').each(function(){
               pic_ids.push($(this).attr('pic_id'));
            });
			ajax('Home/Comment/add', {order_id : evaluationStarObject.order_id, content : content, stars : stars, pic_ids : pic_ids}, function(d){
				if(d.status == 1){
					$.alert('评论成功', function(){
						win.get.order_id=evaluationStarObject.order_id;
						page.back();
					});
				}else{
					$.alert(d.info, 'error');
				}
			});
		});
	},
    remove: function (e, id) {
        $(e).parent().remove();
    },
	onload : function(){
		evaluationStarObject.order_id = win.get.order_id;
		for(var i=0; i<5; i++){
			$('<span>').addClass('linght').click(function(){
				var num = $(this).index();
				evaluationStarObject.evaluationStar(num);
			}).appendTo('.page_evaluationStar .stars');
		}
	},
	onshow:function(){
		$('.page_evaluationStar .addImgs .btn').click(function(){
			jump('myPictures', {size:[200,200], count:8, backFun:function(d){
				var code = '';
				for(var i in d){
					code += '<li>' +
						'<img src="'+ d[i].path +'" pic_id="'+ d[i].pic_id +'">' +
						'<a href="javascript:void(0);" onclick="evaluationStarObject.remove(this,'+ d[i].pic_id +')">×</a>' +
						'</li>';
				}
				$('.page_evaluationStar .addImgs').prepend(code);
			}});
		});
	}
};
