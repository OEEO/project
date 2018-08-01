<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class RaiseLuckyResultEditViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A' => [
            'id','raise_id','title','type','is_free','content',
            '_table' => '__RAISE_TIMES__',
            '_type' =>'LEFT',
        ],
        'B' => [
            'id' => 'raise_lottery_id',
            'raise_times_id',
            'trade_date',
            'sh',
            'sz',
            'run_time',
            'lucky_num',
            'base_x',
            'num',
            'status' => 'lottery_status',
            '_on' => 'A.id=B.raise_times_id',
            '_table' => '__RAISE_LUCKY_RESULT__'
        ]
    ];
}