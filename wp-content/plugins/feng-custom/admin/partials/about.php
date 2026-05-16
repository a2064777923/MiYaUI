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
 * 关于插件页面
 */

// 引入头部模板
require_once FENG_CUSTOM_PATH . 'admin/partials/header.php';

?>
<h1 style="padding-top: 30px"><?php esc_html_e('说明', 'feng-custom'); ?></h1>
<div style="padding-left: 30px">
	<p><?php esc_html_e('该插件主要使用前端代码实现，定时执行通过浏览器端完成，可节省服务器资源，请放心使用。', 'feng-custom'); ?></p>
</div>
<h2><?php esc_html_e('主要组件', 'feng-custom'); ?></h2>
<div style="padding-left: 30px">
	<h3><?php esc_html_e('Fancybox（图片灯箱）', 'feng-custom'); ?> v4.0.22</h3>
	<p>
		<a href="https://fancyapps.com/docs/ui/quick-start" target="_blank">https://fancyapps.com/docs/ui/quick-start</a>
	</p>
	<p>Fanybox是JavaScript灯箱的终极替代品，它为多媒体显示的高级用户体验设定了标准。支持图像，视频，地图，内联内容，iframes和任何其他HTML内容。</p>
	<h3><?php esc_html_e('activate-power-mode（输入框七彩光子特效）', 'feng-custom'); ?></h3>
	<p>
		<a href="https://github.com/disjukr/activate-power-mode"
			target="_blank">https://github.com/disjukr/activate-power-mode</a>
	</p>
	<h3><?php esc_html_e('JQuery Snowfall（雪花飘落）', 'feng-custom'); ?></h3>
	<p>
		<a href="https://github.com/loktar00/JQuery-Snowfall" target="_blank">https://github.com/loktar00/JQuery-Snowfall</a>
	</p>
	<h3><?php esc_html_e('CSS3动画灯笼', 'feng-custom'); ?></h3>
	<p>
		<a href="https://zmingcx.com/hanging-lantern.html" target="_blank">https://zmingcx.com/hanging-lantern.html</a>
	</p>
	<h3>lunar.js（日期工具） v1.6.7</h3>
	<p><a target="_blank" href="https://gitee.com/6tail/lunar-javascript">https://gitee.com/6tail/lunar-javascript</a></p>
	<p>lunar是一款无第三方依赖的公历(阳历)、农历(阴历、老黄历)、佛历和道历工具，支持星座、儒略日、干支、生肖、节气、节日、彭祖百忌、每日宜忌、吉神宜趋、凶煞宜忌、吉神(喜神/福神/财神/阳贵神/阴贵神)方位、胎神方位、冲煞、纳音、星宿、八字、五行、十神、建除十二值星、青龙名堂等十二神、黄道日及吉凶等。</p>
</div>
<h2><?php esc_html_e('主要贡献', 'feng-custom'); ?></h2>
<div style="padding-left: 30px">
	<p>
		<a href="https://feng.pub?utm_source=feng-custom" target="_blank">阿锋</a>
	</p>
</div>

<?php 

// 引入底部模板
require_once FENG_CUSTOM_PATH . 'admin/partials/footer.php';
?>