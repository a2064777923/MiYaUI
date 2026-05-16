<?php
/**
 * vip页面，极主题https://www.jitheme.com/
 */
get_header();
?>
<div id="vips" v-cloak>
    <div  id="Onecad_vips" style="background:url(<?php echo b2_get_option('Jitheme_user_main','onecad_vips_img'); ?>) center top no-repeat;">
        <div class="vip-top">
            <div class="Onecad_vip_top">
                <div class="Onecad_vip_max Onecad-container">
                        <div class="Onecad_vip_logo">
                            <a rel="home" href="./">
                                <img itemprop="logo" src="https://www.miyaui.com/wp-content/uploads/2024/07/2024071502582648.png">
                            </a>
                        </div>
                    <h2>
                    <?php echo b2_get_option('Jitheme_user_main','index_onecad_vip_desc')?>
                    </h2>
                    <?php echo Onecad_vips_top() ?>
                </div>
                    <div class="Onecad_vip_pd" style="margin-top: 35px; line-height: 25px;" v-if="data.user">
                        <span v-if="data.user == 'guest'">
                            <?php echo __('游客！您好，欢迎您来到本网站','b2'); ?>
                        </span>
                        <span v-else-if="!data.user.vip.lv">
                            <?php echo __('您当前为普通用户，推荐升级成高级会员！','b2'); ?>
                        </span>
                        <div v-else>
                             <?php echo sprintf(__('您当前为%s用户，有效期至：%s','b2'),'<span v-html="data.user.vip.icon"></span>','<span v-text="data.user.time === \'long\' ? \''.__('终身','b2').'\' : data.user.time"></span>'); ?>
                        </div>
                    </div>
            </div>   
        </div>
        <div id="Jitheme_vips_cs2"  class="vip-page wrapper">
            <main id="main" class="site-main " ref="vips">
                <div class="vip-list">
                    <div :id="'onecad-id-'+ item.time" class="vip-item" v-for="(item,index) in data.data">
                        
                        <div  class="home-homevip-boxmk one-dongtai one-background-default b2-radius" >
                            <div v-if="item.vip_tj == '1'" class="tag">推荐购买</div>
                            <div class="choose-vip-item-top ">
                                <img :src="('<?php echo B2_CHILD_URI; ?>/Center/Assets/images/'+ item.name +'.png')">
                                <div class="vip-item-top-name">
                                    <p class="vip-name"  v-text="item.name" :style="'color:'+item.color"></p>
                                </div>
                            </div>
                            
                            <div class="home-homevip-boxmktitle b-b">
                            <a class="vip-buy vip-btn"><button @click="join('vip'+index,item.name,item.price)"><span v-if="item.allow_buy === false"><?php echo __('续签会员','b2'); ?></span><span v-else><?php echo __('立刻加入','b2'); ?></span><span class="buyOne-getOne"><?php echo B2_MONEY_SYMBOL; ?><span  v-text="item.price"></span></span></button>
                                </span></a>
                                <!--<div class="onecad_vips_tj"><?php echo __('已加入','b2'); ?><b>{{parseInt(item.count)}}</b><?php echo __('人','b2'); ?></div>-->
                            </div>
                            <div class="home-homevip-boxmks">
                                <div class="onecad_vips_title"><span>会员权益:</span></div>
                                <li><?php echo __('免费下载资源','b2'); ?>:<em><span>{{item.allow_download_count}}</span><?php echo __('次/天','b2'); ?></em></li>
                                <li>会员享特权期限:<em><span v-if="item.time > 0">{{item.time}}<p><?php echo __('天','b2'); ?></p></span><span v-else><?php echo __('终身','b2'); ?></span></em></li>
                                <li :class="['vip-allow',{'allow':item.allow_read === '1'}]">
                                    <?php echo __('查看所有隐藏内容','b2'); ?>
                                    <span v-if="item.allow_read === '1'"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                    <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                </li>
                                <li :class="[{'allow':item.allow_videos === '1'}]">
                                    <?php echo __('免费查看所有付费视频','b2'); ?>
                                    <span v-if="item.allow_videos === '1'"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                    <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                </li>
                                <!--<li v-for="role in item.user_role" :class="['vip-allow',{'allow':role.allow}]">-->
                                <!--    {{role.name}}-->
                                <!--    <span v-if="role.allow"><?php echo b2_get_icon('b2-check-line'); ?></span>-->
                                <!--    <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>-->
                                <!--</li>-->
                                <li class="vip-allow allow">
                                    <?php echo __('享受专属打折商品','b2'); ?>
                                    <span><?php echo b2_get_icon('b2-check-line'); ?></span>
                                </li>
                                <li :class="['vip-allow',{'allow':m.role == 1}]" v-if="item.more.length > 0" v-for="(m,_i) in item.more" :key="_i">
                                    {{m.text}}
                                    <span v-if="m.role == 1"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                    <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                    
                                </li>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="Onecad-vip2-qy">
                    <?php echo Onecad_vips_quanyi() ?>
                </div>
                <div class="vip-footer">
                    <div class="vip2-qy-title">
                        <h2><?php echo __('常见问题','b2'); ?></h2>
                        <p><?php echo __('FAQ','b2'); ?></p>
                    </div>
                       <?php echo  Onecad_vips_wenti() ?>
                </div>
            </main>
        </div>
    </div>
</div>
<?php
get_footer();