const eventProxy = require('../../proxy/redis/event');
const baseInfoProxy = require('../../proxy/redis/baseInfo');
const { getPlayerIndex } = require('../../proxy/remote');

let obj = {};

module.exports = obj;

function grepPlayer(item, baseInfo, players) {
    for (let key in item) {
        if (/^p\d+$/.test(key)) {
            item[key] = _getPlayerBaseInfo(+item[key], baseInfo, players);
        }
    }

    return item;
}

function _getPlayerBaseInfo(index, baseInfo, players) {

    if (index >= 0 && index < 10 && baseInfo && players) {
        let side = index >= 5
            ? 'right'
            : 'left';

        return Object.assign({}, baseInfo[side].players[index % 5], players[side][index % 5]);
    } else {
        return null;
    }
    
}

/**
 * @api {get} /api/event/list 获取事件列表
 * @apiGroup api
 * @apiName getEventLine
 * @apiParam {String} gametype=kog 游戏,kog或lol
 * @apiParam {Number} matchId,游戏id
 * @apiParam {Number} game 第几局
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": [
 *  {
 *  "p1": {"heroId": "ID", "heroName": "heroName", "name": "队员名", "teamId": "队伍id", "playerId": "队员id"},
 *  "p2": null,
 *  "timestamp": "时间戳",
 *  "time": "时间发生时间,单位s",
 *  "type": "事件类型值"
 *  }
 * ]
 * }
 */
obj.getEventLine = (req, res) => {
    let game = req.query.game;
    let gametype = req.query.gametype || 'kog';
    let id = req.query.matchId;

    id += game;

    let list = [];
    let baseInfo = null;
    // let players = [];

    eventProxy
        .getEventList(gametype, id)
        .then(data => {
            list = data.map(item =>  JSON.parse(item));
            return baseInfoProxy.getBaseInfo(gametype, id);
        })
        .then(data => {
            baseInfo = data;
            return getPlayerIndex(gametype, id);
        })
        .then(_players => {
            list = list.map(item => {
                return grepPlayer(item, baseInfo, _players);
            });

            res.json({
                status: 'success',
                msg: '',
                data: list
            });
        })
        .catch(exp => {
            console.error(exp);
            res.json({
                status: 'error',
                msg: exp.toString()
            });
        });
};


/**
 * @api {get} /api/gradient/event 获取拐点事件列表
 * @apiGroup api
 * @apiName getGradientEvent
 * @apiParam {String} gametype=kog 游戏,kog或lol
 * @apiParam {Number} matchId,游戏id
 * @apiParam {Number} game 第几局
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": [
 *  {
 *  "p1": {"heroId": "ID", "heroName": "heroName", "name": "队员名", "teamId": "队伍id", "playerId": "队员id"},
 *  "p2": null,
 *  "timestamp": "时间戳",
 *  "time": "时间发生时间,单位s",
 *  "type": "事件类型值"
 *  }
 *  ]
 * }
 */
obj.getGradientEvent = (req, res) => {
    let game = req.query.game;
    let gametype = req.query.gametype || 'kog';
    let id = req.query.matchId;

    id += game;

    let list = [];
    let baseInfo = null;

    eventProxy
        .getEventLine(gametype, id)
        .then(data => {
            list = data.map(item => JSON.parse(item));

            return baseInfoProxy.getBaseInfo(gametype, id);
        })
        .then(data => {
            baseInfo = data;
            return getPlayerIndex(gametype, id);
        })
        .then(_players => {

            list = list.map(item => {
                return grepPlayer(item, baseInfo, _players);
            });

            res.json({
                status: 'success',
                msg: '',
                data: list
            });
        });
};