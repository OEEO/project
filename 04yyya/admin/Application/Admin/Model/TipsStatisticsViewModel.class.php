<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TipsStatisticsViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> [ '_table'=>'__TIPS__','_type'=>'LEFT'],
        'B'=> [
			"count(distinct B.id)" =>"count_id",
			'FROM_UNIXTIME(start_time, "%Y-%m-%d")'=> 'start_time' ,
			'FROM_UNIXTIME(release_time, "%Y-%m-%d")'=> 'release_time' ,
            '_on'=>'B.tips_id=A.id',
            '_table'=>'__TIPS_TIMES__',
            '_type'=>'LEFT'
		],
		'C'=> [
			'is_public',
			'_on'=>'C.tips_id=A.id',
			'_table'=>'__TIPS_SUB__',
			'_type'=>'LEFT'
		],
	];
	
}
