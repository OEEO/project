console.log("井号后的内容\t"+window.location.hash);

var page = 1,parameterId,RequestAdd;/*Type=[],*/ //参数id ，参数类型，接口地址；
Type = new Object();
$(function(){
/*	var parameter = window.location.href.split('#');
	console.log("获取传递的参数\t"+parameter);
	if(parameter <= 1){
		$('body').html('非法访问！');
		return;
	}
	// parameter[2];
	 parameterId = parameter[1];
	// parameterId =145;
	 
	if(parameter[2]==''||parameter[2]=='undefined'){
		}else{
			//var Type=[];
			switch(parameter[2]){
				case 'tips':Type.parameterType='tips_id';RequestAdd = 'Goods/Tips/getCommentList';evalueLoadTips();break;	
				case 'goods':Type.parameterType='goods_id';RequestAdd = 'Goods/Goods/getCommentList';evalueLoadGoods();break;	
				default:break;
				}
				console.log("请求的接口\t"+RequestAdd);
			}
*/
	//parameterId = 4254; 测试 tips 的 id
	//evalueLoad();
	
	var XX=GetHrefParameter();
	if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='tips_id'){parameterId = XX[i][1];evalueLoadTips();}
		if(XX[i][0]=='goods_id'){parameterId = XX[i][1];evalueLoadGoods();}
		}
});

$(window).scroll(function(){
	//滚动加载分页
});

function evalueLoadTips(){//加载活动的评论
		console.log("正在加载 活动 评论！ ");
		//console.log(Type['parameterType']);
		//var b=Type['parameterType'];
		ajax('Goods/Tips/getCommentList', {get:{'page':page}, post:{'tips_id':parameterId}}, function(d){	
		if(d.length > 0){
			//评论列表
			var code = '';
			for(var i in d){
				code += '<div class="comments"><img class="imgPortrait" src="'+d[i].path.pathFormat()+'" />';
				code += '<div class="right"><div class="name">'+ d[i].nickname +'<span>'+d[i].datetime+'</span></div>';
				var stars = '<div class="starGrade">';
				for(var j=0; j<5; j++){
					if(j < d[i].stars){
						stars +='<span></span>';
					}else{
						stars +='<span class="empty"></span>';
					}
				}
				stars+='</div>';
				//stars.append('<span class="empty"></span>');
				
				code +=stars;
				code +='</div>';//right结束
				code += '<div class="clearfix"></div>';
				code += '<p>'+d[i].content+'</p>';
				if(d[i].pics.length >0){
				code += '<div class="imgList" style="overflow:hidden;">';
					for( var j in d[i].pics){
						code +='<img src="'+d[i].pics[j].pathFormat()+'" />';
						}
				code+='</div>';
				}else{
					//如果评论图片组为空，就不加载 imgList
					}
				code += '</div>';//comments 的div 结束
				code += '<div class="the_blank"></div>';
			}
		}else{
			var code = '<center>暂时没有评价！</center>';
		}
		$('.content').html(code);
	});
}



function evalueLoadGoods(){//加载商品的评论
		console.log("正在加载 商品 评论！ ");
		//console.log(Type['parameterType']);
		//var b=Type['parameterType'];
		ajax('Goods/Goods/getCommentList', {get:{'page':page}, post:{'goods_id':parameterId}}, function(d){	
		if(d.length > 0){
			//评论列表
			var code = '';
			for(var i in d){
				code += '<div class="comments"><img class="imgPortrait" src="'+d[i].path.pathFormat()+'" />';
				code += '<div class="right"><div class="name">'+ d[i].nickname +'<span>'+d[i].datetime+'</span></div>';
				var stars = '<div class="starGrade">';
				for(var j=0; j<5; j++){
					if(j < d[i].stars){
						stars +='<span></span>';
					}else{
						stars +='<span class="empty"></span>';
					}
				}
				stars+='</div>';
				//stars.append('<span class="empty"></span>');
				
				code +=stars;
				code +='</div>';//right结束
				code += '<div class="clearfix"></div>';
				code += '<p>'+d[i].content+'</p>';
				if(d[i].pics.length >0){
				code += '<div class="imgList" style="overflow:hidden;">';
					for( var j in d[i].pics){
						code +='<img src="'+d[i].pics[j].pathFormat()+'" />';
						}
				code+='</div>';
				}else{
					//如果评论图片组为空，就不加载 imgList
					}
				code += '</div>';//comments 的div 结束
				code += '<div class="the_blank"></div>';
			}
		}else{
			var code = '<center>暂时没有评价！</center>';
		}
		$('.content').html(code);
	});
}