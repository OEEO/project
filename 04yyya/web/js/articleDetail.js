var articleObject = {
	article_id : null,
    onload : function(){
        articleObject.article_id = win.get.article_id;
        if(!articleObject.article_id){
            $('.page_articleDetail').text('非法访问');
            return;
        }

        ajax('Goods/Article/getDetail', {article_id:articleObject.article_id}, function(d){
            if(d.info){
                $.alert(d.info, 'error');
                return;
            }
            //封面主图
            $('<img>').attr('src', d.path).prependTo('.page_articleDetail .headimg');
            //评论数量
            $('.page_articleDetail .comment span').text(d.comment_count);
            $('.page_articleDetail .comment').click(function(){
                jump('commentList', {'article_id': articleObject.article_id});
            });
            //赏赐次数
            $('.page_articleDetail .shang span').text(d.give_count);
            $('.page_articleDetail .headimg button').click(function(){

            });
            //标题
            $('.page_articleDetail .articletitle').text(d.title);
            //作者和日期
            $('.page_articleDetail .author').text('作者:' + d.author + ' [' + d.datetime + ']');
            //文章内容
            $('.page_articleDetail .content').html(d.content);

            if(d.tips.length > 0){
                //关联活动
                var code = '';
                for(var i in d.tips){
                    code += '<li>';
                    code += '	<div class="pro_top">';
                    code += '		<div class="User_Img">';
                    code += '			<img class="imgPortrait" src="'+ d.tips[i].headpic +'" />';
                    code += '		</div>';
                    code += '	<div class="User_Conten">';
                    code += '		<div class="Contens">';
                    code += '			<div class="qUserName">'+ d.tips[i].nickname +'</div>';
                    code += '        		<div class="msgDetail">';
                    code += '           		<div class="song_mi b_right">';
                    code += '               		<font>送米:&nbsp;</font>'+ d.tips[i].wealth +'人';
                    code += '               	</div>';
                    code += '           		<div class="song_mi">';
                    code += '           			<font>赏味:&nbsp;</font>'+ d.tips[i].customers +'人';
                    code += '           		</div>';
                    code += '        	 	</div>';
                    code += '     		</div>';
                    code += '    		<div class="User_right">';
                    code += '     			<a href="javascript:jump(\'tipsDetail\', {tips_id:'+ d.tips[i].id +'})"><img src="images/songmi.png" /></a>';
                    code += '     		</div>';
                    code += '		</div>';
                    code += '	</div>';
                    code += '   <div class="pro_center">';
                    code += '   	<span>'+ d.tips[i].catname +'</span>';
                    code += '   	<a href="javascript:jump(\'tipsDetail\', {tips_id:'+ d.tips[i].id +'})"><img src="'+ d.tips[i].mainpic +'" /></a>';
                    code += '	</div>';
                    code += '	<div class="pro_title">';
                    code += '		<span class="title_left">'+ d.tips[i].title +'</span>';
                    code += '  		<span class="price_right">￥'+ Math.floor(d.tips[i].price) +'/份</span>';
                    code += '	</div>';
                    code += '	<div class="pro_buttom">';
                    code += '		<span class="pro_time">'+ d.tips[i].start_time.timeFormat('m月d日 W H:i') +'-'+ d.tips[i].end_time.timeFormat('H:i') +'</span>';
                    code += '		<span class="pro_adress">'+ d.tips[i].address +'</span>';
                    code += '	</div>';
                    code += '</li>';
                    if(i%3 == 2)code += '<li><a href="javascript:void(0);"><div class="p_banner"></div></a></li>';
                }
                $('.page_articleDetail .tips .list').html(code);
            }else{
                $('.page_articleDetail .tips').remove();
            }
        });
    }
};
