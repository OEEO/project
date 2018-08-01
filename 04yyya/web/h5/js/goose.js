/**
 * Created by fyt on 2016/11/7.
 */
var nickname = '';var sex = '';
var cvs,cvs2,cvs3,context,context2,context3,cvs_width,touch_state = false;
var img = {x:0,y:0,w:0,h:0,em:null,size:0,left:0,top:0,r:0};
var touch = {x:0,y:0};
var randnum = '';

$(function(){
    // window.localStorage.removeItem('goose_num');
    $('html').attr('style', 'font-size:' + ($(window).width() / 7.5) + 'px');
    $(window).resize(function(){
        $('html').attr('style', 'font-size:' + ($(window).width() / 7.5) + 'px');
    });

    $.ajax({
        // url : 'http://api.yummy194.cn/',
        url : 'http://api.m.yami.ren/',
        async : false,
        data : {url:location.href},
        success : function(d){
            loadWechat(d.appId, d.timestamp, d.nonceStr, d.signature);
        }
    });


    $('.container').height($(window).height());//页面主体的高度
    cvs =$('.canvas')[0];
    cvs2 = $('.makeimg')[0];//获取canvas
    context = cvs.getContext('2d');
    context2 = cvs2.getContext('2d'); //获取canvas画笔
    
    cvs_width = $('.third .canvaslay').width();
    $('.makeimg').attr('width', '750').attr('height', '1334'); //最后生成出来图片宽高
    $('.canvas').attr('width', cvs_width).attr('height', cvs_width);
    //拖动函数
    $('.canvaslay')[0].addEventListener('touchstart', function(event){
        event.preventDefault();
    }, false);

    $('.canvaslay')[0].addEventListener('touchmove', function(event){
        event.preventDefault();
        var ev = event.touches;
        if(ev.length == 1) {
            if(touch_state){
                img.x += ev[0].pageX - touch.x;
                img.y += ev[0].pageY - touch.y;
                draw();
            }
            touch.x = ev[0].pageX;
            touch.y = ev[0].pageY;
            touch_state = true;
        }
    }, false);

    $('.canvas')[0].addEventListener('touchend', function(event){
        event.preventDefault();
        touch_state = false;
    }, false);
//缩小放大函数
    $('.line')[0].addEventListener('touchstart', function(event){
        event.preventDefault();
        var ev = event.touches;
        if(ev.length == 1) {
            if(ev[0].pageY > $(this).offset().top + ($(this).children('.move').height()/2) && ev[0].pageY < $(this).offset().top + $(this).height() - ($(this).children('.move').height()/2)){
                $(this).children('.move').css('top', ev[0].pageY - $(this).offset().top - ($(this).children('.move').height()/2));
                img.size = Math.round($(this).children('.move').position().top / ($(this).height() - $(this).children('.move').height()) * 100);
                draw();
            }else if(ev[0].pageY > $(this).offset().top + $(this).height() - ($(this).children('.move').height()/2)){
                $(this).children('.move').css('top',$(this).height() - $(this).children('.move').height());
            }
            else{
                $(this).children('.move').css('top','0');
            }
        }
    }, false);

    $('.line')[0].addEventListener('touchmove', function(event){
        event.preventDefault();
        var ev = event.touches;
        if(ev.length == 1) {
            if(touch_state){
                var top = ev[0].pageY - $(this).offset().top - ($(this).children('.move').height()/2);
                if(top <= 0){
                    $(this).children('.move').css('top', 0);
                }else if(top > $(this).height() - $(this).children('.move').height()){
                    $(this).children('.move').css('top', $(this).height() - $(this).children('.move').height());
                }else{
                    $(this).children('.move').css('top', top);
                }
                img.size = Math.round($(this).children('.move').position().top / ($(this).height() - $(this).children('.move').height()) * 100);
                draw();
            }
            touch.y = ev[0].pageY;
            touch_state = true;
        }
    }, false);

    $('.line')[0].addEventListener('touchend', function(event){
        event.preventDefault();
        touch_state = false;
    }, false);
    // 放大
    $('.third .add').click(function(){
        img.w = img.w + 10;
        img.h = img.h + 10/img.w*img.h;
        draw();
    });
    // 缩小
    $('.third .sub').click(function(){
        img.w = img.w - 10;
        img.h = img.h - 10/img.w*img.h;
        // if(img.w < cvs.width){
        //     img.w = cvs.width;
        //     img.h = img.w / img_scale;
        // }else if(img.h < cvs.height){
        //     img.h = cvs.height;
        //     img.w = img.h * img_scale;
        // }
        draw();
    });

    $('.sex input').click(function(){
        sex = $(this).val();
        if(sex == 0){
            $(this).css('background','url("img/goose/2/5_1.png") no-repeat 0 0 / 2.25rem auto');
            $(this).siblings('.boy').css('background','url("img/goose/2/6.png") no-repeat 0 0 / 2.25rem auto');
        }else{
            $(this).css('background','url("img/goose/2/6_1.png") no-repeat 0 0 / 2.25rem auto');
            $(this).siblings('.girl').css('background','url("img/goose/2/5.png") no-repeat 0 0 / 2.25rem auto');
        }
        nickname = $('#nickname').val();

    });
});
// 跳转
function go(){
    // window.localStorage.clear();
    if(localStorage.goose_num != 1 && localStorage.goose_num != 2 && localStorage.goose_num != 3 && localStorage.goose_num != 4 && localStorage.goose_num != 5){
        window.localStorage.goose_num = 0;
    }

    var gg = parseInt(window.localStorage.goose_num) + 1;
    window.localStorage.goose_num = gg;

    if(window.localStorage.goose_num > 3){
        alert('每个人只有三次机会哦，快分享给朋友试试吧！');
        window.location.href='http://yummy194.cn';
    }
    $('.index').hide();
    $('.second').fadeIn('fast');
}
//上传函数
function upload(file){
    $('#upload-file').val('');
    var url = window.URL.createObjectURL(file);
    img.em = $('<img/>').attr('src', url);
    img.em.load(function(){
        $('.canvaslay').attr('data','1');
        //获取图片的原始宽高+比例
        var img_width = this.width; //图片本身宽度
        var img_height = this.height;//图片本身高度
        var img_scale = img_width / img_height;
        if(img_scale > cvs.width / cvs.height){
            img.h = cvs.height;
            img.w = img.h * img_scale;
        }else{
            img.w = cvs.width;
            img.h = img.w / img_scale;
        }

        draw();
    });
}

function rotating(){
    img.r += 90;
    draw();
}

function draw(){
    context.clearRect(0,0,cvs.width,cvs.height);
    context.save();//保存当前的绘制状态
    switch((img.r / 90) % 4){
        case 0:
            var x = img.x;
            var y = img.y;
            break;
        case 1:
            var x = img.y;
            var y = -1 * (img.x );
            break;
        case 2:
            var x = -1 * (img.x);
            var y = -1 * (img.y);
            break;
        case 3:
            var x = -1 * (img.y);
            var y = img.x;
            break;
    }
    var ra = img.r * Math.PI / 180;
    // context.translate(cvs.width/2, img.top + cvs_width/2);
    context.translate(img.x+(img.w/2), img.y + (img.h/2));
    context.rotate(ra);
    context.translate(-(img.x+(img.w/2)), -1*(img.y + (img.h/2)));
    // context.translate(-cvs.width/2, -1*(img.top + cvs_width/2));
    context.drawImage(img.em[0], x, y, img.w + img.w*(img.size/100), img.h + img.h*(img.size/100));
    context.restore();//还原状态
}
// 制作图片
function create(){
    if($('.canvaslay').attr('data') != '1'){
        alert('请上传你的靓照！');
        return;
    }
    // var r = $(window).width() / 7.5;
    //加入黄底
    // var imgData = context.getImageData(0,0,$(window).width(),$(window).height());
    // context3.putImageData(imgData,0,0);
    // var main = $('<img/>').attr('src', cvs.toDataURL('image/jpeg'));
    // context2.save();
    context2.fillStyle="#fccf00";
    context2.fillRect(0,0,750,1334);
    // context2.fillRect(0,0,100,cvs2.height);
    // context2.fillRect(1100,0,cvs2.width,cvs2.height);
    // context2.fillRect(0,1050,cvs2.width,cvs2.height);
    context2.save();
    //context.globalCompositeOperation = 'source-atop';
    var num = 0;

    //加入各种图片

    var imgs = {
        mc1:{},
        mc2:{},
        mc3:{},
        mc4:{},
        mc5:{},
        mc6:{},
        mc7:{},
    };

    // 头像
    $('<img>').attr('src',cvs.toDataURL('image/jpeg')).load(function(){
        imgs.mc1.em = this;
        imgs.mc1.x = 39;
        imgs.mc1.y = 150;
        imgs.mc1.w = 296;
        imgs.mc1.h = 296;
        num ++;
    });
    $('<img>').attr('src', 'img/goose/1.png?v=1.1').load(function(){
        imgs.mc2.em = this;
        imgs.mc2.x = 15;
        imgs.mc2.y = 22;
        imgs.mc2.w = 468;
        imgs.mc2.h = 676;
        num ++;
    });

    $('<img>').attr('src', 'img/goose/3.png?v=1.1').load(function(){
        imgs.mc3.em = this;
        imgs.mc3.x = 50;
        imgs.mc3.y = 47;
        imgs.mc3.w = 383;
        imgs.mc3.h = 100;
        num ++;
    });

    // 女鹅
    if(sex == 0){
        $('<img>').attr('src', 'img/goose/g.png?v=1.1').load(function(){
            imgs.mc4.em = this;
            imgs.mc4.x = 250;
            imgs.mc4.y = 22;
            imgs.mc4.w = 480;
            imgs.mc4.h = 676;
            num ++;
        });
    }else{
        $('<img>').attr('src', 'img/goose/b.png?v=1.1').load(function(){
            imgs.mc4.em = this;
            imgs.mc4.x = 250;
            imgs.mc4.y = 22;
            imgs.mc4.w = 480;
            imgs.mc4.h = 676;
            num ++;
        });
    }
    // PK
    $('<img>').attr('src', 'img/goose/2.png?v=1.1').load(function(){
        imgs.mc5.em = this;
        imgs.mc5.x = 313;
        imgs.mc5.y = 235;
        imgs.mc5.w = 265;
        imgs.mc5.h = 248;
        num ++;
    });

    //星座图片
    var arr = [5, 8, 10, 15, 20];
    var lv = [500, 300, 200, 100, 100];
    var s = '';
    randnum = rand(arr, lv);
    if(sex == '0'){
        s = 'girl';
    }else{
        s = 'boy';
    }

    if((sex == '0' && randnum == '5') || (sex == '1' && randnum == '8') || (sex == '1' && randnum == '20')){
       var a = Math.ceil(Math.random() * 7);
    }else if(sex == '1' && randnum == '5'){
       var a = Math.ceil(Math.random() * 6);
    }else if(sex == '0' && randnum == '8'){
      var a = Math.ceil(Math.random() * 8);
    }else if(randnum == '10'){
       var a = Math.ceil(Math.random() * 5);
    }else if((sex == '0' && randnum == '15') || (sex == 0 && randnum == '20') || (sex == '1' && randnum == '15')){
        var a = Math.ceil(Math.random() * 4);
    }
    console.log(randnum);
    $('<img>').attr('src', 'img/goose/'+s+'/'+randnum+'/'+a+'.png?v=1.1').load(function(){
        imgs.mc6.em = this;
        imgs.mc6.x = 15;
        imgs.mc6.y = 675;
        imgs.mc6.w = 728;
        imgs.mc6.h = 632;
        num ++;
    });
    // 二维码
    $('<img>').attr('src', 'img/goose/code.png?v=1.1').load(function(){
        imgs.mc7.em = this;
        imgs.mc7.x = 390;
        imgs.mc7.y = 1120;
        imgs.mc7.w = 325;
        imgs.mc7.h = 162;
        num ++;
    });

    var p = setInterval(function(){
        if(num >= 7){
            for(var i in imgs){
                context2.drawImage(imgs[i].em, imgs[i].x, imgs[i].y, imgs[i].w, imgs[i].h);
            }
            clearInterval(p);
            //绘制名字
            context2.font=40 + "px 微软雅黑";
            context2.fillStyle='#333';
            context2.textAlign='center';//文本水平对齐方式
            context2.textBaseline='middle';//文本垂直方向，基线位置
            context2.fillText(nickname,180,90);
            var data = cvs2.toDataURL('image/jpeg');
            $('body').css('background','#fccf00');
            $('<img>').css({height:'100%','display':'block','margin':'0 auto','text-align':'text-align'}).attr('src', data).appendTo($('body').empty());

                $('<div>').addClass('layBox').appendTo('body')
                    .html('<img src="img/goose/share.png" class="share"/><img src="img/goose/text.png" class="text"/><div class="contentBoxs">分享立刻领取现金红包<br><img src="img/goose/'+randnum+'.png" class="yuan"/>长按保存颜值PK值<br></div>')
                    .click(function(){
                        $(this).remove();
                    });

            wechat.onMenuShareTimeline({
                title: '帅鹅颜值大PK赢红包',
                desc: '比我帅？比我美？就不给你红包~',
                link: 'http://yummy194.cn/h5/goose.html',
                imgUrl: 'http://yummy194.cn/h5/img/goose/s.png',
                success: function(){
                    if(randnum == '5'){
                        window.location.href = 'https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=wlbfoldk';
                    }else if(randnum == '8'){
                        window.location.href = 'https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=8smzeen3';
                    }else if(randnum == '10'){
                        window.location.href = ' https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=zir71rz6';
                    }
                    else if(randnum == '15'){
                        window.location.href = 'https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=dh3twv1j';
                    }
                    else if(randnum == '20'){
                        window.location.href = 'https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=tozwd122';
                    }

                }
            });
            wechat.onMenuShareAppMessage({
                title: '帅鹅颜值大PK赢红包',
                desc: '比我帅？比我美？就不给你红包~',
                link: 'http://yummy194.cn/h5/goose.html',
                imgUrl: 'http://yummy194.cn/h5/img/goose/s.png',
                success: function(){
                    if(randnum == '5'){
                        window.location.href = 'https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=wlbfoldk';
                    }else if(randnum == '8'){
                        window.location.href = 'https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=8smzeen3';
                    }else if(randnum == '10'){
                        window.location.href = ' https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=zir71rz6';
                    }
                    else if(randnum == '15'){
                        window.location.href = 'https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=dh3twv1j';
                    }
                    else if(randnum == '20'){
                        window.location.href = 'https://h5.koudaitong.com/v2/ump/promocard/fetch?alias=tozwd122';
                    }
                }
            });

        }
    }, 100);
}
//获取随机数
function rand(arr, lv){
    var sum = 0;
    var item = [];
    for(var i in lv){
        sum += lv[i];
        if(i == 0){
            item.push(lv[i]);
        }else{
            item.push(item[i-1] + lv[i]);
        }
    }
    var m = Math.floor(Math.random() * sum);
    for(var i in item){
        if(m < item[i])return arr[i];
    }
}
//提交信息
function save(){
    nickname = $('#nickname').val();
    if(nickname == '' || sex == ''){
        console.log(nickname);
        console.log(sex);
        alert('请将信息填写完整!');
        return;
    }
    $('.container.second').hide();
    $('.container.third').fadeIn('fast');
}
