<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';

class ServicePagesHttpTest extends HttpTestBase {
    /**
     * @dataProvider servicePageProvider
     */
    public function testServicePage(string $path, string $heading): void {
        $this->assertPage($this->get($path), $heading);
    }

    public static function servicePageProvider(): array {
        return [
            'commercial about' => ['commercial/about.php', 'About Saperstone Studios'],
            'commercial background' => ['commercial/background.php', 'Background Options'],
            'commercial details' => ['commercial/details.php', 'Details'],
            'commercial expect' => ['commercial/expect.php', 'What to Expect'],
            'commercial faq' => ['commercial/faq.php', 'FAQ'],
            'commercial index' => ['commercial/index.php', 'Commercial'],
            'commercial pricing' => ['commercial/pricing.php', 'Pricing'],
            'commercial retouch' => ['commercial/retouch.php', 'Retouch'],
            'commercial services' => ['commercial/services.php', 'Services'],
            'commercial studio' => ['commercial/studio.php', 'Home Studio'],

            'portrait details' => ['portrait/details.php', 'Portrait Session Details'],
            'portrait faq' => ['portrait/faq.php', 'Portrait Session Frequently Asked Questions'],
            'portrait index' => ['portrait/index.php', 'Portraits'],
            'portrait manipulation' => ['portrait/manipulation.php', 'Other Image Edits'],
            'portrait newborn faq' => ['portrait/newborn-faq.php', 'Newborn Frequently Asked Questions'],
            'portrait retouch detail' => ['portrait/portrait-retouch.php', 'Portrait Retouch'],
            'portrait process' => ['portrait/process.php', 'The Process'],
            'portrait restoration' => ['portrait/restoration.php', 'Restoration'],
            'portrait retouch' => ['portrait/retouch.php', 'Retouch'],
            'portrait sessions' => ['portrait/sessions.php', 'Session Information'],
            'portrait studio' => ['portrait/studio.php', 'Home Studio'],
            'portrait what to wear' => ['portrait/what-to-wear.php', 'What to Wear'],

            'wedding details' => ['wedding/details.php', 'Wedding Session Details'],
            'wedding engagement' => ['wedding/engagement.php', 'Engagement Session'],
            'wedding experience' => ['wedding/experience.php', 'The Wedding Experience'],
            'wedding index' => ['wedding/index.php', 'Weddings'],
            'wedding night' => ['wedding/night.php', 'Night Photography'],
            'wedding photobooth' => ['wedding/photobooth.php', 'Photobooth'],
            'wedding process' => ['wedding/process.php', 'The Process'],
            'wedding retouch' => ['wedding/retouch.php', 'Retouch'],
            'wedding studio' => ['wedding/studio.php', 'Home Studio'],
        ];
    }
}
