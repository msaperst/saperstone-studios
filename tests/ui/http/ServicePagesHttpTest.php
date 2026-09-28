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
            'mitzvah details' => ['b-nai-mitzvah/details.php', "B'nai Mitzvah Details"],
            'mitzvah experience' => ['b-nai-mitzvah/experience.php', "The B'nai Mitzvah Experience"],
            'mitzvah index' => ['b-nai-mitzvah/index.php', "B'nai Mitzvahs"],
            'mitzvah photobooth' => ['b-nai-mitzvah/photobooth.php', 'Photobooth'],
            'mitzvah process' => ['b-nai-mitzvah/process.php', 'The Process'],
            'mitzvah products' => ['b-nai-mitzvah/products.php', 'Products & Investment'],
            'mitzvah retouch' => ['b-nai-mitzvah/retouch.php', 'Retouch'],
            'mitzvah sessions' => ['b-nai-mitzvah/sessions.php', 'Session Information'],

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
            'portrait products' => ['portrait/products.php', 'Products & Investment'],
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
            'wedding products' => ['wedding/products.php', 'Products & Investment'],
            'wedding retouch' => ['wedding/retouch.php', 'Retouch'],
            'wedding studio' => ['wedding/studio.php', 'Home Studio'],
        ];
    }
}
