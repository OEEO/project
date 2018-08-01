var MsgsDetailObject = {
	page : 1,
	locked : false,
	loadOrder : function(){
		win.loading();
		ajax('Member/Message/getDetail', {get:{page:MsgsDetailObject.page},post:{origin_id:win.get.origin_id}}, function(d){
			win.close_loading();
			var code ='';
			var codes='';
			if(d.length > 0) {
				for (var i in d) {
					code = '<div class="item">';
					code += '	<div class="timess">' + d[i].datetime.timeFormat('Y-m-d  H:i') + '</div>';
					code += '	<div class="bottom_t">';
					code += '		<div class="User_Img">';
					code += '	    	<img class="imgPortrait" src="' + d[i].member_path + '" />';
					code += '		</div>';
					code += '		<div class="User_Conten">';
					code += '			<div class="jiao"></div>';
					code += '	    	<div class="Contens">' + d[i].content + '</div>';
					if(d[i].relation!=''){
						if(d[i].type==3){
							if(d[i].type == 0)
								code += '		<a class="top" href="javascript:jump(\'orderDetail\',{order_id:'+ d[i].type_id +'})">';
							else
								code += '		<a class="top" href="javascript:jump(\'orderGoodsDetail\',{order_id:'+ d[i].type_id +'})">';
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
					code += '		</div>';
					code += ' 	</div>';
					code += '</div>';
					codes = code + codes;
				}
				$('.header.page_MsgsDetail .title').text(d[0].nickname);
			}
			//}else{
			//	var codes = '<center>The End!!!</center>';
			//}
			
			var top = 0;
			if(MsgsDetailObject.page == 1){
				$('.page_MsgsDetail .msgList').html(codes);
				var ppp = setInterval(function(){
					if($('.page_MsgsDetail.wrapper').get(0).scrollHeight > 0){
						clearInterval(ppp);
						top = $('.page_MsgsDetail.wrapper').get(0).scrollHeight - $('.page_MsgsDetail.wrapper').height();
						$('.page_MsgsDetail.wrapper').scroll(function(){
							if($(this).scrollTop() < 10 && !MsgsDetailObject.locked){
								MsgsDetailObject.locked = true;
								var page = Math.ceil($('.page_MsgsDetail .msgList .item').size() / 10) + 1;
								if(page > MsgsDetailObject.page && $('.page_MsgsDetail .msgList center').size() == 0){
									MsgsDetailObject.page = page;
									MsgsDetailObject.loadOrder();
								}
							}
						});
						$('.page_MsgsDetail.wrapper').scrollTop(top);
						MsgsDetailObject.locked = false;
					}
				}, 100);
			}else{
				var em = $('.page_MsgsDetail .msgList .item:eq(0)');
				$('.page_MsgsDetail .msgList').prepend(codes);
				$('.page_MsgsDetail.wrapper').scrollTop(em.position().top);
				MsgsDetailObject.locked = false;
			}
		});
	},
	onload : function(){
		if(!member){
			win.login();
			return;
		}
		$('#message').hide();
		MsgsDetailObject.loadOrder();
	}
};
