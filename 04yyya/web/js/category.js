var categoryObject = {
	category : 0,
	type: 'loadtips',
	catg:null,
	none_img:null,
	tag_id : 0,
	//加载活动列表
	loadtips : function (page){
		if($('.page_category .no_more').size() > 0)return;
		$('.page_category center').show();
		var page = page||1;
		var data = {};
		if(categoryObject.tag_id != 0){
			data = {get:{page:page},post:{tag_id:categoryObject.tag_id}};
		}else{
			data = {get:{page:page}, post:{category:categoryObject.category}};
		}
		ajax('Goods/Tips/getlist', data, function(d){
			// $('.page_category center').hide();
			if(d.info){
				$.alert(d.info, 'error');
				return;
			}
			if(d.length>0){
				var code = '';
				for(var i in d){
					code += '<li>';
					code += '	<div class="pro_top">';
					code += '		<a href="javascript:jump(\'daRen\', {member_id:'+ d[i].member_id +'})" class="User_Img">';
					code += '			<img class="imgPortrait" src="'+ d[i].headpic +'" />';
					code += '		</a>';
					code += '	<div class="User_Conten">';
					code += '		<div class="Contens">';
					code += '			<div class="qUserName">'+ d[i].nickname +'</div>';
					code += '        		<div class="msgDetail">';
					code += '           		<div class="song_mi b_right">';
					code += '               		<font>粉丝:&nbsp;</font><span class="fanes">'+ d[i].follow_num*3 +'</span>人';
					code += '               	</div>';
					code += '           		<div class="song_mi">';
					code += '           			<font>赏味:&nbsp;</font>'+ d[i].customers +'人';
					code += '           		</div>';
					code += '        	 	</div>';
					code += '     		</div>';
					/*code += '    		<div class="User_right">';
					 if(d[i].is_follow)
					 code += '     			<button data="'+ d[i].member_id +'" class="valued" onclick="setFollow(this)"></button>';
					 else
					 code += '     			<button data="'+ d[i].member_id +'" onclick="setFollow(this)"></button>';
					 code += '     		</div>';*/
					code += '		</div>';
					code += '	</div>';
					code += '   <div class="pro_center">';
					if(d[i].is_collect)
						code += '     			<button data="'+ d[i].id +'" class="Collected" onclick="setCollect(this)"></button>';
					else {
						code += '     			<button data="'+ d[i].id +'" onclick="setCollect(this)"></button>';
					}
						
					console.log(d[i].p_tags_id);
					if(d[i].p_tags_id != 76) code += '   	<span>'+ d[i].catname +'</span>';
					if(d[i].sellout==1)
						code += '		<div class="sellout"><img src ="images/sellout.png" /></div>';
					if(categoryObject.tag_id != 0){
						code += '   	<a href="javascript:jump(\'specialDetail\', {tips_id:'+ d[i].id +'})"><img src="'+ d[i].mainpic +'" /></a>';
						code += '	</div>';
						code += '	<div class="pro_title" onclick="javascript:jump(\'specialDetail\', {tips_id:'+ d[i].id +'});">';
					}else{
						if(d[i].catname == '课程'){
							code += '   	<a href="javascript:jump(\'courseDetail\', {tips_id:'+ d[i].id +'})"><img src="'+ d[i].mainpic +'" /></a>';
							code += '	</div>';
							code += '	<div class="pro_title" onclick="javascript:jump(\'courseDetail\', {tips_id:'+ d[i].id +'});">';
						}else{
							code += '   	<a href="javascript:jump(\'tipsDetail\', {tips_id:'+ d[i].id +'})"><img src="'+ d[i].mainpic +'" /></a>';
							code += '	</div>';
							code += '	<div class="pro_title" onclick="javascript:jump(\'tipsDetail\', {tips_id:'+ d[i].id +'});">';
						}
					}
					if (d[i].p_tags_id == 76) {
						code += '<span class="red_tag">预约</span>';
					}
					if(d[i].buy_status != 2){
						code += '		<span class="title_left">'+ d[i].title +'</span>';
					}else{
						code += '		<span class="title_left"><img src="images/buy_status.png" />&nbsp;'+ d[i].title +'</span>';
					}
					code += '	</div>';
					if(categoryObject.tag_id != 0){
						code += '	<div class="pro_buttom" onclick="javascript:jump(\'specialDetail\', {tips_id:'+ d[i].id +'});">';
					}else{
						if(d[i].catname == '课程'){
							code += '	<div class="pro_buttom" onclick="javascript:jump(\'courseDetail\', {tips_id:'+ d[i].id +'});">';
						}else{
							code += '	<div class="pro_buttom" onclick="javascript:jump(\'tipsDetail\', {tips_id:'+ d[i].id +'});">';
						}
					}
					code += '		<span class="pro_adress">'+ (d[i].city) +'</span>';
					if(d[i].buy_status != 2 && d[i].p_tags_id == 76) {
						code += '		<span class="pro_time" style="visibility:hidden;"></span>';
					} else if (d[i].buy_status != 2) {
						code += '		<span class="pro_time">'+ d[i].start_time.timeFormat('m-d W H:i') +'</span>';
					} else {
						code += '		<span class="pro_num">适合:'+ d[i].min_num +'-'+ d[i].restrict_num +'/人</span>';
					}
						
					code += '  		<span class="price_right">￥'+ parseFloat(d[i].price).priceFormat() +'/份</span>';
					code += '	</div>';
					code += '</li>';
				}
			}else{
				if(page ==1) {
					switch (categoryObject.catg) {
						case '私房菜':
							code = '<div class="no_msgs"><img src="images/category_over.png" /><span>暂时还没有可预定的私房美味</span></div>';
							break;
						case '社交饭局':
							code = '<div class="no_msgs"><img src="images/category_over.png" /><span>暂时还没有饭局</span></div>';	
							break;
						default:
							code = '<div class="no_msgs"><img src="images/category_over.png" /><span>暂时还没有'+ categoryObject.catg +'活动哦~</span></div>';	
					}
				} else {
					code = '<div class="no_more"></div>';
					$('.page_category').off('scroll');
				}
					// $('.page_category .pro_list .product_list').append('<div class="no_more"></div>');
			}
			if(page == 1)
				$('.page_category .pro_list .product_list').html(code);
			else
				$('.page_category .pro_list .product_list').append(code);
			$('.page_category center').hide();
		});
	},
	loadgoods : function(page){
        if($('.page_category .no_more').size() > 0)return;
		// if($('.page_category center').size() > 0)return;
        $('.page_category center').show();
		var page = page||1;
		ajax('Goods/Goods/getlist', {get:{page:page}, post:{category:categoryObject.category}}, function(d) {
			if (d.info) {
				$.alert(d.info, 'error');
				return;
			}

			if(d.length>0) {
				var code = '';
				for (var i in d) {
                    code += '<a class="goods_item" href="javascript:jump(\'goodsDetail\', {goods_id:'+ d[i].id +'})">';
                    code += '<img src="'+ d[i].path +'">';

                    if (d[i].isPiece) {
                        code += '<div class="title">'+ d[i].title +'<span class="piece-pie">拼</span></div>';
                    } else {
                    	code += '<div class="title">'+ d[i].title +'</div>';
					}
                    code += '<div class="bottom">';
                    code += '<div class="left">';
                    code += '已售 <font>'+ d[i].cell_count +'</font> 份';
                    if(d[i].shipping == 0){
                        code += '<span>包邮</span>';
                    }
                    if (d[i].price.match(/\d+\.00/ig)) {
                    	d[i].price = (+d[i].price).toFixed(0);
					}
                    code += '</div>';
                    code += '<div class="price">'+ d[i].price +'<small>/份</small></div>';
                    code += '</div>';
                    code += '</a>';
				}
				if(page == 1)
					$('.page_category .pro_list .product_list').html(code);
				else
					$('.page_category .pro_list .product_list').append(code);
				categoryObject.winScrollSock = false;
			}else{
				console.log(categoryObject);
				if(page ==1){
					$('.page_category .pro_list .product_list').html('<div class="no_msgs"><img src="images/category_over.png" /><span>暂时还没有'+ categoryObject.catg +'商品哦~</span></div>');
				}else{
					$('.page_category .pro_list .product_list').append('<div class="no_more"></div>');
				}
			}

            $('.page_category center').hide();
		});
	},
	onload : function(){
		if(win.get.category){
			categoryObject.category = win.get.category;
		}
		if(win.get.tag_id){
			categoryObject.tag_id = win.get.tag_id;
		}
		// $('.page_category .location').text(win.city.name);
		if ($('.page_category.location_list').size() > 0) {
			$('.page_category.location_list a').removeClass('yellow');
			$('.page_category.location_list a[data="' + win.city.id + '"]').addClass('yellow');
		}
		// $('.page_category.header .title').text(win.get.title || '社交饭局');

		// 城市选择
		// $('.page_category .location').click(function () {
		// 	var $this = $(this);
		// 	if ($this.hasClass('on')) {
		// 		$('.page_category.location_list').fadeOut('fast');
		// 		$this.removeClass('on');
		// 	} else {
		// 		if ($('.page_category.location_list').size() == 0) {
		// 			$('<div class="page_category location_list resourcesBox" onclick="$(\'.page_category .location\').click();"><div class="list_name"></div></div>').appendTo('body');
		// 			ajax('Home/Index/citys', function (d) {
		// 				var num = 0;
		// 				var now_city = '';
		// 				var code = '';
		// 				for (var i in d) {
		// 					if (d[i] == win.city.name)
		// 						now_city = ' yellow';
		// 					else
		// 						now_city = '';
		// 					if (num % 3 == 1) {
		// 						code += '<a class="l_cen' + now_city + '" href="javascript:void(0);" data="' + i + '">' + d[i] + '</a>';
		// 					} else {
		// 						code += '<a class="' + now_city + '" href="javascript:void(0);" data="' + i + '">' + d[i] + '</a>';
		// 					}
		// 					num++;
		// 				}
		// 				$('.page_category.location_list .list_name').html(code);
		// 				$('.page_category.location_list').fadeIn('fast');
		// 				$('.page_category.location_list .list_name a').click(function () {
		// 					ajax('Home/Index/ChangeCity', { 'city_id': $(this).attr('data') }, function (d) {
		// 						if (d.status == 1) {
		// 							if (window.localStorage) {
		// 								storage.set('city_id', d.info.id);
		// 								storage.set('city_name', d.info.name);
		// 							}
		// 							$.alert('切换成功', function () {
		// 								$this.text(d.info.name);
		// 								console.log($this.text());
		// 								$('.page_category.location_list').remove();
		// 								win.city.id = d.info.id;
		// 								win.city.name = d.info.name;
		// 								page.reload();
		// 							});
		// 						} else {
		// 							$.alert(d.info, 'error');
		// 						}
		// 					});
		// 				});
		// 			});
		// 		} else {
		// 			$('.page_category.location_list').fadeIn('fast');
		// 		}
		// 		$this.addClass('on');
		// 	}
		// });


		//活动分类
		if(categoryObject.category==1){
			categoryObject.catg = '饭局';
			categoryObject.none_img='homepage_f4_rice.png';
		}else if(categoryObject.category==2){
			categoryObject.catg = '课程';
			categoryObject.none_img='homepage_f4_rice_copy.png';
			$('.page_category .bodyTop').show();
			script.load('plugins/scrollByJie', function(){
				/***********ajax请求页面头部bander数据**************/
				ajax('Home/Index/banner', {type:5}, function(d){
					if(d.length > 0) {
						var sol = new myScroll();
						sol.speed = 3;
						sol.div = ".page_category .bodyTop";
						for (var i in d) {
							sol.src.push(d[i].path);
							sol.link.push(d[i].url);
						}
						sol.start();
					}else{
						$('.page_category .bodyTop').remove();
					}
				});
			});
		}else if(categoryObject.category==3){
			categoryObject.catg = '活动';
			categoryObject.none_img='homepage_f4_rice_cox.png';
		}else if(categoryObject.category==-2){
			categoryObject.catg = '';
		}else if(categoryObject.category==-1){
			categoryObject.catg = '定制';
		}else if(categoryObject.category==-3){
			categoryObject.catg = '商品';
			categoryObject.type = 'loadgoods';
		}else if(categoryObject.category==-4){
			categoryObject.catg = '社交饭局';
		}else if(categoryObject.category==-5){
			categoryObject.catg = '大咖饭局';
		}else if(categoryObject.tag_id==76){
			categoryObject.catg = '私房菜';
		}else{
			$.alert('非法访问', function(){
				page.back();
			}, 'error');
		}
        $('.page_category.header .title').text(categoryObject.catg || '社交饭局');
	},
	onshow:function(){
		$('.page_category .pro_list .product_list').empty();
		//屏幕滚动事件
		$('.page_category.wrapper').on('scroll', function(){
			//滚动加载内容
            var pagenum = 0;
			if (categoryObject.category != -3) {
                pagenum = Math.ceil($('.page_category .product_list>li').length / 5) + 1;
			} else {
                pagenum = Math.ceil($('.page_category .product_list>a').length / 5) + 1;
			}
			if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10
				&& $('.page_category center:visible').length == 0){
				categoryObject[categoryObject.type](pagenum);
			}
		});
		// 加载默认筛选列表
		categoryObject[categoryObject.type]();
	}
};




