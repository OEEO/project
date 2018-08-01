<?php
function createCode($len, $isNumber = true){
    $char = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
    if($isNumber){
        return rand(pow(10, $len - 1), pow(10, $len)) - 1;
    }else{
        $code = '';
        for($i=0; $i<$len; $i++){
            $code .= $char[rand(0, 61)];
        }
        return $code;
    }
}

if(isset($_GET['code'])){
    $skey = substr(base64_encode(sha1($_GET['code'])), 5 + strlen($_GET['code']) % 15, 32);
}else{
    $skey = createCode(32, false);
}
echo $skey . "\n";
