var resumeObject ={
	
	sizeNum : function (em){
		var sizeLength = $(em).val().length;
		if(sizeLength > 400){
			$(em).val($(em).val().substring(0,400));
			$('.page_resume .lastNum font').html('0');
		}else{
			$('.page_resume .lastNum font').empty().append(400-sizeLength);
		}
	},
    submit : function (){
        var signature = $('.page_resume .feedBack').val();
		if(signature=='') {$.alert('请输入你的简介', 'error');return false;}
		ajax('Member/Index/modifyInfo', {signature:signature}, function(d){
			if(d.status == 1){
				member = d.info;
				$.alert('提交成功', function(){
					page.back();
				});
			}
		}, 2);
    },
	onload : function(){
		if(member){
			if(member.dr_introduce!='')
				if(member.dr_introduce.length > 400){
					$('.page_resume .feedBack').val(member.dr_introduce.substring(0,400));
					$('.page_resume .lastNum font').html('0');
				}else{
					$('.page_resume .feedBack').val(member.dr_introduce);
					$('.page_resume .lastNum font').empty().append(400-member.dr_introduce.length);
				}
			console.log(member.dr_introduce.length);
		}else{
			$.alert('请登录你的账号', function(){
				page.back();
			}, 'error');
		}
	}
};

