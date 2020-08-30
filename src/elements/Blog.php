<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Blog {

    private $raw;
    private $id;
    private $title;
    private $safeTitle;
    private $date;
    private $preview;
    private $offset = 0;
    private $active = 0;
    private $twitter = 0;
    private $tags = array();
    private $comments = array();
    private $content = array();

    function __construct() {
    }

    static function withId($id) {
        if (!isset ($id)) {
            throw new Exception("Blog id is required");
        } elseif ($id == "") {
            throw new Exception("Blog id can not be blank");
        }
        $blog = new Blog();
        $id = (int)$id;
        $sql = new Sql();
        $blog->raw = $sql->getRow("SELECT * FROM blog_details WHERE id = $id;");
        if (!$blog->raw ['id']) {
            $sql->disconnect();
            throw new Exception("Blog id does not match any blog posts");
        }
        $blog->id = $blog->raw['id'];
        $blog->title = $blog->raw['title'];
        $blog->safeTitle = $blog->raw['safe_title'];
        $blog->date = date('F jS, Y', strtotime($blog->raw['date']));
        $blog->raw['date'] = $blog->date;   //putting the formatted date back in
        $blog->preview = $blog->raw['preview'];
        $blog->offset = $blog->raw['offset'];
        $blog->active = $blog->raw['active'];
        $blog->twitter = $blog->raw['twitter'];
        $blog->tags = $sql->getRows("SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = $id;");
        $blog->raw['tags'] = $blog->tags;      //putting the tags back in
        // content
        $contentData = $sql->getRows("SELECT * FROM `blog_images` WHERE blog = $id;");    //TODO - consider creating a class for this
        $contentData = array_merge($contentData, $sql->getRows("SELECT * FROM `blog_texts` WHERE blog = $id;"));
        foreach ($contentData as $data) {
            $blog->content[$data ['contentGroup']] [] = $data;
        }
        $blog->raw['content'] = $blog->content;    //putting the content back in
        // comments
        foreach ($sql->getRows("SELECT * FROM `blog_comments` WHERE blog = $id ORDER BY date desc;") as $comment) {
            $blog->comments[] = new Comment($comment['id']);
        }
        $blog->raw['comments'] = array();      //putting the comments back in
        foreach ($blog->comments as $comment) {
            $blog->raw['comments'][] = $comment->getDataArray();
        }
        $sql->disconnect();
        return $blog;
    }

    static function withParams($params) {
        return self::setVals(new Blog(), $params);
    }

    private static function setVals(Blog $blog, $params) {
        $sql = new Sql();
        //blog title
        if (!isset ($params['title'])) {
            $sql->disconnect();
            throw new Exception("Blog title is required");
        } elseif ($params['title'] == "") {
            $sql->disconnect();
            throw new Exception("Blog title can not be blank");
        }
        $blog->title = $sql->escapeString($params ['title']);
        //blog date
        if (!isset ($params['date'])) {
            $sql->disconnect();
            throw new Exception("Blog date is required");
        } elseif ($params['date'] == "") {
            $sql->disconnect();
            throw new Exception("Blog date can not be blank");
        } else {
            $date = $sql->escapeString($params ['date']);
            $format = 'Y-m-d';
            $d = DateTime::createFromFormat($format, $date);
            if (!($d && $d->format($format) === $date)) {
                $sql->disconnect();
                throw new Exception("Blog date is not the correct format");
            }
        }
        $blog->date = $sql->escapeString($params ['date']);
        //blog preview image
        if (!isset ($params ['preview'] ['img'])) {
            $sql->disconnect();
            throw new Exception("Blog preview image is required");
        } elseif ($params ['preview'] ['img'] == "") {
            $sql->disconnect();
            throw new Exception("Blog preview image can not be blank");
        }
        $blog->preview = $sql->escapeString($params ['preview'] ['img']);
        //blog preview offset
        if (isset ($params ['preview'] ['offset'])) {
            $blog->offset = (int)$params ['preview'] ['offset'];
        }
        //blog content
        if (!isset ($params ['content'])) {
            $sql->disconnect();
            throw new Exception("Blog content is required");
        } elseif (empty ($params ['content'])) {
            $sql->disconnect();
            throw new Exception("Blog content can not be empty");
        } else {
            foreach ($params ['content'] as $content) {
                if (!isset($content['type'])) {
                    $sql->disconnect();
                    throw new Exception("Blog content is not the correct format");
                }
                if ($content ['type'] == "text") {
                    $blog->content[] = new BlogText($blog, $content);
                } elseif ($content ['type'] == "images") {
                    foreach ($content ['imgs'] as $img) {
                        $blog->content[] = new BlogImage($blog, $content ['group'], $img);
                    }
                } else {
                    $sql->disconnect();
                    throw new Exception("Blog content is not the correct format");
                }
            }
        }
        //blog tags
        if (isset ($params ['tags'])) {
            foreach ($params ['tags'] as $tag) {
                $tag = (int)$tag;
                $blog->tags[] = $tag;
            }
        }
        return $blog;
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

    function getLocation() {
        return dirname($this->preview);
    }

    public function getTwitter() {
        return $this->twitter;
    }

    function getDataArray() {
        return $this->raw;
    }

    function create() {
        //check for access
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to create blog post");
        }

        // move and resize our preview image
        $storage_dir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . str_replace("-", "/", $this->date);
        if (!is_dir($storage_dir)) {
            mkdir($storage_dir, 0755, true);
        }
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $this->preview, $storage_dir . DIRECTORY_SEPARATOR . 'preview_image.jpg');
        $this->preview = $storage_dir . DIRECTORY_SEPARATOR . 'preview_image.jpg';
        system("mogrify -resize 360x \"{$this->preview}\"");
        system("mogrify -density 72 \"{$this->preview}\"");

        // write our initial blog information
        $sql = new Sql();
        $blogId = $sql->executeStatement("INSERT INTO `blog_details` ( `title`, `date`, `preview`, `offset` ) VALUES ('{$this->title}', '{$this->date}', '{$this->preview}', '{$this->offset}' );");
        $this->id = $blogId;
        // update our preview image with the blog post id
        rename("$storage_dir/preview_image.jpg", "$storage_dir/preview_image-$blogId.jpg");
        $this->preview = substr($storage_dir . DIRECTORY_SEPARATOR . "preview_image-$blogId.jpg", strlen(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR));
        $sql->executeStatement("UPDATE `blog_details` SET `preview` = '{$this->preview}' WHERE `id` = $blogId;");

        //create our content
        foreach ($this->content as $content) {
            $content->setBlog($this);
            $content->create();
        }

        //create our tags
        foreach ($this->tags as $tag) {
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ($blogId, $tag)");
        }
        $sql->disconnect();
        $blog = self::withId($blogId);
        $this->raw = $blog->getDataArray();
        return $blogId;
    }

    function delete() {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to delete blog post");
        }
        // delete our files
        $sql = new Sql();
        $images = $sql->getRows("SELECT * FROM blog_images WHERE blog='{$this->id}';");
        foreach ($images as $image) {
            unlink(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $image['location']);
        }
        //TODO - delete the folder if empty
        $sql->executeStatement("DELETE FROM blog_details WHERE id='{$this->id}';");
        $sql->executeStatement("DELETE FROM blog_images WHERE blog='{$this->id}';");
        $sql->executeStatement("DELETE FROM blog_tags WHERE blog='{$this->id}';");
        $sql->executeStatement("DELETE FROM blog_texts WHERE blog='{$this->id}';");
        $sql->executeStatement("DELETE FROM blog_comments WHERE blog='{$this->id}';");
        $sql->disconnect();
    }
}