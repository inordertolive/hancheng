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

namespace addons\UserCount;

use app\common\controller\Addons;

/**
 * 会员统计
 * @package addons\UserCount
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class UserCount extends Addons
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name'        => 'UserCount',
        // 插件标题[必填]
        'title'       => '会员统计信息',
        // 插件唯一标识[必填],格式：插件名.开发者标识.addons
        'identifier'  => 'UserCount.zbphp.addons',
        // 插件图标[选填]
        'icon'        => 'fa fa-user',
        // 插件描述[选填]
        'description' => '会员统计信息',
        // 插件作者[必填]
        'author'      => '似水星辰',
        // 作者主页[选填]
        'author_url'  => 'javascript:;',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.0.0',
        // 是否有后台管理功能[选填]
        'admin'       => '0',
    ];

    /**
     * @var array 插件钩子
     */
    public $hooks = [
        'admin_index'
    ];

    /**
     * 后台首页钩子
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function adminIndex()
    {
        $config = $this->getConfigValue();
        if ($config['display']) {
            for($i=7;$i>=0;$i--) {
                $daystr = date('Y-m-d', strtotime("-$i day"));
                $searchstr = "TO_DAYS(from_unixtime(create_time,'%Y-%m-%d'))=TO_DAYS('" . $daystr . "')";
                $apCount = \think\Db::name('user')->where($searchstr)->count();
                $MoneyCount = \think\Db::name('order')->where(['order_type'=>1,'pay_status'=>1])->where($searchstr)->sum('real_money');
                $dateArray[] = date('m-d', strtotime("-$i day"));
                $user1[] = $apCount;
                $recharge[] = $MoneyCount;
            }
            $usernum = \think\Db::name('user')->count();
            $ordernum = \think\Db::name('order')->where(['order_type'=>1,'pay_status'=>1])->whereTime('create_time','today')->count();
			$kedan = sprintf("%.2f",$recharge[7]/$ordernum);
            $this->fetch('widget', [
                'config'=>$config,
                'date'=>$dateArray,
                'user1'=>implode(',',$user1),
                'recharge'=>implode(',',$recharge),
                'usernum' => $usernum,
                'ordernum' => $ordernum,
                'rechargenum' => $recharge[7],
                'kedan' => $kedan>0 ? $kedan : 0
            ]);
        }
    }

    /**
     * 安装方法
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public function install(){
        return true;
    }

    /**
     * 卸载方法
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public function uninstall(){
        return true;
    }
}