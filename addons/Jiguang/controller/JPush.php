<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17 0017
 * Time: 15:06
 */

namespace addons\Jiguang\controller;

require_once(dirname(dirname(__FILE__))."/sdk/JPush/autoload.php");
use JPush\Client as JP;
use app\common\controller\Common;
class JPush extends Common
{
    /**
     * 取得Client
     * @param string $type  实例化的类型
     * @return DefaultAcsClient
     */
    public static function getClient($type = 'push') {
        // appKey
        $appKey = addons_config('Jiguang.appKey');
        $masterSecret = addons_config('Jiguang.masterSecret');
        $Client= new JP($appKey, $masterSecret);
        switch ($type){
            case 'push':
                return  $Client->push();
                break;
            case 'report ':
                return  $Client->report();
                break;
            case 'device':
                return  $Client->device();
                break;
            case 'schedule':
                return  $Client->schedule();
                break;
            default:
                return '实例化类型错误';
        }
    }
}