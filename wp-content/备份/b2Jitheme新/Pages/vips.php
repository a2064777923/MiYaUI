<?php
/**
 * vip页面
 */
get_header();
$jitheme_vip=b2_get_option('Jitheme_user_main','jitheme_vip_page');
$vip_h2=b2_get_option('Jitheme_user_main','index_onecad_vip_title');
$vip_title=b2_get_option('Jitheme_user_main','index_onecad_vip_tj_title');
if (!isset($vip_title) || empty($vip_title)) {
    $vip_title="推荐购买";
}
if($jitheme_vip == 'jitheme_vip0'){?>
<div id="vips" v-cloak>
    <div class="vip-top">
        <div class="content-area wrapper">
            <?php do_action('b2_vip_page_top'); ?>
            <!-- <h1><?php echo B2\Modules\Templates\Header::logo();?></h1>
            <p><?php echo B2_BLOG_DESC;?></p> -->
            <div class="vip-count">
                <ul>
                    <li v-for="(item,index) in data.count" v-if="data.data" v-cloak>
                        <div class="vip-in b2-radius box">
                            <span v-text="item.name" :style="'color:'+data.data[index].color"></span>
                            <span class="vip-in-number"><b><?php echo __('已加入','b2'); ?></b>{{parseInt(item.count)}}<b><?php echo __('人','b2'); ?></b></span>
                            <button class="empty" @click="join(item.lv,item.name,data.data[index].price)"><span v-if="data.data[index].allow_buy === false"><?php echo __('续签会员','b2'); ?></span><span v-else><?php echo __('立刻加入','b2'); ?></span></button>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="vip-current" v-if="data.user">
                <span v-if="data.user == 'guest'">
                    <?php echo __('您当前为游客，请登录以后操作','b2'); ?>
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
    <div class="content-area vip-page wrapper">
        <main id="main" class="site-main b2-radius" ref="vips">
            <div class="vip-list">
                <div class="vip-item" v-for="(item,index) in data.data">
                    <div class="vip-list-in box b2-radius">
                        <h2 v-text="item.name" :style="'color:'+item.color"></h2>
                        <div class="vip-price">
                            <div class="vip-price-money"><?php echo B2_MONEY_SYMBOL; ?><span v-text="item.price"></span></div>
                            <div class="vip-price-day">/<span v-if="item.time > 0">{{item.time}}<?php echo __('天','b2'); ?></span><span v-else><?php echo __('终身','b2'); ?></span></div>
                        </div>
                        <div class="vip-row">
                            <ul>
                                <li :class="['vip-allow',{'allow':item.allow_read === '1'}]">
                                    <div><span><?php echo __('查看所有隐藏内容','b2'); ?></span></div>
                                    <div>
                                        <span v-if="item.allow_read === '1'"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                    </div>
                                </li>
                                <li :class="['vip-allow',{'allow':item.allow_download === '1'}]">
                                    <div>
                                        <template v-if="item.allow_download === '1'">
                                            <?php echo __('免费下载所有资源','b2'); ?>
                                            <span v-if="item.allow_download_count < 9999">（{{item.allow_download_count}}<?php echo __('次/天','b2'); ?>）</span>
                                            <span v-else>（<?php echo __('无限制次数','b2'); ?>）</span>
                                        </template>
                                        <template v-else>
                                            <?php echo __('免费下载所有资源','b2'); ?>
                                        </template>
                                    </div>
                                    <div>
                                        <span v-if="item.allow_download === '1'"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                    </div>
                                </li>
                                <li :class="['vip-allow',{'allow':item.allow_videos === '1'}]">
                                    <template v-if="item.allow_videos === '1'">
                                        <div><?php echo __('免费查看所有付费视频','b2'); ?></div>
                                        <div><?php echo b2_get_icon('b2-check-line'); ?></div>
                                    </template>
                                    <template v-else>
                                        <div><?php echo __('免费查看所有付费视频','b2'); ?></div>
                                        <div><?php echo b2_get_icon('b2-close-line'); ?></div>
                                    </template>
                                </li>
                                <!--<li v-for="role in item.user_role" :class="['vip-allow',{'allow':role.allow}]">-->
                                <!--    <div><span>{{role.name}}</span></div>-->
                                <!--    <div>-->
                                <!--        <span v-if="role.allow"><?php echo b2_get_icon('b2-check-line'); ?></span>-->
                                <!--        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>-->
                                <!--    </div>-->
                                <!--</li>-->
                                <li class="vip-allow allow">
                                    <div><?php echo __('享受专属打折商品','b2'); ?></div>
                                    <div><?php echo b2_get_icon('b2-check-line'); ?></div>
                                </li>
                                <li :class="['vip-allow',{'allow':m.role == 1}]" v-if="item.more.length > 0" v-for="(m,_i) in item.more" :key="_i">
                                    <div><span v-text="m.text"></span></div>
                                    <div>
                                        <span v-if="m.role == 1"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                        <!-- :disabled="data.user.time === 'long' && item.time == 0" -->
                        <div class="vip-buy"><button @click="join('vip'+index,item.name,item.price)" ><span v-if="item.allow_buy === false"><?php echo __('续签会员','b2'); ?></span><span v-else><?php echo __('立刻加入','b2'); ?></span></button></div>
                    </div>
                </div>
            </div>
            <div class="vip-footer">
                <h2><?php echo __('常见问题','b2'); ?></h2>
                <p><?php echo __('FAQ','b2'); ?></p>
                <div class="vip-faq box">
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('开通VIP的好处？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('VIP会员根据等级在相应的有效期内享有本站所有资源免费下载资源的权力，免费查看隐藏内容的权力，免费查看视频的权力，同时本站商品还会获得打折价格，并且拥有其他特殊的权力。','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('VIP资源需要单独购买吗？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('不同的VIP拥有不同的权限，通常VIP会员拥有免费资格享受各种资源的权力，但不排除某些特殊情况。','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('VIP会员是否无限次下载资源？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('在遵守VIP会员协议前提下，VIP会员在会员有效期内可以下载所有免费和VIP资源。','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('是否可以与他人分享VIP会员账号？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('一个VIP账号仅限一个人使用，禁止与他人分享账号，一经发现做永久封号处理。','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('是否可以申请退款？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('VIP会员属于虚拟服务，付款后不能够申请退款。如付款前有任何疑问，联系站长处理','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('遇到付款失败，付款后没有生效怎么办？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('理论上来说正常付款后不会出现此类问题，但是也会有部分用户因为网络等原因导致在付款的过程中会有一些小插曲，如果出现类似问题，大可不必惊慌，本站所有支付都会生成订单，不管成功还是失败，所以如果真正遇到网络问题导致付款失败您又不知道是否成功，请查看自己的个人中心的订单管理，截图联系管理员处理。','b2'); ?></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php }else { ?>
<link rel="stylesheet" href="<?php echo B2_CHILD_URI ?>/Render/Css/vips.css"/>
<div id="vips" v-cloak>
    <div id="Onecad_vips">
     <div class="vip-top" style="background: url(<?php echo b2_get_option('Jitheme_user_main','onecad_vips_img'); ?>;background-position: center;background-repeat: no-repeat;background-size: cover;    border-bottom: 0px solid #ccc;">
                <div class="vip-header">
                    <div class="top-title">
                            <h1><?php echo $vip_h2 ?></h1>
                            
                            <?php echo Mini_vip_list() ?>
                        <div class="vip-current" v-if="data.user">
                        <span v-if="data.user == 'guest'">
                            <?php echo __('您当前为游客，请登录以后操作','b2'); ?>
                        </span>
                        <span v-else-if="!data.user.vip.lv">
                            <?php echo __('您当前为普通用户，推荐升级成高级会员！','b2'); ?>
                        </span>
                        <div  class="vip-current-list" v-else>
                            <?php echo sprintf(__('您当前为%s用户，有效期至：%s','b2'),'<span v-html="data.user.vip.icon"></span>','<span v-text="data.user.time === \'long\' ? \''.__('终身','b2').'\' : data.user.time"></span>'); ?>
                        </div>
                    </div>
                        </div>
                </div>   
            </div>
        
        <div class="vip-page wrapper">
            <main id="main" class="site-main b2-radius ji_vips" ref="vips">
            
                <?php if($jitheme_vip == 'jitheme_vip3'){ ?>
                    <div class="vip-list ji_vips_list ji_vips_magin jitheme_vip3">
                    <div :id="'onecad-id-'+ item.time" class="vip-item item.user.vip.lv" v-for="(item,index) in data.data"  v-if="data.data">
                        <div  class="home-homevip-boxmk one-dongtai one-background-default b2-radius" >
                            <img :src="('<?php echo B2_CHILD_URI; ?>/Center/Assets/images/' + item.name + '.svg')">
                            <div v-if="data.count[index].count"  class="vips_tj"> <?php echo __('已加入', 'b2'); ?>{{data.count[index].count}}<?php echo __('人', 'b2'); ?></div>
                            <div class="home-homevip-boxmktitle b-b">
                                <div class="price"><?php echo B2_MONEY_SYMBOL; ?><strong  v-text="item.price"></strong><span v-if="item.time > 0">{{item.time}}<?php echo __('天','b2'); ?></span><span v-else><?php echo __('终身','b2'); ?></span>
                                </div>
                                <p v-text="item.name" :style="'color:'+item.color"></p>
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
                            <a class="vip-buy"><button @click="join('vip'+index,item.name,item.price)"><span v-if="item.allow_buy === false"><?php echo __('续签会员','b2'); ?></span><span v-else><?php echo __('立刻加入','b2'); ?></span></button></a>
                        </div>
                    </div>
                </div>
                <?php }elseif($jitheme_vip == 'jitheme_vip1'){ ?>
                    <div class="vip-list ji_vips_list ji_vips_magin jitheme_vip1">
                    <div class="vip-item" v-for="(item,index) in data.data">
                        <div class="vip-list-in box b2-radius">
                            <div class="top">
                                <div class="tag" v-if="item.vip_tj"><?php echo __($vip_title, 'b2'); ?></div>
                                <h1 v-text="item.name" :style="'color:'+item.color"></h1>
                                <p v-if="data.count[index].count" class="tp" ><?php echo __('已加入', 'b2'); ?>{{data.count[index].count}}<?php echo __('人', 'b2'); ?></p>
                                <p class="tp" v-else><?php echo __('终身无限会员','b2'); ?></p>
                                <p class="pi" v-if="item.allow_download_count < 9999">每天可下载<span>{{item.allow_download_count}}</span><?php echo __('个VIP资源','b2'); ?></p>
                                <p class="pi"  v-else>（<?php echo __('无限制次数','b2'); ?>）</p>
                                <div class="dj">
                                    <span><?php echo B2_MONEY_SYMBOL; ?></span><strong  v-text="item.price"></strong> / <em v-if="item.time > 0">{{item.time}}<?php echo __('天','b2'); ?></em>
                                    <em v-else><?php echo __('终身','b2'); ?></em>
                                </div>
                                
                                <button class="empty" @click="join(item.lv,item.name,data.data[index].price)"><span v-if="data.data[index].allow_buy === false"><?php echo __('续签会员','b2'); ?></span><span v-else><?php echo __('立刻加入','b2'); ?></span></button>
                            </div>
                            <div class="vip-row">
                                <ul>
                                    <li :class="['vip-allow',{'allow':item.allow_read === '1'}]">
                                        <div><span><?php echo __('查看所有隐藏内容','b2'); ?></span></div>
                                        <div>
                                            <span v-if="item.allow_read === '1'"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                            <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                        </div>
                                    </li>
                                    <li :class="['vip-allow',{'allow':item.allow_download === '1'}]">
                                        <div>
                                            <template v-if="item.allow_download === '1'">
                                                <?php echo __('免费下载所有资源','b2'); ?>
                                                <span v-if="item.allow_download_count < 9999">（{{item.allow_download_count}}<?php echo __('次/天','b2'); ?>）</span>
                                                <span v-else>（<?php echo __('无限制次数','b2'); ?>）</span>
                                            </template>
                                            <template v-else>
                                                <?php echo __('免费下载所有资源','b2'); ?>
                                            </template>
                                        </div>
                                        <div>
                                            <span v-if="item.allow_download === '1'"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                            <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                        </div>
                                    </li>
                                    <li :class="['vip-allow',{'allow':item.allow_videos === '1'}]">
                                        <template v-if="item.allow_videos === '1'">
                                            <div><?php echo __('免费查看所有付费视频','b2'); ?></div>
                                            <div><?php echo b2_get_icon('b2-check-line'); ?></div>
                                        </template>
                                        <template v-else>
                                            <div><?php echo __('免费查看所有付费视频','b2'); ?></div>
                                            <div><?php echo b2_get_icon('b2-close-line'); ?></div>
                                        </template>
                                    </li>
                                    <!--<li v-for="role in item.user_role" :class="['vip-allow',{'allow':role.allow}]">-->
                                    <!--    <div><span>{{role.name}}</span></div>-->
                                    <!--    <div>-->
                                    <!--        <span v-if="role.allow"><?php echo b2_get_icon('b2-check-line'); ?></span>-->
                                    <!--        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>-->
                                    <!--    </div>-->
                                    <!--</li>-->
                                    <li class="vip-allow allow">
                                        <div><?php echo __('享受专属打折商品','b2'); ?></div>
                                        <div><?php echo b2_get_icon('b2-check-line'); ?></div>
                                    </li>
                                    <li :class="['vip-allow',{'allow':m.role == 1}]" v-if="item.more.length > 0" v-for="(m,_i) in item.more" :key="_i">
                                        <div><span v-text="m.text"></span></div>
                                        <div>
                                            <span v-if="m.role == 1"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                            <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                        </div>
                                    </li>
                                    
                                </ul>
                            </div>
                            <!-- :disabled="data.user.time === 'long' && item.time == 0" -->
                        </div>
                    </div>
                </div>
                <?php }elseif($jitheme_vip == 'jitheme_vip2'){ ?>
                    <div id="Jitheme_vips_cs2" class="vip-list ji_vips_list ji_vips_magin jitheme_vip2">
                    <div :id="'onecad-id-'+ item.time" class="vip-item" v-for="(item,index) in data.data">
                        
                        <div  class="home-homevip-boxmk one-dongtai one-background-default b2-radius" >
                            <div v-if="item.vip_tj == '1'" class="tag">推荐购买</div>
                            <div class="choose-vip-item-top ">
                                <img :src="('<?php echo B2_CHILD_URI; ?>/Center/Assets/images/'+ item.name +'.png')">
                            </div>
                            <div class="top">
                                <h1 v-text="item.name" :style="'color:'+item.color"></h1>
                                <p v-if="data.count[index].count" class="tp" ><?php echo __('已加入', 'b2'); ?>{{data.count[index].count}}<?php echo __('人', 'b2'); ?></p>
                                <p class="tp" v-else><?php echo __('终身无限会员','b2'); ?></p>
                                <p class="pi" v-if="item.allow_download_count < 9999">每天可下载<span>{{item.allow_download_count}}</span><?php echo __('个VIP资源','b2'); ?></p>
                                <p class="pi"  v-else>（<?php echo __('无限制次数','b2'); ?>）</p>
                                <div class="dj">
                                    <span><?php echo B2_MONEY_SYMBOL; ?></span><strong  v-text="item.price"></strong> / <em v-if="item.time > 0">{{item.time}}<?php echo __('天','b2'); ?></em>
                                    <em v-else><?php echo __('终身','b2'); ?></em>
                                </div>
                                
                                <a class="vip-buy vip-btn b2-radius"><button @click="join('vip'+index,item.name,item.price)"><span v-if="item.allow_buy === false"><?php echo __('续签会员','b2'); ?></span><span v-else><?php echo __('立刻加入','b2'); ?></span></span></button>
                                </span></a>
                            </div>
                            <div class="home-homevip-boxmks">
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
                <?php }?>
                
             <?php  
            $vip_top_list=b2_get_option('Jitheme_vip_main','vip_top_list');
            $vip_top_list_html = '';
            if(is_array($vip_top_list)){
                
                foreach ($vip_top_list as $k => $v) {
                    $vip_top_list_html.='<li class="vip-item">
                            <div class="box mk b2-radius">
                                <img src="'.$v['img'].'" alt="'.$v['title'].'">
                                <span>'.$v['title'].'</span>
                                <p>'.$v['desc'].'</p>
                            </div>
                        </li>';
                }  
            }   
            ?>   
                <div class="vip-problem">
                    <div class="problem-title">
                        <span>会员特权</span>
                        <p>加入VIP，尊享海量丰富作品和功能体验</p>
                    </div>
                    <ul class="vip-problem-list ji_vips_magin">
                        <?php echo $vip_top_list_html ?>
                    </ul>
                </div>
                <div class="vip-footer">
                    <h2><?php echo __('常见问题','b2'); ?></h2>
                    <p><?php echo __('为您解决烦忧，我们势在必行！','b2'); ?></p>
                     <?php echo  Onecad_vips_wenti() ?>
                </div>
            </main>
        </div>
    </div>
</div>
<?php } ?>
<?php
get_footer();