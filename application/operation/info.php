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
    'name' => 'operation',
    'title' => '运营',
    'identifier' => 'operation.zbphp.module',
    'icon' => 'fa fa-fw fa-briefcase',
    'description' => '运营模块',
    'author' => '似水星辰',
    'author_url' => 'javascript:;',
    'version' => '1.0.0',
    'need_module' => [
        [
            'admin',
            'admin.zbphp.module',
            '1.0.0',
        ],
    ],
    'need_plugin' => [],
    'tables' => [
        'operation_ads',
        'operation_ads_type',
        'operation_coupon',
        'operation_coupon_record',
        'operation_nav',
        'operation_nav_type',
        'operation_article',
        'operation_article_body',
        'operation_article_column',
        'operation_message',
        'operation_message_push',
        'operation_system_message',
        'operation_system_message_read',
        'operation_service',
        'operation_service_chat',
        'operation_service_data',
        'operation_service_group',
        'operation_service_log',
        'operation_service_now_data',
        'operation_service_reply',
        'operation_service_words'
    ],
    'database_prefix' => 'mb_',
    'config' => [
        [
            'type' => 'number',
            'name' => 'max_service',
            'title' => '客服最大服务人数',
            'tips' => '客服最大服务人数',
            'value' => 5,
        ],
        [
            'type' => 'radio',
            'name' => 'change_status',
            'title' => '是否启用转接',
            'tips' => '启用转接会自动切换客服',
            'extra' => ['否', '是'],
            'value' => 5,
        ],
        [
            'type' => 'radio',
            'name' => 'is_auto_reply',
            'title' => '是否启用自动回复语',
            'tips' => '客户连接客服时自动发送',
            'extra' => ['否', '是'],
            'value' => 5,
        ],
        [
            'type' => 'text',
            'name' => 'auto_reply',
            'title' => '自动回复语',
            'tips' => '',
            'value' => '您好，欢迎您咨询问题',
        ]
    ],
    'action' => [],
    'access' => [],
];
