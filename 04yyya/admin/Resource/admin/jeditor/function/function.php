<?
/*****************************************************
函数功能：读取模板代码，并替换模板中的关键字。
创建时间：2009年5月8日
修改时间：2009年6月19日
创建人员：李俊杰
参数说明：$temp指模板名称、$array是指定替换数据的名值对数组
*****************************************************/
function readAndChange($str,$array)
{
	while(list($name,$value) = each($array))
	{
		$str = str_replace($name,$value,$str);
	}
	
	return $str;
}

/*****************************************************
函数功能：读取模板代码。
创建时间：2009年6月18日
修改时间：2009年6月18日
创建人员：李俊杰
参数说明：$temp指模板名称
*****************************************************/
function read($temp)
{
	$localhost = ".".TEMP.$temp.".html";
	if(strchr($temp, "admin"))$localhost = "./skin/admin/".$temp.".html";
	if(strchr($temp, "txt"))$localhost = $temp;
	$fp = @fopen($localhost,r);
	$str = fread($fp, filesize($localhost));
	fclose($fp);
	return $str;
}

/*****************************************************
函数功能：截取指定首尾的子字符串。
创建时间：2009年6月18日
修改时间：2009年6月18日
创建人员：李俊杰
参数说明：$str指被截取的字符串、$start指模板起始串、$end指模板结束串
*****************************************************/
function readBetween($str,$start,$end)
{
	
	$str = substr($str, stripos($str,$start) + strlen($start), stripos($str,$end) - stripos($str,$start) - strlen($start));

	return $str;
	
}

/*****************************************************
函数功能：替换指定首尾的子字符串。
创建时间：2009年6月18日
修改时间：2009年6月18日
创建人员：李俊杰
参数说明：$str指被截取的字符串、$start指模板起始串
          $end指模板结束串、$str0指定替换的字符串
*****************************************************/
function changeBetween($str,$start,$end,$str0)
{
	
	$str = str_replace(substr($str, stripos($str,$start), stripos($str,$end) - stripos($str,$start) + strlen($start)),$str0,$str);
	
	return $str;
	
}

/*****************************************************
函数功能：判断字符串是否为字母、数字和下划线组成，以及字数已否超过限制。
创建时间：2009年6月19日
修改时间：2009年6月19日
创建人员：李俊杰
参数说明：$str指定字符串、$max指定最多字数、$min指定最少字数
*****************************************************/
function is_right($str,$max=9999,$min=0)
{
	
	if( preg_match("/^(\w+)$/",$str) && strlen($str) <= $max && strlen($str) >= $min ) return 1;
	return 0;
	
}

/*****************************************************
函数功能：判断字符串是否为自然数组成，以及数字大小已否超过限制。
创建时间：2009年6月19日
修改时间：2009年6月19日
创建人员：李俊杰
参数说明：$str指定字符串、$max指定最多字数
*****************************************************/
function is_number($str,$max=0)
{
	
	if( preg_match("/^(\d+)$/",$str) && ((intval($str) < $max && intval($str) > 0) || $max == 0) ) return 1;
	return 0;
	
}

/*****************************************************
函数功能：判断字符串是否为email格式。
创建时间：2009年6月19日
修改时间：2009年6月19日
创建人员：李俊杰
参数说明：$str指定字符串
*****************************************************/
function is_email($str)
{
	
	if( preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/",$str)) return 1;
	return 0;
	
}

/*****************************************************
函数功能：结合javascript alert()函数，发送客户触发消息。
创建时间：2009年5月18日
修改时间：2009年5月18日
创建人员：李俊杰
参数说明：$text指字幕（为空则不提示），$myURL指转向地址
*****************************************************/
function message($text = "",$myURL = "")
{
	header("Content-Type:text/html;charset=utf-8");
	echo "<div id='message' style='width:300px; position:absolute; z-index:200; font-size:12px; color:#333; background-color:#f7f7f7; border:solid 3px #ccc; padding:10px;'><div class='inTitle'><b>Friendly Prompt:</b></div><div class='inside' style='border:solid 1px #eee; background-color:#fff; padding:10px;'></div></div>";
	echo "
	<SCRIPT src='./function/jquery.js' type='text/javascript'></SCRIPT>
	<script type=text/javascript>
";
	if($text)echo "$('#message > .inside').append('".$text."');";
	echo "//取得视窗高
function windowHeight(){
	var de = document.documentElement;
	return self.innerHeight || ( de && de.clientHeight ) || document.body.clientHeight;
}

//取得视窗宽
function windowWidth(){
	var de = document.documentElement;
	return self.innerWidth || ( de && de.clientWidth) || document.body.clientWidth;
}

	$('#message > .inside').after('<br /><center><span style=\'border:solid 1px #ccc; background-color:#eee; padding:6px 10px 3px 10px; cursor:pointer;\' onclick=\'urlto()\'>明 白</span></center>');
			$('#message').css('top',(windowHeight() - $('#message').height()) / 2);
			$('#message').css('left',(windowWidth() - $('#message').width()) / 2);
			";
	echo "function urlto(){";
	if($myURL == -1){
		echo "window.history.back();";
	}else{
		echo "window.location.href = '".$myURL."';";
	}
	echo "}
	</script>";
	exit;
}

/*****************************************************
函数功能：上传文件函数。
创建时间：2009年8月17日
修改时间：2009年8月17日
创建人员：李俊杰
参数说明：$file指定客户端提交的$_FILE[]
*****************************************************/
function upfiles($file,$name='')
{
	$attachment = $file[tmp_name];
	$new_file = $name;
	if(empty($name))$new_file = date("YmdHis").rand(1,99) . strtolower(strrchr($file[name], '.'));
	if(function_exists("move_uploaded_file")&&move_uploaded_file($attachment,empty($name) ? FILE_UPLOAD.$new_file : 'images/'.$new_file)){
		chmod(empty($name) ? FILE_UPLOAD.$new_file : 'images/'.$new_file,0666);
		return $new_file;
	}elseif(copy($attachment,empty($name) ? FILE_UPLOAD.$new_file : 'images/'.$new_file)){
		chmod(empty($name) ? FILE_UPLOAD.$new_file : 'images/'.$new_file,0666);
		return $new_file;
	}else{
		return false;
	}
}

/*****************************************************
函数功能：判断是否登录函数。
创建时间：2009年10月5日
修改时间：2009年12月12日
创建人员：李俊杰
参数说明：该函数将自动取得SESSION变量进行判断
*****************************************************/
function is_login()
{
	if(session_is_registered(adminname) && session_is_registered(password)){
		if(ADMIN_NAME == $_SESSION[adminname] && ADMIN_PASS == $_SESSION[password])return true;
		session_destroy();
		return false;
	}else{
		return false;
	}
}

/*****************************************************
函数功能：生成文件函数。
创建时间：2009年12月13日
修改时间：2009年12月25日
创建人员：李俊杰
参数说明：$link生成文件路劲，$str生成文件内容
*****************************************************/
function create($link,$str = ""){
	$ary = explode("/", trim($link, "/"));
	$url = "";
	$i = 0;
	if(count($ary) > 1){
		for(;$i < ($str == "" ? count($ary) : count($ary) - 1); $i++){
			$url .= $ary[$i]."/";
			if(!is_dir($url)){
				if(!@mkdir($url))return false;
			}
		}
	}
	
	if($str != ""){
		$fp = fopen($url.$ary[$i], "w");
		fputs($fp, $str);
		fclose($fp);
	}
	
	return true;
}

/*****************************************************
函数功能：截取包括汉字在内的字符串。
创建时间：2010年11月10日
修改时间：2010年11月10日
创建人员：李俊杰
参数说明：$str字符串，$num截取的汉字长度
*****************************************************/
function cut_str($str, $num){
	$str0 = strlen($str) > $num * 2 ? substr($str, 0, $num * 2) : $str;
	$arr = str_split($str0);
	$rs_arr = array(0);
	for($i=0; $i<count($arr); $i++){
		if(!preg_match("/^(\w|\s)$/",$arr[$i])){
			if(!@preg_match("/^(\w|\s)$/",$arr[$i + 1])){
				@array_push($rs_arr, $arr[$i].$arr[$i + 1]);
				$i ++;
			}else{
				array_push($rs_arr, $arr[$i]);
			}
		}else{
			array_push($rs_arr, $arr[$i]);
		}
	};
	return join("",array_slice($rs_arr, 1, $num)).(count($rs_arr) > $num ? "..." : "");
}

/*****************************************************
函数功能：用于辅助uasort()进行二维数组排序。
创建时间：2010年11月10日
修改时间：2010年11月10日
创建人员：李俊杰
参数说明：$a、$b第一、第二参数
*****************************************************/
function cmp($a,$b){
	return $a['date'] > $b['date'] ? 1 : -1;
}

/*****************************************************
函数功能：解释自定义代码，并返回。
创建时间：2010年11月10日
修改时间：2010年11月10日
创建人员：李俊杰
参数说明：$str含自定义代码的内容
*****************************************************/
function codeChange($str){
	$array = array(
		'[img src="' => '<img src="',
		'/]' => '/>',
		'file/' => FILE_UPLOAD,
		'[code=script]' => '<script type="text/javascript">',
		'[/code=script]' => '</script>'
	);
	while(list($name,$value) = each($array)){
		$str = str_replace($name,$value,$str);
	}
	return $str;
}


/*****************************************************
函数功能：清除数组中的空元素，并返回。
创建时间：2011年7月14日
修改时间：2011年7月14日
创建人员：李俊杰
参数说明：$array原始数组
*****************************************************/
function array_remove_empty($array){
	foreach($array as $row){
		if(!empty($row)){
			$array_[] = $row;
		}
	}
	return $array_;
}

?>