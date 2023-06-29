<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 2023/6/28
 * Time: 3:48
 */

namespace MaxSky\WeChat\Utils\Traits;

use Exception;
use MaxSky\WeChat\Exceptions\WeChatUtilsMessageException;
use SimpleXMLElement;

trait WeChatMessageBaseTrait {

    use CodecUtil;

    /**
     * Check message signature
     *
     * @param string      $signature   签名
     * @param string      $timestamp   10 位时间戳
     * @param string      $nonce       随机值
     * @param string|null $encrypt_msg 加密消息
     *
     * @return bool
     */
    public function checkSignature(string $signature,
                                   string $timestamp, string $nonce, ?string $encrypt_msg = null): bool {
        $array = [$this->serverToken, $timestamp, $nonce];

        if ($encrypt_msg) {
            $array[] = $encrypt_msg;
        }

        sort($array, SORT_STRING);

        return sha1(implode($array)) === $signature;
    }

    /**
     * @param string $encrypt_msg 加密消息
     * @param string $timestamp   10 位时间戳
     * @param string $nonce       随机值
     *
     * @return string
     */
    public function generateSignature(string $encrypt_msg, string $timestamp, string $nonce): string {
        $array = [$encrypt_msg, $this->serverToken, $timestamp, $nonce];

        sort($array, SORT_STRING);

        return sha1(implode($array));
    }

    /**
     * @param string $reply_message 回复消息
     * @param string $timestamp     10 位时间戳
     * @param string $nonce         随机值
     *
     * @return string
     * @throws WeChatUtilsMessageException
     */
    public function encryptMessage(string $reply_message, string $timestamp, string $nonce): string {
        // encrypt
        $encrypted = $this->encrypt($reply_message);

        if (is_int($encrypted)) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[$encrypted]);
        }

        $signature = $this->generateSignature($encrypted, $timestamp, $nonce);

        if (!$signature) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[-40003]);
        }

        return sprintf(
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '../../../resources/xml/reply_msg_crypt.xml'),
            $encrypted, $signature, $timestamp, $nonce
        );
    }

    /**
     * 消息解密
     *
     * @param string     $message
     * @param string     $msg_signature
     * @param int|string $timestamp
     * @param string     $nonce
     *
     * @return SimpleXMLElement
     * @throws WeChatUtilsMessageException
     */
    public function decryptMessage(string $message, string $msg_signature, $timestamp, string $nonce): SimpleXMLElement {
        // get message
        try {
            $message = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_COMPACT + LIBXML_NOCDATA);
        } catch (Exception $e) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[-40002], 0, $e);
        }

        // get encrypt text
        $encrypt = $message->Encrypt->__toString();

        if (!$encrypt) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[-40002]);
        }

        // check sign
        if (!$this->checkSignature($msg_signature, $timestamp, $nonce, $encrypt)) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[-40001]);
        }

        $decrypted = $this->decrypt($encrypt);

        if (is_int($decrypted)) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[$decrypted]);
        }

        try {
            return simplexml_load_string($decrypted, 'SimpleXMLElement', LIBXML_COMPACT + LIBXML_NOCDATA);
        } catch (Exception $e) {
            throw new WeChatUtilsMessageException(WECHAT_MSG_ERROR_CODE[-40002], 0, $e);
        }
    }
}
