<?php

class XMW_OUT{
    
    public function __construct(){
        
        add_filter('b2_oauth_types_arg', array($this, 'xmw_oauth_types_arg'));
        
        add_action('b2_content_before', array($this, 'xmw_content_before'));
        
        add_action('rest_api_init', array($this, 'rest_api_init'));
        
        add_filter('b2_vue_html_login', array($this, 'xmw_vue_html_login'),10, 4);
        
        add_action('wp_enqueue_scripts', array($this, 'xmw_tone_front_slow'), 11);
        
        add_action('init', array($this, 'xmw_execution_database'));
        
    }
    
    public function xmw_execution_database(){
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'xmw_wechat_login';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `$table_name` (
                    `id` INT NOT NULL AUTO_INCREMENT , 
                    `code` INT NOT NULL , 
                    `openid` VARCHAR(255) NOT NULL , 
                    `time` INT NOT NULL , 
                    PRIMARY KEY (`id`)
                    )";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    
    public function xmw_tone_front_slow(){
        wp_enqueue_script('xmw_wechat_login',  XMW_WECHAT_LOGIN_URL. '/Assets/js/xmw_wechat_login.js', array(), false, true);
    }
    
    public function xmw_oauth_types_arg($normal){
         $normal['wechat_dyh'] = array(
                'type' => 'wechat_dyh',
    			'name'=>ucfirst('微信'),
    			'icon'=>'b2-weixin',
    			'color'=>'#7BB32E',
    			'url'=>'javascript:void(0)',
    			'open'=>true
    		);
        
        return $normal;
    }
    
    public function xmw_vue_html_login($login_html,$site_terms,$site_privacy,$allow_regeister){
        
        return '<div :class="[\'modal\',{\'show-modal\':show}]" v-cloak>
            <div class="modal-content login-box-content b2-radius">
                <div class="box login-box-top">
                    <span class="close-button" @click="close(0)">×</span>
                    '.\B2\Modules\Templates\VueTemplates::get_logo().'
                    <form @submit.stop.prevent="loginSubmit">

                        <div class="invitation-box" v-show="invitation != 0 && (loginType == 2 && !invitationPass)">
                            '.b2_get_icon('b2-gift-2-line').'
                            <p class="invitation-des">'.__('使用邀请码，您将获得一份特殊的礼物！', 'b2').'</p>
                            <p class="invitation-tips">'.__('请输入邀请码', 'b2').'</p>
                            <div class="invitation-input"><input type="text" id="invitation-code2" name="invitation_code" v-model="data.invitation_code" autocomplete="off"></div>
                            <div class="invitation-button">
                                <div><a :href="invitationLink" target="__blank">{{invitationText}}</a></div>
                                <div>
                                    <b class="empty text button" v-show="invitation == 1" @click.stop.prevent="invitationPass = true;showLuo = true">'.__('跳过','b2').'</b>
                                    <button :class="[\'button\',{\'b2-loading\':locked}]" :disabled="locked" >'.__('提交','b2').'</button>
                                </div>
                            </div>
                        </div>
                        <div class="login-box-in" v-show="!(invitation != 0 && (loginType == 2 && !invitationPass))">
                            <div class="login-title">
                                <span v-if="loginType == 1"><b class="repass-tips" v-if="repass">'.__('密码修改成功，请登录', 'b2').'</b><b v-else>'.__('登录', 'b2').'</b></span>
                                <template v-else-if="allowRegister == 1 && loginType == 2">
                                    <span>'.__('注册', 'b2').'</span>
                                </template>
                                <span v-else-if="loginType == 3">'.__('找回密码', 'b2').'</span>
                                <span v-else-if="loginType == 4">'.__('请输入您的新密码', 'b2').'</span>
                            </div>

                            <label class="login-form-item" v-show="loginType == 2">
                                <input type="text" name="nickname" tabindex="1" v-model="data.nickname" :class="data.nickname ? \'active\' : \'\'" spellcheck="false" autocomplete="off">
                                <span><b>'.__('可爱的昵称', 'b2').'</b></span>
                            </label>

                            <label class="login-form-item" v-show="loginType != 4">
                                <input type="text" name="username" tabindex="2" v-model="data.username" :class="data.username ? \'active\' : \'\'" spellcheck="false" autocomplete="off">
                                <span v-if="loginType == 3"><b>'.__('登录手机号或邮箱', 'b2').'</b></span>
                                <span v-else><b>'.__('登录','b2').'{{loginText}}</b></span>
                                <p class="login-box-des" v-show="loginType == 2 && (checkType == \'luo\' || checkType == \'text\')">'.__('用作登录：字母或数字的组合，最少6位字符','b2').'</p>
                                <p class="login-box-des" v-show="loginType == 2 && (checkType != \'luo\' && checkType != \'text\')">'.__('用作登录','b2').'</p>
                            </label>

                            <label class="login-form-item" v-show="checkType == \'luo\' && showLuo && loginType != 3 && loginType != 4">
                                <div class="check-code-luo"><div class="l-captcha" data-site-key="'.b2_get_option('normal_login','site_key').'" data-width="100%" data-callback="getResponse"></div></div>
                            </label>

                            <label :class="[\'login-form-item login-check-input\',{\'show\':(((loginType == 2 || loginType == 3) && data.username && checkType != \'luo\') || (checkType == \'luo\' && loginType == 3))}]">
                                <input type="text" name="checkCode" tabindex="3" v-model="data.img_code" :class="data.img_code ? \'active\' : \'\'" spellcheck="false" v-if="checkType == \'text\' && loginType != 3" autocomplete="off">
                                <input type="text" name="checkCode" tabindex="3" v-model="data.code" :class="data.code ? \'active\' : \'\'" spellcheck="false" autocomplete="off" v-else>
                                <span><b>'.__('验证码', 'b2').'</b></span>
                                <div class="check-code-img" v-if="checkType == \'text\' && loginType != 3" @click="changeCode">
                                    <img :src="codeImg" v-if="codeImg"/>
                                    <i class="recaptcha-load" v-else></i>
                                </div>
                                <b class="login-eye button text" @click.stop.prevent="!SMSLocked && count == 60 ? sendCode() : \'\'" v-else>{{count < 60 ? count+\''.__('秒后可重发', 'b2').'\' : \''.__('发送验证码', 'b2').'\'}}</b>
                            </label>

                            <label class="login-form-item" v-show="loginType != 3">
                                <input name="password" :type="eye ? \'text\' : \'password\'" tabindex="4" v-model="data.password" :class="data.password ? \'active\' : \'\'" autocomplete="off" spellcheck="false">
                                <span><b v-if="loginType == 4">'.__('新密码', 'b2').'</b><b v-else>'.__('密码', 'b2').'</b></span>
                                <b class="login-eye button text" @click.stop.prevent="eye = !eye"><i :class="[\'b2font\',eye ? \'b2-eye-fill\' : \'b2-eye-off-fill\']"></i></b>
                            </label>

                            <label class="login-form-item" v-show="loginType == 4">
                                <input name="repassword" :type="eye ? \'text\' : \'password\'" tabindex="5" v-model="data.confirmPassword" :class="data.confirmPassword ? \'active\' : \'\'" autocomplete="off" spellcheck="false">
                                <span><b>'.__('重复新密码', 'b2').'</b></span>
                                <p class="login-box-des" v-show="loginType == 4">'.__('最少6位字符').'</p>
                            </label>

                            <div class="forget-pass-info" v-if="loginType == 3">'.__('请填写您注册时使用的手机号码或邮箱，发送验证码以后，请在手机号码或邮箱中查看，并填写到此处！（验证码3分钟有效期）', 'b2').'</div>
                            <div class="site-terms" v-if="loginType == 2 && allowRegister == 1">
                                <label><input type="checkbox" name="xieyi" v-model="$store.state.xieyi"/>'.sprintf(__('我已同意 %s用户协议%s 和 %s隐私政策%s','b2'),'<a href="'.$site_terms.'" target="_blank">','</a>','<a href="'.$site_privacy.'" target="_blank">','</a>').'</label>
                            </div>
                            <div class="login-bottom">
                                <button v-if="loginType == 1" :class="locked ? \'b2-loading\' : \'\'" :disabled="locked">'.__('快速登录', 'b2').'</button>
                                <button :class="locked ? \'b2-loading\' : \'\'" v-if="loginType == 2 && allowRegister == 1" :disabled="locked || !$store.state.xieyi">'.__('快速注册', 'b2').'</button>
                                <button v-if="loginType == 3" :class="locked ? \'b2-loading\' : \'\'" :disabled="locked">'.__('下一步', 'b2').'</button>
                                <button v-if="loginType == 4" :class="locked ? \'b2-loading\' : \'\'" :disabled="locked">'.__('提交', 'b2').'</button>
                            </div>
                            <div :class="loginType == 3 || loginType == 4 || (invitationPass && loginType == 2) ? \'login-tk-forget login-tk\' : \'login-tk\'">
                                <p v-if="loginType == 4"><a href="javascript:void(0)" @click="loginAc(3)">'.__('返回修改','b2').'</a></p>
                                <p v-if="loginType == 2 && invitationPass">'.__('邀请码错了？', 'b2').'<a href="javascript:void(0)" @click="invitationPass = false;showLuo = false">'.__('修改','b2').'</a></p>
                                <p class="login-p" v-if="(loginType == 1 || loginType == 3) && allowRegister == 1"><a v-if="loginType == 1" href="javascript:void(0)" @click="loginAc(3)">'.__('忘记密码？', 'b2').'</a><span>'.__('新用户？', 'b2').'<a href="javascript:void(0)" @click="loginAc(2)">'.__('注册', 'b2').'</a></span></p>
                                <p v-if="loginType == 2 || loginType == 3 || loginType == 4"><a v-if="loginType == 2" href="javascript:void(0)" @click="loginAc(3)">'.__('忘记密码？', 'b2').'</a><span>'.__('已有帐号？', 'b2').'<a href="javascript:void(0)" @click="loginAc(1)">'.__('登录', 'b2').'</a></span></p>
                            </div>
                        </div>
                        <div :class="\'login-social-button \'+(openOauth ? \'show\' : \'\')" v-if="!(invitation != 0 && (loginType == 2 && !invitationPass)) && openOauth">
                            <div :class="[\'login-social-button-bottom\',{\'is-weixin\':isWeixin}]">
                                <div>'.__('社交登录:','b2').'</div>
                                <div>
                                    <template v-for="(open,key,index) in oauth">
                                    
                                        <a v-if="open.type===\'wechat_dyh\' && open.open" :href="open.url" :class="\'button login-\'+key" @click="xmw_wechat_login.close()" :style="\'color:\'+open.color"><i :class="\'b2font b2-\'+key+\' \'+open.icon"></i><span>{{open.name}}</span></a>

                                        <a v-else-if="open.open" :href="open.url" :class="\'button login-\'+key" @click="markHistory(open.mp,$event)"  :style="\'color:\'+open.color"><i :class="\'b2font b2-\'+key+\' \'+open.icon"></i><span>{{open.name}}</span></a>
                                    
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>';
        
    }
    
    public function rest_api_init(){
        
        
            register_rest_route('xmw', 'getWeChatLoginData', array(
                'methods' => 'POST',
                'callback' => function ($request) {
                    
                    $code = sanitize_text_field($request['code']);
                    $res = \Wechat\XMW_FUN::getUserData($code);
                    
                    if (isset($res['error'])) {
    
                        return new \WP_Error('error', $res['error'], array('status' => 403));
                    } else {
    
                        return new \WP_REST_Response($res, 200);
                    }
                },
    
                'permission_callback' => '__return_true'
            ));
            
            
            //验证绑定code
            register_rest_route('xmw', 'yzBindCode', array(
                'methods' => 'POST',
                'callback' => function ($request) {
                    
                    $code = sanitize_text_field($request['code']);
                    $res = \Wechat\XMW_FUN::yzBindCode($code);

                    if (isset($res['error'])) {
    
                        return new \WP_Error('error', $res['error'], array('status' => 403));
                    } else {
    
                        return new \WP_REST_Response($res, 200);
                    }
                },
    
                'permission_callback' => '__return_true'
            ));
            
            
            //验证token
            register_rest_route('xmw', 'yzBindToken', array(
                'methods' => 'POST',
                'callback' => function ($request) {
                    
                    
                    $res = \Wechat\XMW_FUN::yzBindToken($request);
                    
                    if (isset($res['error'])) {
    
                        return new \WP_Error('error', $res['error'], array('status' => 403));
                    } else {
    
                        return new \WP_REST_Response($res, 200);
                    }
                },
    
                'permission_callback' => '__return_true'
            ));
        
    }
    
    public function xmw_content_before(){
    
    $wechat_or = b2_get_option('xmw_wechat_login_bulid','wechat_or');
    
    $wechat_huifu = b2_get_option('xmw_wechat_login_bulid','wechat_huifu') ?: '登陆';
    
    $html = '
        <div class="erphp-weixin-scan" style="text-align: center;">
            <img style="height: 200px;width: 200px;margin: 10px;border: 1px solid #f5f6f7;" src="'.$wechat_or.'" />
            <div class="ews-box" style="height:35px;">
                <input type="text" v-model="code" style="height: 100%;" class="ews-input" placeholder="验证码"/>
                <button type="button" :disabled="button" class="ews-button" style="font-size:unset;height:100%;" @click="post_data()">验证登录</button>
            </div>
            <div class="ews-tips">
                如已关注，请回复“'.$wechat_huifu.'”二字获取验证码
            </div>
        </div>';
    
    echo '<style>
        .erphp-weixin-scan{margin:0 auto;position:relative;max-width: 300px;}
        .erphp-weixin-scan .ews-title{text-align:center;font-size:18px;}
        .erphp-weixin-scan img{max-width: 100%;height: auto;}
        .erphp-weixin-scan .ews-box{text-align: center;}
        .erphp-weixin-scan .ews-box .ews-input{border:1px solid #eee;border-radius:3px;padding:6px 12px;width:150px;height: 35px;box-sizing: border-box;}
        .erphp-weixin-scan .ews-box .ews-button{background: #07C160;border:none;padding:7px 12px;color:#fff;border-radius: 3px;font-size:14px;cursor: pointer;height: 35px;box-sizing: border-box;}
        .erphp-weixin-scan .ews-tips{text-align:center;font-size:13px;color:#999;margin-top:10px;}
        </style>
        <div id="xmw_wechat_login" :class="[\'modal\',{\'show-modal\':show}]" v-cloak>
            <div class="modal-content search-box-content b2-radius" style="width:350px">
                <span class="close-button" @click="close()">×</span>
                <div style="align-items: center;display: flex;justify-content: center;font-size: 27px;">微信登陆</div>
                ' . $html . '
            </div>
        </div>';
    
    
    
    
    
    echo '<style>
        .erphp-weixin-scan{margin:0 auto;position:relative;max-width: 250px;text-align:center;}
        .erphp-weixin-scan .ews-title{text-align:center;font-size:18px;}
        .erphp-weixin-scan img{max-width: 100%;height: auto;margin:5px 0}
        .erphp-weixin-scan .ews-box{text-align: center;}
        .erphp-weixin-scan .ews-box .ews-input{border:1px solid #eee;border-radius:3px;padding:6px 12px;width:120px;height: 35px;box-sizing: border-box;}
        .erphp-weixin-scan .ews-box .ews-bind-button{background: #07C160;border:none;padding:7px 12px;color:#fff;border-radius: 3px;font-size:14px;cursor: pointer;height: 35px;box-sizing: border-box;}
        .erphp-weixin-scan .ews-tips{text-align:center;font-size:13px;color:#999;margin-top:10px;}
        </style>
        <div id="xmw_wechat_bind" :class="[\'modal\',{\'show-modal\':show}]" v-cloak>
            <div class="modal-content search-box-content b2-radius" style="width:350px">
                <span class="close-button" @click="close(2)">×</span>
                <div style="align-items: center;display: flex;justify-content: center;font-size: 27px;">绑定微信</div>
                
                
<div class="login-box-in">

                <div v-if="!token" class="erphp-weixin-scan">
                    <img style="height: 200px;width: 200px;margin: 10px;border: 1px solid #f5f6f7;" src="'.$wechat_or.'" />
                    <div class="ews-box">
                        <input type="text" v-model="code" class="ews-input" placeholder="验证码"/>
                        <button type="button" class="ews-bind-button" @click="yz_bind()">验证绑定</button>
                    </div>
                    <div class="ews-tips">
                    如已关注，请回复“绑定”二字获取验证码
                    </div>
                </div>
                
                
<div v-if="token">

<div style="text-align: center;
    display: flex;
    flex-direction: column;
    margin-top: 10px;
    margin-bottom: 30px;">

    <span class="green" style="font-size: 15px;
    font-weight: 800;">已完成微信验证</span>
    <span>请在<b class="red" v-text="remainingTime"></b>秒内继续输入您的账号密码以继续绑定您的账号</span>

</div>



<label class="login-form-item">
<input type="text" name="username" v-model="username" tabindex="2" spellcheck="false" autocomplete="off" class="">
<span>
<b>登录用户名</b>
</span>
<p class="login-box-des">请输入账号</p>
</label>

<label class="login-form-item">
<input name="password" v-model="password" tabindex="4" autocomplete="off" spellcheck="false" type="password" class="">
<span><b>密码</b></span>
<p class="login-box-des">请输入密码</p>

<b class="login-eye button text">
<i class="b2font b2-eye-off-fill"></i></b>

</label>
<div class="login-bottom">
    <div style="display:flex;">
        <button style="margin:0 10px 0 0;background-color:#c0c0c0;border: 1px solid #c0c0c0;" @click="cancel()">取消</button>
        <button style="margin:0 0 0 10px;background: green;border: 1px solid green;" @click="yzBindToken(\'cjxzh\')">创建新账号</button>
    </div>
    <button class="login-bottom" @click="yzBindToken()">绑定并登陆</button>
</div>
</div>
                
</div>
                
                
                
                
            </div>
        </div>';

    }
    
    
}

new XMW_OUT();