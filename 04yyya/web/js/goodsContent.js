var goodsContentObject = {
	onload : function(){
		if(!win.get.goods_id){
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
		}
		goodsContentObject.goods_id = win.get.goods_id;
		ajax('Goods/Goods/getContent', {goods_id : goodsContentObject.goods_id}, function(d){
			if(d.info){
				$.alert(d.info, 'error');
			}else{
				$('.page_goodsContent .content').html(d.content);
			}
		}, 2);
	}
};