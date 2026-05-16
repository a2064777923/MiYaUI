<?php namespace B2APP\common;

class restApi{

    public $m;

    public function init(){
        add_action( 'rest_api_init', array($this,'rest_regeister'));
        $this->m = new \b2methods();
    }

    public function rest_regeister(){
        register_rest_route('b2/v1/app','/getDefaultData2',array(
            'methods'=>'post',
            'callback'=>array($this,'getDefaultData'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getDefaultData',array(
            'methods'=>'get',
            'callback'=>array($this,'getDefaultData'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/setCid',array(
            'methods'=>'post',
            'callback'=>array($this,'setCid'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getIndex',array(
            'methods'=>'post',
            'callback'=>array($this,'getIndex'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getPostList',array(
            'methods'=>'post',
            'callback'=>array($this,'getPostList'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getAllTerms',array(
            'methods'=>'get',
            'callback'=>array($this,'getAllTerms'),
            'permission_callback' => '__return_true'
        ));
        register_rest_route('b2/v1/app','/getPost',array(
            'methods'=>'post',
            'callback'=>array($this,'getPost'),
            'permission_callback' => '__return_true'
        ));
        register_rest_route('b2/v1/app','/getNews',array(
            'methods'=>'get',
            'callback'=>array($this,'getNews'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('b2/v1/app','/getCircleIndex',array(
            'methods'=>'post',
            'callback'=>array($this,'getCircleIndex'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app', '/getTopic', array(
            'methods'=>'post',
            'callback'=>array($this,'getTopic'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app', '/getMyCircles', array(
            'methods'=>'get',
            'callback'=>array($this,'getMyCircles'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app', '/getCircleCategory', array(
            'methods'=>'post',
            'callback'=>array($this,'getCircleCategory'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app', '/getCreateCircleData', array(
            'methods'=>'get',
            'callback'=>array($this,'getCreateCircleData'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app', '/searchGoods', array(
            'methods'=>'post',
            'callback'=>array($this,'searchGoods'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getNewsflashesIndex',array(
            'methods'=>'post',
            'callback'=>array($this,'getNewsflashesIndex'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getNewsflashesSingle',array(
            'methods'=>'post',
            'callback'=>array($this,'getNewsflashesSingle'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getShopIndex',array(
            'methods'=>'post',
            'callback'=>array($this,'getShopIndex'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/collectionIndex',array(
            'methods'=>'post',
            'callback'=>array($this,'collectionIndex'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/collectionCategory',array(
            'methods'=>'post',
            'callback'=>array($this,'collectionCategory'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getAnnouncements',array(
            'methods'=>'post',
            'callback'=>array($this,'getAnnouncements'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getAnnouncement',array(
            'methods'=>'post',
            'callback'=>array($this,'getAnnouncement'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/documentIndex',array(
            'methods'=>'post',
            'callback'=>array($this,'documentIndex'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/documentSingle',array(
            'methods'=>'post',
            'callback'=>array($this,'documentSingle'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('b2/v1/app','/getShopSingle',array(
            'methods'=>'post',
            'callback'=>array($this,'getShopSingle'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/socialLogin',array(
            'methods'=>'post',
            'callback'=>array($this,'socialLogin'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/phoneLogin',array(
            'methods'=>'post',
            'callback'=>array($this,'phoneLogin'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getMPweixinOpenid',array(
            'methods'=>'post',
            'callback'=>array($this,'getMPweixinOpenid'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getAppVersion',array(
            'methods'=>'post',
            'callback'=>array($this,'getAppVersion'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getShopList',array(
            'methods'=>'post',
            'callback'=>array($this,'getShopList'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getAbout',array(
            'methods'=>'get',
            'callback'=>array($this,'getAbout'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/appleLogin',array(
            'methods'=>'post',
            'callback'=>array($this,'appleLogin'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getInfomationSingle',array(
            'methods'=>'post',
            'callback'=>array($this,'getInfomationSingle'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getInfomationCats',array(
            'methods'=>'post',
            'callback'=>array($this,'getInfomationCats'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getInfomationIndex',array(
            'methods'=>'post',
            'callback'=>array($this,'getInfomationIndex'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getBaiduOpenId',array(
            'methods'=>'post',
            'callback'=>array($this,'getBaiduOpenId'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/baiduLogin',array(
            'methods'=>'post',
            'callback'=>array($this,'baiduLogin'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/baiduInvregister',array(
            'methods'=>'post',
            'callback'=>array($this,'baiduInvregister'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/getUserFavorites',array(
            'methods'=>'post',
            'callback'=>array($this,'getUserFavorites'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('b2/v1/app','/jili',array(
            'methods'=>'post',
            'callback'=>array($this,'jili'),
            'permission_callback' => '__return_true'
        ));

    }

    public function jili($request){
        $res = $this->m::jili($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getUserFavorites($request){
        $res = $this->m::get_user_favorite($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getInfomationCats($request){
        $res = $this->m::get_infomation_cats($request->get_params());

        return $this->restApiReturn($res);
    }

    public function baiduInvregister($request){
        $res = $this->m::mp_baidu_invregister($request->get_params());

        return $this->restApiReturn($res);
    }

    public function baiduLogin($request){
        $res = $this->m::mp_baidu_login($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getBaiduOpenId($request){
        $res = $this->m::get_baidu_open_id($request['code']);

        return $this->restApiReturn($res);
    }

    public function getInfomationIndex($request){
        $res = $this->m::get_infomation_index($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getInfomationSingle($request){
        $res = $this->m::get_infomation_single($request->get_params());

        return $this->restApiReturn($res);
    }

    public function appleLogin($request){
        $res = $this->m::apple_login($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getAbout($request){
        $res = $this->m::get_about();

        return $this->restApiReturn($res);
    }

    public function getShopList($request){
        $res = $this->m::get_shop_list($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getMPweixinOpenid($request){
        $save = isset($request['save']) ? $request['save'] : '';
        $type = isset($request['type']) ? $request['type'] : '';
        $res = $this->m::mp_weixin_get_openid($request['code'],$save, $type);

        return $this->restApiReturn($res);
    }

    public function socialLogin($request){

        $res = $this->m::social_login($request->get_params());

        return $this->restApiReturn($res);
    }

    /**
     * 获取设置项
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function getTopic($request){
        $res = $this->m::get_topic($request['id']);

        return $this->restApiReturn($res);
    }

    public function setCid($request){
        $res = $this->m::setCid($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getDefaultData(){

        $res = $this->m::getDefaultData();

        return $this->restApiReturn($res);
    }

    public function getAnnouncement($request){
        $res = $this->m::get_announcement_info($request['post_id']);

        return $this->restApiReturn($res);
    }

    public function getAnnouncements($request){
        $res = $this->m::get_announcements($request);

        return $this->restApiReturn($res);
    }

    public function collectionIndex($request){
        $res = $this->m::collection_index($request);

        return $this->restApiReturn($res);
    }

    public function collectionCategory($request){
        $res = $this->m::collection_category($request);

        return $this->restApiReturn($res);
    }

    public function getShopIndex(){
        $res = $this->m::get_shop_index();

        return $this->restApiReturn($res);
    }

    public function getIndex(){
        $res = $this->m::getIndex();

        return $this->restApiReturn($res);
    }

    public function getPostList($request){
        $posts = $this->m::getpost($request->get_params());

        if(isset($posts['error'])){
           return new \WP_Error('b2_app_error','无法获取文章',array('status'=>403));
        }

        $term = get_term_by('id', $request['post_cat'], 'category');

        $data = [];

        if(!is_wp_error($term)){
            $data = array(
                'id'=>$request['post_cat'],
                'thumb'=>b2_get_thumb(array('thumb'=>get_term_meta($request['post_cat'],'b2_tax_img',true),'width'=>600,'height'=>200)),
                'name'=>isset($term->name) ? $term->name : '',
                'color'=>get_term_meta($request['post_cat'],'b2_tax_color',true),
                'seo'=>array(
                    'title'=>get_term_meta($request['post_cat'],'seo_title',true),
                    'keywords'=>get_term_meta($request['post_cat'],'seo_keywords',true),
                    'desc'=>get_term_meta($request['post_cat'],'description',true)
                )
            );
        }else{
            return new \WP_Error('b2_app_error','错误的分类法',array('status'=>403));
        }
        
        return array(
            'posts'=>$posts,
            'cat'=>$data
        );
    }

    public function getAllTerms(){
        $res = $this->m::getAllTerms();

        return $this->restApiReturn($res);
    }

    public function restApiReturn($res){
        if(isset($res['error'])){
            return new \WP_Error('b2_app_error',$res['error'],array('status'=>403));
        }else{
            return new \WP_REST_Response($res,200);
        } 
    }

    public function getNewsflashesIndex($request){
        $res = $this->m::get_newsflashes_index($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getNewsflashesSingle($request){
        $res = $this->m::getnewsflashesSingle($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getPost($request){

        $res = $this->m::get_post_data($request['postId']);

        return $this->restApiReturn($res);
    }

    public function getNews($request){
        $res = $this->m::getNews($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getCircleIndex($request){
        $res = $this->m::get_circle_index($request->get_params());

        return $this->restApiReturn($res);
    }

    public function searchGoods($request){
        $res = $this->m::search_goods($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getCircleCategory($request){
        $res = $this->m::get_circle_category($request->get_params());

        return $this->restApiReturn($res);
    }
    
    public function getMyCircles($request){
        $res = $this->m::get_my_circles($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getCreateCircleData(){
        $res = $this->m::get_create_circle_data();

        return $this->restApiReturn($res);
    }

    public function documentIndex($request){
        $res = $this->m::get_document_index($request->get_params());

        return $this->restApiReturn($res);
    }

    public function documentSingle($request){
        $res = $this->m::get_document_single($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getShopSingle($request){
        $res = $this->m::get_shop_single($request->get_params());

        return $this->restApiReturn($res);
    }

    public function phoneLogin($request){
        $res = $this->m::phone_Login($request->get_params());

        return $this->restApiReturn($res);
    }

    public function getAppVersion($request){
        $res = $this->m::getAppVersion($request->get_params());

        return $this->restApiReturn($res);
    }
}