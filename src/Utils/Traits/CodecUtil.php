<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:49 AM
 */

namespace MaxSky\WeChat\Utils\Traits;

use Exception;

trait CodecUtil {

    use RandomUtil;

    private $blockSize = 32;

    /**
     * @param string $text
     *
     * @return string
     */
    private function encode(string $text): string {
        $text_length = strlen($text);

        $amount_to_pad = $this->blockSize - ($text_length % $this->blockSize);

        if ($amount_to_pad == 0) {
            $amount_to_pad = $this->blockSize;
        }

        $pad_chr = chr($amount_to_pad);

        $tmp = str_repeat($pad_chr, $amount_to_pad);

        return $text . $tmp;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function decode(string $text): string {
        $pad = ord(substr($text, -1));

        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }

        return substr($text, 0, (strlen($text) - $pad));
    }

    /**
     * @param string $text
     *
     * @return int|string
     * @throws Exception
     */
    private function encrypt(string $text) {
        $text = $this->str_random() . pack('N', strlen($text)) . $text . $this->appId;

        // use custom fill method for fill-in with plaintext
        $text = $this->encode($text);

        $iv = substr($this->aesKey, 0, 16);

        // encrypt
        $encrypted = openssl_encrypt($text, 'AES-256-CBC', $this->aesKey, OPENSSL_ZERO_PADDING, $iv);

        return $encrypted ?: -40006;
    }

    /**
     * @param string $encrypted
     *
     * @return int|string
     */
    private function decrypt(string $encrypted) {
        $iv = substr($this->aesKey, 0, 16);

        // decrypt
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->aesKey, OPENSSL_ZERO_PADDING, $iv);

        if (!$decrypted) {
            return -40007;
        }

        $result = $this->decode($decrypted);

        if (strlen($result) < 16) {
            return '';
        }

        $content = substr($result, 16, strlen($result));
        $lenList = unpack('N', substr($content, 0, 4));

        $lenXML = $lenList[1];

        $fromAppId = substr($content, $lenXML + 4);

        if ($fromAppId !== $this->appId) {
            return -40001;
        }

        return substr($content, 4, $lenXML);
    }
}
