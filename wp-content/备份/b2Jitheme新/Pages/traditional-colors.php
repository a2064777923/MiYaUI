<?php
/**
 * Template Name: 传统颜色大全
 * Description: 展示中国传统颜色的精美页面
 */

get_header();
?>

<div class="traditional-colors-container">
    <header class="colors-header">
        <h1>中国传统色彩大全</h1>
        <p class="subtitle">探索中华五千年文化中的瑰丽色彩</p>
    </header>

    <div class="color-categories">
        <button class="category-btn active" data-category="all">全部颜色</button>
        <button class="category-btn" data-category="red">红色系</button>
        <button class="category-btn" data-category="yellow">黄色系</button>
        <button class="category-btn" data-category="blue">蓝色系</button>
        <button class="category-btn" data-category="green">绿色系</button>
        <button class="category-btn" data-category="purple">紫色系</button>
        <button class="category-btn" data-category="brown">褐色系</button>
        <button class="category-btn" data-category="gray">灰色系</button>
    </div>

    <div class="colors-grid">
        <?php
        // 中国传统颜色数据
        $colors = array(
            // 红色系
            array('name' => '朱砂红', 'hex' => '#ff461f', 'category' => 'red', 'pinyin' => 'Zhū Shā Hóng', 'desc' => '源自朱砂矿石的鲜艳红色'),
            array('name' => '胭脂红', 'hex' => '#9d2933', 'category' => 'red', 'pinyin' => 'Yān Zhī Hóng', 'desc' => '古代女子化妆用的胭脂颜色'),
            array('name' => '绛红', 'hex' => '#8c4356', 'category' => 'red', 'pinyin' => 'Jiàng Hóng', 'desc' => '深沉的暗红色，古代贵族服饰常用'),
            array('name' => '桃红', 'hex' => '#f47983', 'category' => 'red', 'pinyin' => 'Táo Hóng', 'desc' => '如桃花般的粉红色'),
            array('name' => '海棠红', 'hex' => '#db5a6b', 'category' => 'red', 'pinyin' => 'Hǎi Táng Hóng', 'desc' => '海棠花开的颜色'),
            
            // 黄色系
            array('name' => '明黄', 'hex' => '#ffd700', 'category' => 'yellow', 'pinyin' => 'Míng Huáng', 'desc' => '帝王专用色，象征皇权'),
            array('name' => '杏黄', 'hex' => '#ffa631', 'category' => 'yellow', 'pinyin' => 'Xìng Huáng', 'desc' => '成熟杏子的颜色'),
            array('name' => '秋香色', 'hex' => '#d9b611', 'category' => 'yellow', 'pinyin' => 'Qiū Xiāng Sè', 'desc' => '秋天稻谷成熟的颜色'),
            array('name' => '琥珀黄', 'hex' => '#ca6924', 'category' => 'yellow', 'pinyin' => 'Hǔ Pò Huáng', 'desc' => '如琥珀般的金黄色'),
            array('name' => '缃色', 'hex' => '#f0c239', 'category' => 'yellow', 'pinyin' => 'Xiāng Sè', 'desc' => '浅黄色，古代指丝织品的颜色'),
            
            // 蓝色系
            array('name' => '靛青', 'hex' => '#065279', 'category' => 'blue', 'pinyin' => 'Diàn Qīng', 'desc' => '从蓝草中提取的深蓝色'),
            array('name' => '天青', 'hex' => '#2e8de1', 'category' => 'blue', 'pinyin' => 'Tiān Qīng', 'desc' => '雨过天晴的天空颜色'),
            array('name' => '宝蓝', 'hex' => '#4b5cc4', 'category' => 'blue', 'pinyin' => 'Bǎo Lán', 'desc' => '如宝石般珍贵的蓝色'),
            array('name' => '月白', 'hex' => '#d6ecf0', 'category' => 'blue', 'pinyin' => 'Yuè Bái', 'desc' => '月光下的淡蓝色'),
            array('name' => '藏蓝', 'hex' => '#3b2e7e', 'category' => 'blue', 'pinyin' => 'Zàng Lán', 'desc' => '深邃如西藏天空的蓝色'),
            
            // 绿色系
            array('name' => '松柏绿', 'hex' => '#21a675', 'category' => 'green', 'pinyin' => 'Sōng Bǎi Lǜ', 'desc' => '松柏叶的深绿色'),
            array('name' => '柳绿', 'hex' => '#afdd22', 'category' => 'green', 'pinyin' => 'Liǔ Lǜ', 'desc' => '春天柳叶的嫩绿色'),
            array('name' => '翡翠绿', 'hex' => '#3de1ad', 'category' => 'green', 'pinyin' => 'Fěi Cuì Lǜ', 'desc' => '翡翠宝石般的绿色'),
            array('name' => '竹青', 'hex' => '#789f6b', 'category' => 'green', 'pinyin' => 'Zhú Qīng', 'desc' => '竹子的青绿色'),
            array('name' => '碧色', 'hex' => '#7bcfa6', 'category' => 'green', 'pinyin' => 'Bì Sè', 'desc' => '青绿色的美称'),
            
            // 紫色系
            array('name' => '紫檀', 'hex' => '#4c221b', 'category' => 'purple', 'pinyin' => 'Zǐ Tán', 'desc' => '紫檀木的深紫色'),
            array('name' => '藕荷色', 'hex' => '#e4c6d0', 'category' => 'purple', 'pinyin' => 'Ŏu Hé Sè', 'desc' => '浅紫略带粉红的颜色'),
            array('name' => '黛紫', 'hex' => '#574266', 'category' => 'purple', 'pinyin' => 'Dài Zǐ', 'desc' => '古代女子画眉的深紫色'),
            array('name' => '青莲色', 'hex' => '#801dae', 'category' => 'purple', 'pinyin' => 'Qīng Lián Sè', 'desc' => '青紫色莲花般的颜色'),
            array('name' => '丁香色', 'hex' => '#cca4e3', 'category' => 'purple', 'pinyin' => 'Dīng Xiāng Sè', 'desc' => '丁香花的淡紫色'),
            
            // 褐色系
            array('name' => '赭色', 'hex' => '#955539', 'category' => 'brown', 'pinyin' => 'Zhě Sè', 'desc' => '赤铁矿的颜色，古代常用颜料'),
            array('name' => '茶色', 'hex' => '#b35c44', 'category' => 'brown', 'pinyin' => 'Chá Sè', 'desc' => '浓茶的颜色'),
            array('name' => '棕褐', 'hex' => '#6b4e31', 'category' => 'brown', 'pinyin' => 'Zōng Hè', 'desc' => '深棕带褐的颜色'),
            array('name' => '驼色', 'hex' => '#a88462', 'category' => 'brown', 'pinyin' => 'Tuó Sè', 'desc' => '骆驼毛的颜色'),
            array('name' => '酱色', 'hex' => '#793a13', 'category' => 'brown', 'pinyin' => 'Jiàng Sè', 'desc' => '酱油的深褐色'),
            
            // 灰色系
            array('name' => '银灰', 'hex' => '#bacac6', 'category' => 'gray', 'pinyin' => 'Yín Huī', 'desc' => '带有金属光泽的灰色'),
            array('name' => '鸦青', 'hex' => '#424c50', 'category' => 'gray', 'pinyin' => 'Yā Qīng', 'desc' => '乌鸦羽毛的青灰色'),
            array('name' => '墨灰', 'hex' => '#758a99', 'category' => 'gray', 'pinyin' => 'Mò Huī', 'desc' => '水墨画中的灰色调'),
            array('name' => '铅白', 'hex' => '#f0f0f4', 'category' => 'gray', 'pinyin' => 'Qiān Bái', 'desc' => '略带灰调的白色'),
            array('name' => '石青', 'hex' => '#7b909d', 'category' => 'gray', 'pinyin' => 'Shí Qīng', 'desc' => '石头的青灰色')
        );

        // 生成颜色卡片
        foreach ($colors as $color) {
            echo '<div class="color-card" data-category="' . esc_attr($color['category']) . '">';
            echo '<div class="color-preview" style="background-color: ' . esc_attr($color['hex']) . '"></div>';
            echo '<div class="color-info">';
            echo '<h3 class="color-name">' . esc_html($color['name']) . '</h3>';
            echo '<p class="color-pinyin">' . esc_html($color['pinyin']) . '</p>';
            echo '<div class="color-code">';
            echo '<span class="hex-code">' . esc_html($color['hex']) . '</span>';
            echo '<button class="copy-btn" data-clipboard-text="' . esc_attr($color['hex']) . '">复制</button>';
            echo '</div>';
            echo '<p class="color-desc">' . esc_html($color['desc']) . '</p>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<style>
/* 主容器样式 */
.traditional-colors-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    font-family: 'Noto Sans SC', sans-serif;
}

/* 页眉样式 */
.colors-header {
    text-align: center;
    margin-bottom: 3rem;
}

.colors-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.colors-header .subtitle {
    font-size: 1.1rem;
    color: #666;
    font-style: italic;
}

/* 分类按钮样式 */
.color-categories {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.8rem;
    margin-bottom: 2.5rem;
}

.category-btn {
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 30px;
    background-color: #f5f5f5;
    color: #555;
    cursor: pointer;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.category-btn:hover {
    background-color: #e0e0e0;
}

.category-btn.active {
    background-color: #333;
    color: white;
}

/* 颜色网格布局 */
.colors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

/* 颜色卡片样式 */
.color-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.color-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.color-preview {
    height: 150px;
    width: 100%;
}

.color-info {
    padding: 1.2rem;
}

.color-name {
    margin: 0 0 0.3rem 0;
    font-size: 1.2rem;
    color: #333;
    font-weight: 600;
}

.color-pinyin {
    margin: 0 0 0.8rem 0;
    font-size: 0.9rem;
    color: #666;
    font-style: italic;
}

.color-code {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.8rem;
    background: #f8f8f8;
    padding: 0.5rem 0.8rem;
    border-radius: 5px;
}

.hex-code {
    font-family: monospace;
    font-size: 0.95rem;
    color: #444;
}

.copy-btn {
    background: #333;
    color: white;
    border: none;
    padding: 0.3rem 0.7rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: background 0.2s;
}

.copy-btn:hover {
    background: #555;
}

.copy-btn:active {
    transform: scale(0.95);
}

.color-desc {
    margin: 0;
    font-size: 0.9rem;
    color: #666;
    line-height: 1.5;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .colors-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    
    .colors-header h1 {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .traditional-colors-container {
        padding: 1rem;
    }
    
    .colors-grid {
        grid-template-columns: 1fr;
    }
    
    .color-categories {
        gap: 0.5rem;
    }
    
    .category-btn {
        padding: 0.5rem 0.9rem;
        font-size: 0.85rem;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // 分类过滤功能
    $('.category-btn').on('click', function() {
        const category = $(this).data('category');
        
        // 更新按钮状态
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        
        // 过滤颜色卡片
        if (category === 'all') {
            $('.color-card').fadeIn(300);
        } else {
            $('.color-card').each(function() {
                if ($(this).data('category') === category) {
                    $(this).fadeIn(300);
                } else {
                    $(this).fadeOut(300);
                }
            });
        }
    });
    
    // 复制颜色代码功能
    $('.copy-btn').on('click', function() {
        const hexCode = $(this).data('clipboard-text');
        const tempInput = $('<input>');
        
        $('body').append(tempInput);
        tempInput.val(hexCode).select();
        
        try {
            document.execCommand('copy');
            
            // 显示复制成功反馈
            const originalText = $(this).text();
            $(this).text('已复制!');
            
            setTimeout(() => {
                $(this).text(originalText);
            }, 1500);
        } catch (err) {
            console.error('复制失败:', err);
        }
        
        tempInput.remove();
    });
});
</script>

<?php
get_footer();
?>