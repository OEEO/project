<?php
namespace Member\Controller;
use Think\Controller;

Class VerifyController extends Controller {

	Public function __construct(){
		parent::__construct();
	}


	public function verify() {
//		session_start();
//		session('verify_code', '1');
		$code = base64_decode(I('code'));
		$verify = new \Think\Verify();
		$verify->length = 4;
//		$verify->useCurve = false;
		$Verify->useImgBg = true; 
		$verify->expire = 3600;
		$verify->codeSet = '0123456789';
		$verify->seKey = '123';
		$verify->entry2($code);
//		$data['code'] = '$verify->entry2()';
//		$this->ajaxReturn($code);
	}

	public function verify2() {
//		session_start();
//		session('verify_code', '1');
		$verify = new \Think\Verify();
		$verify->length = 5;
		$verify->expire = 3600;
		$verify->seKey = '123';
		$verify->entry();
//		$this->ajaxReturn($code);
	}


	public function session() {
		session_start();
//		var_dump($_SESSION['verify_code']);
//		echo $_SESSION['verify_code'];
		var_dump($_SESSION);
//		$data['code'] = $_SESSION;
//		$data['code'] = '111';
//		$this->ajaxReturn($data);
		return $_SESSION['verify_code'];
//		echo session('verify_code');
	}


	Protected function error($msg = ''){
        $this->ajaxReturn([
            'status' => 0,
            'info' => $msg
        ]);
    }


	/*
	*验证图片验证码
	*/
	public function checkVerify() {
		session_start();
		/*
		$code = I('post.code');
//		$verify = new \Think\Verify();
//		$_SESSION['verify_code'] = '222';
		$data['code'] = $_SESSION;


//		$data['pic_code'] = $verify->check($code);
//		$data['check_code'] = 1;
		$this->ajaxReturn($data);

/*
		if ($verify->check($code)) {
			$data['check_code'] = 1;
			$this->ajaxReturn($data);
		} else {
//			$this->error('请先完成图片验证');
			
			$this->error($code);
//			$this->error(session('verify_code'));
		}
		*/
	}

	public function getIp() {
		$Ip = M('login_ip');
		$data = array(
			'ip' => get_client_ip(),
		);
//		$Ip->create($data)->add();
		
		var_dump($Ip->select());
	}
/*
     public function qrcode(){
        $save_path = isset($_GET['save_path'])?$_GET['save_path']:ROOT_PATH.'Public/qrcode/';  //图片存储的绝对路径
        $web_path = isset($_GET['save_path'])?$_GET['web_path']:'/Public/qrcode/';        //图片在网页上显示的路径
        $qr_data = isset($_GET['qr_data'])?$_GET['qr_data']:'http://www.zetadata.com.cn/';
        $qr_level = isset($_GET['qr_level'])?$_GET['qr_level']:'H';
        $qr_size = isset($_GET['qr_size'])?$_GET['qr_size']:'10';
        $save_prefix = isset($_GET['save_prefix'])?$_GET['save_prefix']:'ZETA';
        if($filename = createQRcode($save_path,$qr_data,$qr_level,$qr_size,$save_prefix)){
            $pic = $web_path.$filename;
        }
        echo "<img src='".$pic."'>";
    }
*/
    public function qrcode() {
		vendor('PHPQRcode.class#phpqrcode');
		$goods_id = I('goods_id');
		$invitecode = I('invitecode');
//    	$value="http://m.yami.ren/?page=choice-goodsDetail&goods_id=".$goods_id."&type=2&invitecode=".$invitecode; 
		$value="http://test.yummy194.cn/?page=choice-goodsDetail&goods_id=".$goods_id."&type=2&invitecode=".$invitecode;  
		$errorCorrectionLevel = "L"; // 纠错级别：L、M、Q、H  
		$matrixPointSize = "4"; // 点的大小：1到10 
		$QRcode = new \QRcode();
		$QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize); 
    }

    public function water() {
    	error_reporting(E_ALL^E_NOTICE^E_WARNING);
		ob_clean();
        header('Content-Type:image/jpg');
		$cover = 'http://img.m.yami.ren/20171205/b5e823c4000d6d103c211108e459899d9d80d336.jpg';
		$qrcode = 'http://api.m.yami.ren/Member/Verify/qrcode';
		$bg = 'http://m.yami.ren/images/share_bg.jpg';
//		            imagejpeg($bg);

/*
		$path_1 = $bg;
		//装备图片
		$path_2 = $cover;
		//将人物和装备图片分别取到两个画布中
		$image_1 = imagecreatefrompng($path_1);
		$image_2 = imagecreatefrompng($path_2);
		//创建一个和人物图片一样大小的真彩色画布（ps：只有这样才能保证后面copy装备图片的时候不会失真）
		$image_3 = imageCreatetruecolor(imagesx($image_1),imagesy($image_1));
		//为真彩色画布创建白色背景，再设置为透明
		$color = imagecolorallocate($image_3, 255, 255, 255);
		imagefill($image_3, 0, 0, $color);
		imageColorTransparent($image_3, $color);
		//首先将人物画布采样copy到真彩色画布中，不会失真
		imagecopyresampled($image_3,$image_1,0,0,0,0,imagesx($image_1),imagesy($image_1),imagesx($image_1),imagesy($image_1));
		//再将装备图片copy到已经具有人物图像的真彩色画布中，同样也不会失真
		imagecopymerge($image_3,$image_2, 150,150,0,0,imagesx($image_2),imagesy($image_2), 100);
		//将画布保存到指定的gif文件
//		imagepng($cover);

		/*
		$str = "1\n2\n3\n";
$im = imagecreate(100,120);
$white = imagecolorallocate($im,0xFF,0xFF,0xFF);
imagecolortransparent($im,$white);  //imagecolortransparent() 设置具体某种颜色为透明色，若注释
$black = imagecolorallocate($im,0x00,0x00,0x00);
 
imagettftext($im,15,0,50,40,$black,"",$str); //字体设置部分linux和windows的路径可能不同
//imagepng($im);//文字生成的图片
*/
    }

    public function curl() {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://img.m.yami.ren/20171205/b5e823c4000d6d103c211108e459899d9d80d336.jpg');
		curl_setopt($curl, CURLOPT_REFERER, '');
		curl_setopt($curl, CURLOPT_USERAGENT, 'Baiduspider');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		header('Content-type: image/JPEG');
		echo $result;
    }

    public function curl2() {
    	$pic = I('mainpic');
//    	$url = "http://img.m.yami.ren/".$pic;
    	$url = "http://img.test.yummy194.cn/".$pic;
		$ch = curl_init($url); //初始化
		curl_setopt($ch, CURLOPT_HEADER, 0); //不返回header部分
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，而非直接输出	
		$ret = curl_exec($ch);
		//$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
		curl_close($ch);
		header("Content-Type: image/JPEG");
		echo $ret;
    }

    public function setredis() {
    	$value = I('get.value');
    	$redis = getRedis();
    	$redis->set('test',$value);
    	$redis->expire('test', 1200);
    	echo $value;
    }

    public function getredis() {
    	$redis = getRedis();
    	echo $redis->get("test");

    }


}