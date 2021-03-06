<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:31 AM
 */

namespace MaxSky\WeChat\Services;

use GuzzleHttp\Client;
use MaxSky\WeChat\Exceptions\WeChatUtilsException;
use Psr\Http\Message\StreamInterface;
use Throwable;

abstract class WeChatBase {

    protected $access_token;

    protected $httpClient;

    public function __construct() {
        $this->httpClient = new Client();
    }

    /**
     * @param string $app_id
     * @param string $app_secret
     *
     * @return string
     * @throws WeChatUtilsException
     */
    public function getAccessToken(string $app_id, string $app_secret): string {
        $response = $this->httpRequest(WECHAT_OA_GET_API_ACCESS_TOKEN, [
            'query' => [
                'appid' => $app_id,
                'secret' => $app_secret,
                'grant_type' => 'client_credential'
            ]
        ]);

        $response = $this->handleResponse($response);

        $this->access_token = $response['access_token'];

        return $response['access_token'];
    }

    /**
     * @param string $access_token
     *
     * @return WeChatBase
     */
    public function setAccessToken(string $access_token): WeChatBase {
        $this->access_token = $access_token;

        return $this;
    }

    /**
     * @param StreamInterface $response
     *
     * @return array|string
     * @throws WeChatUtilsException
     */
    protected function handleResponse(StreamInterface $response) {
        $result = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $result['errcode'] = $result['errcode'] ?? -999999;

            if ($result['errcode'] === -999999 || $result['errcode'] === 0) {
                return $result;
            }

            $result['errmsg'] = $result['errmsg'] ?? 'Unknown reason request failed';

            throw new WeChatUtilsException(
                "Request WeChat API failed, error code: {$result['errcode']}, error message: {$result['errmsg']}"
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
     * @throws WeChatUtilsException
     */
    protected function httpRequest(string $uri, array $options = [], string $method = 'GET'): StreamInterface {
        try {
            return $this->httpClient->request($method, $uri, $options)->getBody();
        } catch (Throwable $e) {
            throw new WeChatUtilsException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
