<?php
namespace Home\Model;
use Think\Model\ViewModel;

class GoodsMemberViewModel extends ViewModel {

    public $viewFields = array(
        'A'=>array('id','nickname', '_table'=>'__MEMBER__'),
        'B'=>array('member_id',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__'
        ),
        'C'=>array(
            'path',
            '_on'=>'A.pic_id=C.id',
            '_table'=>'__PICS__'),
    );


}
