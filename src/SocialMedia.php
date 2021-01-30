<?php

use Codebird\Codebird;

require_once 'autoloader.php';
require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'codebird-php-3.1.0' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'codebird.php');

class SocialMedia {
    private $session;
    private $cb;

    function __construct() {
        $this->session = new Session();
        Codebird::setConsumerKey(getenv('CONSUMER_KEY'), getenv('CONSUMER_SECRET'));
        $this->cb = Codebird::getInstance();
        $this->cb->setToken(getenv('TOKEN'), getenv('TOKEN_SECRET'));
    }

    function generateRSS() {
        $output = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog.rss';
        $url = $this->session->getBaseURL();
        $feed = fopen($output, 'w') or die ("Unable to open file!");

        fwrite($feed, "<?xml version='1.0'?>\n");
        fwrite($feed, "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>\n");
        fwrite($feed, "  <channel>\n");
        fwrite($feed, "    <atom:link href='$url/blog.rss' rel='self' type='application/rss+xml' />\n");
        fwrite($feed, "    <title>Saperstone Studios Photo Blog</title>\n");
        fwrite($feed, "    <description>Blogging our way through engagements, weddings, babies, then families</description>\n");
        fwrite($feed, "    <link>$url/blog/</link>\n");
        fwrite($feed, "    <language>en</language>\n");
        fwrite($feed, "\n");

        $sql = new Sql();
        $blogs = $sql->getRows('SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC;');
        $sql->disconnect();
        foreach ($blogs as $blog) {
            fwrite($feed, "    <item>\n");
            fwrite($feed, "      <title>" . htmlspecialchars($blog['title']) . "</title>\n");
            fwrite($feed, "      <link>$url/blog/post.php?p={$blog['id']}</link>\n");
            fwrite($feed, "      <guid>$url/blog/post.php?p={$blog['id']}</guid>\n");
            fwrite($feed, "    </item>\n");
            fwrite($feed, "\n");
        }

        fwrite($feed, "  </channel>\n");
        fwrite($feed, "</rss>\n");

        fclose($feed);
    }

    function publishBlogToTwitter(Blog $blog) {
        // get our post information
        $link = $this->session->getBaseURL() . "/blog/post.php?p={$blog->getId()}";
        // first, send the image to twitter
        $reply = $this->cb->media_upload(array(
            'media' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $blog->getPreview()
        ));
        // and collect their IDs
        if (property_exists($reply, 'media_id_string') && $reply->media_id_string != NULL && $reply->media_id_string > 0) {
            $mediaId = $reply->media_id_string;
            // next send our message and the image
            $reply = $this->cb->statuses_update(array(
                'status' => "{$blog->getTitle()}\n$link",
                'media_ids' => $mediaId
            ));
            $sql = new Sql();
            $sql->executeStatement("UPDATE `blog_details` SET `twitter` = '{$reply->id}' WHERE id={$blog->getId()};");
            $sql->disconnect();
            return $reply->id;
        }
        return 0;
    }

    function removeBlogFromTwitter(Blog $blog) {
        $this->cb->statuses_destroy_ID([
            'id' => $blog->getTwitter()
        ]);
        $sql = new Sql();
        $sql->executeStatement("UPDATE `blog_details` SET `twitter` = '0' WHERE id={$blog->getId()};");
        $sql->disconnect();
        return 0;
    }
}