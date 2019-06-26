<?php
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";

class UserTest extends TestCase {
    private $user;
    public function testNoUser() {
        $this->user = new User ();
        $this->assertFalse ( $this->user->isLoggedIn () );
        $this->assertEquals ( '', $this->user->getId () );
        $this->assertEquals ( '', $this->user->getUser () );
        $this->assertEquals ( '', $this->user->getRole () );
        $this->assertFalse ( $this->user->isAdmin () );
        $this->assertEquals ( '', $this->user->getFirstName () );
        $this->assertEquals ( '', $this->user->getLastName () );
        $this->assertEquals ( '', $this->user->getName () );
        $this->assertEquals ( '', $this->user->getEmail () );
        $this->user = NULL;
    }
    public function testBadUser() {
        $_SESSION ['hash'] = "1234567890abcdef1234567890abcdef";
        $this->user = new User ();
        $this->assertFalse ( $this->user->isLoggedIn () );
        $this->user = NULL;
    }
    public function testAdminUser() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->user = new User ();
        $this->assertTrue ( $this->user->isLoggedIn () );
        $this->assertEquals ( 1, $this->user->getId () );
        $this->assertEquals ( 'msaperst', $this->user->getUser () );
        $this->assertEquals ( 'admin', $this->user->getRole () );
        $this->assertTrue ( $this->user->isAdmin () );
        $this->assertEquals ( 'Max', $this->user->getFirstName () );
        $this->assertEquals ( 'Saperstone', $this->user->getLastName () );
        $this->assertEquals ( 'Max Saperstone', $this->user->getName () );
        $this->assertEquals ( 'msaperst@gmail.com', $this->user->getEmail () );
        $this->user = NULL;
    }
    public function testDownloadUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $this->user = new User ();
        $this->assertTrue ( $this->user->isLoggedIn () );
        $this->assertEquals ( 3, $this->user->getId () );
        $this->assertEquals ( 'downloader', $this->user->getUser () );
        $this->assertEquals ( 'downloader', $this->user->getRole () );
        $this->assertFalse ( $this->user->isAdmin () );
        $this->assertEquals ( 'Download', $this->user->getFirstName () );
        $this->assertEquals ( 'User', $this->user->getLastName () );
        $this->assertEquals ( 'Download User', $this->user->getName () );
        $this->assertEquals ( 'email@example.org', $this->user->getEmail () );
        $this->user = NULL;
    }
    public function testUploadUser() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $this->user = new User ();
        $this->assertTrue ( $this->user->isLoggedIn () );
        $this->assertEquals ( 4, $this->user->getId () );
        $this->assertEquals ( 'uploader', $this->user->getUser () );
        $this->assertEquals ( 'uploader', $this->user->getRole () );
        $this->assertFalse ( $this->user->isAdmin () );
        $this->assertEquals ( 'Upload', $this->user->getFirstName () );
        $this->assertEquals ( 'User', $this->user->getLastName () );
        $this->assertEquals ( 'Upload User', $this->user->getName () );
        $this->assertEquals ( 'uploader@example.org', $this->user->getEmail () );
        $this->user = NULL;
    }
}