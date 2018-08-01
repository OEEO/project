var daRenObject ={
	daRen_id : null,
	follow : null,
	page : 1,
	sock : false,
	onload : function(){
		daRenObject.member_id = win.get.member_id;
		ajax('Home/Daren/darenZone', {member_id : daRenObject.member_id}, function(d){
			if(d.status == 0){
				$.alert(d.info,function(){
					page.back();
				},'error');
				return;
			}
			//分享绑定
			$('.page_daRen .u_shares').click(function(){
				var desc = d.base_info.daren_info.introduce;
				var url = win.host + '?page=choice-daRen&member_id=' + daRenObject.member_id;
				if(member && member.invitecode){
					url += '&invitecode=' + member.invitecode;
				}
				share(d.base_info.daren_info.nickname, desc, url, d.base_info.daren_info.pic_path);

				showShareBox();
			});

			$('.page_daRen .bg').attr('src', d.base_info.daren_info.cover_path);
			$('.page_daRen .imgPortrait img').attr('src',d.base_info.daren_info.pic_path);
			$('.page_daRen .front .name').text(d.base_info.daren_info.nickname);
			$('.page_daRen .front .user_introduce .activity_num').text(d.base_info.tips);
			$('.page_daRen .front .user_introduce .shanwei').text(d.base_info.shangwei);
			var code = '<span class="m_li">活动<font style="color:#b39851;padding-left: 1rem;">'+ d.base_info.tips +'</font></span>';
			code += '<span class="m_li">赏味<font style="color:#b39851;padding-left: 1rem;">'+ d.base_info.shangwei +'</font></span>';
			code += '<span class="m_li">粉丝<font style="color:#b39851;padding-left: 1rem;" class="fans">'+ d.base_info.fans*3 +'</font></span>';
			//code += '<span class="m_li"><font style="color:#b39851">'+ d.base_info.mibi +'</font><br/>送米</span>';
			code += '';
			$('.page_daRen .menus_li').html(code);
			if(d.base_info.daren_info.introduce){
				$('.page_daRen .intro_content').html(d.base_info.daren_info.introduce);
			}else{
				$('.page_daRen .intro_content').text('主人暂无介绍~');
			}
			$('.page_daRen .activiting').text(d.base_info.doing);
			$('.page_daRen .activitied').text(d.base_info.over_tips);
			$('.page_daRen .commentss').text(d.base_info.comment);
			if(d.base_info.isfollow)$('.page_daRen .followBtn button').addClass('valued');
			$('.page_daRen .followBtn button').attr('data', daRenObject.member_id).click(function(){
				setFollow(this, function(d){
					if(d){
						$('.page_daRen .fans').text(parseInt($('.page_daRen .fans').text()) + 1);
					}else{
						$('.page_daRen .fans').text(parseInt($('.page_daRen .fans').text()) - 1);
					}
				});
			});
			//展开达人简介
			$('.page_daRen .text_intro .check_all').click(function(){
				$('.page_daRen .text_intro .intro_content').addClass('all');
				$(this).hide();
			});
			var l = parseInt($('.page_daRen .text_intro .intro_content').text().length);
			if(l<110)$('.page_daRen .text_intro .check_all').hide();

			if(d.dynamic.tips.id){
				var codet = '<li>';
				codet += '<div class="pro_top">';
				codet += '	<div class="User_Img">';
				codet += '   <img class="imgPortrait" src="'+ d.dynamic.tips.headpic +'" />';
				codet += '	</div>';
				codet += '	<div class="User_Conten">';
				codet += '		<div class="Contens">';
				codet += '			<div class="qUserName">'+ d.dynamic.tips.nickname +'</div>';
				codet += '				<div class="msgDetail">';
				codet += '					<div class="song_mi b_right">';
				codet += '						<font>粉丝:&nbsp;</font>'+ d.base_info.fans*3 +'人';
				codet += '					</div>';
				codet += '					<div class="song_mi">';
				codet += '						<font>赏味:&nbsp;</font>'+ d.dynamic.tips.customers +'人';
				codet += '					</div>';
				codet += '				</div>';
				codet += '			</div>';
				codet += '			<!--<div class="User_right">';
				codet += '				<a href=""><img src="images/songmi.png" /></a>';
				codet += '			</div>-->';
				codet += '		</div>';
				codet += '	</div>';
				codet += '	<div class="pro_center">';
				codet += '		<span>'+ d.dynamic.tips.catname +'</span>';
				codet += '		<a href="javascript:jump(\'tipsDetail\',{tips_id:'+ d.dynamic.tips.id +'});"><img src="'+ d.dynamic.tips.mainpic +'" /></a>';
				codet += '	</div>';
				codet += '	<div class="pro_title">';
				codet += '		<span class="title_left">'+ d.dynamic.tips.title +'</span>';
				// codet += '		<span class="price_right">￥'+ parseFloat(d.dynamic.tips.price).priceFormat() +'/份</span>';
				codet += '	</div>';
				codet += '	<div class="pro_buttom">';
				codet += '		<span class="pro_time">'+d.dynamic.tips.start_time.timeFormat('m-d W H:i') +'-'+ d.dynamic.tips.end_time.timeFormat('H:i')+'</span>';
				codet += '		<span class="pro_adress">'+ d.dynamic.tips.address +'</span>';
				codet += '		<span class="price_right">￥'+ parseFloat(d.dynamic.tips.price).priceFormat() +'/份</span>';
				codet += '	</div>';
				codet += '	<div class="pro_line"></div>';
				codet += '</li>';
				$('.page_daRen .product_list').html(codet);
			}else{
				$('.page_daRen .center_title').remove();
			}
		});
	}
};
