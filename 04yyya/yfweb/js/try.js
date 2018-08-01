//$('<script>').attr('src', 'js/member.js?q=' + Math.random()).appendTo('html');
var tryObject = {
	onload : function(){
		if(member){
			$('.page_try #nickname .activity_name').html(member.nickname);
			if(member.path){
				$('.page_try .imgPortrait img').attr('src', member.path.pathFormat());
			}else{
				$('.page_try .imgPortrait img').attr('src', 'images/portrait.jpg');
			}
			if(member.dr_status && member.dr_status!=0)$('.page_ucenter .list4').hide();
		}

		$('#a_img').bubble();


		//页面加载完毕后自动执行
		$$(function(){
			//检查是否有新消息
			if($('.page_ucenter .header .msg').size() > 0){
				ajax('Member/Message/UnreadCount', function(d){
					if(d.count && d.count > 0){
						$('.page_ucenter .header .msg').addClass('new');
					}else{
						$('.page_ucenter .header .new').removeClass('new');
					}
				});
			}
		});
	}
}
// (function(){
//
// })();