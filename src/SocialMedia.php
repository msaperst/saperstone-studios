<?php

require_once 'autoloader.php';

class SocialMedia {
    private $session;

    function __construct() {
        $this->session = new Session();
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
}