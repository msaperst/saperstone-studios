<?php

/**
 * Finds only pre-generated, bounded web derivatives in album storage.
 *
 * Callers must check album/image authorization before using the returned path.
 * This does not create derivatives or expose originals. The same resolver is
 * used by the preview endpoint and its candidate availability metadata.
 */
final class AlbumCoverFile {
    private const MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];

    /**
     * @return array{path: string, mime: string}|null
     */
    public static function resolve(
        string $albumLocation,
        string $imageLocation,
        bool $completedMigration,
        ?string $storageRoot = null
    ): ?array {
        if ($albumLocation === '') {
            return null;
        }
        $root = realpath($storageRoot ?? dirname(__DIR__) . '/public/albums');
        if ($root === false) {
            return null;
        }
        $folder = realpath($root . DIRECTORY_SEPARATOR . $albumLocation);
        if ($folder === false || !str_starts_with($folder, $root . DIRECTORY_SEPARATOR)) {
            return null;
        }
        $filename = basename(str_replace('\\', '/', $imageLocation));
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return null;
        }

        foreach ([400, 800, 1200] as $maxDimension) {
            $thumbs = self::containedDirectory($folder, $folder . '/thumbs/' . $maxDimension);
            if ($thumbs === null) {
                continue;
            }
            $derivative = self::imageFile($thumbs, $filename, $maxDimension);
            if ($derivative !== null) {
                return $derivative;
            }
        }

        if (!$completedMigration) {
            return null;
        }

        // A completed thumbnail migration backs up the original in full/
        // and leaves a processed 1600px derivative at the album root.
        // An original-only album (or a partially completed migration)
        // cannot satisfy both guards.
        $full = self::containedDirectory($folder, $folder . '/full');
        $original = $full === null ? null : self::containedFile($full, $filename);
        $webDerivative = self::containedFile($folder, $filename);
        if ($original === null || $webDerivative === null) {
            return null;
        }
        // A status flag alone is not proof of completed processing: legacy
        // migrations could have a backed-up but unchanged public original.
        if (filesize($original) === filesize($webDerivative)
            && hash_file('sha256', $original) === hash_file('sha256', $webDerivative)) {
            return null;
        }
        return self::imageFile($folder, $filename, 1600);
    }

    private static function containedDirectory(string $parent, string $candidate): ?string {
        $resolved = realpath($candidate);
        return $resolved !== false && is_dir($resolved)
            && str_starts_with($resolved, $parent . DIRECTORY_SEPARATOR)
            ? $resolved : null;
    }

    private static function containedFile(string $directory, string $filename): ?string {
        $resolved = realpath($directory . DIRECTORY_SEPARATOR . $filename);
        return $resolved !== false && is_file($resolved)
            && str_starts_with($resolved, $directory . DIRECTORY_SEPARATOR)
            ? $resolved : null;
    }

    /** @return array{path: string, mime: string}|null */
    private static function imageFile(string $directory, string $filename, int $limit): ?array {
        $path = self::containedFile($directory, $filename);
        if ($path === null || filesize($path) === 0) {
            return null;
        }
        $dimensions = @getimagesize($path);
        if ($dimensions === false || max($dimensions[0], $dimensions[1]) > $limit) {
            return null;
        }
        $mime = $dimensions['mime'] ?? '';
        if (!in_array($mime, self::MIME_TYPES, true)
            || (new finfo(FILEINFO_MIME_TYPE))->file($path) !== $mime) {
            return null;
        }
        return ['path' => $path, 'mime' => $mime];
    }
}
