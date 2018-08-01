<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class ThemeViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','pic_id','citys_id','sort','title','url','type','content','datetime','pic_group_id','_table'=>'__THEME__','_type'=>'LEFT'],
        'B'=> [
            'path',
            '_on'=>'B.id=A.pic_id',
            '_table'=>'__PICS__'
        ]
    ];
}