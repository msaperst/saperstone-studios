<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class BlogImage {
    private $blog;
    private $group;
    private $location;
    private $top;
    private $left;
    private $width;
    private $height;

    /**
     * BlogImage constructor.
     * @param Blog $blog
     * @param $group
     * @param $params
     * @throws Exception
     */
    function __construct(Blog $blog, $group, $params) {
        $this->blog = $blog;
        if (!isset ($group)) {
            throw new Exception("Blog content group is required");
        } elseif ($group == "") {
            throw new Exception("Blog content group can not be blank");
        }
        $this->group = (int)$group;
        if (!isset ($params['top'])) {
            throw new Exception("Blog image top location is required");
        } elseif ($params['top'] == "") {
            throw new Exception("Blog image top location can not be blank");
        }
        $this->top = (int)$params ['top'];
        if (!isset ($params['left'])) {
            throw new Exception("Blog image left location is required");
        } elseif ($params['left'] === "") {
            throw new Exception("Blog image left location can not be blank");
        }
        $this->left = (int)$params['left'];
        if (!isset ($params['width'])) {
            throw new Exception("Blog image width is required");
        } elseif ($params['width'] === "") {
            throw new Exception("Blog image width can not be blank");
        }
        $this->width = (int)$params['width'];
        if (!isset ($params['height'])) {
            throw new Exception("Blog image height is required");
        } elseif ($params['height'] === "") {
            throw new Exception("Blog image height can not be blank");
        }
        $this->height = (int)$params['height'];
        $sql = new Sql();
        if (!isset ($params['location'])) {
            throw new Exception("Blog image location is required");
        } elseif ($params['location'] === "") {
            throw new Exception("Blog image location can not be blank");
        }
        $this->location = $sql->escapeString($params['location']);
        $sql->disconnect();
    }

    function getLocation(): string {
        return $this->location;
    }

    function setBlog(Blog $blog) {
        $this->blog = $blog;
    }

    /**
     * @throws Exception
     */
    function create() {
        //check for access
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to create blog content");
        }
        // setup the image, and add it to the database
        $newLocation = $this->blog->getLocation() . DIRECTORY_SEPARATOR . basename($this->location);
        if (!is_dir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $this->blog->getLocation())) {
            $oldMask = umask(0);
            mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $this->blog->getLocation(), 0775, true);
            umask($oldMask);
        }
        rename(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $this->location, dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $newLocation);
        $fullLocation = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $newLocation;
        system("mogrify -resize {$this->width}x \"{$fullLocation}\"");
        system("mogrify -density 72 \"{$fullLocation}\"");
        $this->location = $newLocation;
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `blog_images` ( `blog`, `contentGroup`, `location`, `top`, `left`, `width`, `height` ) VALUES ({$this->blog->getId()}, {$this->group}, '{$this->location}', {$this->top}, {$this->left}, {$this->width}, {$this->height});");
        $sql->disconnect();
    }
}