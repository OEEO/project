/**
 * Created by fyt on 2016/10/19.
 */
var raisePay2Object = {
    raise_id: null,
    data:{},
    address_id:'',
    wx_id:'',
    cefi:'',
    ok:'0',
    price:0,

    //buy_price : null,
    buy_num : 1,
    // order_pid:'',

    stock : 0,
    limit : 0,
    limit_buy_times : 0,
    // 限额说明
    explain:function(){
        var code = '<div class="solve"><h3>大额支付解决方法(暂行)</h3><div class="subtitle">微信支付</div><p>1.微信支付需实名认证。</p><p>2.微信支付采用银行卡，支付额度详见各银行规则。</p><p>3.微信支付暂不支持信用卡，支持中国大陆地区的储蓄卡。</p><p>4.微信零钱支付日额度1万元，一年额度累计20万元。</p></div>';
        $.dialog(code, null, true, 'largepay');
        $('#dialogBox.largepay .btns .agree').remove();
        $('#dialogBox.largepay .btns .closeBtn').text('知道了');
    },
    //判断是否实名认证
    is_certification:function(){
        ajax('Goods/Raise/getRaiseReal', {}, function(d){
                if (d.status == 0) {
                    $('.page_raisePay2 .certification .cbox').on('click', function () {
                        raisePay2Object.certification();
                    });
                } else {
                    $('.page_raisePay2 .certification .cbox').off('click');
                    $('.page_raisePay2 .certification .cbox').css('color','#333');
                    $('.page_raisePay2 .certification .cbox').html(d.surname + ' ' + d.identity);
                    raisePay2Object.cefi = d.surname + ' ' + d.identity;
                }
        });
    },
    //判断微信号
    is_wx:function(){
        ajax('Member/Index/info', {}, function(d){
            if(window.sessionStorage.getItem('address_id') != '' && window.sessionStorage.getItem('address_id') != null){
                $('.page_raisePay2 .shop_address .add').html(window.sessionStorage.getItem('address'));
                raisePay2Object.address_id = window.sessionStorage.getItem('address_id');
            }
            if(d.default_address_id){
                raisePay2Object.address_id = d.default_address_id;
                $('.page_raisePay2 .shop_address .add').html(d.default_address);
                $('.page_raisePay2 .shop_address .add').css('color','#333');
            }else{
                    raisePay2Object.address_id = ' ';

            }
            if (d.weixincode != null && d.weixincode !='') {
                raisePay2Object.wx_id = d.weixincode;
                $('.page_raisePay2 .wxinput .wxcode').css('color','#333');
                $('.page_raisePay2 .wxinput .wxcode').val(d.weixincode);
                // 微信号只可读
                // $('.page_raisePay2 .wxinput .wxcode').attr('readonly','readonly');
            }
        });
    },
    //选择地址
    addressInput : function(id, name, tel, address){
        raisePay2Object.address_id = id;
        $('.page_raisePay2 .shop_address .add').html(address);
        $('.page_raisePay2 .shop_address .add').css('color','#333');
    },
    //实名认证
    certification :function(){
        var c ='';
        c += '<i class="clearpsd"></i>';
        c += '<div class="cform">';
        c += '<label class="name">姓名</label><input type="text" class="nick"/>';
        c += '<label class="cid">身份证号</label><input type="text" class="id"/>';
        c += '<label class="codet">验证码</label><label class="updatecode">点击图片更新验证码</label><div class="codebox"><input type="text" class="code" /><img src="'+win.host+'Home/Index/captcha.do?token='+win.token+'" class="codeimg"/></div>';
        c += '<span class="errorinfo">*身份证号位数不足！</span>';
        c += '</div>';
        $.dialog(c, function(){
            var nick = $('#dialogBox.certificationbox .nick').val();
            var id = $('#dialogBox.certificationbox .id').val();
            var captcha = $('#dialogBox.certificationbox .code').val();
            var data = {};
            data.surname = nick;
            data.identity = id;
            data.captcha = captcha;
            ajax('Goods/Raise/raise_real', data, function(d){
                if(d.status == 1){
                    $.alert(d.info);
                    $('.page_raisePay2 .certification .cbox').css('color','#333');
                    $('.page_raisePay2 .certification .cbox').html(nick + ' ' + id);
                    raisePay2Object.cefi = nick + ' ' + id;
                    $('#dialogBox.certificationbox .errorinfo').hide();
                    $('#dialogBox').css({'opacity':0,'margin-top': -1 * ($('#dialogBox').height()+(win.width / 360 * 100))});
                    $('.alertBoxLay').css('opacity', 0);
                    setTimeout(function(){
                        $('#dialogBox').remove();
                        $('.alertBoxLay').remove();
                    }, 500);
                }else{
                    $('#dialogBox.certificationbox .errorinfo').css('display','block');
                    $('#dialogBox.certificationbox .errorinfo').html('*'+d.info);
                }

            });
        }, 'false', 'certificationbox');
        $('#dialogBox.certificationbox .codeimg').click(function () {
            $(this).attr('src',''+win.host+'Home/Index/captcha.do?token='+win.token+'&'+Math.random());
        })
        $('#dialogBox.certificationbox .btns .closeBtn').remove();
        $('#dialogBox.certificationbox .btns .agree').text('确定');
    },
    //判断付款阶段
    raisestep:function(){
        var sdata = {};
        sdata.raise_id = raisePay2Object.data.raise_id;
        sdata.raise_times_id = raisePay2Object.data.times_id;
        ajax('Goods/Raise/getRaiseOrder', sdata, function(d){
            if(d.status == 1) {
                raisePay2Object.price = d.info.data.pay_price;
                $('.page_raisePay2 .raiseitem .title').html(d.info.data.title);
                $('.page_raisePay2 .raiseitem .money').html('￥' + d.info.data.pay_price);
                $('.page_raisePay2.payMenu .paymoney [name="money"]').html(d.info.data.pay_price);
                //当支付金额超过2000,则引导至支付宝支付
                if (parseFloat(d.info.data.pay_price) > 2000 || win.get.android) {
                    $('.page_raisePay2 .paytype a.wx').addClass('disable');
                    $('.page_raisePay2 .paytype a.alipay ').click();
                }
                if (d.status == 0) {
                    $.alert(d.info,'error');
                    return;
                }
                var scode = '';
                /*
                if (d.info.data.is_address == 0) {
                    $('.page_raisePay2 .shop_address').remove();
                    $('.page_raisePay2 .raiseitem').next('.the_blank').remove();
                }
                */
                if (d.info.data.is_address == 0) {
                    $('.page_raisePay2 .shop_address').remove();
                    $('.page_raisePay2 .pay_num').next('.the_blank').remove();
                }
                if (d.info.data.is_realname == 0) {
                    $('.page_raisePay2 .certification').remove();
                    $('.page_raisePay2 .wx_num').next('.the_blank').remove();
                }
                if (d.info.data.step == 1) {
                    scode += '<li class="having"><div class="hline">';
                    scode += '<p class="stage-one">阶段一 <font class="state">（进行中）</font></p>';
                    scode += ' <div class="subitem"><span class="subscription">预约金</span><span class="subtotal">小计：￥<font class="moneys">' + d.info.data.prepay + '</font></span></div>';
                    scode += '</div></li>';
                    scode += '<li><div class="hline">';
                    scode += '<p class="stage-one">阶段二 <font class="state">（未开始）</font></p>';
                    scode += ' <div class="subitem"><span class="subscription">尾款</span><span class="subtotal">小计：￥<font class="moneys">' + d.info.data.retainage + '</font></span></div>';
                    scode += '</div></li>';
                }
                $('.page_raisePay2 .stage .stageitem').html(scode);
            }else{
                $.alert(d.info,'error');
            }
        });
    },

    findUnpaid:function() {
        //        console.log(raisePay2Object.data.raise_id);
 //       console.log(raisePay2Object.data.times_id);
        ajax('Member/Order/findUnpaid', {}, function (d) {
//            console.log(d.member_id);
         //   console.log(d.order_id);
            for (i in d.order_id) {
                if (d.order_id[i]) {
                 //   console.log(d.order_id[i].ware_id);
               //     console.log(raisePay2Object.data.raise_id);
                //    console.log(d.order_id[i].tips_times_id);
             //       console.log(raisePay2Object.data.times_id);
                    if (d.order_id[i].ware_id == raisePay2Object.data.raise_id && d.order_id[i].tips_times_id == raisePay2Object.data.times_id) {
          //              console.log('good');
                        jump('choice-ucenter-myRaiseOrder-orderRaiseDetail',{order_id: d.order_id[i].id});
                    }
                }
            }
        });

//        jump('choice-ucenter-myRaiseOrder');
    },

    onload : function(){
        raisePay2Object.data = win.get;
        // raisePay2Object.is_certification();
        // raisePay2Object.raisestep();
        // raisePay2Object.is_wx();
        //
        // raisePay2Object.findUnpaid();

        var select = 'a';
        $('.page_raisePay2 .gift-detail .p').click(function (e) {
            $(e.currentTarget).siblings().removeClass('active')
            $(e.currentTarget).addClass('active')
            let index = $(e.currentTarget).index()
            if (index === 1) {
                select = 'a'
            } else {
                select = 'b'
            }
        })

        $('.page_raisePay2 .confirm-btn').click(function (e) {
            var code = $('.page_raisePay2 .box-item .gift-code').val()
            var name = $('.page_raisePay2 .box-item .name').val()
            var address = $('.page_raisePay2 .box-item .address').val()
            var telephone = $('.page_raisePay2 .box-item .phone').val()
            var wxcode = $('.page_raisePay2 .box-item .wxcode').val()

            var data = {
                code: code,
                name: name,
                address: address,
                telephone: telephone,
                weixincode: wxcode,
                type: 2,
                ware_id: win.get.raise_id,
                select: select
            }
            console.log(data)
            ajax('home/recieve/submitRecieve', data, function (data) {
                console.log('提交成功', data)
                if (data.status == 1) {
                    $.alert('恭喜您！兑换成功', function () {
                        jump('choice')
                    })
                } else {
                    $.alert(data.info)
                }
            })

        })
        //判断是否登录，没有登录则跳转登录
        if(!member){
            win.login();
            return;
        }
    },
    onshow:function(){

    },

    //改变数量
    changeCopies : function (em, num){
        if (num > 0 && raisePay2Object.limit_buy_times > 0 && raisePay2Object.buy_num >= raisePay2Object.limit_buy_num) {
            $.alert('每人限购' + raisePay2Object.limit_buy_num + '份');
            return;
        }
        if (em) {
            var currentVal = raisePay2Object.buy_num;
            if(currentVal <= 1 && num == '-1')num=0;
            if(currentVal >= raisePay2Object.stock && num == '1' && raisePay2Object.stock != '-1')num=0;
            if(currentVal >= raisePay2Object.limit_buy_times && num == '1' && raisePay2Object.limit != '0')num=0;
            currentVal += (+num);
            raisePay2Object.buy_num = currentVal;
        } else {
            raisePay2Object.buy_num = num;
        }

        // $(em).parent().children('.b').value(currentVal);
        $('.page_raisePay2 #num').val(raisePay2Object.buy_num);
        raisePay2Object.changePrice();
    },

    //改变价钱
    changePrice : function(){
        //raisePay2Object.buy_price = parseFloat(raisePay2Object.buy_price);
        var price = raisePay2Object.price;
        var num = raisePay2Object.buy_num;
        price *= num;
        price = price.toFixed(2);
       // raisePay2Object.price = pric

        /*
        var _price = price;
        //计算优惠券
        if(raisePay2Object.type == 0) {
            _price = Math.round((price - raisePay2Object.value + raisePay2Object.shipping) * 100) / 100;
            $('.page_raisePay2 #coupon_release_price').text("- ￥" + raisePay2Object.value);
        } else if(raisePay2Object.type == 1) {
            _price = price * raisePay2Object.value / 100 + raisePay2Object.shipping;
            $('.page_raisePay2 #coupon_release_price').text("- ￥" + price * (100 - raisePay2Object.value) / 100);
        }


        _price = _price > 0 ? _price : 0;

        // price = price.priceFormat();
        // _price = _price.priceFormat();
        raisePay2Object.buy_price = _price;
            */
        $('.page_raisePay2 #buy_price').html(price);
       // $('.page_raisePay2 #number_price').html('￥' + raisePay2Object.item_price + '<i>x</i>' + num);
    },
};
