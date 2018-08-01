var confirmBuyObject = {
    goods_id : null,
    stocks : 0,
    coupon_id : null,
    item_price: 0,
    buy_price : null,
    buy_num : 1,
    limit_buy_num: Infinity,
    type : 0,
    value : 0,
    piece: 0,
    pieceInfo: null,
    available: true,
    piece_originator_id: null,
    //选择地址
    addressInput : function(id, name, tel, address){
        confirmBuyObject.address_id = id;
        $('.page_confirmBuy #phone').text(tel);
        $('.page_confirmBuy #address').text(address);
    },
    couponInput : function (id, type, value, content, name){
        confirmBuyObject.coupon_id = id;
        confirmBuyObject.type = type;
        confirmBuyObject.value = parseFloat(value);

        if (type) {
            $('.page_confirmBuy #cancel_coupon').show();
        }

        if(type == 0){
            $('.page_confirmBuy #coupon_price').html(name);
            $('.page_confirmBuy #coupon_release_price').text("- ￥" + value);
        }else if(type == 1){
            var val = value / 10 + '折';
            $('.page_confirmBuy #coupon_price').html(name);
            $('.page_confirmBuy #coupon_release_price').text("- ￥" + value);
        }else if(type == 2){
            $('.page_confirmBuy #coupon_price').html("赠品：" + content);
        }
        confirmBuyObject.changePrice();
    },
    cancelCoupon : function(){
        $('.page_confirmBuy #coupon_price').html('使用优惠券');
        $('.page_confirmBuy #coupon_release_price').text('￥0');
        this.couponInput(null,null,null,null);
    },
    vals : function (em){
        if(!/^\d+$/.test($(em).text())){
            $(em).text(1);
        }else{
            var currentVal = parseInt($(em).text());
            if(currentVal > confirmBuyObject.stocks){
                $(em).text(confirmBuyObject.stocks);
            }else if(currentVal < 1){
                $(em).text(1);
            }
        }
        confirmBuyObject.changePrice();
    },

    changeCopies : function (em, num){
        if (num > 0 && confirmBuyObject.limit_buy_num > 0 && confirmBuyObject.buy_num >= confirmBuyObject.limit_buy_num) {
//            $.alert('每人限购' + confirmBuyObject.limit_buy_num + '份');
            return;
        }

        if (em) {
            var currentVal = confirmBuyObject.buy_num;
            if(currentVal <= 1 && num == '-1')num=0;
            if(currentVal >= confirmBuyObject.stocks && num == '1')num=0;
            currentVal += (+num);
            confirmBuyObject.buy_num = currentVal;
        } else {
            confirmBuyObject.buy_num = num;
        }

        // $(em).parent().children('.b').value(currentVal);
        $('.page_confirmBuy #num').val(confirmBuyObject.buy_num);
        confirmBuyObject.changePrice();
    },

    changePrice : function(){
        confirmBuyObject.buy_price = parseFloat(confirmBuyObject.buy_price);
        var price = confirmBuyObject.item_price;
        var num = confirmBuyObject.buy_num;
        price *= num;

        var _price = price;
        //计算优惠券
        if(confirmBuyObject.type == 0) {
            _price = Math.round((price - confirmBuyObject.value + confirmBuyObject.shipping) * 100) / 100;
            $('.page_confirmBuy #coupon_release_price').text("- ￥" + confirmBuyObject.value);
        } else if(confirmBuyObject.type == 1) {
            _price = price * confirmBuyObject.value / 100 + confirmBuyObject.shipping;
            $('.page_confirmBuy #coupon_release_price').text("- ￥" + price * (100 - confirmBuyObject.value) / 100);
        }


        _price = _price > 0 ? _price : 0;

        // price = price.priceFormat();
        // _price = _price.priceFormat();
        confirmBuyObject.buy_price = _price;
        $('.page_confirmBuy #buy_price').html(_price);
        $('.page_confirmBuy #number_price').html('￥' + confirmBuyObject.item_price + '<i>x</i>' + num);
    },
    onload : function(){
        confirmBuyObject.goods_id = win.get.goods_id;
        confirmBuyObject.piece = win.get.piece || 0;
        confirmBuyObject.piece_originator_id = win.get.piece_originator_id;

        //判断是否登录，没有登录则跳转登录
        if(!member){
            win.login();
            return;
        }

        //文本清空
        $('.words').focus(function() {
            $(this).empty();
        });

        ajax('Order/Index/getGoods', {goods_id:confirmBuyObject.goods_id, ispiece: confirmBuyObject.piece}, function(d){
            if(d.info){
                $.alert(d.info, 'error', function(){
                    page.back();
                });
                return;
            }

            if (d.piece) {
                $('.page_confirmBuy .piece').show();
                confirmBuyObject.item_price = +d.piece.price;
                confirmBuyObject.pieceInfo = d.piece;
//                confirmBuyObject.limit_buy_num = +d.piece.limit_num;
                confirmBuyObject.limit_buy_num = 1;
            } else {
                confirmBuyObject.item_price = +d.buy_price;
                confirmBuyObject.limit_buy_num = +d.limit_num;
            }

            //插入主图
            $('.page_confirmBuy #mainpic').html('<img src="'+ d.mainpic +'">');
            //插入标题
            $('.page_confirmBuy #title').text(d.title);
            //插入价格
            $('.page_confirmBuy #price').html('<font>' + parseFloat(confirmBuyObject.item_price) + '元/</font>份');
            //邮费
            if(parseFloat(d.shipping) > 0)
                $('.page_confirmBuy #postage').html('￥' + parseFloat(d.shipping).priceFormat());
            else
                $('.page_confirmBuy #postage').html('[包邮]');

            if(d.address && d.address.address){
                var address = d.address.province_name + d.address.province_alt + d.address.city_name + d.address.city_alt + d.address.area_name + d.address.area_alt + d.address.address;
                confirmBuyObject.addressInput(d.address.id, d.address.linkman, d.address.telephone, address);
            }

            //更多地址按钮
            $('.page_confirmBuy #address').click(function(){
                jump('myAddress', {address_id: confirmBuyObject.address_id});
            });

            //插入单价
            confirmBuyObject.buy_price = parseFloat(d.buy_price);
            confirmBuyObject.shipping = parseFloat(d.shipping);

            // 如果是团购


            //库存
            confirmBuyObject.stocks = parseInt(d.stocks);
            confirmBuyObject.changePrice();

            $('.page_confirmBuy #num').change(function () {
                console.log($(this).val());
                confirmBuyObject.changeCopies(null, +$(this).val());
            });

            //插入可用优惠券
            if(d.coupon){
                confirmBuyObject.couponInput(d.coupon.id, d.coupon.type, d.coupon.value, d.coupon.content, d.coupon.name);
                $('.page_confirmBuy #cancel_coupon').show();
                $('.page_confirmBuy #cancel_coupon').click(function () {
                    $(this).hide();
                    confirmBuyObject.cancelCoupon();
                });
                //选择优惠券
                $$(function(){
                    if(parent.win.coupon){
                        confirmBuyObject.couponInput(parent.win.coupon.id, parent.win.coupon.type, parent.win.coupon.value, parent.win.coupon.content, parent.win.coupon.name);
                        delete parent.win.coupon;
                    }
                });
                $('#coupon_price').click(function(){
                    var min_price = confirmBuyObject.buy_price * confirmBuyObject.buy_num;
                    if($('#is_book').attr('is_book') == 1){
                        min_price *= confirmBuyObject.book_discount / 100;
                    }
                    jump('myCoupon', {goods_id:confirmBuyObject.goods_id, coupon_id:confirmBuyObject.coupon_id, min_price:min_price});
                });

            }else{
                $('.page_confirmBuy #coupon_price').text('没有优惠券可用');
            }

        }, 2);
    },
    onshow:function(){
        //提交按钮绑定时间
        $('.page_confirmBuy #submitBtn').click(function(){

            if (!confirmBuyObject.available) {
                return;
            }

            if (confirmBuyObject.buy_num > confirmBuyObject.stocks) {
                $.alert('购买数量不能大于' + confirmBuyObject.stocks);
                return;
            }

            if (confirmBuyObject.buy_num === 0) {
                $.alert('购买数量不能为0');
                return;
            }

            if (!confirmBuyObject.address_id) {
                $.alert('需要填写地址');
                return;
            }

            var data = {};
            data.goods_id = confirmBuyObject.goods_id;
            data.address_id = confirmBuyObject.address_id;
            data.num = confirmBuyObject.buy_num;

            if(confirmBuyObject.coupon_id != null)data.coupon_id = confirmBuyObject.coupon_id;

            data.context = $(".page_confirmBuy .leaveWords .words").val();

            console.log('生成订单');
            confirmBuyObject.available = false;

            var pieceType = -1;

            if (confirmBuyObject.piece === 1) {
                data.type_piece_id = confirmBuyObject.pieceInfo.id;
                data.tips_id = confirmBuyObject.goods_id;
                pieceType = 1;
                confirmBuyObject.piece_originator_id && (data.piece_originator_id = confirmBuyObject.piece_originator_id, pieceType = 0);
            }

            //提交订单
            ajax('Order/Index/create', data, function(d){
                confirmBuyObject.available = true;
                if(d.status == 1){
                    //jump('payMoney', {order_id : d.info.order_id});
                    confirmBuyObject.order_id = d.info.order_id;
                    wxpay(d.info.order_id,confirmBuyObject.buy_price,d.info.limit_pay_time, 1, d.info.piece_originator_id, null, {pieceType: pieceType, group_id: d.info.piece_originator_id});
                }else{
                    if(d.info == 'open_id_is_null'){
                        $.dialog('尚未获得授权!是否现在授权?', function(){
                            ajax('Home/Wx/getOauthUrl', function(d){
                                if(typeof(d) == 'string'){
                                    if(window.sessionStorage){
                                        window.sessionStorage.setItem('jumpUrl', 'page=choice-goodsDetail-confirmBuy&goods_id=' + confirmBuyObject.goods_id);
                                    }
                                    window.location.href = d;
                                }
                            });
                        });
                    }else{
                        $.alert(d.info,'error');
                    }
                }
            }, 2);
        });
    }
};
