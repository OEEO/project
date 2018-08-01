<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TipsViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','category_id', 'is_top','member_id','price','buy_status','title','pic_id','is_pass','status','datetime','_table'=>'__TIPS__','_type'=>'LEFT'],
        'B'=> [
            'name'=>'category_name',
            '_on'=>'A.category_id=B.id',
            '_table'=>'__CATEGORY__',
            '_type' => 'LEFT'
        ],
        /*'C'=> [
            '_on'=>'A.id=C.tips_id',
            '_table'=>'__TIPS_TAG__'
        ],*/
         'D'=> [
             'telephone'=>'member_telephone',
             'nickname'=>'member_nickname',
             '_on'=>'A.member_id=D.id',
             '_table'=>'__MEMBER__',
             '_type' => 'LEFT'
         ],
         'E'=> [
             'path'=>'pics_path',
             '_on'=>'A.pic_id=E.id',
             '_table'=>'__PICS__',
             '_type' => 'LEFT'
         ],
         'F'=> [
             'content'=>'tips_sub_content',
             'citys_id'=>'tips_sub_citys_id',
             'address'=>'tips_sub_address',
             'pics_group_id'=>'tips_sub_pics_group_id',
             'environment_pics_group_id',
             'is_public',
             '_on'=>'A.id=F.tips_id',
             '_table'=>'__TIPS_SUB__',
             '_type' => 'LEFT'
         ],
        'G' =>[
            '_on'=>'A.space_id = G.id',
            '_table' => '__SPACE__',
            '_type' => 'LEFT'
        ],
        'H' => [
            'id' => 'apply_id',
            'channel' => 'apply_channel',
            'datetime' => 'apply_time',
            '_on' => "H.type_id=A.id",
            '_table' => '__MEMBER_APPLY__',
            '_type' => 'LEFT'
        ],
        /*'H' => [
            'id' => 'tag_id',
            'name' => 'tag_name',
            '_on' => 'C.tag_id=H.id',
            '_table' => '__TAG__',
            '_type' => 'LEFT'
        ],*/
        /*'I' => array(
            'type' => 'theme_tipsorgoods_type',
            '_on' => 'I.type_id=A.id',
            '_table' => '__THEME_TIPSORGOODS__',
            '_type' => 'LEFT'
        ),*/
        /*'J' => array(
            'id' => 'theme_id',
            'title' => 'theme_title',
            '_on' => 'J.id=I.theme_id',
            '_table' => '__THEME__',
            '_type' => 'LEFT'
        ),*/
        /*'K' => array(
            'tips_id'=> 'tips_time_tips_id',
            'phase' => 'tips_time_phase',
            'start_time' => 'tips_time_start_time',
            'end_time' => 'tips_time_end_time',
            '_on' => 'K.tips_id=A.id',
            '_table' => '__TIPS_TIMES__'
        )*/
        'L' => [
            'weight',
            '_type' => 'RIGHT',
            '_on' => 'A.id=L.t_id',
            '_table' => '__HOME__'
        ]
    ];

}
