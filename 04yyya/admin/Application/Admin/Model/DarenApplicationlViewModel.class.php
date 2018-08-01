<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class DarenApplicationViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','nickname','telephone','datetime','_table'=>'__MEMBER__'),
        /*'B'=>array(
            'surname'=>'member_info_surname',
            'sex'=>'member_info_sex',
            'address'=>'member_info_address',
            'qq'=>'member_info_qq',
            'contact'=>'member_info_contact',
            'interest'=>'member_info_interest',
            'signature'=>'member_info_signature',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__'
        ),
        'C'=>array(
            'introduce' => 'member_daren_introduce',
            'goodat'=>'member_daren_goodat',
            '_on'=>'B.member_id=C.member_id',
            '_table'=>'__MEMBER_DAREN__'
        ),
        'D'=>array(
            '_on'=>'A.id=D.member_id',
            '_table'=>'__MEMBER_APPLY__'
        )*/
    );
}