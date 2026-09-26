<?php

class Strings {

    /**
     * @param int $length
     * @return string
     */
    static function randomString(int $length = 10): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters [rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Add a deterministic content version to a first-party asset URL.
     *
     * External URLs are returned unchanged. Missing local files are also returned
     * unchanged so a missing asset does not generate a PHP warning.
     */
    static function assetUrl(string $url, ?string $documentRoot = null): string {
        if (preg_match('#^(?:https?:)?//#i', $url) || Strings::startsWith($url, 'data:')) {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false || !isset($parts['path'])) {
            return $url;
        }

        $root = $documentRoot ?? ($_SERVER['DOCUMENT_ROOT'] ?? '');
        if ($root === '') {
            return $url;
        }

        $file = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . ltrim($parts['path'], '/\\');
        if (!is_file($file)) {
            return $url;
        }

        $separator = isset($parts['query']) ? '&' : '?';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        $withoutFragment = $fragment === '' ? $url : substr($url, 0, -strlen($fragment));

        $version = substr(hash_file('sha256', $file), 0, 12);
        return $withoutFragment . $separator . 'v=' . $version . $fragment;
    }

    /**
     * @param $text
     * @return string
     */
    static function textToHTML($text): string {
        $text = str_replace("\n", "<br/>", $text);
        return str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $text);
    }

    /**
     * Escape a value for use inside a quoted HTML attribute.
     */
    static function escapeHtmlAttribute($text): string {
        return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param $strings
     * @return string
     */
    static function commaSeparate($strings): string {
        $last = array_slice($strings, -1);
        $first = join(', ', array_slice($strings, 0, -1));
        $both = array_filter(array_merge(array(
            $first
        ), $last), 'strlen');
        return join(' and ', $both);
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    static function startsWith($haystack, $needle): bool {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    static function endsWith($haystack, $needle): bool {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    /**
     * @param $date
     * @param string $format
     * @return bool
     */
    static function isDateFormatted($date, $format = 'Y-m-d'): bool {
        $d = DateTime::createFromFormat($format, $date);
        return ($d && $d->format($format) === $date);
    }
}
