<?php
/**
 * @author  ryan <cumt.xiaochi@gmail.com>
 */

namespace ptf;

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
    return preg_replace_callback(
        '/([A-Z])/', 
        function($char) { return '_'.strtolower($char[1]); }, 
        lcfirst($value)
    );
}

/**
 * 貌似可以直接放在Controller中
 * @param type $msg
 */
function sae_log($msg)
{
    sae_set_display_errors(false); //关闭信息输出
    sae_debug($msg); //记录日志
    sae_set_display_errors(true); //记录日志后再打开信息输出，否则会阻止正常的错误信息的显示
}

