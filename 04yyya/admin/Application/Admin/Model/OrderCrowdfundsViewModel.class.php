<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderCrowdfundsViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','sn','price','act_status','create_time','limit_pay_time','channel','context','order_pid','status'=>'order_status','is_send','is_free','_table'=>'__ORDER__'],
        'B'=> [
            'id'=>'member_id',
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
        /*'E'=>array(
            'id'=>'member_comment_id',
            'type'=>'member_comment_type',
            'target_id'=>'member_comment_target_id',
            'content'=>'member_comment_content',
            '_on'=>'A.comment_id=E.id',
            '_table'=>'ym_member_comment'
        ),*/
        'F'=> [
            'id' => 'order_wares_id',
            'tips_times_id',
            'count(ware_id)'=>'buy_num',
            'server_status'=>'order_wares_server_status',
            'type'=>'order_wares_type',
            'price'=>'order_wares_price',
            '_on'=>'A.id=F.order_id  and A.act_status<>11',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT'
        ],
        'G'=> [
            'id'=>'raise_times_id',
            'title'=>'raise_times_title',
            'price'=>'raise_times_price',
            'prepay'=>'raise_times_prepay',
            'stock'=>'raise_times_stock',
            'quota'=>'raise_times_quota',
            '_on'=>'F.tips_times_id=G.id',
            '_table'=>'__RAISE_TIMES__',
            '_type'=>'LEFT'
        ],
        'H'=> [
            //'id' => 'tips_id',
            //'start_buy_time'=>'tips_start_buy_time',
            //'stop_buy_time'=>'tips_stop_buy_time',
            'title'=>'raise_title',
            'start_time' => 'raise_times_start_time',
            'end_time' => 'raise_times_end_time',
            //'category_id'=>'tips_category_id',
            '_on'=>'G.raise_id=H.id',
            '_table'=>'__RAISE__',
            '_type'=>'LEFT'
        ],
//        'I'=> [
//            'name'=>'category_name',
//            '_on'=>'I.id=H.category_id',
//            '_table'=>'__CATEGORY__',
//            '_type'=>'LEFT'
//        ],
        'J'=> [
            'cause'=>'order_refund_cause',
            'money'=>'order_refund_money',
            '_on'=>'J.order_id=A.id',
            '_table'=>'__ORDER_REFUND__',
            '_type'=>'LEFT'
        ],
        'M'=> [
            'nickname'=>'inviter_nickname',
            '_on'=>'A.invite_member_id=M.id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ],
    ];
}