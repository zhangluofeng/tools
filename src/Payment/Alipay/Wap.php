<?php

namespace CommonTools\Payment\Alipay;

require_once '../../Lib/alipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
require_once '../../Lib/alipay/wappay/service/AlipayTradeService.php';


class Wap
{
    public function __construct($appid, $merchant_private_key, $alipay_public_key, $notify_url = '', $return_url = '')
    {
        $this->app_id = $appid;
        $this->merchant_private_key = $merchant_private_key;
        $this->alipay_public_key= $alipay_public_key;
        $this->notify_url = $notify_url;//不需要urlencode
        $this->return_url = $return_url;//不需要urlencode

    }

    public function pay($params)
    {
        $out_trade_no = $params['out_trade_no'];
        $subject = empty($params['subject']) ? '订单-' . $params['out_trade_no'] : $params['subject'];
        $total_amount = $params['total_amount'];


        $config = $this->getConfig();

        $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody('');
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress( "1m");

        $payResponse = new \AlipayTradeService($config);
        $result = $payResponse->wapPay($payRequestBuilder, $config['return_url'], $config['notify_url']);
        var_dump($result);

    }

    public function notify($input = [])
    {
        if (empty($input)) {
            $input = $_POST;
        }

        $alipaySevice = new \AlipayTradeService($this->getConfig());
        return $alipaySevice->check($input);
    }

    private function getConfig(){
        $config =  [
            //应用ID,您的APPID。
            'app_id' => $this->app_id,
            //商户私钥，您的原始格式RSA私钥
            'merchant_private_key' => $this->merchant_private_key,
            //异步通知地址
            'notify_url' => $this->notify_url,
            //同步跳转
            'return_url' => $this->return_url,
            //编码格式
            'charset' => "UTF-8",
            //签名方式
            'sign_type' => "RSA2",
            //支付宝网关
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
            //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
            'alipay_public_key' => $this->alipay_public_key,
            //日志路径
            'log_path' => "",
        ];
        return $config;
    }

}