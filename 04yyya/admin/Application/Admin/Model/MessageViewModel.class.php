<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MessageViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = array(
        'A'=>array('id','member_id', 'type' , 'content' , 'sms_send' , 'wx_send' , 'ios_push' , 'isMass' , 'sendtime' ,'datetime','_table'=>'__MESSAGE__','_type'=>'LEFT'),
        'B'=>array(
            'nickname',
            '_on'=>'A.member_id=B.id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ),
        'C'=>array(
            'title'=>'tips_title',
            '_on'=>'A.type=4 and A.type_id=C.id',
            '_table'=>'__TIPS__',
            '_type'=>'LEFT'
        ),
        'D'=>array(
            'title'=>'theme_title',
            '_on'=>'A.type=5 and A.type_id=D.id',
            '_table'=>'__THEME__',
            '_type'=>'LEFT'
        ),
        'E'=>array(
            'sn',
            '_on'=>'A.type=3 and A.type_id=E.id',
            '_table'=>'__ORDER__',
            '_type'=>'LEFT'
        ),
        'F'=>array(
            '_on'=>'A.type=2 and A.type_id=F.id',
            '_table'=>'__BANG__',
            '_type'=>'LEFT'
        ),
        'G'=>array(
            'path'=>'bang_path',
            '_on'=>'F.pic_id=G.id',
            '_table'=>'__PICS__'
        )



	);

}
