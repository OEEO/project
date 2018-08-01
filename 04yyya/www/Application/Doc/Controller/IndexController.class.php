<?php
namespace Doc\Controller;
use Think\Controller;
class IndexController extends Controller {
	
	Public function _initialize(){
		if(!session('?version_id'))session('version_id', M('version')->order('id desc')->getField('id'));
	}
	
	//页面视图
	public function index(){
		$version_id = I('get.version_id', session('version_id'));
		if($version_id != session('version_id'))session('version_id', $version_id);
		$api_id = I('get.api_id');
		
		$data['doc'] = D('VersionPathView')->where(array('version_id' => $version_id, 'id' => $api_id))->find();
		$data['doc']['getparams'] = json_decode($data['doc']['getparams'], true);
		$data['doc']['postparams'] = json_decode($data['doc']['postparams'], true);
		$data['doc']['url'] = "http://api." . DOMAIN . "/";
		if(C('URL_MODEL') != 2){
			$data['doc']['url'] .= 'index.php/';
		}
		$data['doc']['url'] .= $data['doc']['module_sign'] . '/' . $data['doc']['controller_sign'] . '/' . $data['doc']['sign'] . '.html';
		//dump($data['doc']);exit;
		$rs = D('PathView')->where(array('type'=>2, 'version_id' => $version_id))->select();
		$menu = $apis = array();
		foreach($rs as $row){
			$apis[$row['p_id']][$row['id']]['title'] = $row['title'];
			$apis[$row['p_id']][$row['id']]['sign'] = $row['sign'];
			$apis[$row['pid']][$row['id']]['datetime'] = $row['datetime'];
			$apis[$row['pid']][$row['id']]['isnew'] = $row['datetime']==$row['updatetime']?1:0;

			$menu[$row['m_id']]['title'] = $row['m_title'];
			$menu[$row['m_id']]['sign'] = $row['m_sign'];
			$menu[$row['m_id']]['sub'][$row['p_id']]['title'] = $row['p_title'];
			$menu[$row['m_id']]['sub'][$row['p_id']]['sign'] = $row['p_sign'];
		}
		$data['menu'] = $menu;
		$data['apis']=$apis;
		//dump($menu);exit;
		$data['version'] = M('version')->order('id desc')->select();
		$data['ver'] = $data['version'][0]['num1'] . '.' . $data['version'][0]['num2'];
		$this->assign($data);
		$this->display();
	}
	
	//发布版本
	public function submit(){
		$ver = I('get.ver');
		if(empty($ver) || strpos($ver, '.') === false)$this->error('请输入正确的版本号!');
		$arr = explode('.', $ver);
		//获取最后一个版本
		$num3 = M('version')->order('num3 desc')->where(['num1' => $arr[0], 'num2' => $arr[1]])->getField('num3');
		if(!$num3)
			$num3 = 1;
		else
			$num3 ++;
		$version_id = M('version')->add(array('num1' => $arr[0], 'num2' => $arr[1], 'num3' => $num3));
		if(!$version_id)$this->error('添加新版本出错！');

		$apimodule = C('API_MODULE');
		
		foreach($apimodule as $module => $modulename){
			$path = APP_PATH . $module . '/Controller/';
			if(is_dir($path)){
				//获取模块信息
				$module_id = M('version_path')->where("`title`='{$modulename}' and `pid` is null")->getField('id');
				if(!$module_id)$module_id = M('version_path')->add(array(
					'title' => $modulename,
					'sign' => $module,
					'type' => 0
				));
				$files = scandir($path);
				foreach($files as $file){
					if(strpos($file, '.bak.') !== false)continue;
					if(is_file($path . $file)){
						$code = file_get_contents($path . $file);
						
						//获取控制器信息
						$controller_name = $controller_sign = substr($file, 0, strpos($file, 'Controller'));
						if(preg_match_all('/@className(.+)/i', $code, $arr)){
							$controller_name =  trim($arr[1][0]);
						}
						$controller_id = M('version_path')->where("`title`='{$controller_name}' and `pid`={$module_id}")->getField('id');
						if(!$controller_id)$controller_id = M('version_path')->add(array(
							'title' => $controller_name,
							'sign' => $controller_sign,
							'pid' => $module_id,
							'type' => 1
						));
						
						//获取所有注释
						if(preg_match_all('/\/\*\*(.+?function.+?)\(/s', $code, $arr)){
							
							$contents = $arr[1];
							foreach($contents as $content){
								//分析出方法信息
								$action_name = $action_sign = trim(strrchr($content, ' '));
								if(preg_match_all('/@apiName(.+)/i', $content, $arr)){
									$action_name =  trim($arr[1][0]);
								}
								$datatime = date('Y-m-d H:i:s');
								$action_id = M('version_path')->where("`title`='{$action_name}' and `pid`={$controller_id}")->getField('id');
								if(!$action_id)$action_id = M('version_path')->add(array(
									'title' => $action_name,
									'sign' => $action_sign,
									'pid' => $controller_id,
									'type' => 2,
									'datetime' => $datatime
								));
								
								//分析出GET参数信息
								$getparams = array();
								if(preg_match_all('/@apiGetParam(.+)/i', $content, $arr)){
									foreach($arr[1] as $val){
										if(preg_match('/\{(\w+?)\}(.+?):(.+)$/', trim($val), $_arr)){
											$getparams[] = array(
												'name' => trim($_arr[2]),
												'type' => trim($_arr[1]),
												'context' => trim($_arr[3])
											);
										}
									}
								}
								
								//分析出POST参数信息
								$postparams = array();
								if(preg_match_all('/@apiPostParam(.+)/i', $content, $arr)){
									foreach($arr[1] as $val){
										if(preg_match('/\{(\w+?)\}(.+?):(.+)$/', trim($val), $_arr)){
											$postparams[] = array(
												'name' => trim($_arr[2]),
												'type' => trim($_arr[1]),
												'context' => trim($_arr[3])
											);
										}
									}
								}
								
								//分析出Success信息
								$success = null;
								if(preg_match_all('/@apiSuccessResponse(.+?)(@|\*\/)/s', $content, $arr)){
									$success = preg_replace('/[\s\t]*\*/', "\n", $arr[1][0]);
								}else{
									continue;
								}
							
								//分析出Error信息
								$error = null;
								if(preg_match_all('/@apiErrorResponse(.+?)(@|\*\/)/s', $content, $arr)){
									$error = preg_replace('/[\s\t]*\*/', "\n", $arr[1][0]);
								}

								//插入版本文档路径
								$api_id = M('VersionCorrelationPath')->add(array('version_id' => $version_id, 'path_id' => $action_id, 'datetime' => $datatime));
								
								//插入api文档
								M('version_doc')->add(array(
									'api_id' => $api_id,
									'url' => $module . '/' . $controller_sign . '/' . $action_sign . '.html',
									'getparams' => json_encode($getparams),
									'postparams' => json_encode($postparams),
									'success' => $success,
									'error' => $error
								));
							}
						}
					}
				}
			}
		}
		$this->success('v'. $ver . '.' . $num3 .' 版本发布完成！', __MODULE__);
	}
}