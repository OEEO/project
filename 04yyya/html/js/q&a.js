function whetherNull(em){
			var content = $(em).text();
			if (content== "我要提问" || content == "") {
				$(em).parent().children('button').attr('disabled',"true");
			}else{
				$(em).parent().children('button').removeAttr('disabled');
				}
	}

$(function(){
/*	tips_id = window.location.href.split('#');
	if(tips_id <= 1){
		$('body').html('非法访问！');
		return;
	}
	tips_id = tips_id[1];*/
	var XX=GetHrefParameter();
	if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='tips_id')tips_id = XX[i][1];
		}
	contentLoad();
	
	$('.commitQuestion button').click(function(){
		var ask = $('.m_question').text();
		console.log("提问的问题：\t"+ask);
		if (ask== "我要提问" || ask == "") {
			alert("请输入问题！");
		}else{
			ajax('Goods/Tips/submitAsk', {'tips_id':tips_id, 'ask':ask}, function(d){
				if(d.status == 1){
					alert('提交成功！等待达人回复后显示出来！');
					$('.question').text('');
				}else{
					alert(d.info);
				}
			});
		}
	});
	
});

$(window).scroll(function(){
	//滚动加载分页
});

var page = 1,tips_id;
function contentLoad(){
	ajax('Goods/Tips/getFeedbackList', {get:{'page':page}, post:{'tips_id':tips_id}}, function(d){
		if(d.length > 0){
			//答疑列表
			var code = '<ul class="questionList">';
			for(var i in d){
				code += '<li>';
				code += '<img class="imgPortrait" src="'+ d[i].path.pathFormat() +'" />';
				code += '<div style="float:left;width:78%">';
				code += '<div class="qUserName">'+ d[i].nickname +'</div>';
				code += '<div class="commitTime">'+ d[i].datetime +'</div>';
				code += '</div>';
				code += '<div class="question">'+ d[i].content +'</div>';
				code += '<div class="clearfix"></div>';
				if(d[i].answer != null)
					code += '<div class="answer">'+ d[i].answer +'</div>';
				code += '</li>';
				code+='</ul>';
			}
		}else{
			var code = '<center style="margin-top:2rem">暂时没有答疑！</center>';
		}
		$('.content').html(code);
	});
}
