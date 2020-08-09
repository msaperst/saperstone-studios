<?php

class Errors {

    public function throwError($errorCode) {
        header($_SERVER ["SERVER_PROTOCOL"] . " $errorCode Not Found");
        require dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . $errorCode . '.php';
        if (isset($sql) && $sql->isConnected()) {
            $sql->disconnect();
        }
        exit ();
    }

    public function throw401() {
        $this->throwError(401);
    }

    public function throw404() {
        $this->throwError(404);
    }
}

?>