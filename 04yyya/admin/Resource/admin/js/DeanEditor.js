/**
 * Created by Dean on 16/12/14.
 */

var DeanEditor = function(opt){
    if(opt.em == '' || $(opt.em).size() == 0){
        console.warn('DeanEditor创建失败! '+ opt.em +' 找不到..');
        return false;
    }
    if(!document.execCommand){
        alert('浏览器不支持富文本编辑!');
        return false;
    }
    var $em = $(opt.em);
    var uploadURL = opt.uploadURL||0;
    var colors = ["rgb(51,51,51)", "rgb(153,153,153)", "rgb(255,0,0)", "rgb(255,102,0)", "rgb(255,204,0)", "rgb(51,153,0)", "rgb(0,204,255)", "rgb(0,51,255)", "rgb(255,0,255)"];
    if(localStorage.DeanEditorColors)colors = localStorage.DeanEditorColors.split('|');
    var that = this;
    var btns = {};
    var pp,dd,command,myRange,add_title;

    if (window.getSelection) {
        //主流的浏览器，包括mozilla，chrome，safari
        var selection = window.getSelection();
    } else if (document.selection) {
        var selection = document.selection.createRange();//IE浏览器下的处理，如果要获取内容，需要在selection 对象上加上text 属性
    }

    var $box = $('<div>').addClass('DeanEditor').height($em.height());
    var $header = $('<div>').appendTo($box);
    var $editor = $('<div>').attr('contenteditable', 'true').appendTo($box);
    var $text = $('<textarea>').appendTo($box);

    //功能按钮
    btns.bold = $('<button>').text('B').appendTo($header);
    btns.italic = $('<button>').text('I').appendTo($header);
    btns.underline = $('<button>').text('U').appendTo($header);
    btns.fontsize = $('<button>').text('T').appendTo($header);
    btns.lineheight = $('<button>').text('T').appendTo($header);
    btns.color = $('<button>').html('A<span></span>').appendTo($header);
    btns.backcolor = $('<button>').html('<span>A</span>').appendTo($header);
    $header.append('<i></i>');
    //添加五个栏目
    //btns.addtitle = $('<button>').text('Add').appendTo($header);
    $header.append('<i></i>');
    btns.left = $('<button>').appendTo($header);
    btns.center = $('<button>').appendTo($header);
    btns.right = $('<button>').appendTo($header);
    $header.append('<i></i>');
    btns.indent = $('<button>').appendTo($header);
    btns.outdent = $('<button>').appendTo($header);
    btns.unorderedlist = $('<button>').appendTo($header);
    btns.orderedlist = $('<button>').appendTo($header);
    $header.append('<i></i>');
    btns.quote = $('<button>').text('“').appendTo($header);
    btns.link = $('<button>').appendTo($header);
    btns.image = $('<button>').appendTo($header);
    $header.append('<i></i>');
    btns.clear = $('<button>').appendTo($header);
    btns.html = $('<button>').appendTo($header);
    $header.append('<i></i>');
    for(var i in btns){
        btns[i].attr('type', 'button');
    }

    //弹出框
    var code = '<div class="layBox sizeBox">';
    for(var i=1; i<8; i++){
        var n = 18 + i*4;
        if(i == 7)n += 10;
        code += '<button type="button" style="height:'+ n +'px; line-height:'+ n +'px"><font size="'+ i +'">字体大小</font></button>';
    }
    code += '</div>';
    var $sizeBox = $(code).appendTo($box);
    $sizeBox.find('button').on('click', function(){
        execCommand('FontSize', $(this).find('font').attr('size'));
    });

    var code = '<div class="layBox lineheightBox">';
    for(var i=1; i<8; i++){
        var n = 1 + i*0.5;
        code += '<button type="button">'+ n +'em</button>';
    }
    code += '</div>';
    var $lineheightBox = $(code).appendTo($box);
    $lineheightBox.find('button').on('click', function(){
        execCommand('FormatBlock', '<div>');
        var n = parseInt($(this).text());
        $editor.find('div').each(function(){
            //判断该节点是否全部被选中
            if(selection.containsNode(this, true)){
                $(this).css('line-height', n + 'em')
            }
        });
    });

    var code = '<div class="layBox colorBox">';
    code += '<div class="list">';
    for(var i in colors){
        code += '    <button type="button" style="background-color: '+ colors[i] +';"></button>';
    }
    code += '</div>';
    code += '<div class="diy">';
    code += '<ul>';
    code += '    <li><button type="button">-</button><em>33</em><button type="button">+</button></li>';
    code += '    <li><button type="button">-</button><em>33</em><button type="button">+</button></li>';
    code += '    <li><button type="button">-</button><em>33</em><button type="button">+</button></li>';
    code += '</ul>';
    code += '<div class="right">';
    code += '    <div class="color_point"></div>';
    code += '    <button type="button">添加</button>';
    code += '</div>';
    code += '</div>';
    code += '</div>';
    var $colorBox = $(code).appendTo($box);
    $colorBox.find('.list button').on('click', function(){
        setColor($(this).css('background-color'));
    });
    $colorBox.find('ul button').each(function(){
        $(this).on('mousedown', function(){
            var index = $colorBox.find('ul button').index(this);
            var color = [];
            $colorBox.find('em').each(function(){
                color.push(parseInt($(this).text()));
            });
            var n = Math.floor(index/2);
            var i = (index % 2)*2 - 1;
            clearInterval(pp);
            pp = setInterval(function(){
                if((i == 1 && color[n] < 255) || (i == -1 && color[n] > 0)){
                    color[n] += i;
                    $colorBox.find('em').eq(n).text(color[n]);
                    $colorBox.find('.color_point').css('background-color', 'rgb('+ color.join(',') +')');
                    setColor('rgb('+ color.join(',') +')');
                }else{
                    clearInterval(pp);
                }
            }, 100);
        }).on('mouseout mouseup', function(){
            clearInterval(pp);
        });
    });
    $colorBox.find('.right button').on('click', function(){
        color = $colorBox.find('.color_point').css('background-color');
        if(localStorage.DeanEditorColors)colors = localStorage.DeanEditorColors.split('|');
        if(colors.indexOf(color) == -1){
            colors.push(color);
            $colorBox.find('.list').empty();
            for(var i in colors){
                $('<button type="button">').css('background-color', colors[i]).appendTo($colorBox.find('.list')).on('click', function(){
                    setColor($(this).css('background-color'));
                });
            }
            localStorage.DeanEditorColors = colors.join('|');
        }
    });

    var code = '<div class="imageBox">';
    code += '<div>我的图片列表：<button type="button" class="close">×</button></div>';
    code += '<div>';
    code += '<button type="button" class="add"></button>';
    code += '</div>';
    code += '</div>';
    var $imageBox = $(code).appendTo($box);
    var pics = [];
    if(localStorage.DeanEditorImages)pics = JSON.parse(localStorage.DeanEditorImages);
    for(var i in pics){
        var img = $('<img>').attr('src', pics[i]);
        img.load(function(){
            var btn = $('<button type="button">');
            $(this).appendTo(btn);
            var div = $('<div>').text(this.width + ' × ' + this.height).appendTo(btn);
            btn.appendTo($imageBox.children('div:last'));
            btn.on('click', function(){
                execCommand('insertImage', $(this).find('img').attr('src'));
                $editor.find('img').css('max-width', '100%');
            });
        });
    }
    $imageBox.find('button.close').on('click', function(){
        $imageBox.removeClass('show');
    });
    $imageBox.find('button.add').on('click', function(){
        if($(this).hasClass('loading')){
            return;
        }
        //上传图片
        if($(window.uploadWindow).size() == 0){
            var $uploadWindow = $('<iframe width="0" height="0" name="uploadWindow"></iframe>');
            $uploadWindow.css({
                'position':'absolute',
                'width':'0px',
                'height':'0px'
            });
            $uploadWindow.appendTo('body');
        }else{
            var $uploadWindow = $(window.uploadWindow);
        }

        if($(window.uploadWindow.document.upload).size() == 0){
            window.uploadWindow.document.write('<form name="upload" action="'+ uploadURL +'" method="post" enctype="multipart/form-data"></form>');
        }
        if($(window.uploadWindow.document.upload.file).size() == 0){
            var $file = $('<input type="file" name="file" />').appendTo(window.uploadWindow.document.upload);
            $file.change(function(){
                $(window.uploadWindow.document.upload).submit();
                $imageBox.find('.add').addClass('loading');
                $uploadWindow.load(function(){
                    $imageBox.find('.add').removeClass('loading');
                    var path = $(window.uploadWindow.document).text();
                    if(!/^http:\/\//.test(path)){
                        parent.alert(path);
                        return false;
                    }
                    var img = $('<img>').attr('src', path);
                    img.load(function(){
                        var pics = [];
                        if(localStorage.DeanEditorImages)pics = JSON.parse(localStorage.DeanEditorImages);
                        pics.push(path);
                        localStorage.DeanEditorImages = JSON.stringify(pics);
                        var btn = $('<button type="button">');
                        $(this).appendTo(btn);
                        var div = $('<div>').text(this.width + ' × ' + this.height).appendTo(btn);
                        btn.appendTo($imageBox.children('div:last'));
                        btn.on('click', function(){
                            execCommand('insertImage', path);
                            $editor.find('img').css('max-width', '100%');
                        });
                    });
                    this.remove();
                });
            });
        }else{
            $file = $(window.uploadWindow.document.upload.file);
        }
        $file.click();
    });

    //添加五个栏目
    /*var code = '<div class="layBox addtitle">';
        code+='<button type="button">我的自述</button>';
        code+='<button type="button">我的项目</button>';
        code+='<button type="button">何为众筹</button>';
        code+='<button type="button">我的回报</button>';
        code+='<button type="button">详情进度</button>';
        code+='</div>';
    var $addTitleBox = $(code).appendTo($box);
    $addTitleBox.find('button').on('click', function(){
        //alert($(this).text());
        var top_str = $(this).text();
        var n = 1;
        if(top_str == '我的自述'){
            n=1;
        }else if(top_str == '我的项目'){
            n=2;
        }else if(top_str == '何为众筹'){
            n=3;
        }else if(top_str == '我的回报'){
            n=4;
        }else if(top_str == '详情进度'){
            n=5;

        }
        var str = '<div name="title_item_'+n+'" style="margin: 3.84rem 0rem"><div style="width: 7.8rem;height: 2.4rem;font-size: 1.6rem;font-weight: bold;color:#b39851;border: 0.1rem solid #b39851;border-left: 0.76rem solid #b39851;text-align:center;line-height: 2.4rem;margin: 0 0  1.4rem;">'+top_str+'</div><div style="font-size: 1.44rem;color:#b39851;margin-bottom: 3.8rem;line-height: normal;">在这里输入副标题...</div></div><div class="textContent" style="margin-bottom: 7.6rem; color: #666666">在这里输入内容...</div>';

        var n = $editor.find("div[name^='title_item_']").length;
        if( n<=0 ){
            execCommand('insertHTML', str);
        }else{
            $(str).appendTo($editor);
        }

    });*/

    //设置样式
    if(opt.class){
        $box.addClass(opt.class);
    }

    //设置功能
    function execCommand(name, value){
        document.execCommand(name, false, value);
    }
    //取消复制自动加链接
    execCommand("AutoUrlDetect", false);

    btns.bold.on('click', function(){execCommand('Bold');});
    btns.italic.on('click', function(){execCommand('Italic');});
    btns.underline.on('click', function(){execCommand('Underline');});
    btns.fontsize.on('mouseover click', function(){
        $sizeBox.fadeIn('fast');
    }).on('mouseout', function(){
        setTimeout(function(){
            if(!dd)$sizeBox.hide();
        }, 300);
    });
    $sizeBox.on('mouseover', function(){
        dd = true;
    }).on('mouseout', function(){
        dd = false;
        setTimeout(function(){
            if(!dd)$sizeBox.hide();
        }, 300);
    });
    btns.lineheight.on('mouseover click', function(){
        $lineheightBox.fadeIn('fast');
    }).on('mouseout', function(){
        setTimeout(function(){
            if(!dd)$lineheightBox.hide();
        }, 300);
    });
    $lineheightBox.on('mouseover', function(){
        dd = true;
    }).on('mouseout', function(){
        dd = false;
        setTimeout(function(){
            if(!dd)$lineheightBox.hide();
        }, 300);
    });
    btns.color.on('click', function(){
        execCommand('foreColor', $(this).find('span').attr('data'));
    }).on('mouseover', function(){
        command = 'foreColor';
        $colorBox.css('left', '159px').fadeIn('fast');
    }).on('mouseout', function(){
        setTimeout(function(){
            if(!dd)$colorBox.hide();
        }, 300);
    });
    $colorBox.on('mouseover', function(){
        dd = true;
    }).on('mouseout', function(){
        dd = false;
        setTimeout(function(){
            if(!dd)$colorBox.hide();
        }, 300);
    });
    btns.backcolor.on('click', function(){
        execCommand('backColor', $(this).find('span').attr('data'));
    }).on('mouseover', function(){
        command = 'backColor';
        $colorBox.css('left', '189px').fadeIn('fast');
    }).on('mouseout', function(){
        setTimeout(function(){
            if(!dd)$colorBox.hide();
        }, 300);
    });
    btns.left.on('click', function(){execCommand('justifyLeft');});
    btns.center.on('click', function(){execCommand('justifyCenter');});
    btns.right.on('click', function(){execCommand('justifyRight');});
    btns.indent.on('click', function(){execCommand('indent');});
    btns.outdent.on('click', function(){execCommand('outdent');});
    btns.unorderedlist.on('click', function(){execCommand('insertUnorderedList');});
    btns.orderedlist.on('click', function(){execCommand('insertOrderedList');});
    btns.quote.on('click', function(){execCommand('FormatBlock', '<blockquote>');});
    btns.link.on('click', function(){
        var hasUrl = false;
        $editor.find('a').each(function(){
            if(selection.containsNode(this, true)){
                hasUrl = true;
                execCommand('unlink');
            }
        });
        if(!hasUrl){
            var url = window.prompt('连接地址', 'http://..');
            if(/^(http|https):\/\//.test(url)){
                execCommand('createLink', url);
            }
        }
    });
    btns.image.on('click', function(){
        if($imageBox.hasClass('show'))
            $imageBox.removeClass('show');
        else
            $imageBox.addClass('show');
    });
    btns.clear.on('click', function(){execCommand('removeFormat');});
    btns.html.on('click', function(){
        if($text.css("display") == 'none'){
            $(this).addClass('code');
            $text.val($editor.html());
            $text.show();
            $editor.hide();
        }else{
            $(this).removeClass('code');
            $editor.html($text.val());
            $text.hide();
            $editor.show();
        }
    });

    //添加五个栏目
    /*btns.addtitle.on('mouseover click', function(){
        $addTitleBox.fadeIn('fast');
    }).on('mouseout', function(){
        setTimeout(function(){
            if(!add_title)$addTitleBox.hide();
        }, 300);
    });

    $addTitleBox.on('mouseover', function(){
        add_title = true;
    }).on('mouseout', function(){
        add_title = false;
        setTimeout(function(){
            if(!add_title)$addTitleBox.hide();
        }, 300);
    });*/

    $em.before($box);
    $em.hide();
    $editor.html($em.val());
    $editor.on('blur', function(){
        $em.val($(this).html());
    });
    $text.on('blur', function(){
        $em.val($(this).val());
    });

    //设置Hex色码的颜色
    function setColor(color){
        if(!/^rgb\([\d, ]+\)/.test(color)){
            return false;
        }
        var c = color.replace(/^rgb\((\d+).+?(\d+).+?(\d+)\)$/, function(){
            var arr = [];
            for(var i=1; i<4; i++){
                var a = parseInt(arguments[i]).toString(16);
                if(a.length == 1)a = '0' + a;
                arr.push(a);
            }
            return '#' + arr.join('');
        });
        execCommand(command, c);
        if(command == 'foreColor')$header.find('button:eq(5) > span').css('background', color).attr('data', c);
        if(command == 'backColor')$header.find('button:eq(6) > span').css('background', color.replace(/^rgb\(([\d, ]+)\)$/, function(a,b){return 'rgba('+ b +', 0.2)';})).attr('data', c);
    }

    function getRange(){
        var range = selection.getRangeAt(0);
        if(selection.containsNode($editor[0], true) && !selection.containsNode($editor[0], false)){
            return range;
        }else{
            return false;
        }
    }

    //获取HTML代码
    this.getHtml = function(){
        return $editor.html();
    }

    //获取text文本
    this.getText = function(){
        return $editor.text();
    }

    //输入HTML代码
    this.InputHtml= function(str){
        //alert(str);
        $editor.html(str);
    }
}