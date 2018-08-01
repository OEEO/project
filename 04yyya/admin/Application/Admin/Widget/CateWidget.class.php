<?php
namespace Admin\Widget;
use Admin\Controller\MainController;

class CateWidget extends MainController {
	
	/**
	 * 数据表格组建
	 * @param array $data 用于生成表格的二维数组
	 * @param array $lang 头部字段别名
	 * @param array $operations 每条数据后面的操作 array('操作名' => '触发事件或URL', ...)
	 * 							'查看详情' => array(
									'text' => '查看详情', //按钮或选项文本
									'style' => 'success', //按钮样式(默认为下拉菜单没有样式) default - 灰色按钮
																														primary - 蓝色按钮
																														secondary - 浅蓝色按钮
																														success - 绿色按钮
																														warning - 橙色按钮
																														danger - 红色按钮
																														link - 链接按钮
									'fun' => 'dispose(%id)', //按钮会被触发的javascript函数
									'condition' => '%is_dispose == 1' //该选项会出现的条件
								)
	 * @param array $pages 分页 array('num'=> 当前页, 'sum'=>总记录数, 'count' => 每页显示数量, 'max' => 最多页码数量)
	 * @param array $batch 批量操作 array('操作名' => '触发事件或URL', ...)
	 * @param bool  $isset_ID ID的存在
	 * @param array $context 注解
	 */
	public function table($data, $lang = [], $operations = [], $pages = [], $batch = [],$isset_ID=true, $context = ''){

		if(!empty($lang)){
			if(!isset($lang['id']) && $isset_ID == true){
				$lg = ['id' => 'ID'];
				$lang = array_merge($lg, $lang);
			}
		}
		
		if(isset($pages['sum']) && $pages['sum'] > 0){
			//读取table默认配置
			$config = C('table');
			$sum = $pages['sum'];
			$page_num = isset($pages['num']) ? $pages['num'] : (isset($_GET['page']) ? $_GET['page'] : 1);
			$page_count = isset($pages['count']) ? $pages['count'] : $config['listnum'];
			$page_offset = ($page_num - 1) * $page_count;
			$page_max = isset($pages['max']) ? $pages['max'] : $config['listmax'];
			$page_sum = ceil($sum / $page_count);
			
			if($page_sum > $page_max){
				//计算出中间页数
				$page_center = ceil($page_max / 2);
				//计算出第一个可显示页码和最后一个可显示页码
				if($page_num > $page_center){
					$page_first = $page_num - $page_center + 1;
					if($page_num > $page_sum - $page_center){
						$page_first = $page_sum - $page_max + 1;
					}
				}else{
					$page_first = 1;
				}
				$page_last = $page_first + $page_max - 1;
			}else{
				$page_first = 1;
				$page_last = $page_sum;
			}
			
			$url = preg_replace('/[\?\&]page=\d+/', '', $_SERVER['REQUEST_URI']);
			if(strpos($url, '?'))
				$url .= '&page=';
			else
				$url .= '?page=';
			
			$pages = [
				'page_num' => $page_num,
				'page_sum' => $page_sum,
				'page_first' => $page_first,
				'page_last' => $page_last,
				'url' => $url
			];
		}
		
		$this->assign([
			'wid_data' => $data,
			'wid_lang' => $lang,
			'wid_opes' => $operations,
			'wid_pages' => $pages,
			'wid_batch' => $batch,
			'wid_context' => $context
		]);
		
		$this->display('Widget:table');
	}
	
	/**
	 * 生成操作按钮
	 * @param array $op
	 * @param array $row
	 */
	Public function tableOpes($op, $row){
		echo '<td>';
		$btns = $opts = '';
		foreach($op as $key => $val){
			$code = '';
			if(is_string($val)){
				$code = $val;
			}elseif(isset($val['fun'])){
				$code = $val['fun'];
				if(!empty($val['condition']))$code .= '$|$' . $val['condition'];
			}
			
			//用正则分析出变量，并用对应的值替换掉变量
			preg_match_all('/%(\w+)/', $code, $code_arr);
			$code_arrcount = count($code_arr[1]);
			for($i=0; $i<$code_arrcount; $i++){
				$code_arr[1][$i] = $row[$code_arr[1][$i]];
			}
			foreach($code_arr[0] as $k => $v){
				$code = str_replace($v, $code_arr[1][$k], $code);
			}
			$condition = true;
			if(strpos($code, '$|$')){
				$arr = explode('$|$', $code);
				$code = $arr[0];
				if(!empty($arr[1]))eval("\$condition = ({$arr[1]});");
			}
			if(isset($val['style']) && in_array($val['style'], ['default','primary','secondary','success','warning','danger','link'])){
				if($condition){
					$btns .= '<button class="am-btn am-btn-'. $val['style'] .' am-btn-xs" type="button" onclick="' . $code . '">' . $key . '</button>';
				}
			}else{
				$opts .= '<li><a href="javascript:void(0);" onclick="' . $code . '">' . $key . '</a></li>';
			}
		}
		
		echo $btns;
		if(!empty($opts))echo '<div class="am-dropdown" data-am-dropdown>
					<button class="am-btn am-btn-default am-btn-xs am-dropdown-toggle" data-am-dropdown-toggle><span class="am-icon-cog"></span> <span class="am-icon-caret-down"></span></button>
					<ul class="am-dropdown-content">
					' . $opts . '</ul></div>';
		echo '</td>';
	}
}