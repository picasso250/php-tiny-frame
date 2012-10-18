<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
/**
 * @file    lib
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jun 27, 2012 6:27:14 PM
 * @version 7.4 
 */

// 防止未定义错误
function i(&$param, $or='') {
    return isset($param)? $param : $or;
}

// 防止写那么长的函数名
// 任何来自用户的输入要显示在页面上都要经过这个函数过滤
function h($str) {
    return htmlspecialchars($str);
}

function js_node($src='', $code='') {
    $src_str = $src? ' src="' . ROOT . 'js/'.$src.'.js?v='. JS_VER .'"' : '';
    return '<script type="text/javascript"'.$src_str.'>'.$code.'</script>';
}

function css_node($src='', $type='css') {
    $rel = 'rel="stylesheet'.($type!='css'?'/'.$type:'').'"';
    $href = 'href="'.ROOT.'css/'.$src.'.'.$type.'?v='. CSS_VER .'"';
    $type = 'type="text/css"';
    return "<link $rel $type $href />";
}

function js_var($var_name, $arr) {
    return js_node('', $var_name.'='.json_encode($arr));
}

function _css($file) {
    return "css/$file.css";
}

function _js($file) {
    return "js/$file.js";
}

/** translate Y-m-d to xx之前 or 今天XX
 *
 * @param type $date_time_str 形如 Y-m-d H:i:s （sql中获得的DateTime类型即可）
 */
function friendly_time($date_time_str) {
    $date_time = new DateTime($date_time_str);
    $nowtime = new DateTime();
    $diff = $nowtime->diff($date_time);
    if ($diff->y==0 && $diff->m==0 && $diff->d==0) { // 同一天
        if ($diff->h<1) // 一个小时以内
            if ($diff->i==0) // 一分钟以内
                return '刚刚';
            else
                return $diff->i.'分钟前'; // minutes
        else
            return '今天'.end(explode(' ', $date_time_str));
    } else {
        return $date_time_str;
    }
}

function d($param, $var_dump=0) {
    if (DEBUG) {
        echo "<p><pre>\n";
        if ($var_dump || empty($param) || is_bool($param)) {
            var_dump($param);
        } else {
            print_r($param);
        }
        echo "</p></pre>\n";
    } else {
        return;
    }
}

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
function image_resize ($file_content, $crop, $width, $height, $new_width, $new_height) {
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
 *
 * @param type $image like $_FILE['xx']
 * @param type $opt resize crop width height
 * @return string url of the final img
 * @throws Exception
 */
function make_image($image, $opt=array()) {
    
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

function image_file_resize($tmp_img_file, $image_type, $crop, $new_width, $new_height) {
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
function write_upload($content, $file_name) {
    
    if (ON_SERVER) {
        $up_domain = UP_DOMAIN;
        $s = new SaeStorage();
        $s->write($up_domain , $file_name , $content);
//        unlink($tmp_img); // maybe we don't need to do that
        return $s->getUrl($up_domain ,$file_name);
    } else {
        $root = 'data/';
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
}

function file_ext($file_name) {
    $arr = explode('.', $file_name);
    if (count($arr) < 2) {
        throw new Exception('bad file name: ' . $image['name']);
    }
    return end($arr);
}

function out_json($arr, $quit=true) {
    echo json_encode($arr);
    if($quit){
        exit;
    }
}

function redirect($url='') {
    if (strpos($url, 'http') !== 0) { // inner link
        $url = ROOT . $url;
    }
    header('Location:'.$url);
    exit();
}

function sae_log($msg){
    sae_set_display_errors(false);//关闭信息输出
    sae_debug($msg);//记录日志
    sae_set_display_errors(true);//记录日志后再打开信息输出，否则会阻止正常的错误信息的显示
}

function req($para, $defalut = '') {
    return isset($_REQUEST[$para]) && $_REQUEST[$para] ? trim($_REQUEST[$para]) : $defalut;
}

function is_mobile() {
    static $mobile_browser = null;
    if ($mobile_browser !== null) {
        return $mobile_browser > 0;
    }
    
    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
 
    $mobile_browser = '0';
 
    if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
        $mobile_browser++;
 
    if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
        $mobile_browser++;
 
    if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
        $mobile_browser++;
 
    if(isset($_SERVER['HTTP_PROFILE']))
        $mobile_browser++;
 
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array(
                        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
                        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
                        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
                        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
                        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
                        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
                        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
                        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
                        'wapr','webc','winw','winw','xda','xda-'
                        );
 
    if(in_array($mobile_ua, $mobile_agents))
        $mobile_browser++;
 
    if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
        $mobile_browser++;
 
    // Pre-final check to reset everything if the user is on Windows
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
        $mobile_browser=0;
 
    // But WP7 is also Windows, with a slightly different characteristic
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
        $mobile_browser++;
 
    if($mobile_browser>0)
        return true;
    else
        return false;
}

/**
 * PhpFile Pf
 * 现在看来这个也可以用来 js css 啥的
 * Usage: Pf::controller('index');
 */
class Pf {
    
    public static function __callStatic($type, $arguments) {
        if (count($arguments) === 0) {
            throw new Exception('no file name');
        }
        $ext = 'php';
        $file = $arguments[0] . '.' . $ext; // file name
        $file = $type . '/' . $file;    // path
        return $file;
    }

}

function smart_view($view, $default = 'default') {
    if (is_mobile() && ($m = Pf::view('mobile/' . $view)) && file_exists($m))
        return $m;
    if (($file = Pf::view($view)) && file_exists($file))
        return Pf::view($view);
    if (is_mobile() && ($m = Pf::view('mobile/' . $default)) && file_exists($m))
        return $m;
    return Pf::view($default);
}

?>