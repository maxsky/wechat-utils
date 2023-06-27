<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 2:16 AM
 */

namespace MaxSky\WeChat\Utils\Traits;

trait WeChatOAMessage {

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
}
