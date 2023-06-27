<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 2023/6/27
 * Time: 20:32
 */

namespace MaxSky\WeChat\Services\Com;

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
}
