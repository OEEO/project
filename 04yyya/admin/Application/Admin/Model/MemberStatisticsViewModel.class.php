<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberStatisticsViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> ["FROM_UNIXTIME(register_time, '%Y-%m-%d')"=> "registertime" , "count(distinct A.id)" =>"count_id", '_table'=>'__MEMBER__','_type'=>'LEFT'],
        'B'=> [
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_TAG__',
            '_type'=>'LEFT'
		]
	];
	
}
