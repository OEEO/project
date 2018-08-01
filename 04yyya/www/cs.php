<?php

$serv = new swoole_websocket_server('120.25.165.87', '88') or die('Swoole启动失败!');

$serv->set([
    'heartbeat_idle_time' => 120,
    'heartbeat_check_interval' => 60,
    'worker_num' => 1
]);

$serv->on('workerstart', function($serv){
    $serv->tick(3000, function ($id) use ($serv) {
        foreach($serv->connections as $fd){
            $serv->push($fd, 0, 0x9);
        }
    });
});

$serv->on('open', function($server, $request){
});

$serv->on('message', function($server, $frame){
    var_dump($frame->data);
});

$serv->on('close', function($serv, $fd){
});

$serv->start();

//class M
//{
//    public function connect(){
//        $link = new Swoole\Coroutine\MySQL();
//        $link->connect(['host' => '127.0.0.1', 'user' => 'root', 'password' => 'deanjj', 'database' => 'test']);
//        //$this->link->setDefer(false);
//        $rs = $link->query('Select * from test limit 1');
//        var_dump($rs);
//    }
//}




