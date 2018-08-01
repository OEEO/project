var addressId;
$(function(){
/*	if(window.location.href.indexOf('#') == -1){
		$('body').html('非法访问！');
	}
	addressId = window.location.href.split('#')[1];*/
	var XX=GetHrefParameter();
	if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='address_id')addressId = XX[i][1];
		}
		
	ajax('Member/Address/getList', {'address_id':addressId}, function(d){
		console.log(d.linkman);
		$('.addRessDetails .a').children('input').addClass('aa').val(d.linkman);
		$('.addRessDetails .b').children('input').addClass('bb').val(d.telephone);
		$('.addRessDetails .c').children('input').addClass('cc').val(d.zipcode);
		$('.addRessDetails .add .chooseAddInf').children('#province').html('<option value=\"'+d.id+'\">'+d.province_name+'</option>');
		$('.addRessDetails .add .chooseAddInf').children('#city').html('<option value=\"'+d.city_id+'\">'+d.city_name+'</option>');
		$('.addRessDetails .add .chooseAddInf').children('#part').html('<option value=\"'+d.area_id+'\">'+d.area_name+'</option>');
		$('.addRessDetails .add .editAddress').addClass('address').text(d.address);
		if(d.is_default==1)$('.radioSty').addClass('checked');
			}, false);
	
	});

	
function getCity(){
	$('#part').html('<option value="0">请选择区/县</option>');
	var pid = $('#province').val();
	//console.log("选中的省份\t"+pid);
	ajax('Member/Address/getCityList',{'pid':pid},function(d){
	 	var code = '<option value="0">请选择市</option>';
		for(var i in d){
			//alert(d[i]['id']+'---'+d[i]['nickname']);
			code += '<option value=\"'+d[i]['id']+'\">'+d[i].name+'</option>';
		}
		$('#city').html(code);
	});
		
	}
	
function getPart(){
	$('#part').html('<option value="0">请选择区/县</option>');
	var pid = $('#city').val();
	//console.log("选中的市\t"+pid);
	ajax('Member/Address/getCityList',{'pid':pid},function(d){
 	var code = '<option value="0">请选择区/县</option>';
		for(var i in d){
			//alert(d[i]['id']+'---'+d[i]['nickname']);
			code += '<option value=\"'+d[i]['id']+'\">'+d[i].name+'</option>';
		}
		$('#part').html(code);

	});
		
	}
	
function updateAddress(){
	var is_default=0; 
	
	var linkman = $('.a>input').val();
	var telephone = $('.b>input').val();
	var province_id = $('#province').val();
	var citys_id =$('#city').val();
	var area_id = $('#part').val();
	var address = $('.add .editAddress').text();
	var zipcode = $('.c>input').val();
	if($('.addRessDetails').children('div').hasClass('checked')) is_default=1;
	/*
	console.log("提交的数据\t"+province_id+'\t\t'+citys_id+'\t\t'+area_id);
	console.log("获取到的 收货人  : \t"+linkman);
	console.log("获取到的 手机号码:\t"+telephone);
	console.log("获取到的 地址详情:\t"+address);
	console.log("获取到的 邮政编码:\t"+zipcode);
	console.log("获取到的 是否默认:\t"+is_default);
	*/
	ajax('Member/Address/save', {'address_id':addressId,'citys_id':area_id,'linkman':linkman,'telephone':telephone,'address':address,'zipcode':zipcode,'is_default':is_default}, function(d){
		if(d.status){
			alert("添加成功!");
			}
	
		});
	
	}