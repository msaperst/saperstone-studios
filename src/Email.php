<?php

require 'autoloader.php';
require 'Mail.php';
require 'Mail/mime.php';

class Email {

    private $email_user_creds = 'EMAIL_USER';
    private $email_pass_creds = 'EMAIL_PASS';
    private $headers;
    private $message;
    private $smtp;
    private $logLocation = '/var/www/logs';
    private $myFile;

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
        $this->myFile = $this->logLocation . '/emails.txt';
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
        $sql = new Sql();
        $IP = $session->getClientIP();
        $geo_info = json_decode(file_get_contents("http://ipinfo.io/$IP/json"));
        $resolution = "";
        if (isset ($_POST ['resolution']) && $_POST ['resolution'] != "") {
            $resolution = $sql->escapeString($_POST ['resolution']);
        }
        $html = "";
        if (!isset ($geo_info->city)) {
            $html .= "<strong>Location</strong>: unknown (use $IP to manually lookup)<br/>";
        } else {
            if (isset ($geo_info->postal)) {
                $html .= "<strong>Location</strong>: " . $geo_info->city . ", " . $geo_info->region . " " . $geo_info->postal . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";
            } else {
                $html .= "<strong>Location</strong>: " . $geo_info->city . ", " . $geo_info->region . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";
            }
            $html .= "<strong>Hostname</strong>: " . $geo_info->hostname . "<br/>";
        }
        $html .= "<strong>Browser</strong>: " . $browser->getBrowser() . " " . $browser->getVersion() . "<br/>";
        $html .= "<strong>Resolution</strong>: $resolution<br/>";
        $html .= "<strong>OS</strong>: " . $browser->getPlatform() . "<br/>";
        $html .= "<strong>Full UA</strong>: " . $_SERVER ['HTTP_USER_AGENT'] . "<br/>";
        return $html;
    }

    public function getUserInfoText() {
        $browser = new Browser();
        $session = new Session();
        $sql = new Sql();
        $IP = $session->getClientIP();
        $geo_info = json_decode(file_get_contents("http://ipinfo.io/$IP/json"));
        $resolution = "";
        if (isset ($_POST ['resolution']) && $_POST ['resolution'] != "") {
            $resolution = $sql->escapeString($_POST ['resolution']);
        }
        $text = "";
        if (!isset ($geo_info->city)) {
            $text .= "Location: unknown (use $IP to manually lookup)\n";
        } else {
            if (isset ($geo_info->postal)) {
                $text .= "Location: " . $geo_info->city . ", " . $geo_info->region . " " . $geo_info->postal . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";
            } else {
                $text .= "Location: " . $geo_info->city . ", " . $geo_info->region . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";
            }
            $text .= "Hostname: " . $geo_info->hostname . "\n";
        }
        $text .= "Browser: " . $browser->getBrowser() . " " . $browser->getVersion() . "\n";
        $text .= "Resolution: $resolution";
        $text .= "OS: " . $browser->getPlatform() . "\n";
        $text .= "Full UA: " . $_SERVER ['HTTP_USER_AGENT'] . "\n";
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
            return $mail->getMessage();
        } else {
            return NULL;
        }
    }
}