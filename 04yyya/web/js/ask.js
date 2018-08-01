var askObject = {
	page : 1,
	tips_id : null,
	whetherNull : function (em){
		var content = $(em).text();
		if (content== "我要提问" || content == "") {
			$(em).parent().children('button').attr('disabled',"true");
		}else{
			$(em).parent().children('button').removeAttr('disabled');
		}
	},
	contentLoad : function(){
		ajax('Goods/Tips/getFeedbackList', {get:{'page':askObject.page}, post:{'tips_id':askObject.tips_id}}, function(d){
			if(d.length > 0){
				//答疑列表
				var code = '<ul class="questionList">';
				for(var i in d){
					code += '<li>';
					code += '<div class="imgPortrait"><img src="'+ d[i].path.pathFormat() +'" /></div>';
					code += '<div style="float:left;width:78%">';
					code += '<div class="qUserName">'+ d[i].nickname +'</div>';
					code += '<div class="commitTime">'+ d[i].datetime +'</div>';
					code += '</div>';
					code += '<div class="question">'+ d[i].content +'</div>';
					code += '<div class="clearfix"></div>';
					if(d[i].answer != null)
						code += '<div class="answer">'+ d[i].answer +'</div>';
					code += '</li>';
				}
				code+='</ul>';
			}else{
				var code = '<center style="margin-top:2rem">暂时没有答疑</center>';
			}
			$('.page_ask .content').html(code);
		});
	},
	onload : function(){
		askObject.tips_id = win.get.tips_id;
		askObject.contentLoad();
	},
	onshow:function(){
		$('.page_ask.commitQuestion button').click(function(){
			var ask = $('.page_ask .m_question').text();
			if (ask== "我要提问" || ask == "") {
				$.alert("请输入问题", 'error');
			}else{
				ajax('Goods/Tips/submitAsk', {'tips_id':askObject.tips_id, 'ask':ask}, function(d){
					if(d.status == 1){
						$.alert('提交成功,等待达人回复...', function(){
							page.reload();
						});
					}else{
						$.alert(d.info, 'error');
					}
				});
			}
		});
	}
};



