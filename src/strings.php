<?php
class Strings {
    function randomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen ( $characters );
        $randomString = '';
        for($i = 0; $i < $length; $i ++) {
            $randomString .= $characters [rand ( 0, $charactersLength - 1 )];
        }
        return $randomString;
    }
    function textToHTML($text) {
        $text = str_replace ( "\n", "<br/>", $text );
        return str_replace ( "\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $text );
    }
    function commaSeparate($strings) {
        $last = array_slice ( $strings, - 1 );
        $first = join ( ', ', array_slice ( $strings, 0, - 1 ) );
        $both = array_filter ( array_merge ( array (
                $first 
        ), $last ), 'strlen' );
        return join ( ' and ', $both );
    }
}
?>