<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 3:12 AM
 */

namespace MaxSky\WeChat\Utils\Traits;

use MaxSky\WeChat\Exceptions\WeChatUtilsException;

trait WeChatMPUtil {

    /**
     * @param string $session_key
     * @param string $iv
     * @param string $encrypted_data
     *
     * @return array
     * @throws WeChatUtilsException
     */
    public function decryptUserData(string $session_key, string $iv, string $encrypted_data): array {
        if (strlen($session_key) !== 24) {
            throw new WeChatUtilsException(WECHAT_MP_ERROR_CODE[-41001]);
        }

        $aesKey = base64_decode($session_key);

        if (strlen($iv) !== 24) {
            throw new WeChatUtilsException(WECHAT_MP_ERROR_CODE[-41002]);
        }

        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encrypted_data);

        $dataStr = openssl_decrypt($aesCipher, 'AES-128-CBC', $aesKey, 1, $aesIV);

        /** @var array $data */
        $data = json_decode($dataStr, true);

        if (($data['watermark']['appid'] ?? null) === $this->appId) {
            return $data;
        }

        throw new WeChatUtilsException(WECHAT_MP_ERROR_CODE[-41003]);
    }
}
