var feedBackObject ={
	
	sizeNum:function (em){
		var sizeLength = $(em).text().length;
		if(sizeLength>255){
			var content =  $(em).text().substr(0,255);
			$(em).empty().append(content);
			sizeLength = $(em).text().length;
		}
		$('.page_feedBack .lastNum font').empty().append(255-sizeLength);
	},
    submit:function (){
        var content = $('.page_feedBack .feedBack').text();
        ajax('Member/Index/feedback', {content:content}, function(d){
            if(d.status == 1){
                $.alert(d.info, function(){
                    $('.page_feedBack .feedBack').empty();
                });
            }else{
                $.alert(d.info, 'error');
            }
        });
    },
	//邀请好友扫一扫二维码
	showCode : function(){
		$('.page_feedBack .er_wei_ma').show().click(function(){
			$(this).hide();	
		});
	}
	
};



