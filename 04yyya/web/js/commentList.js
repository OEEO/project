var commentListObject = {
	data : {},
	winScrollSock:false,
	load : function(page){
		var page = page||1;
		$('.page_commentList center').show();
		ajax('home/comment/getlist', {get:{page:page}, post:commentListObject.data}, function(d){
			$('.page_commentList center').hide();
			if(d.info){
				$.alert(d.info, 'error');
			}else{
				var code = '';
				for(var i in d){
					code += '<div class="item clearfix">';
					if(d[i].head_path != ''){
						code += '<div class="headpic"><img src="'+ d[i].head_path +'"></div>';
					}else{
						code += '<div class="headpic"><img src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg"></div>';
					}
					code += '<div class="right">';
					//if(d[i].reply && d[i].reply.length > 0){
					//	code += '<div class="nickname">'+ d[i].nickname +' <span>回复</span> '+ d[i].reply[0].nickname;
					//}else{
						code += '<div class="nickname">'+ d[i].nickname;
					//}
					
					if(d[i].type == 0 || d[i].type == 1){
						code += '<div class="starGrade">';
						for(var j = 0; j < 5; j ++){
							if(j < d[i].stars)
								code += '<span></span>';
							else
								code += '<span class="empty"></span>';
						}
						code += '</div>';
					}
					
					code += '<div class="context">'+ d[i].content +'</div>';
					if(d[i].pics.length > 0){
						code += '<div class="pics">';
						for(var j in d[i].pics){
							code += '<img src="'+ d[i].pics[j] +'">';
						}
						code += '</div>';
					}
					code += '<p class="report_time">';
					code +='<span>'+ d[i].datetime.timeFormat('Y.m.d H:i') +'</span>';
					if(d[i].is_report == 1)
						code += '<span class="report">[已举报]</span>';
					else
						code += '<span class="report" onclick="report('+d[i].id+', this)">[举报]</span>';
					code +='<p>';
					
					if(d[i].type == 2 || d[i].type == 3){
						code += '<button onclick="commentListObject.reply('+ d[i].member_id +', '+ d[i].nickname +')">回复</button>';
					}
					if(d[i].reply && d[i].reply.length > 0){
						code += '	<div class="tips_title">饭局 • '+ d[i].tips_title +'</div>';
						code += '	<div class="tips_hui"><font>达人回复：</font>'+ d[i].reply[0].content +'</div>';
						code += '	<div class="tips_time">' + d[i].reply[0].datetime.timeFormat('Y.m.d H:i') + '</div>';
					}
					code += '</div>';
					code += '</div>';
					code += '</div>';
				}
				if(code == ''){
					if(page == 1)
					$('.page_commentList .content').append('<div class="no_msgs"><img src="images/comments.png" /><span>暂时无评论！</span></div>');
					else
					$('.page_commentList .content').append('<div class="no_more"></div>');
				} else {
					if(page == 1){
						$('.page_commentList .content').html(code);
					}else{
						$('.page_commentList .content').append(code);
					}
					commentListObject.winScrollSock = false;
				}
			}
			$('.page_commentList .pics').each(function(){
				$(this).bubble();
			});
		});
	},
	reply : function(member_id, nickname){
		$('.page_commentList .m_question').attr('reply_id', member_id);
		$('.page_commentList .m_question').addClass('reply');
		$('.page_commentList .replyBox span').text(nickname);
		$('.page_commentList .replyBox').show();
	},
	onload : function(){
		if(win.get.tips_id) {
			commentListObject.data.tips_id = win.get.tips_id;
			$('.commitQuestion.page_commentList').remove();
		}else if(win.get.goods_id){
			commentListObject.data.goods_id = win.get.goods_id;
			$('.commitQuestion.page_commentList').remove();
		}

		if(win.get.article_id){
			commentListObject.data.article_id = win.get.article_id;
		}else if(win.get.bang_id){
			commentListObject.data.bang_id = win.get.bang_id;
		}else if(win.get.member_id) {
            commentListObject.data.member_id = win.get.member_id;
        }

		//滚动条加载
		$('.page_commentList.wrapper').scroll(function(){
			//滚动加载内容
			if($(this).scrollTop() + $(this).height() > $(this)[0].scrollHeight - 10 && !commentListObject.winScrollSock){
				commentListObject.winScrollSock = true;
				commentListObject.load(Math.ceil($('.page_commentList .item').size() / 10 + 1));
			}
		});

		commentListObject.load();
	}
};
