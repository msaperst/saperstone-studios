<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();

if ($argc < 3) {
    fwrite(STDERR, "Usage: send-download-email.php <email> <file>\n");
    exit(2);
}

$email = $argv[1];
$file = $argv[2];

$maxWaitSeconds = getenv('DOWNLOAD_FILE_WAIT_SECONDS');
$maxWaitSeconds = $maxWaitSeconds === false ? 1200 : max(0, (int)$maxWaitSeconds);

$counter = 0;
while (!file_exists($file) && $counter < $maxWaitSeconds) {
    sleep(1);
    $counter++;
}

if (!file_exists($file)) {
    exit(1);
}

// send email
$from = "noreply@saperstonestudios.com";
$to = "$email <$email>";
$email = new Email($to, $from, "Your Download Is Ready");

$html = "<html><body>";
$html .= "<p>Your download is ready</p>";
$text = "Your download is ready\n\n";
$html .= "<p>You can access your photos at <a href='https://saperstonestudios.com" . substr($file,2) ."'>https://saperstonestudios.com" . substr($file,2) ."</a></p>";
$text = "You can access your photos at https://saperstonestudios.com" . substr($file,2) . "\n\n";
$html .= "<p>This download will be available for the next 48 hours</p>";
$text .= "This download will be available for the next 48 hours";
$html .= "</body></html>";

$email->setHtml($html);
$email->setText($text);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing...
}
exit();
