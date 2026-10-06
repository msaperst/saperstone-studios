<?php

class ImageProcessor {

    private const NULL_DEVICE = '/dev/null';

    public static function resize(string $imagePath, int $width, ?int $height = null): void {
        $geometry = $height === null ? "{$width}x" : "{$width}x{$height}";
        self::runMogrify($imagePath, '-resize', $geometry);
    }

    public static function setDensity(string $imagePath, int $density): void {
        self::runMogrify($imagePath, '-density', (string)$density);
    }

    public static function crop(string $imagePath, int $width, int $height, int $top): void {
        self::runMogrify($imagePath, '-crop', "{$width}x{$height}+0+{$top}");
    }

    /**
     * Run ImageMagick without invoking a shell.
     *
     * The operation is selected internally and operation values are built from
     * typed numeric arguments. The image path is canonicalized before it is
     * passed as a single process argument.
     */
    private static function runMogrify(string $imagePath, string $operation, string $value): void {
        $canonicalPath = realpath($imagePath);
        if ($canonicalPath === false || !is_file($canonicalPath)) {
            throw new InvalidArgumentException('Image file does not exist');
        }

        $process = proc_open(
            ['mogrify', $operation, $value, $canonicalPath],
            [
                0 => ['file', self::NULL_DEVICE, 'r'],
                1 => ['file', self::NULL_DEVICE, 'w'],
                2 => ['file', self::NULL_DEVICE, 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Unable to start ImageMagick');
        }

        if (proc_close($process) !== 0) {
            throw new RuntimeException('ImageMagick failed to process image');
        }
    }
}
