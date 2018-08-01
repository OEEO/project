/**
 * 同步本地数据到远端服务器
 * 根据配置文件中keys，进行查询
 */
const schedule = require('node-schedule');
const { keys, remoteUrl, backupDomain } = require('../config');
const R = require('../utils/redisUtil');
const { get, post } = require('../utils/request');
const args = require('../utils/args')();

const matchId = args.id; // 如果限定了只备份某个id的数据

function _parseId(id) {
    let reg = id.match(/(\w+)-(\d+)/);
    let game = reg[1];
    id = reg[2];
    
    return {
        game,
        id
    };
}

function _backupPlayers(key, data) {
    let {game, id} = _parseId(key);

    return post(`${remoteUrl}/admin/players`, {
        game: game,
        id: id,
        data: data
    }, {
        'Content-Type': 'application/json;charset=utf-8'
    });
}

function _backupBuff(key, data) {
    let {game, id} = _parseId(key);

    return post(`${remoteUrl}/admin/buff`, {
        game: game,
        id: id,
        data: data
    }, {
        'Content-Type': 'application/json;charset=utf-8'
    });
}

function _backupStringData(key, data) {
    let reg = key.match(/(\w+)-(\d+):(\w+)/);
    if (!reg) {
        throw new Error('key is nut match');
    }
    let [, game, id, name] = reg;
    let queueName = '';
    if (name === 'bp') {
        queueName = 'bpqueue';
    } else if (name === 'live') {
        queueName = 'queue';
    }

    return post(`${backupDomain}/api/remote/backup`, {
        game: game,
        id: id,
        name: name,
        type: 'string',
        data: data,
        zList: 'y',
        queueName: queueName
    }, {
        'Content-Type': 'application/json;charset=utf-8'
    });
}

async function backup() {
    for (let key of keys) {
        let list = await R.zRange(`${key.name}-list`, 0, -1);
        // console.log(list);

        let type = key.type;
        for (let id of list) {

            let reg = new RegExp('^\\w+-' + matchId + ':\\w+');
            // console.log(matchId, reg.test(id));
            if (matchId && !reg.test(id)) {
                continue;
            }

            console.log(`开始备份==${id}`);

            let _data = null;

            if (type === 'string') {
                _data = await R.getDataByKey(id);
                _data = _data
                    ? JSON.parse(_data)
                    : null;
            } else if (type === 'hash') {
                _data = await R.hGetall(id);
            }

            if (!_data) {
                continue;
            }

            try {
                // if (key.name === 'buff') {
                //     await _backupBuff(id, _data);
                // } else if (key.name === 'players') {
                //     await _backupPlayers(id, _data);
                // } else if (key.type === 'string') { 
                //     await _backupStringData(id, _data);
                // }

                if (key.type === 'string') { 
                    _backupStringData(id, _data)
                        .then(_ => {
                            console.log('备份成功');
                        });
                }

                
            } catch (exp) {
                console.error(exp);
            }
        }

        await R.setDataForKey('last_backup', parseInt(Date.now() / 1000));
    }

    // process.exit();
}

schedule.scheduleJob('*/1 * * * * *', function () {
    backup();
});