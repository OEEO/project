<?php
namespace Admin\Model;
use Think\Model;

class UserModel extends Model {
	
	public $connection = 'DB1';
	protected $tablePrefix = 'admin_';
	
	protected $_validate = array(
		array('username','','帐号名称已经存在！',0,'unique'),
		array('password','/^.{8,32}$/','密码格式不正确'),
		array('group_id','require','管理员组不能为空！'),
        array('email','require','邮箱不能为空！'),
        array('telephone','require','联系电话不能为空！')
	);
	
}
