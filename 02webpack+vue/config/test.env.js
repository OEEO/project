/**
 * Created by yuhuajie on 2017/11/7.
 */
'use strict';
const merge = require('webpack-merge');
const devEnv = require('./dev.env');

module.exports = merge(devEnv, {
    NODE_ENV: '"testing"',
    API_ROOT: '"http://kpl.portal.gameday.ren:8077"',
    API_LOCAL_ROOT: '"http://localhost:8078"'
});
