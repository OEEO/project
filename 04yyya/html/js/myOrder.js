$(function(){
	ajax('Member/Order/index', {get:{page:1}}, function(d){
		var code = '';
		var types = ['活动', '商品'];
		var status = ['未支付', ['未参加', '未发货'], ['已参加', '已发货'], '未确认', '已完成', '退款中', '已退款', '已取消'];
		var btns = {
			a_0_0 : '立即付款',
			a_0_1 : '消费码',
			a_0_2 : '',
		}
		for(var i in d){
			code += '<li>';
			code += '	<a class="top" href="javascript:jump(\'orderDetail.html?order_id='+ d[i].id +'\')">';
			code += '		<div class="left">';
			code += '			<div class="type">'+ (d[i].catname||'商品') +'</div>';
			code += '			<img src="'+ d[i].path.pathFormat() +'">';
			code += '		</div>';
			code += '		<div class="right">';
			code += '			<div class="t">'+ d[i]['title_' + d[i].type] +'</div>';
			if(d[i].type == 0){
				code += '			<div class="c">'+ d[i].start_time.timeFormat('m-d W H:i') +'-'+ d[i].end_time.timeFormat('H:i') +'</div>';
			}else{
				code += '			<div class="c">邮费：￥'+ d[i].postage + '</div>';
			}
			code += '			<div class="b">';
			code += '				<div class="l"><span>'+ d[i].count +'</span>份</div>';	
			code += '				<div class="r">合计：<span>￥'+ d[i].price +'</span></div>';
			code += '			</div>';
			code += '		</div>';
			code += '	</a>';
			code += '	<div class="bottom">';
			code += '		<div class="status">'+ (typeof(status[d[i].act_status])=='string'?status[d[i].act_status]:status[d[i].act_status][d[i].type]) +'</div>';
			if(d[i].type == 0){
				if(d[i].act_status == 0){
					code += '		<a href="payment.html?order_id='+ d[i].id +'" class="btn">立即付款</a>';
					code += '		<a href="javascript:cancelOrder(this, '+ d[i].id +')" class="btn">取消订单</a>';
				}else if(d[i].act_status == 1){
					code += '		<a href="javascript:showCode('+ d[i].id +');" class="btn">消费码</a>';
				}else if((d[i].act_status == 2 || d[i].act_status == 3) && d[i].comment_id == null){
					code += '		<a href="javascript:comment('+ d[i].id +');" class="btn">追加评论</a>';
				}else if(d[i].act_status == 5){
					code += '		<a href="javascript:cancelRefund('+ d[i].id +');" class="btn">取消退款</a>';
				}
			}else{
				if(d[i].act_status == 0){
					code += '		<a href="payment.html?order_id='+ d[i].id +'" class="btn">立即付款</a>';
					code += '		<a href="javascript:cancelOrder(this, '+ d[i].id +')" class="btn">取消订单</a>';
				}else if(d[i].act_status == 1){
					code += '		<a href="javascript:modifyOrder('+ d[i].id +');" class="btn">修改订单备注</a>';
				}else if(d[i].act_status == 2){
					code += '		<a href="javascript:logistics('+ d[i].id +');" class="btn">查看物流</a>';
				}else if(d[i].act_status == 3){
					code += '		<a href="javascript:ConfirmReceipt('+ d[i].id +');" class="btn">确认收货</a>';
				}else if(d[i].act_status == 4 && d[i].comment_id == null){
					code += '		<a href="javascript:comment('+ d[i].id +');" class="btn">追加评论</a>';
				}else if(d[i].act_status == 5){
					code += '		<a href="javascript:cancelRefund('+ d[i].id +');" class="btn">取消退款</a>';
				}
			}
			code += '	</div>';
			code += '</li>';
		}
		$('.content').html(code);
	}, false);
});