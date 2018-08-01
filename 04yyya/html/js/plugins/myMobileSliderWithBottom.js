/**
 * $.yxMobileSlider
 * @charset utf-8
 * @extends jquery.1.9.1
 * @fileOverview 创建一个焦点轮播插件，兼容PC端和移动端，若引用请保留出处，谢谢！
 * @author 李玉玺
 * @version 1.0
 * @date 2013-11-12
 * @example
 * $(".container").yxMobileSlider();
 */
(function($){
    $.fn.myMobileSliderWithBottom = function(settings){
        var defaultSettings = {
            width:$(window).width(), //容器宽度 为屏幕宽度
            height:$(window).width()*0.65, //容器高度 为屏幕宽度的 0.65
            during: 4000, //间隔时间
            speed:30, //滑动速度
			inf:[],//设置菜单的文字
            color:"#dad9d5"//设置  菜单的文字 的背景颜色
        }
        settings = $.extend(true, {}, defaultSettings, settings);
        return this.each(function(){
            var _this = $(this), s = settings;
            var startX = 0, startY = 0; //触摸开始时手势横纵坐标 
            var temPos; //滚动元素当前位置
            var iCurr = 0; //当前滚动屏幕数
            var timer = null; //计时器
            var oMover = $("ul", _this); //滚动元素
            var oLi = $("li", oMover); //滚动单元
            var num = oLi.length; //滚动屏幕数
            var oPosition = {}; //触点位置
            var moveWidth = s.width; //滚动宽度
    
      /*******************************************************/      
            //初始化主体样式
            _this.width(s.width).height(s.height).css({
                position: 'relative',
                overflow: 'hidden',
				margin:'0 auto'
            }); //设定容器宽高及样式
            oMover.css({
                position: 'absolute',
                left: 0
            });
            oLi.css({
                float: 'left',
                display: 'inline'
            });
            $("img", oLi).css({
                width: '100%',
                height: '100%'
            });

 /*****************************************************/      
            var addinf = s.inf;
			var bottomstation = 0;
         if(addinf.length>0) {bottomSettings();}//设置 滚动图片的 菜单
         else {defaultBottom();bottomstation = 1;}

          function bottomSettings() {
                var ImgAli = _this.children().find("li");

            for(i in addinf){
                var bb ="<div class='imgBottomInf'><span>"+addinf[i]['name']+"</span><p>"+addinf[i]["info"]+"</p></div>";
                 $(bb).appendTo(ImgAli.eq(i).find('a'));
                }   

            var imgBottomStyleName = "."+_this.attr('class')+" .imgBottomInf";
            $(imgBottomStyleName).css({
                "background-color":s.color,
                filter: 'Alpha(Opacity=80)',
                "-moz-opacity":0.8,/* Moz + FF 透明度*/ 
                "opacity": '0.8', /* 支持CSS3的浏览器（FF 1.5也支持）透明度*/

                position:'absolute',
                bottom:40,
                width:s.width,
                height:'60px',
                "text-align":'center',
                "z-index":'2'
            });  
            
           $(imgBottomStyleName).children('span').css({
                height:'30px',
                color:'#48342b',
                "font-size":'20px',
                "line-height":'30px'
            });  
                                   
            $(imgBottomStyleName).children('p').css({
				margin:0,
                height:'25px',
                color:'#523e35',
                "font-size":'16px',
                "line-height":'25px'
            });  

          }
           
           //设置 默认滚动底部
           function defaultBottom(){
              //初始化焦点容器及按钮
            _this.append('<div class="focus"><div></div></div>');
            var thisFocus = "."+_this.attr('class')+" .focus";
            //var oFocusContainer = $("."+_this.attr('class')+".focus");
            var oFocusContainer = $(thisFocus);
        
            for (var i = 0; i < num; i++) {
                $("div", oFocusContainer).append("<span></span>");
            }
            var oFocus = $("span", oFocusContainer);
            oFocusContainer.css({
                minHeight: $(this).find('span').height() * 2,
                position: 'absolute',
                bottom: 0,
                background: 'rgba(0,0,0,0.5)'
            })
            $("span", oFocusContainer).css({
                width:'10px',
                height:'10px',
                display: 'block',
                float: 'left',
                cursor: 'pointer',
                "margin-right":'10px',
                "border-radius":'50%',
                "font-size":'0'
            })
            
            $("div", oFocusContainer).width(oFocus.outerWidth(true) * num).css({
                position: 'absolute',
                right: 10,
                //top: '40%',
                top: 10
                //marginTop: -$(this).find('span').height() / 2
            });
            
            oFocus.first().addClass("current");

            //oFocusContainer.width(_this.width()).height(_this.height() * 0.15).css({
            oFocusContainer.width(_this.width()).height(30).css({
                    zIndex: 2
                });//设定焦点容器宽高样式

           } 
            //页面加载或发生改变
            $(window).bind('resize load', function(){

                    bindTochuEvent();
                
                oLi.width(_this.width()).height(_this.height());//设定滚动单元宽高
                oMover.width(num * oLi.width());
                _this.fadeIn(300);//采用 淡入效果
            });
            //页面加载完毕BANNER自动滚动
            autoMove();
				
            //自动运动
            function autoMove(){
                timer = setInterval(doMove, s.during);
            }
            //停止自动运动
            function stopMove(){
                clearInterval(timer);
            }
            //运动效果
            function doMove(){
                iCurr = iCurr >= num - 1 ? 0 : iCurr + 1;
                doAnimate(-moveWidth * iCurr);
                 if(bottomstation)$("."+_this.attr('class')+" .focus"+" span").eq(iCurr).addClass("current").siblings().removeClass("current");
                   // oFocus.eq(iCurr).addClass("current").siblings().removeClass("current");
            }
            //绑定触摸事件
            function bindTochuEvent(){
                oMover.get(0).addEventListener('touchstart', touchStartFunc, false);
                oMover.get(0).addEventListener('touchmove', touchMoveFunc, false);
                oMover.get(0).addEventListener('touchend', touchEndFunc, false);
            }
            //获取触点位置
            function touchPos(e){
                var touches = e.changedTouches, l = touches.length, touch, tagX, tagY;
                for (var i = 0; i < l; i++) {
                    touch = touches[i];
                    tagX = touch.clientX;
                    tagY = touch.clientY;
                }
                oPosition.x = tagX;
                oPosition.y = tagY;
                return oPosition;
            }
            //触摸开始
            function touchStartFunc(e){
                clearInterval(timer);
                touchPos(e);
                startX = oPosition.x;
                startY = oPosition.y;
                temPos = oMover.position().left;
            }
            //触摸移动 
            function touchMoveFunc(e){
                touchPos(e);
                var moveX = oPosition.x - startX;
                //var moveY = oPosition.y - startY;
             //   if (Math.abs(moveY) < Math.abs(moveX)) {
                if (Math.abs(moveX)>0) {
                    e.preventDefault();
                    oMover.css({
                        left: temPos + moveX
                    });
                }
            }
            //触摸结束
            function touchEndFunc(e){
                touchPos(e);
                var moveX = oPosition.x - startX;
                //console.log("本次触摸X轴移动距离为：\t"+moveX);
               // var moveY = oPosition.y - startY;
            //    if (Math.abs(moveY) < Math.abs(moveX)) {
             if (Math.abs(moveX)>0) {
                    if (moveX > 0) {
                        iCurr--;
                        if (iCurr >= 0) {
                            var moveX = iCurr * moveWidth;
                            doAnimate(-moveX, autoMove);
                        }
                        else {
                            doAnimate(0, autoMove);
                            iCurr = 0;
                        }
                    }
                    else {
                        iCurr++;
                        if (iCurr < num && iCurr >= 0) {
                            var moveX = iCurr * moveWidth;
                            doAnimate(-moveX, autoMove);
                        }
                        else {
                            iCurr = num - 1;
                            doAnimate(-(num - 1) * moveWidth, autoMove);
                        }
                    }	
                     if(bottomstation)$("."+_this.attr('class')+" .focus"+" span").eq(iCurr).addClass("current").siblings().removeClass("current");		
                }
            }
            //动画效果
            var move_status = true;/*如果不添加这个参数，函数会被调用两次而导致出错*/
            function doAnimate(iTarget, fn){
                if(!move_status)return;
                move_status = false;
                oMover.stop().animate({
                    left: iTarget
                }, _this.speed , function(){
                    move_status = true;
                    if (fn) 
                        fn();
                });
            }


        });
    }
})(jQuery);
