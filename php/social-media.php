<?php
class SocialMedia {
    var $sql;
    var $db;
    function __construct() {
        include_once "sql.php";
        $sql = new Sql ();
        $sql->connect ();
        $this->db = $sql->db;
    }
    function generateRSS() {
        $output = "../blog.rss";
        $url = $this->baseURL ();
        $feed = fopen ( $output, 'w' ) || die ( "Unable to open file!" );
        
        fwrite ( $feed, "<?xml version=\"1.0\"?>\n" );
        fwrite ( $feed, "<rss version=\"2.0\">\n" );
        fwrite ( $feed, "  <channel>\n" );
        fwrite ( $feed, "    <title>Saperstone Studios Photo Blog</title>\n" );
        fwrite ( $feed, "    <description>Blogging our way through engagements, weddings, babies, then families</description>\n" );
        fwrite ( $feed, "    <link>$url/blog/</link>\n" );
        fwrite ( $feed, "    <language>en</language>\n" );
        fwrite ( $feed, "\n" );
        
        $sql = "SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC;";
        $result = mysqli_query ( $this->db, $sql );
        while ( $r = mysqli_fetch_assoc ( $result ) ) {
            fwrite ( $feed, "    <item>\n" );
            fwrite ( $feed, "      <title>" . $r ['title'] . "</title>\n" );
            fwrite ( $feed, "      <link>$url/blog/post.php?p=" . $r ['id'] . "</link>\n" );
            fwrite ( $feed, "    </item>\n" );
            fwrite ( $feed, "\n" );
        }
        
        fwrite ( $feed, "  </channel>\n" );
        fwrite ( $feed, "</rss>\n" );
        
        fclose ( $feed );
    }
    function publishBlogToTwitter($id) {
        // get our post information
        $details = mysqli_fetch_assoc ( mysqli_query ( $this->db, "SELECT * FROM `blog_details` WHERE id=$id;" ) );
        
        $title = $details ['title'];
        $image = $details ['preview'];
        $link = $this->baseURL () . "/blog/post.php?p=$id";
        
        // require codebird
        require_once ('../plugins/codebird-php-3.1.0/src/codebird.php');
        
        \Codebird\Codebird::setConsumerKey ( "8DQvx2b18QkSCARsUJs1KDnvp", "fiFRlU4uZfLkyu24yqEB1jcUiprETciiUI4VaSAUlKjkie3GlA" );
        $cb = \Codebird\Codebird::getInstance ();
        $cb->setToken ( "291879421-eRCel3CGfQWUgtxnYUdolozLiHGbmqJYVJAzUmVB", "1WjoGMCkoI47OHPEBTVEwXrk9V8N6kPa8pczMInka0fvm" );
        
        /*
         * TODO - actually enable the below
         * // first, send the image to twitter
         * $reply = $cb->media_upload ( array (
         * 'media' => $image
         * ) );
         * // and collect their IDs
         * $media_id = $reply->media_id_string;
         *
         * // next send our message and the image
         * $reply = $cb->statuses_update ( array (
         * 'status' => "$title\n$link",
         * 'media_ids' => $media_id
         * ) );
         */
    }
    function baseURL() {
        $pageURL = 'http';
        if (isset ( $_SERVER ["HTTPS"] ) && strtolower ( $_SERVER ["HTTPS"] ) == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER ["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"];
        } else {
            $pageURL .= $_SERVER ["SERVER_NAME"];
        }
        return $pageURL;
    }
}
?>