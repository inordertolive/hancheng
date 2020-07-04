<?php

/**
 * 
 * 回调基础类
 * @author huliangming<215628355@qq.com>
 *
 */
class WxPayNotify extends WxPayNotifyReply {

    /**
     * 
     * 回调入口,会返回$data数组
     */
    final public function Handle() {
        $msg = "OK";
        return WxpayApi::notify($msg);
    }

    /**
     * 
     * 打印内容给微信 ,result 为true 表示支付成功 ,msg为提示内容 
     * @param bool $needSign  是否需要签名输出,可重写，建议重写
     */
    public function NotifyProcess($result = false, $msg = '', $needSign = false) {

        if ($result == true) {
            $this->SetReturn_code("SUCCESS");
            $this->SetReturn_msg("OK");
            $this->ReplyNotify($needSign);
        } else {
            $this->SetReturn_code("FAIL");
            $this->SetReturn_msg($msg);
            $this->ReplyNotify(false);
        }
        die;
    }

    /**
     * 
     * 回复通知
     * @param bool $needSign 是否需要签名输出
     */
    final private function ReplyNotify($needSign = true) {
        //如果需要签名
        if ($needSign == true &&
                $this->GetReturn_code($return_code) == "SUCCESS") {
            $this->SetSign();
        }
        WxpayApi::replyNotify($this->ToXml());
    }

}
