<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TipsOrderExportViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','sn','price','act_status','context','channel','invite_member_id','create_time','_table'=>'__ORDER__'],
        'B'=> [
            //'id'=>'member_id',
            //'type'=>'member_type',
            'telephone'=>'member_telephone',
            'nickname'=>'member_nickname',
            '_on'=>'A.member_id=B.id',
            '_table'=>'__MEMBER__',
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

        'F'=> [
            'count(H.id)'=>'buy_num',
            'server_status'=>'order_wares_server_status',
            'type'=>'order_wares_type',
            'ware_id'=>'order_wares_ware_id',
            'price'=>'order_wares_price',
            'check_code'=>'order_wares_check_code',
            '_on'=>'A.id=F.order_id',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT'
        ],
        'G'=> [
            //'issue'=>'tips_sub_issue',
            'address'=>'tips_address',
            '_on'=>'F.ware_id=G.tips_id',
            '_table'=>'__TIPS_SUB__',
            '_type'=>'LEFT'
        ],
        'E'=> [
            'id'=>'citys_id',
            'name'=>'citys_name',
            '_on'=>'G.citys_id=E.id',
            '_table'=>'__CITYS__',
            '_type'=>'LEFT'
        ],
        'H'=> [
            //'id' => 'tips_id',
            /*'start_buy_time'=>'tips_start_buy_time',
            'stop_buy_time'=>'tips_stop_buy_time',*/
            'title'=>'tips_title',
            'discount'=>'tips_discount',
            'space_id',
            //'category_id'=>'tips_category_id',
            '_on'=>'F.ware_id=H.id',
            '_table'=>'__TIPS__',
            '_type'=>'LEFT'
        ],
        'I'=> [
            'name'=>'category_name',
            '_on'=>'I.id=H.category_id',
            '_table'=>'__CATEGORY__',
            '_type'=>'LEFT'
        ],
        'J'=> [
            'money'=>'order_refund_money',
            'cause'=>'order_refund_cause',
            '_on'=>'J.order_id=A.id',
            '_table'=>'__ORDER_REFUND__',
            '_type' => 'LEFT'
        ],
//        'K' => [
//            'type' => 'pay_type',
//            '_on' => 'K.order_id=A.id',
//            '_table' => '__ORDER_PAY__',
//            '_type' => 'LEFT'
//        ],
        'L'=> [
            'start_time','end_time',
            'phase'=>'tips_times_phase',
            '_on'=>'F.tips_times_id=L.id',
            '_table'=>'__TIPS_TIMES__'
        ],
        'M'=>[
            'name'=>'space_name',
            'address'=>'space_address',
            '_on'=>'M.id = H.space_id',
            '_table'=>'__SPACE__',
            '_type'=>'LEFT',

        ],
    ];
}