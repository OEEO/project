var foodClubListObject = {
    goodslock : false,
    classobj:'',
    catname:null,
    page:1,
    //加载课程和活动列表
    loadTipList : function(type,page){
        if(type == 2){
            foodClubListObject.catname = '课程';
            foodClubListObject.classobj = '.course';
        }
        if(type == 3){
            foodClubListObject.catname = '活动';
            foodClubListObject.classobj = '.activited';
        }
        if($('.page_foodClubList center').size() > 0)return;
        var page = page||1;
        ajax('Goods/Tips/getlist', {get:{page:page}, post:{category:type}}, function(d){
            if(d.info){
                $.alert(d.info, 'error');
                return;
            }
            if(d.length>0){
                var code = '';
                for(var i in d){
                    if(i <= 1){
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
                        else
                            code += '     			<button data="'+ d[i].id +'" onclick="setCollect(this)"></button>';
                        code += '   	<span>'+ d[i].catname +'</span>';
                        if(d[i].sellout==1)
                            code += '		<div class="sellout"><img src ="images/sellout.png" /></div>';
                        code += '   	<a href="javascript:jump(\'tipsDetail\', {tips_id:'+ d[i].id +'})"><img src="'+ d[i].mainpic +'" /></a>';
                        code += '	</div>';
                        code += '	<div class="pro_title" onclick="javascript:jump(\'tipsDetail\', {tips_id:'+ d[i].id +'});">';
                        if(d[i].buy_status != 2){
                            code += '		<span class="title_left">'+ d[i].title +'</span>';
                        }else{
                            code += '		<span class="title_left"><img src="images/buy_status.png" />&nbsp;'+ d[i].title +'</span>';
                        }
                        code += '	</div>';
                        code += '	<div class="pro_buttom" onclick="javascript:jump(\'tipsDetail\', {tips_id:'+ d[i].id +'});">';
                        if(d[i].buy_status != 2)
                            code += '		<span class="pro_time">'+ d[i].start_time.timeFormat('m-d W H:i') +'</span>';
                        else
                            code += '		<span class="pro_num">适合:'+ d[i].min_num +'-'+ d[i].restrict_num +'/人</span>';
                        code += '		<span class="pro_adress">'+ (d[i].simpleaddress ? d[i].simpleaddress : d[i].address) +'</span>';
                        code += '  		<span class="price_right">￥'+ parseFloat(d[i].price).priceFormat() +'/份</span>';
                        code += '	</div>';
                        code += '</li>';
                    }
                }
                if(page == 1)
                    $('.page_foodClubList '+foodClubListObject.classobj+' .product_list').html(code);
                else
                    $('.page_foodClubList '+foodClubListObject.classobj+' .product_list').append(code);
            }else{
                $(foodClubListObject.classobj).hide();
                // if(page ==1){
                //     $('.page_foodClubList '+foodClubListObject.classobj+' .product_list').html('<div class="no_msgs">暂时没有'+foodClubListObject.catname+'</div>');
                // }
            }
        },2);
    },
    //加载商品列表
    loadgoods : function(page){
        if($('.page_foodClubList center').size() > 0)return;
        var page = page||1;
        ajax('Goods/Goods/getlist', {get:{page:page}}, function(d) {
            if (d.info) {
                $.alert(d.info, 'error');
                return;
            }
            if(d.length>0) {
                var code = '';
                for (var i in d) {
                    code += '<a class="goods_item" href="javascript:jump(\'goodsDetail\', {goods_id:'+ d[i].id +'})">';
                    code += '<img src="'+ d[i].path +'">';
                    code += '<div class="title">'+ d[i].title +'</div>';
                    code += '<div class="bottom">'
                    code += '<div class="left">';
                    code += '已售 <font>'+ d[i].cell_count +'</font> 份';
                    if(d[i].shipping == 0){
                        code += '<span>包邮</span>';
                    }
                    code += '</div>';
                    code += '<div class="price">'+ parseFloat(d[i].price).priceFormat() +'<small>元/份</small></div>';
                    code += '</div>'
                    code += '</a>';
                }
                if(page == 1)
                    $('.page_foodClubList .goods .product_list').html(code);
                else
                    $('.page_foodClubList .goods .product_list').append(code);
                    foodClubListObject.goodslock = false;
            }else{
                if(page ==1){
                    $('.goods').hide();
                    // $('.page_foodClubList .goods .product_list').html('<div class="no_msgs">=<span>暂时还没有商品哦~</span></div>');
                }else{
                    $('.page_foodClubList .goods .product_list').append('<div class="no_more"></div>');
                }
            }
        });
    },
    onload : function(){
        foodClubListObject.loadTipList(2);
        foodClubListObject.loadTipList(3);
        foodClubListObject.loadgoods(1);
        // 商品滚动加载
        $('.page_foodClubList').scroll(function(){
            if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !foodClubListObject.goodslock){
                foodClubListObject.goodslock = true;
                var page = Math.floor($('.page_foodClubList .goods .product_list  > a').size() / 9) + 1;
                if(page > foodClubListObject.page && $('.page_foodClubList .goods .over').size() == 0){
                    foodClubListObject.page = page;
                    foodClubListObject.loadgoods(page);
                }
            }
        });
        script.load('plugins/scrollByJie', function(){
            /***********ajax请求页面头部bander数据**************/
            ajax('Home/Index/banner',{type:2}, function(d){
                var sol = new myScroll();
                sol.speed = 3;
                //sol.height = win.width * 0.4;
                sol.div = ".page_foodClubList .bodyHead";
                for(var i in d){
                    sol.src.push(d[i].path.pathFormat());
                    sol.link.push(d[i].url);
                }
                sol.start();
            }, 2);
        });

        //加载私房菜列表
        ajax('Goods/Tips/getlist', {post:{category:'-1'}}, function(d){
            if(d.info){
                $.alert(d.info, 'error');
            }
            if(d.length>0){
                var code = '';
                for(var i in d){
                    code += '<a href="javascript:jump(\'daRen\', {member_id:'+ d[i].member_id +'});" class="item">';
                    code += '	<img src="'+ d[i].mainpic +'">';
                    code += '	<div class="bottom">';
                    code += '		<div class="t">'+ d[i].title +'</div>';
                    code += '		<div class="b">';
                    if(d[i].buy_status != 2)
                        code += '		<span class="left pro_time">'+ d[i].start_time.timeFormat('m-d W H:i') +'</span>';
                    else
                        code += '		<span class="left pro_num">适合:'+ d[i].min_num +'-'+ d[i].restrict_num +'/人</span>';
                    code += '		<span class="pro_adress">'+ (d[i].simpleaddress ? d[i].simpleaddress : d[i].address) +'</span>';
                    code += '			<div class="price">'+ parseFloat(d[i].price).priceFormat() +'<small>/位</small></div>';
                    code += '		</div>';
                    code += '	</div>';
                    if(d[i].isCollect)
                        code += '   <button data="'+ d[i].id +'" class="Collected" onclick="setCollect(this, 1)"></button>';
                    else
                        code += '   <button data="'+ d[i].id +'" onclick="setCollect(this, 1)"></button>';
                    code += '</a>';
                }

                $('.page_foodClubList .homedisk_list .list').html(code).css('width', 27 * d.length + 'rem');
                var box = $('.page_foodClubList .homedisk_list .plist');
                box.on('touchstart', function(event){
                    var ev = event.originalEvent.targetTouches;
                    //判断触摸数量
                    if(ev.length == 1){
                        //拖动处理
                        foodClubListObject.itemMoveRange = box.scrollLeft();
                        foodClubListObject.touchLeft = ev[0].pageX;
                    }
                });
                box.on('touchmove', function(event){
                    var ev = event.originalEvent.targetTouches;
                    if(ev.length == 1){
                        //拖动处理
                        ev = ev[0];
                        var x = ev.pageX;
                        var left = foodClubListObject.itemMoveRange - x + foodClubListObject.touchLeft;
                        box.scrollLeft(left);
                    }
                });
            }else{
                $('.homedisk_list').hide();
            }
        }, 2);
        //加载厨房+列表
        ajax('Home/Space/getList', {}, function(d){
            if(d.info){
                $.alert(d.info, 'error');
            }
            if(d.length>0){
                var code = '';
                for(var i in d){
                    code += '<a href="javascript:jump(\'kitchenDetail\', {space_id:\''+d[i].id+'\'});" class="item">';
                    code += '	<img src="'+ d[i].path +'">';
                    code += '<div class="category">'+d[i].category_name+'</div>';
                    code += '<div class="icons">';
                    for(var j in d[i].facility){
                        code += '<span><img src="images/kitchen_List'+d[i].facility[j]+'.png"/></span>';
                    }
                    code += '</div>';
                    code += '	<div class="bottom">';
                    code += '		<div class="t">';
                    code += '           <span class="title">'+ d[i].name +'</span>';
                    code += '           <span class="size">'+ d[i].proportion +'平米·'+d[i].volume+'人</span>';
                    code +=         '</div>';
                    code += '		<div class="b">';
                    code += '		<span class="left">'+ d[i].address +'</span>';
                    code += '		</div>';
                    code += '	</div>';
                    code += '</a>';
                }

                $('.page_foodClubList .kitchen_list .list').html(code).css('width', 26 * d.length + 'rem');
                var box = $('.page_foodClubList .kitchen_list .plist');
                box.on('touchstart', function(event){
                    var ev = event.originalEvent.targetTouches;
                    //判断触摸数量
                    if(ev.length == 1){
                        //拖动处理
                        foodClubListObject.itemMoveRange = box.scrollLeft();
                        foodClubListObject.touchLeft = ev[0].pageX;
                    }
                });
                box.on('touchmove', function(event){
                    var ev = event.originalEvent.targetTouches;
                    if(ev.length == 1){
                        //拖动处理
                        ev = ev[0];
                        var x = ev.pageX;
                        var left = foodClubListObject.itemMoveRange - x + foodClubListObject.touchLeft;
                        box.scrollLeft(left);
                    }
                });
            }else{
                $('.kitchen_list').hide();
            }
        }, 2);
    }
};

