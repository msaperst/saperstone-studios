<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$output_dir = "../tmp/";
if (!dir($output_dir)) {
    mkdir($output_dir);
}
if (isset ($_FILES ["myfile"])) {
    $ret = array();

    $error = $_FILES ["myfile"] ["error"];
    // You need to handle both cases
    // If Any browser does not support serializing of multiple files using FormData()
    // single file
    if (!is_array($_FILES ["myfile"] ["name"])) {
        $fileName = $_FILES ["myfile"] ["name"];
        move_uploaded_file($_FILES ["myfile"] ["tmp_name"], $output_dir . $fileName);
        $ret [] = $fileName;
        // Multiple files, file[]
    } else {
        $fileCount = count($_FILES ["myfile"] ["name"]);
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $_FILES ["myfile"] ["name"] [$i];
            move_uploaded_file($_FILES ["myfile"] ["tmp_name"] [$i], $output_dir . $fileName);
            $ret [] = $fileName;
        }
    }

    // verify our image is not too small
    foreach ($ret as $file) {
        $size = getimagesize($output_dir . $file);
        if ($size [0] < 1200) {
            echo json_encode("This image doesn't meet the minimum width requirements of 1200px");
            unlink($output_dir . $file);
            exit ();
        }
    }

    echo json_encode($ret);
}