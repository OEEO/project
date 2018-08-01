<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Model;

class Index2Controller extends Controller {
    protected $conf = array(
        'DB1' => array(
            'db_type'  => 'mysql',
            'db_user'  => 'root',
            'db_pwd'   => 'woaixuexi',
            'db_host'  => '123.57.205.111',
            'db_port'  => '3306',
            'db_name'  => 'youfan'),
        'DB2' => array(
            'db_type'  => 'mysql',
            'db_user'  => 'root',
            'db_pwd'   => 'DJjNmJiZTR',
            'db_host'  => 'localhost',
            'db_port'  => '3306',
            'db_name'  => 'yummy_bak4'),//////////////记得改回来
        'DB3' => array(
            'db_type'  => 'mysql',
            'db_user'  => 'root',
            'db_pwd'   => 'DJjNmJiZTR',
            'db_host'  => 'localhost',
            'db_port'  => '3306',
            'db_name'  => 'youfan'),
//        'DB4' => array(
//            'db_type'  => 'mysql',
//            'db_user'  => 'root',
//            'db_pwd'   => 'DJjNmJiZTR',
//            'db_host'  => 'localhost',
//            'db_port'  => '3306',
//            'db_name'  => 'yummy_bak'),
    );
    public function __construct(){
        set_time_limit(0);
    }
    //旧库
    private function m1($tableName){
        return M($tableName,'',$this->conf['DB3']);
    }
    //新库1
    private function m2($tableName){
        return M($tableName,'ym_',$this->conf['DB2']);
    }
    //新库2
//    private function m3($tableName){
//        return M($tableName,'admin_',$this->conf['DB3']);
//    }
//
//    private function m4($tableName){
//        return M($tableName,'admin_',$this->conf['DB4']);
//    }

    public function index(){
		$sql = "Select a.id as 'tips_id',d.name as 'food_type', c.name as 'food_name' from ym_tips a
				join youfan.`events` b on a.oldid=b.id
				join youfan.foods c on concat(';',b.food_ids,';') like concat('%;',c.id,';%')
				join youfan.food_types d on c.type_id=d.id
				where a.oldid<>'' and b.food_ids<>'' and a.id not in (Select tips_id from ym_tips_menu)";
		$rs = $this->m2()->query($sql);
		$data = [];
		foreach($rs as $row){
			$type = str_replace([',','@','|'], ['，','',''], $row['food_type']);
			$name = str_replace([',','@','|'], ['，','',''], $row['food_name']);
			if(!array_key_exists($row['tips_id'], $data))$data[$row['tips_id']] = [];
			if(!array_key_exists($type, $data[$row['tips_id']]))$data[$row['tips_id']][$type] = [];
			$data[$row['tips_id']][$type][] = $name;
		}
		echo count($data) . "\n";
		$datas = [];
		foreach($data as $tips_id => $row){
			foreach($row as $type => $name){
				$datas[] = [
					'tips_id' => $tips_id,
					'food_type' => $type,
					'food_name' => join(',', $name)
				];
			}
		}
		echo count($datas) . "\n";

		exit;
    }

    /**
     * 可以统计中文字符串长度的函数
     * @param $str 要计算长度的字符串
     * @param $type 计算长度类型，0(默认)表示一个中文算一个字符，1表示一个中文算两个字符
     *
     */
    function abslength($str)
    {
        if(empty($str)){
            return 0;
        }
        if(function_exists('mb_strlen')){
            return mb_strlen($str,'utf-8');
        }
        else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }

    /**
     * utf-8编码下截取中文字符串,参数可以参照substr函数
     * @param $str 要进行截取的字符串
     * @param $start 要进行截取的开始位置，负数为反向截取
     * @param $end 要进行截取的长度
     */
    function utf8_substr($str,$start=0)
    {
        if (empty($str)) {
            return false;
        }
        if (function_exists('mb_substr')) {
            if (func_num_args() >= 3) {
                $end = func_get_arg(2);
                return mb_substr($str, $start, $end, 'utf-8');
            } else {
                mb_internal_encoding("UTF-8");
                return mb_substr($str, $start);
            }

        } else {
            $null = "";
            preg_match_all("/./u", $str, $ar);
            if (func_num_args() >= 3) {
                $end = func_get_arg(2);
                return join($null, array_slice($ar[0], $start, $end));
            } else {
                return join($null, array_slice($ar[0], $start));
            }
        }
    }

    function createCode($len, $isNumber = true){
        $char = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        if($isNumber){
            return rand(pow(10, $len - 1), pow(10, $len)) - 1;
        }else{
            $code = '';
            for($i=0; $i<$len; $i++){
                $code .= $char[rand(0, 62) - 1];
            }
            return $code;
        }
    }

    //批量添加
    private function addAll($table, $dataAll){
        if(empty($dataAll))return 0;
        $arrKey = $arrVal = $chars = [];

        //组装数据
        foreach($dataAll as $datas){
            $arr = array_keys($datas);
            if(empty($arrKey))$arrKey = $arr;
            if($arrKey != $arr)continue;
            $vals = array_values($datas);
            foreach($vals as $k => $v){
                if($v === null)
                    $vals[$k] = 'null';
                else
                    $vals[$k] = "'" . addslashes($v) . "'";
            }
            $arrVal[] = "(" . join(",", $vals) . ")";
        }
        $sql = "Insert into `ym_{$table}` (`". join('`,`', $arrKey) ."`) values ". join(',', $arrVal) .";";
        return $this->m2()->execute($sql);
    }
    
    /**
     * 下载远程图片
     * @param string $pic_url 远程图片地址
     * @param string $save_path 图片保存目录
     * @param string $is_thumb 生成缩略图
     * @return boolean 是否成功
     */
    private function getPicAndSave($pic_url, $is_thumb = []) {
    	$header_array = get_headers($pic_url, true);
    	$size = $header_array['Content-Length'];
    	if($size > C('UPLOAD_CONFIG.maxSize'))return false;
    	$picinfo = getimagesize($pic_url);
    	$type = strrchr($pic_url, '.');
    	switch($picinfo[2]){
    		case 1 : $type = '.gif';break;
    		case 2 : $type = '.jpg';break;
    		case 3 : $type = '.png';break;
    	}
    	//$path = C('UPLOAD_CONFIG.rootPath') . $save_path;
    	$path = C('UPLOAD_CONFIG.rootPath');
    	if(!is_dir($path))mkdir($path);
    	$path .= '/' . date('Ymd');
    	if(!is_dir($path))mkdir($path);
    	$newname = $this->createCode(20);
    	\Org\Net\Http::curlDownload($pic_url, $path . '/' . $newname . $type);
    
    	if(!empty($is_thumb)){
    		$tb_conf = C("THUMB_CONFIG");
    		foreach($is_thumb as $tb){
    			$width = $tb_conf[$tb][0];
    			$height = $tb_conf[$tb][1];
    			$image = new \Think\Image();
    			$image->open($path . '/' . $newname . $type);
    			$tb_name = $newname . '_' . $width . 'x' . $height;
    			$image->thumb($width, $height,\Think\Image::IMAGE_THUMB_CENTER)->save($path . '/' . $tb_name . $type);
    		}
    		return date('Ymd') . '/' . $tb_name . $type;
    	}
    	return date('Ymd') . '/' . $newname . $type;
    }

	function zouni(){
		$this->member();
		$this->tips();
		$this->coupon();
		$this->orderAndComment();
	}

    //转移会员表
    function member(){
        //$this->m2('')->startTrans();
        $sum = $this->m1('users')->where(['id' => ['GT', 266499]])->count();
        $last_member_id = $this->m2('member')->order('id desc')->getField('id') + 1;
        $pics = ['1000000001','1000000002','1000000003','1000000004','1000000005','1000000006','1000000007','1000000008','1000000009'];
        $constellation = ['白羊座' => 1, '金牛座' => 2, '双子座' => 3, '巨蟹座' => 4, '狮子座' => 5, '处女座' => 6, '天秤座' => 7, '天蝎座' => 8, '射手座' => 9, '摩羯座' => 10, '水瓶座' => 11, '双鱼座' => 12];
        $city = ['北京' => 35, '上海' => 37, '广州' => 224, '深圳' => 234, '合肥' => 121, '杭州' => 112];
        $datetime = date('Y-m-d H:i:s');
        for($page = 1; $page <= ceil($sum/500); $page ++){
            $rs = $this->m1('users')->page($page, 500)->where(['id' => ['GT', 266499]])->select();
            $member_data = $member_info_data = $member_daren_data = $member_tag_data = [];
            foreach($rs as $row){
                if(is_numeric($row['phone'])){
                    $username = $row['phone'];
                }elseif(!empty($row['weixin_openid'])){
                    $username = $row['weixin_openid'];
                }
                $nickname = $row['name'];
                if(empty($nickname)){
                    if(is_numeric($row['phone']))
                        $nickname = '手机号_' . substr($row['phone'], 0, 3) . '****' . substr($row['phone'], 7, 4);
                    else
                        $nickname = '匿名用户';
                }
                //获取图片并入库
                if(substr($row['avatar'], 0, 19) == 'http://wx.qlogo.cn/'){
                    $pic_id = $this->m2('pics')->add([
                        'path' => $row['avatar'],
                        'is_used' => 1
                    ]);
                }else{
                    $pic_id = $pics[rand(0,8)];
                }

                //会员核心表
                $member_data[] = [
                    'id' => ++ $last_member_id,
                    'username' => $username,
                    'telephone' => is_numeric($row['phone'])?$row['phone']:'',
                    'email' => $row['email'],
                    'nickname' => $nickname,
                    'pic_id' => $pic_id,
                    'yf_openid' => $row['weixin_openid']?:'',
                    'channel' => !empty($row['weixin_openid'])?6:5,
                    'invitecode' => $this->createCode(32, false),
                    'status' => $row['is_delete']?0:1,
                    'register_time' => strtotime($row['created_at']),
                    'oldid' => $row['id']
                ];

                //会员信息表
                $member_info = ['member_id' => $last_member_id];
                if(array_key_exists($row['location'], $city))$member_info['citys_id'] = $city[$row['location']];
                else $member_info['citys_id'] = 224;
                if(!empty($row['birthday']))$member_info['birth'] = strtotime($row['birthday']) > 0 ? strtotime($row['birthday']) : 0;
                else $member_info['birth'] = 0;
                if(!empty($row['birthday']))$member_info['birth'] = strtotime($row['birthday']) > 0 ? strtotime($row['birthday']) : 0;
                else $member_info['birth'] = 0;
                if(!empty($row['job']))$member_info['company'] = $row['job'];
                else $member_info['company'] = null;
                if(!empty($row['updated_at']))$member_info['last_update_time'] = strtotime($row['updated_at']);
                else $member_info['last_update_time'] = null;
                if(!empty($row['constellation']))$member_info['constellation'] = $constellation[$row['constellation']];
                else $member_info['constellation'] = null;
                $member_info_data[] = $member_info;

                //获取host表数据
                $host_rs = $this->m1('hosts')->where(['user_id' => $row['id']])->find();
                if(!empty($host_rs)){
                    //达人表
                    $member_daren = ['member_id' => $last_member_id];
                    if(!empty($host_rs['city']) && array_key_exists($host_rs['city'], $city))
                        $member_daren['city_id'] = $city[$host_rs['city']];
                    else
                        $member_daren['city_id'] = 224;
                    if(!empty($host_rs['site']))$member_daren['site'] = $host_rs['site'];
                    else $member_daren['site'] = null;
                    if(!empty($host_rs['phone']))$member_daren['contact'] = $host_rs['phone'];
                    else $member_daren['contact'] = null;
                    if(!empty($host_rs['wechat']))$member_daren['wechat'] = $host_rs['wechat'];
                    else $member_daren['wechat'] = null;
                    if(!empty($host_rs['occupation']))$member_daren['job'] = $host_rs['occupation'];
                    else $member_daren['job'] = null;
                    if(!empty($host_rs['age']))$member_daren['age'] = $host_rs['age'];
                    else $member_daren['age'] = null;
                    if(!empty($host_rs['description']))$member_daren['introduce'] = $host_rs['description'];
                    else $member_daren['introduce'] = null;
                    if(!empty($host_rs['created_at']))$member_daren['datetime'] = $host_rs['created_at'];
                    else $member_daren['datetime'] = $datetime;
                    $member_daren_data[] = $member_daren;
                    
                    $member_tag_data[] = [
                    	'member_id' => $last_member_id,
                    	'tag_id' => 18
                    ];
                }
            }
            $n1 = $this->addAll('member', $member_data);
            $n2 = $this->addAll('member_info', $member_info_data);
            $n3 = $this->addAll('member_daren', $member_daren_data);
            $n4 = $this->addAll('member_tag', $member_tag_data);
            //$this->m2('')->comment();
            echo "已执行 " . ($page * 500) . " 条数据, 成功导入 {$n1}/{$n2}/{$n3}/{$n4} 条数据!\n";
            ob_flush();
            flush();
        }

    }
    
    function tips(){
    	//$this->m2('')->startTrans();
    	$sum = $this->m1('events')->where(['id' => ['GT', 3979]])->count();
    	$page_count = 50;
    	$last_tips_id = $this->m2('tips')->order('id desc')->getField('id') + 1;
    	$city = ['北京' => 35, '上海' => 37, '广州' => 224, '深圳' => 234, '合肥' => 121, '杭州' => 112];
    	for($page=1; $page <= ceil($sum / $page_count); $page++){
    		$rs = $this->m1('events')->where(['id' => ['GT', 3979]])->page($page, $page_count)->select();
    		$tips_data = $tips_sub_data = $tips_menu_data = $tips_times_data = $tips_tag_data = [];
    		$pics_num = 0;
    		foreach($rs as $row){
    			if(empty($row['host_id']))continue;
    			$user_id = $this->m1('hosts')->where(['id' => $row['host_id']])->getField('user_id');
    			if(empty($user_id))continue;
    			$member_id = $this->m2('member')->where(['oldid' => $user_id])->getField('id');
    			
    			//下载图片并保存到本地
				//封面
//     				$pic = $this->m1('pictures')->where(['type' => 'event_cover', 'type_id' => $row['id']])->getField('url');
//     				$pic_id = null;
//     				if(!empty($pic)){
//     					if($path = $this->getPicAndSave(str_replace('/uploads', 'http://youfanapp.b0.upaiyun.com', $pic), [1])){
//     						$pic_id = $this->m2('pics')->add([
//     							'member_id' => $member_id,
//     							'type' => 0,
//     							'path' => $path,
//     							'size' => '[[640, 420]]',
//     							'is_used' => 1
//     						]);
//     					}
//     				}
				//活动和菜单图组
				$pic_rs = $this->m1('pictures')->where(['type' => 'menu_item', 'type_id' => $row['style_menu_item_id']])->getField('url', true);
				$pic_id = $pics_group_id = $menu_pics_group_id = null;
				if(!empty($pic_rs)){
					$pics_group_id = $this->m2('pics_group')->add(['type' => 0]);
					$menu_pics_group_id = $this->m2('pics_group')->add(['type' => 0]);
					foreach($pic_rs as $pic){
						//if($path = $this->getPicAndSave(str_replace('/uploads', 'http://youfanapp.b0.upaiyun.com', $pic), [1])){
						$path = str_replace('/uploads', 'http://youfanapp.b0.upaiyun.com', $pic);
						$pics_num ++;
						$pic_id = $this->m2('pics')->add([
							'member_id' => $member_id,
							'group_id' => $pics_group_id,
							'type' => 0,
							'path' => $path,
							'size' => '[[640, 420]]',
							'is_used' => 1
						]);
						$this->m2('pics')->add([
							'member_id' => $member_id,
							'group_id' => $menu_pics_group_id,
							'type' => 0,
							'path' => $path,
							'size' => '[[640, 420]]',
							'is_used' => 1
						]);
						//}
					}
				}
				$pic_rs = $this->m1('pictures')->where(['type' => 'host_show', 'type_id' => $row['host_id']])->getField('url', true);
				$environment_pics_group_id = null;
				if(!empty($pic_rs)){
					$environment_pics_group_id = $this->m2('pics_group')->add(['type' => 0]);
					foreach($pic_rs as $pic){
						//if($path = $this->getPicAndSave(str_replace('/uploads', 'http://youfanapp.b0.upaiyun.com', $pic), [1])){
						$path = str_replace('/uploads', 'http://youfanapp.b0.upaiyun.com', $pic);
						$pics_num ++;
						$this->m2('pics')->add([
							'member_id' => $member_id,
							'group_id' => $environment_pics_group_id,
							'type' => 0,
							'path' => $path,
							'size' => '[[640, 420]]',
							'is_used' => 1
						]);
						//}
					}
				}
    			
    			//活动报名的截止时间
    			$stop_buy_time = strtotime($row['start_date']) - strtotime($row['end_date']);
    			//状态
    			if($row['is_delete'])$status=0;
    			elseif($row['is_complete'])$status=3;
    			elseif($row['is_shelve'])$status=1;
    			else $status = 2;
    			
    			$tips_data[] = [
	    			'id' => ++$last_tips_id,
	    			'category_id' => 1,
	    			'member_id' => $member_id,
    				'price' => $row['price'],
    				'title' => $row['title'],
    				'pic_id' => $pic_id,
    				'stop_buy_time' => $stop_buy_time > 0 ? $stop_buy_time : 24 * 3600,
    				'min_num' => $row['min_count'],
    				'restrict_num' => $row['max_count'],
    				'is_pass' => $row['is_sale'],
    				'status' => $status,
    				'oldid' => $row['id']
	    		];
    			
    			//活动副表
    			//亮点
    			$edges = [];
    			if(!empty($row['point']))$edges = explode('-', $row['point']);
    			$edge_1 = isset($edges[0]) ? $edges[0] : null;
    			$edge_2 = isset($edges[1]) ? $edges[1] : null;
    			$edge_3 = isset($edges[2]) ? $edges[2] : null;
    			//地址
    			if(!empty($row['address_id'])){
    				$ad = $this->m1('addresses')->where(['id' => $row['address_id']])->find();
    				if(!empty($ad)){
    					$postion = explode(';', $ad['private_lat_lng']);
    				}else{
    					$ad = ['private_address' => null, 'public_address' => null];
    					$postion = [null, null];
    				}
    			}else{
    				$ad = ['private_address' => null, 'public_address' => null];
    				$postion = [null, null];
    			}
    			$tips_sub_data[] = [
    				'tips_id' => $last_tips_id,
    				'edge_1' => $edge_1,
    				'edge_2' => $edge_2,
    				'edge_3' => $edge_3,
    				'intro' => $row['description']?:null,
    				'citys_id' => $city[$row['city']]?:35,
    				'address' => $ad['private_address']?:null,
    				'simpleaddress' => $ad['public_address']?:null,
    				'longitude' => $postion[1]?:null,
    				'latitude' => $postion[0]?:null,
    				'pics_group_id' => $pics_group_id,
    				'environment_pics_group_id' => $environment_pics_group_id,
    				'menu_pics_group_id' => $menu_pics_group_id
    			];
    			
    			//菜单
    			$menu_rs = $this->m1('foods')->join('left join food_types on type_id=food_types.id')->field('foods.name as f_name, food_types.name as t_name')->where(['foods.id' => ['IN', str_replace(';', ',', $row['food_ids'])]])->select();
    			$foods = [];
    			foreach($menu_rs as $menu){
    				if(!array_key_exists($menu['t_name'], $foods))$foods[$menu['t_name']] = [];
    				$foods[$menu['t_name']][] = $menu['f_name'];
    			}
    			foreach($foods as $type => $food){
    				$tips_menu_data[] = [
    					'tips_id' => $last_tips_id,
    					'food_type' => $type,
    					'food_name' => join(',', $food)
    				];
    			}
    			
    			//时间段
    			$tips_times_data[] = [
    				'tips_id' => $last_tips_id,
    				'phase' => 1,
    				'start_time' => !empty($row['start_date']) ? strtotime($row['start_date']) : 0,
    				'end_time' => !empty($row['start_date']) ? strtotime($row['start_date']) + 7200 : 0,
    				'stock' => 0
    			];
    		}
    		$tips_num = $this->addAll('tips', $tips_data);
    		$tips_sub_num = $this->addAll('tips_sub', $tips_sub_data);
    		$tips_menu_num = $this->addAll('tips_menu', $tips_menu_data);
    		$tips_times_num = $this->addAll('tips_times', $tips_times_data);
    		echo "已执行 " . ($page * $page_count) . " 条数据, 成功导入 tips[{$tips_num}]/tips_sub[{$tips_sub_num}]/tips_menu[{$tips_menu_num}]/tips_times[{$tips_times_num}] 条数据! 需下载图片 {$pics_num} 张\n";
    		ob_flush();
    		flush();
    	}
		//$this->m2('')->comment();
    }

	function coupon(){
		//$this->m2('')->startTrans();
		$sum = $this->m1('coupons')->count();
		$page_count = 50;
		$last_coupon_id = $this->m2('coupon')->order('id desc')->getField('id') + 100;
		for($page=1; $page <= ceil($sum / $page_count); $page++){
			$rs = $this->m1('coupons')->page($page, $page_count)->select();
			$coupon_data = $member_coupon_data = [];
			foreach($rs as $row){
				$coupon_data[] = [
					'id' => ++$last_coupon_id,
					'member_id' => 1,
					'category' => 0,
					'name' => $this->utf8_substr($row['title'], 0, 10),
					'type' => 0,
					'value' => $row['price'],
					'count' => $row['count'],
					'start_time' => strtotime($row['start_date']),
					'end_time' => strtotime($row['end_date']),
					'min_amount' => $row['order_scope'],
					'status' => 0,
					'datetime' => $row['created_at'],
					'oldid' => $row['id']
				];

				$coupon_rs = $this->m1('coupon_numbers')->where(['coupon_id' => $row['id'], 'is_used' => 1])->select();
				if(!empty($coupon_rs)){
					$user_ids = [];
					foreach($coupon_rs as $coupon) {
						$user_ids[] = $coupon['user_id'];
					}
					$member_rs = $this->m2('member')->field(['id', 'oldid'])->where(['oldid' => ['IN', join(',', $user_ids)]])->select();
					$member_ids = [];
					foreach($member_rs as $r){
						$member_ids[$r['oldid']] = $r['id'];
					}
					foreach($coupon_rs as $coupon){
						$member_coupon_data[] = [
							'member_id' => $member_ids[$coupon['user_id']],
							'coupon_id' => $last_coupon_id,
							'sn' => $this->createCode(12),
							'used_time' => $coupon['is_used'] ? strtotime($coupon['updated_at']) : 0,
							'datetime' => $coupon['created_at'],
							'oldid' => $coupon['id']
						];
					}
				}
			}
			$coupon_num = $this->addAll('coupon', $coupon_data);
			$member_coupon_num = $this->addAll('member_coupon', $member_coupon_data);
			echo "已执行 " . ($page * $page_count) . " 条数据, 成功导入 coupon[{$coupon_num}]/member_coupon[{$member_coupon_num}] 条数据!\n";
			ob_flush();
			flush();
		}
		//$this->m2('')->comment();
	}

	function orderAndComment(){
		//$this->m2('')->startTrans();
		$sum = $this->m1('purchases')->where(['is_delete' => 0, 'id' => ['GT', 17798]])->count();
		$page_count = 100;
		$last_order_id = $this->m2('order')->order('id desc')->getField('id') + 1;
		for($page=1; $page <= ceil($sum / $page_count); $page++){
			$rs = $this->m1('purchases')->where(['is_delete' => 0, 'id' => ['GT', 17798]])->page($page, $page_count)->select();
			$order_data = $order_wares_data = [];
			$pics_num = $comment_num = 0;
			foreach($rs as $row){
				$tips_id = $this->m2('tips')->where(['oldid' => $row['event_id']])->getField('id');
				if(empty($tips_id))continue;
				$member_id = $this->m2('member')->where(['oldid' => $row['user_id']])->getField('id');
				if(empty($member_id))continue;

				//订单状态
				if($row['is_payed'] == 1){
					$act_status = 4;
				}else{
					$act_status = 7;
				}
				//价格
				$price = $row['total'] - $row['coupon_fee'];
				//优惠券
				$member_coupon_id = null;
				if(!empty($row['coupon_number_id'])){
					$member_coupon_id = $this->m2('member_coupon')->where(['oldid' => $row['coupon_number_id']])->getField('id');
				}
				//评价
				$comment_rs = $this->m1('comments')->where(['purchase_id' => $row['id']])->find();
				$comment_id = null;
				if(!empty($comment_rs) && !empty($comment_rs['content'])){
					//评价用户ID
					$m_id = $this->m2('member')->where(['oldid' => $comment_rs['user_id']])->getField('id');
					//评价图组
					$pics_group_id = null;
					$comment_pics = $this->m1('pictures')->where(['type' => 'comment', 'type_id' => $comment_rs['id']])->getField('cdn', true);
					if(!empty($comment_pics)){
						$pics_group_id = $this->m2('pics_group')->add(['type' => 2]);
						foreach($comment_pics as $path){
							$pics_num ++;
							$this->m2('pics')->add([
								'member_id' => $member_id,
								'group_id' => $pics_group_id,
								'type' => 2,
								'path' => $path,
								'size' => [[200,200]],
								'is_used' => 1
							]);
						}
					}
					$comment_id = $this->m2('member_comment')->add([
						'member_id' => $m_id,
						'stars' => $comment_rs['service'],
						'type' => 0,
						'type_id' => $tips_id,
						'content' => $comment_rs['content'],
						'pics_group_id' => $pics_group_id,
						'status' => $comment_rs['is_show'],
						'datetime' => $comment_rs['created_at']
					]);
					$comment_num ++;
				}
				$order_data[] = [
					'id' => ++$last_order_id,
					'sn' => $row['id'],
					'member_id' => $member_id,
					'price' => $price > 0 ? $price : 0,
					'act_status' => $act_status,
					'member_coupon_id' => $member_coupon_id,
					'create_time' => strtotime($row['created_at']),
					'comment_id' => $comment_id,
					'is_book' => $row['type'],
					'datetime' => $row['updated_at'],
					'oldid' => $row['id']
				];

				//订单商品表
				for($i=0; $i < $row['count']; $i++){
					$order_wares_data[] = [
						'order_id' => $last_order_id,
						'type' => 0,
						'ware_id' => $tips_id,
						'price' => $row['price'],
						'check_code' => $row['validation_code']?:$this->createCode(8),
						'server_status' => $row['is_payed'],
						'tips_times_id' => $this->m2('tips_times')->where(['tips_id' => $tips_id])->getField('id'),
						'snapshot' => $this->m1('snapshots')->where(['purchase_id' => $row['id']])->getField('content'),
						'datetime' => $row['updated_at']
					];
				}
			}
			$order_num = $this->addAll('order', $order_data);
			$order_wares_num = $this->addAll('order_wares', $order_wares_data);
			echo "已执行 " . ($page * $page_count) . " 条数据, 成功导入 order[{$order_num}]/order_wares[{$order_wares_num}]/member_comment[{$comment_num}] 条数据! 需下载图片 {$pics_num} 张\n";
			ob_flush();
			flush();
		}
		//$this->m2('')->comment();
	}

}