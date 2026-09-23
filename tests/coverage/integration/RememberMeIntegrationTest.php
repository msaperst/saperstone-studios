<?php

namespace coverage\integration;

use PHPUnit\Framework\TestCase;
use RememberMe;
use Sql;
use User;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class RememberMeIntegrationTest extends TestCase {
    private Sql $sql;
    private $sessionHash;
    private $sessionUser;
    private $rememberCookie;
    private $legacyHashCookie;
    private $cookiePreferences;

    public function setUp(): void {
        $this->sql = new Sql();
        $this->sql->executeStatement("DELETE FROM `remember_tokens` WHERE `user` = 4");
        $this->sessionHash = $_SESSION['hash'] ?? null;
        $this->sessionUser = $_SESSION['usr'] ?? null;
        $this->rememberCookie = $_COOKIE[RememberMe::COOKIE_NAME] ?? null;
        $this->legacyHashCookie = $_COOKIE['hash'] ?? null;
        $this->cookiePreferences = $_COOKIE['CookiePreferences'] ?? null;
        unset($_SESSION['hash'], $_SESSION['usr'], $_COOKIE[RememberMe::COOKIE_NAME], $_COOKIE['hash'], $_COOKIE['CookiePreferences']);
    }

    public function tearDown(): void {
        $this->sql->executeStatement("DELETE FROM `remember_tokens` WHERE `user` = 4");
        $this->restoreValue($_SESSION, 'hash', $this->sessionHash);
        $this->restoreValue($_SESSION, 'usr', $this->sessionUser);
        $this->restoreValue($_COOKIE, RememberMe::COOKIE_NAME, $this->rememberCookie);
        $this->restoreValue($_COOKIE, 'hash', $this->legacyHashCookie);
        $this->restoreValue($_COOKIE, 'CookiePreferences', $this->cookiePreferences);
        $this->sql->disconnect();
    }

    public function testRememberStoresOnlyValidatorHash(): void {
        $cookieValue = RememberMe::remember(4);
        [$selector, $validator] = explode(':', $cookieValue, 2);

        $row = $this->sql->getRow("SELECT * FROM `remember_tokens` WHERE `selector` = ?", [$selector]);
        $this->assertEquals(4, $row['user']);
        $this->assertNotEquals($validator, $row['token_hash']);
        $this->assertTrue(hash_equals($row['token_hash'], hash('sha256', $validator)));
    }

    public function testRememberedLoginRestoresSession(): void {
        $_COOKIE[RememberMe::COOKIE_NAME] = RememberMe::remember(4);

        $user = User::fromSystem();

        $this->assertTrue($user->isLoggedIn());
        $this->assertEquals(4, $user->getId());
        $this->assertEquals($user->getHash(), $_SESSION['hash']);
        $this->assertEquals('uploader', $_SESSION['usr']);
        $row = $this->sql->getRow("SELECT `last_used_at` FROM `remember_tokens` WHERE `user` = 4");
        $this->assertNotNull($row['last_used_at']);
    }

    public function testInvalidValidatorIsRejectedAndRevoked(): void {
        $cookieValue = RememberMe::remember(4);
        [$selector, $validator] = explode(':', $cookieValue, 2);
        $_COOKIE[RememberMe::COOKIE_NAME] = $selector . ':' . str_repeat('0', strlen($validator));

        $user = User::fromSystem();

        $this->assertFalse($user->isLoggedIn());
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `remember_tokens` WHERE `selector` = ?", [$selector]));
    }

    public function testMalformedCookieIsRejectedAndCleared(): void {
        $_COOKIE[RememberMe::COOKIE_NAME] = 'not-a-valid-remember-token';

        $user = User::fromSystem();

        $this->assertFalse($user->isLoggedIn());
        $this->assertArrayNotHasKey(RememberMe::COOKIE_NAME, $_COOKIE);
    }

    public function testExpiredTokenIsRejectedAndRevoked(): void {
        $cookieValue = RememberMe::remember(4);
        [$selector] = explode(':', $cookieValue, 2);
        $this->sql->executeStatement(
            "UPDATE `remember_tokens` SET `expires_at` = DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY) WHERE `selector` = ?",
            [$selector]
        );
        $_COOKIE[RememberMe::COOKIE_NAME] = $cookieValue;

        $user = User::fromSystem();

        $this->assertFalse($user->isLoggedIn());
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `remember_tokens` WHERE `selector` = ?", [$selector]));
    }

    public function testInvalidLegacyCookieIsClearedAndIgnored(): void {
        $_COOKIE['hash'] = 'not-a-valid-auth-token';

        $user = User::fromSystem();

        $this->assertFalse($user->isLoggedIn());
        $this->assertArrayNotHasKey('hash', $_COOKIE);
    }

    public function testUnknownLegacyCookieIsClearedAndIgnored(): void {
        $_COOKIE['hash'] = '1234567890abcdef1234567890abcdef';

        $user = User::fromSystem();

        $this->assertFalse($user->isLoggedIn());
        $this->assertArrayNotHasKey('hash', $_COOKIE);
    }

    public function testLegacyCookieIsMigratedOnce(): void {
        $_COOKIE['hash'] = 'c90788c0e409eac6a95f6c6360d8dbf7';

        $user = User::fromSystem();

        $this->assertTrue($user->isLoggedIn());
        $this->assertEquals(4, $user->getId());
        $this->assertArrayNotHasKey('hash', $_COOKIE);
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `remember_tokens` WHERE `user` = 4"));
    }

    public function testRejectedPreferenceCookiesRevokeRememberedLogin(): void {
        $cookieValue = RememberMe::remember(4);
        [$selector] = explode(':', $cookieValue, 2);
        $_COOKIE[RememberMe::COOKIE_NAME] = $cookieValue;
        $_COOKIE['CookiePreferences'] = '[]';

        $user = User::fromSystem();

        $this->assertFalse($user->isLoggedIn());
        $this->assertArrayNotHasKey(RememberMe::COOKIE_NAME, $_COOKIE);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `remember_tokens` WHERE `selector` = ?", [$selector]));
    }

    private function restoreValue(array &$target, string $key, $value): void {
        if ($value === null) {
            unset($target[$key]);
        } else {
            $target[$key] = $value;
        }
    }
}
