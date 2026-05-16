<?php

namespace Wechat;
use \Firebase\JWT\JWT;

class XMW_FUN{

    private static function getWeChatData(){
        
        $WeChatOp = array(
            'appid'             =>  b2_get_option('xmw_wechat_login_bulid','appid'),
            'appsecret'         =>  b2_get_option('xmw_wechat_login_bulid','appsecret'),
            'token'             =>  b2_get_option('xmw_wechat_login_bulid','token'),
        );
        
        return new \Wechat\Lib\Common($WeChatOp);
        
    }

    // 公众号-认证
    public static function valid(){
        $rz = self::getWeChatData();
        $rz->valid();
    }
    
    // 公众号-获取消息内容
    public static function WechatReceive(){
        
        // $data = self::getWeChatData();
        // $AccessToken = $data->getAccessToken();
        
        $scvasc = new \Wechat\WechatReceive();
        
        $scvasc->getRev();//获取消息内容
        
        $getRevType = $scvasc->getRevType();//获取消息类型
        
        $sxx = $scvasc->getRevData();//
        
        $openid = $scvasc->getRevFrom();//获取用户的openid
        
        $wechat_huifu = b2_get_option('xmw_wechat_login_bulid','wechat_huifu') ?: '登陆';

        if($sxx['Content'] == $wechat_huifu || $sxx['Content'] == '绑定'){
            
            //将 token 存入缓存，防止重复提交，
            if(wp_using_ext_object_cache() && $openid){
                $isset_token = wp_cache_get(md5($openid.'1'));
                if($isset_token){
                    $duix = $scvasc->text('验证码已发送，请五分后再重新获取');
                    $scvasc->reply();
                    return;
                }
            }
            
            //缓存token，防止重复注册
            if(wp_using_ext_object_cache() && $openid){
                wp_cache_add(md5($openid.'1'),'1','',300);
            }
            
            // $sss = $this->access_token;
            // $get_user_data = new \Wechat\WechatUser();
            // $duix = $scvasc->text(json_encode($get_user_data->getUserInfo($openid)));
            
            $duix = $scvasc->text(self::binding_data($openid));
            $scvasc->reply();
            
        }
        
    }
    
    
    private static function binding_data($openid){
        
        $code = rand(100000,999999);

        $bd = self::bd($code, $openid);
        
        if($bd){
            
            $content = "验证码：{$code}，有效期5分钟。";
            
        }else{
            
            $content = "验证码处理失败";
        }
        
        return $content;
    
    }
    

    private static function bd($code, $openid){
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'xmw_wechat_login';
        
        date_default_timezone_set('Asia/Shanghai');
        
        $data = array(
            'code' => $code,
            'openid' => $openid,
            'time' => time(),
        );

        $insert = $wpdb->insert(
            $table_name,
            $data,
            array(
                '%d', //code 
                '%s', //openid
                '%s',//time
            )
        );

        return $wpdb->insert_id;
    }
    
    
    public static function getUserData($code){
        
        if(strlen((string)$code) != 6) return array('error' => '验证码输入有误');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'xmw_wechat_login';
        
        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `code`= %d",$code),
            ARRAY_A
        );

        if(!empty($res)){
            
            $res = $res[0];
            
            $code_time = $res['time'];
            
            //验证完成之后删除该验证码
            $wpdb->query("DELETE FROM $table_name WHERE `code` = '$code'");
            wp_cache_delete(md5($openid.'1'));
            
            if(self::isTimestampValid($code_time)){
            
                $openid = $res['openid'];
 
                $user_id = self::get_user_id_by_meta('xmw_wechat_openid', $openid);

                if($user_id){
                    // 执行登陆
                    return \B2\Modules\Common\OAuth::user_login($user_id);
                    
                }else{
                    
                    //对验证码和手机号码进行加密
                    $issuedAt = time();
                    $expire = $issuedAt + 300;//5分钟时效
            
                    $token = array(
                        "iss" => B2_HOME_URI,
                        "iat" => $issuedAt,
                        "nbf" => $issuedAt,
                        'exp'=>$expire,
                        'data'=>array(
                            'openid'=>$openid,
                        )
                    );
            
                    $jwt = JWT::encode($token, AUTH_KEY);
                    
                    return array('msg' => '未绑定账号','token' => $jwt);
                }


            }else{
                
                $wpdb->query("DELETE FROM $table_name WHERE `code` = '$code'");
                
                return array('error' => '验证码已过期或失效');
            }

        }else{
            
            return array('error' => '验证码已过期或失效');
        }

    }
    
    public static function yzBindToken($request){
        
        $token = sanitize_text_field($request['token']);
        $username = sanitize_text_field($request['username']);
        $password = sanitize_text_field($request['password']);
        
        $type = sanitize_text_field($request['type']);
        
        if($type === 'cjxzh'){
            
            if(empty($username) || empty($password)) return array('error'=>'创建新账号的账号和密码不能为空');
            
        }else{
            
            if(!$type && (empty($username) || empty($password))) return array('error'=>'账号密码不能为空');
        
        }
    
            if(wp_using_ext_object_cache() && $token){
                $isset_token = wp_cache_get(md5($token.'2'));
                if($isset_token && $isset_token > 5) return array('error'=>__('请不要重复提交','b2'));
            }    
        
            try{
                //检查验证码
                $decoded = JWT::decode($token, AUTH_KEY,array('HS256'));
                
                if(!isset($decoded->data->openid)){
                    
                    return array('error'=>__('Token错误','b2'));
                    
                }else{
                    
                    //此处执行绑定操作
                    $openid = $decoded->data->openid;
                        
                    if($type === 'cjxzh'){
                        
                        if(username_exists($username)) return array('error'=>__('该用户名已存在','b2'));
                        
                       // if(count($password) < 6) return array('error'=>__('密码长度需要大于6位','b2'));
                        
                        $login_name = 'uid_'.mt_rand(1000,9999).mt_rand(1000,9999).mt_rand(1000,9999).mt_rand(1000,9999);;
                        
                        $userdata = array(
                          'user_login' => $username?:$login_name,
                          'display_name' => '新用户',
                          'user_pass' => $password
                        );
                        
                        $user_ID = wp_insert_user( $userdata );
                        
                        if(!$user_ID) return array('error'=>__('账号创建失败','b2'));

                         $up = update_user_meta($user_ID, 'xmw_wechat_openid', $openid);
                        
                        if($up){
                            
                            return array(
                                'msg' => '创建新账号成功，并绑定成功',
                                'data' => \B2\Modules\Common\OAuth::user_login($user_ID),
                            );
                            
                        }else{
                            
                            return array('error'=>__('账号创建成功，绑定微信失败','b2'));
                        }

                    }else{
                        
                        //验证账号密码
                        $result = wp_authenticate($username,$password);
                        
                        if(is_wp_error($result)){
                            return array('error'=>$result->get_error_message());
                        }
                        
                        $user_id = $result->ID;
                        
                        if(self::get_user_id_by_meta('xmw_wechat_openid', $openid)){
                            return array('error'=>'该微信已被其他账号绑定');
                        }
    
                        if(get_usermeta($user_id, 'xmw_wechat_openid')){
                            return array('error'=>'该账号已被绑定');
                        }
    
                        $up = update_user_meta($user_id, 'xmw_wechat_openid', $openid);
                        
                        if($up){
                            
                            return array(
                                'msg' => '绑定成功',
                                'data' => \B2\Modules\Common\OAuth::user_login($user_id),
                            );
                            
                        }else{
                            
                            return array('error'=>__('绑定失败','b2'));
                        }
                        
                    }

                }

            }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            
                return array('error'=>__('验证码过期失效','b2'));
                
            }catch(\Exception $e) {  //其他错误
            
                return array('error'=>__('验证码错误','b2'));
            }
        
    }
    
    private static function isTimestampValid($timestamp) {
        date_default_timezone_set('Asia/Shanghai');
        $currentTime = time();
        $expiryTime = $timestamp + (5 * 60); // 五分钟的秒数
        
        if ($currentTime <= $expiryTime) {
            return true; // 时间戳在五分钟内有效
        } else {
            return false; // 时间戳已过期
        }
    }
    
    private static function get_user_id_by_meta($meta_key,$meta_value){
        global $wpdb;
           $user=$wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value = %s", $meta_key, $meta_value ) );
        if(empty($user)){
           return false;
        }
        return $user->user_id;
    }
    
    
}