<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:43 AM
 */

namespace MaxSky\WeChat\Services\App;

use MaxSky\WeChat\Exceptions\WeChatUtilsGeneralException;
use MaxSky\WeChat\Services\WeChatBase;

class App extends WeChatBase {

    /**
     * 通过客户端 SDK 返回的 Access Token 以及 OpenID 获取用户信息
     * 一般情况下，App 开发无需自行实现 Code 换取工作，如有需要，请调用 getUserInfoByAuthCode 方法进行用户信息获取
     *
     * @url https://developers.weixin.qq.com/doc/oplatform/Mobile_App/WeChat_Login/Authorized_API_call_UnionID.html
     *
     * @param string $auth_access_token 授权后 Access Token
     * @param string $open_id           用户 OpenID
     * @param string $lang              语言，默认 zh_CN
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getUserInfoFromAuth(string $auth_access_token, string $open_id, string $lang = 'zh_CN'): array {
        $response = $this->httpRequest(WECHAT_GET_USER_INFO_FROM_AUTH, [
            'query' => [
                'access_token' => $auth_access_token,
                'openid' => $open_id,
                'lang' => $lang
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * 通过授权返回 Code 获取用户信息
     *
     * @url https://developers.weixin.qq.com/doc/oplatform/Mobile_App/WeChat_Login/Development_Guide.html#%E7%AC%AC%E4%BA%8C%E6%AD%A5%EF%BC%9A%E9%80%9A%E8%BF%87-code-%E8%8E%B7%E5%8F%96-access-token
     *
     * @param string $code
     * @param string $lang
     *
     * @return array
     * @throws WeChatUtilsGeneralException
     */
    public function getUserInfoByAuthCode(string $code, string $lang = 'zh_CN'): array {
        $response = $this->httpRequest(WECHAT_OA_GET_AUTH_ACCESS_TOKEN, [
            'query' => [
                'appid' => $this->appId,
                'secret' => $this->appSecret,
                'code' => $code,
                'grant_type' => 'authorization_code'
            ]
        ]);

        $authed = $this->handleResponse($response);

        return $this->getUserInfoFromAuth($authed['access_token'], $authed['openid'], $lang);
    }
}
