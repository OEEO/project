
function evaluationStar(num){
	$('.stars').children('img').attr('src','./images/star_empty.png');
	if(num==4){
		$('.stars').children('img').attr('src','./images/star_full.png');
	}else{
		$('.stars').children('img').eq(num+1).prevAll().attr('src','./images/star_full.png');//.css('background','red');
	}
	$('.stars').children('span').children('i').html(num+1);
}