var myValueObject ={
	whichToshow : function(num){
	$('.page_myValue .valueMenu').children('div').removeClass('show');
	$('.page_myValue .valueMenu').children('div').eq(num).addClass('show');
		
	$('.page_myValue .tipsList').css('display','none');
	$('.page_myValue .goodsList').css('display','none');
	$('.page_myValue .daRenList').css('display','none');
		switch(num){
			case 0:$('.page_myValue .tipsList').css('display','block');break;
			case 1:$('.page_myValue .goodsList').css('display','block');break;
			case 2:$('.page_myValue .daRenList').css('display','block');break;
			default:break;
			}
	
	 myValueObject.contentLoad(num);
	},

CancleValue : function(type,id,Class){// 当取消收藏时，删除当前 div 和 间隔的 the_blank
	var doDel;
	if(type!="daRen"){
		doDel=$(Class).parent().parent();
		}else{
		doDel=$(Class).parent();	
			}
	//console.log("将要删除的 \t"+$(doDel).attr("class"));
	
//	$(doDel).remove();
//	$(doDel).next('.the_blank').remove();
	
	makeConditions(type,id,Class);
	},	
 contentLoad : function (type){
	//console.log("开始加载数据了哦...\n\t type="+type);
	ajax('Member/Follow/memberFollow',{'type':type}, function(d){
		var valueCode = '';
		if(d.info){//如果没有对应的收藏记录
			//valueCode+="没有收藏记录！";
			console.log("没有对应的  "+type+"  收藏记录！");
		}else{
			switch(type){
				case 0://获取收藏的活动 列表
					for(var i in d){
					var nowTime = new Date().getTime();
					//console.log("获取的现在时间 \t"+nowTime+"\n\t数据库里的时间\t"+d[i].end_time);
					if(nowTime/1000<d[i].end_time){
							valueCode+='<div class="tipsInfStyle">';
					}else{
							valueCode+='<div class="tipsInfStyle overdue">';
						}
						valueCode+='<a href="javascript:jump(\'tipsDetail\',{tips_id:'+d[i].id+'})"><div class="orderListLeft">';
						valueCode+='<div class="orderTypeBg"></div><div class="orderType">';
						valueCode+='饭局'+'</div><img width=100% height=100% src="'+d[i].path.pathFormat()+'"></div></a>';
						valueCode+='<div class="orderListRight"><a href="javascript:jump(\'tipsDetail\',{tips_id:'+d[i].id+'})">';
						valueCode+='<div class="orderTitle">'+d[i].title+'</div></a>';
						valueCode+='<div class="actAdd">'+"测试地址"+'</div>';
						valueCode+='<div class="actTime">'+d[i].start_time.timeFormat("m.d W H:i")+'</div>';
						valueCode+='<div class="actPrice">￥'+d[i].price+'</div>';
						valueCode+='<div class="values valued" onclick="myValueObject.CancleValue(\'tipsDetail\','+d[i].id+',this)"></div></div>';
						valueCode+='<div class="clearfix"></div></div>';
						valueCode+='<div class="the_blank"></div>';
									}
						$('.page_myValue .tipsList').html(valueCode);
						break;
				
				case 1://获取收藏的 商品列表
					for(var i in d){
						//console.log("商品是否已经下架：\t"+d[i].status);
						if(parseInt(d[i].status)== 1){
							valueCode+='<div class="goodsInfStyle">';
						}else{
							valueCode+='<div class="goodsInfStyle overdue">';
							}
						valueCode+='<a href="javascript:jump(\'goodsDetail\',{goods_id:'+d[i].id+'})">';
						valueCode+='<div class="orderListLeft">';
						valueCode+='<img width=100% height=100% src="'+d[i].path.pathFormat()+'">';
						valueCode+='</div></a>';
						valueCode+='<div class="orderListRight">';
						valueCode+='<a href="javascript:jump(\'goodsDetail\',{goods_id:'+d[i].id+'})">';
						valueCode+='<div class="orderTitle">'+d[i].title+'</div></a>';
						valueCode+='<div class="orderPrice">￥'+d[i].price+'</div>';
						valueCode+='<div class="values valued" onclick="myValueObject.CancleValue(\'goodsDetail\','+d[i].id+',this)"></div>';
						valueCode+='</div><div class="clearfix"></div></div>';
						valueCode+='<div class="the_blank"></div>';
									}
						$('.page_myValue .goodsList').html(valueCode);
						break;
								
				case 2://获取收藏的 达人列表
					for(var i in d){
						valueCode+='<div class="daRenInfStyle">';
						valueCode+='<a href="javascript:jump(\'daRen\',{daRen_id:'+d[i].memberId+'})">';
						valueCode+='<div class="daRenPic"><img class="imgPortrait" src="'+d[i].path.pathFormat()+'"></div>';
						valueCode+='<div class="daRenInf"><div class="daRenName">'+d[i].nickname+'</div>';
						valueCode+='<div class="daRenActInf">';
						valueCode+='<span>广州</span><i></i><span>5.0 星达人</span><i></i><span>22场活动</span>';
						valueCode+='</div>';
						valueCode+='</div></a>';
						valueCode+='<div class="values valued" onclick="myValueObject.CancleValue(\'daRen\','+d[i].memberId+',this)"></div></div>';
						valueCode+='<div class="the_blank"></div>';
									}
						$('.page_myValue .daRenList').html(valueCode);
						break;
				default:break;	
					}//switch 结束
			}
		});//ajax请求 结束
	},
	onload : function(){
		if(member == null){
			$.dialog("尚未登陆!是否进行登录？", function(){
				window.location.href("javascript:jump('ucenter')");
			});
		}else{
			myValueObject.member_id=parseInt(member.id);
			//console.log("获取的用户id为："+member_id);
			myValueObject.whichToshow(0);
		}
	}
};

