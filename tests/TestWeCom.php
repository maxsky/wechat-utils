<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 2023/6/28
 * Time: 4:57
 */

namespace Tests;

use Exception;
use MaxSky\WeChat\Exceptions\WeChatUtilsMessageException;
use MaxSky\WeChat\Services\Com\WeCom;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class TestWeCom extends TestCase {

    // All params form official sample
    private $corpId = 'wx5823bf96d3bd56c7'; // same as AppID of WeChat
    private $token = 'QDG6eK';
    private $encodingAesKey = 'jWmYm7qr5nMoAUwZRjGtBxmz3KA1tkAj3ykkR6q2B2C';

    private $timestamp = '1409659813'; // for message decrypt and encrypt test
    private $nonce = '1372623149'; // for message decrypt and encrypt test

    private $weCom;

    protected function setUp(): void {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->weCom = new WeCom($this->corpId, null, $this->token, $this->encodingAesKey);
    }

    public function testVerifyURL() {
        $msgSignature = '5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3';
        $timestamp = '1409659589';
        $nonce = '263014780';
        $echoStr = 'P9nAzCzyDtyTWESHep1vC5X9xho/qYX3Zpb4yKa9SKld1DsH3Iyt3tP3zNdtp+4RPcs8TgAE7OaBO+FZXvnaqQ==';

        // message param need use `$echStr` in WeCom, same as WeChat message
        try {
            $result = $this->weCom->verifyURL($echoStr, $msgSignature, $timestamp, $nonce);
        } catch (WeChatUtilsMessageException $e) {
            $result = null;
        }

        $this->assertIsString($result);
    }

    public function testDecryptUserMessage() {
        $msgSignature = '477715d11cdb4164915debcba66cb864d751f3e6';
        $requestMessage = '<xml><ToUserName><![CDATA[wx5823bf96d3bd56c7]]></ToUserName><Encrypt><![CDATA[RypEvHKD8QQKFhvQ6QleEB4J58tiPdvo+rtK1I9qca6aM/wvqnLSV5zEPeusUiX5L5X/0lWfrf0QADHHhGd3QczcdCUpj911L3vg3W/sYYvuJTs3TUUkSUXxaccAS0qhxchrRYt66wiSpGLYL42aM6A8dTT+6k4aSknmPj48kzJs8qLjvd4Xgpue06DOdnLxAUHzM6+kDZ+HMZfJYuR+LtwGc2hgf5gsijff0ekUNXZiqATP7PF5mZxZ3Izoun1s4zG4LUMnvw2r+KqCKIw+3IQH03v+BCA9nMELNqbSf6tiWSrXJB3LAVGUcallcrw8V2t9EL4EhzJWrQUax5wLVMNS0+rUPA3k22Ncx4XXZS9o0MBH27Bo6BpNelZpS+/uh9KsNlY6bHCmJU9p8g7m3fVKn28H3KDYA5Pl/T8Z1ptDAVe0lXdQ2YoyyH2uyPIGHBZZIs2pDBS8R07+qN+E7Q==]]></Encrypt><AgentID><![CDATA[218]]></AgentID></xml>';

        try {
            $decrypted = $this->weCom->decryptMessage($requestMessage, $msgSignature, $this->timestamp, $this->nonce);
        } catch (WeChatUtilsMessageException $e) {
            $decrypted = null;
        }

        $this->assertInstanceOf(SimpleXMLElement::class, $decrypted);
    }

    public function testEncryptReplyMessage() {
        $replyMessage = '<xml><ToUserName><![CDATA[mycreate]]></ToUserName><FromUserName><![CDATA[wx5823bf96d3bd56c7]]></FromUserName><CreateTime>1348831860</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[this is a test]]></Content><MsgId>1234567890123456</MsgId><AgentID>128</AgentID></xml>';

        try {
            $encryptedMessage = $this->weCom->encryptMessage($replyMessage, $this->timestamp, $this->nonce);

            $encrypted = simplexml_load_string($encryptedMessage, 'SimpleXMLElement', LIBXML_COMPACT + LIBXML_NOCDATA);
        } catch (Exception $e) {
            $encrypted = null;
        }

        $this->assertInstanceOf(SimpleXMLElement::class, $encrypted);
    }
}
