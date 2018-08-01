<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberAnswerApplyViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=> ['id','member_id', 'number','tips_times_id','type', '_table'=>'__PRIVILEGE__','_type'=>'LEFT'],
        'B'=> [
            'title'=>'raise_times_title',
            '_on' => 'A.tips_times_id = B.id',
            '_table' => '__RAISE_TIMES__',
            '_type' => 'LEFT'
        ],
        'C'=> [
            'nickname'=>'distribute_nickname',
            '_on'=>'C.id=A.member_id',
            '_table'=>'__MEMBER__'
        ]
    );

}
