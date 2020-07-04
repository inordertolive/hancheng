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

/**
 * 模块信息
 */
return [
  'name' => '{name}',
  'title' => '{title}',
  'identifier' => '{identifier}',
  'icon' => '{icon}',
  'description' => '{description}',
  'author' => '{author}',
  'author_url' => 'javascript:;',
  'version' => '{version}',
  'need_module' => [
    [
      'admin',
      'admin.zbphp.module',
      '1.0.0',
    ],
  ],
  'need_plugin' => [],
  'tables' => [],
  'database_prefix' => 'lb_',
  'config' => [
    [
      'type' => 'number',
      'name' => 'test',
      'title' => '测试配置',
      'tips' => '测试提示',
      'value' => '',
      'min' => 0,
      '' => '',
    ],
  ],
  'action' => [],
  'access' => [],
];
