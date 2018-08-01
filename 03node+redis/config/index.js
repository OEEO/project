let NODE_ENV = process.env.NODE_ENV.trim();

console.log(NODE_ENV);

let config = {
    events: [
        {
            'label': '第一滴血', 'value': 'first_blood'
        },
        {
            'label': '击杀', 'value': 'kill'
        },
        {
            'label': '二连击破', 'value': 'double_kill'
        },
        {
            'label': '锋芒必露', 'value': 'killing_spree'
        },
        {
            'label': '无坚不摧', 'value': 'rampage'
        },
        {
            'label': '势不可挡', 'value': 'unstoppable'
        },
        {
            'label': '横扫千军', 'value': 'god_like'
        },
        {
            'label': '天下无双', 'value': 'legendary'
        },
        {
            'label': '终结', 'value': 'shutdown'
        },
        {
            'label': '三连决胜', 'value': 'triple_kill'
        },
        {
            'label': '四连超凡', 'value': 'quadra_kill'
        },
        {
            'label': '五连绝世', 'value': 'penta_kill'
        },
        {
            'label': '黑暗暴君已被击败', 'value': 'dark_baojun'
        },
        {
            'label': '暴君已被击败', 'value': 'baojun'
        },
        {
            'label': '主宰已被击败', 'value': 'zhuzai'
        },
        {
            'label': '我方防御塔被摧毁', 'value': 'mt'
        },
        {
            'label': '摧毁敌方防御塔 ', 'value': 'et'
        },
    ],

    redisConf: {
        host: '127.0.0.1',
        port: 6379
    },

    exec: {
        exec: 'node',
        libPath: '/',
        mode: './bin/generate.js'
    },

    keys: [
        {
            name: 'buff',
            type: 'hash'
        },
        {
            name: 'players',
            type: 'string'
        },
        {
            name: 'event',
            type: 'list'
        },
        {
            name: 'eventLine',
            type: 'zset'
        },
        {
            name: 'bp',
            type: 'string'
        }, {
            name: 'live',
            type: 'string'
        }, {
            name: 'baseInfo',
            type: 'string'
        }, {
            name: 'bpR',
            type: 'string"'
        }
    ],

    remoteUrl: 'http://192.168.11.3:3004',

    ocrApiDomain: 'http://127.0.0.1:8072'
};

if (NODE_ENV === 'localhost') {
    config.exec = {
        exec: 'esanalyser',
        libPath: '/home/gameday/local/esanalyser/lib',
        mode: 'event'
    };
    config.redisConf = {
        host: '192.168.10.161',
        port: '6379'
    };
    config.remoteUrl = 'http://111.230.28.69:8075';
    config.ocrApiDomain = 'http://192.168.10.161:8072';
    config.backupDomain = 'http://111.230.28.69:8072';
}

if (NODE_ENV === 'production') {
    config.redisConf = {
        host: '10.0.0.12',
        port: '6379',
        password: 'crs-hjjpbyht:W8G6O86bm8'
    };

    config.exec = {
        exec: 'esanalyser',
        libPath: '/root/esanalyser/lib',
        mode: 'event'
    };

    config.remoteUrl = 'http://kpl.portal.gameday.ren:3002';
    config.ocrApiDomain = 'http://111.230.28.69:8072';
}

module.exports = config;
