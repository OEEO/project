<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class DarenExportViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','nickname', 'telephone','openid','unionid','register_time', '_table'=>'__MEMBER__'),
        'B'=>array(
            'surname',
            'sex',
            'birth',
            'signature',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__'
        ),
        'C'=>array(
            'contact',
            'age',
            'job',
            '_on'=>'B.member_id=C.member_id',
            '_table'=>'__MEMBER_DAREN__',
            '_type'=>'LEFT'
        ),
        'D'=>array(
            'name'=>'city_name',
            '_on'=>'C.city_id=D.id',
            '_table'=>'__CITYS__'
        )

    );
}