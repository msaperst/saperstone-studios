<?php

class Session {

    function __construct() {
    }

    function initialize() {
        if (session_status() != PHP_SESSION_ACTIVE && !headers_sent()) {
            // Starting the session
            session_name('session');
            // Making the cookie live for 2 weeks
            session_set_cookie_params(2 * 7 * 24 * 60 * 60);
            // Start our session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        }
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
            return false;
        }
        $preferences = json_decode($_COOKIE['CookiePreferences']);
        $server = 'saperstonestudios.com';
        return (isset ($_SERVER ['HTTP_X_FORWARDED_HOST']) && Strings::endsWith($_SERVER ['HTTP_X_FORWARDED_HOST'], $server) && in_array("analytics", $preferences));
    }
}