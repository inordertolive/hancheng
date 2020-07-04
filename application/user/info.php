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
    'name' => 'user',
    'title' => '会员',
    'identifier' => 'user.zbphp.module',
    'icon' => 'fa fa-fw fa-user',
    'description' => '会员模块',
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
        'user',
        'user_address',
        'user_certified',
        'user_follow',
        'user_info',
        'user_label',
        'user_level',
        'user_level_votes',
        'user_money_log',
        'user_recharge_rule',
        'user_score_log',
        'user_signin',
        'user_signin_log',
        'user_suggestions',
        'user_vip',
        'user_virtual_log',
        'user_withdraw',
        'user_withdraw_account',
    ],
    'database_prefix' => 'mb_',
    'config' => [
        [
            'type' => 'radio',
            'name' => 'auto_withdraw',
            'title' => '提现转账方式',
            'extra' => ['手动打款', '系统转账'],
            'value' => 0,
            'tips' => '手动打款需要前台上传微信支付宝收款二维码，系统打款则需要绑定微信支付宝账号，并开通配置微信支付宝等开放平台的转账功能'
        ],
        [
            'type' => 'text',
            'name' => 'virtual_money',
            'title' => '虚拟币和现金兑换比例',
            'value' => 1,
            'tips' => '例如1，代表1:1兑换，例如10，代表10虚拟币兑换1元现金，例如100，代表100虚拟币兑换1元现金,以此类推'
        ],
		[
            'type' => 'number',
            'name' => 'min_withdraw_money',
            'title' => '最小提现金额',
            'value' => 100,
            'tips' => '大于此金额才能提现'
        ],
		 [
            'type' => 'radio',
            'name' => 'withdraw_handling_type',
            'title' => '手续费收取方式',
            'extra' => ['固定金额', '百分比'],
            'value' => 0,
            'tips' => ''
        ],
		[
            'type' => 'number',
            'name' => 'withdraw_handling_fee',
            'title' => '手续费',
            'value' => 2,
            'tips' => '请输入整数，根据收取方式决定，例如:输入2，代表2元或者2%'
        ]
    ],
    'action' => [],
    'access' => [],
];
