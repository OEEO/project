var myAddressObject = {
	address_id : null,
	//选择地址
	select : function(em, id){
		var name = $(em).parent().find('.name').text();
		var tel = $(em).parent().find('.tel').text();
		var address = $(em).parent().find('.b').text();
		address = address.replace('<span>[默认地址]</span>', '');
		page.back(function(){
			if(page.names.indexOf('raisePay') === -1){
				confirmBuyObject.addressInput(id, name, tel, address);
			}else{
				raisePayObject.addressInput(id, name, tel, address);
			}
		});
	},
	type: 0, // 0 -- 选择 ， 1 -- 管理
	selectAddress: function (id) {
		if (myAddressObject.type === 1) {
                jump('myAddressEdit', {edit_address_id: id});
            } else {
                var $addressItem = $('[data-address="' + id + '"]');
                var name = $addressItem.find('.name').text();
                var tel = $addressItem.find('.tel').text();
                var address = $addressItem.find('.b').text();
                address = address.replace('[默认地址]', '');
            	sessionStorage.removeItem('address_id');
                page.back(function(){
                    if(page.names.indexOf('raisePay') === -1){
                        confirmBuyObject.addressInput(id, name, tel, address);
                    }else{
                        raisePayObject.addressInput(id, name, tel, address);
                    }
                });
		}
	},
	onload : function(){
		if(win.get.address_id || myAddressObject.address_id || sessionStorage.getItem('address_id')){
			$('.page_myAddress.header .title').text('选择收货地址');
			myAddressObject.address_id = win.get.address_id||myAddressObject.address_id || sessionStorage.getItem('address_id');
			sessionStorage.setItem('address_id', myAddressObject.address_id);
		}

        $('.page_myAddress.header .setup').click(function(){
            if($(this).text() == '管理'){
                myAddressObject.type = 1;
                $(this).text('取消管理');
                $('.page_myAddress .content').find('.editBtn').show();
                $('.page_myAddress .content').find('.selectBtn').hide();
            }else{
                $(this).text('管理');
                myAddressObject.type = 2;
                $('.page_myAddress .content').find('.editBtn').hide();
                $('.page_myAddress .content').find('.selectBtn').show();
                $('.page_myAddress .content').find('[data-address="'+ myAddressObject.address_id +'"] .selectBtn').addClass('selected');
            }
        });

		ajax('Member/Address/getList', {}, function(d){
			var code = '';
			for(var i in d){
				code += '<div class="item" data-address="'+ d[i].id +'"><a href="javascript:;">';
				code += '	<div class="left">';
				code += '		<div class="t">';
				code += '			<div class="name">'+ d[i].linkman +'</div>';
				code += '			<div class="tel">'+ d[i].telephone +'</div>';
				code += '		</div>';
				if(d[i].is_default == 1){
					code += '	<div class="b"><span>[默认地址]</span>'+ d[i].province_name + d[i].province_alt + d[i].city_name + d[i].city_alt + d[i].area_name + d[i].area_alt + d[i].address +'</div>';
				}else{
					code += '	<div class="b">'+ d[i].province_name + d[i].province_alt + d[i].city_name + d[i].city_alt + d[i].area_name + d[i].area_alt + d[i].address +'</div>';
				}
				code += '	</div></a>';
				code += '	<span class="editBtn">&gt;</span>';
				code += '	<button class="selectBtn"></button>';
				code += '</div>';
			}
			$('.page_myAddress .content').html(code);
			if(myAddressObject.address_id){
				$('.page_myAddress .content').find('.editBtn').hide();
				$('.page_myAddress .content').find('.selectBtn').show();
				$('.page_myAddress .content').find('[data-address="'+ myAddressObject.address_id +'"] .selectBtn').addClass('selected');
			}
		}, 2);
	},
	onshow: function () {
		$('.content').on('click', '.item', function () {
			var id = $(this).data('address');
			myAddressObject.selectAddress(id);
		});
	}
};
