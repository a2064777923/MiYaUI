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
 * 表单类
 * 
 * @author 阿锋
 *
 */
class Feng_Custom_Forms {
    
    /**
     * 表单字段id前缀
     * @var string
     */
    private $field_prefix = 'fct_';
    
    /**
     * 配置名
     * @var string
     */
    private $option_name;
    
    /**
     * 
     * @var Feng_Custom_Forms
     */
    static private $instance;
    
    /**
     * 返回实例（单例）
     * @return Feng_Custom_Forms
     */
    static public function instance($option_name = null) {
        if(!self::$instance) self::$instance = new self();
        if ($option_name) {
            self::$instance->option_name = $option_name;
        }
        return self::$instance;
    }
    
    /**
     * 渲染
     * @param string $name
     * @param array $params
     * @param array|string|int $value
     */
    public function render($name, $params, $value = null) {
        $render_type = str_replace('-', '_', 'render_' . $params['type']);
        if (method_exists(self::instance(),  $render_type)) {
            call_user_func('self::' . $render_type, $name, $params, $value);
        }else {
            ?>
            <div class="field-box">
                <fieldset>
                    <label class="label-title"><?php echo $params['title']; ?></label>
    			</fieldset>
                <p >未定义 <?php echo $render_type; ?></p>
            </div>
            <?php 
        }
    }
    
    /**
     * 渲染 input 单行输入框
     * @param string $name
     * @param array $params
     * @param string $value
     */
    public function render_input($name, $params, $value = null) {
        $filed_id = $this->build_field_id($name);
        ?>
        <div class="field-box">
        	<label class="label-title" for="<?php echo $filed_id; ?>">
        		<?php echo $params['title']; ?>
        	</label>
			<input name="<?php echo $name; ?>" type="text"
				id="<?php echo $filed_id; ?>" class="regular-text"
				value="<?php echo $value; ?>"
				placeholder="<?php echo isset($params['placeholder']) ? $params['placeholder'] : ''; ?>" />
			<p class="description"><?php echo $params['sub']; ?></p>
        </div>
        <?php 
    }
    
    /**
     * 渲染开关
     * @param string $name
     * @param array $params
     * @param string $value
     */
    public function render_switch($name, $params, $open = '') {
        $open ? $open = '1' : $open = '';
        ?>
        <div class="field-box">
    		<fieldset>
    			<legend class="screen-reader-text">
    				<span><?php echo $params['title']; ?></span>
    			</legend>
    			<label for="<?php echo $this->build_field_id($name); ?>">
    				<input name="<?php echo $name; ?>" type="checkbox" id="<?php echo $this->build_field_id($name); ?>" value="1" <?php checked( '1', $open ); ?> />
    				<?php echo $params['title']; ?>
    			</label>
    		</fieldset>
    		<p class="description"><?php echo $params['sub'] ?></p>
		</div>
        <?php 
    }
    
    /**
     * 渲染 select
     * @param string $name
     * @param array $params
     * @param array|string|int $value
     */
    public function render_select($name, $params, $value = null) {
        ?>
        <div class="field-box">
            <fieldset>
            	<p>
            		<label class="label-title" for="<?php echo $this->build_field_id($name); ?>"><?php echo $params['title']; ?></label>
            	</p>
            	<select name="<?php echo $name; ?>" <?php echo $this->build_field_id($name); ?>">
            		<?php
            		if (isset($params['option_none'])) {
            		    ?>
            		    <option value="<?php echo isset($params['option_none_value']) ? $params['option_none_value'] : ''; ?>"><?php echo $params['option_none']; ?></option>
            		    <?php
            		}
                	foreach ($params['options'] as $key => $title) {
                	    ?> 
                	    <option class="level-0" value="<?php echo $key; ?>" <?php selected( $key, $value ); ?>><?php echo $title; ?></option>
                	    <?php 
                	}
                	?>
                </select>
    		</fieldset>
		</div>
        <?php 
    }
    
    /**
     * 渲染 textarea 多换行文本区
     * @param string $name
     * @param array $params
     * @param string $value
     */
    public function render_textarea($name, $params, $value = null) {
        ?>
        <div class="field-box">
            <fieldset>
            	<label class="label-title" <?php echo $this->build_field_id($name); ?>><?php echo $params['title']; ?></label>
            	<?php 
            	if (isset($params['help_url'])) {
            	?>
            	<a class="help_btn" href="<?php echo $params['help_url']; ?>"
    				title="<?php esc_html_e('点击获取帮助', 'feng-custom'); ?>"
    				target="_blank">?</a>
            	<?php 
            	}
            	?>
            </fieldset>
            <p>
            	<textarea name="<?php echo $name; ?>" rows="5" cols="50"
    				id="<?php echo $this->build_field_id($name); ?>" 
    				placeholder="<?php echo $params['placeholder']; ?>"
    				class="large-text code"><?php echo esc_html( stripslashes($value) ); ?></textarea>
    		</p>
    		<p class="description">
    			<?php echo $params['sub']; ?>
    		</p>
		</div>
        <?php 
    }
    
    /**
     * 渲染 radio 单选图片样式
     * @param string $name
     * @param array $params
     * @param array|string|int $value
     */
    public function render_radio_image($name, $params, $value = null) {
        ?>
        <div class="field-box">
            <fieldset>
            	<p><?php echo $params['title']; ?></p>
    		</fieldset>
    		<div>
    		<?php 
    		foreach ($params['options'] as $key => $data) {
    		    ?>
    		    <label style="display: inline-block; vertical-align: top; text-align: center;">
    				<input type="radio" name="<?php echo $name; ?>" value="<?php echo $key; ?>" <?php checked( $key, $value ); ?>>
    				<br />
    				<img src="<?php echo $data['url']; ?>" title="<?php echo $data['title']; ?>" />
    			</label>
    		    <?php 
    		}
    		?>
			</div>
		</div>
        <?php 
    }
    
    /**
     * 渲染 number 
     * @param string $name
     * @param array $params
     * @param array|string|int $value
     */
    public function render_number($name, $params, $value = null) {
        ?>
        <div class="field-box">
            <fieldset>
            	<label for="<?php echo $this->build_field_id($name); ?>"><?php echo $params['title']; ?></label>
    			<input name="<?php echo $name; ?>" type="number" id="<?php echo $this->build_field_id($name); ?>"
            		value="<?php echo esc_attr($value); ?>"
            		placeholder="<?php echo $params['placeholder']; ?>" class="small-text"> 
            	<label for="<?php echo $this->build_field_id($name); ?>"><?php echo $params['sub']; ?></label>
    		</fieldset>
		</div>
        <?php 
    }
    
    /**
     * 渲染 number 范围
     * @param string $name
     * @param array $params
     * @param array|string|int $value
     */
    public function render_number_range($name, $params, $value = null) {
       ?>
       <div class="field-box">
       		<fieldset>
           		<label class="label-title" for="<?php echo $this->build_field_id($name, 'min'); ?>"><?php echo $params['title']; ?></label>
				<input name="<?php echo $name . '[min]'; ?>" type="number"
               		id="<?php echo $this->build_field_id($name, 'min'); ?>"
               		value="<?php echo $value['min']; ?>"
               		placeholder="<?php echo $params['placeholder']['min']; ?>"
               		class="small-text"> <?php echo isset($params['unit']) ? $params['unit'] : ''; ?>
           		<label for="<?php echo $this->build_field_id($name, 'max'); ?>">&nbsp;&nbsp; ~ &nbsp;&nbsp;</label>
               	<input name="<?php echo $name . '[max]'; ?>" type="number"
               		id="<?php echo $this->build_field_id($name, 'max'); ?>"
               		value="<?php echo $value['max']; ?>"
               		placeholder="<?php echo $params['placeholder']['max'] ?>"
               		class="small-text"> <?php echo isset($params['unit']) ? $params['unit'] : ''; ?>
           	</fieldset>
           	<p class="description"><?php echo $params['sub']; ?></p>
       	</div>
       	<?php 
    }
    
    /**
     * 渲染select 选择页面
     * @param string $name
     * @param array $params
     * @param array|string|int $value
     */
    public function render_select_page($name, $params, $value = null) {
        // 获取页面
        $has_pages = (bool) get_posts(
            array(
                'post_type'      => 'page',
                'posts_per_page' => 1,
                'post_status'    => array( 'publish', 'draft' ),
            )
        );
        ?>
        <div class="field-box">
            <fieldset>
            	<p>
            		<label class="label-title" <?php echo $this->build_field_id($name); ?>><?php echo $params['title']; ?></label>
    			</p>
            	<?php
                if ( $has_pages ) {
                    wp_dropdown_pages(
                        array(
                            'name'              => $name,
                            'show_option_none'  => esc_html__( $params['option_none'] ),
                            'option_none_value' => $params['option_none_value'],
                            'selected'          => $value,
                            'post_status'       => array( 'draft', 'publish' ),
                        )
                    );
                }
                ?>
            </fieldset>
            <p class="description">
            	<a href="<?php echo admin_url('edit.php?post_type=page'); ?>">
                	<?php esc_html_e('管理页面', 'feng-custom');?>
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * 渲染checkbox 多选分类（多选框）
     * @param string $name
     * @param array $params
     * @param array|string|int $value
     */
    public function render_checkbox_terms($name, $params, $value = null) {
        ?>
        <div class="field-box">
            <fieldset>
            	<label class="label-title" <?php echo $this->build_field_id($name); ?>><?php echo $params['title'];?></label>
    		</fieldset>
    		<fieldset>
    		<?php
    		if (!is_array($value)) {
    		    $value = explode(',', $value);
            }
            $terms = get_terms([
                'taxonomy' => 'link_category',
                'hide_empty' => 0
            ]);
            foreach ($terms as $taxonomy) {
               ?>
               <p>
                   <label for="<?php echo $this->build_field_id($name) . '_' . $taxonomy->term_id; ?>">
                   		<input name="<?php echo $name;?>[]" type="checkbox" id="<?php echo $this->build_field_id($name) . '_' . $taxonomy->term_id; ?>" 
                   			value="<?php echo $taxonomy->term_id; ?>" <?php echo in_array($taxonomy->term_id, $value) ? 'checked' : ''; ?> />
                   		<?php echo $taxonomy->name; ?>
                   </label>
               </p>
            <?php
            }
            ?>
    		</fieldset>
    		<p class="description">
    			<a href="<?php echo admin_url('edit-tags.php?taxonomy=' . $params['taxonomy']); ?>"><?php esc_html_e('管理分类', 'feng-custom');?></a>
        	</p>
    	</div>
		<?php 
    }
    
    /**
     * 渲染checkbox 链接分类（多选框）
     * @param string $name
     * @param array $params
     * @param array|string|int $value
     */
    public function render_checkbox_terms_link($name, $params, $value = null) {
        $params['taxonomy'] = 'link_category';
        $this->render_checkbox_terms($name, $params, $value);
    }
    
    /**
     * 间隔数据
     * @param string $name
     * @param array $params
     * @param array $value
     */
    public function render_spacing($name, $params, $value = []) {
        ?>
        <div class="field-box">
            <fieldset>
            	<label class="label-title"><?php echo $params['title'];?></label>
    		</fieldset>
    		<fieldset class="field-spacing">
    		<?php 
    		if (is_string($value)) {
    		    $value = explode(',', $value);
    		}
    		$control = $params['control'];
    		foreach ($control['tips'] as $k => $v) {
    		?>
    			<span>
    				<input name="<?php echo $name;?>[<?php echo $k;?>]" 
    					id="<?php echo $this->build_field_id($name) . "-" . $k; ?>" 
    					type="text" 
    					value="<?php echo isset($value[$k]) ? $value[$k] : ''; ?>" 
    					placeholder="<?php echo isset($params['placeholder'][$k]) ? $params['placeholder'][$k] : ''; ?>" />
    				<label for="<?php echo $this->build_field_id($name) . "-" . $k; ?>">
    					<?php echo $v; ?>
    				</label>
    			</span>
    		<?php 
    		}
    		?>
    		</fieldset>
    		<p class="description"><?php echo $params['sub']; ?></p>
    	</div>
        <?php 
    }
    
    public function render_lunar_month_day_range($name, $params, $value = []) {
        $lunar = [
            'month' => [
                1 => '正月', 2 => '二月', 3 => '三月', 4 => '四月', 5 => '五月', 6 => '六月', 
                7 => '七月', 8 => '八月', 9 => '九月', 10 => '十月', 11 => '冬月', 12 => '腊月'
            ],
            'day' => [
                1 => '初一', 2 => '初二', 3 => '初三', 4 => '初四', 5 => '初五', 6 => '初六', 7 => '初七',
                8 => '初八', 9 => '初九', 10 => '初十', 11 => '十一', 12 => '十二', 13 => '十三', 14 => '十四',
                15 => '十五', 16 => '十六', 17 => '十七', 18 => '十八', 19 => '十九', 20 => '二十', 21 => '廿一',
                22 => '廿二', 23 => '廿三', 24 => '廿四', 25 => '廿五', 26 => '廿六', 27 => '廿七', 28 => '廿八',
                29 => '廿九', 30 => '三十',
            ],
        ];
        ?>
        <div class="field-box">
            <fieldset>
            	<label class="label-title" for=""><?php echo $params['title'];?></label>
    		</fieldset>
    		<fieldset>
    			<?php 
    			echo '<select name="' . $name . '[start][month] " id="' . $this->build_field_id($name, 'start_month') . '">';
    			foreach ($params['option_range']['month'] as $month) {
    			    if ($value['start']['month'] == $month) {
    			        echo '<option class="level-0" value="' . $month . '" selected="selected">' . $lunar['month'][$month] . '</option>';
    			    }else {
    			        echo '<option class="level-0" value="' . $month . '" >' . $lunar['month'][$month] . '</option>';
    			    }
    			}
    			echo '</select>';
    			echo '<select name="' . $name . '[start][day] " id="' . $this->build_field_id($name, 'start_month') . '">';
    			foreach ($lunar['day'] as $day => $text) {
    			    if ($value['start']['day'] == $day) {
    			        echo '<option class="level-0" value="' . $day . '" selected="selected">' . $text . '</option>';
    			    }else {
    			        echo '<option class="level-0" value="' . $day . '" >' . $text . '</option>';
    			    }
    			}
    			echo '</select> ~ ';
    			echo '<select name="' . $name . '[end][month] " id="' . $this->build_field_id($name, 'start_month') . '">';
    			foreach ($params['option_range']['month'] as $month) {
    			    if ($value['end']['month'] == $month) {
    			        echo '<option class="level-0" value="' . $month . '" selected="selected">' . $lunar['month'][$month] . '</option>';
    			    }else {
    			        echo '<option class="level-0" value="' . $month . '" >' . $lunar['month'][$month] . '</option>';
    			    }
    			}
    			echo '</select>';
    			echo '<select name="' . $name . '[end][day] " id="' . $this->build_field_id($name, 'start_month') . '">';
    			foreach ($lunar['day'] as $day => $text) {
    			    if ($value['end']['day'] == $day) {
    			        echo '<option class="level-0" value="' . $day . '" selected="selected">' . $text . '</option>';
    			    }else {
    			        echo '<option class="level-0" value="' . $day . '" >' . $text . '</option>';
    			    }
    			}
    			echo '</select>';
    			?>
    		</fieldset>
    		<p class="description"><?php echo $params['sub']; ?></p>
    	</div>
        <?php 
    }
    
    
    /**
     * 设置字段前缀
     * @param string $prefix
     */
    public function set_field_prefix($prefix) {
        $this->field_prefix = $prefix;
    }
    
    /**
     * 构建表单字段id字符串
     * @param string $name
     * @param string $after
     * @return string
     */
    private function build_field_id($name, $after = null) {
        if ($after) {
            return $this->field_prefix . $this->option_name . '_' . $name . '_' . $after;
        }else {
            return $this->field_prefix . $this->option_name . '_' . $name;
        }
    }
}