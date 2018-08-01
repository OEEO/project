
if(parent.member == null){
	if(confirm("尚未登陆!是否进行登录？"))window.location.href(jump('ucenter.html'));
}else{
	member_id=parseInt(parent.member.id);
	//console.log("获取的用户id为："+member_id);
	whichToshow(0);
	}



var member_id=-1;

function whichToshow(num){
	$('.valueMenu').children('div').removeClass('show');
	$('.valueMenu').children('div').eq(num).addClass('show');
		
	$('.tipsList').css('display','none');
	$('.goodsList').css('display','none');
	$('.daRenList').css('display','none');
	switch(num){
		case 0:$('.tipsList').css('display','block');break;
		case 1:$('.goodsList').css('display','block');break;
		case 2:$('.daRenList').css('display','block');break;
		default:break;
		}
	
	contentLoad(num);
	}
	
$(window).scroll(function(){
	//滚动加载分页
});

var page = 1;
function contentLoad(type){
	//console.log("开始加载数据了哦...\n\t type="+type);
	ajax('Member/Follow/memberFollow', {get:{'page':page}, post:{'type':type}}, function(d){
		var valueCode = '';
		if(d.info){//如果没有对应的收藏记录
			//valueCode+="没有收藏记录！";
			console.log("没有对应的  "+type+"  收藏记录！");
		}else{
			switch(type){
				case 0://获取收藏的活动 列表
					for(var i in d){
/*						
<div class="tipsInfStyle">
	<a href="tips.html#651">
		<div class="orderListLeft">
			<div class="orderTypeBg"></div>
			<div class="orderType">饭局</div>
			<img width=100% height=100% src="uploads/20151012/1.jpg">
		</div>
	</a>	
		<div class="orderListRight">
			<a href="tips.html#651">
				<div class="orderTitle">综合咖啡体验，新手之选，走进咖啡的世界</div>
			</a>
				<div class="actAdd">珠江新城</div>
				<div class="actTime">09.26&#160;周六&#160;09:30</div>	
				<div class="actPrice">￥ 120</div>
				<div class="values valued" onclick="makeConditions('tips.html#651',this)"></div>
		</div>
		<div class="clearfix"></div>
</div>	
<div class="the_blank"></div>
	*/
					var nowTime = new Date().getTime();
					//console.log("获取的现在时间 \t"+nowTime+"\n\t数据库里的时间\t"+d[i].end_time);
					if(nowTime/1000<d[i].end_time){
							valueCode+='<div class="tipsInfStyle">';
						}else{
								valueCode+='<div class="tipsInfStyle overdue">';
							}
					
						valueCode+='<a href="javascript:parent.page.jump(\'tips.html?tips_id='+d[i].id+'\')"><div class="orderListLeft">';
						valueCode+='<div class="orderTypeBg"></div><div class="orderType">';
						valueCode+='饭局'+'</div><img width=100% height=100% src="'+d[i].path.pathFormat()+'"></div></a>';
						valueCode+='<div class="orderListRight"><a href="javascript:parent.page.jump(\'tips.html?tips_id='+d[i].id+'\')">';
						valueCode+='<div class="orderTitle">'+d[i].title+'</div></a>';
						valueCode+='<div class="actAdd">'+"测试地址"+'</div>';
						valueCode+='<div class="actTime">'+d[i].start_time.timeFormat("m.d W H:i")+'</div>';
						valueCode+='<div class="actPrice">￥'+d[i].price+'</div>';
						valueCode+='<div class="values valued" onclick="makeConditions(\'tips.html?tips_id='+d[i].id+'\',this)"></div></div>';
						valueCode+='<div class="clearfix"></div></div>';
						valueCode+='<div class="the_blank"></div>';
									}
						$('.tipsList').html(valueCode);
						break;
				
					case 1:for(var i in d){//获取收藏的 商品列表
/*					
<div class="goodsInfStyle">
	<a href="goodsDetail.html#651">
		<div class="orderListLeft">
			<img width=100% height=100% src="uploads/20151012/1.jpg">
		</div>
	</a>	
		<div class="orderListRight">
			<a href="goodsDetail.html#651">
				<div class="orderTitle">综合咖啡体验，新手之选，走进咖啡的世界</div>
			</a>	
				<div class="orderPrice">￥ 120</div>
				<div class="values valued" onclick="makeConditions('goodsDetail.html#651',this)"></div>
		</div>
		<div class="clearfix"></div>
</div>	
<div class="the_blank"></div>
*/
							//console.log("商品是否已经下架：\t"+d[i].status);
							if(parseInt(d[i].status)== 1){
								valueCode+='<div class="goodsInfStyle">';
							}else{
								valueCode+='<div class="goodsInfStyle overdue">';
								}
								valueCode+='<a href="javascript:parent.page.jump(\'goodsDetail.html?goods_id='+d[i].id+'\')">';
								valueCode+='<div class="orderListLeft">';
								valueCode+='<img width=100% height=100% src="'+d[i].pic_path.pathFormat()+'">';
								valueCode+='</div></a>';
								valueCode+='<div class="orderListRight">';
								valueCode+='<a href="javascript:parent.page.jump(\'goodsDetail.html?goods_id='+d[i].id+'\')">';
								valueCode+='<div class="orderTitle">'+d[i].title+'</div></a>';
								valueCode+='<div class="orderPrice">￥'+d[i].price+'</div>';
								valueCode+='<div class="values valued" onclick="makeConditions(\'goodsDetail.html?goods_id='+d[i].id+'\',this)"></div>';
								valueCode+='</div><div class="clearfix"></div></div>';
								valueCode+='<div class="the_blank"></div>';
											}
								$('.goodsList').html(valueCode);
							break;
								
					case 2:for(var i in d){//获取收藏的 达人列表
								valueCode+='<div class="daRenInfStyle">';
								valueCode+='<a href="javascript:parent.page.jump(\'daRen.html?daRen_id='+d[i].memberId+'\')">';
								valueCode+='<div class="daRenPic"><img class="imgPortrait" src="'+d[i].path.pathFormat()+'"></div>';
								valueCode+='<div class="daRenInf"><div class="daRenName">'+d[i].nickname+'</div>';
								valueCode+='<div class="daRenActInf">';
								valueCode+='<span>广州</span><i></i><span>5.0 星达人</span><i></i><span>22场活动</span>';
								valueCode+='</div>';
								valueCode+='</div></a>';
								valueCode+='<div class="values valued" onclick="makeConditions(\'daRen.html?daRen_id='+d[i].memberId+'\',this)"></div></div>';
								valueCode+='<div class="the_blank"></div>';
											}
								$('.daRenList').html(valueCode);
							break;
					default:break;	
					}//case 时间结束
		}
	});
}

