const CommonProxy = require('./index');
const R = require('../../utils/redisUtil');

class EventProxy extends CommonProxy {
    getEventList(game, id) {
        return R.lrangeData(`${game}-${id}:${this.key}`, 0, -1);
    }

    getNearEvents(game, id, time, limitTime = 20) {
        return this
            .getEventList(game, id)
            .then(list => {
                let result = [];

                for (let item of list) {
                    item = JSON.parse(item);
                    if (Math.abs(item.time - time) <= limitTime) {
                        result.push(item);
                    }
                }

                return result;
            });
    }

    setEventLine(zKey, sKey, data, time) {
        return R
            .zAdd(zKey, sKey, time)
            .then(_ => {
                return R.setDataForKey(sKey, JSON.stringify(data));
            });
    }

    delEventLine(game, id, time) {
        return R    
            .zRem(`${game}-${id}:eventLine`, `${game}-${id}-event:${time}`)
            .then(_ => {
                return R.delDataForKey(`${game}-${id}-event:${time}`);
            });
    }

    isInflextion(game, id, time) {
        return R
            .zCount(`${game}-${id}:eventLine`, time, time)
            .then(c => {
                return c >= 1;
            });
    }

    getGradientEventIds(game, id) {
        return R
            .zRange(`${game}-${id}:eventLine`, 0, -1);
    }

    getEventLine(game, id) {
        return this
            .getGradientEventIds(game, id)
            .then(ids => {
                return R.mGetDataByKeys(ids);
            });
    }
}   

module.exports = new EventProxy('event');