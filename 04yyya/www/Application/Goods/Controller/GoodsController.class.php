<?php

namespace Goods\Controller;
use Goods\Common\MainController;

// @className 实物类商品接口
class GoodsController extends MainController {

	/**
	 * @apiName 获取商品列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} is_public: 是否只显示公开(默认1只显示非公开,0显示全部)
	 *
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 		"id": "50",
	 * 		"title": "黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹",
	 * 		"price": "99.00",
	 * 		"stocks": "96",
	 * 		"path": "http://img.m.yami.ren/20160706/b2db220360d5e5314febf0cde5132900fac2615c.jpg",
	 * 		"catname": "坚果",
	 * 		"shipping": "8.00",
	 * 		"cell_count": "4",
     *      "isCollect": 1 //是收藏
     *      "isPiece": 1 // 是团购
	 * 	},
	 * 	{
	 * 		"id": "51",
	 * 		"title": "黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹",
	 * 		"price": "99.00",
	 * 		"stocks": "96",
	 * 		"path": "http://img.m.yami.ren/20160706/b2db220360d5e5314febf0cde5132900fac2615c.jpg",
	 * 		"catname": "坚果",
	 * 		"shipping": "8.00",
	 * 		"cell_count": "4",
     *      "isCollect": 0, //未收藏
     *      "isPiece": 0 // 非团购
	 * 	}
	 * ]
	 */
	Public function getlist(){
		$where = [];
		if(I('post.is_public', 1)){
			$where['is_public'] = 1;
		}
		$where['status'] = 1;
		$where['is_pass'] = 1;
		$page = I('get.page', 1);

		$rs = D('GoodsView')->where($where)->page($page, 5)->order('A.is_top desc, A.id desc')->select();

		$data = [];
		$curTime = time();
		foreach($rs as $row){
			//查询已售数量
			$cell_count = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 1, 'ware_id' => $row['id'], 'act_status' => ['IN', '1,2,3,4'], 'status' => 1])->count();
			//查询是否收藏
			$isCollect = 0;
			if(session('member')){
				$count = M('MemberCollect')->where(['member_id' => session('member.id'), 'type' => 1, 'type_id' => $row['id']])->count();
				if($count > 0)$isCollect = 1;
			}
			//是否支持拼团
			$isTuan = 0;
//			$tuan = M('GoodsPiece')->where(['goods_id' => $row['id']])->find();
            $tuan = M('Piece')->where(['type_id' => $row['id'], 'type' => 1, 'status' => 1, 'start_time' => ['ELT', $curTime], 'end_time' => ['EGT', $curTime]])->find();
			if(!empty($tuan)){
				$isTuan = 1;
			}
			$data[] = [
				'id' => $row['id'],
				'title' => $row['title'],
				'path' => thumb($row['path'], 1),
				'price' => $row['price'],
				'catname' => $row['catname'],
				'stocks' => $row['stocks'],
				'shipping' => $row['shipping'],
				'cell_count' => $cell_count,
				'isCollect' => $isCollect,
				'isPiece' => $isTuan,
			];
		}
		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 获取商品详情数据
	 * 
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} goods_id: 商品ID
	 * 
	 * @apiSuccessResponse
	 * {
	 * 	"title": "黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹黄浦江大闸蟹",
	 * 	"edge": [
	 * 		"黄浦江大闸蟹",
	 * 		"黄浦江大闸蟹",
	 * 		"黄浦江大闸蟹"
	 * 	],
	 * 	"price": "99.00",
	 * 	"mainpic": "http://img.m.yami.ren/20160706/b2db220360d5e5314febf0cde5132900fac2615c.jpg",
	 * 	"headpic": "http://img.m.yami.ren/20160603/8d3aa82e4760ce7cfaa9d017173a0d7af6332bbf.jpg",
	 * 	"nickname": "弦霄",
	 * 	"introduce": "输入您的个人简介 (最多225字)",
	 * 	"stocks": "96",
	 * 	"shipping": "8.00",
	 * 	"stars": 5,
	 * 	"isfollow": 0,
	 * 	"isCollect": 0,
     *  "status": 1, // 状态
	 * 	"tags": [
	 * 		"测试4",
	 * 		"sp",
	 * 		"drink"
	 * 	],
	 * 	"tips": [
	 * 		{
	 * 			"id": "3305",
	 * 			"title": "La France Spécial--法国料理私厨课堂",
	 * 			"path": "http://img.m.yami.ren/20160614/105aa88b9c55e7577c648c88ce3c243b94422da0.jpg",
	 * 			"price": "568.00"
	 * 		},
	 * 		{
	 * 			"id": "3313",
	 * 			"title": "Cubic Chic--时尚几何零钱包",
	 * 			"path": "http://img.m.yami.ren/20160615/d5c7f0830dfcc1ed75b652c2c19f03661052788f.jpg",
	 * 			"price": "288.00"
	 * 		},
	 * 		{
	 * 			"id": "3317",
	 * 			"title": "#测试#饭局K11测试",
	 * 			"path": "http://img.m.yami.ren/20160617/60fafdc1554bea9847fb1e192721cb0a1723ea9d.jpg",
	 * 			"price": "100.00"
	 * 		},
	 * 		{
	 * 			"id": "3318",
	 * 			"title": "#测试#沙龙K11测试",
	 * 			"path": "http://img.m.yami.ren/20160617/d5fe6df53b5d112a730dfc937178eb342a35b7af.jpg",
	 * 			"price": "50.00"
	 * 		}
	 * 	],
	 * 	"pics_group": [
	 * 		"http://img.m.yami.ren/20160706/e9f78c7b19ecc97fe63e6cf23ab455190e457ac0.jpg",
	 * 		"http://img.m.yami.ren/20160706/3efc99a52f85811952aa47e991b9d94ffdd953b8.jpg",
	 * 		"http://img.m.yami.ren/20160706/60e316e74ce85353415525cba9bb52630218c7fa.jpg",
	 * 		"http://img.m.yami.ren/20160706/cc22f2f0b75aed9cdb038300c6a8ad4220d6e4cf.jpg",
	 * 		"http://img.m.yami.ren/20160706/b2db220360d5e5314febf0cde5132900fac2615c.jpg",
	 * 		"http://img.m.yami.ren/20160706/7d93411f74b3e2eab9314413501d9e06e9570cb7.jpg"
	 * 	],
	 * 	"attrs": [
	 * 		{
	 * 			"name": "品牌",
	 * 			"value": "吖咪牌"
	 * 		},
	 * 		{
	 * 			"name": "产地",
	 * 			"value": "黄浦江"
	 * 		},
	 * 		{
	 * 			"name": "规格",
	 * 			"value": "3KG"
	 * 		},
	 * 		{
	 * 			"name": "赏味期限",
	 * 			"value": "30天"
	 * 		},
	 * 		{
	 * 			"name": "包装",
	 * 			"value": "精美礼品盒"
	 * 		},
	 * 		{
	 * 			"name": "贮存",
	 * 			"value": "零度冷冻"
	 * 		},
	 * 		{
	 * 			"name": "品牌",
	 * 			"value": "吖咪牌"
	 * 		},
	 * 		{
	 * 			"name": "产地",
	 * 			"value": "黄浦江"
	 * 		},
	 * 		{
	 * 			"name": "规格",
	 * 			"value": "3KG"
	 * 		},
	 * 	],
	 * 	"notice": [
	 * 		"成功报名后，请关注官方客服微信号吖咪酱：yami194。",
	 * 		"已付费的活动名额，不接受退款，不改期，可自行转让名额。",
	 * 		"无特别说明的情况下，活动费用为单人单次费用，如携带他人参加需购买相应份数。",
	 * 		"本活动不接受临时加入和现场付款，参加请在吖咪平台上购买。",
	 * 		"如活动未达到最低成局人数，吖咪会进行退款。",
	 * 		"如需开具发票，请咨询活动方。",
	 * 		"活动会准时开始，请不要迟到哦。",
	 * 		"本活动为亲子活动，可带一名小朋友参加。"
	 * 	],
	 * 	"comment": [
	 * 		{
	 * 			"id": "76",
	 * 			"stars": "5",
	 * 			"type": "1",
	 * 			"type_id": "50",
	 * 			"content": "偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的，偶觉得挺不错的",
	 * 			"pics_group_id": null,
	 * 			"datetime": "2016-07-12 11:41:32",
	 * 			"status": "1",
	 * 			"nickname": "弦霄",
	 * 			"head_path": "http://img.m.yami.ren/20160603/8d3aa82e4760ce7cfaa9d017173a0d7af6332bbf.jpg",
	 * 			"pics": []
	 * 		},
	 * 		{
	 * 			"id": "75",
	 * 			"stars": "5",
	 * 			"type": "1",
	 * 			"type_id": "50",
	 * 			"content": "这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~这是什么鬼！~~~",
	 * 			"pics_group_id": null,
	 * 			"datetime": "2016-07-12 11:43:39",
	 * 			"status": "1",
	 * 			"nickname": "弦霄",
	 * 			"head_path": "http://img.m.yami.ren/20160603/8d3aa82e4760ce7cfaa9d017173a0d7af6332bbf.jpg",
	 * 			"pics": []
	 * 		}
	 * 	]
	 * }
	 */
    public function getDetail(){
    	$goods_id = I('post.goods_id');
    	if(empty($goods_id))$this->error('非法访问！');
		//查询商品数据
    	$rs = D('GoodsView')->where(['id' => $goods_id, 'status' => ['NEQ', 0]])->find();

    	if (empty($rs)) {
    	    $this->error('该商品已删除');
        }

		//查询达人数据
		$m = new \Daren\Model\DarenViewModel;
		$daren = $m->where(['member_id' => $rs['member_id']])->find();

		$follow = $isCollect = 0;
		if(session('?member')){
			$member_id = session('member.id');
			//查询是否已关注此达人
			$memberFollow = M('MemberFollow')->where(['member_id' => $member_id, 'follow_id' => $rs['member_id']])->find();
			if(!empty($memberFollow))$follow = 1;

			//查询是否已收藏活动
			$collect = M('MemberCollect')->where(['member_id' => $member_id, 'type' => 1, 'type_id' => $goods_id])->find();
			if(!empty($collect))$isCollect = 1;
		}

		//查询评分
		$stars = M('MemberComment')->field('count(stars) as count, sum(stars) as sum')->where(['type' => 1, 'type_id' => $goods_id])->find();

		$daren['introduce'] = trim(strip_tags($daren['member_introduce']));

		//30天内销量
		//$month_selled = D('OrderWaresView')->where(['type' => 1, 'ware_id' => $goods_id, "datetime" => ['GT', date("Y-m-d H:i:s",strtotime('-30 day'))], 'act_status'=> ['IN', '0,1,2,3,4'], 'status' => 1])->count();
		//历史销量
		$selled = D('OrderWaresView')->where(['type' => 1, 'ware_id' => $goods_id, 'act_status'=> ['IN', '1,2,3,4'], 'status' => 1])->count();

		$data = [
			'title' => $rs['title'],
			'edge' => explode('[^|^]', $rs['edge']),
			'price' => $rs['price'],
			'mainpic' => thumb($rs['path'], 1),
			'headpic' => thumb($daren['path'], 2),
			'member_id' => $rs['member_id'],
			'nickname' => $daren['nickname'],
			'introduce' => $daren['introduce'],
			'stocks' => $rs['stocks'],
			//'month_selled' => $month_selled,
			'selled' => $selled,
			'shipping' => $rs['shipping'],
			'content' => $rs['content'],
			'stars' => ceil($stars['sum'] / $stars['count']),
			'isfollow' => $follow,
			'isCollect' => $isCollect,
            'status' => $rs['status']
		];
		$curTime = time();
        $piece = M('Piece')->where(['type_id' => $goods_id, 'type' => 1, 'status' => 1, 'start_time' => ['ELT', $curTime], 'end_time' => ['EGT', $curTime]])->find();
		if(!empty($piece)){
			$data['piece'] = $piece;
			if ($piece['is_public'] == 1){
				$data['piece_group'] = $this->getPieceGroup($goods_id);
			}else {
				$no_data = [];
		        $no_data['list'] = [];
				$no_data['pieces_people_count'] = 0;
				$data['piece_group'] = $no_data;
			}
			
		}
		
		//获取商品标签
		$data['tags'] = D('GoodstagView')->where(['goods_id' => $goods_id])->getField('name', true);
		if(!empty($data['tags'])){
			//获取关联的活动
//			$tips_ids = D('TipstagView')->where(['name' => ['IN', join(',', $data['tags'])]])->order('tips_id desc')->getField('tips_id', true);
//			$data['tips'] = [];
//			if(!empty($tips_ids)){
//				$data['tips'] = M('tips')->field(['ym_tips.id', 'title', 'path', 'price'])->join('__PICS__ a on a.id=pic_id')->where(['ym_tips.id' => ['IN', join(',', $tips_ids), 'ym_tips.status' => 1, 'ym_tips.is_pass' => 1]])->limit(4)->select();
//				foreach($data['tips'] as $key => $row){
//					$data['tips'][$key]['path'] = thumb($row['path'], 1);
//				}
//			}
		}

		$data['other'] = D('GoodsView')->where(['id' => ['NEQ', $goods_id], 'status' => 1, 'is_public' => 1, 'is_pass' => 1])->page(1, 4)->order('id desc')->select();
		foreach ($data['other'] as $key => $row) {
            $tuan = M('Piece')->where(['type_id' => $row['id'], 'type' => 1, 'status'=> 1, 'start_time' => ['ELT', $curTime], 'end_time' => ['EGT', $curTime]])->find();
            if(!empty($tuan)){
                $isTuan = 1;
                $data['other'][$key]['piece_price'] = $tuan['price'];
                $data['other'][$key]['reward'] = $tuan['reward'];
            } else {
                $isTuan = 0;
            }
            $count = D('OrderWaresView')->where(['A.type' => 1, 'A.ware_id' => $row['id'], 'B.act_status' => ['IN', [1,2,3,4]], 'B.status' => 1, 'order_pid'=>['EXP', 'IS NULL']])->count();
		    $data['other'][$key]['path'] = thumb($row['path'],1);
            $data['other'][$key]['isTuan'] = $isTuan;
            $data['other'][$key]['count'] = $count;
        }

		//获取商品图组
		$pics = M('pics')->where(['group_id'=>$rs['pics_group_id']])->getField('path', true);
		$data['pics_group'] = [];
		foreach($pics as $pic){
			$data['pics_group'][] = thumb($pic, 1);
		}

		//获取商品规格
		$attrs = M('GoodsAttr')->field(['name', 'value'])->where(['goods_id' => $goods_id])->select();
		$data['attrs'] = [];
		foreach($attrs as $attr){
			if(!empty($attr['value'])){
				$data['attrs'][] = [
					'name' => substr($attr['name'], 2),
					'value' => $attr['value']
				];
			}
		}

		//获取商品提示
		if(!empty($rs['notice'])){
			$data['notice'] = M('GoodsNotice')->where("`status`=1 or (`status`=2 and `id` in (".$rs['notice']."))")->getField('context', true);
		}else{
			$data['notice'] = M('GoodsNotice')->where("`status`=1")->getField('context', true);
		}

		if (!empty($rs['add_notice'])) {
            $addNotice = explode(',', $rs['add_notice']);
            $data['notice'] = array_merge($data['notice'], $addNotice);
        }

		//获取2条评论
		$data['comment'] = D('CommentView')->where(['type' => 1, 'goods_member_id' => $rs['member_id'], 'status' => 1, 'pid' => ['EXP', 'IS NULL']])->order('id desc')->limit(2)->select();
		if(!empty($data['comment'])){
			$ids = [];
			foreach($data['comment'] as $row){
				if(!empty($row['pics_group_id'])){
					$ids[] = $row['pics_group_id'];
				}
			}
			$pics = M('pics')->where(['group_id' => ['IN', join(',', $ids)]])->select();
			foreach($data['comment'] as $key => $row){
				$data['comment'][$key]['head_path'] = thumb($row['head_path'], 2);
				$_pics = [];
				foreach($pics as $pic){
					if($row['pics_group_id'] == $pic['group_id']){
						$_pics[] = thumb($pic['path'], 5);
					}
				}
				$data['comment'][$key]['pics'] = $_pics;
			}
		}

		if (!empty($data['content'])) {
            $data['content'] = preg_replace('/\[img(.*?)\/\]/', '<img$1>', $data['content']);
        }

		//获取促销信息
//		$marketing = M('marketing')->where(['type' => 1, 'type_id' => $goods_id, 'end_time' => ['GT', time()], 'num' => ['GT', 0]])->order('start_time,price')->find();
//		if(!empty($marketing)){
//			$data['marketing'] = $marketing;
//		}
		
		$this->ajaxReturn($data);
    }

	/**
	 * @apiName 获取某商品详情
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} goods_id: 活动ID
	 *
	 * @apiSuccessResponse
	 * {content:"xxxxxx"}
	 */
	Public function getContent(){
		$goods_id = I('post.goods_id');
		$content = M('GoodsSub')->where(['goods_id' => $goods_id])->getField('content');
		if(empty($content)){
			$this->error('商品不存在或没有详情描述!');
		}
		$content = preg_replace('/\[img(.*?)\/\]/', '<img$1>', $content);
		$this->ajaxReturn(['content' => $content]);
	}

	/**
	 * @apiName 拼团广场
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} goods_id: 活动ID
	 *
	 * @apiSuccessResponse
	 * {
    "list": [
        {
            "id": "73",
            "member_id": "11605",
            "end_time": "1528275248",
            "p_act_status": "1",
            "nickname": "kk",
            "headpath": "http://thirdwx.qlogo.cn/mmopen/vi_32/AzBl17RfP6zvLWx1EaRu4xevJZRbZt7u8j7j8kJtRzAxAMJFiakABIET6Zs45miaY6srkejFqD8KYEMI5kch4EoA/132",
            "piece_id": "22",
            "piece_type": "1",
            "piece_type_id": "53",
            "piece_type_times_id": null,
            "piece_phase": "1",
            "piece_price": "0.01",
            "piece_count": "3",
            "is_cap": "1",
            "piece_limit_time": "6",
            "piece_status": "1",
            "title": "#测试#来自宝岛的私房美味[张家食堂]东港东西小卷片",
            "price": "0.01",
            "type_path": "20171206/49758cc620646c6fb8cf9c868818769cbdd173a6.jpg",
            "tips_stop_buy_time": null,
            "remain": 2, //剩余多少人
            "status": "1",
            "act_status": "1"
        },
        {
            "id": "74",
            "member_id": "11008",
            "end_time": "1528286891",
            "p_act_status": "1",
            "nickname": "余华~~杰",
            "headpath": "http://wx.qlogo.cn/mmopen/hRkOoB5ZTmLVnsOWlrenlSbadcWCZSNj07uiaMSGwT3cTzctstIDNSvR54jXlT1DeNKL3tXo4zibia6CNlhzcRjECjnP86QbS4q/0",
            "piece_id": "22",
            "piece_type": "1",
            "piece_type_id": "53",
            "piece_type_times_id": null,
            "piece_phase": "1",
            "piece_price": "0.01",
            "piece_count": "3",
            "is_cap": "1",
            "piece_limit_time": "6",
            "piece_status": "1",
            "title": "#测试#来自宝岛的私房美味[张家食堂]东港东西小卷片",
            "price": "0.01",
            "type_path": "20171206/49758cc620646c6fb8cf9c868818769cbdd173a6.jpg",
            "tips_stop_buy_time": null,
            "remain": 2,
            "status": "1",
            "act_status": "1"
        },
        {
            "id": "75",
            "member_id": "269197",
            "end_time": "1528288494",
            "p_act_status": "1",
            "nickname": "手机号_137****8688",
            "headpath": null,
            "piece_id": "22",
            "piece_type": "1",
            "piece_type_id": "53",
            "piece_type_times_id": null,
            "piece_phase": "1",
            "piece_price": "0.01",
            "piece_count": "3",
            "is_cap": "1",
            "piece_limit_time": "6",
            "piece_status": "1",
            "title": "#测试#来自宝岛的私房美味[张家食堂]东港东西小卷片",
            "price": "0.01",
            "type_path": "20171206/49758cc620646c6fb8cf9c868818769cbdd173a6.jpg",
            "tips_stop_buy_time": null,
            "remain": 2,
            "status": "1",
            "act_status": "1"
        }
    ],
    "pieces_people_count": "3"
}
	 */
	public function getOtherPiece(){
		$goods_id = I('post.goods_id','');
        $data = $this->getPieceGroup($goods_id);
        $this->ajaxReturn($data);
	}

	private function getPieceGroup($goods_id) {
		$model = D('Member/MemberPieceGoodsView');
        $where['piece_type_id'] = $goods_id;
        $where['end_time'] = ['GT', time()];
        $where['piece_status'] = ['EQ', 1];
        $where['p_act_status'] = ['IN', [1,2]];
        $where['is_cap'] = 1;
        $where['p_status'] = 1;
        $pieces_count = $model->where($where)->count();
        $pieces = $model->where($where)->order('end_time asc')->limit(5)->select();
        $list = array();
        foreach ($pieces as $key => $value) {
            # code...
            $buyer_count = $this->getBuyerCountByPieceOriginatorId($pieces[$key]['id']);
            $pieces[$key]['remain'] = ((int)$pieces[$key]['piece_count']) - $buyer_count;
            $pieces[$key]['status'] = $pieces[$key]['p_status'];
			$pieces[$key]['act_status'] = $pieces[$key]['p_act_status'];
			
			$joiner = D('Member/PieceOrderView')->where(['piece_originator_id'=>$value['id'],'status'=> ['IN', [1,2]],'act_status'=>['IN',[1,2,3,4]]])->group('B.id')->order('A.id asc')->select();

			$pieces[$key]['joiner'] = $joiner;

			foreach($pieces[$key]['joiner'] as $key1 => $val){
				if($key1 == 0){
					$pieces[$key]['joiner'][$key1]['is_colonel'] = 1;
				}else{
					$pieces[$key]['joiner'][$key1]['is_colonel'] = 0;
				}
				if(session('member.id') != $val['member_id']){
					$pieces[$key]['joiner'][$key1]['id'] = '';
				}
				$pieces[$key]['joiner'][$key1]['joiner_path'] = thumb($val['joiner_path']);
				// $pieces[$key]['joiner'][$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
	
				if ($pieces[$key]['joiner'][$key1]['member_id'] === $member_id) {
					// 如果参与者中，用户已经购买过，则不能再进行购买此团
					$pieces[$key]['is_buy'] = 0;
				}
			}

			// $pieces[$key]['nickname'] = $this->thumbName($pieces[$key]['nickname']);
        	unset($pieces[$key]['p_status']);
        	unset($$pieces[$key]['p_act_status']);
            //if($buyer_count>0) {
            $list[] = $pieces[$key];
            //}
		}
		$data = [];
        $data['list'] = $list;
		$data['pieces_people_count'] = $pieces_count;
		
		return $data;
	}
	
	private function thumbName($name) {
		$len = abslength($name);

		if ($len <= 3) {
			return "***";
		} else {
			return  $name[0] . "***" . $name[$len - 1];
		}
	}

    private function getBuyerCountByPieceOriginatorId($piece_originator_id){
        $buyer = D('Member/PieceOrderView')->where(['piece_originator_id'=>$piece_originator_id,'status'=> ['IN', [1,2]],'act_status'=>['IN',[1,2,3,4]]])->group('B.id')->select();
        return count($buyer);

    }

}