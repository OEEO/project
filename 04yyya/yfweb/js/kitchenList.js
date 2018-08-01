var kitchenListObject = {
    lock : false,
    page : 1,
    loadKitchen : function(page){
        var data = {get:{page:page}};
        ajax('Home/Space/getList',data,function(d){
            var code = '';
            if(d.length > 0){
                for(var i in d){
                    code += '<a href="javascript:jump(\'kitchenDetail\', {id:\''+d[i].id+'\'});">';
                    code += '<img class="bg_img" src="'+ d[i].path+'"/>';
                    code += '<div class="mask"></div>';
                    code += '<div class="category">'+d[i].category_name+'</div>';
                    code += '<div class="title">'+d[i].name+'</div>';
                    code += '<div class="icons">';
                    for(var j in d[i].facility){
                        code += '<span><img src="images/kitchen_List'+d[i].facility[j]+'.png"/></span>';
                    }
                    code += '</div>';
                    code += '<div class="bottom"><font class="size">'+d[i].proportion+'</font>㎡ · <font class="num">'+d[i].volume+'</font>人 <font class="area">'+d[i].area_name+'</font> <font class="address">'+d[i].address+'</font></div>';
                    code += '<div class="but">预定</div>';
                    code += '</a>';
                }
                $('.page_kitchenList .content').append(code);
            }else{
                code += '<center>抱歉！没有相关的数据！</center>';
                $('.page_kitchenList .content').append(code);
            }
            kitchenListObject.locked = false
        });
    },
    onload : function(){
        kitchenListObject.loadKitchen(1);

        $('.page_kitchenList').scroll(function(){
            if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !kitchenListObject.locked){
                kitchenListObject.locked = true;
                var page = Math.floor($('.page_kitchenList .content > a').size() / 5) + 1;
                if(page > kitchenListObject.page && $('.page_kitchenList .content .over').size() == 0){
                    kitchenListObject.page = page;
                    kitchenListObject.loadKitchen(page);
                }
            }
        });
    }
};