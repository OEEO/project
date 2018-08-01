var fn = {};
var names = ['choice'];
var num = 0;

//设置跳转回调
function ready(f){
    if(typeof(f) == 'function'){
        var name = this.names[this.num];
        this.fn[name] = f;
    }
};

//跳转下一页
function jump(pagename, get, fn){
    if(pagename.indexOf('-') == -1){
        $('#filterMenu').remove();
        page.names.push(pagename);
        page.num ++;
        win.load(pagename, get, function(){
            page.move(-1, function(){
                if(typeof(fn) == 'function')fn();
            });
        });
        $('#header, #main, #footer').fadeOut('');
        if(pagename.indexOf('Msgs') > -1 || pagename.indexOf('myPictures') > -1){
            $(document).off('touchstart touchmove');
            $('#message').remove();
            clearInterval(win.pm);
        }else{
            loadMyMessages();
        }
    }else{
        var arr = pagename.split('-');
        page.names = [];
        page.clear();
        for(var i in arr){
            page.names.push(arr[i]);
        }
        page.num = arr.length - 1;
        win.load(arr[page.num], get, function(){
            page.move(-1, function(){
                $('#header, #main, #footer').children().not('.page_' + page.names[page.num]).remove();
                if(typeof(fn) == 'function')fn();
            });
        });
        $('#header, #main, #footer').hide();
        if(arr[page.num].indexOf('Msgs') > -1 || arr[page.num].indexOf('myPictures') > -1){
            $(document).off('touchstart touchmove');
            $('#message').remove();
            clearInterval(win.pm);
        }else{
            loadMyMessages();
        }
    }
}

//返回上一页
function back(fn){
    if(page.num > 0){
        page.num --;
        if($('.page_' + page.names[page.num]).size() > 0){
            $('#header, #main, #footer').find('.page_' + page.names[page.num]).fadeIn('');
            $('.shareBox').hide();
            page.move(1, function(){
                var name = page.names[page.num];
                if(page.fn[name] && typeof(page.fn[name]) == 'function'){
                    page.fn[name]();
                }
                if(typeof(fn) == 'function')fn();
                page.clear();
            });
        }else{
            win.load(page.names[page.num], win.get, function(){
                page.move(1, function(){
                    $('.shareBox').hide();
                    var name = page.names[page.num];
                    if(page.fn[name] && typeof(page.fn[name]) == 'function'){
                        page.fn[name]();
                    }
                    if(typeof(fn) == 'function')fn();
                    page.clear();
                });
            }, true);
        }
    }
    if(page.names[page.num].indexOf('Msgs') > -1 || page.names[page.num].indexOf('myPictures') > -1){
        $(document).off('touchstart touchmove');
        $('#message').remove();
        clearInterval(win.pm);
    }else{
        loadMyMessages();
    }
}

//刷新页面
function reload(){
    eval("delete " + page.names[page.num] + "Object;");
    if(win.debug){
        if(page.script[page.num])page.script[page.num].remove();
        page.script = removeArr(page.script, page.num);
        if(page.css[page.num])page.css[page.num].remove();
        page.css = removeArr(page.css, page.num);
    }
    if(page.header[page.num])page.header[page.num].remove();
    page.header = removeArr(page.header, page.num);
    if(page.wrapper[page.num])page.wrapper[page.num].remove();
    page.wrapper = removeArr(page.wrapper, page.num);
    if(page.footer[page.num])page.footer[page.num].remove();
    page.footer = removeArr(page.footer, page.num);
    win.load(page.names[page.num], win.get, function(){
        $('.page_' + page.names[page.num]).fadeIn('fast');
        $('#main .page_' + page.names[page.num]).scrollTop(0);
    });
}

//清理不用的页面
function clear(){
    if(page.names.length > page.num + 1){
        for(var i=page.num + 1; i<page.names.length; i++){
            eval("delete " + page.names[i] + "Object;");
            page.names = removeArr(page.names, i);
            if(win.debug){
                if(page.script[i])page.script[i].remove();
                page.script = removeArr(page.script, i);
                if(page.css[i])page.css[i].remove();
                page.css = removeArr(page.css, i);
            }
            if(page.header[i])page.header[i].remove();
            page.header = removeArr(page.header, i);
            if(page.wrapper[i])page.wrapper[i].remove();
            page.wrapper = removeArr(page.wrapper, i);
            if(page.footer[i])page.footer[i].remove();
            page.footer = removeArr(page.footer, i);
        }
    }
}

function move(way, fn){
    $('#header, #footer').fadeOut('fast');
    $('#main').fadeOut('fast', function(){
        $('#header, #main, #footer').children().hide();
        $('#header, #main, #footer').find(".page_" + page.names[page.num]).show();
        $('#header').fadeIn('fast');
        $('#main').fadeIn('fast', function(){
            win.close_loading();
            $('.resourcesBox.page_' + page.names[page.num]).fadeIn('fast');
            if(typeof(fn) == 'function')fn();
        });
        //判断有空样式，则隐藏底部，否则会遮挡其他元素
        if(!$('#footer .page_' + page.names[page.num]).hasClass('empty'))$('#footer').fadeIn('fast');
        //将资源盒子元素隐藏
        $('.resourcesBox').not('.page_' + page.names[page.num]).fadeOut('fast');
    });
}