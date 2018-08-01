var setupObject = {
	//邀请好友扫一扫二维码
	showCode : function(){
		$('.page_setup .er_wei_ma').show().click(function(){
			$(this).hide();	
		});
	},
	onload : function(){
		if(member.password==0){
			$('.page_setup .modify').text('设置');
		}
		// $('.page_setup .logout').tap(function () {
		// 	$.alert('退出');
		// })
	}
};
