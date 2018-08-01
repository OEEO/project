var changePhoneNumObject = {
	s : 0,
	sendcode : function(){
		if(changePhoneNumObject.s > 0){
			$.alert('请稍后再发送', 'error');
			return;
		}
		var tel = $('.page_changePhoneNum .telephone').val();
		if(!/^1\d{10}$/.test(tel)){
			$.alert('手机号格式不正确', 'error');
			return;
		}
		win.ajax('Member/Index/sendSMS', {telephone : tel}, function(d){
			if(d.status == 1){
				changePhoneNumObject.s = 60;
				(function timejump(){
					changePhoneNumObject.s --;
					$('.page_changePhoneNum .sendcode').css('color','#ddd').html(''+ changePhoneNumObject.s +'s');
					if(changePhoneNumObject.s <= 0){
						$('.page_changePhoneNum .sendcode').css('color','#333333').html('获取验证码');
					}else{
						window.setTimeout(function(){
							timejump(changePhoneNumObject.s);
						}, 1000);
					}
				})();
			}else{
				$.alert(d.info, 'error');
			}
		});
	},
    bind : function(){
        var tel = $('.page_changePhoneNum .telephone').val();
        var verifycode = $('.page_changePhoneNum .verifycode').val();
        if(!/^1\d{10}$/.test(tel)){
			$.alert('手机号格式不正确', 'error');
			return;
		}
        if(!/^\d{4}$/.test(verifycode)){
			$.alert('验证码格式不正确', 'error');
			return;
		}
        win.ajax('Member/Index/resetPhone',{telephone : tel , verifycode : verifycode},function(d){
            if(d.status==1){
                $.alert(d.info);
            }else{
                $.alert(d.info, 'error');
            }
        });

    }
};
