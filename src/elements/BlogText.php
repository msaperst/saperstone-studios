<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class BlogText {
    private $blog;
    private $group;
    private $text;

    function __construct(Blog $blog, $params) {
        $this->blog = $blog;
        if (!isset ($params['group'])) {
            throw new Exception("Blog content group is required");
        } elseif ($params['group'] == "") {
            throw new Exception("Blog content group can not be blank");
        }
        $this->group = (int)$params['group'];
        $sql = new Sql();
        if (!isset ($params['text'])) {
            $sql->disconnect();
            throw new Exception("Blog content text is required");
        } elseif ($params['text'] == "") {
            $sql->disconnect();
            throw new Exception("Blog content text can not be blank");
        }
        $this->text = $sql->escapeString($params['text']);
        $sql->disconnect();
    }

    function setBlog(Blog $blog) {
        $this->blog = $blog;
    }

    function getValues() {
        return "{$this->blog->getId()}, {$this->group}, '{$this->text}'";
    }

    function create() {
        //check for access
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to create blog content");
        }
        //add the text to the db
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `blog_texts` ( `blog`, `contentGroup`, `text` ) VALUES ({$this->blog->getId()}, {$this->group}, '{$this->text}');");
        $sql->disconnect();
    }
}