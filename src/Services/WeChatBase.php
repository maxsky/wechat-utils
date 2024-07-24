<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:31 AM
 */

namespace MaxSky\WeChat\Services;

use GuzzleHttp\Client;
use MaxSky\WeChat\Exceptions\WeChatUtilsGeneralException;
use Psr\Http\Message\StreamInterface;
use Throwable;

abstract class WeChatBase {

    protected $appId = null;
    protected $appSecret = null;

    protected $access_token;

    protected $httpClient;

    protected $debug;

    public function __construct(?string $app_id = null, ?string $app_secret = null, bool $debug = false) {
        $this->appId = $app_id;
        $this->appSecret = $app_secret;

        $this->debug = $debug;

        $this->httpClient = new Client();
    }

    /**
     * 获取全局 Access Token，此 Token 与 Auth Access Token（即微信授权登录返回 Token） 不同
     *
     * @url https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
     *
     * @param string|null $app_id
     * @param string|null $app_secret
     *
     * @return string
     * @throws WeChatUtilsGeneralException
     */
    public function getAccessToken(?string $app_id = null, ?string $app_secret = null): string {
        $response = $this->httpRequest(WECHAT_OA_GET_API_ACCESS_TOKEN, [
            'query' => [
                'appid' => $app_id ?: $this->appId,
                'secret' => $app_secret ?: $this->appSecret,
                'grant_type' => 'client_credential'
            ]
        ]);

        $response = $this->handleResponse($response);

        $this->access_token = $response['access_token'];

        return $response['access_token'];
    }

    /**
     * 设置对象 Access Token，一般用于取出已缓存的 Access Token 进行设置
     *
     * @param string $access_token
     *
     * @return WeChatBase
     */
    public function setAccessToken(string $access_token): WeChatBase {
        $this->access_token = $access_token;

        return $this;
    }

    /**
     * @return void
     * @throws WeChatUtilsGeneralException
     */
    protected function issetAccessToken() {
        if (!$this->access_token) {
            throw new WeChatUtilsGeneralException('Must set Access Token first.');
        }
    }

    /**
     * @param StreamInterface $response
     *
     * @return array|string
     * @throws WeChatUtilsGeneralException
     */
    protected function handleResponse(StreamInterface $response) {
        $result = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $result['errcode'] = $result['errcode'] ?? -999999;

            if (in_array($result['errcode'], [0, -999999])) {
                return $result;
            }

            $result['errmsg'] = $result['errmsg'] ?? 'Unknown reason request failed';

            throw new WeChatUtilsGeneralException(
                "Request WeChat API failed, error code: {$result['errcode']}, error message: {$result['errmsg']}",
                $result['errcode']
            );
        }

        return $response->getContents();
    }

    /**
     * @param string $uri
     * @param array  $options
     * @param string $method
     *
     * @return StreamInterface
     * @throws WeChatUtilsGeneralException
     */
    protected function httpRequest(string $uri, array $options = [], string $method = 'GET'): StreamInterface {
        if ($this->debug) {
            $options['query']['debug'] = 1;
        }

        try {
            return $this->httpClient->request($method, $uri, $options)->getBody();
        } catch (Throwable $e) {
            throw new WeChatUtilsGeneralException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
