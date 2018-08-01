<?php

namespace Home\Controller;
use Home\Common\MainController;
use Common\Util\Cache;

// @className 明星达人接口
class DarenController extends MainController {

    /**
     * @apiName 明星达人接口
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {string} page: 分页 默认1
     * @apiPostParam {int} order: 排序方式   0：按新旧排序 1：按热度排序
     * @apiPostParam {int} tag_id: 标签ID
     * @apiPostParam {int} city_id: 城市ID
     *
     * @apiSuccessResponse
     * [
     *     {
     *     "id": "10416",  //会员ID
     *     "name": "杨广",  //会员昵称
     *     "introduce": "&nbsp; &nbsp; &nbsp; &nbsp;中国第一位编写出版葡萄酒书籍的80后，深圳最大的美食美酒栏目主编，ISG国际侍酒师协会讲师；主办的活动包括：品味广东美食之旅；名厨大师班，厨艺培训课程；尝鲜高端食材品鉴交流会；大咖、小宴顶级美食美酒品鉴会。&nbsp; &nbsp; &nbsp; &nbsp;杨广先生曾自己编写过第一本新书《葡萄酒精选指南》已由广东省出版集团和广东人民出版社出版，并在各大书城及购书中心上架销售。",  //会员简介
     *     "path": "http://yummy194.cn/uploads/member/15989430702/56dd36c67c67b.jpg",  //会员头像
     *     "address": "天河区",
     *     "label": null,  //会员标签
     *     "wealth": 21,  //送米
     *     "customers": 0,  //赏味
     *     "people": {
     *          "info": [   //送米人信息
     *              {
     *              "member_id": "18",
     *              "nickname": "何六二",
     *              "path": "/uploads/member/18689315230/55473b5e088ad.jpg"
     *              },
     *              {
     *              "member_id": "1",
     *              "nickname": "李广先生",
     *              "path": "/uploads/member/18565765105/55e51332d73ef.jpg"
     *              },
     *              {
     *              "member_id": "2",
     *              "nickname": "你好",
     *              "path": "/uploads/member/13750344681/559a94a63853e.jpg"
     *              }
     *          ],
     *          "count": 3   //送米人数
     *          }
     *     },
     *     {
     *     "id": "10395",
     *     "name": "ANNY",
     *     "introduce": "大家好，我是anny chen，一个狂热的牛肉爱好者，在欧洲生活了十年，在世界性葡萄酒、烈酒专业媒体界获得一定知名度，为了让国内同胞们吃到世界上最顶级的牛肉之一日本和牛，毅然回国创立了禾口农场，潜心研究肉质细腻柔软、霜降纹路的黑毛和牛的养殖，纯种基因、传统饲养、细致切割、专业烹饪，每一道工序就不放过， 让国人吃到新鲜的顶级牛肉盛宴。",
     *     "path": "http://wx.qlogo.cn/mmopen/1Qw8iaBGVXgOJ89Xica2Cz0FGkK7Aia7sFBxIXWOE4gxJrXwx72Nxv2Y615azUQG8ZDSwialoiaOJuCAtyeYiaKmgd2TIc4LXEhovia/0",
     *     "address": "天河区",
     *     "label": null,
     *     "wealth": 0,
     *     "customers": 0,
     *     "people": 0
     *     },
     *     {
     *     "id": "10354",
     *     "name": "Ken",
     *     "introduce": "1990年、2005年两次获得“法国蓝带奖” 还成为了法国最大的厨艺学校Ecole de Lenotre 全校唯一的华人教授 他就是Ken叔",
     *     "path": "http://wx.qlogo.cn/mmopen/jZUIEF2vTwyLPfv3Tgj5EfIQfMXiaunE0VawWvugxR9dGMSLCMzKmZuOl0v7NTCz9IF7tnEffkkr1vJ4LRSsibkX1W5RvgtoTT/0",
     *     "address": "天河区",
     *     "label": null,
     *     "wealth": 0,
     *     "customers": 0,
     *     "people": 0
     *     },
     *     {
     *     "id": "10351",
     *     "name": "Sam 燊",
     *     "introduce": "【ONE CHEF专业烘焙师】 Sam说：每次闻着烤箱里飘出来的烘烤香味，看着甜点从面粉、鸡蛋、牛奶、黄油这些最普通的原料，变成最美味的西点，就是他最大的幸福；而分享美食、把身边的人都变成魔法西点师是他最大的愿望。   【ONE CHEF场地介绍】 ONE CHEF是广州为数不多开设在购物广场里，全方位进行体验式烹饪活动的工作室。在cooking studio里面会定期举办烹饪教学、美食分享等活动。在学习过程中学员将全程使用西门子的电器设备以及国外高档厨具品牌提供的产品，是你梦寐以求的厨房体验。",
     *     "path": "http://wx.qlogo.cn/mmopen/hRkOoB5ZTmJwibRgA7gtkYZuBtwu4XAChrAMMia336MA8JJNorRyaOeu65hia6fwYG1YIZAZ3wzhJZVnMWebUK7iaCA5d7nRGeud/0",
     *     "address": "天河区",
     *     "label": null,
     *     "wealth": 0,
     *     "customers": 0,
     *     "people": 0
     *     }
     *     ]
     */
    public function index(){
        $order = I('post.order');
        $tag_id = I('post.tag_id',null);
        $city_id = I('post.city_id',null);
        $page = I('get.page',1);

        $where['state'] = 1;
        if(!empty($city_id))$where['A.city_id'] = $city_id;
        if(!empty($tag_id))$where['D.tag_id'] = $tag_id;

        $rs = D('MemberDarenView')->order('A.id desc')->where($where)->group('A.id')->page($page,5)->select();

        if(!empty($rs)) {
            foreach ($rs as $row) {
                $idandpath = [
                    'id' => $row['member_id'],
                    'name' => $row['nickname'],
                    'introduce' => trim(strip_tags($row['member_introduce'])),
                    'path' => thumb($row['path'], 2),
                    'address' => $row['city_name'].$row['name_type']
                ];
                //查找会员标签
                $labels = M('member_tag')->join('__TAG__ ON __MEMBER_TAG__.tag_id=__TAG__.id')->field('ym_tag.name')->where('ym_member_tag.member_id=' . $row['member_id'])->select();
                if (!empty($labels)) {
                    foreach ($labels as $row0) {
                        $idandpath['label'][] = $row0['name'];
                    }
                } else {
                    $idandpath['label'] = null;
                }

                //送米
                $wealth = M('member_wealth')->join('__MEMBER_WEALTH_LOG__ ON __MEMBER_WEALTH_LOG__.member_wealth_id=__MEMBER_WEALTH__.id')->field('ym_member_wealth_log.quantity')->where('ym_member_wealth_log.type=\'huoqu\' and ym_member_wealth.member_id=' . $row['member_id'] . ' and ym_member_wealth.wealth=2')->select();
                if (!empty($wealth)) {
                    foreach ($wealth as $row1) {
                        $idandpath['wealth'] += abs($row1['quantity']);
                    }
                } else {
                    $idandpath['wealth'] = 0;
                }

                $idandpath['customers'] = D('ShangweiView')->where(['member_id' => $row['member_id'], 'act_status' => ['IN', '1,2,3,4'], 'status' => 1])->getField('count');

                //已送米人数,及其头像信息
                $target = M('member_wealth')->where(['member_id'=>$row['member_id'],'wealth'=>2])->getField('id');

                $re = D('SongMiDarenView')->where(['B.target'=>$target,'B.type'=>'zengsong'])->order('abs(sum(B.quantity)) desc')->group('B.member_wealth_id')->select();
                if(!empty($re)){
                    foreach($re as $key3=>$row3){
                        $idandpath['people']['info'][] = $row3;
                    }
                    $idandpath['people']['count'] = count($idandpath['people']['info']);
                }else{
                    $idandpath['people']['count'] = 0;
                    $idandpath['people']['info'] = null;
                }

                $data[] = $idandpath;
            }


            //按热度排序
            if ($order == '1') {
                for ($i = 0; $i < count($data); $i++) {
                    for ($j = $i + 1; $j <= (count($data) - $i - 1); $j++) {
                        if ($data[$j]['customers'] > $data[$i]['customers']) {
                            $temp = $data[$i];
                            $data[$i] = $data[$j];
                            $data[$j] = $temp;
                        }
                    }
                }
            }
        }
        $this->ajaxReturn($data);
    }

    /**
     * @apiName 达人空间数据
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} member_id: 会员ID
     *
     * @apiSuccessResponse
     * {
     *     "base_info": {  //基本信息
     *          "tips": "78", //活动数量
     *          "shangwei": "319",  //赏味
     *          "follow": "0",  //关注
     *          "fans": "0",  //粉丝
     *          "bang": "1",  //食报
     *          "mibi": 999,  //米币
     *          "doing": "18",  //正在举行的活动
     *          "daren_info": {  //简介和达人图片
     *              "introduce": "我是小花Rita，从在职场拼杀的上班族身份脱离出来，不要整天开会加班，美食才是真爱！现在经营着自己小小的工作室—C家美食工作室，不仅是专职美食讲师，更是自由美食撰稿人，擅长西点烘焙、简易西餐以及家常菜，告诉你一枚温暖的女子应该是这样的，不仅会烹饪，更要会生活。 &nbsp;C家美食工作室，是一个寻着美味才能找到的地方，温馨的感觉让你可以放下心里的些许烦恼，沉浸在有着浓浓香气的厨房世界里。教学内容细腻易懂，零基础的学员也可以学会哦，每次吖咪课程都马上被秒杀，你的手速可要快点哦~ &nbsp;&nbsp;一段闹市里的治愈系时光，一次给自己的心灵小憩。做一份精致的甜品，品一道不一样的清茶，在自己动手创造美好的事物的同事，相信也会被这份静谧和甜美所打动。&nbsp;  &nbsp;",
     *              "pic_group_id": "1",
     *              "pic_path": "http://img.m.yami.ren/member/2016-04-12/Q0NWY5YjI1ZTViZDFmMGI3NWJhMDBj_320x320.jpg",
     *              "group_path": [
     *                  "http://yummy194.cn/uploads/member/18565765105/55e51332d73ef.jpg",
     *                  "http://yummy194.cn/uploads/member/13750344681/559a94a63853e.jpg"
     *              ]
     *     },
     *     "comment": "9"  //评论
     *     },
     *     "dynamic": {  //动态信息
     *          "tips": {  //活动
     *              "id": "3042",  //活动ID
     *              "nickname": "弦霄",  //发布者昵称
     *              "wealth": 999,  //送米
     *              "customers": "319",  //赏味
     *              "member_id": "6395",  //发布者ID
     *              "headpic": "http://img.m.yami.ren/member/2016-04-12/Q0NWY5YjI1ZTViZDFmMGI3NWJhMDBj_320x320.jpg",  //发布者头像
     *              "mainpic": "http://yummy194.cn/uploads/20160309/56df96d630bd5.jpg",  //活动主图
     *              "catname": "其他",  //活动分类
     *              "tagname": "中餐",  //活动标签
     *              "title": "吖咪分享会| 小花×Magic cici的奇幻塔罗", //活动名
     *              "price": "88.00",  //活动价格
     *              "start_time": "1461000000",  //开始时间
     *              "end_time": "1461010000",  //结束时间
     *              "date": "04月19号 周2 01:20 - 04:06",
     *              "address": "广州市越秀区建设六马路47号201（C家美食工作室"  //活动地址
     *          },
     *          "bang": [
     *              {
     *                  "bang_id": "4",  //食报ID
     *                  "member_id": "6395",  //发布者ID
     *                  "content": "t4",  //食报内容
     *                  "type": "0",  //食报类型
     *                  "type_id": null,  //类型ID（文章或活动ID）
     *                  "send_time": "1460462594",  //发布时间
     *                  "nickname": "弦霄",  //发布者昵称
     *                  "head_pic_path": "http://img.m.yami.ren/member/2016-04-12/Q0NWY5YjI1ZTViZDFmMGI3NWJhMDBj_320x320.jpg",  //发布者头像
     *                  "count": null,  //打赏数
     *                  "comment": null,  //评论数
     *                  "pics_path": "http://yummy194.cn/themes/default/images/1.png",  //食报主图
     *                  "pics_group_path": [    //食报主图
     *                      "http://yummy194.cn/uploads/member/18565765105/55e51332d73ef.jpg",
     *                      "http://yummy194.cn/uploads/member/13750344681/559a94a63853e.jpg"
     *                  ],
     *                  "hot": 0    //是否热点（0-否，1-是）
     *              }
     *              ]
     *          }
     *     }
     */
    public function darenZone(){
        $member_id = I('post.member_id', session('member.id'));

        //判断是否是达人
        $dareView = new \Daren\Model\DarenViewModel();
        $info = $dareView->where(['member_id' => $member_id, 'tag_id' => 18])->find();
        if(empty($info))$this->error('非达人，无法进入达人服务中心！');

        $data = [];
        //基本信息$data['base_info']
        //获取总活动数
        $tips = M('tips')->join('ym_tips_sub on ym_tips.id=tips_id')->where(['member_id' => $member_id , 'is_pass'=>1 , 'is_public' => 1, 'status' => 1])->count();
        $data['base_info']['tips'] = (string)$tips;
        //获取赏味数
        $shangweiView = new \Member\Model\ShangweiViewModel();
        $data['base_info']['shangwei'] = $shangweiView->where(['member_id' => $member_id, 'act_status' => ['IN', '1,2,3,4'], 'status' => 1])->getField('count');
        //获取关注数
        $data['base_info']['follow'] = M('MemberFollow')->where(['member_id' => $member_id])->count();
        //获取粉丝数
        $data['base_info']['fans'] = M('MemberFollow')->where(['follow_id' => $member_id])->count();
        //获取食报数
        //$data['base_info']['bang'] = M('Bang')->where(['member_id' => $member_id])->count();
        //获取送米数
        //$wealthView = new \Member\Model\WealthViewModel();
        //$data['base_info']['mibi'] = $wealthView->where(['member_id' => $member_id, 'type' => 'huoqu', 'wealth' => 2])->getField('sum');
        //$data['base_info']['mibi'] = $data['base_info']['mibi']==null?0:(int)$data['base_info']['mibi'];
        //获取进行中的报名活动数量(即将开始)
        $doing = D('TipsView')->where(['A.member_id' => $member_id, 'A.is_pass' => 1, 'A.status' => 1, 'C.end_time' => ['GT', time()], 'is_public' => 1])->group('A.id')->select();
        $doing = empty($doing)?0:count($doing);
        $data['base_info']['doing'] = $doing;
        //获取举办过的活动
        $data['base_info']['over_tips'] = (string)($tips - $doing);
        //是否关注该达人
        $is_follow = M('MemberFollow')->where(['member_id'=>session('member.id'),'follow_id'=>$member_id])->find();
        $data['base_info']['is_follow'] = empty($is_follow)?0:1;
        //获取达人基本信息
        $data['base_info']['daren_info']['nickname'] = $info['nickname'];
        $data['base_info']['daren_info']['introduce'] = preg_replace('/\&\w+?;/', '', strip_tags($info['member_introduce']));
        $data['base_info']['daren_info']['pic_path'] = thumb($info['path'],2);

        //会员封面背景图组
        if(!empty($info['cover_pic_id'])){
            $path = M('pics')->where(['id'=>$info['cover_pic_id']])->getField('path');
            $data['base_info']['daren_info']['cover_path'] = thumb($path);
        }else{
            $data['base_info']['daren_info']['cover_path'] = $data['base_info']['daren_info']['pic_path'];
        }

        //会员拿手菜图组
//        if(!empty($data['base_info']['daren_info']['pic_group_id'])){
//            $group_path = M('pics')->where(['group_id'=>$data['base_info']['daren_info']['pic_group_id']])->getField('path',true);
//            foreach($group_path as $row){
//                $data['base_info']['daren_info']['group_path'][] = thumb($row,7);
//            }
//        }else{
//            $data['base_info']['daren_info']['group_path'][] = [];
//        }

        $tipsIds = M('Tips')->where(['member_id'=>$member_id,'status'=>['IN','1,2'],'is_pass'=>1])->getField('id',true);
        $goodsIds = M('Goods')->where(['member_id'=>$member_id,'status'=>1,'is_pass'=>1])->getField('id',true);
        if(!empty($tipsIds) && !empty($goodsIds)){
            $condition = "(A.type=0 and A.type_id in(".join(',',$tipsIds).")) or (A.type=1 and A.type_id in(".join(',',$goodsIds)."))";
        }elseif(!empty($tipsIds)){
            $condition = "(A.type=0 and A.type_id in(".join(',',$tipsIds)."))";
        }elseif(!empty($goodsIds)){
            $condition = "(A.type=1 and A.type_id in(".join(',',$goodsIds)."))";
        }else{
            $condition = "A.id < 0";
        }
        $condition .= ' and A.pid is null';//达人自己回复的不算入内
        $commentView = new \Home\Model\CommentViewModel();
        $data['base_info']['comment'] = $commentView->where($condition)->count();

        //查询是否已关注此达人
        $follow = 0;
        if(session('?member')){
            $memberFollow = M('MemberFollow')->where(['member_id' => session('member.id'), 'follow_id' => $member_id])->find();
            if(!empty($memberFollow))$follow = 1;
        }
        $data['base_info']['isfollow'] = $follow;

        //动态信息$data['dynamic']
        //最新活动信息1条
        $tipsview = new \Goods\Model\TipsViewModel();
        $tips_rs = $tipsview->where(['A.member_id'=>$member_id,'A.status'=>1,'A.is_pass'=>1,'is_public'=>1,'F.start_time'=>['GT',time()]])->group('A.id')->order('F.start_time')->limit(1)->select();
        if(!empty($tips_rs)){
            //获取所有tips的id
            $ids = [];
            foreach($tips_rs as $key => $row){
                $ids[] = $row['id'];
            }
            /*查询出所有tips的标签*/
            $tipstagview = new \Goods\Model\TipstagViewModel();
            $tipstag=$tipstagview->where('A.tips_id in (' . join(',', $ids) . ')')->select();
            //查询出所有的订单活动
            $orderWaresView = new \Goods\Model\OrderWaresViewModel();
            $rs = $orderWaresView->field(['ware_id', 'count(ware_id) as num'])->where(array('type' => 0, 'ware_id' => array('IN', join(',', $ids)), 'act_status' => array('LT', 6)))->group('ware_id')->select();
            foreach($tips_rs as $k => $r){
                //把对应的标签加入到相应的tips中
                $tagnames= [];
                foreach($tipstag as $tt){
                    if($tt['tips_id']==$r['id'])$tagnames[] = $tt['name'];
                }
                $tips_rs[$k]['tags']=$tagnames;

                //运用缩略图
                $tips_rs[$k]['path'] = thumb($r['path'], 1);
                $tips_rs[$k]['headpic'] = thumb($r['headpic'], 2);

                //把剩余份数加入到相应的tips中
                $amount = $r['restrict_num'];
                foreach($rs as $row){
                    if($row['ware_id']==$r['id'])$amount = $r['restrict_num'] - $row['num'];
                }
                $tips_rs[$k]['amount'] = $amount < 0 ? 0 : $amount;

                //送米
//                $wealth = M('member_wealth')->join('__MEMBER_WEALTH_LOG__ ON __MEMBER_WEALTH_LOG__.member_wealth_id=__MEMBER_WEALTH__.id')->field('ym_member_wealth_log.quantity')->where('ym_member_wealth_log.type=\'huoqu\' and ym_member_wealth.member_id=' . $r['member_id'] . ' and ym_member_wealth.wealth=2')->select();
//                if (!empty($wealth)) {
//                    foreach ($wealth as $row1) {
//                        $tips_rs[$k]['wealth'] += abs($row1['quantity']);
//                    }
//                } else {
//                    $tips_rs[$k]['wealth'] = 0;
//                }

                $tips_rs[$k]['customers'] = $shangweiView->where(['member_id' => $r['member_id'], 'act_status' => ['IN', '1,2,3,4'], 'status' => 1])->getField('count');
            }
        }
        $_data = [];
        foreach($tips_rs as $row){

            if(date('md',$row['start_time'])==date('md',$row['end_time'])){
                //开始和结束时间在同一天
                $row['date'] = date('m月d号 周N H:i',$row['start_time']).' - '.date('H:i',$row['end_time']);
            }else{
                //开始和结束时间不在同一天
                $row['date'] = date('m月d号 周N H:i',$row['start_time']).' - '.date('m月d号 周N H:i',$row['end_time']);
            }

            $_data[] = [
                'id' => $row['id'],
                'nickname' => $row['nickname'],
                'wealth' => $row['wealth'],
                'customers' => $row['customers'],
                'member_id' => $row['member_id'],
                'headpic' => $row['headpic'],
                'mainpic' => $row['path'],
                'catname' => $row['catname'],
                'tagname' => $row['tags'],
                'title' => $row['title'],
                'price' => $row['price'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'date' => $row['date'],
                'address' => $row['address']
            ];
        }
        $data['dynamic']['tips'] = $_data[0]?$_data[0]: [];

        //最新食报信息3条
//        $heat = M('config')->where(array('threshold'=>'bang_hot'))->getField('value');
//        $where = '(D.target=0 or D.target is null) and (D.type=1 or D.type is null)';
//        $where.=" and A.member_id=".$member_id;
//        $bangListView = new \Member\Model\BangListViewModel();
//        $bang_rs = $bangListView->where($where)->order('send_time desc')->group('A.id')->limit(3)->select();
//
//        if(!empty($bang_rs)){
//            //数据处理
//            foreach ($bang_rs as $key=>$row){
//                $pics_id[] = $row['pic_id'];
//                $pics_group[] = $row['pic_group_id'];
//                $bang_id[] = $row['bang_id'];
//
//                $bang_rs[$key]['comment'] = null;
//                $bang_rs[$key]['pics_path'] = null;
//                $bang_rs[$key]['pics_group_path'] = null;
//
//                if($row['send_time']==0)$bang_rs[$key]['send_time']=(string)strtotime($bang_rs[$key]['datetime']);
//                unset($bang_rs[$key]['datetime']);
//            }
//            $pics_id = join(',',$pics_id);
//            $pics_group = join(',',$pics_group);
//            $bang_id = join(',',$bang_id);
//
//            //bang主图
//            $pics_arr = M('pics')->where('id in (' .$pics_id. ')')->field('id,path')->select();
//            //bang组图
//            $pics_group_arr = M('pics')->where('group_id in (' .$pics_group. ')')->field('group_id,path')->select();
//            //评论信息
//            $bangCommentView = new \Member\Model\BangCommentViewModel();
//            $comment_arr = $bangCommentView->where('A.type_id in('. $bang_id .')')->select();
//
//
//            //获取评论组图id
//            foreach($comment_arr as $k=>$rs){
//                $comment_arr[$k]['comment_pics_group_path'] = array();
//                if(!empty($rs['pics_group_id'])){
//                    $comment_pics_group_ids[] =$rs['pics_group_id'];
//                }
//                $comment_ids[] = $rs['id'];
//            }
//            $comment_pics_group_ids = join(',',$comment_pics_group_ids);
//            $comment_ids = join(',',$comment_ids);
//
//            //@了谁
//            if(!empty($comment_ids)){
//                $at_list = M('MemberCommentAt')->join('__MEMBER__ ON __MEMBER_COMMENT_AT__.member_id=__MEMBER__.id')->where('comment_id in('.$comment_ids.')')->field('comment_id,nickname')->select();
//            }else{
//                $at_list = array();
//            }
//
//
//            if(!empty($comment_pics_group_ids)){
//                $comment_pics_group = M('pics')->where('group_id in('.$comment_pics_group_ids.')')->field('group_id,path')->select();
//            }else{
//                $comment_pics_group = array();
//            }
//
//            //添加评论组图路径，@信息，删除无用数据
//            foreach($bang_rs as $key=>$row){
//                $bang_rs[$key]['head_pic_path'] = thumb($bang_rs[$key]['head_pic_path'],2);
//                foreach($comment_arr as $key0=>$row0){
//                    if($row['bang_id'] == $row0['bang_id']){
//                        unset($comment_arr[$key0]['stars']);
//                        unset($comment_arr[$key0]['type']);
//                        unset($comment_arr[$key0]['type_id']);
//                        unset($comment_arr[$key0]['bang_id']);
//
//                        //评论图组
//                        $comment_arr[$key0]['comment_pics_group_path'] = array();
//                        foreach($comment_pics_group as $key3=>$row3){
//                            if($row3['group_id'] == $row0['pics_group_id']){
//                                $comment_arr[$key0]['comment_pics_group_path'][] = thumb($row3['path'],5);
//                            }
//                        }
//                        //@谁
//                        $comment_arr[$key0]['at_name'] = array();
//                        foreach($at_list as $key4=>$row4){
//                            if($row4['comment_id'] == $row0['id']){
//                                $comment_arr[$key0]['at_name'][] = $row4['nickname'];
//                            }
//                        }
//                        $bang_rs[$key]['comment'][$key0] = $comment_arr[$key0];
//                    }
//                }
//                //bang主图
//                foreach($pics_arr as $row1){
//                    if($row['pic_id'] == $row1['id']){
//                        $bang_rs[$key]['pics_path'] = thumb($row1['path'],3);
//                    }
//                }
//                //bang组图
//                foreach($pics_group_arr as $row2){
//                    if($row['pic_group_id'] == $row2['group_id']){
//                        $bang_rs[$key]['pics_group_path'][] = thumb($row2['path'],3);
//                    }
//                }
//                unset($bang_rs[$key]['pic_id']);
//                unset($bang_rs[$key]['pic_group_id']);
//
//                if($row['count'] >= $heat){
//                    $bang_rs[$key]['hot'] = 1;
//                    $hot_arr[] = $bang_rs[$key];
//                }else{
//                    $bang_rs[$key]['hot'] = 0;
//                    $_arr[] = $bang_rs[$key];
//                }
//            }
//            if(is_null($hot_arr))$hot_arr = array();
//            if(is_null($_arr))$_arr = array();
//            $bang_rs = array_merge($hot_arr,$_arr);
//            $data['dynamic']['bang'] = $bang_rs;
//        }else{
//            $data['dynamic']['bang'] = array();
//        }


        $this->ajaxReturn($data);
    }

    /**
     * @apiName 主厨达人空间数据
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} member_id: 会员ID
     *
     * @apiSuccessResponse
     *[
     *  {
     *      "income": {
     *        "get": 100,
     *        "take": -60
     *      },
     *      "mibi": 0, //总米币
     *      "shangwei": "330", //赏味
     *      "follow": "1",  //关注数
     *      "fans": "0"  //粉丝
     *  }
     *}
     */
    public function cookerDarenZone(){
        $member_id = I('post.member_id',session('member.id'));

        //判断是否是达人
        $dareView = new \Daren\Model\DarenViewModel();
        $info = $dareView->where(array('member_id' => $member_id))->find();
        if(empty($info))$this->error('非达人，无法进入达人服务中心！');

        //获取余额数量
        $data = array();
        $wealthView = new \Member\Model\WealthViewModel();
        $data['income']['get'] = $wealthView->where(['member_id' => $member_id, 'type' => ['IN','chongzhi,huoqu,shoumai'], 'wealth' => 1])->getField('sum');
        $data['income']['get'] = $data['income']['get']==null?0:(int)$data['income']['get'];
        $data['income']['take'] = $wealthView->where(['member_id' => $member_id, 'type' => ['IN','tuikuan,tixian'], 'wealth' => 1])->getField('sum');
        $data['income']['take'] = $data['income']['take'] = null?0:(int)$data['income']['take'];

        //获取送米数
        $wealthView = new \Member\Model\WealthViewModel();
        $data['mibi'] = $wealthView->where(['member_id' => $member_id, 'type' => 'huoqu', 'wealth' => 2])->getField('sum');
        $data['mibi'] = $data['mibi']==null?0:(int)$data['mibi'];
        //获取赏味数
        $shangweiView = new \Member\Model\ShangweiViewModel();
        $data['shangwei'] = $shangweiView->where(['member_id' => $member_id, 'act_status' => ['IN', '1,2,3,4'], 'status' => 1])->getField('count');
        //获取关注数
        $data['follow'] = M('MemberFollow')->where(['member_id' => $member_id])->count();
        //获取粉丝数
        $data['fans'] = M('MemberFollow')->where(['follow_id' => $member_id])->count();

        $this->ajaxReturn($data);
    }

}