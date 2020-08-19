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
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            return $_SERVER["REMOTE_ADDR"];
        } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        return '';
    }

    function getServer() {
        return $_SERVER['HTTP_X_FORWARDED_SERVER'] ?? $_SERVER ['SERVER_NAME'];
    }

    function getHost() {
        return $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER ['HTTP_HOST'];
    }

    function getBaseURL() {
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

    function getCurrentPage() {
        return $this->getBaseURL() . $_SERVER ["REQUEST_URI"];
    }
}