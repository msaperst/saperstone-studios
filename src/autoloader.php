<?php
spl_autoload_register(function ($class_name) {
    $CLASSES_DIR = __DIR__ . DIRECTORY_SEPARATOR;
    $ELEMENTS_DIR = $CLASSES_DIR . 'elements' . DIRECTORY_SEPARATOR;
    if (file_exists($CLASSES_DIR . $class_name . '.php')) {
        include $CLASSES_DIR . $class_name . '.php';
    } elseif (file_exists($ELEMENTS_DIR . $class_name . '.php')) {
        include $ELEMENTS_DIR . $class_name . '.php';
    }
});