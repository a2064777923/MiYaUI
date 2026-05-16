<div id="Onecad_vips" class="">
    <div class="one-home-homevip" style="background-image: url(<?php echo b2_get_option('Jitheme_user_main','index_onecad_vip_img') ?>);">
        <div class="one-container  wrapper">
    <?php
        $html='';
        $style='';
        $tj_vip =b2_get_option('Jitheme_user_main','index_onecad_vip_tj');
        $html.='<div class="one-home-title">
                <span>'.b2_get_option('Jitheme_user_main','index_onecad_vip_title').'</span>
                <p>'.b2_get_option('Jitheme_user_main','index_onecad_vip_desc').'</p>
            </div>
            <div class="one-home-homevip-box one-grid-ceosmls one-grid ">';
            $index_vip = b2_get_option('normal_user','user_vip_group');
            if(is_array($index_vip)){
                foreach ($index_vip as $k => $v) {
                    $more_vip='';
                    $vip_name='';
                    $time='';
                    $box='';
                    $rili='';
                    $rilit='';
                    if($v['time'] != 0){
                        $vip_name='vip'.$k;
                        $rili=$v['time'];
                        $rilit='天';
                    }else{
                        $vip_name='vip'.$k;
                        $time='永久';
                        $rilit='永久';
                    }
                    if($v['time'] == 365) {
                        $rilit='年';
                        $rili='';
                    }
                    if($vip_name == $tj_vip ){
                        $tj_vip_css='<div class="vip_tj">'.b2_get_option('Jitheme_user_main','index_onecad_vip_tj_title').'</div>';
                        $border='var(--b2color)';
                        $box='jitheme_box';
                    }else {
                        $tj_vip_css='<div class=""></div>';
                        $border='var(--main-bg-color)';
                    }
                    //自定义权限截取
                    if(!empty($v['more'])){
                        $str = trim($v['more'], "\t\n\r\0\x0B\xC2\xA0");
                        $more=array(
                        'more' => explode(PHP_EOL, $str),
                    	); 
                        foreach ($more['more'] as $user) {
                                $array=explode('|', $user);
                                if($array[1] == 1){
                                    $vip_ico='<i class="b2font b2-check-line"></i>';
                                }else{
                                    $vip_ico='<i class="b2font b2-close-line"></i>';
                                }
                                $more_vip .='<li>'.$array[0].':<em><span>'.$vip_ico.'</em></li>';
                        }
                    }
                    $html.='<div id="onecad-id-'.$v['time'].'" class="one-width-1-1 one-width-1 b2-radius">
                        <div class="home-homevip-boxmk one-dongtai one-background-default b2-radius '.$box.'">
                           '.$tj_vip_css.' 
                            <img src=" '.B2_CHILD_URI.'/Center/Assets/images/'.$v['name'].'.svg">
                            <div class="home-homevip-boxmktitle b-b">
                                <div class="price">¥<strong>'.$v['price'].'</strong>/'.$rili.$rilit.'</div>
                                <p>'.$v['name'].'</p>
                            </div>
                            <div class="home-homevip-boxmks">
                                <div class="onecad_vips_title"><span>会员权益</span></div>
                                <li>每天可下载资源:<em><span>'.$v['allow_download_count'].'</span>个</em></li> 
                                <li>会员享特权期限:<em><span>'.$rili.'</span>'.$rilit.'</em></li>  
                                '.$more_vip.'
                            </div>
                            <a href="/vips" class="jitheme-jb-btn">更多权益</a>
                        </div>
                    </div>';
                }
            }
            $html.='</div>   
        </div>';
        echo $html;
    ?> 
    </div>
</div>