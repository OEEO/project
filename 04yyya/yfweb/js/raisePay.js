/**
 * Created by fyt on 2016/10/19.
 */
var raisePayObject = {
    raise_id: null,
    data:{},
    address_id:'',
    wx_id:'',
    cefi:'',
    ok:'0',
    price:0,
    // order_pid:'',
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
                    $('.page_raisePay .certification .cbox').on('click', function () {
                        raisePayObject.certification();
                    });
                } else {
                    $('.page_raisePay .certification .cbox').off('click');
                    $('.page_raisePay .certification .cbox').css('color','#333');
                    $('.page_raisePay .certification .cbox').html(d.surname + ' ' + d.identity);
                    raisePayObject.cefi = d.surname + ' ' + d.identity;
                }
        });
    },
    //判断微信号
    is_wx:function(){
        ajax('Member/Index/info', {}, function(d){
            if(window.sessionStorage.getItem('address_id') != '' && window.sessionStorage.getItem('address_id') != null){
                $('.page_raisePay .shop_address .add').html(window.sessionStorage.getItem('address'));
                raisePayObject.address_id = window.sessionStorage.getItem('address_id');
            }
            if(d.default_address_id){
                raisePayObject.address_id = d.default_address_id;
                $('.page_raisePay .shop_address .add').html(d.default_address);
                $('.page_raisePay .shop_address .add').css('color','#333');
            }else{
                    raisePayObject.address_id = ' ';

            }
            if (d.weixincode != null && d.weixincode !='') {
                raisePayObject.wx_id = d.weixincode;
                $('.page_raisePay .wxinput .wxcode').css('color','#333');
                $('.page_raisePay .wxinput .wxcode').val(d.weixincode);
                $('.page_raisePay .wxinput .wxcode').attr('readonly','readonly');

            }
        });
    },
    //选择地址
    addressInput : function(id, name, tel, address){
        raisePayObject.address_id = id;
        $('.page_raisePay .shop_address .add').html(address);
        $('.page_raisePay .shop_address .add').css('color','#333');
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
                    $('.page_raisePay .certification .cbox').css('color','#333');
                    $('.page_raisePay .certification .cbox').html(nick + ' ' + id);
                    raisePayObject.cefi = nick + ' ' + id;
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
        sdata.raise_id = raisePayObject.data.raise_id;
        sdata.raise_times_id = raisePayObject.data.times_id;
        ajax('Goods/Raise/getRaiseOrder', sdata, function(d){
            if(d.status == 1) {
                raisePayObject.price = d.info.data.pay_price;
                $('.page_raisePay .raiseitem .title').html(d.info.data.title);
                $('.page_raisePay .raiseitem .money').html('￥' + d.info.data.pay_price);
                $('.page_raisePay.payMenu .paymoney [name="money"]').html(d.info.data.pay_price);
                //当支付金额超过2000,则引导至支付宝支付
                if (parseFloat(d.info.data.pay_price) > 2000 || win.get.android) {
                    $('.page_raisePay .paytype a.wx').addClass('disable');
                    $('.page_raisePay .paytype a.alipay ').click();
                }
                if (d.status == 0) {
                    $.alert(d.info,'error');
                    return;
                }
                var scode = '';
                if (d.info.data.is_address == 0) {
                    $('.page_raisePay .shop_address').remove();
                    $('.page_raisePay .raiseitem').next('.the_blank').remove();
                }
                if (d.info.data.is_realname == 0) {
                    $('.page_raisePay .certification').remove();
                    $('.page_raisePay .wx_num').next('.the_blank').remove();
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
                $('.page_raisePay .stage .stageitem').html(scode);
            }else{
                $.alert(d.info,'error');
            }
        });
    },
    onload : function(){
        raisePayObject.data = win.get;
        raisePayObject.is_certification();
        raisePayObject.raisestep();
        raisePayObject.is_wx();

        //console.log(raisePayObject.data);
        //更多地址按钮
        $('.page_raisePay .shop_address .address').on('click',function(){
            jump('myAddress', {address_id: raisePayObject.address_id});
        });

        //支付方式
        $('.page_raisePay .paytype a').on('click', function(){
            if(!$(this).hasClass('disable')){
                $('.page_raisePay .paytype a .selected').removeClass('on');
                $(this).find('.selected').addClass('on');
            }
        });
        $('.page_raisePay .submitBtn').click(function(){
            var datas = {};
            datas.raise_id = raisePayObject.data.raise_id;
            datas.times_id = raisePayObject.data.times_id;
            if($('.page_raisePay .shop_address').length > 0){
                datas.address_id = raisePayObject.address_id;
            }
            if(raisePayObject.data.id){
                datas.member_privilege_id = raisePayObject.data.id;
            }
            datas.weixincode = $('.page_raisePay .wxinput .wxcode').val();
            datas.oper_read = '1';
            console.log(datas);
            for(var i in datas){
                if(datas[i] == '' || datas[i] == ' '){
                    $.alert('请完善信息','error');
                    return;
                }
            }
            if($('.page_raisePay .certification').length > 0){
                if(raisePayObject.cefi == ''){
                    $.alert('请完善信息','error');
                    return;
                }
            }
            var from = getQueryString(location.href, 'from');
            if (from) {
                switch (from) {
                    case 'singlemessage':
                        datas.from = 1;
                        break;
                    case 'timeline':
                        datas.from = 0;
                        break;
                    default:
                        datas.from = 2;
                        break;
                }
                datas.platform = 1;
            }


            var code = '';
            code += '<div class="agreement">';
            code += '    <div class="agreement-title">众筹协议</div>';
            code += '    <div class="agreement-text">';
            code += '<p>欢迎来到吖咪众筹！</p><p>这里是我们的众筹项目使用协议，当你使用吖咪众筹，都代表着你同意此页面里的规则。有些规则是需要用法律术语来描述，我们（在本协议中，我们亦指代吖咪众筹）会尽力向你提供简单明了的解释。</p><p>吖咪众筹是由广州吖咪网络科技有限公司开发的众筹平台。</p><p>当你开始使用吖咪众筹平台或是由吖咪众筹平台提供的服务（包括广州吖咪网络有限公司及其工作人员提供的相关服务），都视为你同意本页上列举的具有法律约束力的条款。你也同意我们的隐私规则，并同意按照网站上的其他规则行事：比如社区指导原则或是发起项目的规则。</p><p>我们可能会改变某些条款。如果我们这样做，我们会通过在网站上通知或发送电子邮件通知你。新版本的条款不会具有追溯效力，我们会告诉你确切的日期生效。如果你在通知后，继续使用吖咪众筹，这意味着你接受新条款。</p><p>关于创建注册帐户</p><p>没有注册账户，并不影响你浏览吖咪众筹。但是当你使用吖咪众筹的一些功能时，你将需要注册账户。</p><p>注册账户时，你提供的信息必须准确、完整。</p><p>账户名字不要模仿别人的名字，不要选择有攻击性或侮辱性的名字，当然，账户的名字也不能违法国家法律。如果你不遵守这些规则，我们可以取消你的账户。</p><p>你需要对自己账户的行为负责，并保密你的密码。如果你发现你的账户被盗或有其他异常，你应该和service@yami.ren联系。</p><p>注册账户，你至少需要18岁。如果有必要，我们会要求你提供你年龄的证明。</p><p>禁止条款</p><p>你使用吖咪众筹过程中：</p><p>*不得违反中华人民共和国的现行法律。</p><p>*不得说谎。提供错误的、有误导性的信息，将被视为欺骗或欺诈。</p><p>*不得提供违禁物品。你提供的实物回报，不能违反吖咪众筹的规定，也不能违反任何适用的法规。</p><p>*不得伤害他人。不得威胁、辱骂、骚扰、诽谤他人，也不得侵犯他人隐私。</p><p>*不得伤害他人的电脑。不要分发软件病毒。</p><p>我们还需要确保吖咪众筹是安全的，我们的系统可以正常运行。所以，别做下面的事，你也可以理解为“别惹系统”。</p><p>*不得干扰服务的政策运作。</p><p>*不得试图非授权访问系统、数据、密码或其他信息。</p><p>*不得让我们的服务器承受不合理的负担。</p><p>*不得在我们的网站发布病毒。</p><p>发起人和支持者之间的合同关系</p><p>吖咪众筹为创意项目提供众筹平台。当发起人发起众筹项目，支持者付款支持后，发起人与支持者之间就形成合同关系：支持者接受发起人的提议，并形成合同。</p><p>吖咪众筹并不是这个合同的一部分——合同只有双方：发起人和支持者。但这份合同里包括以下定式条款：</p><p>*当一个项目众筹成功后，发起人必须完成项目，并按承诺将实物回报给支持者。一旦发起人完成了这些任务，即可视为他履行了针对支持者的义务。</p><p>*从众筹开始到实物回报送到每位支持者手中前，发起人对支持者承担如下责任：为完成项目的高标准的努力与付出、诚实的沟通。</p><p>*同时，支持者必须明白，他们支持一个项目，他们是在帮助创造崭新的事物，而不是订购已经存在的东西。这个过程中会有变化、延迟，甚至可能发生一些事情，让发起人无法完成他们的项目。</p><p>如果一个发起人无法完成他的项目或是兑现他承诺的回报，他们将被视为未能履行该合同的基本条款。此时，发起人必须对支持者进行补救：</p><p>*发布一个更新声明，解释项目已经完结，资金如何使用，以及是什么阻止他们完成这个项目；</p><p>*工作努力，并且尽一切可能在指定的时间内完成项目，并和支持者进行了沟通；</p><p>*能够证明自己的资金使用合理，并采用了每个合理的步骤去完成项目；</p><p>*项目发起时的描述是诚实的，没有任何虚假的宣传或实物展示；</p><p>*如果众筹金额还有剩余，必须返还给未收到实物回报的支持者（按支持者的金额比例）；否则，需要承诺这些资金将被用来完成项目的替代。</p><p>发起人是其履行承诺的负责人。如果他们不能完成本协议的条款，支持者可针对他们采取法律救助。</p><p>如何众筹</p><p>如果你是支持者，你需要了解以下内容：</p><p>*当项目达到它的众筹目标，你才需要付钱。当你支持一个项目，你的钱将打入第三方支付平台的账户中，项目众筹时间完成，若达到目标金额，这笔钱将转入发起人提供的账户，若未完成，则将全额返回你的账户中。</p><p>*发起人在项目页面里描述的回报时间是个大概时间，而不是保证履行的日期。发起人的安排可能改变。我们会要求发起人慎重考虑回报的时间，并确定有信心完成，如果有任何更改都要在3天内通知支持者。</p><p>*你支持项目的发起人可能会向你提问，比如你的邮寄地址或者你的t恤大小。他们会在众筹成功后要求你回答这些问题，为了接受回报，你需要在合理的时间内提供这些问题的答案。</p><p>*吖咪众筹不提供退款或类似的保证。完成项目的责任在于项目的发起人，吖咪众筹不是这些发起人的代表，它不能保证发起人的工作进度，也不能为发起人提供担保。</p><p>免责</p><p>吖咪众筹不承担任何赔偿义务。我们不参与评判用户之间的争端，或用户和任何第三方服务之间的争端。我们不负责监督项目是否准时或回报物的性能。当你使用吖咪众筹的服务之后，你就放弃了对吖咪众筹以任何方式索赔的权利。</p><p>吖咪众筹的权利</p><p>吖咪众筹拥有这些权利:</p><p>*我们可以更改网站和服务不另行通知（除约定要通知的以外）。</p><p>*我们有权决定谁有资格使用吖咪众筹，我们可以取消账户或拒绝提供服务。我们可以在任何时间改变我们的合格标准。</p><p>*我们有权拒绝、取消、中断，删除或暂停任何项目。</p><p>吖咪众筹不因为这些行动承担任何赔偿。</p><p>保证免责声明</p><p>吖咪众筹明确声明，我们不提供任何资金担保，无侵权的保证或其他适用于特殊用途的保证，或众筹过程中任何行为的保证。吖咪众筹向你提供的任何建议或信息（口头或书面）均不构成保证。</p><p>赔偿</p><p>如果你的行为让我们被起诉，或者违反任何在本协议中你做出的承诺，你必须帮我们进行辩护、赔偿，并让我们免于因为你使用吖咪众筹或错误使用吖咪众筹造成的任何索赔或债务。我们保留权利，依据此条款，你会和我们合作，帮我们进行辩护。</p><p>争议解决</p><p>如果你有问题，吖咪众筹鼓励你先联系我们。如果有任何法律纠纷，并需在法院诉讼解决，诉讼地必须在广州。你特此不可撤销地放弃在其他地点起诉的任何权利。</p><p>其他</p><p>这就是你和吖咪众筹之间的完整的协议。它是唯一处理你和吖咪众筹之间服务关系的依据。除非条款和法律抵触，否则这些条款将始终具有效力。你或者吖咪众筹未能行使某项条款中规定的权利，不会被视为放弃其他权利。</p><p>如果该协议发生修改，吖咪众筹有义务通过电子邮件或其他联系方式通知你，或者在网站显著位置发布通知。</p>';
            code += '    </div></div>';
            code += '<div class="readagree"><i></i>我已阅读并同意此协议</div>';
            $.dialog(code, function(){
                //提交订单
                ajax('Order/Index/create', datas, function (d) {
                    if (d.status == 1) {
                        raisePayObject.order_id = d.info.order_id;
                        // if($('.page_raisePay .paytype .alipay .selected').hasClass('on')){
                        //     window.location.href = 'http://' + DOMAIN + '/order/pay/submitAlipay.do?token='+ win.token +'&order_id=' + d.info.order_id;
                        // }else{
                        wxpay(d.info.order_id,raisePayObject.price,d.info.limit_pay_time,2);
                        // }
                    } else {
                        if (d.info == 'open_id_is_null') {
                            $.dialog('尚未获得授权!是否现在授权?', function () {
                                ajax('Home/Wx/getOauthUrl', function (d) {
                                    if (typeof(d) == 'string') {
                                        if (window.sessionStorage) {
                                            window.sessionStorage.setItem('address', $('.page_raisePay .shop_address .add').text());
                                            window.sessionStorage.setItem('address_id', raisePayObject.address_id);
                                            window.sessionStorage.setItem('jumpUrl', 'page=raisePay&raise_id=' + raisePayObject.data.raise_id + '&times_id=' + raisePayObject.data.times_id);
                                        }
                                        window.location.href = d;
                                    }
                                });
                            });
                        } else {
                            $.alert(d.info,'error');
                        }
                    }
                }, 2);
            }, true, 'agreementBox');
            $('#dialogBox.agreementBox .btns .agree').attr('disabled','disabled');
            $('#dialogBox.agreementBox .context .readagree i').click(function(){
                if($(this).hasClass('icon_checkbox')){
                    $(this).removeClass('icon_checkbox');
                    $('#dialogBox.agreementBox .btns .agree').attr('disabled','disabled');
                }else{
                    $(this).addClass('icon_checkbox');
                    $('#dialogBox.agreementBox .btns .agree').removeAttr('disabled');
                }
            });
            $('#dialogBox.agreementBox .btns .closeBtn').remove();
            $('#dialogBox.agreementBox .btns .agree').text('继续支付');

        });
    },
    onshow:function(){

    }
};
