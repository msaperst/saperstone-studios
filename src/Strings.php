<?php

class Strings {

    /**
     * @param int $length
     * @return string
     */
    static function randomString($length = 10): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters [rand(0, $charactersLength - 1)];
        }
        return $randomString;
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