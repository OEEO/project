/**
 * Created by yuhuajie on 2017/4/7.
 */
const axios = require('axios');
const http = require('http');
const url = require('url');
const querystring = require('querystring');


/**
 * Post请求
 * @param url {String} 请求地址
 * @param param {Object} 请求体
 * @param header {Object} 请求头
 * @returns {Promise<R2|R1>|Promise|Promise.<TResult>|Promise<R>|*}
 */
exports.post = (url, param = {}, header = {}) => {

    let _header = Object.assign({
        'Content-Type': 'application/x-www-form-urlencoded',
        // 'web': 'true'
    }, header);

    return axios({
        url: url,
        method: 'post',
        data: param,
        timeout: 10000,
        transformRequest: [function (data) {
            // Do whatever you want to transform the data
            if (_header['Content-Type'] === 'application/x-www-form-urlencoded') {
                let ret = '';
                for (let it in data) {
                    if (Object.prototype.hasOwnProperty.call(data, it)) {
                        ret += encodeURIComponent(it) + '=' + encodeURIComponent(data[it]) + '&';
                    }
                }
                return ret.slice(0, -1);
            } else {
                return JSON.stringify(data);
            }
        }],
        headers: _header
    }).then(res => {
        _header = null;
        if (res.statusText === 'OK') {
            return res.data;
        } else {
            throw new Error('RequestError');
        }
    });
};

/**
 * Get请求
 * @param url {String} 请求的url
 * @param params {Object} 查询参数
 * @param header {Object} 请求头
 * @param proxy {Object} 代理 {host,port, auth: {username, password}}
 * @param cookie 是否获取Cookie
 * @param cookieName 获取的Cookie的名字，没有，默认全部cookie获取
 * @returns {Promise<R2|R1>|Promise|Promise.<TResult>|Promise<R>|*}
 */
exports.get = (url, params = null, header = {}, proxy = null, cookie = false, cookieName = '') => {

    let query = params ? querystring.stringify(params) : '';
    let _url = query !== '' ? `${url}?${query}` : url;
    let _header = Object.assign({}, header);

    return axios.get(_url, {
        headers: _header,
        timeout: 15000,
        proxy: proxy
    }).then(res => {
        _header = null;
        query = null;
        _url = null;

        if (res.statusText === 'OK') {

            if (!cookie) {
                return res.data;
            } else {

                let cookieStr = res.headers['set-cookie'].join(';');

                return {
                    data: res.data,
                    cookie: cookieName ? getCookie(cookieStr, cookieName) : cookieStr
                };
            }

        } else {
            throw new Error('RequestError');
        }
    });
};

function getCookie(cookie, name) {
    let reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)');
    let res = cookie.match(reg);

    return res ? res[2] : null;
}