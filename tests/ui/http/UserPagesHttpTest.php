<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';

class UserPagesHttpTest extends HttpTestBase {
    /**
     * @dataProvider protectedPageProvider
     */
    public function testProtectedPageRejectsAnonymousUser(string $path): void {
        $response = $this->get($path);
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('401 Unauthorized', $this->text($response, '//h1'));
        self::assertSame($this->copyright, $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' copyright ')]"));
        $this->submitErrorReport($response);
        \CustomAsserts::assertEmailSubjectExists('401 Error');
    }

    public static function protectedPageProvider(): array {
        return [
            'contracts' => ['user/contracts.php'],
            'albums' => ['user/index.php'],
            'profile' => ['user/profile.php'],
            'users' => ['user/users.php'],
        ];
    }

    public function testRegularUserCannotAccessContractsPage(): void {
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $response = $this->get('user/contracts.php');

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('401 Unauthorized', $this->text($response, '//h1'));
    }

    public function testAdminContractsPage(): void {
        $this->adminLogin();
        $response = $this->get('user/contracts.php');
        $this->assertPage($response, 'Manage Contracts');
        self::assertSame(1, $this->elementCount($response, "//*[@id='add-contract-btn']"));
        self::assertSame(8, $this->elementCount($response, "//*[@id='contracts']//thead//th"));
    }

    public function testAdminAlbumsPage(): void {
        $this->adminLogin();
        $response = $this->get('user/index.php');
        $this->assertPage($response, 'Manage Albums');
        self::assertSame(1, $this->elementCount($response, "//*[@id='add-album-btn']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='add-album-div']"));
        self::assertSame(8, $this->elementCount($response, "//*[@id='albums']//thead//th"));
    }

    public function testRegularUserAlbumsPage(): void {
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $response = $this->get('user/index.php');

        $this->assertPage($response, 'View Albums');
        self::assertSame(1, $this->elementCount($response, "//*[@id='add-album-div']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='add-album-btn']"));
        self::assertSame(4, $this->elementCount($response, "//*[@id='albums']//thead//th"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='thumbnail-status-filter']"));
    }

    public function testAdminProfilePage(): void {
        $this->adminLogin();
        $response = $this->get('user/profile.php');
        $this->assertPage($response, 'Manage Profile');
        self::assertSame('msaperst', $this->attribute($response, "//*[@id='profile-username']", 'value'));
        self::assertSame('Max', $this->attribute($response, "//*[@id='profile-firstname']", 'value'));
        self::assertSame('Saperstone', $this->attribute($response, "//*[@id='profile-lastname']", 'value'));
        self::assertSame('msaperst@gmail.com', $this->attribute($response, "//*[@id='profile-email']", 'value'));
    }

    public function testRegularUserCannotAccessUsersPage(): void {
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $response = $this->get('user/users.php');

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('401 Unauthorized', $this->text($response, '//h1'));
    }

    public function testAdminUsersPage(): void {
        $this->adminLogin();
        $response = $this->get('user/users.php');
        $this->assertPage($response, 'Manage Users');
        self::assertSame(1, $this->elementCount($response, "//*[@id='add-user-btn']"));
        self::assertSame(8, $this->elementCount($response, "//*[@id='users']//thead//th"));
    }
}
