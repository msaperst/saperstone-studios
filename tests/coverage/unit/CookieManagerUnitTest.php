<?php

namespace coverage\unit;

use CookieManager;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CookieManagerUnitTest extends TestCase {

    protected function tearDown(): void {
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);
    }

    public function testServerCookieDefaultsAreSecureByPolicy(): void {
        $_SERVER['HTTPS'] = 'on';

        $options = CookieManager::options(1234);

        $this->assertSame(1234, $options['expires']);
        $this->assertSame('/', $options['path']);
        $this->assertTrue($options['secure']);
        $this->assertTrue($options['httponly']);
        $this->assertSame('Lax', $options['samesite']);
    }

    public function testBrowserReadableCookieCanDisableHttpOnly(): void {
        $options = CookieManager::options(0, false);

        $this->assertFalse($options['secure']);
        $this->assertFalse($options['httponly']);
    }

    public function testSessionCookieUsesForwardedHttpsAndLifetime(): void {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $options = CookieManager::sessionOptions(3600);

        $this->assertSame(3600, $options['lifetime']);
        $this->assertSame('/', $options['path']);
        $this->assertTrue($options['secure']);
        $this->assertTrue($options['httponly']);
        $this->assertSame('Lax', $options['samesite']);
    }

    public function testDeleteRemovesCookieFromCurrentRequest(): void {
        $_COOKIE['obsolete'] = 'value';

        CookieManager::delete('obsolete');

        $this->assertArrayNotHasKey('obsolete', $_COOKIE);
    }
}
