<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class RaiseOrderExportViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','sn','price','act_status','context','invite_member_id','order_pid','channel','create_time','is_free', 'member_address_id' ,'_table'=>'__ORDER__'],
        'B'=> [
            //'id'=>'member_id',
            //'type'=>'member_type',
            'telephone'=>'member_telephone',
            'nickname'=>'member_nickname',
            'channel'=>'member_channel',
            '_on'=>'A.member_id=B.id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ],
        'C'=> [
            'type'=>'order_wares_type',
            'count(ware_id)'=>'buy_num',
            'price'=>'order_wares_price',
            '_on'=>'A.id=C.order_id',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'id' => 'raise_id',
            'start_time'=>'start_time',
            'end_time'=>'end_time',
            'title'=>'raise_title',
            '_on'=>'C.ware_id=D.id',
            '_table'=>'__RAISE__',
            '_type'=>'LEFT'
        ],
        'E'=> [
            'id'=>'raise_times_id',
            'title'=>'raise_times_title',
            'price'=>'raise_times_price',
            'prepay'=>'raise_times_prepay',
            'stock'=>'raise_times_stock',
            'quota'=>'raise_times_quota',
            '_on'=>'C.tips_times_id=E.id',
            '_table'=>'__RAISE_TIMES__',
            '_type'=>'LEFT'
        ],
        'F'=> [
            'weixincode','identity','surname',
            '_on'=>'F.member_id=B.id',
            '_table'=>'__MEMBER_INFO__',
            '_type' => 'LEFT'
        ],
//        'G' => [
//            'type' => 'pay_type',
//            '_on' => 'G.order_id=A.id',
//            '_table' => '__ORDER_PAY__',
//            '_type' => 'LEFT'
//        ],
        'I' => [
            'name' => 'city_name',
            '_on' => 'F.citys_id=I.id',
            '_table' => '__CITYS__'

        ]
    ];
}