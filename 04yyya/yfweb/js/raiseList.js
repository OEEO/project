/**
 * Created by fyt on 2016/10/18.
 */
var raiseListObject = {
    winScrollSock: false,
    raise_page: 1,
    getLastTime: function (current, end, start, format, successed) {
        if (current < start) {
            return '即将上线';
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
            format = _f['d+'] + '天';
        } else if (_f['h+'] > 0) {
            format = _f['d+'] + '小时'
        } else if (_f['m+'] > 0) {
            format = _f['d+'] + '分钟'
        } else if (_f['s+'] > 0) {
            format = _f['d+'] + '秒'
        }

        return format;
    },
    // 加载列表
    roadList: function (page) {
        if ($('.page_raiseList center').size() > 0) return;
        var page = page || raiseListObject.raise_page;
        ajax('Goods/Raise/getlist', { get: { page: page } }, function (d) {
            if (d.info) {
                $.alert(d.info, 'error');
                return;
            }

            if (d.length > 0) {
                var code = '';
                var cur = parseInt(Date.now() / 1000);

                //
                if (raiseListObject.raise_page === 1) {
                    // build temp raise
                }


                for (var i in d) {
                    code += '<div class="items" onclick="javascript:jump(\'raiseDetail\', {raise_id:' + d[i].id + '})">';
                    code += '<img class="raiseimg" src="' + d[i].path + '"/>';
                    code += '<div class="items-top">';
                    code += '<div class="raise-city"><img src="../images/row_button.png" />' + d[i].city_name + '</div>';
                    code += '<span class="name">' + d[i].nickname + '</span>';
                    // code +=         '<span class="address">广州</span>';
                    code += '</div>';
                    code += '<div class="raise-title">' + d[i].title + '</div>';
                    code += '<p class="dec">' + d[i].introduction + '</p>';

                    var per = +d[i].total === 0 ? 0 : (+d[i].totaled / +d[i].total)  * 100;
					per = per.toFixed(2);
                    code += '<div class="shell">';
                    code += '<div class="c_line">';
                    code += '<span style="width: ' + per + '%;"></span></div></div>';
                    code += '<div class="sublist">';

                    
                    code += '<span><font name="percent">' + per + '</font>%</span>';
                    code += '<span><font name="sum">' + (d[i].buyer_num || 0) + '</font>人认筹</span>';

                    var end = +d[i].end_time;
                    var time = raiseListObject.getLastTime(cur, end, +d[i].start_time, null, d[i].totaled - d[i].total);
                    code += '<span name="days">' + time + '</span></div>';

                    code += '</div>';
                    code += '<div class="the_blank"></div>';
                }
                if (page == 1)
                    $('.page_raiseList .raiseitem').html(code);
                else
                    $('.page_raiseList .raiseitem').append(code);
                raiseListObject.winScrollSock = false;
            } else {
                if (page == 1) {
                    $('.page_raiseList .raiseitem').html('<div class="no_msgs"><img src="images/category_over.png" /><span>暂时还没有' + categoryObject.catg + '活动哦~</span></div>');
                } else {
                    $('.page_raiseList .raiseitem').append('<li class="the_end"><div class="no_more"></div></li>');
                }
            }

        }, 2);
    },
    onload: function () {
        // script.load('plugins/scrollByJie', function () {
        //     /***********ajax请求页面头部bander数据**************/
        //     ajax('Home/Index/banner', {type: 3}, function (d) {
        //         var sol = new myScroll();
        //         sol.speed = 3;
        //         //sol.height = win.width * 0.4;
        //         sol.div = ".pageHead";
        //         for (var i in d) {
        //             sol.src.push(d[i].path.pathFormat());
        //             sol.link.push(d[i].url);
        //         }
        //         sol.start();
        //     }, 2);
        // });
        $('.page_raiseList.wrapper').scroll(function () {
            //滚动加载内容
            if ($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !raiseListObject.winScrollSock) {
                raiseListObject.winScrollSock = true;
                raiseListObject.roadList(Math.ceil($('.page_raiseList .raiseitem .items').size() / 5 + 1));
            }
        });
        raiseListObject.roadList();
    }
};