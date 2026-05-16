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


// 获取配置名
$options_name = $module_name;

require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-options.php';
require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-forms.php';
$OptionClass = Feng_Custom_Options::instance($options_name);
$FormsClass = Feng_Custom_Forms::instance($options_name);

$options_data = [];
// POST请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 处理配置
    $result = $OptionClass->submit_options($options_name);
    $options_data = $result['data'];
}

if (!$options_data) {
    // 获取配置数据
    $options_data = $OptionClass->get_options_data($options_name);
}

// 引入头部模板
require_once FENG_CUSTOM_PATH . 'admin/partials/header.php';

?>

<form action="" method="post">
	<table class="form-table" role="presentation">
		<tbody>
<?php
$options_params = $OptionClass->get_options_params($options_name);
foreach ($options_params as $item) {
?>
			<tr>
				<th scope="row">
					<?php 
					echo $item['group']['title'];
					if (isset($item['group']['help_url'])) {
					?>
					<a class="help_btn" href="<?php echo $item['group']['help_url']; ?>"
    					title="<?php esc_html_e('点击获取帮助', 'feng-custom'); ?>"
    					target="_blank">?</a>
    				<?php 
					}
    				?>
				</th>
				<td>
					<?php 
					foreach ($item['fields'] as $field_name => $field_params) {
					    $FormsClass->render($field_name, $field_params, (isset($options_data[$field_name]) ? $options_data[$field_name] : null));
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="td-hr" colspan="2"><hr /></td>
			</tr>
<?php
}
?>
		</tbody>
	</table>
	<input name="action_nonce" type="hidden"
		value="<?php echo esc_attr(wp_create_nonce('fct-submit-options-' . $options_name)); ?>">
	<?php submit_button(); ?>
</form>
<?php 
// 引入底部模板
require_once FENG_CUSTOM_PATH . 'admin/partials/footer.php';