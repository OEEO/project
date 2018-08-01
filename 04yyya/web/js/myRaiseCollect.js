/**
 * Created by fyt on 2016/11/15.
 */
var myRaiseCollectObject={
    cancel : function(type, type_id){
        $.dialog('您确定要取消收藏此活动吗?', function(){
            ajax('Member/Follow/ChangeCollect', {type:type, type_id:type_id, operate:0}, function(d){
                if(d.status == 1){
                    $.alert('操作成功', function(){
                        page.reload();
                    });
                }else{
                    $.alert(d.info, 'error');
                }
            });
        });
    },
    load : function(page){
        var page = page||1;
        $('.page_myRaiseCollect center').show();
        ajax('member/follow/getCollectList', {get:{page:page}, post:{type:2}}, function(d){
            if(d.info){
                $.alert(d.info, 'error');
                return;
            }else{
                if(d.length > 0){
                    var code = '';
                    for(var i in d) {
                        code += '<div class="item">';
                        code += '<a href="javascript:jump(\'raiseDetail\', {raise_id:' + d[i].id + '})">';
                        code += '<div class="rtop">';
                        code += '   <img src="' + d[i].path.pathFormat() + '">';
                        if (parseInt(d[i].start_time) > Math.round((new Date()).getTime() / 1000)) {
                            code += '<span class="category">未开始</span>';
                        } else if (parseInt(d[i].end_time) < Math.round((new Date()).getTime() / 1000)) {
                            code += '<span class="category">已结束</span>';
                        } else {
                            code += '<span class="category">众筹中</span>';
                        }

                        code += '   <div class="raisetext">';
                        code += '       <p class="title">' + d[i].title + '</p>';
                        code += '       <div class="b">';
                        code += '           <span class="rname">' + d[i].nickname + '</span>';
                        // code += '           <span class="radd">广州</span>';
                        if (parseInt(d[i].end_time) < Math.round((new Date()).getTime() / 1000)) {
                            code += '           <span class="rtime">已结束</span>';
                        } else if (parseInt(d[i].start_time) > Math.round((new Date()).getTime() / 1000)) {
                            code += '           <span class="rtime">未开始</span>';
                        } else {
                            var t = parseInt(d[i].end_time) - parseInt(d[i].start_time);
                            code += '           <span class="rtime">剩余' + Math.floor(t / 24 / 3600) + '天</span>';
                        }

                        code += '       </div>';
                        code += '   </div></div>';
                        code += '<div class="rbottom">';
                        code += '   <table><tr><td><per class="rdec">' + d[i].introduction + '</per></td></tr></table>';
                        code += '</div>';
                        code += '</a>';
                        code += '<button class="clearfix" onclick="myRaiseCollectObject.cancel(' + d[i].type + ', ' + d[i].id + ')">取消收藏</button>';
                        code += '</div>';
                    }
                }else{
                    if(page==1)
                        code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有收藏众筹！</span></div>';
                    else
                        code = '<div class="no_more"></div>';
                    $('.page_myRaiseCollect').off('scroll');
                }
                if(page == 1)
                    $('.page_myRaiseCollect .content').html(code);
                else
                    $('.page_myRaiseCollect .content').append(code);
                $('.page_myRaiseCollect center').hide();
            }
        });
    },
    cancel : function(type, type_id){
        $.dialog('您确定要取消收藏此活动吗?', function(){
            ajax('Member/Follow/ChangeCollect', {type:type, type_id:type_id, operate:0}, function(d){
                if(d.status == 1){
                    $.alert('操作成功', function(){
                        page.reload();
                    });
                }else{
                    $.alert(d.info, 'error');
                }
            });
        });
    },
    onload : function(){
        // /*屏幕滚动事件*/
        // $('.page_myRaiseCollect.wrapper').scroll(function(){
        //     if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !myRaiseCollectObject.winScrollSock){
        //         myRaiseCollectObject.winScrollSock = true;
        //         myRaiseCollectObject.load(Math.ceil($('.page_myRaiseCollect .content>a').size() / 10 + 1));
        //     }
        // });
        //
        // myRaiseCollectObject.load();
    },
    onshow:function(){
        $('.page_myRaiseCollect .content').empty();
        /*屏幕滚动事件*/
        $('.page_myRaiseCollect.wrapper').on('scroll', function(){
            var pagenum = Math.ceil($('.page_myRaiseCollect .content>a').length / 10) + 1;
            if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10
                && $('.page_myRaiseCollect center:visible').length == 0){
                myRaiseCollectObject.load(pagenum);
            }
        });
        myRaiseCollectObject.load();
    }
};
