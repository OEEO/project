<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class GoodsUpdateViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = array(
        'A'=>array('id','member_id','category_id','pic_id','stocks','price','title','is_pass','status', '_table'=>'__GOODS__'),
        'B'=>array(
            'group_id' => 'pics_group_id',
            'path'=>'pics_path',
            '_on'=>'A.pic_id=B.id',
            '_table'=>'__PICS__'
        ),
        'C'=>array(
            'content' => 'goods_sub_content',
            'pics_group_id' => 'goods_sub_pics_group_id',
            '_on' => 'A.id=C.goods_id',
            '_table' => '__GOODS_SUB__'
        ),
        'D'=>array(
            'nickname' => 'member_nickname',
            '_on' => 'A.member_id=D.id',
            '_table' => '__MEMBER__',
            '_type' => 'LEFT'
        ),
        'E'=>array(
            'tag_id' => 'goods_tag_tag_id',
            '_on' => 'A.id=E.goods_id',
            '_table' => '__GOODS_TAG__',
            '_type' => 'LEFT'
        ),
        'F'=>array(
            'name' => 'tag_name',
            '_on' => 'F.id=E.tag_id',
            '_table' => '__TAG__'
        )
    );
}