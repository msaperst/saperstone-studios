<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $blog = Blog::withId($_POST['post']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$sql->executeStatement("UPDATE `blog_details` SET `active` = '1' WHERE `id` = '{$blog->getId()}';");
$sql->disconnect();

$sm = new SocialMedia ();
$sm->generateRSS();
$sm->publishBlogToTwitter($blog);
exit ();