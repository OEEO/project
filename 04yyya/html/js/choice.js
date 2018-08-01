$(function(){
/***************************************************************************************/
	var _top;
	setTimeout(function (){   	
		_top = $('.filterMenu').offset().top;
		console.log("更新后的 Top 值"+_top);
	}, 1000);

	
	/*屏幕滚动事件*/	
	var cloneExist= 0;
	$(window).scroll(function(){
		/********************判断滚动位置是否到了 .filterMenu 的位置************************/
		if($(this).scrollTop()+$('.header').height()+2>_top&&_top){
			if(!cloneExist){
				var newfilterMenu = $('.filterMenu').clone(true,true).attr('id', 'filterMenu');
				newfilterMenu.appendTo('body');
				newfilterMenu.css({"border-bottom":"1px solid #ccc","position":"fixed","background":"#fff","width":"100%","top":$('.header').height(),"z-index":"998"})
				cloneExist=1;
			}
		}
		if($(this).scrollTop()+$('.header').height()<_top){
			$('#filterMenu').remove();
			cloneExist = 0;
		}
		/*************判断添加的克隆菜单是否存在，从而决定 img_item 的显示位置*******************/							  
		if(document.getElementById('filterMenu')&&cloneExist){//如果存在		
			$('.img_item').css({"position":"fixed","top":$('.filterMenu').height()+$('.header').height()}).show();
		}else{//如果不存在
			$('.img_item').css({"position":"absolute","top":$('.filterMenu').height()}).hide();
		}
		
		//滚动加载内容
		if($(this).scrollTop() + $(this).height() > $(document).height() - 10 && !winScrollSock){
			winScrollSock = true;
			loadtips({page:Math.ceil($('#actList>li').size() / 5 + 1)});
		}
	});
/*屏幕滚动事件结束*/	

	$('.ui-loader').remove();
	
});
var winScrollSock = false;

/*****************************************************************************************************/
var preLiNum = -1;//值 -1 表示 隐藏
function ChangeSelectMenuStyle(obj,num){	
	var lis = $(obj).parent().children('span');
		/*****************如果  筛选菜单的子菜单为显示状态，禁止 滚动事件******************/
	var	whether_same = preLiNum;
		preLiNum = num;
		if(whether_same!=num||preLiNum==-1) {
			
			$('.img_item').css({"visibility":"visible","display":"block"});
			$('.nav_1').css("visibility", "hidden");
			$('.nav_2').css("visibility", "hidden");
			$('.nav_3').css("visibility", "hidden");
			$('.nav_4').css("visibility", "hidden");
			lis.css("color", "#7d7d7d");
			lis.css({"background":"url('images/pulldown_L_normal@2x.png')no-repeat 80% 50%","background-size":"0.6rem 0.4rem"});
			lis.eq(num).css("color", "#fcd41a");
			var triangle_up_left = 10.5+num*25+'%';
			$('.triangle-up').css("margin-left",triangle_up_left);
			lis.eq(num).css({"background":"url('images/pulldown_L_selected@2x.png')no-repeat 80% 50%","background-size":"0.6rem 0.4rem"});
			switch (num) {
				case 0:
					$('.nav_1').css("visibility", "visible");
					break;
				case 1:
					$('.nav_2').css("visibility", "visible");
					break;
				case 2:
					$('.nav_3').css("visibility", "visible");
					break;
				case 3:
					$('.nav_4').css("visibility", "visible");
					break;
				default:
					break;
					 }
		}else{
			$('.img_item').css({"visibility":"hidden","display":"none"});
			$('.nav_1').css("visibility", "hidden");
			$('.nav_2').css("visibility", "hidden");
			$('.nav_3').css("visibility", "hidden");
			$('.nav_4').css("visibility", "hidden");
			lis.css("color", "#7d7d7d");
			lis.eq(num).css({"background":"url('images/pulldown_L_normal@2x.png')no-repeat 80% 50%","background-size":"0.6rem 0.4rem"});
			preLiNum =-1;
		}
	}//自定义函数结束
/*****************************************************************************************************/
$(document).click(function(){//系统的点击事件
	if($('.img_item').css('visibility')=="visible"){
		
	//**判断点击的是否是标签外的空白部分，是的话，隐藏 标签列表**
		$('.img_item_bg').click(function(e){
			var _filterMenu;
			if(document.getElementById('filterMenu')){
				_filterMenu = $(document.getElementById('filterMenu'));
			}else{_filterMenu =  $('.filterMenu'); }
			
			var lis =_filterMenu.children('span');
			//console.log(lis);
			$('.img_item').css({"visibility":"hidden","display":"none"});
			$('.nav_1').css("visibility", "hidden");
			$('.nav_2').css("visibility", "hidden");
			$('.nav_3').css("visibility", "hidden");
			$('.nav_4').css("visibility", "hidden");
			lis.css({"color":"#7d7d7d","background":"url('images/pulldown_L_normal@2x.png')no-repeat 80% 50%","background-size":"0.6rem 0.4rem"}); 
		});
		
		//document.documentElement.style.overflow='hidden';
		document.body.style.overflow='hidden';
		document.ontouchmove = function(e){ e.preventDefault();} //文档禁止 touchmove事件
		 
	}else{
		//document.documentElement.style.overflow='visible';
		document.body.style.overflow='visible';
		document.ontouchmove = function(e){  }; //文档允许 touchmove事件
		preLiNum = -1;
	}
});

/************************************/
