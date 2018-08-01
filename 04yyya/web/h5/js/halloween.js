/**
 * Created by fyt on 2016/10/26.
 */
var w = $(window).width() - 90;
var h = $(window).height();
var sum = 0;//红包数量

//创建一个
function addGoods(){
    var l=Math.round(Math.random()*w);
    var n=Math.round(Math.random()*2+1);
    var player=new Image();
    player.src="img/halloween/h"+n+".png";
    var goods = $('<div>').addClass('goods').css({'left':l,'top':'-100'}).appendTo('body');
    var goods_img = $('<div>').addClass('goods_img').appendTo(goods);
    $(player).appendTo(goods_img);
    var score = $('<div>').addClass('score').appendTo(goods);
    move(goods);
    $(goods)[0].addEventListener('touchstart', function(event){
        event.preventDefault();
        goodsscore(this);
    });
}
//删除一个
function delGoods(){
    if($('.goods').size() > 5){
        $('.goods').eq(0).remove();
    }
}
//游戏开始
function star(){
    var timer = setInterval(function(){
        addGoods();
        delGoods();
    },3000);
}

//移动
function move(goods){
    // var s = Math.round(Math.random()*6+1);
    $(goods).animate({top:h},5*1000);
}
//分数
function goodsscore(t){
    $(t).stop();
    var v = Math.round(Math.random()*4+1);
    sum += v;
    $('.final-grade').html(sum);
    $(t).children('.score').html('+'+v);
    $(t).children('.score').animate({top:'-20px'},1000,function(){
        $(t).hide();
    });

}

$(function(){
    addGoods();
    star();
})


