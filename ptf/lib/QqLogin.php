<?php

/**
 * QQ 互联登录
 * usage:
 * 
 *
 * @file    QqLogin
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jul 21, 2012 1:57:41 PM
 * @version 0.7 add usage
 * Usage:
 *     
 * $config = array(
 *     'app_id'=>'10030730x',
 *     'app_key'=>'689e7841d05f33cf83f085ecce5be3cx',
 *     'scope'=>implode(',', array('get_user_info')),
 *     'callback'=> !ON_SERVER?'http://appname.sinaapp.com/appname/login':'http://appname.sinaapp.com/login',
 * );
 */

namespace kindcent;

class QqLogin {

    private $app_id = null;
    private $app_key = null;
    private $scope = null;
    private $callback_url = null;
    private $access_token = null;
    private $openid = null;

    /**
     *
     * @param type $config app_id, app_key, scope, callback
     */
    function __construct($config = array()) {
        if ($config) {
            $this->setConfig($config);
        }
        if (!isset($_SESSION['se_ptf_qq_state']))
            $_SESSION['se_ptf_qq_state'] = md5(uniqid(rand(), TRUE));
    }

    public function setConfig($para=array()) {
        $this->app_id = $para['app_id'];
        $this->app_key = $para['app_key'];
        $this->scope = $para['scope'];
        $this->callback_url = $para['callback'];
    }

    public function getLoginHref() {
        return 'https://graph.qq.com/oauth2.0/authorize?'.implode('&amp;', array(
            'response_type=code',
            "client_id=$this->app_id",
            "redirect_uri=$this->callback_url",
            'state='.$_SESSION['se_ptf_qq_state'],
            "scope=$this->scope",
        ));
    }

    /**
     * 是否是从QQ那里跳转过来的
     * @return type
     */
    public function isCallback() {
        return i($_GET['code']) && $_GET['state'] == $_SESSION['se_ptf_qq_state'];
    }

    public function getAccessToken($code) {
        $url = 'https://graph.qq.com/oauth2.0/token?'.implode('&', array(
            'grant_type=authorization_code',
            "client_id=$this->app_id",
            "redirect_uri=$this->callback_url", // why we need callback uri here?
            "client_secret=$this->app_key",
            "code=$code"
        ));
        $response = file_get_contents($url);
        if (strpos($response, "callback") !== false) {
            throw new Exception('there be callback in response:'.$response);
        }
        parse_str($response, $arr);
        $this->access_token = i($arr['access_token']); // 获取access_token
        $_SESSION['se_ptf_access_token'] = $this->access_token; // ??? 还需要吗?
        return $this->access_token;
    }

    /**
     * 调用这个函数前，必须先调用getAccessToken($code) 
     * @return type
     * @throws Exception
     */
    public function getOpenId() {
        if (empty($this->access_token)) {
            $this->access_token = $this->getAccessToken($_GET['code']);
        }
        $url = 'https://graph.qq.com/oauth2.0/me?access_token='.$this->access_token;
        $response = file_get_contents($url);
        if (strpos($response, "callback") !== false) {
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $str  = substr($response, $lpos + 1, $rpos - $lpos -1);
        }
        $arr = json_decode($str);
        if (!isset($arr->openid)) {
            d($arr->openid);
            throw new Exception('no open id');
        }
        return $this->openid = $arr->openid; // 获取openid
    }

    public function getInfo() {
        if (empty($this->access_token)) {
            $this->access_token = $this->getAccessToken($_GET['code']);
        }
        $url = 'https://graph.qq.com/user/get_user_info?'.implode('&', array(
            'access_token='.$this->access_token,
            'oauth_consumer_key='.$this->app_id,
            'openid='.$this->openid
        ));
        $response = file_get_contents($url);
        $arr = json_decode($response); // 貌似这里还可以加图片头像 ???
        return array(
            'name'=>$arr->nickname,
            'avatar'=>$arr->figureurl
        );
    }
}

?>
