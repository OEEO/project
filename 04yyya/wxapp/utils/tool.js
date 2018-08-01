function timeFormat(format, t) {
    var time = t.toString();
    if (/^\d+$/.test(time)) {
        var myDate = new Date(time * 1000);
    } else {
        time = time.replace(/\-/g, '/');
        var myDate = new Date(time);
    }
    var _date = {};
    _date.Y = myDate.getFullYear();
    _date.m = (myDate.getMonth() + 1).toString();
    if (_date.m.length == 1) _date.m = '0' + _date.m;
    _date.d = myDate.getDate().toString();
    if (_date.d.length == 1) _date.d = '0' + _date.d;
    _date.H = myDate.getHours();
    _date.i = myDate.getMinutes().toString();
    if (_date.i.length == 1) _date.i = '0' + _date.i;
    _date.s = myDate.getSeconds().toString();
    if (_date.s.length == 1) _date.s = '0' + _date.s;
    _date.w = myDate.getDay().toString();
    var weekday = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
    _date.W = weekday[myDate.getDay()];
    for (var i in _date) {
        format = format.replace(i, _date[i]);
    }
    return format;
}
function stamp(str_time) {
    var new_str = Date.parse(new Date(str_time));
    new_str = new_str / 1000;
    return new_str
}
function abs(str, s) {
    if (str.indexOf(s) < 0) {
        return false;
    }
    var a = str.indexOf(s);
    return str.substring(a + s.length);
}

function calLessDay(current, end, start, format, successed) {
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
        format = (_f['d+'] + 1) + '天';
    } else if (_f['h+'] > 0) {
        format = _f['h+'] + '小时'
    } else if (_f['m+'] > 0) {
        format = _f['m+'] + '分钟'
    } else if (_f['s+'] > 0) {
        format = _f['s+'] + '秒'
    }

    return format;
}

function calLessDayIndex (current, end, start, format, successed) {
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
}

function _setStr(str) {
    return ('00' + str).slice(-2);
}

function countDown(startTime, endTime) {
    // console.log(startTime, endTime);

    if (isNaN(+startTime) || isNaN(+endTime)) {
        console.error('startTime and endTime should be a Number');
        return ['00','00', '00', '00'];
    }

    var diff = endTime - startTime;

    

    return [
        _setStr(Math.floor(diff / 86400)),
        _setStr(Math.floor(diff / 3600 % 24)),
        _setStr(Math.floor(diff / 60 % 60)),
        _setStr(Math.floor(diff % 60))
    ];
}

module.exports = {
    timeFormat: timeFormat,
    stamp: stamp,
    abs: abs,
    calLessDay: calLessDay,
    calLessDayIndex: calLessDayIndex,
    countDown: countDown
}