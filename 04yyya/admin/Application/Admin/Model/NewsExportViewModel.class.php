<?php
/**
 * Created by PhpStorm.
 * User: Cherry
 * Date: 2017/5/16 0016
 * Time: 16:37
 */

namespace Admin\Model;
use Think\Model\ViewModel;

class NewsExportViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','category_id', 'title','abstract','status','datetime','_table'=>'__NEWS__','_type'=>'LEFT'],
        'B'=> [
            'name'=>'category_name',
            '_on'=>'A.category_id=B.id',
            '_table'=>'__CATEGORY__',
            '_type' => 'LEFT'
        ],
        'C'=> [
            'nickname'=>'member_nickname',
            '_on'=>'A.member_id=C.id',
            '_table'=>'__MEMBER__',
            '_type' => 'LEFT'
        ],
        'D'=> [
            'path'=>'pics_path',
            '_on'=>'A.pic_id=D.id',
            '_table'=>'__PICS__',
            '_type' => 'LEFT'
        ],
    ];

}