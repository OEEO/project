<?php
namespace Daren\Controller;
use Daren\Common\MainController;

// @className 我的活动
class TipsController extends MainController {

	/**
	 * @apiName 获取我的活动列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} page: 页码（每页5条）
	 * @apiPostParam {int} status: 活动状态（1-上线中，2-待处理，3-已结束，默认null-全部）
	 *
	 * @apiSuccessResponse
	 * 	    [
	 * 	        {
	 * 		        "id": "4212",
	 * 		        "title": "吖咪烘焙课 | 小花の圣诞树根蛋糕+圣诞草莓塔",
	 * 		        "status": "上架",
	 * 		        "catname": "烘焙课",
	 * 		        "path": "uploads/20151201/1450248177.jpg",
	 * 		        "price": "380.00",
	 * 		        "times": [
	 * 		            {
	 * 		                "start_time": "1453350300",
	 * 		                "end_time": "1453350600",
	 * 		                "times_id": "12403",
	 * 		                "min_num": "0", //最少成局
	 * 		                "restrict_num": "4", //最多参与
	 * 		                "sold": "0", //已售卖
	 * 		                "joined": "0", //已参与
	 * 						"phase" : "1"
	 * 		            }
	 * 		        ]
	 * 		    },
	 * 		    {
	 * 		        "id": "4205",
	 * 		        "title": "吖咪烘焙课| 小花の圣诞亲子【姜饼屋】",
	 * 		        "status": "上架",
	 * 		        "catname": "烘焙课",
	 * 		        "path": "uploads/20151216/5670bec0a1200.jpg",
	 * 		        "price": "280.00",
	 * 		        "times": [
	 * 		            {
	 * 		                "start_time": "1452306600",
	 * 		                "end_time": "1452313800",
	 * 		                "times_id": "12448",
	 * 		                "min_num": "0",
	 * 		                "restrict_num": "4",
	 * 		                "sold": "0",
	 * 		                "joined": "0",
	 * 						"phase" : "1"
	 * 		            },
	 * 		            {
	 * 		                "start_time": "1452483000",
	 * 		                "end_time": "1452515400",
	 * 		                "times_id": "12447",
	 * 		                "min_num": "0",
	 * 		                "restrict_num": "4",
	 * 		                "sold": "0",
	 * 		                "joined": "0",
	 * 						"phase" : "2"
	 * 		            },
	 * 		            {
	 * 		                "start_time": "1453107300",
	 * 		                "end_time": "1453107600",
	 * 		                "times_id": "12396",
	 * 		                "min_num": "0",
	 * 		                "restrict_num": "4",
	 * 		                "sold": "9",
	 * 		                "joined": "0",
	 * 						"phase" : "3"
	 * 		            }
	 * 		        ]
	 * 		    },
	 * 		]
	 */
	public function getList(){
		$page = I('get.page', 1);
		$_status = I('post.status',null);

		//筛选条件
		$where = [
			'member_id' => session('member.id')
		];
		if(!empty($_status)){
			//上线中
			if($_status == 1){
				$where['is_pass'] = 1;
				$where['stop_buy_time'] = ['GT', time()];
				$where['status'] = 1;
			}
			//待处理(审核中)
			if($_status == 2){
				$where['is_pass'] = 0;
				$where['status'] = ['IN', '1,2,3'];
			}
			//已结束
			if($_status == 3){
				$where['stop_buy_time'] = ['LT', time()];
				$where['status'] = 1;
			}
		}else{
			$where['status'] = ['IN', '1,2,3'];
		}
		//查询出该达人的活动
		$rs = D('TipsView')->where($where)->page($page, 5)->order('id desc')->group('A.id')->select();
		$ids = [];
		foreach($rs as $row){
			$ids[] = $row['id'];
		}
		$times = M('TipsTimes')->where(['tips_id' => ['IN', join(',', $ids)]])->order('id desc')->select();
		$data = [];
		//匹配每个活动的时间段
		foreach($rs as $key => $row){
			//状态判断
			if($row['status'] == 1){
				if($row['is_pass'] == 0){
					$status = '草稿(审核中)';
				}elseif($row['is_pass'] == 1){
					$status = '上架';
				}elseif($row['is_pass'] == 2){
					$status = '草稿(审核未通过)';
				}
			}elseif($row['status'] == 2){
				$status = '下架';
			}else{
				$status = '草稿';
			}

			$timesData = [];
			foreach($times as $_row){
				if($_row['tips_id'] == $row['id']){
					$timesData[] = [
						'start_time' => $_row['start_time'],
						'end_time' => $_row['end_time'],
						'times_id' => $_row['id'],
						'phase' => $_row['phase'],
						'min_num' => $_row['min_num'],
						'max_num' => $_row['max_num'],
						'restrict_num' => $_row['max_num'],
						'sold' => $_row['max_num'] - $_row['stock'],
						'joined' => M('OrderWares')->where(['tips_times_id' => $_row['id'], 'server_status' => 1])->count(),
						'is_over' => $_row['end_time'] < time() ? 1 : 0
					];
				}
			}

			$data[$key] = [
				'id' => $row['id'],
				'title' => $row['title'],
				'status' => $row['status'],
				'is_pass' => $row['is_pass'],
				'catname' => $row['catname'],
				'path' => thumb($row['path'], 1),
				'price' => $row['price'],
				'times' => $timesData
			];
		}
		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 获取用户评论列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} page: 页码
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *    "id": "13",
	 *    "member_id": "63",
	 *    "type_id": "109",
	 *    "content": "顶！",
	 *    "pics_group_id": null,
	 *    "datetime": "2016-03-09 18:57:37",
	 *    "nickname": "☀️没谱儿",
	 *    "path": "http://yummy194.cn/uploads/member/15622273375/559df60c7c1fe.jpeg"
	 *    },
	 *    {
	 *    "id": "19",
	 *    "member_id": "18",
	 *    "type_id": "3042",
	 *    "content": "内内是美女啊！",
	 *    "pics_group_id": "1",
	 *    "datetime": "2016-03-31 11:52:18",
	 *    "nickname": "何六二",
	 *    "path": "http://yummy194.cn/uploads/member/18689315230/55473b5e088ad.jpg",
	 *    "group_path": [
	 *    "http://yummy194.cn/uploads/member/18565765105/55e51332d73ef.jpg",
	 *    "http://yummy194.cn/uploads/member/13750344681/559a94a63853e.jpg"
	 *    ]
	 *    },
	 * ]
	 *
	 */

	Public function getCommentList(){
		$page = I('get.page',1);
		$pageSize = 5;

		//获取该达人举办过的活动
		$tips_id = M('Tips')->where(['member_id'=>session('member.id'),'status'=>['IN','1,2'],'is_pass'=>1])->getField('id',true);
		//找出活动对应的评论
		$tips_id = join(',',$tips_id);
		if(!empty($tips_id)){
			$comment_list = D('TipsCommentView')->where(['type_id'=>['IN',$tips_id]])->page($page,$pageSize)->select();
		}else{
			$comment_list = array();
		}


		foreach($comment_list as $row){
			$pics_group_ids[] = $row['pics_group_id'];
			//$pic_group_info[] = array('tips_id'=>$row['type_id'],'group_id'=>$row['pics_group_id']);
		}
		//找出所有图组path
		$pics_group_ids = join(',',$pics_group_ids);
		if(!empty($pics_group_ids)){
			$group_path = M('Pics')->where(['group_id'=>['IN',$pics_group_ids]])->field('path,group_id')->select();
		}else{
			$group_path = array();
		}


		//把图组加入评论列表
		foreach($comment_list as $key=>$row){
			foreach($group_path as $row2){
				if($row['pics_group_id'] == $row2['group_id']){
					$comment_list[$key]['group_path'][] = thumb($row2['path'],5);
				}
			}
			$comment_list[$key]['path'] = thumb($row['path'],5);
		}

		$this->ajaxReturn($comment_list);
	}


	/**
	 * @apiName 将活动设置为下架状态
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} tips_id: 要下架的活动ID
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "下架成功！",
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "下架失败，请确认该活动还处于上架状态！",
	 *     "status": 0,
	 *     "url": ""
	 * }
	 */
	Public function offShelf(){
		$tips_id = I('post.tips_id');
		if(empty($tips_id))$this->error('请指定要下架的活动ID');
		//判断是否有未完成的订单
		$count = M('Order')->join('__ORDER_WARES__ on __ORDER__.id=order_id')->where('act_status in (1,2,3,4,5) and ware_id=' . $tips_id)->count();
		if($count > 0){
			$this->error('已创建订单，无法下架！请联系客服处理..');
		}
		$rs = M('tips')->where(array('id' => $tips_id))->save(array('status' => 2));
		//取消相关促销
		$m_rs = M('marketing')->field('id,end_time')->where('type=0 and type_id='.$tips_id)->group('id desc')->find();
		if($m_rs['end_time']>time()){
			M('theme_element')->where(array('type'=>0,'type_id'=>$tips_id))->delete();
			M('marketing')->data(array('end_time'=>time(),'id'=>$m_rs['id']))->save();
		}
		//取消未支付订单
		M('Order')->join('__ORDER_WARES__ on __ORDER__.id=order_id')->where('act_status=0 and ware_id=' . $tips_id)->save(['act_status' => 7]);

		if($rs && $rs > 0){
			$this->success('下架成功！');
		}else{
			$this->error('下架失败，请确认该活动还处于上架状态！');
		}
	}

	/**
	 * @apiName 将活动设置为上架状态
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} tips_id: 要下架的活动ID
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "上架成功！",
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "上架失败，请确认该活动还处于下架状态！",
	 *     "status": 0,
	 *     "url": ""
	 * }
	 */
	Public function onShelf(){
		$tips_id = I('post.tips_id');
		if(empty($tips_id))$this->error('请指定要上架的活动ID');
		$rs = M('tips')->where(array('id' => $tips_id, 'is_pass' => 1))->save(['status' => 1]);
		if($rs && $rs > 0){
			$this->success('上架成功！');
		}else{
			$this->error('上架失败，请确认该活动还处于下架状态且审核已通过！');
		}
	}

	/**
	 * @apiName 验证会员的活动消费码(废弃)
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} tips_id: 待验证的活动ID
	 * @apiPostParam {int} code: 8位的订单商品消费码
	 *
	 * @apiSuccessResponse
	 * {
	 *    "info" : "消费码验证成功!",
	 *    "status" : 1
	 * }
	 * @apiErrorResponse
	 * {
	 *    "info" : "失败原因",
	 *    "status" : 0
	 * }
	 */
	public function validate(){
		$code = I('post.code');
		$tips_id = I('post.tips_id');
		if(empty($code)){
			$this->error('非法访问!');
		}
		$rs = M('OrderWares')->where(['check_code' => $code, 'type' => 0, 'ware_id' => $tips_id])->find();
		$order_id=$rs['order_id'];
		if(empty($rs))$this->error('无效的消费码!');
		$tips = M('tips')->where(['id' => $tips_id, 'member_id' => session('member.id')])->find();
		if(empty($tips))$this->error('无法验证别人的活动!');
		$_rs = M('TipsTimes')->where(['tips_id' => $tips_id, 'end_time' => ['GT', time()]])->find();
		if($_rs['id'] != $rs['tips_times_id']){
			if(empty($_rs))
				$this->error('要验证的活动已结束!');
			elseif($_rs['start_time'] - $tips['stop_buy_time'] > time())
				$this->error('要验证的活动尚未开始!');
			else
				$this->error('验证失败!');
		}
		//if($_rs['start_time'] - $tips['stop_buy_time'] > time())$this->error('要验证的活动尚未开始!');

		//更新订单商品状态
		$_rs = M('OrderWares')->where(['check_code' => $code])->save(['server_status' => 1]);
		if(empty($_rs))$this->error('该消费码已经被验证过了!');

		//验票成功，给达人余额账号打钱
//        $rs = D('OrderView')->where(['id'=>$rs['order_id'], 'act_status'=>1])->find();
//		if(!empty($rs)){
//			//计算优惠前的价格
//			if($rs['member_coupon_id'] != null){
//				switch($rs['type']){
//					case 0:
//						$price = $rs['price']+$rs['value'];//抵价券
//						break;
//					case 1:
//						$price = $rs['price']/($rs['value']/100);//折扣券
//						break;
//					case 2:
//						$price = $rs['price'];//礼品券
//						break;
//				}
//			}else{
//				$price = $rs['price'];
//			}
//            //财富表和财富日志表更新
//			$MemberWealthId = M('MemberWealth')->where(['member_id'=>session('member.id'),'wealth'=>'1'])->getField('id');
//			M('MemberWealthLog')->data(['member_wealth_id'=>$MemberWealthId,'type'=>'shoumai','quantity'=>$price])->add();
//            M('MemberWealth')->where(['member_id'=>session('member.id'),'wealth'=>'1'])->setInc('quantity',$price);
//		}
		M('Order')->where(['id' => $rs['order_id']])->save(['act_status' => 2]);
		\Common\Util\Cache::getInstance()->set('checkCode_' . $code, 1);

		//延迟推送消息
		$rs = D('OrderView')->where(['id'=>$order_id])->find();//不能碰
		if(!in_array($rs['channel'], [7,8,9])){
			$channel = 0;
			$context = "您参与的『{$rs['title']}』已经结束，现场气氛如何？达人手艺棒不棒？和达人互动愉快吗？快来给达人评分吧！";
		}else{
			$channel = 1;
			$context = "您参与的『{$rs['title']}』已经结束，现场气氛如何？主人手艺棒不棒？和主人互动愉快吗？快来给主人评分吧！";
		}
		$this->pushMessage($rs['member_id'], $context, 'sms', 3, $rs['id'], $rs['end_time'] + 3600, $channel);

		$this->success('消费码验证成功!');
	}

	/**
	 * @apiName 获取活动分类列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "id": "7",
	 *         "name": "其他",
	 *         "sign": "other"
	 *     },
	 *     {
	 *         "id": "10",
	 *         "name": "品鉴会",
	 *         "sign": "tasting"
	 *     },
	 *     {
	 *         "id": "8",
	 *         "name": "饮品课",
	 *         "sign": "drink"
	 *     },
	 * ]
	 */
	Public function getCatList(){
		$rs = M('category')->field('id, name, sign')->where(['type' => 0])->order('`order` desc')->select();
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
	 * @apiName 获取标签列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "id": "1",
	 *         "name": "西餐"
	 *     },
	 *     {
	 *         "id": "2",
	 *         "name": "麻辣"
	 *     },
	 *     {
	 *         "id": "3",
	 *         "name": "西餐"
	 *     },
	 * ]
	 */
	Public function getTagList(){
		$rs = M('tag')->field('id, name')->where(['type' => 1, 'official' => 0])->select();
		$this->ajaxReturn($rs);
	}

	/**
	 * @apiName 获取编辑活动的信息
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} tips_id: 要编辑的活动ID
	 *
	 * @apiSuccessResponse
	 * //第一大项是否已填写,第二大项是否已填写...
	 * [1, 0, 0, 0, 0, 0, 0, 0, 0]
	 */
	Public function startEdit(){
		$tips_id = I('post.tips_id');
		if(empty($tips_id)){
			if(session('?EditingTipsID'))
				$tips_id=session('EditingTipsID');
		}

		$result = [0, 0, 0, 0, 0, 0, 0, 0, 0];
		if(empty($tips_id)){
			$data = [
				'member_id' => session('member.id'),
				'title' => null,
				'category_id' => null,
				'price' =>  0,
				'min_num' => 0,
				'restrict_num' => 0,
				'citys_id' => null,
				'address' => null,
				'longitude' => null,
				'latitude' => null,
				'tel' => null,
				'is_pass' => 0,
				'status' => 3
			];
			$id = M('tips')->add($data);
			session('EditingTipsID', $id);
			$data['tips_id'] = $id;
			M('tips_sub')->add($data);
		}else{
			$rs = D('TipsView')->where(['id' => $tips_id])->find();
			if(empty($rs))$this->error('要编辑的活动不存在！');
			if($rs['member_id'] != session('member.id'))$this->error('这活动不属于你，无法编辑！');
			if($rs['status'] == 1)$this->error('当前活动处于上架(审核中)状态无法编辑，请先下架！');
			//记录处于编辑状态的活动ID
			session('EditingTipsID', $tips_id);
			//将活动设置为草稿状态
			if($rs['status'] != 3)M('tips')->where(['id' => $tips_id])->save(['status' => 3, 'is_pass' => 0]);
			//判断分类是否填写
			if(!empty($rs['category_id'])){
				$result[0] = 1;
			}
			//判断标签是否填写
			$tags = D('TagView')->where(['tips_id' => $tips_id, 'type' => 1, 'official' => 0])->count();
			if($tags > 0){
				$result[1] = 1;
			}
			//判断标题/介绍是否已填写
			if(!empty($rs['pic_id']) && !empty($rs['title']) && !empty($rs['intro'])){
				$result[2] = 1;
			}
			//判断时间是否已填写
			$times = M('TipsTimes')->where(['tips_id' => $tips_id])->count();
			if($times > 0){
				$result[3] = 1;
			}
			//判断人数及价格是否已填写
			if(!empty($rs['price']) && !empty($rs['discount']) && !empty($rs['min_num']) && !empty($rs['restrict_num'])){
				$result[4] = 1;
			}
			//判断创建菜单是否已填写
			$menu = M('TipsMenu')->where(['tips_id' => $tips_id])->count();
			if(!empty($rs['menu_pics_group_id']) && $menu > 0){
				$result[5] = 1;
			}
			//判断地址是否填写
			if(!empty($rs['citys_id']) && !empty($rs['address']) && !empty($rs['simpleaddress']) && !empty($rs['longitude'])){
				$result[6] = 1;
			}
			//判断环境图片是否已填写
			if(!empty($rs['environment_pics_group_id'])){
				$result[7] = 1;
			}
			//判断须知是否已填写
			if(!empty($rs['notice'])){
				$result[8] = 1;
			}
		}
		$this->ajaxReturn($result);
	}

	/**
	 * @apiName 获取分类和标签
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"cat_id": "2",
	 * 	"cat_name": "课程",
	 * 	"tags": [
	 * 		{
	 * 			"id": "1",
	 * 			"name": "西餐"
	 * 		},
	 * 		{
	 * 			"id": "2",
	 * 			"name": "麻辣"
	 * 		},
	 * 		{
	 * 			"id": "5",
	 * 			"name": "日料"
	 * 		}
	 * 	]
	 * }
	 */
	Public function getCatAndTags(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}
		$tags = D('TagView')->field(['id', 'name'])->where(['tips_id' => $tips_id, 'type' => 1, 'official' => 0])->select();
		$data = [
			'cat_id' => $rs['category_id'],
			'cat_name' => $rs['catname'],
			'tags' => $tags
		];
		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 保存分类和标签
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} category_id: 分类ID
	 * @apiPostParam {string} tag_ids: 标签ID(多个ID用逗号隔开)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function saveCatAndTags(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = M('Tips')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		$category_id = I('post.category_id');
		$tag_ids = I('post.tag_ids');

		//保存分类
		if(!empty($category_id)){
			M('tips')->save([
				'id' => $tips_id,
				'category_id' => $category_id
			]);
		}

		//插入标签
		if(!empty($tag_ids)){
			$tag_ids = explode(',', $tag_ids);
			$data = [];
			foreach($tag_ids as $id){
				$data[] = [
					'tips_id' => $tips_id,
					'tag_id' => $id
				];
			}
			M('TipsTag')->where(['tips_id' => $tips_id])->delete();
			if(!empty($data))M('TipsTag')->addAll($data);
		}

		M('TipsSub')->where(['tips_id' => $tips_id])->save(['last_update_time' => time()]);

		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取主题和介绍信息及图组
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"title": "这条旧路依然没有改变",
	 * 	"intro": "我转过我的脸，不让你看见。",
	 * 	"edge_1": "每当我闭上眼我总是可以看见",
	 * 	"edge_2": "我还是祝福你过的好一点",
	 * 	"edge_3": "静静的陪你走了好远好远",
	 * 	"catname": "课程",
	 * 	"pics": [
	 * 		{
	 * 			"id": "13555",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fce6a26056d_640x420.png"
	 * 		},
	 * 		{
	 * 			"id": "13557",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fceaf098410_640x420.jpg"
	 * 		},
	 * 		{
	 * 			"id": "13558",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fd054e19eb5_640x420.jpg"
	 * 		}
	 * 	]
	 * }
	 */
	Public function getTitleAndPic(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		//读取图组
		$pics = M('pics')->field(['id', 'path'])->where(['group_id' => $rs['pics_group_id'], 'member_id' => session('member.id')])->select();
		foreach($pics as $k => $v){
			$pics[$k]['path'] = thumb($v['path'], 1);
		}

		$this->ajaxReturn([
			'title' => $rs['title'],
			'intro' => $rs['intro'],
			'edge_1' => $rs['edge_1'],
			'edge_2' => $rs['edge_2'],
			'edge_3' => $rs['edge_3'],
			'catname' => $rs['catname'],
			'pics' => $pics
		]);
	}

	/**
	 * @apiName 保存主题和介绍信息及图组
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} title: 活动标题(25字以内)
	 * @apiPostParam {string} intro: 活动副标题(25字以内)
	 * @apiPostParam {string} edge_1: 活动亮点一(15字以内)
	 * @apiPostParam {string} edge_2: 活动亮点二(15字以内)
	 * @apiPostParam {string} edge_3: 活动亮点三(15字以内)
	 * @apiPostParam {string} pics: 活动图组图片ID(多张图片逗号隔开)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function saveTitleAndPic(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$title = I('post.title', null);
		$intro = I('post.intro', null);
		$edge_1 = I('post.edge_1', null);
		$edge_2 = I('post.edge_2', null);
		$edge_3 = I('post.edge_3', null);
		$pics = I('post.pics', null);

		if(abslength($title) > 25)$this->error('标题不能超出25字!');
		if(abslength($intro) > 25)$this->error('小标题不能超出25字!');
		if(abslength($edge_1) > 15 || abslength($edge_2) > 15 || abslength($edge_3) > 15)$this->error('亮点不能超出15字!');

		$data = [
			'title' => $title
		];
		$_data = [
			'intro' => $intro,
			'edge_1' => $edge_1,
			'edge_2' => $edge_2,
			'edge_3' => $edge_3,
			'last_update_time' => time()
		];

		if(!empty($pics)){
			//判断是否有原先的图组
			if(!empty($rs['pics_group_id'])){
				$group_id = $rs['pics_group_id'];
				M('pics')->where(['group_id' => $group_id])->save(['group_id' => ['exp','null'], 'is_used' => 0]);
			}else {
				//添加新的图组
				$group_id = M('PicsGroup')->add([
					'type' => 0
				]);
			}

			M('pics')->where(['id' => ['IN', $pics]])->save([
				'group_id' => $group_id,
				'is_used' => 1
			]);
			//将图组ID保存到SUB表中
			$_data['pics_group_id'] = $group_id;
			$pics = explode(',', $pics);
			$data['pic_id'] = $pics[0];
		}
		M('tips')->where(['id' => $tips_id])->save($data);
		M('TipsSub')->where(['tips_id' => $tips_id])->save($_data);

		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取时间场次
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"stop_buy_time": 48,
	 * 	"times": [
	 * 		{
	 * 			"id": "13053",
	 * 			"phase": "1",
	 * 			"start_time": "1458302400",
	 * 			"end_time": "1458309600"
	 * 		},
	 * 		{
	 * 			"id": "13054",
	 * 			"phase": "2",
	 * 			"start_time": "1458475200",
	 * 			"end_time": "1458484200"
	 * 		}
	 * 	]
	 * }
	 */
	Public function getTimes(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = M('Tips')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}
		$times = M('TipsTimes')->field(['id', 'phase', 'start_time', 'end_time', 'min_num', 'max_num', 'stock', 'start_buy_time', 'stop_buy_time', 'limit_num'])->where(['tips_id' => $tips_id])->select();
		$this->ajaxReturn([
			'stop_buy_time' => round(($times[0]['start_time'] - $times[0]['stop_buy_time']) / 3600),
			'times' => $times
		]);
	}

	/**
	 * @apiName 保存时间场次
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {array} times: 时间数组(格式:times[0][phase]=1&times[0][id]=16049&times[0][start_time]=2016-08-24 19:00&times[0][end_time]=2016-08-24 20:00&times[0][min_num]=1&times[0][max_num]=22&times[0][stock]=12&times[0][start_buy_time]=2016-06-22 01:25&times[0][stop_buy_time]=2016-08-23 19:00&times[0][limit_num]=0)
	 *
	 * @apiPostParam {string} newTimeNode: 新增期数节点（自定义格式：开始时间-结束时间，如：1454916800-1453450600, 1454916800-1453450600）【非必填】
	 * @apiPostParam {string} oldTimeNode: 修改原有期数节点（自定义格式：旧节点ID-开始时间-结束时间，如：12403-1454916800-1453450600）【非必填】
	 * @apiPostParam {string} stopBuyTime: 活动开始前多少小时截止购买(单位 小时)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "错误原因",
	 *     "status": 0,
	 *     "url": ""
	 * }
	 */
	Public function saveTimes(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		if(isset($_POST['times'])){
			$times = I('post.times');
			if(count($times) == 0){
				$this->error('不能没有时间段!');
			}
			M()->startTrans();
			$times_ids = [];
			foreach($times as $row){
				if(empty($row['start_time']))$this->error('活动开始时间不能为空!');
				$row['start_time'] = strtotime($row['start_time'] . ':00');
				if(empty($row['end_time']))$this->error('活动结束时间不能为空!');
				$row['end_time'] = strtotime($row['end_time'] . ':00');
				if($row['start_time'] >= $row['end_time'])$this->error('开始时间必须小于结束时间!');
				if(empty($row['start_buy_time']))$row['start_buy_time'] = 0;
				$row['start_buy_time'] = strtotime($row['start_buy_time'] . ':00');
				if(empty($row['stop_buy_time']))$row['stop_buy_time'] = $row['start_time'];
				$row['stop_buy_time'] = strtotime($row['stop_buy_time'] . ':00');

				if($row['min_num'] > $row['max_num'])$this->error('成局人数不能小于接待人数!');
				$row['tips_id'] = $tips_id;
				if(empty($row['id'])){
					unset($row['id']);
					$times_ids[] = M('TipsTimes')->add($row);
				}else{
					$times_ids[] = $row['id'];
					M('TipsTimes')->save($row);
				}
			}
			$rs = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 0, 'ware_id' => $tips_id, 'tips_times_id' => ['NOT IN', join(',', $times_ids)], 'status' => 1, 'act_status' => ['IN', '1,2,3,4,5']])->find();
			if(!empty($rs)){
				M()->rollback();
				$this->error('某时间段已产生订单,不能删除!');
			}
			M('TipsTimes')->where(['tips_id' => $tips_id, 'id' => ['NOT IN', join(',', $times_ids)]])->delete();
			M()->commit();
			$this->success('保存成功!');
		}else{
			$stopBuyTime = I('post.stopBuyTime', 24);
			$stopBuyTime *= 3600;
			$oldTimeNode = I('post.oldTimeNode');
			$newTimeNode = I('post.newTimeNode');

			M('tips')->save([
				'id' => $tips_id,
				'stop_buy_time' => $stopBuyTime
			]);

			//json字符串转换成对象
			$timeNode = [];
			if(!empty($oldTimeNode)){
				$nodes = explode(',', $oldTimeNode);
				$oldTimeNode = [];
				foreach($nodes as $row){
					$arr = explode('-', trim($row));
					$oldTimeNode[] = [
						'id' => $arr[0],
						'start_time' => $arr[1],
						'end_time' => $arr[2]
					];
				}
				$timeNode = $oldTimeNode;
			}
			if(!empty($newTimeNode)){
				$nodes = explode(',', $newTimeNode);
				$newTimeNode = [];
				foreach($nodes as $row){
					$arr = explode('-', trim($row));
					$newTimeNode[] = [
						'start_time' => $arr[0],
						'end_time' => $arr[1]
					];
				}
				$timeNode = array_merge($timeNode, $newTimeNode);
			}
			//判断是否有重合时间
			foreach($timeNode as $node){
				if($node['start_time'] >= $node['end_time'])$this->error('开始时间不能在结束时间之后！');
				foreach($timeNode as $time){
					if(($node['start_time'] < $time['start_time'] && $node['end_time'] > $time['start_time']) || ($node['end_time'] > $time['end_time'] && $node['start_time'] < $time['end_time'])){
						$this->error('时间段有重合，请重新确认！');
					}
				}
			}

			$ids = [];
			$phase = 1;
			//修改旧的节点
			if(!empty($oldTimeNode)){
				foreach($oldTimeNode as $node){
					$ids[] =$node['id'];
					$node['phase'] = $phase ++;
					M('TipsTimes')->save($node);
				}
			}
			//删掉多余的节点
			$where = ['tips_id' => $tips_id];
			if(!empty($ids))$where['id'] = ['NOT IN', join(',', $ids)];
			M('TipsTimes')->where($where)->delete();
			//添加新节点
			$stock = M('Tips')->where(['id'=>$tips_id])->getField('restrict_num');
			if(!empty($newTimeNode)){
				foreach($newTimeNode as $node){
					$node['tips_id'] = $tips_id;
					$node['phase'] = $phase ++;
					$node['stock'] = $stock;
					M('TipsTimes')->add($node);
				}
			}
			M('TipsSub')->where(['tips_id' => $tips_id])->save(['last_update_time' => time()]);

			$this->success('保存成功！');
		}
	}

	/**
	 * @apiName 获取人数单价及包场折扣(临时)
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"price": "298.00", //价格
	 * 	"discount": "88.00", //包场折扣
	 *  "buy_status" : 0, //购买类型 0-常规 1-包场 2-定制
	 * 	"min_num": "6", //最小成局人数
	 * 	"restrict_num": "20" //最高接待人数
	 * }
	 */
	Public function getPriceAndNum(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = M('tips')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}
		$this->ajaxReturn([
			'price' => $rs['price'],
			'discount' => $rs['discount'],
			'buy_status' => $rs['buy_status'],
			'min_num' => $rs['min_num'],
			'restrict_num' => $rs['restrict_num']
		]);
	}

	/**
	 * @apiName 获取人数单价及包场折扣
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"price": "298.00", //价格
	 * 	"discount": "88.00", //包场折扣
	 *  "buy_status" : 0, //购买类型 0-常规 1-包场 2-定制
	 * 	"min_num": "6", //最小成局人数
	 * 	"restrict_num": "20" //最高接待人数
	 * }
	 */
	Public function getPrice(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = M('tips')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}
		$this->ajaxReturn([
			'price' => $rs['price'],
			'discount' => $rs['discount'],
			'buy_status' => $rs['buy_status']
		]);
	}

	/**
	 * @apiName 保存单价及包场折扣(临时)
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} price: 单价
	 * @apiPostParam {int} discount: 包场折扣(0-100)[0为无包场折扣]
	 * @apiPostParam {int} buy_status: 购买类型 0-常规 1-包场 2-定制
	 * @apiPostParam {int} min_num: 最小成局人数
	 * @apiPostParam {int} restrict_num: 最高接待人数
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function savePriceAndNum(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$price = (int)I('post.price', 0);
		$discount = (float)I('post.discount', 0);
		$buy_status = (int)I('post.buy_status', 0);
		$min_num = (float)I('post.min_num', 0);
		$restrict_num = (float)I('post.restrict_num', 0);

		if($price < 0)$this->error('单价不能为负!');
		if($buy_status == 1 && $discount < 0 && $discount > 100)$this->error('折扣取值在0-100之间');

		M('tips')->where(['id' => $tips_id])->save([
			'price' => $price,
			'discount' => $discount,
			'buy_status' => $buy_status,
			'min_num' => $min_num,
			'restrict_num' => $restrict_num
		]);
		$times_ids = M('OrderWares')->where(['type' => 0, 'ware_id' => $tips_id])->getField('tips_times_id', true);
		$where = ['tips_id' => $tips_id];
		if(!empty($times_ids)){
			$where['id'] = ['NOT IN', join(',', $times_ids)];
		}
		M('TipsTimes')->where($where)->save([
			'stock' => $restrict_num
		]);

		M('TipsSub')->where(['tips_id' => $tips_id])->save(['last_update_time' => time()]);

		$this->success('保存成功!');
	}

	/**
	 * @apiName 保存单价及包场折扣
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} price: 单价
	 * @apiPostParam {int} discount: 包场折扣(0-100)[0为无包场折扣]
	 * @apiPostParam {int} buy_status: 购买类型 0-常规 1-包场 2-定制
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function savePrice(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$price = (int)I('post.price', 0);
		$discount = (float)I('post.discount', 0);
		$buy_status = (int)I('post.buy_status', 0);

		if($price < 0)$this->error('单价不能为负!');
		if($buy_status == 1 && $discount < 0 && $discount > 100)$this->error('折扣取值在0-100之间');

		M('tips')->where(['id' => $tips_id])->save([
			'price' => $price,
			'discount' => $discount,
			'buy_status' => $buy_status
		]);

		M('TipsSub')->where(['tips_id' => $tips_id])->save(['last_update_time' => time()]);

		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取图组及菜单
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"pics": [
	 * 		{
	 * 			"id": "13555",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fce6a26056d.png"
	 * 		},
	 * 		{
	 * 			"id": "13557",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fceaf098410.jpg"
	 * 		},
	 * 		{
	 * 			"id": "13558",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fd054e19eb5.jpg"
	 * 		}
	 * 	],
	 * 	"menu": [
	 * 		{
	 * 			"name": "A@主菜",
	 * 			"value": [
	 * 				"红烧狮子头",
	 * 				"清蒸猴脑",
	 * 				"红焖熊掌",
	 * 				"凤凰人参汤"
	 * 			]
	 * 		},
	 * 		{
	 * 			"name": "A@甜品",
	 * 			"value": [
	 * 				"哥本哈根",
	 * 				"冰镇橙汁"
	 * 			]
	 * 		},
	 * 		{
	 * 			"name": "A@主食",
	 * 			"value": [
	 * 				"脆香馒头",
	 * 				"泰国香米"
	 * 			]
	 * 		},
	 * 		{
	 * 			"name": "A@开胃汤",
	 * 			"value": [
	 * 				"鱼翅银耳羹"
	 * 			]
	 * 		}
	 * 	]
	 * }
	 */
	Public function getPicAndMenu(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		//读取图组
		$data['pics'] = M('pics')->field(['id', 'path'])->where(['group_id' => $rs['menu_pics_group_id'], 'member_id' => session('member.id')])->select();
		foreach($data['pics'] as $k => $v){
			$data['pics'][$k]['path'] = thumb($v['path'], 1);
		}

		//读取菜单
		$menu = M('TipsMenu')->where(['tips_id' => $tips_id])->select();
		$data['menu'] = [];
		foreach($menu as $m){
			$data['menu'][] = [
				'name' => $m['food_type'],
				'value' => explode(',', $m['food_name'])
			];
		}

		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 保存菜单及菜品图组
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} pics: 图片ID(多张图片用逗号隔开)
	 * @apiPostParam {string} menu: 菜单文字(格式: A@主食:AA,BB,CC,DD|A@甜品:EE,FF|...)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function saveMenuAndPic(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$pics = I('post.pics');
		$menu = I('post.menu');

		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		if(!empty($pics)){
			//判断是否有原先的图组
			if(!empty($rs['menu_pics_group_id'])){
				$group_id = $rs['menu_pics_group_id'];
				M('pics')->where(['group_id' => $group_id])->save(['group_id' => ['exp','null'], 'is_used' => 0]);
			}else {
				//添加新的图组
				$group_id = M('PicsGroup')->add([
					'type' => 0
				]);
			}

			//循环更改图片的图组
			//$pics = explode(',', $pics);
			M('pics')->where(['id' => ['IN', $pics]])->save([
				'group_id' => $group_id,
				'is_used' => 1
			]);
			//将图组ID保存到SUB表中
			M('TipsSub')->where(['tips_id' => $tips_id])->save(['menu_pics_group_id' => $group_id]);
		}

		if(!empty($menu)){
			M('TipsMenu')->where(['tips_id' => $tips_id])->delete();
			$menus = explode('|', $menu);
			$data = [];
			foreach($menus as $menu){
				$menu = explode(':', $menu);
				$data[] = [
					'tips_id' => $tips_id,
					'food_type' => $menu[0],
					'food_name' => $menu[1]
				];
			}
			if(!empty($data)){
				M('TipsMenu')->addAll($data);
			}
		}
		M('TipsSub')->where(['tips_id' => $tips_id])->save(['last_update_time' => time()]);
		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取活动地址
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"address": "琶洲新村8A2503",
	 * 	"longitude": null,
	 * 	"latitude": null,
	 * 	"citys_id": "2094",
	 * 	"citys_name": "海珠",
	 * 	"simpleaddress": "琶洲新村"
	 * }
	 */
	Public function getAddress(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		$data = [
			'address' => $rs['address'],
			'longitude' => $rs['longitude'],
			'latitude' => $rs['latitude'],
			'area_id' => $rs['citys_id'],
			'area_name' => $rs['area_name'],
			'simpleaddress' => $rs['simpleaddress']
		];

		if($rs['area_alt'] == '市'){
			$data['city_id'] = $rs['citys_id'];
			$data['city_name'] = $rs['area_name'];
			$data['area_id'] = '';
			$data['area_name'] = '';
		}elseif($rs['area_alt'] == '区'){
			$ct = M('citys')->where(['id' => $rs['pid']])->find();
			$data['city_id'] = $ct['id'];
			$data['city_name'] = $ct['name'];
		}

		//读取图组
		$data['pics'] = M('pics')->field(['id', 'path'])->where(['group_id' => $rs['environment_pics_group_id'], 'member_id' => session('member.id')])->select();
		foreach($data['pics'] as $k => $v){
			$data['pics'][$k]['path'] = thumb($v['path'], 1);
		}

		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 保存活动地址
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} citys_id: 区ID
	 * @apiPostParam {string} longitude: 经度坐标
	 * @apiPostParam {string} latitude: 纬度坐标
	 * @apiPostParam {string} address: 详细地址
	 * @apiPostParam {string} simpleaddress: 简要地址
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function saveAddress(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$address = I('post.address', null);
		$simpleaddress = I('post.simpleaddress', null);
		$longitude = I('post.longitude', null);
		$latitude = I('post.latitude', null);
		$citys_id = I('post.citys_id', null);
		$pics = I('post.pics', null);

		$group_id = '';
		if(!empty($pics)){
			$group_id = M('TipsSub')->where(['tips_id' => $tips_id])->getField('environment_pics_group_id');
			//判断是否有原先的图组
			if(!empty($group_id)){
				M('pics')->where(['group_id' => $group_id])->save(['group_id' => ['exp','null'], 'is_used' => 0]);
			}else{
				//添加新的图组
				$group_id = M('PicsGroup')->add([
					'type' => 0
				]);
			}

			//循环更改图片的图组
			M('pics')->where(['id' => ['IN', $pics]])->save([
				'group_id' => $group_id,
				'is_used' => 1
			]);
		}
		M('TipsSub')->where(['tips_id' => $tips_id])->save([
			'address' => $address,
			'simpleaddress' => $simpleaddress,
			'longitude' => $longitude,
			'latitude' => $latitude,
			'citys_id' => $citys_id,
			'environment_pics_group_id' => $group_id,
			'last_update_time' => time()
		]);

		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取环境图片(临时)
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"pics": [
	 * 		{
	 * 			"id": "13555",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fce6a26056d.png"
	 * 		},
	 * 		{
	 * 			"id": "13557",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fceaf098410.jpg"
	 * 		},
	 * 		{
	 * 			"id": "13558",
	 * 			"path": "http://img.m.yami.ren/public/20160331/56fd054e19eb5.jpg"
	 * 		}
	 * 	]
	 * }
	 */
	Public function getEnvironmentPics(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		//读取图组
		$data['pics'] = M('pics')->field(['id', 'path'])->where(['group_id' => $rs['environment_pics_group_id'], 'member_id' => session('member.id')])->select();
		foreach($data['pics'] as $k => $v){
			$data['pics'][$k]['path'] = thumb($v['path'], 1);
		}

		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 保存环境图片(临时)
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} pics: 图片ID(多张图片用逗号隔开)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function saveEnvironmentPics(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');

		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		$pics = I('post.pics', null);

		if(!empty($pics)){
			//判断是否有原先的图组
			if(!empty($rs['environment_pics_group_id'])){
				$group_id = $rs['environment_pics_group_id'];
				M('pics')->where(['group_id' => $group_id])->save(['group_id' => ['exp','null'], 'is_used' => 0]);
			}else {
				//添加新的图组
				$group_id = M('PicsGroup')->add([
					'type' => 0
				]);
			}

			//循环更改图片的图组
			M('pics')->where(['id' => ['IN', $pics]])->save([
				'group_id' => $group_id,
				'is_used' => 1
			]);
			//将图组ID保存到SUB表中
			M('TipsSub')->where(['tips_id' => $tips_id])->save(['environment_pics_group_id' => $group_id, 'last_update_time' => time()]);
		}

		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取活动须知
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"fixed": [
	 * 		{
	 * 			"id": "1",
	 * 			"context": "成功报名后，请关注吖咪酱官方客服号：yami194"
	 * 		},
	 * 		{
	 * 			"id": "2",
	 * 			"context": "已付费的活动名额，不接受退款，不改期，可自行转让名额。\n\n已付费的活动名额，不接受退款，不改期，可自行\n\n转让名额。\n\n已付费的活动名额，不接受退款，不改期，可自行\n\n转让名额。"
	 * 		},
	 * 		{
	 * 			"id": "3",
	 * 			"context": "无特别说明的情况下，活动费用为单人单次费用，如携带他人参加必须购买相应份数。"
	 * 		},
	 * 		{
	 * 			"id": "4",
	 * 			"context": "本饭局不接受临时加入或现场付款，想参加饭局请一定要在这里下单哦。"
	 * 		},
	 * 		{
	 * 			"id": "5",
	 * 			"context": "如活动未达到最低成局人数，吖咪会进行退款。"
	 * 		},
	 * 		{
	 * 			"id": "6",
	 * 			"context": "如需开具发票，请咨询活动方。"
	 * 		}
	 * 	],
	 * 	"active": [
	 * 		 {
	 *			"id": "7",
	 *			"context": "活动会准时开始，请不要迟到哦。",
	 *			"selected": 1
	 *		},
	 *		{
	 *			"id": "8",
	 *			"context": "如有食品过敏、忌口等情况，请提前告知。",
	 *			"selected": 1
	 *		},
	 *		{
	 *			"id": "9",
	 *			"context": "本活动为亲子活动，可带一名小朋友参加。",
	 *			"selected": 0
	 *		},
	 *		{
	 *			"id": "10",
	 *			"context": "本活动非亲子活动，暂不接受12岁以下小朋友报名。",
	 *			"selected": 0
	 *		}
	 * 	],
	 *  "is_public": 1
	 * }
	 */
	Public function getNotice(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');

		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		//固定须知
		$notice_fixed = M('TipsNotice')->field(['id', 'context'])->where(['status' => 1])->select();
		//可选须知
		$notice_active = M('TipsNotice')->field(['id', 'context'])->where(['status' => 2])->select();

		$arr = explode(',', $rs['notice']);
		foreach($notice_active as $k => $v){
			if(in_array($v['id'], $arr)){
				$notice_active[$k]['selected'] = 1;
			}else{
				$notice_active[$k]['selected'] = 0;
			}
		}

		$this->ajaxReturn([
			'is_public' => $rs['is_public'],
			'fixed' => $notice_fixed,
			'active' => $notice_active
		]);
	}

	/**
	 * @apiName 保存活动须知
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} notice_ids: 须知ID(多个ID用逗号隔开)
	 * @apiPostParam {int} is_public: 是否公开(0-非公开 1-公开[默认])
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function saveNotice(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$notice_ids = I('post.notice_ids');
		$is_public = I('post.is_public', 1);

		M('TipsSub')->where(['tips_id' => $tips_id])->save([
			'is_public' => $is_public,
			'notice' => $notice_ids,
			'last_update_time' => time()
		]);

		$this->success('保存成功!');
	}

	/**
	 * @apiName 保存并发布活动
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} tips_id: 要提交审核的活动ID(非必填)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "发布成功！等待审核……",
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
	Public function submitSave(){
		$tips_id = I('post.tips_id');
		if(empty($tips_id)) {
			if (!session('?EditingTipsID')) $this->error('检测不到要编辑的活动，请重新选择！');
			$tips_id = session('EditingTipsID');
		}

		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		//获取标签
		$tags = M('tag')->join('__TIPS_TAG__ on tag_id=__TAG__.id')->where(['tips_id' => $tips_id])->getField('tag_id', true) ?: [];
		//获取上级城市ID
		$citys = new \Member\Model\CityViewModel;
		$citys = $citys->field(['district_id', 'district_name', 'city_id', 'city_name', 'province_id', 'province_name'])->where(['district_id' => $rs['citys_id']])->find();

		//获取时间段
		$times = M('TipsTimes')->field(['id, start_time, end_time, phase'])->where(['tips_id' => $tips_id])->select();

		//获取菜单
		$menu = M('TipsMenu')->where(['tips_id' => $tips_id])->count();

		if(empty($rs['title']))$this->error('活动标题不能为空！');
		if(empty($rs['price']))$this->error('活动价格不能为空！');
		if(!is_numeric($rs['category_id']))$this->error('活动分类不能为空！');
		if(empty($rs['address']))$this->error('活动详细地址不能为空！');
		if(empty($rs['simpleaddress']))$this->error('活动简写地址不能为空！');
		if(empty($rs['longitude']))$this->error('活动坐标经度不能为空！');
		if(empty($rs['latitude']))$this->error('活动坐标纬度不能为空！');
		if(empty($tags))$this->error('活动标签不能为空！');
		if(empty($citys))$this->error('活动城市区域不能为空！');
		if(empty($times))$this->error('活动时间节点不能为空！');
		if(empty($rs['pic_id']) || empty($rs['pics_group_id']))$this->error('活动主图不能为空！');
		if(empty($rs['citys_id']))$this->error('活动城市不能为空！');
		if(empty($rs['environment_pics_group_id']))$this->error('环境图组不能为空！');
		if(empty($rs['menu_pics_group_id']))$this->error('菜单图组不能为空！');
		if(empty($menu))$this->error('菜单不能为空！');
		//if(empty($rs['notice']))$this->error('活动须知不能为空！');

		M('tips')->save(['id' => $tips_id, 'status' => 1, 'is_pass' => 0]);
		M('MemberApply')->where(['member_id'=>session('member.id'),'type'=>0,'type_id'=>$tips_id,'is_pass'=>0])->delete();
		M('MemberApply')->data(['member_id'=>session('member.id'),'type'=>0,'type_id'=>$tips_id,'is_pass'=>0])->add();
		session('EditingTipsID', null);
		$this->success('发布成功！等待审核……');
	}

	/**
	 * @apiName 结束活动的编辑状态（重要）
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "编辑已结束!",
	 *     "status": 1,
	 *     "url": ""
	 * }
	 */
	Public function endEdit(){
		session('EditingTipsID', null);
		$this->success('编辑已结束！');
	}

	/**
	 * @apiName 删除活动（重要）
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} tips_id: 要删除的活动ID
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "删除成功!",
	 *     "status": 1,
	 *     "url": ""
	 * }
	 */
	Public function delete(){
		$tips_id = I('post.tips_id');
		$rs = M('tips')->where(['id' => $tips_id, 'member_id' => session('member.id'), 'status' => ['IN', '2,3']])->find();
		if(empty($rs)){
			$this->error('活动不存在或未下架,无法删除!');
		}
		//更改活动状态为删除
		M('tips')->where(['id' => $tips_id])->save(['status' => 0]);
		$this->success('删除成功!');
	}

	/**
	 * @apiName 复制活动
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} tips_id: 要复制的活动ID
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "1234",//新活动ID
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
	public function copyTips(){
		$tips_id = I('post.tips_id');
		//MainInfo
		$rs = M('Tips')->where(['id'=>$tips_id,'member_id'=>session('member.id')])->find();
		if(empty($rs))$this->error('该活动不属于你或活动不存在');
		//获取活动菜单
		$tips_menu = M('TipsMenu')->where(['tips_id'=>$tips_id])->select();
		//获取活动标签
		$tips_tag = M('TipsTag')->where(['tips_id'=>$tips_id])->select();


		$data = D('TipsView')->where(['id'=>$tips_id])->find();
		$_data = [
			'member_id' => session('member.id'),
			'title' => $data['title'],
			'pic_id' => $data['pic_id'],
			'category_id' => $data['category_id'],
			'price' =>  $data['price'],
			'buy_status' => $data['buy_status'],
			'min_num' => $data['min_num'],
			'restrict_num' => $data['restrict_num'],
			'citys_id' => $data['citys_id'],
			'address' => $data['address'],
			'longitude' => $data['longitude'],
			'latitude' => $data['latitude'],
			'tel' => $data['tel'],
			'is_pass' => 0,
			'status' => 3,
			'edge_1' => $data['edge_1'],
			'edge_2' => $data['edge_2'],
			'edge_3' => $data['edge_3'],
			'content' =>  trim(strip_tags($data['content'])),
			'intro' => $data['intro'],
			'pics_group_id' => $data['pics_group_id'],
			'environment_pics_group_id' => $data['environment_pics_group_id'],
			'menu_pics_group_id' => $data['menu_pics_group_id'],
			'notice' => $data['notice'],
		];
		$id = M('tips')->add($_data);
		session('EditingTipsID', $id);
		$_data['tips_id'] = $id;
		M('tips_sub')->add($_data);

		//加入菜单表
		$data = array();
		foreach($tips_menu as $row){
			$data = array(
				'tips_id'=>$id,
				'food_type' => $row['food_type'],
				'food_name' => $row['food_name']
			);
			M('TipsMenu')->data($data)->add();
		}

		//加入标签表
		$data = array();
		foreach($tips_tag as $row){
			$data = array(
				'tips_id' => $id,
				'tag_id' => $row['tag_id']
			);
			M('TipsTag')->data($data)->add();
		}
		$this->success($id);
	}

	/**
	 * @apiName 获取时间段列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} tips_id: 活动ID
	 *
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 		"id": "4573",
	 * 		"tips_id": "3000",
	 * 		"phase": "1",
	 * 		"start_time": "1456396200",
	 * 		"end_time": "1456407000",
	 * 		"stock": "12",
	 * 		"datetime": "2016-05-19 21:26:21"
	 * 	},
	 * 	{
	 * 		"id": "4573",
	 * 		"tips_id": "3000",
	 * 		"phase": "2",
	 * 		"start_time": "1456396200",
	 * 		"end_time": "1456407000",
	 * 		"stock": "12",
	 * 		"datetime": "2016-05-19 21:26:21"
	 * 	}
	 * ]
	 */
	public function getTimesList(){
		$tips_id = I('post.tips_id');
		$rs = M('TipsTimes')->where(['tips_id' => $tips_id])->select();
		$this->ajaxReturn($rs);
	}

	/**
	 * @apiName 添加时间段
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} tips_id: 活动ID
	 * @apiPostParam {int} start_time: 开始时间(时间戳)
	 * @apiPostParam {int} end_time: 结束时间(时间戳)
	 * @apiPostParam {int} stock: 库存(默认为活动的最大接待人数)
	 * @apiPostParam {int} min_num: 最小成局人数
	 * @apiPostParam {int} max_num: 最大接待人数
	 * @apiPostParam {int} start_buy_time: 开始购买时间(时间戳)
	 * @apiPostParam {int} stop_buy_time: 截止购买时间(时间戳)
	 * @apiPostParam {int} limit_num: 限购(0为不限购)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
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
	Public function addTimes(){
		$tips_id = I('post.tips_id');
		$start_time = I('post.start_time');
		$end_time = I('post.end_time');
		$min_num = I('post.min_num');
		$max_num = I('post.max_num');
		$stock = I('post.stock', $max_num);
		$start_buy_time = I('post.start_buy_time', 0);
		$stop_buy_time = I('post.stop_buy_time', $start_time);
		$limit_num = I('post.limit_num', 0);
		if($start_time >= $end_time){
			$this->error('结束时间不能小于开始时间!');
		}
		if(empty($max_num) || $max_num < $min_num){
			$this->error('最大接待人数不能为空或小于最小成局人数!');
		}
		$rs = M('TipsTimes')->where(['tips_id' => $tips_id])->order('end_time desc')->find();

		if(empty($rs))$this->error('要添加时间段的活动不存在!');
		if($rs['end_time'] >= $start_time)$this->error('开始时间不能小于往期的结束时间!');

		if(empty($start_buy_time))$start_buy_time = 0;
		if(empty($stop_buy_time))$stop_buy_time = $start_time;
		if(empty($stock))$stock = $max_num;

		M('TipsTimes')->add([
			'tips_id' => $tips_id,
			'start_time' => $start_time,
			'end_time' => $end_time,
			'stock' => $stock,
			'min_num' => $min_num,
			'max_num' => $max_num,
			'start_buy_time' => $start_buy_time,
			'stop_buy_time' => $stop_buy_time,
			'limit_num' => $limit_num,
			'phase' => $rs['phase'] + 1
		]);

		$this->success('保存成功！');
	}

}
