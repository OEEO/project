<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class ReceivePrivilegeViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=> ['id','member_id', 'start_time','end_time','order_id','status'=>'privilege_status', '_table'=>'__MEMBER_PRIVILEGE__','_type'=>'LEFT'],
        'B'=> [
            '_on'=>'B.id=A.privilege_id',
            '_table'=>'__PRIVILEGE__',
            '_type'=>'LEFT'
        ],
        'C' => [
            'title' => 'privileg_title',
            '_on' => 'C.id = B.type_id AND type=0',
            '_table' => '__TIPS__',
            '_type'=>'LEFT'
        ],
        'D' => [
            'title' => 'privileg_title',
            '_on' => 'D.id = B.type_id AND type=1',
            '_table' => '__GOODS__',
            '_type'=>'LEFT'
        ],
        'E' => [
            'title' => 'privileg_title',
            '_on' => 'E.id = B.type_id AND type=2',
            '_table' => '__RAISE__',
            '_type'=>'LEFT'
        ],
        'G' => [
            'title' => 'privileg_times_title',
            '_on' => 'G.id = B.tips_times_id AND type=2',
            '_table' => '__RAISE_TIMES__',
            '_type'=>'LEFT'
        ],
        'I' => [
            'nickname' => 'originator',
            '_on' => 'I.id = B.member_id',
            '_table' => '__MEMBER__',
            '_type'=>'LEFT'
        ],
        'J' => [
            'nickname' => 'receiver',
            '_on' => 'J.id = A.member_id',
            '_table' => '__MEMBER__',
            '_type'=>'LEFT'
        ],
    );

}
