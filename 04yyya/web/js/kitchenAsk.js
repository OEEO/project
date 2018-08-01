var kitchenAskObject = {
	id : null,
	onload : function(){
		if(!member){
			$.alert('尚未登录,无法询价', function(){
				win.login();
			}, 'error');
			return;
		}
		if(!win.get.space_id){
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
			return;
		}
		win.get.id = win.get.space_id;
		kitchenAskObject.id = win.get.space_id;

		if(!member){
			win.login();
			return;
		}

		var date = new Date();
		var mydate = new Date(member.birth*1000 || 0);
		//月
		var opt = [];
		for(var m=0; m<12; m++){
			var o = {
				name : m + 1 + '月',
				value : m + 1
			};
			if(m == mydate.getMonth())o.selected = 1;
			opt.push(o);
		}
		$('.page_kitchenAsk .month').selectFormat(opt, "月").change(function(){
			var opt = [];
			var m = $(this).attr('value') - 1;
			for(var i in days[m]){
				var d = days[m][i];
				var o = {
					name : d + '日',
					value : d
				};
				opt.push(o);
			}
			$('.page_kitchenAsk .day').selectFormat(opt, "日");
		});
		//日
		var days = [
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],
			[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31]
		];
		var opt = [];
		for(var i in days[mydate.getMonth()]){
			var d = days[mydate.getMonth()][i];
			var o = {
				name : d + '日',
				value : d
			};
			if(d == mydate.getDate())o.selected = 1;
			opt.push(o);
		}
		$('.page_kitchenAsk .day').selectFormat(opt, "日");
		var opt = [{name:'全天',value:0,selected:1}, {name:'上午',value:1}, {name:'下午',value:2}, {name:'晚上',value:3}];
		$('.page_kitchenAsk .time').selectFormat(opt, "时间");
		$('.page_kitchenAsk [name="contacts"]').val(member.nickname);
		$('.page_kitchenAsk [name="telephone"]').val(member.telephone);

	},
	onshow:function () {
		$('.page_kitchenAsk .btn').click(function(){
			var data = {};
			data.space_id = kitchenAskObject.id;
			data.telephone = $('.page_kitchenAsk [name="telephone"]').val();
			data.budget = $('.page_kitchenAsk [name="budget"]').val();
			data.contacts = $('.page_kitchenAsk [name="contacts"]').val();
			data.aim = $('.page_kitchenAsk [name="aim"]').val();
			data.num = $('.page_kitchenAsk [name="num"]').val();
			data.context = $('.page_kitchenAsk [name="context"]').val();
			data.month = $('.page_kitchenAsk .month').attr('value');
			data.day = $('.page_kitchenAsk .day').attr('value');
			data.time = $('.page_kitchenAsk .time').attr('value');

			ajax('Home/Space/query', data, function(d){
				if(d.status == 1){
					$('.page_kitchenAsk .content').empty().addClass('success');
					$('.page_kitchenAsk .btn').remove();
				}else{
					$.alert(d.info, 'error');
				}
			});
		});
	}
};