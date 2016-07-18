<?php
class strings {
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
        $text = str_replace("\n","<br/>",$text);
        $text = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$text);
        return $text;
    }
}
?>