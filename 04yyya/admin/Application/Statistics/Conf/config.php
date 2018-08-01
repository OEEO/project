<?php
header("Content-type: text/html; charset=utf8");
//header('Access-Control-Allow-Origin: *');
return [
    'URL_MODEL'  => '2',
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'ym_statistics', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'      => 'DJjNmJiZTR', // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'ym_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8mb4', // 字符集

    //日志目录
    'logDirPath' => '/disk/wwwlogs/',
    //备份日志目录
    'logBakPath' => '/disk/wwwlogs/bak/',
    'key' => 'TfqfDqf23L3w2tP'
];