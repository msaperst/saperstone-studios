<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class CheckoutTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }
//TODO - uncomment
//     public function testNotLoggedIn() {
//         $response = $this->http->request('POST', 'api/checkout.php');
//         $this->assertEquals(200, $response->getStatusCode());
//         $this->assertEquals("You must be logged in to submit your order", $response->json()['error']);
//     }

    //TODO - need to finish the rest
}
?>