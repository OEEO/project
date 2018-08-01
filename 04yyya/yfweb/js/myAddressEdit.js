var myAddressEditObject = {
	id : null,
	//切换默认地址
	setDetail : function(em){
		if($(em).hasClass('on')){
			$(em).removeClass('on');
		}else{
			$(em).addClass('on');
		}
	},
	//信息提交
	submit : function(){
		var data = {};
		if(myAddressEditObject.id)
			data.address_id = myAddressEditObject.id;
		data.linkman = $('.page_myAddressEdit .name').val();
		data.citys_id = $('.page_myAddressEdit .area').attr('value');
		data.telephone = $('.page_myAddressEdit .tel').val();
		data.zipcode = $('.page_myAddressEdit .zip').val();
		data.address = $('.page_myAddressEdit .detail').val();
		if($('.page_myAddressEdit .setDefault:visible').size() > 0)
			data.is_default = $('.page_myAddressEdit .setDefault').hasClass('on') ? 1 : 0;
		else
			data.is_default = $('.page_myAddressEdit .setDefault2').hasClass('on') ? 1 : 0;

		ajax('Member/Address/save', data, function(d){
			if(d.status == 1){
				$.alert('提交成功', function(){
					page.back(function(){
						page.reload();
					});
				});
			}else{
				$.alert(d.info, 'error');
			}
		}, 2);
	},
	//删除地址
	delete : function(){
		ajax('Member/Address/delete', {address_id : myAddressEditObject.id}, function(d){
			if(d.status == 1){
				$.alert('删除成功', function(){
					page.back(function(){
						// if(myAddressObject.address_id)win.get.address_id = myAddressObject.address_id;
						page.reload();
					});
				});
			}else{
				$.alert(d.info, 'error');
			}
		}, 2);
	},
	onload : function(){
		myAddressEditObject.id = win.get.edit_address_id||'';

		if(!member){
			win.login();
			return;
		}

		// $('.page_myAddressEdit .name').focus(function(){
		// 	if($(this).text() == '收件人姓名'){
		// 		$(this).text('').removeClass('ps');
		// 	}
		// }).blur(function(){
		// 	if($(this).text() == ''){
		// 		$(this).text('收件人姓名').addClass('ps');
		// 	}
		// });

		// $('.page_myAddressEdit .tel').focus(function(){
		// 	if($(this).text() == '收件人手机号'){
		// 		$(this).text('').removeClass('ps');
		// 	}
		// }).blur(function(){
		// 	if($(this).text() == ''){
		// 		$(this).text('收件人手机号').addClass('ps');
		// 	}
		// });
		//
		// $('.page_myAddressEdit .zip').focus(function(){
		// 	if($(this).text() == '收件人邮编(选填)'){
		// 		$(this).text('').removeClass('ps');
		// 	}
		// }).blur(function(){
		// 	if($(this).text() == ''){
		// 		$(this).text('收件人邮编(选填)').addClass('ps');
		// 	}
		// });

		$('.page_myAddressEdit .detail').focus(function(){
			if($(this).text() == '请在此输入详细的收货地址...'){
				$(this).text('').removeClass('ps');
			}
		}).blur(function(){
			if($(this).text() == ''){
				$(this).text('请在此输入详细的收货地址...').addClass('ps');
			}
		});

		if(myAddressEditObject.id){
			ajax('Member/Address/getDetail', {address_id : myAddressEditObject.id}, function(dt){
				if(dt.info){
					$.alert(dt.info, 'error');
				}else{
					$('.page_myAddressEdit .name').val(dt.linkman).removeClass('ps');
					$('.page_myAddressEdit .tel').val(dt.telephone).removeClass('ps');
					if(dt.zipcode && dt.zipcode != '0')$('.page_myAddressEdit .zip').text(dt.zipcode).removeClass('ps');
					$('.page_myAddressEdit .detail').text(dt.address).removeClass('ps');
					if(dt.is_default && dt.is_default == 1)$('.page_myAddressEdit .setDefault2').addClass('on');
					//省市区关联
					ajax('Home/Index/getCityList', function(d){
						var opt = [];
						for(var i in d){
							var o = {
								name : d[i].name,
								value : d[i].id
							};
							if(d[i].id == dt.province_id)o.selected = 1;
							opt.push(o);
						}
						$('.page_myAddressEdit .province').selectFormat(opt, "选择省份").change(function(){
							ajax('Home/Index/getCityList', {pid : $(this).attr('value')}, function(d){
								var opt = [];
								for(var i in d){
									var o = {
										name : d[i].name,
										value : d[i].id
									};
									if(d[i].id == dt.city_id)o.selected = 1;
									opt.push(o);
								}
								$('.page_myAddressEdit .city').selectFormat(opt, "选择城市").change(function(){
									ajax('Home/Index/getCityList', {pid : $(this).attr('value')}, function(d){
										var opt = [];
										for(var i in d){
											var o = {
												name : d[i].name,
												value : d[i].id
											};
											if(d[i].id == dt.area_id)o.selected = 1;
											opt.push(o);
										}
										$('.page_myAddressEdit .area').selectFormat(opt, "选择区域");
									});
								}).change();
							});
						}).change();
					});
					$('.page_myAddressEdit .setDefault2').show();
					$('.page_myAddressEdit .delete').show();
					$('.page_myAddressEdit.footer span').css('display', 'inline-block');
				}
			}, 2);
		}else{
			//省市区关联
			ajax('Home/Index/getCityList', function(d){
				var opt = [];
				for(var i in d){
					var o = {
						name : d[i].name,
						value : d[i].id
					};
					opt.push(o);
				}
				$('.page_myAddressEdit .province').selectFormat(opt, "选择省份").change(function(){
					ajax('Home/Index/getCityList', {pid : $(this).attr('value')}, function(d){
						var opt = [];
						for(var i in d){
							var o = {
								name : d[i].name,
								value : d[i].id
							};
							opt.push(o);
						}
						$('.page_myAddressEdit .city').selectFormat(opt, "选择城市").change(function(){
							ajax('Home/Index/getCityList', {pid : $(this).attr('value')}, function(d){
								var opt = [];
								for(var i in d){
									var o = {
										name : d[i].name,
										value : d[i].id
									};
									opt.push(o);
								}
								$('.page_myAddressEdit .area').selectFormat(opt, "选择区域");
							});
						});
					});
				});
			});
			$('.page_myAddressEdit .setDefault').show();
		}
	}
};
