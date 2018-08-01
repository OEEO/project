<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class DarenDetailViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','nickname','status', 'telephone','invitecode', '_table'=>'__MEMBER__'),
        'B'=>array(
            'surname', 'sex', 'interest', 'signature', 'cover_pic_id',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__'
        ),
        'C'=>array(
            'status' => 'daren_status', 'introduce',
            '_on'=>'A.id=C.member_id',
            '_table'=>'__MEMBER_DAREN__'
        ),
        'D'=>array(
            'name'=>'city_name',
            '_on' => 'C.city_id=D.id',
            '_table' => '__CITYS__'
        )
    );
}