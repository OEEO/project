const processProxy = require('../../proxy/process');

let obj = {};

module.exports = obj;

/**
 * @api {post} /admin/process 开启新的进程任务
 * @apiGroup admin/process
 * @apiName generateProcess
 * @apiParam {Number} leftId 左队id
 * @apiParam {Number} rightId 右队id
 * @apiParam {String} game 游戏，kog或lol
 * @apiParam {Number} id 游戏id
 * @apiParam {String} input rtmp地址
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
obj.generateProcess = (req, res) => {
    let leftId = req.body.left_id;
    let rightId = req.body.right_id;
    let rtmpUrl = req.body.input;
    let game = req.body.game;
    let id = req.body.id;

    processProxy
        .generateProcess({
            game: game,
            leftId: leftId,
            rightId: rightId,
            input: rtmpUrl,
            id: id
        });

    res.json({
        status: 'success',
        msg: ''
    });    
};

/**
 * @api {get} /admin/process/list 获取实例列表
 * @apiGroup admin/process
 * @apiName getProcess
 * 
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "",
 *  "data": [
 *      { "game": "kog", "id": 1, "status": "idel", "pid": "23234", startTime: 1529496441 } // status有：idel -- 等待，running -- 正在运行中，exit -- 已关闭，error -- 发生错误
 *  ]
 * }
 * 
 * 
 * @apiErrorExample Error-Response:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }
 */
obj.getProcess = (req, res) => {
    let result = processProxy.getProcesses();
    res.json({
        status: 'success',
        msg: '',
        data: result
    });
};

/**
 * @api {get} /admin/process/stop 停止实例进程
 * @apiGroup admin/process
 * @apiName stopProcess
 * @apiParam {String} game 游戏,kog或lol
 * @apiParam {Number} id 游戏id
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
 *  "msg": ""
 * }
 */
obj.stopProcess = (req, res) => {
    let game = req.query.game;
    let id = req.query.id;

    try {
        processProxy.stopProcess(game, id);
        res.json({
            status: 'success',
            msg: ''
        });
    } catch (exp) {
        res.json({
            status: 'error',
            msg: exp.toString()
        });
    }
};

/**
 * @api {get} /admin/process/del 删除实例进程
 * @apiGroup admin/process
 * @apiName delProcess
 * @apiParam {String} game 游戏,kog或lol
 * @apiParam {Number} id 游戏id
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
 *  "msg": ""
 * }
 */
obj.delProcess = (req, res) => {
    let game = req.query.game;
    let id = req.query.id;

    try {
        processProxy.delProcess(game, id);
        res.json({
            status: 'success',
            msg: ''
        });
    } catch (exp) {
        res.json({
            status: 'error',
            msg: exp.toString()
        });
    }
};