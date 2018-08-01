<?php
namespace Home\Model;
use Think\Model\ViewModel;

class MembertipsViewModel extends ViewModel {

    public $viewFields = array(
        'A' => array(
            'id','title','price','member_id',
            '_table' => '__TIPS__',
            '_type' => 'LEFT'
        ),
        'B' => array(
            'path','is_thumb',
            '_on' => 'A.pic_id=B.id',
            '_table' => '__PICS__',
            '_type' => 'LEFT'
        ),
        'C' => array(
            'start_time','end_time',
            '_on' => 'A.id=C.tips_id',
            '_table' => '__TIPS_TIMES__',
            '_type' => 'LEFT'
        ),
        'D' => array(
            'simpleaddress',
            '_on' => 'A.id=D.tips_id',
            '_table' => '__TIPS_SUB__',
            '_type' => 'LEFT'
        )
    );

}
