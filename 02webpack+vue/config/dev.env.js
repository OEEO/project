/**
 * Created by yuhuajie on 2017/11/7.
 */
'use strict';
const merge = require('webpack-merge');
const prodEnv = require('./prod.env');

module.exports = merge(prodEnv, {
    NODE_ENV: '"development"',
    API_ROOT: '"http://192.168.15.7:8077"',
    API_LOCAL_ROOT: '"http://192.168.15.7:8078"'
});