<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class CouponViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> [
            'id','coupon_id',
            'sn' => 'member_coupon_sn',
            '_table'=>'__MEMBER_COUPON__',
            '_type'=>'LEFT'
        ],
        'B'=> [
            'type'=>'coupon_type',
            'value'=>'coupon_value',
            '_on'=>'A.coupon_id=B.id',
            '_table'=>'__COUPON__'
        ],
    ];
}