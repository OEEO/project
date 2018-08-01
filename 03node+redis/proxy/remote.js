const { get, post } = require('../utils/request');
const baseInfoProxy = require('./redis/baseInfo');
const  { ocrApiDomain } = require('../config');

function obj2Array(obj) {
    let result = [];
    for (let key in obj) {
        result.push(obj[key]);
    }

    return result;
}

function getGoldTimeLine(id, game = 'kog') {
    return get(`${ocrApiDomain}/api/live/golds`, {
        matchId: id,
        game: game
    });
}

/**
 * 计算标准差
 * @param {Array} data 
 */
function calStd(data) {
    let avg_data = data.reduce((a, b) => a + b) / data.length;

    let result = 0;

    for (let item of data) {
        result += Math.pow((item - avg_data), 2);
    }

    return Math.sqrt(result / (data.length));
}

/**
 * 计算数组内的曲线斜率
 * @param {Array} data 
 */
function calGradient(data) {
    let l = data.length;
    let avg_index = parseInt(l / 2);


    let deg1 = (data[avg_index] - data[0]) / avg_index;
    let deg2 = (data[l - 1] - data[avg_index]) / (l - avg_index - 1);

    return deg1 ^ deg2;
}

/**
 * 计算数组内经济的差异
 * @param {Array} golds
 * @param {Function} fn 计算的方法 
 */
function calGoldsStd(golds, fn = calStd) {
    let _arr = [];

    for (let item of golds) {
        _arr.push(item.left_total - item.right_total);
    }

    return fn(_arr);
}


/**
 * 预处理经济
 * @param {Array} golds 
 */
function preProcessGolds(golds) {

    let _ = {};

    golds = golds.map(item => {
        item.left_total = item.left.reduce((a, b) =>  a + b);
        item.right_total = item.right.reduce((a, b) => a + b);
        return item;
    }); 

    for (let item of golds) {
        _[item.time] = item;
    }

    return obj2Array(_);
}

function hasOwnProperty(obj, key) {
    return Object.prototype.hasOwnProperty.call(obj, key);
}

/**
 * 生成拐点
 * @param {Array} golds 经济曲线
 * @param {Number} win 窗口大小
 */
function generateInflexion(golds, win = 3, type = 'std') {
    golds = preProcessGolds(golds);

    let startIndex = parseInt(win / 2);

    let fn = type === 'std'
        ? calStd
        : calGradient;
    let result = [];

    // for (let i = startIndex, num = golds.length; i < num - win / 2; i += 1) {
    //     let processGolds = golds.slice(i - parseInt(win / 2), i + Math.ceil(win / 2));
        
    //     let std = calGoldsStd(processGolds, fn);

    //     // golds[i].std = std;
    //     result.push(Math.abs(std));
    // }

    // let MAX = result.sort((a, b) => b - a).slice(0, 5);

    // golds.forEach(item => {
    //     if (hasOwnProperty(item, 'std') && MAX.indexOf(Math.abs(item.std)) === -1) {
    //         delete item.std;
    //     }
    // });

    return golds;
    // return result;
}

function buildGoldsLine(id, game = 'kog', win = 5, type = 'gradient') {
    return getGoldTimeLine(id, game)
        .then(data => {
            // console.log(data);
            win = win 
                ? +win
                : 20;
            return generateInflexion(data.data, win, type);
        });
}

function getBaseInfo(id, game) {
    return get(`${ocrApiDomain}/api/baseInfo`, {
        matchId: id,
        game: game
    });
}

function syncBaseInfo(id, game) {
    return getBaseInfo(id, game)
        .then(data => {
            if (data.status === 'ok') {
                return baseInfoProxy.setBaseInfo(game, id, data.data);
            } else {
                throw new Error(data.msg);
            }
        });
}

function getBp(id, game) {
    console.log(`${ocrApiDomain}/api/bp`);
    return get(`${ocrApiDomain}/api/bp`, {
        matchId: id, 
        game: game
    })
    .then(data => {
        console.log(data);
        if (data.status === 'ok') {
            return data.data;
        } else {
            return null;
        }
    });
}

function setBp(id, game, data, r = 'n') {
    return post(`${ocrApiDomain}/api/bp`, {
        matchId: id, 
        game: game,
        data: data,
        r: r
    }, {
        'Content-Type': 'application/json'
    })
    .then(res => {
        if (res.status === 'error') {
            console.error(res.msg);
        }
        return res.status;
    });
}

function getPlayerIndex(game, id) {
    return get(`${ocrApiDomain}/api/live/playerIndex`, {
        game: game,
        matchId: id
    })
    .then(res => {
        if (res.status === 'ok') {
            return res.data;
        } else {
            return null;
        }
    });
}

module.exports = {
    buildGoldsLine,
    syncBaseInfo,
    getBp,
    setBp,
    getPlayerIndex
};