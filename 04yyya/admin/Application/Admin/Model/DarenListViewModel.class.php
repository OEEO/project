<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class DarenListViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','member_id','contact','introduce','status', '_table'=>'__MEMBER_DAREN__','_type'=>'LEFT'),
        'B'=>array(
            'nickname'=>'member_nickname',
            'telephone'=>'member_telephone',
            'openid'=>'member_openid',
            'pic_id'=>'member_pic_id',
            'register_time'=>'member_register_time',
            'datetime'=>'member_datetime',
            '_on'=>'A.member_id=B.id',
            '_table'=>'__MEMBER__',
            '_type' => 'LEFT'
        ),
        'C'=>array(
            'path'=>'pics_path',
            '_on'=>'B.pic_id=C.id',
            '_table'=>'__PICS__'
        ),


    );
}