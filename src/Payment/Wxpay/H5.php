<?php

namespace CommonTools\Payment\Wxpay;


class H5
{
    const UNIFIEDORDER_URL_V3 = 'https://api.mch.weixin.qq.com/v3/pay/transactions/h5';

    private $private_key_path;

    //公众账号ID
    private $appid;
    //商户号
    private $mch_id;
    //证书序列号
    private $serial_no;
    //支付结果回调通知地址
    private $notify_url;
    //支付密钥
    private $key;

    public function __construct($appid, $mch_id, $key, $notify_url = '', $serial_no = '', $private_key_path = '')
    {
        $this->appid = $appid;
        $this->mch_id = $mch_id;
        $this->notify_url = $notify_url;//不需要urlencode
        $this->key = $key;
        $this->serial_no = $serial_no;
        $this->private_key_path = $private_key_path;
    }

    //统一下单接口
    public function unifiedOrderV3($params)
    {
        //请求参数(报文主体)
        $body = [
            'appid' => $this->appid,
            'mchid' => $this->mch_id,
            'description' => empty($params['description']) ? '订单-' . $params['out_trade_no'] : $params['description'],
            'out_trade_no' => $params['out_trade_no'],
            'notify_url' => $this->notify_url,
            'amount' => [
                'total' => $params['total'],
                'currency' => 'CNY'
            ],
            'scene_info' => [
                'payer_client_ip' => $this->get_client_ip(),
                'h5_info' => [
                    'type' => 'Wap',
                ],
            ],
        ];
        $headers = $this->sign('POST', self::UNIFIEDORDER_URL_V3, json_encode($body));
        $res = $this->curl_post(self::UNIFIEDORDER_URL_V3, json_encode($body), $headers);
        return $res;
    }

    public function notifyV3($input=[])
    {
        if (empty($input)) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
        } else {
            $data = json_decode($input, true);
        }
        $ciphertext = $data['resource']['ciphertext'];
        $associatedData = $data['resource']['associated_data'];
        $nonceStr = $data['resource']['nonce'];

        $ciphertext = \base64_decode($ciphertext);
        $ctext = substr($ciphertext, 0, -16);
        $authTag = substr($ciphertext, -16);
        $r = \openssl_decrypt($ctext, 'aes-256-gcm', $this->key, \OPENSSL_RAW_DATA, $nonceStr,
            $authTag, $associatedData);

        $r = json_decode($r, true);
        return $r;
    }

    private function sign($http_method = 'POST', $url = '', $body = '')
    {
        $mch_private_key = $this->getMchKey();//私钥
        $timestamp = time();
        $nonce = $this->genRandomString();//随机串
        $url_parts = parse_url($url);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        //构造签名串
        $message = $http_method . "\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";//报文主体
        //计算签名值
        openssl_sign($message, $raw_sign, $mch_private_key, 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        //设置HTTP头
        $token = sprintf('WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->mch_id, $nonce, $timestamp, $this->serial_no, $sign);
        $headers = [
            'Accept: application/json',
            'User-Agent: */*',
            'Content-Type: application/json; charset=utf-8',
            'Authorization: ' . $token,
        ];
        return $headers;
    }

    private function getMchKey()
    {
        $localpath = $this->private_key_path;
        return openssl_get_privatekey(file_get_contents($localpath));
    }

    private function curl_post($url, $data, $headers = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private function get_client_ip()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
    }

    private function genRandomString($len = 32)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9",
        );
        $charsLen = count($chars) - 1;
        // 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }

        return $output;
    }
}

