<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class BannerViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
        'A'=> [
            'id','citys_id','title','pic_id','url','type','is_show','sort',
            '_table'=>'__BANNERS__',
            '_type'=>'LEFT'
		],
        'B'=> [
            'path',
            '_on'=>'A.pic_id=B.id',
            '_table'=>'__PICS__',
            '_type'=>'LEFT'
		],
        'C'=>[
            'name' => 'city_name',
            '_on'=>'A.citys_id=C.id',
            '_table'=>'__CITYS__'
        ]
	];
	
}
