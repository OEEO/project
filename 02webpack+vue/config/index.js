/**
 * Created by toreant on 2017/10/19.
 */

'use strict';
// Template version: 1.1.3
// see http://vuejs-templates.github.io/webpack for documentation.

const path = require('path');

module.exports = {
    build: {
        env: require('./prod.env'),
        index: path.resolve(__dirname, '../dist/index.html'),
        assetsRoot: path.resolve(__dirname, '../dist'),
        assetsSubDirectory: 'static',
        assetsPublicPath: '/',
        productionSourceMap: false,
        publicPath: './',
        //publicPath: 'https://mat1.gtimg.com/sports/sportAppWeb/douyuthirdparty/web',
        productionGzip: false,
        productionGzipExtensions: ['js', 'css'],
        bundleAnalyzerReport: process.env.npm_config_report
    },
    dev: {
        env: require('./dev.env'),
        port: process.env.PORT || 8086,
        autoOpenBrowser: true,
        assetsSubDirectory: 'static',
        assetsPublicPath: '/',
        proxyTable: {},
        cssSourceMap: false
    },
    pre: {
        env: require('./test.env'),
        index: path.resolve(__dirname, '../pre/index.html'),
        assetsRoot: path.resolve(__dirname, '../pre'),
        assetsSubDirectory: 'static',
        assetsPublicPath: '/',
        productionSourceMap: true,
        //publicPath: 'https://static.gameday.ren/esport_data_web/pre/',
        publicPath: './',
        productionGzip: false,
        productionGzipExtensions: ['js', 'css'],
        bundleAnalyzerReport: process.env.npm_config_report
    }
};
