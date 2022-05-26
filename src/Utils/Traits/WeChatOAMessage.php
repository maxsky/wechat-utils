<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 2:16 AM
 */

namespace MaxSky\WeChat\Utils\Traits;

use Exception;
use MaxSky\WeChat\Exceptions\WeChatUtilsException;
use SimpleXMLElement;

trait WeChatOAMessage {

    use CodecUtil;

    /**
     * Check message signature
     *
     * @param string      $signature   签名
     * @param string      $timestamp   10 位时间戳
     * @param string      $nonce       随机数
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
     * @param string $encrypt_msg
     * @param string $timestamp
     * @param string $nonce
     *
     * @return string
     */
    public function generateSignature(string $encrypt_msg, string $timestamp, string $nonce): string {
        $array = [$encrypt_msg, $this->serverToken, $timestamp, $nonce];

        sort($array, SORT_STRING);

        return sha1(implode($array));
    }

    /**
     * @param string $open_id
     * @param string $to
     * @param string $timestamp
     * @param string $reply_msg
     * @param string $msg_type
     *
     * @return string
     */
    public function getReplyMessage(string $open_id,
                                    string $to, string $timestamp, string $reply_msg, string $msg_type = 'text'): string {
        return sprintf(
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '../../../resources/xml/reply_msg.xml'),
            $open_id, $to, $timestamp, $msg_type, $reply_msg
        );
    }

    /**
     * @param string $reply_message
     * @param string $timestamp
     * @param string $nonce
     *
     * @return string
     * @throws Exception
     */
    public function encryptMessage(string $reply_message, string $timestamp, string $nonce): string {
        // encrypt
        $encrypted = $this->encrypt($reply_message);

        if (is_int($encrypted)) {
            return WECHAT_MSG_ERROR_CODE[$encrypted];
        }

        $signature = $this->generateSignature($encrypted, $timestamp, $nonce);

        if (!$signature) {
            return WECHAT_MSG_ERROR_CODE[-40001];
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
     * @return SimpleXMLElement|string
     * @throws WeChatUtilsException
     */
    public function decryptMessage(string $message, string $msg_signature, $timestamp, string $nonce) {
        // get message
        try {
            $message = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_COMPACT + LIBXML_NOCDATA);
        } catch (Exception $e) {
            throw new WeChatUtilsException(WECHAT_MSG_ERROR_CODE[-40002], 0, $e);
        }

        // get encrypt text
        $encrypt = $message->Encrypt->__toString();

        if (!$encrypt) {
            return WECHAT_MSG_ERROR_CODE[-40002];
        }

        // check sign
        if (!$this->checkSignature($msg_signature, $timestamp, $nonce, $encrypt)) {
            return WECHAT_MSG_ERROR_CODE[-40001];
        }

        $decrypted = $this->decrypt($encrypt);

        if (is_int($decrypted)) {
            return WECHAT_MSG_ERROR_CODE[$decrypted];
        }

        try {
            return simplexml_load_string($decrypted, 'SimpleXMLElement', LIBXML_COMPACT + LIBXML_NOCDATA);
        } catch (Exception $e) {
            throw new WeChatUtilsException(WECHAT_MSG_ERROR_CODE[-40002], 0, $e);
        }
    }
}
