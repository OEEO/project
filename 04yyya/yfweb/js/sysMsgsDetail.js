var sysMsgsDetailObject = {
	page : 1,
	locked : false,
	loadOrder : function(){
		win.loading();
		ajax('Member/Message/getDetail', {get:{page:sysMsgsDetailObject.page},post:{type:win.get.type}}, function(d){
			win.close_loading();
			var code ='';
			var codes='';
			if(d.length > 0){
				for(var i in d){
					if(!d[i].content)continue;
					code ='<div class="item">';
					code +='	<div class="timess">'+ d[i].datetime.toString().timeFormat('Y-m-d H:i') +'</div>';
					code +='	<div class="bottom_t">';
					code +='		<div class="User_Img">';
					code +='	    	<img class="imgPortrait" src="'+ sysMsgsDetailObject.path +'" />';
					code +='		</div>';
					code +='		<div class="User_Conten">';
					code +='			<div class="jiao"></div>';
					var content = d[i].content.replace(/(http:\/\/\S+)/g, '<a href="$1">$1</a>');
					code +='	    	<div class="Contens">'+ content +'</div>';
					if(d[i].relation!=''){
						if(d[i].type==3){
							if(d[i].relation.type == 0)
								code += '		<a class="top" href="javascript:jump(\'orderDetail\',{order_id:'+ d[i].type_id +'})">';
							else if(d[i].relation.type == 1)
								code += '		<a class="top" href="javascript:jump(\'orderGoodsDetail\',{order_id:'+ d[i].type_id +'})">';
							else
								code += '		<a class="top" href="javascript:jump(\'orderRaiseDetail\',{order_id:'+ d[i].type_id +'})">';
						}else if(d[i].type==4){
							code +='        <a href="javascript:jump(\'tipsDetail\', {tips_id:'+ d[i].type_id +'})">';
						}else if(d[i].type==5){
							if(d[i].relation.url==''){
								code +='        <a href="javascript:jump(\'themeDetail\',{theme_id:'+ d[i].type_id +'})">';
							}else{
								code +='        <a href="'+ d[i].relation.url +'">';
							}
						}
						code +='	   		<div class="User_right">';
						code +='        		<img class="dayu" src="'+ d[i].relation.path +'" />';
						code +='        		<div class="t_right">';
						code +='       				<div class="titles">'+ d[i].relation.title +'</div>';
						if(d[i].type==3){
							code +='        			<div class="titles_sub">价格：'+ d[i].relation.price + '&nbsp;&nbsp;&nbsp;数量：'+ d[i].relation.count +'</div>';
						}else if(d[i].type==4){
							//code +='        			<div class="titles_sub">'+ d[i].relation.intro +'</div>';
						}else if(d[i].type==5){
							code +='        			<div class="titles_sub">'+ d[i].relation.content +'</div>';
						}
						code +='       			</div>';
						code +='   			</div>';
						code +='		</a>';

					}
					code +='		</div>';
					code +=' 	</div>';
					code +='</div>';
					codes = code + codes;
				}
			}
			var top = 0;
			if(sysMsgsDetailObject.page == 1){
				$('.page_sysMsgsDetail .msgList').html(codes);
				var ppp = setInterval(function(){
					if($('.page_sysMsgsDetail.wrapper').get(0).scrollHeight > 0){
						clearInterval(ppp);
						top = $('.page_sysMsgsDetail.wrapper').get(0).scrollHeight - $('.page_sysMsgsDetail.wrapper').height();
						$('.page_sysMsgsDetail.wrapper').scroll(function(){
							if($(this).scrollTop() < 10 && !sysMsgsDetailObject.locked){
								sysMsgsDetailObject.locked = true;
								var page = Math.ceil($('.page_sysMsgsDetail .msgList .item').size() / 10) + 1;
								if(page > sysMsgsDetailObject.page && $('.page_sysMsgsDetail .msgList center').size() == 0){
									sysMsgsDetailObject.page = page;
									sysMsgsDetailObject.loadOrder();
								}
							}
						});
						$('.page_sysMsgsDetail.wrapper').scrollTop(top);
						sysMsgsDetailObject.locked = false;
					}
				}, 100);
			}else{
				var em = $('.page_sysMsgsDetail .msgList .item:eq(0)');
				$('.page_sysMsgsDetail .msgList').prepend(codes);
				$('.page_sysMsgsDetail.wrapper').scrollTop(em.position().top);
				console.log(em.position().top);
				sysMsgsDetailObject.locked = false;
			}
		});
	},
	onload : function(){
		if(!member){
			win.login();
			return;
		}
		$('#message').hide();
		var tp = win.get.type;
		var path ='';
		var title ='';
		if(tp == '0'){
			title = '吖咪小助理';
			sysMsgsDetailObject.path = 'images/mymsgs_x1.png';
		}else if(tp == '1'){
			title = '评论与回复';
			sysMsgsDetailObject.path = 'images/mymsgs_x2.png';
		}else if(tp == '2'){
			title = '动态达人';
			sysMsgsDetailObject.path = 'images/mymsgs_x3.png';
		}else{
			title = '数据错误';
		}
		$('.header.page_sysMsgsDetail .title').text(title);
		sysMsgsDetailObject.loadOrder();
	}
};
