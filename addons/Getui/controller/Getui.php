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
namespace addons\Getui\controller;

use app\common\controller\Common;

require_once(dirname(dirname(__FILE__)) . '/sdk/IGt.Push.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/igetui/IGt.AppMessage.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/igetui/IGt.TagMessage.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/igetui/IGt.APNPayload.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/igetui/template/IGt.BaseTemplate.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/IGt.Batch.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/igetui/utils/AppConditions.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/igetui/template/notify/IGt.Notify.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/igetui/IGt.MultiMedia.php');
require_once(dirname(dirname(__FILE__)) . '/sdk/payload/VOIPPayload.php');

/**
 * 个推控制器,服务端推送接口，支持三个接口推送
 * 1.PushMessageToSingle接口：支持对单个用户进行推送
 * 2.PushMessageToList接口：支持对多个用户进行推送，建议为50个用户
 * 3.pushMessageToApp接口：对单个应用下的所有用户进行推送，可根据省份，标签，机型过滤推送
 * @package addons\Getui\controller
 * @author 似水星辰 [2630481389@qq.com]
 */
class Getui extends Common
{
    // AppID
    public $AppID;
    // AppKey
    public $AppKey;
    // AppSecret
    public $AppSecret;
    // masterSecret
    public $MasterSecret;
    // 产品域名
    public $domain;

    public function initialize()
    {
        parent::initialize();
        // 插件配置参数
        $config = addons_config('Getui');
        $this->AppID = $config['AppID'];
        $this->AppKey = $config['AppKey'];
        $this->AppSecret = $config['AppSecret'];
        $this->MasterSecret = $config['MasterSecret'];
        $this->domain = "http://sdk.open.api.igexin.com/apiex.htm";
    }

    /**
     * 单推接口案例
     * @param $data
     * @return array
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/29 11:49
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public function pushMessageToSingle($data)
    {
        $igt = new \IGeTui($this->domain, $this->AppKey, $this->MasterSecret);

        //消息模版：
        // 1.NotificationTemplate：通知透传功能模板
        // 2.LinkTemplate:通知打开链接功能模板
        // 3.TransmissionTemplate:IOS透传功能模板
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板
        switch ($data['template_type']) {
            case 1:
				$template = $this->NotificationTemplate($data);
                break;
            case 2:
                $template = $this->LinkTemplate($data);
                break;
            case 3:
                $template = $this->TransmissionTemplate($data);
                break;
            case 4:
                $template = $this->NotyPopLoadTemplate($data);
                break;
        }

        //个推信息体
        $message = new \IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600 * 12 * 1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送

        //接收方
        $target = new \IGtTarget();
        $target->set_appId($this->AppID);
        $target->set_clientId($data['client_id']);

        $rep = $igt->pushMessageToSingle($message, $target);

        return $rep;
    }


    /**
     * 多推接口案例
     * @param $data
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/6 16:07
     */
    function pushMessageToList($data)
    {
        putenv("gexin_pushList_needDetails=true");
        putenv("gexin_pushList_needAsync=true");

        $igt = new \IGeTui($this->domain, $this->AppKey, $this->MasterSecret);
        //消息模版：
        // 1.TransmissionTemplate:透传功能模板
        // 2.LinkTemplate:通知打开链接功能模板
        // 3.NotificationTemplate：通知透传功能模板
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板

        switch ($data['template_type']) {
            case 1:
                $template = $this->NotificationTemplate($data);
                break;
            case 2:
                $template = $this->LinkTemplate($data);
                break;
            case 3:
                $template = $this->TransmissionTemplate($data);
                break;
            case 4:
                $template = $this->NotyPopLoadTemplate($data);
                break;
        }
        //个推信息体
        $message = new \IGtListMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600 * 12 * 1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);	//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        $contentId = $igt->getContentId($message);	//根据TaskId设置组名，支持下划线，中文，英文，数字

        //接收方
        foreach($data['client_id'] as $k=>$v){
            $target[$k] = new \IGtTarget();
            $target[$k]->set_appId($this->AppID);
            $target[$k]->set_clientId($v);
        }
        $rep = $igt->pushMessageToList($contentId, $target);

        return $rep;
    }

    /**
     * 群推
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/29 8:00
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public function pushMessageToApp($data)
    {
        $igt = new \IGeTui($this->domain, $this->AppKey, $this->MasterSecret);

        switch ($data['template_type']) {
            case 1:
                $template = $this->NotificationTemplate($data);
                break;
            case 2:
                $template = $this->LinkTemplate($data);
                break;
            case 3:
                $template = $this->TransmissionTemplate($data);
                break;
            case 4:
                $template = $this->NotyPopLoadTemplate($data);
                break;
        }

        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(3600 * 1000 * 2);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);

        //$message->setPushTime("201808011537");
        $appIdList = array($this->AppID);
        $phoneTypeList = array('ANDROID');
        //$provinceList=array('浙江');
        //$tagList=array('中文');
        //$age = array("0000", "0010");


        //$cdt = new \AppConditions();
        //$cdt->addCondition(\AppConditions::PHONE_TYPE, $phoneTypeList);
        //$cdt->addCondition(\AppConditions::REGION, $provinceList);
        //$cdt->addCondition(\AppConditions::TAG, $tagList);
        //$cdt->addCondition("age", $age);

        $message->set_appIdList($appIdList);
        //$message->set_conditions($cdt);

        $rep = $igt->pushMessageToApp($message);
        return $rep;
    }

    /**
     * 通知透传消息
     * @return IGtNotificationTemplate
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/29 10:16
     */
    public function NotificationTemplate($data)
    {
        $msg['title'] = $data['title'];
        $msg['content'] = $data['content'];
        $msg['payload'] = 'test';
        $template = new \IGtNotificationTemplate();
        $template->set_appId($this->AppID);                   //应用appid
        $template->set_appkey($this->AppKey);                 //应用appkey
        $template->set_transmissionType(1);            //透传消息类型
        $template->set_transmissionContent("");//透传内容
        $template->set_title($data['title']);      //通知栏标题
        $template->set_text($data['content']);     //通知栏内容
        $template->set_logo("");                       //通知栏logo
        $template->set_logoURL("");                    //通知栏logo链接
        $template->set_isRing(true);                   //是否响铃
        $template->set_isVibrate(true);                //是否震动
        $template->set_isClearable(true);              //通知栏是否可清除

        return $template;
    }

    /**
     * 通知弹框下载模板
     * @return IGtNotyPopLoadTemplate
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/29 14:32
     */
    public function NotyPopLoadTemplate($data){
        $template =  new \IGtNotyPopLoadTemplate();

        $template ->set_appId($this->AppID);//应用appid
        $template ->set_appkey($this->AppKey);//应用appkey
        //通知栏
        $template ->set_notyTitle($data['title']);//通知栏标题
        $template ->set_notyContent($data['content']);//通知栏内容
        $template ->set_notyIcon("");//通知栏logo
        $template ->set_isBelled(true);//是否响铃
        $template ->set_isVibrationed(true);//是否震动
        $template ->set_isCleared(true);//通知栏是否可清除
        //弹框
        $template ->set_popTitle($data['pop_title']);//弹框标题
        $template ->set_popContent($data['pop_content']);//弹框内容
        $template ->set_popImage("");//弹框图片
        $template ->set_popButton1("下载");//左键
        $template ->set_popButton2("取消");//右键
        //下载
        $template ->set_loadIcon(get_file_url(config('web_site_logo')));//弹框图片
        $template ->set_loadTitle("正在下载中……");
        $template ->set_loadUrl($data['download']);
        $template ->set_isAutoInstall(false);
        $template ->set_isActived(true);
        //$template->set_notifyStyle(0);
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息

        return $template;
    }

    /**
     * IOS透传模板
     * 注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能
     * @param $data
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/6 16:07
     */
    public function TransmissionTemplate($data)
    {
        $msg['title'] = $data['title'];
        $msg['content'] = $data['content'];
        $msg['payload'] = 'payload';
        $template = new \IGtTransmissionTemplate();
        $template->set_appId($this->AppID);//应用appid
        $template->set_appkey($this->AppKey);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent(json_encode($msg));//透传内容
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        //APN简单推送
        $apn = new \IGtAPNPayload();
        $alertmsg = new \SimpleAlertMsg();
        $alertmsg->alertMsg = "abcdefg3";
        $apn->alertMsg = $alertmsg;
        $apn->badge = 2;
        $apn->sound = "";
        $apn->add_customMsg("payload", "payload");
        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);

        //VOIP推送
//        $voip = new VOIPPayload();
//        $voip->setVoIPPayload("新浪");
//        $template->set_apnInfo($voip);
//
//
//        //第三方厂商推送透传消息带通知处理
//        $notify = new IGtNotify();
//        $notify -> set_payload("透传测试内容");
//        $notify->set_title("透传通知标题");
//        $notify->set_content("透传通知内容");
//        $notify->set_url("https://www.baidu.com");
//        $notify->set_type(NotifyInfo_Type::_url);
//        $template->set3rdNotifyInfo($notify);

        //APN高级推送
        $apn = new \IGtAPNPayload();
        $alertmsg = new \DictionaryAlertMsg();
        $alertmsg->body = "body";
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
//        IOS8.2 支持
        $alertmsg->title = "Title";
        $alertmsg->titleLocKey = "TitleLocKey";
        $alertmsg->titleLocArgs = array("TitleLocArg");

        $apn->alertMsg = $alertmsg;
        $apn->badge = 7;
        $apn->sound = "";
        $apn->add_customMsg("payload", "payload");
        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";

        //IOS多媒体消息处理
        $media = new \IGtMultiMedia();
        $media->set_url("http://docs.getui.com/start/img/pushapp_android.png");
        $media->set_onlywifi(false);
        $media->set_type(\MediaType::pic);
        $medias = array();
        $medias[] = $media;
        $apn->set_multiMedias($medias);
        $template->set_apnInfo($apn);
        return $template;
    }

    /**
     * 打开网页消息
     * @return \IGtLinkTemplate
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/29 10:15
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public function LinkTemplate($data)
    {
        $template = new \IGtLinkTemplate();
        $template->set_appId($this->AppID);//应用appid
        $template->set_appkey($this->AppKey);//应用appkey
        $template->set_title($data['title']);//通知栏标题
        $template->set_text($data['content']);//通知栏内容
        $template->set_logo("");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        $template->set_url($data['link']);//打开连接地址
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }
}