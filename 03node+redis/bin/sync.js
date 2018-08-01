/**
 * 拉取远端同步进程
 */
const R = require('../utils/redisUtil');
const { get, post } = require('../utils/request');
const { keys, remoteUrl } = require('../config');
const buffProxy = require('../proxy/redis/buff');
const playersProxy = require('../proxy/redis/players');
const bpProxy = require('../proxy/redis/bp');

function parseGameId(key) {
    let reg = key.match(/(\w+)-(\d+)/);
    return reg 
        ? {
            game: reg[1],
            id: reg[2]
        }
        : null;
}

function getTypeList(type) {
    return get(`${remoteUrl}/admin/home/list?type=${type}`)
        .then(data => {
            if (data.status === 'success') {
                return data.data;
            } else {
                return [];
            }
        })
        .catch(exp => {
            console.error(exp);
            return [];
        });
}

function getRemoteData(type, game, id) {
    let url = `${remoteUrl}/admin/${type}?game=${game}&id=${id}`;

    return get(url)
        .then(data => {
            if (data.status === 'success') {
                return data.data;
            } else {
                return null;
            }
        })
        .catch(exp => {
            console.error(exp);
            return null;
        });
}

function getLocalData(type, game, id) {
    if (type === 'buff') {
        return R
            .hGetall(`${game}-${id}:${type}`)
            .catch(exp => {
                console.error(exp);
                return null;
            });
    } else {
        return R
            .getDataByKey(`${game}-${id}:${type}`)
            .then(data => {
                if (data) {
                    return JSON.parse(data);
                } else {
                    return null;
                }
            })
            .catch(exp => {
                console.error(exp);
                return null;
            });
    }
}

function updateData(type, game, id, data) {
    switch (type) {
        case 'buff':
            return buffProxy.setBuff(game, id, data);
        case 'players':
            return playersProxy.setPlayers(game, id, data);
        case 'bp':
            return bpProxy.setBpData(game, id, data);    
    }
}

/**
 * @define 判断远端数据和本地数据的内容和更新时间
 * @description 如果存在远端数据且更新时间大于本地数据的更新时间，则返回true;否则返回false
 * @param {Object} rData 
 * @param {Object} lData 
 */
function valid(rData, lData) {
    if (!rData) {
        return false;
    } else if (!lData) {
        return true;
    } else {
        return rData.__update_time__ - lData.__update_time__ > 0;
    }
}

async function run() {
    for (let key of keys) {
        let list = await getTypeList(key.name);
        let _type = key.type;

        for (let item of list) {
            let gameId = parseGameId(item);

            if (!gameId) {
                continue;
            }

            let _rData = await getRemoteData(key.name, gameId.game, gameId.id); // 远端数据
            let _lData = await getLocalData(key.name, gameId.game, gameId.id); // 本地数据

            if (valid(_rData, _lData)) {
                await updateData(key.name, gameId.game, gameId.id, _rData);
                console.log(`更新${gameId.game}-${gameId.id}:${key.name}`);
            } else {
                console.log(`====pass==== ${gameId.game}-${gameId.id}:${key.name}`);
            }
        }
    }
    console.log('全部更新完毕');
    process.exit();
}  

run();