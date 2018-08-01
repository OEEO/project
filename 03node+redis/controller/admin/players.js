const playersProxy = require('../../proxy/redis/players');
const baseInfoProxy = require('../../proxy/redis/baseInfo');

let obj = {};

module.exports = obj;

/**
 * @api {get} /admin/players/ 获取游戏的选座情况
 * @apiGroup admin/players
 * @apiName getPlayersById
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏名，lol或kog
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": {
 *      "left": [
 *          { "name": "", "playerId": "", teamId: "" }
 *      ],
 *      "right": [
 *          RIGHT
 *      ]
 *  }
 * }
 * 
 * @apiErrorExample Error-Response:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }
 */
obj.getPlayersById = (req, res) => {
    let id = req.query.id; 
    let game = req.query.game;

    playersProxy
        .getPlayers(game, id)
        .then(data => {
            res.json({
                status: 'success',
                msg: '',
                data: data
            });
        })
        .catch(exp => {
            console.error(exp);
            res.json({
                status: 'error',
                msg: exp.toString(),
                data: null
            });
        });
};

/**
 * @api {post} /admin/players/ 设置游戏的选座情况
 * @apiGroup admin/players
 * @apiName setPlayers
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏名，lol或kog
 * @apiParam {Object} data 选座情况
 * 
 * 
 * @apiExample 测试请求体
 * {
 *  "id": 1,
 *  "game": "kog",
 *  "data": {
 *      "left": [
 *          { "name": "name", "playerId": "1", "teamId": "4375" }
 *      ],
 *      "right": [
 *          { "name": "name", "playerId": "1", "teamId": "4375" }
 *      ]
 *  }
 * }
 * 
 * @apiSuccessExample SuccessResponse:
 * {
 *  "status": "success",
 *  "msg": ""
 * }
 * 
 * @apiErrorExample ErrorResponse:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }
 */
obj.setPlayers = (req, res) => {
    let id = req.body.id;
    let game = req.body.game;
    let data = req.body.data;

    playersProxy
        .setPlayers(game, id, data)
        .then(r => {
            res.json({
                status: 'success',
                msg: ''
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
 * @api {get} /admin/players/baseInfo 获取当局基本对位信息
 * @apiGroup admin/players
 * @apiName getBaseInfo
 * @apiParam {String} game 游戏名，kog或lol
 * @apiParam {Number} id 游戏id
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": {
 *      "left": {
 *          "players": [
 *          {
 *              "heroId": "英雄Id",
 *              "heroName": "英雄名"
 *          }
 *      ]
 *      },
 *      "right": RIGHT,
 *      "__update_time__": "更新的时间戳"
 *  }
 * }
 */
obj.getBaseInfo = (req, res) => {
    let game = req.query.game;
    let id = req.query.id;

    baseInfoProxy
        .getBaseInfo(game, id)
        .then(data => {
            res.json({
                status: 'success',
                msg: '',
                data: data
            });
        })
        .catch(exp => {
            res.json({
                status: 'error',
                msg: exp.toString()
            });
        });
};