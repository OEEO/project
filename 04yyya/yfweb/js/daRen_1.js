// JavaScript Document
$(function(){
        var screenWidth = $(window).width();/*获取设备宽度*/
        var userPicWidth = screenWidth/5+'px';/*用户头像宽高*/
        //var activitiesListLiHeight = screenWidth*0.45+'px';
        // $('.activitiesList>li').css("height",activitiesListLiHeight);/*设置每一个活动所占的高度 li */
        var activitiesListMenuLis = $('.activitiesListMenu>ul').find('li');

        activitiesListMenuLis.eq(0).css({"color":"#ffcb00","border-bottom":"#ffcb00 5px solid"});
        activitiesListMenuLis.click(function(){
            activitiesListMenuLis.eq($(this).index()).css({"color":"#ffcb00","border-bottom":"#ffcb00 5px solid"});
            activitiesListMenuLis.eq(!$(this).index()).css({"color":"#636363","border-bottom":"#fff 5px solid"});
        });
	});