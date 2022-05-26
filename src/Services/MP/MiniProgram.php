<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 2:58 AM
 */

namespace MaxSky\WeChat\Services\MP;

use MaxSky\WeChat\Exceptions\WeChatUtilsException;
use MaxSky\WeChat\Services\WeChatBase;
use MaxSky\WeChat\Utils\Traits\WeChatMPUtil;

class MiniProgram extends WeChatBase {

    use WeChatMPUtil;

    private $appId;
    private $appSecret;

    public function __construct(string $app_id, string $app_secret) {
        parent::__construct();

        $this->appId = $app_id;
        $this->appSecret = $app_secret;
    }

    /**
     * @param string $code
     *
     * @return array|null
     * @throws WeChatUtilsException
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
     * 有限制获取小程序码
     *
     * @param string $path       最长 128 字节
     * @param int    $width      二维码宽度，单位 px。最小 280；最大 1280
     * @param bool   $auto_color 自动配置线条颜色
     * @param array  $line_color 使用 RGB 设置颜色，auto_color 为 false 时生效
     * @param bool   $is_hyaline 是否需要透明底色
     *
     * @return string
     * @throws WeChatUtilsException
     */
    public function getWxaCode(string $path, int $width = 430, bool $auto_color = false,
                               array  $line_color = ['r' => 0, 'g' => 0, 'b' => 0],
                               bool   $is_hyaline = false): string {
        if (!$this->access_token) {
            throw new WeChatUtilsException('Must set Access Token first.');
        }

        $response = $this->httpRequest(WECHAT_MP_GET_WXA_CODE, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'path' => $path,
                'width' => $width,
                'auto_color' => $auto_color,
                'line_color' => $line_color,
                'is_hyaline' => $is_hyaline
            ]
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * 无限制获取小程序码（适用于自定参数）
     *
     * @param string      $scene      最大 32 个可见字符，只支持数字，大小写英文以及部分特殊字符
     * @param string|null $page       小程序页面
     * @param int         $width      二维码宽度，单位 px。最小 280；最大 1280
     * @param bool        $auto_color 自动配置线条颜色
     * @param array       $line_color 使用 RGB 设置颜色，auto_color 为 false 时生效
     * @param bool        $is_hyaline 是否需要透明底色
     *
     * @return string
     * @throws WeChatUtilsException
     */
    public function getWxaCodeUnlimited(string $scene, ?string $page = null, int $width = 430, bool $auto_color = false,
                                        array  $line_color = ['r' => 0, 'g' => 0, 'b' => 0],
                                        bool   $is_hyaline = false): string {
        if (!$this->access_token) {
            throw new WeChatUtilsException('Must set Access Token first.');
        }

        $response = $this->httpRequest(WECHAT_MP_GET_WXA_CODE_UNLIMITED, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'scene' => $scene,
                'page' => $page,
                'width' => $width,
                'auto_color' => $auto_color,
                'line_color' => $line_color,
                'is_hyaline' => $is_hyaline
            ]
        ], 'POST');

        return $this->handleResponse($response);
    }

    /**
     * @param string      $open_id
     * @param string      $template_id
     * @param array       $data
     * @param string|null $page
     * @param string      $miniprogram_state
     *
     * @return bool
     * @throws WeChatUtilsException
     */
    public function sendSubscribeMessage(string  $open_id,
                                         string  $template_id,
                                         array   $data,
                                         ?string $page = null,
                                         string  $miniprogram_state = 'formal'): bool {
        if (!$this->access_token) {
            throw new WeChatUtilsException('Must set Access Token first.');
        }

        $response = $this->httpRequest(WECHAT_MP_SEND_SUBSCRIBE_MESSAGE, [
            'query' => [
                'access_token' => $this->access_token
            ],
            'json' => [
                'touser' => $open_id,
                'template_id' => $template_id,
                'data' => $data,
                'page' => $page,
                'miniprogram_state' => $miniprogram_state
            ]
        ], 'POST');

        return (bool)$this->handleResponse($response);
    }
}
