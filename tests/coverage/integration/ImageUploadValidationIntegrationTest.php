<?php

namespace coverage\integration;

use BadRequestException;
use File;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ImageUploadValidationIntegrationTest extends TestCase {
    private array $temporaryFiles = [];

    protected function tearDown(): void {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }
    }

    private function temporaryImage(string $bytes): string {
        $path = tempnam(sys_get_temp_dir(), 'image-upload-validation-');
        self::assertNotFalse($path);
        file_put_contents($path, $bytes);
        $this->temporaryFiles[] = $path;
        return $path;
    }

    public function testAcceptsSupportedImageBytesWithMatchingExtensions(): void {
        $jpeg = dirname(__DIR__, 2) . '/resources/flower.jpeg';
        File::validateUploadedImage('photo.JPG', $jpeg);
        $this->addToAssertionCount(1);

        $png = $this->temporaryImage(base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/mXcAAAAASUVORK5CYII='
        ));
        File::validateUploadedImage('photo.png', $png);
        $this->addToAssertionCount(1);

        $gif = $this->temporaryImage(base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs='));
        File::validateUploadedImage('photo.gif', $gif);
        $this->addToAssertionCount(1);
    }

    public function testRejectsNonImageDespiteJpegExtension(): void {
        $path = $this->temporaryImage('<?php echo "executed"; ?>');
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Uploaded file is not a valid image');

        File::validateUploadedImage('malicious.jpg', $path);
    }

    public function testRejectsImageBytesDisguisedWithDifferentImageExtension(): void {
        $jpeg = dirname(__DIR__, 2) . '/resources/flower.jpeg';
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Uploaded file is not a valid image');

        File::validateUploadedImage('photo.png', $jpeg);
    }

    public function testRejectsUnsupportedExtensionBeforeDecoding(): void {
        $jpeg = dirname(__DIR__, 2) . '/resources/flower.jpeg';
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Uploaded file type is not supported');

        File::validateUploadedImage('photo.mvg', $jpeg);
    }

    public function testRejectsBatchBeforeCreatingDestinationOrMovingFiles(): void {
        $valid = dirname(__DIR__, 2) . '/resources/flower.jpeg';
        $invalid = $this->temporaryImage('not a photographic image');
        $destination = sys_get_temp_dir() . '/image-upload-batch-' . bin2hex(random_bytes(8));

        $file = new File([
            'error' => '0',
            'name' => ['valid.jpeg', 'invalid.jpeg'],
            'tmp_name' => [$valid, $invalid],
        ]);

        try {
            $file->upload($destination);
            self::fail('Expected the entire invalid batch to be rejected');
        } catch (BadRequestException $e) {
            self::assertSame('Uploaded file is not a valid image', $e->getMessage());
        } finally {
            self::assertDirectoryDoesNotExist($destination);
            self::assertFileExists($valid);
        }
    }
}
