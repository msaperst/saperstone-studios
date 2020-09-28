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
    private $directory = '';

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
        $blog->raw['content'] = array();
        foreach( $sql->getRows("SELECT * FROM `blog_images` WHERE blog = $id;") as $image) {
            $blog->raw['content'][$image['contentGroup']] [] = $image;
            $blog->content[] = new BlogImage($blog, $image['contentGroup'], $image);
        }
        foreach( $sql->getRows("SELECT * FROM `blog_texts` WHERE blog = $id;") as $text) {
            $blog->raw['content'][$text['contentGroup']] [] = $text;
            $text['group'] = $text['contentGroup'];  //KLUDGE for my ugly naming convention
            $blog->content[] = new BlogText($blog, $text);
        }
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
        $blog = self::setBasicValues(new Blog(), $params);
        return self::setContent($blog, $params);
    }

    private static function setBasicValues(Blog $blog, $params) {
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
        $blog->offset = 0;
        if (isset ($params ['preview'] ['offset'])) {
            $blog->offset = (int)$params ['preview'] ['offset'];
        }
        //directory to hold blog
        $blog->directory = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . str_replace("-", "/", $blog->date);
        if (!is_dir($blog->directory)) {
            mkdir($blog->directory, 0755, true);
        }
        //blog tags
        $blog->tags = array();
        if (isset ($params ['tags']) && !empty($params['tags'])) {
            foreach ($params ['tags'] as $tag) {
                $tag = (int)$tag;
                $blog->tags[] = $tag;
            }
        }
        $sql->disconnect();
        return $blog;
    }

    private static function setContent(Blog $blog, $params) {
        $sql = new Sql();
        $blog->content = array();
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
        $sql->disconnect();
        return $blog;
    }

    function getId() {
        return $this->id;
    }

    function getTitle() {
        return $this->title;
    }

    function getDate() {
        return $this->date;
    }

    function getPreview() {
        return $this->preview;
    }

    function getOffset() {
        return $this->offset;
    }

    function getTags() {
        return $this->tags;
    }

    function getLocation() {
        return dirname($this->preview);
    }

    public function getTwitter() {
        return $this->twitter;
    }

    public function getImages() {
        $images = array();
        foreach( $this->content as $content ) {
            /** @var BlogImage $content */
            if( $content instanceof BlogImage ) {
                $images[] = $content->getLocation();
            }
        }
        return $images;
    }

    function getDataArray() {
        return $this->raw;
    }

    function isActive() {
        return (bool) $this->active;
    }

    function create() {
        //check for access
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to create blog post");
        }

        // move and resize our preview image
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $this->preview, $this->directory . DIRECTORY_SEPARATOR . 'preview_image.jpg');
        $this->preview = $this->directory . DIRECTORY_SEPARATOR . 'preview_image.jpg';
        system("mogrify -resize 360x \"{$this->preview}\" > /dev/null 2>&1");
        system("mogrify -density 72 \"{$this->preview}\" > /dev/null 2>&1");

        // write our initial blog information
        $sql = new Sql();
        $blogId = $sql->executeStatement("INSERT INTO `blog_details` ( `title`, `date`, `preview`, `offset` ) VALUES ('{$this->title}', '{$this->date}', '{$this->preview}', '{$this->offset}' );");
        $this->id = $blogId;
        // update our preview image with the blog post id
        rename("{$this->directory}/preview_image.jpg", "{$this->directory}/preview_image-$blogId.jpg");
        $this->preview = substr($this->directory . DIRECTORY_SEPARATOR . "preview_image-$blogId.jpg", strlen(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR));
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

    function update($params) {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to update blog post");
        }
        // get our updated values
        self::setBasicValues($this, $params);
        // if we have a new image - process it
        if (isset($params['preview']) && isset($params['preview']['img']) && $params['preview']['img'] != '') {
            //setup our new image
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $this->preview, $this->directory . DIRECTORY_SEPARATOR . "preview_image-{$this->id}.jpg");
            $this->preview = $this->directory . DIRECTORY_SEPARATOR . "preview_image-{$this->id}.jpg";
            system("mogrify -resize 360x \"{$this->preview}\" > /dev/null 2>&1");
            system("mogrify -density 72 \"{$this->preview}\" > /dev/null 2>&1");
            $this->preview = substr($this->directory . DIRECTORY_SEPARATOR . "preview_image-{$this->id}.jpg", strlen(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR));
        }

        $sql = new Sql();
        // update our basic information
        $sql->executeStatement("UPDATE `blog_details` SET `title` = '{$this->title}', `date` = '{$this->date}', `offset` = '{$this->offset}' WHERE `id` = {$this->id};");

        // update our tags
        $sql->executeStatement("DELETE FROM blog_tags WHERE blog='{$this->id}';");
        foreach ($this->tags as $tag) {
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ({$this->id}, $tag)");
        }

        // update our status
        $originalStatus = $this->active;
        if (isset ($params ['active'])) {
            $this->active = (int)$params ['active'];
            if ($this->active != $originalStatus) {
                // if we have a change in status
                $sql->executeStatement("UPDATE `blog_details` SET `active` = '{$this->active}' WHERE `id` = {$this->id};");
                $socialMedia = new SocialMedia ();
                $socialMedia->generateRSS();
                if ($this->active == 1) {
                    // if we just published it
                    $this->twitter = $socialMedia->publishBlogToTwitter($this);
                } else {
                    // if we're archiving it
                    $this->twitter = $socialMedia->removeBlogFromTwitter($this);
                }
            }
        }

        if( isset($params['content']) && !empty($params['content'])) {
            self::setContent($this, $params);
            // update our content
            $sql->executeStatement("DELETE FROM blog_texts WHERE blog='{$this->id}';");
            $sql->executeStatement("DELETE FROM blog_images WHERE blog='{$this->id}';");
            foreach ($this->content as $content) {
                $content->setBlog($this);
                $content->create();
            }
        }
        $sql->disconnect();
        $this->raw = self::withId($this->id)->getDataArray();
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