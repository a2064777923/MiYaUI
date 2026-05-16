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
 * links-rss.php
 */
// 链接类实例
$LinksClass = Feng_Custom_Links::instance();

// 操作标识
$action = sanitize_key(isset($_GET['action']) ? $_GET['action'] : '');

$do_action_method = 'do_rss_' . $action;
if (method_exists($LinksClass, $do_action_method)) {
    // 执行操作
    $result = call_user_func(array($LinksClass, 'do_rss_' . $action));
}

// 引入头部模板
require_once FENG_CUSTOM_PATH . 'admin/partials/header.php';
?>
<p>
    <button class="button button-primary" data-btn="refreshAll">更新下列链接的RSS信息</button>
    <a class="button" href="<?php echo admin_url('themes.php?page=feng-custom&module=links') ?>">设置
        链接&RSS</a>
</p>
<?php
// 获取配置数据
$config = $LinksClass->get_config();
// 获取RSS聚合来源分类的链接
$links_data = $LinksClass->get_links([
    'category' => $config['show_rss_category'],
    'orderby' => 'id',
    'order' => 'ASC'
]);
$feed_data = $LinksClass->get_feed_data_cache();
$feed_check_data = isset($feed_data['check_data']) ? $feed_data['check_data'] : [];
?>
<div class="links-list">
    <table>
        <thead>
            <tr>
                <th>站点</th>
                <th>RSS状态</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 显示连接列表
            foreach ($links_data as $link) {
                $link_id = $link->link_id;
                $link_name = $link->link_name;
                $link_url = $link->link_url;
                $link_rss = $link->link_rss;
                ?>
                <tr data-link="<?php echo $link_id; ?>" <?php echo $link_rss ? 'data-rss="' . $link_rss . '"' : ''; ?>>
                    <td class="site">
                        <h3 class="title"><a target="_blank" href="<?php echo $link_url; ?>">
                                <?php echo $link_name; ?>
                            </a></h3>
                        <?php
                        echo $link_rss ? '<p><a target="_blank" href="' . $link_rss . '">' . $link_rss . '</a></p>' : '';
                        ?>
                    </td>
                    <td class="status">
                        <?php
                        if ($link_rss) {
                            $check_data = isset($feed_check_data[$link_id]) ? $feed_check_data[$link_id] : [];
                            if ($check_data) {
                                echo '<p class="check-time">检查RSS时间：<span>' . wp_date($this->get_date_format(), $check_data['check_feed_time']) . '</span></p>';
                                if ($check_data['check_feed_error']) {
                                    echo '<p class="error"><span>错误信息如下：</span><br />' . $check_data['check_feed_error'] . '</p>';
                                }else {
                                    echo '<p class="ok">OK</p>';
                                }
                            }else {
                                echo '<p>系统尚未检查该RSS源</p>';
                            }
                        } else {
                            echo '<p class="warning">该链接未设置<span>RSS 地址</span></p>';
                        }
                        ?>
                    </td>
                    <td class="action">
                        <?php echo $link_rss ? '<button class="button" data-btn="check-rss">检查RSS</button>' : ''; ?>
                        <a class="button"
                            href="<?php echo add_query_arg(array('action' => 'edit', 'link_id' => $link_id), admin_url('link.php?')); ?>"
                            title="编辑链接">编辑</a>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>

<?php 
// 引入底部模板
require_once FENG_CUSTOM_PATH . 'admin/partials/footer.php';
?>