<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class GoodsOrderExportViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','sn','price','act_status','context','channel','create_time','_table'=>'__ORDER__'],
        'B'=> [
            'telephone'=>'member_telephone',
            'nickname'=>'member_nickname',
            '_on'=>'A.member_id=B.id',
            '_table'=>'ym_member',
            '_type'=>'LEFT'
        ],
        'C'=> [
            'sn' => 'member_coupon_sn',
            '_on'=>'A.member_coupon_id=C.id',
            '_table'=>'__MEMBER_COUPON__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'type'=>'coupon_type',
            'value'=>'coupon_value',
            '_on'=>'C.coupon_id=D.id',
            '_table'=>'__COUPON__',
            '_type'=>'LEFT'
        ],
        'E'=> [
            'number'=>'order_logistics_number',
            '_on'=>'E.order_id=A.id',
            '_table'=>'__ORDER_LOGISTICS__',
            '_type' => 'LEFT'
        ],
        'F'=> [
            'type'=>'order_wares_type',
            'price'=>'order_wares_price',
            '_on'=>'A.id=F.order_id',
            '_table'=>'ym_order_wares',
            '_type'=>'LEFT'
        ],
        'G'=> [
            'name'=>'logistics_name',
            '_on'=>'E.logistics_id=G.id',
            '_table'=>'__LOGISTICS__',
            '_type'=>'LEFT'
        ],
        'H'=> [
            'title'=>'goods_title',
            'price' => 'goods_price',
            '_on'=>'F.ware_id=H.id',
            '_table'=>'__GOODS__',
            '_type'=>'LEFT'
        ],
        'I'=> [
            'citys_id' => 'member_address_citys_id',
            'linkman' => 'member_address_linkman',
            'telephone' => 'member_address_telephone',
            'zipcode' => 'member_address_zipcode',
            'address'=>'member_address_address',
            '_on'=>'A.member_address_id=I.id',
            '_table'=>'__MEMBER_ADDRESS__',
            '_type'=>'LEFT'
        ],
//        'K' => [
//            'type' => 'pay_type',
//            '_on' => 'K.order_id=A.id',
//            '_table' => '__ORDER_PAY__',
//            '_type' => 'LEFT'
//        ],
        'L'=> [
            'cause'=>'order_refund_cause',
            'money'=>'order_refund_money',
            '_on'=>'L.order_id=A.id',
            '_table'=>'__ORDER_REFUND__',
            '_type'=>'LEFT'
        ],
        'M'=> [
            'name'=>'category_name',
            '_on'=>'M.id=H.category_id',
            '_table'=>'__CATEGORY__'
        ]
    ];
}