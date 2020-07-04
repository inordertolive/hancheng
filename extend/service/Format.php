<?php
// +----------------------------------------------------------------------
// |ZBPHP[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://www.benbenwangluo.com
// +----------------------------------------------------------------------
// | Author 似水星辰 [ 2630481389@qq.com ]
// +----------------------------------------------------------------------
// | 中犇软件 技术六部 出品
// +----------------------------------------------------------------------

namespace service;

use think\Controller;
use app\admin\model\Menu;
use app\admin\model\Role;
/**
 * 表格类型格式化集合
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Format extends Controller
{
	/**
     * @var int 前缀模式:0-不含表前缀，1-含表前缀，2-使用模型
     */
    private $_prefix = 1;

    /**
     * @var array 存储搜索框数据
     */
    private $_search = [];
    private $_top_search = [];

    /**
     * @var array 顶部下拉菜单默认选项集合
     */
    private $_select_list_default = [];

    /**
     * @var variable array 模板变量
     */
    private $variable = [
        'page_title' => '',         // 页面标题
        'page_tips'       => '',    // 页面提示
        'tips_type'       => '',    // 提示类型
        'search'             => [],       // 搜索参数
        'search_button'      => false,    // 搜索按钮
        'top_search'             => [],       // 搜索参数
        'order_columns'      => [],       // 需要排序的列表
        'hide_checkbox' => false,    // 是否隐藏第一列多选
		'primary_key' => 'id',     // 表格主键名称
        'fields' => [],       // 表格列集合
		'right_button' => [],       // 按钮列集合
		'top_button' => [],       // 按钮列集合
        'replace_right_buttons' => [],  //替换的按钮集合
        'row_list' => [],       // 表格数据列表
        '_filter_time' => [],       // 表格数据列表
        'pages' => '',       // 分页数据
        'template' => APP_PATH . 'admin/view/public/table.html',       // 分页数据
    ];

	/**
     * 初始化
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function initialize()
    {
        $this->_module     = $this->request->module();
        $this->_controller = parse_name($this->request->controller());
        $this->_action     = $this->request->action();
		$this->_table_name = strtolower($this->_module.'_'.$this->_controller);
    }

    /**
     * 实例化一下
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @throws Exception
     */
    public static function ins()
    {
        return new Format();
    }

    /**
     * 设置页面标题
     * @param string $page_title 页面标题
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setPageTitle($page_title = '')
    {
        if ($page_title != '') {
            $this->variable['page_title'] = $page_title;
        }
        return $this;
    }

    /**
     * 设置表单页提示信息
     * @param string $tips 提示信息
     * @param string $type 提示类型：success,info,danger,warning
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setPageTips($tips = '', $type = 'info')
    {
        if ($tips != '') {
            $this->variable['page_tips'] = $tips;
            $this->variable['tips_type'] = trim($type);
        }
        return $this;
    }

    /**
     * 隐藏第一列多选框
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function hideCheckbox()
    {
        $this->variable['hide_checkbox'] = true;
        return $this;
    }

    /**
     * 设置表格主键
     * @param string $key 主键名称
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setPrimaryKey($key = '')
    {
        if ($key != '') {
            $this->variable['primary_key'] = $key;
        }
        return $this;
    }

    /**
     * 添加一列
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $type 单元格类型
     * @param string $default 默认值
     * @param string $param 额外参数
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function addColumn($name = '', $title = '', $type = '', $default = '', $param = '', $class = '', $table = '')
    {
        $column = [
            'name' => $name,
            'title' => $title,
            'type' => $type,
            'default' => $default,
            'param' => $param,
			'class' => $class,
            'table' => $table
        ];

        $args = array_slice(func_get_args(), 5);
        $column = array_merge($column, $args);

        $this->variable['fields'][] = $column;
        return $this;
    }

    /**
     * 一次性添加多列
     * @param array $columns 数据列
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function addColumns($columns = [])
    {
        if (!empty($columns)) {
            foreach ($columns as $column) {
                call_user_func_array([$this, 'addColumn'], $column);
            }
        }
        return $this;
    }

    /**
     * 添加表头排序
     * @param array|string $column 表头排序字段，多个以逗号隔开
     * @author 蔡伟明 <314013107@qq.com>
     * @return $this
     */
    public function setOrder($column = [])
    {
        if (!empty($column)) {
            $column = is_array($column) ? $column : explode(',', $column);
            $this->variable['order_columns'] = array_merge($this->variable['order_columns'], $column);
        }
        return $this;
    }

    /**
     * 设置Tab按钮列表
     * @param array $tab_list Tab列表  ['title' => '标题', 'href' => 'http://www.baidu.com']
     * @param string $active 当前tab
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setTabNav($tab_list = [], $active = '')
    {
        if (!empty($tab_list)) {
            $this->variable['tab_nav'] = [
                'tab_list' => $tab_list,
                'active' => $active,
            ];
        }
        return $this;
    }

    /**
     * 设置搜索参数
     * @param array $fields 参与搜索的字段
     * @param string $placeholder 提示符
     * @param string $url 提交地址
     * @param null $search_button 提交按钮
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setSearch($fields = [], $placeholder = '', $url = '', $search_button = null)
    {
        if (!empty($fields)) {
            $this->_search = [
                'fields'      => is_string($fields) ? explode(',', $fields) : $fields,
                'placeholder' => $placeholder,
                'url'         => $url,
            ];

            $this->_vars['search_button'] = $search_button !== null ? $search_button : '搜索';
        }
        return $this;
    }

    /**
     * @Title  搜索接口
     * @author 高翔 [ 3591837534@qq.com ]
     * @created 2019/9/29 0029 12:00
     */
    public function setTopSearch($fields){
        if (!empty($fields)) {
            $this->_top_search = $fields;
        }
        return $this;
    }

	/*
     * @title 获取button组
     * @param $button 按钮规则
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function setRightButton($button = [])
    {
        if ($button) {
            if(is_array($button['href'])){
				// 判断当前用户是否有权限，没有权限则不生成按钮
				if (session('admin_auth.role') != 1 && substr($button['href'][0], 0, 4) != 'http') {
					if ($this->checkButtonAuth($button) === false) {
						return $this;
					}
				}
				// 自定义参数按钮
				$button['href'] = $this->getUrl($button['href'][0], $button['href'][1]);
			}else{
				// 判断当前用户是否有权限，没有权限则不生成按钮
				if (session('admin_auth.role') != 1 && substr($button['href'], 0, 4) != 'http') {
					if ($this->checkButtonAuth($button) === false) {
						return $this;
					}
				}
				// 自动加上ID
				$button['href'] = $this->getUrl($button['href'], ['id' => '__id__']);
			}
			
            $this->variable['right_button'][] = $button;
        }
        return $this;
    }
	/*
     * @title 获取button组
     * @param $button 按钮规则
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function setRightButtons($buttons = [], $filter = [])
    {
		if (!empty($buttons)) {
            $buttons = is_array($buttons) ? $buttons : explode(',', $buttons);
            foreach ($buttons as $value) {
				if(in_array($value['ident'], $filter)){
					continue;
				}
                $this->setRightButton($value);
            }
        }
        return $this;
    }

	/**
     * 添加一个顶部按钮
     * @param string $button 按钮类型：edit/enable/disable/delete
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setTopButton($button = [])
    {
       if ($button) {
			if(is_array($button['href'])){
				// 判断当前用户是否有权限，没有权限则不生成按钮
				if (session('admin_auth.role') != 1 && substr($button['href'][0], 0, 4) != 'http') {
					if ($this->checkButtonAuth($button) === false) {
						return $this;
					}
				}
				// 自定义参数按钮
				$button['href'] = $this->getUrl($button['href'][0], $button['href'][1]);
			}else{
				if(!empty($button['href'])){
					// 判断当前用户是否有权限，没有权限则不生成按钮
					if (session('admin_auth.role') != 1 && substr($button['href'], 0, 4) != 'http') {
						if ($this->checkButtonAuth($button) === false) {
							return $this;
						}
					}
					// 替换数据变量
					$button['href'] = $this->getUrl($button['href']);
				}else{
					$button['href'] = 'javascript:void(0);';
				}
			}
        }

        $this->variable['top_button'][] = $button;
        return $this;
    }

	/**
     * 添加多个顶部按钮
     * @param string $button 按钮类型：add/enable/disable/delete
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setTopButtons($button = [],$filter = [])
    {
       if ($button) {
            foreach ($button as $value) {
				if(in_array($value['ident'], $filter)){
					continue;
				}
				$this->setTopButton($value);
            }
        }

        $this->variable['top_button'][] = $button;
        return $this;
    }


    /**
     * 时间段过滤
     * @param string $field 字段名
     * @param string $text 字段名
     * @param string|array $date 默认的开始日期和结束日期
     * @param string|array $tips 开始日期和结束日期的提示
     * @author 蔡伟明 <314013107@qq.com>
     * @return $this
     */
    public function addTimeFilter($field = '', $field_title = '',$date = '', $tips = '')
    {
        if ($field != '') {
            $date_start = '';
            $date_end   = '';
            $tips_start = '开始日期';
            $tips_end   = '结束日期';

            if (!empty($date)) {
                if (!is_array($date)) {
                    if (strpos($date, ',')) {
                        list($date_start, $date_end) = explode(',', $date);
                    } else {
                        $date_start = $date_end = $date;
                    }
                } else {
                    list($date_start, $date_end) = $date;
                }
            }

            if (!empty($tips)) {
                if (!is_array($tips)) {
                    if (strpos($tips, ',')) {
                        list($tips_start, $tips_end) = explode(',', $tips);
                    } else {
                        $tips_start = $tips_end = $tips;
                    }
                } else {
                    list($tips_start, $tips_end) = $tips;
                }
            }
            $this->loadFile('js', '/static/plugins/layer/laydate/laydate.js');
//            $this->variable['_js_files'][]  = 'datepicker_js';
//            $this->variable['_css_files'][] = 'datepicker_css';
//            $this->variable['_js_init'][]   = 'datepicker';
            $this->variable['_filter_time'] = [
                'field'      => $field,
                'field_title'=> $field_title,
                'tips_start' => $tips_start,
                'tips_end'   => $tips_end,
                'date_start' => $date_start,
                'date_end'   => $date_end,
            ];
        }
        return $this;
    }

    /**
     * 替换右侧按钮
     * @param array $map 条件，格式为：['字段名' => '字段值', '字段名' => '字段值'....]
     * @param string $content 要替换的内容
     * @param null $target 要替换的目标按钮
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function replaceRightButton($map = [], $content = '', $target = null)
    {
        if (!empty($map)) {
            $maps   = [];
            $target = is_string($target) ? explode(',', $target) : $target;
            if (is_callable($map)) {
                $maps[] = [$map, $content, $target];
            } else {
                foreach ($map as $key => $value) {
                    if (is_array($value)) {
                        $op = strtolower($value[0]);
                        switch ($op) {
                            case '=':  $op = 'eq';  break;
                            case '<>': $op = 'neq'; break;
                            case '>':  $op = 'gt';  break;
                            case '<':  $op = 'lt';  break;
                            case '>=': $op = 'egt'; break;
                            case '<=': $op = 'elt'; break;
                            case 'in':
                            case 'not in':
                            case 'between':
                            case 'not between':
                                $value[1] = is_array($value[1]) ? $value[1] : explode(',', $value[1]); break;
                        }
                        $maps[] = [$key, $op, $value[1]];
                    } else {
                        $maps[] = [$key, 'eq', $value];
                    }
                }
            }

            $this->variable['replace_right_buttons'][] = [
                'maps'    => $maps,
                'content' => $content,
                'target'  => $target
            ];
        }
        return $this;
    }

    /**
     * 检查是否有按钮权限
     * @param array $button 按钮属性
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    private function checkButtonAuth($button = [])
    {
        if (preg_match('/\/(index.php|admin.php)\/(.*)/', $button['href'], $match)) {
            $url_value = explode('/', $match[2]);
            if (strpos($url_value[2], '.')) {
                $url_value[2] = substr($url_value[2], 0, strpos($url_value[2], '.'));
            }
            $url_value = $url_value[0].'/'.$url_value[1].'/'.$url_value[2];
            $url_value = strtolower($url_value);
            return Role::checkAuth($url_value, true);
        }
        return true;
    }

	/**
     * 获取默认url
     * @param string $type 按钮类型：add/enable/disable/delete
     * @param array $params 参数
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return string
     */
    private function getUrl($type = '', $params = [])
    {
        $url = $this->_module.'/'.$this->_controller.'/'.$type;
		//检查是否存在该菜单
        $MenuModel = new Menu();
        $menu  = $MenuModel->where('url_value', $url)->find();
        if ($menu['params'] != '') {
            $url_params = explode('&', trim($menu['params'], '&'));
            if (!empty($url_params)) {
                foreach ($url_params as $item) {
                    list($key, $value) = explode('=', $item);
                    $params[$key] = $value;
                }
            }
        }

        if (!empty($params) && config('url_common_param')) {
            $params = array_filter($params, function($v){return $v !== '';});
        }

        return url($url, $params);
    }

	/**
     * 编译HTML属性
     * @param array $attr 要编译的数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array|string
     */
    private function compileHtmlAttr($attr = []) {
        $result = [];
        if ($attr) {
            foreach ($attr as $key => &$value) {
                if ($key == 'title') {
                    $value = trim(htmlspecialchars(strip_tags(trim($value))));
                }else if ($key == 'extra') {
                    unset($value);
                } else {
                    $value = htmlspecialchars($value);
                }
				if ($key != 'extra') {
                   array_push($result, "$key=\"$value\"");
                }
            }
        }
        return implode(' ', $result);
    }

    /**
     * 编译row_list的值
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    private function compileRows()
    {
        foreach ($this->variable['row_list'] as $key => &$row) {

			// 编译右侧按钮
            if ($this->variable['right_button']) {

                foreach ($this->variable['right_button'] as $index => $button) {
                    // 处理按钮替换
                    if (!empty($this->variable['replace_right_buttons'])) {
                        foreach ($this->variable['replace_right_buttons'] as $replace_right_button) {
                            // 是否能匹配到条件
                            $_button_match = true;
                            foreach ($replace_right_button['maps'] as $condition) {
                                if (is_string($condition[0])) {
                                    if (!isset($row[$condition[0]])) {
                                        $_button_match = false; continue;
                                    }
                                    $_button_match = $this->parseCondition($row, $condition) ? $_button_match : false;
                                } elseif (is_callable($condition[0])) {
                                    $_button_match = call_user_func($condition[0], $row) ? $_button_match : false;
                                }
                            }

                            // 替换按钮内容支持数据变量
                            if ($replace_right_button['content'] != '') {
                                if (preg_match_all('/__(.*?)__/', $replace_right_button['content'], $matches)) {
                                    $replace_to = [];
                                    $pattern    = [];
                                    foreach ($matches[1] as $match) {
                                        $pattern[]    = '/__'. $match .'__/i';
                                        $replace_to[] = $row[$match];
                                    }
                                    $replace_right_button['content'] = preg_replace($pattern, $replace_to, $replace_right_button['content']);
                                }
                            }

                            if ($_button_match) {
                                if ($replace_right_button['target'] === null) {
                                    $row['right_button'] = $replace_right_button['content'];
                                    break(2);
                                } else {
                                    if (in_array($button['ident'], $replace_right_button['target'])) {
                                        $row['right_button'] .= $replace_right_button['content'];
                                        continue(2);
                                    }
                                }
                            }
                        }
                    }

					if($row['status'] == 0){
						if($button['title'] == '禁用'){
							$button['title'] = '启用';
							$button['class'] = 'btn btn-xs mr5 btn-default btn-flat ajax-get confirm';
							$button['href'] = preg_replace('/disable/i','enable',$button['href']);
							$button['icon'] = "fa fa-check-circle pr5";
						}
					}
                    // 处理主键变量值
                    $button['href'] = preg_replace(
                        '/__id__/i',
                        $row[$this->variable['primary_key']],
                        $button['href']
                    );

					

					// 替换其他字段值
                    if (preg_match_all('/__(.*?)__/', $button['href'], $matches)) {
                        // 要替换的字段名
                        $replace_to = [];
                        $pattern    = [];
                        foreach ($matches[1] as $match) {
                            $replace = isset($row[$match]) ? $row[$match] : '';
                            if (isset($row[$match])) {
                                $pattern[]    = '/__'. $match .'__/i';
                                $replace_to[] = $replace;
                            }
                        }
                        $button['href'] = preg_replace(
                            $pattern,
                            $replace_to,
                            $button['href']
                        );
                    }
                    // 编译按钮属性
                    $button['attribute'] = $this->compileHtmlAttr($button);
                    $button_extra = isset($button['extra']) ? $button['extra'] : '' ;
                    $row['right_button'] .= '<a '.$button['attribute'].' ' .$button_extra. '><i class="'.$button['icon'].'"></i>'.$button['title'].'</a> ';
                }
            }

            // 编译单元格数据类型
            if ($this->variable['fields']) {
				// 另外拷贝一份主键值，以免将主键设置为快速编辑的时候解析出错
                $row['_primary_key_value'] = isset($row[$this->variable['primary_key']]) ? $row[$this->variable['primary_key']] : '';
                foreach ($this->variable['fields'] as $column) {
					$_name       = $column['name'];
                    $_table_name = isset($column['table']) ? $column['table'] : '';
					$row['checkid'] = $row[$this->variable['primary_key']];

                    // 如果需要显示编号
                    if ($column['name'] == '__KEY__') {
                        $row[$column['name']] = $key + 1;
                    }

                    switch ($column['type']) {
                        case 'link': // 链接
                            if ($column['default'] != '') {
                                // 要替换的字段名
                                $replace_to = [];
                                $pattern = [];
                                $url = $column['default'];
                                $param = $column['param'];
                                if (preg_match_all('/__(.*?)__/', $column['default'], $matches)) {
                                    foreach ($matches[1] as $match) {
                                        $pattern[] = '/__' . $match . '__/i';
                                        $replace_to[] = $row[$match];
                                    }
                                    $url = preg_replace($pattern, $replace_to, $url);
                                }

                                //$url = $column['class'] == 'pop' ? $url . (strpos($url, '?') ? '&' : '?') . '_pop=1' : $url;

                                $row[$column['name'] . '__' . $column['type']] = '<a href="' . $url . '"
                                    title="' . $row[$column['name']] . '"
                                    class="' . $column['class'] . '"' . $param . '">' . $row[$column['name']] . '</a>';
                            }
                            break;
                        case 'status': // 状态
                            $status = $row[$column['name']];
                            $list_status = !empty($column['param']) ? $column['param'] : ['禁用:warning', '启用:success'];

                            if (isset($list_status[$status])) {
                                switch ($status) {
                                    case '0':
                                        $class = 'warning';
                                        break;
                                    case '1':
                                        $class = 'success';
                                        break;
                                    case '2':
                                        $class = 'primary';
                                        break;
                                    case '3':
                                        $class = 'info';
                                        break;
                                    default:
                                        $class = 'default';
                                }
                                if (strpos($list_status[$status], ':')) {
                                    list($label, $class) = explode(':', $list_status[$status]);
                                } else {
                                    $label = $list_status[$status];
                                }
                                $row[$column['name'] . '__' . $column['type']] = '<span class="label label-flat label-' . $class . '">' . $label . '</span>';
                            }
                            break;
                        case 'password': // 密码框
                            $column['param'] = $column['param'] != '' ? $column['param'] : $column['name'];
                            $row[$column['name'] . '__' . $column['type']] = '<a href="javascript:void(0);" 
                                class="text-edit" 
                                data-placeholder="请输入' . $column['title'] . '" 
                                data-table="' . $this->createTableToken($_table_name == '' ? $this->_table_name : $_table_name, $this->_prefix) . '" 
                                data-type="password" 
                                data-value="" 
                                data-pk="' . $row['_primary_key_value'] . '" 
                                data-name="' . $_name . '">******</a>';
                            break;
                        case 'datetime': // 日期时间
                            // 默认格式
                            $format = 'Y-m-d H:i:s';
                            switch ($column['type']) {
                                case 'date':
                                    $format = 'Y-m-d';
                                    break;
                                case 'datetime':
                                    $format = 'Y-m-d H:i:s';
                                    break;
                                case 'time':
                                    $format = 'H:i:s';
                                    break;
                            }
                            // 格式
                            $format = $column['param'] == '' ? $format : $column['param'];
                            if ($row[$column['name']] == '') {
                                $row[$column['name'] . '__' . $column['type']] = $column['default'];
                            } else {
                                $row[$column['name'] . '__' . $column['type']] = format_time($row[$column['name']], $format);
                            }
                            break;
                        case 'avatar': // 头像
                            break;
                        case 'img_url': // 外链图片
                            if ($row[$column['name']] != '') {
                                $row[$column['name'] . '__' . $column['type']] = '<div class="js-gallery"><img class="image" data-original="' . $row[$column['name']] . '" src="' . $row[$column['name']] . '"></div>';
                            }
                            break;
                        case 'picture': // 单张图片
                            $row[$column['name'] . '__' . $column['type']] = '<div class="js-gallery"><a data-magnify="gallery" id="iview" href="'.get_file_url($row[$column['name']]).'"> <img class="image" style="height:30px;" data-original="' . get_file_url($row[$column['name']]) . '" src="' . get_thumb($row[$column['name']]) . '"> </a></div>';
                            break;
                        case 'pictures': // 多张图片
                            if ($row[$column['name']] === '') {
                                $row[$column['name'] . '__' . $column['type']] = !empty($column['default']) ? $column['default'] : '暂无图片';
                            } else {
                                $list_img = is_array($row[$column['name']]) ? $row[$column['name']] : explode(',', $row[$column['name']]);
                                $imgs = '<div class="js-gallery">';
                                foreach ($list_img as $k => $img) {
                                    if ($column['param'] != '' && $k == $column['param']) {
                                        break;
                                    }
                                    $imgs .= ' <a data-magnify="gallery" id="iview" href="'.get_file_url($img).'"> <img class="image" style="height:30px;" data-original="' . get_file_url($img) . '" src="' . get_thumb($img) . '" ></a>';
                                }
                                $row[$column['name'] . '__' . $column['type']] = $imgs . '</div>';
                            }
                            break;
                        case 'files':
                            if ($row[$column['name']] === '') {
                                $row[$column['name'] . '__' . $column['type']] = !empty($column['default']) ? $column['default'] : '暂无文件';
                            } else {
                                $list_file = is_array($row[$column['name']]) ? $row[$column['name']] : explode(',', $row[$column['name']]);
                                $files = '<div>';
                                foreach ($list_file as $k => $file) {
                                    if ($column['param'] != '' && $k == $column['param']) {
                                        break;
                                    }
                                    $files .= ' [<a href="' . get_file_url($file) . '">' . get_file_name($file) . '</a>]';
                                }
                                $row[$column['name'] . '__' . $column['type']] = $files . '</div>';
                            }
                            break;
                        case 'select': // 下拉框
                            if ($column['default']) {
                                $prepend = isset($column['default'][$row[$column['name']]]) ? $column['default'][$row[$column['name']]] : '无对应值';
                                $class = $prepend == '无对应值' ? 'select-edit text-danger' : 'select-edit';
                                $source = json_encode($column['default'], JSON_FORCE_OBJECT);
                                $row[$column['name'] . '__' . $column['type']] = '<a href="javascript:void(0);" 
                                    class="' . $class . '"
                                    data-table="' . $this->createTableToken($_table_name == '' ? $this->_table_name : $_table_name, $this->_prefix) . '" 
                                    data-type="select" 
                                    data-value="' . $row[$column['name']] . '" 
                                    data-source=\'' . $source . '\' 
                                    data-pk="' . $row['_primary_key_value'] . '" 
                                    data-name="' . $_name . '">' . $prepend . '</a>';
                            }
                            break;
                        case 'callback': // 调用回调方法
                            unset($column['field']);
                            unset($column['table']);
                            $params = array_slice($column, 4);
                            $params = array_filter($params, function ($v) {
                                return $v !== '';
                            });

                            if (isset($row[$column['name']]) || array_key_exists($column['name'], $row)) {
                                $params = array_merge([$row[$column['name']]], $params);
                            }

                            if (!empty($params)) {
                                foreach ($params as &$param) {
                                    if ($param === '__data__') $param = $row;
                                }
                            }

                            $row[$column['name'] . '__' . $column['type']] = call_user_func_array($column['default'], $params);
                            break;
                        case 'text':
						case 'text.edit': // 可编辑的单行文本
							$row[$column['name'].'__'.$column['type']] = '<a href="javascript:void(0);" 
                            class="text-edit" 
                            data-placeholder="请输入'.$column['title'].'" 
                            data-table="'.$this->createTableToken($_table_name == '' ? $this->_table_name : $_table_name, $this->_prefix).'" 
                            data-type="text" 
                            data-pk="'.$row['_primary_key_value'].'" 
                            data-name="'.$_name.'">'.$row[$column['name']].'</a>';
                        break;
                        default: // 默认
                            // 设置默认值
                            if (!isset($row[$column['name']]) && !empty($column['default'])) {
                                $row[$column['name']] = $column['default'];
                            }

                            if (is_array($column['type']) && !empty($column['type'])) {
                                if (isset($column['type'][$row[$column['name']]])) {
                                    $row[$column['name']] = $column['type'][$row[$column['name']]];
                                }
                            } else {
                                if (!empty($column['param'])) {
                                    if (isset($column['param'][$row[$column['name']]])) {
                                        $row[$column['name']] = $column['param'][$row[$column['name']]];
                                    }
                                } else {
                                    if (isset($row[$column['name']]) && $row[$column['name']] == '' && $column['default'] != '') {
                                        $row[$column['name']] = $column['default'];
                                    }
                                }
                            }
                    }
                }
            }
        }
    }

	/**
     * 设置数据库表名
     * @param string $table 数据库表名，不含前缀，如果为true则使用模型方式
     * @param int $prefix 前缀类型：0使用Db类(不添加表前缀)，1使用Db类(添加表前缀)，2使用模型
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setTableName($table = '', $prefix = 1)
    {
        if ($table === true) {
            $this->_prefix     = 2;
            $this->_table_name = strtolower($this->_module.'/'.$this->_controller);
        } else {
            $this->_prefix = $prefix === true ? 2 : $prefix;

            if ($this->_prefix == 2) {
                $this->_table_name = strpos($table, '/') ? $table : strtolower($this->_module.'/'.$table);
            } else {
                $this->_table_name = $table;
            }
        }
        return $this;
    }

	/**
     * 创建表名Token
     * @param string $table 表名
     * @param int $prefix 前缀类型：0使用Db类(不添加表前缀)，1使用Db类(添加表前缀)，2使用模型
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool|string
     */
    private function createTableToken($table = '', $prefix = 1)
    {
        $data = [
            'table'      => $table, // 表名或模型名
            'prefix'     => $prefix,
            'module'     => $this->_module,
            'controller' => $this->_controller,
            'action'     => $this->_action,
        ];

        $table_token = substr(sha1($this->_module.'-'.$this->_controller.'-'.$this->_action.'-'.$table), 0, 8);
        session($table_token, $data);
        return $table_token;
    }

    /**
     * 设置表格数据列表
     * @param array|object $row_list 表格数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setData($row_list = null)
    {
        if ($row_list !== null) {
            // 原始表格数据
            $this->data = $row_list;
            // 转为数组后的表格数据
            $this->variable['row_list'] = $this->toArray($row_list);
            if (is_object($row_list) && !$row_list->isEmpty()) {
                $this->variable['page_info'] = $row_list;
                // 设置分页
                $this->setPages($row_list->render());
            }
        }
        if (empty($this->variable['row_list'])) {
            $params = $this->request->param();
            if (isset($params['page'])) {
                unset($params['page']);
                $url = url($this->_module . '/' . $this->_controller . '/' . $this->_action) . '?' . http_build_query($params);
                $this->redirect($url);
            }
        }
        return $this;
    }

    /**
     * 将表格数据转换为纯数组
     * @param array|object $row_list 数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    private function toArray($row_list)
    {
        if (is_array($row_list)) {
            if (empty($row_list)) return [];
            if (is_object(current($row_list))) {
                $items = [];
                foreach ($row_list as $key => $value) {
                    $items[$key] = $value->toArray();
                }
                return $items;
            }
            return $row_list;
        }

        if ($row_list->isEmpty()) return [];

        if (is_object(current($row_list->getIterator()))) {
            return $row_list->toArray()['data'];
        }
        return $row_list->all();
    }

    /**
     * 分析条件
     * @param mixed $row 行数据
     * @param array $condition 对比条件
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    private function parseCondition($row, $condition = [])
    {
        $match = true;
        switch ($condition[1]) {
            case 'eq':
                $row[$condition[0]] != $condition[2] && $match = false;
                break;
            case 'neq':
                $row[$condition[0]] == $condition[2] && $match = false;
                break;
            case 'gt':
                $row[$condition[0]] <= $condition[2] && $match = false;
                break;
            case 'lt':
                $row[$condition[0]] >= $condition[2] && $match = false;
                break;
            case 'egt':
                $row[$condition[0]] < $condition[2] && $match = false;
                break;
            case 'elt':
                $row[$condition[0]] > $condition[2] && $match = false;
                break;
            case 'in':
                !in_array($row[$condition[0]], $condition[2]) && $match = false;
                break;
            case 'not in':
                in_array($row[$condition[0]], $condition[2]) && $match = false;
                break;
            case 'between':
                ($row[$condition[0]] < $condition[2][0] || $row[$condition[0]] > $condition[2][1]) && $match = false;
                break;
            case 'not between':
                ($row[$condition[0]] >= $condition[2][0] && $row[$condition[0]] <= $condition[2][1]) && $match = false;
                break;
        }
        return $match;
    }

    /**
     * 设置分页
     * @param string $pages 分页数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setPages($pages = '')
    {
        if ($pages != '') {
            $this->variable['pages'] = $pages;
        }
        return $this;
    }

    /**
     * 引入模块js文件
     * @param string $files_url js文件路径，多个文件用逗号隔开
     * @param string $module 指定模块
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function js($files_url = '')
    {
        if ($files_url != '') {
            $this->loadFile('js', $files_url);
        }
        return $this;
    }

    /**
     * 引入模块css文件
     * @param string $files_url css文件路径，多个文件用逗号隔开
     * @param string $module 指定模块
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function css($files_url = '')
    {
        if ($files_url != '') {
            $this->loadFile('css', $files_url);
        }
        return $this;
    }

    /**
     * 引入css或js文件
     * @param string $type 类型：css/js
     * @param string $files_url 文件名，多个用逗号隔开
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    private function loadFile($type = '', $files_url = '')
    {
        if ($files_url != '') {
            if (!is_array($files_url)) {
                $files_name = explode(',', $files_url);
            }
            foreach ($files_name as $item) {
                $this->variable[$type.'_list'][] = $item;
            }
        }
    }

    /**
     * 设置页面模版
     * @param string $template 模版
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    public function setTemplate($template = '')
    {
        if ($template != '') {
            $this->variable['template'] = $template;
        }
        return $this;
    }
	private function compileTable(){

		$this->compileRows();

        // 处理字段排序
        if ($this->variable['order_columns']) {
            $order_columns = [];
            foreach ($this->variable['order_columns'] as $key => $value) {
                if (is_numeric($key)) {
                    if (strpos($value, '.')) {
                        $tmp = explode('.', $value);
                        $order_columns[$tmp[1]] = $value;
                    } else {
                        $order_columns[$value] = $value;
                    }
                } else {
                    if (strpos($value, '.')) {
                        $order_columns[$key] = $value;
                    } else {
                        $order_columns[$key] = $value. '.' .$key;
                    }
                }
            }
            $this->variable['order_columns'] = $order_columns;
        }

		// 编译顶部按钮
        if ($this->variable['top_button']) {
            foreach ($this->variable['top_button'] as &$button) {

                $button['attribute'] = $this->compileHtmlAttr($button);
                $new_button = "<a {$button['attribute']} ".$button['extra'].">";
                if (isset($button['icon']) && $button['icon'] != '') {
                    $new_button .= '<i class="'.$button['icon'].'"></i> ';
                }
                $new_button .= "{$button['title']}</a>";
                $button = $new_button;
            }
        }

        // 处理搜索框
        if ($this->_search) {
            $_temp_fields = [];
            foreach ($this->_search['fields'] as $key => $field) {
                if (is_numeric($key)) {
                    if (strpos($field, '.')) {
                        $_field = explode('.', $field)[1];
                    } else {
                        $_field = $field;
                    }
                    $_temp_fields[$field] = isset($this->_field_name[$_field]) ? $this->_field_name[$_field] : '';
                } else {
                    $_temp_fields[$key]   = $field;
                }
            }
            $this->variable['search'] = [
                'fields'      => $_temp_fields,
                'field_all'   => implode('|', array_keys($_temp_fields)),
                'placeholder' => $this->_search['placeholder'] != '' ? $this->_search['placeholder'] : '请输入'. implode('/', $_temp_fields),
                'url'         => $this->_search['url'] == '' ? $this->request->baseUrl(true) : $this->_search['url']
            ];
        }

        if ($this->_top_search) {
            $this->variable['top_search'] = $this->_top_search;
        }

		// 处理页面标题
        if ($this->variable['page_title'] == '') {
            $location = get_location('', false, false);
            if ($location) {
                $curr_location = end($location);
                $this->variable['page_title'] = $curr_location['title'];
            }
        }
	}
    /**
     * 返回结果
     * @param array $vars 输出变量
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
		// 编译数据
        $this->compileTable();
        if (!empty($vars)) {
            $this->variable = array_merge($this->variable, $vars);
        }
        // 实例化视图并渲染
        return parent::fetch($template ? $template : $this->variable['template'], $this->variable, $replace, $config);
    }
}