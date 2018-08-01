(function($){
	$.fn.tpl = function(data, options){
		var $this = $(this);
		var code = $this.html();
		(function loop(code){
			var re = /\{loop( data=["']\w+?["'])( key=["']\w+?["'])( val=["']\w+?["'])?\}(.+)\{\/loop\}/ig;
			var loopcode = code.match(re);
			console.log(code);
			console.log(loopcode);
		})(code);
	}
})(jQuery);