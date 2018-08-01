var changeUserinfoObject = {
	signtext:null,
	//信息提交
	submit : function(){
		var data = {};
		data.nickname = $('.page_changeUserinfo .nickname').text();
		data.city_id = $('.page_changeUserinfo .city').children('option:selected').attr('value');
		data.sex = $('.page_changeUserinfo .sex').children('option:selected').attr('value');

		// var y = $('.page_changeUserinfo .birth .y').attr('value');
		// var m = $('.page_changeUserinfo .birth .m').attr('value');
		// var d = $('.page_changeUserinfo .birth .d').attr('value');
		// data.birth = Math.round((new Date(y+'/'+m+'/'+d+' 00:00:00')).getTime() / 1000);
		//data.signature = changeUserinfoObject.signtext;
		//data.pic_id = $('.page_changeUserinfo .takePhotos img').attr('pic_id');
		ajax('Member/Index/modifyInfo', data, function(d){
			if(d.status == 1){
				member = d.info;
				//document.write("js获取上(前)一页url"+document.referrer);return false;
				if(location.href.indexOf('changeUserinfo') == -1){
					page.back(function(){
						page.reload();
					});
				}else{
					window.location.href ='?page=choice';
				}
			}else{
				$.alert(d.info, 'error');
			}
		}, 2);
	},
	onload : function(){
		if(member.path){
			$('.page_changeUserinfo .takePhotos img').attr('src', member.path);
			$('.page_changeUserinfo .takePhotos img').attr('pic_id', member.pic_id);
		}else{
			//$('.page_changeUserinfo .takePhotos img').attr('src', win.defaultPics[Math.floor(Math.random()*9)].path);
		}
		/*$('.page_changeUserinfo .takePhotos a').click(function(){
		 jump('myPictures', {size:[320,320],count:1, backFun:function(d){
		 ajax('Member/Index/modifyInfo', {pic_id:d[0].pic_id}, function(dt) {
		 if(dt.status == 1){
		 $('.page_changeUserinfo .takePhotos img').attr('src', d[0].path).attr('pic_id', d[0].pic_id);
		 member.pic_id = d[0].pic_id;
		 member.path = d[0].path;
		 $.alert('头像修改成功!');
		 }else{
		 $.alert(dt.info, 'error');
		 }
		 });
		 }});
		 });*/

		$('.page_changeUserinfo .userInf .nickname').text(member.nickname);

		//省市区关联
		ajax('Home/Index/getCityList', function(d){
			//var opt = [];
			for(var i in d){
				if(d[i].id == member.province_id){
					var option = '<option value="'+ d[i].id +'" selected>'+ d[i].name +'</option>';
				}else{
					var option = '<option value="'+ d[i].id +'">'+ d[i].name +'</option>';
				}
				$( '.page_changeUserinfo .province' ).append(option);
			}
			// $('.page_changeUserinfo .province').html(c);
			$('.page_changeUserinfo .province').change(function(){
				ajax('Home/Index/getCityList', {pid : $(this).children('option:selected').attr('value')}, function(d){
					// var opt = [];
					var code = '';
					for(var i in d){
						if(d[i].id == member.city_id){
							code += '<option value="'+ d[i].id +'" selected>'+ d[i].name +'</option>';
						}else{
							code += '<option value="'+ d[i].id +'">'+ d[i].name +'</option>';
						}
					}
					$('.page_changeUserinfo .city').html(code);
				});
			});
		});
		ajax('Home/Index/getCityList', {pid : member.province_id}, function(d){
			var opt = [];
			for(var i in d){
				if(d[i].id == member.city_id){
					var option = '<option value="'+ d[i].id +'" selected>'+ d[i].name +'</option>';
				}else{
					var option = '<option value="'+ d[i].id +'">'+ d[i].name +'</option>';
				}
				$( '.page_changeUserinfo .city' ).append(option);
			}
		});

		//出生年月日
		var date = new Date();
		var mydate = new Date(member.birth*1000 || 0);
		//年
		var opt = [];
		for(var y=1950; y < date.getFullYear() - 10; y++){
			var o = {
				name : y,
				value : y
			};
			if(y == mydate.getFullYear())o.selected = 1;
			opt.push(o);
		}
		$('.page_changeUserinfo .birth .y').selectFormat(opt, "年");
		//月
		var opt = [];
		for(var m=0; m<12; m++){
			var o = {
				name : m + 1,
				value : m + 1
			};
			if(m == mydate.getMonth())o.selected = 1;
			opt.push(o);
		}
		$('.page_changeUserinfo .birth .m').selectFormat(opt, "月").change(function(){
			var opt = [];
			var m = $(this).attr('value') - 1;
			for(var i in days[m]){
				var d = days[m][i];
				var o = {
					name : d,
					value : d
				};
				opt.push(o);
			}
			$('.page_changeUserinfo .birth .d').selectFormat(opt, "日");
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
				name : d,
				value : d
			};
			if(d == mydate.getDate())o.selected = 1;
			opt.push(o);
		}
		$('.page_changeUserinfo .birth .d').selectFormat(opt, "日");
		$('.page_changeUserinfo .telephone').text(member.telephone);

		changeUserinfoObject.signtext = member.signature;

		var opt = [
			{name:"未选择", value:0}, {name:"男", value:1}, {name:"女", value:2}
		];
		if(member.sex){
			$('.page_changeUserinfo .sex option').eq(member.sex).attr('selected',true).siblings('option').removeAttr('selected');
			opt[member.sex].selected = 1;
		}
		// $('.page_changeUserinfo .sex').selectFormat(opt, "性别");
	},
	onshow:function(){
		//头像更换
		$('.page_changeUserinfo .takePhotos').click(function(){
			jump('myPictures', {size:[320,320], count:1, backFun:function(d){
				ajax('Member/Index/modifyInfo', {pic_id:d[0].pic_id}, function(dt) {
					if(dt.status == 1){
						$('.page_changeUserinfo .takePhotos img').attr('src', d[0].path).attr('pic_id', d[0].pic_id);
						member.pic_id = d[0].pic_id;
						member.path = d[0].path;
						$.alert('头像修改成功');
					}else{
						$.alert(dt.info, 'error');
					}
				});
				// $('.page_changeUserinfo .takePhotos img').attr('src',d[0].path).attr('pic_id',d[0].pic_id);
				// member.pic_id = d[0].pic_id;
				// member.path = d[0].path;
				// $.alert('头像修改成功');
			}});
			$('.page_changeUserinfo .sex').change(function(){
				$('.page_changeUserinfo .userInf .sex').css('color','#333');
			});
			// if($('.page_changeUserinfo .head_pic_box a').size() == 0){
			// 	updateImg(function(){
			// 		var d = win.defaultPics;
			// 		for(var i in d){
			// 			var pem = $('<a href="javascript:void(0);" pic_id="'+ d[i].id +'"><img src="'+ d[i].path +'"/></a>').one('click', function(){
			// 				var id = $(this).attr('pic_id');
			// 				var src = $(this).children('img').attr('src');
			// 				ajax('Member/Index/modifyInfo', {pic_id:id}, function(dt) {
			// 					if(dt.status == 1){
			// 						$('.page_changeUserinfo .takePhotos img').attr('src', src).attr('pic_id', id);
			// 						member.pic_id = id;
			// 						member.path = src;
			// 						$('.page_changeUserinfo .head_pic_box a').css({'top':'8rem','left':'15rem'});
			// 						$('.page_changeUserinfo .head_pic_box').hide();
			// 						$.alert('头像修改成功');
			// 					}else{
			// 						$.alert(dt.info, 'error');
			// 					}
			// 				});
			// 			}).appendTo('.page_changeUserinfo .head_pic_box');
			// 		}
			// 		$('.page_changeUserinfo .head_pic_box a[pic_id="'+ member.pic_id +'"]').addClass('add_border_select');
			// 		$('.page_changeUserinfo .head_pic_box a').each(function(){
			// 			var i = $(this).index();
			// 			$(this).animate(positions[i]);
			// 		});
			// 	});
			// }
		});
	}
};
