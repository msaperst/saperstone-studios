<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Comment {

    private $raw;
    private $id;
    private $blog;
    private $user;
    private $name = "";
    private $date;
    private $ip;
    private $email = "";
    private $comment;
    private $delete = false;

    function __construct() {
    }

    /**
     * @param $id
     * @return Comment
     * @throws Exception
     */
    static function withId($id): Comment {
        if (!isset ($id)) {
            throw new Exception("Comment id is required");
        } elseif ($id == "") {
            throw new Exception("Comment id can not be blank");
        }
        $comment = new Comment();
        $id = (int)$id;
        $sql = new Sql();
        $comment->raw = $sql->getRow("SELECT * FROM blog_comments WHERE id = $id;");
        if (!isset($comment->raw) || !isset($comment->raw['id'])) {
            $sql->disconnect();
            throw new Exception("Comment id does not match any comments");
        }
        $comment->id = $comment->raw['id'];
        $comment->blog = $comment->raw['blog'];
        $comment->user = $comment->raw['user'];
        $comment->name = $comment->raw['name'];
        $comment->date = $comment->raw['date'];
        $comment->ip = $comment->raw['ip'];
        $comment->email = $comment->raw['email'];
        $comment->comment = $comment->raw['comment'];
        if ($comment->canUserGetData()) {
            $comment->delete = true;
            $comment->raw['delete'] = true;
        }
        $sql->disconnect();
        return $comment;
    }

    /**
     * @param $params
     * @return Comment
     * @throws Exception
     */
    static function withParams($params): Comment {
        $comment = new Comment();
        if (!isset($params['post'])) {
            throw new Exception("Blog id is required");
        }
        $comment->blog = Blog::withId($params['post']);
        $sql = new Sql ();
        // name is optional
        if (isset ($params ['name']) && $params ['name'] != "") {
            $comment->name = $sql->escapeString($params ['name']);
        }
        //email is optional
        if (isset ($params ['email']) && $params ['email'] != "") {
            $comment->email = $sql->escapeString($params ['email']);
        }
        //message is required
        if (!isset ($params['message'])) {
            $sql->disconnect();
            throw new Exception("Message is required");
        } elseif ($params['message'] == "") {
            $sql->disconnect();
            throw new Exception("Message can not be blank");
        }
        $comment->comment = $sql->escapeString($params ['message']);
        $sql->disconnect();
        // determine our user
        $user = User::fromSystem();
        if ($user->getId() != "") {
            $comment->user = "'" . $user->getId() . "'";
        } else {
            $comment->user = "NULL";
        }
        return $comment;
    }

    function getId() {
        return $this->id;
    }

    function getDate() {
        return $this->date;
    }

    function getDataArray() {
        return $this->raw;
    }

    /**
     * @return bool
     * @throws Exception
     */
    function canUserGetData(): bool {
        $user = User::fromSystem();
        // only admin users and the user who created the comment can get all data
        return ($this->user != NULL && $this->user == $user->getId()) || $user->isAdmin();
    }

    /**
     * @return int
     * @throws Exception
     */
    public function create(): int {
        $session = new Session();
        $session->initialize();
        $sql = new Sql();
        $commentId = $sql->executeStatement("INSERT INTO blog_comments ( blog, user, name, date, ip, email, comment ) VALUES ({$this->blog->getId()}, {$this->user}, '{$this->name}', CURRENT_TIMESTAMP, '{$session->getClientIP()}', '{$this->email}', '{$this->comment}' );");
        $this->id = $commentId;
        $sql->disconnect();
        $comment = static::withId($commentId);
        $this->raw = $comment->getDataArray();
        $this->date = $comment->getDate();
        return $commentId;
    }

    /**
     * @throws Exception
     */
    public function delete() {
        if (!$this->canUserGetData()) {
            throw new Exception("User not authorized to delete comment");
        }
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM blog_comments WHERE id={$this->id};");
        $sql->disconnect();
    }
}