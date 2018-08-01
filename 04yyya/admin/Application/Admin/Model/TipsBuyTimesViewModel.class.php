<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TipsBuyTimesViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','status','_table'=>'__TIPS__','_type'=>'LEFT'),
        'B'=>array(
            'count(tips_times_id)'=>'buy_num',
            'tips_times_id',
            '_on'=>'B.type=0 and A.id=B.ware_id',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT'
        ),
        'C'=>array(
            'act_status',
            '_on'=>'B.order_id=C.id',
            '_table'=>'__ORDER__'
        )
    );

}
