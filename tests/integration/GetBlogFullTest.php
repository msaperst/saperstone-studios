<?php
class GetBlogFullTest extends PHPUnit_Framework_TestCase {
    private $client;
    protected function setUp() {
        $this->client = new GuzzleHttp\Client ( [ 
                'base_uri' => 'http://localhost/api/' 
        ] );
    }
    public function testPostProvided() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php' );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        
        $this->assertEquals ( "No blog post provided", $response->getBody () );
    }
    public function testInputType() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => "5" 
                ] 
        ] );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
    }
    public function testFields() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => "5" 
                ] 
        ] );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertEquals ( 12, sizeof ( $data ) );
    }
    public function testId() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "id", $data );
        $this->assertEquals ( 5, $data ['id'] );
    }
    public function testTitle() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "title", $data );
        $this->assertEquals ( "Down Under :: Sydney Sites", $data ['title'] );
    }
    public function testSafeTitle() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "safe_title", $data );
        $this->assertEquals ( "", $data ['safe_title'] );
    }
    public function testDate() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "date", $data );
        $this->assertEquals ( "December 13th, 2011", $data ['date'] );
    }
    public function testPreviewImage() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "preview", $data );
        $this->assertEquals ( "/blog/2011/12/13/preview_image.jpg", $data ['preview'] );
    }
    public function testOffset() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "offset", $data );
        $this->assertEquals ( 0, $data ['offset'] );
    }
    public function testActive() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "active", $data );
        $this->assertEquals ( 1, $data ['active'] );
    }
    public function testTwitter() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "twitter", $data );
        $this->assertEquals ( 0, $data ['twitter'] );
    }
    public function testFacebook() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "facebook", $data );
        $this->assertEquals ( 0, $data ['facebook'] );
    }
    public function testContent() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "content", $data );
        // TODO - fill in content check
    }
    public function testTags() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "tags", $data );
        // TODO - fill in content check
    }
    public function testComments() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 5 
                ] 
        ] );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "comments", $data );
        // TODO - fill in content check
    }
}
?>