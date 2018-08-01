<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class GetCommentViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','type','type_id','stars','content','pid', '_table'=>'__MEMBER_COMMENT__'),
        'B'=>array(
            'nickname'=>'member_nickname',
            '_on'=>'A.member_id=B.id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ),
        'C'=>array(
            'id'=>'pics_group_id',
            'type'=>'pics_group_type',//type=1
            '_on'=>'A.pics_group_id=C.id',
            '_table'=>'__PICS_GROUP__',
        )
        /*'D'=>array(
            'path'=>'pics_path',
            '_on'=>'D.group_id=C.id',
            '_table'=>'__PICS__'
        )*/

    );
}