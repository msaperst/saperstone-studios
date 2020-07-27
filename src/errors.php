<?php
function throwError($error) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " $error Not Found" );
    include dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/$error.php";
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
?>