<?php
if(isset($_GET['url'])){
    if(file_put_contents('url.txt', $_GET['url']) !== false){
        exit('修改成功!');
    }else{
        exit('修改失败!');
    }
}elseif(!isset($_GET['input'])){
    $url = @file_get_contents('url.txt');
    if(empty($url))exit('没有跳转链接');
}
?>

<html>
<head>
    <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
<?php if(!isset($_GET['input']))echo '<meta http-equiv="refresh" content="0;'. $url .'">';?>
</head>
<body>
<?php if(!isset($_GET['input'])):echo '请稍后!页面跳转中...';
else:?>
<form>
    <p><input type="text" name="url" placeholder="填写url.." style="width:20rem; height:2rem;"></p>
    <p><input type="submit" value="提交URL" style="width:6rem; height:2rem;"></p>
</form>
<?php endif;?>
</body>
</html>
