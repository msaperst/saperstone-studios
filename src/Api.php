<?php

class Api {
    private $sql;
    private $user;

    function __construct($sql, $user) {
        $this->sql = $sql;
        $this->user = $user;
    }

    private function retrievePost($variable, $variableName, $type) {
        if (isset ($_POST [$variable]) && $_POST [$variable] != "") {
            switch ($type) {
                case "int":
                    return ( int )$_POST [$variable];
                case "float":
                    return floatval(str_replace('$', '', $_POST [$variable]));
                case "string":
                default:
                    return $this->sql->escapeString($_POST [$variable]);
            }
        } else {
            if (!isset ($_POST [$variable])) {
                $error = "$variableName is required";
            } else {
                $error = "$variableName can not be blank";
            }
            $this->sql->disconnect();
            return array('error' => $error);
        }
    }

    function retrieveValidatedPost($variable, $variableName, $validation) {
        if (isset ($_POST [$variable]) && filter_var($_POST [$variable], $validation)) {
            return $this->sql->escapeString($_POST [$variable]);
        } else {
            if (!isset ($_POST [$variable])) {
                $error = "$variableName is required";
            } elseif ($_POST [$variable] == "") {
                $error = "$variableName can not be blank";
            } else {
                $error = "$variableName is not valid";
            }
            $this->sql->disconnect();
            return array('error' => $error);
        }
    }

    function retrievePostDateTime($variable, $variableName, $format) {
        if (isset ($_POST [$variable]) && $_POST [$variable] != "") {
            $date = $this->sql->escapeString($_POST [$variable]);
            $d = DateTime::createFromFormat($format, $date);
            if (!($d && $d->format($format) === $date)) {
                $error = "$variableName is not the correct format";
            } else {
                return $date;
            }
        } else {
            if (!isset ($_POST [$variable])) {
                $error = "$variableName is required";
            } else {
                $error = "$variableName can not be blank";
            }
        }
        $this->sql->disconnect();
        return array('error' => $error);
    }

    function retrievePostInt($variable, $variableName) {
        return $this->retrievePost($variable, $variableName, 'int');
    }

    function retrievePostFloat($variable, $variableName) {
        return $this->retrievePost($variable, $variableName, 'float');
    }

    function retrievePostString($variable, $variableName) {
        return $this->retrievePost($variable, $variableName, 'string');
    }

    private function retrieveGet($variable, $variableName, $type) {
        if (isset ($_GET [$variable]) && $_GET [$variable] != "") {
            switch ($type) {
                case "int":
                    return ( int )$_GET [$variable];
                case "float":
                    return floatval(str_replace('$', '', $_GET [$variable]));
                case "string":
                default:
                    return $this->sql->escapeString($_GET [$variable]);
            }
        } else {
            if (!isset ($_GET [$variable])) {
                $error = "$variableName is required";
            } else {
                $error = "$variableName can not be blank";
            }
            $this->sql->disconnect();
            return array('error' => $error);
        }
    }

    function retrieveGetInt($variable, $variableName) {
        return $this->retrieveGet($variable, $variableName, 'int');
    }

    function retrieveGetFloat($variable, $variableName) {
        return $this->retrieveGet($variable, $variableName, 'float');
    }

    function retrieveGetString($variable, $variableName) {
        return $this->retrieveGet($variable, $variableName, 'string');
    }

    function forceLoggedIn() {
        if (!$this->user->isLoggedIn()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "You must be logged in to perform this action";
            $this->sql->disconnect();
            exit ();
        }
    }

    function forceAdmin() {
        if (!$this->user->isAdmin()) {
            header('HTTP/1.0 401 Unauthorized');
            if ($this->user->isLoggedIn()) {
                echo "You do not have appropriate rights to perform this action";
            }
            $this->sql->disconnect();
            exit ();
        }
    }
}

?>