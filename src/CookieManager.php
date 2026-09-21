<?php

class CookieManager {

    public const SAME_SITE = 'Lax';

    public static function set(string $name, string $value, int $expires = 0, bool $httpOnly = true): void {
        if (!headers_sent()) {
            setcookie($name, $value, self::options($expires, $httpOnly));
        }
    }

    public static function delete(string $name, bool $httpOnly = true): void {
        self::set($name, '', time() - 3600, $httpOnly);
        unset($_COOKIE[$name]);
    }

    public static function options(int $expires = 0, bool $httpOnly = true): array {
        return [ // NOSONAR Secure is false only for local HTTP development and CI.
            'expires' => $expires,
            'path' => '/',
            'secure' => Session::isSecureRequest(),
            'httponly' => $httpOnly,
            'samesite' => self::SAME_SITE
        ];
    }

    public static function sessionOptions(int $lifetime = 0): array {
        return [ // NOSONAR Secure is false only for local HTTP development and CI.
            'lifetime' => $lifetime,
            'path' => '/',
            'secure' => Session::isSecureRequest(),
            'httponly' => true,
            'samesite' => self::SAME_SITE
        ];
    }
}
