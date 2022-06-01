# WeChat Utils

该库用于微信相关接口调用

要求：`PHP >= 7.2.5`

安装：`composer require maxsky/wechat-utils`

## App

App 目前仅完成一个方法用于登录获取用户信息

```php
$app = new \MaxSky\WeChat\Services\App\App();

/** @var array */
$userInfo = $app->getUserInfoFromAuth('auth_access_token', 'open_id');
```



## 公众号

### 一般方法

该方法用于微信网页授权登录获取用户信息

```php
$oa = new \MaxSky\WeChat\Services\OA\OfficialAccount();

/** @var array */
$userInfo = $oa->getUserInfoByAuthCode('js_auth_code');
```

### Access Token 方法

```php
// 通过 AppID 和 AppSecret 获取 Access Token，该 Token 不同于 auth_access_token，需缓存，建议缓存 7000s 左右
$accessToken = $oa->getAccessToken('app_id', 'app_secret');

$oa = $oa->setAccessToken($accessToken);

/** @var array */
$userInfo = $oa->getUserInfo('open_id');

// 创建二维码，第二个参数为类型，可用值：QR_SCENE|QR_STR_SCENE|QR_LIMIT_SCENE|QR_LIMIT_STR_SCENE
// 如果第一个参数为 true，则类型仅可用 QR_LIMIT_SCENE 或 QR_LIMIT_STR_SCENE，反之亦然
// 第三个参数为场景值，可传入 int|string
// 第四个参数为过期时间，仅第一个参数为 false 时有效，单位秒，默认 30 天
$result = $oa->createQrcode('is_forever', 'QrCode Type', 'Scene Value', 2592000);

// 通过创建二维码接口返回的 ticket 获取二维码图片 image/jpg
$qrcodeImage = $oa->getQrcode($result['ticket']);

// 组装模板消息数据，关键字段根据模板数量决定，1 个或多个
$data = \MaxSky\WeChat\Utils\OATemplateMessage::constructTemplateMessage([
    'first',
    'keyword1', // 模板消息关键字段1
    'keyword2', // 模板消息关键字段2
    'remark'
], [
    '对应 first 内容', '对应 keyword1 内容', '对应 keyword2 内容', '对应 remark 内容'
]);

/** @var bool 发送模板消息 */
$result = $oa->sendTemplateMessage('open_id', 'template_id', $data, 'url');
```

### JsAPI Ticket 方法

```php
// JsApi Ticket 需缓存，建议缓存 7000s 左右
$jsApiTicket = $oa->getJsApiTicket();

$oa = $oa->setJsApiTicket($jsApiTicket);

// URL 签名包
$signPkg = $oa->getSignPackage('url');
```

### 消息方法



## 小程序

小程序登录，通过授权 `code` 获取 `session` 数组，小程序获取用户信息后传递 `iv` 及加密用户数据到后端，通过 `decryptUserData` 方法解密得到微信用户数据

```php
$mp = new \MaxSky\WeChat\Services\MP\MicroProgram('app_id', 'app_secret');

$session = $mp->code2Session('js_auth_code');

/** @var array */
$userInfo = $mp->decryptUserData($session['session_key'], 'iv_from_request', 'encrypted_data_from_request');
```

