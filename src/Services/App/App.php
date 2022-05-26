<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:43 AM
 */

namespace MaxSky\WeChat\Services\App;

use MaxSky\WeChat\Exceptions\WeChatUtilsException;
use MaxSky\WeChat\Services\WeChatBase;

class App extends WeChatBase {

    /**
     * @param string $auth_access_token
     * @param string $open_id
     * @param string $lang
     *
     * @return array
     * @throws WeChatUtilsException
     */
    public function getUserInfoFromAuth(string $auth_access_token, string $open_id, string $lang = 'zh_CN'): array {
        $response = $this->httpRequest(WECHAT_GET_USER_INFO_FROM_AUTH, [
            'access_token' => $auth_access_token,
            'openid' => $open_id,
            'lang' => $lang
        ]);

        return $this->handleResponse($response);
    }
}
