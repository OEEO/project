var changePassword = {
	old : null,
    new : null,
    new2 : null,
    hasPassword : null,
    reset : function(){
        win.ajax('Member/Index/resetPassword',{oldpassword : changePassword.old , newpassword : changePassword.new , hasPassword : changePassword.hasPassword},function(d){
            if(d.status==1){
                $.alert(d.info, function(){
                    page.back(function(){
                        page.reload();
                    });
                });
            }else{
                $.alert(d.info, 'error');
            }
        })
    },
    onload : function(){
        if(member.password==0){
            $('<input type="hidden" name="hasPassword" value="0"/>').appendTo('.page_changePassword .headerBlank');
            $('.oldpassword').parent().hide();
        }else{
            $('<input type="hidden" name="hasPassword" value="1"/>').appendTo('.page_changePassword .headerBlank');
        }
        changePassword.hasPassword = $('[name="hasPassword"]').val();
        //alert(changePassword.hasPassword);
    }
};

    function submit(){
        var oldpassword = $('.oldpassword').val();
        var newpassword = $('.password').val();
        var newpassword2 = $('.password2').val();

        changePassword.old = oldpassword;
        changePassword.new = newpassword;
        changePassword.new2 = newpassword2;

        if(newpassword != newpassword2){
            $.alert('两次密码不一致', 'error');
            return false;
        }else{
            changePassword.reset();
        }
    }

