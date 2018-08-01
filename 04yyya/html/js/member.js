var ajax = parent.win.ajax;
$(function(){
	if(parent.member == null){
		var code = '<div class="loginBox">';
		code += '<div class="btn_box">';
		code += '<button class="wx">微信登入</button>';
		code += '<button class="dx">短信登入</button>';
		code += '<button class="pw">密码登入</button>';
		code += '</div>';
		code += '<form class="dx_box" name="dx_box">';
		code += '<label><span class="tel_icon"></span><input type="text" name="telephone" class="telephone" placeholder="手机号"></label>';
		code += '<label><span class="ver_icon"></span><input type="text" name="verifycode" class="verifycode" placeholder="验证码">';
		code += '<a href="javascript:void(0);" class="sendcode">获取验证码</a></label>';
		code += '<input type="submit" class="submit" value="登&nbsp;&nbsp;入">';
		code += '<div class="bottom"><a class="wx" href="javascript:void(0);">微信登入</a><a class="pw" href="javascript:void(0);">账号登录</a></div>';
		code += '</form>';
		code += '<form class="pw_box" name="pw_box">';
		code += '<label><span class="tel_icon"></span><input type="text" name="telephone" class="telephone" placeholder="手机号/邮箱"></label>';
		code += '<label><span class="pwd_icon"></span><input type="password" name="password" class="password" placeholder="登录密码"></label>';
		code += '<input type="submit" class="submit" value="登&nbsp;&nbsp;录">';
		code += '<div class="bottom"><a class="wx" href="javascript:void(0);">微信登入</a><a class="dx" href="javascript:void(0);">忘记密码？验证短信登入</a></div>';
		code += '</form>';
		code += '</div>';
		$('body').empty().html(code);
		$(window).resize(function() {
			$('.loginBox').height(parent.win.height);
		});
		$('.loginBox').height(parent.win.height);
		$('.pw').click(function(){
			$('.loginBox').children(':visible').animate({left:-1 * $(this).width(), opacity:0}, 'fast', function(){
				$(this).css({'left':$(this).width(), opacity:1}).hide();
			});
			$('.pw_box').show();
			$('.pw_box').animate({left:0}, 'fast');
		});
		
		$('.dx').click(function(){
			$('.loginBox').children(':visible').animate({left:-1 * $(this).width(), opacity:0}, 'fast', function(){
				$(this).css({'left':$(this).width(), opacity:1}).hide();
			});
			$('.dx_box').show();
			$('.dx_box').animate({left:0}, 'fast');
		});
		
		//微信登录
		$('.wx').click(function(){
			ajax('Home/Wx/getOauthUrl', function(d){
				if(typeof(d) == 'string'){
					parent.location.href = d;
				}
			});
		});
		
		//密码登录
		$(document.pw_box).submit(function(){
			if(btnSubmit.isLoading())return false;
			var data = {};
			data.username = this.telephone.value;
			data.password = this.password.value;
			btnSubmit.loading($(this).find('.submit'));
			ajax("Member/Index/login", data, function(d){
				btnSubmit.close();
				if(d.status == 1){
					parent.member = d.info.info;
					parent.win.saveSkey(d.info.info.id, d.info.skey);
					alert('登录成功！');
					window.location.reload();
				}else{
					alert(d.info);
				}
			});
			return false;
		});
		
		//发送登录短信
		$('.sendcode').click(function(){
			var tel = document.dx_box.telephone.value;
			if(!/^1\d{10}$/.test(tel))alert('手机号格式不正确！');
			ajax('Member/Index/sendSMS', {telephone:tel}, function(d){
				if(d.status == 1){
					(function timejump(s){
						s --;
						$('.sendcode').css('color','#eee').html('短信已发送('+ s +')');
						if(s <= 0){
							$('.sendcode').css('color','#fff').html('获取验证码');
						}else{
							window.setTimeout(function(){
								timejump(s);
							}, 1000);
						}
					})(60);
				}else{
					alert(d.info);
				}
			});
		});
		
		//账户登录
		$(document.dx_box).submit(function(){
			if(btnSubmit.isLoading())return false;
			var data = {};
			data.telephone = this.telephone.value;
			data.smsverify = this.verifycode.value;
			btnSubmit.loading($(this).find('.submit'));
			ajax("Member/Index/register", data, function(d){
				btnSubmit.close();
				if(d.status == 1){
					parent.member = d.info.info;
					parent.win.saveSkey(d.info.info.id, d.info.skey);
					alert('登录成功！');
					window.location.reload();
				}else{
					alert(d.info);
				}
			});
			return false;
		});
	}
});

//页面加载完毕后自动执行
$$(function(){
	//检查是否有新消息
	if($('.header .msg').size() > 0){
		ajax('Member/Message/UnreadCount', function(d){
			if(d.count && d.count > 0){
				$('.header .msg').addClass('new');
			}else{
				$('.header .new').removeClass('new');
			}
		});
	}
});

function logout(){
	ajax('Member/Index/logout', function(d){
		if(d.status == 1){
			window.localStorage.clear();
			parent.member = null;
			window.location.reload();
		}
	});
}





