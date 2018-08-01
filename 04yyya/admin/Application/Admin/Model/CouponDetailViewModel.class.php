<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class CouponDetailViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['coupon_id','member_id','sn','used_time','_table'=>'__MEMBER_COUPON__','_type'=>'LEFT'],
        'D'=> [
            'count'=>'coupon_count',
            'category'=>'coupon_category',
            '_on'=>'D.id=A.coupon_id',
            '_table'=>'__COUPON__',
            '_type'=>'LEFT'
        ],
        'E'=> [
            'nickname',
            '_on'=>'E.id=A.member_id',
            '_table'=>'__MEMBER__'
        ]
    ];
}