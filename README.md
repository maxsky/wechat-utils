# WeChat Utils

该库用于微信相关接口调用

要求：`PHP >= 7.2.5`

安装：`composer require maxsky/wechat-utils`

## App

App 目前仅完成一个方法用于微信授权登录获取用户信息

```php
$app = new \MaxSky\WeChat\Services\App\App();

/** @var array */
$userInfo = $app->getUserInfoFromAuth('auth_access_token', 'open_id');
```

## 公众号

### 一般方法

微信网页授权登录获取用户信息

```php
$oa = new \MaxSky\WeChat\Services\OA\OfficialAccount('app_id', 'app_secret');

/** @var array */
$userInfo = $oa->getUserInfoByAuthCode('js_auth_code');
```

### 依赖 Access Token 的方法

一些需要 Access Token 的方法

```php
// 注意该 Token 不同于 auth_access_token，需自行缓存，建议缓存 7000s 左右，不大于 7200s
// 这里简单示例从缓存中取出 Access Token 并设置，如果不存在则调用 getAccessToken 进行获取
// 成功获取时会自动设置到当前对象成员 access_token，即 $oa->access_token，故无需手动 setAccessToken
if ($accessTokenFromCacheExist) {
    $oa->setAccessToken($accessTokenFromCacheExist);
} else {
    $accessToken = $oa->getAccessToken('app_id', 'app_secret');
    // TODO: 设置到缓存
}

/** @var array UnionID 机制获取用户信息 */
$userInfo = $oa->getUserInfo('open_id');

// 创建二维码，第二个参数为类型，可用值：QR_SCENE | QR_STR_SCENE | QR_LIMIT_SCENE | QR_LIMIT_STR_SCENE
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
], '对应 first 内容', '对应 keyword1 内容', '对应 keyword2 内容', '对应 remark 内容');

/** @var bool 发送模板消息 */
$result = $oa->sendTemplateMessage('open_id', 'template_id', $data, 'url');
```

### JsAPI Ticket 方法

```php
// JsApi Ticket 需缓存，建议缓存 7000s 左右，不大于 7200s
// 同 Access Token 缓存

if ($jsApiTicketFromCacheExist) {
    $oa->setJsApiTicket($jsApiTicketFromCacheExist);
} else {
    $jsApiTicket = $oa->getJsApiTicket();
    // TODO: 设置到缓存
}

// URL 签名，用于分享等操作
$signPkg = $oa->getSignPackage('url');
```

### 消息方法

略

## 小程序

### 登录

小程序登录，通过授权 `code` 获取 `session` 数组，小程序获取用户信息后传递 `iv` 及加密用户数据到后端，通过 `decryptUserData` 方法解密得到微信用户数据

```php
$mp = new \MaxSky\WeChat\Services\MP\MicroProgram('app_id', 'app_secret');

$session = $mp->code2Session('code');

/** @var array */
$userInfo = $mp->decryptUserData($session['session_key'], 'iv_from_request', 'encrypted_data_from_request');
```

### 获取用户手机号

需要先获取 `Access Token`，同公众号，**注意区分缓存**

```php
if ($mpAccessTokenFromCacheExist) {
    $mp->setAccessToken($mpAccessTokenFromCacheExist);
} else {
    $accessToken = $mp->getAccessToken('app_id', 'app_secret');
    // TODO: 设置到缓存
}

$phoneInfo = $mp->code2PhoneNumber('code');
```

## 企业微信

### 验证 URL 有效性

该阶段为企业微信后台设置 **接收消息服务器**、**Token** 以及 **EncodingAESKey** 时使用

```php
$weCom = new \MaxSky\WeChat\Services\Com\WeCom();

// 将验证结果直接响应即可
return $weCom->verifyURL($echostr, $msg_signature, $timestamp, string $nonce);
```

### 解密消息

第一步验证签名

```php
// 获取请求内容 XML
$message = $request->getContent();

$parsed = $weCom->parseMessage($message);

// 返回 bool
$weCom->checkSignature($msg_signature, $timestamp, $nonce, $parsed->Encrypt->__toString());
```

解密

```php
$decrypted = $weCom->decryptMessage($message, $msg_signature, $timestamp, $nonce);
```

### 获取客户详情

需要先获取 `Access Token`

```php
if ($weComAccessTokenFromCacheExist) {
    $weCom->setAccessToken($weComAccessTokenFromCacheExist);
} else {
    $accessToken = $weCom->getAccessToken('corp_id', 'app_secret');
}

$externalUserInfo = $weCom->getExternalUserInfo('external_userid_from_decrypted_msg');
```

### 客户联系「联系我」管理

文档链接：[https://developer.work.weixin.qq.com/document/path/92228](https://developer.work.weixin.qq.com/document/path/92228)

`WeCom` 实例化后，可使用 `getContactWay`、`getContactWayList`、`addContactWay`、
`updateContactWay`、`deleteContactWay` 方法进行管理
