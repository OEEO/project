const R = require('../../utils/redisUtil');
const { isObject } = require('../../utils');

class CommonProxy {
    constructor(key) {
        this.key = key;
    }

    getData(game, id, key) {
        key = key || this.key;
        return R
            .getDataByKey(`${game}-${id}:${key}`)
            .then(data => {
                if (data) {
                    return JSON.parse(data);
                } else {
                    return null;
                }
            });
    }

    setData(game, id, data, key) {

        if (isObject(data)) {
            data.__update_time__ = parseInt(Date.now() / 1000);
            data = JSON.stringify(data);
        }

        key = key || this.key;

        return R
            .setDataForKey(`${game}-${id}:${key}`, data)
            .then(r => {
                // 入队列
                return R.zAdd(`${key}-list`, `${game}-${id}:${key}`, parseInt(Date.now() / 1000));
            });
    }

    static getList(key, withScore = '') {
        return R    
            .zRange(`${key}-list`, 0, -1, withScore);
    }
}

module.exports = CommonProxy;