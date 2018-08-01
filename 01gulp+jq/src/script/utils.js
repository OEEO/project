define(['jquery'], function ($) {
    function splitKDA(kda) {
        if (kda.match(/(\d+)-(\d+)-(\d+)/)) {
            return kda.split('-');
        } else {
            throw new Error('kda should be like “K-D-A”');
        }
    }

    function execKDA(k, d, a) {
        return parseInt(d) === 0
            ? parseInt(k) + parseInt(a)
            : (parseInt(k) + parseInt(a)) / parseInt(d);
    }

    function doubleToPer(d) {
        if (d) {
            return (d * 100).toFixed(1) + '%';
        } else {
            return '0';
        }
    }

    /**
     * 获取js脚本数据
     * @param {String} url 
     * @param {String} pagename 
     * @param {Object} options
     * @param {Function} callback 
     */
    var getScript = function (url, pagename, options, callback) {

        if (isFunction(options)) {
            callback = options;
        } else {
            callback = callback || function () {};
        }
        

        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = url;
        script.setAttribute('charset', 'UTF-8');
        script.setAttribute('data-page', pagename);
        script.setAttribute('async', 'true');
        script.onreadystatechange = function () {  // ie
            var r = script.readyState; 
            if (r === 'loaded' || r === 'complete') { 
                script.onreadystatechange = null; 
                script.parentNode.removeChild(script);
                callback(null, window[pagename]); 
            } 
        }; 

        script.onload = function () {
            script.parentNode && script.parentNode.removeChild(script);
            callback(null, window[pagename]);
        };

        script.onerror = function (e) {
            callback(e);
        };

        var s = document.getElementsByTagName('script')[0]; 
        s.parentNode.insertBefore(script, s);
    };

    var hasOwnProperty = function (obj, key) {
        return Object.prototype.hasOwnProperty.call(obj, key);
    };

    /**
     * 把Object对象转化为“key=val&key2=val2”类型
     * @param {Object} obj 
     */
    var stringify = function (obj) {
        var str = '';
        for (var key in obj) {
            if (hasOwnProperty(obj, key)) {
                if (obj[key] !== null && obj[key] !== undefined) {
                    str += key + '=' + obj[key] + '&';
                }
            }
        }
        return str.slice(0, -1);
    };

    /**
     * 获取url中参数为name的值
     * @param {String} name 
     */
    var getQueryString = function (name) {
        var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
        var r = window.location.search.slice(1).match(reg);
        if (r !== null) {
            return decodeURI(r[2]);
        }
        return null;
    };

    /**
     * 时间转化
     * @param {Date} date date对象
     * @param {String} fmt 例如：yyyyMMdd格式
     */
    var timeFormat = function (date, fmt) {
        var o = {
            'M+': date.getMonth() + 1, //月份
            'd+': date.getDate(), //日
            'h+': date.getHours(), //小时
            'm+': date.getMinutes(), //分
            's+': date.getSeconds(), //秒
            'q+': Math.floor((date.getMonth() + 3) / 3), //季度
            'S': date.getMilliseconds(), //毫秒
            'D+': date.getDay(), // 星期几
        };
        if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (date.getFullYear() + '').substr(4 - RegExp.$1.length));
        for (var k in o) {
            if (new RegExp('(' + k + ')').test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (('00' + o[k]).substr(('' + o[k]).length)));
        }
        return fmt;
    };

    var format = function (str, n, label) {
        label = label || '0';
        for (var i = 0; i < n; i++) {
            str = label + str;
        }

        return str.substr(str.length - n, n);
    };

    function parseISO8601(dateStringInRange) {
        var isoExp = /^\s*(\d{4})-(\d+)-(\d+)\s*$/,
            date = new Date(NaN), month,
            parts = isoExp.exec(dateStringInRange);

        if (parts) {
            month = +parts[2];
            date.setFullYear(parts[1], month - 1, parts[3]);
            if (month !== date.getMonth() + 1) {
                date.setTime(NaN);
            }
        }
        return date;
    }

    function isFunction(fn) {
        return Object.prototype.toString.call(fn) === '[object Function]';
    }

    return {
        splitKDA: splitKDA,
        execKDA: execKDA,
        doubleToPer: doubleToPer,
        getScript: getScript,
        hasOwnProperty: hasOwnProperty,
        stringify: stringify,
        getQueryString: getQueryString,
        timeFormat: timeFormat,
        format: format,
        parseISO8601: parseISO8601,
        isFunction: isFunction
    };
});