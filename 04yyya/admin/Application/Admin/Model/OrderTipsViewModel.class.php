<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderTipsViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','sn','price','act_status','create_time','limit_pay_time','channel','context','status'=>'order_status','_table'=>'__ORDER__'],
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
            'check_code',
            'id' => 'order_wares_id',
            'tips_times_id',
            'count(ware_id)'=>'buy_num',
            'server_status'=>'order_wares_server_status',
            'type'=>'order_wares_type',
            'price'=>'order_wares_price',
            '_on'=>'A.id=F.order_id and A.act_status<>11',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT'
        ],
        'G'=> [
            'phase'=>'tips_times_phase',
            'start_time' => 'tips_times_start_time',
            'end_time' => 'tips_times_end_time',
            '_on'=>'F.tips_times_id=G.id',
            '_table'=>'__TIPS_TIMES__',
            '_type'=>'LEFT'
        ],
        'H'=> [
            //'id' => 'tips_id',
            //'start_buy_time'=>'tips_start_buy_time',
            //'stop_buy_time'=>'tips_stop_buy_time',
            'title'=>'tips_title',
            //'category_id'=>'tips_category_id',
            '_on'=>'G.tips_id=H.id',
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
            'cause'=>'order_refund_cause',
            'money'=>'order_refund_money',
            '_on'=>'J.order_id=A.id',
            '_table'=>'__ORDER_REFUND__',
            '_type'=>'LEFT'
        ],
        'L'=> [
            '_on'=>'L.tips_id=H.id',
            '_table'=>'__TIPS_SUB__',
            '_type'=>'LEFT'
        ],
        'M'=> [
            'name'=>'city_name',
            '_on'=>'M.id=L.citys_id',
            '_table'=>'__CITYS__',
            '_type'=>'LEFT'
        ],
        'N'=> [
            'nickname'=>'inviter_nickname',
            '_on'=>'A.invite_member_id=N.id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ],
        'O'=> [
            '_on'=>'A.id=O.order_id',
            '_table'=>'__ORDER_PIECE__'
        ],
    ];
}