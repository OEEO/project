var calendarObject = {
	cur_day : null,
	page : 1,
	lock : false,
	load_lock : false,
	loadTips : function(day, page, fn){
		var d = new Date();
		var day = day||d.getDate();
		if(day == calendarObject.cur_day && $('.page_calendar .tipslist .the_end').size() > 0)return;
		var page = calendarObject.page||1;
		var date = d.getFullYear() + '-' + (d.getMonth()+1) + '-' + day;
		$('.page_calendar .tipslist').append('<div class="loading">正在加载更多的内容…</div>');
		ajax('Home/Index/tips', {get:{page:page}, post:{date:date}}, function(thisData){
			$('.page_calendar .tipslist .loading').remove();
			if(calendarObject.cur_day != day)$('.page_calendar .tipslist').empty();
			if(thisData.length == 0){
				$('.page_calendar .tipslist').append('<div class="the_end">The End!!!</div>');
				$('.page_calendar .tipslist').fadeIn('fast');
				calendarObject.cur_day = day;
				if(typeof(fn) == 'function')fn();
				return;
			}
			var code = '';
			for(var i in thisData){
				var start_date = thisData[i].start_time.timeFormat('Y-m-d');
				var end_date = thisData[i].end_time.timeFormat('Y-m-d');
				if(start_date == end_date){
					start_h = thisData[i].start_time.timeFormat('H');
					start = (start_h > 12 ? "PM " + ((start_h-1) % 12 + 1) : "AM " + start_h) + thisData[i].start_time.timeFormat(':m');
					end_h = thisData[i].end_time.timeFormat('H');
					end = (end_h > 12 ? "PM " + ((end_h-1) % 12 + 1) : "AM " + end_h) + thisData[i].end_time.timeFormat(':m');
				}else{
					start = thisData[i].start_time.timeFormat('Y-m-d H:i');
					end = thisData[i].end_time.timeFormat('Y-m-d H:i');
				}
				var tags = thisData[i].tags;
				var taghtml = '';
				for(var j in tags){
					taghtml += '<a href="#">'+ tags[j] +'</a>';
				}
				code += '<div class="tips">';
				code += '<div class="top">'+ start +' - '+ end +'</div>';
				code += '<div class="center">';
				code += '<div class="pic"><img src="'+ thisData[i].path.pathFormat() +'"></div>';
				code += '<div class="right">';
				code += '<div class="t">'+ thisData[i].title +'</div>';
				code += '<div class="c">￥'+ thisData[i].price +'</div>';
				code += '<div class="b">'+ thisData[i].address +'</div>';
				code += '</div>';
				code += '</div>';
				code += '<div class="bottom"><a href="#">'+ thisData[i].catename +'</a>'+ taghtml +'</div>';
				code += '</div>';
			}
			$('.page_calendar .tipslist').append(code);
			calendarObject.cur_day = day;
			if(typeof(fn) == 'function')fn();
		}, false);
	},
	onload : function(){
		var date = new Date();
		var code = $('.page_calendar .calendar').html();
		var year = date.getFullYear();
		var month = date.getMonth();
		var day = date.getDate();
		var html = [];

		$('.page_calendar .title span').text(win.city.name + (month + 1));

		// 获取本月天数
		days = new Date(year, month + 1, 0).getDate();
		// 获取本月第一天是星期几
		weekStart = new Date(year, month, 1).getDay();
		for(i = 1; i <= 35; i ++){
			var d = i - weekStart;
			if(d <= 0 || d > days){
				html.push('<td></td>');
			}else{
				if(d == day)
					html.push('<td class="now clicked">'+ d +'</td>');
				else if(d < day)
					html.push('<td class="passed">'+ d +'</td>');
				else
					html.push('<td>'+ d +'</td>');
				if(i % 7 == 0 && i < 35)html.push('</tr><tr>');
			}
		}

		$('.page_calendar .calendar').html(code + "<tr>" + html.join('') + "</tr>");
		$('.page_calendar .calendar tbody td').not('.calendar tbody td:empty, .calendar tbody td.passed').click(function(){
			$('.page_calendar td.clicked').removeClass('clicked');
			var day = $(this).text();
			$("td").each(function(){
				if($(this).text() == day)$(this).addClass('clicked');
			});
			calendarObject.lock = true;
			calendarObject.page = 1;
			$('.page_calendar.wrapper').animate({scrollTop: 0}, 'fast', function(){
				if(calendarObject.load_lock)return;
				calendarObject.load_lock = true;
				$('.page_calendar .tipslist').hide();
				calendarObject.loadTips(day, 1, function(){
					$('.page_calendar .tipslist').fadeIn('fast');
					calendarObject.lock = false;
					window.setTimeout(function(){
						calendarObject.load_lock = false;
					}, 500);
				});
			});
		});

		$('.page_calendar.wrapper').scroll(function(){
			if($(this).scrollTop() > $('.page_calendar td.clicked').offset().top - $('thead').height()){
				if(!calendarObject.table){
					calendarObject.table = $("<table>").addClass('calendar');
					$('.page_calendar thead').clone().appendTo(calendarObject.table);
					$('.page_calendar td.clicked').parent().clone(true).appendTo($("<tbody>").appendTo(calendarObject.table));
					calendarObject.table.css({'position':'fixed', 'top':'0', 'left':'0', 'right':'0', 'margin-top':'0'}).appendTo('body');
				}
			}else{
				if(calendarObject.table){
					calendarObject.table.remove();
					delete calendarObject.table;
				}
			}
			if($(this).scrollTop() >= this.scrollHeight - $(this).height() - 10 && !calendarObject.lock){
				var p = Math.ceil($('.page_calendar .tipslist').children().size() / 5) + 1;
				if(p == calendarObject.page + 1){
					calendarObject.loadTips(calendarObject.cur_day, p, function(){window.setTimeout(function(){calendarObject.page = Math.ceil($('.page_calendar .tipslist').children().size() / 5);}, 500)});
				}
			}
		});
		calendarObject.loadTips();
	}
};


