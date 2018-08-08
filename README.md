### 环境要求
 - PHP >= 5.4
 - [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) >=5.3
 
 ### 安装
 
 ```shell
composer require wulove52/hello-word:dev-master
```

### 使用

#### 设置配置

```php
use Wulove52\HelloWord\Alidayu;
Alidayu::setConfig([
   'appKey' => '23356838',
   'appSecret' => '254fee5fbabe2e01be04581d855c9af3',
   // 使用HTTPs
   'secure' => false,

]);
```

#### 调用API

```php
use Wulove52\HelloWord\Alidayu;
// 发送短信通知
$sms = Alidayu::sms('17191195520', 'SMS_100875117', '潘晓亮', ['number' => '1234']);
// 发送请求并返回响应
$response = $sms->send();

if ($response->success()) {
    // 接口返回成功
    print_r($response->getData());
} else {
    // 接口返回错误
    echo $response->getError();
}


```
