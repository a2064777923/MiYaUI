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
 * 验证类 
 * 
 * @package    Feng_Custom
 * @subpackage Feng_Custom/includes
 * @author     阿锋 <mypen@163.com>
 */
Class Feng_Custom_Validate {
    
    /**
     * 错误信息
     * @var string|array
     */
    protected $error;
    
    /**
     *
     * @var Feng_Custom_Validate
     */
    static private $instance;
    
    /**
     * 返回实例（单例）
     * @return Feng_Custom_Validate
     */
    static public function instance() {
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }
    
    /**
     * 验证表单随机数（POST数据）
     * 
     * @param string $name 表单元素名
     * @param string $nonce Nonce
     * @return boolean
     */
    public function verify_nonce($name, $nonce) {
        if (!isset($_POST[$name])) {
            $this->error = esc_html__('[wp_verify_nonce]缺少验证参数！', 'feng-custom');
            return false;
        }
        // 验证随机数
        $result = wp_verify_nonce($_POST[$name], $nonce);
        if ($result !== 1) {
            $this->error = esc_html__('请求验证失败，刷新页面后重新操作！', 'feng-custom');
            return false;
        }
        return true;
    }
    
    /**
     * 验证数据
     * @param string|array $value
     * @param string|array $rule
     * @param array $data
     * @return bool
     */
    public function validate($value, $rule, $data = []) {
        $condition = null;
        if (strpos($rule, '||') !== false) {
            $rule = explode('||', $rule);
            $condition = 'or';
        }else if (strpos($rule, '&&')) {
            $rule = explode('&&', $rule);
            $condition = 'and';
        }
        
        if (is_array($rule)) {
            // $rule 为数组时，需要进行遍历进行验证
            $rule_count = count($rule);
            foreach ($rule as $k => $v) {
                if ($this->validate($value, $v, $data) === false) {
                    if ($condition === 'and') {
                        // 验证失败，且关系直接返回false
                        return false;
                    }
                    if ($condition === 'or' && $k <= ($rule_count - 1)) {
                        // 验证失败 或关系 最后一个验证结果为false
                        return false;
                    }
                }else {
                    // 验证成功
                    if ($condition === 'or') {
                        // 或关系直接返回true 验证成功
                        return true;
                    }
                }
            }
        }else {
            if ($this->is_require($value)) {
                // $value 不为空的时候进行验证
                if ($this->is($value, $rule, $data) === false) {
                    return false;
                }
            }
            
            if ($rule === 'require') {
                // 要求必须是验证
                if ($this->is($value, $rule, $data) === false) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * 验证字段值是否为有效格式
     * @access public
     * @param mixed  $value 字段值｜字段
     * @param string $rule  验证规则
     * @param array  $data  数据
     * @return bool
     */
    public function is($value, string $rule, array $data = []): bool
    {
        if (!empty($data)) {
            $value = $this->get_data_value($data, $value);
        }
        switch ($rule) {
            case 'require':
                // 必须不能为空
                $this->error = '不能为空';
                $result = !empty($value) || '0' == $value;
                break;
            case 'accepted':
                // 接受
                $this->error = '请选择接受';
                $result = in_array($value, ['1', 'on', 'yes']);
                break;
            case 'date':
                // 是否是一个有效日期
                $this->error = '非有效日期';
                $result = false !== strtotime($value);
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $this->error = '网址无效';
                $result = checkdnsrr($value);
                break;
            case 'boolean':
            case 'bool':
                // 是否为布尔值
                $this->error = '不是布尔类型';
                $result = in_array($value, [true, false, 0, 1, '0', '1'], true);
                break;
            case 'number':
                // 验证某个字段的值是否为纯数字（采用ctype_digit验证，不包含负数和小数点）
                $this->error = '非整数数字';
                $result = ctype_digit((string) $value);
                break;
            case 'alphaNum':
                // 检查是字母数字
                $this->error = '只能包含字母和数字';
                $result = ctype_alnum($value);
                break;
            case 'array':
                // 是否为数组
                $this->error = '非数组类型';
                $result = is_array($value);
                break;
            default:
                if (strpos($rule, ':') !== false) {
                    $rule_params = explode(':', $rule);
                    $rule_method = str_replace('-', '_', 'is_' . $rule_params[0]);
                }else {
                    $rule_method = str_replace('-', '_', 'is_' . $rule);
                }
                if (method_exists($this->instance(), $rule_method) === false) {
                    // 验证方法不存在
                    $this->error = '未定义 ' . $rule . ' 验证类型的方法';
                    $result = false;
                }else {
                    $result = call_user_func(array($this->instance(), $rule_method), $value, $rule, $data);
                }
                break;
        }
        return $result;
    }
    
    /**
     * 判断是否为时间格式
     * @param string $value 
     * @param boolean $rule 是否返回时间戳
     * @return boolean
     */
    public function is_date($value, $rule = false, $data = []) {
        $this->error = '';
        return strtotime($value) ? true : false;
    }
    
    /**
     * 必须
     * @param string $value 字段名称或值
     * @param string $rule
     * @param array $data 数据
     */
    public function is_require($value, $rule = '', $data = []) {
        $this->error = '不能为空';
        if( empty($value) || $value === 0 || $value === '0') {
            return false;
        }
    }
    
    /**
     * 验证是否大于等于某个值 >=
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则｜字段
     * @param array $data  数据
     * @return bool
     */
    public function is_egt($value, $rule, array $data = []): bool
    {
        return $value >= $this->getDataValue($data, $rule);
    }
    
    /**
     * 验证是否大于某个值 >
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则｜字段
     * @param array $data  数据
     * @return bool
     */
    public function is_gt($value, $rule, array $data = []): bool
    {
        return $value > $this->getDataValue($data, $rule);
    }
    
    /**
     * 验证是否小于等于某个值 <=
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则｜字段
     * @param array $data  数据
     * @return bool
     */
    public function is_elt($value, $rule, array $data = []): bool
    {
        return $value <= $this->getDataValue($data, $rule);
    }
    
    /**
     * 验证是否小于某个值 <
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则｜字段
     * @param array $data  数据
     * @return bool
     */
    public function is_lt($value, $rule, array $data = []): bool
    {
        return $value < $this->getDataValue($data, $rule);
    }
    
    /**
     * 验证是否等于某个值 =
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则｜字段
     * @return bool
     */
    public function is_eq($value, $rule, $data = []): bool
    {
        $this->error = '不相同';
        return $value == $rule;
    }
    
    /**
     * 检查是否为页面
     * @param int|string|int[]|string[] $value
     * @param string $rule
     * @param array $data
     */
    public function is_wp_page($value, $rule = null, $data = []) {
        // 有则验证
        $this->error = '不是有效页面或参数有误';
        return is_page($value);
    }
    
    /**
     * 检查是否是分类
     * @param int|string $value 分类ID、简码或名称。
     * @param int|string $rule int 表示父ID，string 表示分类类型
     * @param array $data
     */
    public function is_wp_term($value, $rule = null, $data = []) {
        $this->error = '不是有效的类别或参数有误';
        if (is_int($rule)) {
            $term = term_exists($value);
            return $term === 0 || empty($term) ? false : true;
        }
        $taxonomy = '';
        $parent_term = null;
        if (strpos($rule, ':') !== false) {
            // 包含冒号，需要分割分类
            $rule_params = explode(':', $rule);
            foreach ($rule_params as $param) {
                is_int($param) ? $parent_term = $param : $taxonomy = $param;
            }
        }else {
            ( $rule && is_int($rule) )  ? $parent_term = $rule : $taxonomy = $rule;
        }
        $term = term_exists($value, $taxonomy, $parent_term);
        return $term === 0 || empty($term) ? false : true;
    }
    
    /**
     * 获取数据值
     * @access protected
     * @param array  $data 数据
     * @param string $key  数据标识 支持二维
     * @return mixed
     */
    private function get_data_value(array $data, $key)
    {
        if (is_numeric($key)) {
            $value = $key;
        } elseif (is_string($key) && strpos($key, '.')) {
            // 支持多维数组验证
            foreach (explode('.', $key) as $key) {
                if (!isset($data[$key])) {
                    $value = null;
                    break;
                }
                $value = $data = $data[$key];
            }
        } else {
            $value = $data[$key] ?? null;
        }
        
        return $value;
    }
    
    /**
     * 获取错误信息
     * @return string|[]
     */
    public function get_error() {
        return $this->error;
    }
}