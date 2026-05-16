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
 * options_header.php（被引用）
 */

/** WordPress Administration Bootstrap */
require_once ABSPATH . 'wp-admin/admin.php';

// 管理权限
if (!current_user_can('edit_theme_options')) {
	wp_die(esc_html__('对不起，您无权访问该页。'));
}

$nav_tab_active_class = ' nav-tab-active';
$nav_aria_current = ' aria-current="page"';

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
<div class="wrap feng-custom-wrap">
	<h1 class="wp-heading-inline">
		<?php isset($menu['title']) ? _e($menu['title']) : esc_html_e('晨风自定义', 'feng-custom'); ?>
	</h1>
	<hr class="wp-header-end">
	<?php
	if (isset($screens)) {
		?>
		<nav class="nav-tab-wrapper wp-clearfix" aria-label="">
			<?php
			foreach ($screens as $screen_key => $screen) {
				?>
				<a href="<?php echo esc_url(add_query_arg(array('module' => $screen_key))); ?>"
					class="nav-tab<?php echo esc_attr($module_name === $screen_key ? $nav_tab_active_class : ''); ?>" <?php echo esc_attr($module_name === $screen_key ? $nav_aria_current : ''); ?>>
					<?php esc_html_e($screen['tab_title']); ?>
				</a>
				<?php
			}
			?>
		</nav>
		<?php
	}
	$result_status = '';
	$result_message = '';
	if (isset($result)) {
	    $result_status = esc_attr($result['status'] ? 'success' : 'error');
	    $result_message = esc_html($result['message']);
	}
	?>
		<div id="setting-error-settings_updated"
			class="notice settings-error is-dismissible">
			<p>
				<strong class="message"></strong>
			</p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text">
					<?php esc_html_e('忽略此通知。', 'feng-custom'); ?>
				</span>
			</button>
		</div>
<script>
	const messageElement = document.getElementById("setting-error-settings_updated")
    function fct_notice_show(status, message) {
        if (status === "success") {
            messageElement.classList.add("notice-success")
            messageElement.classList.remove("notice-error")
        } else {
            messageElement.classList.add("notice-error")
            messageElement.classList.remove("notice-success")
        }
        messageElement.querySelector(".message").innerHTML = message
        messageElement.style.display = "block"
    }

    messageElement.querySelector(".notice-dismiss").addEventListener("click", () => {
        messageElement.style.display = "none";
    })
    <?php 
    if (isset($result)) {
        ?>
    window.addEventListener('load', () => {
		fct_notice_show("<?php echo $result_status; ?>", "<?php echo $result_message; ?>")
	});
        <?php 
    }
    ?>
	
</script>
