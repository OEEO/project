const playerJSON = require('../../data/players.json');
const kogSkill = require('../../data/skillKog.json');
const lolSkill = require('../../data/skillLol.json');
const playersProxy = require('../../proxy/redis/players');
const RedisProxy = require('../../proxy/redis');

let obj = {};

module.exports = obj;

/**
 * @api {get} /admin/home/players 获取队员列表
 * @apiGroup admin/home
 * @apiName getPlayers
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": [
 *      {
 *          "label": "王者荣耀",
 *          "value": "kog",
 *          "children": [
 *              {
 *                  "label": "队伍名",
 *                  "value": "队伍id",
 *                  "children": [
 *                      {
 *                          "label": "队员名",
 *                          "value": "队员id"
 *                      }
 *                  ]
 *              }
 *          ]
 *      }
 *  ]
 * }
 */
obj.getPlayers = (req, res) => {
    res.json({
        status: 'success',
        msg: '',
        data: playerJSON
    });
};

/**
 * @api {get} /admin/home/games 获取游戏的每局id
 * @apiGroup admin/home
 * @apiName getGameList
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏，kog或lol
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": ["123", "344"]
 * }
 * 
 * @apiErrorExample Error-Response:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }
 */
obj.getGameList = (req, res) => {
    let id = req.query.id;
    let game = req.query.game;

    playersProxy
        .getPlayerListLikeId(id.slice(0, -1), game)
        .then(list => {
            console.log(list);
            return list.map(item => {
                return item.match(/(\d+)/)[1];
            });
        })
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
                msg: exp.toString()
            });
        });
};

obj.getTypeList = (req, res) => {
    let type = req.query.type;
    let w = req.query.w;

    let withScore = w === 'y'
        ? 'WITHSCORES'
        : '';

    RedisProxy
        .getList(type, withScore)
        .then(list => {
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
 * @api {get} /admin/home/skill 获取英雄技能
 * @apiGroup admin/home
 * @apiName getSkill
 * @apiParam {String} game 游戏，kog或lol
 * 
 * @apiSuccessExample {json} Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": [{summoner_id: "技能id", summoner_name: "技能名"}]
 * }
 * 
 * // kog技能图片地址http://game.gtimg.cn/images/yxzj/img201606/summoner/${id}.jpg
 * // lol技能图片地址http://ossweb-img.qq.com/images/lol/img/spell/${id}.png
 */
obj.getSkill = (req, res) => {
    let game = req.query.game;

    let data = null;

    if (game === 'kog') {
        data = kogSkill;
    } else if (game === 'lol') {
        data = lolSkill;
    }

    res.json({
        status: 'success',
        msg: '',
        data: data
    });
};