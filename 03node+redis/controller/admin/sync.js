const spawn = require('child_process').spawn;
const path = require('path');

const R = require('../../utils/redisUtil');
const remoteProxy = require('../../proxy/remote');

let obj = {};

let backChild = null;
let syncChild = null;

module.exports = obj;

/**
 * @api {get} /admin/sync/backup 开启备份进程
 * @apiGroup admin/sync
 * @apiName startBackUp
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "开启备份进程成功，pid为${backChild.pid}"
 * }
 * 
 * @apiSuccessExample Fail-Response:
 * {
 *  "status": "fail",
 *  "msg": "备份进程已开启"
 * }
 * 
 * @apiErrorExample Error-Response:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }
 */
obj.startBackUp = (req, res) => {

    if (backChild) {
        res.json({
            status: 'fail',
            msg: '备份进程已开启'
        });
        return;
    }

    let _path = path.resolve(__dirname, '../../bin/backup.js');
    backChild = spawn('node', [_path]);

    backChild.stdout.on('data', data => {
        console.log('stdout: %s', data.toString());
    });

    backChild.stderr.on('data', data => {
        console.log('stderr: %s', data.toString());
    });

    backChild.on('exit', _ => {
        backChild = null;
    });

    backChild.on('error', err => {
        console.error('备份进程发生错误\n: %s', err.toString());
        backChild = null;
    });

    if (backChild.pid) {
        res.json({
            status: 'success',
            msg: `开启备份进程成功，pid为${backChild.pid}`
        });
    } else {
        res.json({
            status: 'error',
            msg: '备份进程开启失败'
        });
    }
};

/**
 * @api {get} /admin/sync/backup/stop 关闭备份进程
 * @apiGroup admin/sync
 * @apiName stopBackup
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "关闭备份进程成功"
 * }
 * 
 * @apiSuccessExample Fail-Response:
 * {
 *  "status": "fail",
 *  "msg": "备份进程未开启"
 * }
 * 
 * @apiErrorExample Error-Response:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }
 */
obj.stopBackup = (req, res) => {
    if (!backChild) {
        res.json({
            status: 'fail',
            msg: '备份进程未开启'
        });
        return;
    }

    backChild.kill('SIGINT');

    res.json({
        status: 'success',
        msg: '关闭备份进程成功'
    });
};

/**
 * @api {get} /admin/sync/backup/status 查看备份进程的状态
 * @apiGroup admin/sync
 * @apiName statusBackup
 * @apiSuccessExample Success-Response:
 * {
 *  "status": "success",
 *  "msg": "备份进程正在运行中，pid为${backChild.pid}, 上一次备份时间为${new Date(t * 1000).toString()}"
 * }
 * 
 * @apiSuccessExample Fail-Response:
 * {
 *  "status": "fail",
 *  "msg": "备份进程未开启, 上一次备份时间为${new Date(t * 1000).toString()}"
 * }
 * 
 * @apiErrorExample Error-Response:
 * {
 *  "status: "error",
 *  "msg": "error msg"
 * }
 */
obj.statusBackup = (req, res) => {
    R
        .getDataByKey('last_backup')
        .then(t => {
            res.json({
                status: backChild ? 'success' : 'fail',
                msg: backChild 
                    ? `备份进程正在运行中，pid为${backChild.pid}, 上一次备份时间为${new Date(t * 1000).toString()}`
                    : `备份进程未开启, 上一次备份时间为${new Date(t * 1000).toString()}`
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
 * @api {get} /admin/sync 开启同步进程
 * @apiGroup admin/sync
 * @apiName remoteSync
 * @apiExample {js} 说明
 * 这个接口使用Server Send Event, 如果接收到event为end，请务必关闭连接。
 */
obj.remoteSync = (req, res) => {
    res.writeHead(200, {
        'Content-Type': 'text/event-stream',
        'Cache-Control': 'no-cache',
        'Connection': 'keep-alive'
    });

    if (syncChild) {
        res.write('data: 进程已开启\n\n');
    } else {
        let _path = path.resolve(__dirname, '../../bin/sync.js');
        console.log(_path);
        syncChild = spawn('node', [_path]);
    }

    req.connection.addListener('close', function () {
        console.log('客户端关闭');
        syncChild = null;
    }, false);

    if (syncChild) {
        syncChild.stdout.on('data', data => {
            console.log(data.toString());
            res.write('data: ' + data.toString() + '\n\n');
        });

        syncChild.stderr.on('data', err => {
            console.log(err.toString());
            res.write('data: ' + err.toString() + '\n\n');
        });

        syncChild.on('exit', _ => {
            syncChild = null;
            res.write('event: end\n\n');
            res.write('data: end\n\n');
        });

        syncChild.on('error', err => {
            syncChild = null;
            res.write('data: ' + err.toString() + '\n\n');
        });
    } else {
        res.write('data: 同步进程未开启\n\n');
    }
};

/**
 * @api {get} /admin/sync/baseInfo 同步baseInfo
 * @apiGroup admin/sync
 * @apiName syncBaseInfo
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏
 * 
 */
obj.syncBaseInfo = (req, res) => {
    let id = req.query.id;
    let game = req.query.game;

    remoteProxy
        .syncBaseInfo(id, game)
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