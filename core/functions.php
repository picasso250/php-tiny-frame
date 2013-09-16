<?php
/**
 * @author  ryan <cumt.xiaochi@gmail.com>
 */

/* $_GET, $_POST, $_REQUEST helpers or shortens */

function _req($name, $default = null) 
{
    return isset($_REQUEST[$name]) ? ($_REQUEST[$name]) : $default;
}

function _post($name, $default = null)
{
    return isset($_POST[$name]) ? ($_GET[$name]) : null;
}

function _get($name, $default = null)
{
    return isset($_GET[$name]) ? ($_GET[$name]) : null;
}

function g($a)
{
    // get
    if (is_string($a)) { // get
        return isset($GLOBALS[$a]) ? $GLOBALS[$a] : null;
    } 
    // set
    if (is_array($a)) { // set
        foreach ($a as $key => $value) {
            $GLOBALS[$key] = $value;
        }
    }
}

/* image upload helpers */

/**
 * what is this?
 * @param type $file_content
 * @param type $crop
 * @param type $width
 * @param type $height
 * @param type $new_width
 * @param type $new_height
 * @return type
 * @throws Exception
 */
function image_resize ($file_content, $crop, $width, $height, $new_width, $new_height) 
{
    if ($new_width < 1 || $new_height < 1) {
        throw new Exception('specified size too small');
    } else if ($width<$new_width || $height<$new_height) {
        throw new Exception('too small');
    } else {
        $dst = imagecreatetruecolor($new_width, $new_height);
        $src_x = 0;
        $src_y = 0;
        if ($crop) {
            $ratio = $width / $height;
            $new_ratio = $new_width / $new_height;
            if ($ratio > $new_ratio) {
                $width = ceil($new_ratio * $height);
            } else if ($ratio < $new_ratio) {
                $height = ceil($width / $new_ratio);
            }
        }
        imagecopyresampled($dst, $file_content, 0, 0, $src_x, $src_y, $new_width, $new_height, $width, $height);
        return $dst;
    }
}

/**
 * main function
 * @param type $image like $_FILE['xx']
 * @param type $opt resize crop width height
 * @return string url of the final img
 * @throws Exception
 */
function make_image($image, $opt=array()) 
{
    
    // deault option
    $opt = array_merge(array(
        'crop' => 0,
        'resize' => 0,
        'width' => 50,
        'height' => 50,
        'list' => null,
    ), $opt);
    
    $arr = explode('/', $image['type']);
    $file_type = reset($arr);
    $image_type = end($arr);
    if ($file_type == 'image') {
        
        $extention = file_ext($image['name']);
        
        $tmp_img = $image['tmp_name'];
        
        $resize = $opt['resize'];
        $ret_list = $opt['list'];
        if ($ret_list) {
            $ret = array();
            foreach ($ret_list as $opt_) {
                if ($resize) {
                    image_file_resize($tmp_img, $image_type, $opt_['crop'], $opt_['width'], $opt_['height']);
                }
                $content = file_get_contents($tmp_img);
                $file_name = uniqid() . '.' . $extention;
                $ret[] = write_upload($content, $file_name);
            }
            return $ret;
        } else {
            if ($resize) {
                image_file_resize($tmp_img, $image_type, $opt['crop'], $opt['width'], $opt['height']);
            }
            $content = file_get_contents($tmp_img);
            $file_name = uniqid() . '.' . $extention;
            return write_upload($content, $file_name);
        }
    } else { // maybe throw??
        return '';
    }
}

function image_file_resize($tmp_img_file, $image_type, $crop, $new_width, $new_height) 
{
    list($width, $height) = getimagesize($tmp_img_file);
    switch ($image_type) {
        case 'jpg':
        case 'jpeg':
        case 'pjpeg': // yes, this strange type is for ie
            $src = imagecreatefromjpeg($tmp_img_file);
            $dst = image_resize($src, $crop, $width, $height, $new_width, $new_height);
            imagejpeg($dst, $tmp_img_file);
            break;
        case 'png':
        case 'x-png': // for ie
            $src = imagecreatefrompng($tmp_img_file);
            $dst = image_resize($src, $crop, $width, $height, $new_width, $new_height);
            imagepng($dst, $tmp_img_file);
            break;
        case 'gif': // ??
            $src = imagecreatefromgif($tmp_img_file);
            $dst = image_resize($src, $crop, $width, $height, $new_width, $new_height);
            imagegif($dst, $tmp_img_file);
            break;
        default :
            break;
    }
}

// write file content to dst
function write_upload($content, $file_name) 
{
    if (ON_SERVER) {
        sae_upload($content, $file_name);
    }

    $root = APP_ROOT.'data/';
    if (!file_exists($root)) {
        mkdir($root);
    }
    $dst_root = $root .'upload/';
    if (!file_exists($dst_root)) {
        mkdir($dst_root);
    }
    $year_month_folder = date('Ym');
    $path = $year_month_folder;
    if (!file_exists($dst_root.$path)) {
        mkdir($dst_root.$path);
    }
    $date_folder = date('d');
    $path .= '/'.$date_folder;
    if (!file_exists($dst_root.$path)) {
        mkdir($dst_root.$path);
    }
    $path .= '/'.$file_name;
    file_put_contents($dst_root.$path, $content);
    return ROOT . 'data/upload/' . $path;
}

function sae_upload$content, $file_name) 
{
    $up_domain = UP_DOMAIN;
    $s = new SaeStorage();
    $s->write($up_domain , $file_name , $content);
    return $s->getUrl($up_domain ,$file_name);
}


function file_ext($file_name) 
{
    $arr = explode('.', $file_name);
    if (count($arr) < 2) {
        throw new Exception('bad file name: ' . $image['name']);
    }
    return end($arr);
}

// (CamelCase or camelCase) to under_score
// support only one Upper Case
// this function is very important, move it to core!
function camel2under($str)
{
    if (preg_match('/.+[A-Z].+/', $str)) {
        $str = preg_replace('/^(.+)([A-Z].+)$/', '$1_$2', $str); // with underscore
    }
    return strtolower($str);
}

// now you'd better use this name
function underscoreToCamelCase($value) 
{
    return implode(array_map(function($value) { return ucfirst($value); }, explode('_', $value)));
}
 
function camelCaseToUnderscore($value) 
{
    return preg_replace_callback('/([A-Z])/', function($char) { return '_'.strtolower($char[1]); }, lcfirst($value));
}


// usage: 
//     $url could be empty, which will go to index, 
//     could be out link, such "http://google.com"
//     also could be absulote path, such as "/root/login"
//     the begining "/" could be omitted
function redirect($url='') 
{
    // 1. out link ==> directly
    // 2. inner link (without root) ==> add ROOT first
    // 3. inner link (with root) ==> directly
    if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) { // inner link relatively
        $url = ROOT . $url;
    }
    header('Location:'.$url);
    exit();
}

function sae_log($msg)
{
    sae_set_display_errors(false); //关闭信息输出
    sae_debug($msg); //记录日志
    sae_set_display_errors(true); //记录日志后再打开信息输出，否则会阻止正常的错误信息的显示
}

