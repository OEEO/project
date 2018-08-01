var orderRaiseDetailObject = {
    order_id : null,
    check_code : [],
    num : 0,
    number:1,//二维码序号
    num_i:0,//二维码总数
    time : Math.round((new Date()).getTime() / 1000),
    //返回
    back : function(){
        page.back(function(){
            orderRaiseDetailObject.loadOrder(1);
        });
    },
    shareSuccess: function(item_id) {
        return function(target) {
            ajax('Home/Index/shareSuccess', {type: 3, item_id: item_id, target: target, platform: 1}, function(d) {
                if (d.status == 1) {
                    console.log('分享成功');
                } else {
                    console.error(d.info);
                }
            });
        };
    },
    onload : function(){
        if(!member){
            $.alert('非法访问', function(){
                page.back();
            }, 'error');
            return;
        }

        orderRaiseDetailObject.order_id = win.get.order_id

        ajax('Member/Order/getDetail', {order_id : orderRaiseDetailObject.order_id}, function(d){
            var $em = $('.page_orderRaiseDetail');
            //var status = ['待付款', '众筹中','众筹中', '回报', '完成', '退款中', '已退款', '已取消'];
            //当前的状态 0：未支付 1：已支付 4：完成 5：退款 6：取消,0,1,2,3=@"众筹中。4=@“众筹完成”。5，6，7，8=@“众筹失败”
            var statu = '';
            //分享绑定
            var desc = d.content;
            var url = win.host + '?page=choice-raiseDetail&raise_id='+d.id;
            if(member && member.invitecode){
                url += '&invitecode=' + member.invitecode;
            }
            share(d.title, desc, url, d.path, orderRaiseDetailObject.shareSuccess(d.id));
            if(d.act_status == 0){
                // $em.find('.orderOperation').text('取消订单').click(function(){
                //     ajax('Member/Order/cancel', {order_id : orderRaiseDetailObject.order_id}, function(d){
                //         if(d.status == 1){
                //             $.alert('成功取消订单', function(){
                //                 page.reload();
                //             });
                //         }else{
                //             $.alert(d.info, 'error');
                //         }
                //     });
                // });

                $('.page_orderRaiseDetail.orderBottom').text('立即付款').click(function(){
                    if(win.get.android){
                        window.location.href = 'http://' + DOMAIN + '/order/pay/submitAlipay.do?token='+ win.token +'&order_id=' + orderRaiseDetailObject.order_id;
                    }else{
                        wxpay(orderRaiseDetailObject.order_id,d.step == 5 ? d.retainage : d.pay_price,d.limit_time,2);
                    }

                });

            }else if(d.act_status == 1 || d.act_status == 2){

                $em.find('.orderOperation').empty();
                if(d.step == 3){
                    $('.page_orderRaiseDetail.orderBottom').html('<span class="sharefriend">邀请好友</span><i>|</i><span class="sendinvite">支付尾款</span>');

                    $('.page_orderRaiseDetail.orderBottom .sendinvite').click(function(){
                        jump('orderRaiseDetail',{order_id:d.order_cid});
                    });

                }else{
                    $('.page_orderRaiseDetail.orderBottom').html('邀请好友');
                }

                if (d.end_time <= orderRaiseDetailObject.time) {
                    $('.page_orderRaiseDetail.orderBottom').addClass('disabled');
                    $('.page_orderRaiseDetail.orderBottom .sharefriend').addClass('disabled');
                } else {

                    $('.page_orderRaiseDetai.orderBottom').click(function(){
                        showShareBox();
                    });

                    $('.page_orderRaiseDetail.orderBottom .sharefriend').click(function(){
                        showShareBox();
                    });
                }

            }else{
                $('.footer.page_orderRaiseDetail.orderBottom').remove();
            }

            var total = ((d.totaled/d.total)*100).toFixed(1);
            if(d.totaled == 0 || (total.split('.')[0] <=0 && total.split('.')[1] == 0)){total = 0;}
            var days = '';
            if(parseInt(d.end_time) < orderRaiseDetailObject.time){
                days = '已结束';
            }else if(parseInt(d.start_time) > orderRaiseDetailObject.time){
                days = '未开始';
            }else{
                var t = parseInt(d.end_time) - orderRaiseDetailObject.time;
                days = '剩余<font style="font-weight: 700;">' + Math.ceil(t / 24 / 3600) + '</font>天';
            }
            $('.page_orderRaiseDetail [name="totaled"]').text(d.totaled);
            $('.page_orderRaiseDetail [name="total"]').text(d.total);
            $('.page_orderRaiseDetail [name="percent"]').text(total);
            $('.page_orderRaiseDetail [name="sum"]').text(d.count);
            $('.page_orderRaiseDetail [name="days"]').html(days);

            if(total >= 100){
                $('.page_orderRaiseDetail .c_list .c_line span').css('width','100%');
                $('.page_orderRaiseDetail .c_list .shell .stone').css('left','94%');
            }else{
                $('.page_orderRaiseDetail .c_list .c_line span').css('width',total+'%');
                if(total > 4){
                    $('.page_orderRaiseDetail .c_list .shell .stone').css('left',(total - 6) +'%');
                }
            }

            //商品信息
            switch(parseInt(d.act_status)){
                case 0:
                    statu = '待付款';
                    break;
                case 1:
                case 2:
                case 3:
                    statu = '已付款';
                    break;
                case 4:
                    statu = '众筹成功';
                    break;
                case 5:
                case 8:
                    statu = '退款中';
                    break;
                case 6:
                    statu = '退款成功';
                    break;
                case 7:
                    statu = '已取消';
                    break;
            }
            $('<img>').attr('src', d.path.pathFormat()).appendTo($em.find('.orderListLeft')).click(function(){
                jump('raiseDetail',{raise_id:d.id});
            });
            $em.find('.orderListRight .orderTitle').text(d.title);
            $em.find('.orderListRight .total font').text(d.pay_price);
            $em.find('.orderListRight .status').text(statu);
            $em.find('.orderListRight .subot').text(d.raise_times_title);

            var code = '';
            code += '<li><font>订单编号</font><span>'+ d.sn +'</span></li>';
            code += '<li><font>下单时间</font><span>'+ d.create_time.timeFormat('Y-m-d H:i:s') +'</span></li>';
            code += '<li><font>微信号</font><span>'+ d.weixincode +'</span></li>';
            if(d.invite_nickname != ''){
                code += '<li><font>邀请人</font><span>'+ d.invite_nickname +'</span></li>';
            }
            code += '<li><font>收货地址</font><span>'+ d.address +'</span></li>';
            $em.find('.orderDetail').html(code);
            if(d.step == 0){
                $('.page_orderRaiseDetail .stage').remove();
            }else if(d.step == 1 || d.step == 2 || d.step == 3 || d.step == 4){
                $('.page_orderRaiseDetail .stage .stageitem li').removeClass('having');
                $('.page_orderRaiseDetail .stage .stageitem li:first-child').addClass('having');
                if(d.step == 1){
                    console.log(d.step);
                    $('.page_orderRaiseDetail .stage .stageitem li .stage-one .state').text('(未开始)');
                    $('.page_orderRaiseDetail .stage .stageitem li.having .stage-one .state').text('(进行中)');
                }else if(d.step == 2){
                    $('.page_orderRaiseDetail .stage .stageitem li .stage-one .state').text('(未开始)');
                    $('.page_orderRaiseDetail .stage .stageitem li.having .stage-one .state').text('(已完成)');
                }else if(d.step == 3){
                    $('.page_orderRaiseDetail .stage .stageitem li .stage-one .state').text('(进行中)');
                    $('.page_orderRaiseDetail .stage .stageitem li.having .stage-one .state').text('(已完成)');
                }else if(d.step == 4){
                    $('.page_orderRaiseDetail .stage .stageitem li .stage-one .state').text('(已完成)');
                    $('.page_orderRaiseDetail .stage .stageitem li.having .stage-one .state').text('(已完成)');
                }
                $('.page_orderRaiseDetail .stage .stageitem li .subitem .subscription').text('尾款');
                $('.page_orderRaiseDetail .stage .stageitem li.having .subitem .subscription').text('预约金');
                $('.page_orderRaiseDetail .stage .stageitem li .subitem .subtotal font').text(d.retainage);
                $('.page_orderRaiseDetail .stage .stageitem li.having .subitem .subtotal font').text(d.prepay);
            }else{
                //第二阶段未付款
                $('.page_orderRaiseDetail .stage .stageitem li').removeClass('having');
                $('.page_orderRaiseDetail .stage .stageitem li:last-child').addClass('having');
                $('.page_orderRaiseDetail .stage .stageitem li .stage-one .state').text('(已完成)');
                $('.page_orderRaiseDetail .stage .stageitem li .subitem .subscription').text('预约金');
                $('.page_orderRaiseDetail .stage .stageitem li.having .subitem .subscription').text('尾款');
                $('.page_orderRaiseDetail .stage .stageitem li .subitem .subtotal font').text(d.prepay);
                $('.page_orderRaiseDetail .stage .stageitem li.having .subitem .subtotal font').text(d.retainage);
                if(d.step == 5){
                    $('.page_orderRaiseDetail .stage .stageitem li.having .stage-one .state').text('(进行中)');
                }else{
                    $('.page_orderRaiseDetail .stage .stageitem li.having .stage-one .state').text('(已完成)');
                }
            }

            if(d.lottery && d.lottery.lucky_num) {
                $('.lottery_detail').data('href', d.lottery.url);
                var lotteryStatus = {
                    '-1': '未中奖',
                    '0': '未抽奖',
                    '1': '已中奖'
                };
                $('#lottery_status').text(lotteryStatus[d.lottery.lucky_status]);
                $('#code').text(d.lottery.lucky_num);
                $('.orderLottery').show();
            }

        });

        $('.lottery_detail').click(function () {
            var url = $(this).data('href');
            var page = getQueryString(url, 'page');
            var raise_id = getQueryString(url, 'raise_id');
            var times_id = getQueryString(url, 'times_id');

            page = page.split('-')[1];
            jump(page, {raise_id: raise_id, times_id: times_id});
        });
    }
};



