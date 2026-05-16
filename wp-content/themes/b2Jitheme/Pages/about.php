<?php
/**
 * Template Name:关于我们
 * 极主题设计 QQ：860376600
 * 网址：https://www.jitheme.com
 */
get_header();
    $about_title=b2_get_option('Jitheme_about_main','about_title');
    $about_desc=b2_get_option('Jitheme_about_main','about_desc');
    $about_bg=b2_get_option('Jitheme_about_main','about_bg');
    $user_img=b2_get_option('Jitheme_about_main','about_user_img');
    $user_tx=b2_get_option('Jitheme_about_main','about_user_tx');
    $about_wx=b2_get_option('Jitheme_about_main','about_wx');
    $about_qq=b2_get_option('Jitheme_about_main','about_qq');
    $about_sma=b2_get_option('Jitheme_about_main','about_sma');
    $about_smb=b2_get_option('Jitheme_about_main','about_smb');
?>
<div id="Jitheme_about_main" class="Jitheme_about_main">
    <div class="about-header">
        <div class="bg-img">
            <div class="wrapper"><i class="img" style="background-image: url(<?php echo $about_bg ?>);"></i></div>
        </div>
        <div class="wrapper main-container">
            <div class="about-contact  b2-radius">
                <div class="contact-in">
                    <div class="c-header">
                        <div class="c-header-in">
                            <div class="c-avatar"><i class="g-avatar"><i class="g-thumb thumb-pos-center thumb-bg" style="background-image: url(<?php echo $user_tx ?>);"></i></i></div>
                            <h1 class="c-title"><?php echo $about_title ?></h1>
                            <p class="c-entry"><?php echo $about_desc ?></p>
                        </div>
                    </div>
                    <div class="c-menus flex f-items f-2 b2-radius">
                        <div class="c-menu b2-radius"><div class="txt"><?php echo jitheme_get_icon('Jifont-notice-1', 'Jifont','Jifont_pad') ?>联系方式一</div></div>
                        <div class="c-menu b2-radius"><div class="txt"><?php echo jitheme_get_icon('Jifont-QQ', 'Jifont','Jifont_pad') ?>联系方式二</div></div>
                    </div>
                    <div class="c-toggles">
                        <div class="c-toggle toggle-wechat">
                            <div class="about-contact-wechat about-contact-item b2-radius">
                                <div class="ac-ewm"><img src="<?php echo $user_img ?>" alt="<?php echo $about_wx ?>"></div>
                                <h3 class="ac-title">扫描上方微信号：<em class="clr_orange"><?php echo $about_wx ?></em></h3>
                                <h4 class="ac-subtitle"><?php echo $about_sma ?></h4>
                            </div>
                        </div>
                        <div class="c-toggle toggle-qq" style="display: none;">
                            <div class="about-contact-qq about-contact-item b2-radius">
                                <div class="ac-qq acqq-use-1  b2-radius"><img src="<?php echo B2_CHILD_URI ?>/Center/Assets/images/qqicon.png" alt="<?php echo $about_qq ?>"></div>
                                <h3 class="ac-title">QQ：<em class="clr_blue"><?php echo $about_qq ?></em></h3>
                                <h4 class="ac-subtitle"><?php echo $about_smb ?></h4>
                                <div class="btns"><a href="tencent://message/?menu=yes&amp;uin=860376600" target="_blank" class="btn btn-blue  b2-radius">点击交谈</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="about-honour">
        <div class="wrapper">
            <div id="Mini-quku" class="html-box">
                <?php echo jitheme_about_quk(); ?>
            </div>
        </div>
    </div>
    <div class="about-calture about-section">
        <div class="wrapper">
            <?php echo jitheme_about_team() ?>
        </div>
    </div>
    <div class="about-serve main3">
        <?php echo jitheme_about_serve(); ?>
    </div>
    <div class="about-selection about-section">
        <div class="wrapper">
            <?php echo  jitheme_about_hezuo(); ?>  
        </div>
    </div>
    <div id="about_contactus" class="about-contactus">
        <div class="wrapper">
            <div class="c-items f-items flex sm:f-2">
                <div class="intro-wrap c-item f-item">
                    <div class="c-box f-box b2-radius">
                        <div class="intro">
                            <div class="item-thumb"><i class="thumb " style="background-image: url(<?php echo $user_tx ?>);"></i> <i class="hi">Hi</i></div>
                            <h3 class="item-title"><?php echo $about_title ?></h3>
                            <p class="item-desc"><?php echo $about_desc ?>👉</p>
                            <div class="item-list">
                                <ul>
                                    <li><i class="Jifont Jifont-option"></i> 合作洽谈</li>
                                    <li><i class="Jifont Jifont-option"></i> 广告投放</li>
                                    <li><i class="Jifont Jifont-option"></i> 反馈建议</li>
                                    <li><i class="Jifont Jifont-option"></i> 投稿求职</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contact-wrap c-item f-item">
                    <div class="c-box f-box b2-radius">
                        <div class="contact">
                            <div class="ct-items f-items flex f-2">
                                <div class="ct-item f-item">
                                    <div class="box ct-box f-box b2-radius">
                                        <h3 class="ct-title"><i class="jitheme jitheme-wechat-fill"></i> 联系方式一</h3>
                                        <div class="about-contact-wechat about-contact-item">
                                            <div class="ac-ewm b2-radius"><img src="<?php echo $user_img ?>" alt="<?php echo $about_wx ?>"></div>
                                            <h3 class="ac-title">扫描上方微信号：<em class="clr_orange"><?php echo $about_wx ?></em></h3>
                                            <h4 class="ac-subtitle"><?php echo $about_sma ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="ct-item f-item">
                                    <div class="box ct-box f-box b2-radius">
                                        <h3 class="ct-title"><i class="jitheme jitheme-qq-fill"></i> 联系方式二</h3>
                                        <div class="about-contact-qq about-contact-item">
                                            <div class="ac-qq acqq-use-2 b2-radius"><img src="<?php echo B2_CHILD_URI ?>/Center/Assets/images/qqicon.png" alt="<?php echo $about_qq ?>">
                                                <div class="qq-btns"><a href="tencent://message/?menu=yes&amp;uin=<?php echo $about_qq ?>" target="_blank" class="btn btn-blue b2-radius">点击交谈</a></div>
                                            </div>
                                            <h3 class="ac-title">QQ：<em class="clr_blue"><?php echo $about_qq ?></em></h3>
                                            <h4 class="ac-subtitle"><?php echo $about_smb ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  </style>
    <script>
  // Add your JavaScript code here
  document.addEventListener('DOMContentLoaded', function () {
    // 获取DOM元素
    const cMenus = document.querySelectorAll('.c-menus .c-menu');
    const toggleWechat = document.querySelector('.toggle-wechat');
    const toggleQQ = document.querySelector('.toggle-qq');

    // 默认显示第一个c-menu，并显示toggle-wechat
    cMenus[0].classList.add('active');
    toggleWechat.style.display = 'block';

    // 监听c-menu的点击事件
    cMenus.forEach((cMenu, index) => {
      cMenu.addEventListener('click', () => {
        // 切换active类
        cMenus.forEach((cMenu) => cMenu.classList.remove('active'));
        cMenu.classList.add('active');

        // 切换toggle-wechat和toggle-qq的显示
        if (index === 0) {
          toggleWechat.style.display = 'block';
          toggleQQ.style.display = 'none';
        } else if (index === 1) {
          toggleWechat.style.display = 'none';
          toggleQQ.style.display = 'block';
        }
      });
    });
  });
</script>


<?php
  get_footer();
?>