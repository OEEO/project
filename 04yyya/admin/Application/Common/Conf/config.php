<?php
return array(

    'APP_SUB_DOMAIN_DEPLOY' => 1, // 开启子域名配置
    'APP_SUB_DOMAIN_RULES' => array(
        'admin.test.yami.ren' => 'Admin'
    ),

    'URL_MODEL'          => '2',
    //数据库配置信息
    'DB1' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '120.25.165.87', // 服务器地址
        'DB_NAME'   => 'ym_admin', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'        => 'DJjNmJiZTR', // 密码

        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'admin_', // 数据库表前缀
        'DB_CHARSET'=> 'utf8', // 字符集
    ),
    'DB2' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '120.25.165.87', // 服务器地址
        'DB_NAME'   => 'yummy_bak2', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'      => 'DJjNmJiZTR', // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'ym_', // 数据库表前缀
        'DB_CHARSET'=> 'utf8', // 字符集
    ),
    'LAYOUT_ON' => true,
    'LAYOUT_NAME' => 'layout',
    'TMPL_PARSE_STRING' => array(
        '__RS__' => '/Resource/admin',
        '__AMUI__' => '/Resource/amaze',
        '__THUMB__' => '/Resource/thumb',
        '__UPLOAD__' => '/Resource/upload',
        '__MINTHUMB__' => '/Resource/minthumb'
    ),
    'table' => array(
        'listnum' => 10,
        'listmax' => 5
    ),
//websocket配置
    'WS' => [
        'ip' => '120.25.165.87',
        'port' => 9501,
        'key' => 'B3mk4o5aOFkKGwIRHvgGznNkbBuhin'
    ],
//微信配置
    'WX_CONF' => [
        'id' => 'gh_60c6eba5b032',
        'appid' => 'wx2913320dd8970616',
        'secret' => '2d5bf92ce57c1caf2ee01ae0aeb0942b',
        'mchid' => '1343961401',
        'key' => 'MMEnvvEnrucfHkAEQ3nhrBHhaN7zxzVi',
        'apiclient_cert' => COMMON_PATH . 'Util/cacert/text_apiclient_cert.pem',
        'apiclient_key' => COMMON_PATH . 'Util/cacert/text_apiclient_key.pem'
    ],

    //启用memcache来缓存
    'MEMCACHE' => array(
        'HOST' => '127.0.0.1',
        'PORT' => 11211,
        'TIMEOUT' => 3600,
        'PREFIX' => 'ym_'
    ),

    'DEFAULT_MODULE'        =>  'Admin',  // 默认模块

    //websocket配置
    'WS' => [
        'ip' => '127.0.0.1',
        'port' => 9501,
        'key' => 'B3mk4o5aOFkKGwIRHvgGznNkbBuhin'
    ],

    //扩展配置
    'LOAD_EXT_CONFIG' => 'extend,sms',
);