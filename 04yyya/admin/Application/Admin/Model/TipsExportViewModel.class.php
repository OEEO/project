<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TipsExportViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','price','title','category_id','status','_table'=>'__TIPS__'],
        'B'=> [
            'nickname'=>'member_nickname',
            '_on'=>'A.member_id=B.id',
            '_table'=>'__MEMBER__',
            '_type' => 'LEFT'
        ],
        'C'=> [
            'id'=>'tips_times_id',
            'min_num',
            'max_num',
            'phase',
            'start_time'=>'tips_times_start_time',
            'end_time'=>'tips_times_end_time',
            '_on'=>'A.id=C.tips_id',
            '_table'=>'__TIPS_TIMES__',
            '_type' => 'LEFT'
        ],
        'D'=> [
            'name' => 'category_name',
            '_on'=>'D.id=A.category_id',
            '_table'=>'__CATEGORY__',
            '_type' => 'LEFT'

        ],
        'F'=> [
            /*'content'=>'tips_sub_content',
            'citys_id'=>'tips_sub_citys_id',
            'address'=>'tips_sub_address',
            'pics_group_id'=>'tips_sub_pics_group_id',*/
            'citys_id'=>'tips_sub_citys_id',
            '_on'=>'A.id=F.tips_id',
            '_table'=>'ym_tips_sub'
        ],
        'G'=> [
            'id'=>'citys_id',
            'name'=>'citys_name',
            '_on'=>'G.id=F.citys_id',
            '_table'=>'__CITYS__',
            '_type' => 'LEFT'
        ],

//        'H'=>array(
//            'count(tips_times_id)'=>'buy_num',
//            'tips_times_id',
//            '_on'=>'H.type=0 and C.id=H.tips_times_id ',
//            '_table'=>'__ORDER_WARES__',
//            '_type'=>'LEFT'
//        ),
//        'I'=>array(
//            'status'=>'order_status',
//            '_on'=>'I.id=H.order_id ',
//            '_table'=>'__ORDER__',
//            '_type'=>'LEFT'
//        ),
//        'H'=>[
//            'city_id'=>'tips_sub_area_id',
//            'address'=>'address',
//            '_on'=>'H.id=A.space_id',
//            '_table'=>'__SPACE__',
//            '_type' => 'LEFT'
//        ],
//        'I'=> [
//            'id'=>'area_id',
//            'name'=>'area_name',
//            '_on'=>'I.id=H.city_id',
//            '_table'=>'__CITYS__',
//            '_type' => 'LEFT'
//        ]
        /*'H'=>array(
            '_on'=>'H.tips_id=A.id',
            '_table'=>'__TIPS_TAG__',
            '_type' => 'LEFT'

        ),*/
        /*'I'=>array(
            'id' => 'tag_id',
            'name'=>'tag_name',
            '_on'=>'I.id=H.tag_id',
            '_table'=>'__TAG__',
            '_type' => 'LEFT'
        ),*/
        /*'J'=>array(
            'type' => 'theme_tipsorgoods_type',
            '_on' => 'A.id=J.type_id',
            '_table'=>'__THEME_ELEMENT__',
            '_type' => 'LEFT'
        ),
        'K'=>array(
            'id' => 'theme_id',
            'title' => 'theme_title',
            '_on' => 'K.id=J.theme_id',
            '_table' => '__THEME__'
        )*/
    ];

}
