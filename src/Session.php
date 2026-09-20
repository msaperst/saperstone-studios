<?php

class Session {

    private const CSRF_TOKEN_KEY = 'csrf_token';

    function __construct() {
    }

    function initialize() {
        if (session_status() != PHP_SESSION_ACTIVE && !headers_sent()) {
            // Starting the session
            session_name('session');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            // Start our session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        }
    }

    public function getCsrfToken(): string {
        $this->initialize();
        if (!isset($_SESSION[self::CSRF_TOKEN_KEY]) || !is_string($_SESSION[self::CSRF_TOKEN_KEY])) {
            $_SESSION[self::CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CSRF_TOKEN_KEY];
    }

    public function isCsrfTokenValid($token): bool {
        $this->initialize();
        return is_string($token)
            && isset($_SESSION[self::CSRF_TOKEN_KEY])
            && is_string($_SESSION[self::CSRF_TOKEN_KEY])
            && hash_equals($_SESSION[self::CSRF_TOKEN_KEY], $token);
    }

    function getClientIP() {
        $ip = '';
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        return $ip;
    }

    function getServer() {
        return $_SERVER['HTTP_X_FORWARDED_SERVER'] ?? $_SERVER ['SERVER_NAME'];
    }

    function getHost() {
        return $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER ['HTTP_HOST'];
    }

    function getBaseURL(): string {
        $pageURL = 'http';
        if (isset ($_SERVER ["SERVER_PORT"]) && $_SERVER ["SERVER_PORT"] == "9443") {
            $pageURL .= "s";
        }
        $pageURL .= "://" . $this->getServer();
        if ($_SERVER ["SERVER_PORT"] != "90" && $_SERVER ["SERVER_PORT"] != "9443") {
            $pageURL .= ":" . $_SERVER ["SERVER_PORT"];
        }
        return $pageURL;
    }

    function getCurrentPage(): string {
        return $this->getBaseURL() . $_SERVER ["REQUEST_URI"];
    }

    static function useAnalytics(): bool {
        if (!isset($_COOKIE['CookiePreferences'])) {
            return true;    // unless users say no, we're sending back to google analytics
        }
        $preferences = json_decode($_COOKIE['CookiePreferences']);
        $server = 'saperstonestudios.com';
        return (isset ($_SERVER ['HTTP_HOST']) && Strings::endsWith($_SERVER ['HTTP_HOST'], $server) && in_array("analytics", $preferences));
    }

    static function allowsPreferenceCookies(): bool {
        if (!isset($_COOKIE['CookiePreferences'])) {
            return true;
        }
        $preferences = json_decode($_COOKIE['CookiePreferences'], true);
        return is_array($preferences) && in_array('preferences', $preferences, true);
    }
}
