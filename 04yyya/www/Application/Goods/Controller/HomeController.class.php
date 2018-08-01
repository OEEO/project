<?php
namespace Goods\Controller;
use Goods\Common\MainController;

//@className 首页接口
class HomeController extends MainController {
    /**
	 * @apiName 获取首页中列表数据
	 * 
     * @apiGetParam {string} token: 令牌
	 *
	 * @apiSuccessResponse
	 *	[
	 *	{
	 *		"id": "7801", 
	 *		"nickname": "吖咪酱",
	 *		"customers": "3040",
	 *		"follow_num": 0,
	 *		"member_id": "23572",
	 * 		"headpic": "http://img.m.yami.ren/20160523/I4MzJhMTljZmFiNjQyMWMwMTIxMzMx.jpg",
	 *		"mainpic": "http://img.m.yami.ren/20170710/abe153e9491f149f7884d37b2f90f298b19c4466.jpg",
 	 *		"catname": "饭局",
	 *		"tagname": [
	 *		"私房菜"
	 *		],
	 *		"title": "js测试",
	 *		"price": "1",
	 *		"start_time": "1499679000",
	 *		"end_time": "1501514700",
 	 *		"sellout": 0,
	 *		"p_tags_id": 76,
	 *		"buy_status": "0",
	 *		"date": "07月10号 周一 17:30 - 07月31号 周一 23:25",
	 *		"simpleaddress": "海安路",
	 *		"address": "海安路海安路2号",
	 *		"is_follow": 0,
	 *		"is_collect": 0,
	 *		"min_num": "1",
	 *		"restrict_num": "3",
	 *		"type": 0 // 饭局
	 *	},
	 *	{
	 *		"id": "89",
	 *		"title": "他让这家136年的老字号刷爆了朋友圈，最潮的年轻人都甘愿排队",
	 *		"content": "我的自述消失中的广州味道上个月，有七八十年历史的大同酒家登出公告，暂停营业。在门口喝早茶的老伯老太扑了个空。“大同酒家我5岁就来了，现在70多岁了，开了这么多年没想到还是做不下去，早知道我就多点来帮衬",
	 *		"total": "200000",
	 *		"totaled": "0",
	 *		"status": "1",
	 *		"category_id": "29",
	 *		"datetime": "2017-07-17 15:58:36",
	 *		"start_time": "1500393600",
	 *		"city_id": "224",
	 *		"end_time": "1502899200",
	 *		"video_url": null,
	 *		"introduction": "江湖上大家一般都叫我江哥。我是一个天生就热爱商业的人，18年前，我在广州创办了山东老家。2015年，我让一家136年历史的老牌粤菜茶楼重获新生。",
	 *		"buyer_num": "0",
	 *		"path": "http://img.m.yami.ren/20170720/654d2a2b20dfba695a23b8ab700bec1098c6661e.jpg",
	 *		"catname": "众筹分类1",
	 *		"nickname": "余华~~杰",
	 *		"headpath": "http://wx.qlogo.cn/mmopen/1Qw8iaBGVXgOR1icpFQ1llEDeez9ElZQwzzNEx5SibIfN3oxur3ib5eXicaJSKlQNRV8l8W4bkzolgXVdicoBq9y2WaQ4RmuSXtOo7/0",
	 *		"city_name": "广州",
	 *		"type": 1 // 众筹
	 *	}
	 *	]
	 *
	 */
	public function getHomeList() {
		$where = [];
		$where[] = 'A.status=1';
		$where[] = 'A.is_pass=1';
		$where[] = 'is_public=1';
		
		// 区域选择
		$citys = M('citys')->where(['pid' => session('city_id')])->getField('id', true);
		$city = session('city_id');
		if (empty($city)) {
			$citys[] = 224;
		} else {
			$citys[] = $city;
		}
//		$where[] = "M.city_id in (". join(',', $citys) .")";

		// $tags = M('TipsTag')->field('tips_id')->where(['tag_id' => ['not in', [76]]])->buildSql();
		// $where[] = "A.id in " . $tags;

		$t = M('Home')->where(['type' => 0])->getField('t_id', true);
		if (!empty($t)) {
			$where[] = "A.id in (" . join(',', $t) .")";

			if(in_array($this->channel, [7,8,9])){
				$where[] = 'C.id=1';
				$where[] = 'A.buy_status<>2';
			}

			$where = join(' and ', $where);

			$data = D('TipsHomeView')->where($where)->group('A.id')->order('N.weight desc')->select();
		} else {
			$data = array();
		}
		
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


                //获取粉丝数量
                $data[$k]['follow_num'] = M('MemberFollow')->where(['follow_id'=>$r['member_id']])->count();

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
				'type' => 0,
                'city' => C('CITYS')[$row['tips_city_id']]
			];
		}

		// // 众筹
//		$r = M('Home')->where(['type' => 1])->getField('r_id', true);
		$data2 = [];

        $where2[] = "A.status = 1";
        $where2[] = "is_public = 1";
//			$where2[] = "A.id in (" . join(',', $r) .")";
//			$where2 = join(' and ', $where2);
        $where2 = ['status' => 1,'is_public'=>1];

        $rs2 = D('RaiseView')->where($where2)->group('id')->order('id desc')->select();
        foreach($rs2 as $row){
            $row['type'] = 1;
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
                    $row['totaled'] += 38640;
                    $row['buyer_num'] += 280;
                }

                if ($row['id'] == 94) {
                    $row['totaled'] += 998;
                    $row['buyer_num'] += 2;
                }
            }

            $gids = M('raise_goods')->where(['rid'=>$row['id']])->getField('gid', true);
            if (!empty($gids)) {
                $selled = $this->getJoinGoodsOrder($gids);
                if (!empty($selled)) {
                    $row['totaled'] += $selled['total_selled'];
                    $row['buyer_num'] += $selled['total_count'];
                }
            }
            $data2[] = $row;
        }
		unset($row);

		$result = array_merge($data2, $_data);

		$this->ajaxReturn($result);
		// $this->ajaxReturn($_data);
    }
    
    private function getJoinGoodsOrder($gids) {
        $selled = D('OrderView')->where(['type' => 1, 'ware_id' => ['IN', join(',', $gids)], 'act_status'=> ['IN', '1,2,3,4'], 'status' => 1])->select();
        $data['total_selled'] = 0;
        $data['total_count'] = count($selled);
        foreach ($selled as $item) {
            $data['total_selled'] += $item['price'];
        }
        return $data;
    }
}