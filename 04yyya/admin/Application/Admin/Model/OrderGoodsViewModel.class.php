<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderGoodsViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','sn','member_id','price','context','act_status','status'=>'order_status','create_time','limit_pay_time','channel','_table'=>'__ORDER__'],
        'B'=> [
            //'id'=>'member_id',
            //'type'=>'member_type',
            'telephone'=>'member_telephone',
            'nickname'=>'member_nickname',
            '_on'=>'A.member_id=B.id',
            '_table'=>'ym_member',
            '_type' => 'LEFT'
        ],
        'C'=> [
            'linkman'=>'member_address_linkman',
            'citys_id'=>'member_address_citys_id',
            'address'=>'member_address_address',
            '_on'=>'A.member_address_id=C.id',
            '_table'=>'__MEMBER_ADDRESS__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'number'=>'order_logistics_number',
            //'coupon_id'=>'member_coupon_coupon_id',
            '_on'=>'A.id=D.order_id',
            '_table'=>'__ORDER_LOGISTICS__',
            '_type' => 'LEFT'
        ],
        'E'=> [
            'name'=>'logistics_name',
            '_on'=>'D.logistics_id=E.id',
            '_table'=>'__LOGISTICS__',
            '_type' => 'LEFT'
        ],
        'F'=> [
            'id'=>'order_wares_id',
            'type'=>'order_wares_type',
            'ware_id'=>'order_wares_ware_id',
            'price'=>'order_wares_price',
            //'num'=>'order_wares_num',
            '_on'=>'A.id=F.order_id  and A.act_status<>11',
            '_table'=>'ym_order_wares',
            '_type'=>'LEFT'
        ],
        /*'G'=>array(
            'issue'=>'tips_sub_issue',
            '_on'=>'F.ware_id=G.tips_id',
            '_table'=>'ym_tips_sub'
        ),*/
        'H'=> [
            'title'=>'goods_title',
            //'category_id'=>'goods_category_id',
            '_on'=>'F.ware_id=H.id',
            '_table'=>'__GOODS__',
            '_type' => 'LEFT'
        ],
        'I'=> [
            'name'=>'category_name',
            '_on'=>'I.id=H.category_id',
            '_table' => '__CATEGORY__',
            '_type' => 'LEFT'

        ],
        'J'=> [
            '_on'=>'J.id=A.member_coupon_id',
            '_table'=>'__MEMBER_COUPON__',
            '_type' => 'LEFT'
        ],
        'K'=> [
            'type'=>'coupon_type',
            'value'=>'coupon_value',
            '_on'=>'K.id=J.coupon_id',
            '_table'=>'__COUPON__'
        ],
        'M'=> [
            'nickname'=>'inviter_nickname',
            '_on'=>'A.invite_member_id=M.id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ],
    ];
}