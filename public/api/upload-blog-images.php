<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
Api::requireMethod('POST');
$api = new Api ();

$api->forceAdmin();

try {
    if (!isset($_FILES['myfile'])) {
        throw new ImageException('File(s) are required');
    }
    $file = new File($_FILES ["myfile"]);
    $files = $file->upload('../tmp/');
    $file->resize(1200, 0);
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo json_encode($e->getMessage());
    exit();
}
echo json_encode($files);
exit();
