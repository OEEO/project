const buffProxy = require('../../proxy/redis/buff');

let obj = {};

module.exports = obj;

/**
 * @api {get} /admin/buff 获取buff数据
 * @apiGroup admin/buff
 * @apiName getBuff
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏，kog或lol
 * 
 * @apiSuccessExample {json} Success-Response:
 * {
        "status": "success",
        "msg": "",
        "data": {
            "left_baojun": "2",
            "right_baojun": "2",
            "left_darkbaojun": "3",
            "right_darkbaojun": "3",
            "left_zhuzai": "4",
            "right_zhuzai": "4"
        }
    }

 * @apiErrorExample {json} Error-Response:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }   
 */
obj.getBuff = (req, res) => {
    let game = req.query.game;
    let id = req.query.id;

    buffProxy
        .getBuff(game, id)
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
 * @api {post} /admin/buff 修改buff数据
 * @apiGroup admin/buff
 * @apiName setBuff
 * @apiParam {Number} id 游戏id
 * @apiParam {String} game 游戏，kog或lol
 * @apiParam {Object} data
 * 
 * @apiExample {js} 说明
 * 请求体中的data, 是包含以下1个或以上键值的对象
 * {
 *  "left_baojun": Number, // 左队暴君
 *  "right_baojun": Number, // 右队暴君
 *  "left_darkbaojun": Number, // 左队黑暗暴君
 *  "right_darkbaojun": Number, // 右队黑暗暴君
 *  "left_zhuzai": Number, // 左队主宰
 *  "right_zhuzai": Number // 右队主宰
 * }
 * 
 * @apiSuccessExample {json} Success-Response:
 * {
 *  "status": "success",
 *  "msg": ""
 * }
 * 
 * @apiErrorExample {json} Error-Response:
 * {
 *  "status": "error",
 *  "msg": "error msg"
 * }
 */
obj.setBuff = (req, res) => {
    let game = req.body.game;
    let id = req.body.id;
    let data = req.body.data;

    buffProxy
        .setBuff(game, id, data)
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