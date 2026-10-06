<?php

class ImageProcessor {

    /**
     * @throws ImageProcessingException
     */
    public static function resize(string $imagePath, int $width, ?int $height = null): void {
        if ($width <= 0 || ($height !== null && $height <= 0)) {
            throw new ImageProcessingException('Image resize dimensions must be positive');
        }

        self::process($imagePath, static function (Imagick $image) use ($width, $height): void {
            foreach ($image as $frame) {
                if ($height === null) {
                    $frame->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
                } else {
                    $frame->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
                }
            }
        });
    }

    /**
     * @throws ImageProcessingException
     */
    public static function setDensity(string $imagePath, int $density): void {
        if ($density <= 0) {
            throw new ImageProcessingException('Image density must be positive');
        }

        self::process($imagePath, static function (Imagick $image) use ($density): void {
            foreach ($image as $frame) {
                $frame->setImageResolution($density, $density);
            }
        });
    }

    /**
     * @throws ImageProcessingException
     */
    public static function crop(string $imagePath, int $width, int $height, int $top): void {
        if ($width <= 0 || $height <= 0 || $top < 0) {
            throw new ImageProcessingException('Image crop dimensions are not valid');
        }

        self::process($imagePath, static function (Imagick $image) use ($width, $height, $top): void {
            foreach ($image as $frame) {
                $frame->cropImage($width, $height, 0, $top);
            }
        });
    }

    /**
     * Apply an image operation without invoking an external process.
     *
     * ImageMagick processing failures remain non-fatal to preserve the legacy
     * behavior of the previous mogrify calls, which ignored nonzero exit codes.
     *
     * @throws ImageProcessingException
     */
    private static function process(string $imagePath, callable $operation): void {
        $canonicalPath = realpath($imagePath);
        if ($canonicalPath === false || !is_file($canonicalPath)) {
            throw new ImageProcessingException('Image file does not exist');
        }

        try {
            $image = new Imagick($canonicalPath);
            try {
                $operation($image);
                $image->writeImages($canonicalPath, true);
            } finally {
                $image->clear();
            }
        } catch (ImagickException) {
            // Preserve legacy behavior: invalid/unprocessable images were non-fatal.
        }
    }
}
