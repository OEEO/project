<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class SettlementGoodsViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A' => [
            'id','member_id','price','title','is_pass','status',
            '_table'=>'__GOODS__'
        ],
        'B' => [
            'nickname','telephone',
            '_on' => 'A.member_id=B.id',
            '_table' => '__MEMBER__'
        ]
    ];

}
