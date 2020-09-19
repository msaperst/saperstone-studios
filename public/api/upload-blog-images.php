<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $file = new File($_FILES ["myfile"]);
    $files = $file->upload('../tmp/');
    $file->resize(1200, 0);
} catch (Exception $e) {
    echo json_encode($e->getMessage());
    exit();
}
echo json_encode($files);
exit();