/**
 * 这是入口文件
 */
require(['../common'], function (common) {
    require(['jquery', 'utils'], function ($, utils) {
        $(document).ready(function () {
            console.log('这是工具类', utils);
            console.log('这里是{page}页面');

            /**
             * @SET gameType 
             * @SET gameId
             */
            var gameType = 'kog';
            var gameId = {
                'lol': 2,
                'kog': 6
            };
        });
    });
});