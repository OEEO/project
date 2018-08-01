<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberExportViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','nickname', 'telephone', 'openid','register_time','status', '_table'=>'__MEMBER__','_type'=>'LEFT'),
        'B'=>array(
            'citys_id'=>'member_info_citys_id',
            'sex' => 'member_info_sex',
            'birth' => 'member_info_birth',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__',
            '_type'=>'LEFT'
        ),
        'C'=>array(
            'name' => 'citys_name',
            '_on' => 'C.id=B.citys_id',
            '_table' => '__CITYS__',
            '_type' => 'LEFT'
        ),
        'E'=> [
            'tag_id',
            '_on'=>'A.id=E.member_id',
            '_table'=>'__MEMBER_TAG__'
        ],
    );

}
