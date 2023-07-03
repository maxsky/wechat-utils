<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 2023/6/27
 * Time: 20:32
 */

namespace MaxSky\WeChat\Services\Com;

use MaxSky\WeChat\Exceptions\WeChatUtilsGeneralException;
use MaxSky\WeChat\Services\WeChatBase;
use MaxSky\WeChat\Utils\Traits\WeChatComMessage;

class WeCom extends WeChatBase {

    use WeChatComMessage;

    private $serverToken;
    private $aesKey;

    public function __construct(string  $app_id,
                                ?string $app_secret = null, ?string $server_token = null, ?string $aes_key = null) {
        parent::__construct($app_id, $app_secret);

        $this->serverToken = $server_token;

        $this->aesKey = $aes_key ? base64_decode("$aes_key=") : null;
    }

    /**
     * @param string|null $app_id
     * @param string|null $app_secret
     *
     * @return string
     * @throws WeChatUtilsGeneralException
     */
    public function getAccessToken(?string $app_id = null, ?string $app_secret = null): string {
        $response = $this->httpRequest(WECHAT_COM_GET_API_ACCESS_TOKEN, [
            'query' => [
                'corpid' => $app_id ?: $this->appId,
                'corpsecret' => $app_secret ?: $this->appSecret
            ]
        ]);

        $response = $this->handleResponse($response);

        $this->access_token = $response['access_token'];

        return $response['access_token'];
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/92114
     *
     * 获取客户详情
     *
     * @param string      $external_userid
     * @param string|null $cursor
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getExternalUserInfo(string $external_userid, ?string $cursor = null): array {
        if (!$this->access_token) {
            throw new WeChatUtilsGeneralException('Must set Access Token first.');
        }

        $response = $this->httpRequest(WECHAT_COM_GET_USER_INFO, [
            'query' => [
                'access_token' => $this->access_token,
                'external_userid' => $external_userid,
                'cursor' => $cursor
            ]
        ]);

        return $this->handleResponse($response);
    }
}
