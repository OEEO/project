<?php
namespace Member\Controller;
use Member\Common\MainController;

// @className 我的关注/收藏
Class FollowController extends MainController {

	/**
	 * @apiName 添加/取消关注
	 *
	 * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} member_id: 被关注的达人会员ID
     * @apiPostParam {int} type: 0-取消关注 1-关注(默认)
     *
	 * @apiSuccessResponse
	 * {
	 *     "info": "关注成功！",
	 *     "status": 1,
	 *     "url": ""
	 * }
     * {
     *     "info": "取消关注成功！",
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
	Public function changeFollow(){
		$type = I('post.type', 1);
        $member_id = (int)I('post.member_id');
		if(empty($member_id))$this->error('未指定关注类型或关注ID！');
        $condition = [];
        $condition['member_id'] = session('member.id');
        $condition['follow_id'] = $member_id;

        if(!$type){
            M('MemberFollow')->where($condition)->delete();

			//删除达人消息
			$ids = D('MsgView')->where(['member_id' => session('member.id'), 'origin_id' => $member_id])->getField('message_id', true);
            if(!empty($ids)){
                M('MemberMessage')->where(['message_id' => ['IN', join(',', $ids)]])->delete();
                M('Message')->where(['id' => ['IN', join(',', $ids)]])->delete();
            }
            $this->success('取消关注成功！');
        }else{
            $rs = M('MemberFollow')->where($condition)->find();
            if(empty($rs))M('MemberFollow')->add($condition);

			//查询该达人的最新活动
			$tips_id = M('tips')->join('ym_tips_sub on id=tips_id')->where(['member_id' => $member_id, 'status' => 1, 'is_pass' => 1, 'is_public' => 1])->order('id desc')->getField('id');
            $tips_times = M('TipsTimes')->where(['tips_id'=>$tips_id,'start_time'=>['GT',time()]])->find();
			if(empty($tips_times)){
                $goods_id = M('Goods')->join('__GOODS_SUB__ on id=goods_id')->where(['member_id' => $member_id, 'status' => 1, 'is_pass' => 1, 'is_public' => 1])->order('id desc')->getField('id');
				if(!empty($goods_id))
                    $this->push_Message(session('member.id'),array(),null,$member_id, '感谢您的关注!', 6, $goods_id);
                else
                    $this->push_Message(session('member.id'),array(),null,$member_id, '感谢您的关注!');
			}else{
				$this->push_Message(session('member.id'),array(),null,$member_id, '感谢您的关注!', 4, $tips_id);
			}

            $this->success('关注成功！');
        }
        $this->error('关注失败！');
	}


    /**
     * @apiName 粉丝/关注列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 页数
     *
     * @apiPostParam {int} type: 0-粉丝列表 1-关注列表
     *
     *
     * @apiSuccessResponse
     * [
     *     {
     *     "member_id": "1884",
     *     "fans_nickname": "啦啦啦12",
     *     "path": "http://img.m.yami.ren/member/2016-04-13/yMzkxZGI1ZGMwYzEwMzI2NWEwZDlmM_320x320.jpg"
     *     },
     *     {
     *     "member_id": "6395",
     *     "fans_nickname": "啦啦啦12",
     *     "path": "http://img.m.yami.ren/20160503/ca317e58267280612ffcb1e38c90de489f29674b_320x320.jpg"
     *     }
     * ]
     *
     */
    public function getlist(){
        $type = I('post.type',0);
        $member_id = session('member.id');
        $page = I('get.page',1);

        if($type==0){
            //粉丝列表
            $data = D('FansView')->where(['A.follow_id'=>$member_id])->page($page,10)->select();
            foreach($data as $key=>$row){
                $data[$key]['path'] = thumb($row['path'],2);
            }
            $this->ajaxReturn($data);
        }elseif($type==1){
            //关注列表
            $data = D('FollowView')->where(['A.member_id'=>$member_id])->page($page,10)->select();
            foreach($data as $key=>$row){
                $data[$key]['path'] = thumb($row['path'],2);
                $data[$key]['tips'] = '';
                $mid[] = $row['member_id'];
            }
            if(!empty($mid)){
                $tips_id = M('Tips')->join('ym_tips_sub on id=tips_id')->where(['member_id'=>['IN',join(',',$mid)],'is_pass'=>1,'status'=>1, 'is_public' => 1])->order('id desc')->group('member_id')->getField('max(id)',true);
                $tips_info = M('Tips')->where(['id'=>['IN',join(',',$tips_id)]])->field('id,member_id,title,category_id')->order('id desc')->select();
                foreach($data as $key=>$row){
                    foreach($tips_info as $k=>$r){
                        if($row['member_id'] == $r['member_id']){
                            $category = M('category')->where(['id'=>$r['category_id']])->getField('name');
                            $data[$key]['tips'] = $category.'-'.$r['title'];
                            //$data[$key]['tips_id'] = $r['id'];
                        }
                    }
                }
            }

            $this->ajaxReturn($data);
        }

    }


    /**
     * @apiName 添加/取消收藏
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} type_id: 被关注对象的ID
     * @apiPostParam {int} type: 0-活动(默认)
     * @apiPostParam {int} operate: 0-取消收藏 1-收藏(默认)
     *
     * @apiSuccessResponse
     * {
     *     "info": "收藏成功！",
     *     "status": 1,
     *     "url": ""
     * }
     * {
     *     "info": "取消收藏成功！",
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
    Public function changeCollect(){
        $type = I('post.type',0);
        $operate = I('post.operate', 1);
        $type_id = I('post.type_id');
        if(empty($type_id))$this->error('未指定收藏类型或收藏ID！');
        $condition = [];
        $condition['member_id'] = session('member.id');
        $condition['type_id'] = $type_id;
        $condition['type'] = $type;

        if(!$operate){
            M('MemberCollect')->where($condition)->delete();

            $this->success('取消收藏成功！');
        }else{
            $rs = M('MemberCollect')->where($condition)->find();
            if(empty($rs))M('MemberCollect')->add($condition);

            $this->success('收藏成功！');
        }
        $this->error('收藏失败！');
    }

    /**
     * @apiName 获取收藏列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 页数
     *
     * @apiPostParam {int} type: 0-活动列表(默认)
     *
     *
     * @apiSuccessResponse
     * [
     *     {
     *         "id": "3591",
     *         "title": "欧洲杯决赛日恒大足球美食深度游双人套餐",
     *         "buy_status": "0",
     *         "stop_buy_time": "172800",
     *         "catname": "沙龙",
     *         "path": "http://img.yummy194.cn/20160630/8be6347fbcafcad8d1ff7a24ff6587336ae22918_640x420.jpg",
     *         "simpleaddress": "广州恒大酒店",
     *         "times": {
     *             "id": "6229",
     *             "tips_id": "3591",
     *             "phase": "1",
     *             "start_time": "1468119600",
     *             "end_time": "1468166100",
     *             "stock": "93",
     *             "datetime": "2016-07-05 16:16:33"
     *         }
     *     },
     *     {
     *         "id": "3616",
     *         "title": "与高级品酒师Andy来一局美酒粤菜的盛宴",
     *         "buy_status": "0",
     *         "stop_buy_time": "86400",
     *         "catname": "饭局",
     *         "path": "http://img.yummy194.cn/20160704/d1e321301ef6366eed7ee1f00ac0f2d870acaa2b_640x420.jpg",
     *         "simpleaddress": "猎德大道天銮",
     *         "times": {
     *             "id": "6477",
     *             "tips_id": "3616",
     *             "phase": "1",
     *             "start_time": "1468060200",
     *             "end_time": "1468071000",
     *             "stock": "10",
     *             "datetime": "2016-07-04 17:23:15"
     *         }
     *     }
     * ]
     */
    public function getCollectList(){
        $page = I('get.page',1);
        $type = I('post.type',0);
        $pageSize = 10;

        $rs = M('MemberCollect')->where(['member_id' => session('member.id'),'type'=>$type])->page($page,$pageSize)->order('id desc')->select();
        $data = [];
        foreach($rs as $row){
            if($row['type'] == 0){
                $dt = D('TipsView')->field(['id', 'title', 'buy_status', 'stop_buy_time', 'catname', 'path', 'simpleaddress'])->where(['id' => $row['type_id']])->find();
                $dt['path'] = thumb($dt['path'],1);
                //活动近期可参与时间段
                $dt['times'] = M('TipsTimes')->where(['tips_id' => $row['type_id'], 'start_time' => ['GT', $row['stop_buy_time'] + time()]])->order('start_time asc')->find();
                if(empty($dt['times'])){
                    $dt['times'] = M('TipsTimes')->where(['tips_id' => $row['type_id']])->order('start_time desc')->find();
                }
            }elseif($row['type'] == 1){
                $dt = D('GoodsView')->field(['id', 'title', 'shipping', 'path', 'price', 'catname', 'stocks'])->where(['id' => $row['type_id']])->find();
                $dt['path'] = thumb($dt['path'],1);
                //已售数量
                $dt['cell_count'] = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 1, 'ware_id' => $row['type_id'], 'act_status' => ['IN', '1,2,3,4']])->count();
            }elseif($row['type'] == 2){
                $dt = D('RaiseView')->field(['id', 'title','start_time','end_time', 'path','nickname','content','introduction'])->where(['id' => $row['type_id']])->find();
                $dt['path'] = thumb($dt['path'],1);
                //已售数量
                $dt['cell_count'] = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 2, 'ware_id' => $row['type_id'], 'act_status' => ['IN', '1,2,3,4']])->count();
            }
            $dt['type'] = $row['type'];
            if(!empty($dt))$data[] = $dt;
        }
        $this->ajaxReturn($data);
    }
}
