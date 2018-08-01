<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class SettlementTipsViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A' => [
            'id','member_id','price','title','is_pass','status',
            '_table'=>'__TIPS__'
        ],
        'B' => [
            'id'=> 'times_id','start_time','stop_buy_time','stock',
            '_on' => 'B.tips_id=A.id',
            '_table' => '__TIPS_TIMES__'
        ],
        'C' => [
            'nickname','telephone',
            '_on' => 'A.member_id=C.id',
            '_table' => '__MEMBER__'
        ]
    ];

}
