<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderRaisePayViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','sn','member_id','member_address_id','price','act_status','channel','order_pid','context','status','_table'=>'__ORDER__'],
        'B'=> [
            'id' => 'order_wares_id',
            'tips_times_id',
            'ware_id',
            'server_status'=>'order_wares_server_status',
            'type'=>'order_wares_type',
            'price'=>'order_wares_price',
            '_on'=>'A.id=B.order_id',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT'
        ],
        'C'=> [
            'content'=>'raise_times_content',
            'price'=>'raise_times_price',
            'prepay'=>'raise_times_prepay',
            'screen_num',
            '_on'=>'C.id=B.tips_times_id',
            '_table'=>'__RAISE_TIMES__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'title'=>'raise_title',
            'content'=>'raise_content',
            'total'=>'raise_total',
            'introduction',
            'start_time'=>'start_time',
            'end_time'=>'end_time',
            '_on'=>'D.id=B.ware_id',
            '_table'=>'__RAISE__',
            '_type'=>'LEFT'
        ],
        'E'=> [
            'type',
            '_on'=>'E.order_id=A.id',
            '_table'=>'__ORDER_PAY__',
            '_type'=>'LEFT'
        ],

    ];
}