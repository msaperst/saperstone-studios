<?php
$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
class GetBlogFullTest extends PHPUnit_Framework_TestCase {
    private $client;
    protected function setUp() {
        // setup our guzzle client
        $this->client = new GuzzleHttp\Client ( [ 
                'base_uri' => 'http://localhost/api/' 
        ] );
        // seed required test data
        $conn = new Sql ();
        $conn->connect ();
        $sql = "INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`, `twitter`) VALUES (9999998, 'Integration Test Post', '2012-10-25', 'some_preview_img.jpg', '-5', '0', '0');";
        mysqli_query ( $conn->db, $sql );
        $conn->disconnect ();
    }
    protected function tearDown() {
        // removed seeded test data
        $conn = new Sql ();
        $conn->connect ();
        $sql = "DELETE FROM `blog_details` WHERE `id` = '9999998';";
        mysqli_query ( $conn->db, $sql );
        $conn->disconnect ();
    }
    public function testNoPostProvided() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php' );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        $this->assertEquals ( "No blog post provided", $response->getBody () );
    }
    public function testBadPostProvided() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 9999999 
                ] 
        ] );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        $this->assertEquals ( "Blog doesn't exist!", $response->getBody () );
    }
    public function testInputType() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => "9999998" 
                ] 
        ] );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
    }
    //TODO - look at reworking this one
//     public function testFields() {
//         $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
//                 'query' => [ 
//                         'post' => 9999998 
//                 ] 
//         ] );
//         $data = json_decode ( $response->getBody (), true );
        
//         $this->assertEquals ( 200, $response->getStatusCode () );
//         $this->assertEquals ( 9, sizeof ( $data ) );
//     }
    public function testId() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 9999998 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "id", $data );
        $this->assertEquals ( 9999998, $data ['id'] );
    }
    public function testTitle() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 9999998 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "title", $data );
        $this->assertEquals ( "Integration Test Post", $data ['title'] );
    }
    public function testDate() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 9999998 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "date", $data );
        $this->assertEquals ( "October 25th, 2012", $data ['date'] );
    }
    public function testPreviewImage() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 9999998 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "preview", $data );
        $this->assertEquals ( "some_preview_img.jpg", $data ['preview'] );
    }
    public function testOffset() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 9999998 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "offset", $data );
        $this->assertEquals ( - 5, $data ['offset'] );
    }
    public function testActive() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 9999998 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "active", $data );
        $this->assertEquals ( 0, $data ['active'] );
    }
    public function testTwitter() {
        $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [ 
                'query' => [ 
                        'post' => 9999998 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "twitter", $data );
        $this->assertEquals ( 0, $data ['twitter'] );
    }
    
    // TODO add in additional posts with these elements for additional tests
    // public function testContent() {
    // $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [
    // 'query' => [
    // 'post' => 9999998
    // ]
    // ] );
    
    // $data = json_decode ( $response->getBody (), true );
    
    // $this->assertArrayHasKey ( "content", $data );
    // // TODO - fill in content check
    // }
    // public function testTags() {
    // $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [
    // 'query' => [
    // 'post' => 9999998
    // ]
    // ] );
    // $data = json_decode ( $response->getBody (), true );
    
    // $this->assertArrayHasKey ( "tags", $data );
    // // TODO - fill in content check
    // }
    // public function testComments() {
    // $response = $this->client->get ( 'http://localhost/api/get-blog-full.php', [
    // 'query' => [
    // 'post' => 9999998
    // ]
    // ] );
    // $data = json_decode ( $response->getBody (), true );
    
    // $this->assertArrayHasKey ( "comments", $data );
    // // TODO - fill in content check
    // }
}
?>