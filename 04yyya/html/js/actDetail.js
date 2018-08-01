$(function(){
	var starInfoMenuLis = $('.starInfoMenu>ul').find('li');
	starInfoMenuLis.eq(0).css({"border-bottom":"0.2rem solid #f9cc1e","color":"#f9cc1e"});
	starInfoMenuLis.click(function(){
		 starInfoMenuLis.css({"border-bottom":"none","color":"#969696"});
		 starInfoMenuLis.eq($(this).index()).css({"border-bottom":"0.2rem solid #f9cc1e","color":"#f9cc1e"});
	});
	$('.contactStar').click(function(){
		$('.contactStar>a').css("color","#f9cc1e");
	});
});