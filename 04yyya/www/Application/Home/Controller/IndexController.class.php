<?php

namespace Home\Controller;
use Home\Common\MainController;
use Common\Util\Cache;
use Symfony\Component\Finder\Expression\Expression;

// @className 页面常规接口
class IndexController extends MainController {
	
	public function index(){
//		$this->push_Message(11089, [], 'SMS_36020221', 'ios');
//
//		$data['name'] = 'ThinkPHP';
//		$data['email'] = 'ThinkPHP@gmail.com';
//		$User->where('id=5')->save($data); // 根据条件更新记录

//		M('MemberMessage')->where(['id'=>283228])->data(['is_sms'=>1])->save();
//		print_r(M('MemberMessage')->getLastSql());
//		$this->wechat->sendTemplateMessage([
//			"touser" => "oUX2WxBK5CUwjLfJ7sXw8xAw_SNk",
//			"template_id" => "dvdtDT3tShba3XpVvnhXAMlh9YMb-edIrJjwcIjy7uA",
//			"url" => "http://weixin.qq.com/download",
//			"topcolor" => "#FF0000",
//			"data" => [
//				"first" => [
//					"value" => "您2017年02月18日的打卡,被评论了..",
//					"color" => "#173177"
//				],
//				"keyword1" => [
//					"value" => "真的真的好棒哦~~~ 打的一手好卡!",
//					"color" => "#173177"
//				],
//				"keyword2" => [
//					"value" => "2017年3月11日 上午 10点23分",
//					"color" => "#173177"
//				],
//				"remark" => [
//					"value" => "点击查看详情",
//					"color" => "#173177"
//				]
//			]
//		]);
//		$j = 50;
//		for($i=1;$i<=$j;$i++){
//			$data = [
//				'username' => '吖咪',
//				'telephone' => ($i<10)?('1358800000'.$i):('135880000'.$i)  ,
//				'nickname' => '吖咪',
//				'register_time' => time(),
//				'datetime' => date('Y-m-d H:i:s'),
//				'channel' => 1,
//				'invitecode' => createCode(32, false)
//			];
//			$id = M('member')->add($data);
//echo $id;
//			M('MemberInfo')->add(['member_id'=>$id,'is_white'=>1]);
//			if($i>50)exit;
//		}
		exit;
	}
	
	/**
	 * @apiName 根据坐标获取地名地址
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {float} latitude: 经度坐标
	 * @apiPostParam {float} longitude: 纬度坐标
	 * @apiPostParam {int} is_location: 是否记录为本地城市(0-否（默认） 1-是)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": {
	 *         "city_id":"200",
	 *         "city_name": "荆州",
	 *         "nation": "中国",
	 *         "province": "湖北省",
	 *         "city": "荆州市",
	 *         "district": "松滋市",
	 *         "street": "白云路",
	 *         "street_number": "",
	 *         "inset":0 //该城市是否在开放城市列表中
	 *     },
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0,
	 *     "url": ""
	 * }
	 */
	Public function getAddress(){
		$latitude = (float)I('post.latitude');
		$longitude = (float)I('post.longitude');
		$is_location = I('post.is_location', 0);
		
		if(!is_float($latitude) || !is_float($longitude)){
			$this->error('坐标有误，请重新提交！');
		}
		
		$url = 'http://apis.map.qq.com/ws/geocoder/v1';
		$url .= '?location=' . $latitude . ',' . $longitude . '&key=' . C('TX_MAP_KEY');
		$data = file_get_contents($url);
		$data = json_decode($data, true);
		if($data['status'] == 0){
			$data = $data['result']['address_component'];
			$data['inset'] = 0;
			$citys = C('CITYS');
			foreach ($citys as $key => $city){
				if(strpos($data['city'], $city) !== false){
					if($is_location == 1){
						session('city_id', $key);
						session('city_name', $city);
						$data['city_id'] = $key;
						$data['city_name'] = $city;
					}
					$data['inset'] = 1;
				}
			}
			$this->success($data);
		}else{
			$this->error($data['message']);
		}
	}

    /**
     * @apiName 获取厨房列表(废弃)
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 页数
     *
     * @apiSuccessResponse
     * [
     *   {
     *     "id": "5",
     *     "space_name": "测试2",
     *     "address": "广州市越秀区中山一路",
     *     "proportion": "100",
     *     "volume": "10",
     *     "area_name": "越秀",
     *     "city_name": "广州",
     *     "category_name": "厨房分类1",
     *     "path": "http://img.m.yami.ren/20160528/225072399ab8937699b28525092ea6abc8ee43a0.jpg",
     *     "facility": [
     *     "1",
     *     "2"
     *     ]
     *   },
     *   {
     *     "id": "4",
     *     "space_name": "测试2",
     *     "address": "广州市天河区天河北路",
     *     "proportion": "100",
     *     "volume": "50",
     *     "area_name": "天河",
     *     "city_name": "广州",
     *     "category_name": "厨房分类1",
     *     "path": "http://img.m.yami.ren/20160527/d1219f6c593b24da56f091facbc5efd03c9016a4.jpg",
     *     "facility": [
     *     "2"
     *     ]
     *   },
     * ]
     */
    public function kitchenList(){
        $page = I('get.page',1);
        $pageSize = 5;

        $condition = [];
        //城市筛选
        $area_id = session('city_id');
        if(!empty($area_id)){
            $condition['C.id'] = $area_id;
        }
        $data = D('KitchenListView')->where($condition)->page($page,$pageSize)->order('id desc')->select();
        foreach($data as $row){
            $ids[] = $row['id'];
        }
        if(!empty($ids)){
            $space_facility = M('SpaceFacility')->where(['space_id'=>['IN',join(',',$ids)]])->select();
            $space_tag = D('KitchenTagView')->where(['space_id'=>['IN',join(',',$ids)]])->select();

            foreach($data as $key=>$row){
                $data[$key]['path'] = thumb($row['path'],10);
                $data[$key]['facility'] = [];
                $data[$key]['tag'] = [];
                foreach($space_facility as $r){
                    if($r['space_id'] == $row['id']){
                        $data[$key]['facility'][] = $r['facility_id'];
                    }
                }
                foreach($space_tag as $re){
                    if($row['id'] == $re['space_id']){
                        $data[$key]['tag'][] = $re['tag_name'];
                    }
                }
            }
        }

        $this->ajaxReturn($data);
    }

    /**
     * @apiName 获取厨房详情(废弃)
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} id: 厨房ID
     *
     * @apiSuccessResponse
     * {
     *     "id": "1",
     *     "name": "厨房",
     *     "introduction": "简介",
     *     "address": "地址",
     *     "pic_group_id": "1",
     *     "latitude": "0",
     *     "longitude": "0",
     *     "proportion": "50",
     *     "volume": "20",
     *     "opening_time": "100-200,400-500",
     *     "context": "备注",
     *     "category_name": "饭局",
     *     "city_name": "广州",
     *     "path": "http://img.m.yami.ren/20160523/3ZjFmYmE5NzNlZDJkODY3NDdiYzY1Z.jpg",
     *     "server_time": [
     *          "2016-05-23 00:01~2016-05-23 00:03",
     *          "2016-05-23 00:06~2016-05-23 00:08"
     *     ],
     *     "group_path": [
     *          "http://img.m.yami.ren/20160523/3ZjFmYmE5NzNlZDJkODY3NDdiYzY1Z.jpg",
     *          "http://img.m.yami.ren/20160523/DZlNzg2MGRhZjM4MzYzNTg4ZWY0NTN.jpg"
     *     ],
     *     "facility": [
     *          {
     *          "id": "1",
     *          "name": "wifi",
     *          "path": "http://img.m.yami.ren/20160523/DZlNzg2MGRhZjM4MzYzNTg4ZWY0NTN.jpg"
     *          },
     *          {
     *          "id": "2",
     *          "name": "wc",
     *          "path": "http://img.m.yami.ren/20160523/3ZjFmYmE5NzNlZDJkODY3NDdiYzY1Z.jpg"
     *          }
     *     ]
     * }
     */
    public function KitchenDetail(){
        $id = I('post.id');

        if(empty($id))$this->ajaxReturn('非法访问');
        $space_data = D('SpaceListView')->where(['id'=>$id])->find();

        //厨房标签
        $tags = D('KitchenTagView')->where(['space_id'=>$id])->getField('tag_name',true);
        $space_data['tags'] = empty($tags)?array():$tags;

		//数据格式处理
		$space_data['address'] = $space_data['city_name'].'市  '.$space_data['area_name'].'区  '.$space_data['address'];
        $space_data['path'] = thumb($space_data['path'],10);

		//计算开放时间
        $times = explode(',',$space_data['opening_time']);
        $date_array = [1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'日'];
        foreach($times as $row3){
            $times_sub = explode('-',$row3);
            //本周一0点时间戳
            $re = getmonsun();
            $monday = $re['mon'];
            //$space_data['server_time'][] = date('Y-m-d H:i',($monday+$times_sub[0])).'~'.date('Y-m-d H:i',($monday+$times_sub[1]));
            $space_data['server_time'][] = '周'.$date_array[date('N',$monday+$times_sub[0])].' '.date('H:i',$monday+$times_sub[0]) .'~'. '周'.$date_array[date('N',$monday+$times_sub[1])].' '.date('H:i',$monday+$times_sub[1]);
        }

        //厨房图组
        if(!empty($space_data['pic_group_id'])){
            $group_path = M('Pics')->where(['group_id'=>$space_data['pic_group_id']])->getField('path',true);
            foreach($group_path as $key=>$row){
                $group_path[$key] = thumb($row,10);
            }
            $space_data['group_path'] = $group_path;
        }

        //设备
        $facility_data = D('FacilityListView')->where(['B.space_id'=>$space_data['id']])->select();
        $space_data['facility'] = $facility_data;

        $this->ajaxReturn($space_data);
    }

    /**
     * @apiName 场地询价(废弃)
     *
     * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} space_id: 场地ID
	 * @apiPostParam {int} telephone: 联系电话
	 * @apiPostParam {string} aim: 活动目的
	 * @apiPostParam {int} month: 月份
	 * @apiPostParam {int} day: 日期
	 * @apiPostParam {int} time: 时间段(0-全天 1-上午 2-下午 3-晚上)
	 * @apiPostParam {int} num: 预估参与人数
	 * @apiPostParam {int} budget: 预算
	 * @apiPostParam {string} contacts: 联系人
	 * @apiPostParam {string} context: 留言
     *
     * @apiSuccessResponse
     * [
     *  status:1,
     *  info:'申请已提交，请耐心等候回复'
     * ]
     */
    public function kitchenApply(){
		if(!session('?member'))$this->error('没有登录!');
        $data = I('post.');
        if(empty($data))$this->error('资料填写不完整');
        if(!is_numeric($data['telephone']) || strlen($data['telephone'])>15)$this->error('非法电话号码');
        if(empty($data['aim']))$this->error('活动目的未填写');
        if(empty($data['month']) || empty($data['day']) || !isset($data['time']))$this->error('预约时间未填写');
        if(empty($data['num']))$this->error('参加人数未填写');
        if(empty($data['contacts']))$this->error('联系人未填写');

        $data['budget'] = empty($data['budget'])?0:$data['budget'];
        $re = M('SpaceApply')->where(['space_id'=>$data['space_id'],'telephone'=>$data['telephone'],'month'=>$data['month'],'day'=>$data['day'],'time'=>$data['time']])->find();
        if(!empty($re))$this->error('请勿重复申请');
        M('SpaceApply')->data($data)->add();

        if(session('member.id')){
            //发送信息
            $space_re = M('Space')->where(['id'=>$data['space_id']])->find();
            $city_id = M('Citys')->where(['id'=>$space_re['city_id']])->getField('pid');
            $charge = C('SPACE_CHARGE');
            if(in_array($city_id,array_keys($charge))){
                $telephone = $charge[$city_id];
//                $message = session('member.nickname').' 提交了 “厨房+” 【'.$space_re['name'].'】的询价';
//                sms_send($telephone, $message);

				//2016-12-27
				$params =array(
					'nickname'=>session('member.nickname'),
					'space_name'=>$space_re['name'],

				);
                smsSend($telephone,$params,'SMS_35990158');

            }
            //发送站内消息
            $message = '您的询价已反馈成功，我们会尽快联系您，若您有什么问题，可以直接联系吖咪厨房+负责人电话--广州：18672366543';
            $this->push_Message(session('member.id'),array(),null,null,$message);
        }
        $this->success('申请已提交，请耐心等候回复');
    }
	
	/**
	 * @apiName 获取顶部轮播图片
	 * 
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} type: 渠道 0-吖咪 1-我有饭 2-美食福利社 3-众筹 4-早餐打卡 5-食物学园
	 * 
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 	"path": "http://img.m.yami.ren/official/20160311/56e232f93b6b5_640x260.jpg",
	 * 	"url": "www.baidu.com"
	 * 	"c_type": "0"
	 * 	},
	 * 	{
	 * 	"path": "http://img.m.yami.ren/official/20160311/56e2332a3b0e9_640x260.jpg",
	 * 	"url": "www.qq.com"
	 * 	"c_type": "0"//区分私房菜和课程（1-私房菜 2-课程 0-其他的页面）
	 * 	}
	 * 	]
	 */
	public function banner(){
        $type = I('post.type', 0);
		if($type==0 && in_array($this->channel, [7,8,9])){
			$type = 1;
		}
		$rs = M('banners')->field("a.path, url")->join('__PICS__ a on a.id=pic_id')->where(['is_show' => 1, 'ym_banners.type'=>$type, 'citys_id' => [['EXP', 'is null'], session('city_id'), 'or']])->order('sort desc')->select();
		$data = [];
		foreach($rs as $row){
			$row['path'] = thumb($row['path'], 3);

			$row['c_type'] = 0;
			if(isset($row['url']) && strpos($row['url'], 'm.yami.ren') === false) {
				if(strpos($row['url'], 'specialDetail') === false){
					if(strpos($row['url'], 'courseDetail') === false){
						$row['c_type'] = 0;
					}else{//课程
						$row['c_type'] = 2;
					}
				}else{//私房菜
					$row['c_type'] = 1;
				}
			}else{
				if(strpos($row['url'], 'specialDetail') === false){
					if(strpos($row['url'], 'courseDetail') === false){
						$row['c_type'] = 0;
					}else{//课程
						$row['c_type'] = 2;
					}
				}else{//私房菜
					$row['c_type'] = 1;
				}

			}

			$data[] = $row;
		}
		$this->ajaxReturn($data);
	}
	
	/**
	 * @apiName 获取闪购列表(废弃)
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 *     "stocks": 8,
	 *     "type": "0",
	 *     "id": "2198",
	 *     "title": "吖咪烘焙课│小凯老师的原香烧烤鸡肉披萨和香浓培根芝士披萨",
	 *     "price": "168.00",
	 *     "status": "1",
	 *     "mainpic": "uploads/20150915/55f7f377888bd.jpg",
	 *     "market_price": "9.90",
	 *     "start_time": "1451353800",
	 *     "end_time": "1451353920"
	 * }
	 */
	/*public function flash(){
		//查出抢购专题下的商品id
		$rs = D('ThemeElementView')
		->where(array('theme_id' => 1, 'start_time' => array('GT', strtotime(date('Y-m-d 00:00:00')))))->order('sort, start_time')->find();//->order('sort, start_time,B.id desc')??
		if(empty($rs))$this->ajaxReturn(array());
		$data = array();
		$ware_id = $rs['type_id'];
		//根据type判断出是活动还是实物商品
		if($rs['type'] == 0){
			$tips_id = $rs['type_id'];
			//关联出具体的活动
			$data = D('MyTipsView')->field(array(
					'id','title','price','status','mainpic','restrict_num as stocks',"'0' as 'type'"
			))->where(array('id' => $tips_id, 'status' => 1, 'is_pass' => 1))->find();
			$data['mainpic'] = thumb($data['mainpic'], 1);
		} else {
			$goods_id = $rs['type_id'];
			//关联出具体的商品
			$data = D('GoodsView')->field(array(
					'id','title','stocks', 'price','status','mainpic',"'1' as type"
			))->where(array('id' => $goods_id, 'status' => 1))->find();
		}

		//加入折扣信息
		$data['market_price'] = $rs['price'];
		$data['start_time'] = $rs['start_time'];
		$data['end_time'] = $rs['end_time'];

		//计算剩余库存数量
		$count = M('OrderWares')->where(array('type' => $rs['type'], 'ware_id' => $ware_id))->count();
		$data['stocks'] = $data['stocks'] - $count;
		if($data['stocks'] < 0)$data['stocks'] = "∞";

		$this->ajaxReturn($data);
	}*/
	
	/**
	 * @apiName 获取吖咪推荐活动列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "id": "4209",
 	 *        "path": "uploads/20151216/5670ee069d6aa.jpg"
 	 *    },
 	 *    {
 	 *        "id": "4210",
 	 *        "path": "uploads/20151216/56710748736c6.jpg"
	 *     },
	 * ]
	 */
	public function recommend(){
		$tops = D('TipsView')->field(['id', 'path'])->where(array('is_top' => 1, 'status' => 1, 'is_pass' => 1, 'start_time' => ['GT', time()]))->limit(15)->select();
		foreach($tops as $key => $row){
			$tops[$key]['path'] = thumb($row['path'], 1);
		}
		$this->ajaxReturn($tops);
	}
	
	/**
	 * @apiName 获取热门标签列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "id": "1",
	 *         "name": "牛肉"
	 *     },
 	 *    {
	 *         "id": "2",
	 *         "name": "火锅"
 	 *    },
	 * ]
	 */
	public function tags(){
		$tops = M('tag')->field('id,name')->where(['type' => 1])->limit(20)->select();
		$this->ajaxReturn($tops);
	}

    /**
     * @apiName 获取官方标签列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} type: 标签类型  0：达人，1：活动，2：商品
     *
     * @apiSuccessResponse
     * [
     *     {
     *         "id": "1",
     *         "name": "牛肉"
     *     },
     *    {
     *         "id": "2",
     *         "name": "火锅"
     *    },
     * ]
     */
    public function official_tags(){
        $type = I('post.type',1);
        $tops = M('tag')->field('id,name')->where(array('type' => $type , 'official' => 1))->select();
        $this->ajaxReturn($tops);
    }

	/**
	 * @apiName 获取当前经营的城市列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "id": "1",
	 *         "name": "北京"
	 *     },
	 *    {
	 *         "id": "224",
	 *         "name": "广州"
	 *    },
	 * ]
	 */
	public function citys(){
		$this->ajaxReturn(C('CITYS'));
	}

	/**
	 * @apiName 切换当前经营的城市
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} city_id: 城市id
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info" : {
	 *         "id": "1",
	 *         "name": "北京"
	 *     },
	 *     "status" : 1
	 * }
	 */
	public function changeCity(){
		$city_id = I('post.city_id');
		$citys = C('CITYS');
		if(array_key_exists($city_id, $citys)){
			$city_name = M('citys')->where(['id' => $city_id])->getField('name');
			session('city_id', $city_id);
			session('city_name', $city_name);
			$this->success(['id'=>$city_id, 'name'=>$city_name]);
		}else{
			$this->error('切换失败!目标城市尚未覆盖!');
		}
	}
	
	/**
	 * @apiName 获取当前城市的区
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "area_id": "2094",
	 *         "area_name": "海珠区",
	 *         "city_id": "224",
	 *         "city_name": "广州市",
	 *         "province_id": "19",
	 *         "province_name": "广东省"
	 *     },
	 *     {
	 *         "area_id": "2095",
	 *         "area_name": "天河区",
	 *         "city_id": "224",
	 *         "city_name": "广州市",
	 *         "province_id": "19",
	 *         "province_name": "广东省"
	 *     },
	 * ]
	 */
	public function area(){
		$city_id = session('city_id');
		$rs = D('AreaView')->where(array('city_id' => $city_id))->select();
		$this->ajaxReturn($rs);
	}
	
	/**
	 * @apiName 获取城市列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} pid: 父级城市ID（不填则返回所有省份）
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "id": "1",
	 *         "name": "北京",
	 *         "pinyin": "Beijing",
	 *         "pid": null,
	 *         "alt": "直辖市"
	 *     },
	 *     {
	 *         "id": "3",
	 *         "name": "河北",
	 *         "pinyin": "Hebei",
	 *         "pid": null,
	 *         "alt": "省"
	 *     },
	 *     {
	 *         "id": "4",
	 *         "name": "山西",
	 *         "pinyin": "Shanxi",
	 *         "pid": null,
	 *         "alt": "省"
	 *     },
	 * ]
	 */
	Public function getCityList(){
		$pid = I('post.pid');
		$where = 'pid is null';
		if(!empty($pid)){
			$where = array('pid' => $pid);
		}
		$rs = M('citys')->where($where)->select();
		$this->ajaxReturn($rs);
	}

	/**
	 * @apiName 获取默认头图
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 *   {
	 *     'id': '100000001',
	 *     'path': 'xxxx/xxxxxxxxxxx.jpg'
	 *   },
	 *   {
	 *     'id': ''10000002,
	 *     'path': 'xxxx/xxxxxxxxxxxx.jpg'
	 *   }
	 * ]
	 */
	Public function getDefaultHeadPics(){
		$ids = C('DefaultHeadPicIds');
		$rs = M('pics')->field(['id', 'path'])->where(['id' => ['IN', join(',', $ids)]])->select();
		$data = [];
		foreach($rs as $row){
			$row['path'] = thumb($row['path']);
			$data[] = $row;
		}
		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 查找口令源
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} title: 要查找的标题
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"type" : "0", //0-活动 1-商品 2-专题
	 * 	"id" : "123", //活动ID/商品ID/专题ID
	 * 	"title" : "xxxxxxxxx",
	 * 	"path" : "http://xxxxxxxxxxxxxxxxxxx"
	 * }
	 */
	Public function findOrigin(){
		$title = I('post.title');
		if(empty($title))$this->error('标题不能为空!');
		$rs = M('tips')->where(['title' => $title])->find();
		if(empty($rs))$rs = M('tips')->where(['title' => ['like', "%{$title}%"]])->find();
		$data = [];
		if(!empty($rs)){
			$path = M('pics')->where(['id' => $rs['pic_id']])->getField('path');
			$data = [
				'type' => 0,
				'id' => $rs['id'],
				'title' => $rs['title'],
				'path' => thumb($path, 1)
			];
		}
		$this->put($data);
//		$rs = M('goods')->where(['title' => ['like', "%{$title}%"]])->find();
//		if(!empty($rs)){
//			$path = M('pics')->where(['id' => $rs['pic_id']])->getField('path');
//			$data = [
//				'type' => 1,
//				'id' => $rs['id'],
//				'title' => $rs['title'],
//				'path' => thumb($path, 1)
//			];
//			$this->ajaxReturn($data);
//		}
//		$rs = M('theme')->where(['title' => ['like', "%{$title}%"]])->find();
//		if(!empty($rs)){
//			$path = M('pics')->where(['id' => $rs['pic_id']])->getField('path');
//			$data = [
//				'type' => 2,
//				'id' => $rs['id'],
//				'title' => $rs['title'],
//				'path' => thumb($path, 1)
//			];
//			$this->ajaxReturn($data);
//		}
	}

	/**
	 * @apiName 提交反馈
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} type: 反馈类型(0-活动反馈 1-BUG反馈 2-异常反馈 3-评论举报)
	 * @apiPostParam {int} type_id: 活动ID/评论ID
	 * @apiPostParam {string} content: 反馈内容(必填)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "异常提交成功",
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0,
	 *     "url": ""
	 * }
	 */
	Public function exception(){
		$data = [
			'type' => I('post.type', 2),
			'type_id' => I('post.type_id', '')
		];
		if(session('?member'))$data['member_id'] = session('member.id');
		$data['content'] = I('post.content');
		if(empty($data['content']))$this->error('需要提交反馈内容');
		M('feedback')->add($data);
		$this->success('反馈提交成功!');
	}

	/**
	 * @apiName 获取购买者分享邀请吃饭的相关信息
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 订单的ID
	 *
	 * @apiSuccessResponse
	 * {
	 * 		"nickname": "紫嫣",
	 * 		"member_pic_path": "http://yummy194.cn/uploads/20151117/564af95fb70ce.jpg",
	 * 		"tips_title": "吖咪×好色派 你见过如此好色的沙拉吗？【午餐】法式尼斯吞拿鱼沙拉 佐 魔芥酱(吖咪价39元",
	 * 		"start_time": "1456405200",
	 * 		"end_time": "1456405500",
	 * 		"city_name": "广州",
	 * 		"city_alt": "市",
	 * 		"area_name": "天河",
	 * 		"area_alt": "区",
	 * 		"simpleaddress": "员村街道",
	 * 		"address": "员村街道建中路50号",
	 * 		"longitude": "22.5553",//经度
	 * 		"latitude": "114.124",//纬度
	 * 		"inviter_id": "2556",//邀请会员id
	 * 		"check_code": "226565545",//消费码(当为no时，为已领取完)
	 * 		"tips_pic_path": "http://yummy194.cn/uploads/20151117/564af95fb70ce.jpg",
	 *		"menu": [//菜单列表
	 *		{
	 *			0:[
	 * 			{
	 *				"name":"头道"，
	 * 				"value":[
	 *				{
	 *					0:"测试",
	 *					1:"测试二"，
	 *				 }
	 *				]
	 *			}
	 *			]
	 *		}
	 *		]
	 */
	Public function getBuyerOrder(){
		$order_id = I('post.order_id');
//		$order_id = 25343;

		$rs = D('TipsOrdersWaresView')->where(['id'=>$order_id,'type'=>0,'server_status'=>0,'A.status' => 1])->find();
		if(empty($rs))$this->error('非法访问！');
		$check_code = '';
		$member_id = session('member.id');
//		$member_id = 278518;
		if(session('?member'))$check_code = M('OrderWares')->where(['type' => 0, 'order_id' => $order_id, 'inviter_id' => $member_id])->getField('check_code') ? : '';
		$count = M('OrderWares')->where(['order_id'=>$order_id,'type'=>0,'inviter_id'=>['exp','is null'],'server_status'=>0])->count();
		$data = [
			'order_id'=>$rs['id'],
			'order_wares_id'=>$rs['order_wares_id'],
			'nickname' => $rs['member_nickname'],
			'member_pic_path' => thumb($rs['member_pic_path'],2),
			'tips_title' => $rs['tips_title'],
			'start_time' => $rs['start_time'],
			'end_time' => $rs['end_time'],
			'city_name' => $rs['city_name'],
			'city_alt' => $rs['city_alt'],
			'area_name' => $rs['area_name'],
			'area_alt' => $rs['area_alt'],
			'simpleaddress' => $rs['simpleaddress'],
			'address' => $rs['address'],
			'longitude' => $rs['longitude'],
			'latitude' => $rs['latitude'],
			'check_code' => $check_code?:(!empty($count)?'':'no'),
			'tips_pic_path' => thumb($rs['tips_pic_path'],1),
		];
		//获取菜单[转换数据表tips_menu->tips_menus(2016-11-18)]
//		$menu = M('TipsMenu')->where(['tips_id'=>$rs['tips_id']])->select();
//		if(!empty($menu)){
//			$menu_data = [];
//			foreach($menu as $m_rs){
//				$food_name = explode(',',$m_rs['food_name']);
//				$menu_data[$m_rs['food_type']] = $food_name?$food_name:null;
//			}
//			$count = 0;
//			foreach($menu_data as $key=>$m_rs){
//				$arr = explode('@', $key);
//				$new_menu_data[$count]['name'] = isset($arr[1])?$arr[1]:$key;
//				$new_menu_data[$count]['value'] = $m_rs;
//				$count++;
//			}
//			$data['menu'] = $new_menu_data;
//		}else{
//			$data['menu'] = [];
//		}

		$menus = M('TipsMenus')->where(['tips_id'=>$rs['tips_id']])->select();
		if(!empty($menus)){
			foreach($menus as $m_rs){
				if(empty($m_rs['pid'])){
					if(strpos($m_rs['name'],'@') === false) {
						$name_str = $m_rs['name'];
					}else{
						$arr = explode('@', $m_rs['name']);
					}
					$new_menu_data[$m_rs['id']]['name'] = $arr?$arr[1]:$name_str;
				}else{
					foreach($new_menu_data as $key =>$m_val){
						if($key == $m_rs['pid']){
							$new_menu_data[$key]['value'][] = $m_rs['name'];
						}
					}
				}
			}
			foreach($new_menu_data as $me_rs){
				$data_menu[]=$me_rs;
			}
			$data['menu'] = $data_menu;
		}else{
			$data['menu'] = [];
		}
		$this->ajaxReturn($data);


	}

	/**
	 * @apiName 获取消费码
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 订单的ID
	 * @apiPostParam {int} order_wares_id: 订单详情的ID
	 *
	 * @apiSuccessResponse
	 * {
	 * 		"info": [
	 * 		{
	 * 				"code":2,//(0-请登录再进行领取,1-已领取完,2-领取成功,3-领取不成功，4-已领取过了,5-邀请函过期)
	 * 				"check_code":'23232212'
	 * 		}
	 * 		],
	 * 		"status": 1,
	 * 		"url": "",
	 * 	}
	 * @apiErrorResponse
	 * {
	 * 		"info": [
	 * 		{
	 * 			"code":0,//(0-请登录再进行领取,1-已领取完,2-领取成功,3-领取不成功，4-已领取过了,5-邀请函过期)
	 * 			"info":领取成功
	 * 		}
	 * 		"status": 0,
	 * 		"url": "",
	 * 	}
	 *
	 **/
	Public function getOrderCode(){
		$member_id = session('member.id');
		$order_id = I('post.order_id');
		if(!empty($member_id)){
			$count = M('OrderWares')->where(['order_id'=>$order_id,'type'=>0,'inviter_id'=>['exp','is null'],'server_status'=>0])->count();
			$order = M('OrderWares')->where(['order_id'=>$order_id])->find();
			$tips_times = M('TipsTimes')->where(['id'=>$order['tips_times_id']])->find();
			if($tips_times['end_time']<time()){
				$this->error([
					'code'=>5,
					'reason'=>'饭局已举办，邀请函过期',
				]);
			}else{
				if($count>0){
					$member_inviter_num = M('OrderWares')->where(['order_id'=>$order_id,'inviter_id'=>$member_id])->count();
					if($member_inviter_num>0){
						$this->error([
							'code'=>4,
							'reason'=>'已领取过了',
						]);
					}
					$order_code = D('TipsOrdersWaresView')->where(['id'=>$order_id,'type'=>0,'inviter_id'=>['exp','is null'],'server_status'=>0])->group('order_wares_id')->lock(true)->find();
					$order_wares_id = M('OrderWares')->where(['order_id'=>$order_id,'inviter_id'=>['exp','is null']])->getField('id');
					$c=M('OrderWares')->where(['order_id'=>$order_id,'id'=>$order_wares_id])->save(['inviter_id'=>$member_id]);
					if(empty($c)){
						$this->error([
							'code'=>3,
							'reason'=>'领取不成功',
						]);
					}
					$nickname = M('Member')->where(['id'=>$member_id])->getField('nickname');
					$host_nickname = M('Member')->where(['id'=>$order_code['host_id']])->getField('nickname');
					$start_dates =date('Y-m-d H:i',$order_code['start_time']);
					$detail_address = $order_code['city_name'].$order_code['alt'].$order_code['area_name'].$order_code['alt'].$order_code['address'];
//					$this->pushMessage($member_id, "尊敬的".$nickname."：".$order_code['member_nickname']."诚邀您参加吃".$host_nickname."的饭局，时间：".$start_dates."，地点：".$detail_address."。恭候您的到来。邀请码为：".$order_code['check_code'], 'sms', 0, 0, 0, 0);

					//2016-12-27
					$params =array(
						'nickname'=>$nickname,
						'member_nickname'=>$order_code['member_nickname'],
						'host_nickname'=>$host_nickname,
						'start_dates'=>$start_dates,
						'detail_address'=>$detail_address,
						'check_code'=>$order_code['check_code'],

					);
					$this->push_Message($member_id,$params ,'SMS_48580145', 'sms', null,0, 0, 0, 0);
					$this->success([
						'code'=>2,
						'check_code'=>$order_code['check_code']
					]);
				}else{
					$this->error([
						'code'=>1,
						'reason'=>'已领取完',
					]);
				}

			}
		}else{
			$this->error([
				'code'=>0,
				'reason'=>'请登录再进行领取',
			]);
		}

	}

	/**
	 * @apiName 生成验证码
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 */
	Public function captcha(){
		$config = array(
			'fontSize'	=>	30,	// 验证码字体大小
			'length'	  =>	5,	 // 验证码位数
			'fontttf '	  =>  '4.ttf',	 // 验证码字体
		);

		$Verify = new \Think\Verify($config);
		$Verify->entry();
	}

	/**
	 * @apiName 开启提醒
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} type: 类型(0-活动，1-商品 2-众筹)
	 * @apiPostParam {int} type_id: 活动、商品、众筹的ID
	 * @apiPostParam {int} d: 是否取消(0-默认不取消，1-取消)
	 *
	 *
	 * @apiSuccessResponse
	 *{
	 *"status": 1,
	 *"info": "开启成功"
	 *}
	 *
	 * @apiErrorResponse
	 *{
	 *"status": 0,
	 *"info": "开启失败"
	 *}
	 *
	 */
	Public function OpenReminder(){
		$type = I('post.type',0);
		$type_id = I('post.type_id');
		$d = I('post.d');
		$member_id = session('member.id');

		if(empty($member_id)){
			$this->error('登录后才能启动开启提醒');
		}
		//获取是否提醒功能
		$msgRs = M('Message')->join('__MEMBER_MESSAGE__ AS A ON A.message_id = __MESSAGE__.id ')->where(['A.member_id'=>$member_id,'type'=>7,'code_type'=>'SMS_48040327','type_id'=>$type_id])->find();
		$re_count = count($msgRs);
		if($re_count>0 && !$d){
			$this->error('已开启提醒');
		}

		if ($d == 1) {
			// 取消提醒
			// M('MemberMessage')->where(['member_id' => $member_id, 'type_id' => $type_id, '']);
			
			if (!empty($msgRs)) {
				M('MemberMessage')->where(['member_id' => $member_id, 'message_id' => $msgRs['message_id']])->delete();
				M('Message')->where(['id' => $msgRs['message_id']])->delete();
			}

			$this->success('取消成功');

			return;
		}

		if($type == 0){
			$rs =M('Tips')->where(['type_id'=>$type_id])->find();
		}elseif($type == 1){
			$rs =M('Goods')->where(['type_id'=>$type_id])->find();
		}elseif($type = 2){
			$rs = D('RaiseView')->field('nickname,title,start_time,end_time')->where(['A.id'=>$type_id])->find();
			if(!empty($rs)) {
				$params = array(
					'daren' => $rs['nickname'],
					'project_name' => '众筹',
					'title' => $rs['title'],
					'start_time' => date('m月d日H时', $rs['start_time']),
					'wx' => 'yami194',
				);
				$this->push_Message($member_id, $params, 'SMS_48040327', 'wx|sms|ios', null, 7, $type_id, $rs['start_time'], 0);

				$this->success('开启成功');
			}else{
				$this->error('开启失败');
			}
		}

	}

	/**
	 * @apiName 分享活动、商品、众筹
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} type: 类型(0-活动，1-商品 2-众筹)
	 * @apiPostParam {int} type_id: 活动、商品、众筹的ID
	 * @apiPostParam {int} code: 邀请码
	 *
	 *
	 * @apiSuccessResponse
	 *{
	 *	"list": {
	 *		"id": "32",
	 *		"title": "标题"
	 *	},
	 *	"member": {
	 *		"nickname": "紫嫣",
	 *		"path": "http://img.m.yami.ren/20170313/NhYTUwODczZGQzOTcwMTM2NzEyYWIw.jpg"
	 *	}
	 *}
	 *
	 * @apiErrorResponse
	 *{
	 *"status": 0,
	 *"info": "失败"
	 *}
	 *
	 */
	Public function getEnjoy(){
		$type = I('post.type',0);
		$type_id = I('post.type_id');
		$code = I('post.code');
		$member = D('MemberInfoView')->field('nickname,path')->where(['invitecode'=>$code,'A.status'=>1])->find();
		if(empty($member))$this->error('不存在该用户');
		if(!empty($member['path']))$member['path'] = thumb($member['path']);
		if($type==0){
			$rs = M('Tips')->field('id,title')->where(['id'=>$type_id])->find();
		}elseif($type==1){
			$rs = M('Goods')->field('id,title')->where(['id'=>$type_id])->find();
		}elseif($type==2){
			$rs = D('RaiseView')->field('id,title,introduction,path')->where(['id'=>$type_id])->find();
			if(!empty($member['path']))$rs['path'] = thumb($rs['path']);
		}
		$data = [
			'list'=>$rs,
			'member'=>$member,
		];
		$this->put($data);
	}

    /**
     * @apiName 分享成功回调
     *
     * @apiPostParam {int} type: 分享商品类别，0--活动 1--商品 2--文章 3--众筹
     * @apiPostParam {int} item_id: 类别的id
     * @apiPostParam {int} target: 分享的目标，0--朋友圈 1--微信好友 2--微信群
     * @apiPostParam {int} platform: 平台， 0--吖咪微信 1--我有饭微信
     */
	public function shareSuccess() {
        if(!session('?member')) {
            $this->error('未登陆');
        }

        $member_id = session('member.id');
        $type = I('post.type');
        $item_id = I('post.item_id');
        $target = I('post.target');
        $platform = I('post.platform');

        $data['member_id'] = $member_id;
        $data['type'] = $type;
        $data['item_id'] = $item_id;
        $data['target'] = $target;
        $data['platform'] = $platform;

        $old = M('MemberShare')->where(['member_id' => $member_id, 'type' => $type, 'item_id' => $item_id, 'target' => $target, 'platform' => $platform])->find();

        if (empty($old)) {
            $id = M('MemberShare')->add($data);
            $this->ajaxReturn(array('status' => 1, 'info' => '分享成功', 'msg'=> $id));
        } else {
            $this->ajaxReturn(array('status' => 1, 'info' => '分享成功', 'msg'=> $old['id']));
        }

    }

    public function slotShare() {

    }
}