function myScroll(type){
	this.speed = 5;
	this.div = "";
	this.height = null;
	this.src = [];
	this.link = [];
	this.type = type||1;
	
	var $div,p,count,$main,main_width,main_height,speed,$dots,$divHeight,o,_width;
	o = this;
	//console.log();	
	//$("\'"+this.div+"\'").css({"width":"100%","overflow":"hidden","position":"relative"});
	this.start = function(){
		count = this.src.length;
		if(this.div == '' || count == 0)return;
		$main = $(this.div).empty();
		if(this.height != null){
			$main.css({"min-height":100,"height":this.height,"width":"100%"});
		}
		$main.css({"overflow":"hidden","position":"relative"});
		
		main_width = $main.width();
		main_height = $main.height();
		speed = this.speed * 1000;

		$dots = $('<div></div>');
		$div = $("<div></div>");
		for(i in this.src){
			var lk = this.link[i] ? this.link[i] : '#';
			if(this.type == 1){
				var $a = $('<a href="javascript:jump(\''+ lk +'\')"></a>').css({
					'width':main_width,
					'height':main_height,
					'overflow':'hidden',
					'text-decoration':'none',
					'float':'left'
				});
			}else if(type == 2){
				var $a = $('<a href="javascript:jump(\''+ lk +'\')"></a>').css({
					'overflow':'hidden',
					'text-decoration':'none',
					'float':'left'
				});
			}
			$('<img src="'+ this.src[i] +'">').css({'border':'none'}).appendTo($a).load(function(){
				var w = $(this).width();
				var h = $(this).height();
				var r = w / h;
				var _w = $(this).parent().width();
				var _h = $(this).parent().height();
				var _r = _w / _h;
				if(r < _r){
					$(this).width(_w);
					$(this).css('margin-top', (_h - $(this).height()) / 2 + 'px');
				}else{
					$(this).height(_h);
					$(this).css('margin-left', (_w - $(this).width()) / 2 + 'px');
				}
			});

			
			$a.appendTo($main);
			
			if(o.type == 1){
				var $em = $('<em></em>').css({'width':'8px','height':'8px','border-radius':'50%','display':'inline-table','margin':'11px 5px','background':'#fff'}).appendTo($dots);
				if(i == 0)$em.css('background', 'green');
			}
			if(i == this.src.length - 1)run();
		}
		_width = $main.find('a').width() + $main.find('a:eq(0)').offset().left;
		
		$main.children().appendTo($div);
		$div.css({'height':'100%', 'position':'absolute', 'top':0, 'left':0}).appendTo($main);
		$div.width(($div.children().width() + $div.children().first().offset().left) * count + 5);
		//$div.children().width(main_width);
		if(o.type == 1){
			$dots.css({'position':'absolute','width':'100%','height':'30px','z-index':2,'bottom':0,'left':0,'right':0,'background':'rgba(0,0,0,0.5)','text-align':'center','overflow':'hidden'}).appendTo($main);
		}
		
		$div.touchwipe({
			'wipeLeft' : function(){
				window.clearInterval(p);
				if(Math.abs($div.position().left) < $div.width() - main_width - 10){
					$div.animate({'left' : $div.position().left + -1 * _width}, 'fast', function(){
						run();
					});
				}else run();
			},
			'wipeRight' : function(){
				window.clearInterval(p);
				if(Math.abs($div.position().left) > 10){
					$div.animate({'left' : $div.position().left + _width}, 'fast', function(){
						run();
					});
				}else run();
			}
		});
		
	}
	
	function run(){
		if(o.type == 1){
			$dots.children().css('background', '#fff');
			$dots.children().eq(Math.abs(Math.round($div.position().left / main_width))).css('background', 'green');
		}
		p = window.setInterval(function(){
			if(o.type == 1)$dots.children().css('background', '#fff');
			if(Math.abs($div.position().left) < $div.width() - main_width - 10)
				$div.animate({'left' : $div.position().left + -1 * _width}, 'fast', function(){
					if(o.type == 1)$dots.children().eq(Math.abs(Math.round($div.position().left / main_width))).css('background', 'green');
				});
			else
				$div.animate({'left' : 0}, 'fast',function(){
					if(o.type == 1)$dots.children().eq(Math.abs(Math.round($div.position().left / main_width))).css('background', 'green');
				});
		}, speed);
	}

};
/*********************
$(function(){
	var sol = new myScroll();
	sol.speed = 3;
	sol.div = ".main";
	sol.src.push('images/1.jpg');
	sol.src.push('images/2.jpg');
	sol.src.push('images/3.jpg');
	sol.src.push('images/4.jpg');
	sol.src.push('images/5.jpg');
	sol.start();
});

/*********************/
/*$(function(){
	var top = $('.title').offset().top;
	$(window).scroll(function(){
		if($(this).scrollTop() >= top){
			if($('.title_clone').size() == 0){
				var title = $('.title').clone().appendTo('body').addClass('title_clone');
				title.css({'position':'fixed', 'top':0, 'width':'100%'});
			}
		}else{
			$('.title_clone').remove();
		}
	});
});*/
