<?php

namespace Wulove52\HelloWord;

use GuzzleHttp\Client as GuzzleHttp;
use function GuzzleHttp\Psr7\build_query;
use Wulove52\HelloWord\Request\AbstractRequest;
use Wulove52\HelloWord\Request\DoubleCall;
use Wulove52\HelloWord\Request\FlowCharge;
use Wulove52\HelloWord\Request\FlowChargeProvince;
use Wulove52\HelloWord\Request\FlowGrade;
use Wulove52\HelloWord\Request\FlowQuery;
use Wulove52\HelloWord\Request\SingleCall;
use Wulove52\HelloWord\Request\Sms;
use Wulove52\HelloWord\Request\SmsQuery;
use Wulove52\HelloWord\Request\TtsSingleCall;

class Client
{

    /**
     * 正式环境http请求地址
     */
    const HTTP_URL = 'http://dysmsapi.aliyuncs.com';

    /**
     * 正式环境的https请求地址
     */
    const HTTPS_URL = 'https://dysmsapi.aliyuncs.com';

    /**
     * 是否使用https接口
     * @var bool
     */
    protected $secure = false;

    /**
     * 阿里大于App Key
     * @var string
     */
    protected $appKey = null;

    /**
     * 阿里大于App Secret
     * @var string
     */
    protected $appSecret = null;

    /**
     * Alidayu constructor.
     * @param array $config 阿里大于配置
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->setConfig($config);
        }

    }

    /**
     * 设置配置
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config = [])
    {
        // 设置App Key
        $this->appKey = isset($config['appKey']) ? $config['appKey'] : '';

        // 设置App Secret
        $this->appSecret = isset($config['appSecret']) ? $config['appSecret'] : '';

        // 使用HTTPS
        $this->secure = isset($config['secure']) ? (bool)$config['secure'] : false;

        return $this;
    }

    /**
     * 发送请求
     * @param AbstractRequest $request
     * @return Response
     */
    public function send(AbstractRequest $request)
    {
        $params = array_merge([
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0,0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $this->appKey,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ],$request->getParams());

        $params = array_merge($params,array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ));

        // 将参数Key按字典顺序排序
        ksort($params);

        $sortedQueryStringTmp  = '';
        foreach ($params as $key => $value) {

            $sortedQueryStringTmp  .= "&" . static::encode($key) . "=" . static::encode($value);
        }

        $signature = $this->buildSignature($sortedQueryStringTmp, $this->appSecret);

        $client = new GuzzleHttp();

        // 发起请求

        $response = $client->get($this->getUrl() . '?Signature='.$signature.$sortedQueryStringTmp,
            [
                'headers' => [
                    "x-sdk-client" => "php/2.0.0"
                ]
            ]);

        return new Response($response, $request);
    }

    /**
     * 返回发送短信通知
     * @param string $recNum 短信接收号码
     * @param string $smsTemplateCode 模板ID
     * @param string $smsFreeSignName 短信签名
     * @param array $smsParam 短信模板变量参数
     * @param string $extend 回传参数
     * @return Sms
     */
    public function sms($recNum, $smsTemplateCode, $smsFreeSignName, array $smsParam = [], $extend = '')
    {
        return new Sms($recNum, $smsTemplateCode, $smsFreeSignName, $smsParam, $extend);
    }



    /**
     * 返回接口请求地址
     * @return string
     */
    protected function getUrl()
    {

        if (!($this->secure)) {
            return static::HTTP_URL;
        } else {
            return static::HTTPS_URL;
        }
    }

    /**
     * 生成签名
     * @param array $parameters
     * @param $appSecret
     * @return string
     */
    static private function buildSignature($sortedQueryStringTmp, $appSecret)
    {
        // 将参数Key按字典顺序排序
        $stringToSign = "GET&%2F&" . static::encode(substr($sortedQueryStringTmp, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $appSecret . "&",true));

        return static::encode($sign);

    }

    static public function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }
}
