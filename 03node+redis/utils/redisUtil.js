let redis = require('redis');
const redisConfig = require('../config/').redisConf;
const client = redis.createClient(redisConfig.port, redisConfig.host);
const { isArray, isObject } = require('./index');


client.on('error', error => {
    console.log(error);
});

client.on('ready', function (err) {
    console.log('redis ready');
});

let R = {
    getDataByKey: (key) => {
        return new Promise(resolve => {
            client.select('0', error => {
                if (error) {
                    throw new Error(error);
                } else {
                    client.get(key, (error, res) => {
                        if (error) {
                            throw new Error(error);
                        } else {
                            resolve(res);
                        }
                    });
                }
            });
        });
    },

    mGetDataByKeys: keys => {
        return new Promise((resolve, reject) => {
            client.select('0', err => {
                if (err) {
                    reject(err);
                } else {
                    client.mget(keys, (err, data) => {
                        err
                            ? reject(err)
                            : resolve(data);
                    });
                }
            });
        });
    },

    getAllKeys: (key) => {
        return new Promise(resolve => {
            client.select('0', error => {
                if (error) {
                    resolve(null);
                } else {
                    client.keys(`${key}*`, (error, data) => {
                        if (error) {
                            throw error;
                        } else {
                            resolve(data);
                        }
                    });
                }
            });
        });
    },

    /**
     * 默认设置为缓存时间为7天
     */
    setDataForKey: (key, data, time = 86400 * 7) => {
        return new Promise(resolve => {
            client.select('0', error => {
                if (error) {
                    resolve(null);
                } else {
                    client.set(key, data, (error, result) => {
                        client.expire(key, time);
                        resolve('ok');
                    });
                }
            });
        });
    },

    delDataForKey: (key) => {
        return new Promise((resolve, reject) => {
            client.select('0', err => {
                if (err) {
                    reject(err);
                } else {
                    client.del(key, err => {
                        if (err) {
                            reject(err);
                        } else {
                            resolve('ok');
                        }
                    });
                }

            });
        });
    },

    pushData: (key, data, time = 86400 * 7) => {
        return new Promise((resolve, reject) => {
            client.select('0', err => {
                if (err) {
                    reject(err);
                } else {
                    client.rpush(key, data, err => {

                        // client.expire(key, time);

                        err
                            ? reject(err)
                            : resolve('ok');
                    });
                }
            });
        });
    },

    lrangeData: (key, start = 0, offset = -1) => {
        return new Promise((resolve, reject) => {
            client.select('0', err => {
                if (err) {
                    reject(err);
                } else {
                    client.lrange(key, start, offset, (err, data) => {
                        err
                            ? reject(err)
                            : resolve(data);
                    });
                }
            });
        });
    },

    lremData: (key, val) => {
        return new Promise((resolve, reject) => {
            client.select('0', err => {
                if (err) {
                    reject(err);
                } else {
                    client.lrem(key, 0, val, err => {
                        err
                            ? reject(err)
                            : resolve('ok');
                    });
                }
            });
        });
    },

    llen: (key) => {
        return new Promise((resolve, reject) => {
            client.llen(key, (err, data) => {
                err
                    ? reject(err)
                    : resolve(data);
            });
        });
    },

    lpop: key => {
        return new Promise((resolve, reject) => {
            client.lpop(key, (err, data) => {
                err
                    ? reject(err)
                    : resolve(data);
            });
        });
    },

    zAdd: (key, data, score) => {
        return new Promise((resolve, reject) => {
            client.zadd(key, score, data, (err, data) => {
                err
                    ? reject(err)
                    : resolve(data);
            });
        });
    },

    zRange: (key, start, end, withScore = '') => {

        let params = [start, end];

        if (withScore) {
            params.push(withScore);
        }

        return new Promise((resolve, reject) => {
            client.zrange(key, ...params, (err, data) => {
                err
                    ? reject(err)
                    : resolve(data);
            });
        });
    },

    zRem: (key, member) => {
        return new Promise((resolve, reject) => {
            client.zrem(key, member, (err, data) => {
                err
                    ? reject(err)
                    : resolve(data);
            });
        });
    },

    zCount: (key, min, max) => {
        return new Promise((resolve, reject) => {
            client.zcount(key, min, max, (err, count) => {
                err
                    ? reject(err)
                    : resolve(count);
            });
        });
    },

    hmSet: (key, data) => {
        return new Promise((resolve, reject) => {
            let _setData = [];
            if (isObject(data)) {
                for (let key in data) {
                    _setData.push(key);
                    _setData.push(data[key]);
                }
            } else if (isArray(data)) {
                _setData = data;
            } else {
                throw new Error('data should be an Array or Object');
            }

            client.hmset(key, data, (err, r) => {
                err
                    ? reject(err)
                    : resolve(r);
            });
        });
    },

    hGetall: (key) => {
        return new Promise((resolve, reject) => {
            client.hgetall(key, (err, r) => {
                err
                    ? reject(err)
                    : resolve(r);
            });
        });
    },

    hGet: (key, field) => {
        return new Promise((resolve, reject) => {
            client.hget(key, field, (err, r) => {
                err
                    ? reject(err)
                    : resolve(r);
            });
        });
    }
};


module.exports = R;