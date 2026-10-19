<?php

namespace coverage\unit;

use ImageProcessingException;
use ImageProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ImageProcessorUnitTest extends TestCase {

    public function testRejectsMissingImage(): void {
        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('Image file does not exist');

        ImageProcessor::resize('/tmp/saperstone-image-that-does-not-exist.jpg', 300);
    }

    #[DataProvider('invalidResizeProvider')]
    public function testRejectsInvalidResizeDimensions(int $width, ?int $height): void {
        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('Image resize dimensions must be positive');

        ImageProcessor::resize('/tmp/unused.jpg', $width, $height);
    }

    public static function invalidResizeProvider(): array {
        return [
            'zero width' => [0, null],
            'negative width' => [-1, null],
            'zero height' => [100, 0],
            'negative height' => [100, -1],
        ];
    }

    #[DataProvider('invalidDensityProvider')]
    public function testRejectsInvalidDensity(int $density): void {
        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('Image density must be positive');

        ImageProcessor::setDensity('/tmp/unused.jpg', $density);
    }

    public static function invalidDensityProvider(): array {
        return [
            'zero density' => [0],
            'negative density' => [-1],
        ];
    }

    #[DataProvider('invalidCropProvider')]
    public function testRejectsInvalidCropDimensions(int $width, int $height, int $top): void {
        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('Image crop dimensions are not valid');

        ImageProcessor::crop('/tmp/unused.jpg', $width, $height, $top);
    }

    public static function invalidCropProvider(): array {
        return [
            'zero width' => [0, 100, 0],
            'negative width' => [-1, 100, 0],
            'zero height' => [100, 0, 0],
            'negative height' => [100, -1, 0],
            'negative top' => [100, 100, -1],
        ];
    }
}
