<?php

namespace api;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CreateBlogPostTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/create-blog-post.php');
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/create-blog-post.php', [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoTitle() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog title is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankTitle() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'form_params' => [
                'title' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog title can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'form_params' => [
                'title' => 'Sample Blog'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog date is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'form_params' => [
                'title' => 'Sample Blog',
                'date' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog date can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'form_params' => [
                'title' => 'Sample Blog',
                'date' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog date is not the correct format", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPreviewImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'form_params' => [
                'title' => 'Sample Blog',
                'date' => date("Y-m-d")
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog preview image is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPreviewImage2() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'form_params' => [
                'title' => 'Sample Blog',
                'date' => date("Y-m-d"),
                'preview' => array()
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog preview image is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankPreviewImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'form_params' => [
                'title' => 'Sample Blog',
                'date' => date("Y-m-d"),
                'preview' => [
                    'img' => ''
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog preview image can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoContent() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-blog-post.php', [
            'form_params' => [
                'title' => 'Sample Blog',
                'date' => date("Y-m-d"),
                'preview' => [
                    'img' => 'my-preview-img.jpg'
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog content is required", (string)$response->getBody());
    }

// TODO - commenting out, not sure how to make it work right
//     public function testEmptyContent() {
//         $cookieJar = CookieJar::fromArray([
//                     'hash' => '1d7505e7f434a7713e84ba399e937191'
//                 ], getenv('DB_HOST'));
//         $response = $this->http->request('POST', 'api/create-blog-post.php', [
//                'form_params' => [
//                    'title' => 'Sample Blog',
//                    'date' => date("Y-m-d"),
//                    'preview' => [
//                        'img' => 'my-preview-img.jpg'
//                    ],
//                    'content' => []
//                ],
//                'cookies' => $cookieJar
//         ]);
//         $this->assertEquals(200, $response->getStatusCode());
//         $this->assertEquals("Blog content can not be empty", (string) $response->getBody() );
//     }
//
//     public function testBadContent() {
//         $cookieJar = CookieJar::fromArray([
//                     'hash' => '1d7505e7f434a7713e84ba399e937191'
//                 ], getenv('DB_HOST'));
//         $response = $this->http->request('POST', 'api/create-blog-post.php', [
//                 'form_params' => [
//                     'title' => 'Sample Blog',
//                     'date' => date("Y-m-d"),
//                     'preview' => [
//                         'img' => 'my-preview-img.jpg'
//                     ],
//                     'content' => [
//                         0 => [
//                             'type' => 'foo'
//                         ]
//                     ]
//                 ],
//                 'cookies' => $cookieJar
//         ]);
//         $this->assertEquals(200, $response->getStatusCode());
//         $this->assertEquals("Unexpected content present, please consult the webmaster", (string) $response->getBody() );
//     }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testPreviewOffsetOnlyImage() {
        try {
            touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'image.jpg');
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/create-blog-post.php', [
                'form_params' => [
                    'title' => 'Sample Blog',
                    'date' => '2030-01-01',
                    'preview' => [
                        'img' => '../blog/posts/image.jpg',
                        'offset' => '10px'
                    ],
                    'content' => [
                        1 => [
                            'group' => 1,
                            'type' => 'images',
                            'imgs' => [
                                0 => [
                                    'location' => '../blog/posts/image.jpg',
                                    'top' => '0px',
                                    'left' => '0px',
                                    'width' => '1140px',
                                    'height' => '647px',
                                ]
                            ]
                        ]
                    ]
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $blogId = (string)$response->getBody();
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->assertEquals($blogId, $blogDetails['id']);
            $this->assertEquals("Sample Blog", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2030-01-01", $blogDetails['date']);
            $this->assertEquals("posts/2030/01/01/preview_image-$blogId.jpg", $blogDetails['preview']);
            $this->assertEquals("10", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRow("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;");
            $this->assertEquals($blogId, $blogImages['blog']);
            $this->assertEquals(1, $blogImages['contentGroup']);
            $this->assertEquals('posts/2030/01/01/image.jpg', $blogImages['location']);
            $this->assertEquals('1140', $blogImages['width']);
            $this->assertEquals('647', $blogImages['height']);
            $this->assertEquals('0', $blogImages['left']);
            $this->assertEquals('0', $blogImages['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;"));
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;"));
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/blog/2030/01/01/preview_image-$blogId.jpg"));
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/blog/2030/01/01/image.jpg"));
        } finally {
            // cleanup
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/delete-blog.php', [
                'form_params' => [
                    'post' => $blogId
                ],
                'cookies' => $cookieJar
            ]);
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testNoPreviewOffsetOnlyText() {
        try {
            touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'image.jpg');
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/create-blog-post.php', [
                'form_params' => [
                    'title' => 'Sample Blog',
                    'date' => '2030-01-01',
                    'preview' => [
                        'img' => '../blog/posts/image.jpg',
                    ],
                    'content' => [
                        1 => [
                            'group' => 1,
                            'type' => 'text',
                            'text' => 'Some blog text'
                        ]
                    ]
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $blogId = (string)$response->getBody();
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->assertEquals($blogId, $blogDetails['id']);
            $this->assertEquals("Sample Blog", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2030-01-01", $blogDetails['date']);
            $this->assertEquals("posts/2030/01/01/preview_image-$blogId.jpg", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;"));
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;"));
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;");
            $this->assertEquals($blogId, $blogTexts['blog']);
            $this->assertEquals(1, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
        } finally {
            // cleanup
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/delete-blog.php', [
                'form_params' => [
                    'post' => $blogId
                ],
                'cookies' => $cookieJar
            ]);
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testTagsOffsetImagesText() {
        try {
            $oldMask = umask(0);
            copy(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/image.jpg');
            chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'image.jpg', 0777);
            umask($oldMask);
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/create-blog-post.php', [
                'form_params' => [
                    'title' => 'Sample Blog',
                    'date' => '2030-01-01',
                    'preview' => [
                        'img' => '../blog/posts/image.jpg',
                        'offset' => '33px'
                    ],
                    'tags' => [
                        "4", "2"
                    ],
                    'content' => [
                        1 => [
                            'group' => 1,
                            'type' => 'images',
                            'imgs' => [
                                0 => [
                                    'location' => '../blog/posts/image.jpg',
                                    'top' => '0px',
                                    'left' => '0px',
                                    'width' => '1140px',
                                    'height' => '647px',
                                ]
                            ]
                        ],
                        2 => [
                            'group' => 2,
                            'type' => 'text',
                            'text' => 'Some blog text'
                        ]
                    ]
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $blogId = (string)$response->getBody();
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->assertEquals($blogId, $blogDetails['id']);
            $this->assertEquals("Sample Blog", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2030-01-01", $blogDetails['date']);
            $this->assertEquals("posts/2030/01/01/preview_image-$blogId.jpg", $blogDetails['preview']);
            $this->assertEquals("33", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . substr($blogDetails['preview'], 6)));
            $blogImages = $this->sql->getRow("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;");
            $this->assertEquals($blogId, $blogImages['blog']);
            $this->assertEquals(1, $blogImages['contentGroup']);
            $this->assertEquals('posts/2030/01/01/image.jpg', $blogImages['location']);
            $this->assertEquals('1140', $blogImages['width']);
            $this->assertEquals('647', $blogImages['height']);
            $this->assertEquals('0', $blogImages['left']);
            $this->assertEquals('0', $blogImages['top']);
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . substr($blogImages['location'], 6)));
            $blogTags = $this->sql->getRows("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;");
            $this->assertEquals(2, sizeOf($blogTags));
            $this->assertEquals($blogId, $blogTags[0]['blog']);
            $this->assertEquals(4, $blogTags[0]['tag']);
            $this->assertEquals($blogId, $blogTags[1]['blog']);
            $this->assertEquals(2, $blogTags[1]['tag']);
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;");
            $this->assertEquals($blogId, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
        } finally {
            // cleanup
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/delete-blog.php', [
                'form_params' => [
                    'post' => $blogId
                ],
                'cookies' => $cookieJar
            ]);
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        }
    }
}