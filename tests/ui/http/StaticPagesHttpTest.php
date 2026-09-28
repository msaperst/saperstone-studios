<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';

class StaticPagesHttpTest extends HttpTestBase {
    public function testMainPage(): void {
        $this->assertPage($this->get(), 'Welcome to Saperstone Studios', 'h2');
    }

    public function testAboutPage(): void {
        $this->assertPage($this->get('about.php'), 'About Saperstone Studios');
    }

    public function testLeighAnnPage(): void {
        $this->assertPage($this->get('leighAnn.php'), 'Meet Leigh Ann');
    }

    public function testContactPage(): void {
        $response = $this->get('contact.php');
        $this->assertPage($response, 'Contact');
        self::assertSame('', $this->attribute($response, "//*[@id='name']", 'value'));
        self::assertSame('', $this->attribute($response, "//*[@id='email']", 'value'));
    }

    public function testContactPageLoggedIn(): void {
        $this->adminLogin();
        $response = $this->get('contact.php');
        self::assertSame('Max Saperstone', $this->attribute($response, "//*[@id='name']", 'value'));
        self::assertSame('msaperst@gmail.com', $this->attribute($response, "//*[@id='email']", 'value'));
    }

    public function testRetouchPageUsesPersistentImages(): void {
        $response = $this->get('retouch.php');
        self::assertSame('Retouch', $this->text($response, '//h1'));
        $images = $this->xpath($response)->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' img-portfolio ')]//img");
        self::assertNotFalse($images);
        $expected = [
            '/img/main/b-nai-retouch.jpg',
            '/img/main/portrait-retouch.jpg',
            '/img/main/wedding-retouch.jpg',
            '/img/main/commercial-retouch.jpg',
        ];
        self::assertCount(count($expected), $images);
        foreach ($expected as $index => $path) {
            self::assertStringEndsWith($path, $images->item($index)->attributes?->getNamedItem('src')?->nodeValue ?? '');
        }
    }

    public function testPrivacyPolicyPage(): void {
        $response = $this->get('Privacy-Policy.php');
        $this->assertPage($response, 'Privacy Policy');
        $body = $this->text($response, '//body');
        self::assertStringContainsString('Google Analytics and Meta Pixel', $body);
        self::assertStringContainsString('native sharing', $body);
        self::assertStringContainsString('Effective September 24, 2026', $body);
        self::assertStringNotContainsString('AddToAny', $body);
    }

    public function testTermsOfUsePage(): void {
        $response = $this->get('Terms-of-Use.php');
        $this->assertPage($response, 'Terms of Use');
        $body = $this->text($response, '//body');
        self::assertStringContainsString('use of the website alone does not grant consent', $body);
        self::assertStringContainsString('Last updated September 24, 2026', $body);
        self::assertStringContainsString('ARIZONA STATE LAW', $body);
        self::assertStringContainsString('MARICOPA COUNTY, ARIZONA', $body);
        self::assertStringNotContainsString('VIRGINIA STATE LAW', $body);
    }

    public function testRegisterPage(): void {
        $this->assertPage($this->get('register.php'), 'Register');
    }

    public function testRegisterRedirectsLoggedInUser(): void {
        $this->adminLogin();
        $response = $this->get('register.php');
        self::assertContains($response->getStatusCode(), [301, 302, 303, 307, 308]);
        self::assertSame('/user/profile.php', parse_url($response->getHeaderLine('Location'), PHP_URL_PATH));
    }

    public function testReviewsPages(): void {
        foreach ([
            'reviews.php' => 'Raves',
            'reviews.php?c=1' => 'Portrait Raves',
            'reviews.php?c=2' => 'Wedding Raves',
            'reviews.php?c=3' => 'Commercial Raves',
        ] as $path => $heading) {
            $this->assertPage($this->get($path), $heading);
        }
    }
}
