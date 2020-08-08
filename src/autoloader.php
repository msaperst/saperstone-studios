<?php
spl_autoload_register(function ($class_name) {
    $CLASSES_DIR = __DIR__ . DIRECTORY_SEPARATOR;
    $file = $CLASSES_DIR . $class_name . '.php';
    if (file_exists($file)) {
        include $file;
    }
});