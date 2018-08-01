/**
 * Created by fyt on 2017/2/18.
 */
var raiseInviteObject = {
    invitecode:null,
    raise_id:null,
    onload : function(){
        raiseInviteObject.raise_id = win.get.raise_id;
        raiseInviteObject.invitecode = win.invitecode;
        ajax('Home/Index/getEnjoy', {get:{token:win.token},post:{type:2,type_id:raiseInviteObject.raise_id,code:raiseInviteObject.invitecode}}, function(d){
            if(d.info){
                $.alert(d.info,'error');
                return;
            }else{
                var url = win.host + '?page=raiseInvite&'+'raise_id='+raiseInviteObject.raise_id+'&invitecode='+raiseInviteObject.invitecode;
                share(d.list.title, d.list.introduction, url, d.list.path);
                $('.page_raiseInvite .headpic').attr('src',d.member.path);
                $('.page_raiseInvite .nickname').html(d.member.nickname);
                $('.page_raiseInvite .title').html(d.list.title);
            }
        },2);
    },

};
