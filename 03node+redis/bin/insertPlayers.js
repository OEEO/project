const R = require('../utils/redisUtil');

async function run() {
    const ids = [43680, 43681, 43682, 43683, 43684, 43685, 43686, 43687, 43688];
    for (let id of ids) {
        for (let i = 1; i <= 3; i++) {
            await R.zAdd('players-list', `kog-${id}${i}:players`, parseInt(Date.now() / 1000));
        }
    }

    process.exit();
}

run();
