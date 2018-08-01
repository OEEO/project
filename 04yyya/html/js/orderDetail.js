$(function(){
	var XX=GetHrefParameter();
	//if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='order_id')order_id = XX[i][1];
		}
		
	ajax('Member/Order/getDetail',{'order_id':order_id},function(d){
/*
	<div class="consumerCode">
		<div class="consumptionStatusTop">
			<div class="consumptionStatusLeft">消费码</div>
			<div class="consumptionStatusRight" onclick="showCode();">查看二维码</div>
		</div>
		<ul class="consumerCodeList">
			<li>1000 2345 5652<span style="color#000:;">待验证</span></li>
			<li><del>1000 2345 5652</del><span style="color:#d1d1d1;">已验证</span></li>
		</ul>
		<div class="consumerCodeInf">有效期
			<div class="consumerCodeInfDate">2015年6月23日 18:00</div>
		</div>
	</div>
	<div class="the_blank" ></div>
	<!-- ------------------------------------------------------------- -->

	<div class="orderInf">
		<div class="actInfTitle">
			<div class="actInfDaren">达人：Frank Hoo</div>
			<a class="actInfconnect"  href="tel:123456789">联系达人</a>
		</div>
		<a href="">
			<div class="orderListLeft">
				<div class="orderTypeBg"></div>
				<div class="orderType">饭局</div>
				<img width=100% height=100% src="uploads/20151012/1.jpg">
			</div>
			<div class="orderListRight">
				<div class="orderTitle">综合咖啡体验，新手之选，走进咖啡的世界</div>
				<div class="orderTime">￥ 120×1</div>
				<div class="total">待付款</div>
			</div>
			<div class="clearfix"></div>
		</a>
	</div>
	<div class="the_blank" ></div>
	
	<ul class="orderDetail">
		<li class="orderAddress">地点<span>滚动图片特效是前端开发常用的特效在有限空间展示更多内展示更多内容展示更多内容展示更多内容容</span></li>
		<div class="clearfix"></div>
		<li>时间<span>2015.6.23 周六 18:00-20:00</span></li>
		<li>手机<span>18888888888</span></li>
		<li>优惠金额<span>-￥20.00</span></li>
		<li>订单总额<span>￥380.00</span></li>
		<li  class="leaveWords">留言<span>无缝滚动图片特效在网站经常见到，可无缝循环左右滚动,速度能自定义，可以用“无处不在”来形容它。有横向和上下方向平滑滚动的代码，鼠标划过自动停止</span></li>
		<div class="clearfix"></div>
	</ul>


	<div class="orderDetailBottom">
		<div>订单编号：<span>12000400586</span></div>
		<div>下单时间：<span>2015年12月31日 10:23:48</span></div>
		<div class="services" onclick="javascript:alert();">联系客服</div>
	</div>
	<div class="bottomBlank"></div>
	<div class="orderBottom">
		<div class="left">剩余时间: <span><i>14</i>分<i>33</i>秒</span></div>
		<div class="right"><a href="payMoney.html">立即付款</a></div>
	</div>
*/	
	var code='';
	if(d.act_status==0)	{//订单未支付状态
		$('.header').html($('.header').html()+'<div class="Operation" onclick="javascript:confirm(\'真的要取消订单吗？\');">取消订单</div>');
	}else if(d.act_status==7){//订单已取消状态
	
	}else{
		if(d.act_status==2){
			$('.header').html($('.header').html()+'<div class="Operation" onclick="javascript:confirm(\'真的要取消订单，申请退款 吗？\');">申请退款</div>');
			}
		code +='<div class="consumerCode">';
		code +='<div class="consumptionStatusTop">';
		code +='<div class="consumptionStatusLeft">消费码</div>';
		code +='<div class="consumptionStatusRight" onclick="showCode();">查看二维码</div>';
		code +='</div>';
		code +='<ul class="consumerCodeList">';
		var checkCode;
		for(var i in d.check_code){
			if(d.check_code[i].status==0){
				checkCode +='<li>'+d.check_code[i].code+'<span style="color：#000;">待验证</span></li>';
			}else{checkCode +='<li><del>'+d.check_code[i].code+'</del><span style="color:#d1d1d1;">已验证</span></li>';}
		}
		code +=checkCode;
		code +='</ul>';
		code +='<div class="consumerCodeInf">有效期';
		code +='<div class="consumerCodeInfDate">'+d.end_time.timeFormat("y.m.d H:i")+'</div>';
		code +='</div>';
		code +='</div>';
		code +='<div class="the_blank" ></div>';
	}
	code +='<div class="orderInf">';
	code +='<div class="actInfTitle">';
	code +='<div class="actInfDaren">'+d.nickname+'</div>';
	code +='<a class="actInfconnect"  href="tel:'+d.telephone+'">联系达人</a>';
	code +='</div>';

	code +='<a href="javascript:parent.page.jump(\'tips?tips_id='+d.id+'\')">';
	code +='<div class="orderListLeft">';
	code +='<div class="orderTypeBg"></div>';
	code +='<div class="orderType">饭局</div>';
	code +='<img width=100% height=100% src="uploads/20151012/1.jpg">';
	code +='</div>';
	code +='<div class="orderListRight">';
	code +='<div class="orderTitle">综合咖啡体验，新手之选，走进咖啡的世界</div>';
	code +='<div class="orderTime">￥ 120×1</div>';
	code +='<div class="status">待付款</div>';
	code +='</div>';
	code +='<div class="clearfix"></div>';
	code +='</a>';
/*
</div>
	<div class="the_blank" ></div>
	
	<ul class="orderDetail">
		<li class="orderAddress">地点<span>滚动图片特效是前端开发常用的特效在有限空间展示更多内展示更多内容展示更多内容展示更多内容容</span></li>
		<div class="clearfix"></div>
		<li>时间<span>2015.6.23 周六 18:00-20:00</span></li>
		<li>手机<span>18888888888</span></li>
		<li>优惠金额<span>-￥20.00</span></li>
		<li>订单总额<span>￥380.00</span></li>
		<li  class="leaveWords">留言<span>无缝滚动图片特效在网站经常见到，可无缝循环左右滚动,速度能自定义，可以用“无处不在”来形容它。有横向和上下方向平滑滚动的代码，鼠标划过自动停止</span></li>
		<div class="clearfix"></div>
	</ul>

*/	
	code +='</div>';
	code +='<div class="the_blank" ></div>';
	code +='<ul class="orderDetail">';
	code +='<li class="orderAddress">地点<span>滚动图片特效是前端开发常用的特效在有限空间展示更多内展示更多内容展示更多内容展示更多内容容</span></li>';
	code +='<div class="clearfix"></div>';
	code +='<li>时间<span>2015.6.23 周六 18:00-20:00</span></li>';
	code +='<li>手机<span>18888888888</span></li>';
	code +='<li>优惠金额<span>-￥20.00</span></li>';
	code +='<li>订单总额<span>￥380.00</span></li>';
	code +='<li  class="leaveWords">留言<span>无缝滚动图片特效在网站经常见到，可无缝循环左右滚动,速度能自定义，可以用“无处不在”来形容它。有横向和上下方向平滑滚动的代码，鼠标划过自动停止</span></li>';
	code +='<div class="clearfix"></div>';
	code +='</ul>';
/*
<div class="orderDetailBottom">
	<div>订单编号：<span>12000400586</span></div>
	<div>下单时间：<span>2015年12月31日 10:23:48</span></div>
	<div class="services" onclick="javascript:alert();">联系客服</div>
</div>
<div class="bottomBlank"></div>
<div class="orderBottom">
	<div class="left">剩余时间: <span><i>14</i>分<i>33</i>秒</span></div>
	<div class="right"><a href="payMoney.html">立即付款</a></div>
</div>
*/
	code +='<div class="orderDetailBottom">';
	code +='<div>订单编号：<span>12000400586</span></div>';
	code +='<div>下单时间：<span>2015年12月31日 10:23:48</span></div>';
	code +='<div class="services" onclick="javascript:alert();">联系客服</div>';
	code +='</div>';
	code +='<div class="bottomBlank"></div>';
	code +='<div class="orderBottom">';
	code +='<div class="left">剩余时间: <span><i>14</i>分<i>33</i>秒</span></div>';
	code +='<div class="right"><a href="payMoney.html?order_id=">立即付款</a></div>';
	code +='</div>';
	
	$('mainContent').html(code);
	
	/*消费码部分数据*/
	$('.QRcode .views .c').children('span').text("1234 4567");
	var imgs = '<img width=100% height=100% src="./images/pic2.jpg">';
	$('.QRcode .views .d').html(imgs+$('.QRcode .views .d').html());
	/********/
	
	},false);
	

});

var order_id = null;

function showCode(){
	$('.QRcode').css('display','block');
	$('#mainContent').addClass('content');
	
	var top = ($(window).height()-$('.QRcode .views').height())/3;
	var wid = ($(window).width()-$('.QRcode .views').width())/1.6;
	var closedTop =$('.QRcode .views').height()+top;
	$('.QRcode .closed').css("top",closedTop);
	$('.QRcode .views').css({'margin-top':top,'margin-left':wid});

	document.body.style.overflow='hidden';
	document.ontouchmove = function(e){ e.preventDefault();} //文档禁止 touchmove事件
}

function hideCode(){
	$('.QRcode').css('display','none');
	$('#mainContent').removeClass('content');
	document.body.style.overflow='visible';
	document.ontouchmove = function(e){} //文档禁止 touchmove事件
}