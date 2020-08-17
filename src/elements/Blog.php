<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Blog {

    private $sql;
    private $raw;
    private $id;
    private $title;
    private $safeTitle;
    private $date;
    private $preview;
    private $offset;
    private $active;
    private $twitter;
    private $tags = array();
    private $comments = array();
    private $content = array();


    function __construct($id) {
        if (!isset ($id)) {
            throw new Exception("Blog id is required");
        } elseif ($id == "") {
            throw new Exception("Blog id can not be blank");
        }
        $this->sql = new Sql();
        $id = (int)$id;
        $this->raw = $this->sql->getRow("SELECT * FROM blog_details WHERE id = $id;");
        if (!$this->raw ['id']) {
            $this->sql->disconnect();
            throw new Exception("Blog id does not match any blog posts");
        }
        $this->id = $this->raw['id'];
        $this->title = $this->raw['title'];
        $this->safeTitle = $this->raw['safe_title'];
        $this->date = date('F jS, Y', strtotime($this->raw['date']));
        $this->raw['date'] = $this->date;   //putting the formatted date back in
        $this->preview = $this->raw['preview'];
        $this->offset = $this->raw['offset'];
        $this->active = $this->raw['active'];
        $this->twitter = $this->raw['twitter'];
        $this->tags = $this->sql->getRows("SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = $id;");
        $this->raw['tags'] = $this->tags;      //putting the tags back in
        // content
        $contentData = $this->sql->getRows("SELECT * FROM `blog_images` WHERE blog = $id;");    //TODO - consider creating a class for this
        $contentData = array_merge($contentData, $this->sql->getRows("SELECT * FROM `blog_texts` WHERE blog = $id;"));
        foreach ($contentData as $data) {
            $this->content[$data ['contentGroup']] [] = $data;
        }
        $this->raw['content'] = $this->content;    //putting the content back in
        // comments
        foreach( $this->sql->getRows("SELECT * FROM `blog_comments` WHERE blog = $id ORDER BY date desc;") as $comment) {
            $this->comments[] = new Comment($comment['id']);
        }
        $this->raw['comments'] = array();      //putting the comments back in
        foreach( $this->comments as $comment) {
            $this->raw['comments'][] = $comment->getDataArray();
        }
    }

    function getId() {
        return $this->id;
    }

    function getTitle() {
        return $this->title;
    }

    function getPreview() {
        return $this->preview;
    }

    public function getTwitter() {
        return $this->twitter;
    }

    function getDataArray() {
        return $this->raw;
    }

    function delete() {
        $user = new CurrentUser($this->sql);
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to delete blog post");
        }
        // delete our files
        $images = $this->sql->getRows("SELECT * FROM blog_images WHERE blog='{$this->id}';");
        foreach ($images as $image) {
            unlink(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $image['location']);
        }
        //TODO - delete the folder if empty
        $this->sql->executeStatement("DELETE FROM blog_details WHERE id='{$this->id}';");
        $this->sql->executeStatement("DELETE FROM blog_images WHERE blog='{$this->id}';");
        $this->sql->executeStatement("DELETE FROM blog_tags WHERE blog='{$this->id}';");
        $this->sql->executeStatement("DELETE FROM blog_texts WHERE blog='{$this->id}';");
        $this->sql->executeStatement("DELETE FROM blog_comments WHERE blog='{$this->id}';");
    }
}