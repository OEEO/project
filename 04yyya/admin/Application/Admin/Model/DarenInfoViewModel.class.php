<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class DarenInfoViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','nickname','pic_id', 'telephone' , '_table'=>'__MEMBER__'),
        'B'=>array(
            'surname',
            'sex',
            'birth',
            'signature',
            'cover_pic_id',
            //'invitation_code'=>'member_invitation_code',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__'
        ),
        'C'=>array(
            '_on'=>'B.member_id=C.member_id',
            '_table'=>'__MEMBER_TAG__',
            '_type'=>'LEFT'
        ),
        'D'=>array(
            'path',
            '_on'=>'A.pic_id=D.id',
            '_table'=>'__PICS__',
            '_type'=>'LEFT'
        ),
        'E'=>array(
            'path'=>'cover_path',
            '_on'=>'B.cover_pic_id=E.id',
            '_table'=>'__PICS__',
            '_type'=>'LEFT'
        )
    );
}