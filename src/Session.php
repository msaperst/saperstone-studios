<?php

class Session {

    private const CSRF_TOKEN_KEY = 'csrf_token';

    function __construct() {
    }

    function initialize() {
        if (session_status() != PHP_SESSION_ACTIVE && !headers_sent()) {
            // Starting the session
            session_name('session');
            // The Secure value is false only for local HTTP development/CI.
            // Production HTTPS, including forwarded HTTPS, always receives true.
            session_set_cookie_params(CookieManager::sessionOptions()); // NOSONAR Secure is enabled for HTTPS requests.
            // Start our session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        }

        if (isset($_COOKIE['searched']) && !self::allowsPreferenceCookies()) {
            CookieManager::delete('searched');
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
        return $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
    }

    function getBaseURL(): string {
        $scheme = self::isSecureRequest() ? 'https' : 'http';
        $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';

        if ($forwardedHost !== '') {
            $host = trim(explode(',', $forwardedHost)[0]);
        } else if ($httpHost !== '') {
            $host = $httpHost;
        } else {
            $host = $this->getServer();
            $port = (string)($_SERVER['SERVER_PORT'] ?? '');
            $defaultPort = $scheme === 'https' ? '443' : '80';
            if ($port !== '' && $port !== $defaultPort) {
                $host .= ':' . $port;
            }
        }

        $defaultPortSuffix = $scheme === 'https' ? ':443' : ':80';
        if (substr($host, -strlen($defaultPortSuffix)) === $defaultPortSuffix) {
            $host = substr($host, 0, -strlen($defaultPortSuffix));
        }

        return $scheme . '://' . $host;
    }

    function getCurrentPage(): string {
        return $this->getBaseURL() . $_SERVER ["REQUEST_URI"];
    }

    static function useAnalytics(): bool {
        if (!self::allowsAnalyticsCookies()) {
            return false;
        }

        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
        $server = 'saperstonestudios.com';
        return $host === $server || Strings::endsWith($host, '.' . $server);
    }

    public static function allowsAnalyticsCookies(): bool {
        if (!isset($_COOKIE['CookiePreferences'])) {
            return false;
        }
        $preferences = json_decode($_COOKIE['CookiePreferences'], true);
        return is_array($preferences) && in_array('analytics', $preferences, true);
    }

    public static function allowsPreferenceCookies(): bool {
        if (!isset($_COOKIE['CookiePreferences'])) {
            return true;
        }
        $preferences = json_decode($_COOKIE['CookiePreferences'], true);
        return is_array($preferences) && in_array('preferences', $preferences, true);
    }

    public static function isSecureRequest(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }
}
