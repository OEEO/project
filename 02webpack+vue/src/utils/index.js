exports.splitKDA = function (kda) {
    if (kda.match(/(\d+)-(\d+)-(\d+)/)) {
        return kda.split('-');
    } else {
        throw new Error('kda should be like “K-D-A”');
    }
};

exports.execKDA = function (k, d, a) {
    return parseInt(d) === 0
        ? parseInt(k) + parseInt(a)
        : (parseInt(k) + parseInt(a)) / parseInt(d);
};

exports.doubleToPer = function (d) {
    if (d) {
        return (d * 100).toFixed(1) + '%';
    } else {
        return '0';
    }
};

/**
 * 获取js脚本数据
 * @param {String} url 
 * @param {String} pagename 
 * @param {Function} callback 
 */
exports.getScript = function (url, pagename, callback) {
    callback = callback || function () {};

    let script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;
    script.setAttribute('charset', 'UTF-8');
    script.setAttribute('data-page', pagename);
    script.setAttribute('async', 'true');
    script.onload = function () {
        document.head.removeChild(script);
        callback(null);
    };
    script.onerror = function (e) {
        callback(e);
    };

    document.head.appendChild(script);
};

exports.removeScript = function (pagename) {
    let script = document.querySelector('[data-page="' + pagename + '"]');
    if (script !== null) {
        document.head.removeChild(script);
    }
};

/**
 * 节流函数
 * @param method 方法 {Function}
 * @param delay 延时 {Number}
 * @param duration 持续时间 {Number}
 * @returns {Function}
 */
exports.throttle = function (method, delay, duration) {
    let timer = null, begin = new Date();
    return function (val, oldVal) {
        let context = this, args = arguments, current = new Date();
        clearTimeout(timer);
        if (current - begin >= duration) {
            method.apply(context, args);
            begin = current;
        } else {
            timer = setTimeout(function () {
                method.apply(context, args);
            }, delay);
        }
    };
};

exports.hasOwnProperty = function (obj, key) {
    return Object.prototype.hasOwnProperty.call(obj, key);
};

exports.obj2Array = function (obj) {
    let arr = [];
    for (let key in obj) {
        if (exports.hasOwnProperty(obj, key)) {
            let item = obj[key];
            arr.push(item);
        }
    }
    return arr;
};

exports.stringify = function (obj) {
    let str = '';
    for (let key in obj) {
        if (exports.hasOwnProperty(obj, key)) {
            if (obj[key] !== null && obj[key] !== undefined) {
                str += `${key}=${obj[key]}&`;
            }
        }
    }
    return str.slice(0, -1);
};

exports.getUrlParams = function () {
    return this.$route.query;
};

/**
 * 获取url中，key为name的参数
 * 如：?name=val
 * @param {String} name 
 */
exports.getQueryString = function (name) {
    let reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
    let r = window.location.search.slice(1).match(reg);
    if (r != null) {
        return decodeURI(r[2]);
    }
    return null;
};

exports.isFunction = function (fn) {
    return Object.prototype.toString.call(fn) === '[object Function]';
};

/**
 * 时间转化
 * @param {Date} date date对象
 * @param {String} fmt 例如：yyyyMMdd格式
 */
exports.timeFormat = function (date, fmt) {
    let o = {
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
    for (let k in o) {
        if (new RegExp('(' + k + ')').test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (('00' + o[k]).substr(('' + o[k]).length)));
    }
    return fmt;
};

exports.format = function (str, n, label = '0') {
    for (let i = 0; i < n; i++) {
        str = label + str;
    }

    return str.substr(-n);
};