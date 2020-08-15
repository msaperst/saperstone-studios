<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$image = $api->retrievePostString('image', 'Image');
if (is_array($image)) {
    echo $image['error'];
    exit();
}
if (!file_exists($_POST['image'])) {
    echo "Image does not exist";
    $sql->disconnect();
    exit ();
}

$maxWidth = $api->retrievePostInt('max-width', 'Image max-width');
if (is_array($maxWidth)) {
    echo $maxWidth['error'];
    exit();
}

$top = $api->retrievePostInt('top', 'Image top');
if (is_array($top)) {
    echo $top['error'];
    exit();
}

$bottom = $api->retrievePostInt('bottom', 'Image bottom');
if (is_array($bottom)) {
    echo $bottom['error'];
    exit();
}

$height = $bottom - $top;

// ensure we have no 'red' leaking through
if ($top < 0) {
    $bottom = $bottom - $top;
    $top = 0;
}

// fix our image with it's width
system("mogrify -resize ${maxWidth}x \"$image\"");
system("mogrify -density 72 \"$image\"");

// verify that our image can fit in the specified crop
if (getimagesize($image) [1] < ($height - 1)) {
    echo "Cropped image is smaller than the required image";
    unlink($image);
    $sql->disconnect();
    exit ();
}

// crop our image
system("mogrify -crop ${maxWidth}x${height}+0+${top} \"$image\"");

// rename the image
$filePath = dirname($image);
$fileName = basename($image);
rename($image, "$filePath/" . substr($fileName, 4));

$sql->disconnect();
exit ();
?>