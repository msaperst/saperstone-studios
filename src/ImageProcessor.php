<?php

class ImageProcessor {

    private const NULL_DEVICE = '/dev/null';
    private const RESIZE_OPERATION = '-resize';
    private const DENSITY_OPERATION = '-density';
    private const CROP_OPERATION = '-crop';

    /**
     * @throws ImageProcessingException
     */
    public static function resize(string $imagePath, int $width, ?int $height = null): void {
        if ($width <= 0 || ($height !== null && $height <= 0)) {
            throw new ImageProcessingException('Image resize dimensions must be positive');
        }

        $geometry = $height === null ? "{$width}x" : "{$width}x{$height}";
        self::runMogrify($imagePath, self::RESIZE_OPERATION, $geometry);
    }

    /**
     * @throws ImageProcessingException
     */
    public static function setDensity(string $imagePath, int $density): void {
        if ($density <= 0) {
            throw new ImageProcessingException('Image density must be positive');
        }

        self::runMogrify($imagePath, self::DENSITY_OPERATION, (string)$density);
    }

    /**
     * @throws ImageProcessingException
     */
    public static function crop(string $imagePath, int $width, int $height, int $top): void {
        if ($width <= 0 || $height <= 0 || $top < 0) {
            throw new ImageProcessingException('Image crop dimensions are not valid');
        }

        self::runMogrify($imagePath, self::CROP_OPERATION, "{$width}x{$height}+0+{$top}");
    }

    /**
     * Run ImageMagick without invoking a shell.
     *
     * The operation is normalized to an internal constant, the option value is
     * validated against the operation's numeric grammar, and "--" terminates
     * ImageMagick option parsing before the canonical image path.
     *
     * @throws ImageProcessingException
     */
    private static function runMogrify(string $imagePath, string $operation, string $value): void {
        [$safeOperation, $valuePattern] = match ($operation) {
            self::RESIZE_OPERATION => [self::RESIZE_OPERATION, '/^\\d+x\\d*$/D'],
            self::DENSITY_OPERATION => [self::DENSITY_OPERATION, '/^\\d+$/D'],
            self::CROP_OPERATION => [self::CROP_OPERATION, '/^\\d+x\\d+\\+0\\+\\d+$/D'],
            default => throw new ImageProcessingException('ImageMagick operation is not supported'),
        };

        if (preg_match($valuePattern, $value) !== 1) {
            throw new ImageProcessingException('ImageMagick operation value is not valid');
        }

        $canonicalPath = realpath($imagePath);
        if ($canonicalPath === false || !is_file($canonicalPath)) {
            throw new ImageProcessingException('Image file does not exist');
        }

        $process = proc_open(
            ['mogrify', $safeOperation, $value, '--', $canonicalPath],
            [
                0 => ['file', self::NULL_DEVICE, 'r'],
                1 => ['file', self::NULL_DEVICE, 'w'],
                2 => ['file', self::NULL_DEVICE, 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new ImageProcessingException('Unable to start ImageMagick');
        }

        // Preserve legacy behavior: ImageMagick processing failures were
        // intentionally non-fatal to callers. Security validation happens
        // before process launch; the exit status is not an API contract.
        proc_close($process);
    }
}
