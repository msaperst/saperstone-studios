<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateContractTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `contracts` (`id`, `link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`, `session`, `details`, `amount`, `deposit`, `invoice`, `content`, `signature`, `initial`, `file`) VALUES (999, '8e07fb32bf072e1825df8290a7bcdc57', 'commercial', 'MaxMaxMax', 'Address', '1234567890', 'email-address', '2021-10-13', '1234 Sesame Street', 'Some session', 'Up to one hour photo session to include:\r\n\r\nBusiness Headshots:\r\n- Web gallery for viewing/making selects\r\n- One file of your choice, color corrected/retouched per person\r\n- Additional files can be purchased for $80/file\r\n- Print release for marketing/web needs for purchased/included files\r\n\r\nOffice Photos:\r\n- All images delivered via web gallery for download\r\n- Print release for marketing/web needs', '0.00', '0.00', 'nope!', '    <h2>Saperstone Studios LLC. Commercial Contract</h2>\n    \n    <p>\n        <strong>This Contract</strong> is made by and between <u>&nbsp;Saperstone\n            Studios&nbsp;</u> (the \"Photographer\") and <u>&nbsp;MaxMaxMax&nbsp;\n        </u>(the \"Client\").<br> <strong>Whereas</strong>, Client wishes to\n        engage Photographer to provide certain photography services and\n        Photographer is willing to accept such engagement, all on the terms\n        and conditions set forth herein.<br> <strong>Now therefore</strong>,\n        in consideration of the mutual promises contained herein and other\n        good and valuable consideration, the receipt and sufficiency of which\n        is hereby acknowledged, the parties agree as follows:\n    </p>\n    <ol>\n        <li><strong>Services.</strong> Photographer hereby agrees to provide\n            the photography services set forth on the attached Statement of\n            Services (the \"Services\") to the best of her abilities.</li>\n        <li><strong>Compensation.</strong> In consideration of the Services,\n            Client agrees to pay Photographer the following amounts as follows:\n            <p>\n                            <span class=\"contract-line-item\"> Hugs: $20 / Kiss\n                    <br>\n                </span>\n                            <span class=\"contract-line-item\"><br>Kisses: $50 / Unit <br></span>\n                <br>  <br>Checks should\n                be made payable to <em>Saperstone Studios</em> and mailed to <em>5012\n                    Whisper Willow Dr, Fairfax VA 22030</em>. Final balance is due with\n                delivered invoice, paid no later than 30 days of delivery, in\n                compliance with Terms and Conditions.\n            </p></li>\n        <li><strong>Session Details.</strong> The above session with take\n            place at the below location on 2021-10-13 <br> 1234 Sesame Street</li>\n        <li><strong>Term.</strong> The initial term of this Contract shall\n            commence on the date hereof and terminate upon completion of the\n            services. Client may terminate this Contract at any time upon 30 days\n            advance written notice. Client shall be liable for the expenses\n            enumerated upon termination. Photographer shall have the right to\n            terminate this Agreement upon 30 days advance written notice and upon\n            return of amounts paid for remaining Services not provided, less\n            expenses.</li>\n        <li><strong>Standard Terms.</strong> Attached hereto is a statement of\n            Standard Terms and Conditions which will apply to the relationship\n            between Photographer and Client; such terms and conditions are\n            incorporated herein by this reference.</li>\n    </ol>\n    <p>\n        <strong>In witness whereof</strong>, the undersigned have caused this\n        Contract to be executed as of the date first above written.\n    \n    \n    </p><h4>Client:</h4>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Name:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <input id=\"contract-name-signature\" class=\"form-control keep\" type=\"text\" placeholder=\"Client Name\" value=\"\">\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Signature:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <div id=\"contract-signature\" class=\"signature\"></div>\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Address:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <textarea id=\"contract-address\" class=\"form-control keep\" type=\"text\" placeholder=\"Client Address\" value=\"\">Address</textarea>\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Phone Number:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <input id=\"contract-number\" class=\"form-control keep\" type=\"tel\" placeholder=\"Client Phone Number\" value=\"1234567890\">\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Email: </strong>\n        </div>\n        <div class=\"col-md-9\">\n            <input id=\"contract-email\" class=\"form-control keep\" type=\"email\" placeholder=\"Client Email\" value=\"email-address\">\n        </div>\n    </div>\n    <h4>Photographer:</h4>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Name:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            Saperstone Studios<br> Leigh Ann Saperstone\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Address: </strong>\n        </div>\n        <div class=\"col-md-9\">\n            5012 Whisper Willow Dr.<br>Fairfax, VA 22030\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Phone Number:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <a target=\"_blank\" href=\"tel:5712660004\">(571) 266-0004</a>\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Email:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <a target=\"_blank\" href=\"mailto:contracts@saperstonestudios.com\">contracts@saperstonestudios.com</a>\n        </div>\n    </div>\n    <p></p>\n    <h3>Exhibit A: Statement Of Services</h3>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Project/Assignment:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            Some session\n        </div>\n    </div>\n    Up to one hour photo session to include:<br><br>Business Headshots:<br>- Web gallery for viewing/making selects<br>- One file of your choice, color corrected/retouched per person<br>- Additional files can be purchased for $80/file<br>- Print release for marketing/web needs for purchased/included files<br><br>Office Photos:<br>- All images delivered via web gallery for download<br>- Print release for marketing/web needs\n    <h3>Standard Terms and Conditions</h3>\n    <p>\n        <strong>Copyright.</strong> The photographs produced by Photographer\n        shall be the intellectual property and copyrighted works of\n        Photographer, and shall not be deemed to be works made for hire.\n        Photographer agrees to give, upon payment in full, a limited usage\n        license to Client for reproduction and display of selected\n        photographs, including private or internal use by Client or its\n        employees and reasonable use in business communications by Client\n        (such as website, newsletter or annual report). Additionally, the\n        files delivered may only be reproduced for Clientâ€™s use and cannot\n        under any circumstance be sold to, licensed to, or used by a\n        third-party without written authorization from Photographer.\n        Photographer retains creative control over the images produced and all\n        decisions made by Photographer with regards to edits are deemed final.\n    </p>\n    <p>\n        <strong>Credit.</strong> In any publication, display, exhibit or other\n        permitted use of the photographs of Photographer, Client agrees to\n        include photo credit to Photographer and include a copyright notice\n        evidence of Photographerâ€™s ownership thereof. In addition, in any\n        publication in which the artists are recognized, Client shall include\n        a brief biography of Photographer approved by Photographer of\n        comparable position and prominence to other artists whose work is\n        included.\n    </p>\n    <p>\n        <strong>Retouch.</strong> Retouching is included but at the\n        Photographers discretion. Any additional retouch requests are handled\n        on a case by case basis and a separate fee may be negotiated.\n    </p>\n    <p>\n        <strong>Scheduling.</strong> Client will be responsible for insuring\n        access and authority to enable Photographer to shoot on the dates set\n        forth. If Photographer is unable to shoot on the dates scheduled for\n        reasons beyond Photographerâ€™s or Clientâ€™s reasonable control, the\n        parties will endeavor to agree on mutually acceptable alternative\n        dates, subject to Photographer availability.\n    </p>\n    <p>\n        <strong>Color Changes.</strong> Client recognizes that color dyes in\n        photography may fade or discolor over time due to the inherent\n        qualities of dyes; Photographer shall have no liability for any claims\n        based upon fading or discoloration due to such inherent qualities.\n    </p>\n    <p>\n        <strong>Advertising.</strong> Client gives Photographer permission to\n        use photographs for advertising (example: website).\n    </p>\n    <p>\n        <strong>Payment &amp; Charges.</strong> If any payment due Photographer is\n        not be made within 30 days of its due date, Photographer shall have\n        the options to assess a service charge equal 1.5% of the outstanding\n        amount each month (or fraction thereof) thereafter that payment is\n        late. If Photographer institutes any proceeding to obtain payment from\n        Client, Client will pay any costs and expenses (including reasonable\n        attorney\'s fees) incurred by Photographer in connection therewith. If\n        check payment does not process, client is liable for all processing,\n        late and overdraft charges.\n    </p>\n    <p>\n        <strong>Limit on Liability.</strong> Absent willful misconduct or\n        infringement by Photographer of intellectual property rights of any\n        third party, the liability of Photographer to Client arising out of\n        the Services shall not exceed the fees paid to Photographer hereunder.\n        In no event shall Photographer have any liability for special,\n        incidental, indirect, consequential or punitive damages hereunder.\n    </p>\n    <p>\n        <strong>Permissions.</strong> Client shall be solely responsible for\n        obtaining all permissions and consent of persons whose photographs are\n        taken and of owners of property and indemnify Photographer from any\n        claims, costs and expenses arising from any failure to do so.\n    </p>\n    <p>\n        <strong>Relationship.</strong> The relationship between Photographer\n        and Client is one of independent contractor. Photographer shall be\n        responsible for payment of all federal, state and local income,\n        payroll and withholding taxes and assessments arising from or\n        attributable to receipt of payments hereunder.\n    </p>\n    <p>\n        <strong>Miscellaneous.</strong> (a) The provisions hereof shall\n        survive termination of this Agreement; (b) this Agreement will be\n        governed by the laws of the State of Virginia (c) this Agreement\n        contains the entire agreement between the parties with respect to the\n        subject matter hereof and supersedes all prior understandings among\n        the parties with respect thereto; this Agreement may be changed only\n        in writing signed by the party against whom enforcement is sought; (d)\n        this Agreement may be executed in counterpart originals, each of which\n        shall constitute an original and all of which together shall\n        constitute a single document and shall be effective upon execution by\n        both parties; and (e) this Agreement shall be binding upon the parties\n        hereto and their respective successors and assigns; no party shall\n        assign its duties or obligations hereunder without the prior written\n        consent of the other party hereto.\n    </p>\n', NULL, NULL, NULL)");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `contracts` WHERE `contracts`.`id` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `contracts`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `contracts` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/update-contract.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/update-contract.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testWithIdNoContractId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Contract id is required', (string)$response->getBody());
    }

    public function testWithIdBlankContractId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Contract id can not be blank', (string)$response->getBody());
    }

    public function testWithIdBadContractId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '998'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Contract id does not match any contracts', (string)$response->getBody());
    }

    public function testNoType() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract type is required", (string)$response->getBody());
    }

    public function testBlankType() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999',
                'type' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract type can not be blank", (string)$response->getBody());
    }

    public function testNoName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999',
                'type' => 'wedding'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract name is required", (string)$response->getBody());
    }

    public function testBlankName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999',
                'type' => 'wedding',
                'name' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract name can not be blank", (string)$response->getBody());
    }

    public function testNoSession() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999',
                'type' => 'wedding',
                'name' => 'MaxMaxMax'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract session is required", (string)$response->getBody());
    }

    public function testBlankSession() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999',
                'type' => 'wedding',
                'name' => 'MaxMaxMax',
                'session' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract session can not be blank", (string)$response->getBody());
    }

    public function testNoContent() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999',
                'type' => 'wedding',
                'name' => 'MaxMaxMax',
                'session' => 'funsies'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract content is required", (string)$response->getBody());
    }

    public function testBlankContent() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999',
                'type' => 'wedding',
                'name' => 'MaxMaxMax',
                'session' => 'funsies',
                'content' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract content can not be blank", (string)$response->getBody());
    }

    public function testNoDetails() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-contract.php', [
                'form_params' => [
                    'id' => '999',
                    'type' => 'wedding',
                    'name' => 'MaxMaxMax',
                    'session' => 'funsies',
                    'content' => 'my awesome contract!'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $contractDetails = $this->sql->getRow("SELECT * FROM `contracts` WHERE `contracts`.`id` = 999;");
            $this->assertEquals(999, $contractDetails['id']);
            $this->assertEquals('8e07fb32bf072e1825df8290a7bcdc57', $contractDetails['link']);
            $this->assertEquals('wedding', $contractDetails['type']);
            $this->assertEquals('MaxMaxMax', $contractDetails['name']);
            $this->assertEquals('Address', $contractDetails['address']);
            $this->assertEquals('1234567890', $contractDetails['number']);
            $this->assertEquals('email-address', $contractDetails['email']);
            $this->assertEquals('2021-10-13', $contractDetails['date']);
            $this->assertEquals('1234 Sesame Street', $contractDetails['location']);
            $this->assertEquals('funsies', $contractDetails['session']);
            $this->assertStringStartsWith('Up to one hour photo session to include:', $contractDetails['details']);
            $this->assertEquals(0, $contractDetails['amount']);
            $this->assertEquals(0, $contractDetails['deposit']);
            $this->assertEquals('nope!', $contractDetails['invoice']);
            $this->assertEquals('my awesome contract!', $contractDetails['content']);
            $this->assertNull($contractDetails['signature']);
            $this->assertNull($contractDetails['initial']);
            $this->assertNull($contractDetails['file']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `contract_line_items` WHERE `contract_line_items`.`contract` = 999;"));
        } finally {
            $this->sql->executeStatement("DELETE FROM `contracts` WHERE `contracts`.`id` = 999;");
            $this->sql->executeStatement("DELETE FROM `contract_line_items` WHERE `contract_line_items`.`contract` = 999;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `contracts`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `contracts` AUTO_INCREMENT = $count;");
        }
    }

    public function testBadDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-contract.php', [
            'form_params' => [
                'id' => '999',
                'type' => 'wedding',
                'name' => 'MaxMaxMax',
                'session' => 'funsies',
                'content' => 'my awesome contract!',
                'date' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract date is not the correct format", (string)$response->getBody());
    }

    public function testAllDetails() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-contract.php', [
                'form_params' => [
                    'id' => '999',
                    'type' => 'wedding',
                    'name' => 'MaxMaxMax',
                    'session' => 'funsies',
                    'content' => 'my awesome contract!',
                    'amount' => '$25.25',
                    'deposit' => 9.267,
                    'address' => '123 Seasame Street',
                    'number' => '12345 F Off',
                    'email' => 'msaperst+sstest@gmail.com',
                    'date' => '2020-12-01',
                    'location' => 'Universal Studios',
                    'details' => 'None you care about',
                    'invoice' => 'link here!!!',
                    'lineItems' => [
                        0 => [
                            'amount' => 12.0,
                            'item' => 'snuggles',
                            'unit' => 'hugs'
                        ],
                        1 => [
                            'amount' => '$12.45'
                        ]
                    ]
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $contractDetails = $this->sql->getRow("SELECT * FROM `contracts` WHERE `contracts`.`id` = 999;");
            $this->assertEquals(999, $contractDetails['id']);
            $this->assertEquals('8e07fb32bf072e1825df8290a7bcdc57', $contractDetails['link']);
            $this->assertEquals('wedding', $contractDetails['type']);
            $this->assertEquals('MaxMaxMax', $contractDetails['name']);
            $this->assertEquals('123 Seasame Street', $contractDetails['address']);
            $this->assertEquals('12345 F Off', $contractDetails['number']);
            $this->assertEquals('msaperst+sstest@gmail.com', $contractDetails['email']);
            $this->assertEquals('2020-12-01', $contractDetails['date']);
            $this->assertEquals('Universal Studios', $contractDetails['location']);
            $this->assertEquals('funsies', $contractDetails['session']);
            $this->assertEquals('None you care about', $contractDetails['details']);
            $this->assertEquals(25.25, $contractDetails['amount']);
            $this->assertEquals(9.27, $contractDetails['deposit']);
            $this->assertEquals('link here!!!', $contractDetails['invoice']);
            $this->assertEquals('my awesome contract!', $contractDetails['content']);
            $this->assertNull($contractDetails['signature']);
            $this->assertNull($contractDetails['initial']);
            $this->assertNull($contractDetails['file']);
            $contractLineItems = $this->sql->getRows("SELECT * FROM `contract_line_items` WHERE `contract_line_items`.`contract` = 999;");
            $this->assertEquals(2, sizeOf($contractLineItems));
            $this->assertEquals(999, $contractLineItems[0]['contract']);
            $this->assertEquals(12.0, $contractLineItems[0]['amount']);
            $this->assertEquals('snuggles', $contractLineItems[0]['item']);
            $this->assertEquals('hugs', $contractLineItems[0]['unit']);
            $this->assertEquals(999, $contractLineItems[1]['contract']);
            $this->assertEquals(12.45, $contractLineItems[1]['amount']);
            $this->assertEquals('', $contractLineItems[1]['item']);
            $this->assertEquals('', $contractLineItems[1]['unit']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `contracts` WHERE `contracts`.`id` = 999;");
            $this->sql->executeStatement("DELETE FROM `contract_line_items` WHERE `contract_line_items`.`contract` = 999;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `contracts`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `contracts` AUTO_INCREMENT = $count;");
        }
    }
}