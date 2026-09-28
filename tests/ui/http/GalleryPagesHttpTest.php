<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';

class GalleryPagesHttpTest extends HttpTestBase {
    /**
     * @dataProvider galleryPageProvider
     */
    public function testGalleryPage(string $path, string $heading): void {
        $this->assertPage($this->get($path), $heading);
    }

    public static function galleryPageProvider(): array {
        return [
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
            'wedding main gallery' => ['wedding/gallery.php?w=8', 'Wedding Gallery'],
            'wedding product gallery' => ['wedding/gallery.php?w=38', 'Product Gallery'],
            'wedding heirloom album gallery' => ['wedding/gallery.php?w=40', 'Heirloom Albums Gallery'],
            'wedding proposals' => ['wedding/gallery.php?w=9', 'Surprise Proposals Gallery'],
            'wedding engagements' => ['wedding/gallery.php?w=10', 'Engagements Gallery'],
            'wedding weddings' => ['wedding/gallery.php?w=11', 'Weddings Gallery'],
        ];
    }

    /**
     * @dataProvider invalidGalleryPageProvider
     */
    public function testInvalidGalleryPage(string $path): void {
        $response = $this->get($path);
        self::assertSame('404 Not Found', $this->text($response, '//h1'));
        self::assertSame($this->copyright, $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' copyright ')]"));
        $this->submitErrorReport($response);
        \CustomAsserts::assertEmailSubjectExists('404 Error');
    }

    public static function invalidGalleryPageProvider(): array {
        return [
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

    public static function reviewPageProvider(): array {
        return [
            'commercial reviews' => ['commercial/reviews.php?c=3', 'Commercial Raves'],
            'portrait reviews' => ['portrait/reviews.php?c=1', 'Portrait Raves'],
            'wedding reviews' => ['wedding/reviews.php?c=2', 'Wedding Raves'],
        ];
    }
}
