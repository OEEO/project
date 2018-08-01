// const bpProxy = require('../../proxy/redis/bp');
const bpScene = require('../../data/bpScene.json');
const remoteProxy = require('../../proxy/remote');

let obj = {};

module.exports = obj;

/**
 * @api {get} /admin/bp 获取bp数据
 * @apiGroup admin/bp
 * @apiName getBP
 * @apiParam {Number} id 游戏Id
 * @apiParam {String} game 游戏,kog或lol
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": {
 *      "left": {
 *          "ban": [123, 111], // ban英雄id
 *          "banHero": ["姜子牙", "关羽"], // ban英雄名字
 *          "pick": [1], // pick英雄id
 *          "pickHero": ["白起"], // pick英雄的名字
 *          "skill": [1243,43434,3434], // pick英雄对应的英雄技能
 *      },
 *      "right": RIGHT,
 *      "scene": "BP状态值"
 *  }
 * }
 * 
 * @apiErrorExample Error-Response:
 * {
 *  "status": "error",
 *  "msg": ""
 * }
 */
obj.getBP = (req, res) => {
    let id = req.query.id;
    let game = req.query.game;

    remoteProxy
        .getBp(id, game)
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

/**
 * @api {post} /admin/bp 设置bp数据
 * @apiGroup admin/bp
 * @apiName setBP
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏，kog或lol
 * @apiParam {Object} data 修改的数据
 * @apiParam {String} r 是否强制覆盖ocr生成的数据。 y -- 覆盖， n -- 不覆盖（默认）
 * 
 * @apiExample {js} 说明
 * 参数data传回getBP中的data数据类型
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": ""
 * }
 * 
 * @apiErrorExample Error-Response:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }
 */
obj.setBP = (req, res) => {
    let id = req.body.id;
    let game = req.body.game;
    let data = req.body.data;
    let r = req.body.r || 'n';

    remoteProxy
        .setBp(id, game, data, r)
        .then(r => {
            res.json({
                status: r,
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
 * @api {get} /admin/bp/scene 获取bp状态码
 * @apiGroup admin/bp
 * @apiName getBPScene
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": [
 *      {
 *          "label": "BP状态码",
 *          "value": "scene",
 *          "children": [
 *              {
 *                  "label": "状态名",
 *                  "value": "状态值"
 *              }
 *          ]
 *      }
 *  ]
 * }
 */
obj.getBPScene = (req, res) => {
    res.json({
        status: 'success',
        msg: '',
        data: bpScene
    });
};