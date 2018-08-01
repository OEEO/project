const CommonProxy = require('./index');
const R = require('../../utils/redisUtil');
const { isObject, isArray } = require('../../utils/');

class BuffProxy extends CommonProxy {
    getBuff(game, id) {
        return R
            .hGetall(`${game}-${id}:${this.key}`)
            .then(data => {
                return data;
            });
    }

    setBuff(game, id, data) {

        if (isObject) {
            data.__update_time__ = parseInt(Date.now() / 1000);
        } else {
            data = [...data, '__update_time__', parseInt(Date.now() / 1000)];
        }

        return R
            .hmSet(`${game}-${id}:${this.key}`, data)
            .then(r => {
                if (r === 'OK') {
                    return R.zAdd(`${this.key}-list`, `${game}-${id}:${this.key}`, parseInt(Date.now() / 1000));
                } else {
                    return 'false';
                }
            });
    }
}

module.exports = new BuffProxy('buff');