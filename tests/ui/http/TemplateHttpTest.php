<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

use GuzzleHttp\Cookie\SetCookie;
use Sql;

class TemplateHttpTest extends HttpTestBase {
    protected function tearDown(): void {
        $sql = new Sql();
        try {
            $sql->executeStatement('DELETE FROM announcements WHERE id = 999');
        } finally {
            $sql->disconnect();
        }
    }

    public function testUnknownPageRendersNotFoundTemplate(): void {
        $response = $this->get('badPage123');
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('404 Not Found', $this->text($response, '//h1'));
        self::assertStringContainsString('Home 404', $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' breadcrumb ')]"));
        self::assertStringContainsString('server has not found anything matching the Request-URI', (string) $response->getBody());
        self::assertSame($this->copyright, $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' copyright ')]"));
        $this->submitErrorReport($response);
        \CustomAsserts::assertEmailSubjectExists('404 Error');
    }

    public function testFooterIdentifiesAnonymousUser(): void {
        $response = $this->get();
        self::assertSame('', $this->attribute($response, "//*[@id='my-user-id']", 'value'));
        self::assertSame('', $this->attribute($response, "//*[@id='my-user-role']", 'value'));
    }

    public function testFooterIdentifiesAdminUser(): void {
        $this->adminLogin();
        $response = $this->get();
        self::assertSame('1', $this->attribute($response, "//*[@id='my-user-id']", 'value'));
        self::assertSame('admin', $this->attribute($response, "//*[@id='my-user-role']", 'value'));
    }

    public function testExpiredAnnouncementIsNotRendered(): void {
        $this->insertAnnouncement('/', '2000-07-23 00:00:00', '2000-12-31 00:00:00');
        self::assertSame(0, $this->elementCount($this->get(), "//*[contains(concat(' ', normalize-space(@class), ' '), ' alert-warning ')]"));
    }

    public function testActiveAnnouncementIsRendered(): void {
        $this->insertAnnouncement('/', '2000-07-23 00:00:00', '3000-12-31 00:00:00');
        $response = $this->get();
        self::assertSame(1, $this->elementCount($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' alert-warning ')]"));
        self::assertStringContainsString('HTTP Test Announcement', $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' alert-warning ')]"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='announcement-999']"));
    }

    public function testDismissedAnnouncementIsNotRendered(): void {
        $this->insertAnnouncement('/', '2000-07-23 00:00:00', '3000-12-31 00:00:00');
        $this->cookies->setCookie(new SetCookie([
            'Name' => 'announcement-999',
            'Value' => 'dismissed',
            'Domain' => getenv('APP_URL'),
            'Path' => '/',
        ]));
        self::assertSame(0, $this->elementCount($this->get(), "//*[contains(concat(' ', normalize-space(@class), ' '), ' alert-warning ')]"));
    }

    public function testAnnouncementOnlyRendersUnderConfiguredPath(): void {
        $this->insertAnnouncement('/portrait', '2000-07-23 00:00:00', '3000-12-31 00:00:00');
        self::assertSame(0, $this->elementCount($this->get(), "//*[contains(concat(' ', normalize-space(@class), ' '), ' alert-warning ')]"));
        self::assertSame(1, $this->elementCount($this->get('portrait/'), "//*[contains(concat(' ', normalize-space(@class), ' '), ' alert-warning ')]"));
    }

    public function testGuestNavigationOffersLogin(): void {
        $response = $this->get();
        self::assertSame(1, $this->elementCount($response, "//*[@id='login-menu-item']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='logout-button']"));
        self::assertSame(0, $this->elementCount($response, "//a[@href='/user/users.php']"));
    }

    public function testMainNavigationExposesCurrentSections(): void {
        $response = $this->get();
        foreach (["B'Nai Mitzvahs", 'Portraits', 'Weddings', 'Commercial', 'Blog', 'Information'] as $label) {
            self::assertGreaterThan(0, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[contains(normalize-space(.), \"$label\")]"), "Missing main navigation section $label");
        }
    }

    public function testGuestBlogAndInformationNavigation(): void {
        $response = $this->get();
        foreach (['/blog/posts.php', '/blog/categories.php'] as $href) {
            self::assertSame(1, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[normalize-space(.)='Blog']/following-sibling::ul//a[@href='$href']"), "Missing guest blog navigation link $href");
        }
        foreach (['/about.php', '/leighAnn.php', '/reviews.php', '/contact.php'] as $href) {
            self::assertSame(1, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[normalize-space(.)='Information']/following-sibling::ul//a[@href='$href']"), "Missing guest information navigation link $href");
        }
        self::assertSame(1, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[normalize-space(.)='Information']/following-sibling::ul//a[@href='#album']"));
        self::assertSame(0, $this->elementCount($response, "//a[@href='/blog/new.php']"));
        self::assertSame(0, $this->elementCount($response, "//a[@href='/blog/manage.php']"));
    }

    public function testAdminBlogAndInformationNavigation(): void {
        $this->adminLogin();
        $response = $this->get();
        self::assertSame(1, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[normalize-space(.)='Blog']/following-sibling::ul//a[@href='/blog/new.php']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[normalize-space(.)='Blog']/following-sibling::ul//a[@href='/blog/manage.php']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[normalize-space(.)='Information']/following-sibling::ul//a[@href='#album']"));
        foreach (['/about.php', '/leighAnn.php', '/reviews.php', '/contact.php'] as $href) {
            self::assertSame(1, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[normalize-space(.)='Information']/following-sibling::ul//a[@href='$href']"), "Missing admin information navigation link $href");
        }
    }

    public function testRegularUserNavigationOffersUserActions(): void {
        $this->loginAs('c90788c0e409eac6a95f6c6360d8dbf7');
        $response = $this->get();
        self::assertSame(0, $this->elementCount($response, "//*[@id='login-menu-item']"));
        self::assertSame(1, $this->elementCount($response, "//a[@href='/user/index.php' and contains(normalize-space(.), 'View Albums')]"));
        self::assertSame(1, $this->elementCount($response, "//a[@href='/user/profile.php' and contains(normalize-space(.), 'Manage Profile')]"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='logout-button']"));
        self::assertSame(0, $this->elementCount($response, "//a[@href='/user/users.php']"));
    }

    public function testAdminNavigationOffersAdminActions(): void {
        $this->adminLogin();
        $response = $this->get();
        foreach (['/user/users.php', '/user/index.php', '/user/contracts.php', '/user/profile.php'] as $href) {
            self::assertSame(1, $this->elementCount($response, "//a[@href='$href']"), "Missing admin navigation link $href");
        }
        self::assertSame(1, $this->elementCount($response, "//*[@id='logout-button']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='login-menu-item']"));
    }

    /**
     * @dataProvider serviceNavigationProvider
     */
    public function testServiceNavigation(string $path): void {
        $response = $this->get($path);
        foreach (['Details', 'Gallery', 'Retouch', 'Raves', 'Blog', 'About', 'Contact'] as $label) {
            self::assertGreaterThan(0, $this->elementCount($response, "//*[@id='bs-example-navbar-collapse-1']//a[contains(normalize-space(.), '$label')]"), "Missing $label navigation on $path");
        }
        self::assertSame(1, $this->elementCount($response, "//*[@id='login-menu-item']"));
    }

    public static function serviceNavigationProvider(): array {
        return [
            'mitzvah' => ['b-nai-mitzvah/'],
            'portrait' => ['portrait/'],
            'wedding' => ['wedding/'],
            'commercial' => ['commercial/'],
        ];
    }

    private function insertAnnouncement(string $path, string $start, string $end): void {
        $sql = new Sql();
        try {
            $sql->executeStatement(
                'INSERT INTO announcements (id, message, path, start, end, dismissible) VALUES (?, ?, ?, ?, ?, 1)',
                [999, 'HTTP Test Announcement', $path, $start, $end]
            );
        } finally {
            $sql->disconnect();
        }
    }
}
