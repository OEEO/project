const CommonProxy = require('./index');

class BaseInfoProxy extends CommonProxy {
    setBaseInfo(game, id, data) {
        return this.setData(game, id, data);
    }

    getBaseInfo(game, id) {
        return this.getData(game, id);
    }
}

module.exports = new BaseInfoProxy('baseInfo');