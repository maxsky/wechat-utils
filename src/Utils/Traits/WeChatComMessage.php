<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 2023/6/27
 * Time: 20:39
 */

namespace MaxSky\WeChat\Utils\Traits;

use MaxSky\WeChat\Exceptions\WeChatUtilsMessageException;

trait WeChatComMessage {

    use WeChatMessageBaseTrait;

    /**
     * @param string     $message
     * @param string     $msg_signature
     * @param int|string $timestamp
     * @param string     $nonce
     *
     * @return true
     * @throws WeChatUtilsMessageException
     */
    public function verifyURL(string $message, string $msg_signature, $timestamp, string $nonce): bool {
        if (strlen($this->aesKey) !== 32) { // the original length is 43, but after `base64_decode` is 32
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[-40004]);
        }

        if (!$this->checkSignature($msg_signature, $timestamp, $nonce, $message)) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[-40001]);
        }

        $ret = $this->decrypt($message);

        if (is_int($ret)) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[$ret]);
        }

        return true;
    }
}
