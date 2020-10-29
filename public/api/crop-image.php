<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $image = $api->retrievePostString('image', 'Image');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
if (!file_exists($_POST['image'])) {
    echo "Image does not exist";
    exit ();
}

try {
    $maxWidth = $api->retrievePostInt('max-width', 'Image max-width');
    $top = $api->retrievePostInt('top', 'Image top');
    $bottom = $api->retrievePostInt('bottom', 'Image bottom');
} catch (Exception $e) {
    echo $e->getMessage();
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
    exit ();
}

// crop our image
system("mogrify -crop ${maxWidth}x${height}+0+${top} \"$image\"");

// rename the image
$filePath = dirname($image);
$fileName = basename($image);
rename($image, "$filePath/" . substr($fileName, 4));

exit ();