var withdrawRecordObject = {
	onload: function () {
	
		ajax('Member/Profit/getWithdrawList', {}, function(d) {
//			console.log(d);
			var code = '';

			if(d.length > 0){
				for(var i in d){
					if (d[i].is_balance == '0') {
						var is_balance = '申请中';
					} else if (d[i].is_balance == '1') {
						var is_balance = '已成功';
					} else if (d[i].is_balance == '2' || d[i].is_balance == '3') {
						var is_balance = '提现失败'
					}
					code += '<div style="margin: 1rem 0;border-bottom: 1px solid #eee;">';
					code += '	<p style="font-size: 1.45rem;margin: 0 0 1rem 0;color:black;">申请提现'+d[i].price+'元 ('+ is_balance+')<span style="text-align: right;float:right;font-size: 1.2rem;color:#A0A0A0;">'+ d[i].start_time.timeFormat('Y-m-d H:i:s') +'</span></p>';
					if (d[i].is_balance == '2' || d[i].is_balance == '3') {
						code += '	<p style="color:red;margin: 0.2rem 0 0.8rem 0;">'+ d[i].reason +'</p>';
					} else {
						code += '	<p style="color:#A0A0A0;margin: 0.2rem 0 0.8rem 0;">扣除手续费0.6%</p>';
					}
					code += '</div>';
				}
			}else{
				if(page==1)
//				var code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
				var code = '<div class="no_msgs">暂无数据</div>';
				else
				var code = '<div class="no_more">暂无数据</div>';
			}
			if(page == 1)
				$('.page_withdrawRecord .withdraw_mark').html(code);
			else
				$('.page_withdrawRecord .withdraw_mark').append(code);
		});
	},

};