<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:54 AM
 */

namespace MaxSky\WeChat\Services\OA;

use MaxSky\WeChat\Exceptions\WeChatUtilsException;
use MaxSky\WeChat\Services\WeChatBase;
use MaxSky\WeChat\Utils\Traits\SignPackage;
use MaxSky\WeChat\Utils\Traits\WeChatOAMessage;
use Psr\Http\Message\StreamInterface;

class OfficialAccount extends WeChatBase {

    use WeChatOAMessage, SignPackage;

    private $appId;
    private $appSecret;
    private $serverToken;
    private $aesKey;

    protected $jsapi_ticket;

    public function __construct(string $app_id,
                                string $app_secret, ?string $server_token = null, ?string $aes_key = null) {
        parent::__construct();

        $this->appId = $app_id;
        $this->appSecret = $app_secret;
        $this->serverToken = $server_token;

        $this->aesKey = $aes_key ? base64_decode("$aes_key=") : null;
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#3
     *
     * @param string $code
     *
     * @return array
     * @throws WeChatUtilsException
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
     * @param string $open_id
     *
     * @return array
     * @throws WeChatUtilsException
     */
    public function getUserInfo(string $open_id): array {
        if (!$this->access_token) {
            throw new WeChatUtilsException('Must set Access Token first.');
        }

        $response = $this->httpRequest(WECHAT_OA_GET_USER_INFO, [
            'query' => [
                'access_token' => $this->access_token,
                'openid' => $open_id,
                'lang' => 'zh_CN'
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html#54
     *
     * @return string
     * @throws WeChatUtilsException
     */
    public function getJsApiTicket(): string {
        if (!$this->access_token) {
            throw new WeChatUtilsException('Must set Access Token first.');
        }

        $response = $this->httpRequest(WECHAT_OA_GET_JSAPI_TICKET, [
            'query' => [
                'access_token' => $this->access_token,
                'type' => 'jsapi'
            ]
        ]);

        $response = $this->handleResponse($response);

        return $response['ticket'];
    }

    /**
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
     * @param string $open_id
     * @param string $template_id
     * @param array  $data
     * @param string $url
     *
     * @return bool
     * @throws WeChatUtilsException
     */
    public function sendTemplateMessage(string $open_id, string $template_id, array $data, string $url = ''): bool {
        if (!$this->access_token) {
            throw new WeChatUtilsException('Must set Access Token first.');
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
                //'topcolor' => '#FF0000'
            ]
        ]);

        return (bool)$this->handleResponse($response);
    }

    /**
     * @url https://developers.weixin.qq.com/doc/offiaccount/Account_Management/Generating_a_Parametric_QR_Code.html
     *
     * @param bool       $limit          forever
     * @param string     $action_name    QrCode type
     * @param int|string $scene_value
     * @param int        $expire_seconds default???2592000s, 30 days
     *
     * @return array
     * @throws WeChatUtilsException
     */
    public function createQrcode(bool $limit, string $action_name, $scene_value, int $expire_seconds = 2592000): array {
        if (!$this->access_token) {
            throw new WeChatUtilsException('Must set Access Token first.');
        }

        if (($limit && !in_array($action_name, WECHAT_SCENE_LIMIT))
            || (!$limit && !in_array($action_name, WECHAT_SCENE))) {
            throw new WeChatUtilsException('?????????????????????????????????');
        }

        if (is_string(WECHAT_SCENE_TYPE[$action_name])) {
            $scene = ['scene_str' => $scene_value];
        } elseif (is_int(WECHAT_SCENE_TYPE[$action_name])) {
            $scene = ['scene_id' => $scene_value];
        } else {
            throw new WeChatUtilsException('???????????????????????????????????????');
        }

        $params = [
            'action_name' => $action_name,
            'action_info' => [
                'scene' => $scene
            ]
        ];

        if (!$limit) {
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
     * @param string $ticket
     *
     * @return StreamInterface
     * @throws WeChatUtilsException
     */
    public function getQrcode(string $ticket): StreamInterface {
        return $this->httpRequest(WECHAT_OA_UTIL_SHOW_QRCODE, [
            'query' => [
                'ticket' => urlencode($ticket)
            ]
        ]);
    }
}
