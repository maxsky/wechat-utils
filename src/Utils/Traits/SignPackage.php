<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 2:34 AM
 */

namespace MaxSky\WeChat\Utils\Traits;

use MaxSky\WeChat\Exceptions\WeChatUtilsException;

trait SignPackage {

    /**
     * @param string $url
     *
     * @return array
     * @throws WeChatUtilsException
     */
    public function getSignPackage(string $url): array {
        if (!$this->jsapi_ticket) {
            throw new WeChatUtilsException('Must set JsApi Ticket first.');
        }

        $timestamp = time();

        $nonceStr = $this->str_random();

        // Key order by ASCII
        $string = "jsapi_ticket=$this->jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        return [
            'appId' => $this->appId,
            'nonceStr' => $nonceStr,
            'timestamp' => $timestamp,
            'url' => $url,
            'signature' => $signature,
            //'rawString' => $string // must remove in production environment
        ];
    }
}
