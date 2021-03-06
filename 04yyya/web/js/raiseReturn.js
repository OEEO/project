/**
 * Created by fyt on 2016/10/8.
 */
var raiseReturnObject = {
    raise_id: null,
    d: null,
    time : Math.round((new Date()).getTime() / 1000),
    //分享
    share: function (title, desc, link, imgUrl) {
        share(title, desc, link, imgUrl);
    },
    target_times_id: -1,
    member_privilege_id: null,
    member_privilege_map:null,
    submitbtn:function(em){
        var data = {};
        data.raise_id = raiseReturnObject.raise_id;
        data.times_id = $(em).attr('times_id');
        if (raiseReturnObject.member_privilege_map) {
            data.id = raiseReturnObject.member_privilege_map[''+data.times_id];
        }
        jump('raisePay', data);
    },
    shareRaise: function(em, d) {
        var raise_times_id = $(em).attr('times_id');
        // ajax('Goods/Raise/raiseShareSuccess', {'raise_times_id': raise_times_id}, function(d) {
        //     console.log(d);
        //     $.alert('分享成功');
        // });
        raiseReturnObject.target_times_id = $(em).attr('times_id');

        if (Yami.platform() === 'android') {
            // android
            Yami.share({
                "platform": 0,
                "title": raiseReturnObject.d.title,
                "imgUrl": raiseReturnObject.d.img,
                "link": raiseReturnObject.d.url,
                "desc": raiseReturnObject.d.desc,
                'success': function () {
                    ajax('Goods/Raise/raiseShareSuccess', {'raise_times_id': raise_times_id}, function(d) {
                        if (d.status == '1') {
                            $.alert('分享成功');
                            jump('orderRaiseDetail', {'order_id': +d.info});
                        } else {
                            $.alert(d.info);
                        }
                    });
                },
                'fail': function () {
                    $.alert('分享失败');
                }
            });
        } else {
            location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.yummy.dis'; // 应用宝下载连接
        }
    },
    shareSuccess: function(item_id) {
        return function(target) {
            ajax('Home/Index/shareSuccess', {type: 3, item_id: item_id, target: target, platform: 0}, function(d) {
                if (d.status == 1) {
                    console.log('分享成功');
                } else {
                    console.error(d.info);
                }
            });
        };
    },
    shareRaiseReturnFail: function () {
        $.alert('分享失败');
    },
    shareRaiseReturnCancel: function () {},
    getLastTime: function (current, end, start, format, successed) {
        if (current < start) {
//            return '即将上线';
            return '预约中';
        }
        var timediff = +end - +current;

        if (timediff <= 0 && successed >= 0) {
            return '已成功';
        } else if (timediff <= 0) {
            return '未成功';
        }

        var _f = {
            'd+': Math.floor(timediff / 86400),
            'h+': Math.floor(timediff % 86400 / 3600),
            'm+': Math.floor(timediff % 86400 % 3600 / 60),
            's+': Math.floor(timediff % 86400 % 60)
        };

        format = format || '';

        if (format.length > 0) {
            for (var k in _f) {
                if (new RegExp('(' + k + ')').test(format)) format = format.replace(RegExp.$1, (RegExp.$1.length === 1) ? (_f[k]) : (('00' + _f[k]).substr(('' + _f[k]).length)));
            }
        } else if (_f['d+'] > 0) {
            format = '剩余' + (_f['d+'] + 1) + '天';
        } else if (_f['h+'] > 0) {
            format = '剩余' + _f['h+'] + '小时'
        } else if (_f['m+'] > 0) {
            format = '剩余' + _f['m+'] + '分钟'
        } else if (_f['s+'] > 0) {
            format = '剩余' + _f['s+'] + '秒'
        }

        return format;
    },

    test:function() {
        t_statu = 'normal';
        ajax('Member/Order/findTemporaryTel',{},function (d) {
            //console.log(d);
           // console.log(d.mem_tel);
            for(i in d.tel) {
               // console.log(d.tel[i].tel);
                if (d.mem_tel == d.tel[i].tel) {

                    ajax('Member/Order/findTemporaryBuy',{'raise_id':raiseReturnObject.raise_id},function(d) {
//                        console.log(d.raise_id);
                    });

                    t_statu = 'tem';
//                   console.log(t_statu);
                }
            }
            ajax('Goods/Raise/getDetail', {'raise_id':raiseReturnObject.raise_id,t_statu:t_statu}, function(d){
           //     console.log(d.times);
                if(d.info){
                    $.alert(d.info, 'error');
                    return;
                }
                console.log(d);
                // var total = Math.round((d.totaled/d.total)*100);
                var total = ((d.totaled/d.total)*100).toFixed(1);
                if(d.totaled == 0 || (total.split('.')[0] <=0 && total.split('.')[1] == 0)){total = 0;}
                 var days = raiseReturnObject.getLastTime(raiseReturnObject.time, d.end_time, d.start_time, null, d.totaled - d.total);
                //var days = raiseReturnObject.getLastTime(raiseReturnObject.time, d.end_time, d.start_time);
                // if(parseInt(d.end_time) < raiseReturnObject.time){
                //     days = '已结束';
                // }else if(parseInt(d.start_time) > raiseReturnObject.time){
                //     days = '未开始';
                // }else{
                //     var t = parseInt(d.end_time) - raiseReturnObject.time;
                //     days = '剩余<font style="font-weight: 700;">'+Math.ceil(t / 24 / 3600) + '</font>天';
                // }
                raiseReturnObject.member_privilege_map=d.tips_privilege;
                $('.page_raiseReturn [name="totaled"]').text(d.totaled);
                $('.page_raiseReturn [name="total"]').text(d.total);
                $('.page_raiseReturn [name="percent"]').text(total);
                $('.page_raiseReturn [name="sum"]').text(d.sum);
                $('.page_raiseReturn [name="days"]').html(days);
                $('.page_raiseReturn .c_boxcontext [name="end_time"]').text(d.end_time.timeFormat("Y-m-d H:i:s"));
                var code = '';
                if(d.times.length > 0){
                    var num= '';
                    for(var i in d.times){
                        // if(!d.tips_privilege[d.times[i].times_id+'']){
                        //     continue;
                        // }
                        code += '<div class="c_block"></div>';
                        code += '<div class="cr_list">';
                        code += '    <div class="l_title">';
                        if(d.times[i].is_free === '0' && d.times[i].prepay <= 0){
                            code += '       <span class="money">'+d.times[i].price+'</span>';
                        }else if(d.times[i].is_free === '0'){
                            code += '       <span class="money">'+d.times[i].prepay+'<small>(预约金)</small></span>';
                        } else if(d.times[i].is_free === '1') {
                            code += '<div class="lottery_title"><span class="red">App专属</span><span class="title" data-lottery>抽奖福利</span></div>';
                        }

                        if(d.times[i].stock == 0){
                            num = '已售罄';
                        }else if(d.times[i].stock == -1){
                            num = '无限制';
                        }else{
                            num = '剩余名额'+parseInt(d.times[i].stock)+'人';
                        }
                        if (d.times[i].is_free === '0') {
                            /*
                            if (d.times[i].times_id == '507') {
                                d.times[i].count = Number(d.times[i].count);
                                d.times[i].count += 5;
                                code += '       <span>认筹'+d.times[i].count+'人/'+num+'</span>';
                            } else if (d.times[i].times_id == '508') {
                                d.times[i].count = Number(d.times[i].count);
                                d.times[i].count += 12;
                                code += '       <span>认筹'+d.times[i].count+'人/'+num+'</span>';
                            } else if (d.times[i].times_id == '509') {
                                d.times[i].count = Number(d.times[i].count);
                                d.times[i].count += 10;
                                code += '       <span>认筹'+d.times[i].count+'人/'+num+'</span>';
                            } else {
                                code += '       <span>认筹'+d.times[i].count+'人/'+num+'</span>';
                            }
                            */
                            code += '       <span>认筹'+d.times[i].count+'人/'+num+'</span>';
                        } else {
                            code += '       <span>已支持'+d.times[i].count+'人/'+num+'</span>';
                        }
                        code += '    </div>';
                        code += '    <div class="l_con">';
                        code += '       <h5>'+d.times[i].title+'</h5>';
                        code += '       <pre>'+d.times[i].content+'</pre>';
                        code += '   </div>';

                        code += '   <div style="margin-bottom: 2rem; color: #aaa">*项目结束'+d.times[i].send_day+'天后发送</div>'

                        code += '   <div class="l_foot">';
                        code += '      <span class="left-text-wrap">'

                        // 按钮左边文字
                        if (d.times[i].is_free == '1' && d.times[i].type == 1) {
                            code += '<span class="lottery_rule">抽奖细则</span>';
                        }

                        // 档位
                        var startTime = +d.start_time;
                        var endTime = +d.end_time;
                        var itemStartTime = +d.times[i].start_time;
                        var itemEndTime = +d.times[i].end_time;

                        itemStartTime = itemStartTime != 0 ? itemStartTime : startTime
                        itemEndTime = itemEndTime != 0 ? itemEndTime : endTime

                        // 档位开放、结束时间与项目一致、或没设置
                        var normalGear = itemStartTime === startTime && itemEndTime === endTime
                        // 档位结束时间与项目一致
                        var endGear = itemEndTime === endTime && itemStartTime != startTime
                        // 特定开放时间
                        var specialGear = itemEndTime !== endTime

                        var nowTime = Math.round(new Date().getTime() / 1000);

                        if (itemStartTime > nowTime) {
                            // 档位开放前
                            if (endGear || specialGear) {
                                code += '<span>档位开放时间：<span class="yellow-text">'+ (itemStartTime + '').timeFormat('Y-m-d H:i') +'</span></span>';
                            }
                        } else if(itemStartTime < nowTime && nowTime < itemEndTime) {
                            // 档位开放中
                            if (specialGear && d.times[i].is_display_remain_time == 1) {
                                var getLastTime = function (endTime) {
                                    var lastSecond = endTime - Math.round(new Date().getTime() / 1000)
                                    var d = Math.floor(lastSecond / 86400)
                                    var h = Math.floor((lastSecond % 86400) / 3600) + ''
                                    var m = Math.floor((lastSecond % 3600) / 60) + ''
                                    var s = lastSecond % 60 + ''
                                    h = h.length === 2 ? h : '0' + h;
                                    m = m.length === 2 ? m : '0' + m;
                                    s = s.length === 2 ? s : '0' + s;
                                    var str = d + '天' + h + ':' + m + ':' + s;
                                    return str;
                                }

                                var lastTimeStr = getLastTime(itemEndTime);

                                code += '<span class="last-time-str-wrap">限时开放 仅剩 <span class="yellow-text last-time-str">'+ lastTimeStr +'</span></span>';
                                (function(num){
                                    var itemEndTime = +d.times[num].end_time
                                    var timer = setInterval(function () {
                                        if (itemEndTime < Math.round(new Date().getTime() / 1000)) {
                                            clearInterval(timer)

                                            var aBtn = $('.page_raiseReturn .cr_list').eq(num).find('.l_foot a');
                                            aBtn.removeAttr('onclick')
                                            aBtn.attr('href', 'javascript:$.alert(\'档位认购时间已过\',\'error\')');
                                            aBtn.css({
                                                color: '#999999',
                                                background: '#f9f9f9'
                                            });

                                            $('.page_raiseReturn .cr_list').eq(num).find('.l_foot .last-time-str-wrap').remove();
                                            return;
                                        }
                                        var lastTimeStr = getLastTime(itemEndTime);
                                        $('.page_raiseReturn .cr_list').eq(num).find('.last-time-str').text(lastTimeStr)
                                    }, 500)
                                })(i)


                            } else if (itemEndTime < nowTime && itemEndTime != 0) {
                                // 档位结束后

                            }
                        } else {
                            // 其它

                        }


                        // code += '       <span>项目结束'+d.times[i].send_day+'天后发送</span>';

                        code += '</span>'; //left-text-wrap

                        // 按钮
                        if(itemEndTime < Date.now() / 1000){
                            // code += '       <a href="javascript:void(0);" style="color: #999999;background:#f9f9f9;">已结束</a>';
                            // 档位结束后按钮
                            if (endGear || specialGear) {
                                code += '<a href="javascript:$.alert(\'档位认购时间已过\',\'error\')" style="color: #999999;background:#f9f9f9;">我要支持</a>';
                            }
                        }else if(d.start_time > (Date.now() / 1000) && (d.isPrivilege == 0 || !d.tips_privilege[d.times[i].times_id+'']) || itemStartTime > nowTime){
                            code += '       <a href="javascript:$.alert(\'该众筹尚未开放\',\'error\')" class="disabled">我要支持</a>';
                        }else{
                            if(d.times[i].is_buy == 1 && d.times[i].is_free === '0'){

                                if (d.times[i].stock == 0) {
                                    //售完
                                    var tips = '<div class="tips"><span>有名额通知我</span> <span style="font-family:\'宋体\'">&gt;</span></div>';
                                    code += '<a class="inform-user" style="color: #999999;background:#f9f9f9;" times_id=' + d.times[i].times_id + ' count=' + d.times[i].count + '>' + tips + '我要支持</a>';
                                } else {
                                    // 还有贷
                                   if(+d.times[i].limit_num > 0){
                                            //有限
                                            if(d.times[i].limit_buy_times == 0){
                                                //可购买次数为0
        //                                        code += '       <a href="javascript:$.alert(\'一个id只限支持一次哦，请分享给更多好友一起来支持吧\',\'error\')" times_id='+d.times[i].times_id+' style="border: 1px solid #ccc;color: #ccc;">我要支持</a>';
                                                code += '       <a href="javascript:$.alert(\'购买数量已超过限购数，请分享给更多好友一起来支持吧\',\'error\')" times_id='+d.times[i].times_id+' style="color: #999999;background:#f9f9f9;">我要支持</a>';
                                            }else{
                                                code += '       <a href="javascript:void(0)" onclick="raiseReturnObject.submitbtn(this);" times_id='+d.times[i].times_id+'>我要支持</a>';
                                            }
                                        }else{
                                            //无限制
                                            code += '       <a href="javascript:void(0)" onclick="raiseReturnObject.submitbtn(this);" times_id='+d.times[i].times_id+'>我要支持</a>';
                                    }
                                }
                            } else if (d.times[i].is_buy == 1 && d.times[i].is_free === '1') {
                                code += '       <a href="javascript:void(0)" onclick="raiseReturnObject.shareRaise(this);" times_id='+d.times[i].times_id+'>领取福利</a>';
                            }else{
                                code += '       <a href="javascript:$.alert(\'该类目当前时间还未开放，敬请期待\',\'error\')" times_id='+d.times[i].times_id+' style="color: #999999;background:#f9f9f9;">我要支持</a>';
                            }
                        }
                        code += '   </div>';
                        code += '</div>';
                    }
                }
                $('.page_raiseReturn .r_return .con').html(code);
                if(total >= 100){
                    $('.page_raiseReturn .c_list .c_line span').css('width','100%');
                    $('.page_raiseReturn .c_list .shell .stone').css('left','96%');
                }else{
                    $('.page_raiseReturn .c_list .c_line span').css('width',total+'%');
                    if(total > 4){
                        $('.page_raiseReturn .c_list .shell .stone').css('left',(total - 4) +'%');
                    }
                }
                //分享绑定
                var desc = d.introduction;
                var url = win.host + '?page=raiseDetail&raise_id='+d.id;
                share(d.title, desc, url, d.path, raiseReturnObject.shareSuccess(d.id));
                raiseReturnObject.d = {
                    title: d.title,
                    desc: desc,
                    url: url,
                    img: d.path
                };


                //有名额通知我
                $('.page_raiseReturn .inform-user').click(function(e) {
                    var informUserDialog = function(msg, fn, is_lock, classname, btnText){
                        is_lock = is_lock || true;
                        if(typeof(fn) != 'function')return;
                        classname = classname ||'';
                        var box = $('<div>').addClass('dialogBox');
                        var sb = $('<div>').addClass('sbox').appendTo(box);
                        var cancel_icon = $('<div>').addClass('cancel-icon').appendTo(box);
                        sb.html('<div class="context">' + msg + '</div>');
                        box.appendTo('body');
                        var h = win.width / 360 * 100;
                        if(is_lock){
                            var dialogBoxLay = $('<div>').addClass('dialogBoxLay').appendTo('body');
                        }
                        var btns = $('<div>').addClass('btns').appendTo(box);
                        // $('<button>').addClass('closeBtn').appendTo(btns).text('否');
                        var agree = $('<button>').addClass('agree').appendTo(btns).text(btnText);
                        agree.click(function(){
                            if(fn() !== false){
                                box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
                                if(is_lock)dialogBoxLay.css('opacity', 0);
                                setTimeout(function(){
                                    box.remove();
                                    if(is_lock)dialogBoxLay.remove();
                                }, 500);
                            }
                        });
                        cancel_icon.click(function() {
                            box.css({'opacity':0,'margin-top': -1 * (box.height()+h)});
                            if(is_lock)dialogBoxLay.css('opacity', 0);
                            setTimeout(function(){
                                box.remove();
                                if(is_lock)dialogBoxLay.remove();
                            }, 500);
                        });
                    };


                    var raise_times_id = $(e.currentTarget).attr('times_id');
                    var peopleNum = $(e.currentTarget).attr('count');
                    var phone = member.telephone;
                    var msg = '<div><div class="h1">名额预约</div><div class="h2">\('+ peopleNum +'人已参与\)</div><p>若有名额释放，我们会发送短信至你的手机号'+ phone +'</p></div>';
                    var callback = function () {
                        ajax('Goods/Raise/raiseRemind',{'raise_times_id':raise_times_id, 'raise_id': raiseReturnObject.raise_id}, function (d) {
                            console.log(d);
                            $.alert('预约成功');
                        });
                    };
                    informUserDialog(msg, callback, true, 'inform-user-dialog', '马上预约');
                });

                $('.page_raiseReturn .prompt').click(function(){
                    var code = '';

                    code += '<div class="prompt">';

                    code += '    <div class="prompt-title">风险提示</div>';

                    code += '    <div class="prompt-text">';

                    code += '    <h5>关于项目</h5>';

                    code += '    <p>该项目须在规定的时间前达到规定的目标金额才算成功，否则已支持订单将取消；订单取消时已支付金额将在7个工作日退还到您的微信账号/支付宝账号/银行账号。</p>';

                    code += '    <h5>什么是众筹?</h5>';

                    code += '    <p>人们不是在这里购买已经存在的商品——我们是在参与创意。实现创意并不总是那么容易，一些项目会完成得很精彩，还有一些会遇到想不到的问题——但请对它们保持耐心。 </p>';

                    code += '    <p>发起者对他们的项目负责。一旦你支持一个项目，你需要相信发起者可以很好的完成他的工作。你可以对发起者做一点研究，了解他们的经验、名声。支持者也要判断什么是值得支持的项目。一些项目并不会按照计划进行，所以众筹的项目发起者会在这里详细记录他的计划进展，但是没有事情可以保证。当你支持一个项目，你需要记住这点。</p>';

                    code += '    <h5>如何找到我？</h5>';

                    code += '    <p>官方客服微信号：yami194</p>';

                    code += '    <p>客服热线：020-23336323</p><p>客服邮箱：service@yami.ren</p>';

                    code += '    </div></div>';

                    $.dialog(code, function() {}, true, 'promptBox');

                    $('#dialogBox.promptBox .btns .agree').remove();

                    $('#dialogBox.promptBox .btns .closeBtn').text('知道了');

                });

                $('.page_raiseReturn .r_return .con').on('click', '[data-lottery]', function () {
                    jump('lotteryRule');
                });

                $('.page_raiseReturn').on('click', '.lottery_rule', function () {
                    jump('lotteryRule');
                });

            }, 2);
        });
        //console.log(t_statu);

    },


    onload : function(){
        raiseReturnObject.raise_id = win.get.raise_id;



        raiseReturnObject.t_statu = 'normal';
        raiseReturnObject.test();

        if (win.get.member_privilege_id) {
            raiseReturnObject.member_privilege_id = win.get.member_privilege_id;
        }

        //判断是否登录，没有登录则跳转登录
        if(!member){
            win.login();
            return;
        }

    }
};


