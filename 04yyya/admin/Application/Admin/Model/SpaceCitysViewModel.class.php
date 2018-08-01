<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class SpaceCitysViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';
	public $viewFields = array(
		'A' => array(
			'id'=>'spaceId',
            'member_id'=>'member_id',
			'introduction'=>'introduction',
			'address'=>'address',
			'latitude'=>'latitude',
			'longitude'=>'longitude',
			'city_id'=>'areaId',
			'name'=>'spacename',
			'pic_id'=>'pic_id',
			'pic_group_id'=>'pic_group_id',
            '_table'=>'__SPACE__'
        ),
        'D' => array(
            'name'=>'AreaName',
			'pid' =>'City_pid',
            '_on' => 'A.city_id=D.id',
            '_table' => '__CITYS__',
            '_type' => 'LEFT'
        ),
		'C'=>array(
			'name' =>'City_name',
			'_on' => 'D.pid=C.id',
			'_table' => '__CITYS__',
            '_type' => 'LEFT'

		)

	);

}
