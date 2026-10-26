<?php

namespace coverage\integration;

use ImageProcessor;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ImageProcessorIntegrationTest extends TestCase {

    private string $imagePath;
    private string $injectionMarker;

    protected function setUp(): void {
        $this->imagePath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'saperstone-image;touch saperstone-imageprocessor-injected;.jpeg';
        $this->injectionMarker = dirname(__DIR__, 3)
            . DIRECTORY_SEPARATOR
            . 'saperstone-imageprocessor-injected';

        @unlink($this->imagePath);
        @unlink($this->injectionMarker);

        copy(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'flower.jpeg',
            $this->imagePath
        );
    }

    protected function tearDown(): void {
        @unlink($this->imagePath);
        @unlink($this->injectionMarker);
    }

    public function testResizeProcessesImageWithoutInterpretingFilenameAsCommand(): void {
        ImageProcessor::resize($this->imagePath, 800, 1000);

        $size = getimagesize($this->imagePath);
        self::assertSame(800, $size[0]);
        self::assertSame(600, $size[1]);
        self::assertFileDoesNotExist($this->injectionMarker);
    }

    public function testCropUsesRequestedGeometry(): void {
        ImageProcessor::resize($this->imagePath, 300);
        ImageProcessor::crop($this->imagePath, 300, 100, 10);

        $size = getimagesize($this->imagePath);
        self::assertSame(300, $size[0]);
        self::assertSame(100, $size[1]);
    }

    public function testSetDensityKeepsImageReadable(): void {
        ImageProcessor::setDensity($this->imagePath, 72);

        self::assertNotFalse(getimagesize($this->imagePath));
    }

    public function testBzip2CompressedImageIsNotPassedToImagick(): void {
        // This is a complete GIF compressed with bzip2, disguised by a .jpeg
        // filename. ImageMagick transparently opens bzip2 blobs before coder
        // policy checks unless we reject the BZh magic at the call site.
        $payload = base64_decode(
            'QlpoOTFBWSZTWUsvjV4AABDdQOTAAAQAyAGgIAAgAAAAoAAxTAAA1PIQB6lFkCGUaQKVmRmZgZ32H5uR8XckU4UJBLL41eA='
        );
        file_put_contents($this->imagePath, $payload);

        ImageProcessor::resize($this->imagePath, 2);
        self::assertSame($payload, file_get_contents($this->imagePath));
    }

    public function testInvalidImagePreservesLegacyNonFatalBehavior(): void {
        file_put_contents($this->imagePath, 'not an image');

        ImageProcessor::setDensity($this->imagePath, 72);

        self::assertFileExists($this->imagePath);
    }
}
