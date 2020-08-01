<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";

function throwError($errorCode) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " $errorCode Not Found" );
    require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/$errorCode.php";
    if( isset( $sql ) && $sql->isConnected() ) {
        $sql->disconnect();
    }
    exit ();
}

function throw401() {
    throwError(401);
}

function throw404() {
    throwError(404);
}

function getPostInt() {
}
?>