<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2022/5/27
 * Time: 14:25
 */

namespace Tests;

use MaxSky\WeChat\Exceptions\WeChatUtilsGeneralException;
use MaxSky\WeChat\Services\OA\OfficialAccount;
use PHPUnit\Framework\TestCase;

class TestOfficialAccount extends TestCase {

    private $oa;

    protected function setUp(): void {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->oa = new OfficialAccount('test', 'test');

        $this->oa->setAccessToken('test');
    }

    public function testGetUserInfo() {
        try {
            $this->assertIsArray($this->oa->getUserInfoByAuthCode('test'));
        } catch (WeChatUtilsGeneralException $e) {
            if ($e->getCode() === 40013) {
                $this->assertTrue(true);
            }
        }
    }
}
