var groupsDetailObject = {
    surplus_num : null,
    groups_id : null,//开团id
    type_id: null, // 商品id或活动id
    times : 0,
    type: 1,
    time : Math.round((new Date()).getTime() / 1000),
    goshare:function(){
        $('#fixed,.page_groupsDetail.groups_success').hide();
        showShareBox();
    },
    rules:function(n){
        if(n == 1){
            $('#fixed,.page_groupsDetail.rulebox').show();
        }else{
            $('#fixed,.page_groupsDetail.rulebox').hide();
        }
    },
    hideGroupsSuccess: function () {
        $('#fixed,.page_groupsDetail.groups_success').hide();
    },
    DecTimes:function(){
        clearInterval(win.groupsDetailInterval);
        win.groupsDetailInterval = setInterval(function(){
            try {
                // var days = Math.floor(groupsDetailObject.times / 24 / 3600);
                var hours = Math.floor(groupsDetailObject.times % (24 * 3600) / 3600);
                var mins = Math.floor((groupsDetailObject.times % (24 * 3600) % 3600) / 60);
                var secs = Math.floor((groupsDetailObject.times % (24 * 3600) % 3600) % 60);

                var str = '';
                if(hours > 0){
                    if(parseInt(hours) < 10){
                        str += '<font>0'+hours + '</font><font class="dian">：</font>';
                    }else{
                        str += '<font>'+hours+ '</font><font class="dian">：</font>';
                    }
                }else{
                    str += '<font>00</font><font class="dian">：</font>';
                }
                if(hours > 0 || mins > 0){
                    if(parseInt(mins) < 10){
                        str += '<font>0'+mins + '</font><font class="dian">：</font>';
                    }else{
                        str += '<font>'+mins + '</font><font class="dian">：</font>';
                    }
                }else{
                    str += '<font>00</font><font class="dian">：</font>';
                }
                if(hours > 0 || mins > 0 || secs > 0){
                    if(parseInt(secs) < 10){
                        str += '<font>0'+secs + '</font>';
                    }else{
                        str += '<font>'+secs + '</font>';
                    }
                    $('.page_groupsDetail .groupsbox .endtime').html('剩余'+str+'结束');
                    groupsDetailObject.times --;
                } else {
                    $('.page_groupsDetail .groupsbox .endtime').remove();
                    clearInterval(win.groupsDetailInterval);
                    page.reload();
                }
            }catch(e){
                clearInterval(win.groupsDetailInterval);
            }
        }, 1000);
    },
    loadGoodsDetail: function (id) {
        ajax('Goods/Goods/getDetail', {goods_id: id}, function (d) {
            if (d.title) {
                $('.page_groupsDetail #goods_detail').show();
                //达人信息
                //$('.page_goodsDetail .darenDetail').attr('href', 'javascript:jump(\'daRen\', {member_id:'+ d.member_id +'});');
                $('.page_groupsDetail #goods_detail .darenDetail img').attr('src', d.headpic);
                $('.page_groupsDetail #goods_detail .darenDetail .t').text(d.nickname);
                $('.page_groupsDetail #goods_detail .darenDetail .b').text(d.introduce);

                //商品规格
                var code = '';
                for(var i in d.attrs){
                    code += '<tr>';
                    code += '	<td class="left">'+ d.attrs[i].name +'</td>';
                    code += '	<td class="right">'+ d.attrs[i].value +'</td>';
                    code += '</tr>';
                }
                $('.page_groupsDetail #goods_detail .attr_list').html(code);

                $('.page_groupsDetail #goods_detail #goods_content').html(d.content);

                //贴心提示
                var code = '';
                for(var i in d.notice){
                    code += '<li>'+ d.notice[i] +'</li>';
                }
                $('.page_groupsDetail #goods_detail .tell_List').html(code);
            }
        });
    },
    createNewPiece: function () {
        if (groupsDetailObject.type == 0) {
            jump('courseDetail',{tips_id: groupsDetailObject.type_id});
        } else if (groupsDetailObject.type == 1) {
            jump('goodsDetail', {goods_id: groupsDetailObject.type_id});
        }
    },
    back: function () {
        page.back(null, function () {

        });
    },
    onload:function(){
        groupsDetailObject.type = win.get.type || groupsDetailObject.type;
        //判断是否登录，没有登录则跳转登录
        if(!member){
            win.login();
            return;
        }

        if(win.get.surplus_num){
            console.log(win.get.surplus_num);
            groupsDetailObject.surplus_num = win.get.surplus_num;
            $('#fixed,.page_groupsDetail.groups_success').show();
            $('.page_groupsDetail.groups_success .num').html(groupsDetailObject.surplus_num);
        }else{
            $('#fixed,.page_groupsDetail.groups_success').hide();
        }

        groupsDetailObject.groups_id = win.get.groups_id;

        ajax('Member/Piece/Index', {'piece_originator_id':groupsDetailObject.groups_id, 'type': groupsDetailObject.type}, function(d){
            if(d.info){
                $.alert(d.info,function(){
                    jump('choice');
                });
            }

            groupsDetailObject.loadGoodsDetail(d.piece_type_id);
            groupsDetailObject.type_id = d.piece_type_id;

            var desc = d.title;
            var url = win.host + '?page=groupsDetail&groups_id=' + groupsDetailObject.groups_id + '&invitecode=' + member.invitecode;
            share(member.nickname + '邀请你一起来拼团，'+ d.piece_count +'人参与享特惠！', desc, url, d.type_path);

            $('.page_groupsDetail .orderListLeft img').attr('src',d.type_path);
            $('.page_groupsDetail .orderListRight .orderTitle').html(d.title);
            $('.page_groupsDetail .orderListRight .t_b font').html(d.piece_price + '元');
            $('.page_groupsDetail .orderListRight .t_b #piece_count').text(d.piece_count + '人团');
            $('.page_groupsDetail .groupsbox .groups-t .g_line font').html(parseInt(d.buyer_num) + '/' + parseInt(d.piece_count));
            if(parseInt(d.buyer_num) >= parseInt(d.piece_count)){
                $('.page_groupsDetail .groupsbox .groups-t .g_line span').css('width','100%');
            }else{
                $('.page_groupsDetail .groupsbox .groups-t .g_line span').css('width',((parseInt(d.buyer_num)/parseInt(d.piece_count))*100) + '%');
            }
            //尚未结束倒计时
            if(parseInt(d.end_time) > groupsDetailObject.time || parseInt(d.piece_count) <= parseInt(d.buyer_num) || ['1','2'].indexOf(d.act_status) !== -1){
                if(parseInt(d.end_time) > groupsDetailObject.time){
                    groupsDetailObject.times = parseInt(d.end_time) - groupsDetailObject.time;
                    groupsDetailObject.DecTimes();
                }else{
                    $('.page_groupsDetail .groupsbox .endtime').remove();
                }

                if(d.is_buy == 1){
                    var num = parseInt(d.piece_count) - parseInt(d.buyer_num);
                    if(parseInt(d.buyer_num) >= parseInt(d.piece_count)){
                        $('.page_groupsDetail .groupsbox .groups-t .owe').html('已拼团成功，可继续购买');
                    }else {
                        $('.page_groupsDetail .groupsbox .groups-t .owe').html('还差<font>' + num + '</font>人成团，快去召唤小伙伴来组团吧');
                    }
                    $('.page_groupsDetail.footer .kaituan').css('background','#b39851');
                    $('.page_groupsDetail.footer .kaituan').on('click',function(){

                        if (groupsDetailObject.type == 0) {
                            jump('confirmEnrolling',{groups_id:d.piece_id,tips_id:d.piece_type_id,time_id:d.piece_type_times_id,piece_oid:d.id});
                        } else if (groupsDetailObject.type == 1) {
                            jump('confirmBuy', {goods_id: d.piece_type_id, piece: 1, piece_originator_id: d.id});
                        }
                    });
                }else{

                    $('.page_groupsDetail.footer .kaituan').css('background','#ccc');
                    $('.page_groupsDetail.footer .kaituan').off('click');
                    if (d.joiner.length >= d.piece_count) {
                        // 拼团成功
                        $('.page_groupsDetail .groupsbox .groups-t .owe').html('此团已满<span>' + d.piece_count + '</span>人');
                        $('.page_groupsDetail .piece-success').css('display', 'block')
                    } else {
                        // 拼团失败
                        $('.page_groupsDetail .groupsbox .groups-t .owe').html('还差<span>' + (d.piece_count - d.joiner.length) + '</span>人成团，快去召唤小伙伴来拼团吧');
                    }
                }

                if ((d.act_status === '1' || d.act_status === '2') && d.self_member_id === d.member_id) {
                    $('.footer.page_groupsDetail').html('<a href="javascript:void(0)" class="groupshare" onclick="groupsDetailObject.goshare()">邀请好友参团</a>');
                }

                if (d.act_status !== '0' && d.act_status !== '1') {
                    $('.page_groupsDetail #piece-success_member_avatar').attr('src', d.joiner[0].joiner_path || 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg');
                    $('.page_groupsDetail .piece-success_member .main-title').text('我是团长：' + d.joiner[0].nickname);
                    $('.page_groupsDetail .piece-success_member').show();
                }
            }else{
                // $('.page_groupsDetail .groupsbox .groups-t .owe').html('时间已到，拼团失败');
                $('.page_groupsDetail .groupsbox .endtime').remove();
                $('.page_groupsDetail.footer .kaituan').css('background','#ccc');
                $('.page_groupsDetail.footer .kaituan').off('click');

                // 失败信息显示
                $('.page_groupsDetail #piece-success_member_avatar').attr('src', d.joiner[0].joiner_path || 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg');
                $('.page_groupsDetail .piece-success_member .main-title').text('我是团长：' + d.joiner[0].nickname);
                $('.page_groupsDetail .piece-success_member').show();
                $('.page_groupsDetail .groupsbox .groups-t .owe').html('还差<span>' + (d.piece_count - d.joiner.length) + '</span>人成团，快去召唤小伙伴来拼团吧');
            }
            if(d.joiner.length > 0){
                var code = '';
                var code2 = '';
                for(var i = 0, num = d.joiner.length; i < num; i++){
                    code += '<div class="groups-b">';
                    if(d.joiner[i].joiner_path == ''){
                        code += '    <div class="headimg"><img src="http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg"/></div>';
                        i != 0
                            ? (code2 += '<div class="group-imgs-item" style="background: transparent url(http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg) no-repeat 100% / 100%"></div>')
                            : (code2 += '<div class="group-imgs-item active" style="background: transparent url(http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg) no-repeat 100% / 100%"></div>');

                    }else{
                        code += '    <div class="headimg"><img src="'+d.joiner[i].joiner_path+'"/></div>';
                        i != 0
                            ? (code2 += '<div class="group-imgs-item" style="background: transparent url(' + d.joiner[i].joiner_path + ') no-repeat 100% / 100%"></div>')
                            : (code2 += '<div class="group-imgs-item active" style="background: transparent url(' + d.joiner[i].joiner_path + ') no-repeat 100% / 100%"></div>');
                    }
                    code += '    <div class="headinfo">';
                    if(i == 0){
                        code += '<div class="headinfo">' +
                                    '<div class="sub-headinfo">' +
                                        '<p>团长</p>' +
                                        '<p class="text-primary">' + d.joiner[i].nickname + '</p>' +
                                    '</div>' +
                                    '<div class="text-grey">' + d.joiner[i].create_time + '</div>' +
                                    '<div class="text-danger">开团</div>' +
                                '</div>';
                    }else {
                        code += '<div class="headinfo">' +
                            '<div class="sub-headinfo">' +
                            '<p class="text-primary">' + d.joiner[i].nickname + '</p>' +
                            '</div>' +
                            '<div class="text-grey">' + d.joiner[i].create_time + '</div>' +
                            '<div>参团</div>' +
                            '</div>';
                    }
                    code += '   </div>';
                    code += '</div>';
                }
                $('.page_groupsDetail .groups_list').html(code);
                $('.page_groupsDetail .group-imgs').html(code2);
            }
        }, 2);
    },
    onshow : function(){
        $('.page_groupsDetail.rulebox,.page_groupsDetail.groups_success').hide();
    }
};
