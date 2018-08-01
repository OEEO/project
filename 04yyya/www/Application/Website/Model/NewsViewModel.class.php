<?php
/**
 * Created by PhpStorm.
 * User: cherry
 * Date: 2017/5/10 0010
 * Time: 17:35
 */

namespace Website\Model;
use Think\Model\ViewModel;

class NewsViewModel extends ViewModel {

    public $viewFields = [
        'A' => [
            'id','member_id','title','content','datetime','abstract',
            '_table' => '__NEWS__',
            '_type' => 'LEFT'
        ],
        'B' => [
            'nickname' ,
            '_on' => 'A.member_id=B.id',
            '_table' => '__MEMBER__',
            '_type' => 'LEFT'
        ],
        'C' => [
            'path',
            '_on' => 'A.pic_id=C.id',
            '_table' => '__PICS__'
        ]
    ];
}