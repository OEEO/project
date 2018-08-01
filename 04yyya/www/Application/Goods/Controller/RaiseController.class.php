<?php

namespace Goods\Controller;
use Goods\Common\MainController;
use Order\Controller\IndexController;

//@className 众筹类商品接口
class RaiseController extends MainController {

    private function getJoinGoodsOrder($gids) {
        $selled = D('OrderView')->where(['type' => 1, 'ware_id' => ['in', join(',', $gids)], 'act_status'=> ['IN', '1,2,3,4'], 'status' => 1])->select();
        $data['total_selled'] = 0;
        $data['total_count'] = count($selled);
        foreach ($selled as $item) {
            $data['total_selled'] += $item['price'];
        }
        return $data;
    }

	/**
	 * @apiName 获取众筹列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} page: 分页编号，默认1
	 * @apiGetParam {int} city_id: 城市编号，默认没有（全部城市）
	 *
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 		"id": "1",
	 * 		"title": "她在3毫米的甜蜜里，放了486层酥皮",
	 * 		"total": "2000",
	 * 		"totaled": "1800", // 没有时，为空字符串
	 * 		"status": "1",
	 * 		"category_id": "",
	 *      "city_id": "224",
	 *      "city_name": "广州",
	 * 		"nickname": "xxxxxx",
	 * 		"datetime": "2016-10-07 18:42:48",
	 * 		"start_time": "1475836958", //大于当前时间,状态为未开始
	 * 		"end_time": "1477836958", //小于当前时间,状态为已完结
	 * 		"path": "http://youfanapp.b0.upaiyun.com/host_show/0c3abf0eb47153a260ce46041384895a.png",
	 * 		"content": "xxxxxxxxxx"
	 * 		"introduction": "xxxxxxxxxx"//简介
	 * 	}
	 * ]
	 */
	public function getlist(){
		$page = I('get.page', 1);
		$city = I('get.city_id');

		if ($city) {
			$city = intval($city);
			$where = ['status' => 1,'is_public'=>1,'city_id'=>$city];
		} else {
			$where = ['status' => 1,'is_public'=>1];
		}
		

//		$rs = D('RaiseView')->where($where)->order('id desc')->page($page, 5)->group('id')->select();
		$rs = D('RaiseView')->where($where)->order('id desc')->page($page, 10)->group('id')->select();

		$data = [];
		foreach($rs as $row){ 
			$row['path'] = thumb($row['path'], 1);
			$row['content'] = utf8_substr(preg_replace(['/\&\w+?;/', '/\[img.+?\]/'], '', strip_tags($row['content'])), 0, 100);

            $raise_id = $row['id'];

            if($row['end_time']>= time()){
                $row['totaled'] = 0;
                $row['sum'] = 0;
                $row['buyer_num'] = 0;
                $rs_arr = D('RaiseOrderWaresView')->where(['A.type' => 2, 'A.ware_id' => $raise_id, 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->group('A.id')->select();
                foreach($rs_arr as $row_a){
                    $row['totaled'] += $row_a['raise_times_price'];
                    $row['buyer_num'] ++;
                }

                if ($row['id'] == 51) {
                    $row['totaled'] += 41124;
                    $row['buyer_num'] += 298;
                }
            }

            $gids = M('raise_goods')->where(['rid'=>$row['id']])->getField('gid', true);
            if (!empty($gids)) {
                $selled = $this->getJoinGoodsOrder($gids);
                
                // foreach($selled as $item) {
                //     $row['totaled'] += $item['price'];
                //     $row['buyer_num'] += 1;
                // }
                if (!empty($selled)) {
                    $row['totaled'] += $selled['total_selled'];
                    $row['buyer_num'] += $selled['total_count'];
                }
            }

            $data[] = $row;
		}
		unset($row);
		$this->put($data);
	}

	public function getNew() {
		$page = I('get.page', 1);
		/*
		$city = I('get.city_id');

		if ($city) {
			$city = intval($city);
			$where = ['status' => 1,'is_public'=>1,'city_id'=>$city];
		} else {
			$where = ['status' => 1,'is_public'=>1];
		}
		*/
		$where = ['id' => '51'];

		$rs = D('RaiseView')->where($where)->select();

		$data = [];
		foreach($rs as $row){ 
			$row['path'] = thumb($row['path'], 1);
			$row['content'] = utf8_substr(preg_replace(['/\&\w+?;/', '/\[img.+?\]/'], '', strip_tags($row['content'])), 0, 100);

            $raise_id = $row['id'];

            if($row['end_time']>= time()){
                $row['totaled'] = 0;
                $row['sum'] = 0;
                $row['buyer_num'] = 0;
                $rs_arr = D('RaiseOrderWaresView')->where(['A.type' => 2, 'A.ware_id' => $raise_id, 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->group('A.id')->select();
                foreach($rs_arr as $row_a){
                    $row['totaled'] += $row_a['raise_times_price'];
                    $row['buyer_num'] ++;
                }

                if ($row['id'] == 51) {
                    $row['totaled'] += 41124;
                    $row['buyer_num'] += 298;
                } elseif ($row['id'] == 94) {
                    $row['totaled'] += 998;
                    $row['buyer_num'] += 2;
                }
            }



            $data[] = $row;
		}
		unset($row);
		$this->put($data);
	}

	/**
	 * @apiName 获取众筹详情数据
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} raise_id: 活动ID
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"id": "1",
	 * 	"title": "她在3毫米的甜蜜里，放了486层酥皮",
	 * 	"content": "",
	 * 	"path": "http://youfanapp.b0.upaiyun.com/host_show/0c3abf0eb47153a260ce46041384895a.png",
	 * 	"total": "2000", //目标金额
	 * 	"catname": "",
	 * 	"inviter": [
	 * 	{
	 * 		"nickname":"紫嫣"
	 * 		"path":"http://img.yummy194.cn/2...wZmNmNTg4MTg4NDAwZD.jpg"
	 * 	}
	 * 	],
	 * 	"start_time": "1475836958",
	 * 	"end_time": "1477836958",
	 * 	"totaled": "0", //众筹总额
	 * 	"sum": "0", //众筹总人数
	 *  "collected_count": 0, // 关注人数，也就是开启提醒的人数
	 * 	"times": [
	 * 		{
	 * 			"times_id": "1",
	 * 			"title": "甜蜜的小确幸",
	 * 			"content": "感谢您对深吻时刻的支持，我们将在此档中送出甜蜜的幸运！您将有机会获得价值299元的6寸蛋糕一个，口味不限。\r\r抽奖规则1：10人及以内抽取1人，20人及以内抽取2人，以此类推；\r\r抽奖规则2：我们会在项目结束7天内，将此档的支持者单独拉群（微信群），以抢红包的形式抽取。",
	 * 			"price": "2",
	 * 			"prepay": "0",//预付金额
	 * 			"stock": "0",//-1不限制库存，
	 * 			"quota": "0",//-1不限制库存，
	 * 			"count": "0"
     *          "limit_num": 2, // 没人限购2份
     *          "limit_buy_times": 0, // 这个人还可以购买多少次
	 * 			"is_address": "0"//填写地址 (0-不需要，1-需要)
	 * 			"is_buy": "0"//是否可以购买 (0-不可以，1-可以)
	 * 			"send_day": "0",//项目结束发货天数
     *          "is_free": "0", // 是否免费, 0——非免费，1——免费
     *          "type": "0", // 类型， 0——普通付费，1——抽奖
	 * 		},
	 * 		{
	 * 			"times_id": "2",
	 * 			"title": "午后的闲暇",
	 * 			"content": "感谢您对深吻时刻的支持，您将获得：\r价值199元法式甜品套餐，您可以选择迷恋法兰西+红茶套餐或者咖啡两杯+甜品套餐8件（宫廷松饼 焦糖坚果塔 马卡龙 吞拿鱼泡芙 芝士棒）。\r限10月31号前使用。",
	 * 			"price": "99",
	 * 			"prepay": "0",//预付金额
	 * 			"stock": "0",//-1不限制库存，
	 * 			"quota": "0",//-1不限制库存，
	 * 			"count": "0"
     *          "limit_num": 2, // 没人限购2份
     *          "limit_buy_times": 0, // 这个人还可以购买多少次
	 * 			"is_address": "0"//填写地址 (0-不需要，1-需要)
	 * 			"is_buy": "0"//是否可以购买 (0-不可以，1-可以)
	 * 			"send_day": "0", //项目结束发货天数
     *          "is_free": "0", // 是否免费, 0——非免费，1——免费
     *          "type": "0", // 类型， 0——普通付费，1——抽奖
	 * 		},
	 * 		{
	 * 			"times_id": "3",
	 * 			"title": "晚间的盛典",
	 * 			"content": "感谢您对深吻时刻的支持，您将获得：\r价值199元法式甜品套餐，您可以选择迷恋法兰西+红茶套餐或者咖啡两杯+甜品套餐8件（宫廷松饼 焦糖坚果塔 马卡龙 吞拿鱼泡芙 芝士棒）。\r限10月31号前使用。",
	 * 			"price": "998",
	 * 			"prepay": "0.1",//预付金额
	 * 			"stock": "0",//-1不限制库存，
	 * 			"quota": "0",//-1不限制库存，
	 * 			"count": "0"
     *          "limit_num": 2, // 没人限购2份
     *          "limit_buy_times": 0, // 这个人还可以购买多少次
	 * 			"is_address": "0"//填写地址 (0-不需要，1-需要)
	 * 			"is_buy": "0"//是否可以购买 (0-不可以，1-可以)
	 * 			"send_day": "0", //项目结束发货天数
     *          "is_free": "0", // 是否免费, 0——非免费，1——免费
     *          "type": "0", // 类型， 0——普通付费，1——抽奖
     *          "is_display_remain_time" : 1, //是否显示剩余时间（开放中的时候）
     *          "start_time": 1543433343, //档位开放的开始时间戳 （null为无限制）
     *			"end_time": 1556556565 //档位开放的结束时间戳 （null为无限制）
	 * 		}
	 * 	]
	 * }
	 */
	public function getDetail(){
		$raise_id = I('post.raise_id');
		$t_statu = I('post.t_statu');


		if(empty($raise_id))$this->error('非法访问！');
		//查询活动数据
		$rs = D('RaiseView')->where(['id' => $raise_id])->find();
		if(empty($rs))$this->error('众筹不存在!');

		$collecedCount = 0; // 已关注的人数
		$isCollect = 0;
		$isPrivilege = 0;
		$isReminder = 0;
		if(session('?member')){
			$member_id = session('member.id');
			//查询是否已收藏活动
			$collect = M('MemberCollect')->where(['member_id' => $member_id, 'type' => 2, 'type_id' => $raise_id])->find();
			if(!empty($collect))$isCollect = 1;

			//获取特权权限
			$privilege_ids = M('Privilege')->where(['type' => 2, 'type_id' => $raise_id])->getField('id', true);
			$privilege_idstr = $privilege_ids?join(',', $privilege_ids):'';
			$member_privilege = M('MemberPrivilege')->where(['member_id'=>$member_id,'privilege_id' => ['in',$privilege_idstr ]])->find();
			$member_privilege_id_map=null;
			if(!empty($member_privilege) && $member_privilege['end_time']>time()){
				$isPrivilege = 1;
				// session('privilege', [
				// 	'member_privilege_id' => $member_privilege['id'],
				// 	'privilege_id' => $member_privilege['privilege_id'],
				// 	'member_privilege' => $member_id
				// ]);
				$member_privileges = D('MemberPrivilegeView')->where(['A.member_id'=>$member_id,'B.type' => 2, 'B.type_id' => $raise_id])->select();
				$member_privilege_id_map = array();
				foreach($member_privileges as $mp){
					$member_privilege_id_map[$mp['tips_times_id'].'']=$mp['member_privilege_id'];
				}
			}

			//获取是否提醒功能
			$re_count = M('Message')->join('__MEMBER_MESSAGE__ AS A ON A.message_id = __MESSAGE__.id ')->where(['A.member_id'=>$member_id,'type'=>7,'code_type'=>'SMS_48040327','type_id'=>$raise_id])->count();
			if($re_count>0)$isReminder = 1;
		}

		// 设置已关注的人数(也就是开启提醒的人数)
		$collecedCount = M('Message')->join('__MEMBER_MESSAGE__ AS A ON A.message_id = __MESSAGE__.id ')->where(['type'=>7,'code_type'=>'SMS_48040327','type_id'=>$raise_id])->count();

		//问答数量
		$ask_count = M('feedback')->where(['type' => 3, 'type_id' => $raise_id, 'answer' => ['exp', 'is not null']])->count();

		//城市
		$city_id = M('raise')->where(['id' => $raise_id])->getField('city_id');
		$city_name = M('citys')->where(['id' => $city_id])->getField('name');

		$data = [
			'id' => $raise_id,
			'title' => $rs['title'],
			'content' => preg_replace('/\[img(.*?)\/\]/', '<img$1>', $rs['content']),
			'content1' => preg_replace('/\[img(.*?)\/\]/', '<img$1>', $rs['content1']),
			'content2' => preg_replace('/\[img(.*?)\/\]/', '<img$1>', $rs['content2']),
			'content3' => preg_replace('/\[img(.*?)\/\]/', '<img$1>', $rs['content3']),
			'content4' => preg_replace('/\[img(.*?)\/\]/', '<img$1>', $rs['content4']),
			'content5' => preg_replace('/\[img(.*?)\/\]/', '<img$1>', $rs['content5']),
			'title1' => $rs['title1'],
			'title2' => $rs['title2'],
			'title3' => $rs['title3'],
			'title4' => $rs['title4'],
			'title5' => $rs['title5'],
			'is_preview' => $rs['is_preview'],
			'introduction' => utf8_substr(preg_replace(['/\&\w+?;/', '/\[img.+?\]/'], '', strip_tags($rs['introduction'])), 0, 100),
			'path' => thumb($rs['path'], 1),
			'nickname' => $rs['nickname'],
			'headpath' => thumb($rs['headpath'], 2),
			'total' => $rs['total'],
			'catname' => $rs['catname'],
			'start_time' => $rs['start_time'],
			'end_time' => $rs['end_time'],
			'isCollect' => $isCollect,
			'isPrivilege' => $isPrivilege,
			'isReminder' => $isReminder,
			'ask_count' => $ask_count,
			'video_url' => $rs['video_url'],
			'tips_privilege'=>$member_privilege_id_map,
			'city_name'=>$city_name,
            'collected_count' => $collecedCount,
            'gid' => $rs['gid']
		];


		//获取分享人的信息
		if(!empty(session('?invite'))){
			$mm = new \Member\Model\MemberViewModel;
			$data['inviter'] = $mm->field('nickname,path')->where(['invitecode'=>session('invite.code')])->find();
			$data['inviter']['path'] = thumb($data['inviter']['path']);
		}


		//获取临时times类目
		$tem_times = M('TemporaryRaiseTimes')->where(['raise_id' => $raise_id])->getField('times_id',true);
		if ($tem_times) {
			foreach($tem_times as $key=>$value) {
				$tem_str .= $tem_times[$key].',';
			}
			$tem_str = substr($tem_str,0,strlen($tem_str)-1);			
		}

	//	$data['test'] = $tem_str;

		$mem_id = session('member.id');
		$rai_id = $raise_id;
		if ($tem_str) {
			$tem_or = M('OrderWares')->where(['ware_id' => $raise_id, 'tips_times_id' => ['in',$tem_str]])->getField('order_id', true);
		}

		if ($tem_or) {
			foreach($tem_or as $key=>$value) {
				$tem_otr .= $tem_or[$key].',';
			}
			$tem_otr = substr($tem_otr,0,strlen($tem_otr)-1);
			$real_or = M('Order')->where(['id' => ['in', $tem_otr], 'member_id' => $mem_id, 'act_status' => ['neq', '0']])->select();
		}


		//获取回报类目
		$data['test'] = $real_or;
		$tem_buy = '';

		/*
		if ($t_statu == 'tem' && empty($real_or)) {
			if ($tem_str) {
				$times = M('RaiseTimes')->where(['raise_id' => $raise_id])->where(['id' => ['in', $tem_str]])->order('datetime')->select();
			} else {
				$times = M('RaiseTimes')->where(['raise_id' => $raise_id])->order('datetime')->select();
			}
		} else {
			if ($tem_str) {
				$times = M('RaiseTimes')->where(['raise_id' => $raise_id])->where(['id' => ['not in', $tem_str]])->order('datetime')->select();
			} else {
				$times = M('RaiseTimes')->where(['raise_id' => $raise_id])->order('datetime')->select();
			}
		}
		*/

		if ($tem_str) {
				$times = M('RaiseTimes')->where(['raise_id' => $raise_id])->where(['id' => ['not in', $tem_str]])->order('datetime')->select();
			} else {
				$times = M('RaiseTimes')->where(['raise_id' => $raise_id])->order('sort asc,datetime asc')->select();
		}

		$_rs = M('OrderWares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 2, 'ware_id' => $raise_id, 'status' => 1, 'act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->select();
		$orders = [];
		foreach($_rs as $row){
			if(!isset($orders[$row['tips_times_id']]['total'])){
				$orders[$row['tips_times_id']]['total'] = 0;
				$orders[$row['tips_times_id']]['count'] = 0;
			}
			$orders[$row['tips_times_id']]['total'] += $row['price'];
			$orders[$row['tips_times_id']]['count'] ++;
		}
		$data['totaled'] = 0;
		$data['sum'] = 0;
		$data['times'] = [];
		foreach($times as $t){
//			$data['totaled'] += $orders[$t['id']]['total']?:0;
			$count = $orders[$t['id']]['count']?:0;
			// 特殊处理
			if ($t['id'] == '420') {
			    $count = $count + 298;
			    $t['stock'] -= 298;
            }

//			$data['sum'] += $count;
			if(session('?member')) {
				$member_id = session('member.id');
				$buy_count = M('OrderWares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 2, 'a.member_id' =>$member_id, 'ware_id' => $raise_id,'tips_times_id' => $t['id'], 'status' => 1, 'act_status' => ['in', '1,2,3,4']])->count();
            }
            
            $stock = $t['stock'];

			if ($t['id'] == '507') {
				$count += 5;
			} elseif ($t['id'] == '508') {
				$count += 12;
			} elseif ($t['id'] == '509') {
				$count += 10;
			} elseif ($t['id'] == '676') {
                $count += 2;
                $stock -= 2;
            }

			$data['times'][] = [
				'times_id' => $t['id'],
				'title' => $t['title'],
				'content' => $t['content'],
				'price' => $t['price'],
				'prepay' => $t['prepay'],
				'stock' => $stock,
				'quota' => $t['quota'],
				'limit_num' => $t['limit_num'],//限购
				'limit_buy_times' => ($t['limit_num']-$buy_count)>0 && $t['limit_num']>0 ? $t['limit_num']-$buy_count:0,//还剩可以购买次数
				'count' => $count,
				'is_address' => $t['is_address'],
				'is_buy' => $t['is_buy'],
				'send_day' => $t['send_day'],
                'type' => $t['type'],
                'is_free' => $t['is_free'],
                'is_display_remain_time' => $t['is_display_remain_time'],
                'start_time' => $t['start_time'],
                'end_time' => $t['end_time']
			];

		}
		if($rs['end_time']>= time()){
			$rs_arr = D('RaiseOrderWaresView')->where(['A.type' => 2, 'A.ware_id' => $raise_id, 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->group('A.id')->select();
			foreach($rs_arr as $row_a){
				$data['totaled'] += $row_a['raise_times_price'];
				$data['sum'] ++;
			}

            // 特殊处理
            if ($data['id'] == 51) {
                $data['totaled'] += 41124;
                $data['sum'] += 298;
            }
            if ($data['id'] == 94) {
                $data['totaled'] += 998;
                $data['sum'] += 2;
            }
		}else{
			$data['totaled'] = $rs['totaled'];
			$data['sum']  = $rs['buyer_num'];
        }
        
        // 有关联的商品
        $gids = M('raise_goods')->where(['rid'=>$raise_id])->getField('gid', true);
        if (!empty($gids)) {
            $selled = $this->getJoinGoodsOrder($gids);

            if (!empty($selled)) {
                $data['totaled'] += $selled['total_selled'];
                $data['sum'] += $selled['total_count'];
            }
        }

		$this->put($data);
	}


	/**
	 * @apiName 众筹实名认证提交
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} surname: 真实姓名
	 * @apiPostParam {string} identity: 身份证号码
	 * @apiPostParam {string} captcha: 验证码

	 * @apiSuccessResponse
	 * {
	 *	 "status": "1",
	 *	 "info": "实名认证成功"
	 * }
	 *
	 * @apiErrorResponse
	 * {
	 *	 "status": "0",
	 *	 "info": "失败原因"
	 * }
	 */
	public function raise_real(){
		$member_id = session('member.id');
		$rs = M('member')->where(['id' => $member_id, 'is_identification' => 1])->find();
		if(!empty($rs)){
			$this->success('您已经通过了实名认证!');
		}
		//补充实名信息
		$data = [];
		$data['key'] = 'b5b0b4472b7d4174828577dcb4f66249';
		$data['surname'] = $_POST['surname'];
		$data['identity'] = strtoupper(I('post.identity'));
		$captcha = I('post.captcha');
		//判断是否符合规范
		if(empty($data['identity'])  || empty($data['surname'])){
			$this->error('请填写完整的实名信息!');
		}
		if(!preg_match('/^(\d{15}$|^\d{18}$|^\d{17}(\d|X))$/', $data['identity'])){
			$this->error('身份证号码格式不正确!');
		}

		if(!$this->checkVerify($captcha)){
			$this->error('验证码输入有误！');
		}
//		$rs = M('MemberActLog')->where(['framework_id'=>219, 'post' => json_encode($_POST)])->find();
//		if(!empty($rs)){
//			$this->error('姓名和身份证号不匹配');
//		}
		$data_0 =$this->identity($data['surname'],$data['identity']);
		\Think\Log::write('验证信息：'.$data_0);
		$_data =json_decode($data_0);
		if($_data->result->isok == true && $_data->error_code ==0){
			M('MemberInfo')->where(['member_id' => $member_id])->save($data);
			M('Member')->where(['id' => $member_id])->save(['is_identification'=>1]);
			$this->success('实名认证成功!');
		}else{
//			\Think\Log::write($_data);
			$this->error('姓名和身份证号不匹配');
		}

	}

	/**
	 * @apiName 获取众筹支付实名认证(报废)
	 *
	 * @apiGetParam {string} token: 通信令牌

	 * @apiSuccessResponse
	 * {
     *       "surname": "弦霄",
     *       "identity": "440982199903532654",
	 *       "is_identification": "1",//(0-未认证，1-已认证)
	 *     }
	 * }
	 *
	 * @apiErrorResponse
	 * {
	 *	 "status": "0",
	 *	 "info": "失败原因"
	 * }
	 */
	public function getRaiseReal_old(){
		$member_id = session('member.id');
		$member = D('RaiseMemberView')->where(['A.id'=>$member_id, 'A.status' =>1])->find();

		$data_0 =$this->identity($member['surname'],$member['identity']);
		$_data =json_decode($data_0);
		if($_data->result->isok ==true && $_data->error_code ==0){
			$data =array(
				'surname'=>$member['surname'],
				'identity'=>$member['identity'],
				'is_identification'=>1
			);
			$this->put($data);
		}else{
			$this->error('该用户未认证或者认证不成功！');
		}

	}

	/**
	 * @apiName 获取众筹支付实名认证
	 *
	 * @apiGetParam {string} token: 通信令牌

	 * @apiSuccessResponse
	 * {
	 *       "surname": "弦霄",
	 *       "identity": "440982199903532654",
	 *       "is_identification": "1",//(0-未认证，1-已认证)
	 *     }
	 * }
	 *
	 * @apiErrorResponse
	 * {
	 *	 "status": "0",
	 *	 "info": "失败原因"
	 * }
	 */
	public function getRaiseReal(){
		$member_id = session('member.id');
		$member = D('RaiseMemberView')->where(['A.id'=>$member_id, 'A.status' =>1])->find();
		if($member['is_identification'] == 1){
			$data = [
				'surname'=>$member['surname'],
				'identity'=>$member['identity'],
				'is_identification'=>$member['is_identification']
			];
			$this->put($data);
		}else{
			$this->error('该用户未认证或者认证不成功！');
		}

	}

	/**
	 * @apiName 获取众筹预付方式以及二次支付默认地址
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} raise_id: 众筹id
	 * @apiPostParam {int} raise_times_id: 众筹类目id
	 * @apiPostParam {int}order_pid: 订单父id(第二次支付流程)

	 * @apiSuccessResponse
	 * [
	 *    "info": {
	 *		"data": [
	 *			{
	 *			"step":"1"//支付阶段(0-支付全额，1-支付预约金，2-支付尾款(二次支付) 3-已完成众筹全部支付)
	 *			"prepay": "10000",//预约金
	 *			"retainage": "20000"//尾款
	 *			"address": "广东省广州市天河区建中路50号"//地址
	 *			"title": "技术开发的接口非法"//标题
	 *			"pay_price": "565.00"//支付的金额
	 * 			}
	 * 		]
	 *     },
	 *     "status": 1,
	 *     "url": ""
	 * ]
	 * @apiErrorResponse
	 * {
	 *	 "status": 0,
	 *	 "info": "失败原因"
	 * }
	 */
	public function getRaiseOrder(){
		$raise_id = I('post.raise_id');
		$raise_times_id = I('post.raise_times_id');
		$order_pid = I('post.order_pid','');
		$rs = D('RaiseDetailView')->where(['raise_times_id'=>$raise_times_id,'A.id'=>$raise_id])->find();
		if(!empty($rs)){
			if($rs['raise_times_prepay']>0){//预付
				$retainage =Number_format(($rs['raise_times_price'] - $rs['raise_times_prepay']), 2, '.','');
				if(!empty($order_pid)){
					$order = M('Order')->where(['id'=>$order_pid,'act_status'=>1])->find();
					if(!empty($order)) {
						$data =[
							'step'=>3,
							'prepay'=>$rs['raise_times_prepay'],
							'retainage'=>$retainage,
							'address'=>'',
							'title'=>$rs['raise_title'],
							'pay_price'=>$retainage,
						];
					}else {
						$order_pay = M('Order')->where(['id'=>$order_pid,'act_status'=>0])->find();
						$member_address = M('MemberAddress')->where(['member_id'=>session('member.id'),'id'=>$order_pay['member_address_id']])->find();
						$detail_address = D('CityView')->where(['area_id'=>$member_address['citys_id']])->find();
						$address = $detail_address['province_name'].$detail_address['province_alt']. $detail_address['city_name'].$detail_address['city_alt'].$detail_address['area_name'].$detail_address['area_alt'].$member_address['address'];
						$data = [
							'step' => 2,
							'prepay' => $rs['raise_times_prepay'],
							'retainage' => $retainage,
							'address'=>$address,
							'title'=>$rs['raise_title'],
							'pay_price'=>0,
						];
					}
				}else{
					$data =[
						'step'=>1,
						'prepay'=>$rs['raise_times_prepay'],
						'retainage'=>$retainage,
						'address'=>'',
						'title'=>$rs['raise_title'],
						'pay_price'=>$rs['raise_times_prepay'],
					];
				}
			}elseif($rs['raise_times_prepay']==0){
				$data =[
					'step'=>0,
					'prepay'=>$rs['raise_times_price'],
					'retainage'=>0,
					'address'=>'',
					'title'=>$rs['raise_title'],
					'pay_price'=>$rs['raise_times_price'],
				];
			}
			$data['is_realname']=$rs['is_realname'];
			$data['is_address']=$rs['is_address'];
			$this->success([
				'data'=>$data
			]);
		}else{
			$this->error('不存在该众筹');
		}

	}

	/**
	 * @apiName 获取众筹问答数据
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} raise_id: 众筹id
	 *
	 * @apiSuccessResponse
	 * [
	 * {
	 * 	"phase": "1"
	 * 	"ask_title": "我支持了有尾款的共建人档位，已和发起人协商一致，如何支付尾款?"
	 * 	"answer_title": "登录您的账号后，会有自动弹框提示您去支付尾款，按“去支付”按钮即可"
	 * },
	 * {
	 * 	"phase": "2"
	 * 	"ask_title": "如何知道我是否付款成功了？"
	 * 	"answer_title": "付款成功后会收到手机短信，同时您也可以在订单页和站内信看到。"
	 * },
	 * {
	 * 	"phase": "3"
	 * 	"ask_title": "我还没有支持项目，对项目很感兴趣想跟发起人联系，该怎么办？"
	 * 	"answer_title": "目前暂不提供发起人联系方式，如有问题请咨询平台客服yami194。"
	 * },
	 * {
	 * 	"phase": "4"
	 * 	"ask_title": "我已经支持了共建人档位，下一步要怎么做？"
	 * 	"answer_title": "请等待平台客服的联系，客服会在3个工作日内电话或微信联系您，敬请留意。"
	 * },
	 * {
	 * 	"phase": "5"
	 * 	"ask_title": "在哪里可以看到项目的最新进展？"
	 * 	"answer_title": "吖咪和我有饭的app&amp;微信平台的项目页面能看到。"
	 * }
	 * ]
	 * */
	public function getQuestionTitle(){
		$raise_id = I('post.raise_id');
		$question_title = M('Raise')->where(['id'=>$raise_id])->getField('question_title');
		$question_title = json_decode($question_title);
		if(!empty($question_title)){
			foreach($question_title as  $key=>$val){
				$data[$key]= (array)$val;
			}
		}
		$this->put($data);
	}

	/**
	 * @apiName 获取微信号(不需要)
	 *
	 * @apiGetParam {string} token: 通信令牌

	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "weixincode": "紫嫣"
	 *     }
	 * ]
	 */
	public function getWeixinCode(){
		$member_id = session('member.id');
		$wixincode = M('MemberInfo')->field('weixincode')->where(['member_id'=>$member_id])->find();
		$data = [
			'weixincode'=>$wixincode,
		];
		$this->put($data);
	}

	/**
	 * @apiName 获取特权数据
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} type: 类型(0-活动，1-商品 2-中餐众筹)
	 * @apiPostParam {int} type_id: 活动、商品、众筹的ID
	 * @apiPostParam {int} tips_times_id: 活动分期、商品、众筹档位的ID
	 * @apiPostParam {int} privilege_id: 特权id

	 * @apiSuccessResponse
	 *	{
	 *	"member_id": "278518",
	 *	"type": "2",
	 *	"type_id": "32",
	 *	"nickname": "紫嫣(´•ω•`๑)",
	 *	"title": "标题",
	 *	"pic_id": "1000668227",
	 *	"head_pic_id": "1000667884",
	 *	"pic_path": "http://img.m.yami.ren/20170205/d408feff71782ea72b1114c702e47efd1413ad95.jpg"
	 *  "head_pic_path": "http://img.m.yami.ren/20161017/59717ac4ca6f63b9f5de8efbbf1ee20c93739691_320x320.jpg",
	 *	"is_receive": "1"（1-领取成功，2-领取人数已满 3-已领取过未使用 4-已使用 5-领取不成功 6-领取不成功,众筹开始时间已到，不能领取特权 7-众筹不存在）
	 *	}
	 *
	 */
	public function GetPrivilege(){
		$member_id = session('member.id');
		$type = I('post.type',2);
		$type_id = I('post.type_id');
		$privilege_id = I('post.privilege_id');
		$tips_times_id = I('post.tips_times_id');

		if ($tips_times_id == -1) {
            $rs_arr = D('PrivilegeView')->where(['type'=>$type,'type_id'=>$type_id,'A.id'=>$privilege_id])->find();
        } else {
            $rs_arr = D('PrivilegeView')->where(['type'=>$type,'type_id'=>$type_id,'D.id'=>$tips_times_id,'A.id'=>$privilege_id])->find();
        }
		if(empty($rs_arr)) $this->error('该特权已失效！');
		$rs_arr['pic_path'] = thumb($rs_arr['pic_path']);
		$rs_arr['head_pic_path'] = thumb($rs_arr['head_pic_path']);
		$rs_arr['order_id'] = '';

		$raise = null;

		if ($tips_times_id == -1) {
		    $raise = D('RaiseDetailView')->where(['id' => $type_id,'status'=>1])->find();
        } else {
            $raise = D('RaiseDetailView')->where(['id' => $type_id,'raise_times_id'=>$tips_times_id,'status'=>1])->find();
        }

		$rs_arr['member_privilege_id'] = '';
		$rs_arr['times_type'] = $tips_times_id == -1 ? '0' : '1'; // 挡位类型，0 -- 全部挡位 ， 1 -- 某个挡位

		if(!empty($member_id)){
			if(!empty($raise)){
				if($raise['start_time']>time()){
					//$pr = M('MemberPrivilege')->where(['member_id'=>$member_id,'privilege_id'=>$privilege_id])->find();
					$pr = D('MemberPrivilegeView')->where(['A.member_id'=>$member_id,'B.type' => $type, 'B.type_id' => $type_id,'A.order_id'=>['EXP','IS NULL']])->select();
					if(!empty($pr)){
					//	if(empty($pr['order_id'])){
							$rs_arr['is_receive'] = 3;//已领取过未使用
							$rs_arr['member_privilege_id'] = $pr[0]['id'];
					//	}else{
						//	$rs_arr['order_id'] = $pr['order_id'];
                        //    $rs_arr['is_receive'] = 4;//已使用
					//	}
					}else{
						//$count = M('MemberPrivilege')->where(['privilege_id'=>$privilege_id])->count();
						//if(($rs_arr['number'] > $count && $rs_arr['number']>=0) ||  $rs_arr['number']<0){
							//一次性把众筹优先权领了
							$privilege = M('Privilege')->where(['type'=>$type,'type_id'=>$type_id])->select();
							foreach($privilege as $p){
								$data = [
									'member_id' => $member_id,
									'privilege_id' => $p['id'],
									'end_time' => $raise['start_time'],
								];
								$id = M('MemberPrivilege')->add($data);
							}
							
							if ($id > 0) {
								$rs_arr['is_receive'] = 1;//领取成功
								$rs_arr['member_privilege_id'] = $id;
							}else{
								$rs_arr['is_receive'] = 5;//领取不成功
							}
						// }else{
						// 	$rs_arr['is_receive'] = 2;//领取人数已满
						// }
					}
				}else{
					$rs_arr['is_receive'] = 6;//领取不成功,众筹开始时间已到，不能领取特权
				}
			}else{
				$rs_arr['is_receive'] = 7;//众筹不存在
			}
			$rs_arr['is_member'] =1;
		}else{
			$rs_arr['is_member']=0;
		}
		$this->put($rs_arr);
	}

    /**
     * @apiName 众筹分享成功的回调函数
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} raise_times_id: 众筹times_id
     *
     * @apiSuccessResponse
     * {
     *   status: 1,
     *   info: "1", // order_id
     * }
     *
     * @apiErrorResponse
     * {
     *   status: 0,
     *   info: ""
     * }
     */
    public function raiseShareSuccess() {
        $timesId = I('post.raise_times_id');

        if (!session('?member')) {
            $this->error('403未登录');
            return;
        }

        $raiseTimeItem = M('RaiseTimes')->where(['id' => $timesId])->limit(1)->find();
        if ($raiseTimeItem['type'] != 1 && $raiseTimeItem['is_free'] != 1) {
            $this->error("当前不是分享抽奖");
            return;
        }

        $memberId = session('member.id');

        $oldLuckyItem = M('RaiseLucky')->where(['raise_times_id' => $timesId, 'member_id' => $memberId])->find();
        if(!empty($oldLuckyItem)) {
            $this->error('你已经分享过了');
            return;
        }

        if(session('?invite'))$invite_member_id = session('invite.member_id');

        $msg = $this->createRaise($raiseTimeItem['raise_id'], $timesId, '', '', '', '', 1, $invite_member_id);
        if (!is_numeric($msg)) {
            $this->error($msg);
        }


        $lastRaiseLuckyItem = M('RaiseLucky')->where(['raise_times_id' => $timesId])->order('id desc')->limit(1)->find();
        $lastLuckyNumber = $lastRaiseLuckyItem ? $lastRaiseLuckyItem['lucky_num'] + 1 : 1;

        $luckyItem['member_id'] = $memberId;
        $luckyItem['lucky_status'] = 0;
        $luckyItem['raise_times_id'] = $timesId;
        $luckyItem['lucky_num'] = $lastLuckyNumber;
        $luckyItem['type'] = 0; // 分享抽奖
        $luckyItem['order_id'] = $msg;
        M('RaiseLucky')->add($luckyItem);

        $this->success($msg);
    }

    private function createRaise($raise_id, $times_id, $address_id, $context,$weixincode,$member_privilege_id,$oper_read,$invite_member_id) {
        $member_id = session('member.id');
        if(empty($member_id))
            return '请登录账号！';
        //给times表加行锁避免超卖
        M('RaiseTimes')->where(['id' => $times_id])->lock(true)->select();
        $rs = D('RaiseEditView')->where(['id' => $raise_id, 'times_id' => $times_id,'status'=>1])->find();
        $buy_count = M('OrderWares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 2, 'a.member_id' =>$member_id, 'ware_id' => $raise_id,'tips_times_id' => $times_id, 'status' => 1, 'act_status' => ['in', '1,2,3,4']])->count();
        if(empty($rs))
            return '非法提交';

        $model = M();
        $model->startTrans();//开启事务
        //执行你想进行的操作, 最后返回操作结果 result
        if ($rs['stock'] == 0) {
            $model->rollback();//回滚
            return '该众筹的所选项目已售罄!';
        }elseif($rs['start_time']>time() && empty($member_privilege_id)){
            $model->rollback();//回滚
            return '该众筹尚未开放';
        }elseif ($rs['limit_num'] > 0 && $buy_count > $rs['limit_num']) {
            $model->rollback();//回滚
            return '限制购买次数为'.$rs["limit_num"].'，您已下单'.$buy_count.'次了，不能再购买了';
        }elseif ($rs['end_time']<time()) {
            $model->rollback();//回滚
            return '该众筹已结束购买';
        }


        //保存用户信息
        M('MemberInfo')->where(['member_id'=>$member_id])->save(['weixincode'=>$weixincode]);
        //查询订单价格
        $time = time();
        //全额/预付头款
        $data = [
            'sn' => createCode(18),
            'member_id' => $member_id,
            'price' => !empty($rs['prepay']) && $rs['prepay']>0?$rs['prepay']:$rs['price'],
            'act_status' => 0,
            'create_time' => $time,
            'limit_pay_time' => $time + $rs['limit_time'],
            'invite_member_id' => $invite_member_id,
            'channel' => $this->channel,
            'is_free' => 1, // 免费订单
            'act_status' => 2
        ];
        if(!empty($address_id))$data['member_address_id'] = $address_id;
        if(!empty($context))$data['context'] = $context;
//		if(session('?invite'))$data['invite_member_id'] = session('invite.member_id');
        $order_id = M('order')->add($data);
        if (!$order_id) {
            $model->rollback();//回滚
            return '订单创建失败!';
        }

        //快照数据
        $snapshot = [
            'raise_id'=>$raise_id,
            'raise_times_id'=>$times_id,
            'raise_title'=>$rs['title'],
            'raise_content'=>$rs['content'],
            'raise_category'=>$rs['catname'],
            'raise_introduction'=>$rs['introduction'],
            'raise_total'=>$rs['total'],
            'raise_price'=>$rs['price'],
            'raise_prepay'=>$rs['prepay'],
            'raise_type'=>$rs['prepay']>0 ? '预付方式' : '全额方式',
            'raise_act_pay'=>!empty($rs['prepay']) && $rs['prepay']>0?$rs['prepay']:$rs['price'],
            'datetime'=>$address_id,
            'datetime'=>time(),
        ];

        $code = createCode(8);

        $result = M('OrderWares')->add(array(
            'order_id' => $order_id,
            'type' => 2,
            'ware_id' => $raise_id,
            'price' => !empty($rs['prepay']) && $rs['prepay']>0?$rs['prepay']:$rs['price'],
            'check_code' => $code,
            'tips_times_id' => $times_id,
            'snapshot' => json_encode($snapshot)
        ));

        if (!$result) {
            $model->rollback();//回滚
            return '订单商品插入失败!';
        }

        //下单减库存
        if($rs['stock'] > 0)M('RaiseTimes')->where(['id' => $times_id])->setDec('stock');
        //使用特权
        if(!empty($member_privilege_id) && $rs['start_time']>time())M('MemberPrivilege')->where(['id' => $member_privilege_id])->save(['order_id'=>$order_id,'status'=>2]);

        $model->commit();//事务提交

        //记录订单快照信息
        $this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
        return $order_id;
    }

    /**
     * @apiName 获取抽奖结果
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {string} raise_id: 众筹id
     * @apiPostParam {string} times_id: 众筹挡位id
     *
     * @apiSuccessResponse
     * {
     *       "rule_title": "本次抽奖由项目发起人授权，平台采用同一算法，系统自动抽取抽奖码，并公开开奖结果。",
     *       "status": "1",
     *       "path": "http://img.m.yami.ren/20170720/654d2a2b20dfba695a23b8ab700bec1098c6661e.jpg",
     *       "raise_title": "#测试#他让这家136年的老字号刷爆了朋友圈，最潮的年轻人都甘愿排队",
     *       "times_title": "抽奖福利",
     *       "nickname": "余华杰",
     *       "price": "0.00",
     *       "end_time": "2017-08-17",
     *       "status_text": "众筹成功",
     *       "lucky_num": [
     *          "1"
     *       ],
     *       "count": "1",
     *       "participator": "1",
     *       "desc": "呵呵\nhehe\n\nhaha",
     *       "info": [
     *          "项目结束时间: 2017-08-17",
     *          "开奖时间: 2017-08-04 18:31:33",
     *          "大盘指数来源交易日: dsad ",
     *          "上证指数: 11121",
     *          "深证指数: 21212",
     *          "挡位认筹人数: 1",
     *          "抽奖码个数: 1"
     *       ]
     *   }
     */
    public function getLotteryResult() {
        $raise_id = I('post.raise_id');
        $times_id = I('post.times_id');

//        if (!session('?member')) {
//            $this->error('403未登录');
//            return;
//        }

        $member_id = session('member.id');
        $raise_lucky_result = M('RaiseLuckyResult')->where(['raise_times_id' => $times_id])->find(); // 众筹抽奖结果信息

        if (empty($raise_lucky_result)) {
            $this->error('未开奖');
        }

        $raise = D('RaiseTimeView')->where(['A.id' => $times_id])->find(); // 众筹数据

        if (empty($raise)) {
            $this->error('该众筹活动不存在');
        }

        $participator = M('RaiseLucky')->where(['raise_times_id' => $times_id])->count(); // 抽奖参与者个数
        $count = M('OrderWares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 2, 'ware_id' => $raise_id, 'tips_times_id' => $times_id,'status' => 1, 'act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->count(); //参与此挡位的人数


        $luckyNum = $raise_lucky_result['status'] == 1 ? preg_split("/[\s,]+/", $raise_lucky_result['lucky_num']) : [];
        $targetLuckyNum = array();

        foreach ($luckyNum as $num) {
            $targetLuckyNum[] = str_pad($num, 6, '0',STR_PAD_LEFT);
        }

        unset($num);

        $data = [
            'rule_title' => '本次抽奖由项目发起人授权，平台采用同一算法，系统自动抽取抽奖码，并公开开奖结果。',
            'status' => '1',
            'path' => thumb($raise['path'], 1),
            'raise_title' => $raise['raise_title'],
            'times_title' => $raise['title'],
            'nickname' => $raise['surname'],
            'price' => $raise['price'],
            'end_time' => date("Y-m-d", $raise['end_time']),
            'status_text' => '众筹成功',
            'lucky_num' => $targetLuckyNum,
            'count' => $count,
            'participator' => $participator,
            'desc' => $raise['content']
        ];

        $info[] = "项目结束时间: " . $data['end_time'];
        $info[] = "开奖时间: " . $raise_lucky_result['run_time'];
        $info[] = "大盘指数来源交易日: " . $raise_lucky_result['trade_date'];
        $info[] = "上证指数: " . $raise_lucky_result['sh'];
        $info[] = "深证指数: " . $raise_lucky_result['sz'];
        $info[] = "档位认筹人数: " . $count;
        $info[] = "抽奖码个数: " . $participator;
        $data['info'] = $info;

        $this->put($data);
    }

    public function getStock() {
    	$raise = I('post.raise_id');
    	$times = I('post.times_id');
    	$stock = M('RaiseTimes')->where(['id' => $times, 'raise' => $raise])->getField('stock');
    	$data['info'] = 'stock';
    	$data['stock'] = $stock;
    	$this->ajaxReturn($data);
    }

    public function getLimit() {
    	$raise = I('post.raise_id');
    	$times = I('post.times_id');
    	$member_id = session('member.id');
    	$limit = M('RaiseTimes')->where(['id' => $times, 'raise' => $raise])->getField('limit_num');
    	$buy_count = M('OrderWares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 2, 'a.member_id' =>$member_id, 'ware_id' => $raise,'tips_times_id' => $times, 'status' => 1, 'act_status' => ['in', '1,2,3,4']])->count();
   		$limit_buy_times = ($limit-$buy_count)>0 && $limit>0 ? $limit-$buy_count:0;//还剩可以购买次数
    	$data['info'] = 'limit';
    	$data['limit'] = $limit;
    	$data['buy_count'] = $buy_count;
    	$data['limit_buy_times'] = $limit_buy_times;
    	$data['member_id'] = $member_id;
    	$this->ajaxReturn($data);
    }


    public function raiseRemind() {
    	$raise = I('post.raise_id');
    	$times = I('post.raise_times_id');
    	$member_id = session('member.id');
    	$rs = M('RaiseRemind')->where(['raise_id' => $raise, 'times_id' => $times, 'member_id' => $member_id, 'statu' => 0])->select();
    	if (!$rs) {
    		$data['raise_id'] = $raise;
    		$data['times_id'] = $times;
    		$data['member_id'] = $member_id;
    		M('RaiseRemind')->add($data);
    		$this->success('预约成功');
    	} else {
    		$this->error('已预约');
    	}
    }
}
