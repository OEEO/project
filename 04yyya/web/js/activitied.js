var activitiedObject ={
	member_id : null,
	follow : null,	
	page : 1,
	sock : false,
	is_overdue:0,
	loadTips : function(){
		$('.page_activitied center').show();
		ajax('Goods/Tips/getlist',{get:{page:activitiedObject.page}, post:{member_id : activitiedObject.member_id, is_overdue: activitiedObject.is_overdue}}, function(d){
			$('.page_activitied center').hide();
			if(d.length==0){
				if(activitiedObject.page==1)
				$('.page_activitied .product_list').append('<div class="no_msgs"><img src="images/huodong_noon.png" /><span>抱歉！您还没有活动噢~</span></div>');
				else
				$('.page_activitied .product_list').append('<div class="no_more"></div>');
				activitiedObject.sock = true;
			}else{
				var codet ='';
				for(var i in d){
					codet += '<li>';
					/*codet += '	<div class="pro_top">';
					codet += '		<div class="User_Img">';
					codet += '   		<img class="imgPortrait" src="'+ d[i].headpic +'" />';
					codet += '		</div>';
					codet += '		<div class="User_Conten">';
					codet += '			<div class="Contens">';
					codet += '				<div class="qUserName">'+ d[i].nickname +'</div>';
					codet += '					<div class="msgDetail">';
					codet += '						<div class="song_mi b_right">';
					codet += '							<font>送米:&nbsp;</font>'+ d[i].wealth +'人';
					codet += '						</div>';
					codet += '						<div class="song_mi">';
					codet += '							<font>赏味:&nbsp;</font>'+ d[i].customers +'人';
					codet += '						</div>';
					codet += '					</div>';
					codet += '				</div>';
					codet += '			<div class="User_right">';
					codet += '				<a href=""><img src="images/songmi.png" /></a>';
					codet += '			</div>';
					codet += '		</div>';
					codet += '		</div>';*/
					codet += '	<div class="pro_center">';
					codet += '		<span>'+ d[i].catname +'</span>';
					if(activitiedObject.is_overdue==1){
						codet += '		<img src="'+ d[i].mainpic +'" />';
					}else{
						if(d[i].sellout==1)
						codet += '		<div class="sellout"><img src ="images/sellout.png" /></div>';
						codet += '		<a href="javascript:jump(\'tipsDetail\',{tips_id:'+ d[i].id +'});"><img src="'+ d[i].mainpic +'" /></a>';
					}
					codet += '	</div>';
					codet += '	<div class="pro_title">';
					codet += '		<span class="title_left">'+ d[i].title +'</span>';
					codet += '		<span class="price_right">￥'+ parseFloat(d[i].price).priceFormat() +'/份</span>';
					codet += '	</div>';
					codet += '	<div class="pro_buttom">';
					if(activitiedObject.is_overdue==1){
						codet += '		<span class="pro_time">已完结</span>';
					}else{
						codet += '		<span class="pro_time">'+d[i].start_time.timeFormat('m月d日 W H:i') +'-'+ d[i].end_time.timeFormat('H:i')+'</span>';
					}
					codet += '		<span class="pro_adress">'+ d[i].address +'</span>';
					codet += '	</div>';
					codet += '</li>';
				}
				$('.page_activitied .product_list').append(codet);
				activitiedObject.page ++;
				activitiedObject.sock = false;
			}
		});
	},
	onload : function(){
		if(!win.get.member_id){
			$.alert('非法访问', function(){
				page.back();
			},'error');
			return;
		}
		activitiedObject.member_id = win.get.member_id;
		activitiedObject.is_overdue = win.get.is_overdue;
		activitiedObject.loadTips();
		//滑动到底部执行加载
		$('.page_activitied.wrapper').scroll(function(){
			if($(this).scrollTop() >= this.scrollHeight - $(this).height() && !activitiedObject.sock){
				activitiedObject.sock = true;
				activitiedObject.loadTips();
			}
		});
		if(activitiedObject.is_overdue==0){
			$('.header.page_activitied .title').text('即将开始的活动');
		}
	}
};
