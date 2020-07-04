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

namespace app\admin\admin;

use app\common\model\Apilist as ApiLists;
use app\common\model\ApiFields;
use service\Format;
use service\ApiReturn;

/**
 * api列表及其操作
 * @package app\admin\admin
 */
class Apilist extends Base
{

    /**
     * api列表
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index($module = "")
    {

        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $map = $this->getMap();

        // 配置分组信息
        $list_group = \app\admin\model\Module::column('name,title');

        $tab_list = [];
        foreach ($list_group as $key => $value) {
            $tab_list[$key]['title'] = $value;
            $tab_list[$key]['url'] = url('index', ['module' => $key]);
        }

        $data_list = ApiLists::alias('api')->join('module m', 'api.module = m.name', 'left')->field('api.*,m.title')->where($map)->order('id DESC')->paginate();

        $fields = [
            ['id', 'ID', 'text'],
            ['title', '所属模块', 'link', url('index', ['module' => '__module__']), '', 'text-center'],
            ['apiName', '真实地址', 'text'],
            ['hash', '接口标识', 'link', url('apiinfo', ['hash' => '__hash__', 'layer' => 1]), 'data-toggle="dialog" data-width="1200" data-height="900"'],
            ['method', '请求方式', 'status', '', ['不限', 'POST', 'GET']],
            ['needLogin', '登录验证', 'status', '', ['否', '是'], 'text-center'],
            ['checkSign', 'sign验证', 'status', '', ['否', '是'], 'text-center'],
            ['isTest', '运行环境', 'status', '', ['测试环境', '生产环境'], 'text-center'],
            ['info', '接口说明', 'text'],
            ['create_time', '创建时间', '', '', '', 'text-center'],
            ['status', '状态', 'status', '', '', 'text-center'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];
        $tips = 'API统一访问地址： ' . config('web_site_domain') . '/api/版本号/接口唯一标识'
            . '<p><span class="label label-warning">测试模式</span> 系统将严格过滤请求字段，不进行sign的认证，但在必要的情况下会进行UserToken的认证！</p>'
            . '<p><span class="label label-success">生产模式</span> 系统将严格过滤请求字段，并且进行全部必要认证！</p>'
            . '<p><span class="label label-warning">警告</span> 修改API必须更新缓存才可以生效！</p>'
            . '<p><span class="label label-danger">禁用</span> 系统将拒绝所有请求，一般应用于危机处理！</p>'
            . '<p><a target="_blank" href="' . url('check') . '" class="label label-success">查看签名算法<a></p>';
        return Format::ins()//实例化
        ->setPageTitle('API接口管理')// 设置页面标题
        //->setPageTips($tips)
        ->setTabNav($tab_list, $module)//设置TAB分组
        ->setSearch(['api.apiName' => '接口名称', 'api.hash' => '接口映射', 'api.info' => '接口说明'])// 设置搜索框
        ->addColumns($fields)//设置字段
        ->setTopButtons($this->top_button)
            ->setTopButton(['title' => '状态码说明', 'data-url' => url('errorlist'), 'icon' => 'fa fa-plug pr5', 'class' => 'btn btn-sm mr5 btn-default btn-flat', 'data-toggle' => 'dialog'])
            ->setRightButton(['title' => '请求参数', 'href' => ['request', ['type' => 0, 'hash' => '__hash__']], 'icon' => 'fa fa-random pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat'])
            ->setRightButton(['title' => '返回参数', 'href' => ['request', ['type' => 1, 'hash' => '__hash__']], 'icon' => 'fa fa-plug pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat'])
            ->setRightButtons($this->right_button)
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    function check()
    {
        $keyword = $_POST['keyword'] ?? '';
        $app_secret = $_POST['app_secret'] ?? '';
        $code = '';
        $arr = explode('&', $keyword);
        foreach ($arr as $v) {
            $key = explode('=', $v);
            $newarr[$key[0]] = $key[1];
        }
        $newarr['appsecret'] = $app_secret;
        ksort($newarr);
        $string = [];
        foreach ($newarr as $key => $val) {
            $string[] = $key . '=' . $val;
        }
        if ($keyword) {
            $code = sha1(implode("&", $string));
        }
        $this->assign('keyword', $keyword);
        $this->assign('app_secret', $app_secret);
        $this->assign('code', $code);
        return $this->fetch(); // 渲染模板
    }

    /**
     * 新增api
     * @param int $id api的id
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function add($module = "admin")
    { //新增
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Apilist.add');
            if (true !== $result)
                $this->error($result);

            if ($res = ApiLists::create($data)) {
                // 记录行为
                action_log('admin_api_list_add', 'admin_api_list', $res->id, UID, $data['apiName']);
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        } else {
            $group = \app\admin\model\Apigroup::where('module', $module)->column('aid,name');
            $group[0] = '无分组';
            $fields = [
                ['type' => 'text', 'name' => 'apiName', 'title' => '接口名称', 'tips' => '控制器名/方法名。如：user/index', 'attr' => 'data-rule="required;"'],
                ['type' => 'text', 'name' => 'hash', 'title' => '接口映射', 'tips' => '系统自动生成，不允许修改', 'value' => uniqid(), 'attr' => 'readonly'],
                ['type' => 'text', 'name' => 'info', 'title' => '接口标题', 'attr' => 'data-rule="required;"'],
                ['type' => 'select', 'name' => 'module', 'title' => '所属模块', 'extra' => \app\admin\model\Module::column('name,title')],
                ['type' => 'select', 'name' => 'group', 'title' => '所属接口分组', 'extra' => $group],
                ['type' => 'radio', 'name' => 'method', 'title' => '请求方式', 'extra' => ['不限', 'POST', 'GET'], 'value' => 1],
                ['type' => 'radio', 'name' => 'needLogin', 'title' => '登录验证', 'extra' => ['忽略验证', '需要验证'], 'value' => 0],
                ['type' => 'radio', 'name' => 'checkSign', 'title' => 'sign验证', 'extra' => ['忽略验证', '需要验证'], 'value' => 0],
                ['type' => 'radio', 'name' => 'isTest', 'title' => '运行环境', 'extra' => ['测试环境', '生产环境'], 'value' => 0],
                ['type' => 'textarea', 'name' => 'readme', 'title' => '接口详细说明'],
                ['type' => 'textarea', 'name' => 'returnStr', 'title' => '返回数据示例'],
                ['type' => 'radio', 'name' => 'status', 'title' => '状态', 'extra' => ['禁用', '启用'], 'value' => 1],
            ];
            $this->assign('page_title', '新增API接口');
            $this->assign('form_items', $fields);
            $this->assign('set_script', ['/static/admin/js/apigroup.js']);
            return $this->fetch('public/add');
        }
    }

    /**
     * 编辑api
     * @param int $id api的id
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function edit($id = 0)
    { //编辑
        if ($id === 0)
            $this->error('缺少参数');
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'Apilist.edit');
            if (true !== $result)
                $this->error($result);

            if ($res = ApiLists::update($data)) {
                cache('apiInfo_' . $data['hash'], null);
                // 记录行为
                action_log('admin_api_list_edit', 'admin_api_list', $id, UID, $data['apiName']);
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        } else {
            $data = ApiLists::get(['id' => $id]);
            $group = \app\admin\model\Apigroup::where('module',$data['module'])->column('aid,name');
            $group[0] = '无分组';
            $fields = [
                ['type' => 'hidden', 'name' => 'id'],
                ['type' => 'text', 'name' => 'apiName', 'title' => '接口地址', 'tips' => '控制器名/方法名。如：user/index', 'attr' => 'data-rule="required;"'],
                ['type' => 'text', 'name' => 'info', 'title' => '接口标题', 'attr' => 'data-rule="required;"'],
                ['type' => 'text', 'name' => 'hash', 'title' => '接口映射', 'tips' => '系统自动生成，不允许修改', 'attr' => 'readonly'],
                ['type' => 'select', 'name' => 'module', 'title' => '所属模块', 'extra' => \app\admin\model\Module::column('name,title')],
                ['type' => 'select', 'name' => 'group', 'title' => '所属接口分组', 'extra' => $group],
                ['type' => 'radio', 'name' => 'method', 'title' => '请求方式', 'extra' => ['不限', 'POST', 'GET']],
                ['type' => 'radio', 'name' => 'needLogin', 'title' => '登录验证', 'extra' => ['忽略验证', '需要验证']],
                ['type' => 'radio', 'name' => 'checkSign', 'title' => 'sign验证', 'extra' => ['忽略验证', '需要验证']],
                ['type' => 'radio', 'name' => 'isTest', 'title' => '运行环境', 'extra' => ['测试环境', '生产环境']],
                ['type' => 'textarea', 'name' => 'readme', 'title' => '接口详细说明'],
                ['type' => 'textarea', 'name' => 'returnStr', 'title' => '返回数据示例'],
                ['type' => 'radio', 'name' => 'status', 'title' => '状态', 'extra' => ['禁用', '启用']],
            ];
            $this->assign('page_title', '编辑API接口');
            $this->assign('form_items', $this->setData($fields, $data));
            $this->assign('set_script', ['/static/admin/js/apigroup.js']);
            return $this->fetch('public/edit');
        }
    }

    /**
     * ajax获取分组
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/9 14:10
     */
    public function get_group($module = "admin")
    {
        $group = \app\admin\model\Apigroup::where('module', $module)->column('aid,name');
        $group[0] = '无分组';

        return $group;
    }


    /**
     * 删除接口
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function delete($ids)
    {
        $api = ApiLists::get(['id' => $ids]);
        $res = $this->batch_api_fields($api['hash']);
        if ($res) {
            parent::delete(); // TODO: Change the autogenerated stub
        }
    }

    public function batch_api_fields($hash)
    {
        $where['hash'] = $hash;
        $count = ApiFields::where($where)->count();
        if ($count == 0) {
            return true;
        }
        $result = ApiFields::where($where)->delete();
        if ($result) {
            cache('apiInfo_' . $hash, null);
            cache("apiFields_" . $hash . '_0', null);
            cache("apiFields_" . $hash . '_1', null);
            // 记录行为
            action_log('admin_api_fields_delete', 'admin_api_fields', $hash, UID);
            return true;
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 请求和返回参数列表
     * @param int $type 0代表请求字段参数，1代表返回字段参数
     * @param string $hash 接口映射
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function request($hash, $type)
    { // 请求/返回 参数列表
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $map['hash'] = $hash;
        $map['type'] = $type;
        $data_list = ApiFields::where($map)->order('sort asc')->paginate(15);
        $fields = [
            ['id', 'ID', 'text'],
            ['fieldName', '字段名称', 'text'],
            ['dataType', '数据类型', 'status', '', [1 => 'Integer[整数]', 2 => 'String[字符串]',
                3 => 'Boolean[布尔]',
                4 => 'Enum[枚举]',
                5 => 'Float[浮点数]',
                6 => 'File[文件]',
                7 => 'Mobile[手机号]',
                8 => 'Object[对象]',
                9 => 'Array[数组]',
                10 => 'Email[邮箱]',
                11 => 'Date[日期]',
                12 => 'Url',
                13 => 'IP',
            ]],
            ['isMust', '是否必须', 'status', '', ['否', '是']],
            ['default', '默认值', 'text'],
            ['sort', '排序', 'text.edit','','','','admin_api_fields'],
            ['info', '字段说明', 'text'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];

        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setTopButton(['title' => '新增参数', 'href' => ['editrs', ['type' => $type, 'layer'=>1,'hash' => $hash]], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat open_right'])
            ->setTopButton(['title' => '批量导入', 'href' => ['import', ['type' => $type, 'hash' => $hash]], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setRightButton(['title' => '编辑', 'href' => ['editrs', ['type' => $type, 'layer'=>1,'hash' => '__hash__', 'id' => '__id__']], 'icon' => 'fa fa-edit pr5', 'class' => 'btn btn-xs mr5 btn-success btn-flat open_right'])
            ->setRightButton(['title' => '删除', 'href' => ['deleters', ['type' => $type, 'hash' => $hash, 'id' => '__id__']], 'icon' => 'fa fa-times pr5', 'class' => 'btn btn-xs mr5 btn-danger btn-flat ajax-get confirm'])
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 新增和编辑参数列表
     * @param int $type 0代表请求字段参数，1代表返回字段参数
     * @param string $hash 接口映射
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function editrs($type, $hash)
    { //编辑/新增 参数字段
        $id = input('id');
        if ($this->request->isPost()) {
            $data = input('post.');
            if (!$id) { //新增字段 提交
                $data['type'] = $type;
                $data['hash'] = $hash;
                if (!empty($data['fieldName'])) {
                    $data['showName'] = $data['fieldName'];
                }
                $result = $this->validate($data, 'ApiFields.add');
                if (true !== $result) {
                    $this->error($result);
                } else {
                    if ($res = ApiFields::create($data)) {
                        cache('apiInfo_' . $hash, null);
                        cache("apiFields_" . $hash . '_' . $type, null);
                        // 记录行为
                        action_log('admin_api_fields_add', 'admin_api_fields', $res->id, UID, $data['fieldName']);
                        $this->success('新增成功', cookie('__forward__'));
                    } else {
                        $this->error('新增失败');
                    }
                }
            } else { //编辑字段 提交
                if (!empty($data['fieldName'])) {
                    $data['showName'] = $data['fieldName'];
                }
                if (count($data) == 2) {
                    foreach ($data as $k => $v) {
                        $fv = $k != 'id' ? $k : '';
                    }
                    $result = $this->validate($data, 'ApiFields.' . $fv);
                } else {
                    $result = $this->validate($data, 'ApiFields.edit');
                }
                if (true !== $result) {
                    $this->error($result);
                } else {
                    if ($res = ApiFields::update($data)) {
                        cache('apiInfo_' . $hash, null);
                        cache("apiFields_" . $hash . '_' . $type, null);
                        // 记录行为
                        action_log('admin_api_fields_edit', 'admin_api_fields', $data['id'], UID, 'ID：' . $data['id'] . ' 字段名：' . $data['fieldName']);
                        $this->success('编辑成功', cookie('__forward__'));
                    } else {
                        $this->error('编辑失败');
                    }
                }
            }
        } else {
            if (!$id) { //新增字段
                if ($type == 0) { //新增请求字段
                    $title = '新增请求字段';
                } else { //新增返回字段
                    $title = '新增返回字段';
                }
                $data['hash'] = $hash;
                $data['id'] = 0;
                $data['isMust'] = '';
            } else { //编辑字段
                if ($type == 0) { //编辑请求字段
                    $title = '编辑请求字段';
                } else { //新增返回字段
                    $title = '编辑返回字段';
                }
                $data = ApiFields::get(['id' => $id]);
            }
            $biao_list = db()->query("SHOW TABLE STATUS"); // 获取数据库的所有表信息
            $biao_data = [];
            foreach ($biao_list as $k => $v) {
                $b_name_arr = explode(config('database.prefix'), $v['Name']);
                $biao_data[$k]['name'] = $b_name_arr[1];
                $biao_data[$k]['info'] = $v['Comment'];
            }
            //读取所有字段
            $fields = ApiFields::where(['hash' => $hash, 'type' => 1, 'pid' => 0])->column('id,fieldName');
            $this->assign('fields', $fields);
            $this->assign('type', $type);
            $this->assign('biao_data', $biao_data);
            $this->assign('title', $title);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 批量导入
     * @param $type
     * @param $hash
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/18 21:06
     */
    public function import($type, $hash){
        if ($this->request->isPost()) {
            $data = input('param.');
            $str = str_replace('"','',$data['param']);
            $arr = explode(',',trim($str));
            foreach($arr as $v){
                $val = explode(':',$v);
                $param['fieldName'] = trim($val[0]);
                $param['info'] = trim($val[1]);
                $param['type'] = $type;
                $param['hash'] = $hash;
                $param['isMust'] = 1;
                $param['dataType'] = 2;
                $params[] = $param;
            }
            $ApiFields = new ApiFields();
            $result = $ApiFields->saveAll($params);
            if($result){
                $this->success('导入成功');
            }
            $this->error('导入失败');
        }
        else
        {
            $fields = [
                ['type' => 'hidden', 'name' => 'type', 'value' => $type],
                ['type' => 'hidden', 'name' => 'hash', 'value' => $hash],
                ['type' => 'textarea', 'name' => 'param', 'title' => '快速导入的参数', 'tips' => '格式例如id:ID,name:名称，每组请用逗号分隔,导入的字段格式默认为字符串，必填项，其他类型需要自行修改，原则上不建议使用快速导入'],
            ];
            $this->assign('page_title', '快速导入');
            $this->assign('form_items', $fields);
            return $this->fetch('public/add');
        }
    }

    /**
     * 删除字段参数
     * @param string $hash 接口映射
     * @param int $type 0代表请求字段参数，1代表返回字段参数
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function deleters($hash, $type)
    { //删除 参数字段
        $id = input('id');
        if (isset($id) && !empty($id)) {
            $where['id'] = $id;
            $where['hash'] = $hash;
            $where['type'] = $type;
            $result = ApiFields::where($where)->delete();
            if ($result) {
                cache('apiInfo_' . $hash, null);
                cache("apiFields_" . $hash . '_' . $type, null);
                // 记录行为
                action_log('admin_api_fields_delete', 'admin_api_fields', $id, UID);
                $this->success('删除成功', url('request', ['type' => $type, 'hash' => $hash]));
            } else {
                $this->error('删除失败');
            }
        }
    }

    /**
     * 获取表信息
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function getInfo()
    {
        if (request()->isPost()) {
            $name = input('name');
            $biaoInfo = db()->query("SHOW FULL COLUMNS FROM " . config('database.prefix') . $name); // 获取 [xzyn_user] 表的所有字段信息
            $biao_info = [];
            foreach ($biaoInfo as $k => $v) {
                $biao_info[$k]['name'] = $v['Field'];
                $biao_info[$k]['info'] = $v['Comment'];
                $biao_info[$k]['type'] = $v['Type'];
            }
            $this->success('操作成功', '', $biao_info);
        }
    }

    /**
     * API接口详情
     * @param $hash 接口标识
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function apiinfo($hash)
    {
        $apiinfo = ApiLists::get(['hash' => $hash]);
        if (empty($hash) || empty($apiinfo)) {
            return ApiReturn::r('-1');
        }
        $f_field = ApiFields::all(['hash' => $hash, 'type' => 1]); //返回字段
        $q_field = ApiFields::all(['hash' => $hash, 'type' => 0]); //请求字段
        $this->assign('f_field', $f_field);
        $this->assign('q_field', $q_field);
        $this->assign('data', $apiinfo);
        return $this->fetch();
    }

    /**
     * 错误码列表
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function errorlist()
    {
        $errorlist = ApiReturn::$Code;
        $this->assign('errorlist', $errorlist);
        return $this->fetch();
    }

    /**
     * 用户字段
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function userlist()
    {
        $userFields = ApiReturn::$userFields;
        $this->assign('userFields', $userFields);
        return $this->fetch();
    }

}
