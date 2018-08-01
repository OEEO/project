<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class OrderController extends MainController{
    Protected $pagename = '订单管理';
    
    //退款
    public function refund(){
        $this->actname = '退款页面';

        $condition = array();
        $condition01 = array();
        $condition02 = array();

        if(isset($_GET['search_type']) && is_numeric($_GET['search_type'])){
            $condition01['E.type'] = $condition['E.type'] = ['EQ',$_GET['search_type']];
        }else{
            $condition01['E.type'] = $condition['E.type'] = ['EQ',0 ];
        }
        $search_status = I('get.search_status');
        $search_title = I('get.search_title');
        $search_type = I('get.search_type');

        if($search_status == 5)$condition['order_act_status'] = ['EQ',5];
        if($search_status == 8)$condition['order_act_status'] = ['EQ',8];
        if($search_status == 1)$condition['is_allow'] = ['EQ',1];
        if($search_status == 2)$condition['is_allow'] = ['EQ',2];
        if(empty($condition['order_act_status']))$condition01['order_act_status'] = $condition['order_act_status'] = ['IN','5,6,8'];
        if($search_type)$condition01['E.type'] = ['EQ',$search_type];
        if(!empty($search_title)) {
            $TypeGoods_id = D('OrderRefundView')->field('order_wares_id')->where($condition01)->order('id desc')->group('A.id')->select();
            foreach ($TypeGoods_id as $Key => $Val) {
                $type_id_arr[$Key] = $Val['order_wares_id'];
            }
            $condition02['id'] = ['IN', join(',', $type_id_arr)];
             $condition02['title'] = ['LIKE', '%' . $search_title . '%'];
            if ($search_type == 0 && !empty($search_title)) {
                $activity_arr = $this->m2('tips')->field('id')->where($condition02)->select();
            } elseif ($search_type == 1 && !empty($search_title)) {
                $activity_arr = $this->m2('goods')->field('id')->where($condition02)->select();
            } elseif ($search_type == 2 && !empty($search_title)) {
                $activity_arr = $this->m2('raise')->field('id')->where($condition02)->select();
            }
//            print_r($this->m2('tips')->getLastSql());
//            print_r($activity_arr);
            $typeId =[];
            foreach ($activity_arr as $ackey => $acval) {
                $typeId[] = $acval['id'];
            }
            $id_str = $typeId?implode(',',$typeId):'';
            $condition['order_wares_id'] = ['IN', $id_str];

        }
//        print_r($condition);
        $this->assign('search_status',$search_status);
        $this->assign('search_title',$search_title);
        $this->assign('search_type',$search_type);

//        exit;
        $model = D('OrderRefundView');
        $datas['datas'] = $model->where($condition)->page(I('get.page',1), 20)->order('id desc')->group('A.id')->select();
//        echo $model->getLastSql();
//        print_r($datas['datas']);exit;
        //数据处理
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['order_create_time'] = date('Y-m-d H:i:s',$row['order_create_time']);
            $datas['datas'][$key]['order_paytime'] = date('Y-m-d H:i:s',$row['order_paytime']);
            if($row['is_allow'] == '0')$datas['datas'][$key]['is_allow'] = '未操作';
            if($row['is_allow'] == '1')$datas['datas'][$key]['is_allow'] = '允许';
            if($row['is_allow'] == '2')$datas['datas'][$key]['is_allow'] = '不允许';
            if($row['coupon_type'] ==0)$datas['datas'][$key]['coupon_value'] = $row['coupon_value'];
            if($row['coupon_type'] ==1)$datas['datas'][$key]['coupon_value'] = $row['coupon_value'].'%';
            if($row['coupon_type'] ==2)$datas['datas'][$key]['coupon_value'] = '礼品券';

            $datas['datas'][$key]['type'] = ['常规退款', '库存不足', '支付异常','未成局'][$row['type']];
            $datas['datas'][$key]['order_paytype'] = ['支付宝客户端', '微信APP', '微信公众号','支付宝网页','小程序支付'][$row['order_paytype']];

            if($row['order_wares_type']==0){
                if(empty($row['order_wares_id'])){
                    echo $row['order_sn'];exit;
                }

                $tips_rs = $this->m2('tips')->where('id='.$row['order_wares_id'],'title =')->find();
                $datas['datas'][$key]['title'] = $tips_rs['title'];
                $datas['datas'][$key]['order_wares_type'] = '活动';
            }
            if($row['order_wares_type']==1){
                $goods_rs = $this->m2('goods')->where('id='.$row['order_wares_id'])->find();
                $datas['datas'][$key]['title'] = $goods_rs['title'];
                $datas['datas'][$key]['order_wares_type'] = '商品';
            }

            if($row['order_wares_type']==2){
                $goods_rs = $this->m2('raise')->where('id='.$row['order_wares_id'])->find();
                $datas['datas'][$key]['title'] = $goods_rs['title'];
                $datas['datas'][$key]['order_wares_type'] = '众筹';
            }
            if(strpos($row['pics_path'],'upload')){
                $datas['datas'][$key]['pics_path'] = '<img  src="'.pathFormat($row['pics_path']).'" width="50px" height="50px" />';
            }else{
                $datas['datas'][$key]['pics_path'] = '<img  src="'.pathFormat($row['pics_path']).'" width="50px" height="50px" />';
            }
        }

        $datas['operations'] = array(
            '允许退款'=> array(
                'style' => 'success',
                'fun' => 'allow(%id,1)',
                'condition' => "%order_act_status==5 || %order_act_status==8 "
            ),
            '拒绝退款'=> array(
                'style' => 'danger',
                'fun' => 'refuse(%id,0)',
                'condition' => "%order_act_status==5 || %order_act_status==8 "
            )

        );
        $datas['pages'] = array(
            'sum' => D('OrderRefundView')->where($condition)->count('DISTINCT A.id'),
            'count' => 20,
        );
        $datas['lang'] = array(
            'id' => 'ID',
            'type' => '退款类型',
            'member_nickname' => '退款人',
            'member_telephone' => '手机号码',
            'order_wares_type' => '类型',
            'title' => '标题',
            'order_sn' =>'订单号',
            'order_price' => '订单价格',
            'order_create_time' => '下单时间',
            'order_paytime' => '支付时间',
            'order_paytype' => '支付方式',
            //'order_act_status_re' => '订单状态',
            'coupon_name' => '优惠券',
            'coupon_value' => '优惠券面值',
            'money' => '退款金额',
            'cause' => '退款理由',
            'pics_path' => '相关图片',
            'refusal_reason' => '拒绝理由',
            'is_allow' => '是否批准退款',
        );

        $this->assign($datas);
        $this->view();
    }

    public function setRefund(){
        $id = I('post.id');
        $oper = I('post.oper');

        if($oper == 1){
            //允许退款
            $order_id = $this->m2('OrderRefund')->where(['id'=>$id])->getField('order_id');
            $pay_type = $this->m2('OrderPay')->where(['order_id'=>$order_id,'success_pay_time'=>['EXP','is not null']])->getField('type');
            $refund_rs = $this->m2('order_refund')->join('__ORDER__ ON __ORDER_REFUND__.order_id = __ORDER__.id','LEFT')->where('ym_order_refund.id='.$id)->find();
            if($refund_rs['price'] <= 0){
                if($err = $this->setRefundInfo($refund_rs['order_id'], $refund_rs['is_book']) === true){
                    //记录订单修改快照信息
                    $this->SaveSnapshotLogs($refund_rs['order_id'],3);
                    $this->success('退款成功！');
                }else{
                    $this->error($err);
                }
            }

            if(in_array($refund_rs['channel'], [7,8,9])){
                C('WX_CONF', C('YF_WX_CONF'));
            }
            if($pay_type == 2 || $pay_type == 1){
                //微信JS退款
                $orderSn = $refund_rs['sn'];
                $out_refund_no = $refund_rs['sn'];
                $total_fee = $refund_rs['money']*100;
                $refund_fee = $refund_rs['money']*100;
                $appid = C('WX_CONF.appid');
                $mch_id = C('WX_CONF.mchid');
                $key = C('WX_CONF.key');
                $nonce_str = md5(uniqid(mt_rand(100,999)));
                //appid:应用APPID，mch_id：商户号，out_trade_no：商户订单号，nonce_str：随机字符32位，sign：签名
                $signstr = "appid={$appid}&mch_id={$mch_id}&nonce_str={$nonce_str}&op_user_id={$mch_id}&out_refund_no={$out_refund_no}&out_trade_no={$orderSn}&refund_fee={$refund_fee}&total_fee={$total_fee}&key={$key}";
                $sign = md5($signstr);

                $code = '<xml>
                        <appid>'.$appid.'</appid>
                        <mch_id>'.$mch_id.'</mch_id>
                        <nonce_str>'.$nonce_str.'</nonce_str>
                        <op_user_id>'.$mch_id.'</op_user_id>
                        <out_refund_no>'.$out_refund_no.'</out_refund_no>
                        <out_trade_no>'.$orderSn.'</out_trade_no>
                        <refund_fee>'.$refund_fee.'</refund_fee>
                        <total_fee>'.$total_fee.'</total_fee>
                        <sign>'.$sign.'</sign>
                        </xml>';

                $_return_data = $this->curl_post('https://api.mch.weixin.qq.com/secapi/pay/refund', $code, [], true);
                $return_data = str_replace(['<![CDATA[', ']]>'], ['', ''], $_return_data);
                \Think\Log::write('微信支付订单ID'.$refund_rs['sn']);
                \Think\Log::write('微信支付'.$return_data);
                $return_data = simplexml_load_string($return_data);
                if ($return_data->return_code == 'SUCCESS') {
                    if($return_data->result_code == 'FAIL'){
                        $fail = '【'.$return_data->err_code.'】'.$return_data->err_code_des;
                    }
                    if($err = $this->setRefundInfo($refund_rs['order_id'], $refund_rs['is_book']) === true){
                        //记录订单修改快照信息
                        $this->SaveSnapshotLogs($refund_rs['order_id'],3);
                        if(!empty($fail)){
                            $this->success('已释放名额，但退款金额未返回给用户。原因是'.$fail);
                        }else{
                            $this->success('退款成功！');
                        }
                    }else{
                        $this->error($err);
                    }
                }else{
                    $this->error('退款失败！！！');
                }
            }elseif($pay_type == 0 || $pay_type == 3){
                //支付宝退款
                require_once(COMMON_PATH . "Util/alipayRefund/alipay_submit.class.php");

                $_rs = $this->m2('OrderRefund')->where(['order_id' => $order_id, 'is_allow' => 0])->find();
                $rs = $this->m2('order')->where(['id' => $order_id])->find();
                $orderPay = $this->m2('OrderPay')->where(['order_id' => $order_id,'type'=>$pay_type,'success_pay_time'=>['EXP','is not null']])->find();

                //退款批次号
                $batch_no = date('YmdHis') . rand(10000, 99999);
                //退款总笔数
                $batch_num = 1;
                //单笔数据集
                $detail_data = $orderPay['trade_no'] . '^' . $_rs['money'] . '^' . $_rs['cause'];

                \Think\Log::write('detail_data'.$detail_data);
                $parameter = [
                    "service" => C('ALIPAY.service'),
                    "partner" => C('ALIPAY.partner'),
                    "notify_url"	=> C('ALIPAY.notify_url'),
                    "batch_no"	=> $batch_no,
                    "refund_date"	=> C('ALIPAY.refund_date'),
                    "batch_num"	=> $batch_num,
                    "detail_data"	=> $detail_data,
                    "_input_charset"	=> C('ALIPAY.input_charset')
                ];
//                print_r($parameter);

                //建立请求
                $alipaySubmit = new \AlipaySubmit(C('ALIPAY'));
                $html_text = $alipaySubmit->buildRequestHttp($parameter);
                \Think\Log::write('支付宝支付'.$html_text);
                $json = simplexml_load_string($html_text);
                if($json->is_success == 'T'){
                    if($err = $this->setRefundInfo($refund_rs['order_id'], $refund_rs['is_book']) === true){
                        //记录订单修改快照信息
                        $this->SaveSnapshotLogs($refund_rs['order_id'],3);
                        $this->success('退款成功！');
                    }else{
                        $this->error($err);
                    }
                }else{
                    $this->success('退款失败！');
                }
            }

        }else{
            //拒绝退款
            $reason = I('post.reason');
            $refund_rs = $this->m2('order_refund')->where('id='.$id)->find();
            $order_id = $refund_rs['order_id'];
            $data['id'] = $order_id;
            $data['act_status'] = 6;
            $this->m2('order')->data($data)->save();

            $data = [];
            $data['id'] = $id;
            $data['is_allow'] = 2;
            $data['refusal_reason'] = $reason;
            $this->m2('order_refund')->data($data)->save();

            //记录订单修改快照信息
            $this->SaveSnapshotLogs($refund_rs['order_id'],0);
            $this->success('已经拒绝退款申请');
        }
    }

    private function setRefundInfo($order_id, $is_book = 0){
        $this->m2('order')->where(['id'=>$order_id])->save(['act_status'=>6]);
        $this->m2('OrderRefund')->where(['order_id'=>$order_id])->save(['is_allow'=>1]);

        //如果使用了优惠券则返还
        $member_coupon_id = $this->m2('order')->where(['id'=>$order_id])->getField('member_coupon_id');
        if(!empty($member_coupon_id))$this->m2('MemberCoupon')->where(['id'=>$member_coupon_id])->save(['used_time'=>0]);

        //恢复库存
        $buy_num = $this->m2('OrderWares')->where(['order_id'=>$order_id])->count();
        \Think\Log::write('buy_num:'.$buy_num);
        $order_wares = $this->m2('OrderWares')->where(['order_id'=>$order_id])->field(['type','ware_id','tips_times_id'])->find();

        $piece = D('OrderPieceView')->where(['id'=>$order_id])->find();
        if(!empty($piece)){
            $order_ids = $this->m2('order_piece')->where(['piece_originator_id'=>$piece['piece_originator_id']])->order('id asc')->getField('order_id',true);
            \Think\Log::write('$order_idsunset:'.json_encode($order_ids));
            if($order_id == $order_ids[0]) {//删除了开团人
                $this->m2('member_piece')->where(['id' => $piece['piece_originator_id']])->save(['act_status' => 10]);
                $search_orderid = array_search($order_id,$order_ids);//去掉开团的订单ID
                $arr = array_splice($search_orderid);//重新排序
                $this->m2('order')->where(['id'=>['IN',join(',',$arr)]])->save(['act_status'=>8,'status'=>2]);
            }
        }
        if($order_wares['type'] == 0){
            $tips_tags = $this->m2('TipsTag')->where(['tips_id' => $order_wares['ware_id']])->getField('tag_id', true);
            //当活动为预约制活动，不减库存
            if(in_array(76,$tips_tags) == false) {
                if ($is_book)
                    $this->m2('TipsTimes')->where(['id' => $order_wares['tips_times_id']])->setField('stock', ['exp', 'max_num']);
                else
                    $this->m2('TipsTimes')->where(['id' => $order_wares['tips_times_id']])->setInc('stock', $buy_num);
            }
            //记录活动修改快照信息
            $this->SaveSnapshotLogs($order_wares['ware_id'],0);
        }elseif($order_wares['type'] == 1){
            $this->m2('Goods')->where(['id'=>$order_wares['ware_id']])->setInc('stocks', $buy_num);
            //记录商品修改快照信息
            $this->SaveSnapshotLogs($order_wares['ware_id'],1);
        }elseif($order_wares['type'] == 2){
            $rs = $this->m2('raise_times')->where(['id'=>$order_wares['tips_times_id']])->find();
            if($rs['stock'] >= 0)$this->m2('raise_times')->where(['id'=>$order_wares['tips_times_id']])->setInc('stock', $buy_num);
            //记录众筹修改快照信息
            $this->SaveSnapshotLogs($order_wares['ware_id'],2);
        }
        return true;
    }

    //手动转换退款状态
    public function Orderrefund(){
        if(IS_AJAX){
            if($_POST['data']['typename'] == 'Tips_Refund') {
                $data = I('post.data');
                $re = $this->m2('OrderRefund')->where(['order_id' => $data['id']])->find();
                if (!empty($re)) $this->error('请勿重复申请退款');
                $this->m2('Order')->where(['id' => $data['order_id']])->data(['act_status' => 5])->save();
                $this->m2('OrderRefund')->data($data)->add();
                //记录订单修改快照信息
                $this->SaveSnapshotLogs($data['order_id'],3);
                $this->success('已提交申请，请在退款管理中进行确认');
            }elseif($_POST['data']['typename'] == 'Raise_Refund'){
                $data = I('post.data');
                $re = $this->m2('OrderRefund')->where(['order_id' => $data['id']])->find();
                if (!empty($re)) $this->error('请勿重复申请退款');
                $this->m2('Order')->where(['id' => $data['order_id']])->data(['act_status' => 5])->save();
                $this->m2('OrderRefund')->data($data)->add();
                //记录订单修改快照信息
                $this->SaveSnapshotLogs($data['order_id'],3);

                $this->success('已提交申请，请在退款管理中进行确认');

            }
        }else{
            $this->error('非法访问');
        }
    }

    //活动订单
    public function TipsOrder(){
        $this->actname = '活动订单';
//        if(isset($_GET['status']) && is_numeric($_GET['status'])){
//            $condition = ['A.status'=> $_GET['status']];
//            $search_status = $datas['status'] =$_GET['status'];
//        }else{
//            $condition = ['A.status' => 1];
//            $search_status = $datas['status'] =1;
//        }
        //条件筛选
        if(IS_GET && $_GET !=null){
            $search_sn = I('get.sn');
            $search_title = I('get.title');
            $search_member = I('get.member');
            $start = I('get.start_order_time');
            $end = I('get.stop_order_time');
            $check_code = I('get.check_code');
            $search_start_order_time = strtotime($start);
            $search_stop_order_time = strtotime($end);
            $search_city = I('get.citys');
            $search_act_status = I('get.act_status');
            $search_status = I('get.status');
            $search_sn && $condition['A.sn'] = ['EQ',$search_sn];
            $search_title && $condition['H.title'] = ['LIKE','%'.$search_title.'%'];
            $search_member && $condition['B.nickname'] = ['LIKE','%'.$search_member.'%'];
            $check_code && $condition['F.check_code'] = $check_code;

            if($start && $end){
                $condition['A.create_time'] = ['BETWEEN',"$search_start_order_time,$search_stop_order_time"];
            }else{
                $start && $condition['A.create_time'] = ['GT',$search_start_order_time];
                $end && $condition['A.create_time'] = ['LT',$search_stop_order_time];
            }

            if(!empty($search_city)){
                $area = $this->m2('Citys')->where(['pid'=>$search_city])->select();
                foreach($area as $re){
                    $area_id[] = $re['id'];
                }
                if(!empty($area_id)){
                    $city_id = join(',',$area_id);
                    $city_id .= ','.$city_id;
                }else{
                    $city_id = $search_city;
                }

            }
            $search_city && $condition['L.citys_id'] = ['IN',$city_id];
            $search_act_status!='' && $condition['A.act_status'] = ['EQ',$search_act_status];
            $search_status!='' && $condition['A.status'] = ['EQ',$search_status];

            $search_sn && $this->assign('search_sn',$search_sn);
            $search_title && $this->assign('search_title',$search_title);
            $search_member && $this->assign('search_member',$search_member);
            $search_start_order_time && $this->assign('search_start_order_time',date('Y-m-d H:i',$search_start_order_time));
            $search_stop_order_time && $this->assign('search_stop_order_time',date('Y-m-d H:i',$search_stop_order_time));
            $search_act_status!='' && $this->assign('search_act_status',$search_act_status);
            $search_city && $this->assign('search_city',$search_city);
            $search_status && $this->assign('search_status',$search_status);
            $check_code && $this->assign('check_code', $check_code);
        }

        $condition['F.type'] = ['EQ',0];
//        $condition['A.act_status'] = ['NEQ',11];
        $datas['datas'] = D('OrderTipsView')->where($condition)->page(I('get.page', 1), 30)->order('id desc')->group('A.id')->select();
        foreach($datas['datas'] as $re){
            $ids[] = $re['id'];
        }
        $ids = join(',',$ids);

        //计算优惠券的实际优惠金额和实际支付金额
        foreach($datas['datas'] as $key=>$row){
            if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                $datas['datas'][$key]['discount'] = $row['coupon_value'];
                //$datas['datas'][$key]['act_pay'] = $row['price']-$datas['datas'][$key]['discount'];
                //if($datas['datas'][$key]['act_pay']<0)$datas['datas'][$key]['act_pay']=0;
                // echo $datas['datas'][$key]['discount'].'----'.$datas['datas'][$key]['act_pay'].'</br>';
            }elseif($row['coupon_type'] == 1){                                      //折扣券
                $datas['datas'][$key]['discount'] = ($row['price']/($row['coupon_value']*0.01)-$row['price']);
                //$datas['datas'][$key]['act_pay'] = $row['price']-$datas['datas'][$key]['discount'];
                // echo $datas['datas'][$key]['discount'].'----'.$datas['datas'][$key]['act_pay'].'</br>';
            }elseif($row['coupon_type'] == 2){                                      //礼品券
                $datas['datas'][$key]['discount'] = 0;
                //$datas['datas'][$key]['act_pay'] = $row['price'];
                // echo $datas['datas'][$key]['discount'].'----'.$datas['datas'][$key]['act_pay'].'</br>';
            }else{
                $datas['datas'][$key]['discount'] = 0;                              //不使用优惠券
                //$datas['datas'][$key]['act_pay'] = $row['price'];
                //echo $datas['datas'][$key]['discount'].'----'.$datas['datas'][$key]['act_pay'].'</br>';
            }
        }

        //数据处理
        foreach($datas['datas'] as $key=>$row){
            if($row['act_status'] == 0){
                $datas['datas'][$key]['status'] = '未支付';
            }elseif($row['act_status'] == 1){
                $datas['datas'][$key]['status'] = '已支付';
            }elseif($row['act_status'] == 2){
                $datas['datas'][$key]['status'] = '待评价';
            }elseif($row['act_status'] == 3){
                $datas['datas'][$key]['status'] = '待评价';
            }elseif($row['act_status'] == 4){
                $datas['datas'][$key]['status'] = '已完成';
            }elseif($row['act_status'] == 5){
                $datas['datas'][$key]['status'] = '申请退款';
            }elseif($row['act_status'] == 6){
                $datas['datas'][$key]['status'] = '退款申请已处理';
            }elseif($row['act_status'] == 7){
                $datas['datas'][$key]['status'] = '已取消';
            }elseif($row['act_status'] == 8){
                $datas['datas'][$key]['status'] = '系统自动操作，退款中';
            }

            $datas['datas'][$key]['tips_times_start_time'] = date('Y-m-d H:i',$datas['datas'][$key]['tips_times_start_time']);
            $datas['datas'][$key]['tips_times_end_time'] = date('Y-m-d H:i',$datas['datas'][$key]['tips_times_end_time']);
            $datas['datas'][$key]['limit_pay_time'] = date('Y-m-d H:i',$datas['datas'][$key]['limit_pay_time']);
            $datas['datas'][$key]['create_time'] = date('Y-m-d H:i',$datas['datas'][$key]['create_time']);
            $datas['datas'][$key]['customer'] = '昵称：'.$row['member_nickname']."<br/>".'手机：'.$row['member_telephone']."<br/>".'留言：'.$row['context'];
            $datas['datas'][$key]['order_wares_server_status'] = $datas['datas'][$key]['order_wares_server_status'] ==0?'未验票':'已验票';

            if($row['act_status'] !=0){
                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                if(!empty($pay_arr['success_pay_time'])){
                    $datas['datas'][$key]['success_pay_time'] =  date('Y-m-d H:i',$pay_arr['success_pay_time']);
                    if($pay_arr['type'] == 0){
                        $datas['datas'][$key]['pay_type'] =  '支付宝客户端';
                    }elseif($pay_arr['type'] == 1){
                        $datas['datas'][$key]['pay_type'] =  '微信APP';
                    }elseif($pay_arr['type'] == 2){
                        $datas['datas'][$key]['pay_type'] =  '微信公众号';
                    }elseif($pay_arr['type'] == 3){
                        $datas['datas'][$key]['pay_type'] =  '支付宝网页支付';
                    }elseif($pay_arr['type'] == 4){
                        $datas['datas'][$key]['pay_type'] =  '小程序支付';
                    }

                }else{
                    $datas['datas'][$key]['pay_type'] =  '';
                    $datas['datas'][$key]['success_pay_time'] ='';
                }
            }else{
                $datas['datas'][$key]['pay_type'] =  '';
                $datas['datas'][$key]['success_pay_time'] ='';
            }
        }

//        var_dump($datas['datas']);exit;

        //table页面参数设置
        $datas['operations'] = [
            '验票' => [
                'style' => 'success',
                'fun' => 'check(%id)',
                'condition' => "%order_wares_server_status=='未验票' && %status!='未支付'"
            ],
            '快照'=> [
                'style' => 'success',
                'fun' => 'snapshot(%id)',
                'condition' => "true"
            ],
            '退款'=> [
                'style' => 'danger',
                'fun' => 'OrderRefund(%id,%price)',
                'condition' => "%act_status==1",
            ],
            '取消'=> [
                'style' => 'warning',
                'fun' => 'cancel(%id)',
                'condition' => "%order_status==1 && %act_status==0",
            ],
            '删除'=> [
                'style' => 'danger',
                'fun' => 'order_delete(%id)',
                'condition' => "%act_status==0",
            ],
            '恢复'=> [
                'style' => 'success',
                'fun' => 'recovery(%id)',
                'condition' => "%order_status==2 && %act_status==0",
            ]
        ];
        $datas['pages'] = [
            'sum' => D('OrderTipsView')->where($condition)->count( 'DISTINCT A.id'),
            'count' => 30,
        ];
        $datas['lang'] = [
            'id' => 'ID',
            'sn' => '订单号',
            'tips_title' =>'活动名称',
            'tips_times_start_time' => '开始时间',
            'tips_times_end_time' => '结束时间',
            'tips_times_phase' => '期数',
            'category_name' => '分类',
            'customer' => '购买者信息',
            'inviter_nickname' => '邀请人昵称',
            'order_wares_price' => '单价',
            'buy_num' => '购买数量',
            'price' => '实付金额',
            'discount' => '优惠金额',
            //'act_pay' => '实际支付',
            'create_time' => '下单时间',
            'limit_pay_time' => '限制支付时间',
            'success_pay_time' => '支付时间',
            'pay_type' => '支付方式',
            'order_wares_server_status' => '消费码状态',
            'status' => '订单状态'
        ];

        $this->assign($datas);
        $this->view();
    }

    //活动订单导出
    public function TipsOrderExport_old(){
        ini_set('memory_limit', '256M');
        $citys = [];
        foreach(C('CITY_CONFIG') as $id => $val){
            $citys[$val] = $this->m2('citys')->where(['id|pid' => $id])->getField('id', true);
        }

        $search_status = I('get.status');

        $condition = array();

//        if(isset($_GET['status']) && is_numeric($_GET['status'])){
//            $condition['A.status'] = array('EQ', $_GET['status']);
//        }else{
//            $condition['A.status'] = array('EQ', 1);
//        }
        //条件筛选
        if(IS_GET && $_GET !=null){
            $search_sn = I('get.sn');
            $search_title = I('get.title');
            $search_member = I('get.member');
            $start_time = I('get.start_order_time');
            $end_time = I('get.stop_order_time');
            $search_start_order_time = strtotime($start_time);
            $search_stop_order_time = strtotime($end_time);
            $search_act_status =  I('get.act_status');
            $search_status =  I('get.status');
            $search_city = I('get.city');
            $search_sn && $condition['A.sn'] = array('EQ',$search_sn);
            $search_title && $condition['H.title'] = array('LIKE','%'.$search_title.'%');
            $search_member && $condition['B.nickname'] = array('LIKE','%'.$search_member.'%');
            if($start_time && $end_time){
                $condition['A.create_time'] = array('BETWEEN',"$search_start_order_time,$search_stop_order_time");
            }else{
                $start_time && $condition['A.create_time'] = array('GT',$search_start_order_time);
                $end_time && $condition['A.create_time'] = array('LT',$search_stop_order_time);
            }
            //$search_stop_order_time && $condition['A.create_time'] = array('ELT',$search_stop_order_time);
            $search_act_status!='' && $condition['A.act_status'] = array('EQ',$search_act_status);
            $search_status!='' && $condition['A.status'] = array('EQ',$search_status);
            if(!empty($search_city)){
                $area = $this->m2('Citys')->where(['pid'=>$search_city])->select();
                foreach($area as $re){
                    $area_id[] = $re['id'];
                }
                if(!empty($area_id)){
                    $city_id = join(',',$area_id);
                    $city_id .= ','.$search_city;
                }else{
                    $city_id = $search_city;
                }
            }
            $search_city && $condition['E.id'] = ['IN',$city_id];
            //$Order_Tips_mod = D('OrderTipsView');
            //$datas['datas'] = $Order_Tips_mod->where($condition)->page(I('get.page'), 40)->order('id desc')->select();

        }
        $Order_Tips_mod = D('TipsOrderExportView');
        //$condition['A.status'] = ['IN','1,2'];
        //$condition['A.act_status'] = array('IN','1,2,3,4,5,6');
        $condition['F.type'] = ['EQ',0];
        $condition['A.oldid'] = ['EXP', 'is null'];
        $datas['datas'] = $Order_Tips_mod->where($condition)->order('id desc')->group('A.id')->limit(1000)->select();
//        print_r($Order_Tips_mod->getLastSql());
//        print_r($datas['datas']);

        //计算优惠券的实际优惠金额和实际支付金额
        foreach($datas['datas'] as $key=>$row){
            if($row['coupon_type'] == 0 && $row['coupon_type']!=''){       //抵价券
                $datas['datas'][$key]['coupon_type'] = '抵价券';
                $datas['datas'][$key]['discount'] = $row['coupon_value'];
            }elseif($row['coupon_type'] == 1){                                      //折扣券
                $datas['datas'][$key]['coupon_type'] = '折扣券';
                $datas['datas'][$key]['discount'] = (100-$row['coupon_value'])*0.01*$row['price'];
            }elseif($row['coupon_type'] == 2){                                      //礼品券
                $datas['datas'][$key]['coupon_type'] = '礼品券';
                $datas['datas'][$key]['discount'] = 0;
            }else{
                $datas['datas'][$key]['discount'] = 0;                              //不使用优惠券
            }
        }

        //获取订单状态以及转换时间戳
        foreach($datas['datas'] as $key=>$row){
            if($row['act_status'] == 0){
                $datas['datas'][$key]['act_status'] = '未支付';
            }elseif($row['act_status'] == 1){
                $datas['datas'][$key]['act_status'] = '已支付（未参加）';
            }elseif($row['act_status'] == 2){
                $datas['datas'][$key]['act_status'] = '已支付（已参加）';
            }elseif($row['act_status'] == 3){
                $datas['datas'][$key]['act_status'] = '已支付（未参加）';
            }elseif($row['act_status'] == 4){
                $datas['datas'][$key]['act_status'] = '已支付（已参加）';
            }elseif($row['act_status'] == 5){
                $datas['datas'][$key]['act_status'] = '申请退款';
            }elseif($row['act_status'] == 6){
                $datas['datas'][$key]['act_status'] = '退款申请已处理';
            }elseif($row['act_status'] == 7){
                $datas['datas'][$key]['act_status'] = '已取消';
            }elseif($row['act_status'] == 8){
                $datas['datas'][$key]['act_status'] = '系统自动操作，退款中';
            }

            $datas['datas'][$key]['origin_price'] = $row['buy_num'] * $row['order_wares_price'];
            $datas['datas'][$key]['start_time'] = date('Y/m/d H:i',$datas['datas'][$key]['start_time']);
            $datas['datas'][$key]['end_time'] = date('Y/m/d H:i',$datas['datas'][$key]['end_time']);
            $datas['datas'][$key]['create_time'] = date('Y/m/d H:i',$datas['datas'][$key]['create_time']);
            if($row['order_wares_server_status'] == 0)$datas['datas'][$key]['order_wares_server_status'] = '未验票';
            if($row['order_wares_server_status'] == 1)$datas['datas'][$key]['order_wares_server_status'] = '已验票';
            foreach($citys as $k => $r){
                if(in_array($row['citys_id'], $r))$datas['datas'][$key]['citys_name'] = $k;
            }
            unset($datas['datas'][$key]['order_wares_type']);
            unset($datas['datas'][$key]['citys_id']);
            if(!empty($row['invite_member_id'])){
                $inviter_nickname = $this->m2('member')->where(['id'=>$row['invite_member_id']])->getField('nickname');
            }
            $datas['datas'][$key]['inviter_nickname'] = $inviter_nickname;
            $datas['datas'][$key]['price'] = $row['price'] - $row['order_refund_money'];
            $datas['datas'][$key]['space_address'] = $this->m2('space')->where(['id'=>$row['space_id']])->getField('address');

            if($row['act_status'] !=0){
                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                $datas['datas'][$key]['success_pay_time'] = $pay_arr['success_pay_time'] ? date('Y-m-d H:i',$pay_arr['success_pay_time']) : '';
                $datas['datas'][$key]['pay_type'] = $pay_arr['type'] !== null ? $pay_arr['type'] : null;
            }else{
                $datas['datas'][$key]['success_pay_time'] ='';
                $datas['datas'][$key]['pay_type'] = null;
            }
        }
//        print_r($datas);
        $_data = [];
        foreach($datas['datas'] as $rr){
            $data = [];
            $data[] = $rr['start_time'];
            $data[] = $rr['end_time'];
            $data[] = $rr['order_wares_ware_id'];
            $data[] = $rr['sn'];
            $data[] = $rr['id'];
            $data[] = $rr['tips_title'];
            $data[] = $rr['tips_times_phase'];
            $data[] = $rr['space_address'];
            $data[] = $rr['citys_name'];
            $data[] = $rr['category_name'];
            $data[] = $rr['member_nickname'];
            $data[] = $rr['member_telephone'];
            $data[] = $rr['inviter_nickname'];
            $data[] = $rr['buy_num'];
            $data[] = $rr['order_wares_price'];
            $data[] = $rr['coupon_value'];
            $data[] = $rr['coupon_type'];
            $data[] = $rr['tips_discount'].'%';
            $data[] = $rr['member_coupon_sn'];
            $data[] = $rr['order_refund_money'];
            $data[] = $rr['order_refund_cause'];
            $data[] = $rr['origin_price'];
            $data[] = $rr['price'];
            $data[] = $rr['create_time'];
            $data[] = $rr['success_pay_time'];
            //支付类型
            $pay_type = '';
            if($rr['pay_type'] !== null){
                $pay_type = ['支付宝','微信APP','微信公众号','支付宝网页支付'][$rr['pay_type']];
            }
            $data[] = $pay_type;
            //支付渠道
            if(in_array($rr['channel'], [7,8,9])){
                $channel = '我有饭';
            }elseif(in_array($rr['channel'], [0,1,2])){
                $channel = '吖咪';
            }else{
                $channel = '第三方';
            }
            $data[] = $channel;
            $data[] = $rr['act_status'];
            //$data[] = $rr['order_wares_check_code'];
            $data[] = $rr['order_wares_server_status'];
            $data[] = $rr['context'];
            $_data[] = $data;
        }

//        print_r($datas['datas']);exit;
        /*$comma_data = $title = array();
        foreach($_data as $row){
            $d = $title = array();
            foreach($row as $k => $r){
                $title[] = $k;
                $r = str_replace(',','，',$r);
                $d[] = iconv('utf-8','gb2312',$r);
            }
            $comma_data[] = join("\t", $d);
        }
        //$title = join("\t",$title);
        $title = '活动开始时间'."\t".'活动结束时间''活动ID''订单号''ID''活动名称''期数''活动地址''城市''分类''购买人''联系电话''数量''活动单价'
        $title = iconv('utf-8','gb2312',$title);
        $comma_data = $title . "\n" . join("\n", $comma_data);

        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename="."活动订单数据".date("Y-m-d",time()).".xls");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $comma_data;
        exit;*/
//        print_r($_data);
        $titleArr = ['活动开始时间','活动结束时间','活动ID','订单号','ID','活动名称','期数','活动地址','城市','分类','购买人','联系电话','邀请人','数量','活动单价','优惠券值','优惠券类型','包桌折扣','优惠券码','退款金额','退款理由','订单原价','实付金额','下单时间','支付时间','支付类型','支付渠道','订单状态','验票','留言'];
        toXls($titleArr,$_data,'活动订单');
    }

    //活动订单导出(2017-02-06)
    public function TipsOrderExport(){
        ini_set('memory_limit', '256M');
        $citys = [];
        foreach(C('CITY_CONFIG') as $id => $val){
            $citys[$val] = $this->m2('citys')->where(['id|pid' => $id])->getField('id', true);
        }

        $condition = array();

        //条件筛选
        if(IS_GET && $_GET !=null){
            $search_sn = I('get.sn');
            $search_title = I('get.title');
            $search_member = I('get.member');
            $start_time = I('get.start_order_time');
            $end_time = I('get.stop_order_time');
            $search_start_order_time = strtotime($start_time);
            $search_stop_order_time = strtotime($end_time);
            $search_act_status =  I('get.act_status');
            $search_status =  I('get.status');
            $search_city = I('get.city');
            $search_sn && $condition['A.sn'] = array('EQ',$search_sn);
            $search_title && $condition['H.title'] = array('LIKE','%'.$search_title.'%');
            $search_member && $condition['B.nickname'] = array('LIKE','%'.$search_member.'%');
            if($start_time && $end_time){
                $condition['A.create_time'] = array('BETWEEN',"$search_start_order_time,$search_stop_order_time");
            }else{
                $start_time && $condition['A.create_time'] = array('GT',$search_start_order_time);
                $end_time && $condition['A.create_time'] = array('LT',$search_stop_order_time);
            }
            //$search_stop_order_time && $condition['A.create_time'] = array('ELT',$search_stop_order_time);
            $search_act_status!='' && $condition['A.act_status'] = array('EQ',$search_act_status);
            $search_status!='' && $condition['A.status'] = array('EQ',$search_status);
            if(!empty($search_city)){
                $area = $this->m2('Citys')->where(['pid'=>$search_city])->select();
                foreach($area as $re){
                    $area_id[] = $re['id'];
                }
                if(!empty($area_id)){
                    $city_id = join(',',$area_id);
                    $city_id .= ','.$search_city;
                }else{
                    $city_id = $search_city;
                }
            }
            $search_city && $condition['E.id'] = ['IN',$city_id];
            //$Order_Tips_mod = D('OrderTipsView');
            //$datas['datas'] = $Order_Tips_mod->where($condition)->page(I('get.page'), 40)->order('id desc')->select();

        }
        $Order_Tips_mod = D('TipsOrderExportView');
        //$condition['A.status'] = ['IN','1,2'];
        //$condition['A.act_status'] = array('IN','1,2,3,4,5,6');
        $condition['F.type'] = ['EQ',0];
        $condition['A.oldid'] = ['EXP', 'is null'];
        $orderArr = $Order_Tips_mod->where($condition)->order('create_time desc id desc')->group('A.id')->limit(1000)->getField('id' ,true);
//        $order_Arr = $this->m2('act_logs')->field('type_id,context')->where(['type'=>3,'type_id'=>['IN',$orderArr]])->order('datetime desc')->group('type_id')->select();
//        $sql = 'select * from `ym_snapshot_logs` AS A WHERE A.id in(select MAX(B.id) from `ym_snapshot_logs` AS B ON B.id = A.id WHERE `type` = 3 AND `type_id` IN ('.join(',',$orderArr).') order by id desc) group by type_id order by datetime desc';
        $sql = 'select * from `ym_snapshot_logs` where id in(select SUBSTRING_INDEX(group_concat(id order by `datetime` desc),",",1) from `ym_snapshot_logs`  WHERE `type` = 3 AND `type_id` IN ('.join(',',$orderArr).') group by type_id ) order by `datetime` desc';
        $order_Arr = $this->m2()->query($sql);
        foreach($order_Arr as $key => $val){
            $orderDetail = json_decode($val['context'],true);

            //计算优惠券的实际优惠金额和实际支付金额
            if(!empty($orderDetail['member_coupon_id']) && $orderDetail['member_coupon_id'] !==0){
                $OrderCoupon = D('CouponView')->where(['id' => $orderDetail['member_coupon_id']])->find();

                $datas['datas'][$key]['coupon_value'] =$OrderCoupon['coupon_value'];
                $datas['datas'][$key]['member_coupon_sn'] =$OrderCoupon['member_coupon_sn'];
                if($OrderCoupon['coupon_type'] == 0 && $OrderCoupon['coupon_type']!=''){       //抵价券
                    $datas['datas'][$key]['coupon_type'] = '抵价券';
                    $datas['datas'][$key]['discount'] = $OrderCoupon['coupon_value'];
                }elseif($OrderCoupon['coupon_type'] == 1){                                      //折扣券
                    $datas['datas'][$key]['coupon_type'] = '折扣券';
                    $datas['datas'][$key]['discount'] = (100-$OrderCoupon['coupon_value'])*0.01*$OrderCoupon['price'];
                }elseif($OrderCoupon['coupon_type'] == 2){                                      //礼品券
                    $datas['datas'][$key]['coupon_type'] = '礼品券';
                    $datas['datas'][$key]['discount'] = 0;
                }else{
                    $datas['datas'][$key]['coupon_type'] = '';
                    $datas['datas'][$key]['discount'] = 0;                              //不使用优惠券
                }
            }else{
                $datas['datas'][$key]['coupon_value'] =0;
                $datas['datas'][$key]['coupon_type'] = '';
                $datas['datas'][$key]['discount'] = 0;                              //不使用优惠券
                $datas['datas'][$key]['member_coupon_sn'] = '';                              //不使用优惠券

            }

            //获取订单状态以及转换时间戳
            if($orderDetail['act_status'] == 0){
                $datas['datas'][$key]['act_status_w'] = '未支付';
            }elseif($orderDetail['act_status'] == 1){
                $datas['datas'][$key]['act_status_w'] = '已支付（未参加）';
            }elseif($orderDetail['act_status'] == 2){
                $datas['datas'][$key]['act_status_w'] = '已支付（已参加）';
            }elseif($orderDetail['act_status'] == 3){
                $datas['datas'][$key]['act_status_w'] = '已支付（未参加）';
            }elseif($orderDetail['act_status'] == 4){
                $datas['datas'][$key]['act_status_w'] = '已支付（已参加）';
            }elseif($orderDetail['act_status'] == 5){
                $datas['datas'][$key]['act_status_w'] = '申请退款';
            }elseif($orderDetail['act_status'] == 6){
                $datas['datas'][$key]['act_status_w'] = '退款申请已处理';
            }elseif($orderDetail['act_status'] == 7){
                $datas['datas'][$key]['act_status_w'] = '已取消';
            }elseif($orderDetail['act_status'] == 8){
                $datas['datas'][$key]['act_status_w'] = '系统自动操作，退款中';
            }
            $Wares = $orderDetail['Wares'];
            $tips = $orderDetail['tips'];
            $times = $tips['times'];
            $OrderRefund = $orderDetail['OrderRefund'];
//            $OrderPay = $orderDetail['OrderPay'];
            $datas['datas'][$key]['buy_num'] =  count($orderDetail['Wares']);
            $datas['datas'][$key]['origin_price'] = count($orderDetail['Wares']) * $Wares[0]['price'];
            $datas['datas'][$key]['start_time'] = date('Y/m/d H:i',$times['start_time']);
            $datas['datas'][$key]['end_time'] = date('Y/m/d H:i',$times['end_time']);
            $datas['datas'][$key]['create_time'] = date('Y/m/d H:i',$orderDetail['create_time']);
            if($Wares[0]['server_status'] == 0)$datas['datas'][$key]['order_wares_server_status'] = '未验票';
            if($Wares[0]['server_status'] == 1)$datas['datas'][$key]['order_wares_server_status'] = '已验票';
            if(!empty($tips['category_id'])){
                $datas['datas'][$key]['category_name'] = $this->m2('category')->where(['id'=>$tips['category_id']])->getField('name');
            }else{
                $datas['datas'][$key]['category_name'] = '';
            }
            foreach($citys as $k => $r){
                if(in_array($tips['citys_id'], $r))$datas['datas'][$key]['citys_name'] = $k;
            }
            $datas['datas'][$key]['price'] = $OrderRefund?($orderDetail['price'] - $OrderRefund['money']):$orderDetail['price'];
            $datas['datas'][$key]['order_refund_money'] = $OrderRefund?$OrderRefund['money']:'';
            $datas['datas'][$key]['order_refund_cause'] = $OrderRefund?$OrderRefund['cause']:'';
            $datas['datas'][$key]['space_address'] = $this->m2('space')->where(['id'=>$tips['space_id']])->getField('address');

            if($orderDetail['act_status'] !=0){
                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$orderDetail['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                $datas['datas'][$key]['success_pay_time'] = $pay_arr['success_pay_time'] ? date('Y-m-d H:i',$pay_arr['success_pay_time']) : '';
                $datas['datas'][$key]['pay_type'] = $pay_arr['type'] !== null ? $pay_arr['type'] : null;

            }else{
                $datas['datas'][$key]['success_pay_time'] ='';
                $datas['datas'][$key]['pay_type'] = null;
            }
            $datas['datas'][$key]['order_wares_ware_id'] = $Wares[0]['ware_id'];
            $datas['datas'][$key]['sn'] = $orderDetail['sn'];
            $datas['datas'][$key]['id'] = $orderDetail['id'];
            $datas['datas'][$key]['tips_title'] = $tips['title'];
            $datas['datas'][$key]['tips_times_phase'] = $times['phase'];
            $member = $this->m2('member')->field('nickname,telephone')->where(['id'=>$orderDetail['member_id']])->find();
            $datas['datas'][$key]['member_nickname'] =$member['nickname'];
            $datas['datas'][$key]['member_telephone'] =$member['telephone'];
            if(!empty($orderDetail['invite_member_id'])){
                $inviter_nickname = $this->m2('member')->where(['id'=>$orderDetail['invite_member_id']])->getField('nickname');
            }
            $datas['datas'][$key]['inviter_nickname'] = $inviter_nickname;
            $datas['datas'][$key]['order_wares_price'] =$Wares[0]['price'];
            $datas['datas'][$key]['tips_discount'] =$tips['discount'];
            $datas['datas'][$key]['channel'] =$orderDetail['channel'];
            $datas['datas'][$key]['server_status'] =$Wares[0]['server_status'];
            $datas['datas'][$key]['context'] =$orderDetail['context'];
        }
        $_data = [];
        foreach($datas['datas'] as $rr){
            $data = [];
            $data[] = $rr['start_time'];
            $data[] = $rr['end_time'];
            $data[] = $rr['order_wares_ware_id'];
            $data[] = $rr['sn'];
            $data[] = $rr['id'];
            $data[] = $rr['tips_title'];
            $data[] = $rr['tips_times_phase'];
            $data[] = $rr['space_address'];
            $data[] = $rr['citys_name'];
            $data[] = $rr['category_name'];
            $data[] = $rr['member_nickname'];
            $data[] = $rr['member_telephone'];
            $data[] = $rr['inviter_nickname'];
            $data[] = $rr['buy_num'];
            $data[] = $rr['order_wares_price'];
            $data[] = $rr['coupon_value'];
            $data[] = $rr['coupon_type'];
            $data[] = $rr['tips_discount'].'%';
            $data[] = $rr['member_coupon_sn'];
            $data[] = $rr['order_refund_money'];
            $data[] = $rr['order_refund_cause'];
            $data[] = $rr['origin_price'];
            $data[] = $rr['price'];
            $data[] = $rr['create_time'];
            $data[] = $rr['success_pay_time'];
            //支付类型
            $pay_type = '';
            if($rr['pay_type'] !== null){
                $pay_type = ['支付宝','微信APP','微信公众号','支付宝网页支付'][$rr['pay_type']];
            }
            $data[] = $pay_type;
            //支付渠道
            if(in_array($rr['channel'], [7,8,9])){
                $channel = '我有饭';
            }elseif(in_array($rr['channel'], [0,1,2])){
                $channel = '吖咪';
            }else{
                $channel = '第三方';
            }
            $data[] = $channel;
            $data[] = $rr['act_status_w'];
            //$data[] = $rr['order_wares_check_code'];
            $data[] = $rr['order_wares_server_status'];
            $data[] = $rr['context'];
            $_data[] = $data;
        }
        $titleArr = ['活动开始时间','活动结束时间','活动ID','订单号','ID','活动名称','期数','活动地址','城市','分类','购买人','联系电话','邀请人','数量','活动单价','优惠券值','优惠券类型','包桌折扣','优惠券码','退款金额','退款理由','订单原价','实付金额','下单时间','支付时间','支付类型','支付渠道','订单状态','验票','留言'];
        toXls($titleArr,$_data,'活动订单');
    }

    //众筹订单列表
    public function crowdfundOrder(){
        $this->actname = '众筹订单';

        if(IS_AJAX && IS_POST){
            //发送未筛选的短信
            if($_POST['TypeName'] == 'Ordersend' && $_POST['id']){
                $order_id = I('post.id');
                $limit_day = I('post.limit_day');
                $rs = D('OrderRaiseView')->where(['order_id'=>$order_id])->find();
                $params = array(
                    'project_name' => '众筹',
                    'project_title' => $rs['title'],
                    'limit_day' => $limit_day,
                    'wx' => 'yami194'
                );
                $this->push_Message($rs['member_id'], $params,'SMS_56100067', 'sms',null, 0, 0,0,0);
                $this->m2('order')->where(['id'=>$order_id])->save(['is_send'=>1]);
                $this->success('发送成功！');
            }
        }
        //条件筛选
        if(IS_GET && $_GET !=null){
            $search_sn = I('get.sn');
            $search_title = I('get.title');
            $search_member = I('get.member');
            $start = I('get.start_order_time');
            $end = I('get.stop_order_time');
            $search_start_order_time = strtotime($start);
            $search_stop_order_time = strtotime($end);
            $search_act_status = I('get.act_status');
            $search_status = I('get.status');
            $search_goods_type = I('get.goods_type');
            $search_is_free = I('get.is_free');
            $condition = [];

            $search_sn && $condition['A.sn'] = ['EQ',$search_sn];
            $search_title && $condition['H.title'] = ['LIKE','%'.$search_title.'%'];
            $search_member && $condition['B.nickname'] = ['LIKE','%'.$search_member.'%'];

            if($start && $end){
                $condition['A.create_time'] = ['BETWEEN',"$search_start_order_time,$search_stop_order_time"];
            }else{
                $start && $condition['A.create_time'] = ['GT',$search_start_order_time];
                $end && $condition['A.create_time'] = ['LT',$search_stop_order_time];
            }
            if($search_goods_type==1){//全款
                $condition['raise_times_prepay'] = ['EQ',0];
            }elseif($search_goods_type==2){//预付金
                $condition['raise_times_prepay'] = ['GT',0];
                $condition['A.order_pid'] = ['exp','is null'];
            }elseif($search_goods_type==3){//尾款
                $condition['A.order_pid'] = ['exp','is not null'];
            }

            $search_is_free != '' && $condition['A.is_free'] = ['EQ', intval($search_is_free)];

            $search_act_status!='' && $condition['A.act_status'] = ['EQ',$search_act_status];

            $search_sn && $this->assign('search_sn',$search_sn);
            $search_title && $this->assign('search_title',$search_title);
            $search_member && $this->assign('search_member',$search_member);
            $search_status && $this->assign('search_status', $_GET['status']);
            $search_start_order_time && $this->assign('search_start_order_time',date('Y-m-d H:i',$search_start_order_time));
            $search_stop_order_time && $this->assign('search_stop_order_time',date('Y-m-d H:i',$search_stop_order_time));
            $search_act_status!='' && $this->assign('search_act_status',$search_act_status);
            $search_goods_type!='' && $this->assign('search_goods_type',$search_goods_type);
            $search_is_free != '' && $this->assign('search_is_free', intval($search_is_free) + 1);
        }
        $condition['F.type'] = ['EQ',2];
//        $condition['A.act_status'] = ['NEQ',11];
        $datas['datas'] = D('OrderCrowdfundsView')->where($condition)->page(I('get.page', 1), 30)->order('id desc')->group('A.id')->select();
//        print_r(D('OrderCrowdfundsView')->getLastSql());
//        foreach($datas['datas'] as $re){
//            $ids[] = $re['id'];
//        }
//        $ids = join(',',$ids);

        //计算优惠券的实际优惠金额和实际支付金额
        foreach($datas['datas'] as $key=>$row){
            if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                $datas['datas'][$key]['discount'] = $row['coupon_value'];
                //$datas['datas'][$key]['act_pay'] = $row['price']-$datas['datas'][$key]['discount'];
                //if($datas['datas'][$key]['act_pay']<0)$datas['datas'][$key]['act_pay']=0;
                // echo $datas['datas'][$key]['discount'].'----'.$datas['datas'][$key]['act_pay'].'</br>';
            }elseif($row['coupon_type'] == 1){                                      //折扣券
                $datas['datas'][$key]['discount'] = ($row['price']/($row['coupon_value']*0.01)-$row['price']);
                //$datas['datas'][$key]['act_pay'] = $row['price']-$datas['datas'][$key]['discount'];
                // echo $datas['datas'][$key]['discount'].'----'.$datas['datas'][$key]['act_pay'].'</br>';
            }elseif($row['coupon_type'] == 2){                                      //礼品券
                $datas['datas'][$key]['discount'] = 0;
                //$datas['datas'][$key]['act_pay'] = $row['price'];
                // echo $datas['datas'][$key]['discount'].'----'.$datas['datas'][$key]['act_pay'].'</br>';
            }else{
                $datas['datas'][$key]['discount'] = 0;                              //不使用优惠券
                //$datas['datas'][$key]['act_pay'] = $row['price'];
                //echo $datas['datas'][$key]['discount'].'----'.$datas['datas'][$key]['act_pay'].'</br>';
            }

            //数据处理
            if($row['act_status'] == 0){
                $datas['datas'][$key]['status'] = '未支付';
            }elseif(in_array($row['act_status'],[1,2,3,4])){
                $datas['datas'][$key]['status'] = '已支付';
            }elseif(in_array($row['act_status'],[5,8])){
                $datas['datas'][$key]['status'] = '退款中';
            }elseif($row['act_status'] == 6){
                $datas['datas'][$key]['status'] = '退款成功';
            }elseif($row['act_status'] == 7){
                $datas['datas'][$key]['status'] = '已取消';
            }
            $order_count = $this->m2('order')->where(['order_pid'=>$row['id'],'status'=>1,'act_status'=>['NEQ',11]])->count();
            if($order_count==0){
                $datas['datas'][$key]['order_type'] =1;//二次付款未生成订单
            }else{
                $datas['datas'][$key]['order_type'] =2;//二次付款已生成订单
            }
            if($row['raise_times_prepay']>0 && empty($row['order_pid'])){
                $datas['datas'][$key]['raise_type'] =1;//预付款
            }elseif($row['raise_times_prepay']>0 && !empty($row['order_pid'])){
                $datas['datas'][$key]['raise_type'] =2;//尾款
                $datas['datas'][$key]['raise_title'] =$row['raise_title'].'【二次支付】';
                $datas['datas'][$key]['raise_times_title'] =$row['raise_times_title'].'【二次支付】';
            }elseif($row['raise_times_prepay']<=0 && empty($row['order_pid'])){
                $datas['datas'][$key]['raise_type'] =3;//全款
            }
            if(empty($row['order_pid'])){
                $datas['datas'][$key]['order_pid_s'] = '为空';
            }else{
                $datas['datas'][$key]['order_pid_s'] = '不为空';
            }

            //是否使用特权下单的
            $datas['datas'][$key]['is_privilege'] = 0;
            $privilege = $this->m2('member_privilege')->where(['order_id'=>$row['id'],'type'=>2])->find();
            if(!empty($privilege) && $row['raise_times_start_time']>time())$datas['datas'][$key]['is_privilege'] = 1;

            if($row['act_status'] !=0){
                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                if(!empty($pay_arr['success_pay_time'])){
                    $datas['datas'][$key]['success_pay_time'] =  date('Y-m-d H:i',$pay_arr['success_pay_time']);
                    if($pay_arr['type'] == 0){
                        $datas['datas'][$key]['pay_type'] =  '支付宝客户端';
                    }elseif($pay_arr['type'] == 1){
                        $datas['datas'][$key]['pay_type'] =  '微信APP';
                    }elseif($pay_arr['type'] == 2){
                        $datas['datas'][$key]['pay_type'] =  '微信公众号';
                    }elseif($pay_arr['type'] == 3){
                        $datas['datas'][$key]['pay_type'] =  '支付宝网页支付';
                    }elseif($pay_arr['type'] == 4){
                        $datas['datas'][$key]['pay_type'] =  '小程序支付';
                    }

                }else{
                    $datas['datas'][$key]['pay_type'] =  '';
                    $datas['datas'][$key]['success_pay_time'] ='';
                }
            }else{
                $datas['datas'][$key]['pay_type'] =  '';
                $datas['datas'][$key]['success_pay_time'] ='';
            }

            $datas['datas'][$key]['raise_times_start_time'] = date('Y-m-d H:i',$datas['datas'][$key]['raise_times_start_time']);
            $datas['datas'][$key]['raise_times_end_time'] = date('Y-m-d H:i',$datas['datas'][$key]['raise_times_end_time']);
            $datas['datas'][$key]['create_time'] = date('Y-m-d H:i',$datas['datas'][$key]['create_time']);
            $datas['datas'][$key]['limit_pay_time'] = date('Y-m-d H:i',$datas['datas'][$key]['limit_pay_time']);
            $datas['datas'][$key]['customer'] = '昵称：'.$row['member_nickname']."<br/>".'ID号：'.$row['member_id']."<br/>".'手机：'.$row['member_telephone']."<br/>".'留言：'.$row['context'];
           // $datas['datas'][$key]['order_wares_server_status'] = $datas['datas'][$key]['order_wares_server_status'] ==0?'未验票':'已验票';

        }


//        var_dump($datas['datas']);exit;

        //table页面参数设置
        $datas['operations'] = [
            '快照'=>  'snapshot(%id)',
            '退款'=> [
                'style' => 'danger',
                'fun' => 'OrderRefund(%id,%price)',
                'condition' => "%act_status==1",
            ],
            '二次支付'=> [
                'style' => 'secondary',
                'fun' => 'OrderNextpay(%id)',
                'condition' => "%raise_type==1 && %act_status==1 && %order_type==1",
            ],
            '发送未筛选短信'=> [
                'style' => 'primary',
                'fun' => 'Ordersend(%id)',
                'condition' => "%is_send==0 && %order_pid_s == '为空'&& %order_type==1 && %raise_type==1 && in_array(%act_status,[1,2,3,4])",
            ],
            '取消'=> [
                'style' => 'warning',
                'fun' => 'cancel(%id)',
                'condition' => "%order_status==1 && %act_status==0",
            ],
            '删除'=> [
                'style' => 'danger',
                'fun' => 'order_delete(%id)',
                'condition' => "%act_status==0",
            ],
            '恢复'=> [
                'style' => 'success',
                'fun' => 'recovery(%id)',
                'condition' => " %order_status==2 && %act_status==0",
            ],
            '优先特权下单'=> [
                'style' => 'secondary',
                'fun' => 'Userprivilege(%id)',
                'condition' => "%is_privilege==1 && %act_status==1&& %order_type==1",
            ],
        ];
        $datas['pages'] = [
            'sum' => D('OrderCrowdfundsView')->where($condition)->count(),
            'count' => 30,
        ];

        $datas['lang'] = [
            'id' => 'ID',
            'sn' => '订单号',
            'raise_title' =>'活动名称',
            'raise_times_start_time' => '开始时间',
            'raise_times_end_time' => '结束时间',
           // 'tips_times_phase' => '期数',
            'category_name' => '分类',
            'customer' => '购买者信息',
            'inviter_nickname' => '邀请人昵称',
            'order_wares_price' => '单价',
            'buy_num' => '购买数量',
            'price' => '实付金额',
            'discount' => '优惠金额',
            //'act_pay' => '实际支付',
            'create_time' => '下单时间',
            'limit_pay_time' => '限制支付时间',
            'success_pay_time' => '支付时间',
            'pay_type' => '支付方式',
           // 'order_wares_server_status' => '消费码状态',
            'status' => '订单状态'
        ];

        $this->assign($datas);
        $this->view();
    }

    //众筹订单导出
    public function raiseOrderExport_old(){
        ini_set('memory_limit', '256M');

        $condition = array();

        //条件筛选
        if(IS_GET && $_GET !=null){
            $search_sn = I('get.sn');
            $search_title = I('get.title');
            $search_member = I('get.member');
            $start_time = I('get.start_order_time');
            $end_time = I('get.stop_order_time');
            $search_start_order_time = strtotime($start_time);
            $search_stop_order_time = strtotime($end_time);
            $search_act_status =  I('get.act_status');
            $search_status =  I('get.status');
            $search_goods_type = I('get.goods_type');
            $search_is_free = I('get.is_free');
            $search_sn && $condition['A.sn'] = array('EQ',$search_sn);
            $search_title && $condition['D.title'] = array('LIKE','%'.$search_title.'%');
            $search_member && $condition['B.nickname'] = array('LIKE','%'.$search_member.'%');
            if($start_time && $end_time){
                $condition['A.create_time'] = array('BETWEEN',"$search_start_order_time,$search_stop_order_time");
            }else{
                $start_time && $condition['A.create_time'] = array('GT',$search_start_order_time);
                $end_time && $condition['A.create_time'] = array('LT',$search_stop_order_time);
            }
            $search_act_status!='' && $condition['A.act_status'] = array('EQ',$search_act_status);
            $search_status!='' && $condition['A.status'] = array('EQ',$search_status);
            if($search_goods_type==1){//全款
                $condition['raise_times_prepay'] = ['EQ',0];
            }elseif($search_goods_type==2){//预付金
                $condition['raise_times_prepay'] = ['GT',0];
                $condition['A.order_pid'] = ['exp','is null'];
            }elseif($search_goods_type==3){//尾款
                $condition['A.order_pid'] = ['exp','is not null'];
            }

            if($search_is_free == 0) {
                // 非免费
                $condition['A.is_free'] = ['EQ', 0];
            } else if($search_is_free == 1) {
                // 免费订单
                $condition['A.is_free'] = ['EQ', 1];
            }
        }
        $Order_Tips_mod = D('RaiseOrderExportView');
        $condition['C.type'] = ['EQ',2];
        $datas['datas'] = $Order_Tips_mod->where($condition)->order('id desc')->group('A.id')->limit(1000)->select();
        //获取订单状态以及转换时间戳
        foreach($datas['datas'] as $key=>$row){
            if($row['act_status'] == 0){
                $datas['datas'][$key]['act_status_w'] = '未支付';
            }elseif(in_array($row['act_status'],[1,2,3,4])){
                $datas['datas'][$key]['act_status_w'] = '已支付';
            }elseif(in_array($row['act_status'],[5,8])){
                $datas['datas'][$key]['act_status_w'] = '申请退款';
            }elseif($row['act_status'] == 6){
                $datas['datas'][$key]['act_status_w'] = '退款申请已处理';
            }elseif($row['act_status'] == 7){
                $datas['datas'][$key]['act_status_w'] = '已取消';
            }
            if(!empty($row['raise_times_prepay'])&&$row['raise_times_prepay']>0){
                if( !empty($row['order_pid'])){
                    $title ='众筹标题：'.$row['raise_title'].',类目标题：'.$row['raise_times_title'].'【二次支付订单,父ID为：'.$row['order_pid'].'】';
                }else{
                    $title ='众筹标题：'.$row['raise_title'].',类目标题：'.$row['raise_times_title'].'【预计支付】';
                }
            }else{
                $title ='众筹标题：'.$row['raise_title'].',类目标题：'.$row['raise_times_title'].'【全额支付】';
            }

            if($row['act_status'] !=0){
                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                $datas['datas'][$key]['success_pay_time'] = $pay_arr['success_pay_time'] ? date('Y-m-d H:i',$pay_arr['success_pay_time']) : '';
                $datas['datas'][$key]['pay_type'] = $pay_arr['type'] !== null ? $pay_arr['type'] : null;
            }else{
                $datas['datas'][$key]['success_pay_time'] ='';
                $datas['datas'][$key]['pay_type'] = null;
            }

            if(!empty($row['invite_member_id'])){
                $inviter_nickname = $this->m2('member')->where(['id'=>$row['invite_member_id']])->getField('nickname');
            }
            $datas['datas'][$key]['inviter_nickname'] = $inviter_nickname;
            $datas['datas'][$key]['origin_price'] = $row['buy_num'] * $row['order_wares_price'];
            $datas['datas'][$key]['start_time'] = date('Y/m/d H:i',$datas['datas'][$key]['start_time']);
            $datas['datas'][$key]['end_time'] = date('Y/m/d H:i',$datas['datas'][$key]['end_time']);
            $datas['datas'][$key]['create_time'] = date('Y/m/d H:i',$datas['datas'][$key]['create_time']);
            $datas['datas'][$key]['raise_titles'] = $title;
            unset($datas['datas'][$key]['order_wares_type']);

        }
        $_data = [];
        foreach($datas['datas'] as $rr){
            $data = [];
            $data[] = $rr['start_time'];
            $data[] = $rr['end_time'];
            $data[] = $rr['id'];
            $data[] = $rr['sn'];
            $data[] = $rr['raise_id'];
            $data[] = $rr['raise_titles'];
            $data[] = $rr['member_nickname'];
            $data[] = $rr['weixincode'];
            $data[] = $rr['surname'];
            $data[] = $rr['identity'];
            $data[] = $rr['city_name'];
            $data[] = $rr['member_telephone'];
            $data[] = $rr['inviter_nickname'];
            $data[] = $rr['buy_num'];
            $data[] = $rr['price'];
            $data[] = $rr['create_time'];
            $data[] = $rr['success_pay_time'];
            //支付类型
            $pay_type = '';
            if($rr['pay_type'] !== null){
                $pay_type = ['支付宝','微信APP','微信公众号','支付宝网页支付'][$rr['pay_type']];
            }
            $data[] = $pay_type;
            //支付渠道
            if(in_array($rr['channel'], [7,8,9])){
                $channel = '我有饭';
            }elseif(in_array($rr['channel'], [0,1,2])){
                $channel = '吖咪';
            }else{
                $channel = '第三方';
            }
            //注册用户注册渠道
            if($rr['member_channel']==0){
                $member_channel = '吖咪Web';
            } elseif($rr['member_channel']==1) {
                $member_channel = '吖咪App';
            }elseif($rr['member_channel']==2) {
                $member_channel = '吖咪Android';
            }elseif($rr['member_channel']==7) {
                $member_channel = '我有饭Web';
            }elseif($rr['member_channel']==8) {
                $member_channel = '我有饭App';
            }elseif($rr['member_channel']==9) {
                $member_channel = '我有饭Android';
            }else{
                $member_channel = '第三方';
            }
            $data[] = $channel;
            $data[] = $member_channel;
            $data[] = $rr['act_status_w'];
            $data[] = $rr['context'];
            $_data[] = $data;
        }
        $titleArr = ['众筹开始时间','众筹结束时间','订单ID','订单号','众筹ID','众筹标题','购买人','微信号','真实姓名','身份证号','居住地区','联系电话','邀请人','数量','活动单价','下单时间','支付时间','支付类型','支付渠道','用户注册渠道','订单状态','留言'];
        toXls($titleArr,$_data,'众筹订单');
    }

    //众筹订单导出(2017-02-16)
    public function raiseOrderExport(){
        ini_set('memory_limit', '256M');

        $condition = array();

        //条件筛选
        if(IS_GET && $_GET !=null){
            $search_sn = I('get.sn');
            $search_title = I('get.title');
            $search_member = I('get.member');
            $start_time = I('get.start_order_time');
            $end_time = I('get.stop_order_time');
            $search_start_order_time = strtotime($start_time);
            $search_stop_order_time = strtotime($end_time);
            $search_act_status =  I('get.act_status');
            $search_status =  I('get.status');
            $search_goods_type = I('get.goods_type');
            $search_is_free = I('get.is_free');
            $search_sn && $condition['A.sn'] = array('EQ',$search_sn);
            $search_title && $condition['D.title'] = array('LIKE','%'.$search_title.'%');
            $search_member && $condition['B.nickname'] = array('LIKE','%'.$search_member.'%');
            if($start_time && $end_time){
                $condition['A.create_time'] = array('BETWEEN',"$search_start_order_time,$search_stop_order_time");
            }else{
                $start_time && $condition['A.create_time'] = array('GT',$search_start_order_time);
                $end_time && $condition['A.create_time'] = array('LT',$search_stop_order_time);
            }
            $search_act_status!='' && $condition['A.act_status'] = array('EQ',$search_act_status);
            $search_status!='' && $condition['A.status'] = array('EQ',$search_status);
            if($search_goods_type==1){//全款
                $condition['raise_times_prepay'] = ['EQ',0];
            }elseif($search_goods_type==2){//预付金
                $condition['raise_times_prepay'] = ['GT',0];
                $condition['A.order_pid'] = ['exp','is null'];
            }elseif($search_goods_type==3){//尾款
                $condition['A.order_pid'] = ['exp','is not null'];
            }

            $search_is_free != '' && $condition['A.is_free'] = ['eq', intval($search_is_free)];
        }
        $Order_Tips_mod = D('RaiseOrderExportView');
        $condition['C.type'] = ['EQ',2];
        $orderArr = $Order_Tips_mod->where($condition)->order('create_time asc id asc')->group('A.id')->limit(1000)->getField('id' ,true);
//        $sql = 'select * from (select * from  `ym_snapshot_logs` order by datetime desc ) AS  a  WHERE `type` = 3 AND `type_id` IN ('.join(',',$orderArr).') group by type_id order by id desc';

        $sql = 'select * from `ym_snapshot_logs` where id in(select SUBSTRING_INDEX(group_concat(id order by `datetime` desc),",",1) from `ym_snapshot_logs`  WHERE `type` = 3 AND `type_id` IN ('.join(',',$orderArr).') group by type_id ) order by `datetime` desc';
        $order_Arr = $this->m2()->query($sql);
        $datas['datas'] = array();
        //获取订单状态以及转换时间戳
        foreach($order_Arr as $key=>$row){
            $orderDetail = json_decode($row['context'],true);
            $raise = $orderDetail['raise'];
            $OrderPay = $orderDetail['OrderPay'];
            $OrderWares = $orderDetail['Wares'];
            $datas['datas'][$key]['id'] = $orderDetail['id'];
            $datas['datas'][$key]['sn'] = $orderDetail['sn'];
            $datas['datas'][$key]['raise_id'] = $raise['id'];

            $member = D('MemberInformationView')->where(['id'=>$orderDetail['member_id']])->find();
            $datas['datas'][$key]['member_nickname'] = $member['nickname'];
            $datas['datas'][$key]['weixincode'] = $member['weixincode'];
            $datas['datas'][$key]['identity'] = $member['identity'];
            $datas['datas'][$key]['surname'] = $member['surname'];
            $datas['datas'][$key]['city_name'] = $member['city_name'];
            $datas['datas'][$key]['member_telephone'] = $member['telephone'];
            if(!empty($orderDetail['invite_member_id'])){
                $inviter_nickname = $this->m2('member')->where(['id'=>$orderDetail['invite_member_id']])->getField('nickname');
            }
            $datas['datas'][$key]['inviter_nickname'] = $inviter_nickname;
            $datas['datas'][$key]['member_channel'] = $member['channel'];

            if($orderDetail['act_status'] == 0){
                $datas['datas'][$key]['act_status'] = '未支付';
            }elseif(in_array($orderDetail['act_status'],[1,2,3,4])){
                $datas['datas'][$key]['act_status'] = '已支付';
            }elseif(in_array($orderDetail['act_status'],[5,8])){
                $datas['datas'][$key]['act_status'] = '申请退款';
            }elseif($orderDetail['act_status'] == 6){
                $datas['datas'][$key]['act_status'] = '退款申请已处理';
            }elseif($orderDetail['act_status'] == 7){
                $datas['datas'][$key]['act_status'] = '已取消';
            }
            if(!empty($raise['times']['prepay']) && $raise['times']['prepay']>0){
                if( !empty($orderDetail['order_pid'])){
                    $title ='众筹标题：'.$raise['title'].',类目标题：'.$raise['times']['title'].'【二次支付订单,父ID为：'.$orderDetail['order_pid'].'】';
                }else{
                    $title ='众筹标题：'.$raise['title'].',类目标题：'.$raise['times']['title'].'【预计支付】';
                }
            }else{
                $title ='众筹标题：'.$raise['raise_title'].',类目标题：'.$raise['times']['title'].'【全额支付】';
            }

            if($orderDetail['act_status'] !=0){
//                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                $datas['datas'][$key]['success_pay_time'] = $OrderPay['success_pay_time'] ? date('Y-m-d H:i',$OrderPay['success_pay_time']) : '';
                $datas['datas'][$key]['pay_type'] = $OrderPay['type'] !== null ? $OrderPay['type'] : null;
            }else{
                $datas['datas'][$key]['success_pay_time'] ='';
                $datas['datas'][$key]['pay_type'] = null;
            }

            $datas['datas'][$key]['origin_price'] = count($OrderWares) * $OrderWares[0]['price'];
            $datas['datas'][$key]['start_time'] = date('Y/m/d H:i',$raise['start_time']);
            $datas['datas'][$key]['end_time'] = date('Y/m/d H:i',$raise['end_time']);
            $datas['datas'][$key]['create_time'] = date('Y/m/d H:i',$orderDetail['create_time']);
            $datas['datas'][$key]['success_pay_time'] = date('Y/m/d H:i',$OrderPay['success_pay_time']);
            $datas['datas'][$key]['raise_titles'] = $title;
            $datas['datas'][$key]['price'] = $orderDetail['price'];
            $datas['datas'][$key]['channel'] = $orderDetail['channel'];
            $datas['datas'][$key]['context'] = $orderDetail['context'];
            $datas['datas'][$key]['buy_num'] = count($orderDetail['Wares']);

        }
        $_data = [];
        foreach($datas['datas'] as $rr){
            $data = [];
            $data[] = $rr['start_time'];
            $data[] = $rr['end_time'];
            $data[] = $rr['id'];
            $data[] = $rr['sn'];
            $data[] = $rr['raise_id'];
            $data[] = $rr['raise_titles'];
            $data[] = $rr['member_nickname'];
            $data[] = $rr['weixincode'];
            $data[] = $rr['surname'];
            $data[] = $rr['identity'];
            $data[] = $rr['city_name'];
            $data[] = $rr['member_telephone'];
            $data[] = $rr['inviter_nickname'];
            $data[] = $rr['buy_num'];
            $data[] = $rr['price'];
            $data[] = $rr['create_time'];
            $data[] = $rr['success_pay_time'];
            //支付类型
            $pay_type = '';
            if($rr['pay_type'] !== null){
                $pay_type = ['支付宝','微信APP','微信公众号','支付宝网页支付'][$rr['pay_type']];
            }
            $data[] = $pay_type;
            //支付渠道
            if(in_array($rr['channel'], [7,8,9])){
                $channel = '我有饭';
            }elseif(in_array($rr['channel'], [0,1,2])){
                $channel = '吖咪';
            }else{
                $channel = '第三方';
            }
            //注册用户注册渠道
            if($rr['member_channel']==0){
                $member_channel = '吖咪Web';
            } elseif($rr['member_channel']==1) {
                $member_channel = '吖咪App';
            }elseif($rr['member_channel']==2) {
                $member_channel = '吖咪Android';
            }elseif($rr['member_channel']==7) {
                $member_channel = '我有饭Web';
            }elseif($rr['member_channel']==8) {
                $member_channel = '我有饭App';
            }elseif($rr['member_channel']==9) {
                $member_channel = '我有饭Android';
            }else{
                $member_channel = '第三方';
            }

            $data[] = $channel;
            $data[] = $member_channel;
            $data[] = $rr['act_status'];
            $data[] = $rr['context'];

            $order_address = D('RaiseOrderAddressView')->where(['A.id' => $rr['id']])->find();


            if ($order_address) {
                $address = D('CityView')->where(['A.id' => $order_address['order_city_id']])->find();
                $data[] = $address['province_name'].$address['province_alt'].$address['city_name'].$address['city_alt'].$address['district_name'].$address['district_alt'].$order_address['address'];
                $data[] = $order_address['linkman'];
				$data[] = $order_address['address_phone'];
            } else {
                $data[] = '';
                $data[] = '';
				$data[] = '';
            }
//            $data[] = $rr['is_free'] == 1 ? '是' : '否';
            $_data[] = $data;
        }
        $titleArr = ['众筹开始时间','众筹结束时间','订单ID','订单号','众筹ID','众筹标题','购买人','微信号','真实姓名','身份证号','居住地区','联系电话','邀请人','数量','活动单价','下单时间','支付时间','支付类型','支付渠道','用户注册渠道','订单状态','留言','收货地址','收货人姓名','收货地址手机'];
        toXls($titleArr,$_data,'众筹订单');
    }

    //众筹二次支付订单生成
    public function OrderNextpay(){
        $order_id = I('post.id');
        $limit_day = strtotime( I('post.limit_day').':00');
//        print_r(date('Y年m月d日 H时i分',$limit_day).'('.$this->weekday($limit_day).')');
//        exit;
        $order = D('OrderRaisePayView')->where(['A.id'=>$order_id])->find();
        $order_count = $this->m2('order')->where(['order_pid'=>$order_id,'status'=>1])->count();
        $raise_count = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['ware_id'=>$order['ware_id'],'tips_times_id'=>$order['tips_times_id'],'type'=>2,'order_pid'=>['EXP','is not null']])->count();
        if($raise_count > $order['screen_num']){
            $this->error('该订单对应的类目筛选人数为'.$order['screen_num'].',已生成'.$raise_count.'个订单了，不能操作二次订单生成了！');
        }
        if($order_count == 0){
            $order_code = createCode(18);
            $order_new_id = $this->m2('order')->add([
                'sn' => $order_code,
                'member_id' => $order['member_id'],
                'member_address_id' => $order['member_address_id'],
                'price' =>  (float)$order['raise_times_price']-(float)$order['raise_times_prepay'],
                'act_status' => 0,
                'create_time' => time(),
                'limit_pay_time' => $limit_day,
                'context' => $order['context'],
                'channel' => $order['channel'],
                'status' => 1,
                'order_pid' => $order['id']
            ]);
            //快照数据
            $snapshot = [
                'raise_id'=>$order['ware_id'],
                'raise_times_id'=>$order['tips_times_id'],
                'raise_title'=>$order['raise_title'],
                'raise_content'=>$order['content'],
                'raise_category'=>$order['catname'],
                'raise_introduction'=>$order['introduction'],
                'raise_total'=>$order['total'],
                'raise_price'=>$order['raise_times_price'],
                'raise_prepay'=>$order['raise_times_prepay'],
                'raise_type'=>$order['raise_times_prepay']>0 ? '预付方式' : '全额方式',
                'raise_act_pay'=>$order['raise_times_price']- $order['raise_times_prepay'],
                'datetime'=>time(),
            ];

            $code = createCode(8);
            $result = $this->m2('order_wares')->add([
                'order_id' => $order_new_id,
                'type' => 2,
                'ware_id' => $order['ware_id'],
                'price' => (float)$order['raise_times_price']-(float)$order['raise_times_prepay'],
                'check_code' => $code,
                'tips_times_id' => $order['tips_times_id'],
                'snapshot' => json_encode($snapshot)
            ]);

            if($result>0 && $order_new_id>0){

                $params = array(
                    'project_name'=>'众筹',
                    'title' =>$order['raise_title'],
                    'limit_day'=> date('Y年m月d日 H时i分',$limit_day).'('.$this->weekday($limit_day).')',
                    'wx'=>'yami194',
                );
                $this->push_Message($order['member_id'], $params,'SMS_39710001', 'sms',null, 3, $order['id'],0,0);

                //记录订单修改快照信息
                $this->SaveSnapshotLogs($order_new_id,3);
                $this->success('二次支付生成的订单成功！');
            }else{
                $this->error('二次支付生成的订单失败！');
            }
        }else{
            $this->error('该订单已生成二次支付的订单了，请勿重新生成！');
        }
    }

    //订单详情
    public function OrderDetail(){
        if(IS_AJAX){
            $order_id = I('post.id');
            $data = D('OrderDetailView')->where(['order_id' => $order_id])->select();
            //拼接图片路径
            foreach($data as $key=>$row){
                if(strpos($row['pics_path'],'uploads')>=0){
                    $data[$key]['pics_path'] = "<img src=\"".OLD_IMG_PATH.$row['pics_path']."\" width='100px' height='100px'/>";
                }else{
                    $data[$key]['pics_path'] = "<img src=\"".NEW_IMG_PATH.$row['pics_path']."\" width='100px' height='100px'/>";
                }
            }
            $this->ajaxReturn($data);
        }
    }

    //查快照
    public function snapshot(){
        $id = I('post.id');
        if(empty($id))$this->error('未选择要查看的订单');

        if(IS_AJAX){
            if($_POST['typename'] == 'TipSnapshot') {
                $data = $this->m2('OrderWares')->where(['order_id' => $id])->getField('snapshot');
                $data = json_decode($data, true);
                $data['is_book'] = $data['is_book'] ? '是' : '否';
                $data['datetime'] = date('Y-m-d H:i', $data['datetime']);
                foreach ($data['tips_menu'] as $key => $row) {
                    $data['tips_menu'][$key]['food_type'] = str_replace(['A@', 'B@', 'C@','@'], '', $row['food_type']);
                    $data['tips_menu'][$key]['food_name'] = $row['food_name'] ? $row['food_name'] : '未填写';
                }
                $data['tips_edges'] = $data['tips_edges'] ? $data['tips_edges'] : '未填写';
                $this->success($data);
            }elseif($_POST['typename'] == 'RaiseSnapshot'){
                $data = $this->m2('OrderWares')->where(['order_id' => $id])->getField('snapshot');
                $data = json_decode($data, true);
                $data['datetime'] = date('Y-m-d H:i', $data['datetime']);
                $data['raise_type'] = $data['raise_type'] ? $data['raise_type'] : '未知';
                $data['raise_introduction'] = $data['raise_introduction'] ? $data['raise_introduction'] : '未知';
                $data['raise_price'] = $data['raise_price'] ? '￥'.$data['raise_price'] : 0;
                $data['raise_prepay'] = $data['raise_prepay'] ? '￥'.$data['raise_prepay'] : 0;
                $data['raise_act_pay'] = $data['raise_act_pay'] ? '￥'.$data['raise_act_pay'] : 0;

                $this->success($data);

            }
        }else{
            $this->error('非法访问!');
        }
    }

    //验票
    public function OrderCheck()
    {
        $order_id = I('post.id');
        $rs = D('OrderView')->where(['id' => $order_id])->group('A.id')->find();
        //更新订单商品状态
        $m = $this->m2('order')->where(['id' => $order_id])->save(['act_status' => 2]);
        if ($m == 0) {
            $this->error('该订单已经验过票了');
        }
        $this->m2('OrderWares')->where(['order_id' => $order_id])->save(['server_status' => 1]);

        //验票成功，给达人余额账号打钱
        //计算优惠前的价格
//        if(!empty($rs['coupon_member_id'])){
//            switch($rs['coupon_type']){
//                case 0:
//                    $price = $rs['price']+$rs['coupon_value'];//抵价券
//                    break;
//                case 1:
//                    $price = $rs['price']/($rs['coupon_value']/100);//折扣券
//                    break;
//                case 2:
//                    $price = $rs['price'];//礼品券
//                    break;
//            }
//        }else{
//            $price = $rs['price'];
//        }
        //财富表和财富日志表更新
//        $MemberWealthId = M('MemberWealth')->where(['member_id'=>session('member.id'),'wealth'=>'1'])->getField('id');
//        M('MemberWealthLog')->data(['member_wealth_id'=>$MemberWealthId,'type'=>'shoumai','quantity'=>$price])->add();
//        M('MemberWealth')->where(['member_id'=>session('member.id'),'wealth'=>'1'])->setInc('quantity',$price);

        //延迟推送消息
//        if (in_array($rs['channel'], [7, 8, 9])) {
//            $channel = 1;
//            $context = "您参与的『{$rs['title']}』已经结束，现场气氛如何？主人手艺棒不棒？和主人互动愉快吗？快来给主人评分吧！";
//        } else {
//            $channel = 0;
//            $context = "您参与的『{$rs['title']}』已经结束，现场气氛如何？达人手艺棒不棒？和达人互动愉快吗？快来给达人评分吧！";
//        }
//        $this->pushMessage($rs['member_id'], $context, 'sms', 3, $rs['id'], $rs['end_time'] + 3600, $channel);

        //2016-12-29
        if (in_array($rs['channel'], [7, 8, 9])) {
            $channel = 1;

            $params = array(
                'title' => $rs['title']
            ,   'host_1' => '主人',
                'host_2' => '主人',
                'host_3' => '主人',
            );
//            $context = "您参与的『{$rs['title']}』已经结束，现场气氛如何？主人手艺棒不棒？和主人互动愉快吗？快来给主人评分吧！";
        } else {
            $channel = 0;
            $params = array(
                'title' => $rs['title']
            ,   'host_1' => '达人',
                'host_2' => '达人',
                'host_3' => '达人',
            );
//            $context = "您参与的『{$rs['title']}』已经结束，现场气氛如何？达人手艺棒不棒？和达人互动愉快吗？快来给达人评分吧！";
        }
        $this->push_Message($rs['member_id'], $params,'SMS_36270252', 'sms',null, 3, $rs['id'], $rs['end_time'] + 3600, $channel);
        //记录订单修改快照信息
        $this->SaveSnapshotLogs($order_id,3);
       $this->success('消费码验证成功!');

    }

    //取消订单
    public function OrderCancel(){
        $order_id = I('post.order_id');
        $rs = $this->m2('order')->where(['id'=>$order_id])->find();
        if($rs){
            //恢复库存
            $buy_num = $this->m2('OrderWares')->where(['order_id'=>$order_id])->count();
            $order_wares = $this->m2('OrderWares')->where(['order_id'=>$order_id])->field(['type','ware_id','tips_times_id'])->find();
            if($order_wares['type'] == 0){
                if($rs['is_book'])
                    $this->m2('TipsTimes')->where(['id'=>$order_wares['tips_times_id']])->setField('stock', ['exp', 'max_num']);
                else
                    $this->m2('TipsTimes')->where(['id'=>$order_wares['tips_times_id']])->setInc('stock', $buy_num);
                //记录订单修改快照信息
                $this->SaveSnapshotLogs($order_wares['ware_id'],0);
            }elseif($order_wares['type'] == 1){
                $this->m2('Goods')->where(['id'=>$order_wares['ware_id']])->setInc('stocks', $buy_num);
                //记录订单修改快照信息
                $this->SaveSnapshotLogs($order_wares['ware_id'],1);
            }elseif($order_wares['type'] == 2){
                if(empty($rs['order_pid'])){
                    $this->m2('raise_times')->where(['id'=>$order_wares['tips_times_id']])->setInc('stock', $buy_num);
                    //记录订单修改快照信息
                    $this->SaveSnapshotLogs($order_wares['ware_id'],2);
                }
            }
            $this->m2('order')->where(['id'=>$order_id])->save(['status'=>2]);
            //记录订单修改快照信息
            $this->SaveSnapshotLogs($order_id,3);
            $this->success('取消成功！');
        }else{
            $this->error('取消失败！');
        }
    }

    //删除订单
    public function OrderDelete(){
        $order_id = I('post.order_id');
        $rs = $this->m2('order')->where(['id'=>$order_id])->find();
        if($rs){
            //恢复库存
            $buy_num = $this->m2('OrderWares')->where(['order_id'=>$order_id])->count();
            $order_wares = $this->m2('OrderWares')->where(['order_id'=>$order_id])->field(['type','ware_id','tips_times_id'])->find();
            if($order_wares['type'] == 0){
                if($rs['is_book'])
                    $this->m2('TipsTimes')->where(['id'=>$order_wares['tips_times_id']])->setField('stock', ['exp', 'max_num']);
                else
                    $this->m2('TipsTimes')->where(['id'=>$order_wares['tips_times_id']])->setInc('stock', $buy_num);
                //记录订单修改快照信息
                $this->SaveSnapshotLogs($order_wares['ware_id'],0);
            }elseif($order_wares['type'] == 1){
                $this->m2('Goods')->where(['id'=>$order_wares['ware_id']])->setInc('stocks', $buy_num);
                //记录订单修改快照信息
                $this->SaveSnapshotLogs($order_wares['ware_id'],1);
            }elseif($order_wares['type'] == 2){
                if(empty($rs['order_pid'])){
                    $this->m2('raise_times')->where(['id'=>$order_wares['tips_times_id']])->setInc('stock', $buy_num);
                    //记录订单修改快照信息
                    $this->SaveSnapshotLogs($order_wares['ware_id'],2);
                }
            }
            $this->m2('order')->where(['id'=>$order_id])->save(['status'=>2,'act_status'=>11]);
            //记录订单修改快照信息
            $this->SaveSnapshotLogs($order_id,3);
            $this->success('删除成功！');
        }else{
            $this->error('删除失败！');
        }
    }

    //订单恢复
    public  function OrderRecovery(){
        $order_id = I('post.order_id');
        $rs = $this->m2('order')->where(['id'=>$order_id])->find();
        if($rs){
            //减库存
            $buy_num = $this->m2('OrderWares')->where(['order_id'=>$order_id])->count();
            $order_wares = $this->m2('OrderWares')->where(['order_id'=>$order_id])->field(['type','ware_id','tips_times_id'])->find();
            if(empty($rs['order_pid'])) {
                $limit_pay_time = time() + 600;
            }else{
                $limit_pay_time = time() + 3600*3;
            }
            if($order_wares['type'] == 0){
                $rs = $this->m2('tips_times')->where(['id'=>$order_wares['tips_times_id']])->find();
                if($rs['stop_buy_time']>time() && $rs['stock']>0){
                    if($rs['is_book'])
                        $this->m2('TipsTimes')->where(['id'=>$order_wares['tips_times_id']])->setField('stock', 0);
                    else
                        $this->m2('TipsTimes')->where(['id'=>$order_wares['tips_times_id']])->setDec('stock', $buy_num);
                    //记录活动修改快照信息
                    $this->SaveSnapshotLogs($order_wares['ware_id'],0);
                }else{
                    $this->error('该订单对应的活动已过期或者已售罄');
                }
            }elseif($order_wares['type'] == 1){
                $rs = $this->m2('goods')->where(['id'=>$order_wares['tips_times_id']])->find();
                if($rs['stocks']>0){
                    $this->m2('Goods')->where(['id'=>$order_wares['ware_id']])->setDec('stocks', $buy_num);
                    //记录商品修改快照信息
                    $this->SaveSnapshotLogs($order_wares['ware_id'],1);
                }else{
                    $this->error('该订单对应的商品已过期或者已售罄');
                }
            }elseif($order_wares['type'] == 2){
                if(empty($rs['order_pid'])){
                    $rs = $this->m2('raise')->join('__RAISE_TIMES__ AS A ON A.raise_id = __RAISE__.id')->where(['A.id'=>$order_wares['tips_times_id']])->find();
                    if($rs['end_time']>time() && $rs['stock']){
                        $this->m2('raise_times')->where(['id'=>$order_wares['tips_times_id']])->setDec('stock', $buy_num);
                        //记录众筹修改快照信息
                        $this->SaveSnapshotLogs($order_wares['ware_id'],2);
                    }else{
                        $this->error('该订单对应的众筹已过期或者已售罄');

                    }
                }
            }
            $this->m2('order')->where(['id'=>$order_id])->save(['status'=>1,'create_time'=>time(),'limit_pay_time'=>$limit_pay_time]);
            //记录订单修改快照信息
            $this->SaveSnapshotLogs($order_id,3);
            $this->success('恢复成功！');
        }else{
            $this->error('恢复失败！');
        }
    }

    //发货
    public function shipment(){
        //获取原来的物流信息
        if(IS_AJAX &&  I('post.number')==''){
            $id = I('post.id');
            $data = $this->m2('order_logistics')->where(['order_id' => $id])->find();
            $this->ajaxReturn($data);
            exit;
        }
        //新增或修改物流信息
        if(IS_POST){
            $order_id = I('post.id');
            $number = I('post.number');
            $logistics_id = I('post.logistics_id');

            $data = [];
            $data['order_id'] = $order_id;
            $data['number'] = $number;
            $data['logistics_id'] = $logistics_id;

            $ol_rs = $this->m2('order_logistics')->where(['order_id' => $order_id])->find();
            $this->m2('logistics')->where(['id' => $logistics_id])->setInc('use_times');
            if($ol_rs){
                $data['id'] = $ol_rs['id'];
                $this->m2('order_logistics')->data($data)->save();
                $this->success('发货信息已经修改');
            }else{
                $this->m2('order_logistics')->data($data)->add();
                $this->m2('order')->where('id='.$order_id)->data(['act_status'=>2])->save();

                //推送发货通知给客户
                $rs = D('OrderGoodsView')->where(['id' => $order_id])->find();
                $datetime = date('Y-m-d');
//                $context = "小主购买的“{$rs['goods_title']}”已于{$datetime}使用{$rs['logistics_name']}发货，快递单号为{$rs['order_logistics_number']}，喜欢请在收货之后点亮五颗星奖励我，评价有福利哦！";
//                $this->pushMessage($rs['member_id'], $context, 'sms', 3, $rs['id'], 0, $rs['channel']);

                //2016-12-29
                $params = array(
                    'goods_title' => $rs['goods_title'],
                    'datetime' => $datetime,
                    'logistics_name' => $rs['logistics_name'],
                    'order_logistics_number' => $rs['order_logistics_number']
                );
                $this->push_Message($rs['member_id'], $params,'SMS_36285323', 'sms',null, 3, $rs['id'], 0, $rs['channel']);

                $this->success('发货信息已添加');
            }
        }
    }

    //商品订单
    public function GoodsOrder(){
        $this->actname = '商品订单';
        $pageSize = 30;

        //条件筛选
        if(IS_GET && $_GET !=null){
            //$search_id = I('get.id');
            $search_title = I('get.title');
            $search_member = I('get.member');
            $search_start_buy_time = strtotime(I('get.start_time'));
            $search_stop_buy_time = strtotime(I('get.stop_time'));
            $search_sn = I('get.sn');
            $search_telephone = I('get.telephone');
            $search_act_status = I('get.act_status');


            $condition = array();
            $search_title && $condition['H.title'] = array('LIKE','%'.$search_title.'%');
            $search_member && $condition['B.nickname'] = array('LIKE','%'.$search_member.'%');
            $search_start_buy_time && $condition['A.create_time'] = array('EGT',$search_start_buy_time);
            $search_stop_buy_time && $condition['A.create_time'] = array('ELT',$search_stop_buy_time);
            $search_sn && $condition['A.sn'] = array('EQ',$search_sn);
            $search_telephone && $condition['B.telephone'] = array('EQ',$search_telephone);
            $search_act_status!='' && $condition['A.act_status'] = array('EQ',$search_act_status);



            $this->assign('search_title',$search_title);
            $this->assign('search_member',$search_member);
            $search_start_buy_time && $this->assign('search_start_time',date('Y-m-d H:i',$search_start_buy_time));
            $search_stop_buy_time && $this->assign('search_stop_time',date('Y-m-d H:i',$search_stop_buy_time));
            $search_sn && $this->assign('search_sn',$search_sn);
            $search_telephone && $this->assign('search_telephone',$search_telephone);
            $search_act_status!='' && $this->assign('search_act_status',$search_act_status);

        }//普通查询

        $Order_Tips_mod = D('OrderGoodsView');
        $condition['A.status'] = array('IN','1,2');
        $condition['F.type'] = array('EQ',1);
//        $condition['A.act_status'] = ['NEQ',11];
        $datas['datas'] = $Order_Tips_mod->where($condition)->page(I('get.page'), $pageSize)->order('id desc')->group('A.sn')->select();
        /*//如果找到退款记录，则加入列表
        $order_refund_mod = $this->m2('order_refund')->select();
        foreach($order_refund_mod as $key1=>$row){
            foreach($datas['datas'] as $key2=>$row2){
                if($row['order_id'] == $row2['id']){
                    $datas['datas'][$key2]['order_refund_money'] = $row['money'];
                    $datas['datas'][$key2]['order_refund_cause'] = $row['cause'];
                    $datas['datas'][$key2]['order_refund_refusal_reason'] = $row['refusal_reason'];
                }
            }
        }*/

        //如果找到相关优惠券，则加入列表
        /*$order_member_coupon_mod = $this->m2('member_coupon')->select();
        $order_coupon_mod = $this->m2('coupon')->select();
        foreach($order_member_coupon_mod as $row){
            foreach($datas['datas'] as $key=>$row2){
                if($row2['member_coupon_id'] == $row['id']){
                    $coupon_id = $row['coupon_id'];
                    foreach($order_coupon_mod as $row3){
                        if($row3['id'] == $coupon_id){
                            $datas['datas'][$key]['coupon_type'] = $row3['type'];
                            $datas['datas'][$key]['coupon_value'] = $row3['value'];
                        }
                    }
                }
            }
        }*/

        //计算优惠券的实际优惠金额和实际支付金额
        foreach($datas['datas'] as $key=>$row){
            if($row['coupon_type'] !== null){
                if($row['coupon_type'] == 0){                                           //抵价券
                    $datas['datas'][$key]['discount'] = $row['coupon_value'];
                    //$datas['datas'][$key]['act_pay'] = $row['price']-$datas['datas'][$key]['discount'];
                }elseif($row['coupon_type'] == 1){                                      //折扣券
                    $datas['datas'][$key]['discount'] = (100-$row['coupon_value'])*0.01*$row['price'];
                    // $datas['datas'][$key]['act_pay'] = $row['price']-$datas['datas'][$key]['discount'];
                }elseif($row['coupon_type'] == 2){                                      //礼品券
                    $datas['datas'][$key]['discount'] = 0;
                    //$datas['datas'][$key]['act_pay'] = $row['price'];
                }
            } else{
                $datas['datas'][$key]['discount'] = 0;                              //不使用优惠券
                //$datas['datas'][$key]['act_pay'] = $row['price'];
            }

            if($row['status'] == 0){
                $datas['datas'][$key]['status'] = '已删除';
            }elseif($row['status'] == 1){
                $datas['datas'][$key]['status'] = '正常状态';
            }elseif($row['status'] ==2){
                $datas['datas'][$key]['status'] = '已关闭';
            }
            /*$datas['datas'][$key]['tips_start_buy_time'] = date('Y-m-d H:i',$datas['datas'][$key]['tips_start_buy_time']);
             $datas['datas'][$key]['tips_stop_buy_time'] = date('Y-m-d H:i',$datas['datas'][$key]['tips_stop_buy_time']);*/
            $datas['datas'][$key]['create_time'] = date('Y-m-d H:i',$datas['datas'][$key]['create_time']);
            $datas['datas'][$key]['limit_pay_time'] = date('Y-m-d H:i',$datas['datas'][$key]['limit_pay_time']);
            if($row['act_status']==0)$datas['datas'][$key]['act_status_new'] = '未支付';
            if($row['act_status']==1)$datas['datas'][$key]['act_status_new'] = '已付未发货';
            if($row['act_status']==2)$datas['datas'][$key]['act_status_new'] = '已付已发货';
            if($row['act_status']==3)$datas['datas'][$key]['act_status_new'] = '已发货未确认';
            if($row['act_status']==4)$datas['datas'][$key]['act_status_new'] = '已发货已确认';
            if($row['act_status']==5)$datas['datas'][$key]['act_status_new'] = '申请退款';
            if($row['act_status']==6)$datas['datas'][$key]['act_status_new'] = '退款已处理';
            if($row['act_status']==7)$datas['datas'][$key]['act_status_new'] = '已取消';

            $citys = D('CityView')->where(['district_id' => $row['member_address_citys_id']])->find();
            $address = $datas['datas'][$key]['member_address_address'];
            if(!empty($citys))$address = $citys['province_name'] . $citys['province_alt'] . $citys['city_name'] . $citys['city_alt'] . $citys['district_name'] . $citys['district_alt'] . $address;
            $datas['datas'][$key]['member_address_address'] = $address;


            if($row['act_status'] !=0){
                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                if(!empty($pay_arr['success_pay_time'])){
                    $datas['datas'][$key]['success_pay_time'] =  date('Y-m-d H:i',$pay_arr['success_pay_time']);
                    if($pay_arr['type'] == 0){
                        $datas['datas'][$key]['pay_type'] =  '支付宝客户端';
                    }elseif($pay_arr['type'] == 1){
                        $datas['datas'][$key]['pay_type'] =  '微信APP';
                    }elseif($pay_arr['type'] == 2){
                        $datas['datas'][$key]['pay_type'] =  '微信公众号';
                    }elseif($pay_arr['type'] == 3){
                        $datas['datas'][$key]['pay_type'] =  '支付宝网页支付';
                    }elseif($pay_arr['type'] == 4){
                        $datas['datas'][$key]['pay_type'] =  '小程序支付';
                    }

                }else{
                    $datas['datas'][$key]['pay_type'] =  '';
                    $datas['datas'][$key]['success_pay_time'] ='';
                }
            }else{
                $datas['datas'][$key]['pay_type'] =  '';
                $datas['datas'][$key]['success_pay_time'] ='';
            }
        }

        //获取物流信息
        /*foreach($datas['datas'] as $key=>$row){
            $logistics_rs = $this->m2('order_logistics')->where('order_id='.$row['id'])->find();
            $datas['datas'][$key]['logistics_name'] = '';
            $datas['datas'][$key]['logistics_num'] = '';
            if($logistics_rs) {
                $log_rs = $this->m2('logistics')->where('id=' . $logistics_rs['logistics_id'])->find();

                $logistics_name = $log_rs['name'];
                $logistics_num = $logistics_rs['number'];
                $datas['datas'][$key]['logistics_name'] = $logistics_name;
                $datas['datas'][$key]['logistics_num'] = $logistics_num;
            }
        }*/
        //var_dump($datas['datas']);exit;

        //table页面参数设置
        $datas['operations'] = [
            '发货'=> [
                'style' => 'success',
                'fun' => 'shipment(%id)',
                'condition' => "%act_status==1"
            ],
            '修改发货信息'=> [
                'style' => 'warning',
                'fun' => 'shipment(%id)',
                'condition' => "%act_status==2"
            ],
            '取消'=> [
                'style' => 'warning',
                'fun' => 'cancel(%id)',
                'condition' => "%order_status==1 && %act_status==0",
            ],
            '删除'=> [
                'style' => 'danger',
                'fun' => 'order_delete(%id)',
                'condition' => "%act_status==0",
            ],
            '恢复'=> [
                'style' => 'success',
                'fun' => 'recovery(%id)',
                'condition' => "%order_status==2 && %act_status==0",
            ],
            '查看订单详情'=>"orderDetail(%id)"
        ];
        $datas['pages'] = [
            'sum' => $Order_Tips_mod->where($condition)->count(),
            'count' => $pageSize,
        ];

        $datas['lang'] = array(
            'id' => 'ID',
            'sn' => '订单号',
            'goods_title' =>'商品名称',
            //'category_name' => '分类',
            'member_address_linkman' => '购买人',
            'inviter_nickname' => '邀请人昵称',
            'member_telephone' => '手机号',
            //'order_wares_price' => '单价',
            'member_address_address' => '收货地址',
            'price' => '实付金额',
            'discount' => '优惠金额',
            //'act_pay' => '实际支付',
            'create_time' => '下单时间',
            'limit_pay_time' => '限制支付时间',
            'success_pay_time' => '支付时间',
            'pay_type' => '支付方式',
            'logistics_name' => '物流公司',
            'order_logistics_number' => '物流单号',
            'act_status_new' => '订单状态'
        );

        //获取物流公司列表并传值
        $logistics = $this->m2('logistics')->order('use_times desc')->select();
        $this->assign('logistics',$logistics);
        $this->assign($datas);
        $this->view();
    }

    //商品订单导出
    public function goodsOrderExport_old(){
        $search_title = I('get.title');
        $search_member = I('get.member');
        $search_start_buy_time = strtotime(I('get.start_time'));
        $search_stop_buy_time = strtotime(I('get.stop_time'));
        $search_sn = I('get.sn');
        $search_telephone = I('get.telephone');
        $search_act_status = I('get.act_status');

        $condition = array();
        $search_title && $condition['H.title'] = array('LIKE','%'.$search_title.'%');
        $search_member && $condition['B.nickname'] = array('LIKE','%'.$search_member.'%');
        $search_start_buy_time && $condition['A.create_time'] = array('EGT',$search_start_buy_time);
        $search_stop_buy_time && $condition['A.create_time'] = array('ELT',$search_stop_buy_time);
        $search_sn && $condition['A.sn'] = array('EQ',$search_sn);
        $search_telephone && $condition['B.telephone'] = array('EQ',$search_telephone);
        $search_act_status && $condition['A.act_status'] = array('EQ',$search_act_status);

        $condition['F.type'] = array('EQ',1);
        $data = D('GoodsOrderExportView')->where($condition)->limit(1000)->group('A.id')->select();

        $datas = $add_ids = $_citys = [];
        foreach($data as $row){
            $add_ids[] = $row['member_address_citys_id'];
        }

        //批量查询地址
        $rs = D('CityView')->where(['district_id' => ['IN', join(',', $add_ids)]])->select();
        foreach($rs as $row){
            $_citys[$row['district_id']] = $row;
        }

        //计算优惠券的实际优惠金额和实际支付金额
        foreach($data as $row){
            if($row['coupon_type'] !== null){
                if($row['coupon_type'] == 0){                                           //抵价券
                    $row['coupon_type'] = '抵价券';
                    $row['discount'] = $row['coupon_value'];
                }elseif($row['coupon_type'] == 1){                                      //折扣券
                    $row['coupon_type'] = '折扣券';
                    $row['discount'] = (100-$row['coupon_value'])*0.01*$row['price'];
                }elseif($row['coupon_type'] == 2){                                      //礼品券
                    $row['coupon_type'] = '礼品券';
                    $row['discount'] = 0;
                }
            }else{
                $row['discount'] = 0;
            }

            if($row['act_status'] == 0){
                $row['act_status'] = '未支付';
            }elseif($row['act_status'] == 1){
                $row['act_status'] = '已支付（进行中）';
            }elseif($row['act_status'] == 2){
                $row['act_status'] = '已支付（已完成）';
            }elseif($row['act_status'] == 3){
                $row['act_status'] = '已发货（未签收）';
            }elseif($row['act_status'] == 4){
                $row['act_status'] = '已发货（已签收）';
            }elseif($row['act_status'] == 5){
                $row['act_status'] = '申请退款';
            }elseif($row['act_status'] == 6){
                $row['act_status'] = '退款申请已处理';
            }elseif($row['act_status'] == 7){
                $row['act_status'] = '已取消';
            }elseif($row['act_status'] == 8){
                $row['act_status'] = '系统自动操作，退款中';
            }

            $row['create_time'] = date('Y-m-d H:i',$row['create_time']);
            if($row['act_status'] !=0){
                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                $row['success_pay_time'] = $pay_arr['success_pay_time'] ? date('Y-m-d H:i',$pay_arr['success_pay_time']) : '';
                $row['pay_type'] = $pay_arr['type'] !== null ? $pay_arr['type'] : null;
            }else{
                $row['success_pay_time'] ='';
                $row['pay_type'] = null;
            }
            $row['sn'] = "'" . $row['sn'];

            //地址
            $address = $row['member_address_address'];
            if(!empty($_citys[$row['member_address_citys_id']])){
                $citys = $_citys[$row['member_address_citys_id']];
                $address = $citys['province_name'] . $citys['province_alt'] . $citys['city_name'] . $citys['city_alt'] . $citys['district_name'] . $citys['district_alt'] . $address;
            }

            //购买数量
            $buy_count = $this->m2('OrderWares')->where(['order_id' => $row['id']])->count();

            //支付类型
            $pay_type = '';
            if($row['pay_type'] !== null){
                $pay_type = ['支付宝','微信APP','微信公众号'][$row['pay_type']];
            }
            //支付渠道
            if(in_array($row['channel'], [7,8,9])){
                $channel = '我有饭';
            }elseif(in_array($row['channel'], [0,1,2])){
                $channel = '吖咪';
            }else{
                $channel = '第三方';
            }

            $datas[] = [
                $row['id'], $row['sn'], $row['goods_title'], $row['category_name'], $row['order_logistics_number'], $row['logistics_name'],
                $address, $row['member_address_linkman'], $row['member_address_telephone'], $buy_count, $row['goods_price'],
                $row['discount'], $row['coupon_type'], $row['member_coupon_sn'], $row['order_refund_money'], $row['order_refund_cause'],
                $row['price'], $row['create_time'], $pay_type, $channel, $row['act_status'], $row['context']
            ];
        }

        //$title = 'ID,订单号,价格,订单状态,留言,下单时间,联系电话,购买人,优惠券码,优惠券类型,优惠券值,物流订单号,商品单价,物流公司,商品名称,收货地址,退款理由,退款金额,分类,共计优惠金额';
        $title = ['订单ID', '订单号', '商品标题', '分类', '物流订单号', '物流公司', '收货地址', '购买人', '联系电话', '数量', '商品单价', '共计优惠金额', '优惠券类型', '优惠券码', '退款金额', '退款理由', '实付金额', '下单时间', '支付时间', '支付类型', '支付渠道', '订单状态', '留言'];

        toXls($title, $datas, '商品订单');
    }

    //商品订单导出(2017-03-09)
    public function goodsOrderExport(){
        $search_title = I('get.title');
        $search_member = I('get.member');
        $search_start_buy_time = strtotime(I('get.start_time'));
        $search_stop_buy_time = strtotime(I('get.stop_time'));
        $search_sn = I('get.sn');
        $search_telephone = I('get.telephone');
        $search_act_status = I('get.act_status');

        $condition = array();
        $search_title && $condition['H.title'] = array('LIKE','%'.$search_title.'%');
        $search_member && $condition['B.nickname'] = array('LIKE','%'.$search_member.'%');
        $search_start_buy_time && $condition['A.create_time'] = array('EGT',$search_start_buy_time);
        $search_stop_buy_time && $condition['A.create_time'] = array('ELT',$search_stop_buy_time);
        $search_sn && $condition['A.sn'] = array('EQ',$search_sn);
        $search_telephone && $condition['B.telephone'] = array('EQ',$search_telephone);
        $search_act_status && $condition['A.act_status'] = array('EQ',$search_act_status);

        $condition['F.type'] = array('EQ',1);
        $Order_Goods_mod = D('GoodsOrderExportView');
        $orderArr = $Order_Goods_mod->where($condition)->order('id desc')->group('A.id')->limit(1000)->getField('id' ,true);
//        $sql = 'select * from (select * from  `ym_snapshot_logs` order by datetime desc ) AS  a  WHERE `type` = 3 AND `type_id` IN ('.join(',',$orderArr).') group by type_id order by id desc';

        $sql = 'select * from `ym_snapshot_logs` where id in(select SUBSTRING_INDEX(group_concat(id order by `datetime` desc),",",1) from `ym_snapshot_logs`  WHERE `type` = 3 AND `type_id` IN ('.join(',',$orderArr).') group by type_id ) order by `datetime` desc';
        $order_Arr = $this->m2()->query($sql);
//        print_r($order_Arr);
//        exit;
        $datas = $add_ids = $_citys = [];
        foreach($order_Arr as $row){
            $add_ids[] = $row['member_address_citys_id'];
        }

        //批量查询地址
        $rs = D('CityView')->where(['district_id' => ['IN', join(',', $add_ids)]])->select();
        foreach($rs as $row){
            $_citys[$row['district_id']] = $row;
        }

        //计算优惠券的实际优惠金额和实际支付金额
        foreach($datas as $row){
            if($row['coupon_type'] !== null){
                if($row['coupon_type'] == 0){                                           //抵价券
                    $row['coupon_type'] = '抵价券';
                    $row['discount'] = $row['coupon_value'];
                }elseif($row['coupon_type'] == 1){                                      //折扣券
                    $row['coupon_type'] = '折扣券';
                    $row['discount'] = (100-$row['coupon_value'])*0.01*$row['price'];
                }elseif($row['coupon_type'] == 2){                                      //礼品券
                    $row['coupon_type'] = '礼品券';
                    $row['discount'] = 0;
                }
            }else{
                $row['discount'] = 0;
            }

            if($row['act_status'] == 0){
                $row['act_status'] = '未支付';
            }elseif($row['act_status'] == 1){
                $row['act_status'] = '已支付（进行中）';
            }elseif($row['act_status'] == 2){
                $row['act_status'] = '已支付（已完成）';
            }elseif($row['act_status'] == 3){
                $row['act_status'] = '已发货（未签收）';
            }elseif($row['act_status'] == 4){
                $row['act_status'] = '已发货（已签收）';
            }elseif($row['act_status'] == 5){
                $row['act_status'] = '申请退款';
            }elseif($row['act_status'] == 6){
                $row['act_status'] = '退款申请已处理';
            }elseif($row['act_status'] == 7){
                $row['act_status'] = '已取消';
            }elseif($row['act_status'] == 8){
                $row['act_status'] = '系统自动操作，退款中';
            }

            $row['create_time'] = date('Y-m-d H:i',$row['create_time']);
            if($row['act_status'] !=0){
                $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                $row['success_pay_time'] = $pay_arr['success_pay_time'] ? date('Y-m-d H:i',$pay_arr['success_pay_time']) : '';
                $row['pay_type'] = $pay_arr['type'] !== null ? $pay_arr['type'] : null;
            }else{
                $row['success_pay_time'] ='';
                $row['pay_type'] = null;
            }
            $row['sn'] = "'" . $row['sn'];

            //地址
            $address = $row['member_address_address'];
            if(!empty($_citys[$row['member_address_citys_id']])){
                $citys = $_citys[$row['member_address_citys_id']];
                $address = $citys['province_name'] . $citys['province_alt'] . $citys['city_name'] . $citys['city_alt'] . $citys['district_name'] . $citys['district_alt'] . $address;
            }

            //购买数量
            $buy_count = $this->m2('OrderWares')->where(['order_id' => $row['id']])->count();

            //支付类型
            $pay_type = '';
            if($row['pay_type'] !== null){
                $pay_type = ['支付宝','微信APP','微信公众号'][$row['pay_type']];
            }
            //支付渠道
            if(in_array($row['channel'], [7,8,9])){
                $channel = '我有饭';
            }elseif(in_array($row['channel'], [0,1,2])){
                $channel = '吖咪';
            }else{
                $channel = '第三方';
            }

            $datas[] = [
                $row['id'], $row['sn'], $row['goods_title'], $row['category_name'], $row['order_logistics_number'], $row['logistics_name'],
                $address, $row['member_address_linkman'], $row['member_address_telephone'], $buy_count, $row['goods_price'],
                $row['discount'], $row['coupon_type'], $row['member_coupon_sn'], $row['order_refund_money'], $row['order_refund_cause'],
                $row['price'], $row['create_time'], $pay_type, $channel, $row['act_status'], $row['context']
            ];
        }

        //$title = 'ID,订单号,价格,订单状态,留言,下单时间,联系电话,购买人,优惠券码,优惠券类型,优惠券值,物流订单号,商品单价,物流公司,商品名称,收货地址,退款理由,退款金额,分类,共计优惠金额';
        $title = ['订单ID', '订单号', '商品标题', '分类', '物流订单号', '物流公司', '收货地址', '购买人', '联系电话', '数量', '商品单价', '共计优惠金额', '优惠券类型', '优惠券码', '退款金额', '退款理由', '实付金额', '下单时间', '支付时间', '支付类型', '支付渠道', '订单状态', '留言'];

        toXls($title, $datas, '商品订单');
    }

    //特权二次订单生成
    public function privilege_order(){
        $order_arr = trim($_POST['order_arr']);
        $limit_day = strtotime( I('post.limit_day').':00');
        $order_ids = join(',',array_filter(explode("\n", $order_arr)));
        $valid_orderid = $this->m2('member_privilege')->where(['order_id'=>['IN',$order_ids],'type'=>2])->getField('order_id',true);
        if(empty($valid_orderid))$this->error('这里没有特权订单');
        $orders = D('OrderRaisePayView')->where(['id'=>['IN',join(',',$valid_orderid),'act_status'=>['IN',['1,2,3,4'],'type'=>2]]])->select();
        $i = 0;
        $id_str = '';
        foreach($orders as $key=>$row){
            $count = $this->m2('order')->where(['order_pid'=>$row['id']])->count();
            if($count == 0){
                if($row['raise_times_prepay']>0){
                    $order_code = createCode(18);
                    $order_new_id = $this->m2('order')->add([
                        'sn' => $order_code,
                        'member_id' => $row['member_id'],
                        'member_address_id' => $row['member_address_id'],
                        'price' =>  (float)$row['raise_times_price']-(float)$row['raise_times_prepay'],
                        'act_status' => 0,
                        'create_time' => time(),
                        'limit_pay_time' => $limit_day,
                        'context' => $row['context'],
                        'channel' => $row['channel'],
                        'status' => 1,
                        'order_pid' => $row['id']
                    ]);
                    //快照数据
                    $snapshot = [
                        'raise_id'=>$row['ware_id'],
                        'raise_times_id'=>$row['tips_times_id'],
                        'raise_title'=>$row['raise_title'],
                        'raise_content'=>$row['content'],
                        'raise_category'=>$row['catname'],
                        'raise_introduction'=>$row['introduction'],
                        'raise_total'=>$row['total'],
                        'raise_price'=>$row['raise_times_price'],
                        'raise_prepay'=>$row['raise_times_prepay'],
                        'raise_type'=>$row['raise_times_prepay']>0 ? '预付方式' : '全额方式',
                        'raise_act_pay'=>$row['raise_times_price']- $row['raise_times_prepay'],
                        'datetime'=>time(),
                    ];

                    $code = createCode(8);
                    $result = $this->m2('order_wares')->add([
                        'order_id' => $order_new_id,
                        'type' => 2,
                        'ware_id' => $row['ware_id'],
                        'price' => (float)$row['raise_times_price']-(float)$row['raise_times_prepay'],
                        'check_code' => $code,
                        'tips_times_id' => $row['tips_times_id'],
                        'snapshot' => json_encode($snapshot)
                    ]);

                    if($result>0 && $order_new_id>0){

                        $params = array(
                            'project_name'=>'众筹',
                            'title' =>$row['raise_title'],
                            'limit_day'=> date('Y年m月d日 H时i分',$limit_day).'('.$this->weekday($limit_day).')',
                            'wx'=>'yami194',
                        );
                        $this->push_Message($row['member_id'], $params,'SMS_39710001', 'sms',null, 3, $row['id'],0,0);

                        //记录订单修改快照信息
                        $this->SaveSnapshotLogs($order_new_id,3);
                        $i++;
                        $id_str .= $row['id'].',';
                    }else{
                        continue;
                    }
                }else{
                    continue;
                }
            }else{
                continue;
            }
        }
        $this->success('生成订单的ID有：'.$id_str.',一共有'.$i.'条');
    }
}