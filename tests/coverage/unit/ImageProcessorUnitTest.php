<?php

namespace coverage\unit;

use ImageProcessor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ImageProcessorUnitTest extends TestCase {

    public function testRejectsMissingImage(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image file does not exist');

        ImageProcessor::resize('/tmp/saperstone-image-that-does-not-exist.jpg', 300);
    }
}
