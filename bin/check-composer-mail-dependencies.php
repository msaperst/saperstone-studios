<?php
/**
 * Verify that the mail classes used by Email.php come from Composer, not
 * a system-wide PEAR installation that bypasses composer.lock.
 */
$vendorDirectory = realpath(dirname(__DIR__) . '/vendor');
if ($vendorDirectory === false) {
    fwrite(STDERR, "Composer vendor directory is missing.\n");
    exit(1);
}

require_once $vendorDirectory . '/autoload.php';

foreach (['PEAR', 'Mail', 'Mail_mime', 'Net_SMTP', 'Auth_SASL'] as $class) {
    if (!class_exists($class)) {
        fwrite(STDERR, "Missing Composer mail dependency: $class\n");
        exit(1);
    }

    $filename = (new ReflectionClass($class))->getFileName();
    $realFilename = $filename === false ? false : realpath($filename);
    if ($realFilename === false || !str_starts_with($realFilename, $vendorDirectory . DIRECTORY_SEPARATOR)) {
        fwrite(STDERR, "Mail dependency $class is not loaded from Composer vendor/.\n");
        exit(1);
    }
}

echo "Mail dependencies are loaded from Composer vendor/.\n";
