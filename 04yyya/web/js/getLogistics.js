var getLogisticsObject = {
	onload : function(){
		if(!member || !win.get.order_id){
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
			return;
		}

		getLogisticsObject.order_id = win.get.order_id;

		ajax('member/order/getLogistics', {order_id:getLogisticsObject.order_id}, function(d){
			if(d.info){
				$.alert(d.info, 'error');
			}else{
				$('.page_getLogistics .headBox img').attr('src', d.path);
				$('.page_getLogistics .headBox .name span').text(d.name);
				$('.page_getLogistics .headBox .state span').text(d.state);
				$('.page_getLogistics .headBox .number span').text(d.number);

				var code = '';
				for(var i in d.traces){
					code += '<div class="item">';
					code += '	<div class="context">'+ d.traces[i].AcceptStation +'</div>';
					code += '	<div class="datetime">'+ d.traces[i].AcceptTime +'</div>';
					code += '</div>';
				}
				if(code == ''){
					code = '<p>查询不到物流跟踪信息!</p>';
				}
				$('.page_getLogistics .infoBox').html(code);
			}
		}, 2);
	}
};