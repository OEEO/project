<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class CouponExportViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','category','name','type','value','content','start_time','end_time','min_amount','count','_table'=>'__COUPON__','_type'=>'LEFT'),
        'B'=>array(
            'nickname'=>'member_nickname',
            '_on'=>'B.id=A.member_id',
            '_table'=>'__MEMBER__'
        )
    );
}