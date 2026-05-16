<?php
namespace XHTheme\AIToolbox;
class Xhtheme_Block_Template
{

    private static $instance = null;

    const TEMPLATE_SLUG = 'XHTheme AI Toolbox';

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $blockTheme = function_exists('wp_is_block_theme') && wp_is_block_theme();
        if ($blockTheme) {
            $this->template_init();
        }

        // 注册区块样板
        add_action('init', [$this, 'register_block_patterns']);
    }

    public function register_block_patterns()
    {
        if (!function_exists('register_block_pattern') || !function_exists('register_block_pattern_category')) {
            return;
        }

        $reg_patterns = apply_filters('xhaitoolbox_add_block_patterns', []);
        if (empty($reg_patterns)) {
            return;
        }

        register_block_pattern_category(
            'xhaitoolbox',
            array(
                'label' => __('XHTheme AI Toolbox', 'xhtheme-ai-toolbox')
            )
        );

        foreach ($reg_patterns as $slug => $config) {
            register_block_pattern(
                'xhaitoolbox/' . $slug,
                array(
                    'title' => $config['title'],
                    'description' => $config['description'],
                    'content' => $config['content'],
                    'categories' => ['xhaitoolbox']
                )
            );
        }
    }


    public function template_init(){
        add_filter('get_block_templates',           [$this, 'register_templates'], 10, 3);
        add_filter('pre_get_block_file_template',   [$this, 'provide_template_content'], 10, 3);
        add_filter('get_block_template',            [$this, 'update_template_details'], 10, 3);
    }

    public function get_templates(){
        $reg_templates  = apply_filters('xhaitoolbox_add_block_templates', []);
        if(empty($reg_templates)) {
            return [];
        }
        return $reg_templates;
    }


    public function register_templates($query_result, $query, $template_type)
    {
        if (!is_array($query_result)) {
            $query_result = [];
        }
        if ('wp_template' !== $template_type) {
            return $query_result;
        }

        $slugs = isset($query['slug__in']) ? $query['slug__in'] : [];

        $reg_templates = $this->get_templates();
        if (empty($reg_templates)) {
            return $query_result;
        }
        
        foreach ($reg_templates as $slug => $config) {
            if (!empty($slugs) && !in_array($slug, $slugs, true)) {
                continue;
            }
            $already_exists = false;
            foreach ($query_result as $template) {
                if (isset($template->slug) && $template->slug === $slug) {
                    $already_exists = true;
                    break;
                }
            }

            if (!$already_exists) {
                $query_result[] = $this->build_template_object($slug, $config);
            }
        }
        //var_dump($query_result, $query, $template_type);exit;
        return $query_result;
    }

    public function provide_template_content($template, $id, $template_type)
    {
        if ('wp_template' !== $template_type) {
            return $template;
        }

        $template_parts = explode('//', $id);
        if (count($template_parts) < 2) {
            return $template;
        }

        list($theme_slug, $template_slug) = $template_parts;

        // 检查是否是我们的模板
        $reg_templates = $this->get_templates();
        if (!isset($reg_templates[$template_slug])) {
            return $template;
        }

        // 返回构建的模板对象
        return $this->build_template_object($template_slug, $reg_templates[$template_slug]);
    }

    public function update_template_details($block_template, $id, $template_type)
    {
        if (!$block_template || 'wp_template' !== $template_type) {
            return $block_template;
        }

        // 检查是否是我们的模板
        $reg_templates = $this->get_templates();

        if (isset($block_template->slug) && isset($reg_templates[$block_template->slug])) {
            $config = $reg_templates[$block_template->slug];

            // 确保标题和描述正确
            if (empty($block_template->title) || $block_template->title === $block_template->slug) {
                $block_template->title = $config['title'];
            }
            if (empty($block_template->description)) {
                $block_template->description = $config['description'];
            }
        }

        return $block_template;
    }

    private function build_template_object($slug, $config)
    {
        $template = new \WP_Block_Template();

        $theme_slug = get_stylesheet();
        $template->slug = $slug;
        $template->id = $theme_slug . '//' . $slug;
        $template->theme = $theme_slug;
        $template->type = 'wp_template';

        $template->title = $config['title'];
        $template->description = $config['description'];
        $template->content = $config['content'];

        $template->source = 'plugin';
        $template->origin = 'plugin';
        $template->status = 'publish';
        $template->has_theme_file = false;
        $template->is_custom = $config['custom'];
        $template->author = null;
        $template->area = 'uncategorized';
        $template->post_types = $config['post_types'];
        $template->plugin = self::TEMPLATE_SLUG;
        return $template;
    }

}