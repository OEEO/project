const schedule = require('node-schedule');

const R = require('../utils/redisUtil');

const { keys } = require('../config');

async function run() {

    const cur = Date.now() / 1000 - 86400 * 7;

    for (let type of keys) {
        let keys = await R.zRange(`${type.name}-list`, 0, -1, 'WITHSCORES');
        for (let i = 0, num = keys.length; i < num; i += 2) {
            // 7天以前的，删除
            if (+keys[i + 1] < cur) {
                await R.zRem(`${type.name}-list`, keys[i]);
            }
        }
    }

    // process.exit();
}

console.log('开启清除进程');
schedule.scheduleJob('0 0 * * *', function () {
    run();
});