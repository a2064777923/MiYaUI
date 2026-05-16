<?php
use B2\Modules\Common\Post;
use B2\Modules\Templates\VueTemplates;
use B2\Modules\Templates\Footer;

// 获取页面业务参数
$post_id = isset($_GET['post_id']) && $_GET['post_id'] ? (int)$_GET['post_id'] : 0;
$index   = isset($_GET['index']) && $_GET['index'] ? (int)$_GET['index'] : 0;
$i       = isset($_GET['i']) && $_GET['i'] ? (int)$_GET['i'] : 0;

// 获取下载资源数据并校验
$download_data = Post::get_post_download_data($post_id);
if(!isset($download_data[$index])){
    wp_die('没有找到这个资源','b2');
}
$download_data = $download_data[$index];

// 获取广告配置数据
$top    = b2_get_option('template_download','download_ads_top');
$middle = b2_get_option('template_download','download_ads_middle');
$bottom = b2_get_option('template_download','download_ads_bottom');
?>
<!doctype html>
<html <?php language_attributes(); ?> class="avgrund-ready">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <!-- 移动端自适应核心配置，标准最优写法 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="Cache-Control" content="no-transform, no-siteapp" />
    <meta name="renderer" content="webkit"/>
    <meta name="force-rendering" content="webkit"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
    <style>
        /* 解决vue加载时插值表达式闪现问题 */
        [v-cloak] { display: none !important; }
        
        /* 全局样式变量 */
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --text-color: #1f2937;
            --text-secondary: #6b7280;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --gradient-start: #3b82f6;
            --gradient-end: #2563eb;
        }

        /* 全局初始化 */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        /* 页面主体容器 */
        .site-content {
            min-height: 100vh;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #f0f7ff 0%, #f5f3ff 100%);
        }
        .wrapper {
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        /* 标题区 - Logo 专属位置【核心：LOGO固定在标题页面区域】 */
        .download-page-title {
            text-align: center;
            margin: 1.5rem 0 3rem;
            transition: all .3s ease;
        }
        .download-page-title img {
            max-height: 50px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            cursor: pointer;
        }
        .download-page-title:hover { opacity: .9; }

        /* 下载卡片主体 */
        .download-page-box {
            background: var(--card-bg);
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05),0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            margin: 1rem auto;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            width: 100%;
        }

        /* 资源标题样式 */
        .download-page-box h1 {
            font-size: clamp(1.5rem, 5vw, 2rem);
            font-weight: 700;
            text-align: center;
            margin: 1rem 0 0.5rem;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .download-page-info {
            display: grid;
            grid-template-columns: 1fr;
        }

        /* 资源属性容器 【核心：资源属性一行两个】 */
        .download-meta {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 1rem;
            padding: 0.8rem;
        }
        .download-meta ul {
            list-style: none;
            padding: 0;
            margin: 0;
            /* 固定一行两列，完美满足属性一行两个的需求，优先级最高 */
            grid-template-columns: repeat(2, 1fr) !important;
            display: grid;
            gap: 1rem;
        }
        .download-meta li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 0.8rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .download-meta li:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .download-meta li span:first-child {
            color: #737373;
            font-size: 1rem;
            min-width: 80px;
            flex-shrink: 0;
        }
        .download-meta li span:last-child {
            font-weight: 500;
            color: var(--text-color);
            flex: 1;
        }

        /* 权限提示区域 */
        .download-current {
            margin: 2rem 0;
            padding: 1.5rem;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 1rem;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        .download-current .green { color: var(--success-color); font-weight: 500; }
        .download-current .red { color: var(--error-color); font-weight: 500; }
        .download-current b {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0 0.3rem;
        }

        /* 提取码/解压码区域 */
        .tqma {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        .fuzhi {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: rgba(243, 244, 246, 0.8);
            border-radius: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid var(--border-color);
        }
        .fuzhi:hover {
            background: rgba(243, 244, 246, 1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .fuzhi span {
            font-family: 'SF Mono', Consolas, monospace;
            font-size: 0.95rem;
            color: var(--primary-color);
            font-weight: 500;
        }

        /* 下载按钮样式 */
        .button {
            display: inline-block;
            width: 100%;
            padding: 0.6rem 2rem;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            color: white;
            text-align: center;
            border-radius: 1rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
            cursor: pointer;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }
        .button:active { transform: translateY(0); }
        .button[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
            background: #94a3b8;
            box-shadow: none;
        }

        /* 复制成功提示 */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: var(--card-bg);
            border-radius: 0.8rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transform: translateX(120%);
            transition: transform 0.3s ease-out;
            z-index: 9999;
        }
        .toast.show { transform: translateX(0); }

        /* ============ 核心移动端自适应适配【重中之重】 ============ */
        @media (max-width: 768px) {
            .download-page-box { padding: 1.5rem 1rem !important; }
            /* 移动端自动切换为单列，适配小屏幕 */
            .download-meta ul { grid-template-columns: 1fr !important; }
            .download-meta li { padding: .8rem; gap: .3rem; }
            .button { padding: 0.7rem 1.5rem; font-size: 1rem; }
            .download-current { padding: 1rem; font-size: .95rem; }
            .fuzhi { padding: .8rem 1rem; }
            .site-content { padding: 1rem .5rem; }
        }
    </style>
</head>
<body <?php body_class(); ?>>
    <div id="page" class="site">
        <div id="content" class="site-content">
            <div class="wrapper">
                <div id="primary-home" class="content-area">
                    <!-- Logo在标题区 核心位置 -->
                    <!--<div class="download-page-title">-->
                    <!--    <a href="<?php echo B2_HOME_URI; ?>">-->
                    <!--        <?php echo VueTemplates::get_logo(); ?>-->
                    <!--    </a>-->
                    <!--</div>-->

                    <div class="download-page-box">
                        <?php echo $top; ?>
                        <h1><?php echo $download_data['name']; ?></h1>
                        
                        <div class="download-page-info">
                            <div class="download-meta">
                                <ul>
                                    <?php 
                                        foreach ($download_data['attrs'] as $k => $v) {
                                            echo '<li>
                                                <span>'.$v['name'].'</span>
                                                <span>'.$v['value'].'</span>
                                            </li>';
                                        }
                                    ?>
                                </ul>

                                <div class="download-page-button" id="download-page" ref="downloadPage">
                                    <div v-if="data === ''"><div class="b2-loading empty-page"></div></div>
                                    <div class="download-current" v-else v-cloak>
                                        <span><?php echo __('您当前的等级为','b2'); ?></span>
                                        <span v-if="data.current_user.lv.lv" v-html="data.current_user.lv.lv.icon"></span>
                                        <span v-if="data.current_user.lv.vip" v-html="data.current_user.lv.vip.icon"></span>

                                        <div v-if="!data.current_user.can.allow">
                                            <span class="red"><?php echo __('没有下载权限，请','b2'); ?></span>
                                            <span v-if="data.current_user.lv.lv.lv == 'guest'"><a href="javascript:void(0)" @click="login()"><?php echo __('登录','b2'); ?></a></span>
                                            <span v-else-if="data.current_user.can.type == 'comment'"><a href="javascript:void(0)"><?php echo __('评论之后下载','b2'); ?></a></span>
                                            <span v-else-if="data.current_user.can.type == 'credit'"><a href="javascript:void(0)"><?php echo __('支付积分以后下载','b2'); ?></a></span>
                                            <span v-else-if="data.current_user.can.type == 'money'"><a href="javascript:void(0)"><?php echo __('支付费用以后下载','b2'); ?></a></span>
                                            <span v-else><a href="javascript:void(0)"><?php echo __('升级会员','b2'); ?></a></span>
                                        </div>
                                        <div v-else>
                                            <span v-if="data.current_user.can.type == 'allow_all'" class="green">
                                                <?php echo __('您有每天下载所有资源','b2'); ?>
                                                <b v-text="data.current_user.can.total_count"></b>
                                                <?php echo __('次的特权，今日剩余','b2'); ?>
                                                <b v-text="data.current_user.can.count"></b>
                                                <?php echo __('次','b2'); ?>
                                            </span>
                                            <span class="green" v-else><?php echo __('已取得下载权限','b2'); ?></span>
                                        </div>
                                    </div>

                                    <div class="tqma" v-if="data !== '' && data.button.attr">
                                        <div v-if="data.button.attr.tq" v-cloak class="fuzhi" data-clipboard-target='#tq'>
                                            <?php echo __('提取码（点击复制）：','b2'); ?>
                                            <span v-text="data.button.attr.tq" :data-clipboard-text="data.button.attr.tq" id="tq"></span>
                                        </div>
                                        <div v-if="data.button.attr.jy" v-cloak class="fuzhi" data-clipboard-target='#jy'>
                                            <?php echo __('解压码（点击复制）：','b2'); ?>
                                            <span v-text="data.button.attr.jy" :data-clipboard-text="data.button.attr.jy" id="jy"></span>
                                        </div>
                                    </div>

                                    <a target="_blank" 
                                       :href="'<?php echo b2_get_custom_page_url('redirect');?>'+'?token='+data.button.url" 
                                       v-text="data.button.name" 
                                       v-if="data.length != 0" 
                                       v-cloak 
                                       class="button">
                                    </a>
                                </div>
                            </div>

                            <div class="download-middle-ads"><?php echo $middle; ?></div>
                        </div>
                        
                        <?php echo $bottom; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 复制成功提示框 -->
    <div id="toast" class="toast">复制成功！</div>

    <script>
        // 复制提取码/解压码功能
        document.querySelectorAll('.fuzhi').forEach(el => {
            el.addEventListener('click', function() {
                const text = this.querySelector('span').getAttribute('data-clipboard-text');
                navigator.clipboard.writeText(text).then(() => {
                    const toast = document.getElementById('toast');
                    toast.classList.add('show');
                    setTimeout(() => toast.classList.remove('show'), 2000);
                }).catch(err => {
                    alert('复制失败，请手动复制');
                });
            });
        });
    </script>

<?php echo Footer::vue_template(); ?>
<?php wp_footer(); ?>
</body>
</html>