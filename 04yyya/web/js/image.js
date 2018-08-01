var imageObject = {
	onload : function(){
		if(!win.get.path){
			$.alert('非法访问!', function(){
				page.back();
			}, 'error');
		}
		$('<img>').attr('src', win.get.path).appendTo('.page_image .content');
		$.alert('生成成功!<br><span style="color:#999;padding-top: 0.96rem;">请长按图片下载到本地..</span>', function(){}, 'success', 9);
	}
};