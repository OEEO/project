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
     * @apiPostParam {int} status: 活动状态（1-公开，2-非公开，3-草稿，默认null-全部）
	 *
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 		"id": "7420",
	 * 		"title": "我们都是神枪手",
	 * 		"status": "1", //1-上架 2-下架 3-草稿
	 * 		"path": "http://img.m.yami.ren/20160826/67199c527caf54da3487d6a42c263ed41602f740_640x420.jpg",
	 * 		"price": "1.00",
	 * 		"address": "xxxxxxxxx",
	 * 		"is_public": "1", //1-公开 0-非公开
	 * 		"is_apply_public": "0", //1-公开申请中 0-未进行公开申请
	 * 		"times": [
	 * 			{
	 * 				"start_time": "1475575200",
	 * 				"end_time": "1475582400",
	 * 				"times_id": "16056",
	 * 				"phase": "3",
	 * 				"min_num": "4",
	 * 				"max_num": "10",
	 * 				"stock": "10", //库存
	 * 				"sold": "0", //已售数量
	 * 				"nopay": "0", //未支付数量
	 * 				"is_over": "0" //是否已结束
	 * 			}
	 * 		]
	 * 	}
	 * ]
	 */
	public function getList(){
		$page = I('get.page', 1);
        $_status = I('post.status',null);

        //筛选条件
        $where = [
            'member_id' => session('member.id')
		];
        if(!empty($_status)){
            //公开
            if($_status == 1){
                $where['is_pass'] = 1;
                $where['status'] = ['IN', '1,2'];
				$where['is_public'] = 1;
            }
            //非公开
            if($_status == 2){
				$where['is_pass'] = 1;
				$where['status'] = ['IN', '1,2'];
				$where['is_public'] = 0;
            }
            //草稿
            if($_status == 3){
				$where['status'] = 3;
            }
        }else{
			$where['status'] = ['IN', '1,2,3'];
		}
		//查询出该达人的活动
        $rs = D('TipsView')->where($where)->page($page, 5)->order('id desc')->select();
		$ids = [];
		foreach($rs as $row){
			$ids[] = $row['id'];
		}
		//率先查询出所有时间段
		$times = M('TipsTimes')->where(['tips_id' => ['IN', join(',', $ids)]])->order('id desc')->select();
		//率先查询出所有时间段的订单数量
		$orders = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 0, 'ware_id' => ['in', join(',', $ids)], 'status' => 1, 'act_status' => ['in', '0,1,2,3,4,5']])->select();
		$sold = $nopay = [];
		foreach($orders as $row){
			if(!array_key_exists($row['tips_times_id'], $sold))$sold[$row['tips_times_id']] = 0;
			if(!array_key_exists($row['tips_times_id'], $nopay))$nopay[$row['tips_times_id']] = 0;
			if($row['act_status'] == 0)
				$nopay[$row['tips_times_id']] ++;
			else
				$sold[$row['tips_times_id']] ++;
		}
		$data = [];
		//匹配每个活动的时间段
		foreach($rs as $key => $row){
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
						'stock' => $_row['stock'],
                        'sold' => $sold[$_row['id']]?:0,
						'nopay' => $nopay[$_row['id']]?:0,
                        'is_over' => $_row['end_time'] < time() ? 1 : 0
					];
				}
			}

			$apply = M('MemberApply')->where(['type' => 4, 'type_id' => $row['id'], 'is_pass' => 0])->count();
			$data[] = [
				'id' => $row['id'],
				'title' => $row['title'],
				'status' => $row['status'],
				'path' => thumb($row['path'], 1),
				'price' => $row['price'],
				'address' => $row['name'],
				'is_public' => $row['is_public'],
				'is_apply_public' => $apply>0?1:0,
				'times' => $timesData
			];
		}
		$this->put($data);
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
            $comment_list = [];
        }


        foreach($comment_list as $row){
            $pics_group_ids[] = $row['pics_group_id'];
        }
        //找出所有图组path
        $pics_group_ids = join(',',$pics_group_ids);
        if(!empty($pics_group_ids)){
            $group_path = M('Pics')->where(['group_id'=>['IN',$pics_group_ids]])->field('path,group_id')->select();
        }else{
            $group_path = [];
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

        $this->put($comment_list);
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
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "下架失败，请确认该活动还处于上架状态！",
	 *     "status": 0
	 * }
	 */
	Public function offShelf(){
		$tips_id = I('post.tips_id');
		if(empty($tips_id))$this->error('请指定要下架的活动ID');
		//判断是否有未完成的订单
		$count = M('Order')->join('__ORDER_WARES__ on __ORDER__.id=order_id')->where(['status' => 1, 'ware_id' => $tips_id, 'type' => 0])->count();
		if($count > 0){
			$this->error('已创建订单，无法下架！请联系客服处理..');
		}
		$rs = M('tips')->where(['id' => $tips_id])->save(['status' => 2]);
		//活动时间段更新下架时间
		$rstimes =  M('TipsTimes')->where(['tips_id' => $tips_id])->select();
		foreach($rstimes as $v){
			if($v['stop_buy_time']>time()){
				M('TipsTimes')->where(['id' => $v['id']])->save(['under_time' => time()]);
			}
		}
        //取消相关促销
        //$m_rs = M('marketing')->field('id,end_time')->where('type=0 and type_id='.$tips_id)->group('id desc')->find();
        //if($m_rs['end_time']>time()){
        //    M('theme_element')->where(['type'=>0,'type_id'=>$tips_id])->delete();
        //    M('marketing')->data(['end_time'=>time(),'id'=>$m_rs['id']])->save();
        //}

		if($rs && $rs > 0 ){
			//记录活动修改快照信息
			$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
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
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "上架失败，请确认该活动还处于下架状态！",
	 *     "status": 0
	 * }
	 */
	Public function onShelf(){
		$tips_id = I('post.tips_id');
		if(empty($tips_id))$this->error('请指定要上架的活动ID');
		$rs = M('tips')->where(['id' => $tips_id, 'is_pass' => 1])->save(['status' => 1]);
		//活动时间段更新下架时间
		$rstimes =  M('TipsTimes')->where(['tips_id' => $tips_id])->select();
		foreach($rstimes as $v){
			if($v['stop_buy_time']>time()){
				M('TipsTimes')->where(['id' => $v['id']])->save(['release_time' => time()]);
			}
		}
		if($rs && $rs > 0){
			//记录活动修改快照信息
			$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
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

		M('Order')->where(['id' => $rs['order_id']])->save(['act_status' => 2]);
		//cache('checkCode_' . $code, 1);

		//延迟推送消息
        $rs = D('OrderView')->where(['id'=>$order_id])->find();//不能碰
//		if(!in_array($rs['channel'], [7,8,9])){
//			$channel = 0;
//			$context = "您参与的『{$rs['title']}』已经结束，现场气氛如何？达人手艺棒不棒？和达人互动愉快吗？快来给达人评分吧！";
//		}else{
//			$channel = 1;
//			$context = "您参与的『{$rs['title']}』已经结束，现场气氛如何？主人手艺棒不棒？和主人互动愉快吗？快来给主人评分吧！";
//		}
//		$this->pushMessage($rs['member_id'], $context, 'sms', 3, $rs['id'], $rs['end_time'] + 3600, $channel);

		//2016-12-27
		if(!in_array($rs['channel'], [7,8,9])){
			$channel = 0;
			$params =array(
				'title' =>SubCN4($rs['title'],15),
				'platform_member_1' =>'达人',
				'platform_member_2' =>'达人',
				'platform_member_3' =>'达人',

			);
		}else{
			$channel = 1;
			$params =array(
				'title' =>SubCN4($rs['title'],15),
				'platform_member_1' =>'主人',
				'platform_member_2' =>'主人',
				'platform_member_3' =>'主人',

			);
		}
		$this->push_Message($rs['member_id'], $params,'SMS_36340051', 'sms',null, 3, $rs['id'], $rs['end_time'] + 3600, $channel);

		$this->success('消费码验证成功!');
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
			$where = ['pid' => $pid];
		}
		$rs = M('citys')->where($where)->select();
		$this->put($rs);
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
		$rs = M('tag')->field(['id', 'name'])->where(['type' => 1, 'official' => 0])->select();
		$this->put($rs);
	}

	/**
	 * @apiName 开始活动编辑
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} tips_id: 要编辑的活动ID
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"title": "测试接口2测试接口2测试接口2测试接口2测试接口2",
	 * 	"space": "20",
	 * 	"info": "0",
	 * 	"menu": "0",
	 * 	"times": "0",
	 * 	"notice": "0",
	 * 	"is_public": "1",
	 * 	"price": "0.10"
	 * }
	 */
	Public function startEdit(){
		$tips_id = I('post.tips_id');
		if(empty($tips_id)){
			if(session('?EditingTipsID'))
				$tips_id=session('EditingTipsID');
		}

		$return = [
			'title' => '',
			'space' => 0,
			'info' => 0,
			'menu' => 0,
			'times' => 0,
			'notice' => 0,
			'is_public' => 0,
			'price' => ''
		];
		if(empty($tips_id)){
			$title = I('post.title');
			if(empty($title) || abslength($title) > 25)$this->error('标题不能为空或大于25字!');
			$data = [
				'member_id' => session('member.id'),
				'title' => $title,
				'category_id' => 1,
				'price' =>  0,
				'is_pass' => 0,
				'status' => 3
			];
			$id = M('tips')->add($data);
			session('EditingTipsID', $id);
			M('tips_sub')->add(['tips_id' => $id, 'is_public' => 0]);
			$return['title'] = $title;

			//记录活动修改快照信息
			$this->SaveSnapshotLogs($id,0,$this->framework_id());
			$this->put($return);
		}else{
			$rs = D('TipsView')->where(['id' => $tips_id])->find();
			if(empty($rs))$this->error('要编辑的活动不存在！');
			if($rs['member_id'] != session('member.id'))$this->error('这活动不属于你，无法编辑！');
			if($rs['status'] == 1)$this->error('当前活动处于上架(审核中)状态无法编辑，请先下架！');
			//记录处于编辑状态的活动ID
			session('EditingTipsID', $tips_id);
			//将活动设置为草稿状态
			if($rs['status'] != 3)M('tips')->where(['id' => $tips_id])->save(['status' => 3, 'is_pass' => 0]);
			//将活动设置为非公开
			M('TipsSub')->where(['tips_id' => $tips_id])->save(['is_public' => 0]);
			//记录活动修改快照信息
			$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
			$this->getEdit($rs);
		}
	}

	/**
	 * @apiName 获取活动编辑首页数据
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"title": "测试接口2测试接口2测试接口2测试接口2测试接口2",
	 * 	"space": "20",
	 * 	"info": "0",
	 * 	"menu": "0",
	 * 	"times": "0",
	 * 	"notice": "0",
	 * 	"is_public": "0",
	 * 	"price": "0.10"
	 * }
	 */
	Public function getEdit($rs = []){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		if(empty($rs)){
			$rs = D('TipsView')->where(['id' => $tips_id])->find();
		}
		if(empty($rs))$this->error('找不到要编辑的活动!');

		//获取标题
		$return['title'] = $rs['title'];

		//获取场地
		if(!empty($rs['space_id']))
			$return['space'] = $rs['space_id'];
		else
			$return['space'] = 0;

		//获取活动信息
		$tag = M('TipsTag')->where(['tips_id' => $tips_id])->find();
		if(!empty($rs['pics_group_id']) && !empty($rs['edges']) && !empty($tag))
			$return['info'] = 1;
		else
			$return['info'] = 0;

		//获取活动菜单
		$menus = M('TipsMenus')->where(['tips_id' => $tips_id])->find();//转换数据表tips_menu->tips_menus(2016-11-17)
		if(!empty($rs['menu_pics_group_id']) && !empty($menus))
			$return['menu'] = 1;
		else
			$return['menu'] = 0;

		//获取时间段
		$times = M('TipsTimes')->where(['tips_id' => $tips_id])->find();
		if(!empty($times))
			$return['times'] = 1;
		else
			$return['times'] = 0;

		//获取须知
		if(!empty($rs['notice']))
			$return['notice'] = 1;
		else
			$return['notice'] = 0;

		//是否公开
		if(!empty($rs['is_public']))
			$return['is_public'] = 1;
		else
			$return['is_public'] = 0;

		//价格
		if(!empty($rs['price']))
			$return['price'] = $rs['price'];
		else
			$return['price'] = '';

		$this->put($return);
	}

	/**
	 * @apiName 保存活动编辑首页数据
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} title: 活动标题(25字以内)
	 * @apiPostParam {int} space_id: 活动场地ID
	 * @apiPostParam {float} price: 活动单价
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0
	 * }
	 */
	Public function saveEdit(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs))$this->error('找不到要编辑的活动!');

		$title = $_POST['title'];
		$space_id = I('post.space_id');
		$price = (float)I('post.price');

		if(empty($title) || abslength($title) > 25)$this->error('标题不能为空或大于25字!');
		$space = M('space')->where(['id' => $space_id, 'member_id' => session('member.id'), 'status' => ['in', '1,2']])->find();
		if(empty($space))$this->error('提交的地址不存在!');
		if(!is_numeric($price) && $price < 0 && $price > 100000){
			$this->error('数值不能为负或大于10W!');
		}
		$price = round($price, 1);

		$data = [
			'title' => $title,
			'space_id' => $space_id,
			'price' => $price
		];
		$citys_id = M('citys')->where(['id' => $space['city_id']])->getField('pid');
		M('tips')->where(['id' => $tips_id])->save($data);
		M('TipsSub')->where(['tips_id' => $tips_id])->save(['citys_id' => $citys_id, 'last_update_time' => time()]);
		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取亮点/标签/图组
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"edges": ["测试亮点1","测试亮点2","测试亮点3"],
	 * 	"pic_group": [
	 * 		{
	 * 			"id": "1000667182",
	 * 			"path": "http://img.m.yami.ren/20160909/41903f17519cc839127aa586886e2ac2710c2745_640x420.jpg"
	 * 		},
	 * 		{
	 * 			"id": "1000667183",
	 * 			"path": "http://img.m.yami.ren/20160909/7652c3e5e2b6362db3b0814383eb8bb38376721d_640x420.jpg"
	 * 		}
	 * 	],
	 * 	"tag_ids": ["1","11","23"],
	 * 	"pic_id" : "1000667182"
	 * }
	 */
	Public function getInformation(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		//读取图组
		$pic_group = [];
		if(!empty($rs['pics_group_id'])){
			$pics = M('pics')->where(['group_id' => $rs['pics_group_id']])->select();
			foreach($pics as $pic){
				$pic_group[] = [
					'id' => $pic['id'],
					'path' => thumb($pic['path'], 1)
				];
			}
		}

		$this->put([
			'edges' => explode(',', $rs['edges']),
			'pic_group' => $pic_group,
			'pic_id' => $rs['pic_id'],
			'tag_ids' => M('TipsTag')->where(['tips_id' => $tips_id])->getField('tag_id', true)
		]);
	}

	/**
	 * @apiName 保存亮点/标签/图组
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} edge[0]: 活动亮点一(15字以内)
	 * @apiPostParam {string} edge[1]: 活动亮点二(15字以内)
	 * @apiPostParam {string} edge[2]: 活动亮点三(15字以内)
	 * @apiPostParam {int} pic_id: 活动封面图ID
	 * @apiPostParam {string} pics: 活动图组图片ID(多张图片逗号隔开)
	 * @apiPostParam {string} tags: 活动标签ID(多个标签ID逗号隔开)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0
	 * }
	 */
	Public function saveInformation(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');

		$edges = $_POST['edge']?:[];
		$pic_id = I('post.pic_id', null);
		$pics = I('post.pics', null);
		$tags = I('post.tags', null);

		foreach($edges as $i => $val){
			if(abslength($val) > 25){
				$this->error('亮点不能超出25字!');
			}
			$edges[$i] = str_replace(',', '，', $val);
		}

		$_data = [
			'edges' => join(',', $edges),
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

			M('pics')->where(['id' => ['IN', $pics], 'member_id' => session('member.id')])->save([
				'group_id' => $group_id,
				'is_used' => 1
			]);
			//将图组ID保存到SUB表中
			$_data['pics_group_id'] = $group_id;
			$pics = explode(',', $pics);
			$data['pic_id'] = $pic_id?:$pics[0];
		}
		M('tips')->where(['id' => $tips_id])->save($data);
		M('TipsSub')->where(['tips_id' => $tips_id])->save($_data);

		//插入标签
		M('TipsTag')->where(['tips_id' => $tips_id])->delete();
		if(!empty($tags)){
			$tags = explode(',', $tags);
			foreach($tags as $tag_id){
				M('TipsTag')->add([
					'tips_id' => $tips_id,
					'tag_id' => $tag_id
				]);
			}
		}
		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());

		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取时间场次
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 		"id": "16103",
	 * 		"phase": "1",
	 * 		"start_time": "1477306800",
	 * 		"end_time": "1477310400",
	 * 		"min_num": "1",
	 * 		"max_num": "22",
	 * 		"stock": "22",
	 * 		"start_buy_time": "1474387200",
	 * 		"stop_buy_time": "1477220400",
	 * 		"limit_num": "0"
	 * 	},
	 * 	{
	 * 		"id": "16104",
	 * 		"phase": "2",
	 * 		"start_time": "1477306800",
	 * 		"end_time": "1477310400",
	 * 		"min_num": "1",
	 * 		"max_num": "22",
	 * 		"stock": "22",
	 * 		"start_buy_time": "1474387200",
	 * 		"stop_buy_time": "1477220400",
	 * 		"limit_num": "3"
	 * 	}
	 * ]
	 */
	Public function getTimes(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$rs = M('Tips')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}
		$times = M('TipsTimes')->field(['id', 'phase', 'start_time', 'end_time', 'min_num', 'max_num', 'stock', 'start_buy_time', 'stop_buy_time', 'limit_num'])->where(['tips_id' => $tips_id])->select();
		$this->put($times);
	}

	/**
	 * @apiName 保存时间场次
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {array} times: 时间数组(格式:times[0][phase]=1&times[0][id]=16049&times[0][start_time]=2016-08-24 19:00&times[0][end_time]=2016-08-24 20:00&times[0][min_num]=1&times[0][max_num]=22&times[0][stock]=12&times[0][start_buy_time]=2016-06-22 01:25&times[0][stop_buy_time]=2016-08-23 19:00&times[0][limit_num]=0)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "错误原因",
	 *     "status": 0
	 * }
	 */
	Public function saveTimes(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$times = I('post.times');
		if(count($times) == 0){
			$this->error('不能没有时间段!');
		}

		\Think\Log::write('活动场次=>'.count($times));
		M()->startTrans();
		$times_ids = [];
		$phase = 1;

		$tips = M('tips')->where(['id' => $tips_id])->find();
		foreach($times as $row){
			if(empty($row['start_time']))$this->error('活动开始时间不能为空!');
			$row['start_time'] = strtotime($row['start_time'] . ':00');
			if(empty($row['end_time']))$this->error('活动结束时间不能为空!');
			$row['end_time'] = strtotime($row['end_time'] . ':00');
			if($row['start_time'] >= $row['end_time'])$this->error('开始时间必须小于结束时间!');
			if(empty($row['start_buy_time']) || $row['start_buy_time'] == '0')$row['start_buy_time'] = 0;
			else $row['start_buy_time'] = strtotime($row['start_buy_time'] . ':00');
			if(empty($row['stop_buy_time']) || $row['stop_buy_time'] == '0')$row['stop_buy_time'] = $row['start_time'] - 24*3600;
			else $row['stop_buy_time'] = strtotime($row['stop_buy_time'] . ':00');

			if($row['min_num'] > $row['max_num'])$this->error('成局人数不能小于接待人数!');
			$row['tips_id'] = $tips_id;

			if(empty($row['phase']))$row['phase'] = $phase++;
			else $phase = $row['phase'];

			if(empty($row['id'])){
				unset($row['id']);
				$row['release_time'] =($tips['is_pass']==1 && $tips['status']==1)?time():'';
				$times_ids[] = M('TipsTimes')->add($row);
			}else{
				$times_ids[] = $row['id'];
				$is_finish = M('TipsTimes')->where(['id' => $row['id']])->getField('is_finish');
				if($is_finish != 0)continue;
				M('TipsTimes')->save($row);
				\Think\Log::write('活动ID=>'.$row['id'].'sql=>'.M('TipsTimes')->getLastSql());
			}
		}

		$rs = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 0, 'ware_id' => $tips_id, 'tips_times_id' => ['NOT IN', join(',', $times_ids)], 'status' => 1, 'act_status' => ['IN', '1,2,3,4,5']])->find();
		if(!empty($rs)){
			M()->rollback();
			$this->error('某时间段已产生订单,不能删除!');
		}
		M('TipsTimes')->where(['tips_id' => $tips_id, 'id' => ['NOT IN', join(',', $times_ids)]])->delete();
		M()->commit();
		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
		$this->success('保存成功!');
	}

	/**
	 * @apiName 获取菜单
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 *{
	 *	"pics": [
	 *		{
	 *			"id": "13555",
	 *			"path": "http://img.m.yami.ren/public/20160331/56fce6a26056d.png"
	 *		},
	 *		{
	 *			"id": "13557",
	 *			"path": "http://img.m.yami.ren/public/20160331/56fceaf098410.jpg"
	 *		},
	 *		{
	 *			"id": "13558",
	 *			"path": "http://img.m.yami.ren/public/20160331/56fd054e19eb5.jpg"
	 *		}
	 *	],
	 *	"model": {
	 *		"中餐菜单": [
	 *			"头道",
	 *			"前菜",
	 *			"汤品",
	 *			"热菜",
	 *			"点心",
	 *			"甜品",
	 *			"其他",
	 *			"Tips"
	 *		],
	 *		"西餐菜单": [
	 *			"头盘",
	 *			"汤",
	 *			"沙拉",
	 *			"主菜",
	 *			"主食",
	 *			"甜品",
	 *			"其他",
	 *			"Tips"
	 *		],
	 *		"其他菜单": [
	 *			"活动流程",
	 *			"主食",
	 *			"点心",
	 *			"饮品",
	 *			"其他",
	 *			"伴手礼",
	 *			"Tips"
	 *		]
	 *	},
	 *	"category": "中餐菜单",
	 *	"menu": [
	 *		{
	 *			"name": "头道",
	 *			"value": [
	 *				"读师范"
	 *			]
	 *		},
	 *		{
	 *			"name": "前菜",
	 *			"value": [
	 *				"是的发生的股份"
	 *			]
	 *		}
	 *	]
	 *}
	 */
	Public function getMenu(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
//		$tips_id = 7725;

		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		//读取图组
		$data['pics'] = M('pics')->field(['id', 'path'])->where(['group_id' => $rs['menu_pics_group_id'], 'member_id' => session('member.id')])->select();
		foreach($data['pics'] as $k => $v){
			$data['pics'][$k]['path'] = thumb($v['path'], 1);
		}

		$data['model'] = C('MENUS');

		//读取菜单
		$menu = M()->query("Select a.name as 'type',b.name as 'name' from ym_tips_menus a join ym_tips_menus b on a.id=b.pid where a.tips_id={$tips_id}");
		$data['category'] = '中餐菜单';
		$data['menu'] = [];
		$menus = [];
		if(!empty($menu[0]['type'])){
			if(strpos($menu[0]['type'],'A@')===false){
				if (strpos($menu[0]['type'], 'B@') === false) {
					$data['category'] = '其他菜单';
				}else{
					$data['category'] = '西餐菜单';
				}
			}else{
				$data['category'] = '中餐菜单';
			}
		}
		foreach($menu as $m){
			$menus[$m['type']][] = $m['name'];
		}
		foreach($menus as $k => $v){
			$data['menu'][] = [
				'name' => str_replace(['A@', 'B@', 'C@'], '', $k),
				'value' => $v
			];
		}

		$this->put($data);
	}

	/**
	 * @apiName 保存菜单图组
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} pics: 图片ID(多张图片用逗号隔开)
	 * @apiPostParam {string} category: 菜单分类(A-中餐 B-西餐 C-其他 [可以为空])
	 * @apiPostParam {string} menu[n][type]: 菜单类型
	 * @apiPostParam {string} menu[n][name]: 菜单名称(多个已逗号隔开)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0
	 * }
	 */
	Public function saveMenu(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$pics = I('post.pics');
		$category = I('post.category');
		$menu = $_POST['menu']?:[];

		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		if(empty($rs)){
			$this->error('非法访问!');
		}

		$_data = ['last_update_time' => time()];
		if(!empty($pics)){
			//判断是否有原先的图组
			if(!empty($rs['menu_pics_group_id'])){
				$group_id = $rs['menu_pics_group_id'];
				M('pics')->where(['group_id' => $group_id])->save(['group_id' => ['exp','null'], 'is_used' => 0]);
			}else{
				//添加新的图组
				$group_id = M('PicsGroup')->add([
					'type' => 0
				]);
			}

			//循环更改图片的图组
			M('pics')->where(['id' => ['IN', $pics], 'member_id' => session('member.id')])->save([
				'group_id' => $group_id,
				'is_used' => 1
			]);
			//将图组ID保存到SUB表中
			$_data['menu_pics_group_id'] = $group_id;
		}

		if(!empty($menu)){
			M('TipsMenus')->where(['tips_id' => $tips_id])->delete();
			$pid = M('TipsMenus')->order('id desc')->getField('id');
			$data = [];
			foreach($menu as $mn){
				//if(empty($mn['name']))continue;
				$pid ++;
				$data[] = [
					'id' => $pid,
					'tips_id' => $tips_id,
//					'name' => str_replace(['A@', 'B@', 'C@','@'], '', $mn['type']),
					'name' => $category.'@'.$mn['type'],
					'pid' => ['exp', 'null']
				];
				$vals = explode(',', $mn['name']);
				foreach($vals as $val){
					$data[] = [
						'id' => ['exp', 'null'],
						'tips_id' => $tips_id,
						'name' => $val,
						'pid' => $pid
					];
				}
			}
			if(!empty($data)){
				M('TipsMenus')->addAll($data);
			}
		}
		M('TipsSub')->where(['tips_id' => $tips_id])->save($_data);
		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
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
	 * 	]
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

		$this->put([
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
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "保存成功！",
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0
	 * }
	 */
	Public function saveNotice(){
		if(!session('?EditingTipsID'))$this->error('检测不到要编辑的活动，请重新选择！');
		$tips_id = session('EditingTipsID');
		$notice_ids = I('post.notice_ids');

		M('TipsSub')->where(['tips_id' => $tips_id])->save([
			'notice' => $notice_ids,
			'last_update_time' => time()
		]);

		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
		$this->success('保存成功!');
	}

	/**
	 * @apiName 保存并发布活动
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} tips_id: 要提交审核的活动ID(非必填)
	 *
	 * @apiPostParam {int} is_apply_public: 是否要申请公开
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "发布成功！",
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0
	 * }
	 */
	Public function submitSave(){
		$tips_id = I('post.tips_id');
		$is_apply_public = I('post.is_apply_public', 0);
		if(empty($tips_id)) {
			if (!session('?EditingTipsID')) $this->error('检测不到要编辑的活动，请重新选择！');
			$tips_id = session('EditingTipsID');
		}
		
		$rs = D('TipsView')->where(['id' => $tips_id])->find();
		//获取标签
		$tags = M('tag')->join('__TIPS_TAG__ on tag_id=__TAG__.id')->where(['tips_id' => $tips_id])->getField('tag_id', true) ?: [];

		//获取时间段
		$times = M('TipsTimes')->field(['id, start_time, end_time, phase,stop_buy_time'])->where(['tips_id' => $tips_id])->select();
		foreach($times as $v){
			if($v['stop_buy_time']>time()-3660){
				M('TipsTimes')->where(['id' => $v['id']])->save(['release_time' =>time()]);
			}
		}

		//获取菜单
		$menu = M('TipsMenus')->where(['tips_id' => $tips_id])->count();//转换数据表tips_menu->tips_menus(2016-11-17)

		if(empty($rs['title']) || abslength($rs['title']) > 25)$this->error('标题不能为空或大于25字!');
		if(preg_match('/(【|】|洋楼|公寓)/', $rs['title']))$this->error('含“洋楼、公寓、【】”等敏感词');
		if(empty($rs['price']) || $rs['price'] == 0)$this->error('活动价格不能为空！');
		if(empty($rs['space_id']))$this->error('活动场地不能为空！');
		if(empty($tags))$this->error('活动标签不能为空！');
		if(empty($times))$this->error('活动时间节点不能为空！');
		if(empty($rs['pic_id']) || empty($rs['pics_group_id']))$this->error('活动主图不能为空！');
		if(empty($rs['menu_pics_group_id']))$this->error('菜单图组不能为空！');
		if(empty($menu))$this->error('菜单不能为空！');
		
		M('tips')->save(['id' => $tips_id, 'status' => 1, 'is_pass' => 1]);

		if($is_apply_public && !$rs['is_public']){
			$this->applyPublic($tips_id, $rs, $times);
		}

		session('EditingTipsID', null);
		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
		$this->success('发布成功！');
	}

	/**
	 * @apiName 申请将活动公开
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} tips_id: 要提交审核的活动ID(非必填)
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "申请公开成功！等待审核……",
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0
	 * }
	 */
	Public function applyPublic($tips_id = 0, $rs = null, $times = null){
		if(!empty($tips_id)){
			if(!empty(I('post.tips_id'))){
				$tips_id = I('post.tips_id');
			}
			$rs = D('TipsView')->where(['id' => $tips_id, 'member_id' => session('member.id'), 'status' => 1])->find();
			\Think\Log::write('申请公开的ID=>'.$rs['id']);
			\Think\Log::write('申请公开的语句为=>'.D('TipsView')->getLastSql());
			if(empty($rs)){
				$this->error('没有找到指定的活动或活动还未上架!');
			}
			if($rs['is_public'] == 1)$this->error('活动已经公开,请勿重复申请!');

			//获取时间段
			$times = M('TipsTimes')->where(['tips_id' => $tips_id])->select();
		}

		foreach($times as $t){
			if($t['start_time'] - $t['stop_buy_time'] < 24 * 3600)
				$this->error('截止报名时间需于活动开始前24小时');
			if($t['end_time'] - $t['start_time'] > 24 * 3600)
				$this->error('活动不可持续超过24小时');
		}

		M('MemberApply')->where(['member_id'=>session('member.id'),'type'=>4,'type_id'=>$tips_id])->delete();
		M('MemberApply')->data(['member_id'=>session('member.id'),'channel' => $this->channel,'type'=>4,'type_id'=>$tips_id,'is_pass'=>0])->add();

		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
		if($tips_id)$this->success('申请公开成功!等待审核...');
	}
	
	/**
	 * @apiName 结束活动的编辑状态（重要）
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": "编辑已结束!",
	 *     "status": 1
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
	 *     "status": 1
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
		M('MemberCollect')->where(['type'=>0 ,'type_id'=>$tips_id])->delete();
		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
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
     *     "status": 1
     * }
     * @apiErrorResponse
     * {
     *     "info": "失败原因",
     *     "status": 0
     * }
     */
    public function copyTips(){
        $tips_id = I('post.tips_id');
        //MainInfo
		$member_id = session('member.id');
        $rs = M('Tips')->where(['id'=>$tips_id,'member_id'=>$member_id])->find();
        if(empty($rs))$this->error('该活动不属于你或活动不存在');
        //获取活动菜单
        $tips_menu = M('TipsMenus')->where(['tips_id'=>$tips_id])->order('id,pid')->select();//转换数据表tips_menu->tips_menus(2016-11-17)
        //获取活动标签
        $tips_tag = M('TipsTag')->where(['tips_id'=>$tips_id])->select();

        $data = D('TipsView')->where(['id'=>$tips_id])->find();

		//复制主图
		$pics_group_id = null;
		$pic_id = null;
		if(!empty($data['pics_group_id'])){
			$pics_group_id = M('PicsGroup')->add(['type' => 0]);
			$pics = M('pics')->field(['member_id', 'type', 'path', 'size', 'is_used'])->where(['group_id' => $data['pics_group_id']])->order('id desc')->select();
			foreach($pics as $pic){
				$pic['group_id'] = $pics_group_id;
				$pic_id = M('pics')->add($pic);
			}
		}

		//复制菜单图
		$menu_pics_group_id = null;
		if(!empty($data['menu_pics_group_id'])){
			$menu_pics_group_id = M('PicsGroup')->add(['type' => 0]);
			$pics = M('pics')->field(['member_id', 'type', 'path', 'size', 'is_used'])->where(['group_id' => $data['menu_pics_group_id']])->select();
			foreach($pics as $pic){
				$pic['group_id'] = $menu_pics_group_id;
				M('pics')->add($pic);
			}
		}

        $_data = [
            'member_id' => $member_id,
            'title' => $data['title'],
			'space_id' => $data['space_id'],
            'pic_id' => $pic_id,
            'category_id' => $data['category_id'],
            'price' =>  $data['price'],
            'is_pass' => 0,
            'status' => 3,
            'edges' => $data['edges'],
            'pics_group_id' => $pics_group_id,
            'menu_pics_group_id' => $menu_pics_group_id,
            'notice' => $data['notice'],
        ];
        $id = M('tips')->add($_data);
        $_data['tips_id'] = $id;
        M('tips_sub')->add($_data);

        //加入菜单表[转换数据表tips_menu->tips_menus(2016-11-17)]
        foreach($tips_menu as $row){
			if(empty($row['pid'])){
				$data_m[$row['id']] = [
					'id'=>$row['id'],
					'name' => $row['name'],
					'pid' => $row['pid'],
				];
			}else{
				foreach($data_m as $k =>$v){
					if($k == $row['pid']){
						$data_m[$row['pid']]['child_menu'][] = [
							'id'=>$row['id'],
							'name' => $row['name'],
							'pid' => $row['pid'],
						];
					}
				}
			}
        }
		foreach($data_m as $key=>$val){
			$data_1 = [
				'id' => ['exp', 'null'],
				'tips_id' => $id,
				'name' => str_replace(['A@', 'B@', 'C@','@'], '', $val['name']),
				'pid' => ['exp', 'null']
			];
			$pid =M('TipsMenus')->add($data_1);
			if(!empty($val['child_menu'])){
				foreach($val['child_menu'] as $m =>$mm){
					$data_2 = [
						'id' => ['exp', 'null'],
						'tips_id' => $id,
						'name' => str_replace(['A@', 'B@', 'C@','@'], '', $mm['name']),
						'pid' => $pid
					];
					M('TipsMenus')->add($data_2);
				}

			}
		}


        //加入标签表
        foreach($tips_tag as $row){
            $data = [
                'tips_id' => $id,
                'tag_id' => $row['tag_id']
			];
            M('TipsTag')->add($data);
        }

		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());
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
		$data = [];
		foreach($rs as $row){
			$count = M('OrderWares')->join('join __ORDER__ a on a.id=order_id')->where(['tips_times_id' => $row['id'], 'type' => 0, 'status' => 1])->count();
			if($count > 0)$row['lock'] = 1;
			else $row['lock'] = 0;
			$data[] = $row;
		}
		$this->put($data);
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
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0
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
		$tips = M('tips')->where(['id' => $tips_id, 'member_id' => session('member.id')])->find();
		if(empty($tips))$this->error('无法操作别人的活动!');

		$rs = M('TipsTimes')->where(['tips_id' => $tips_id])->order('end_time desc')->find();

		if(empty($rs))$this->error('要添加时间段的活动不存在!');
		if($rs['end_time'] >= $start_time)$this->error('开始时间不能小于往期的结束时间!');

		if(empty($start_buy_time))$start_buy_time = 0;
		if(empty($stop_buy_time))$stop_buy_time = $start_time - 24*3600;
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
			'release_time' => ($tips['is_pass']==1 && $tips['status']==1)?time():'',
			'limit_num' => $limit_num,
			'phase' => $rs['phase'] + 1
		]);

		//将活动设置为非公开
		M('TipsSub')->where(['tips_id' => $tips_id])->save(['is_public' => 0]);
		//记录活动修改快照信息
		$this->SaveSnapshotLogs($tips_id,0,$this->framework_id());

		$this->success('保存成功！');
	}

	/**
	 * @apiName 编辑或修改时间段
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {int} times_id: 要编辑的期数ID
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
	 *     "info": '操作成功!',
	 *     "status": 1
	 * }
	 * @apiErrorResponse
	 * {
	 *     "info": "失败原因",
	 *     "status": 0
	 * }
	 */
	Public function modifyTimes(){
		$times_id = I('post.times_id');
		$count = M('OrderWares')->join('join __ORDER__ a on a.id=order_id')->where(['tips_times_id' => $times_id, 'type' => 0, 'status' => 1])->count();
		if($count > 0)$this->error('该期数已经产生订单,无法编辑和删除!');
		$rs = M('TipsTimes')->join('join __TIPS__ a on a.id=tips_id')->where('`ym_tips_times`.id='.$times_id .' AND member_id='. session('member.id'))->find();
		if(empty($rs))$this->error('要编辑的期数不存在!');

		$start_time = I('post.start_time');
		if(empty($start_time)){
			//删除时间段
			M('TipsTimes')->where(['id' => $times_id])->delete();
//			$times_count = M('TipsTimes')->where(['id'=>$times_id])->count();
//			if($times_count==0){
//				M('Tips')->where(['id'=>$rs['id']])->save(['status'=>3]);
//			}
		}else{
			$end_time = I('post.end_time');
			$min_num = I('post.min_num');
			$max_num = I('post.max_num');
			$stock = I('post.stock', $max_num);
			$start_buy_time = I('post.start_buy_time', 0);
			$stop_buy_time = I('post.stop_buy_time', $start_time - 24*3600);
			$limit_num = I('post.limit_num', 0);
			if($start_time >= $end_time){
				$this->error('结束时间不能小于开始时间!');
			}
			if(empty($max_num) || $max_num < $min_num){
				$this->error('最大接待人数不能为空或小于最小成局人数!');
			}
			M('TipsTimes')->where(['id' => $times_id])->save([
				'start_time' => $start_time,
				'end_time' => $end_time,
				'stock' => $stock,
				'min_num' => $min_num,
				'max_num' => $max_num,
				'start_buy_time' => $start_buy_time,
				'stop_buy_time' => $stop_buy_time,
				'release_time' => ($rs['is_pass']==1 && $rs['status']==1)?time():'',
				'limit_num' => $limit_num
			]);

			\Think\Log::write('modifyTimes_活动ID=>'.$times_id.'sql=>'.M('TipsTimes')->getLastSql());
		}

		//将活动设置为非公开
		M('TipsSub')->where(['tips_id' => $rs['tips_id']])->save(['is_public' => 0]);
		//记录活动修改快照信息
		$this->SaveSnapshotLogs($rs['id'],0,$this->framework_id());
		$this->success('操作成功!');
	}

}
