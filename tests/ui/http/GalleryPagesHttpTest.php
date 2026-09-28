<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

use Sql;

class GalleryPagesHttpTest extends HttpTestBase {
    /**
     * @dataProvider galleryPageProvider
     */
    public function testGalleryPage(string $path, string $heading): void {
        $this->assertPage($this->get($path), $heading);
    }

    public static function galleryPageProvider(): array {
        return [
            'mitzvah bimah' => ['b-nai-mitzvah/galleries.php?w=73', 'Bimah Session Gallery'],
            'mitzvah services' => ['b-nai-mitzvah/galleries.php?w=74', 'Services Gallery'],
            'mitzvah reception' => ['b-nai-mitzvah/galleries.php?w=75', 'Reception Gallery'],
            'mitzvah photobooth' => ['b-nai-mitzvah/galleries.php?w=77', 'Photobooth Gallery'],
            'mitzvah story grids' => ['b-nai-mitzvah/galleries.php?w=79', 'Story Grids Gallery'],
            'mitzvah heirloom albums' => ['b-nai-mitzvah/gallery.php?w=80', 'Heirloom Albums Gallery'],
            'mitzvah acrylic' => ['b-nai-mitzvah/galleries.php?w=81', 'Acrylic Prints Gallery'],
            'mitzvah frames' => ['b-nai-mitzvah/galleries.php?w=82', 'Stand Out Frames Gallery'],
            'mitzvah canvas' => ['b-nai-mitzvah/gallery.php?w=83', 'Canvas Pro Gallery'],
            'mitzvah standard albums' => ['b-nai-mitzvah/galleries.php?w=84', 'Standard Albums Gallery'],
            'mitzvah signature albums' => ['b-nai-mitzvah/galleries.php?w=85', 'Signature Albums Gallery'],
            'mitzvah memory book' => ['b-nai-mitzvah/galleries.php?w=86', 'Memory Book Gallery'],
            'mitzvah album block' => ['b-nai-mitzvah/galleries.php?w=87', 'Album Block Gallery'],
            'mitzvah gift prints' => ['b-nai-mitzvah/gallery.php?w=90', 'Gift Prints Gallery'],
            'mitzvah curved metal' => ['b-nai-mitzvah/galleries.php?w=95', 'Curved Metal Gallery'],
            'mitzvah mounted prints' => ['b-nai-mitzvah/galleries.php?w=96', 'Mounted Gallery'],
            'mitzvah main gallery' => ['b-nai-mitzvah/gallery.php?w=72', "B'nai Mitzvah Gallery"],
            'mitzvah product gallery' => ['b-nai-mitzvah/gallery.php?w=78', 'Product Gallery'],

            'commercial studio headshots' => ['commercial/galleries.php?w=53', 'Studio Headshots Gallery'],
            'commercial location headshots' => ['commercial/galleries.php?w=54', 'On Location Headshots Gallery'],
            'commercial company headshots' => ['commercial/galleries.php?w=55', 'Company Headshots and Team Photos Gallery'],
            'commercial photobooth' => ['commercial/galleries.php?w=58', 'Photobooth Gallery'],
            'commercial hot cocoa' => ['commercial/galleries.php?w=65', 'Team Hot Cocoa Social Gallery'],
            'commercial culture' => ['commercial/galleries.php?w=66', 'Corporate Culture Gallery'],
            'commercial doctor care' => ['commercial/galleries.php?w=67', 'Doctor Care Gallery'],
            'commercial neurogrow' => ['commercial/galleries.php?w=68', 'NeuroGrow - Brain Fitness Center Gallery'],
            'commercial meeting' => ['commercial/galleries.php?w=69', 'Company Meeting Gallery'],
            'commercial holiday party' => ['commercial/galleries.php?w=70', 'Holiday Party Gallery'],
            'commercial picnic' => ['commercial/galleries.php?w=71', 'Corporate Picnic Gallery'],
            'commercial branding' => ['commercial/gallery.php?w=56', 'Professional Branding Gallery'],
            'commercial chiropractic branding' => ['commercial/galleries.php?w=97', 'Chiropractic Care Gallery'],
            'commercial life coach branding' => ['commercial/galleries.php?w=98', 'Life Coach Gallery'],
            'commercial events' => ['commercial/gallery.php?w=57', 'Events Gallery'],

            'portrait maternity' => ['portrait/galleries.php?w=2', 'Maternity Gallery'],
            'portrait family' => ['portrait/galleries.php?w=6', 'Kids and Family Gallery'],
            'portrait seniors' => ['portrait/galleries.php?w=7', 'Seniors Gallery'],
            'portrait favorites' => ['portrait/galleries.php?w=13', 'Favorites Gallery'],
            'portrait home' => ['portrait/galleries.php?w=14', 'At Your Home Gallery'],
            'portrait studio' => ['portrait/galleries.php?w=15', 'Studio Gallery'],
            'portrait in studio' => ['portrait/galleries.php?w=35', 'In Studio Gallery'],
            'portrait first birthday' => ['portrait/galleries.php?w=48', 'First Birthday Gallery'],
            'portrait story grids' => ['portrait/galleries.php?w=29', 'Story Grids Gallery'],
            'portrait albums' => ['portrait/galleries.php?w=30', 'Heirloom Albums Gallery'],
            'portrait acrylic' => ['portrait/galleries.php?w=31', 'Acrylic Prints Gallery'],
            'portrait frames' => ['portrait/galleries.php?w=33', 'Stand Out Frames Gallery'],
            'portrait canvas' => ['portrait/galleries.php?w=34', 'Canvas Pro Gallery'],
            'portrait album block' => ['portrait/galleries.php?w=50', 'Album Block Gallery'],
            'portrait keepsake usb' => ['portrait/galleries.php?w=51', 'Keepsake USB Gallery'],
            'portrait reveal box' => ['portrait/galleries.php?w=62', 'Reveal Box Gallery'],
            'portrait gift prints' => ['portrait/gallery.php?w=88', 'Gift Prints Gallery'],
            'portrait curved metal' => ['portrait/galleries.php?w=91', 'Curved Metal Gallery'],
            'portrait mounted prints' => ['portrait/galleries.php?w=92', 'Mounted Gallery'],
            'portrait main gallery' => ['portrait/gallery.php?w=1', 'Portrait Gallery'],
            'portrait product gallery' => ['portrait/gallery.php?w=28', 'Product Gallery'],
            'portrait newborn' => ['portrait/gallery.php?w=3', 'Newborn Gallery'],

            'wedding national harbor' => ['wedding/galleries.php?w=17', 'National Harbor Gallery'],
            'wedding dc mall' => ['wedding/galleries.php?w=18', 'DC Mall Gallery'],
            'wedding georgetown' => ['wedding/galleries.php?w=19', 'Georgetown Gallery'],
            'wedding engagement favorites' => ['wedding/galleries.php?w=20', 'Favorites Gallery'],
            'wedding washington dc' => ['wedding/galleries.php?w=21', 'Washington DC Gallery'],
            'wedding old town' => ['wedding/galleries.php?w=22', 'Old Town Alexandria Gallery'],
            'wedding paint war' => ['wedding/galleries.php?w=23', 'Paint War Gallery'],
            'wedding favorites' => ['wedding/galleries.php?w=24', 'Favorites Gallery'],
            'wedding one' => ['wedding/galleries.php?w=25', 'Wedding 1 Gallery'],
            'wedding two' => ['wedding/galleries.php?w=26', 'Wedding 2 Gallery'],
            'wedding three' => ['wedding/galleries.php?w=27', 'Wedding 3 Gallery'],
            'wedding photobooth' => ['wedding/galleries.php?w=37', 'Photobooth Gallery'],
            'wedding story grids' => ['wedding/galleries.php?w=39', 'Story Grids Gallery'],
            'wedding albums' => ['wedding/galleries.php?w=40', 'Heirloom Albums Gallery'],
            'wedding acrylic' => ['wedding/galleries.php?w=41', 'Acrylic Prints Gallery'],
            'wedding frames' => ['wedding/galleries.php?w=43', 'Stand Out Frames Gallery'],
            'wedding canvas' => ['wedding/galleries.php?w=44', 'Canvas Pro Gallery'],
            'wedding standard albums' => ['wedding/galleries.php?w=45', 'Standard Albums Gallery'],
            'wedding signature albums' => ['wedding/galleries.php?w=46', 'Signature Albums Gallery'],
            'wedding engagement book' => ['wedding/galleries.php?w=47', 'Engagement Book Gallery'],
            'wedding reveal box' => ['wedding/galleries.php?w=63', 'Reveal Box Gallery'],
            'wedding album block' => ['wedding/galleries.php?w=76', 'Album Block Gallery'],
            'wedding gift prints' => ['wedding/gallery.php?w=89', 'Gift Prints Gallery'],
            'wedding curved metal' => ['wedding/galleries.php?w=93', 'Curved Metal Gallery'],
            'wedding mounted prints' => ['wedding/galleries.php?w=94', 'Mounted Gallery'],
            'wedding main gallery' => ['wedding/gallery.php?w=8', 'Wedding Gallery'],
            'wedding product gallery' => ['wedding/gallery.php?w=38', 'Product Gallery'],
            'wedding heirloom album gallery' => ['wedding/gallery.php?w=40', 'Heirloom Albums Gallery'],
            'wedding proposals' => ['wedding/gallery.php?w=9', 'Surprise Proposals Gallery'],
            'wedding engagements' => ['wedding/gallery.php?w=10', 'Engagements Gallery'],
            'wedding weddings' => ['wedding/gallery.php?w=11', 'Weddings Gallery'],
        ];
    }

    public function testProductGalleryRendersExpectedChildHierarchy(): void {
        $response = $this->get('portrait/gallery.php?w=28');

        $this->assertPage($response, 'Product Gallery');
        foreach ([
            29 => 'Story Grids',
            30 => 'Heirloom Albums',
            31 => 'Acrylic Prints',
            33 => 'Stand Out Frames',
            34 => 'Canvas Pro',
            50 => 'Album Block',
            51 => 'Keepsake USB',
            62 => 'Reveal Box',
            88 => 'Gift Prints',
        ] as $id => $title) {
            self::assertSame(
                1,
                $this->elementCount($response, "//*[@section='$title']//a[contains(@href, 'w=$id')]"),
                "Missing $title child gallery"
            );
        }
    }

    public function testGalleryDescriptionAndProtectedImageMarkup(): void {
        $sql = new Sql();
        try {
            $sql->executeStatement(
                "INSERT INTO gallery_images (id, gallery, title, sequence, caption, location, width, height, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [99991, 91, 'HTTP protected image', 999, 'HTTP caption', '/protected/http-test.jpg', 300, 400]
            );

            $response = $this->get('portrait/galleries.php?w=91');

            $this->assertPage($response, 'Curved Metal Gallery');
            self::assertStringContainsString('perfect metal print display', $this->text($response, "//*[@id='gallery-comment']"));
            self::assertSame(1, $this->elementCount($response, "//*[@data-background-image='/protected/http-test.jpg']"));
            self::assertSame(0, $this->elementCount($response, "//*[contains(@class, 'carousel-inner')]//img"));
        } finally {
            $sql->executeStatement('DELETE FROM gallery_images WHERE id = 99991');
            $sql->disconnect();
        }
    }

    /**
     * @dataProvider invalidGalleryPageProvider
     */
    public function testInvalidGalleryPage(string $path): void {
        $response = $this->get($path);
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('404 Not Found', $this->text($response, '//h1'));
        self::assertSame($this->copyright, $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' copyright ')]"));
        $this->submitErrorReport($response);
        \CustomAsserts::assertEmailSubjectExists('404 Error');
    }

    public static function invalidGalleryPageProvider(): array {
        return [
            'mitzvah blank galleries id' => ['b-nai-mitzvah/galleries.php?w='],
            'mitzvah detail id points to collection' => ['b-nai-mitzvah/gallery.php?w=73'],
            'commercial blank galleries id' => ['commercial/galleries.php?w='],
            'commercial detail id points to collection' => ['commercial/gallery.php?w=54'],
            'portrait blank galleries id' => ['portrait/galleries.php?w='],
            'portrait detail id points to collection' => ['portrait/gallery.php?w=2'],
            'wedding blank galleries id' => ['wedding/galleries.php?w='],
            'wedding detail id points to collection' => ['wedding/gallery.php?w=17'],
        ];
    }

    /**
     * @dataProvider invalidServiceReviewProvider
     */
    public function testInvalidServiceReviewReturnsNotFound(string $path): void {
        $response = $this->get($path);
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('404 Not Found', $this->text($response, '//h1'));
        $this->submitErrorReport($response);
        \CustomAsserts::assertEmailSubjectExists('404 Error');
    }

    public static function invalidServiceReviewProvider(): array {
        return [
            'mitzvah blank category' => ['b-nai-mitzvah/reviews.php?c='],
            'mitzvah invalid category' => ['b-nai-mitzvah/reviews.php?c=abc'],
            'commercial blank category' => ['commercial/reviews.php?c='],
            'commercial invalid category' => ['commercial/reviews.php?c=abc'],
            'portrait blank category' => ['portrait/reviews.php?c='],
            'portrait invalid category' => ['portrait/reviews.php?c=abc'],
            'wedding blank category' => ['wedding/reviews.php?c='],
            'wedding invalid category' => ['wedding/reviews.php?c=abc'],
        ];
    }

    /**
     * @dataProvider reviewPageProvider
     */
    public function testReviewPage(string $path, string $heading): void {
        $this->assertPage($this->get($path), $heading);
    }

    /**
     * @dataProvider serviceReviewEntryPointProvider
     */
    public function testServiceReviewEntryPointUsesSharedReviewsPage(string $path, string $heading): void {
        $response = $this->get($path);

        $this->assertPage($response, $heading);
        self::assertSame(1, $this->elementCount($response, "//ol[contains(@class, 'breadcrumb')]//li[contains(@class, 'active') and normalize-space(.)='Raves']"));
    }

    public static function serviceReviewEntryPointProvider(): array {
        return [
            'portrait wrapper' => ['portrait/reviews.php?c=1', 'Portrait Raves'],
            'wedding wrapper' => ['wedding/reviews.php?c=2', 'Wedding Raves'],
            'commercial symlink' => ['commercial/reviews.php?c=3', 'Commercial Raves'],
            'mitzvah symlink' => ['b-nai-mitzvah/reviews.php?c=4', "B'nai Mitzvah Raves"],
        ];
    }

    public static function reviewPageProvider(): array {
        return [
            'mitzvah reviews symlink' => ['b-nai-mitzvah/reviews.php?c=4', "B'nai Mitzvah Raves"],
            'commercial reviews symlink' => ['commercial/reviews.php?c=3', 'Commercial Raves'],
            'portrait reviews' => ['portrait/reviews.php?c=1', 'Portrait Raves'],
            'wedding reviews' => ['wedding/reviews.php?c=2', 'Wedding Raves'],
        ];
    }
}
