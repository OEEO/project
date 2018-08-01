<?php

if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}

//获取命令参数
global $argv;
$page = isset($argv[1]) ? $argv[1] : null;

$dir = "/home/yfweb/";

$_dir = $dir . 'code/';

$files = scandir($dir);

foreach($files as $file){
    if(strpos($file, '.html') === false)continue;
    $filename = substr($file, 0, strpos($file, '.'));
    if($page !== null && $filename != $page)continue;
    if(!is_file($dir . 'css/' . $filename . '.css') || !is_file($dir . 'js/' . $filename . '.js'))continue;
    $code = file_get_contents($dir . $file);
    preg_match('/<body.*?>(.+)<\/body>/s', $code, $arr);
    $code = compress_html($arr[1]);
    $css = file_get_contents($dir . 'css/' . $filename . '.css');
    $code = '<style>' . compress_css($css) . '</style>' . $code;
    $js = file_get_contents($dir . 'js/' . $filename . '.js');
    $code .= '<script>' . JSMin::minify($js) . '</script>';
    file_put_contents($_dir . $file, $code);
    echo $file . " 压缩完成!\n";
    flush();
    usleep(50000);
}

echo "全部完成!\n";

function compress_html($string){
    global $dir;
    $string=str_replace("\r\n",'',$string);//清除换行符
    $string=str_replace("\n",'',$string);//清除换行符
    $string=str_replace("\t",'',$string);//清除制表符
    $pattern=array(
        "/> *([^ ]*) *</",//去掉注释标记
        "/ +/",
        "/<!--[^!]*-->/",
        "/\" /",
        "/ \"/",
        "'/\*[^*]*\*/'"
    );
    $replace=array (
        ">$1<",
        " ",
        "",
        "\"",
        "\"",
        ""
    );
    $string = preg_replace($pattern, $replace, $string);
    preg_match_all("/<img.+?src=[\"'](.+?)[\"']/s", $string, $arr);
    if(!empty($arr[1])){
        foreach($arr[1] as $val){
            $path = $dir . $val;
            if(!is_file($path))continue;
            $filetype = mime_content_type($path);
            if(in_array($filetype, ['image/png', 'image/gif', 'image/x-icon', 'image/jpeg'])){
                $code = file_get_contents($path);
                $code = 'data:'. $filetype .';base64,' . str_replace(' ', '', base64_encode($code));
                $string = str_replace($val, $code, $string);
            }
        }
    }
    return $string;
}

function compress_css($string){
    global $dir;
    $string = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string);
    $string = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $string);
    preg_match_all('/url\((.+?)\)/s', $string, $arr);
    if(!empty($arr[1])){
        foreach($arr[1] as $val){
            $val = trim(trim($val, '"'), "'");
            $path = $dir . str_replace('../', '', $val);
            if(!is_file($path))continue;
            $filetype = mime_content_type($path);
            if(in_array($filetype, ['image/png', 'image/gif', 'image/x-icon', 'image/jpeg'])){
                $code = file_get_contents($path);
                $code = 'data:'. $filetype .';base64,' . str_replace(' ', '', base64_encode($code));
            }
            $string = str_replace($val, $code, $string);
        }
    }
    return $string;
}

class JSMin {
    const ORD_LF            = 10;
    const ORD_SPACE         = 32;
    const ACTION_KEEP_A     = 1;
    const ACTION_DELETE_A   = 2;
    const ACTION_DELETE_A_B = 3;
    protected $a           = '';
    protected $b           = '';
    protected $input       = '';
    protected $inputIndex  = 0;
    protected $inputLength = 0;
    protected $lookAhead   = null;
    protected $output      = '';

    public static function minify($js) {
        $jsmin = new JSMin($js);
        return $jsmin->min();
    }

    public function __construct($input) {
        $this->input       = str_replace("\r\n", "\n", $input);
        $this->inputLength = strlen($this->input);
    }

    protected function action($command) {
        switch($command) {
            case self::ACTION_KEEP_A:
                $this->output .= $this->a;
            case self::ACTION_DELETE_A:
                $this->a = $this->b;
                if ($this->a === "'" || $this->a === '"') {
                    for (;;) {
                        $this->output .= $this->a;
                        $this->a       = $this->get();
                        if ($this->a === $this->b) {
                            break;
                        }
                        if (ord($this->a) <= self::ORD_LF) {
                            throw new JSMinException('Unterminated string literal.');
                        }
                        if ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a       = $this->get();
                        }
                    }
                }
            case self::ACTION_DELETE_A_B:
                $this->b = $this->next();
                if ($this->b === '/' && (
                        $this->a === '(' || $this->a === ',' || $this->a === '=' ||
                        $this->a === ':' || $this->a === '[' || $this->a === '!' ||
                        $this->a === '&' || $this->a === '|' || $this->a === '?' ||
                        $this->a === '{' || $this->a === '}' || $this->a === ';' ||
                        $this->a === "\n" )) {
                    $this->output .= $this->a . $this->b;
                    for (;;) {
                        $this->a = $this->get();
                        if ($this->a === '[') {
                            for (;;) {
                                $this->output .= $this->a;
                                $this->a = $this->get();
                                if ($this->a === ']') {
                                    break;
                                } elseif ($this->a === '\\') {
                                    $this->output .= $this->a;
                                    $this->a       = $this->get();
                                } elseif (ord($this->a) <= self::ORD_LF) {
                                    throw new JSMinException('Unterminated regular expression set in regex literal.');
                                }
                            }
                        } elseif ($this->a === '/') {
                            break;
                        } elseif ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a       = $this->get();
                        } elseif (ord($this->a) <= self::ORD_LF) {
                            throw new JSMinException('Unterminated regular expression literal.');
                        }
                        $this->output .= $this->a;
                    }
                    $this->b = $this->next();
                }
        }
    }

    protected function get() {
        $c = $this->lookAhead;
        $this->lookAhead = null;
        if ($c === null) {
            if ($this->inputIndex < $this->inputLength) {
                $c = substr($this->input, $this->inputIndex, 1);
                $this->inputIndex += 1;
            } else {
                $c = null;
            }
        }
        if ($c === "\r") {
            return "\n";
        }
        if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
            return $c;
        }
        return ' ';
    }

    protected function isAlphaNum($c) {
        return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
    }

    protected function min() {
        if (0 == strncmp($this->peek(), "\xef", 1)) {
            $this->get();
            $this->get();
            $this->get();
        }
        $this->a = "\n";
        $this->action(self::ACTION_DELETE_A_B);
        while ($this->a !== null) {
            switch ($this->a) {
                case ' ':
                    if ($this->isAlphaNum($this->b)) {
                        $this->action(self::ACTION_KEEP_A);
                    } else {
                        $this->action(self::ACTION_DELETE_A);
                    }
                    break;
                case "\n":
                    switch ($this->b) {
                        case '{':
                        case '[':
                        case '(':
                        case '+':
                        case '-':
                        case '!':
                        case '~':
                            $this->action(self::ACTION_KEEP_A);
                            break;
                        case ' ':
                            $this->action(self::ACTION_DELETE_A_B);
                            break;
                        default:
                            if ($this->isAlphaNum($this->b)) {
                                $this->action(self::ACTION_KEEP_A);
                            }
                            else {
                                $this->action(self::ACTION_DELETE_A);
                            }
                    }
                    break;
                default:
                    switch ($this->b) {
                        case ' ':
                            if ($this->isAlphaNum($this->a)) {
                                $this->action(self::ACTION_KEEP_A);
                                break;
                            }
                            $this->action(self::ACTION_DELETE_A_B);
                            break;
                        case "\n":
                            switch ($this->a) {
                                case '}':
                                case ']':
                                case ')':
                                case '+':
                                case '-':
                                case '"':
                                case "'":
                                    $this->action(self::ACTION_KEEP_A);
                                    break;
                                default:
                                    if ($this->isAlphaNum($this->a)) {
                                        $this->action(self::ACTION_KEEP_A);
                                    }
                                    else {
                                        $this->action(self::ACTION_DELETE_A_B);
                                    }
                            }
                            break;
                        default:
                            $this->action(self::ACTION_KEEP_A);
                            break;
                    }
            }
        }
        return $this->output;
    }

    protected function next() {
        $c = $this->get();
        if ($c === '/') {
            switch($this->peek()) {
                case '/':
                    for (;;) {
                        $c = $this->get();
                        if (ord($c) <= self::ORD_LF) {
                            return $c;
                        }
                    }
                case '*':
                    $this->get();
                    for (;;) {
                        switch($this->get()) {
                            case '*':
                                if ($this->peek() === '/') {
                                    $this->get();
                                    return ' ';
                                }
                                break;
                            case null:
                                throw new JSMinException('Unterminated comment.');
                        }
                    }
                default:
                    return $c;
            }
        }
        return $c;
    }

    protected function peek() {
        $this->lookAhead = $this->get();
        return $this->lookAhead;
    }
}

class JSMinException extends Exception {}
