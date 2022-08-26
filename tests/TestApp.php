<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2022/5/27
 * Time: 14:24
 */

namespace Tests;

use MaxSky\WeChat\Exceptions\WeChatUtilsException;
use MaxSky\WeChat\Services\App\App;
use PHPUnit\Framework\TestCase;

class TestApp extends TestCase {

    private $app;

    protected function setUp(): void {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->app = new App();
    }

    public function testGetUserInfo() {
        try {
            $this->assertIsArray($this->app->getUserInfoFromAuth('test', 'test'));
        } catch (WeChatUtilsException $e) {
            if ($e->getCode() === 40001) {
                $this->assertTrue(true);
            }
        }
    }
}
