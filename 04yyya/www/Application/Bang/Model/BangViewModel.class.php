<?php

namespace Bang\Model;
use Think\Model\ViewModel;

Class BangViewModel extends ViewModel {

    Public $viewFields = [
        'A' => [
            'id','member_id','content','send_time',
            '_table' => '__BANG__'
        ],
        'B' => [
            'nickname',
        ]
    ];

}


