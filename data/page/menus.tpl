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
 * 菜单信息
 */
return [
  [
    'title' => '{title}',
    'icon' => '{icon}',
    'url_value' => '{name}/index/index',
    'url_target' => '_self',
    'online_hide' => 0,
    'sort' => 100,
    'status' => 1,
    'child' => [
      [
        'title' => '{title}管理',
        'icon' => 'fa fa-fw fa-folder-open-o',
        'url_value' => '',
        'url_target' => '_self',
        'online_hide' => 0,
        'sort' => 1,
        'status' => 1,
        'child' => [
          [
            'title' => '{title}列表',
            'icon' => 'fa fa-fw fa-list',
            'url_value' => '{name}/index/index',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 1,
            'status' => 1,
            'child' => [
              [
                'title' => '编辑',
                'icon' => '',
                'url_value' => '{name}/index/edit',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 1,
                'status' => 1,
              ],
              [
                'title' => '删除',
                'icon' => '',
                'url_value' => '{name}/index/delete',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 2,
                'status' => 1,
              ],
              [
                'title' => '启用',
                'icon' => '',
                'url_value' => '{name}/index/enable',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 3,
                'status' => 1,
              ],
              [
                'title' => '禁用',
                'icon' => '',
                'url_value' => '{name}/index/disable',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 4,
                'status' => 1,
              ],
              [
                'title' => '新增',
                'icon' => '',
                'url_value' => '{name}/index/add',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 5,
                'status' => 1,
              ],
            ],
          ],
        ],
      ],
    ],
  ],
];
