<?php
    use B2APP\common\opt;
?>
<!doctype html>
<html <?php language_attributes(); ?> class="avgrund-ready">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
	<meta http-equiv="Cache-Control" content="no-transform" />
	<meta http-equiv="Cache-Control" content="no-siteapp" />
    <title><?php echo bloginfo('name').'移动端统一发布页'; ?></title>
	<meta name="renderer" content="webkit"/>
	<meta name="force-rendering" content="webkit"/>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>
    <link rel='stylesheet' id='app-css'  href='//at.alicdn.com/t/font_2761784_o6hsc83ngas.css' type='text/css' media='all' />
    <script type='text/javascript' src="https://cdn.bootcdn.net/ajax/libs/vue/2.6.14/vue.min.js"></script>
    <script type='text/javascript' src="https://cdn.bootcdn.net/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script type='text/javascript' src="<?php echo get_template_directory_uri().'/Assets/fontend/library/message.min.js'; ?>"></script>
    <script type='text/javascript' src="https://cdn.bootcdn.net/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
    <link rel="profile" href="http://gmpg.org/xfn/11">
	<meta name="theme-color" content="<?php echo b2_get_option('template_top','gg_bg_color'); ?>">
</head>

<?php 
    $logo = opt::opt('options','logo');
    $fb_logo = opt::opt('options','fb_logo');
    $title = opt::opt('options','fb_title');
    $desc = opt::opt('options','fb_desc');
    $info = opt::opt('options','fb_info');

    $android = opt::opt('options','fb_android');
    $ios = opt::opt('options','fb_ios');

    $miniapp_weixin = opt::opt('options','fb_miniapp_weixin');
    $miniapp_alipay = opt::opt('options','fb_miniapp_alipay');
    $miniapp_baidu = opt::opt('options','fb_miniapp_baidu');
    $miniapp_toutiao = opt::opt('options','fb_miniapp_toutiao');
    $miniapp_qq = opt::opt('options','fb_miniapp_qq');

    $miniapp_weixin_name = opt::opt('options','fb_miniapp_weixin_name');
    $miniapp_alipay_name = opt::opt('options','fb_miniapp_alipay_name');
    $miniapp_baidu_name = opt::opt('options','fb_miniapp_baidu_name');
    $miniapp_toutiao_name = opt::opt('options','fb_miniapp_toutiao_name');
    $miniapp_qq_name = opt::opt('options','fb_miniapp_qq_name');

    $miniapp_h5 = opt::opt('options','fb_h5');
    $miniapp_kuai = opt::opt('options','fb_kuai');
    $miniapp_thumb = opt::opt('options','fb_thumbs');

    $web_color = b2_get_option('template_main','web_color');
?>
<style>
    * {
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
    }
    .download-main{
        display: flex;
        display: -webkit-flex; /* Safari */
        min-height: 100vh;
        flex-direction: column;
    }
    .content{
        flex: 1;
    }
    .color{
        color:<?php echo $web_color; ?>
    }
    body, html {
        margin: 0;
        background-color: #FFF;
        font-size: 14px;
    }
    .download-main{
        max-width:700px;
        width:100%;
        margin:80px auto 0 auto
    }
    .header{
        display:flex;
        justify-content: space-between;
        position: relative;
    }
    .logo{
        position: absolute;
        left:-120px;
    }
    .logo img{
        width:80px;
        height:80px;
        border-radius:5px;
        display:block
    }
    .box-miniapp,.mini-box{
        display:flex
    }
    .title{
        font-size: 24px;
        color: #2A2A2A;
        font-weight: 700;
        margin-bottom:12px
    }
    .desc{
        font-size: 22px;
        color: #2A2A2A;
        font-weight: 400;
        width: 520px;
        word-wrap: break-word;
        word-break: break-all;
        overflow: hidden;
    }
    .mg-t{
        margin-top:30px
    }
    .app-info{
        color: #818181;
        font-size: 14px;
    }
    h2{
        color:#2A2A2A
    }
    .tab{
        font-size: 20px;
        font-weight:700;
        display:flex;
        justify-content: center;
        border-bottom: 1px solid #f7f7f7;
        padding: 0 20px;
    }
    .tab > div{
        padding:10px 20px;
        margin:0 20px;
        border-bottom:2px solid transparent;
        cursor: pointer;
    }
    .qr-box{
        display:flex;
        justify-content: center;
        margin:50px 0;
        text-align:center;
        border-bottom: 1px solid #f7f7f7;
        padding-bottom:30px
    }
    .app-qr{
        margin:10px 0
    }
    .app-download-link{
        display:flex
    }
    .app-download-link a{
        padding:0 20px;
        line-height: 35px;
        height: 35px;
        display:block;
        border: 1px solid <?php echo $web_color; ?>;
        color:<?php echo $web_color; ?>;
        border-radius:4px
    }
    .app-download-link a + a{
        margin-left:20px
    }
    .box-app img{
        display:block;
        width:256px;
        height:256px
    }
    .miniapp-item img{
        display:block;
        width:200px;
        height:200px
    }
    .miniapp-item + .miniapp-item{
        margin-left:20px
    }
    .box-app{
        display: flex;
        flex-flow: column;
        align-items: center;
        min-width: 350px
    }
    .miniapp-item .mg-t{
        color:#777;
        margin:20px 0 10px
    }
    .tab .picked{
        border-bottom:2px solid <?php echo $web_color; ?>;
    }
    .c-qr{
        position: absolute;
        background-color:#fff;
        padding:20px;
        top:30px;
        left:-32px;
        border-radius:4px;
        box-shadow: rgba(0, 0, 0, 0.2) 0px 12px 28px 0px, rgba(0, 0, 0, 0.1) 0px 2px 4px 0px, rgba(255, 255, 255, 0.05) 0px 0px 0px 1px inset;
    }
    .header-right{
        position: relative;
    }
    .box-H5{
        font-size:20px
    }
    .app-thumbs-box{
        overflow-x: scroll;
        white-space: nowrap;
    }
    .app-thumbs-box img{
        width:176px;
        display:inline-block;
        border:1px solid #efefef
    }
    .app-thumbs-box img + img{
        margin-left:20px
    }
    .footer{
        font-size:12px;
        color:#AEAEAE;
        padding:20px 0;
        text-align:center
    }
    pre{
        white-space: pre-wrap;
        font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;
        word-break: break-all;
    }
    .m-hidd{
        display:block
    }
    .m-show{
        display:none
    }
    [v-cloak]{
        display:none
    }
    .qmsg.qmsg-wrapper{
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-size: 13px;
    font-variant: tabular-nums;
    line-height: 1;
    list-style: none;
    font-feature-settings: "tnum";
    position: fixed;
    top: 136px;
    left: 0;
    z-index: 9999999;
    width: 100%;
    pointer-events: none;
}
.qmsg .qmsg-item{
    padding: 8px;
    text-align: center;
    -webkit-animation-duration: .3s;
    animation-duration: .3s;
    position: relative;
}
.qmsg .qmsg-item .qmsg-count{
    text-align: center;
    position: absolute;
    left: -4px;
    top: -4px;
    background-color:#FF3355;
    color: #fff;
    font-size: 12px;
    line-height: 16px;
    border-radius: 2px;
    display: inline-block;
    min-width: 16px;
    height: 16px;
    -webkit-animation-duration: .3s;
    animation-duration: .3s;
}
.qmsg .qmsg-item:first-child{
    margin-top: -8px;
}
.qmsg .qmsg-content{
    text-align: left;
    position: relative;
    display: inline-block;
    padding: 10px 16px;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
    pointer-events: all;
    /* min-width: 175px; */
    max-width: 80%;
    min-width: 80px;
}
.qmsg .qmsg-content [class^="qmsg-content-"]{
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
}
.qmsg .qmsg-content .qmsg-content-with-close{
    padding-right: 20px;
}
.qmsg .qmsg-icon{
    display: inline-block;
    color: inherit;
    font-style: normal;
    line-height: 0;
    text-align: center;
    text-transform: none;
    vertical-align: -.125em;
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    position: relative;
    top: 1px;
    margin-right: 8px;
    font-size: 16px;
}
.qmsg .qmsg-icon svg{
    display: inline-block;
}

.qmsg .qmsg-content-info .qmsg-icon{
    color: #1890ff;
    user-select: none;
}
.qmsg .qmsg-icon-close{
    position: absolute;
    top: 11px;
    right: 5px;
    padding: 0;
    overflow: hidden;
    font-size: 12px;
    line-height: 22px;
    background-color: transparent;
    border: none;
    outline: none;
    cursor: pointer;
    color: rgba(0, 0, 0, .45);
    transition: color .3s
}
.qmsg .qmsg-icon-close:hover>svg path{
    stroke: #555;
}
.qmsg .animate-turn{
    animation:MessageTurn 1s linear infinite;  
    -webkit-animation: MessageTurn 1s linear infinite;
}
@keyframes MessageTurn{
    0%{-webkit-transform:rotate(0deg);}
    25%{-webkit-transform:rotate(90deg);}
    50%{-webkit-transform:rotate(180deg);}
    75%{-webkit-transform:rotate(270deg);}
    100%{-webkit-transform:rotate(360deg);}
}
@-webkit-keyframes MessageTurn{
    0%{-webkit-transform:rotate(0deg);}
    25%{-webkit-transform:rotate(90deg);}
    50%{-webkit-transform:rotate(180deg);}
    75%{-webkit-transform:rotate(270deg);}
    100%{-webkit-transform:rotate(360deg);}
}

@-webkit-keyframes MessageMoveOut {
    0% {
        max-height: 150px;
        padding: 8px;
        opacity: 1
    }

    to {
        max-height: 0;
        padding: 0;
        opacity: 0
    }
}

@keyframes MessageMoveOut {
    0% {
        max-height: 150px;
        padding: 8px;
        opacity: 1
    }

    to {
        max-height: 0;
        padding: 0;
        opacity: 0
    }
}


@-webkit-keyframes MessageMoveIn {
    
    0% {
        transform: translateY(-100%);
        transform-origin: 0 0;
        opacity: 0
    }

    to {
        transform: translateY(0);
        transform-origin: 0 0;
        opacity: 1
    }
}

@keyframes MessageMoveIn {
    0% {
        transform: translateY(-100%);
        transform-origin: 0 0;
        opacity: 0
    }

    to {
        transform: translateY(0);
        transform-origin: 0 0;
        opacity: 1
    }
}
@-webkit-keyframes MessageShake {
    0%,
    100% {
      transform: translateX(0px);
      opacity: 1;
    }
  
    25%,
    75% {
        transform: translateX(-4px);
      opacity: 0.75;
    }
  
    50% {
        transform: translateX(4px);
        opacity: 0.25;
    }
  }
@keyframes MessageShake {
    0%,
    100% {
      transform: translateX(0px);
      opacity: 1;
    }
  
    25%,
    75% {
        transform: translateX(-4px);
      opacity: 0.75;
    }
  
    50% {
        transform: translateX(4px);
        opacity: 0.25;
    }
  }
    @media screen and (max-width: 768px){
        .m-hidd{
            display:none
        }
        .m-show{
            display:block
        }
        .header{
            flex-flow: column;
            align-items: center;
        }
        .desc{
            font-size:18px;
            width:auto
        }
        .logo{
            position: relative;
            left:0;
            top:0
        }
        .download-main{
            margin: 30px auto 0 auto;
        }
        .header-right{
            display:none
        }
        .header-info{
            margin-top:10px;
            text-align:center
        }
        .tab{
            font-size:16px
        }
        .tab > div{
            margin:0
        }
        .app-info pre {
            width: 100%;
            font-size: 14px;
            color: #2A2A2A;
            text-align: left;
        }
        .app-info{
            padding:0 24px
        }
        .miniapp-item .m-show > div:nth-child(1){
            font-size:16px;
            font-weight:700;
            margin-bottom:20px
        }
        .qr-box{
            margin: 30px 25px;
            display:block
        }
        .mini-box{
            margin:0 25px
        }
        .mini-nav{
            width:72px;
            min-width: 72px;
            text-align:left;
            border-right: 1px solid #f7f7f7;
        }
        .mini-nav-item{
            opacity: .45;
        }
        .mini-nav-item{
            margin-bottom:10px
        }
        .mini-box{
            margin:0 15px;
            flex:1;
            display:flex;
            justify-content: center;
        }
        .miniapp-item{
            text-align:center
        }
        .miniapp-item + .miniapp-item{
            margin:0
        }
        .mini-img{
            display:flex;
            justify-content: center;
        }
        .mini-nav-item.picked{
            opacity: 1;
        }
        .box-app{
            min-width: auto;
        }
        .app-download-link a{
            font-size:14px
        }
        .miniapp-item img{
            width:160px;
            height:160px
        }
        .box-app img{
            width:200px;
            height:200px
        }
        .app-download-link a{
            font-size:13px
        }
        .btn{
            background: none;
            border: 1px solid #efefef;
            padding: 5px 14px;
            border-radius: 3px;
        }
        .btn:active{
            opacity: .5;
        }
    }
</style>

<body>
	<div id="main" class="download-main">
        <div class="header">
            <div class="logo">
                <?php if($fb_logo) { ?>
                    <img src="<?php echo $fb_logo; ?>" />
                <?php }else{ ?>
                    <?php echo bloginfo('name'); ?>
                <?php } ?>
            </div>
            <div class="header-left">
                <div class="header-info">
                    <div class="title">
                        <?php echo $title ? $title : bloginfo('name'); ?>
                    </div>
                    <div class="desc">
                        <?php echo $desc ? $desc : '这是应用的描述'; ?>
                    </div>
                </div>
            </div>
            <div class="header-right" @mouseenter="showQr" @mouseleave="closeQr">
                <button>获取本页二维码</a>
                <div class="c-qr" v-show="showCurrentPageQr" v-cloak>
                    <img :src="qr" v-if="qr"/>
                </div>
            </div>
        </div>
        <div class="content mg-t">
            <div class="tab color">
                <?php if($android || $ios){ ?> 
                    <div @click="show = 'app'" :class="show == 'app' ? 'picked' : ''">APP</div>
                <?php } ?>
                <?php if($miniapp_weixin || $miniapp_alipay || $miniapp_baidu || $miniapp_toutiao || $miniapp_qq){ ?> 
                    <div @click="show = 'miniapp'" :class="show == 'miniapp' ? 'picked' : ''">小程序</div>
                <?php } ?>
                <?php if($miniapp_h5){ ?> 
                    <div @click="show = 'h5'" :class="show == 'h5' ? 'picked' : ''">H5</div>
                <?php } ?>
                <?php if($miniapp_kuai){ ?> 
                    <div @click="show = 'kuai'" :class="show == 'kuai' ? 'picked' : ''">快应用</div>
                <?php } ?>
            </div>
            <div class="qr-box">
                <?php if($android || $ios){ ?>
                    <div class="box box-app" v-if="show == 'app'" v-cloak data-key="app">
                        <div>
                            <img :src="qr" />
                        </div>
                        <div class="color app-qr">扫码获取</div>
                        <div class="app-download-link" v-if="clientWidth > 760" v-cloak>
                            <?php if($android){ ?><a href="<?php echo $android; ?>" target="_blank" download><i class="b2app-android-fill iconfont"></i>Android平台下载</a><?php } ?>
                            <?php if($ios){ ?><a href="<?php echo $ios; ?>" target="_blank"><i class="b2app-apple-fill iconfont"></i>前往iOS应用商店安装</a><?php } ?>
                        </div>
                        <div class="app-download-link" v-if="clientWidth <= 760" v-cloak>
                            <?php if($android){ ?><a href="<?php echo $android; ?>" target="_blank" download v-if="is.android"><i class="b2app-android-fill iconfont"></i>Android平台下载</a><?php } ?>
                            <?php if($ios){ ?><a href="<?php echo $ios; ?>" target="_blank" v-if="is.ios"><i class="b2app-apple-fill iconfont"></i>前往iOS应用商店安装</a><?php } ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="box box-miniapp" v-if="show == 'miniapp'" v-cloak data-key="miniapp">
                    <div class="m-show mini-nav">
                        <?php if($miniapp_weixin) { ?><div :class="['mini-nav-item',{'picked':miniPicked == 'weixin'}]" @click="miniPicked = 'weixin'"><i class="iconfont b2app-wechat-2-fill"></i>微信</div><?php } ?>
                        <?php if($miniapp_alipay) { ?><div :class="['mini-nav-item',{'picked':miniPicked == 'alipay'}]" @click="miniPicked = 'alipay'"><i class="iconfont b2app-alipay-fill"></i>支付宝</div><?php } ?>
                        <?php if($miniapp_baidu) { ?><div :class="['mini-nav-item',{'picked':miniPicked == 'baidu'}]" @click="miniPicked = 'baidu'"><i class="iconfont b2app-baidu-fill"></i>百度</div><?php } ?>
                        <?php if($miniapp_toutiao) { ?><div :class="['mini-nav-item',{'picked':miniPicked == 'toutiao'}]" @click="miniPicked = 'toutiao'"><i class="iconfont b2app-toutiaoyangshi"></i>头条</div><?php } ?>
                        <?php if($miniapp_qq) { ?><div :class="['mini-nav-item',{'picked':miniPicked == 'qq'}]" @click="miniPicked = 'qq'"><i class="iconfont b2app-qq-fill"></i>QQ</div><?php } ?>
                    </div>
                    <div class="mini-box">
                        <?php if($miniapp_weixin){ ?>
                            <div class="miniapp-item" v-show="mshow('weixin')" v-cloak>
                                <div class="m-show">
                                    <div id="weixin-name"><?php echo $miniapp_weixin_name ? $miniapp_weixin_name : '暂未设置'; ?></div>
                                    <button class="btn" data-clipboard-target="#weixin-name">复制名称</button>
                                    <div class="mg-t">复制成功后可在微信搜索小程序</div>
                                </div>
                                <div class="mini-img">
                                    <img src="<?php echo $miniapp_weixin; ?>" />
                                </div>
                                <div class="m-hidd">
                                    <div class="mg-t">扫二维码识别小程序</div>
                                    <div>微信小程序</div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if($miniapp_alipay){ ?>
                            <div class="miniapp-item" v-show="mshow('alipay')" v-cloak>
                                <div class="m-show">
                                    <div id="alipay-name"><?php echo $miniapp_alipay_name ? $miniapp_alipay_name : '暂未设置'; ?></div>
                                    <button class="btn" data-clipboard-target="#alipay-name">复制名称</button>
                                    <div class="mg-t">复制成功后可在支付宝搜索小程序</div>
                                </div>
                                <div class="mini-img">
                                    <img src="<?php echo $miniapp_alipay; ?>" />
                                </div>
                                <div class="m-hidd">
                                    <div class="mg-t">扫二维码识别小程序</div>
                                    <div>支付宝小程序</div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if($miniapp_baidu){ ?>
                            <div class="miniapp-item" v-show="mshow('baidu')" v-cloak>
                                <div class="m-show">
                                    <div id="baidu-name"><?php echo $miniapp_baidu_name ? $miniapp_baidu_name  : '暂未设置'; ?></div>
                                    <button class="btn" data-clipboard-target="#baidu-name">复制名称</button>
                                    <div class="mg-t">复制成功后可在百度应用中搜索小程序</div>
                                </div>
                                <div class="mini-img">
                                    <img src="<?php echo $miniapp_baidu; ?>" />
                                </div>
                                <div class="m-hidd">
                                    <div class="mg-t">扫二维码识别小程序</div>
                                    <div>百度小程序</div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if($miniapp_toutiao){ ?>
                            <div class="miniapp-item" v-show="mshow('toutiao')" v-cloak>
                                <div class="m-show">
                                    <div id="toutiao-name"><?php echo $miniapp_toutiao_name ? $miniapp_toutiao_name : '暂未设置'; ?></div>
                                    <button class="btn" data-clipboard-target="#toutiao-name">复制名称</button>
                                    <div class="mg-t">复制成功后可在头条中搜索小程序</div>
                                </div>
                                <div class="mini-img">
                                    <img src="<?php echo $miniapp_toutiao ? $miniapp_toutiao : '暂未设置'; ?>" />
                                </div>
                                <div class="m-hidd">
                                    <div class="mg-t">扫二维码识别小程序</div>
                                    <div>头条小程序</div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if($miniapp_qq){ ?>
                            <div class="miniapp-item" v-show="mshow('qq')" v-cloak>
                                <div class="m-show">
                                    <div id="qq-name"><?php echo $miniapp_qq_name ? $miniapp_qq_name : '暂未设置'; ?></div>
                                    <button class="btn" data-clipboard-target="#qq-name">复制名称</button>
                                    <div class="mg-t">复制成功后可在手机QQ中搜索小程序</div>
                                </div>
                                <div class="mini-img">
                                    <img src="<?php echo $miniapp_qq; ?>" />
                                </div>
                                <div class="m-hidd">
                                    <div class="mg-t">扫二维码识别小程序</div>
                                    <div>QQ小程序</div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php if($miniapp_h5){ ?>
                    <div class="box box-H5" v-if="show == 'h5'" v-cloak data-key="h5">
                        <div>连接地址</div>
                        <div class="mg-t"><?php if($miniapp_h5){ ?><a href="<?php echo $miniapp_h5; ?>" target="_blank"><?php echo $miniapp_h5; ?></a><?php } ?></div>
                    </div>
                <?php } ?>
                <?php if($miniapp_kuai){ ?>
                    <div class="box box-app miniapp-item" v-if="show == 'kuai'" v-cloak data-key="kuai">
                        <div>
                            <img src="<?php echo $miniapp_kuai; ?>" />
                        </div>
                        <div class="mg-t">快应用</div>
                        <div>扫描二维码或复制名称后可在手机应用市场中搜索快应用</div>
                    </div>
                <?php } ?>
            </div>
            <div class="app-info mg-t">
                <h2>应用描述</h2>
                <pre><?php echo $info ? $info : '没有说明'; ?></pre>
            </div>
            <?php
                if(!empty($miniapp_thumb)){
            ?>
                <div class="app-info app-thumbs mg-t">
                    <h2>应用截图</h2>
                    <div class="app-thumbs-box">
                        <?php
                            foreach ($miniapp_thumb as $k => $v) {
                                echo '<img src="'.$v.'" />';
                            }
                        ?>
                    </div>
                </div>
            <?php
                }
            ?>
        </div>
        <div class="footer">
            本页面为 <?php echo bloginfo('name'); ?> 移动端统一发布页，由 <a href="<?php echo home_url();?>"><?php echo bloginfo('name'); ?></a> 提供技术支持。
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function(){
            
            var apppage = new Vue({
                el:'#main',
                data:{
                    show:'app',
                    qr:'',
                    showCurrentPageQr:false,
                    miniPicked:'weixin',
                    is:{
                        ios:false,
                        android:false
                    },
                    clientWidth:860
                },
                mounted(){
                    this.show = document.querySelector('.qr-box > div:first-child').getAttribute('data-key')
                    this.getQrcode()

                    var clipboard = new ClipboardJS('.btn');

                    clipboard.on('success', function(e) {
                        Qmsg['success']('复制成功',{html:true});
                        e.clearSelection();
                    });

                    clipboard.on('error', function(e) {
                        Qmsg['warning']('复制失败',{html:true});
                    });

                    let u = navigator.userAgent;
                    let isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
                    let isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
                    if (isAndroid) {
                        this.is.android = true
                    }
                    if (isIOS) {
                    　　this.is.ios = true
                    }

                    this.clientWidth = document.body.clientWidth;
                },
                methods:{
                    getQrcode(url){
                        var q = new QRious({
                            value: window.location.href,
                            size:120,
                            level:'L'
                        });
                        this.qr = q.toDataURL('image/jpeg');
                    },
                    showQr(){
                        this.showCurrentPageQr = true
                    },
                    closeQr(){
                        this.showCurrentPageQr = false 
                    },
                    mshow(type){
                        if(this.clientWidth >= 760) return true
                        if(this.miniPicked == type) return true
                        return false
                    }
                }
            })
        });
        
    </script>
</body>
</html>
	

