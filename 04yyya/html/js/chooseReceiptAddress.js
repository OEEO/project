
$$(function(){ 
	window.location.reload();//更新页面数据
	});
/*********是否显示 编辑删除 选项***********
function editMenu(obj){
	if($(obj).parent().parent('.ConsigneeList').children('div').hasClass('hide')){
		$(obj).parent().parent('.ConsigneeList').children('.editConsigneeInf').removeClass('hide');
		//console.log("使 显示");
		}
	else{
		$(obj).parent().parent('.ConsigneeList').children('.editConsigneeInf').addClass('hide');
		//console.log("使 隐藏");
		}
	}
/*************************/
$('.ConsigneeList .aboutConsignee .section radioS').click()==$('.ConsigneeList .aboutConsignee .choiceConsignee').click();
/*******地址的选中 事件 *********/
function chooseThis(obj){
	$('.content .ConsigneeList .aboutConsignee .section .radioS').removeClass('checked');
	$(obj).prev('.section').children('div').addClass('checked');
	/*	$('.content').children('.ConsigneeList').removeClass('selected');*/
	/*	$(obj).parent().parent('.ConsigneeList').addClass('selected');*/
	}
/***********删除收货地址**********/
function delAddress(obj,id){
	ajax('Member/Address/delete',{'address_id':id},function(d){
			if(d.status){
				$(obj).parent().parent('.ConsigneeList').remove();//删除地址信息 部分
				$(obj).parent().parent('.ConsigneeList').next('the_blank').remove();//删除 地址间的 间隔
			}
		});
	
	}
/*************************/

/***********设为默认 收货地址**********/
function setAsDefault(addressId,area_id,address,zipcode,linkman,telephone){
	ajax('Member/Address/save', {'address_id':addressId,'citys_id':area_id,'linkman':linkman,'telephone':telephone,'address':address,'zipcode':zipcode,'is_default':1}, function(d){
						if(d.status){alert("设置成功!");}
							});
	}
/******************************/


$(function(){
	ajax('Member/Address/getList', function(d){
		console.log("一共有\t"+d.length+'条地址数据');
		var code ='';
		if(d.length){
			for(var i in d){
				code+='<div class="ConsigneeList">';
				code+='<div class="aboutConsignee">';
				code+='<div class="section">';
				console.log(i+'\t\t'+d[i].is_default);
				if(d[i].is_default==1)code+='<div class="radioS checked" onclick="radioStyle(this,\'.radioS\')"></div>';
				else code+='<div class="radioS" onclick="radioStyle(this,\'.radioS\')"></div>';
				code+='</div>';
				code+='<div class="choiceConsignee"  onclick="chooseThis(this)">';
				code+='<div class="Consignee">';
				code+='<div class="consigneeName">姓名<p>'+d[i].linkman+'</p></div>';
				code+='<div class="consigneePhone">手机<p>'+d[i].telephone+'</p></div>';
				code+='<div class="consigneeZipCode">邮编<p>'+d[i].zipcode+'</p></div>';
				code+='</div>';
				code+='<div class="clearfix"></div>';
				code+='<div class="ConsigneeAdd">';
				code+='<div>地址<p>'+d[i].province_name+d[i].city_name+d[i].area_name+d[i].address+'</p></div>';
				code+='</div>';
				code+='</div>';
				code+='<div class="clearfix"></div>';
				code+='</div>';
				code+='<div class="editConsigneeInf hide">';
				code+='<div><img src="./images/write@2x.png"><a href="javascript:parent.page.jump(\'editAddress.html?address_id='+d[i].id+'\')">编辑</a></div>';
				code+='<div onclick="delAddress('+'this'+','+d[i].id+')"><img src="./images/closerad@2x.png"><span>删除</span></div>';
				
				if(d[i].is_default==1)code+='<div class="setAsDefault">默认地址</div>';
				else code+='<div class="setAsDefault" onclick="">设为默认地址</div>';
				
				code+='</div>';
				code+='<div class="the_blank"></div>';
				code+='</div>';
				}
			
		}else{
			code='<center>暂时没有收货地址信息 ！</center>';
			}
		$('.content').html(code);
	
	}, false);
	
	});