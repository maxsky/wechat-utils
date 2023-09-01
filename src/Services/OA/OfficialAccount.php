<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:54 AM
 */

namespace MaxSky\WeChat\Services\OA;

use MaxSky\WeChat\Exceptions\WeChatUtilsGeneralException;
use MaxSky\WeChat\Services\WeChatBase;
use MaxSky\WeChat\Utils\Traits\SignPackage;
use MaxSky\WeChat\Utils\Traits\WeChatOAMessage;
use Psr\Http\Message\StreamInterface;

class OfficialAccount extends WeChatBase {

    use WeChatOAMessage, SignPackage;

    private $serverToken;
    private $aesKey;

    protected $jsapi_ticket;

    public function __construct(string $app_id,
                                string $app_secret, ?string $server_token = null, ?string $aes_key = null) {
        parent::__construct($app_id, $app_secret);

        $this->serverToken = $server_token;

        $this->aesKey = $aes_key ? base64_decode("$aes_key=") : null;
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#1
     * @url https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#3
     *
     * 通过授权返回 Code 获取用户信息
     *
     * @param string $code
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getUserInfoByAuthCode(string $code): array {
        $response = $this->httpRequest(WECHAT_OA_GET_AUTH_ACCESS_TOKEN, [
            'query' => [
                'appid' => $this->appId,
                'secret' => $this->appSecret,
                'code' => $code,
                'grant_type' => 'authorization_code'
            ]
        ]);

        $authed = $this->handleResponse($response);

        $response = $this->httpRequest(WECHAT_GET_USER_INFO_FROM_AUTH, [
            'query' => [
                'access_token' => $authed['access_token'],
                'openid' => $authed['openid'],
                'lang' => 'zh_CN'
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/User_Management/Get_users_basic_information_UnionID.html#UinonId
     *
     * 获取用户基本信息（UnionID 机制）
     *
     * @param string $open_id
     * @param string $lang
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getUserInfo(string $open_id, string $lang = 'zh_CN'): array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_OA_GET_USER_INFO, [
            'query' => [
                'access_token' => $this->access_token,
                'openid' => $open_id,
                'lang' => $lang
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html#54
     *
     * 通过 Access Token 获取 JS API Ticket
     *
     * @return string
     * @throws WeChatUtilsGeneralException
     */
    public function getJsApiTicket(): string {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_OA_GET_JSAPI_TICKET, [
            'query' => [
                'access_token' => $this->access_token,
                'type' => 'jsapi'
            ]
        ]);

        $response = $this->handleResponse($response);

        $this->jsapi_ticket = $response['ticket'];

        return $response['ticket'];
    }

    /**
     * 设置对象 JS API Ticket，一般用于取出已缓存的 JS API Ticket 进行设置
     *
     * @param string $jsapi_ticket
     *
     * @return OfficialAccount
     */
    public function setJsApiTicket(string $jsapi_ticket): OfficialAccount {
        $this->jsapi_ticket = $jsapi_ticket;

        return $this;
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#5
     *
     * 发送模板消息
     *
     * @param string      $open_id       接收者
     * @param string      $template_id   模板 ID
     * @param array       $data          模板数据
     * @param string      $url           跳转链接
     * @param string|null $client_msg_id 防重入 ID
     * @param array       $mp_data       小程序数据集
     *
     * @return bool
     * @throws WeChatUtilsGeneralException
     */
    public function sendTemplateMessage(string  $open_id, string $template_id, array $data, string $url = '',
                                        ?string $client_msg_id = null,
                                        array   $mp_data = []): bool {
        $this->issetAccessToken();

        if ($mp_data && !($mp_data['appid'] ?? null)) {
            throw new WeChatUtilsGeneralException('MiniProgram AppID not set.');
        }

        $response = $this->httpRequest(WECHAT_OA_UTIL_SEND_TEMPLATE_MESSAGE, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                    'touser' => $open_id,
                    'template_id' => $template_id,
                    'data' => $data,
                    'url' => $url,
                    'client_msg_id' => $client_msg_id,
                    //'topcolor' => '#FF0000'
                ] + $mp_data
        ], 'POST');

        return (bool)$this->handleResponse($response);
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/Account_Management/Generating_a_Parametric_QR_Code.html
     *
     * 生成带参二维码
     *
     * @param bool       $limit          是否生成永久二维码
     * @param string     $action_name    可选值：QR_SCENE | QR_STR_SCENE | QR_LIMIT_SCENE | QR_LIMIT_STR_SCENE
     * @param int|string $scene_value    场景值
     * @param int        $expire_seconds 过期时间，默认 2592000 秒，即 30 天。仅临时二维码有效（$limit = false）
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function createQrcode(bool $limit, string $action_name, $scene_value, int $expire_seconds = 2592000): array {
        $this->issetAccessToken();

        if (($limit && !in_array($action_name, WECHAT_SCENE_LIMIT))
            || (!$limit && !in_array($action_name, WECHAT_SCENE))) {
            throw new WeChatUtilsGeneralException('场景对应二维码类型错误');
        }

        if (in_array($action_name, WECHAT_SCENE_LIMIT)) {
            $scene = ['scene_str' => $scene_value];
        } elseif (in_array($action_name, WECHAT_SCENE)) {
            $scene = ['scene_id' => $scene_value];
        } else {
            throw new WeChatUtilsGeneralException('场景对应场景值数据类型错误');
        }

        $params = [
            'action_name' => $action_name,
            'action_info' => [
                'scene' => $scene
            ]
        ];

        if (!$limit) {
            if ($expire_seconds <= 0) {
                $expire_seconds = 60;
            }

            $params['expire_seconds'] = $expire_seconds;
        }

        $response = $this->httpRequest(WECHAT_OA_UTIL_CREATE_QRCODE, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => $params
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/Account_Management/Generating_a_Parametric_QR_Code.html
     *
     * 通过 Ticket 换取二维码
     *
     * @param string $ticket
     *
     * @return StreamInterface
     * @throws WeChatUtilsGeneralException
     */
    public function getQrcode(string $ticket): StreamInterface {
        return $this->httpRequest(WECHAT_OA_UTIL_SHOW_QRCODE, [
            'query' => [
                'ticket' => urlencode($ticket)
            ]
        ]);
    }
}
