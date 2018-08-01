var kitchenDetailObject = {
	id:0,
	arr:[],
	onload : function(){
		if(win.get.id){
			kitchenDetailObject.id = win.get.id;
		}else{
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
			return false;
		}

		ajax('Home/Space/getDetail', {space_id:kitchenDetailObject.id}, function(d){
			//banner图轮播
			script.load('plugins/scrollByJie', function(){
				if(d.group_path && d.group_path.length > 0){
					var sol = new myScroll();
					sol.speed = 3;
					sol.div = ".bg_top";
					for(var i in d.group_path){
						sol.src.push(d.group_path[i].path);
					}
					sol.start();
				}else{
					$('.page_kitchenDetail .bg_top').html('<img src="images/actImg.jpg">');
				}
			});
			$('.page_kitchenDetail .title_top .title_t').text(d.name);
			$('.page_kitchenDetail .title_top .title_cont').text(d.introduction);

			$('.page_kitchenDetail .adress .ad_De').text(d.address);


			var url = win.host + '?page=choice-kitchenDetail&id=' + kitchenDetailObject.id;
			if(member && member.invitecode){
				url += '&invitecode=' + member.invitecode;
			}
			share(d.name, d.introduction, url, d.group_path[0].path);

			//分享绑定
			$('.page_kitchenDetail .shares').click(function(){
				showShareBox();
			});

			var map = new qq.maps.Map(document.getElementById("kitchen_container"),{
				zoom: 13
			});
			var marker;
			geocoder = new qq.maps.Geocoder({
				complete : function(result){
					if(marker && marker.setMap)marker.setMap(null);
					map.setCenter(result.detail.location);
					marker = new qq.maps.Marker({
						position:result.detail.location,
						map:map
					});
				}
			});
			var latLng = new qq.maps.LatLng(d.latitude, d.longitude);
			geocoder.getAddress(latLng);

			$('.page_kitchenDetail .box_n .box_c font').text(d.volume);

			if(d.facility.length >0){
				for(var i in d.facility){
					kitchenDetailObject.arr.push(d.facility[i].id);
				}
			}
			var code='';
			if(kitchenDetailObject.arr.indexOf('1')==-1){
				code +='<a href="javascript:void(0);" class="no_t"><img src="images/no_kitchenDetail_ico1.png"><br>WIFI</a>';
			}else{
				code +='<a href="javascript:void(0);"><img src="images/kitchenDetail_ico1.png"><br>WIFI</a>';
			}
			if(kitchenDetailObject.arr.indexOf('2')==-1){
				code +='<a href="javascript:void(0);" class="no_t"><img src="images/no_kitchenDetail_ico2.png"><br>酒具</a>';
			}else{
				code +='<a href="javascript:void(0);"><img src="images/kitchenDetail_ico2.png"><br>酒具</a>';
			}
			if(kitchenDetailObject.arr.indexOf('3')==-1){
				code +='<a href="javascript:void(0);" class="no_t"><img src="images/no_kitchenDetail_ico3.png"><br>电视音响</a>';
			}else{
				code +='<a href="javascript:void(0);"><img src="images/kitchenDetail_ico3.png"><br>电视音响</a>';
			}
			if(kitchenDetailObject.arr.indexOf('4')==-1){
				code +='<a href="javascript:void(0);" class="no_t"><img src="images/no_kitchenDetail_ico4.png"><br>餐具</a>';
			}else{
				code +='<a href="javascript:void(0);"><img src="images/kitchenDetail_ico4.png"><br>餐具</a>';
			}
			if(kitchenDetailObject.arr.indexOf('5')==-1){
				code +='<a href="javascript:void(0);" class="no_t"><img src="images/no_kitchenDetail_ico5.png"><br>空调</a>';
			}else{
				code +='<a href="javascript:void(0);"><img src="images/kitchenDetail_ico5.png"><br>空调</a>';
			}
			if(kitchenDetailObject.arr.indexOf('6')==-1){
				code +='<a href="javascript:void(0);" class="no_t"><img src="images/no_kitchenDetail_ico6.png"><br>明火</a>';
			}else{
				code +='<a href="javascript:void(0);"><img src="images/kitchenDetail_ico6.png"><br>明火</a>';
			}
			if(kitchenDetailObject.arr.indexOf('7')==-1){
				code +='<a href="javascript:void(0);" class="no_t"><img src="images/no_kitchenDetail_ico7.png"><br>开放式厨房</a>';
			}else{
				code +='<a href="javascript:void(0);"><img src="images/kitchenDetail_ico7.png"><br>开放式厨房</a>';
			}
			if(kitchenDetailObject.arr.indexOf('8')==-1){
				code +='<a href="javascript:void(0);" class="no_t"><img src="images/no_kitchenDetail_ico8.png"><br>吸烟</a>';
			}else{
				code +='<a href="javascript:void(0);"><img src="images/kitchenDetail_ico8.png"><br>吸烟</a>';
			}
			$('.page_kitchenDetail .zooles').html(code);

			var str = '';
			for(var i in d.server_time){
				str += d.server_time[i] + '<br>';
			}
			str += '（需要提前预约）';
			$('.page_kitchenDetail .timees').html(str);

			$('.page_kitchenDetail .others').html(d.context);

			$('.page_kitchenDetail .acreage').text(d.proportion + '平米');
			var codes = '';
			for(var i in d.tags){
				codes +='#'+ d.tags[i] +'&nbsp;';
			}
			$('.page_kitchenDetail .title_cate').html(codes);

		});
		$('.page_kitchenDetail .bot_but').click(function(){
			jump('kitchenAsk', {space_id: kitchenDetailObject.id});
		});
	}
};
