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
 * 配置类
 * 
 * @package    Feng_Custom
 * @subpackage Feng_Custom/includes
 * @author     阿锋 <mypen@163.com>
 */
class Feng_Custom_Options
{
    /**
     * 
     * @var string
     */
    private $options_name;

    /**
     * 配置项目
     * @var array
     */
    private $options_params;

    /**
     * 配置前缀
     * @var string
     */
    private $options_prefix = 'feng_custom_';

    /**
     * 开关标识，用于配置数据标识
     * @var string
     */
    private $switch_name = 'switch';
    /**
     * 开关数据
     * @var array
     */
    private $switch_data;

    /**
     *
     * @var Feng_Custom_Options
     */
    static private $instance;

    /**
     * 返回实例（单例）
     * @return Feng_Custom_Options
     */
    static public function instance($options_name = '')
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        if ($options_name) {
            self::$instance->options_name = $options_name;
        }
        return self::$instance;
    }

    /**
     * 初始化
     */
    public function __construct()
    {
        // 效果配置项参数
        $this->options_params['option'] = [
            [
                'group' => [
                    'title' => '显示运行天数',
                ],
                'fields' => [
                    'run_open' => [
                        'title' => '显示运行天数',
                        'sub' => '需要设置博客站点开通时间',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'run_start_time' => [
                        'title' => '博客站点开通时间',
                        'sub' => '时间格式：2021-11-19 00:00:00',
                        'type' => 'input',
                        'default' => '2021-05-04 08:00:00',
                        'placeholder' => '例：2021-04-04 00:00:00',
                        'validate' => '',
                    ],
                    'run_binding_element' => [
                        'title' => '绑定页面元素',
                        'sub' => '该绑定元素使用js选择器，例如：.copyright 或 body',
                        'type' => 'input',
                        'default' => 'body',
                        'placeholder' => '例：.copyright',
                    ],
                    'run_show_text' => [
                        'title' => '显示内容',
                        'sub' => '系统会将 {days} 替换为天数<br />例：&lt;p&gt;站点已运行 {days} 天，感谢有你！&lt;p&gt;',
                        'type' => 'textarea',
                        'default' => '<p>站点已运行 {days} 天，感谢有你！<p>',
                        'placeholder' => '例：<p>站点已运行 {days} 天，感谢有你！<p>',
                    ],
                ], // END FIELDS
            ], // END GROUP
            [
                'group' => [
                    'title' => '键盘特效',
                ],
                'fields' => [
                    'keyboard_open' => [
                        'title' => '开启输入框七彩光子特效',
                        'sub' => '',
                        'type' => 'switch',
                        'default' => ''
                    ],
                ], // END FIELDS
            ], // END GROUP
            [
                'group' => [
                    'title' => '点亮灯笼',
                ],
                'fields' => [
                    'lantern_open' => [
                        'title' => '点亮灯笼',
                        'sub' => '',
                        'type' => 'switch',
                        'default' => ''
                    ],
                    'lantern_text_1' => [
                        'title' => '前一个灯笼文字',
                        'sub' => '',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：春节',
                    ],
                    'lantern_text_2' => [
                        'title' => '后一个灯笼文字',
                        'sub' => '',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：快乐',
                    ],
                    'lantern_start_time' => [
                        'title' => '开始时间（可以为空）',
                        'sub' => '到时间后将显示灯笼效果',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：2022-01-01 00:00:00',
                    ],
                    'lantern_end_time' => [
                        'title' => '结束时间（可以为空）',
                        'sub' => '到时间后将不显示灯笼效果',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：2022-01-02 00:00:00',
                    ],
                ], // END FIELDS
            ], // END GROUP
            [
                'group' => [
                    'title' => '博客灰色',
                ],
                'fields' => [
                    'gray_page_open' => [
                        'title' => '将博客设置为灰色模式',
                        'sub' => '',
                        'type' => 'switch',
                        'default' => ''
                    ],
                    'gray_page_start_time' => [
                        'title' => '开始时间（可以为空）',
                        'sub' => '置为灰色模式后，到时自动变灰。',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：2008-05-12 00:00:00'
                    ],
                    'gray_page_end_time' => [
                        'title' => '结束时间（可以为空）',
                        'sub' => '到时自动变灰后，到时间自动恢复。',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：2008-05-13 00:00:00'
                    ],
                    'gray_page_body_bg' => [
                        'title' => '页面背景色',
                        'sub' => '当标签<body>背景为彩色时请设置该项',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：#a9a9a9'
                    ],
                ], // END FIELDS
            ], // END GROUP
            [
                'group' => [
                    'title' => '图片灯箱'
                ],
                'fields' => [
                    'light_box_open' => [
                        'title' => '开启图片灯箱',
                        'sub' => '图片灯箱，采用Fancybox',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'light_box_binding' => [
                        'title' => '绑定图片元素',
                        'sub' => '<span style="color: red;">此选项为空时将采用自动绑定的方式，适用于古藤堡编辑器的图片、图库、媒体和文本。</span><br />采用js选择器绑定元素，例：.wp-block-image img',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '',
                    ],
                ], // END FIELDS
            ], // END GROUP
            [
                'group' => [
                    'title' => '第三方链接',
                ],
                'fields' => [
                    'url_open' => [
                        'title' => '开启第三方链接优化',
                        'sub' => '开启后将使用JS的方式在新窗口打开第三方链接',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'url_source' => [
                        'title' => '添加来源',
                        'sub' => 'JS的方式在第三方链接后将添加该来源：https://ha.cn?utm_source=feng.pub',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：utm_source=feng.pub',
                    ],
                ], // END FIELDS
            ], // END GROUP
        ];

        // 节日氛围配置参数
        $this->options_params['festivals'] = [
            [
                'group' => [
                    'title' => '节日氛围',
                ],
                'fields' => [
                    'open' => [
                        'title' => '开启节日氛围',
                        'sub' => '<p>开启此项后，以下设置才会生效。</p><p style="color: blue;">手动关闭挂件、全屏海报，10钟后刷新页面才会再次显示。</p>',
                        'type' => 'switch',
                        'default' => '',
                    ],
                ], // END FIELDS
            ], // END GROUP
            [
                'group' => [
                    'title' => '元旦<sup>［公历 1月1日］</sup>',
                ],
                'fields' => [
                    'new_year_open' => [
                        'title' => '开启元旦节日氛围',
                        'sub' => '开启后，每年公历1月1日前后，显示元旦节日氛围。',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'new_year_text' => [
                        'title' => '显示的文字',
                        'sub' => '必须是四个汉字，为空时使用“元旦快乐”。',
                        'type' => 'input',
                        'default' => '元旦快乐',
                        'placeholder' => '元旦快乐',
                    ],
                    'new_year_advance' => [
                        'title' => '提前',
                        'sub' => '天显示，0表示不提前，为空表示使用默认值：1',
                        'type' => 'number',
                        'default' => '1',
                        'placeholder' => '1',
                    ],
                    'new_year_delay' => [
                        'title' => '延后',
                        'sub' => '天关闭，0表示不延后，为空表示使用默认值：1',
                        'type' => 'number',
                        'default' => '1',
                        'placeholder' => '1',
                    ],
                    'new_year_position' => [
                        'title' => '设置元旦氛围盒子的位置',
                        'sub' => '不要忘记单位，例如：100px 或 30%',
                        'type' => 'spacing',
                        'default' => ['', '', '', ''],
                        'placeholder' => ['', '', '', ''],
                        'control' => [
                            'tips' => ['上', '右', '下', '左'],
                        ],
                    ],
                ],
            ], // END GROUP
            [
                'group' => [
                    'title' => '春节<sup>［农历 正月初一］</sup>',
                ],
                'fields' => [
                    'spring_festival_open' => [
                        'title' => '开启春节氛围',
                        'sub' => '开启此项后，以下春节设置才会生效。<a target="_blank" href="https://feng.pub/0120246219.html">查看默认效果</a>',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'spring_festival_open_range' => [
                        'title' => '开启时间',
                        'sub' => '',
                        'type' => 'lunar_month_day_range',
                        'option_range' => [
                            'month' => ['12', '1']
                        ],
                        'default' => [
                            'start' => ['month' => 12, 'day' => 22],
                            'end' => ['month' => 1, 'day' => 9]
                        ]
                    ],
                    'spring_festival_open_countdown' => [
                        'title' => '开启春节倒计时挂件',
                        'sub' => '',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'spring_festival_countdown_image' => [
                        'title' => '倒计时挂件图片',
                        'sub' => '倒计时图片URL地址，为空将显示《春》字。',
                        'type' => 'input',
                        'default' => '',
                    ],
                    'spring_festival_open_pendant' => [
                        'title' => '开启春节氛围挂件',
                        'sub' => '',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'spring_festival_pendant_image' => [
                        'title' => '春节氛围挂件图片',
                        'sub' => '图片URL地址，为空将显示《福》字。',
                        'type' => 'input',
                        'default' => '',
                    ],
                    'spring_festival_open_poster' => [
                        'title' => '启用春节全屏海报',
                        'sub' => '',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'spring_festival_poster_image' => [
                        'title' => '春节海报图片',
                        'sub' => '海报图片URL地址，为空将显示灯笼、《福》字。',
                        'type' => 'input',
                        'default' => '',
                    ],
                    'spring_festival_position' => [
                        'title' => '设置春节挂件的位置',
                        'sub' => '不要忘记单位，例如：100px 或 30%',
                        'type' => 'spacing',
                        'default' => ['', '', '', ''],
                        'placeholder' => ['', '', '', ''],
                        'control' => [
                            'tips' => ['上', '右', '下', '左'],
                        ],
                    ],
                ]
                // END GROUP
            ],
            [
                'group' => [
                    'title' => '中秋节<sup>［农历 八月十五］<sup>',
                ],
                'fields' => [
                    'mid_autumn_open' => [
                        'title' => '开启中秋节氛围',
                        'sub' => '开启后，每年农历八月十五前后，自动显示中秋节日氛围。',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'mid_autumn_text' => [
                        'title' => '显示的文字',
                        'sub' => '默认为“中秋快乐”，必须是四个汉字。',
                        'type' => 'input',
                        'default' => '中秋快乐',
                        'placeholder' => '中秋快乐',
                    ],
                    'mid_autumn_advance' => [
                        'title' => '提前',
                        'sub' => '天显示，0表示不提前，为空表示使用默认值：1',
                        'type' => 'number',
                        'default' => '1',
                        'placeholder' => '1',
                    ],
                    'mid_autumn_delay' => [
                        'title' => '延后',
                        'sub' => '天关闭，0表示不延后，为空表示使用默认值：1。',
                        'type' => 'number',
                        'default' => '1',
                        'placeholder' => '1',
                    ],
                ],
            ], // END GROUP
            [
                'group' => [
                    'title' => '国庆节<sup>［公历 10月1日］<sup>',
                ],
                'fields' => [
                    'national_day_open' => [
                        'title' => '开启国庆节氛围',
                        'sub' => '开启后，每年公历10月1日前后，自动显示国庆节日氛围。',
                        'type' => 'switch',
                        'default' => '',
                    ],
                    'national_day_text' => [
                        'title' => '显示的文字',
                        'sub' => '默认为“欢度国庆”，一个字一个灯笼，必须是四个汉字。',
                        'type' => 'input',
                        'default' => '欢度国庆',
                        'placeholder' => '欢度国庆',
                    ],
                    'national_day_advance' => [
                        'title' => '提前',
                        'sub' => '天显示，0表示不提前，为空表示使用默认值：1',
                        'type' => 'number',
                        'default' => '1',
                        'placeholder' => '1',
                    ],
                    'national_day_delay' => [
                        'title' => '延后',
                        'sub' => '天关闭，0表示不延后，为空表示使用默认值：6。',
                        'type' => 'number',
                        'default' => '6',
                        'placeholder' => '6',
                    ],
                ],
            ], // END GROUP
        ];

        // 雪花飘落配置参数
        $this->options_params['snowflake'] = [
            [
                'group' => [
                    'title' => '雪花特效'
                ],
                'fields' => [
                    'open' => [
                        'title' => '开启雪花飘落特效',
                        'sub' => '开启后全站将显示雪花飘落特效',
                        'type' => 'switch',
                        'default' => '',
                    ],
                ],
            ], // END GROUP
            [
                'group' => [
                    'title' => '飘落图片',
                ],
                'fields' => [
                    'images' => [
                        'title' => '飘落图片',
                        'sub' => '输入飘落图片的链接，也可使用网络图片或插件内置图片。<br />多个图片请换一行，每行只能又一个图片链接',
                        'help_url' => 'https://gitee.com/ouros/feng-custom/wikis/%E4%BD%BF%E7%94%A8%E5%B8%AE%E5%8A%A9/%E9%9B%AA%E8%8A%B1%E7%89%B9%E6%95%88',
                        'type' => 'textarea',
                        'default' => '',
                        'placeholder' => '',
                    ],
                ],
            ], // END GROUP
            [
                'group' => [
                    'title' => '飘落字符',
                ],
                'fields' => [
                    'text' => [
                        'title' => '飘落字符',
                        'sub' => '推荐使用：✻，多个字符请用半角逗号隔开。',
                        'type' => 'input',
                        'default' => '',
                        'placeholder' => '例：❄,❉,❅,❆,✻,✼,❈,❊,✥,✺,坏蛋,哈哈,〇,😁,❄️,🧨',
                    ],
                    'color' => [
                        'title' => '字符颜色',
                        'sub' => '推荐使用：#FFFFFF，多种颜色请用半角逗号隔开。',
                        'type' => 'input',
                        'default' => '#FEFEFE',
                        'placeholder' => '默认：#FEFEFE',
                    ],
                ],
            ], // END GROUP
            [
                'group' => [
                    'title' => '飘落参数',
                ],
                'fields' => [
                    'size' => [
                        'title' => '雪花大小',
                        'sub' => '雪花的大小将在该范围内随机生成。',
                        'unit' => 'px',
                        'type' => 'number_range',
                        'default' => [
                            'min' => '1',
                            'max' => '6',
                        ],
                        'placeholder' => [
                            'min' => '默认：1',
                            'max' => '默认：6',
                        ],
                    ],
                    'speed' => [
                        'title' => '飘落速度',
                        'sub' => '数字越大速度越快，最大速度不建议大于10。',
                        'unit' => '',
                        'type' => 'number_range',
                        'default' => [
                            'min' => '1',
                            'max' => '5',
                        ],
                        'placeholder' => [
                            'min' => '默认：1',
                            'max' => '默认：5',
                        ],
                    ],
                    'count' => [
                        'title' => '飘落数量',
                        'sub' => '个 （页面飘落的总数，不建议过多)。',
                        'type' => 'number',
                        'default' => '10',
                        'placeholder' => '默认：10',
                    ],
                    'zIndex' => [
                        'title' => '图层等级',
                        'sub' => '如果雪花被覆盖，请设置为更大的数字，一般无需设置',
                        'type' => 'number',
                        'default' => '100',
                        'placeholder' => '默认：100',
                    ],
                ],
            ], // END GROUP
        ];

        // 链接&RSS配置项参数
        $this->options_params['links'] = [
            [
                'group' => [
                    'title' => '链接功能（友链）',
                    'help_url' => 'https://gitee.com/ouros/feng-custom/wikis/%E4%BD%BF%E7%94%A8%E5%B8%AE%E5%8A%A9/%E9%93%BE%E6%8E%A5%E5%8A%9F%E8%83%BD'
                ],
                'fields' => [
                    'open' => [
                        'title' => '开启链接功能',
                        'sub' => '开启后通过「后台 -> 链接」管理链接及分类',
                        'type' => 'switch',
                        'default' => ''
                    ],
                    'page_id' => [
                        'title' => '选择链接页面',
                        'sub' => '',
                        'type' => 'select_page',
                        'option_none' => '&mdash; 选择链接页面 &mdash;',
                        'option_none_value' => 0,
                        'default' => '',
                        'validate' => 'wp_page',
                    ],
                    'show_link_category' => [
                        'title' => '选择链接页面显示的分类',
                        'sub' => '',
                        'type' => 'checkbox_terms_link',
                        'default' => '',
                        'validate' => 'wp_term:link_category',
                    ],
                    'link_category_order' => [
                        'title' => '选择链接分类的排序方式',
                        'sub' => '',
                        'type' => 'select',
                        'options' => [
                            'name-asc' => '按名称升序↑',
                            'name-desc' => '按名称降序↓',
                            'id-asc' => '按ID升序↑',
                            'id-desc' => '按ID降序↓',
                        ],
                        'default' => 'name-asc',
                        'validate' => 'require',
                    ],
                    'link_card_order' => [
                        'title' => '选择链接卡片的排序方式',
                        'sub' => '',
                        'type' => 'select',
                        'options' => [
                            'rand' => '随机排序',
                            'name-asc' => '按名称升序↑',
                            'name-desc' => '按名称降序↓',
                            'id-asc' => '按ID升序↑',
                            'id-desc' => '按ID降序↓',
                            'rating-asc' => '按评级升序↑',
                        ],
                        'default' => 'rand',
                        'validate' => 'require',
                    ],
                    'link_card_style' => [
                        'title' => '链接卡片样式',
                        'sub' => '选择链接卡片样式',
                        'type' => 'radio_image',
                        'options' => [
                            '1' => [
                                'title' => '',
                                'url' => FENG_CUSTOM_URL . 'admin/img/links-page-card-1.png?v=' . FENG_CUSTOM_VERSION,
                            ],
                            '2' => [
                                'title' => '',
                                'url' => FENG_CUSTOM_URL . 'admin/img/links-page-card-2.png?v=' . FENG_CUSTOM_VERSION,
                            ],
                        ],
                        'default' => '1',
                        'validate' => 'require',
                    ],
                ] // END fields
            ], // END GROUP
            [
                'group' => [
                    'title' => 'RSS聚合功能（友圈）',
                ],
                'fields' => [
                    'rss_open' => [
                        'title' => '开启RSS聚合功能',
                        'sub' => '需要在链接项目中设置有效的RSS地址',
                        'type' => 'switch',
                        'default' => ''
                    ],
                    'rss_page_id' => [
                        'title' => 'RSS聚合页面',
                        'sub' => '选择RSS聚合页面',
                        'type' => 'select_page',
                        'option_none' => '&mdash; 选择RSS聚合页面 &mdash;',
                        'option_none_value' => '',
                        'default' => '',
                        'validate' => 'wp_page',
                    ],
                    'show_rss_category' => [
                        'title' => 'RSS聚合来源分类（试图获取选中链接分类下的链接的RSS源）',
                        'sub' => '',
                        'type' => 'checkbox_terms_link',
                        'default' => '',
                        'validate' => 'wp_term:link_category'
                    ],
                    'rss_page_limit' => [
                        'title' => '显示聚合数量',
                        'sub' => '默认：50',
                        'type' => 'number',
                        'placeholder' => 50,
                        'default' => 50,
                        'validate' => 'number',
                    ],
                    'rss_cache_expire' => [
                        'title' => 'RSS缓存过期时间',
                        'sub' => '秒，（默认：3600秒，0表示不缓存）',
                        'type' => 'number',
                        'placeholder' => 3600,
                        'default' => 3600,
                        'validate' => 'number',
                    ],
                    'rss_cron_open' => [
                        'title' => '开启检查RSS定时计划',
                        'sub' => '请确保WP-Cron可用，推荐使用系统任务执行WP-Cron定时任务。',
                        'type' => 'switch',
                        'default' => '',
                    ],
                ] // END fields
            ], // END GROUP
            [
                'group' => [
                    'title' => '自定义样式',
                    'help_url' => 'https://gitee.com/ouros/feng-custom/wikis/%E4%BD%BF%E7%94%A8%E5%B8%AE%E5%8A%A9/%E9%93%BE%E6%8E%A5%E5%8A%9F%E8%83%BD',
                ],
                'fields' => [
                    'css_style' => [
                        'title' => '自定义CSS样式',
                        'sub' => '',
                        'type' => 'textarea',
                        'default' => '',
                        'placeholder' => '',
                    ],
                ] // END fields
            ], // END GROUP
            [
                'group' => [
                    'title' => '显示支持信息',
                ],
                'fields' => [
                    'show_prowered' => [
                        'title' => '链接及RSS聚合页面内显示：Links & RSS Prowered by <a target="_blank" href="https://feng.pub/feng-custom">Feng Custom</a>',
                        'sub' => '',
                        'type' => 'switch',
                        'default' => '',
                    ],
                ] // END fields
            ], // END GROUP
        ]; // END links params


    }

    /**
     * 获取配置项的参数
     * @param string $option_name
     */
    public function get_options_params($option_name = null)
    {
        if (empty($option_name)) {
            return $this->options_params;
        } else if ($option_name && isset($this->options_params[$option_name])) {
            return $this->options_params[$option_name];
        }
        return [];
    }

    /**
     * 获取配置项的数据（包含默认数据）
     * @param string $option_name
     * @return mixed
     */
    public function get_options_data($option_name = null)
    {
        if ($option_name) {
            $default = $this->get_options_default_data($this->options_params[$option_name]);
            return $this->get_option($option_name, $default, 'serialize');
        } else {
            // ————————————————————————
            // 获取插件的所有配置数据
            // ————————————————————————
            $options_data = [];
            foreach ($this->options_params as $name => $params) {
                $default = $this->get_options_default_data($name);
                // 获取配置数据
                $options_data[$name] = $this->get_option($name, $default, 'serialize');
            }

            return $options_data;
        }
    }

    /**
     * 获取配置默认数据
     * @param string|array $params
     */
    private function get_options_default_data($params = null)
    {
        if (is_string($params)) {
            $params = isset($this->options_params[$params]) ? $this->options_params[$params] : [];
        }

        if (!empty($params)) {
            foreach ($params as $value) {
                if (isset($value['fields'])) {
                    foreach ($value['fields'] as $field_name => $field) {
                        $default[$field_name] = isset($field['default']) ? $field['default'] : '';
                    }
                }
            }
        } else {
            $params = $this->options_params;
            foreach ($params as $param_name => $param_data) {
                foreach ($param_data as $value) {
                    if (isset($value['fields'])) {
                        foreach ($value['fields'] as $field_name => $field) {
                            $default[$param_name][$field_name] = isset($field['default']) ? $field['default'] : '';
                        }
                    }
                }
            }
        }

        return $default;
    }

    /**
     * 处理POST请求，保存提交的配置数据，构建缓存文件
     * @param string $options_name 功能配置名
     * @param string $callback 成功后回调，用于个性化操作
     * @return array
     */
    public function submit_options($options_name)
    {
        // ————————————————————————
        // 验证请求
        // ————————————————————————
        require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-validate.php';
        $ValidateClass = Feng_Custom_Validate::instance();
        $reslut = $ValidateClass->verify_nonce('action_nonce', 'fct-submit-options-' . $options_name);
        if ($reslut === false) {
            return $this->return_result(0, $ValidateClass->get_error());
        }

        $reslut = $this->update_options($options_name, $_POST);

        if ($reslut['status']) {
            // ————————————————————————
            // 构建js、css文件及其他缓存
            // ————————————————————————
            require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-build.php';
            $BuildClass = Feng_Custom_Build::instance($this->get_options_data());
            if ($BuildClass->build($options_name) === false) {
                return $this->return_result(0, '配置数据已保存，但构建文件出错：' . $BuildClass->get_error(), $reslut['data']);
            }
        }

        return $reslut;
    }

    /**
     * 更新配置，构建缓存文件
     * @param string $options_name 配置项
     * @param string $options_data 配置项数据
     * @return string[][]|boolean[][]|array[][]|array[]
     */
    public function update_options($options_name, $options_data)
    {
        // ————————————————————————
        // 验证并处理数据
        // ————————————————————————
        // 配置参数
        $options_params = isset($this->options_params[$options_name]) ? $this->options_params[$options_name] : [];
        if (empty($options_params)) {
            return $this->return_result(0, '未定义' . $options_name . '配置项的参数，请检查源代码。');
        }
        // 默认配置数据
        $default_data = $this->get_options_default_data($options_params);
        $options_data = wp_parse_args($options_data, $default_data);
        // 定义保存的数据变量
        $save_data = [];
        require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-validate.php';
        $ValidateClass = Feng_Custom_Validate::instance();
        foreach ($options_params as $value) {
            foreach ($value['fields'] as $field_name => $field_params) {
                // 验证数据
                if (isset($field_params['validate'])) {
                    $field_validate = $field_params['validate'];
                } else {
                    $field_validate = in_array($field_params['type'], [
                        'number'
                    ]) ? $field_params['type'] : '';
                }
                if ($field_validate) {
                    $reslut = $ValidateClass->validate($field_name, $field_validate, $options_data);
                    if ($reslut === false) {
                        return $this->return_result(0, $field_params['title'] . ' ' . $ValidateClass->get_error(), $submit_data);
                    }
                }
                // 重置数据
                switch ($field_params['type']) {
                    case 'spacing':
                        $save_data[$field_name] = $options_data[$field_name];
                        break;
                    case 'switch':
                        //                         echo '<h2>' . $field_name. '</h2><pre>'; var_dump($options_data[$field_name]); echo '</pre><hr />';
                        $save_data[$field_name] = isset($options_data[$field_name]) ? $options_data[$field_name] : '';
                        break;
                    default:
                        $save_data[$field_name] = $options_data[$field_name];
                        break;
                }
            }
        }

        // ————————————————————————
        // 保存数据
        // ————————————————————————
        // 保持配置数据
        update_option($this->build_option_name($options_name), $save_data);

        /**
         * open 为功能开关，需单独保存
         *
         * @var string $switch_option_name
         */
        $switch_option_name = $this->build_option_name($this->switch_name);
        if (isset($save_data['open'])) {
            // 获取功能开关配置数据
            $switch_option_data = wp_parse_args([
                $options_name => $save_data['open']
            ], get_option($switch_option_name));
        } else {
            $switch_option_data = get_option($switch_option_name);
        }
        $switch = [];
        foreach ($save_data as $key => $value) {
            if (strpos($key, '_open') !== false) {
                // 找到开关
                $switch[$options_name . '.' . $key] = $value;
            }
        }
        if (!empty($switch)) {
            $switch_option_data = wp_parse_args($switch, $switch_option_data);
        }
        // 更新功能开关配置数据
        update_option($switch_option_name, $switch_option_data);

        return $this->return_result(1, '保存成功！', $save_data);

    }

    /**
     * 构建配置名字符串，用于数据库存储
     * @param string $options_name
     * @param string $option_name
     * @return string
     */
    private function build_option_name($options_name = null)
    {
        if (empty($options_name)) {
            return $this->options_prefix . $this->options_name;
        } else {
            return $this->options_prefix . $options_name;
        }
    }

    /**
     * 获取功能开关
     * @param string $name 配置名称
     * @param boolean $need_refresh 强制刷新开关配置数据
     * @return array|string|false 未获取到数据将返回false
     */
    public function get_switch($name = null, $need_refresh = false)
    {
        if ($need_refresh === true) {
            $this->switch_data = $this->get_option('switch');
        }
        if (empty($name)) {
            $switch_data = $this->switch_data ? $this->switch_data : $this->get_option('switch');
        } else {
            $this->switch_data = $this->switch_data ? $this->switch_data : $this->get_option('switch');
            $switch_data = isset($this->switch_data[$name]) ? $this->switch_data[$name] : false;
        }
        return $switch_data;
    }

    /**
     * 更新开关数据
     * @param string $name
     * @param int $value 1|0
     * @return boolean
     */
    public function update_switch($name, $value)
    {
        // 获取当前开关数据
        if (strpos($name, ".") !== false) {
            // 包含 . 符号
            $nameArr = explode(".", $name);
            $options_name = $nameArr[0];
            $open_name = $nameArr[1];
        } else {
            $options_name = $name;
            $open_name = 'open';
        }

        $options_data = $this->get_options_data($options_name);
        $options_data[$open_name] = $value;
        $this->update_options($options_name, $options_data);

        $this->switch_data = $this->get_option($this->switch_name);
        return true;
    }

    /**
     * 处理结果
     * @param string|boolean $status
     * @param string|array $message
     * @param array $data
     */
    private function return_result($status, $message, $data = [])
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }


    /**
     * 对WordPress提供的get_option()进行扩展
     * @param string $option
     * @param mixed $default_value
     * @param string $type `string\json\serialize`
     * @return array|string
     */
    public function get_option($option, $default_value = false, $type = 'string')
    {
        // 获取配置数据
        $data = get_option($this->build_option_name($option));
        if ($type === 'json') {
            // 解析json字符串
            $json_data = json_decode($data);
            if ($json_data !== null) {
                // 不是json格式直接返回
                return $data;
            }
        }
        $data = maybe_unserialize($data);
        if ($default_value) {
            if (is_array($data)) {
                // 合并数组及值
                return $this->merge_options_data($data, $default_value);
            } else {
                return $data ? $data : $default_value;
            }
        } else {
            return $data;
        }
    }

    /**
     * 合并配置数据
     * @param string|array $data
     * @param string|array $default
     * @return string|array
     */
    private function merge_options_data($data, $default)
    {
        if (is_array($default)) {
            foreach ($default as $key => $value) {
                if (isset($data[$key])) {
                    if (is_array($data[$key])) {
                        $data[$key] = $this->merge_options_data($data[$key], $value);
                    } else {
                        $data[$key] = !empty($data[$key]) || strlen($data[$key]) > 0 ? $data[$key] : $value;
                    }
                } else {
                    $data[$key] = $value;
                }
            }
        } else {
            $data = $data ? $data : $default;
        }
        return $data;
    }



}