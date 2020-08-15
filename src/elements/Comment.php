<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Comment {

    private $sql;
    private $raw;
    private $id;
    private $blog;
    private $user;
    private $name;
    private $date;
    private $ip;
    private $email;
    private $comment;
    private $delete = false;

    function __construct($id) {
        if (!isset ($id)) {
            throw new Exception("Comment id is required");
        } elseif ($id == "") {
            throw new Exception("Comment id can not be blank");
        }
        $this->sql = new Sql();
        $id = (int)$id;
        $this->raw = $this->sql->getRow("SELECT * FROM blog_comments WHERE id = $id;");
        if (!$this->raw ['id']) {
            $this->sql->disconnect();
            throw new Exception("Comment id does not match any comments");
        }
        $this->id = $this->raw['id'];
        $this->blog = $this->raw['blog'];
        $this->user = $this->raw['user'];
        $this->name = $this->raw['name'];
        $this->date = $this->raw['date'];
        $this->ip = $this->raw['ip'];
        $this->email = $this->raw['email'];
        $this->comment = $this->raw['comment'];
        if( $this->canUserGetData() ) {
            $this->delete = true;
            $this->raw['delete'] = true;
        }
    }

    function getId() {
        return $this->id;
    }

    function getDataArray() {
        return $this->raw;
    }

    function canUserGetData() {
        $user = new CurrentUser($this->sql);
        // only admin users and the user who created the comment can get all data
        return ($this->user != NULL && $this->user == $user->getId()) || $user->isAdmin();
    }

    public function delete() {
        if (!$this->canUserGetData()) {
            throw new Exception("User not authorized to delete comment");
        }
        $this->sql->executeStatement("DELETE FROM blog_comments WHERE id={$this->id};");
    }
}