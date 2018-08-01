<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TipsTimesViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','member_id','title','_table'=>'__TIPS__','_type'=>'LEFT'],
        'B' =>[
            'id'=> 'time_id',
            'phase' => 'tips_time_phase',
            'start_time','end_time', 'release_time','stop_buy_time',
            '_on' => 'B.tips_id=A.id',
            '_table' => '__TIPS_TIMES__',
            '_type'=>'LEFT'
        ],
        'C' => [
            'citys_id',
            '_on' => 'C.tips_id=A.id',
            '_table' => '__TIPS_SUB__',
            '_type'=>'LEFT'
        ],
        'D' => [
            'name'=>'city_name',
            '_on' => 'D.id=C.citys_id',
            '_table' => '__CITYS__',
            '_type'=>'LEFT'
        ],
        'E' => [
            'nickname','telephone',
            '_on' => 'E.id=A.member_id',
            '_table' => '__MEMBER__'
        ]
    ];

}
