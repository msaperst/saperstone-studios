<?php

//TODO - redo this by throwing errors?

class Api {
    private $user;

    function __construct() {
        $this->user = User::fromSystem();
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
                    $sql = new Sql();
                    $escaped = $sql->escapeString($_POST [$variable]);
                    $sql->disconnect();
                    return $escaped;
            }
        } else {
            if (!isset ($_POST [$variable])) {
                $error = "$variableName is required";
            } else {
                $error = "$variableName can not be blank";
            }
            return array('error' => $error);
        }
    }

    function retrieveValidatedPost($variable, $variableName, $validation) {
        if (isset ($_POST [$variable]) && filter_var($_POST [$variable], $validation)) {
            $sql = new Sql();
            $escaped = $sql->escapeString($_POST [$variable]);
            $sql->disconnect();
            return $escaped;
        } else {
            if (!isset ($_POST [$variable])) {
                $error = "$variableName is required";
            } elseif ($_POST [$variable] == "") {
                $error = "$variableName can not be blank";
            } else {
                $error = "$variableName is not valid";
            }
            return array('error' => $error);
        }
    }

    function retrievePostDateTime($variable, $variableName, $format) {
        if (isset ($_POST [$variable]) && $_POST [$variable] != "") {
            $sql = new Sql();
            $date = $sql->escapeString($_POST [$variable]);
            $sql->disconnect();
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
                $sql = new Sql();
                $escaped = $sql->escapeString($_GET [$variable]);
                $sql->disconnect();
                return $escaped;
            }
        } else {
            if (!isset ($_GET [$variable])) {
                $error = "$variableName is required";
            } else {
                $error = "$variableName can not be blank";
            }
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
            exit ();
        }
    }

    function forceAdmin() {
        if (!$this->user->isAdmin()) {
            header('HTTP/1.0 401 Unauthorized');
            if ($this->user->isLoggedIn()) {
                echo "You do not have appropriate rights to perform this action";
            }
            exit ();
        }
    }
}