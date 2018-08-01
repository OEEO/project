var orderRefundObject = {
	order_id : null,
	onload : function(){
		orderRefundObject.order_id = win.get.order_id;
		$(".page_orderRefund .price").html(win.get.price +'元');
		$('.page_orderRefund .orderOperation').click(function(){
			var context = $('.page_orderRefund textarea').val();
			if(context.length > 0 && context.length < 150){
				ajax('Member/Order/refund', {order_id : orderRefundObject.order_id, context : context}, function(d){
					if(d.status == 1){
						$.alert('申请提交成功，请耐心等待达人处理', function(){
							page.back(function(){
								page.reload();
							});
						});
					}else{
						$.alert(d.info, 'error');
					}
				});
			}else{
				$.alert('退款原因必填，且不能超过150字', 'error');
			}
		});
	}
};