/******************↓↓ 根据屏幕分辨率，自动调整页面大小 ↓↓*********************/
$('html').attr('style', 'font-size:' + 100 * ($(window).width() / 360) +'px !important');
$(window).resize(function(){
    $('html').attr('style', 'font-size:' + 100 * ($(window).width() / 360) +'px !important');
});