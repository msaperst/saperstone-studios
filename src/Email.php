<?php

require_once 'autoloader.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use cbschuld\Browser;

class Email {

    private $email_user_creds = 'EMAIL_USER';
    private $email_pass_creds = 'EMAIL_PASS';
    private $headers;
    private $message;
    private $smtp;
    private $logLocation = '/var/www/logs';
    private $myFile = '/var/www/logs/emails.txt';

    function __construct($to, $from, $subject) {
        if (Strings::endsWith($to, "@saperstonestudios.com>")) {
            // in order to ensure LA gets these sent emails in her inbox, we'll send them from her ss gmail instead of @ss domain if they're to and from her
            $this->email_user_creds = 'EMAIL_USER_X';
            $this->email_pass_creds = 'EMAIL_PASS_X';
        }
        $this->headers = array(
            'Reply-To' => $from,
            'From' => $from,
            'To' => $to,
            'Subject' => $subject
        );
        $this->smtp = Mail::factory('smtp', array(
            'host' => getenv('EMAIL_HOST'),
            'port' => getenv('EMAIL_PORT'),
            'auth' => true,
            'username' => getenv($this->email_user_creds),
            'password' => getenv($this->email_pass_creds)
        ));
        $this->message = new Mail_mime ("\n");

        if (!file_exists($this->logLocation)) {
            mkdir($this->logLocation, 0700);
        }
    }

    /**
     * Helper to safely fetch geo info from ipinfo.io without generating PHP warnings.
     */
    private function fetchGeoInfo($IP) {
        // The @ operator suppresses the E_WARNING on a 429 or network failure
        $response = @file_get_contents("http://ipinfo.io/$IP/json");

        if ($response === false) {
            return (object)[]; // Return empty object to prevent property access errors
        }

        $data = json_decode($response);
        return $data ? $data : (object)[];
    }

    /**
     * Helper to sanitize and retrieve resolution input.
     */
    private function getResolution() {
        $sql = new Sql();
        return (isset($_POST['resolution']) && $_POST['resolution'] != "")
            ? $sql->escapeString($_POST['resolution'])
            : "";
    }

    public function setHtml($html) {
        $this->message->setHTMLBody($html);
    }

    public function setText($text) {
        $this->message->setTXTBody($text);
    }

    public function addAttachment($file) {
        $this->message->addAttachment($file);
    }

    public function getUserInfoHtml() {
        $browser = new Browser();
        $session = new Session();
        $IP = $session->getClientIP();
        $geo_info = $this->fetchGeoInfo($IP);
        $resolution = $this->getResolution();

        $html = "";
        if (!isset($geo_info->city)) {
            $html .= "<strong>Location</strong>: unknown (use $IP to manually lookup)<br/>";
        } else {
            $locationStr = $geo_info->city . ", " . $geo_info->region;
            if (isset($geo_info->postal)) {
                $locationStr .= " " . $geo_info->postal;
            }
            $html .= "<strong>Location</strong>: " . $locationStr . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";

            if (isset($geo_info->hostname)) {
                $html .= "<strong>Hostname</strong>: " . $geo_info->hostname . "<br/>";
            }
        }

        $html .= "<strong>Browser</strong>: " . $browser->getBrowser() . " " . $browser->getVersion() . "<br/>";
        $html .= "<strong>Resolution</strong>: $resolution<br/>";
        $html .= "<strong>OS</strong>: " . $browser->getPlatform() . "<br/>";
        $html .= "<strong>Full UA</strong>: " . $_SERVER['HTTP_USER_AGENT'] . "<br/>";
        return $html;
    }

    public function getUserInfoText() {
        $browser = new Browser();
        $session = new Session();
        $IP = $session->getClientIP();
        $geo_info = $this->fetchGeoInfo($IP);
        $resolution = $this->getResolution();

        $text = "";
        if (!isset($geo_info->city)) {
            $text .= "Location: unknown (use $IP to manually lookup)\n";
        } else {
            $locationStr = $geo_info->city . ", " . $geo_info->region;
            if (isset($geo_info->postal)) {
                $locationStr .= " " . $geo_info->postal;
            }
            $text .= "Location: " . $locationStr . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";

            if (isset($geo_info->hostname)) {
                $text .= "Hostname: " . $geo_info->hostname . "\n";
            }
        }

        $text .= "Browser: " . $browser->getBrowser() . " " . $browser->getVersion() . "\n";
        $text .= "Resolution: $resolution\n";
        $text .= "OS: " . $browser->getPlatform() . "\n";
        $text .= "Full UA: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
        return $text;
    }

    public function sendEmail() {
        $body = $this->message->get();
        $this->headers = $this->message->headers($this->headers);
        $mail = $this->smtp->send($this->headers['To'], $this->headers, $body);

        $fh = fopen($this->myFile, 'a') or die ("can't open file");
        fwrite($fh, "From: " . $this->headers['From'] . "\n");
        fwrite($fh, "To: " . $this->headers['To'] . "\n");
        fwrite($fh, "Date: " . date('l jS \of F Y h:i:s A') . "\n");
        fwrite($fh, "Subject: " . $this->headers['Subject'] . "\n");
        fwrite($fh, $this->message->getTXTBody() . "\n");
        fwrite($fh, "=====================================================\n");
        fwrite($fh, "=====================================================\n");
        fclose($fh);

        if (PEAR::isError($mail)) {
            throw new Exception($mail->getMessage());
        }
    }
}
