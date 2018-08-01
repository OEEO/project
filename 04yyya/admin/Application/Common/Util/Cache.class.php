<?php
namespace Common\Util;

class Cache {

	private $timeout = null;
	private $obj = null;
	private $config = null;
	private $prefix = 'tmp_';

	Private function __construct($config){
		if($config !== null)
			$this->config = $config;
		elseif(C('MEMCACHE'))
			$this->config = C('MEMCACHE');
	}

	static public function getInstance($config = null){
		static $instance = null;
		if($instance === null){
			$instance = new self($config);
		}
		return $instance;
	}

	public function getMem(){
		return $this->obj;
	}

	//连接memcache
	public function connect(){
		if($this->config == null)return false;
		if($this->obj != null)return true;
		$this->obj = new \Memcache();
		$this->obj->connect($this->config['HOST']. ':' .$this->config['PORT']) or die ("Memcache not connect!");
		$this->timeout = $this->config['TIMEOUT'];
		$this->prefix =$this->config['PREFIX'];
		return true;
	}

	//重新连接memcache
	public function close(){
		$this->obj->close();
		$this->obj = null;
	}

	/**
	 * 设置缓存
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value, $timeout = null){
		$this->connect();
		if($timeout === null)$timeout = $this->timeout;
		if($timeout > 7 * 24 * 60 * 60)die('缓存设置不能超过7天！');
		if(!$this->config){
			if(file_exists(TEMP_PATH . 'cache.tmp')){
				$content = file_get_contents(TEMP_PATH . 'cache.tmp');
				$content = json_decode($content, true);
			}
			$content[$key] = [
				'data' => $value,
				'timeout' => $timeout + time()
			];
			return file_put_contents(TEMP_PATH . 'cache.tmp', json_encode($content));
		}
		if(!is_string($value))$value = json_encode($value);

		//记录索引
		$index = $this->obj->get($this->prefix . 'index');
		if(!$index)
			$this->obj->set($this->prefix . 'index', '|');
		elseif(strpos($index, '|' . $key . '|') === false){
			$this->obj->set($this->prefix . 'index', $index . $key . '|');
		}
		return $this->obj->set($this->prefix . $key, $value, 0, $timeout);
	}

	/**
	 * 读取缓存
	 * @param string $key
	 */
	public function get($key){
		$this->connect();
		if(!$this->config){
			if(!file_exists(TEMP_PATH . 'cache.tmp'))return false;
			$content = file_get_contents(TEMP_PATH . 'cache.tmp');
			$content = json_decode($content, true);
			if(!isset($content[$key]))return false;
			$value = $content[$key];
			if($value['timeout'] > time()){
				return $value['data'];
			}else{
				unset($content[$key]);
				file_put_contents(TEMP_PATH . 'cache.tmp', json_encode($content));
				return false;
			}
		}
		$str = $this->obj->get($this->prefix . $key);
		$data = json_decode($str, true);
		return (json_last_error() == JSON_ERROR_NONE) ? $data : $str;
	}

	/**
	 * 删除指定缓存
	 * @param string $key
	 */
	public function rm($key){
		$this->connect();
		if(!$this->config){
			$content = file_get_contents(TEMP_PATH . 'cache.tmp');
			$content = json_decode($content, true);
			if(!isset($content[$key]))return true;
			unset($content[$key]);
			file_put_contents(TEMP_PATH . 'cache.tmp', json_encode($content));
			return true;
		}
		$index = $this->obj->get($this->prefix . 'index');
		if($index && strpos($index, '|' . $key . '|') !== false)
			$this->obj->set($this->prefix . 'index', str_replace('|' . $key . '|', '|', $index));
		return $this->obj->delete($this->prefix . $key);
	}

	/**
	 * 清除所有缓存
	 */
	public function clear(){
		$this->connect();
		return $this->obj->flush();
	}

	/**
	 * 关键字查询键，返回对应的键值对数组
	 * $keyword 查询用的关键字
	 * $regular 是否使用正则查询
	 */
	public function find($keyword, $regular = false){
		$this->connect();
		$index = $this->obj->get($this->prefix . 'index');
		$index = explode('|', trim($index, '|'));
		$data = [];
		foreach($index as $val){
			if((!$regular && strpos($val, $keyword) !== false) || ($regular && preg_match($keyword, $val))){
				$data[$val] = $this->get($val);
			}
		}
		return $data;
	}

	/**
	 * 魔术方法
	 * @param $name 未定义属性
	 */
	Public function __get($name){
		return $this->get($name);
	}

	/**
	 * 魔术方法
	 * @param $name 未定义属性
	 * @param $value 未定义属性的值
	 */
	public function __set($name, $value) {
		return $this->set($name, $value);
	}

}

