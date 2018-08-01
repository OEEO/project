/**
 * Created by fyt on 2016/10/26.
 */
var invitationObject = {
    tips_id:null,
    onload : function(){
        invitationObject.tips_id = win.get.tips_id;
        ajax('Goods/Tips/invitation', {'tips_id':invitationObject.tips_id}, function(d){
            if(d.info){
                $.alert(d.info, 'error');
                return;
            }
            $('.page_invitation .invitation .inviter img').attr('src', d.head_path);
            $('.page_invitation .invitation .inviter .nick').html(d.nickname);
            if(window.location.search.indexOf('type') > 0){
                $('.page_invitation .invitation .content .enter').html('我发现了<font>'+d.start_time+'</font>的');
            }else{
                $('.page_invitation .invitation .content .enter').html('我报名参加了<font>'+d.start_time+'</font>的');
            }
            $('.page_invitation .invitation .content .title').html(d.title);
            $('.page_invitation .invitation .content .a_img').attr('src',d.tips_path);
        },2);
    }
};