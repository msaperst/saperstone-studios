<?php
class SocialMedia {
    var $sql;
    var $db;
    function SocialMedia() {
        include_once "sql.php";
        $sql = new sql ();
        $sql->connect ();
        $this->db = $sql->db;
    }
    function publishBlogToTwitter($id) {
        // get our post information
        $details = mysqli_fetch_assoc ( mysqli_query ( $this->db, "SELECT * FROM `blog_details` WHERE id=$id;" ) );
        
        $title = $details ['title'];
        $image = $details ['preview'];
        $link = "https://saperstonestudios.com/blog/post.php?p=$id";
        
        // require codebird
        require_once ('../plugins/codebird-php-3.1.0/src/codebird.php');
        
        \Codebird\Codebird::setConsumerKey ( "8DQvx2b18QkSCARsUJs1KDnvp", "fiFRlU4uZfLkyu24yqEB1jcUiprETciiUI4VaSAUlKjkie3GlA" );
        $cb = \Codebird\Codebird::getInstance ();
        $cb->setToken ( "291879421-eRCel3CGfQWUgtxnYUdolozLiHGbmqJYVJAzUmVB", "1WjoGMCkoI47OHPEBTVEwXrk9V8N6kPa8pczMInka0fvm" );

        /* 
         * TODO - actually enable the below
        // first, send the image to twitter
        $reply = $cb->media_upload ( array (
                'media' => $image 
        ) );
        // and collect their IDs
        $media_id = $reply->media_id_string;
        
        // next send our message and the image
        $reply = $cb->statuses_update ( array (
                'status' => "$title\n$link",
                'media_ids' => $media_id 
        ) );
        */
    }
}
?>