<?php
/**
 * vip页面
 */
get_header();
?>
<div id="vips" v-cloak>
  <div class="header_bg">
        <div class="header_title"></div>
            <div class="vip-topding">
        <div class="content-area wrapper">
            <div class="vip-current" v-if="data.user">
                <span v-if="data.user == 'guest'">
                    您当前为游客，请登录以后操作                </span>
                <span v-else-if="!data.user.vip.lv">
                    您当前为普通用户，推荐升级成高级会员！                </span>
                <div v-else>
                    您当前为<span v-html="data.user.vip.icon"></span>用户，有效期至：<span v-text="data.user.time === 'long' ? '终身' : data.user.time"></span>                </div>
            </div>
         </div>
          </div>
        </div>
<main id="main" class="site-main b2-radius" ref="vips">
    <div class="vipcontent_wrapper">
        <div class="vipcontent_item" v-for="(item,index) in data.data">
        <div class="vips_tips"><span><b id="hd">已加入{{parseInt(item.count)}}人</b></span></div>
                    <img src="/wp-content/themes/b2-pinkcatyu/pic/viptj2.png" class="viptjimg" v-if="index==0">
                                <img src="/wp-content/themes/b2-pinkcatyu/pic/viptj2.png" class="viptjimg" v-if="index==1">
                                            <img src="/wp-content/themes/b2-pinkcatyu/pic/viptj.png" class="viptjimg" v-if="index==2">
                        <img src="/wp-content/themes/b2-pinkcatyu/pic/viptj.png" class="viptjimg" v-if="index==3">
                     
            <span class="vipname" v-text="item.name"></span>
            <span class="vip_price_present">限时价格：<span class="vip_price_red" v-text="item.price"></span> 元/<span v-if="item.time > 0">{{item.time}}天</span> 
            <span v-else="">终身</span></span>
            <button @click="join('vip'+index,item.name,item.price)" class="viptopay_button"><span v-if="item.allow_buy === false">续签会员</span> <span v-else="">立即加入</span> </button>
            <span class="vip_group_title">特权服务：</span>
            <ul class="group_detail">
                                <li :class="['vip-allow',{'allow':item.allow_read === '1'}]">
                                    <div><span>查看所有隐藏内容</span></div>
                                    <div>
                                        <span v-if="item.allow_read === '1'"><i class="b2font b2-check-line "></i></span>
                                        <span v-else><i class="b2font b2-close-line "></i></span>
                                    </div>
                                </li>
                                <li :class="['vip-allow',{'allow':item.allow_download === '1'}]">
                                    <div>
                                        <template v-if="item.allow_download === '1'">
                                            免费下载所有资源                                            <span v-if="item.allow_download_count < 9999">（<span style="color: red;">{{item.allow_download_count}}</span>次/天）</span>
                                            <span v-else>（无限制次数）</span>
                                        </template>
                                        <template v-else>
                                            免费下载所有资源                                        </template>
                                    </div>
                                    <div>
                                        <span v-if="item.allow_download === '1'"><i class="b2font b2-check-line "></i></span>
                                        <span v-else><i class="b2font b2-close-line "></i></span>
                                    </div>
                                </li>
                                <li :class="['vip-allow',{'allow':item.allow_videos === '1'}]">
                                    <template v-if="item.allow_videos === '1'">
                                        <div>免费查看所有付费视频</div>
                                        <div><i class="b2font b2-check-line "></i></div>
                                    </template>
                                    <template v-else>
                                        <div>免费查看所有付费视频</div>
                                        <div><i class="b2font b2-close-line "></i></div>
                                    </template>
                                </li>
 
                                <li :class="['vip-allow',{'allow':m.role == 1}]" v-if="item.more.length > 0" v-for="(m,_i) in item.more" :key="_i">
                                    <div><span v-text="m.text"></span></div>
                                    <div>
                                        <span v-if="m.role == 1"><i class="b2font b2-check-line "></i></span>
                                        <span v-else><i class="b2font b2-close-line "></i></span>
                                    </div>
                                </li>
                </ul>
        </div>
    </div>

<div class="vip-footer">
<h2 class="new-sess-head2"> <span>常见问题解答</span></h2><br>
<div class="vip-faq box">
<div class="vip-faq-list" @click.stop="showAc($event)">
<h2>开通VIP的好处？</h2>
<p class="b2-hidden">VIP会员根据等级在相应的有效期内享有本站所有资源免费下载资源的权力，免费查看隐藏内容的权力，免费查看视频的权力，同时本站商品还会获得打折价格，并且拥有其他特殊的权力。</p>
</div>
<div class="vip-faq-list" @click.stop="showAc($event)">
<h2>VIP资源需要单独购买吗？</h2>
<p class="b2-hidden">不同的VIP拥有不同的权限，通常VIP会员拥有免费资格享受各种资源的权力，但不排除某些特殊情况。</p>
</div>
<div class="vip-faq-list" @click.stop="showAc($event)">
<h2>VIP会员是否无限次下载资源？</h2>
<p class="b2-hidden">在遵守VIP会员协议前提下，VIP会员在会员有效期内可以下载所有免费和VIP资源。</p>
</div>
<div class="vip-faq-list" @click.stop="showAc($event)">
<h2>是否可以与他人分享VIP会员账号？</h2>
<p class="b2-hidden">一个VIP账号仅限一个人使用，禁止与他人分享账号，一经发现做永久封号处理。</p>
</div>
<div class="vip-faq-list" @click.stop="showAc($event)">
<h2>是否可以申请退款？</h2>
<p class="b2-hidden">VIP会员属于虚拟服务，付款后不能够申请退款。如付款前有任何疑问，联系站长处理</p>
</div>
<div class="vip-faq-list" @click.stop="showAc($event)">
<h2>遇到付款失败，付款后没有生效怎么办？</h2>
<p class="b2-hidden">理论上来说正常付款后不会出现此类问题，但是也会有部分用户因为网络等原因导致在付款的过程中会有一些小插曲，如果出现类似问题，大可不必惊慌，本站所有支付都会生成订单，不管成功还是失败，所以如果真正遇到网络问题导致付款失败您又不知道是否成功，请查看自己的个人中心的订单管理，截图联系管理员处理。</p>
</div>
</div>
</div>
</main>
</div>
<?php
get_footer();