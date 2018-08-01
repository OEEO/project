const CommonProxy = require('./index');
const R = require('../../utils/redisUtil');

class BpProxy extends CommonProxy {
    getBpData(game, id) {
        return this
            .getData(game, id, 'bpR')
            .then(data => {
                if (!data) {
                    return this.getData(game, id);
                } else {
                    return data;
                }
            });
    }

    /**
     * 
     * @param {String} game 
     * @param {Number} id 
     * @param {Object} data 
     * @param {String} r 是否强制覆盖ocr生成的数据。y -- 覆盖，n -- 不覆盖 
     */
    setBpData(game, id, data, r = 'n') {
        if (r === 'n') {
            return this.setData(game, id, data);
        } else {
            return this.setData(game, id, data, 'bpR');
        }
    }
}

module.exports = new BpProxy('bp');