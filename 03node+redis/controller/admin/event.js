const { buildGoldsLine } = require('../../proxy/remote');
const eventProxy = require('../../proxy/redis/event');
const { events } = require('../../config');

let obj = {};

module.exports = obj;

function _parseGradientEventToGoldLine(golds, gradientTimes = []) {
    golds.forEach(item => {
        if (gradientTimes.indexOf(item.time) !== -1) {
            item.isInflexion = true;
        } else {
            item.isInflexion = false;
        }
    });
}

/**
 * @api {get} /admin/event/line 获取时间的经济曲线
 * @apiGroup admin/event
 * @apiName getGoldsLine
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏名，kog或lol
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": [
        {
            "left": [ // 每个人的经济
                460,
                461,
                490,
                499,
                407
            ],
            "right": [
                344,
                368,
                533,
                444,
                344
            ],
            "time": 53, // 时间，单位s
            "left_total": 2317,
            "right_total": 2033,
            "isInflexion": false // 是否是拐点
        }
 *  ]
 * }
 */
obj.getGoldsLine = (req, res) => {
    let id = req.query.id;
    let game = req.query.game;
    let win = req.query.win;
    let type = req.query.type;
    let gradientEventTimes = [];

    eventProxy
        .getGradientEventIds(game, id)
        .then(ids => {
            gradientEventTimes = ids.map(item => +item.match(/.*event:(\d+)/)[1]);
            return buildGoldsLine(id, game, win, type);
        })
        .then(golds => {
            console.log(gradientEventTimes);
            _parseGradientEventToGoldLine(golds, gradientEventTimes);
            res.json({
                status: 'success',
                msg: '',
                data: golds
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
 * @api {get} /admin/event/near 获取时间点附近的事件
 * @apiGroup admin/event
 * @apiName getNearEvents
 * @apiParam {Number} id 比赛id
 * @apiParam {String} game 游戏,kog或lol
 * @apiParam {Number} time 时间，单位s
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": [
 *      {
 *          "p1": -1,
 *          "p2": 2,
 *          "time": 43, // 单位s
 *          "type": "事件值",
 *          "timestamp": "生成的时间戳",
 *          "scene": ""
 *      }
 *  ],
 *  "isInflexion": false, // 是否是拐点
 * }
 */
obj.getNearEvents = (req, res) => {
    let id = req.query.id;
    let game = req.query.game;
    let time = req.query.time;


    let list = [];

    eventProxy
        .getNearEvents(game, id, time)
        .then(_list => {
            list = _list;

            return eventProxy.isInflextion(game, id, time);
        })
        .then(r => {
            res.json({
                status: 'success',
                msg: '',
                data: list,
                isInflexion: r
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
 * @api {post} /admin/event/set/line 设置拐点事件
 * @apiGroup admin/event
 * @apiName setEventGoldLine
 * 
 * @apiHeader {String} Content-Type=application/json
 * 
 * @apiParam {Array} data 回传设置的事件数组
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏，kog或lol
 * @apiParam {Number} time 事件事件点，单位s
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": ""
 * }
 */
obj.setEventGoldLine = (req, res) => {
    let data = req.body.data;
    let id = req.body.id;
    let game = req.body.game;
    let time = req.body.time;

    let key = `${game}-${id}-event:${time}`;

    eventProxy
        .setEventLine(`${game}-${id}:eventLine`, key, data, time)
        .then(r => {
            res.json({
                status: 'success',
                msg: ''
            });
        })
        .catch(exp => {
            res.json({
                status: 'error',
                msg: exp.toString()
            });
        });
};

/**
 * @api {post} /admin/event/del/line 删除事件拐点
 * @apiGroup admin/event
 * @apiName delEventGoldLine
 * 
 * @apiHeader {String} Content-Type=application/json
 * 
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏
 * @apiParam {Number} time 事件点
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": ""
 * }
 */
obj.delEventGoldLine = (req, res) => {
    let id = req.body.id;
    let game = req.body.game;
    let time = req.body.time;

    eventProxy
        .delEventLine(game, id, time)
        .then(_ => {
            res.json({
                status: 'success',
                msg: ''
            });
        })
        .catch(exp => {
            res.json({
                status: 'error',
                msg: exp.toString()
            });
        });
};

/**
 * @api {get} /admin/event/list 获取事件类型列表
 * @apiGroup admin/event
 * @apiName getEventList
 * 
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
 *                  "label": "事件名",
 *                  "value": "事件值"
 *              }
 *          ]
 *      }
 *  ]
 * }
 */
obj.getEventList = (req, res) => {
    res.json({
        status: 'success',
        msg: '',
        data: [
            {
                label: '王者荣耀',
                value: 'kog',
                children: events
            }
        ]
    });
};