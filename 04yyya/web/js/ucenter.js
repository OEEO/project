var ucenterObject = {
	notice: function () {
		var ncode = '';
		ajax('Member/Index/getData', {}, function (d) {
			if (d.status == 0) {
				$.alert(d.info, 'error');
				return;
			}
			if (d.raise_order.order_status == 2 && d.raise_order.order_id != null && d.raise_order.order_id != '') {
				ncode += '<p class="noticetext">请您于' + d.raise_order.limit_pay_time.timeFormat('Y-m-d H:i') + '前支付《' + d.raise_order.raise_title + '》众筹项目的尾款' + d.raise_order.raise_times_retainage + '，过期视为自动放弃，名额将释放给其他候选人，谢谢您的支持！</p></div>';
				$.dialog(ncode, function () {
					jump('orderRaiseDetail', { order_id: d.raise_order.order_id });
				}, 'true', 'noticeBox');
				$('#dialogBox.noticeBox .btns .closeBtn').text('');
				$('#dialogBox.noticeBox .btns .agree').text('立即支付');
				$('#dialogBox .context').before('<div class="noticepic"></div>');
			}
		});
	},
	onload : function(){
		if(!member){
			win.login();
			return;
		}
		if(member.path){
			$('.page_ucenter .imgPortrait').html('<a href="javascript:jump(\'changeUserinfo\');"><img width="100%" src="'+ member.path +'" /></a>');
		}else{
			$('.page_ucenter .imgPortrait').html('<a href="javascript:jump(\'changeUserinfo\');"><img width="100%" src="http://img.yummy194.cn/20160608/faa0807f970657b79b7b1bfcade9a5349f66e210.jpg" /></a>');
		}
		ajax('Member/Index/getData', {}, function(d){
			var code = '';
			code += '<span class="menu_li" onclick="jump(\'follow\');"><font class="faness">关注</font><font>'+ d.follow +'</font></span>';
			code += '<span class="menu_li"><font class="faness">粉丝</font><font>'+ d.fans*3 +'</font></span>';
			//code += '<span class="menu_li" onclick="jump(\'fans\');"><font class="faness">粉丝</font><font>'+ d.fans +'</font></span>';
			//code += '<span class="menu_li" onclick="jump(\'follow\');"><font>'+ d.follow +'</font><br/>关注</span>';
			//code += '<span class="menu_li" onclick="jump(\'fans\');"><font>'+ d.fans +'</font><br/>粉丝</span>';
			//code += '<span class="menu_li" onclick="alert(\'敬请期待！\');"><font>'+ d.bang +'</font><br/>食报</span>';
			//code += '<span class="menu_li"><font>'+ d.mibi +'</font><br/>送米</span>';
			$('.page_ucenter .my_menus').html(code);
			/*
			$('.page_ucenter .li_item .number font').text(d.doing);
			$('.page_ucenter .li_item .number2 font').text(d.going);
			*/
			if (d.unpaid_tips == 0) $('.page_ucenter .li_item .number3').css('display', 'none');
			if (d.unpaid_goods == 0) $('.page_ucenter .li_item .number4').css('display', 'none');
			if (d.unpaid_raises == 0) $('.page_ucenter .li_item .number5').css('display', 'none');

			$('.page_ucenter .li_item .number3 font').text(d.unpaid_tips);
			$('.page_ucenter .li_item .number4 font').text(d.unpaid_goods);
			$('.page_ucenter .li_item .number5 font').text(d.unpaid_raises);

		},2);
		if(member.tags && member.tags.indexOf('18') > -1) {
			var code = '<div class="li_item"><a href="javascript:jump(\'Tobeachef\');" class="list9">进入达人服务<i class="arrowRight"></i></a></div>';
			code += '<div class="add_border"></div> <div class="li_item"><a href="javascript:jump(\'daRen\',{member_id:' + member.id + '});" class="list10">我的空间<i class="arrowRight"></i></a></div>';
			$('.page_ucenter .daren_orno').html(code);
			//}else if(member.dr_status == 0){
			//	var code = '<div class="li_item"><a href="javascript:void(0);" class="list5">主厨达人申请中...</a></div>';
			//	$('.page_ucenter .daren_orno').html(code);
		}else{
			var code = '<div class="li_item"><a href="javascript:jump(\'applyToBedaRen\');" class="list5">成为主厨<i class="arrowRight"></i></a></div>';
			$('.page_ucenter .daren_orno').html(code);
		}
	},
	onshow: function () {
		if (member) {
			//弹出刷新推送
			if(window.location.href == 'http://'+ DOMAIN +'/?page=choice-ucenter') {
				ucenterObject.notice();
			}
		}
	}
};
