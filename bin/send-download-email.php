<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: send-download-email.php <job-token>\n");
    exit(2);
}

$jobToken = $argv[1];

try {
    $job = DownloadEmailJob::load($jobToken);
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(2);
}

$emailAddress = $job['email'];
$file = $job['file'];
$exitCode = 0;

try {
    $emailDelay = max(0, (int)getenv('SEND_EMAIL_AFTER'));
    if ($emailDelay > 0) {
        sleep($emailDelay);
    }

    $maxWaitSeconds = getenv('DOWNLOAD_FILE_WAIT_SECONDS');
    $maxWaitSeconds = $maxWaitSeconds === false ? 1200 : max(0, (int)$maxWaitSeconds);

    $counter = 0;
    while (!file_exists($file) && $counter < $maxWaitSeconds) {
        sleep(1);
        $counter++;
    }

    if (!file_exists($file)) {
        $exitCode = 1;
    } else {
        $from = "noreply@saperstonestudios.com";
        $to = "$emailAddress <$emailAddress>";
        $email = new Email($to, $from, "Your Download Is Ready");

        $publicFile = '/tmp/' . basename($file);
        $url = 'https://saperstonestudios.com' . $publicFile;
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = "<html><body>";
        $html .= "<p>Your download is ready</p>";
        $text = "Your download is ready\n\n";
        $html .= "<p>You can access your photos at <a href='" . $safeUrl . "'>" . $safeUrl . "</a></p>";
        $text = "You can access your photos at " . $url . "\n\n";
        $html .= "<p>This download will be available for the next 48 hours</p>";
        $text .= "This download will be available for the next 48 hours";
        $html .= "</body></html>";

        $email->setHtml($html);
        $email->setText($text);
        try {
            $email->sendEmail();
        } catch (Exception) {
            // Preserve legacy behavior: notification failures do not change the worker exit status.
        }
    }
} finally {
    DownloadEmailJob::delete($jobToken);
}

exit($exitCode);
