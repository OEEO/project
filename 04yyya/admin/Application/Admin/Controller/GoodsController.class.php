<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class GoodsController extends MainController{
    Protected $pagename = '商品管理';

    public function index(){
        $this->actname = '商品列表';
        $pageSize = 40;

        //模糊查询
        $title = I('get.title');
        $member_nickname = I('get.member');
        $status = (int)I('get.status', 1);

        if(!empty($title))$condition['title'] = array('LIKE','%'.$title.'%');
        if(!empty($member_nickname))$condition['D.nickname'] = array('LIKE','%'.$member_nickname.'%');
        switch($status){
            case 2:
                $condition['status'] = 2;
                $condition['is_pass'] = 1;
                break;
            case 3:
                $condition['status'] = ['IN', '1,2'];
                $condition['is_pass'] = 0;
                break;
            case 4:
                $condition['status'] = 3;
                break;
            default:
                $condition['status'] = 1;
                $condition['is_pass'] = 1;
                break;
        }
        $this->assign('search_title',$title);
        $this->assign('search_member',$member_nickname);

        $datas['datas'] = D('GoodsListView')->where($condition)->page(I('get.page'), $pageSize)->order('id desc')->select();
        //echo D('GoodsListView')->_sql();exit;
        //数据处理
        foreach($datas['datas'] as $key=>$row){
            if($row['is_pass'] == 0)$datas['datas'][$key]['re_is_pass'] = '未审核';
            if($row['is_pass'] == 1)$datas['datas'][$key]['re_is_pass'] = '已审核<br>已通过';
            if($row['is_pass'] == 2)$datas['datas'][$key]['re_is_pass'] = '已审核<br>未通过';
        }
        if(!empty($datas['datas'])){
            //数据处理--抢购显示方式 && 专题显示
            foreach($datas['datas'] as $rs){
                $id_arr[] = $rs['id'];
            }
            $ids = join(',',$id_arr);
            $market_rs = $this->m2('marketing')->field('max(id),type_id,num,allow_coupon,start_time,end_time,price')->where('type=1 and type_id in ('.$ids.')')->group('id')->select();
            $theme_rs = $this->m2('theme_element a')->field('a.type_id,a.theme_id,b.title')->join('__THEME__ b ON a.theme_id = b.id')->where('a.type=1 and a.type_id in ('.$ids.')')->select();
            $goodsTag_rs = $this->m2('goods_tag a')->field('a.goods_id,b.name')->join('__TAG__ b ON a.tag_id = b.id')->where('a.goods_id in ('.$ids.')')->select();
            foreach($datas['datas'] as $key=>$row){
                /*$r_id = $row['id'];
                $marketing_result = $this->m2('marketing')->where('type=1 and type_id='.$r_id)->order('id desc')->find();
                $theme_result = $this->m2('theme_tipsorgoods')->where('type=1 and type_id='.$r_id)->order('id desc')->find();
                if(!empty($marketing_result)){
                    $allow_coupon = $marketing_result['allow_coupon']?'否':'是';
                    if(time()>$marketing_result['end_time']){
                        $datas['datas'][$key]['marketing'] = date('Y-m-d H:i',$marketing_result['start_time']).'--'.date('Y年m月d日 H:i',$marketing_result['end_time']).'(已结束)'."<br/>折扣：{$marketing_result['discount']}<br/>优惠券：".$allow_coupon.'<br/>限购：'.$marketing_result['limit'];
                        $datas['datas'][$key]['marketing_status'] = 0;
                    }else{
                        $datas['datas'][$key]['marketing'] = date('Y-m-d H:i',$marketing_result['start_time']).'--'.date('Y年m月d日 H:i',$marketing_result['end_time'])."<br/>折扣：{$marketing_result['discount']}<br/>优惠券：".$allow_coupon.'<br/>限购：'.$marketing_result['limit'];
                        $datas['datas'][$key]['marketing_status'] = 1;
                    }
                }else{
                    $datas['datas'][$key]['marketing'] = '无抢购';
                    $datas['datas'][$key]['marketing_status'] = 0;
                }*/

                foreach($market_rs as $m_key=>$m_row){
                    $allow_coupon = $m_row['allow_coupon']?'否':'是';
                    if($m_row['type_id'] == $row['id']){
                        if($m_row['end_time']<time()){
                            $datas['datas'][$key]['marketing'] =  '开始：'.date('Y-m-d H:i',$m_row['start_time']).'<br/>结束：'.date('Y-m-d H:i',$m_row['end_time']).'(已结束)'."<br/>折扣价：{$m_row['price']}<br/>优惠券：".$allow_coupon.'<br/>限购：'.$m_row['num'];
                            $datas['datas'][$key]['marketing_status'] = 0;
                            break;
                        }else{
                            $datas['datas'][$key]['marketing'] = '开始：'.date('Y-m-d H:i',$m_row['start_time']).'<br/>结束：'.date('Y-m-d H:i',$m_row['end_time'])."<br/>折扣价：{$m_row['price']}<br/>优惠券：".$allow_coupon.'<br/>限购：'.$m_row['num'];
                            $datas['datas'][$key]['marketing_status'] = 1;
                            break;
                        }
                    }else{
                        $datas['datas'][$key]['marketing'] = '无抢购';
                        $datas['datas'][$key]['marketing_status'] = 0;
                    }
                }

                /*$theme_id =$theme_result['theme_id'];
                //$datas['datas'][$key]['theme']  = '无';
                if($theme_id!=''){
                    $theme_rs = $this->m2('theme')->where('id='.$theme_id)->find();
                    $datas['datas'][$key]['theme'] = $theme_rs['title'];
                }else{
                    $datas['datas'][$key]['theme'] = '无';
                }*/

                foreach($theme_rs as $t_key=>$t_row){
                    if($t_row['type_id'] == $row['id']){
                        $datas['datas'][$key]['theme'] = $t_row['title'];
                    }else{
                        $datas['datas'][$key]['theme'] = '无';
                    }
                }
                // 标签查找拼接
                /*$tag_rs = $this->m2('goods_tag')->where('goods_id='.$row['id'])->select();
                if($tag_rs){
                    foreach($tag_rs as $tag_key=>$tag_row){
                        $tag_name =  $this->m2('tag')->where('id='.$tag_row['tag_id'])->find();
                        $datas['datas'][$key]['tag_name'] .= $tag_name['name'].'，';
                    }
                }*/
                foreach($goodsTag_rs as $g_key=>$g_row){
                    if($g_row['goods_id'] == $row['id']){
                        $datas['datas'][$key]['tag_name'] .=$g_row['name'].'，';
                    }
                }

                //是否有拼团
                $piece = $this->m2('GoodsPiece')->where(['goods_id' => $row['id'], 'status' => 1])->count();
                $datas['datas'][$key]['isPiece'] = $piece > 0 ? 1 : 0;
            }
        }

        $datas['operations'] = [
            '加入拼团' => [
                'style' => 'success',
                'fun' => 'addPiece(%id)',
                'condition' => '%status == 1 && %isPiece === 0'
            ],
            '修改拼团' => [
                'style' => 'warning',
                'fun' => 'modifyPiece(%id)',
                'condition' => '%status == 1 && %isPiece === 1'
            ],
            '下架' => [
                'style' => 'danger',
                'fun' => 'offline(%id)',
                'condition' => '%status == 1 and %is_pass == 1'
            ],
            '上架' => [
                'style' => 'success',
                'fun' => 'online(%id)',
                'condition' => '%status == 2 and %is_pass == 1'
            ],
//            '设置限时折扣' => array(
//                'style' => 'success',
//                'fun' => 'addMarketing(%id,%status)',
//                'condition' => '%marketing_status == 0'
//            ),
//            '取消限时折扣' => array(
//                'style' => 'danger',
//                'fun' => 'removeMarketing(%id)',
//                'condition' => '%marketing_status == 1'
//            ),
//            '取消通过' => array(
//                'style' => 'danger',
//                'fun' => 'pass(%id,2)',
//                'condition' => '%is_pass == 1 and %status == 2'
//            ),
            '确认通过' => [
                'style' => 'success',
                'fun' => 'pass(%id,1)',
                'condition' => '%is_pass != 1 and %status == 2'
            ],
            '提交审核' => [
                'style' => 'success',
                'fun' => 'goods_submit(%id)',
                'condition' => '%status == 3'
            ],
            '修改' => "modify(%id)",
            //'查看评论' => "showComment(%id)",
            '修改标签' => "goods_tags(%id)",
            '历史促销' => "marketingHistory(%id)",
            '修改记录' => 'showLogs(%id)',
            '复制商品' => 'copyGoods(%id)',
            '删除' => [
                'condition' => "%is_pass==0",
                'style' => 'danger',
                'fun' => "dataDelete(%id)"
            ]
        ];

        $datas['pages'] = array(
            'sum' => D('GoodsListView')->where($condition)->count(),
            'count' => $pageSize
        );
        $datas['batch'] = array(
            '批量加入专题' => "joinTheme()",
            '批量移除专题' => "outTheme()"
        );
        $datas['lang'] = [
            'id' => '商品ID',
            'goods_id' => ['预览', '<a><i class="am-icon-eye" onclick="preview(%*%)"></i></a>'],
            'title' => '商品名称',
            'category_name' => '分类',
            'member_nickname' => '发起用户',
//            'pics_path' => '图片',
            'price' => '价格',
            'stocks' => '库存',
//            'marketing' => '限时折扣',
//            'theme' => '专题',
//            'tag_name' => '标签',
            're_is_pass' => '状态',
            //'isPiece' => ['是否拼团', '<script>document.write(["否","是"][%*%])</script>']
        ];
        //获取专题列表
        $datas['themes'] = $this->m2('Theme')->order('sort desc,id desc')->select();
        $this->assign($datas);
        $this->view();
    }

    //删除商品
    Public function delete(){
        if(IS_AJAX){
            $data['id'] = I('post.id');
            $data['status'] = 0;
            $this->m2('goods')->save($data);
            $this->success('删除成功！');
            exit;
        }
        $this->error('非法访问！');
    }

    //验证
    public function verify(){
        if(IS_AJAX){
            $id = I('post.id');
            $oper = I('post.oper', 1);
            $data['id'] = $id;
            $data['is_pass'] = $oper;
            if($oper == 0){
                $data['status'] = 3;
            }else{
                $data['status'] = 1;
            }
            $this->m2('goods')->data($data)->save();
            $this->success('成功');
        }else{
            $this->error('非法访问');
        }
    }

    //商品上架
    public function online(){
        $data = array();
        $data['id'] = I('post.id');
        $data['status'] = 1;
        $this->m2('goods')->data($data)->save();
        $this->success('上架成功');
    }

    //商品下架
    Public function offline(){
        $data = array();
        $data['id'] = I('post.id');
        $data['status'] = 2;
        $this->m2('goods')->data($data)->save();

        //取消相关促销
        $rs = $this->m2('marketing')->field('id,end_time')->where('type=1 and type_id='.I('post.id'))->group('id desc')->find();
        if($rs['end_time']>time()){
            //删除专题
            $this->m2('theme_element')->where('type=1 and type_id='.I('post.id'))->delete();
            //更改marketing的结束时间
            $data = array();
            $data['end_time'] = time();
            $data['id'] = $rs['id'];
            $this->m2('marketing')->data($data)->save();
        }

        $this->success('下架成功');
    }

    //商品添加
    public function add(){
        $this->actname = '商品添加';
        //获取经营的城市列表
        $citys = C('CITY_CONFIG');
        if(IS_AJAX && IS_POST){
            if(!isset($_POST['submit']) && !empty($_POST['member_id'])) {
                //选择达人并创建新商品
                $data = [
                    'member_id' => I('post.member_id'),
                    'title' => null,
                    'category_id' => null,
                    'price' => 0,
                    'content' => null,
                    'stocks' => 0,
                    'pics_group_id' => null,
                    'is_pass' => 0,
                    'status' => 3
                ];
                $id = $this->m2('goods')->add($data);
                $data['goods_id'] = $id;
                $this->m2('goods_sub')->add($data);
                echo $id;
                exit;
            }elseif(isset($_POST['submit']) && $_POST['submit'] == 1){
                //提交审核
                $this->submit();
            }elseif(isset($_POST['submit']) && $_POST['submit'] == 0){
                //保存并预览
                $this->save();
            }
            $this->error('非法提交');
            exit;
        }
        //获取商品分类
        $categorys = $this->m2('category')->field(['id', 'name'])->where(['type' => 1])->order(['order'])->select();
        //获取商品标签
        $tags = $this->m2('tag')->field(['id', 'name'])->where(['type' => 2, 'official' => 0])->select();
        //须知列表
        $notices = $this->m2('GoodsNotice')->field(['id', 'context', 'status'])->where(['status' => ['IN', '1,2']])->select();
        $this->assign([
            'categorys' => $categorys,
            'tags' => $tags,
            'notices' => $notices
        ]);
        $this->view();
    }

    public function modify(){
        $this->actname = '活动修改';
        //获取经营的城市列表
        $citys = C('CITY_CONFIG');
        if(IS_AJAX && IS_POST){
            if(isset($_POST['submit']) && $_POST['submit'] == 1){
                //提交审核
                $this->submit();
            }elseif(isset($_POST['submit']) && $_POST['submit'] == 0){
                //保存并预览
                $this->save();
            }
            $this->error('非法提交');
            exit;
        }
        $goods_id = I('get.goods_id', null);
        $rs = D('GoodsEditView')->where(['id' => $goods_id])->find();

        if(empty($rs)){
            $this->error('要修改的活动不存在!');
        }

        $pics = $this->m2('pics')->field(['id', 'path'])->where(['group_id' => $rs['pics_group_id']])->select();
        foreach($pics as $k => $v){
            $pics[$k]['path'] = thumb($v['path'], 1);
        }
        $rs['pics_group'] = $pics;

        $rs['tags'] = D('TagView')->where(['goods_id' => $goods_id, 'type' => 2, 'official' => 0])->getField('id', true);

        //获取商品分类
        $categorys = $this->m2('category')->field(['id', 'name'])->where(['type' => 1])->order(['order'])->select();
        //获取商品标签
        $tags = $this->m2('tag')->field(['id', 'name'])->where(['type' => 2, 'official' => 0])->select();
        //亮点
        $rs['edge'] = explode('[^|^]', $rs['edge']);

        //规格属性
        $attr = $this->m2('GoodsAttr')->field(['id', 'name', 'value'])->where(['goods_id' => $goods_id])->select();
        $rs['attr'] = ['type' => substr($attr[0]['name'], 0, 1)];
        foreach($attr as $ar){
            if(empty($rs['attr'][substr($ar['name'], 2)]))
                $rs['attr'][] = [
                    'name' => substr($ar['name'], 2),
                    'value' => $ar['value']
                ];
        }

        //须知列表
        $notices = $this->m2('GoodsNotice')->field(['id', 'context', 'status'])->where(['status' => ['IN', '1,2']])->select();
        $data = [
            'data' => $rs,
            'categorys' => $categorys,
            'tags' => $tags,
            'notices' => $notices
        ];
        //dump($data);exit;

        $this->assign($data);
        $this->view();
    }

    //模糊查找达人
    public function getDaren(){
        if(IS_AJAX){
            $search_key = I('post.search_key');

            if(isset($search_key)&&$search_key!=''){
                $condition = 'nickname LIKE '."'%$search_key%'";
                $member_rs = D('DarenInfoView')->field('id,nickname,telephone,sex,path')->where($condition)->limit(20)->select();
                $this->ajaxReturn($member_rs);
            }
        }
    }

    //提交审核
    private function submit(){
        $goods_id = I('post.goods_id');
        $rs = D('GoodsEditView')->where(['id' => $goods_id])->find();
        if(empty($rs)){
            $this->error('非法提交!');
        }
        //获取标签
        $tags = $this->m2('tag')->join('__GOODS_TAG__ on tag_id=__TAG__.id')->where(['goods_id' => $goods_id])->getField('tag_id', true) ?: [];

        //获取属性
        $attrs = $this->m2('GoodsAttr')->where(['goods_id' => $goods_id])->select();

        if(empty($rs['title']))$this->error('商品标题不能为空！');
        if(empty($rs['price']))$this->error('商品价格不能为空！');
        if(!is_numeric($rs['category_id']))$this->error('商品分类不能为空！');
        if(empty($tags))$this->error('商品标签不能为空！');
        if(empty($attrs))$this->error('规格属性不能为空！');
        if(empty($rs['pic_id']) || empty($rs['pics_group_id']))$this->error('商品主图不能为空！');
        if(empty($rs['content']))$this->error('商品描述不能为空！');

        $this->m2('Goods')->save(['id' => $goods_id, 'status' => 2, 'is_pass' => 0]);
        session('EditingTipsID', null);
        $this->success('发布成功！等待审核……');
    }

    //提交编辑内容
    private function save(){
        $goods_id = I('post.goods_id');
        if(session('?copyGoods')){
            $str = session('copyGoods');
            session('copyGoods', null);
            $arr = explode('to', $str);
            if($arr[0] == $goods_id){
                $pics_group_id = $this->m2('GoodsSub')->where(['goods_id' => $goods_id])->getField('pics_group_id');
                $goods_id = $arr[1];
            }
        }
        $member_id = I('post.member_id');
        $rs = $this->m2('Goods')->where(['id' => $goods_id, 'member_id' => $member_id])->find();

        if(empty($rs)){
            $this->error('非法提交!');
        }
        $data = ['member_id' => $member_id];
        $data['category_id'] = I('post.category_id');
        $data['title'] = I('post.title');
        $data['shipping'] = I('post.shipping', 0);
        $data['edge'] = join('[^|^]', $_POST['edge']);
        $data['pic_id'] = I('post.pic_id');
        $data['stocks'] = I('post.stocks');
        //上线的商品不能修改 价格
        if($rs['status'] != 1 || $rs['is_pass'] != 1){
            $data['price'] = I('post.price');
        }
        $data['content'] = preg_replace(['/<script.*?>.*?<\/script>/', '/<iframe.*?>.*?<\/iframe>/', '/<iframe.*?\/>/', '/<img(.*?)>/'], ['', '', '', '[img$1/]'], $_POST['content']);
        $data['notice'] = I('post.notice');
        $data['is_public'] = I('post.is_public', 1);
        if(I('post.tags_id')){
            //商品标签
            $tag_ids = explode(',', I('post.tags_id'));
            //删除旧标签
            $sql = $this->m2('Tag')->field(['id'])->where(['official' => 0, 'type' => 2])->buildSql();
            $this->m2('GoodsTag')->where(['goods_id' => $goods_id, 'tag_id' => ['EXP', "in {$sql}"]])->delete();
            //添加新标签
            $tags = [];
            foreach($tag_ids as $id){
                $tags[] = ['goods_id' => $goods_id, 'tag_id' => $id];
            }
            $this->m2('GoodsTag')->addAll($tags);
        }

        if(!empty($pics_group_id)){
            $data['pics_group_id'] = $pics_group_id;
        }elseif(I('post.group_pic_ids')){
            //商品图组
            $pic_ids = I('post.group_pic_ids');
            if(!empty($rs['pics_group_id'])){
                //删除旧图组
                $this->m2('pics')->where(['group_id' => $rs['pics_group_id']])->delete();
                $group_id = $rs['pics_group_id'];
            }else{
                $group_id = $this->m2('PicsGroup')->add(['type' => 1]);
            }
            //修改上传来的图片
            $this->m2('pics')->where(['id' => ['IN', $pic_ids]])->save(['group_id' => $group_id]);
            $data['pics_group_id'] = $group_id;
        }

        //商品温馨提示
//        if(isset($_POST['notice']) && count($_POST['notice']) > 0){
//            $no_ids = $this->m2('GoodsNotice')->where(['id' => ['IN', $rs['notices']]])->getField('id', true);
//            $notices = [];
//            foreach($_POST['notice'] as $val){
//                if(in_array($val, $no_ids)){
//                    $notices[] = $val;
//                }elseif(is_string($val)){
//                    $notices[] = $this->m2('GoodsNotice')->add([
//                        'context' => $val,
//                        'status' => 2
//                    ]);
//                }
//            }
//            $data['notices'] = $notices;
//        }

        //商品规格属性
        $this->m2('GoodsAttr')->where(['goods_id' => $goods_id])->delete();
        if($attrs = I('post.attrs')){
            //解析提交的字符串
            $arr = explode('|', $attrs);
            foreach($arr as $ar){
                $_arr = explode(':', $ar);
                $_name = explode('@', $_arr[0]);
                $type = $_name[0];
                $name = $_name[1];
                $val = trim(str_replace([' ', ',,', '[_maohao_]', '[_aite_]'], ['', ',', ':', '@'], $_arr[1]), ',');
                if(strpos($name, '-') === false){
                    $this->m2('GoodsAttr')->add([
                        'goods_id' => $goods_id,
                        'type' => 2,
                        'name' => $type . '@' . $name,
                        'value' => $val
                    ]);
                }else{
                    $_ar = explode('-', $name);
                    $this->m2('GoodsAttr')->where(['id' => $_ar[0]])->save([
                        'name' => $type . '@' . $_ar[1],
                        'value' => $val
                    ]);
                }
            }
        }

        //统一修改
        $_data = [];
        foreach($data as $key => $row){
            if($row === 0 || $row === '0' || !empty($row)){
                $_data[$key] = $row;
            }
        }
        if(!empty($_data)){
            $this->m2('goods')->where(['id' => $goods_id])->save($_data);
            $_data['last_update_time'] = time();
            $this->m2('GoodsSub')->where(['goods_id' => $goods_id])->save($_data);
            $this->success($goods_id);
        }
    }

    //抢购
    public function Marketing(){

        $id = I('post.id');
        $oper = I('post.status');

        //删除抢购
        if($oper == 0){
            $data = array();
            //修改终止时间
            $rs = $this->m2('marketing')->where('type=1 and type_id='.$id)->order('id desc')->find();
            $data['id'] = $rs['id'];
            $data['end_time'] = time();
            $this->m2('marketing')->data($data)->save();
            //删除专题
            $this->m2('theme_element')->where('type=1 and type_id='.$id)->delete();
            $this->success('已取消');
        }
        //添加抢购
        if($oper == 1){
            $type = 1;
            $price = I('post.price');
            $start_time = strtotime(I('post.start_time'));
            $end_time = strtotime(I('post.end_time'));
            $title = I('post.title');
            $allow_coupon = I('post.allow_coupon');
            $limit = I('post.limit');
            //添加marketing表
            $data['type'] = $type;
            $data['type_id'] = $id;
            $data['price'] = $price;
            $data['start_time'] = $start_time;
            $data['end_time'] = $end_time;
            $data['title'] = $title;
            $data['allow_coupon'] = $allow_coupon;
            $data['num'] = $limit;
            $this->m2('marketing')->data($data)->add();
            //添加theme_tipsorgoods表
            $data = array();
            $data['theme_id'] = 1;
            $data['type'] = 1;
            $data['type_id'] = $id;
            $this->m2('theme_element')->data($data)->add();

            $this->success('添加成功');
        }
        //查询商品原价格
        if($oper == 2){
            $tips_rs = $this->m2('goods')->where('id='.$id)->find();
            $price = $tips_rs['price'];
            $this->ajaxReturn($price);
        }
        //查看历史促销
        if($oper == 3){
            $data = $this->m2('marketing')->field('title,price,start_time,end_time,num,allow_coupon')->where('type=1 and type_id='.$id)->order('id desc')->select();
            //数据处理
            foreach($data as $key=>$row){
                $data[$key]['start_time'] = date('Y-m-d H:i',$row['start_time']);
                $data[$key]['end_time'] = date('Y-m-d H:i',$row['end_time']);
                if($row['num'] == 0)$data[$key]['num'] = '不限';
                $data[$key]['allow_coupon'] = $row['allow_coupon']==0?'拒绝':'允许';
            }
            $this->ajaxReturn($data);
        }
    }

    //选择用户
    public function getUser(){
        if(IS_AJAX){
            $search_key = I('post.search_key');
            $oper = I('post.oper');
            if($oper == 1){
                if(isset($search_key)&&$search_key!=null){
                    $condition = ' nickname LIKE '."'%$search_key%'";
                    $member_rs = $this->m2('member')->field('id,nickname')->where($condition)->limit(20)->select();
                    $this->ajaxReturn($member_rs);
                }
            }
            if($oper == 2){
                if(isset($search_key)&&$search_key!=null){
                    $rs = $this->m2('member')->field('nickname')->where('id='.$search_key)->find();
                    $this->ajaxReturn($rs['nickname']);
                }
            }
        }
    }

    public function joinTheme(){
        $goods_ids = I('post.goods_ids');
//        dump($goods_ids);
//        exit;
        foreach($goods_ids as $row)
        {
            $data['theme_id'] = I('post.id');
            $data['type'] = 1;
            $data['type_id'] =$row;
            $data['sort'] = 0;

            $result = $this->m2('theme_element')->where('type_id='.$row)->find();
            if($result){
                $data['id'] = $result['id'];
                $this->m2('theme_element')->data($data)->save();
            }else{
                $theme_element = $this->m2('theme_element');
                $theme_element->data($data)->add();
            }
        }
        $this->success('成功');
    }

    //批量移除专题
    public function outTheme(){
        if(IS_POST){
            $goods_ids = I('post.goods_ids');
            //var_dump($goods_ids);exit;
            foreach($goods_ids as $row)
            {
                $this->m2('theme_element')->where('type=1 and type_id='.$row)->delete();
            }
            $this->success('成功');
        }else{
            $this->error('非法访问');
        }
    }

    public function refund(){
        $this->actname = '退款页面';

        if(IS_AJAX){
            $id = I('post.id');
            $oper = I('post.oper');

            if($oper == 1){
                //允许退款
                //前面还要添加退款接口操作，如果成功则执行下面代码
                $refund_rs = $this->m2('order_refund')->where('id='.$id)->find();
                $order_id = $refund_rs['order_id'];
                $data['id'] = $order_id;
                $data['act_status'] = 6;
                $this->m2('order')->data($data)->save();

                $data = array();
                $data['id'] = $id;
                $data['is_allow'] = 1;
                $this->m2('order_refund')->data($data)->save();
                $this->success('成功，退款进行中');
            }else{
                //拒绝退款
                $reason = I('post.reason');
                $refund_rs = $this->m2('order_refund')->where('id='.$id)->find();
                $order_id = $refund_rs['order_id'];
                $data['id'] = $order_id;
                $data['act_status'] = 6;
                $this->m2('order')->data($data)->save();

                $data = array();
                $data['id'] = $id;
                $data['is_allow'] = 2;
                $data['refusal_reason'] = $reason;
                $this->m2('order_refund')->data($data)->save();
                $this->success('已经拒绝退款申请');
            }
        }

        $condition = array();
        $condition['order_act_status'] = array('IN','5,6');
        //$condition['order_wares_type'] = array('EQ',1);
        $datas['datas'] = D('OrderRefundView')->where($condition)->page(I('get.page'), 20)->order('id desc')->select();
//print_r($datas['datas']);exit;
        //数据处理
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['order_create_time'] = date('Y-m-d H:i:s',$row['order_create_time']);
            $datas['datas'][$key]['order_paytime'] = date('Y-m-d H:i:s',$row['order_paytime']);
            if($row['is_allow'] == '0')$datas['datas'][$key]['is_allow'] = '未操作';
            if($row['is_allow'] == '1')$datas['datas'][$key]['is_allow'] = '允许';
            if($row['is_allow'] == '2')$datas['datas'][$key]['is_allow'] = '不允许';
            if($row['order_act_status'] == '0')$datas['datas'][$key]['order_act_status_re'] ='未支付';
            if($row['order_act_status'] == '1')$datas['datas'][$key]['order_act_status_re'] ='已支付未发货/未参加';
            if($row['order_act_status'] == '2')$datas['datas'][$key]['order_act_status_re'] ='已支付已发货/已参加';
            if($row['order_act_status'] == '3')$datas['datas'][$key]['order_act_status_re'] ='已发货未确认';
            if($row['order_act_status'] == '4')$datas['datas'][$key]['order_act_status_re'] ='已完成';
            if($row['order_act_status'] == '5')$datas['datas'][$key]['order_act_status_re'] ='已申请退款';
            if($row['order_act_status'] == '6')$datas['datas'][$key]['order_act_status_re'] ='已完成退款';
            if($row['order_act_status'] == '7')$datas['datas'][$key]['order_act_status_re'] ='已取消';
            if($row['order_member_coupon_id']){
                $coupon_re = $this->m2('coupon')->where('id='.$row['order_member_coupon_id'])->find();
                $coupon_name = $coupon_re['name'];
                $datas['datas'][$key]['order_member_coupon_name'] = $coupon_name;

                $coupon_type = $coupon_re['type'];
                $coupon_value ='';
                if($coupon_type == 0)$coupon_value = $coupon_re['value'];
                if($coupon_type == 1)$coupon_value = $coupon_re['value'].'%';
                if($coupon_type == 2)$coupon_value = '礼品券';
                $datas['datas'][$key]['order_member_coupon_value'] = $coupon_value;
            }
            $order_re = $this->m2('order')->where('id='.$row['order_id'])->find();
            $order_sn = $order_re['sn'];
            $datas['datas'][$key]['order_sn'] =$order_sn;

            $order_id = $row['order_id'];
            $order_wares_rs = $this->m2('order_wares')->where('order_id='.$order_id)->find();
            $id = $order_wares_rs['ware_id'];
            if($order_wares_rs['type']==0){
                $tips_rs = $this->m2('tips')->where('id='.$id)->find();
                $datas['datas'][$key]['title'] = $tips_rs['title'];
            }
            if($order_wares_rs['type']==1){
                $tips_rs = $this->m2('goods')->where('id='.$id)->find();
                $datas['datas'][$key]['title'] = $tips_rs['title'];
            }
            /*$goods_id = $order_wares_rs['ware_id'];
            $goods_rs = $this->m2('goods')->where('id='.$goods_id)->find();
            $datas['datas'][$key]['title'] = $goods_rs['title'];*/
            $datas['datas'][$key]['pic_id'] = NEW_IMG_PATH.$datas['datas'][$key]['pic_id'];
        }
        $datas['operations'] = array(
            '允许退款'=> array(
                'style' => 'success',
                'fun' => 'allow(%id,1)',
                'condition' => '%order_act_status==5'
            ),
            '拒绝退款'=> array(
                'style' => 'danger',
                'fun' => 'refuse(%id,0)',
                'condition' => '%order_act_status==5'
            )
        );
        $datas['pages'] = array(
            'sum' => D('OrderRefundView')->where($condition)->count(),
            'count' => 20,
        );
        $datas['lang'] = array(
            'id' => 'ID',
            'member_nickname' => '退款人',
            'member_telephone' => '手机号码',
            'title' => '标题',
            'order_sn' =>'订单号',
            'order_price' => '订单价格',
            'order_create_time' => '下单时间',
            'order_paytime' => '支付时间',
            'order_act_status_re' => '订单状态',
            'order_member_coupon_name' => '优惠券',
            'order_member_coupon_value' => '优惠券面值',
            'money' => '退款金额',
            'cause' => '退款理由',
            'pic_id' => array('相关图片', '<img  src="%*%" width="50px" height="50px" />'),
            'refusal_reason' => '拒绝理由',
            'is_allow' => '是否批准退款',
        );

        $this->assign($datas);
        $this->view();
    }

    public function GoodsExport(){
        $title = I('get.title');
        $member = I('get.member');
        $status = (int)I('get.status', 1);

        $condition = [];
        $title && $condition['title'] = ['LIKE', "%{$title}%"];
        $member && $condition['member_nickname'] = ['LIKE',  "%{$member}%"];
        switch($status){
            case 2:
                $condition['status'] = 2;
                $condition['is_pass'] = 1;
                break;
            case 3:
                $condition['status'] = ['IN', '1,2'];
                $condition['is_pass'] = 0;
                break;
            case 4:
                $condition['status'] = 3;
                break;
            default:
                $condition['status'] = 1;
                $condition['is_pass'] = 1;
                break;
        }

        $data = D('GoodsExportView')->where($condition)->select();

        //数据处理
        foreach($data as $key=>$row){
            if($row['is_pass'] == 0)$data[$key]['is_pass'] = '未审核';
            if($row['is_pass'] == 1)$data[$key]['is_pass'] = '审核通过';
            if($row['is_pass'] == 2)$data[$key]['is_pass'] = '审核不通过';
            unset($data[$key]['marketing_type']);
            unset($data[$key]['theme_tipsorgoods_type']);
            unset($data[$key]['status']);
            $data[$key]['marketing_start_time'] = $data[$key]['marketing_start_time']?date('Y-m-d H:i',$row['marketing_start_time']):'';
            $data[$key]['marketing_end_time'] = $data[$key]['marketing_end_time']?date('Y-m-d H:i',$row['marketing_end_time']):'';
            if($data[$key]['marketing_allow_coupon'] == 0) $data[$key]['marketing_allow_coupon']='不允许';
            if($data[$key]['marketing_allow_coupon'] == 1) $data[$key]['marketing_allow_coupon']='允许';
        }
        $title = ['商品ID','商品名称','价格','库存','审核状态','发布者','抢购标题','抢购开始时间','抢购结束时间','抢购价','限购数量','使用优惠券','所属专题'];
        toXls($title, $data, '商品列表');

        exit;
    }

    //商品标签查改
    function getGoodsTags(){
        //查询活动标签
        if(IS_AJAX && I('post.goods_id')==''){
            $id = I('post.id');
            $goods_tags = $this->m2('tag')->where('type=2 and official=0')->select();    //普通活动标签
            $official_goods_tags = $this->m2('tag')->where('type=2 and official=1')->select();       //官方活动标签
            $my_tags = $this->m2('goods_tag')->join('__TAG__ ON ym_goods_tag.tag_id = ym_tag.id')->where('goods_id='.$id)->select();
            $label = array();
            $official_label = array();
            foreach($my_tags as $row){
                if($row['official']==0){
                    $label[] = $row['tag_id'];
                }else{
                    $official_label[] = $row['tag_id'];
                }
            }
            $data['goods_tags'] = $goods_tags;
            $data['official_goods_tags'] = $official_goods_tags;
            $data['my_label'] = $label;
            $data['my_official'] = $official_label;

            $this->ajaxReturn($data);
            exit;
        }
        //修改活动标签
        if(IS_AJAX){
            $id = I('post.goods_id');
            $official_tag_ids = I('post.official_tag_ids');
            $tag_ids = I('post.tag_ids');

            $this->m2('goods_tag')->where('goods_id='.$id)->delete();

            foreach($official_tag_ids as $row){
                $data = array();
                $data['goods_id'] = $id;
                $data['tag_id'] = $row;
                $this->m2('goods_tag')->data($data)->add();
            }

            foreach($tag_ids as $row){
                $data = array();
                $data['goods_id'] = $id;
                $data['tag_id'] = $row;
                $this->m2('goods_tag')->data($data)->add();
            }


            $this->success('修改成功');
        }
    }

    //查看修改活动日志
    public function showLogs(){
        if(IS_AJAX && IS_POST){
            $id = I('post.id');

            $map = ['framework_id' => 181, 'gt' => ['LIKE', '%\"'. $id .'\"%'], 'pt' => ['LIKE', '%\"title\"%']];
            $rs = D('ActMemberView')->where($map)->limit(1000)->order('datetime desc')->select();
            $this->success($rs);
        }
    }

    //编辑拼团
    public function editPiece(){
        $id = I('post.id');
        if(!empty($id)){
            $rs = $this->m2('GoodsPiece')->where(['goods_id' => $id])->find();
            $this->ajaxReturn($rs);
        }
        $data = [];
        $data['goods_id'] = (int)I('post.goods_id');
        $data['price'] = (float)I('post.price');
        $data['count'] = (int)I('post.count');
        $data['limit_time'] = (int)I('post.limit_time');
        $data['limit_num'] = (int)I('post.limit_num');
        $data['stocks'] = (int)I('post.stocks');
        $data['reward'] = I('post.reward', '');
        $data['status'] = 1;

        $rs = $this->m2('GoodsPiece')->where(['goods_id' => $data['goods_id']])->find();
        if(empty($rs)){
            $this->m2('GoodsPiece')->add($data);
        }else{
            $this->m2('GoodsPiece')->save($data);
        }
        $this->success('编辑成功!');
    }

    //删除拼团
    public function deletePiece(){
        $goods_id = I('post.goods_id');
        $this->m2('GoodsPiece')->where(['goods_id' => $goods_id])->save(['status' => 0]);
        $this->success('删除成功!');
    }

    public function upload(){
        $rs = parent::upload();
        if($rs['status'] == 1){
            echo $rs['info']['path'];
        }else{
            echo $rs['info'];
        }
    }
}