<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class BlogText {
    /**
     * @var Blog
     */
    private $blog;
    /**
     * @var int
     */
    private $group;
    /**
     * @var string
     */
    private $text;

    /**
     * BlogText constructor.
     * @param Blog $blog
     * @param $params
     * @throws BadBlogTextException
     */
    function __construct(Blog $blog, $params) {
        $this->blog = $blog;
        if (!isset ($params['group'])) {
            throw new BadBlogTextException("Blog content group is required");
        } elseif ($params['group'] == "") {
            throw new BadBlogTextException("Blog content group can not be blank");
        }
        $this->group = (int)$params['group'];
        $sql = new Sql();
        if (!isset ($params['text'])) {
            $sql->disconnect();
            throw new BadBlogTextException("Blog content text is required");
        } elseif ($params['text'] == "") {
            $sql->disconnect();
            throw new BadBlogTextException("Blog content text can not be blank");
        }
        $this->text = $sql->escapeString($params['text']);
        $sql->disconnect();
    }

    /**
     * @param Blog $blog
     */
    function setBlog(Blog $blog) {
        $this->blog = $blog;
    }

    /**
     * @return int
     */
    function getGroup(): int {
        return $this->group;
    }

    /**
     * @return string
     */
    function getText(): string {
        return $this->text;
    }

    /**
     * @return string
     */
    function getValues() {
        return "{$this->blog->getId()}, {$this->group}, '{$this->text}'";
    }

    /**
     * @throws BadUserException
     * @throws BlogTextException
     * @throws SqlException
     */
    function create() {
        //check for access
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new BlogTextException("User not authorized to create blog content");
        }
        //add the text to the db
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `blog_texts` ( `blog`, `contentGroup`, `text` ) VALUES ({$this->blog->getId()}, {$this->group}, '{$this->text}');");
        $sql->disconnect();
    }
}