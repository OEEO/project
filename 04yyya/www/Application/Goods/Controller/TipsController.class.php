<?php

namespace Goods\Controller;
use Goods\Common\MainController;

//@className 活动类商品接口
class TipsController extends MainController {

	/**
	 * @apiName 获取筛选活动列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} page: 分页编号，默认1
	 * @apiPostParam {int} tag_id: 标签ID（ID=>76：预约制饭局）
	 * @apiPostParam {int} week: 星期编号（0-6）
	 * @apiPostParam {int} date: 日期（2015-12-01）
	 * @apiPostParam {int} city_id: 城市ID
	 * @apiPostParam {string} price: 价格区间，必须含有“-”符号，例如：100-200、600-
	 * @apiPostParam {int} theme_id: 专题ID
	 * @apiPostParam {int} member_id: 活动达人的id
	 * @apiPostParam {int} category: 分类 ，（1:饭局，2:课程，3:沙龙, -1:定制 ，-4：社交饭局 -5：大咖饭局）
	 * @apiPostParam {int} is_overdue: 是否包含过期的（默认0-否）
	 *
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 		"id": "3010",
	 * 		"nickname": "Jack",
	 *      "customers": 0, //赏味
	 * 		"member_id": "144",
	 * 		"headpic": "http://yummy194.cn/uploads/member/jackyami.com/5590e622dba89.png",
	 * 		"mainpic": "http://yummy194.cn/uploads/20160229/56d3c040d9b94.jpg",
	 * 		"catname": "课程",
	 *      "tagname": "西餐", //标签
	 * 		"title": "吖咪烘焙课|Wilber's 法式奶油海鲜酥盒",
	 * 		"price": "220.00",
	 * 		"start_time": "1459058400",
	 * 		"end_time": "1459069200",
     *      "sellout": 0,//0-未售罄，1-已售罄
	 * 		"address": "广州市越秀区美华北路16号3楼"
	 * 	},
	 * 	{
	 * 		"id": "2942",
	 * 		"nickname": "Donna",
	 *      "customers": 0, //赏味
	 * 		"member_id": "10213",
	 * 		"headpic": "http://yummy194.cn/uploads/member/13500023669/569cb2c883353.JPG",
	 * 		"mainpic": "http://yummy194.cn/uploads/20160118/569cc0ddd0798.JPG",
	 * 		"catname": "课程",
	 *      "tagname": "西餐", //标签
	 * 		"title": "吖咪烘焙课|带上孩子，在社交厨房来一场与爱相关的温",
	 * 		"price": "1500.00",
	 * 		"start_time": "1483077600",
	 * 		"end_time": "1483084800",
     *      "sellout": 0,//0-未售罄，1-已售罄
	 * 		"address": "广州市新港中路丽影广场c区会展时代25楼"
	 * 	}
	 * ]
	 */
	public function getlist($sort = []){
		$page = I('get.page', 1);
		$tag_id = I('post.tag_id', null);
		$week = I('post.week', null);
		$date = I('post.date', null);
		$city_id = I('post.city_id', null);
		$price = I('post.price', null);
		$count = 5; //每页显示5条
		$theme_id = I('post.theme_id');
		$member_id = I('post.member_id');
		$category = I('post.category',null);
		$is_overdue = I('post.is_overdue', 0);
		$filter = I('post.filter', array());

		$where = [];
		$where[] = 'A.status=1';
		$where[] = 'A.is_pass=1';
		$where[] = 'is_public=1';
		if(!empty($tag_id)){
			$tags = M('TipsTag')->field('tips_id')->where(['tag_id' => $tag_id])->buildSql();
			$where[] = "A.id in " . $tags;
		}
		if($week !== null){
			$where[] = "FROM_UNIXTIME(C.`start_time`,'%w') = '{$week}'";
		}
		if(!empty($date)){
			$datetime1 = strtotime($date . ' 00:00:00');
			$datetime2 = strtotime($date . ' 23:59:59');
			$where[] = "F.`start_time` <= '{$datetime2}' and F.`end_time` >= '{$datetime1}'";
		}

//		if(!empty($city_id)){
//            //如果有子区域则添加进条件
//            $sub_area = M('citys')->where(['pid'=>$city_id])->getField('id',true);
//
//            $area = $sub_area;
//            $area[] = $city_id;
////            $where[] = "M.city_id in (". join(',',$area) .")";
//
//		}elseif(empty($member_id)){
////			$citys = M('citys')->where(['pid' => session('city_id')])->getField('id', true);
////			$citys[] = session('city_id');
////			$where[] = "M.city_id in (". join(',', $citys) .")";
//		}

		if(!empty($sort)){
			$ids = [];
			foreach($sort as $row){
				$ids[] = $row['id'];
			}
			$where[] = "A.id in (" . join(',', $ids) . ")";
		}
		if(!empty($price) && strpos($price, '-') !== false){
			$arr = explode('-', $price);
			if(empty($arr[0]))$arr[0] = 0;
			if(empty($arr[1]))$arr[1] = 9999999;
			$where[] = "A.price > {$arr[0]} and A.price < {$arr[1]}";
		}
		if(!empty($theme_id)){
			$theme = M('theme_element')->field('type_id')->where(['theme_id' => $theme_id , 'type' => '0'])->buildSql();
			$where[] = "A.id in " . $theme;
		}
		if(!empty($member_id)){
			$where[] = "A.member_id=" . $member_id;
		}
		if(!empty($category)){
			if($category > 0) {
				$where[] = 'C.id=' . $category;
			}elseif($category == -1){
				$where[] = 'A.buy_status=2';
			}elseif($category == -2){
				$where[] = 'A.buy_status<>2';
			}elseif($category == -4){
				// 社交饭局
				$tags = M('TipsTag')->field('tips_id')->where(['tag_id' => ['not in', [76]]])->buildSql();
				$where[] = "A.id in " . $tags;
			}elseif($category == -5){
				$tags = M('TipsTag')->field('tips_id')->where(['tag_id' => 65])->buildSql();
				$where[] = "A.id in " . $tags;
			}
		}

		//筛选活动开始前的活动列表
		$having = '';
		if(!$is_overdue || empty($member_id)) {
			$order[] = 'A.is_top desc';
			if($category != -5){
				$having = "max(F.end_time) > ".time();;
				//$where[] = "F.end_time > ".time();
				$order[] = 'F.start_time asc';
			}else{
				$order[] = 'F.start_time desc';
			}
		}else{
			$having = "max(F.end_time) <= ".time();
			$order[] = 'F.start_time desc';
		}

		if(in_array($this->channel, [7,8,9])){
			$where[] = 'C.id=1';
			$where[] = 'A.buy_status<>2';
		}

		$where = join(' and ', $where);

		$data = D('TipsView')->where($where)->group('A.id')->having($having)->page($page, $count)->order(join(',', $order))->select();
		if(!empty($data)){
			//获取所有tips的id
			$ids = $member_ids = [];
			foreach($data as $row){
				$ids[] = $row['id'];
				$member_ids[] = $row['member_id'];
			}
			/*查询出所有tips的标签*/
			$tipstag=D('TipstagView')->where('A.tips_id in (' . join(',', $ids) . ')')->select();

			//查询出所有的订单活动
			$rs = D('OrderWaresView')->field(['ware_id', 'count(ware_id) as num'])->where(array('type' => 0, 'ware_id' => array('IN', join(',', $ids)), 'act_status' => array('LT', 6)))->group('ware_id')->select();

			//查询出所有关注
			if(session('?member'))$follows = M('MemberFollow')->where(['member_id' => session('member.id'), 'follow_id' => ['IN', join(',', $member_ids)]])->getField('follow_id', true);
			if(empty($follows))$follows = [];

            //找出分期库存
            $times = M('TipsTimes')->where(['tips_id'=>['IN',join(',', $ids)],'end_time'=>['GT', time()]])->Field(['tips_id','stock','start_time','end_time','stop_buy_time'])->order('start_time')->select();
            //$this->ajaxReturn($stock);

            //找出已收藏的活动
            if(session('?member')){
                $collect_tips = M('MemberCollect')->where(['member_id'=>session('member.id') , 'type'=>0])->getField('type_id',true);
            }
            if(empty($collect_tips))$collect_tips = [];

			foreach($data as $k => $r){
                //判断库存是否售罄
                $sellout = 1;
                $useful_phase = 0;
                foreach($times as $ke=>$re){
                    if($re['tips_id'] == $r['id']){
                        //$data[$k]['sellout'] = ($re['stock']==0) ?  1 : 0 ;
                        if($re['stock']>0)$sellout = 0;
                        //可用的最近一期时间也顺便赋值下
						if($re['stock'] > 0 && $re['stop_buy_time'] > time() && !$useful_phase){
							$data[$k]['start_time'] = $re['start_time'];
							$data[$k]['end_time'] = $re['end_time'];
							$useful_phase = 1;
						}
                        if(!$useful_phase){
                            $data[$k]['start_time'] = $re['start_time'];
                            $data[$k]['end_time'] = $re['end_time'];
                        }
                    }
                }

				//把对应的标签加入到相应的tips中
				$tagnames= [];
				$data[$k]['p_tags_id']='';//私房菜标签ID
				foreach($tipstag as $tt){
					if($tt['tips_id']==$r['id']) {
						$tagnames[] = $tt['name'];
						if($tt['tag_id'] == 76){
							$data[$k]['p_tags_id']=76;
							$sellout = 0;
						}
					}
				}
				$data[$k]['sellout'] = $sellout;
				$data[$k]['tags']=$tagnames;

				//运用缩略图
				$data[$k]['path'] = thumb($r['path'], 1);
				$data[$k]['headpic'] = thumb($r['headpic'], 2);

				//把剩余份数加入到相应的tips中
				$amount = $r['max_num'];
				foreach($rs as $row){
					if($row['ware_id']==$r['id'])$amount = $r['max_num'] - $row['num'];
				}
				$data[$k]['amount'] = $amount < 0 ? 0 : $amount;

				//送米
//				$wealth = M('member_wealth')->join('__MEMBER_WEALTH_LOG__ ON __MEMBER_WEALTH_LOG__.member_wealth_id=__MEMBER_WEALTH__.id')->field('ym_member_wealth_log.quantity')->where('ym_member_wealth_log.type=\'huoqu\' and ym_member_wealth.member_id=' . $r['member_id'] . ' and ym_member_wealth.wealth=2')->select();
//				if (!empty($wealth)) {
//					foreach ($wealth as $row1) {
//						$data[$k]['wealth'] += abs($row1['quantity']);
//					}
//				} else {
//					$data[$k]['wealth'] = 0;
//				}

                //获取粉丝数量
                $data[$k]['follow_num'] = M('MemberFollow')->where(['follow_id'=>$r['member_id']])->count();

				//赏味
				/*$customer = M('tips')->join('__TIPS_TIMES__ ON __TIPS__.id=__TIPS_TIMES__.tips_id')->field('ym_tips_times.stock,ym_tips.restrict_num')->where('ym_tips.member_id=' . $r['member_id'])->select();
                if (!empty($customer)) {
                    $all = 0;
                    $left = 0;
                    foreach ($customer as $key => $row2) {
                        $all += $row2['restrict_num'];
                        $left += $row2['stock'];
                    }
                    $data[$k]['customers'] = ($all - $left);
                } else {
                    $data[$k]['customers'] = 0;
                }*/
				$data[$k]['customers'] = D('ShangweiView')->where(['member_id' => $r['member_id'], 'act_status' => ['IN', '1,2,3,4'], 'status' => 1])->getField('count');
				if(empty($data[$k]['customers']))$data[$k]['customers'] = '0';
			}
		}

		$_data = [];
        $date_array = [1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'日'];

		foreach($data as $row){

			if(date('md',$row['start_time'])==date('md',$row['end_time'])){
				//开始和结束时间在同一天
                $row['date'] = date('m月d号',$row['start_time']) . ' 周' . $date_array[date('N',$row['start_time'])] . ' ' .date('H:i',$row['start_time']).' - '.date('H:i',$row['end_time']);
			}else{
				//开始和结束时间不在同一天
                $row['date'] = date('m月d号',$row['start_time']) . ' 周' . $date_array[date('N',$row['start_time'])] . ' ' .date('H:i',$row['start_time']).' - '.date('m月d号',$row['end_time']) . ' 周' . $date_array[date('N',$row['end_time'])] . ' ' .date('H:i',$row['end_time']);
			}
			
			if (empty(C('CITYS')[$row['tips_city_id']])) {
				$cityName = '中国';
			} else {
				$cityName = C('CITYS')[$row['tips_city_id']];
			}

			$_data[] = [
				'id' => $row['id'],
				'nickname' => $row['nickname'],
				'customers' => $row['customers'],
                'follow_num' => $row['follow_num']?:0,
				'member_id' => $row['member_id'],
				'headpic' => $row['headpic'],
				'mainpic' => $row['path'],
				'catname' => $row['catname'],
				'tagname' => $row['tags'],
				'title' => $row['title'],
				'price' => (string)(float)$row['price'],
				'start_time' => $row['start_time'],
				'end_time' => $row['end_time'],
                'sellout' => $row['sellout'],
                'p_tags_id' => $row['p_tags_id'],
                'buy_status' => $row['buy_status'],
				'date' => $row['date'],
				'simpleaddress' => $row['simpleaddress']?:'',
				'address' => $row['address']?:'',
				'is_follow' => (in_array($row['member_id'], $follows) ? 1 : 0),
                'is_collect' => (in_array($row['id'],$collect_tips) ? 1 : 0),
				'min_num' => $row['min_num'],
				'restrict_num' => $row['max_num'],
                'city' => $cityName
			];
		}

		if(!empty($sort)) {
			return $_data;
		}

		$this->ajaxReturn($_data);
	}

	/**
	 * @apiName 获取活动详情数据
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} tips_id: 活动ID
	 *
	 * @apiSuccessResponse
	 *{
	 *	"mainpic": "http://img.m.yami.ren/20161229/e64be1f25439ec97e7295051e05dd9c2b5cff5ba.jpg",
	 *	"pics_group": [
	 *		"http://img.m.yami.ren/20161229/e64be1f25439ec97e7295051e05dd9c2b5cff5ba.jpg"
	 *	],
	 *	"title": "测试短信达人和用户",
	 *	"discount": "0.00",
	 *	"catname": "饭局",
	 *	"tags": [
	 *		"中餐"
	 *	],
	 *	"edge": [
	 *		"1",
	 *		"3",
	 *		"4"
	 *	],
	 *	"time": {
	 *		"id": "16465",
	 *		"tips_id": "7756",
	 *		"phase": "9",
	 *		"start_time": "1490932800",
	 *		"end_time": "1490940000",
	 *		"min_num": "5",
	 *		"max_num": "99",
	 *		"stock": "88",
	 *		"start_buy_time": "0",
	 *		"stop_buy_time": "1490889600",
	 *		"release_time": "1489735066",
	 *		"under_time": "1489718115",
	 *		"limit_num": "0",
	 *		"is_finish": "0",
	 *		"datetime": "2017-03-28 09:44:04",
	 *		"isPiece": 0,
	 *		"act_stop_buy_time": "1490889600",
	 *		"member_info": [
	 *			{
	 *				"member_id": "278518",
	 *				"count": "7",
	 *				"tips_times_id": "16465",
	 *				"nickname": "紫嫣",
	 *				"path": "http://img.m.yami.ren/20170313/NhYTUwODczZGQzOTcwMTM2NzEyYWIw_320x320.jpg"
	 *			},
	 *			{
	 *				"member_id": "242629",
	 *				"count": "2",
	 *				"tips_times_id": "16465",
	 *				"nickname": "Ada",
	 *				"path": "http://img.m.yami.ren/20161208/M5YTBlOTlmMGVmYThhOTkzMWE0MDAw_320x320.jpg"
	 *			},
	 *		],
	 *	"count": 10
	 *	},
	 *	"times": [
	 *		{
	 *			"id": "16413",
	 *			"tips_id": "7756",
	 *			"phase": "1",
	 *			"start_time": "1481693400",
	 *			"end_time": "1483070040",
	 *			"min_num": "1",
	 *			"max_num": "3",
	 *			"stock": "3",
	 *			"start_buy_time": "0",
	 *			"stop_buy_time": "1481018040",
	 *			"release_time": "1489455970",
	 *			"under_time": null,
	 *			"limit_num": "0",
	 *			"is_finish": "2",
	 *			"datetime": "2017-03-14 09:46:31",
	 *			"piece": [
	 *				{
	 *					"id": "4",//
	 *					"phase": "1",
	 *					"count": "3",
	 *					"limit_num": "2"
	 *					"limit_time": "2"
	 *				},
	 *				{
	 *					"id": "6",
	 *					"phase": "2",
	 *					"count": "30",
	 *					"limit_num": "2"
	 *					"limit_time": "2"
	 *				},
	 *				{
	 *					"id": "7",
	 *					"phase": "3",
	 *					"count": "13",
	 *					"limit_num": "3"
	 *					"limit_time": "3"
	 *				}
	 *				],
	 *			"act_stop_buy_time": "1481018040",
	 *			"member_info": [],
	 *			"count": 0
	 *		},
	 *		{
	 *			"id": "16451",
	 *			"tips_id": "7756",
	 *			"phase": "2",
	 *			"start_time": "1484879880",
	 *			"end_time": "1484966280",
	 *			"min_num": "5",
	 *			"max_num": "8",
	 *			"stock": "8",
	 *			"start_buy_time": "0",
	 *			"stop_buy_time": "1484793480",
	 *			"release_time": "1489455970",
	 *			"under_time": null,
	 *			"limit_num": "2",
	 *			"is_finish": "2",
	 *			"datetime": "2017-03-14 09:46:32",
	 *			"Piece": []
	 *			"act_stop_buy_time": "1484793480",
	 *			"member_info": [],
	 *			"count": 0
	 *		},
	 *	],
	 *	"count": 10
	 *	}
	 *],
	 *"address": "广东省广州市华翠街",
	 *"simpleaddress": "华翠街",
	 *"daRen_id": "278518",
	 *"headpic": "http://img.m.yami.ren/20170313/NhYTUwODczZGQzOTcwMTM2NzEyYWIw_320x320.jpg",
	 *"cover_path": "http://img.m.yami.ren/20161017/e91bd4d567d00b19675398fc8b8e73121c6829f3_640x480.jpg",
	 *"nickname": "紫嫣",
	 *"tips": "7",
	 *"shangwei": 21,
	 *"follow_num": "3",
	 *"introduce": "测试222sfddsafdsvjklnkoljinjinj",
	 *"member_introduce": "测试222sfddsafdsvjklnkoljinjinj",
	 *"menu": [
	 *	{
	 *		"name": "活动流程",
	 *		"value": [
	 *			""
	 *		]
	 *	},
	 *	{
	 *		"name": "主食",
	 *		"value": [
	 *			""
	 *		]
	 *	},
	 *	{
	 *		"name": "点心",
	 *		"value": [
	 *			""
	 *		]
	 *	},
	 *	{
	 *		"name": "饮品",
	 *		"value": [
	 *			""
	 *		]
	 *	},
	 *	{
	 *		"name": "juikoik",
	 *		"value": [
	 *			""
	 *		]
	 *	},
	 *	{
	 *		"name": "其他",
	 *		"value": [
	 *			""
	 *		]
	 *	},
	 *	{
	 *		"name": "Tips",
	 *		"value": [
	 *			""
	 *		]
	 *	}
	 *],
	 *"menu_pics_group": [
	 *	"http://img.m.yami.ren/20161229/a80e620baef9e5705d346ae791a393dde87e31f7.jpg"
	 *],
	 *"environment_pics_group_id": [
	 *	"http://img.m.yami.ren/20161009/31f3ff74d1b7a4eb7856b8f8ce1a3174abb2473f_640x352.jpg"
	 *],
	 *"comment": [
	 *	{
	 *		"id": "2274",
	 *		"stars": "5",
	 *		"content": "简单测试",
	 *		"pics_group_id": null,
	 *		"datetime": "2017-03-20 16:37:47",
	 *		"status": "1",
	 *		"pid": null,
	 *		"nickname": "Ada",
	 *		"head_path": "http://img.m.yami.ren/20161208/M5YTBlOTlmMGVmYThhOTkzMWE0MDAw_320x320.jpg",
	 *		"tips_member_id": "278518",
	 *		"goods_member_id": null,
	 *		"pics": [],
	 *		"is_report": "0"
	 *	},
	 *	{
	 *		"id": "2272",
	 *		"stars": "5",
	 *		"content": "最后一次",
	 *		"pics_group_id": null,
	 *		"datetime": "2017-03-15 17:25:21",
	 *		"status": "1",
	 *		"pid": null,
	 *		"nickname": "紫嫣",
	 *		"head_path": "http://img.m.yami.ren/20170313/NhYTUwODczZGQzOTcwMTM2NzEyYWIw_320x320.jpg",
	 *		"tips_member_id": "278518",
	 *		"goods_member_id": null,
	 *		"pics": [],
	 *		"is_report": "0"
	 *	}
	 *],
	 *"start_buy_time": "0",
	 *"stop_buy_time": 675360,
	 *"latitude": "23.1262",
	 *"longitude": "113.372",
	 *"min_num": "1",
	 *"restrict_num": "3",
	 *"price": 0.01,
	 *"isfollow": 0,
	 *"isCollect": 0,
	 *"notice": [
	 *	"成功报名后，请关注官方客服微信号吖咪酱：yami194。",
	 *	"已付费的活动名额，不接受退款，不改期，可自行转让名额。",
	 *	"无特别说明的情况下，活动费用为单人单次费用，如携带他人参加需购买相应份数。",
	 *	"本活动不接受临时加入和现场付款，参加请在吖咪平台上购买。",
	 *	"如活动未达到最低成局人数，吖咪会进行退款。",
	 *	"如需开具发票，请咨询活动方。",
	 *	"截止报名后，如未达到成局人数，平台会安排退款"
	 *],
	 *"isFree": 0,
	 *"defaultPics": [
	 *	{
	 *		"id": "1000000001",
	 *		"path": "http://img.m.yami.ren/20160603/6ac0f489e917365a7d3c89eb9483f2a4a32625ec_320x320.jpg"
	 *	},
	 *	{
	 *		"id": "1000000002",
	 *		"path": "http://img.m.yami.ren/20160603/b3c5eb0c78ef385140d33965f295ba428a277428_320x320.jpg"
	 *	},
	 *	{
	 *		"id": "1000000003",
	 *		"path": "http://img.m.yami.ren/20160603/afb88c84153f6f38c6b926f34c0684a57d0b5e8d_320x320.jpg"
	 *	},
	 *	{
	 *		"id": "1000000004",
	 *		"path": "http://img.m.yami.ren/20160603/8d3aa82e4760ce7cfaa9d017173a0d7af6332bbf_320x320.jpg"
	 *	}
	 *]
	 *}
	 */
	public function getDetail(){
		$tips_id = I('post.tips_id');
//		$tips_id = 3192;
		//$app = I('post.app',0);
		if(empty($tips_id))$this->error('非法访问！');
		//查询活动数据
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs))$this->error('活动不存在!');

		//查询达人数据
		$m = new \Daren\Model\DarenViewModel;
		$daren = $m->where(['member_id' => $rs['member_id']])->find();

		$follow = $isCollect = 0;
		if(session('?member')){
			$member_id = session('member.id');
//			$member_id = 34532;
			//查询是否已关注此达人
			$memberFollow = M('MemberFollow')->where(['member_id' => $member_id, 'follow_id' => $rs['member_id']])->find();
			if(!empty($memberFollow))$follow = 1;

			//查询是否已收藏活动
			$collect = M('MemberCollect')->where(['member_id' => $member_id, 'type' => 0, 'type_id' => $tips_id])->find();
			if(!empty($collect))$isCollect = 1;
		}

		//查询评分
		//$stars = M('MemberComment')->field('count(stars) as count, sum(stars) as sum')->where(['type' => 0, 'type_id' => $tips_id])->find();

		//达人简介格式化
		$daren['introduce'] = preg_replace('/\&\w+?;/', '', strip_tags($daren['member_introduce']));
        //达人背景
        //$cover = M('Pics')->where(['id'=>$daren['cover_pic_id']])->getField('path');

		//地址
		$city_rs = D('CityView')->where(['area_id' => $rs['city_id']])->find();
		$address = $city_rs['city_name'] .$city_rs['city_alt']. $city_rs['area_name'] .$city_rs['area_alt']. $rs['simpleaddress'];

		$edges = explode(',', $rs['edges']);
		$data = [
			'edge_1' => isset($edges[0])?$edges[0]:'',
			'edge_2' => isset($edges[1])?$edges[1]:'',
			'edge_3' => isset($edges[2])?$edges[2]:'',
			'daRen_id' => $rs['member_id'],
			'telephone' => $rs['telephone'],
			'title' => $rs['title'],
			'title_sub' => $rs['intro'],
            'catname' => $rs['catname'],
            'discount' => $rs['discount'],
			'environment_pics_group_id' => '',
			'menu_pics_group_id' => '',
			'pics_group_id' => '',
			'price' => $rs['price'],
            'cover_path' => thumb($daren['cover_path'], 7),
			'mainpic' => thumb($rs['path'], 1),
			'headpic' => thumb($daren['path'], 2),
			'introduce' => $daren['introduce'],
			'member_introduce' => $daren['member_introduce'],
			'nickname' => $daren['nickname'],
			'min_num' => $rs['min_num'],
			'restrict_num' => $rs['max_num'],
			'longitude' => $rs['longitude'],
			'latitude' => $rs['latitude'],
			'start_buy_time' => $rs['start_buy_time'],
			'stop_buy_time' => $rs['start_time'] - $rs['stop_buy_time'],
			'address' => $address,
			'simpleaddress' => $rs['simpleaddress'],
			'content' => $rs['content']
		];

		//根据邀请码判断开始购买时间
		$allow_buy = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['invite_member_id' => C('DefaultInviteMember'), 'type' => 0, 'ware_id' => $tips_id, 'status' => 1, 'act_status' => ['IN', '0,1,2,3,4,5']])->find();
		if(session('?invite') && session('invite.member_id') == C('DefaultInviteMember') && empty($allow_buy)){
			$data['start_buy_time'] = 0;
		}

		//特色内容
		$data['context_title'] = '';
		$data['context_text'] = '';
		if(!empty($rs['content']) && strpos($rs['content'], '%#%$%')){
			$arr = explode('%#%$%', $rs['content']);
			$data['context_title'] = $arr[0];
			$data['context_text'] = $arr[1];
		}

		//获取菜单[转换数据表tips_menu->tips_menus(2016-11-18)]
		$menus = M('TipsMenus')->where(['tips_id'=>$tips_id])->select();
		if(!empty($menus)){
            foreach($menus as $m_rs){
				if(empty($m_rs['pid'])){
					$arr =explode('@', $m_rs['name']);
					if(!empty($arr[1])){
						$new_menu_data[$m_rs['id']]['name'] = $arr[1];
					}else{
						$new_menu_data[$m_rs['id']]['name'] = $m_rs['name'];
					}
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

		//tips图组
		$data['pics_group'] = M('pics')->where(['group_id'=>$rs['pics_group_id']])->getField('path',true);
        if(!empty($data['pics_group'])){
            foreach($data['pics_group'] as $key => $row){
                $data['pics_group'][$key] = thumb($row, 1);
            }
        }else{
            $data['pics_group'] = [];
        }

		//菜单图组
		$group = M('pics')->where(['group_id'=>$rs['menu_pics_group_id']])->getField('path',true);
		$menu_pics_group = [];
		foreach($group as $rr){
			$menu_pics_group[] = thumb($rr, 1);
		}
        if(!empty($menu_pics_group)){
            $data['menu_pics_group'] = $menu_pics_group;
        }else{
            $data['menu_pics_group'] = [];
        }


		//环境图组
		$data['environment_pics_group_id'] = M('pics')->where(['group_id'=>$rs['environment_pics_group_id']])->getField('path',true);
        if(!empty($data['environment_pics_group_id'])){
            foreach($data['environment_pics_group_id'] as $kk=>$rr){
                $data['environment_pics_group_id'][$kk] = thumb($rr);
            }
        }else{
            $data['environment_pics_group_id'] = [];
        }

		//获取活动标签
		$data['tags'] = M('TipsTag')->join('__TAG__ a on tag_id=a.id')->where(['tips_id' => $tips_id])->getField('name', true);
        $data['tags'] = empty($data['tags'])? [] :$data['tags'];

		//获取私房菜标签
		$p_tags_id = M('TipsTag')->where(['tips_id' => $tips_id,'tag_id'=>76])->getField('tag_id');
		$data['p_tags_id']=!empty($p_tags_id)?$p_tags_id:'';

		//获取时间节点
		$data['times'] = M('TipsTimes')->where(['tips_id' => $tips_id])->order('phase')->select();
        foreach($data['times'] as $time_key => $time_row){

			$data['times'][$time_key]['tips_piece'] = [];
			//是否存在拼团
			$piece = M('Piece')->field('id,price,count,limit_num,limit_time')->where(['type_times_id'=>$time_row['id'],'type'=>0,'type_id' => $tips_id,'status'=>1])->order('price asc')->select();
			if(!empty($piece)){
				foreach($piece as $k=>$val){
					if($val['count']<=$time_row['stock'] && $time_row['stock']>0 ){
						$data['times'][$time_key]['tips_piece'][$k]['can_buy'] = 1;
					}else{
						$data['times'][$time_key]['tips_piece'][$k]['can_buy'] = 0;
					}
					$data['times'][$time_key]['tips_piece'][$k]['id'] = $val['id'];
					$data['times'][$time_key]['tips_piece'][$k]['price'] = $val['price'];

				}
			}
            $data['times'][$time_key]['act_stop_buy_time'] = (string)$time_row['stop_buy_time'];
        }

		/*$ordertips = D('OrderWaresView')->field(['count', 'tips_times_id'])->where(array('type' => 0, 'ware_id' => $tips_id, 'act_status' => array('IN', '0,1,2,3,4,5'), 'status' => 1))->group('tips_times_id')->select();

        foreach($data['times'] as $k => $r){
            $data['times'][$k]['count'] = 0;
            foreach($ordertips as $v){
                if($r['id'] == $v['tips_times_id']){
                    $data['times'][$k]['count'] = $v['count'];
                    if($data['restrict_num'] > 0){
                        $data['times'][$k]['surplus'] = $data['restrict_num'] - $v['count'];
                    }
                }
            }
        }*/

		//获取2条评论
		$data['comment'] = D('CommentView')->where(['type' => 0, 'tips_member_id' => $rs['member_id'], 'status' => 1, 'pid' => ['EXP', 'IS NULL']])->order('id desc')->limit(2)->select();
		if(!empty($data['comment'])){
			$ids = $pic_ids = [];
			foreach($data['comment'] as $k=>$row){
				$ids[] = $row['id'];
				if(!empty($row['pics_group_id'])){
					$pic_ids[] = $row['pics_group_id'];
				}
				$data['comment'][$k]['head_path'] = thumb($row['head_path'],2);
				unset($data['comment'][$k]['type']);
				unset($data['comment'][$k]['type_id']);
				//unset($data['comment'][$k]['datetime']);
			}

			//评论中的举报
			$reports = M('feedback')->where(['type' => 3, 'type_id' => ['IN', join(',', $ids)], 'member_id' => session('member.id')])->getField('type_id', true);
			//评论图片
			$pics = M('pics')->where(['group_id' => ['IN', join(',', $pic_ids)]])->select();
			foreach($data['comment'] as $key => $row){
				$_pics = [];
				foreach($pics as $pic){
					if($row['pics_group_id'] == $pic['group_id']){
						$_pics[] = thumb($pic['path'],5);
					}
				}
				$data['comment'][$key]['stars'] = $row['stars']?:0;
				$data['comment'][$key]['pics'] = $_pics;

				//评论是否被举报
				$data['comment'][$key]['is_report'] = '0';
				if(session('?member')){
					if(in_array($row['id'], $reports))$data['comment'][$key]['is_report'] = '1';
				}
			}
		}else{
            $data['comment'] = [];
        }

		//获取2条答疑
		/*$where = "tips_id={$tips_id}";
		if(session('?member')){
			$where .= ' and ((answer is not null) or A.member_id='. session('member.id') .')';
		}else{
			$where .= ' and (answer is not null)';
		}
		$data['feedback'] = D('FeedbackView')->where($where)->order('id desc')->limit(2)->select();
        $data['feedback'] = empty($data['feedback'])? [] :$data['feedback'];*/

		//获取促销信息
//		$marketing = M('marketing')->where(['type' => 0, 'type_id' => $tips_id, 'end_time' => ['GT', time()], 'num' => ['GT', 0]])->order('start_time,price')->find();
//		if(!empty($marketing)){
//			$data['marketing'] = $marketing;
//		}else{
//          $data['marketing'] = [];
//      }

		//已报名人数信息
		if(!empty($data['times'])){
			foreach($data['times'] as $result){
				$tips_times_id[] = $result['id'];
			}
		}
		$count = 0;//总报名人数
		$count_num = 0;
		$data['buied_info'] = [];
		if($p_tags_id == 76 || $rs['category_id'] == 2){
			$buied_info = D('OrderWaresView')->where(['ware_id'=>$tips_id,'type'=>0,'act_status'=>['IN','1,2,3,4,5']])->field('B.member_id as member_id,tips_times_id,nickname,path,count(B.member_id) as count')->group('B.member_id')->order('A.datetime desc')->select();

				foreach($buied_info as $k=>$r){
//					if ($r['member_id'] == '270073' && $tips_id == '10661') {
					
              

					//转换缩略图
					$r['path'] = thumb($r['path'],2);
					if(preg_match('/手机号_(.+)$/', $r['nickname'], $arr)){
	                    
						$r['nickname'] = substr($arr[1], 0, 3) . '**' . substr($arr[1], 9, 2);
					}
					/*
					//转换缩略图
					$r['path'] = thumb($r['path'],2);
					if(preg_match('/手机号_(.+)$/', $r['nickname'], $arr)){
						$r['nickname'] = substr($arr[1], 0, 3) . '**' . substr($arr[1], 9, 2);
					}
					*/
					$data['buied_info'][] = $r;
					$count_num += $r['count'];
			}
			$count +=$count_num;
		}
		$data['entered_num'] = $count;
		//把每一期的报名人数信息加入数组(限5人)，添加已报名人数
		foreach($data['times'] as $rk=>$re){
			$num = 0;
			$data['times'][$rk]['member_info'] = [];
			$members_info = D('OrderWaresView')->where(['ware_id'=>$tips_id,'type'=>0,'tips_times_id'=>$re['id'],'act_status'=>['IN','1,2,3,4,5']])->field('B.member_id as member_id,tips_times_id,nickname,path,count(B.member_id) as count')->group('B.member_id')->order('A.datetime desc')->select();
			foreach($members_info as $k=>$r){
					

				//转换缩略图
				$r['path'] = thumb($r['path'],2);

				if ($r['member_id'] == '270073' && $tips_id == '10661') {
                    	$r['path'] = 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg';
                    	$r['nickname'] = '余华杰';
                }

				if(preg_match('/手机号_(.+)$/', $r['nickname'], $arr)){
					$r['nickname'] = substr($arr[1], 0, 3) . '**' . substr($arr[1], 9, 2);
				}

				$len = count($r['nickname']);
				$r['nickname'] = mb_substr($r['nickname'], 0, 1) . '**' . mb_substr($r['nickname'], $len - 2, 1);

				$data['times'][$rk]['member_info'][] = $r;
				$num += $r['count'];


			}

			if ($tips_id == '10588' && $rk == '0') {
					$r1['nickname'] = '期**2';
					$r1['path'] = 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg';
					$r1['count'] = '1';
					$data['times'][$rk]['member_info'][] = $r1;
					$r2['nickname'] = '猫**果';
					$r2['path'] = 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg';
					$r2['count'] = '1';
					$data['times'][$rk]['member_info'][] = $r2;
	//				$data['times'][$rk]['member_info']['1'] = '1';
					$num += '2';
			}

			$data['times'][$rk]['count'] = $num;

			if(!isset($_time) && $re['stock'] > 0 && $re['act_stop_buy_time'] > time()){
				$_time = $data['times'][$rk];
			}

		}
		//获取发布者的相关信息
		$data['tips_count'] = M('tips')->join('join `ym_tips_sub` on id=tips_id')->where(['member_id' => $rs['member_id'], 'status' => 1, 'is_public' => 1])->count();
		//获取赏味数
		$data['shangwei'] = (int)D('ShangweiView')->where(['member_id' => $rs['member_id'], 'act_status' => ['IN', '1,2,3,4,5'], 'status' => 1])->getField('count');
		$data['shangwei'] = (int)$data['shangwei'];
		if(empty($data['shangwei']))$data['shangwei']=0;
		//获取送米数
//		$data['mibi'] = D('WealthView')->where(['member_id' => $rs['member_id'], 'type' => 'huoqu', 'wealth' => 2])->getField('sum');
//		$data['mibi'] = (int)$data['mibi'];
//		if(empty($data['mibi']))$data['mibi']=0;
        //获取粉丝数
        $data['follow_num'] = M('MemberFollow')->where(['follow_id'=>$data['daRen_id']])->count();

		//亮点
		if(!empty($data['edge_1']))$data['edge'][] = $data['edge_1'];
		if(!empty($data['edge_2']))$data['edge'][] = $data['edge_2'];
		if(!empty($data['edge_3']))$data['edge'][] = $data['edge_3'];
		//达人作品图组
		//$cover_pic_id = M('MemberInfo')->where(array('member_id'=>$data['daRen_id']))->getField('cover_pic_id');
        //$data['daren_group_path'] = thumb(M('Pics')->where(['id'=>$cover_pic_id])->getField('path'),7);
		/*foreach($daren_group_path as $g_rs){
			$data['daren_group_path'][] = thumb($g_rs['path']);
		}*/

		//须知
        if(!empty($rs['notice']) && $p_tags_id != 76){
//            $notice_rs = M('TipsNotice')->where("`status`=1 or (`status`=2  and `id` in(".$rs['notice']."))")->getField('context', true);
            $notice_rs = M('TipsNotice')->where("`status`=1 or ((`status`=2 or `status`=3) and `id` in(".$rs['notice']."))")->getField('context', true);
        } elseif(!empty($rs['notice']) && $p_tags_id == 76) {
			// 私房菜
			$notice_rs = M('TipsNotice')->where("`status` = 4 or ((`status`=2 or `status`=3) and `id` in(".$rs['notice']."))")->getField('context', true);
		}else{
            $notice_rs = M('TipsNotice')->where("`status`=1")->getField('context', true);
        }
		$notice = [];
		foreach($notice_rs as $row){
			if(in_array('早餐',$data['tags'])) {
				$row = str_replace('yami194', 'woyoufan-shanghai', $row);
			}elseif(session('city_id') == '35') {
				$row = str_replace('yami194', 'woyoufan-beijing', $row);
			}elseif(session('city_id') == '37') {
				$row = str_replace('yami194', 'woyoufan-beijing', $row);
			}
			if(in_array($this->channel, [7,8,9]))
				$row = str_replace('吖咪', '我有饭', $row);
			$notice[] = $row;
		}

		$time = !empty($_time)?$_time:$data['times'][count($data['times']) - 1];
		$time['piece']=[];
		if(!empty($time)){
			foreach($time['tips_piece'] as $k=>$v){
				if($time['stock']>0 && $v['count']<$time['stock'] && $v['can_buy'] == 1){
					$time['piece'][] = $v;
				}
			}
			unset($time['tips_piece']);
		}

		if(!empty($data['times'])){
			foreach($data['times'] as $_k=>$_v){
				$data['times'][$_k]['piece'] =[];
				foreach($_v['tips_piece'] as $r){
					if($r['can_buy'] == 1){
						$data['times'][$_k]['piece'][] = $r;
					}
				}
				unset($data['times'][$_k]['tips_piece']);
			}
		}
		//活动的所有图片
		$data['all_pics'] = array_merge($data['pics_group'],$data['menu_pics_group'],$data['environment_pics_group_id']);
		//调整顺序
		$_data = [
			'mainpic' => $data['mainpic'],
			'pics_group' => $data['pics_group'],
			'title' => $data['title'],
            'discount' => $data['discount'],
            'catname' => $data['catname'],
			'tags' => $data['tags'],
			'p_tags_id' => $data['p_tags_id'],
			'edge' => $data['edge']?:[],
			'time' => $time,
			'times' => $data['times'],
			'address' => $data['address'],
			'simpleaddress' => $data['simpleaddress'],
			'daRen_id' => $data['daRen_id'],
			'headpic' => $data['headpic'],
			'cover_path' => thumb($data['cover_path'],7),
			'nickname' => $data['nickname'],
			'telephone' => $data['telephone'],
			'tips' => $data['tips_count'],
			'shangwei' => $data['shangwei'],
            'follow_num' => $data['follow_num'] = !empty($data['follow_num'])?$data['follow_num']:0,
			//'daren_group_path' => $data['daren_group_path'],
			'introduce' => $data['introduce'],
			'content' => $data['content'],
			'member_introduce' => $data['member_introduce'],
			'menu' => $data['menu'],
			'menu_pics_group' => $data['menu_pics_group'],
			'environment_pics_group_id' => $data['environment_pics_group_id'],
			'comment' => $data['comment'],
			'buied_info' => $data['buied_info'],
			'entered_num' => $data['entered_num'],
			'all_pics' => $data['all_pics'],
			'start_buy_time' => !empty($_time)?$_time['start_buy_time']:$data['start_buy_time'],
			'stop_buy_time' => $data['stop_buy_time']?:0,
			'latitude' => $data['latitude'],
			'longitude' => $data['longitude'],
			'min_num' => $data['min_num'],
			'restrict_num' => $data['restrict_num'],
			'price' => (float)$data['price'],
			'isfollow' => $follow,
			'isCollect' => $isCollect,
			'notice' => $notice,
			'isFree' => 0
		];

		if(!empty($data['context_title'])){
			$_data['member_introduce'] .= '<style>.tips_context_title {text-align: center; font-size: 1.4rem; margin: 2rem auto; font-weight: bold;}
.tips_context_title:after {content: ""; height: 0.1rem; width: 10rem; background: #999; margin: 1.2rem; display: inline-block; vertical-align: middle;}
.tips_context_title:before {content: ""; height: 0.1rem; background: #999; width: 10rem; vertical-align: middle; margin: 1.2rem; display: inline-block;}
</style><div class="tips_context_title">'. $data['context_title'] .'</div>' . $data['context_text'];
		}

		//读取默认头像
		$ids = C('DefaultHeadPicIds');
		$rs = M('pics')->field(['id', 'path'])->where(['id' => ['IN', join(',', $ids)]])->select();
		$_data['defaultPics'] = [];
		foreach($rs as $row){
			$row['path'] = thumb($row['path'], 2);
			$_data['defaultPics'][] = $row;
		}

		//临时处理: 被分享用户奖励优惠券
		if($tips_id == 9116 && session('?invite')){
			if(!session('?member'))
				$_data['isFree'] = 1;
			else {
				//判断是否已经领取该优惠券
				$coupon_rs = M('MemberCoupon')->where(['member_id' => session('member.id'), 'coupon_id' => 1550])->find();
				if(empty($coupon_rs)){
					$_data['isFree'] = 1;
				}
			}
		}
		//临时处理结束
		$this->ajaxReturn($_data);
//		$this->put($_data);
	}

	Public function getInfo(){
		$tips_id = I('get.tips_id');
		if(empty($tips_id))$this->error('请携带活动ID!');
		$rs = M('tips')->join('__PICS__ a on a.id=pic_id')->where(['ym_tips.id' => $tips_id])->find();
		if(empty($rs))$this->error('活动不存在!');
		$this->ajaxReturn([
			'id' => $tips_id,
			'name' => $rs['title'],
			'imageurl' => thumb($rs['path'], 1),
			'url' => 'https://itunes.apple.com/us/app/wo-you-fan-jing-xuan-fan-ju/id1031148816?mt=8&uo=4',
			'goodsprice' => $rs['price']
		]);
	}

	/**
	 * @apiName 获取某活动下的问答列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} page: 分页页码
	 * @apiPostParam {string} tips_id: 活动ID
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "id": "31",
	 *         "tips_id": "4212",
	 *         "content": "是否可以带着基友一起去？怎么算钱？",
	 *         "answer": "不可以哦！请额外购买一份！",
	 *         "datetime": "2016-01-14 14:41:47",
	 *         "nickname": "王太家的鹏仔",
	 *         "path": "http://wx.qlogo.cn/mmopen/4WA8P7aH4OAXJ3yC1BM0a6icznP7rV1lnVgfWVs7mOeTMbObMLBibo1GA9NxNzFup7LBlEs168kuVibEF2LjVsno7g5wXtfYiaWT/0"
	 *     },
	 *     {
	 *         "id": "30",
	 *         "tips_id": "4212",
	 *         "content": "再次测试问答是否合理！能否回答！",
	 *         "answer": null,
	 *         "datetime": "2016-01-14 14:40:07",
	 *         "nickname": "石頭貓",
	 *         "path": "http://wx.qlogo.cn/mmopen/Oe67HqhICYsteVviadFCsCTcAf11AFOCgibCRZeKPEJicnHGic3ZajNeOEDTbOaiaH66kRoGc04Bibm30n2ejzKqE8wfX1kwKnO6up/0"
	 *     }
	 * ]
	 */
	Public function getFeedbackList(){
		$tips_id = I('post.tips_id');
		if(empty($tips_id))$this->error('非法访问！');
		$page = I('get.page', 1);

		$where = "tips_id={$tips_id}";
		if(session('?member')){
			$where .= ' and ((answer is not null) or A.member_id='. session('member.id') .')';
		}else{
			$where .= ' and (answer is not null)';
		}

		$data = D('FeedbackView')->where($where)->order('id desc')->page($page, 5)->select();

		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 提交问题入答疑库
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} tips_id: 活动ID
	 * @apiPostParam {string} ask: 问题内容
	 *
	 * @apiSuccessResponse
	 * {
	 * 	   'status' => 1,
	 *     'info' => '提交问题成功！'
	 * }
	 * @apiErrorResponse
	 * {
	 * 	   'status' => 0,
	 *     'info' => '失败原因'
	 * }
	 */
	Public function submitAsk(){
		$tips_id = I('post.tips_id');
		$ask = I('post.ask');
		if(empty($tips_id) || empty($ask)){
			$this->error('信息提交不正确！');
		}
		if(M('feedback')->add(array(
			'member_id' => session('member.id'),
			'tips_id' => $tips_id,
			'content' => $ask
		)))$this->success('提交问题成功！');
		$this->error('提交失败！');
	}

	/**
	 * @apiName 活动邀请函
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} tips_id: 活动ID
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"nickname": "弦霄",
	 * 	"head_path": "http://img.m.yami.ren/20160721/Y1NTUwNWVkNGZiMDE0YmNmNGE1NDZm.jpg",
	 * 	"start_time": "2016-12-01",
	 * 	"title": "测试",
	 * 	"tips_path": "http://img.m.yami.ren/20161017/f65ebedbca507df9ba15181df75c787a4187d555_640x420.jpg"
	 * }
	 */
	Public function invitation(){
		$tips_id = I('post.tips_id');
		if(!session('?invite')){
			$this->error('请通过邀请链接进入!');
		}
		$member_id = session('invite.member_id');

		$data = [];
		//获取邀请用户信息
		$rs = M('member')->field(['nickname', 'path'])->join('__PICS__ a on pic_id=a.id')->where(['ym_member.id' => $member_id])->find();
		$data['nickname'] = $rs['nickname'];
		$data['head_path'] = thumb($rs['path'], 2);
		//获取活动信息
		$rs = M('TipsTimes')->field(['start_time', 'title', 'path'])->join('__TIPS__ a on a.id=tips_id')->join('__PICS__ b on b.id=a.pic_id')->where(['a.id' => $tips_id, 'stop_buy_time' => ['GT', time()], 'a.status' => 1, 'a.is_pass' => 1])->find();
		if(empty($rs))$this->error('被邀请的活动已下架或已过期!');
		$data['start_time'] = date('Y-m-d', $rs['start_time']);
		$data['title'] = $rs['title'];
		$data['tips_path'] = thumb($rs['path'], 1);

		$this->put($data);
	}
}