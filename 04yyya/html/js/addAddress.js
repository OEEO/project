$(function(){
	ajax('Member/Address/getCityList',function(d){
	 	var code = '<option value="0">请选择省份</option>';
		for(var i in d){
			//alert(d[i]['id']+'---'+d[i]['nickname']);
			code += '<option value=\"'+d[i]['id']+'\">'+d[i].name+'</option>';
		}
		$('#province').html(code);
	}, false);
	
	
	//addAddress();
	
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

	
/*
address_id	int	要修改的地址ID(添加操作忽略该参数)
citys_id	int	城市ID
address	string	详细地址
zipcode	int	邮政编码(默认510000)
linkman	string	联系人(默认会员昵称)
telephone	int	联系人手机号(默认会员手机号)
is_default	int	是否为默认地址(0-否 1-是)
*/

$(document).click(function(){//系统的点击事
	//addAddress();
	});


function addAddress(){
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
	ajax('Member/Address/save', {'citys_id':area_id,'linkman':linkman,'telephone':telephone,'address':address,'zipcode':zipcode,'is_default':is_default}, function(d){
		if(d.status){
			alert("添加成功!");
			}
	
		});
	
	}
	