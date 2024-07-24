<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 2:58 AM
 */

namespace MaxSky\WeChat\Services\MP;

use MaxSky\WeChat\Exceptions\WeChatUtilsGeneralException;
use MaxSky\WeChat\Services\WeChatBase;
use MaxSky\WeChat\Utils\Traits\WeChatMPUtil;

class MiniProgram extends WeChatBase {

    use WeChatMPUtil;

    /**
     * 通过 Code 获取 session_key（会话密钥）
     *
     * @url https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/code2Session.html
     *
     * @param string $code
     *
     * @return array|null
     * @throws WeChatUtilsGeneralException
     */
    public function code2Session(string $code): ?array {
        $response = $this->httpRequest(WECHAT_MP_CODE_TO_SESSION, [
            'query' => [
                'appid' => $this->appId,
                'secret' => $this->appSecret,
                'js_code' => $code,
                'grant_type' => 'authorization_code'
            ]
        ]);

        return $this->handleResponse($response);
    }

    /**
     * 通过 Code 获取用户手机号码
     *
     * @url https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/phone-number/getPhoneNumber.html
     *
     * @param string $code
     *
     * @return array|null
     * @throws WeChatUtilsGeneralException
     */
    public function code2PhoneNumber(string $code): ?array {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_MP_GET_USER_PHONE_NUMBER, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'code' => $code
            ]
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * 获取有数量限制的小程序码
     *
     * @url https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getQRCode.html
     *
     * @param string $path        小程序页面路径，最长 1024 字节
     * @param int    $width       二维码宽度，单位 px。默认 430，最小 280；最大 1280
     * @param bool   $auto_color  自动配置线条颜色
     * @param array  $line_color  使用 RGB 设置颜色，auto_color 为 false 时生效
     * @param bool   $is_hyaline  是否需要透明底色
     * @param string $env_version 小程序版本，默认 release 正式版，可选体验版 trial，开发版 develop
     *
     * @return string
     * @throws WeChatUtilsGeneralException
     */
    public function getWxaCode(string $path, int $width = 430, bool $auto_color = false,
                               array  $line_color = ['r' => 0, 'g' => 0, 'b' => 0],
                               bool   $is_hyaline = false, string $env_version = 'release'): string {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_MP_GET_WXA_CODE, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'path' => $path,
                'width' => $width,
                'auto_color' => $auto_color,
                'line_color' => $line_color,
                'is_hyaline' => $is_hyaline,
                'env_version' => $env_version
            ]
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * 获取无数量限制的小程序码（适用于自定义参数，包含场景、类型等）
     *
     * @url https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getUnlimitedQRCode.html
     *
     * @param string      $scene       最大 32 个可见字符，只支持数字，大小写英文以及部分特殊字符
     * @param string|null $page        小程序页面
     * @param bool        $check_path  检查 page 是否存在，默认 true
     * @param int         $width       二维码宽度，单位 px。默认 430，最小 280；最大 1280
     * @param bool        $auto_color  自动配置线条颜色
     * @param array       $line_color  使用 RGB 设置颜色，auto_color 为 false 时生效
     * @param bool        $is_hyaline  是否需要透明底色
     * @param string      $env_version 小程序版本，默认 release 正式版，可选体验版 trial，开发版 develop
     *
     * @return string
     * @throws WeChatUtilsGeneralException
     */
    public function getWxaCodeUnlimited(string $scene, ?string $page = null, bool $check_path = true, int $width = 430,
                                        bool   $auto_color = false, array $line_color = ['r' => 0, 'g' => 0, 'b' => 0],
                                        bool   $is_hyaline = false, string $env_version = 'release'): string {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_MP_GET_WXA_CODE_UNLIMITED, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'scene' => $scene,
                'page' => $page,
                'check_path' => $check_path,
                'width' => $width,
                'auto_color' => $auto_color,
                'line_color' => $line_color,
                'is_hyaline' => $is_hyaline,
                'env_version' => $env_version
            ]
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * 发送订阅消息
     *
     * @url https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/sendMessage.html
     *
     * @param string      $open_id           接收者
     * @param string      $template_id       订阅模板 ID
     * @param array       $data              模板内容
     * @param string|null $page              跳转页面
     * @param string      $miniprogram_state 小程序类型，默认 formal 为正式版，developer 开发版，trial 体验版
     * @param string      $lang              语言类型，默认 zh_CN（简体中文）
     *
     * @return bool
     * @throws WeChatUtilsGeneralException
     */
    public function sendSubscribeMessage(string  $open_id,
                                         string  $template_id,
                                         array   $data,
                                         ?string $page = null,
                                         string  $miniprogram_state = 'formal', string $lang = 'zh_CN'): bool {
        $this->issetAccessToken();

        $response = $this->httpRequest(WECHAT_MP_SEND_SUBSCRIBE_MESSAGE, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'touser' => $open_id,
                'template_id' => $template_id,
                'data' => $data,
                'page' => $page,
                'miniprogram_state' => $miniprogram_state,
                'lang' => $lang
            ]
        ], 'POST');

        return (bool)$this->handleResponse($response);
    }
}
