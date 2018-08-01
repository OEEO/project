<?
	
	define('DB_HOST','localhost');
	define('DB_USER','root');
	define('DB_PASS','root');
	define('DB_DATA','moonloise');

/*****************************************************
函数功能：处理Insert、Update、Delete对数据库的写操作。
创建时间：2009年5月8日
修改时间：2009年5月22日
创建人员：李俊杰
参数说明：$query指带入的SQL语句
*****************************************************/
function sql_w($query)
{
	$conn = mysql_connect(DB_HOST,DB_USER,DB_PASS);
	mysql_select_db(DB_DATA,$conn);
	$result = mysql_query($query);
	mysql_close($conn);
	
	return $result;
}

/*****************************************************
函数功能：处理Setect对数据库的读操作。
创建时间：2009年5月8日
修改时间：2009年7月13日
创建人员：李俊杰
参数说明：$query指带入的SQL语句
*****************************************************/
function sql_r($query)
{
	$conn = mysql_connect(DB_HOST,DB_USER,DB_PASS);
	mysql_select_db(DB_DATA);
	$put_out = array();
	if(!$result = mysql_query($query))return false;
	while($row = mysql_fetch_array($result))
	{
		$num = count($row)/2;
		for($i = 0; $i < $num; $i++)
		{
			array_splice($row, $i, 1);
		}
		$put_out []= $row;
	}
	mysql_close($conn);
	return $put_out;
}

/*************************************************************
函数功能：处理Insert对数据库的写操作，并返回其产生的自动编号。
创建时间：2009年5月8日
修改时间：2009年5月22日
创建人员：李俊杰
参数说明：$query指带入的SQL语句
*************************************************************/
function sql_in($query)
{
	$conn = mysql_connect(DB_HOST,DB_USER,DB_PASS);
	mysql_select_db(DB_DATA,$conn);
	if($result = mysql_query($query)){
		$result = mysql_insert_id();
	}
	mysql_close($conn);
	
	return $result;
}

?>