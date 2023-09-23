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

    public function __construct(string  $app_id, ?string $app_secret = null,
                                ?string $server_token = null, ?string $aes_key = null, bool $debug = false) {
        parent::__construct($app_id, $app_secret, $debug);

        $this->serverToken = $server_token;

        $this->aesKey = $aes_key ? base64_decode("$aes_key=") : null;
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/10013#%E7%AC%AC%E4%B8%89%E6%AD%A5%EF%BC%9A%E8%8E%B7%E5%8F%96access-token
     *
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
     * @url https://developer.work.weixin.qq.com/document/path/90208
     *
     * @param int|null $id
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getDepartmentIdList(?int $id = null): array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_COM_GET_DEPARTMENT_ID_LIST, [
            'query' => [
                'access_token' => $this->access_token ?? null,
                'id' => $id
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * 获取子部门 ID 列表（性能强于 getDepartmentIdList）
     *
     * @url https://developer.work.weixin.qq.com/document/path/95350
     *
     * @param int|null $id
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getDepartmentSubList(?int $id = null): array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_COM_GET_DEPARTMENT_SUB_LIST, [
            'query' => [
                'access_token' => $this->access_token ?? null,
                'id' => $id
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/90200
     *
     * @param int $department_id
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getDepartmentUserList(int $department_id): array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_COM_GET_DEPARTMENT_USER_LIST, [
            'query' => [
                'access_token' => $this->access_token,
                'department_id' => $department_id
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/95351
     *
     * @param int $department_id
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getDepartment(int $department_id): array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_COM_GET_DEPARTMENT, [
            'query' => [
                'access_token' => $this->access_token,
                'department_id' => $department_id
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/96067
     *
     * @param string|null $cursor
     * @param int         $limit
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getUserIdList(?string $cursor = null, int $limit = 15): array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_COM_GET_USER_ID_LIST, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'cursor' => $cursor,
                'limit' => $limit
            ]
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/90196
     *
     * @param string $id
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getUser(string $id): array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_COM_GET_USER, [
            'query' => [
                'access_token' => $this->access_token,
                'userid' => $id
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/92228#%E8%8E%B7%E5%8F%96%E4%BC%81%E4%B8%9A%E5%B7%B2%E9%85%8D%E7%BD%AE%E7%9A%84%E3%80%8C%E8%81%94%E7%B3%BB%E6%88%91%E3%80%8D%E6%96%B9%E5%BC%8F
     *
     * @param string $config_id
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getContactWay(string $config_id): array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_COM_GET_CONTACT_WAY, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'config_id' => $config_id
            ]
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/92228#%E8%8E%B7%E5%8F%96%E4%BC%81%E4%B8%9A%E5%B7%B2%E9%85%8D%E7%BD%AE%E7%9A%84%E3%80%8C%E8%81%94%E7%B3%BB%E6%88%91%E3%80%8D%E5%88%97%E8%A1%A8
     *
     * @param int|null    $start_time
     * @param int|null    $end_time
     * @param string|null $cursor from `next_cursor` field in previous page response
     * @param int         $limit  default 100, max 1000
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getContactWayList(?int $start_time = null,
                                      ?int $end_time = null, ?string $cursor = null, int $limit = 100): array {
        $this->issetAccessToken();

        $params = [
            'limit' => $limit
        ];

        if ($start_time) {
            $params['start_time'] = $start_time;
        }

        if ($end_time) {
            $params['end_time'] = $end_time;
        }

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        $response = $this->httpRequest(WECHAT_COM_GET_CONTACT_WAY_LIST, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => $params
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/92228#%E9%85%8D%E7%BD%AE%E5%AE%A2%E6%88%B7%E8%81%94%E7%B3%BB%E3%80%8C%E8%81%94%E7%B3%BB%E6%88%91%E3%80%8D%E6%96%B9%E5%BC%8F
     *
     * @param int    $type
     * @param int    $scene
     * @param string $state           custom value, max 30 characters
     * @param bool   $is_temp         generate a temporary QRCode
     * @param int    $expires_in      default value is 864800 seconds, 7 days
     * @param int    $chat_expires_in default value is 86400 seconds, 1 day
     * @param array  $options         other parameters
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function addContactWay(int $type, int $scene, string $state = '', bool $is_temp = false,
                                  int $expires_in = 604800, int $chat_expires_in = 86400, array $options = []): array {
        $this->issetAccessToken();

        if ($is_temp) {
            $options['is_temp'] = $is_temp;
            $options['expires_in'] = $expires_in;
            $options['chat_expires_in'] = $chat_expires_in;
        }

        $params = array_merge([
            'type' => $type,
            'scene' => $scene,
            'state' => $state
        ], $options);

        $response = $this->httpRequest(WECHAT_COM_ADD_CONTACT_WAY, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => $params
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * @url https://developer.work.weixin.qq.com/document/path/92228#%E6%9B%B4%E6%96%B0%E4%BC%81%E4%B8%9A%E5%B7%B2%E9%85%8D%E7%BD%AE%E7%9A%84%E3%80%8C%E8%81%94%E7%B3%BB%E6%88%91%E3%80%8D%E6%96%B9%E5%BC%8F
     *
     * @param string $config_id
     * @param string $state
     * @param int    $expires_in
     * @param int    $chat_expires_in
     * @param array  $options
     *
     * @return bool
     * @throws WeChatUtilsGeneralException
     */
    public function updateContactWay(string $config_id, string $state = '', int $expires_in = 604800,
                                     int    $chat_expires_in = 86400, array $options = []): bool {
        $this->issetAccessToken();

        $params = array_merge([
            'config_id' => $config_id,
            'state' => $state,
            'expires_in' => $expires_in,
            'chat_expires_in' => $chat_expires_in
        ], $options);

        $response = $this->httpRequest(WECHAT_COM_UPDATE_CONTACT_WAY, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => $params
        ], 'POST');

        $result = $this->handleResponse($response);

        return ($result['errcode'] ?? null) === 0;
    }

    /**
     * @param string $config_id
     *
     * @return bool
     * @throws WeChatUtilsGeneralException
     */
    public function deleteContactWay(string $config_id): bool {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_COM_DELETE_CONTACT_WAY, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'config_id' => $config_id
            ]
        ], 'POST');

        $result = $this->handleResponse($response);

        return ($result['errcode'] ?? null) === 0;
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
        $this->issetAccessToken();

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
