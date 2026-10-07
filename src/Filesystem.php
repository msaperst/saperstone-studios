<?php

final class Filesystem {

    // Web/storage directories need owner/group write access for application and maintenance processes.
    // World access is read/execute only; Apache/application access controls are enforced separately.
    private const PUBLIC_DIRECTORY_MODE = 0775;

    public static function createPublicDirectory(string $path, bool $recursive = false): void {
        if (is_dir($path)) {
            return;
        }

        $oldMask = umask(0);
        try {
            if (!@mkdir($path, self::PUBLIC_DIRECTORY_MODE, $recursive) && !is_dir($path)) {
                throw new FilesystemException('Unable to create directory');
            }
        } finally {
            umask($oldMask);
        }
    }
}
