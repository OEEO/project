<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class CouponController extends MainController
{
    Protected $pagename = '优惠券管理';

    //优惠券列表
    public function index(){
        $this->actname = '优惠券列表';
        $condition = array();

        $name = I('get.name');
        $categoryType = I('get.categoryType');

        if($name != '')$condition['a.name'] = array('LIKE','%'.$name.'%');
        if($categoryType != '')$condition['a.category'] = array('EQ',$categoryType);

        $this->assign('search_name',$name);
        $this->assign('categoryType',$categoryType);

        $condition['category'] = array('IN','0,4');
        $datas['datas'] = $this->m2('coupon as a')->join('LEFT JOIN __MEMBER__ as b ON a.member_id = b.id')->field('a.id,a.name,a.value,a.start_time,a.end_time,a.value,a.category,a.type,a.status,a.min_amount,a.content,a.count,a.remarks,b.nickname')->page(I('get.page'), 30)->where($condition)->order('id desc')->select();
//print_r($datas['datas']);exit;
        //数据处理
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['value'] = $datas['datas'][$key]['value'].'(最低消费：'.$datas['datas'][$key]['min_amount'].')';
            $datas['datas'][$key]['start_time'] = $datas['datas'][$key]['start_time']==0?'不限':date('Y-m-d H:i',$row['start_time']);
            $datas['datas'][$key]['end_time'] = $datas['datas'][$key]['end_time']==0?'不限':date('Y-m-d H:i',$row['end_time']);
            $datas['datas'][$key]['value_and_content'] = $datas['datas'][$key]['content'].$datas['datas'][$key]['value'];
           /* $member_mod ='';
            if($row['member_id']){
                $member_mod = $this->m2('member')->where('id='.$row['member_id'])->find();
            }*/
            $datas['datas'][$key]['release'] = $datas['datas'][$key]['nickname']?$datas['datas'][$key]['nickname']:'系统';
            if($row['category'] =='0')$datas['datas'][$key]['category'] = '营销券';
            if($row['category'] =='1')$datas['datas'][$key]['category'] = '邀请券';
            if($row['category'] =='2')$datas['datas'][$key]['category'] = '注册券';
            if($row['category'] =='4')$datas['datas'][$key]['category'] = '微信卡券-自定义';
            if($row['type'] =='0')$datas['datas'][$key]['type'] = '抵价券';
            if($row['type'] =='1')$datas['datas'][$key]['type'] = '折扣券';
            if($row['type'] =='2')$datas['datas'][$key]['type'] = '礼品券';
            if($row['status'] =='0')$datas['datas'][$key]['status'] = '未发布';
            if($row['status'] =='1')$datas['datas'][$key]['status'] = '已发布';
        }

        $datas['operations'] = array(
            //'修改优惠券' => "update(%id)",
           /* //'发送优惠券' => "getUser(%id)",*/
            '详情' => "detail(%id)",
            '修改优惠券' => array(
                'style' => 'success',
                'fun' => 'update(%id)',
                'condition' => '%category !== 微信卡券-自定义'
            )
            /*'取消发布' => array(
                'style' => 'danger',
                'fun' => 'unrelease(%id)',
                'condition' => '%status == 已发布'
            ),*/
        );
        $datas['pages'] = array(
            'sum' => $this->m2('coupon as a')->where($condition)->count(),
            'count' => 30,
        );

        $datas['lang'] = array(
            'release' => '发布者',
            'category' => '分类',
            'name' => '优惠券名称',
            'type' => '类型',
            'value_and_content'=>'优惠值/礼品内容',
            /*'value' => '优惠值',
            'content' => '礼品内容',*/
            'count' => '数量',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            //'min_amount' => '最低消费',
            'remarks' => '备注',
        );

        $this->assign($datas);
        $this->view();
    }

    //上传微信卡券图标 300 x 300
    public function ajaxUpload()
    {
        //图片上传
        $info = parent::ajaxUpload(true);
        $info = $info[0];
        if($info['status'] == 0)$this->error('上传失败!');
        $info = $info['info'];
        //上传到微信
        $cfile = curl_file_create(C('UPLOAD_CONFIG.rootPath') .  $info['path'], 'image/jpeg'); // try adding
        $imgdata = ['buffer' => $cfile];
        $accessToken = getAccessToken();
        $header = ['User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://someaddress.tld','Content-Type: multipart/form-data'];
        $rs = $this->curl_post('https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=' . $accessToken, $imgdata, $header);
        $rs = json_decode($rs, true);
        if(isset($rs['url'])){
            $info['url'] = $rs['url'];
            $info['path'] = thumb($info['path'], [300,300]);
            $this->ajaxReturn([[
                'status' => 1,
                'info' => $info
            ]]);
        }else{
            $this->error($rs['errmsg']);
        }
    }

    //生成微信默认卡券
    public function wxDefaultCoupon(){
        $this->actname = '生成微信默认卡券';

        if(IS_POST){

            $member_tag = I('post.member_tag');
            $tips_tag = I('post.tips_tag');
            $goods_tag = I('post.goods_tag');

            $data = [];
            $data['member_id'] = I('post.CouponMember_id', null);
            $data['category'] = $category = I('post.CouponCategory');
            $data['name'] = $name = I('post.CouponName');
            $data['value'] = $value = I('post.CouponValue', 0);
            $data['content'] = $content = I('post.CouponContent');
            $data['count'] = $count = I('post.CouponCount');
            $data['start_time'] = $start_time = strtotime(I('post.CouponStart_time'))?strtotime(I('post.CouponStart_time')):0;
            $data['end_time'] = $end_time = strtotime(I('post.CouponEnd_time'))?strtotime(I('post.CouponEnd_time')):0;
            $data['min_amount'] = $min_amount = I('post.CouponMin_amount');
            $data['member_tags'] = $member_tags = I('post.checkedAll_member_tag')?'*':join(',',$member_tag);
            $data['tips_tags'] = $tips_tags = I('post.checkedAll_tips_tag')?'*':join(',',$tips_tag);
            $data['goods_tags'] = $goods_tags = I('post.checkedAll_goods_tag')?'*':join(',',$goods_tag);
            $data['remarks'] = $remarks = I('post.remarks');
            $data['pic_id'] = I('post.pic_id', null);
            $data['status'] = 1;

            //添加到微信卡券
            $url = I('post.url');
            $getlimit = I('post.getLimit');
            $accessToken = getAccessToken();

            $accept_category = [];
            if($member_tags != '*' && !empty($member_tags)) {
                $accept_category[] = '限'. $member_tags .'用户使用';
            }
            if($tips_tags != '*' && !empty($tips_tags)) {
                $accept_category[] = '限'. $tips_tags .'活动使用';
            }
            if($goods_tags != '*' && !empty($goods_tags)) {
                $accept_category[] = '限'. $goods_tags .'活动使用';
            }
            if(empty($accept_category))$accept_category = '全场通用';
            else $accept_category = join('|', $accept_category);

            $post_data = [
                "card" => [
                    "card_type" => "CASH",
                    "cash" => [
                        "base_info" => [
                            "logo_url" => $url,
                            "brand_name" => "吖咪美食精选",
                            "code_type" => "CODE_TYPE_NONE",
                            "title" => $name,
                            "color" => "Color070",
                            "notice" => "仅限吖咪微商城中使用",
                            "description" => $remarks,
                            "sku" => [
                                "quantity" => $count
                            ],
                            "quantity" => $count,
                            "date_info" => [
                                "type" => "DATE_TYPE_FIX_TIME_RANGE",
                                "begin_timestamp" => $start_time,
                                "end_timestamp" => $end_time
                            ],
                            "use_custom_code" => true,
                            "center_title" => "立即使用",
                            "center_url" => "http://api.". WEB_DOMAIN ."/member/coupon/into.html?type=2",
                            "custom_url_name" => "立即使用",
                            "custom_url" => "http://" . WEB_DOMAIN,
                            "get_limit" => $getlimit,
                            "can_share" => true,
                            "can_give_friend" => true
                        ],
                        "least_cost" => $data['min_amount']*100,
                        "reduce_cost" => $data['value']*100,
                    ]
                ]
            ];

            $rs = $this->curl_post('https://api.weixin.qq.com/card/create?access_token=' . $accessToken, json_encode($post_data));
            if($rs['errcode']==0 && $rs['errmsg']=='ok'){
                $data['wx_sn'] = $rs['card_id'];
            }else{
                var_dump($rs);
                exit;
                $this->error('微信卡券生成失败');
            }
            $this->m2('coupon')->data($data)->add();
            $this->success('已生成优惠券','index');
            exit;
        }

        $member_tag = $this->m2('tag')->where('type=0 and official=1')->select();
        $tips_tag = $this->m2('tag')->where('type=1 and official=1')->select();
        $goods_tag = $this->m2('tag')->where('type=2 and official=1')->select();
        $this->assign('member_tag',$member_tag);
        $this->assign('tips_tag',$tips_tag);
        $this->assign('goods_tag',$goods_tag);
        $this->view();

    }

    public function wxCustomCoupon(){
        $this->actname = '生成微信自定义券';

        if(IS_POST){

            $member_tag = I('post.member_tag');
            $tips_tag = I('post.tips_tag');
            $goods_tag = I('post.goods_tag');

            $data = array();
            $data['member_id'] = I('post.CouponMember_id', null);
            $data['category'] = $category = I('post.CouponCategory');
            $data['name'] = $name = I('post.CouponName');
            $data['type'] = $type = I('post.CouponType');
            $data['value'] = $value = I('post.CouponValue', 0);
            $data['content'] = $content = I('post.CouponContent');
            $data['count'] = $count = I('post.CouponCount');
            $data['start_time'] = $start_time = strtotime(I('post.CouponStart_time'))?strtotime(I('post.CouponStart_time')):0;
            $data['end_time'] = $end_time = strtotime(I('post.CouponEnd_time'))?strtotime(I('post.CouponEnd_time')):0;
            $data['min_amount'] = $min_amount = I('post.CouponMin_amount');
            $data['member_tags'] = $member_tags = I('post.checkedAll_member_tag')?'*':join(',',$member_tag);
            $data['tips_tags'] = $tips_tags = I('post.checkedAll_tips_tag')?'*':join(',',$tips_tag);
            $data['goods_tags'] = $goods_tags = I('post.checkedAll_goods_tag')?'*':join(',',$goods_tag);
            $data['remarks'] = $remarks = I('post.remarks');
            $data['pic_id'] = I('post.pic_id', null);

            //添加到微信卡券
            $url = I('post.url');
            $getlimit = I('post.getLimit');
            $accessToken = getAccessToken();
            if($data['type']==0)$wx_type = 'CASH';
            if($data['type']==1)$wx_type = 'DISCOUNT';
            if($data['type']==2)$wx_type = 'GIFT';

            $post_data = '{
                "card": {
                    "card_type": "'.$wx_type.'",
                    "'.strtolower($wx_type).'": {
                        "base_info": {
                            "logo_url": "'.$url.'",
                            "brand_name": "吖咪美食",
                            "code_type": "CODE_TYPE_TEXT",
                            "title": "'.$data['name'].'",
                            "color": "Color070",
                            "notice": "如有疑问请联系。。。",
                            "description": "不可与其他优惠同享",
                            "date_info": {
                                "type": "DATE_TYPE_FIX_TIME_RANGE",
                                "begin_timestamp": '.$data['start_time'].',
                                "end_timestamp": '.$data['end_time'].'
                            },
                            "sku": {
                                "quantity": 0
                            },
                            "use_custom_code": true,
                            "get_custom_code_mode": "GET_CUSTOM_CODE_MODE_DEPOSIT",
                            "bind_openid": false,
                            "can_share": true,
                            "can_give_friend": false,
                            "center_title": "立即使用",
                            "center_url": "http://api.". WEB_DOMAIN ."/member/coupon/into.html?type=2",
                            "custom_url_name": "立即使用",
                            "custom_url": "http://". WEB_DOMAIN,
                            "custom_url_sub_title": "更多惊喜",
                            "promotion_url_name": "更多优惠",
                            "promotion_url": "http://". WEB_DOMAIN,
                            "get_limit": '.$getlimit.'
                        },';
            if($wx_type == 'CASH') {
                $post_data .= '"advanced_info": {

                                "abstract": {
                                    "abstract": "吖咪，期待您的光临",
                                    "icon_url_list": [
                                        "'.$url.'"
                                    ]
                                },
                                "text_image_list": [
                                    {
                                        "image_url": "'.$url.'",
                                        "text": "'.$data['remarks'].'。"
                                    }
                                ]
                            },
                            "least_cost": '. $data['min_amount']*100 .',
                            "reduce_cost": '. $data['value']*100 .'
                        }
                    }
                }';
            }
            if($wx_type == 'DISCOUNT') {
                $post_data .= '"advanced_info": {

                                "abstract": {
                                    "abstract": "吖咪，期待您的光临",
                                    "icon_url_list": [
                                        "'.$url.'"
                                    ]
                                },
                                "text_image_list": [
                                    {
                                        "image_url": "'.$url.'",
                                        "text": "'.$data['remarks'].'。"
                                    }
                                ]
                            },
                            "discount": '.(100-$data['value']).'
                        }
                    }
                }';
            }
            if($wx_type == 'GIFT'){
                $post_data .= '"advanced_info": {
                                "abstract": {
                                    "abstract": "吖咪，期待您的光临",
                                    "icon_url_list": [
                                        "'.$url.'"
                                    ]
                                },
                                "text_image_list": [
                                    {
                                        "image_url": "'.$url.'",
                                        "text": "'.$data['remarks'].'。"
                                    }
                                ]
                            },
                            "gift": "'.$data['content'].'"
                        }
                    }
                }';
            }
            $rs = $this->curl_post('https://api.weixin.qq.com/card/create?access_token=' . $accessToken, $post_data);
            if($rs['errcode']==0 && $rs['errmsg']=='ok'){
                $data['wx_sn'] = $rs['card_id'];
            }else{
                var_dump($rs);
                //exit;
                $this->error('微信卡券生成失败');
            }
            $this->m2('coupon')->data($data)->add();
            $this->success('已生成优惠券','index');
            exit;
        }

        $member_tag = $this->m2('tag')->where('type=0 and official=1')->select();
        $tips_tag = $this->m2('tag')->where('type=1 and official=1')->select();
        $goods_tag = $this->m2('tag')->where('type=2 and official=1')->select();
        $this->assign('member_tag',$member_tag);
        $this->assign('tips_tag',$tips_tag);
        $this->assign('goods_tag',$goods_tag);
        $this->view();
    }

    //添加卡券
    public function add(){
        $this->actname = '添加优惠券';

        if(IS_POST){

            $member_tag = I('post.member_tag');
            $tips_tag = I('post.tips_tag');
            $goods_tag = I('post.goods_tag');

            $data = array();
            $data['member_id'] = I('post.CouponMember_id', null);
            $data['category'] = $category = I('post.CouponCategory');
            $data['name'] = $name = I('post.CouponName');
            $data['type'] = $type = I('post.CouponType');
            $data['value'] = $value = I('post.CouponValue', 0);
            $data['content'] = $content = I('post.CouponContent');
            $data['count'] = $count = I('post.CouponCount');
            $data['start_time'] = $start_time = strtotime(I('post.CouponStart_time'))?strtotime(I('post.CouponStart_time')):0;
            $data['end_time'] = $end_time = strtotime(I('post.CouponEnd_time'))?strtotime(I('post.CouponEnd_time')):0;
            $data['min_amount'] = $min_amount = I('post.CouponMin_amount');
            $data['member_tags'] = $member_tags = I('post.checkedAll_member_tag')?'*':join(',',$member_tag);
            $data['tips_tags'] = $tips_tags = I('post.checkedAll_tips_tag')?'*':join(',',$tips_tag);
            $data['goods_tags'] = $goods_tags = I('post.checkedAll_goods_tag')?'*':join(',',$goods_tag);
            $data['remarks'] = $remarks = I('post.remarks');
            $data['pic_id'] = I('post.pic_id', null);

            if(!is_numeric($data['value']) || $data['value'] < 0)$this->error('优惠数值非法');

            //添加到微信卡券
            /*$url = I('post.url');
            $accessToken = getAccessToken();
            if($data['type']==0)$wx_type = 'CASH';
            if($data['type']==1)$wx_type = 'DISCOUNT';
            if($data['type']==2)$wx_type = 'GIFT';

            $post_data = '{
                "card": {
                    "card_type": "'.$wx_type.'",
                    "'.strtolower($wx_type).'": {
                        "base_info": {
                            "logo_url": "'.$url.'",
                            "brand_name": "吖咪美食",
                            "code_type": "CODE_TYPE_TEXT",
                            "title": "'.$data['name'].'",
                            "color": "Color070",
                            "notice": "如有疑问请联系。。。",
                            "description": "不可与其他优惠同享",
                            "date_info": {
                                "type": "DATE_TYPE_FIX_TIME_RANGE",
                                "begin_timestamp": '.$data['start_time'].',
                                "end_timestamp": '.$data['end_time'].'
                            },
                            "sku": {
                                "quantity": 0
                            },
                            "use_custom_code": true,
                            "get_custom_code_mode": "GET_CUSTOM_CODE_MODE_DEPOSIT",
                            "bind_openid": false,
                            "can_share": true,
                            "can_give_friend": false,
                            "center_title": "立即使用",
                            "center_url": "http://api.m.yami.ren/member/coupon/into.html?type=2",
                            "custom_url_name": "立即使用",
                            "custom_url": "http://m.yami.ren",
                            "custom_url_sub_title": "更多惊喜",
                            "promotion_url_name": "更多优惠",
                            "promotion_url": "http://m.yami.ren",
                            "get_limit": 1
                        },';
            if($wx_type == 'CASH') {
                $post_data .= '"advanced_info": {

                                "abstract": {
                                    "abstract": "吖咪，期待您的光临",
                                    "icon_url_list": [
                                        "'.$url.'"
                                    ]
                                },
                                "text_image_list": [
                                    {
                                        "image_url": "'.$url.'",
                                        "text": "优惠券详细说明"
                                    }
                                ]
                            },
                            "least_cost": '. $data['min_amount']*100 .',
                            "reduce_cost": '. $data['value']*100 .'
                        }
                    }
                }';
            }
            if($wx_type == 'DISCOUNT') {
                $post_data .= '"advanced_info": {

                                "abstract": {
                                    "abstract": "吖咪，期待您的光临",
                                    "icon_url_list": [
                                        "'.$url.'"
                                    ]
                                },
                                "text_image_list": [
                                    {
                                        "image_url": "'.$url.'",
                                        "text": "优惠券详细说明"
                                    }
                                ]
                            },
                            "discount": '.(100-$data['value']).'
                        }
                    }
                }';
            }
            if($wx_type == 'GIFT'){
                $post_data .= '"advanced_info": {
                                "abstract": {
                                    "abstract": "吖咪，期待您的光临",
                                    "icon_url_list": [
                                        "'.$url.'"
                                    ]
                                },
                                "text_image_list": [
                                    {
                                        "image_url": "'.$url.'",
                                        "text": "优惠券详细说明"
                                    }
                                ]
                            },
                            "gift": "'.$data['content'].'"
                        }
                    }
                }';
            }
            $rs = $this->curl_post('https://api.weixin.qq.com/card/create?access_token=' . $accessToken, $post_data);
            if($rs['errcode']==0 && $rs['errmsg']=='ok'){
                $data['wx_sn'] = $rs['card_id'];
            }else{
                var_dump($rs);
                //exit;
                $this->error('微信卡券生成失败');
            }*/
            $this->m2('coupon')->data($data)->add();
            $this->success('已生成优惠券','index');
            exit;
        }

        $member_tag = $this->m2('tag')->where('type=0 and official=1')->select();
        $tips_tag = $this->m2('tag')->where('type=1 and official=1')->select();
        $goods_tag = $this->m2('tag')->where('type=2 and official=1')->select();
        $this->assign('member_tag',$member_tag);
        $this->assign('tips_tag',$tips_tag);
        $this->assign('goods_tag',$goods_tag);
        $this->view();
    }

    public function update(){
        $this->actname = '修改优惠券';
        if(IS_POST && $_POST!=''){
            //print_r($_POST);exit;
            $member_tag = I('post.member_tag');
            $tips_tag = I('post.tips_tag');
            $goods_tag = I('post.goods_tag');

            $data = array();
            $data['id'] = I('post.id');
            $data['member_id'] = I('post.CouponMember_id')?I('post.CouponMember_id'):null;
            $data['category'] = $category = I('post.CouponCategory');
            $data['name'] = $name = I('post.CouponName');
            $data['type'] = $type = I('post.CouponType');
            $data['value'] = $value = I('post.CouponValue')?I('post.CouponValue'):0;
            $data['content'] = $content = I('post.CouponContent');
            $data['count'] = $count = I('post.CouponCount');
            $data['start_time'] = $start_time = strtotime(I('post.CouponStart_time'))?strtotime(I('post.CouponStart_time')):0;
            $data['end_time'] = $end_time = strtotime(I('post.CouponEnd_time'))?strtotime(I('post.CouponEnd_time')):0;
            $data['min_amount'] = $min_amount = I('post.CouponMin_amount');
            $data['member_tags'] = $member_tags = I('post.checkedAll_member_tag')?'*':join(',',$member_tag);
            $data['tips_tags'] = $tips_tags = I('post.checkedAll_tips_tag')?'*':join(',',$tips_tag);
            $data['goods_tags'] = $goods_tags = I('post.checkedAll_goods_tag')?'*':join(',',$goods_tag);
            $data['remarks'] = $remarks = I('post.remarks');

            $this->m2('coupon')->data($data)->save();
            $this->success('修改优惠券成功','index');
            exit;
        }


        $id = I('get.id');
        $rs = $this->m2('coupon')->where('id='.$id)->find();
        //数据处理
        $rs['start_time'] = $rs['start_time']==0?$rs['start_time']:date('Y-m-d H:i:s',$rs['start_time']);
        $rs['end_time'] = $rs['end_time']==0?$rs['end_time']:date('Y-m-d H:i:s',$rs['end_time']);
        if($rs['member_tags']!='*'){
            $rs['member_tags'] = explode(',',$rs['member_tags']);
        }
        if($rs['tips_tags']!='*'){
            $rs['tips_tags'] = explode(',',$rs['tips_tags']);
        }
        if($rs['goods_tags']!='*'){
            $rs['goods_tags'] = explode(',',$rs['goods_tags']);
        }
        $member_tag = $this->m2('tag')->where('type=0 and official=1')->select();
        $tips_tag = $this->m2('tag')->where('type=1 and official=1')->select();
        $goods_tag = $this->m2('tag')->where('type=2 and official=1')->select();
        $this->assign('member_tag',$member_tag);
        $this->assign('tips_tag',$tips_tag);
        $this->assign('goods_tag',$goods_tag);
        $this->assign('data',$rs);
        $this->assign('id',$id);
        $this->view();
    }

    public function registerCoupon(){
        $this->actname = '注册券修改';

        if(IS_POST){
            $member_tag = I('post.member_tag');
            $tips_tag = I('post.tips_tag');
            $goods_tag = I('post.goods_tag');

            $data = array();
            $data['id'] = I('post.id');
            $data['member_id'] = I('post.CouponMember_id')?I('post.CouponMember_id'):null;
            $data['category'] = $category = I('post.CouponCategory');
            $data['name'] = $name = I('post.CouponName');
            $data['type'] = $type = I('post.CouponType');
            $data['value'] = $value = I('post.CouponValue')?I('post.CouponValue'):0;
            $data['content'] = $content = I('post.CouponContent');
            $data['count'] = $count = I('post.CouponCount');
            $data['start_time'] = 0;
            $data['end_time'] = I('post.CouponEnd_time');
            $data['min_amount'] = $min_amount = I('post.CouponMin_amount');
            $data['member_tags'] = $member_tags = I('post.checkedAll_member_tag')?'*':join(',',$member_tag);
            $data['tips_tags'] = $tips_tags = I('post.checkedAll_tips_tag')?'*':join(',',$tips_tag);
            $data['goods_tags'] = $goods_tags = I('post.checkedAll_goods_tag')?'*':join(',',$goods_tag);
            $data['remarks'] = $remarks = I('post.remarks');
            $data['status'] = $status = I('post.status');

            if(!empty($data['id'])){
                $this->m2('coupon')->data($data)->save();
                $this->success('修改优惠券成功','index');
            }else{
                unset($data['id']);
                $this->m2('coupon')->data($data)->add();
                $this->success('生成优惠券成功','index');
            }

            exit;
        }
        $data = $this->m2('coupon')->where('category=2')->find();
        //print_r($data);exit;
        $member_tag = $this->m2('tag')->where('type=0 and official=1')->select();
        $tips_tag = $this->m2('tag')->where('type=1 and official=1')->select();
        $goods_tag = $this->m2('tag')->where('type=2 and official=1')->select();
        if($data['member_tags']!='*'){
            $data['member_tags'] = explode(',',$data['member_tags']);
        }
        if($data['tips_tags']!='*'){
            $data['tips_tags'] = explode(',',$data['tips_tags']);
        }
        if($data['goods_tags']!='*'){
            $data['goods_tags'] = explode(',',$data['goods_tags']);
        }
        $this->assign('member_tag',$member_tag);
        $this->assign('tips_tag',$tips_tag);
        $this->assign('goods_tag',$goods_tag);
        $this->assign('data',$data);
        $this->view();
    }

    public function inviteCoupon(){
        $this->actname = '邀请券修改';

        if(IS_POST){
            $member_tag = I('post.member_tag');
            $tips_tag = I('post.tips_tag');
            $goods_tag = I('post.goods_tag');

            $data = array();
            $data['id'] = I('post.id');
            $data['member_id'] = I('post.CouponMember_id')?I('post.CouponMember_id'):null;
            $data['category'] = $category = I('post.CouponCategory');
            $data['name'] = $name = I('post.CouponName');
            $data['type'] = $type = I('post.CouponType');
            $data['value'] = $value = I('post.CouponValue')?I('post.CouponValue'):0;
            $data['content'] = $content = I('post.CouponContent');
            $data['count'] = $count = I('post.CouponCount');
            $data['start_time'] = 0;
            $data['end_time'] = I('post.CouponEnd_time');
            $data['min_amount'] = $min_amount = I('post.CouponMin_amount');
            $data['member_tags'] = $member_tags = I('post.checkedAll_member_tag')?'*':join(',',$member_tag);
            $data['tips_tags'] = $tips_tags = I('post.checkedAll_tips_tag')?'*':join(',',$tips_tag);
            $data['goods_tags'] = $goods_tags = I('post.checkedAll_goods_tag')?'*':join(',',$goods_tag);
            $data['remarks'] = $remarks = I('post.remarks');
            $data['status'] = $status = I('post.status');

            if(!empty($data['id'])){
                $this->m2('coupon')->data($data)->save();
                $this->success('修改优惠券成功','index');
            }else{
                unset($data['id']);
                $this->m2('coupon')->data($data)->add();
                $this->success('添加优惠券成功','index');
            }

            exit;
        }
        $data = $this->m2('coupon')->where('category=1')->find();
        $member_tag = $this->m2('tag')->where('type=0 and official=1')->select();
        $tips_tag = $this->m2('tag')->where('type=1 and official=1')->select();
        $goods_tag = $this->m2('tag')->where('type=2 and official=1')->select();
        if($data['member_tags']!='*'){
            $data['member_tags'] = explode(',',$data['member_tags']);
        }
        if($data['tips_tags']!='*'){
            $data['tips_tags'] = explode(',',$data['tips_tags']);
        }
        if($data['goods_tags']!='*'){
            $data['goods_tags'] = explode(',',$data['goods_tags']);
        }
        $this->assign('member_tag',$member_tag);
        $this->assign('tips_tag',$tips_tag);
        $this->assign('goods_tag',$goods_tag);
        $this->assign('data',$data);
        $this->view();
    }

    public function invitedCoupon(){
        $this->actname = '被邀请券修改';

        if(IS_POST){
            $member_tag = I('post.member_tag');
            $tips_tag = I('post.tips_tag');
            $goods_tag = I('post.goods_tag');

            $data = array();
            $data['id'] = I('post.id');
            $data['member_id'] = I('post.CouponMember_id')?I('post.CouponMember_id'):null;
            $data['category'] = $category = I('post.CouponCategory');
            $data['name'] = $name = I('post.CouponName');
            $data['type'] = $type = I('post.CouponType');
            $data['value'] = $value = I('post.CouponValue')?I('post.CouponValue'):0;
            $data['content'] = $content = I('post.CouponContent');
            $data['count'] = $count = I('post.CouponCount');
            $data['start_time'] = 0;
            $data['end_time'] = I('post.CouponEnd_time');
            $data['min_amount'] = $min_amount = I('post.CouponMin_amount');
            $data['member_tags'] = $member_tags = I('post.checkedAll_member_tag')?'*':join(',',$member_tag);
            $data['tips_tags'] = $tips_tags = I('post.checkedAll_tips_tag')?'*':join(',',$tips_tag);
            $data['goods_tags'] = $goods_tags = I('post.checkedAll_goods_tag')?'*':join(',',$goods_tag);
            $data['remarks'] = $remarks = I('post.remarks');
            $data['status'] = $status = I('post.status');

            if(!empty($data['id'])){
                $this->m2('coupon')->data($data)->save();
                $this->success('修改优惠券成功','index');
            }else{
                unset($data['id']);
                $this->m2('coupon')->data($data)->add();
                $this->success('添加优惠券成功','index');
            }

            exit;
        }
        $data = $this->m2('coupon')->where('category=3')->find();
        $member_tag = $this->m2('tag')->where('type=0 and official=1')->select();
        $tips_tag = $this->m2('tag')->where('type=1 and official=1')->select();
        $goods_tag = $this->m2('tag')->where('type=2 and official=1')->select();
        if($data['member_tags']!='*'){
            $data['member_tags'] = explode(',',$data['member_tags']);
        }
        if($data['tips_tags']!='*'){
            $data['tips_tags'] = explode(',',$data['tips_tags']);
        }
        if($data['goods_tags']!='*'){
            $data['goods_tags'] = explode(',',$data['goods_tags']);
        }
        $this->assign('member_tag',$member_tag);
        $this->assign('tips_tag',$tips_tag);
        $this->assign('goods_tag',$goods_tag);
        $this->assign('data',$data);
        $this->view();
    }

    //发送优惠券
    public function sentCoupon(){
        if(IS_POST){
            $coupon_sn = I('post.sn');
            $telephone = I('post.telephone');
           // $member_rs = $this->m2('member')->where('id='.$member_id)->find();
            $member_id = $this->m2('member')->where('telephone='.$telephone)->getField('id');
            if(!$member_id)$this->error('该用户不存在');
           $this->m2('member_coupon')->where('sn='.$coupon_sn)->data(array('member_id'=>$member_id))->save();
            /*$open_id = $member_rs['openid'];
            if(!empty($open_id)){
                $accessToken = getAccessToken();
                $coupon_id = $this->m2('member_coupon')->where(array('sn'=>$coupon_sn,'member_id'=>$member_id))->getField('coupon_id');
                $wx_sn = $this->m2('coupon')->where(array('id'=>$coupon_id))->getField('wx_sn');
                $post_data = '{"touser":["'.$open_id.'"],"wxcard":{"card_id":"'.$wx_sn.'","code":'.$coupon_sn.'},"msgtype":"wxcard"}';
                $rs = $this->curl_post('https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=' . $accessToken, $post_data);
                if($rs['errcode'] != 0){
                    $this->error($rs);
                }else{
                    //调用库存更改接口

                    $this->success('本地优惠券发送成功，微信卡券发送成功');
                }
            }*/

            $this->success('本地优惠券发送成功');
        }
    }

    //生成子券
    public function createCoupon(){
        if(IS_POST){
            $id = I('post.couponID');
            $num = I('post.coupon_num');
            //微信接口限制，一次最多100个
            if($num>10000)$this->error('每次最多生成10000个券');
            $count = $this->m2('member_coupon')->where('coupon_id='.$id)->count();
            $start = $count?$count+1:1;
            $datas = [];
            for($i=$start;$i<=$count+$num;$i++){
                $data = [];
                $data['sn'] = createCode(12);
                $sn_arr[] = $data['sn'];
                $data['coupon_id'] = $id;
                $datas[] = $data;
            }
            $this->m2('member_coupon')->addAll($datas);
            $this->m2('coupon')->where('id='.$id)->data(['status'=>1])->save();

            //如果是微信自定义券，则同步生成子券
            $result = $this->m2('coupon')->field('wx_sn,category')->where(array('id'=>$id))->find();
            if($result['category'] == 4){
                $card_id = $result['wx_sn'];
                $codes = '';
                foreach($sn_arr as $key=>$row){
                    $codes .= '"'.$row.'",';
                }
                $codes = substr($codes,0,strlen($codes)-1);
                $codes = '"code":['.$codes.']}';
                $post_data = '{"card_id":"'.$card_id.'",'.$codes;

                $access_token = getAccessToken();
                $url = 'http://api.weixin.qq.com/card/code/deposit?access_token='.$access_token;
                $rs = $this->curl_post($url, $post_data);
                //微信卡券更改库存接口
                $post_data = array('card_id'=>$card_id,'increase_stock_value'=>count($sn_arr));
                $url = 'https://api.weixin.qq.com/card/modifystock?access_token='.$access_token;
                $rs2 = $this->curl_post($url,json_encode($post_data));
                if($rs['errcode']!=0 && $rs2['errcode']!=0){
                    $this->error($rs);
                }
            }

            $this->success('已生成优惠券');
        }
    }

    //优惠券导出
    public function CouponExport(){
        $condition = array();
        $couponName = I('get.couponName');
        $couponName && $condition['name'] = $couponName;

        $data = D('CouponExportView')->where($condition)->select();

        foreach($data as $key=>$row){
            //查找全部子券数量
            $all_count = $this->m2('member_coupon')->where('coupon_id='.$row['id'])->count();
            $data[$key]['all_count'] = $all_count;
            //查找已领取的数量
            $get_count = $this->m2('member_coupon')->where("coupon_id={$row['id']} and member_id is not null")->count();
            $data[$key]['get_count'] = $get_count;
            //查找已使用的数量
            $use_count = $this->m2('member_coupon')->where("coupon_id={$row['id']} and used_time > 0")->count();
            $data[$key]['use_count'] = $use_count;

            if($row['category'] == 0)$data[$key]['category'] = '营销券';
            if($row['category'] == 1)$data[$key]['category'] = '邀请券';
            if($row['category'] == 2)$data[$key]['category'] = '注册券';
            if($row['type'] == 0)$data[$key]['type'] = '抵价券';
            if($row['type'] == 1)$data[$key]['type'] = '折扣券';
            if($row['type'] == 2)$data[$key]['type'] = '礼品券';

            $data[$key]['start_time'] = $row['start_time']?date('Y-m-d H:i',$row['start_time']):'不限';
            $data[$key]['end_time'] = $row['end_time']?date('Y-m-d H:i',$row['end_time']):'不限';

            if($row['member_nickname']){
                $data[$key]['sent_member'] = $row['member_nickname'];
            }else{
                $data[$key]['sent_member'] = '系统';
            }

            unset($data[$key]['member_nickname']);
        }

        $title = ['ID','分类','优惠券名称','类型','优惠值','备注','开始时间','结束时间','最低消费金额','子券上限','子券生成数','领取数量','使用数量','发布者'];

        toXls($title,$data,'优惠券导出');
    }

    //优惠券码导出
    public function CouponSnExport(){
        $couponId = I('get.couponId');

        $data = D('CouponDetailView')->field('coupon_id,sn,used_time,nickname')->where('A.coupon_id='.$couponId)->order('A.id desc')->select();

        //数据处理
        foreach($data as $key=>$row){

            if($row['used_time']==0){
                $data[$key]['used_time'] = '未使用';
            }else{
                $data[$key]['used_time'] = date('Y-m-d H:i', $row['used_time']);
            }

            //更改输出格式
            $data[$key]['sn'] = $data[$key]['sn']?'#'.$row['sn']:'';

        }

        /*$comma_data = $title = array();
        foreach($data as $row){
            $d = $title = array();
            foreach($row as $k => $r){
                $title[] = $k;
                $r = str_replace(',','，',$r);
                $d[] = iconv('utf-8','gb2312',$r);
            }
            $comma_data[] = join(',', $d);
        }*/
        //$title = ['主券ID','子券券码','使用时间','使用订单号','领取人'];

        /*$i = 0;
        $dir =  date("Ymd").mt_rand(100,999);
        foreach ($data as $key => $row) {
            if($i % 10000 == 0 || $i == count($data) - 1){
                if(isset($comma_data) && !empty($comma_data)) {
                    ob_start();
                    echo $comma_data;
                    $context = ob_get_clean();
                    if(!is_dir($dir))mkdir($dir);
                    file_put_contents($dir.'/' . date("YmdHis") . '('.ceil($i / 10000).')' . ".xls", $context);
                }
                $comma_data = "子券券码\t使用时间\t使用订单号\t领取人\n";
                $comma_data = iconv('utf-8', 'gb2312', $comma_data);
            }
            $comma_data .= $row["sn"] . "\t" . iconv('utf-8', 'gb2312',$row["used_time"]) . "\t" . $row["order_sn"] . "\t" . iconv('utf-8', 'gb2312',$row["nickname"])  . "\n";
            $i ++;
        }
        $open_dir = 'export/'.date("YmdHis").'-'.mt_rand(100,999).'.zip';
        if(!is_dir('export'))mkdir('export');
        $zip=new \ZipArchive();
        if(/*$zip->open($open_dir, $zip::OVERWRITE)===*/// TRUE) {
            /*$zip->open($open_dir, \ZipArchive::CREATE);
            addFileToZip($dir, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
            $zip->close(); //关闭处理的zip文件

            $dh = opendir($dir);
            while ($file = readdir($dh)) {
                if ($file != "." && $file != "..") {
                    $fullpath = $dir . "/" . $file;
                    if (!is_dir($fullpath)) {
                        unlink($fullpath);
                    } else {
                        deldir($fullpath);
                    }
                }
            }
            closedir($dh);
            //删除当前文件夹：
            rmdir($dir);
        }
        header('location: http://'. $_SERVER['HTTP_HOST'] .'/'.$open_dir);*/
        toXls_new($data, ['主券ID','子券券码','使用时间','领取人'], 10000, '券码列表');
    }


    public function detail(){
        $this->actname = '优惠券详情';
        $id = I('get.id');
        $pageSize = 30;
        $page = I('get.page', 1);
        $condition = array();

        $search_sn = I('get.sn');
        if($search_sn!='')$condition['sn'] = array('EQ',$search_sn);
        $this->assign('search_sn',$search_sn);

        $condition['A.coupon_id'] = array('EQ',$id);
        $datas['datas'] = D('CouponDetailView')->where($condition)->order('A.id desc')->page($page, $pageSize)->select();
        //数据处理
        foreach($datas['datas'] as $key=>$row){

            //判断是否已经发送给用户
            if($row['member_id']==null){
                $datas['datas'][$key]['nickname']= '无';
                $datas['datas'][$key]['send'] = 0;//未发送
            }else{
                $datas['datas'][$key]['send'] = 1;//已发送
            }
            //防止nickname为null的用户出现误判
            if($datas['datas'][$key]['nickname']==null){
                $datas['datas'][$key]['nickname']= '用户未设置昵称';
            }

            if($row['used_time']==0){
                $datas['datas'][$key]['used_time'] = '未使用';
            }else{
                $datas['datas'][$key]['used_time'] = date('Y-m-d H:i',$row['used_time']);
            }
            //添加订单查询链接
            if($row['order_wares_type'] == 0 && $row['order_wares_type']!=''){
                if($row['order_wares_id']){
                    $url = U('order/TipsOrder',array('sn'=>$row['order_sn']));
                    $datas['datas'][$key]['use_place'] = "<a href='$url'>查看订单</a>";

                }
            }elseif($row['order_wares_type'] == 1){
                if($row['order_wares_id']){
                    $url = U('order/GoodsOrder',array('sn'=>$row['order_sn']));
                    $datas['datas'][$key]['use_place'] = "<a href='$url'>查看订单</a>";
                }
            }else{
                $datas['datas'][$key]['use_place'] = '未使用';
            }

        }
        //print_r($datas);exit;
        $datas['pages'] = array(
            'sum' => D('CouponDetailView')->where($condition)->count(),
            'count' => $pageSize
        );
        $datas['lang'] = array(
            'sn' => '券码',
            'nickname' => '领取人',
            'used_time' => '使用时间'
        );
        $datas['operations'] = array(
            '发送优惠券' => array(
                'style' => 'success',
                'fun' => "sentCoupon(%sn)",
                'condition' => '%send == 0 && %coupon_category != 4'
            )
            /*'发布' => array(
                'style' => 'success',
                'fun' => 'release(%id)',
                'condition' => '%status == 未发布'
            ),
            '取消发布' => array(
                'style' => 'danger',
                'fun' => 'unrelease(%id)',
                'condition' => '%status == 已发布'
            ),*/
        );

        $all_sn_count = $this->m2('member_coupon')->where('coupon_id='.$id)->count();
        $used_count = $this->m2('member_coupon')->where("coupon_id=$id and used_time>0")->count();
        $coupon_rs = $this->m2('coupon')->where('id='.$id)->find();
        //优惠券分类
        $this->assign('category',$coupon_rs['category']);
        //微信卡券号
        $this->assign('cardId',$coupon_rs['wx_sn']);
        //限制数量
        $this->assign('couponCount',$coupon_rs['count']);
        //已生成数量
        $this->assign('all_count',$all_sn_count);
        //使用数量
        $this->assign('used_count',$used_count);
        $this->assign('couponID',$id);

        $this->assign($datas);
        $this->view();
        /*$data = array();
        //优惠券总量
        $coupon_rs = $this->m2('coupon')->where('id='.$id)->find();
        $data['coupon_count'] = $count = $coupon_rs['count'];
        //子券列表
        $data['coupon_list'] = $member_coupon = $this->m2('member_coupon')->where('coupon_id='.$id)->select();
        //已发送的数量
        $data['coupon_sent_count'] =$sentCount = count($member_coupon);
        //已使用的数量
        $data['coupon_use_count'] = $useCount = $this->m2('member_coupon')->where("coupon_id=$id and used_time>0")->count();
        //数据处理
        foreach($data['coupon_list'] as $key=>$row){
            $member_rs = $this->m2('member')->where('id='.$row['member_id'])->find();
            $data['coupon_list'][$key]['member_nickname'] = $member_rs['nickname'];
        }

        $this->ajaxReturn($data);*/
        exit;
    }

    public function getUser(){
        if(IS_AJAX){
            $search_key = I('post.search_key');
            $condition = array();

            if(isset($search_key)&&$search_key!=''){
                $condition = ' nickname LIKE '."'%$search_key%'";
                $member_rs = $this->m2('member')->field('id,nickname,telephone')->where($condition)->limit(20)->select();
                $this->ajaxReturn($member_rs);
            }
        }
    }

    public function checkWxCoupon(){
        $data['card_id'] = 'p6FWTtxx5VfA2xXoaFg0PbCXG760';
        $data['code'] = [
            '2016022325319837', '2016022325395936', '2016022325388935'
        ];
        $access_token = getAccessToken();
        $url = 'http://api.weixin.qq.com/card/code/checkcode?access_token='.$access_token;
        $rs = $this->curl_post($url, json_encode($data));
        var_dump($rs);
    }

    //群发自定义微信卡券
    public function sentWxCoupon(){
        $this->actname = '群发微信券(官方投放)';

        $type = I('post.type');
        //$cardId = I('post.cardId',null);

        if($type == 'sent'){
            $tag_id = I('post.group');
            $cardId = I('post.cardId');
            $mt_rs = $this->m2('member_tag a')->join('__MEMBER__ b ON a.member_id = b.id','LEFT')->field('b.openid')->where('a.tag_id='.$tag_id)->select();
            $i=0;
            foreach($mt_rs as $key=>$row){
                //转换为一维数组
                $mt_rs[$key] = $row['openid'];
                if(!empty($mt_rs[$key])){
                    $i++;
                }else{
                    unset($mt_rs[$key]);
                }

                if($i>=2)break;
            }
            if($i<2)$this->error('该组用户中拥有openid的用户不超过2个，无法发送!');

            $openid = array_values($mt_rs);
            $accessToken = getAccessToken();
            if(count($openid)>10000){
                $act = true;
                $all_openid = array_chunk($openid,10000);
                foreach($all_openid as $row){
                    $post_data = json_encode(array('touser'=>$row,'wxcard'=>array('card_id'=>$cardId),"msgtype"=>"wxcard"));
                    $rs = $this->curl_post('https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$accessToken,$post_data);
                    if($rs['errcode']!=0)$act==false;
                }
                if($act==false){
                    $this->error('发送失败！！');
                }else{
                    $this->success('发送成功');
                }
            }else{
                //3月25日再测试
                $post_data = json_encode(array('touser'=>$openid,'wxcard'=>array('card_id'=>$cardId),"msgtype"=>"wxcard"));
                //$post_data = '{"touser":["oW-OvsxiuUuBPnHQld9ofCU_cfnQ","oW-OvsyfjwLR91CP7l-Z_ggWFux4"],"wxcard":{"card_id":"p6FWTtwm6ZMUQRUk79i6a3L4grOE"},"msgtype":"wxcard"}';//
                //$post_data = '{"touser":["oW-OvsxiuUuBPnHQld9ofCU_cfnQ","oW-OvsyfjwLR91CP7l-Z_ggWFux4"],"msgtype": "text","text": { "content": "hello from boxer."}}';
                $rs = $this->curl_post('https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$accessToken,$post_data);
                if($rs['errcode']==0 ){
                    $this->success('发送成功');
                }else{
                    $this->error($accessToken);
                }
            }

        }elseif($type == 'search'){
            //$tag_ids = $this->m2('member_tag')->field('tag_id')->group('tag_id')->bulidsql();
            $tag_rs = $this->m2('tag')->field('id,name')->where('type=0')->select();

            foreach($tag_rs as $key=>$row){
                $tag_rs[$key]['number'] = $this->m2('member_tag')->where('tag_id='.$row['id'])->count();
                if($tag_rs[$key]['number']<2)unset($tag_rs[$key]);
            }
            $this->ajaxReturn($tag_rs);
            /*$this->assign('group',$tag_rs);
            $this->assign('cardId',I('get.cardid'));
            $this->view();*/
        }



    }

    /*public function release(){
        if(IS_AJAX){
            $id = I('post.id');
            $oper = I('post.oper');

            //取消发布
            if($oper==0){
                $data = array();
                $data['id'] = $id;
                $data['status'] = 0;
                $this->m2('coupon')->save($data);
                $this->success('已取消发布');
            }
            //发布
            if($oper==1){
                $data = array();
                $data['id'] = $id;
                $data['status'] = 1;
                $this->m2('coupon')->save($data);
                $this->success('发布成功');
            }
        }
    }*/

}