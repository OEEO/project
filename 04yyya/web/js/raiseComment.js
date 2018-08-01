/**
 * Created by fyt on 2016/10/19.
 */
var raiseCommentObject = {
    raise_id: null,
    winScrollSock : false,
    showsock : false,
    comment_page : 1,
    feedback_id : '',
    modify_text : '',
    reply:function(em,id){
        if(id){
            raiseCommentObject.showsock = true;
        }
        if(typeof($(em).attr('an')) != "undefined"){
            $('.page_raiseComment .commentask .ask').val($(em).attr('an'));
            raiseCommentObject.modify_text = $(this).attr('an');
        }else{
            $('.page_raiseComment .commentask .ask').val('');
            raiseCommentObject.modify_text = '';
        }
        raiseCommentObject.feedback_id = id || '';
        $('.page_raiseComment .commentbtn').hide();
        $('.page_raiseComment .commentask').show();
        $('.page_raiseComment .commentask .ask').focus();
    },
    myfocu:function(){
        var top = $('.page_raiseComment.wrapper').get(0).scrollHeight - $('.page_raiseComment.wrapper').height();
        $('.page_raiseComment.wrapper').scrollTop(top);
    },
    // 加载列表
    roadList:function(page){
        if($('.page_raiseComment center').size() > 0)return;
        var page = page || raiseCommentObject.comment_page;
        ajax('Home/Feedback/getfeedlist', {get:{page:page},post:{raise_id:raiseCommentObject.raise_id}}, function(d){
            if(d.info){
                $.alert(d.info, 'error');
                return;
            }else{
                var code = '';
                for (var i in d.list) {
                    code += '<div class="item clearfix">';
                    if(d.list[i].path != ''){
                        code += '    <div class="headpic"><img src="' + d.list[i].path + '"/></div>';
                    }else{
                        code += '    <div class="headpic"><img src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg"/></div>';
                    }
                    code += '    <div class="right">';
                    code += '       <div class="comment-title">';
                    code += '           <span class="nickname">' + d.list[i].nickname + '</span>';
                    code += '           <span class="ctime">' + d.list[i].datetime + '</span>';
                    code += '       </div>';
                    code += '       <div class="context">' + d.list[i].content + '</div>';
                    if(d.is_reply != 0) {
                        if (d.list[i].answer != '') {
                            code += '       <p class="reply"><font>'+d.list[i].answer_nickname+'</font> 回复 <font>' + d.list[i].nickname + '</font></p>';
                            code += '       <p class="reply-text">' + d.list[i].answer + '</p>';
                            code += '<div class="delicon" an = '+d.list[i].answer+' onclick="raiseCommentObject.reply(this,'+d.list[i].id+')"><i></i>修改</div>';
                        }else{
                            code += '<div class="replyicon" onclick="raiseCommentObject.reply(this,'+d.list[i].id+')"><i></i>回复</div>';
                        }
                    }else{
                        if (d.list[i].answer != '') {
                            code += '       <p class="reply"><font>' + d.list[i].answer_nickname + '</font> 回复 <font>' + d.list[i].nickname + '</font></p>';
                            code += '       <p class="reply-text">' + d.list[i].answer + '</p>';
                        }
                    }
                    code += '   </div>';
                    code += '</div>';
                }
                if(code == ''){
                    if(page == 1){
                        $('.page_raiseComment .commentlist').append('<div class="no_msgs"><img src="images/comments.png" /><span>暂时无评论！</span></div>');
                    }
                } else {
                    if(page == 1){
                        $('.page_raiseComment .commentlist').html(code);
                    }else{
                        $('.page_raiseComment .commentlist').append(code);
                    }
                    raiseCommentObject.winScrollSock = false;
                }
            }
        }, 2);

    },
    // 提交问题
    submitq:function(){
        var data = {};
        data.content= $('.page_raiseComment .commentask .ask').val();
        data.feedback_id = raiseCommentObject.feedback_id;
        data.raise_id = raiseCommentObject.raise_id;
        ajax('Home/Feedback/submit', data, function(d){
            if(d.status == 1){
                $.alert(d.info, function () {
                    page.reload();
                });
            }else{
                $.alert(d.info, 'error');
            }
        }, 2);
    },
    onload : function(){
        raiseCommentObject.raise_id = win.get.raise_id;
        //判断是否登录，没有登录则跳转登录
        if(!member){
            win.login();
            return;
        }
        $('.page_raiseComment.wrapper').click(function () {
            // $.alert(raiseCommentObject.showsock);
            if(raiseCommentObject.showsock == false){
                raiseCommentObject.feedback_id = '';
                raiseCommentObject.modify_text = '';
                $('.page_raiseComment .commentbtn').show();
                $('.page_raiseComment .commentask').hide();
            }else{
                $('.page_raiseComment .commentbtn').hide();
                $('.page_raiseComment .commentask').show();
                $('.page_raiseComment .commentask .ask').focus();
                raiseCommentObject.showsock = false;
            }
        });

        $('.page_raiseComment.wrapper').scroll(function(){
            //滚动加载内容
            if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !raiseCommentObject.winScrollSock){
                raiseCommentObject.winScrollSock = true;
                raiseCommentObject.roadList(Math.ceil($('.page_raiseComment .commentlist .item').size() / 5 + 1));
            }
        });
        raiseCommentObject.roadList();
    }
};
