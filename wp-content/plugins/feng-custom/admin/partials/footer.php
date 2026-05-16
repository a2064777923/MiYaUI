<?php
// +----------------------------------------------------------------------
// | 晨风自定义 [ 用最简单的代码，实现最简单的事情。 ]
// +----------------------------------------------------------------------
// | Home Page: https://feng.pub/feng-custom
// +----------------------------------------------------------------------
// | Gitee: https://gitee.com/ouros/feng-custom
// +----------------------------------------------------------------------
// | WordPress: https://cn.wordpress.org/plugins/feng-custom
// +----------------------------------------------------------------------
// | Author: 阿锋 <mypen@163.com>
// +----------------------------------------------------------------------
/**
 * footer.php
 */
?>
<hr />
<?php esc_html_e('插件版本号：', 'feng-custom'); echo esc_attr(FENG_CUSTOM_VERSION); ?> ｜
<a href="https://gitee.com/ouros/feng-custom/issues" target="_blank">
	<?php esc_html_e('建议、问题、BUG', 'feng-custom'); ?>
</a>

</div><!-- .wrap -->
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
?>