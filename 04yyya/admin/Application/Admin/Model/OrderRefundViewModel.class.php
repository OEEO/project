<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderRefundViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','order_id','type', 'money','cause','pic_id','is_allow','refusal_reason','_table'=>'__ORDER_REFUND__','_type'=>'LEFT'],
        'B'=> [
            'sn' => 'order_sn',
            'price'=>'order_price',
            'act_status'=>'order_act_status',
            'member_coupon_id'=>'order_member_coupon_id',
            'create_time'=>'order_create_time',
            //'paytime'=>'order_paytime',
            '_on'=>'A.order_id=B.id',
            '_table'=>'__ORDER__',
            '_type'=>'LEFT'
        ],
        'C'=> [
            'nickname'=>'member_nickname',
            'telephone'=>'member_telephone',
            '_on'=>'C.id=B.member_id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'name'=>'coupon_name',
            'type'=>'coupon_type',
            'value' => 'coupon_value',
            '_on' => 'D.id=B.member_coupon_id',
            '_table'=>'__COUPON__',
            '_type' => 'LEFT'
        ],
        'E'=> [
            'type'=>'order_wares_type',
            'ware_id'=>'order_wares_id',
            '_on' => 'B.id=E.order_id',
            '_table' => '__ORDER_WARES__',
            '_type' => 'LEFT'
        ],
        'F'=> [
            'path'=>'pics_path',
            '_on'=>'A.pic_id=F.id',
            '_table' => '__PICS__',
            '_type' => 'LEFT'
        ],
        'G'=> [
            'success_pay_time'=>'order_paytime',
            'type'=>'order_paytype',
            '_on' => 'A.order_id=G.order_id AND G.success_pay_time is not null',
            '_table' => '__ORDER_PAY__'
        ]
    ];

}
