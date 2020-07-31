<?php
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname( dirname ( __DIR__ ) );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class UserIntegrationTest extends TestCase {

    private $sql;

    public function setUp() {
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->sql->disconnect();
    }

    public function testNoUser() {
        $user = new User ($this->sql);
        $this->assertFalse ( $user->isLoggedIn () );
        $this->assertEquals ( '', $user->getId () );
        $this->assertEquals ( '', $user->getUser () );
        $this->assertEquals ( '', $user->getRole () );
        $this->assertFalse ( $user->isAdmin () );
        $this->assertEquals ( '', $user->getFirstName () );
        $this->assertEquals ( '', $user->getLastName () );
        $this->assertEquals ( '', $user->getName () );
        $this->assertEquals ( '', $user->getEmail () );
    }
    public function testBadSessionUser() {
        $_SESSION ['hash'] = "1234567890abcdef1234567890abcdef";
        $user = new User ($this->sql);
        unset( $_SESSION ['hash'] );
        $this->assertFalse ( $user->isLoggedIn () );
    }
    public function testBadCookieUser() {
        $_COOKIE ['hash'] = "1234567890abcdef1234567890abcdef";
        $user = new User ($this->sql);
        unset( $_COOKIE ['hash'] );
        $this->assertFalse ( $user->isLoggedIn () );
    }
    public function testAdminUser() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = new User ($this->sql);
        unset( $_SESSION ['hash'] );
        $this->assertTrue ( $user->isLoggedIn () );
        $this->assertEquals ( 1, $user->getId () );
        $this->assertEquals ( 'msaperst', $user->getUser () );
        $this->assertEquals ( 'admin', $user->getRole () );
        $this->assertTrue ( $user->isAdmin () );
        $this->assertEquals ( 'Max', $user->getFirstName () );
        $this->assertEquals ( 'Saperstone', $user->getLastName () );
        $this->assertEquals ( 'Max Saperstone', $user->getName () );
        $this->assertEquals ( 'msaperst@gmail.com', $user->getEmail () );
    }
    public function testDownloadUser() {
        $_COOKIE ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $user = new User ($this->sql);
        unset( $_COOKIE ['hash'] );
        $this->assertTrue ( $user->isLoggedIn () );
        $this->assertEquals ( 3, $user->getId () );
        $this->assertEquals ( 'downloader', $user->getUser () );
        $this->assertEquals ( 'downloader', $user->getRole () );
        $this->assertFalse ( $user->isAdmin () );
        $this->assertEquals ( 'Download', $user->getFirstName () );
        $this->assertEquals ( 'User', $user->getLastName () );
        $this->assertEquals ( 'Download User', $user->getName () );
        $this->assertEquals ( 'email@example.org', $user->getEmail () );
    }
    public function testUploadUser() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $user = new User ($this->sql);
        unset( $_SESSION ['hash'] );
        $this->assertTrue ( $user->isLoggedIn () );
        $this->assertEquals ( 4, $user->getId () );
        $this->assertEquals ( 'uploader', $user->getUser () );
        $this->assertEquals ( 'uploader', $user->getRole () );
        $this->assertFalse ( $user->isAdmin () );
        $this->assertEquals ( 'Upload', $user->getFirstName () );
        $this->assertEquals ( 'User', $user->getLastName () );
        $this->assertEquals ( 'Upload User', $user->getName () );
        $this->assertEquals ( 'uploader@example.org', $user->getEmail () );
    }

    public function testGeneratePassword() {
        $user = new User ($this->sql);
        $this->assertEquals( 20, strlen( $user->generatePassword() ) );
        $this->assertEquals( 1, preg_match( "/^([a-zA-Z0-9]{20})$/", $user->generatePassword() ) );
    }
}