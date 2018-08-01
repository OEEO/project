
// let DOMAIN = process.env.API_ROOT;
// let DOMAIN = process.env.API_LOCAL_ROOT;
// let DOMAIN = 'http://192.168.11.3:3004';

import utils from '../utils';

let DOMAIN = 'http://192.168.11.3:3004';

let origin = utils.getQueryString('r');

if (!origin) {
    let href = `${window.location.origin}/?r=local${window.location.hash}`;
    window.location.href = href;
}
// 源切换
if (origin === 'local') {
    DOMAIN = process.env.API_LOCAL_ROOT;
} else if (origin === 'root') {
    DOMAIN = process.env.API_ROOT;
} else if (origin === 'test') {
    DOMAIN = 'http://192.168.11.3:3004';
}

exports.DOMAIN = DOMAIN;

exports.PLAYERS = `${DOMAIN}/admin/players/`;
exports.HOME_PLAYERS = `${DOMAIN}/admin/home/players`;
exports.HOME_GAMES = `${DOMAIN}/admin/home/games`;

exports.PROCESS_LIST = `${DOMAIN}/admin/process/list`;
exports.ADD_PROCESS = `${DOMAIN}/admin/process`;
exports.STOP_PROCESS = `${DOMAIN}/admin/process/stop`;
exports.DEL_PROCESS = `${DOMAIN}/admin/process/del`;

exports.BP_LIST = `${DOMAIN}/admin/bp`;
exports.SKILL_LIST = `${DOMAIN}/admin/home/skill`;
exports.BUFF_LIST = `${DOMAIN}/admin/buff`;
exports.ECONOMIC_DATA = `${DOMAIN}/admin/event/line`;
exports.BP_STATUS = `${DOMAIN}/admin/bp/scene`;
exports.EVENT_NEAR = `${DOMAIN}/admin/event/near`;
exports.SET_LINE = `${DOMAIN}/admin/event/set/line`;
exports.EVENT_LIST = `${DOMAIN}/admin/event/list`;
exports.PLAYER_INFO = `${DOMAIN}/admin/players/baseInfo`;
exports.BACKUP_STATUS = `${DOMAIN}/admin/sync/backup/status`;
exports.OPEN_BACKUP = `${DOMAIN}/admin/sync/backup`;
exports.STOP_BACKUP = `${DOMAIN}/admin/sync/backup/stop`;
exports.SYNC_PROCESS = `${DOMAIN}/admin/sync`;

// exports.init = function () {
//     // 当点击切换源时，才触发
//     if (DOMAIN.isClick) {
//         console.log('点击切换源');
//         if (DOMAIN.value === process.env.API_ROOT) {
//             window.location.href = window.location.href.replace('r=local', 'r=root');
//         } else {
//             window.location.href = window.location.href.replace('r=root', 'r=local');
//         }
//         DOMAIN.isClick = false;
//     }


// };
// exports.init();
